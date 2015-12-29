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

    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function registerHooks()
    {
        $error = false;
        $lengow_hook = array(
            // Common version
            'footer' => '1.4',
            'postUpdateOrderStatus' => '1.4',
            'paymentTop' => '1.4',
            'addproduct' => '1.4',
            'adminOrder' => '1.4',
            'home' => '1.4',
            'newOrder' => '1.4',
            'updateOrderStatus' => '1.4',
            'orderConfirmation' => '1.4',
            // Version 1.5
            'displayAdminHomeStatistics' => '1.5',
            'actionAdminControllerSetMedia' => '1.5',
            'actionObjectUpdateAfter' => '1.5',
            // Version 1.6
            'dashboardZoneTwo' => '1.6',
        );
        foreach ($lengow_hook as $hook => $version) {
            if ($version <= Tools::substr(_PS_VERSION_, 0, 3)) {
                $log = 'Registering hook - ';
                if (!$this->module->registerHook($hook)) {
                    LengowCore::log($log . $hook . ': error');
                    $error = true;
                } else {
                    LengowCore::log($log . $hook . ': success');
                }
            }
        }
        return ($error ? false : true);
    }

    public function hookHome()
    {
        self::$_CURRENT_PAGE_TYPE = self::LENGOW_TRACK_HOMEPAGE;
    }

    /**
     * Generate tracker on front footer page.
     *
     * @return varchar The data.
     */
    public function hookFooter()
    {
        $tracking_mode = Configuration::get('LENGOW_TRACKING');

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            self::$_USE_SSL = true;
        }

        if (empty($tracking_mode)) {
            return '';
        }

        $current_controller = $this->context->controller;

        if ($current_controller instanceof OrderConfirmationController) {
            self::$_CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE_CONFIRMATION;
        } elseif ($current_controller instanceof ProductController) {
            self::$_CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE;
        } elseif ($current_controller instanceof OrderController) {
            if ($current_controller->step == -1 || $current_controller->step == 0) {
                self::$_CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE_CART;
            } elseif ($current_controller instanceof IndexController) {
                self::$_CURRENT_PAGE_TYPE = self::LENGOW_TRACK_HOMEPAGE;
            }
        }


        // ID category
        if (!(self::$_ID_CATEGORY = (int)Tools::getValue('id_category'))) {
            if (isset($_SERVER['HTTP_REFERER']) && preg_match('!^(.*)\/([0-9]+)\-(.*[^\.])|(.*)id_category=([0-9]+)(.*)$!', $_SERVER['HTTP_REFERER'], $regs) && !strstr($_SERVER['HTTP_REFERER'], '.html')) {
                if (isset($regs[2]) && is_numeric($regs[2])) {
                    self::$_ID_CATEGORY = (int)$regs[2];
                } elseif (isset($regs[5]) && is_numeric($regs[5])) {
                    self::$_ID_CATEGORY = (int)$regs[5];
                }
            } elseif ($id_product = (int)Tools::getValue('id_product')) {
                $product = new Product($id_product);
                self::$_ID_CATEGORY = $product->id_category_default;
            }
            if (self::$_ID_CATEGORY == 0) {
                self::$_ID_CATEGORY = '';
            }
        } else {
            self::$_CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE_LIST;
        }

        // Basket
        if (self::$_CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE_CART ||
            self::$_CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE_PAYMENT
        ) {
            self::$_ORDER_TOTAL = $this->context->cart->getOrderTotal();
        }

        // Product IDS
        if (self::$_CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE_LIST || self::$_CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE || self::$_CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE_CART) {
            $array_products = array();
            $products_cart = array();
            $products = (isset(Context::getContext()->smarty->tpl_vars['products']) ? Context::getContext()->smarty->tpl_vars['products']->value : array());

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
                        $products_cart[] = 'i' . $i . '=' . $id_product . '&p' . $i . '=' . (isset($p->price_wt) ? $p->price_wt : $p->price) . '&q' . $i . '=' . $p->quantity;
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
                        $products_cart[] = 'i' . $i . '=' . $id_product . '&p' . $i . '=' . (isset($p['price_wt']) ? $p['price_wt'] : $p['price']) . '&q' . $i . '=' . $p['quantity'];
                    }
                    $i++;
                    $array_products[] = $id_product;
                }
            } else {
                $p = (isset(Context::getContext()->smarty->tpl_vars['product']) ? Context::getContext()->smarty->tpl_vars['product']->value : null);
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
            self::$_IDS_PRODUCTS_CART = implode('&', $products_cart);
            self::$_IDS_PRODUCTS = implode('|', $array_products);
        }

        if (!$this->smarty) {
            $this->smarty = $this->context->smarty;
        }

        // Generate tracker
        if ($tracking_mode == 'simpletag') {
            if (self::$_CURRENT_PAGE_TYPE == self::LENGOW_TRACK_PAGE_CONFIRMATION) {
                $this->context->smarty->assign(
                    array(
                        'page_type' => self::$_CURRENT_PAGE_TYPE,
                        'order_total' => self::$_ORDER_TOTAL,
                        'id_order' => self::$_ID_ORDER,
                        'ids_products' => self::$_IDS_PRODUCTS_CART,
                        'mode_payment' => self::$_ID_ORDER,
                        'id_customer' => LengowCore::getIdCustomer(),
                        'id_group' => LengowCore::getGroupCustomer(false),
                    )
                );
                return $this->display(__FILE__, 'views/templates/front/tagpage.tpl');
            }
        } elseif ($tracking_mode == 'tagcapsule') {
            $this->context->smarty->assign(
                array(
                    'page_type' => self::$_CURRENT_PAGE_TYPE,
                    'order_total' => self::$_ORDER_TOTAL,
                    'id_order' => self::$_ID_ORDER,
                    'ids_products' => self::$_IDS_PRODUCTS,
                    'ids_products_cart' => self::$_IDS_PRODUCTS_CART,
                    'use_ssl' => self::$_USE_SSL ? 'true' : 'false',
                    'id_category' => self::$_ID_CATEGORY,
                    'id_customer' => LengowCore::getIdCustomer(),
                    'id_group' => LengowCore::getGroupCustomer(false),
                )
            );
            return $this->display(__FILE__, 'views/templates/front/tagcapsule.tpl');
        }
        return '';
    }

    /**
     * Hook before an status' update to synchronize status with lengow.
     *
     * @param array $args Arguments of hook
     */
    public function hookUpdateOrderStatus($args)
    {
        $lengow_order = new LengowOrder($args['id_order']);
        // Not send state if we are on lengow import module
        if (LengowOrder::isFromLengow($args['id_order']) && LengowImport::$current_order != $lengow_order->id_lengow) {
            LengowCore::disableMail();
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
        if (LengowOrder::isFromLengow($args['id_order']) && LengowImport::$current_order != $lengow_order->id_lengow) {
            $new_order_state = $args['newOrderStatus'];
            $id_order_state = $new_order_state->id;
            $marketplace = LengowCore::getMarketplaceSingleton((string)$lengow_order->lengow_marketplace);
            if ($marketplace->isLoaded()) {
                // Call Lengow API WSDL to send shipped state order
                if ($id_order_state == LengowCore::getOrderState('shipped')) {
                    $marketplace->wsdl('shipped', $lengow_order->id_feed_lengow, $lengow_order->id_lengow, $args);
                }
                // Call Lengow API WSDL to send refuse state order
                if ($id_order_state == LengowCore::getOrderState('cancel')) {
                    $marketplace->wsdl('refuse', $lengow_order->id_feed_lengow, $lengow_order->id_lengow, $args);
                }
            }
            if ($id_order_state == (int)LengowCore::getLengowErrorStateId()) {
                $lengow_order->setStateToError();
            }
        }
    }

    /**
     * Update, if isset tracking number
     */
    public function hookActionObjectUpdateAfter($args)
    {
        if ($args['object'] instanceof Order) {
            if (LengowOrder::isFromLengow($args['object']->id)) {
                $lengow_order = new LengowOrder($args['object']->id);
                if ($lengow_order->shipping_number != '' &&
                    $args['object']->current_state == LengowCore::getOrderState('shipped')
                ) {
                    $params = array();
                    $params['id_order'] = $args['object']->id;
                    $marketplace = LengowCore::getMarketplaceSingleton((string)$lengow_order->lengow_marketplace);
                    $marketplace->wsdl('shipped', $lengow_order->id_feed_lengow, $lengow_order->id_lengow, $params);
                }
            }
        }
    }

    /**
     * Hook on order confirmation page to init order's product list.
     *
     * @param array $args Arguments of hook
     */
    public function hookOrderConfirmation($args)
    {
        self::$_CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE_CONFIRMATION;
        self::$_ID_ORDER = $args['objOrder']->id;
        self::$_ORDER_TOTAL = $args['total_to_pay'];
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
        self::$_IDS_PRODUCTS_CART = implode('&', $products_cart);
        self::$_IDS_PRODUCTS = implode('|', $ids_products);
    }

    /**
     * Hook on Payment page.
     *
     * @param array $args Arguments of hook
     */
    public function hookPaymentTop($args)
    {
        self::$_CURRENT_PAGE_TYPE = self::LENGOW_TRACK_PAGE;
        $args = 0; // Prestashop validator
    }

    /**
     * Hook after add new product.
     *
     * @param array $params Arguments of hook
     *
     * @return boolean
     */
    public function hookAddProduct($params)
    {
        if (!isset($params['product']->id)) {
            return false;
        }
        $id_product = $params['product']->id;
        if ((int)$id_product < 1) {
            return false;
        }
        if (Configuration::get('LENGOW_EXPORT_NEW')) {
            LengowProduct::publish($id_product);
        }
    }

    /**
     * Hook on header dashboard.
     *
     * @param array $args Arguments of hook
     */
    public function hookActionAdminControllerSetMedia($args)
    {
        $this->context = Context::getContext();

        $controllers = array('admindashboard', 'adminhome', 'adminlengow');
        if (in_array(Tools::strtolower(Tools::getValue('controller')), $controllers)) {
            $this->context->controller->addJs($this->_path . 'views/js/chart.min.js');
        }

        if (Tools::getValue('controller') == 'AdminModules' && Tools::getValue('configure') == 'lengow') {
            $this->context->controller->addJs($this->_path . '/views/js/admin.js');
            $this->context->controller->addCss($this->_path . '/views/css/admin.css');
        }
        if (Tools::getValue('controller') == 'AdminOrders') {
            $this->context->controller->addJs($this->_path . '/views/js/admin.js');
        }
        $args = 0; // Prestashop validator
    }

    /**
     * Prestashop 1.6 - Dashboard
     */
    public function hookDashboardZoneTwo($params)
    {
        $this->context->smarty->assign(
            array(
                'token' => LengowCore::getTokenCustomer(),
                'id_customer' => LengowCore::getIdCustomer(),
                'id_group' => LengowCore::getGroupCustomer(),
                'params' => $params,
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/dashboard/stats_16.tpl');
    }

//    /**
//     * Hook on dashboard.
//     *
//     * @param array $args Arguments of hook
//     */
//    public function hookDisplayAdminHomeStatistics($args)
//    {
//        $args = $args; // Prestashop validator
//        $this->context->smarty->assign(
//            array(
//                'token' => LengowCore::getTokenCustomer(),
//                'id_customer' => LengowCore::getIdCustomer(),
//                'id_group' => LengowCore::getGroupCustomer(),
//            )
//        );
//        return $this->display(__FILE__, 'views/templates/admin/dashboard/stats.tpl');
//    }

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
            $order = new LengowOrder($args['id_order']);
            if (Tools::getValue('action') == 'synchronize') {
                $lengow_connector = new LengowConnector((integer)LengowCore::getIdCustomer(), LengowCore::getTokenCustomer());
                $api_args = array(
                    'idClient' => LengowCore::getIdCustomer(),
                    'idFlux' => $order->id_feed_lengow,
                    'idGroup' => LengowCore::getGroupCustomer(),
                    'idCommandeMP' => $order->id_lengow,
                    'idCommandePresta' => $order->id);
                $lengow_connector->api('updatePrestaInternalOrderId', $api_args);
            }

            if (_PS_VERSION_ < '1.5') {
                $action_reimport = 'index.php?tab=AdminOrders&id_order=' . $order->id . '&vieworder&action=reImportOrder&token=' . Tools::getAdminTokenLite('AdminOrders') . '';
                $action_reimport = $this->_path . 'v14/ajax.php?';
                $action_synchronize = 'index.php?tab=AdminOrders&id_order=' . $order->id . '&vieworder&action=synchronize&token=' . Tools::getAdminTokenLite('AdminOrders');
                $add_script = true;
            } else {
                $action_reimport = 'index.php?controller=AdminLengow&id_order=' . $order->id . '&lengoworderid=' . $order->id_lengow . '&feed_id=' . $order->id_feed_lengow . '&action=reimportOrder&ajax&token=' . Tools::getAdminTokenLite('AdminLengow');
                $action_synchronize = 'index.php?controller=AdminOrders&id_order=' . $order->id . '&vieworder&action=synchronize&token=' . Tools::getAdminTokenLite('AdminOrders');
                $add_script = false;
            }
            $lengow_order_extra = Tools::jsonDecode($order->lengow_extra);

            $template_data = array(
                'id_order_lengow' => $order->id_lengow,
                'id_flux' => $order->id_feed_lengow,
                'marketplace' => $order->lengow_marketplace,
                'total_paid' => $order->lengow_total_paid,
                'carrier' => $order->lengow_carrier,
                'message' => $order->lengow_message,
                'action_synchronize' => $action_synchronize,
                'action_reimport' => $action_reimport,
                'order_id' => $args['id_order'],
                'add_script' => $add_script,
                'url_script' => $this->_path . 'views/js/admin.js',
                'version' => _PS_VERSION_
            );
            if (!is_object($lengow_order_extra->tracking_informations->tracking_method)) {
                $template_data['tracking_method'] = $lengow_order_extra->tracking_informations->tracking_method;
            } else {
                $template_data['tracking_method'] = '';
            }
            if (!is_object($lengow_order_extra->tracking_informations->tracking_carrier)) {
                $template_data['tracking_carrier'] = $lengow_order_extra->tracking_informations->tracking_carrier;
            } else {
                $template_data['tracking_carrier'] = '';
            }
            if (!is_object($lengow_order_extra->tracking_informations->tracking_deliveringByMarketPlace)) {
                $template_data['sent_markeplace'] = $lengow_order_extra->tracking_informations->tracking_deliveringByMarketPlace ? $this->l('yes') : $this->l('no');
            } else {
                $template_data['sent_markeplace'] = '';
            }

            $this->context->smarty->assign($template_data);
            if (_PS_VERSION_ >= '1.6') {
                return $this->display(__FILE__, 'views/templates/admin/order/info_16.tpl');
            }
            return $this->display(__FILE__, 'views/templates/admin/order/info.tpl');
        }
        return '';
    }
}