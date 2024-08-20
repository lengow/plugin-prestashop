<?php
/**
 * Copyright 2021 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2021 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
/*
 * Lengow Hook Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowHook
{
    /* PrestaShop track pages */
    public const LENGOW_TRACK_HOMEPAGE = 'homepage';
    public const LENGOW_TRACK_PAGE = 'page';
    public const LENGOW_TRACK_PAGE_LIST = 'listepage';
    public const LENGOW_TRACK_PAGE_PAYMENT = 'payment';
    public const LENGOW_TRACK_PAGE_CART = 'basket';
    public const LENGOW_TRACK_PAGE_CONFIRMATION = 'confirmation';

    /**
     * @var string PrestaShop current page type
     */
    private static $currentPageType = 'page';

    /**
     * @var string PrestaShop order id
     */
    private static $idOrder = '';

    /**
     * @var string PrestaShop cart id
     */
    private static $idCart = '';

    /**
     * @var string order payment
     */
    private static $orderPayment = '';

    /**
     * @var string order currency
     */
    private static $orderCurrency = '';

    /**
     * @var string total order
     */
    private static $orderTotal = '';

    /**
     * @var string product cart ids
     */
    private static $idsProductCart = '';

    /**
     * @var string PrestaShop category id
     */
    private static $idCategory = '';

    /**
     * @var array order is already shipped
     */
    protected $alreadyShipped = [];

    /**
     * @var Lengow Lengow module instance
     */
    private $module;

    /**
     * Construct
     *
     * @param Lengow $module Lengow module instance
     */
    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
    }

    /**
     * Register Lengow Hook
     *
     * @return bool
     */
    public function registerHooks()
    {
        $error = false;
        $lengowHooks = [
            // common version
            'postUpdateOrderStatus' => '1.4',
            'paymentTop' => '1.4',
            'displayAdminOrder' => '1.4',
            'home' => '1.4',
            'actionOrderStatusUpdate' => '1.4',
            'orderConfirmation' => '1.4',
            // version 1.5
            'actionObjectUpdateAfter' => '1.5',
            // version 1.6
            'displayBackOfficeHeader' => '1.6',
        ];
        foreach ($lengowHooks as $hook => $version) {
            if ((float) $version <= (float) Tools::substr(_PS_VERSION_, 0, 3)) {
                if ($this->module->isRegisteredInHook($hook)) {
                    continue;
                }
                if (!$this->module->registerHook($hook)) {
                    LengowMain::log(
                        LengowLog::CODE_INSTALL,
                        LengowMain::setLogMessage('log.install.registering_hook_error', ['hook' => $hook])
                    );
                    $error = true;
                } else {
                    LengowMain::log(
                        LengowLog::CODE_INSTALL,
                        LengowMain::setLogMessage('log.install.registering_hook_success', ['hook' => $hook])
                    );
                }
            }
        }

        return !$error;
    }

    /**
     * Hook to display the icon
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCss(_PS_MODULE_LENGOW_DIR_ . 'views/css/lengow-tab.css');
    }

    /**
     * Hook on Home page
     */
    public function hookHome()
    {
        self::$currentPageType = self::LENGOW_TRACK_HOMEPAGE;
    }

    /**
     * Hook on Payment page
     */
    public function hookPaymentTop()
    {
        self::$currentPageType = self::LENGOW_TRACK_PAGE;
    }

    /**
     * Generate tracker on front footer page
     *
     * @return mixed
     */
    public function hookFooter()
    {
        // tracker is disabled now
        return '';
    }

    /**
     * Hook on order confirmation page to init order's product list
     *
     * @param array $args arguments of hook
     *
     * @return mixed null|void
     */
    public function hookOrderConfirmation($args)
    {
        if (!isset($args['objOrder']) && !isset($args['order'])) {
            return;
        }
        $i = 0;
        $productsCart = [];
        $order = isset($args['objOrder']) ? $args['objOrder'] : $args['order'];
        $orderTotal = Tools::ps_round($order->total_paid, 2);
        $paymentMethod = LengowMain::replaceAccentedChars(Tools::strtolower(str_replace(' ', '_', $order->payment)));
        $currency = new Currency($order->id_currency);
        $productsList = $order->getProducts();
        foreach ($productsList as $p) {
            ++$i;
            switch (LengowConfiguration::get(LengowConfiguration::TRACKING_ID)) {
                case 'upc':
                    $idProduct = $p['upc'];
                    break;
                case 'ean':
                    $idProduct = $p['ean13'];
                    break;
                case 'ref':
                    $idProduct = $p['reference'];
                    break;
                default:
                    if ($p['product_attribute_id']) {
                        $idProduct = $p['product_id'] . '_' . $p['product_attribute_id'];
                    } else {
                        $idProduct = $p['product_id'];
                    }
                    break;
            }
            $price = isset($p['product_price_wt']) ? $p['product_price_wt'] : $p['unit_price_tax_incl'];
            // basket product
            $productsCart[] = [
                'product_id' => $idProduct,
                'price' => Tools::ps_round($price, 2),
                'quantity' => $p['product_quantity'],
            ];
        }
        self::$idsProductCart = json_encode($productsCart);
        self::$currentPageType = self::LENGOW_TRACK_PAGE_CONFIRMATION;
        self::$idOrder = $order->id;
        self::$idCart = $order->id_cart;
        self::$orderTotal = $orderTotal;
        self::$orderPayment = $paymentMethod;
        self::$orderCurrency = $currency->iso_code;
    }

    /**
     * Hook on admin page's order
     *
     * @param array $args arguments of hook
     *
     * @return mixed
     */
    public function hookAdminOrder($args)
    {
        if (!isset($args['id_order'])) {
            return;
        }
        if (LengowOrder::isFromLengow($args['id_order'])) {
            $lengowLink = new LengowLink();
            $locale = new LengowTranslation();
            $lengowOrder = new LengowOrder($args['id_order']);
            $lengowOrderController = $lengowLink->getAbsoluteAdminLink('AdminLengowOrder');
            $baseAction = $lengowOrderController . '&id_order=' . $lengowOrder->id;
            $orderCurrentState = (int) $lengowOrder->getCurrentState();
            $actionType = $orderCurrentState === LengowMain::getOrderState(LengowOrder::STATE_CANCELED)
                ? LengowAction::TYPE_CANCEL
                : LengowAction::TYPE_SHIP;

            if (!empty($lengowOrder->lengowExtra)) {
                try {
                    $decoded = json_decode($lengowOrder->lengowExtra, true, 512, JSON_THROW_ON_ERROR);
                    $shipping_phone = $decoded['packages'][0]['delivery']['phone_mobile']
                        ?? $decoded['packages'][0]['delivery']['phone_home']
                        ?? $decoded['packages'][0]['delivery']['phone_office'];
                    $billing_phone = $decoded['billing_address']['phone_mobile']
                        ?? $decoded['billing_address']['phone_home']
                        ?? $decoded['billing_address']['phone_office'];
                } catch (JsonException $e) {
                }
            }

            $templateData = [
                'marketplace_sku' => $lengowOrder->lengowMarketplaceSku,
                'id_flux' => $lengowOrder->lengowIdFlux,
                'delivery_address_id' => $lengowOrder->lengowDeliveryAddressId,
                'marketplace_label' => $lengowOrder->lengowMarketplaceLabel,
                'total_paid' => $lengowOrder->lengowTotalPaid,
                'commission' => $lengowOrder->lengowCommission,
                'currency' => $lengowOrder->lengowCurrency,
                'customer_vat_number' => $lengowOrder->lengowCustomerVatNumber,
                'customer_name' => $lengowOrder->lengowCustomerName,
                'customer_email' => $lengowOrder->lengowCustomerEmail,
                'carrier' => $lengowOrder->lengowCarrier,
                'carrier_method' => $lengowOrder->lengowMethod,
                'carrier_tracking' => $lengowOrder->lengowTracking,
                'carrier_id_relay' => $lengowOrder->lengowIdRelay,
                'is_express' => $lengowOrder->isExpress(),
                'is_delivered_by_marketplace' => $lengowOrder->isDeliveredByMarketplace(),
                'is_business' => $lengowOrder->isBusiness(),
                'message' => $lengowOrder->lengowMessage,
                'imported_at' => $lengowOrder->date_add,
                'extra' => $lengowOrder->lengowExtra,
                'action_synchronize' => $baseAction . '&action=synchronize',
                'action_reimport' => $baseAction . '&action=cancel_re_import',
                'action_resend' => $baseAction . '&action=force_resend&action_type=' . $actionType,
                'lengow_locale' => $locale,
                'debug_mode' => LengowConfiguration::debugModeIsActive(),
                'can_resend_action' => $lengowOrder->canReSendOrder(),
                'check_resend_action' => $locale->t('admin.order.check_resend_action', ['action' => $actionType]),
                'customer_shipping_phone' => $shipping_phone ?? null,
                'customer_billing_phone' => $billing_phone ?? null,
            ];
            $this->context->smarty->assign($templateData);

            return $this->module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/order/info.tpl');
        }

        return '';
    }

    /**
     * Hook before an status' update to synchronize status with lengow
     *
     * @param array $args arguments of hook
     *
     * @return mixed null|void
     */
    public function hookUpdateOrderStatus($args)
    {
        if (!isset($args['id_order'])) {
            return;
        }
        if (!(bool) LengowConfiguration::get(LengowConfiguration::SEND_EMAIL_DISABLED)) {
            return;
        }
        $lengowOrder = new LengowOrder($args['id_order']);
        // not send state if we are on lengow import module
        if (LengowImport::$currentOrder !== $lengowOrder->lengowMarketplaceSku
            && LengowOrder::isFromLengow($args['id_order'])
        ) {
            LengowMain::disableMail();
        }
    }

    /**
     * Hook after an status' update to synchronize status with lengow
     *
     * @param array $args arguments of hook
     *
     * @return mixed null|void
     */
    public function hookPostUpdateOrderStatus($args)
    {
        if (!isset($args['id_order'])) {
            return;
        }
        $lengowOrder = new LengowOrder($args['id_order']);
        // do nothing if order is not from Lengow or is being imported
        if (LengowImport::$currentOrder !== $lengowOrder->lengowMarketplaceSku
            && !array_key_exists($lengowOrder->lengowMarketplaceSku, $this->alreadyShipped)
            && LengowOrder::isFromLengow($args['id_order'])
        ) {
            $newOrderState = $args['newOrderStatus'];
            $idOrderState = (int) $newOrderState->id;
            if ($idOrderState === LengowMain::getOrderState(LengowOrder::STATE_SHIPPED)) {
                $lengowOrder->callAction(LengowAction::TYPE_SHIP);
                $this->alreadyShipped[$lengowOrder->lengowMarketplaceSku] = true;
            }
            // call Lengow API WSDL to send refuse state order
            if ($idOrderState === LengowMain::getOrderState(LengowOrder::STATE_CANCELED)) {
                $lengowOrder->callAction(LengowAction::TYPE_CANCEL);
                $this->alreadyShipped[$lengowOrder->lengowMarketplaceSku] = true;
            }
        }
    }

    /**
     * Update, if isset tracking number
     *
     * @param array $args arguments of hook
     *
     * @return mixed null|void
     */
    public function hookActionObjectUpdateAfter($args)
    {
        if (!isset($args['object']->id)) {
            return;
        }
        if (!$args['object'] instanceof Order) {
            return;
        }
        if (($args['object'] instanceof Order) && LengowOrder::isFromLengow($args['object']->id)) {
            $lengowOrder = new LengowOrder($args['object']->id);

            // Check if the tracking field has been updated
            if (isset($args['object']->shipping_number) && !empty($args['object']->shipping_number)) {
                $trackingNumber = $args['object']->shipping_number;

                if ($lengowOrder->setWsShippingNumber($trackingNumber) !== ''
                    && LengowImport::$currentOrder !== $lengowOrder->lengowMarketplaceSku
                    && !array_key_exists($lengowOrder->lengowMarketplaceSku, $this->alreadyShipped)
                    && (int) $args['object']->current_state === LengowMain::getOrderState(LengowOrder::STATE_SHIPPED)
                ) {
                    $lengowOrder->callAction(LengowAction::TYPE_SHIP);
                    $this->alreadyShipped[$lengowOrder->lengowMarketplaceSku] = true;
                }
            }
        }
    }
}
