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

class LengowImport
{

    /**
     * Version.
     */
    const VERSION = '1.0.1';

    /**
     * @var integer lang id
     */
    protected $id_lang;

    /**
     * @var integer shop id
     */
    protected $id_shop;

    /**
     * @var integer shop group id
     */
    protected $id_shop_group;

    /**
     * @var boolean use debug mode
     */
    protected $debug = false;

    /**
     * @var string order id
     */
    protected $order_id = null;

    /**
     * @var integer feed id
     */
    protected $feed_id = null;

    /**
     * @var integer number of orders to import
     */
    protected $limit = 0;

    /**
     * @var boolean import marketplace prices
     */
    protected $force_price = true;

    /**
     * @var boolean import inactive & out of stock products
     */
    protected $force_product = true;

    /**
     * @var string start import date
     */
    protected $date_from;

    /**
     * @var string end import date
     */
    protected $date_to;

    /**
     * @var boolean import is processing
     */
    public static $processing;

    /**
     * @var string order id being imported
     */
    public static $current_order = -1;

    /**
     * Construct the import manager
     *
     * @param integer $id_lang language id
     * @param integer $id_shop shop id
     * @param integer $id_shop_group shop group id
     * @param boolean $force_product force import of products
     * @param string $order_id lengow order id to import
     * @param integer $feed_id lengow feed id
     * @param boolean $debug debug mode
     * @param string $date_from starting import date
     * @param string $date_to ending import date
     * @param integer $limit number of orders to import
     * @param boolean $log_output display log messages
     * @param boolean $force_price import API prices
     */
    public function __construct(
        $order_id = null,
        $feed_id = null,
        $force_product = true,
        $debug = false,
        $date_from = null,
        $date_to = null,
        $limit = 0,
        $log_output = false,
        $force_price = true
    ) {
        $this->id_lang = Context::getContext()->language->id;
        $this->id_shop = Context::getContext()->shop->id;
        $this->id_shop_group = Context::getContext()->shop->id_shop_group;

        $this->force_price = $force_price;
        $this->force_product = $force_product;
        $this->debug = $debug;
        $this->limit = $limit;
        if (!is_null($order_id)) {
            $this->order_id = $order_id;
            $this->feed_id = $feed_id;
            $log_output = false;
        }
        $this->log_output = $log_output;
        $this->date_from = $date_from;
        $this->date_to = $date_to;

        if (Configuration::get('LENGOW_REPORT_MAIL') && !$this->debug) {
            LengowCore::sendMailAlert();
        }
    }

    /**
     * Excute import : fetch orders and import them
     *
     */
    public function exec()
    {
        // 1st step: check if import is already in process
        $force_import = (bool)Tools::getValue('force');
        if (LengowImport::isInProcess() && !$this->debug && !$force_import) {
            LengowCore::log('import already in process', $this->log_output);
            return;
        }
        // 2nd step: start import process
        LengowImport::setInProcess();

        // 3rd step: disable emails
        LengowCore::disableMail();
        try {    // start of actual import process
            // get orders from Lengow API
            $result = LengowImport::getOrdersFromApi(
                $this->order_id,
                $this->feed_id,
                $this->date_from,
                $this->date_to
            );
            if (isset($result->error)) {
                throw new LengowImportException(Tools::jsonEncode($result));
            }
            $total_orders = count($result->orders->order);
            LengowCore::log($total_orders . ' order(s) found', $this->log_output);
            if ($total_orders <= 0) {
                return;
            }

            // import orders in prestashop
            // uncomment follow to enable force price option and comment next
            // LengowImport::importOrders($orders, $this->id_lang, $this->id_shop, $this->debug, $this->force_product, $this->limit, $this->log_output, $this->force_price);
            LengowImport::importOrders(
                $result->orders,
                $this->id_lang,
                $this->id_shop,
                $this->debug,
                $this->force_product,
                $this->limit,
                $this->log_output
            );
        } catch (LengowApiException $lae) {
            LengowCore::log('error on lengow webservice: ' . $lae->getMessage(), $this->log_output);
        } catch (Exception $e) {
            LengowCore::log('Error: ' . $e->getMessage(), $this->log_output);
            LengowImport::setEnd();
            return;
        }
        LengowImport::setEnd();
    }

    /**
     * Check if import is already in process
     *
     * @return boolean
     */
    public static function isInProcess()
    {
        $timestamp = Configuration::get('LENGOW_IS_IMPORT');
        if ($timestamp == 'stopped') {
            $timestamp = -1;
        }
        if ($timestamp > 0) {
            if (($timestamp + (60 * 10)) < time()) // security check : if last import is more than 10 min old => authorize new import to be launched
            {
                LengowImport::setEnd();
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Set import to "in process" state
     *
     * @return boolean
     */
    protected static function setInProcess()
    {
        LengowImport::$processing = true;
        return Configuration::updateValue('LENGOW_IS_IMPORT', time());
    }

    /**
     * Call Lengow order API
     *
     * @param LengowConnector $connector
     *
     * @return SimpleXmlElement
     */
    protected function getOrdersFromApi($order_id, $feed_id, $date_from, $date_to)
    {
        $connector = new LengowConnector((integer)LengowCore::getIdCustomer(), LengowCore::getTokenCustomer());
        $orders = $connector->api(
            'commands',
            array(
                'order_id' => $order_id,
                'feed_id' => $feed_id,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'group_id' => LengowCore::getGroupCustomer(),
                'state' => 'plugin',
            )
        );
        if (!$orders || !isset($orders->orders)) {
            throw new LengowApiException('no order data retrieved from API - ' . Tools::jsonEncode($orders), 4);
        }

        return $orders;
    }

    /**
     * Set import to finished
     *
     * @return boolean
     */
    public static function setEnd()
    {
        LengowImport::$processing = false;
        return Configuration::updateValue('LENGOW_IS_IMPORT', -1);
    }

    /**
     * Create or update order in prestashop
     *
     * @param SimpleXmlElement $orders API orders
     * @param integer $id_lang language id
     * @param integer $id_shop shop id
     * @param boolean $debug debug mode
     * @param integer $limit limit number of import
     * @param boolean $force_price force prices
     *
     */
    protected static function importOrders(
        $orders,
        $id_lang,
        $id_shop,
        $debug = false,
        $force_product = true,
        $limit = 0,
        $log_output = false,
        $force_price = true
    ) {
        $count_orders_updated = 0;
        $count_orders_added = 0;
        foreach ($orders->order as $order_data) {
            $lengow_id = (string)$order_data->order_id;
            if ($debug) {
                $lengow_id .= '--' . time();
            }

            // set current order to cancel hook updateOrderStatus
            LengowImport::$current_order = $lengow_id;
            $feed_id = (string)$order_data->idFlux;
            // check order status
            $marketplace = LengowCore::getMarketplaceSingleton((string)$order_data->marketplace);
            $order_state = (string)$order_data->order_status->marketplace;

            // update order state if already imported
            $order_id = LengowOrder::getOrderIdFromLengowOrders($lengow_id, $feed_id, $marketplace->name);
            if ($order_id) {
                LengowCore::log('order already imported (ORDER ' . $order_id . ')', $log_output, $lengow_id);
                $order = new LengowOrder($order_id);
                if ($order->is_disabled) // Lengow -> Cancel and reimport order
                {
                    $order->setStateToError();
                    LengowLog::deleteLog($lengow_id);
                } else {
                    try {
                        $api_state = (string)$order_data->order_status->marketplace;
                        if ($order->updateState($marketplace, $api_state,
                            (string)$order_data->tracking_informations->tracking_number)
                        ) {
                            $available_states = LengowCore::getOrderStates($id_lang);
                            $state_name = '';
                            foreach ($available_states as $state) {
                                if ($state['id_order_state'] === LengowCore::getOrderState($marketplace->getStateLengow($api_state))) {
                                    $state_name = $state['name'];
                                }
                            }
                            LengowCore::log('order\'s state has been updated to \"' . $state_name . '\"', $log_output,
                                $lengow_id);
                            $count_orders_updated++;
                        }
                    } catch (Exception $e) {
                        LengowCore::log('error while updating state: ' . $e->getMessage(), $log_output, $lengow_id);
                    }
                    unset($order);
                    continue;
                }
            }

            // if order is cancelled or new -> skip
            if (!LengowImport::checkState($order_state, $marketplace)) {
                LengowCore::log('current order\'s state [' . $order_state . '] makes it unavailable to import',
                    $log_output, $lengow_id);
                continue;
            }
            try {
                // checks if an external id already exists
                $external_id = (string)$order_data->order_external_id;
                // if order is disabled, reimport it
                $is_reimport = false;
                if (isset($order)) {
                    $is_reimport = $order->is_disabled;
                }
                if (!empty($external_id) && !$debug && !$is_reimport) {
                    $message = 'already imported in Prestashop with order ID ' . $external_id;
                    LengowLog::addLog($order_data, $lengow_id, $message, 1);
                    LengowCore::log($message, $log_output, $lengow_id);
                    continue;
                }
                // shipment by marketplace
                $shipped_by_mp = (string)$order_data->tracking_informations->tracking_deliveringByMarketPlace == 1;
                if ($shipped_by_mp) {
                    $message = 'order shipped by ' . $marketplace->name;
                    LengowCore::log($message, $log_output, $lengow_id);
                    if (!Configuration::get('LENGOW_IMPORT_SHIPPED_BY_MP')) {
                        LengowLog::addLog($order_data, $lengow_id, $message, 1);
                        continue;
                    }
                }
                if ($force_price) {
                    $order_data = LengowImport::rewriteData($order_data,
                        Configuration::get('LENGOW_IMPORT_PROCESSING_FEE'));
                }

                $cart_data = array();
                $cart_data['id_lang'] = $id_lang;
                $cart_data['id_shop'] = $id_shop;

                // get billing data
                $billing_data = LengowAddress::extractAddressDataFromAPI($order_data->billing_address,
                    LengowAddress::BILLING);

                // create customer based on billing data
                if (Configuration::get('LENGOW_IMPORT_FAKE_EMAIL') || $debug || empty($billing_data['email'])) {
                    $billing_data['email'] = 'generated-email+' . $lengow_id . '@' . LengowCore::getHost();
                    LengowCore::log('generate unique email : ' . $billing_data['email'], $debug, $lengow_id);
                }
                $customer = LengowImport::getCustomer($billing_data);
                $customer->validateLengow();
                $cart_data['id_customer'] = $customer->id;

                // create addresses from API data
                // billing
                $billing_address = LengowImport::getAddress($billing_data);
                $billing_address->id_customer = $customer->id;
                $billing_address->validateLengow();
                $cart_data['id_address_invoice'] = $billing_address->id;
                // shipping
                $shipping_data = LengowAddress::extractAddressDataFromAPI($order_data->delivery_address,
                    LengowAddress::SHIPPING);
                $shipping_address = LengowImport::getAddress($shipping_data, $order_data->tracking_informations);
                $shipping_address->id_customer = $customer->id;
                $shipping_address->validateLengow();

                // get billing phone numbers if empty in shipping address
                if (empty($shipping_address->phone) && !empty($billing_address->phone)) {
                    $shipping_address->phone = $billing_address->phone;
                    $shipping_address->update();
                }
                if (empty($shipping_address->phone_mobile) && !empty($billing_address->phone_mobile)) {
                    $shipping_address->phone_mobile = $billing_address->phone_mobile;
                    $shipping_address->update();
                }

                $cart_data['id_address_delivery'] = $shipping_address->id;

                // get currency
                $cart_data['id_currency'] = (int)Currency::getIdByIsoCode((string)$order_data->order_currency);

                // get carrier
                $cart_data['id_carrier'] = LengowImport::getCarrierId($order_data->tracking_informations, $marketplace,
                    $lengow_id, $id_lang, $shipping_address);

                // create cart based on previous data
                if (_PS_VERSION_ < '1.5') {
                    $cart = new LengowCart(Context::getContext()->cart->id);
                } else {
                    $cart = new LengowCart();
                }

                $cart->assign($cart_data);
                $cart->validateLengow();
                $cart->force_product = $force_product;
                // add products to cart
                $products = LengowImport::getProducts($order_data->cart, $marketplace, $lengow_id, $id_shop);
                $cart->addProducts($products, $force_product);
                // add cart to context
                Context::getContext()->cart = $cart;

                // create payment
                $id_order_state = LengowCore::getPrestahopStateId($order_state, $marketplace, $shipped_by_mp);
                $payment = new LengowPaymentModule();
                $payment->active = true;
                $payment_method = Configuration::get('LENGOW_IMPORT_METHOD_NAME') == 'lengow' ? 'Lengow' : (string)$order_data->marketplace;
                $message = 'Import Lengow | ' . "\r\n"
                    . 'ID order : ' . (string)$order_data->order_id . ' | ' . "\r\n"
                    . 'Marketplace : ' . (string)$order_data->marketplace . ' | ' . "\r\n"
                    . 'ID flux : ' . (integer)$order_data->idFlux . ' | ' . "\r\n"
                    . 'Total paid : ' . (float)$order_data->order_amount . ' | ' . "\r\n"
                    . 'Shipping : ' . (string)$order_data->order_shipping . ' | ' . "\r\n"
                    . 'Message : ' . (string)$order_data->order_comments . "\r\n";
                $order_list = array();
                if ($force_price) {
                    if (_PS_VERSION_ >= '1.5') {
                        $order_list = $payment->makeOrder(
                            $cart->id,
                            $id_order_state,
                            (float)$order_data->order_amount,
                            $payment_method,
                            $message,
                            $products,
                            (float)$order_data->order_shipping,
                            (float)$order_data->order_processing_fee
                        );
                    } else {
                        $order_list = $payment->makeOrder14(
                            $cart->id,
                            $id_order_state,
                            (float)$order_data->order_amount,
                            $payment_method,
                            $message,
                            $products,
                            (float)$order_data->order_shipping,
                            (float)$order_data->order_processing_fee
                        );
                    }
                } else {
                    $cart_total = Tools::ps_round((float)$cart->getOrderTotal(true, Cart::BOTH, null, null, false), 2);
                    $payment_valid = $payment->validateOrder(
                        $cart->id,
                        $id_order_state,
                        (float)$cart_total,
                        $payment_method,
                        $message
                    );
                    if ($payment_valid && $payment->currentOrder) {
                        $order_list[] = new LengowOrder($payment->currentOrder);
                    }
                }

                if (empty($order_list)) // if no order in list
                {
                    throw new Exception('order could not be saved');
                } else {
                    $count_orders_added++;
                    foreach ($order_list as $order) {
                        // add order comment from marketplace to prestashop order
                        if (_PS_VERSION_ >= '1.5') {
                            $comment = (string)$order_data->order_comment;
                            if (!empty($comment)) {
                                $msg = new Message();
                                $msg->id_order = $order->id;
                                $msg->private = 1;
                                $msg->message = $comment;
                                $msg->add();
                            }
                        }
                        $success_message = 'order successfully imported (ID ' . $order->id . ')';
                        if (!LengowImport::addLengowOrder($lengow_id, $feed_id, $order, $order_data)) {
                            LengowCore::log('WARNING ! Order could NOT be saved in lengow orders table', $debug,
                                $lengow_id);
                        } else {
                            LengowCore::log('order saved in lengow orders table', $debug, $lengow_id);
                        }

                        // if more than one order (different warehouses)
                        LengowCore::log($success_message, $log_output, $lengow_id);
                    }

                    // Update status on lengow if no debug
                    if ($debug == false) {
                        $connector = new LengowConnector((integer)LengowCore::getIdCustomer(),
                            LengowCore::getTokenCustomer());
                        $orders = $connector->api('updatePrestaInternalOrderId', array(
                            'idClient' => LengowCore::getIdCustomer(),
                            'idFlux' => $feed_id,
                            'idGroup' => LengowCore::getGroupCustomer(),
                            'idCommandeMP' => $lengow_id,
                            'idCommandePresta' => $order->id
                        ));
                    }
                    LengowLog::addLog($order_data, $lengow_id, $success_message, 1);

                    // ensure carrier compatibility with SoColissimo & Mondial Relay
                    try {
                        if (!$carrier_name = (string)$order_data->tracking_informations->tracking_carrier) {
                            $carrier_name = (string)$order_data->tracking_informations->tracking_method;
                        }

                        $carrier_compatibility = LengowCarrier::carrierCompatibility($order->id_customer,
                            $order->id_cart, $order->id_carrier, $shipping_address);
                        if ($carrier_compatibility < 0) {
                            throw new LengowCarrierException('carrier ' . $carrier_name . ' could not be found in your Prestashop');
                        } elseif ($carrier_compatibility > 0) {
                            LengowCore::log('carrier compatibility ensured with carrier ' . $carrier_name, $debug,
                                $lengow_id);
                        }
                    } catch (LengowCarrierException $lce) {
                        LengowCore::log($lce->getMessage(), $log_output, $lengow_id);
                    }

                }
                if ($shipped_by_mp) {
                    LengowCore::log('adding quantity back to stock (order shipped by marketplace)', $log_output,
                        $lengow_id);
                    LengowImport::addQuantityBack($products, $id_shop);
                }
            } catch (InvalidLengowObjectException $iloe) {
                $error_message = $iloe->getMessage();
            } catch (LengowImportException $lie) {
                $error_message = $lie->getMessage();
            } catch (PrestashopException $pe) {
                $error_message = $pe->getMessage();
            } catch (Exception $e) {
                $error_message = $e->getMessage();
            }

            if (isset($error_message)) {
                if (isset($cart)) {
                    $cart->delete();
                }
                LengowCore::log('order import failed: ' . $error_message, $log_output, $lengow_id);
                LengowLog::addLog($order_data, $lengow_id, $error_message);
                unset($error_message);
            }
            LengowImport::$current_order = -1;

            unset($cart);
            unset($billing_address);
            unset($shipping_address);
            unset($customer);
            unset($payment);
            unset($order);

            // if limit is set
            if ($limit > 0 && $count_orders_added == $limit || Configuration::get('LENGOW_IS_IMPORT') <= 0) {
                break;
            }
        }
        LengowCore::log($count_orders_added . ' order(s) imported', $log_output);
        LengowCore::log($count_orders_updated . ' order(s) updated', $log_output);

        if (isset($order))// return last order id of the list
        {
            return $order->id;
        }
    }


    /**
     * Create or load customer based on API data
     *
     * @param array $customer_data API data
     *
     * @return LengowCustomer
     */
    protected static function getCustomer($customer_data = array())
    {
        $customer = new LengowCustomer();
        // check if customer already exists in Prestashop
        $customer->getByEmail($customer_data['email']);
        if ($customer->id) {
            return $customer;
        }

        // create new customer
        $customer->assign($customer_data);
        return $customer;
    }

    /**
     * Create or load address based on API data
     *
     * @param array $address_data API data
     * @param SimpleXMLElement $tracking_informations API data
     *
     * @return LengowAddress
     */
    protected static function getAddress($address_data = array(), $tracking_informations = null)
    {
        $address_data['address_full'] = '';
        // construct field address_full
        $address_data['address_full'] .= !empty($address_data['address']) ? $address_data['address'] . ' ' : '';
        $address_data['address_full'] .= !empty($address_data['address_2']) ? $address_data['address_2'] . ' ' : '';
        $address_data['address_full'] .= !empty($address_data['address_complement']) ? $address_data['address_complement'] . ' ' : '';
        $address_data['address_full'] .= !empty($address_data['zipcode']) ? $address_data['zipcode'] . ' ' : '';
        $address_data['address_full'] .= !empty($address_data['city']) ? $address_data['city'] . ' ' : '';
        $address_data['address_full'] .= !empty($address_data['country_iso']) ? $address_data['country_iso'] . ' ' : '';
        // if tracking_informations exist => get id_relay
        if (!is_null($tracking_informations) && !empty($tracking_informations->tracking_relay)) {
            $address_data['id_relay'] = (string)$tracking_informations->tracking_relay;
        }
        // construct LengowAddress and assign values
        $address = new LengowAddress();
        $address->assign($address_data);
        return $address;
    }

    /**
     * Rewrite order amount
     *
     * @param SimpleXMLElement $data API order data
     * @param Boolean $keep_fees keep fees
     *
     * @return SimpleXmlElement    overwritten data
     */
    protected static function rewriteData($data, $keep_fees = false)
    {
        $data->order_amount = new SimpleXMLElement('<order_amount><![CDATA['
            . ((float)$data->order_amount - (float)$data->order_processing_fee)
            . ']]></order_amount>');
        if (!$keep_fees) {
            $data->order_processing_fee = new SimpleXMLElement('<order_processing_fee><![CDATA[ ]]></order_processing_fee>');
            LengowCore::log('rewrite amount without processing fee', false, (string)$data->order_id);

        }

        return $data;
    }

    /**
     * Get products from API data
     *
     * @param SimpleXMLElement $cart_data API cart data
     * @param LengowMarketplace $marketplace order marketplace
     * @param string $lengow_id lengow order id
     * @param integer $id_shop shop id
     *
     * @return array list of products
     */
    protected static function getProducts($cart_data, $marketplace, $lengow_id, $id_shop)
    {
        $products = array();
        foreach ($cart_data->products->product as $product) {
            $product_data = LengowProduct::extractProductDataFromAPI($product);
            if (!empty($product_data['status'])) {
                if ($marketplace->getStateLengow((string)$product_data['status']) == 'canceled') {
                    LengowCore::log('product ' . $product_data['sku'] . ' could not be added to cart - status: ' . $marketplace->getStateLengow((string)$product_data['status']),
                        false, $lengow_id);
                    continue;
                }
            }
            $ids = false;
            $product_ids = array(
                (string)$product->sku['field'] => $product_data['sku'],
                'sku' => $product_data['sku'],
                'idLengow' => $product_data['idLengow'],
                'idMP' => $product_data['idMP'],
                'ean' => $product_data['ean'],
            );
            $found = false;
            foreach ($product_ids as $attribute_name => $attribute_value) {
                // remove _FBA from product id
                $attribute_value = preg_replace('/_FBA$/', '', $attribute_value);
                //$attribute_value = str_replace('_FBA', '', $attribute_value);

                if (empty($attribute_value)) {
                    continue;
                }
                $ids = LengowProduct::matchProduct($attribute_name, $attribute_value, $id_shop, $product_ids);
                if (!$ids) // no product found in the "classic" way => use advanced search
                {
                    LengowCore::log(
                        'product not found with field '
                        . $attribute_name
                        . ' ('
                        . $attribute_value
                        . '). Using advanced search.',
                        false,
                        $lengow_id
                    );
                    $ids = LengowProduct::advancedSearch($attribute_value, $id_shop, $product_ids);
                }
                // for testing => replace values
                // $ids['id_product'] = '1';
                // $ids['id_product_attribute'] = '1';
                if (!empty($ids)) {
                    $id_full = $ids['id_product'];
                    if (!isset($ids['id_product_attribute'])) {
                        $p = new LengowProduct($ids['id_product']);
                        if ($p->hasAttributes()) {
                            throw new LengowImportException('product ' . $p->id . ' is a parent ID. Product variation needed');
                        }
                    }
                    $id_full .= isset($ids['id_product_attribute']) ? '_' . $ids['id_product_attribute'] : '';
                    $products[$id_full] = $product_data;
                    LengowCore::log(
                        'product id '
                        . $id_full
                        . ' found with field '
                        . $attribute_name
                        . ' ('
                        . $attribute_value
                        . ')',
                        false,
                        $lengow_id
                    );
                    $found = true;
                    break;
                }

            }
            if (!$found) {
                throw new Exception('product ' . $product_data['sku'] . ' could not be found');
            }
        }
        return $products;
    }

    /**
     * Check if order status is valid and is available for import
     *
     * @param string $order_state order state
     * @param LengowMarketplace $marketplace order marketplace
     *
     * @return boolean
     */
    protected static function checkState($order_state, $marketplace)
    {
        if (empty($order_state)) {
            return false;
        }
        if ($marketplace->getStateLengow($order_state) != 'processing' && $marketplace->getStateLengow($order_state) != 'shipped') {
            return false;
        }
        return true;
    }


    /**
     * Save order in lengow orders table
     *
     * @param string $lengow_id Lengow order id
     * @param integer $feed_id Lengow feed id
     * @param LengowOrder $order order imported
     * @param SimpleXmlElement $order_data order data
     * @param string $message order message
     *
     * @return boolean
     */
    protected static function addLengowOrder($lengow_id, $feed_id, $order, $order_data, $message = '')
    {
        if (_PS_VERSION_ >= '1.5') {
            return Db::getInstance()->insert(
                'lengow_orders',
                array(
                    'id_order' => (int)$order->id,
                    'id_order_lengow' => pSQL($lengow_id),
                    'id_shop' => (int)$order->id_shop,
                    'id_shop_group' => (int)$order->id_shop_group,
                    'id_lang' => (int)$order->id_lang,
                    'id_flux' => (int)$feed_id,
                    'marketplace' => pSQL(Tools::strtolower((string)$order_data->marketplace)),
                    'message' => pSQL($message),
                    'total_paid' => (float)$order_data->order_amount,
                    'carrier' => pSQL((string)$order_data->tracking_informations->tracking_carrier),
                    'tracking' => pSQL((string)$order_data->tracking_informations->tracking_number),
                    'extra' => pSQL(Tools::jsonEncode($order_data)),
                    'date_add' => date('Y-m-d H:i:s'),
                    'is_disabled' => 0,
                )
            );
        } else {
            return Db::getInstance()->autoExecute(
                _DB_PREFIX_ . 'lengow_orders',
                array(
                    'id_order' => (int)$order->id,
                    'id_order_lengow' => pSQL($lengow_id),
                    'id_shop' => (int)Context::getContext()->shop->id,
                    'id_shop_group' => (int)Context::getContext()->shop->id_shop_group,
                    'id_lang' => (int)$order->id_lang,
                    'id_flux' => (int)$feed_id,
                    'marketplace' => pSQL(Tools::strtolower((string)$order_data->marketplace)),
                    'message' => pSQL($message),
                    'total_paid' => (float)$order_data->order_amount,
                    'carrier' => pSQL((string)$order_data->tracking_informations->tracking_carrier),
                    'tracking' => pSQL((string)$order_data->tracking_informations->tracking_number),
                    'extra' => pSQL(Tools::jsonEncode($order_data)),
                    'date_add' => date('Y-m-d H:i:s'),
                    'is_disabled' => 0,
                ),
                'INSERT');
        }
    }

    /**
     * Get carrier id according to the tracking informations given in the API
     *
     * @param SimpleXmlElement $tracking_informations API shipping information
     * @param LengowMarketplace $marketplace Lengow Marketplace
     * @param integer $id_lang language id
     * @param string $lengow_id Lengow order id
     * @param LengowAddress $shipping_address Lengow Address
     *
     * @return mixed
     */
    public static function getCarrierId(
        $tracking_informations,
        LengowMarketplace $marketplace,
        $lengow_id,
        $id_lang,
        $shipping_address
    ) {
        $carrier_id = false;
        if (!Configuration::get('LENGOW_MP_SHIPPING_METHOD')) {
            $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
        }

        // get by tracking carrier
        if (!$carrier_id) {
            $carrier = Tools::strtolower((string)$tracking_informations->tracking_carrier);
            if (!empty($carrier)) {
                $carrier_id = LengowCarrier::matchCarrier($carrier, $marketplace, $id_lang, $shipping_address);
            }
        }

        // get by tracking method
        if (!$carrier_id) {
            $carrier = Tools::strtolower((string)$tracking_informations->tracking_method);
            if (!empty($carrier)) {
                $carrier_id = LengowCarrier::matchCarrier($carrier, $marketplace, $id_lang, $shipping_address);
            }
        }

        // assign default carrier if no carrier is found
        if (!$carrier_id) {
            $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
            LengowCore::log('no matching carrier found. Default carrier assigned.', false, $lengow_id);
        } else {
            // check if module is active and has not been deleted
            $carrier = new LengowCarrier($carrier_id);
            if (!$carrier->active || $carrier->deleted) {
                LengowCore::log('carrier ' . $carrier->name . ' is inactive or marked as deleted. Default carrier assigned.',
                    false, $lengow_id);
                $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
            } elseif ($carrier->is_module) // if carrier is module -> check if module is installed and active
            {
                if (!LengowCore::isModuleInstalled($carrier->external_module_name)) {
                    $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
                    LengowCore::log('carrier module ' . $carrier->external_module_name . ' not installed. Default carrier assigned.',
                        false, $lengow_id);
                }
            }

            // if carrier is SoColissimo -> check if module version is compatible
            if ($carrier_id == Configuration::get('SOCOLISSIMO_CARRIER_ID')) {
                if (!LengowCore::isSoColissimoAvailable()) {
                    $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
                    LengowCore::log('module version ' . $carrier->external_module_name . ' not supported. Default carrier assigned.',
                        false, $lengow_id);
                }
            }

            // if carrier is mondialrelay -> check if module version is compatible
            if ($carrier->external_module_name == 'mondialrelay') {
                if (!LengowCore::isMondialRelayAvailable()) {
                    $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
                    LengowCore::log('module version ' . $carrier->external_module_name . ' not supported. Default carrier assigned.',
                        false, $lengow_id);
                }
            }
        }
        return $carrier_id;
    }

    /**
     * Add quantity back to stock
     * @param array $products list of products
     * @param integer $id_shop shop id
     *
     * @return boolean
     */
    protected static function addQuantityBack($products, $id_shop)
    {
        foreach ($products as $sku => $product) {
            $product_ids = explode('_', $sku);
            $id_product_attribute = isset($product_ids[1]) ? $product_ids[1] : null;
            if (_PS_VERSION_ < '1.5') {
                $p = new LengowProduct($product_ids[0]);
                return $p->addStockMvt($product['quantity'], (int)_STOCK_MOVEMENT_ORDER_REASON_, $id_product_attribute);
            } else {
                return StockAvailable::updateQuantity((int)$product_ids[0], $id_product_attribute, $product['quantity'],
                    $id_shop);
            }
        }
    }

}


/**
 * Lengow Import Exception class
 */
class LengowImportException extends Exception
{

}