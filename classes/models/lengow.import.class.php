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
* Lengow Import class
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
     * @var string markeplace name
     */
    protected $marketplace_name = null;

    /**
     * @var integer number of orders to import
     */
    protected $limit = 0;

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
     * @var array valid states lengow to import
     */
    public static $STATES_LENGOW = array(
        'accepted',
        'waiting_shipment',
        'shipped',
        'closed',
    );

    /**
     * Construct the import manager
     *
     * @param string    $order_id           lengow order id to import
     * @param string    $marketplace_name   lengow marketplace name to import
     * @param boolean   $force_product      force import of products
     * @param boolean   $debug              debug mode
     * @param string    $date_from          starting import date
     * @param string    $date_to            ending import date
     * @param integer   $limit              number of orders to import
     * @param boolean   $log_output         display log messages
     */
    public function __construct(
        $order_id = null,
        $marketplace_name = null,
        $force_product = true,
        $debug = false,
        $date_from = null,
        $date_to = null,
        $limit = 0,
        $log_output = false
    ) {
        $this->id_lang          = Context::getContext()->language->id;
        $this->id_shop          = Context::getContext()->shop->id;
        $this->id_shop_group    = Context::getContext()->shop->id_shop_group;
        $this->force_product    = $force_product;
        $this->debug            = $debug;
        $this->limit            = $limit;
        if (!is_null($order_id)) {
            $this->order_id         = $order_id;
            $this->marketplace_name = $marketplace_name;
            $log_output = false;
        }
        $this->log_output       = $log_output;
        $this->date_from        = $date_from;
        $this->date_to          = $date_to;

        if (Configuration::get('LENGOW_REPORT_MAIL') && !$this->debug) {
            LengowMain::sendMailAlert();
        }
    }

    /**
     * Excute import : fetch orders and import them
     *
     */
    public function exec()
    {
        // 1st step: check if import is already in process
        // if (LengowImport::isInProcess() && !$this->debug) {
        //     LengowMain::log('import already in process', $this->log_output);
        //     return false;
        // }
        // // 2nd step: start import process
        // LengowImport::setInProcess();
        // 3rd step: disable emails
        LengowMain::disableMail();
        try {
            // start of actual import process
            // get orders from Lengow API
            $result = LengowImport::getOrdersFromApi(
                $this->order_id,
                $this->marketplace_name,
                $this->date_from,
                $this->date_to
            );
            $total_orders = count($result);
            if ($this->order_id) {
                LengowMain::log(
                    $total_orders
                    .' order found for order ID: '.$this->order_id
                    .' and markeplace: '.$this->marketplace_name
                    .' with account ID: '.LengowMain::getIdAccount(),
                    $this->log_output
                );
            } else {
                LengowMain::log(
                    $total_orders.' order'.($total_orders > 1 ? 's ' : ' ')
                    .'found with account ID: '.LengowMain::getIdAccount(),
                    $this->log_output
                );
            }
            if ($total_orders <= 0) {
                LengowImport::setEnd();
                return false;
            }
            // import orders in prestashop
            LengowImport::importOrders(
                $result,
                $this->id_lang,
                $this->id_shop,
                $this->debug,
                $this->force_product,
                $this->limit,
                $this->log_output
            );
        } catch (Exception $e) {
            LengowMain::log('Error: '.$e->getMessage(), $this->log_output);
            LengowImport::setEnd();
            return false;
        }
        LengowImport::setEnd();
        return true;
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
            // security check : if last import is more than 10 min old => authorize new import to be launched
            if (($timestamp + (60 * 10)) < time()) {
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
     * @param string $order_id
     * @param string $marketplace_name
     * @param string $date_from
     * @param string $date_to
     *
     * @return SimpleXmlElement
     */
    protected function getOrdersFromApi($order_id, $marketplace_name, $date_from, $date_to)
    {
        $page = 1;
        $orders = array();
        $account_id = LengowMain::getIdAccount();

        if (LengowCheck::isValidAuth()) {
            $connector  = new LengowConnector(LengowMain::getAccessToken(), LengowMain::getSecretCustomer());
            if ($order_id && $marketplace_name) {
                LengowMain::log(
                    'Connector: get order with order id: '.$order_id
                    .' and marketplace: '.$marketplace_name,
                    $this->log_output
                );
            } else {
                LengowMain::log(
                    'Connector: get orders between '.date('Y-m-d', strtotime((string)$date_from))
                    .' and '.date('Y-m-d', strtotime((string)$date_to))
                    .' with account ID: '.$account_id,
                    $this->log_output
                );
            }
            do {
                if ($order_id && $marketplace_name) {
                    $results = $connector->get(
                        '/v3.0/orders',
                        array(
                            'marketplace_order_id'  => $order_id,
                            'marketplace'           => $marketplace_name,
                            'account_id'            => $account_id,
                            'page'                  => $page
                        ),
                        'stream'
                    );
                } else {
                    $results = $connector->get(
                        '/v3.0/orders',
                        array(
                            'account_id'            => $account_id,
                            'updated_from'          => $date_from,
                            'updated_to'            => $date_to,
                            'page'                  => $page
                        ),
                        'stream'
                    );
                }
                if (is_null($results)) {
                    throw new LengowImportException('the connection didn\'t work with the Lengow webservice');
                }
                $results = Tools::jsonDecode($results);
                if (isset($results->error)) {
                    throw new LengowImportException(
                        'Lengow webservice : '.$results->error->code.' - '.$results->error->message
                    );
                }
                // Construct array orders
                foreach ($results->results as $order) {
                    $orders[] = $order;
                }
                $page++;
            } while ($results->next != null);
        } else {
            throw new LengowImportException('Account ID, Token access or Secret are not valid');
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
     * @param SimpleXmlElement  $orders         API orders
     * @param integer           $id_lang        language id
     * @param integer           $id_shop        shop id
     * @param boolean           $debug          debug mode
     * @param integer           $limit          limit number of import
     *
     */
    protected static function importOrders(
        $orders,
        $id_lang,
        $id_shop,
        $debug = false,
        $force_product = true,
        $limit = 0,
        $log_output = false
    ) {
        $count_orders_updated = 0;
        $count_orders_added = 0;
        foreach ($orders as $order_data) {
            $lengow_id = (string)$order_data->marketplace_order_id;
            if ($debug) {
                $lengow_id .= '--'.time();
            }
            // set current order to cancel hook updateOrderStatus
            LengowImport::$current_order = $lengow_id;
            // if order contains no package
            if (count($order_data->packages) == 0) {
                LengowMain::log('create order fail: no package in the order', true, $lengow_id);
                continue;
            }
            // check order status
            $marketplace = LengowMain::getMarketplaceSingleton((string)$order_data->marketplace);
            $order_state = (string)$order_data->marketplace_status;
            // if first package -> import processing fees and shipping
            $first = true;
            foreach ($order_data->packages as $package) {
                // if package contains no product
                if (count($package->cart) == 0) {
                    LengowMain::log('create order fail: no product in the order', $log_output, $lengow_id);
                    continue;
                }
                // if marketplace order line id is null
                if (is_null($package->cart[0]->marketplace_order_line_id)) {
                    LengowMain::log('create order fail: no order line id for a product', $log_output, $lengow_id);
                    continue;
                }
                // get all lines ids
                $order_line_ids = array();
                foreach ($package->cart as $product) {
                    $order_line_ids[] = (string)$product->marketplace_order_line_id;
                }
                // if log import exist and not finished
                $message = LengowLog::loadLogInfo($lengow_id, $order_line_ids[0]);
                if ($message) {
                    LengowMain::log($message, $log_output, $lengow_id);
                    continue;
                }
                // check order data
                if (!LengowImport::checkOrderData($order_data, $package, $lengow_id, $order_line_ids[0], $debug)) {
                    continue;
                }
                // update order state if already imported
                $order_id = LengowOrder::getOrderIdFromLengowOrders(
                    $lengow_id,
                    $order_line_ids[0],
                    (string)$marketplace->name
                );
                if ($order_id) {
                    LengowMain::log('order already imported (ORDER '.$order_id.')', $log_output, $lengow_id);
                    $order = new LengowOrder($order_id);
                    // Lengow -> Cancel and reimport order
                    if ($order->is_disabled) {
                        LengowMain::log('order is disabled (ORDER '.$order_id.')', $log_output, $lengow_id);
                        $order->setStateToError();
                    } else {
                        try {
                            if ($order->updateState(
                                $marketplace,
                                $order_state,
                                (count($package->delivery->trackings) > 0
                                    ?(string)$package->delivery->trackings[0]->number
                                    : null
                                )
                            )) {
                                $api_state = $marketplace->getStateLengow($order_state);
                                $available_states = LengowMain::getOrderStates($id_lang);
                                foreach ($available_states as $state) {
                                    if ($state['id_order_state'] === LengowMain::getOrderState($api_state)) {
                                        $state_name = $state['name'];
                                    }
                                }
                                LengowMain::log(
                                    'order\'s state has been updated to "'.$state_name.'"',
                                    $log_output,
                                    $lengow_id
                                );
                                $count_orders_updated++;
                            }
                        } catch (Exception $e) {
                            LengowMain::log('error while updating state: '.$e->getMessage(), $log_output, $lengow_id);
                        }
                        unset($order);
                        continue;
                    }
                }
                // if order is cancelled or new -> skip
                if (!LengowImport::checkState($order_state, $marketplace)) {
                    LengowMain::log(
                        'current order\'s state ['.$order_state.'] makes it unavailable to import',
                        $log_output,
                        $lengow_id
                    );
                    continue;
                }
                try {
                    // checks if an external id already exists
                    $external_ids = $order_data->merchant_order_id;
                    $line_id = false;
                    if (!is_null($external_ids) && count($external_ids) > 0) {
                        foreach ($external_ids as $external_id) {
                            $line_id = LengowOrder::getIdFromLengowOrderLine((integer)$external_id, $order_line_ids[0]);
                            if ($line_id) {
                                $id_order_prestashop = $external_id;
                                break;
                            }
                        }
                    }
                    // if order is disabled, reimport it
                    $is_reimport = false;
                    if (isset($order)) {
                        $is_reimport = $order->is_disabled;
                    }
                    if ($line_id && !$debug && !$is_reimport) {
                        $message = 'already imported in Prestashop with order ID '.$id_order_prestashop;
                        LengowLog::addLog($order_data, $lengow_id, $order_line_ids[0], $message, 1);
                        LengowMain::log($message, $log_output, $lengow_id);
                        continue;
                    }
                    // get all trackings from order
                    $trackings = $package->delivery->trackings;
                    if (count($trackings) == 0) {
                        $trackings = null;
                    }
                    // shipment by marketplace
                    $shipped_by_mp = false;
                    if (!is_null($trackings) && (integer)$trackings[0]->is_delivered_by_marketplace == 1) {
                        $shipped_by_mp = true;
                        $message = 'order shipped by '.$marketplace->name;
                        LengowMain::log($message, $log_output, $lengow_id);
                        if (!Configuration::get('LENGOW_IMPORT_SHIPPED_BY_MP')) {
                            if (!$debug) {
                                LengowLog::addLog($order_data, $lengow_id, $order_line_ids[0], $message, 1);
                            }
                            continue;
                        }
                    }
                    $cart_data = array();
                    $cart_data['id_lang'] = $id_lang;
                    $cart_data['id_shop'] = $id_shop;
                    // get billing datas
                    $billing_data = LengowAddress::extractAddressDataFromAPI($order_data->billing_address);
                    // create customer based on billing data
                    if (Configuration::get('LENGOW_IMPORT_FAKE_EMAIL') || $debug || empty($billing_data['email'])) {
                        $billing_data['email'] = 'generated-email+'.$lengow_id.'@'.LengowMain::getHost();
                        LengowMain::log('generate unique email : '.$billing_data['email'], $debug, $lengow_id);
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
                    $shipping_data = LengowAddress::extractAddressDataFromAPI($package->delivery);
                    $shipping_address = LengowImport::getAddress(
                        $shipping_data,
                        (!is_null($trackings) ? $trackings[0] : null)
                    );
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
                    $cart_data['id_currency'] = (int)Currency::getIdByIsoCode((string)$order_data->currency->iso_a3);
                    // get carrier
                    $cart_data['id_carrier'] = LengowImport::getCarrierId(
                        (!is_null($trackings) ? $trackings[0] : null),
                        $marketplace,
                        $lengow_id,
                        $id_lang,
                        $shipping_address
                    );
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
                    $products = LengowImport::getProducts($package->cart, $marketplace, $lengow_id, $id_shop);
                    $cart->addProducts($products, $force_product);
                    // add cart to context
                    Context::getContext()->cart = $cart;
                    // rewrite processing fees and shipping cost
                    if (!Configuration::get('LENGOW_IMPORT_PROCESSING_FEE') || $first == false) {
                        $order_data->processing_fee = 0;
                        LengowMain::log('rewrite amount without processing fee', $log_output, $lengow_id);
                    }
                    if ($first == false) {
                        $order_data->commission = 0;
                        $order_data->shipping = 0;
                        LengowMain::log('rewrite amount without shipping cost', $log_output, $lengow_id);
                    }
                    // get total amount and shipping
                    $total_amount = 0;
                    foreach ($package->cart as $product) {
                        // check whether the product is canceled for amount
                        if (!is_null($product->marketplace_status)) {
                            $state_product = $marketplace->getStateLengow((string)$product->marketplace_status);
                            if ($state_product == 'canceled' || $state_product == 'refused') {
                                continue;
                            }
                        }
                        $total_amount += (float)$product->amount;
                    }
                    $order_amount = (float)$total_amount + (float)$order_data->processing_fee + (float)$order_data->shipping;
                    // create payment
                    $id_order_state = LengowMain::getPrestahopStateId($order_state, $marketplace, $shipped_by_mp);
                    $payment = new LengowPaymentModule();
                    $payment->active = true;
                    $payment_method = Configuration::get('LENGOW_IMPORT_METHOD_NAME') == 'lengow'
                        ? 'Lengow'
                        : (string)$order_data->marketplace;
                    $message = 'Import Lengow | '."\r\n"
                        .'ID order : '.(string)$order_data->marketplace_order_id.' | '."\r\n"
                        .'Marketplace : '.(string)$order_data->marketplace.' | '."\r\n"
                        .'ID order line : '.(string)$order_line_ids[0].' | '."\r\n"
                        .'Total paid : '.(float)$order_amount.' | '."\r\n"
                        .'Shipping : '.(string)$order_data->shipping.' | '."\r\n"
                        .'Message : '.(string)$order_data->comments."\r\n";
                    // validate order
                    $order_list = array();
                    if (_PS_VERSION_ >= '1.5') {
                        $order_list = $payment->makeOrder(
                            $cart->id,
                            $id_order_state,
                            $order_amount,
                            $payment_method,
                            $message,
                            $products,
                            (float)$order_data->shipping,
                            (float)$order_data->processing_fee
                        );
                    } else {
                        $order_list = $payment->makeOrder14(
                            $cart->id,
                            $id_order_state,
                            $order_amount,
                            $payment_method,
                            $message,
                            $products,
                            (float)$order_data->shipping,
                            (float)$order_data->processing_fee
                        );
                    }
                    // if no order in list
                    if (empty($order_list)) {
                        throw new Exception('order could not be saved');
                    } else {
                        $count_orders_added++;
                        foreach ($order_list as $order) {
                            // add order comment from marketplace to prestashop order
                            if (_PS_VERSION_ >= '1.5') {
                                $comment = (string)$order_data->comments;
                                if (!empty($comment)) {
                                    $msg = new Message();
                                    $msg->id_order = $order->id;
                                    $msg->private = 1;
                                    $msg->message = $comment;
                                    $msg->add();
                                }
                            }
                            $success_message = 'order successfully imported (ID '.$order->id.')';
                            if (!LengowImport::addLengowOrder(
                                $lengow_id,
                                $order,
                                $order_data,
                                $package,
                                $order_amount
                            )) {
                                LengowMain::log(
                                    'WARNING ! Order could NOT be saved in lengow orders table',
                                    $debug,
                                    $lengow_id
                                );
                            } else {
                                LengowMain::log('order saved in lengow orders table', $debug, $lengow_id);
                            }
                            // Save order line id in lengow_order_line table
                            $order_line_saved = false;
                            foreach ($order_line_ids as $order_line_id) {
                                LengowImport::addLengowOrderLine($order, $order_line_id);
                                $order_line_saved .= (!$order_line_saved ? $order_line_id : ' / '.$order_line_id);
                            }
                            LengowMain::log('save order lines product : '.$order_line_saved, $debug, $lengow_id);
                            // if more than one order (different warehouses)
                            LengowMain::log($success_message, $log_output, $lengow_id);
                        }
                        // Sync to lengow if no debug
                        if (!$debug) {
                            $order_ids = LengowOrder::getOrderIdFromLengowOrder($lengow_id, (string)$marketplace->name);
                            if (count($order_ids) > 0) {
                                $presta_ids = array();
                                foreach ($order_ids as $order_id) {
                                    $presta_ids[] = $order_id['id_order'];
                                }
                                $connector  = new LengowConnector(
                                    LengowMain::getAccessToken(),
                                    LengowMain::getSecretCustomer()
                                );
                                $result = $connector->patch(
                                    '/v3.0/orders',
                                    array(
                                        'account_id'            => LengowMain::getIdAccount(),
                                        'marketplace_order_id'  => $lengow_id,
                                        'marketplace'           => (string)$order_data->marketplace,
                                        'merchant_order_id'     => $presta_ids
                                    )
                                );
                                if (is_null($result)
                                    || (isset($result['detail']) && $result['detail'] == 'Pas trouvé.')
                                    || isset($result['error'])
                                ) {
                                    LengowMain::log(
                                        'WARNING ! Order could NOT be synchronised with Lengow webservice (ID '
                                        .$order->id
                                        .')',
                                        $debug,
                                        $lengow_id
                                    );
                                } else {
                                    LengowMain::log(
                                        'order successfully synchronised with Lengow webservice (ID '
                                        .$order->id
                                        .')',
                                        $debug,
                                        $lengow_id
                                    );
                                }
                            }
                            LengowLog::addLog($order_data, $lengow_id, $order_line_ids[0], $success_message, 1);
                        }
                        // ensure carrier compatibility with SoColissimo & Mondial Relay
                        try {
                            $carrier_name = '';
                            if (!is_null($trackings)) {
                                if (!$carrier_name = (string)$trackings[0]->carrier) {
                                    $carrier_name = (string)$trackings[0]->method;
                                }
                            }
                            $carrier_compatibility = LengowCarrier::carrierCompatibility(
                                $order->id_customer,
                                $order->id_cart,
                                $order->id_carrier,
                                $shipping_address
                            );
                            if ($carrier_compatibility < 0) {
                                throw new LengowCarrierException(
                                    'carrier '.$carrier_name.' could not be found in your Prestashop'
                                );
                            } elseif ($carrier_compatibility > 0) {
                                LengowMain::log(
                                    'carrier compatibility ensured with carrier '.$carrier_name,
                                    $debug,
                                    $lengow_id
                                );
                            }
                        } catch (LengowCarrierException $lce) {
                            LengowMain::log($lce->getMessage(), $debug, $lengow_id);
                        }
                    }
                    if ($shipped_by_mp) {
                        LengowMain::log(
                            'adding quantity back to stock (order shipped by marketplace)',
                            $log_output,
                            $lengow_id
                        );
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
                    LengowMain::log('order import failed: '.$error_message, $log_output, $lengow_id);
                    if (!$debug) {
                        LengowLog::addLog($order_data, $lengow_id, $order_line_ids[0], $error_message);
                    }
                    unset($error_message);
                }
                // clean process
                LengowImport::$current_order = -1;
                $first = false;
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
        }
        LengowMain::log($count_orders_added.' order(s) imported', $log_output);
        LengowMain::log($count_orders_updated.' order(s) updated', $log_output);
        // return last order id of the list
        if (isset($order)) {
            return $order->id;
        }
    }

    /**
     * Checks if order data are present
     *
     * @param mixed     $order_data
     * @param mixed     $package
     * @param string    $lengow_id
     * @param string    $order_line_id
     * @param boolean   $debug
     *
     * @return boolean
     */
    protected static function checkOrderData($order_data, $package, $lengow_id, $order_line_id, $debug)
    {
        $error_message = false;
        if (is_null($order_data->currency)) {
            $error_message = 'no currency in the order';
        } elseif (is_null($order_data->billing_address)) {
            $error_message = 'no billing address in the order';
        } elseif (is_null($order_data->billing_address->common_country_iso_a2)) {
            $error_message = 'billing address doesn\'t have country';
        } elseif (!isset($package->delivery->id)) {
            $error_message = 'create order fail: no delivery address in the order';
        } elseif (is_null($package->delivery->common_country_iso_a2)) {
            $error_message = 'delivery address doesn\'t have country';
        }
        if ($error_message) {
            LengowMain::log('order import failed: '.$error_message, true, $lengow_id);
            if (!$debug) {
                LengowLog::addLog($order_data, $lengow_id, $order_line_id, $error_message);
            }
            return false;
        }
        return true;
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
     * @param array     $address_data           API data
     * @param array     $tracking_informations  API data
     *
     * @return LengowAddress
     */
    protected static function getAddress($address_data = array(), $tracking_informations = null)
    {
        $address_data['address_full'] = '';
        // construct field address_full
        $address_data['address_full'] .= !empty($address_data['first_line']) ? $address_data['first_line'].' ' : '';
        $address_data['address_full'] .= !empty($address_data['second_line']) ? $address_data['second_line'].' ' : '';
        $address_data['address_full'] .= !empty($address_data['complement']) ? $address_data['complement'].' ' : '';
        $address_data['address_full'] .= !empty($address_data['zipcode']) ? $address_data['zipcode'].' ' : '';
        $address_data['address_full'] .= !empty($address_data['city']) ? $address_data['city'].' ' : '';
        $address_data['address_full'] .= !empty($address_data['common_country_iso_a2']) ? $address_data['common_country_iso_a2'].' ' : '';
        // if tracking_informations exist => get id_relay
        if (!is_null($tracking_informations) && !empty($tracking_informations->relay->id)) {
            $address_data['id_relay'] = (string)$tracking_informations->relay->id;
        }
        // construct LengowAddress and assign values
        $address = new LengowAddress();
        $address->assign($address_data);
        return $address;
    }

    /**
     * Get products from API data
     *
     * @param SimpleXMLElement  $cart_data      API cart data
     * @param LengowMarketplace $marketplace    order marketplace
     * @param string            $lengow_id      lengow order id
     * @param integer           $id_shop        shop id
     *
     * @return array list of products
     */
    protected static function getProducts($cart_data, $marketplace, $lengow_id, $id_shop)
    {
        $products = array();
        foreach ($cart_data as $product) {
            $product_data = LengowProduct::extractProductDataFromAPI($product);
            if (!is_null($product_data['marketplace_status'])) {
                $state_product = $marketplace->getStateLengow((string)$product_data['marketplace_status']);
                if ($state_product == 'canceled' || $state_product == 'refused') {
                    LengowMain::log(
                        'product '.$product_data['merchant_product_id']->id
                        .' could not be added to cart - status: '.$state_product,
                        false,
                        $lengow_id
                    );
                    continue;
                }
            }
            $ids = false;
            $product_ids = array(
                                'idMerchant' => (string)$product_data['merchant_product_id']->id,
                                'idMP' => (string)$product_data['marketplace_product_id'],
                            );
            $found = false;
            foreach ($product_ids as $attribute_name => $attribute_value) {
                // remove _FBA from product id
                $attribute_value = preg_replace('/_FBA$/', '', $attribute_value);

                if (empty($attribute_value)) {
                    continue;
                }
                $ids = LengowProduct::matchProduct($attribute_name, $attribute_value, $id_shop, $product_ids);
                // no product found in the "classic" way => use advanced search
                if (!$ids) {
                    LengowMain::log(
                        'product not found with field '.$attribute_name
                        .' ('.$attribute_value.'). Using advanced search.',
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
                            throw new LengowImportException(
                                'product '.$p->id.' is a parent ID. Product variation needed'
                            );
                        }
                    }
                    $id_full .= isset($ids['id_product_attribute']) ? '_'.$ids['id_product_attribute'] : '';
                    if (array_key_exists($id_full, $products)) {
                        $products[$id_full]['quantity'] += (integer)$product_data['quantity'];
                        $products[$id_full]['amount'] += (float)$product_data['amount'];
                    } else {
                        $products[$id_full] = $product_data;
                    }
                    LengowMain::log(
                        'product id '.$id_full
                        .' found with field '.$attribute_name.' ('.$attribute_value.')',
                        false,
                        $lengow_id
                    );
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                throw new Exception(
                    'product '
                    .(!is_null($product_data['merchant_product_id']->id)
                        ? (string)$product_data['merchant_product_id']->id
                        : (string)$product_data['marketplace_product_id']
                    )
                    .' could not be found'
                );
            }
        }
        return $products;
    }

    /**
     * Check if order status is valid and is available for import
     *
     * @param string            $order_state    order state
     * @param LengowMarketplace $marketplace    order marketplace
     *
     * @return boolean
     */
    protected static function checkState($order_state, $marketplace)
    {
        if (empty($order_state)) {
            return false;
        }
        if (!in_array($marketplace->getStateLengow($order_state), self::$STATES_LENGOW)) {
            return false;
        }
        return true;
    }


    /**
     * Save order in lengow orders table
     *
     * @param string        $lengow_id      Lengow order id
     * @param LengowOrder   $order          order imported
     * @param array         $order_data     order data
     * @param array         $package_data   package data
     * @param float         $order_amount   order amount
     *
     * @return boolean
     */
    protected static function addLengowOrder($lengow_id, $order, $order_data, $package_data, $order_amount)
    {
        if (_PS_VERSION_ >= '1.5') {
            return Db::getInstance()->insert(
                'lengow_orders',
                array(
                    'id_order'          => (int)$order->id,
                    'id_order_lengow'   => pSQL($lengow_id),
                    'id_order_line'     => pSQL((string)$package_data->cart[0]->marketplace_order_line_id),
                    'id_shop'           => (int)$order->id_shop,
                    'id_shop_group'     => (int)$order->id_shop_group,
                    'id_lang'           => (int)$order->id_lang,
                    'marketplace'       => pSQL(Tools::strtolower((string)$order_data->marketplace)),
                    'message'           => pSQL((string)$order_data->comments),
                    'total_paid'        => (float)$order_amount,
                    'carrier'           => pSQL(
                        count($package_data->delivery->trackings) > 0
                        ? (string)$package_data->delivery->trackings[0]->carrier
                        : null
                    ),
                    'method'            => pSQL(
                        count($package_data->delivery->trackings) > 0
                        ? (string)$package_data->delivery->trackings[0]->method
                        : null
                    ),
                    'tracking'          => pSQL(
                        count($package_data->delivery->trackings) > 0
                        ? (string)$package_data->delivery->trackings[0]->number
                        : null
                    ),
                    'sent_marketplace'  => pSQL(
                        count($package_data->delivery->trackings) > 0
                        ? ($package_data->delivery->trackings[0]->is_delivered_by_marketplace == null ? 0 : 1)
                        : 0
                    ),
                    'extra'             => pSQL(Tools::jsonEncode($order_data)),
                    'date_add'          => date('Y-m-d H:i:s'),
                    'is_disabled'       => 0,
                )
            );
        } else {
            return Db::getInstance()->autoExecute(
                _DB_PREFIX_.'lengow_orders',
                array(
                    'id_order'          => (int)$order->id,
                    'id_order_lengow'   => pSQL($lengow_id),
                    'id_order_line'     => pSQL((string)$package_data->cart[0]->marketplace_order_line_id),
                    'id_shop'           => (int)Context::getContext()->shop->id,
                    'id_shop_group'     => (int)Context::getContext()->shop->id_shop_group,
                    'id_lang'           => (int)$order->id_lang,
                    'marketplace'       => pSQL(Tools::strtolower((string)$order_data->marketplace)),
                    'message'           => pSQL((string)$order_data->comments),
                    'total_paid'        => (float)$order_amount,
                    'carrier'           => pSQL(
                        count($package_data->delivery->trackings) > 0
                        ? (string)$package_data->delivery->trackings[0]->carrier
                        : null
                    ),
                    'method'            => pSQL(
                        count($package_data->delivery->trackings) > 0
                        ? (string)$package_data->delivery->trackings[0]->method
                        : null
                    ),
                    'tracking'          => pSQL(
                        count($package_data->delivery->trackings) > 0
                        ? (string)$package_data->delivery->trackings[0]->number
                        : null
                    ),
                    'sent_marketplace'  => pSQL(
                        count($package_data->delivery->trackings) > 0
                        ? ($package_data->delivery->trackings[0]->is_delivered_by_marketplace == null ? 0 : 1)
                        : 0
                    ),
                    'extra'             => pSQL(Tools::jsonEncode($order_data)),
                    'date_add'          => date('Y-m-d H:i:s'),
                    'is_disabled'       => 0,
                    ),
                'INSERT'
            );
        }
    }

    /**
     * Save order line in lengow orders line table
     *
     * @param LengowOrder   $order          order imported
     * @param string        $order_line_id  order line ID
     *
     * @return boolean
     */
    protected static function addLengowOrderLine($order, $order_line_id)
    {
        if (_PS_VERSION_ >= '1.5') {
            return Db::getInstance()->insert(
                'lengow_order_line',
                array(
                    'id_order'      => (int)$order->id,
                    'id_order_line' => pSQL($order_line_id),
                )
            );
        } else {
            return Db::getInstance()->autoExecute(
                _DB_PREFIX_.'lengow_order_line',
                array(
                    'id_order'      => (int)$order->id,
                    'id_order_line' => pSQL($order_line_id),
                    ),
                'INSERT'
            );
        }
    }

    /**
     * Get carrier id according to the tracking informations given in the API
     *
     * @param SimpleXmlElement  $tracking_informations  API shipping information
     * @param LengowMarketplace $marketplace            Lengow Marketplace
     * @param integer           $id_lang                language id
     * @param string            $lengow_id              Lengow order id
     * @param LengowAddress     $shipping_address       Lengow Address
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
        if (!Configuration::get('LENGOW_MP_SHIPPING_METHOD') || is_null($tracking_informations)) {
            $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
        }
        // get by tracking carrier
        if (!$carrier_id) {
            $carrier = Tools::strtolower((string)$tracking_informations->carrier);
            if (!empty($carrier)) {
                $carrier_id = LengowCarrier::matchCarrier($carrier, $marketplace, $id_lang, $shipping_address);
            }
        }
        // get by tracking method
        if (!$carrier_id) {
            $carrier = Tools::strtolower((string)$tracking_informations->method);
            if (!empty($carrier)) {
                $carrier_id = LengowCarrier::matchCarrier($carrier, $marketplace, $id_lang, $shipping_address);
            }
        }
        // assign default carrier if no carrier is found
        if (!$carrier_id) {
            $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
            LengowMain::log('no matching carrier found. Default carrier assigned.', false, $lengow_id);
        } else {
            // check if module is active and has not been deleted
            $carrier = new LengowCarrier($carrier_id);
            if (!$carrier->active || $carrier->deleted) {
                LengowMain::log(
                    'carrier '.$carrier->name.' is inactive or marked as deleted. Default carrier assigned.',
                    false,
                    $lengow_id
                );
                $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
            } elseif ($carrier->is_module) {
                if (!LengowMain::isModuleInstalled($carrier->external_module_name)) {
                    $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
                    LengowMain::log(
                        'carrier module '.$carrier->external_module_name.' not installed. Default carrier assigned.',
                        false,
                        $lengow_id
                    );
                }
            }
            // if carrier is SoColissimo -> check if module version is compatible
            if ($carrier_id == Configuration::get('SOCOLISSIMO_CARRIER_ID')) {
                if (!LengowMain::isSoColissimoAvailable()) {
                    $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
                    LengowMain::log(
                        'module version '.$carrier->external_module_name.' not supported. Default carrier assigned.',
                        false,
                        $lengow_id
                    );
                }
            }
            // if carrier is mondialrelay -> check if module version is compatible
            if ($carrier->external_module_name == 'mondialrelay') {
                if (!LengowMain::isMondialRelayAvailable()) {
                    $carrier_id = (int)Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT');
                    LengowMain::log(
                        'module version '.$carrier->external_module_name.' not supported. Default carrier assigned.',
                        false,
                        $lengow_id
                    );
                }
            }
        }
        return $carrier_id;
    }

    /**
     * Add quantity back to stock
     * @param array     $products   list of products
     * @param integer   $id_shop    shop id
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
                return StockAvailable::updateQuantity(
                    (int)$product_ids[0],
                    $id_product_attribute,
                    $product['quantity'],
                    $id_shop
                );
            }
        }
    }
}
