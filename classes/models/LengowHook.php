<?php
/**
 * Copyright 2016 Lengow SAS.
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
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * The Lengow Hook class
 *
 */
class LengowHook
{
    /**
    * Constant for tag capsule and tracker
    */
    const LENGOW_TRACK_HOMEPAGE = 'homepage';
    const LENGOW_TRACK_PAGE = 'page';
    const LENGOW_TRACK_PAGE_LIST = 'listepage';
    const LENGOW_TRACK_PAGE_PAYMENT = 'payment';
    const LENGOW_TRACK_PAGE_CART = 'basket';
    const LENGOW_TRACK_PAGE_LEAD = 'lead';
    const LENGOW_TRACK_PAGE_CONFIRMATION = 'confirmation';

    /**
    * variables for tag capsule and tracker
    */
    static private $CURRENT_PAGE_TYPE = 'page';
    static private $USE_SSL = false;
    static private $ID_ORDER = '';
    static private $ORDER_PAYMENT = '';
    static private $ORDER_CURRENCY = '';
    static private $ORDER_TOTAL = '';
    static private $IDS_PRODUCTS = '';
    static private $IDS_PRODUCTS_CART = '';
    static private $ID_CATEGORY = '';

    /**
    * array order is already shipped
    */
    protected $alreadyShipped = array();

    /**
    * lengow module
    */
    private $module;

    /**
     * Construct
     *
     * @param lengow module $module
     */
    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
    }

    /**
     * Register Lengow Hook
     */
    public function registerHooks()
    {
        $error = false;
        $lengow_hook = array(
            // Common version
            'footer'                  => '1.4',
            'postUpdateOrderStatus'   => '1.4',
            'paymentTop'              => '1.4',
            'adminOrder'              => '1.4',
            'home'                    => '1.4',
            'updateOrderStatus'       => '1.4',
            'orderConfirmation'       => '1.4',
            // Version 1.5
            'actionObjectUpdateAfter' => '1.5'
        );
        foreach ($lengow_hook as $hook => $version) {
            if ($version <= Tools::substr(_PS_VERSION_, 0, 3)) {
                if (!$this->module->registerHook($hook)) {
                    LengowMain::log(
                        'Hook',
                        LengowMain::setLogMessage('log.hook.registering_hook_error', array('hook'  => $hook))
                    );
                    $error = true;
                } else {
                    LengowMain::log(
                        'Hook',
                        LengowMain::setLogMessage('log.hook.registering_hook_success', array('hook'  => $hook))
                    );
                }
            }
        }
        return ($error ? false : true);
    }

    /**
     * Hook on Home page
     *
     * @param array $args Arguments of hook
     */
    public function hookHome($args)
    {
        self::$CURRENT_PAGE_TYPE = self::LENGOW_TRACK_HOMEPAGE;
        $args = 0; // Prestashop validator
    }

    /**
     * Hook on Payment page
     *
     * @param array $args Arguments of hook
     */
    public function hookPaymentTop($args)
    {
        self::$CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE;
        $args = 0; // Prestashop validator
    }


    /**
     * Generate tracker on front footer page
     *
     * @param array $args Arguments of hook
     *
     * @return mixed
     */
    public function hookFooter($args)
    {
        $args = 0; // Prestashop validator
        if (!Configuration::get('LENGOW_TRACKING_ENABLED')) {
            return '';
        }
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            self::$USE_SSL = true;
        }
        $current_controller = $this->context->controller;
        if ($current_controller instanceof OrderConfirmationController) {
            self::$CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE_CONFIRMATION;
        } elseif ($current_controller instanceof ProductController) {
            self::$CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE;
        } elseif ($current_controller instanceof OrderController) {
            if ($current_controller->step == -1 || $current_controller->step == 0) {
                self::$CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE_CART;
            } elseif ($current_controller instanceof IndexController) {
                self::$CURRENT_PAGE_TYPE = self::LENGOW_TRACK_HOMEPAGE;
            }
        }
        // ID category
        if (!(self::$ID_CATEGORY = (int)Tools::getValue('id_category'))) {
            if (isset($_SERVER['HTTP_REFERER'])
                && preg_match(
                    '!^(.*)\/([0-9]+)\-(.*[^\.])|(.*)id_category=([0-9]+)(.*)$!',
                    $_SERVER['HTTP_REFERER'],
                    $regs
                )
                && !strstr($_SERVER['HTTP_REFERER'], '.html')
            ) {
                if (isset($regs[2]) && is_numeric($regs[2])) {
                    self::$ID_CATEGORY = (int)$regs[2];
                } elseif (isset($regs[5]) && is_numeric($regs[5])) {
                    self::$ID_CATEGORY = (int)$regs[5];
                }
            } elseif ($id_product = (int)Tools::getValue('id_product')) {
                $product = new Product($id_product);
                self::$ID_CATEGORY = $product->id_category_default;
            }
            if (self::$ID_CATEGORY == 0) {
                self::$ID_CATEGORY = '';
            }
        } else {
            self::$CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE_LIST;
        }
        // Basket
        if (self::$CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE_CART ||
            self::$CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE_PAYMENT
        ) {
            self::$ORDER_TOTAL = $this->context->cart->getOrderTotal();
        }
        // Product IDS
        if (self::$CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE_LIST
            || self::$CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE
            || self::$CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE_CART
        ) {
            $array_products = array();
            $products_cart = array();
            $products = (
                isset($this->context->smarty->tpl_vars['products'])
                ? $this->context->smarty->tpl_vars['products']->value
                : array()
            );
            if (!empty($products)) {
                $i = 1;
                foreach ($products as $p) {
                    if (is_object($p)) {
                        switch (Configuration::get('LENGOW_TRACKING_ID')) {
                            case 'upc':
                                $id_product = $p->upc;
                                break;
                            case 'ean':
                                $id_product = $p->ean13;
                                break;
                            case 'ref':
                                $id_product = $p->reference;
                                break;
                            default:
                                if (isset($p->id_product_attribute)) {
                                    $id_product = $p->id . '_' . $p->id_product_attribute;
                                } else {
                                    $id_product = $p->id;
                                }
                                break;
                        }
                        $products_cart[] = 'i'.$i.'='.$id_product
                            .'&p'.$i.'='.(isset($p->price_wt) ? $p->price_wt : $p->price)
                            .'&q'.$i.'='.$p->quantity;
                    } else {
                        switch (Configuration::get('LENGOW_TRACKING_ID')) {
                            case 'upc':
                                $id_product = $p['upc'];
                                break;
                            case 'ean':
                                $id_product = $p['ean13'];
                                break;
                            case 'ref':
                                $id_product = $p['reference'];
                                break;
                            default:
                                if (array_key_exists('id_product_attribute', $p) && $p['id_product_attribute']) {
                                    $id_product = $p['id_product'] . '_' . $p['id_product_attribute'];
                                } else {
                                    $id_product = $p['id_product'];
                                }
                                break;
                        }
                        $products_cart[] = 'i'.$i.'='.$id_product
                            .'&p'.$i.'='.(isset($p['price_wt']) ? $p['price_wt'] : $p['price'])
                            .'&q'.$i.'='.$p['quantity'];
                    }
                    $i++;
                    $array_products[] = $id_product;
                }
            } else {
                $p = (
                    isset($this->context->smarty->tpl_vars['product'])
                    ? $this->context->smarty->tpl_vars['product']->value
                    : null
                );
                if ($p instanceof Product) {
                    switch (Configuration::get('LENGOW_TRACKING_ID')) {
                        case 'upc':
                            $id_product = $p->upc;
                            break;
                        case 'ean':
                            $id_product = $p->ean13;
                            break;
                        case 'ref':
                            $id_product = $p->reference;
                            break;
                        default:
                            if (isset($p->id_product_attribute)) {
                                $id_product = $p->id . '_' . $p->id_product_attribute;
                            } else {
                                $id_product = $p->id;
                            }
                            break;
                    }
                    $array_products[] = $id_product;
                }
            }
            self::$IDS_PRODUCTS_CART = implode('&', $products_cart);
            self::$IDS_PRODUCTS = implode('|', $array_products);
        }
        if (!isset($this->smarty)) {
            $this->smarty = $this->context->smarty;
        }
        // Generate tracker
        if (self::$CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE_CONFIRMATION) {
            $shop_id = $this->context->shop->id;
            $this->context->smarty->assign(
                array(
                    'account_id'     => LengowMain::getIdAccount($shop_id),
                    'order_ref'      => self::$ID_ORDER,
                    'amount'         => self::$ORDER_TOTAL,
                    'currency_order' => self::$ORDER_CURRENCY,
                    'payment_method' => self::$ORDER_PAYMENT,
                    'cart'           => self::$IDS_PRODUCTS_CART,
                    'newbiz'         => 1,
                    'secure'         => 0,
                    'valid'          => 1,
                    'page_type'      => self::$CURRENT_PAGE_TYPE
                )
            );
            return $this->module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/front/tagpage.tpl');
        }
        return '';
    }

    /**
     * Hook on order confirmation page to init order's product list
     *
     * @param array $args Arguments of hook
     */
    public function hookOrderConfirmation($args)
    {
        self::$CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE_CONFIRMATION;
        self::$ID_ORDER = $args['objOrder']->id;
        self::$ORDER_TOTAL = $args['total_to_pay'];
        $payment_method = Tools::strtolower(str_replace(' ', '_', $args['objOrder']->payment));
        self::$ORDER_PAYMENT = LengowMain::replaceAccentedChars($payment_method);
        self::$ORDER_CURRENCY = $args['currencyObj']->iso_code;
        $ids_products = array();
        $products_list = $args['objOrder']->getProducts();
        $i = 0;
        $products_cart = array();
        foreach ($products_list as $p) {
            $i++;
            switch (Configuration::get('LENGOW_TRACKING_ID')) {
                case 'upc':
                    $id_product = $p['upc'];
                    break;
                case 'ean':
                    $id_product = $p['ean13'];
                    break;
                case 'ref':
                    $id_product = $p['reference'];
                    break;
                default:
                    if ($p['product_attribute_id']) {
                        $id_product = $p['product_id'] . '_' . $p['product_attribute_id'];
                    } else {
                        $id_product = $p['product_id'];
                    }
                    break;
            }
            // Ids Product
            $ids_products[] = $id_product;
            // Basket Product
            $products_cart[] = 'i' . $i . '=' . $id_product . '&p' . $i .
                '=' . Tools::ps_round($p['unit_price_tax_incl'], 2) . '&q' . $i . '=' . $p['product_quantity'];
        }
        self::$IDS_PRODUCTS_CART = implode('&', $products_cart);
        self::$IDS_PRODUCTS = implode('|', $ids_products);
    }


    /**
     * Hook before an status' update to synchronize status with lengow
     *
     * @param array $args Arguments of hook
     */
    public function hookUpdateOrderStatus($args)
    {
        $lengow_order = new LengowOrder($args['id_order']);
        // Not send state if we are on lengow import module
        if (LengowOrder::isFromLengow($args['id_order']) &&
            LengowImport::$current_order != $lengow_order->lengow_marketplace_sku) {
            LengowMain::disableMail();
        }
    }

    /**
     * Hook after an status' update to synchronize status with lengow.
     *
     * @param array $args Arguments of hook
     */
    public function hookPostUpdateOrderStatus($args)
    {
        $lengow_order = new LengowOrder($args['id_order']);
        // do nothing if order is not from Lengow or is being imported
        if (LengowOrder::isFromLengow($args['id_order'])
            && LengowImport::$current_order != $lengow_order->lengow_marketplace_sku
            && !array_key_exists($lengow_order->lengow_marketplace_sku, $this->alreadyShipped)
        ) {
            $new_order_state = $args['newOrderStatus'];
            $id_order_state = $new_order_state->id;
            // Compatibility V2
            if ((int)$lengow_order->lengow_id_flux > 0) {
                $lengow_order->checkAndChangeMarketplaceName();
            }
            if ($id_order_state == LengowMain::getOrderState('shipped')) {
                $lengow_order->callAction('ship');
                $this->alreadyShipped[$lengow_order->lengow_marketplace_sku] = true;
                return true;
            }
            // Call Lengow API WSDL to send refuse state order
            if ($id_order_state == LengowMain::getOrderState('canceled')) {
                $lengow_order->callAction('cancel');
                $this->alreadyShipped[$lengow_order->lengow_marketplace_sku] = true;
                return true;
            }
        }
        return false;
    }

    /**
     * Update, if isset tracking number
     *
     * @param array $args Arguments of hook
     */
    public function hookActionObjectUpdateAfter($args)
    {
        if ($args['object'] instanceof Order) {
            if (LengowOrder::isFromLengow($args['object']->id)) {
                $lengow_order = new LengowOrder($args['object']->id);
                if ($lengow_order->shipping_number != ''
                    && $args['object']->current_state == LengowMain::getOrderState('shipped')
                    && LengowImport::$current_order != $lengow_order->lengow_marketplace_sku
                    && !array_key_exists($lengow_order->lengow_marketplace_sku, $this->alreadyShipped)
                ) {
                    $params = array();
                    $params['id_order'] = $args['object']->id;
                    // Compatibility V2
                    if ($lengow_order->lengow_id_flux != null) {
                        $lengow_order->checkAndChangeMarketplaceName();
                    }
                    $lengow_order->callAction('ship');
                    $this->alreadyShipped[$lengow_order->lengow_marketplace_sku] = true;
                }
            }
        }
    }

    /**
     * Hook on admin page's order.
     *
     * @param array $args Arguments of hook
     *
     * @return display
     */
    public function hookAdminOrder($args)
    {
        if (LengowOrder::isFromLengow($args['id_order'])) {
            $lengow_order = new LengowOrder($args['id_order']);
            // get actions re-import and synchronize orders
            $lengow_link = new LengowLink();
            $locale = new LengowTranslation();
            $lengow_order_controller = $lengow_link->getAbsoluteAdminLink('AdminLengowOrder');
            $action_reimport = $lengow_order_controller.'&id_order='.$lengow_order->id.'&action=cancel_re_import';
            $action_synchronize = $lengow_order_controller.'&id_order='.$lengow_order->id.'&action=synchronize';
            $sent_markeplace = (
                $lengow_order->lengow_sent_marketplace
                    ? $locale->t('product.screen.button_yes')
                    : $locale->t('product.screen.button_no')
            );
            $template_data = array(
                'marketplace_sku'    => $lengow_order->lengow_marketplace_sku,
                'id_flux'            => $lengow_order->lengow_id_flux,
                'marketplace_name'   => $lengow_order->lengow_marketplace_name,
                'total_paid'         => $lengow_order->lengow_total_paid,
                'carrier'            => $lengow_order->lengow_carrier,
                'tracking_method'    => $lengow_order->lengow_method,
                'tracking'           => $lengow_order->lengow_tracking,
                'tracking_carrier'   => $lengow_order->lengow_carrier,
                'customer_email'     => $lengow_order->lengow_customer_email,
                'sent_markeplace'    => $sent_markeplace,
                'message'            => $lengow_order->lengow_message,
                'action_synchronize' => $action_synchronize,
                'action_reimport'    => $action_reimport,
                'order_id'           => $args['id_order'],
                'version'            => _PS_VERSION_,
                'lengow_locale'      => $locale
            );
            $this->context->smarty->assign($template_data);
            if (_PS_VERSION_ >= '1.6') {
                return $this->module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/order/info_16.tpl');
            }
            return $this->module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/order/info.tpl');
        }
        return '';
    }
}
