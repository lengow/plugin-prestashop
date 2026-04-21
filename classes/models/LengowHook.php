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
    /**
     * @var array<string, mixed> order is already shipped
     */
    protected array $alreadyShipped = [];

    /**
     * @var Lengow Lengow module instance
     */
    private Lengow $module;

    /**
     * @var Context PrestaShop context
     */
    private Context $context;

    /**
     * Construct
     *
     * @param Lengow $module Lengow module instance
     * @param Context $context PrestaShop context
     */
    public function __construct(Lengow $module, Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    /**
     * Register Lengow Hook
     *
     * @return bool
     */
    public function registerHooks(): bool
    {
        $error = false;
        $lengowHooks = [
            // common version
            'postUpdateOrderStatus' => '1.4',
            'displayPaymentTop' => '1.4',
            'displayAdminOrder' => '1.4',
            'actionOrderStatusUpdate' => '1.4',
            'orderConfirmation' => '1.4',
            // version 1.5
            'actionObjectUpdateAfter' => '1.5',
            // version 1.6
            'displayBackOfficeHeader' => '1.6',
            'displayAdminOrderSide' => '1.6',
            // version 8.0
            'displayHome' => '8.0',
            'actionOrderStatusPostUpdate' => '8.0',
            'actionProductCancel' => '8.0',
            // PS 8.0+ — order page tabs
            'displayAdminOrderTabLink' => '8.0',
            'displayAdminOrderTabContent' => '8.0',
            // PS routing: maps legacy webservice/*.php URLs to the FO controllers
            'moduleRoutes' => '1.5',
        ];

        foreach ($lengowHooks as $hook => $version) {
            if ((float) $version <= (float) Tools::substr(_PS_VERSION_, 0, 3)) {
                if ($this->isRegisteredForCurrentContext($hook)) {
                    continue;
                }
                try {
                    $registered = $this->module->registerHook($hook);
                } catch (PrestaShopDatabaseException|PrestaShopException $e) {
                    if (!str_contains($e->getMessage(), 'ps_hook_module.PRIMARY')) {
                        throw $e;
                    }
                    LengowMain::log(
                        LengowLog::CODE_INSTALL,
                        LengowMain::setLogMessage('log.install.registering_hook_success', ['hook' => $hook])
                    );
                    continue;
                }
                if (!$registered) {
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

        // Cleanup: If the deprecated 'home' hook is registered, unregister it to avoid
        // PrestaShop deprecation warnings. New hook name is 'displayHome'.
        if ($this->module->isRegisteredInHook('home')) {
            $this->module->unregisterHook('home');
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.unregistering_deprecated_hook', ['hook' => 'home'])
            );
        }

        return !$error;
    }

    /**
     * @param string $hook
     *
     * @return bool
     */
    private function isRegisteredForCurrentContext(string $hook): bool
    {
        $hookId = (int) Hook::getIdByName($hook);
        $moduleId = (int) $this->module->id;
        if ($hookId === 0 || $moduleId === 0) {
            return false;
        }

        $shopIds = Shop::getContextListShopID();
        if (empty($shopIds)) {
            $shopIds = Shop::getCompleteListOfShopsID();
        }
        $shopIds = array_values(array_unique(array_map('intval', $shopIds)));
        if (empty($shopIds)) {
            return false;
        }

        $rows = Db::getInstance()->executeS(
            'SELECT id_shop FROM ' . _DB_PREFIX_ . 'hook_module
            WHERE id_hook = ' . $hookId . '
            AND id_module = ' . $moduleId . '
            AND id_shop IN (' . implode(',', $shopIds) . ')'
        );
        if (!is_array($rows)) {
            return false;
        }

        $registeredShopIds = array_map('intval', array_column($rows, 'id_shop'));

        return [] === array_diff($shopIds, $registeredShopIds);
    }

    /**
     * Hook to display the icon
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader(): void
    {
        $this->context->controller->addCss(_PS_MODULE_LENGOW_DIR_ . 'views/css/lengow-tab.css');
    }

    /**
     * Hook on Home page
     *
     * @return void
     */
    public function hookDisplayHome(): void
    {
        // tracker is disabled now
    }

    /**
     * Hook on Payment page
     *
     * @return void
     */
    public function hookPaymentTop(): void
    {
        // tracker is disabled now
    }

    /**
     * Generate tracker on front footer page
     *
     * @return mixed
     */
    public function hookFooter(): mixed
    {
        // tracker is disabled now
        return '';
    }

    /**
     * Hook on order confirmation page to init order's product list
     *
     * @param array<string, mixed> $args arguments of hook
     *
     * @return void
     */
    public function hookOrderConfirmation(array $args): void
    {
        // tracker is disabled now
    }

    /**
     * Hook on admin page's order
     *
     * @param array<string, mixed> $args arguments of hook
     *
     * @return mixed
     */
    public function hookAdminOrder(array $args): mixed
    {
        if (!isset($args['id_order'])) {
            return null;
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
     * Hook on admin page's order side
     *
     * @param array<string, mixed> $params Arguments of hook
     *
     * @return mixed
     */
    public function hookAdminOrderSide(array $params): mixed
    {
        $id_order = (int) $params['id_order'];
        $lengowOrder = LengowOrder::getLengowOrderByPrestashopId($id_order);

        if (!$lengowOrder) {
            return '';
        }

        $marketplaceName = $lengowOrder['marketplace_name'];
        $shippingMethods = LengowMarketplace::getValidShippingMethods($marketplaceName);

        $ajaxUrl = $this->context->link->getAdminLink('AdminLengowOrder', true);
        $this->context->smarty->assign([
            'id_order' => $id_order,
            'shipping_methods' => $shippingMethods,
            'lengowOrder' => $lengowOrder,
            'ajax_url' => $ajaxUrl,
        ]);

        return $this->module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/hook/order/admin_order_side.tpl');
    }

    /**
     * Hook before an status' update to synchronize status with lengow
     *
     * @param array<string, mixed> $args arguments of hook
     *
     * @return void
     */
    public function hookUpdateOrderStatus(array $args): void
    {
        if (!isset($args['id_order']) || !(bool) LengowConfiguration::get(LengowConfiguration::SEND_EMAIL_DISABLED)) {
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
     * @param array<string, mixed> $args arguments of hook
     *
     * @return void
     */
    public function hookActionOrderStatusPostUpdate(array $args): void
    {
        if (!isset($args['id_order'])) {
            return;
        }

        $lengowOrder = new LengowOrder($args['id_order']);

        // Do nothing if order is not from Lengow or is being imported
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

            // Call Lengow API to send refund state order
            if ($idOrderState === LengowMain::getOrderState(LengowOrder::STATE_REFUNDED)) {
                $lengowOrder->callAction(LengowAction::TYPE_REFUND);
                $this->alreadyShipped[$lengowOrder->lengowMarketplaceSku] = true;
            }

            // Call Lengow API to send partial refund state order
            if ($idOrderState === LengowMain::getOrderState(LengowOrder::STATE_PARTIALLY_REFUNDED)) {
                $lengowOrder->callAction(LengowAction::TYPE_REFUND, true);

                $this->alreadyShipped[$lengowOrder->lengowMarketplaceSku] = true;
            }

            // Call Lengow API WSDL to send refuse state order
            if ($idOrderState === LengowMain::getOrderState(LengowOrder::STATE_CANCELED)) {
                $lengowOrder->callAction(LengowAction::TYPE_CANCEL);
                $this->alreadyShipped[$lengowOrder->lengowMarketplaceSku] = true;
            }
        }
    }

    /**
     * Update, if isset tracking number
     *
     * @param array<string, mixed> $args arguments of hook
     *
     * @return void
     */
    public function hookActionObjectUpdateAfter(array $args): void
    {
        if (!isset($args['object']->id) || !$args['object'] instanceof Order) {
            return;
        }

        if (LengowOrder::isFromLengow($args['object']->id)) {
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

    /**
     * Hook to add a Lengow tab link in the order detail tabs
     *
     * @param array<string, mixed> $params arguments of hook
     *
     * @return string
     */
    public function hookDisplayAdminOrderTabLink(array $params): string
    {
        $idOrder = (int) ($params['id_order'] ?? 0);
        if (!$idOrder || !LengowOrder::isFromLengow($idOrder)) {
            return '';
        }

        $lengowOrder = new LengowOrder($idOrder);
        $marketplace = $lengowOrder->getMarketplace();
        if (!$marketplace) {
            return '';
        }

        $hasContent = $marketplace->hasReturnTrackingNumber()
            || $marketplace->hasReturnTrackingCarrier()
            || !empty($marketplace->getRefundReasons())
            || !empty($marketplace->getRefundModes());

        if (!$hasContent) {
            return '';
        }

        return '<li class="nav-item">
            <a class="nav-link" id="lengow-tab" data-toggle="tab" href="#lengow-tab-content" role="tab">
                Lengow
            </a>
        </li>';
    }

    /**
     * Hook to add a Lengow tab content (return tracking, refund reason/mode) in the order detail tabs
     *
     * @param array<string, mixed> $params arguments of hook
     *
     * @return string
     */
    public function hookDisplayAdminOrderTabContent(array $params): string
    {
        $idOrder = (int) ($params['id_order'] ?? 0);
        if (!$idOrder || !LengowOrder::isFromLengow($idOrder)) {
            return '';
        }

        $lengowOrder = new LengowOrder($idOrder);
        $marketplace = $lengowOrder->getMarketPlace();
        if (!$marketplace) {
            return '';
        }

        $isActiveReturnTrackingNumber = $marketplace->hasReturnTrackingNumber();
        $isActiveReturnCarrier = $marketplace->hasReturnTrackingCarrier();
        $refundReasons = $marketplace->getRefundReasons();
        $refundModes = $marketplace->getRefundModes();
        $refundSelectedDatas = $lengowOrder->getRefundDataFromLengowOrder($idOrder, $marketplace->name);

        $locale = new LengowTranslation();

        $this->context->smarty->assign([
            'orderId' => $idOrder,
            'ajax_url' => $this->context->link->getAdminLink('AdminLengowOrder', true),
            'isActiveReturnTrackingNumber' => $isActiveReturnTrackingNumber,
            'isActiveReturnCarrier' => $isActiveReturnCarrier,
            'returnTrackingNumber' => $isActiveReturnTrackingNumber ? LengowOrderDetail::getOrderReturnTrackingNumber($idOrder) : '',
            'returnCarrier' => $isActiveReturnCarrier ? LengowOrderDetail::getOrderReturnCarrier($idOrder) : '',
            'returnTrackingNumberLabel' => $locale->t('order.screen.return_tracking_number_label'),
            'returnCarrierLabel' => $locale->t('order.screen.return_carrier_label'),
            'carriers' => $isActiveReturnCarrier ? LengowCarrier::getCarriersChoices($this->context->language->id) : [],
            'refundReasons' => $refundReasons,
            'refundModes' => $refundModes,
            'refundReasonSelected' => $refundSelectedDatas['refund_reason'] ?? '',
            'refundModeSelected' => $refundSelectedDatas['refund_mode'] ?? '',
        ]);

        return $this->module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/hook/order/admin_order_tab.tpl');
    }

    /**
     * Hook on product cancel
     *
     * @param array<string, mixed> $args
     *
     * @return void
     */
    public function hookActionProductCancel(array $args): void
    {
        if (!isset($args['order'])) {
            return;
        }
        $order = $args['order'];
        $lengowOrder = new LengowOrder($order->id);
        if (LengowOrder::isFromLengow($order->id)) {
            $idOrderDetail = (int) $args['id_order_detail'];
            $cancelQuantity = (int) $args['cancel_quantity'];

            $orderDetail = new OrderDetail($idOrderDetail);
            if (!Validate::isLoadedObject($orderDetail)) {
                return;
            }
            $lengowOrderLine = LengowOrderLine::findOrderLineByOrderDetailId($orderDetail->id);
            LengowOrderLine::setRefunded($idOrderDetail, $lengowOrderLine['id_order_line'], $cancelQuantity);
        }
    }
}
