<?php
/**
 * Copyright 2017 Lengow SAS.
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
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * Lengow Hook Class
 */
class LengowHook
{
    /**
     * @var string name of Prestashop homepage
     */
    const LENGOW_TRACK_HOMEPAGE = 'homepage';

    /**
     * @var string name of Prestashop classic page
     */
    const LENGOW_TRACK_PAGE = 'page';

    /**
     * @var string name of Prestashop listepage page
     */
    const LENGOW_TRACK_PAGE_LIST = 'listepage';

    /**
     * @var string name of Prestashop payment page
     */
    const LENGOW_TRACK_PAGE_PAYMENT = 'payment';

    /**
     * @var string name of Prestashop basket page
     */
    const LENGOW_TRACK_PAGE_CART = 'basket';

    /**
     * @var string name of Prestashop confirmation page
     */
    const LENGOW_TRACK_PAGE_CONFIRMATION = 'confirmation';

    /**
     * @var string Prestashop current page type
     */
    static private $currentPageType = 'page';

    /**
     * @var string Prestashop order id
     */
    static private $idOrder = '';

    /**
     * @var string Prestashop cart id
     */
    static private $idCart = '';

    /**
     * @var string order payment
     */
    static private $orderPayment = '';

    /**
     * @var string order currency
     */
    static private $orderCurrency = '';

    /**
     * @var string total order
     */
    static private $orderTotal = '';

    /**
     * @var string product cart ids
     */
    static private $idsProductCart = '';

    /**
     * @var string Prestashop category id
     */
    static private $idCategory = '';

    /**
     * @var array order is already shipped
     */
    protected $alreadyShipped = array();

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
     * @return boolean
     */
    public function registerHooks()
    {
        $error = false;
        $lengowHooks = array(
            // common version
            'footer' => '1.4',
            'postUpdateOrderStatus' => '1.4',
            'paymentTop' => '1.4',
            'adminOrder' => '1.4',
            'home' => '1.4',
            'updateOrderStatus' => '1.4',
            'orderConfirmation' => '1.4',
            // version 1.5
            'actionObjectUpdateAfter' => '1.5',
            // version 1.6
            'displayBackOfficeHeader' => '1.6',
        );
        foreach ($lengowHooks as $hook => $version) {
            if ($version <= Tools::substr(_PS_VERSION_, 0, 3)) {
                if (!$this->module->registerHook($hook)) {
                    LengowMain::log(
                        'Install',
                        LengowMain::setLogMessage('log.install.registering_hook_error', array('hook' => $hook))
                    );
                    $error = true;
                } else {
                    LengowMain::log(
                        'Install',
                        LengowMain::setLogMessage('log.install.registering_hook_success', array('hook' => $hook))
                    );
                }
            }
        }
        return $error ? false : true;
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
        if (!LengowConfiguration::get('LENGOW_TRACKING_ENABLED')) {
            return '';
        }
        $currentController = $this->context->controller;
        if ($currentController instanceof OrderConfirmationController) {
            self::$currentPageType = self::LENGOW_TRACK_PAGE_CONFIRMATION;
        } elseif ($currentController instanceof ProductController) {
            self::$currentPageType = self::LENGOW_TRACK_PAGE;
        } elseif ($currentController instanceof OrderController) {
            if ($currentController->step == -1 || $currentController->step == 0) {
                self::$currentPageType = self::LENGOW_TRACK_PAGE_CART;
            } elseif ($currentController instanceof IndexController) {
                self::$currentPageType = self::LENGOW_TRACK_HOMEPAGE;
            }
        }
        // id category
        if (!(self::$idCategory = (int)Tools::getValue('id_category'))) {
            if (isset($_SERVER['HTTP_REFERER'])
                && preg_match(
                    '!^(.*)\/([0-9]+)\-(.*[^\.])|(.*)id_category=([0-9]+)(.*)$!',
                    $_SERVER['HTTP_REFERER'],
                    $regs
                )
                && !strstr($_SERVER['HTTP_REFERER'], '.html')
            ) {
                if (isset($regs[2]) && is_numeric($regs[2])) {
                    self::$idCategory = (int)$regs[2];
                } elseif (isset($regs[5]) && is_numeric($regs[5])) {
                    self::$idCategory = (int)$regs[5];
                }
            } elseif ($idProduct = (int)Tools::getValue('id_product')) {
                try {
                    $product = new Product($idProduct);
                    self::$idCategory = $product->id_category_default;
                } catch (Exception $e) {
                    self::$idCategory = 0;
                }
            }
            if (self::$idCategory == 0) {
                self::$idCategory = '';
            }
        } else {
            self::$currentPageType = self::LENGOW_TRACK_PAGE_LIST;
        }
        // basket
        if (self::$currentPageType === self::LENGOW_TRACK_PAGE_CART ||
            self::$currentPageType === self::LENGOW_TRACK_PAGE_PAYMENT
        ) {
            try {
                $orderTotal = $this->context->cart->getOrderTotal();
            } catch (Exception $e) {
                $orderTotal = 0;
            }
            self::$orderTotal = $orderTotal;
        }
        // product ids
        if (self::$currentPageType === self::LENGOW_TRACK_PAGE_LIST
            || self::$currentPageType === self::LENGOW_TRACK_PAGE
            || self::$currentPageType === self::LENGOW_TRACK_PAGE_CART
        ) {
            $productsCart = array();
            $products = isset($this->context->smarty->tpl_vars['products'])
                ? $this->context->smarty->tpl_vars['products']->value
                : array();
            if (!empty($products)) {
                $i = 1;
                foreach ($products as $p) {
                    if (is_object($p)) {
                        switch (LengowConfiguration::get('LENGOW_TRACKING_ID')) {
                            case 'upc':
                                $idProduct = $p->upc;
                                break;
                            case 'ean':
                                $idProduct = $p->ean13;
                                break;
                            case 'ref':
                                $idProduct = $p->reference;
                                break;
                            default:
                                if (isset($p->id_product_attribute)) {
                                    $idProduct = $p->id . '_' . $p->id_product_attribute;
                                } else {
                                    $idProduct = $p->id;
                                }
                                break;
                        }
                        $productDatas = array(
                            'product_id' => $idProduct,
                            'price' => isset($p->price_wt) ? $p->price_wt : $p->price,
                            'quantity' => $p->quantity,
                        );
                    } else {
                        switch (LengowConfiguration::get('LENGOW_TRACKING_ID')) {
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
                                if (array_key_exists('id_product_attribute', $p) && $p['id_product_attribute']) {
                                    $idProduct = $p['id_product'] . '_' . $p['id_product_attribute'];
                                } else {
                                    $idProduct = $p['id_product'];
                                }
                                break;
                        }
                        $productDatas = array(
                            'product_id' => $idProduct,
                            'price' => isset($p['price_wt']) ? $p['price_wt'] : $p['price'],
                            'quantity' => $p['quantity'],
                        );
                    }
                    $productsCart[] = $productDatas;
                    $i++;
                }
            }
            self::$idsProductCart = Tools::jsonEncode($productsCart);
        }
        if (!isset($this->smarty)) {
            $this->smarty = $this->context->smarty;
        }
        // generate Lengow tracker
        if (self::$currentPageType === self::LENGOW_TRACK_PAGE_CONFIRMATION) {
            $this->context->smarty->assign(
                array(
                    'account_id' => LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_ID'),
                    'order_ref' => self::$idOrder,
                    'amount' => self::$orderTotal,
                    'currency_order' => self::$orderCurrency,
                    'payment_method' => self::$orderPayment,
                    'cart' => self::$idsProductCart,
                    'cart_number' => self::$idCart,
                    'newbiz' => 1,
                    'valid' => 1,
                    'page_type' => self::$currentPageType,
                )
            );
            return $this->module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/front/tagpage.tpl');
        }
        return '';
    }

    /**
     * Hook on order confirmation page to init order's product list
     *
     * @param array $args arguments of hook
     */
    public function hookOrderConfirmation($args)
    {
        $i = 0;
        $productsCart = array();
        $order = isset($args['objOrder']) ? $args['objOrder'] : $args['order'];
        $orderTotal = Tools::ps_round($order->total_paid, 2);
        $paymentMethod = LengowMain::replaceAccentedChars(Tools::strtolower(str_replace(' ', '_', $order->payment)));
        $currency = new Currency($order->id_currency);
        $productsList = $order->getProducts();
        foreach ($productsList as $p) {
            $i++;
            switch (LengowConfiguration::get('LENGOW_TRACKING_ID')) {
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
            $productsCart[] = array(
                'product_id' => $idProduct,
                'price' => Tools::ps_round($price, 2),
                'quantity' => $p['product_quantity'],
            );
        }
        self::$idsProductCart = Tools::jsonEncode($productsCart);
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
        if (LengowOrder::isFromLengow($args['id_order'])) {
            $lengowOrder = new LengowOrder($args['id_order']);
            // get actions re-import, synchronize orders, add tracking and resend actions
            $lengowLink = new LengowLink();
            $locale = new LengowTranslation();
            $canResendAction = $lengowOrder->canReSendOrder();
            $canAddTracking = $lengowOrder->canAddTracking();
            $lengowOrderController = $lengowLink->getAbsoluteAdminLink('AdminLengowOrder');
            $baseAction = $lengowOrderController . '&id_order=' . $lengowOrder->id;
            $actionReimport = $baseAction . '&action=cancel_re_import';
            $actionSynchronize = $baseAction . '&action=synchronize';
            $actionAddTracking = $baseAction . '&action=add_tracking&tracking_number=';
            $orderCurrentState = (int)$lengowOrder->getCurrentState();
            $actionType = $orderCurrentState === LengowMain::getOrderState(LengowOrder::STATE_CANCELED)
                ? LengowAction::TYPE_CANCEL
                : LengowAction::TYPE_SHIP;
            $checkResendAction = $locale->t('admin.order.check_resend_action', array('action' => $actionType));
            $actionResend = $lengowOrderController . '&action=force_resend&action_type=' . $actionType;
            $sentMarketplace = $lengowOrder->lengowSentMarketplace
                ? $locale->t('product.screen.button_yes')
                : $locale->t('product.screen.button_no');
            $templateDatas = array(
                'marketplace_sku' => $lengowOrder->lengowMarketplaceSku,
                'id_flux' => $lengowOrder->lengowIdFlux,
                'delivery_address_id' => $lengowOrder->lengowDeliveryAddressId,
                'marketplace_label' => $lengowOrder->lengowMarketplaceLabel,
                'total_paid' => $lengowOrder->lengowTotalPaid,
                'commission' => $lengowOrder->lengowCommission,
                'currency' => $lengowOrder->lengowCurrency,
                'customer_name' => $lengowOrder->lengowCustomerName,
                'customer_email' => $lengowOrder->lengowCustomerEmail,
                'carrier' => $lengowOrder->lengowCarrier,
                'carrier_method' => $lengowOrder->lengowMethod,
                'carrier_tracking' => $lengowOrder->lengowTracking,
                'carrier_id_relay' => $lengowOrder->lengowIdRelay,
                'sent_marketplace' => $sentMarketplace,
                'message' => $lengowOrder->lengowMessage,
                'imported_at' => LengowMain::getDateInCorrectFormat(strtotime($lengowOrder->lengowDateAdd)),
                'action_synchronize' => $actionSynchronize,
                'action_reimport' => $actionReimport,
                'action_resend' => $actionResend,
                'action_add_tracking' => $actionAddTracking,
                'order_id' => $args['id_order'],
                'version' => _PS_VERSION_,
                'lengow_locale' => $locale,
                'debug_mode' => LengowConfiguration::debugModeIsActive(),
                'can_resend_action' => $canResendAction,
                'can_add_tracking' => $canAddTracking,
                'check_resend_action' => $checkResendAction,
            );
            $this->context->smarty->assign($templateDatas);
            if (_PS_VERSION_ >= '1.6') {
                return $this->module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/order/info_16.tpl');
            }
            return $this->module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/order/info.tpl');
        }
        return '';
    }

    /**
     * Hook before an status' update to synchronize status with lengow
     *
     * @param array $args arguments of hook
     */
    public function hookUpdateOrderStatus($args)
    {
        $lengowOrder = new LengowOrder($args['id_order']);
        // not send state if we are on lengow import module
        if (LengowOrder::isFromLengow($args['id_order']) &&
            LengowImport::$currentOrder !== $lengowOrder->lengowMarketplaceSku
        ) {
            LengowMain::disableMail();
        }
    }

    /**
     * Hook after an status' update to synchronize status with lengow
     *
     * @param array $args arguments of hook
     */
    public function hookPostUpdateOrderStatus($args)
    {
        $lengowOrder = new LengowOrder($args['id_order']);
        // do nothing if order is not from Lengow or is being imported
        if (LengowOrder::isFromLengow($args['id_order'])
            && LengowImport::$currentOrder !== $lengowOrder->lengowMarketplaceSku
            && !array_key_exists($lengowOrder->lengowMarketplaceSku, $this->alreadyShipped)
        ) {
            $newOrderState = $args['newOrderStatus'];
            $idOrderState = (int)$newOrderState->id;
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
     */
    public function hookActionObjectUpdateAfter($args)
    {
        if ($args['object'] instanceof Order) {
            if (LengowOrder::isFromLengow($args['object']->id)) {
                $lengowOrder = new LengowOrder($args['object']->id);
                if ($lengowOrder->shipping_number !== ''
                    && (int)$args['object']->current_state === LengowMain::getOrderState(LengowOrder::STATE_SHIPPED)
                    && LengowImport::$currentOrder !== $lengowOrder->lengowMarketplaceSku
                    && !array_key_exists($lengowOrder->lengowMarketplaceSku, $this->alreadyShipped)
                ) {
                    $lengowOrder->callAction(LengowAction::TYPE_SHIP);
                    $this->alreadyShipped[$lengowOrder->lengowMarketplaceSku] = true;
                }
            }
        }
    }
}
