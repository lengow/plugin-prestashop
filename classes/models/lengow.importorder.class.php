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
class LengowImportOrder
{
    /**
     * Version.
     */
    const VERSION = '1.0.1';

    /**
     * @var integer shop id
     */
    protected $id_shop = null;

    /**
     * @var integer shop group id
     */
    protected $id_shop_group;

    /**
     * @var integer lang id
     */
    protected $id_lang;

    /**
     * @var Context Context for import order
     */
    protected $context;

    /**
     * @var boolean import inactive & out of stock products
     */
    protected $force_product = true;

    /**
     * @var boolean use preprod mode
     */
    protected $preprod_mode = false;

    /**
     * @var boolean display log messages
     */
    protected $log_output = false;

    /**
     * @var string id lengow of current order
     */
    protected $marketplace_sku;

    /**
     * @var integer id of delivery address for current order
     */
    protected $delivery_address_id;

    /**
     * @var mixed
     */
    protected $order_data;

    /**
     * @var mixed
     */
    protected $package_data;

    /**
     * @var boolean
     */
    protected $first_package;

    /**
     * @var mixed old Prestashop order for re-import
     */
    protected $old_order = null;

    /**
     * @var boolean re-import order
     */
    protected $is_reimported = false;

    /**
     * @var integer id of the record Lengow order table
     */
    protected $id_order_lengow;

    /**
     * @var LengowMarketplace
     */
    protected $marketplace;

    /**
     * @var string
     */
    protected $order_state_marketplace;

    /**
     * @var string
     */
    protected $order_state_lengow;

     /**
     * @var float
     */
    protected $processing_fee;

    /**
     * @var float
     */
    protected $shipping_cost;

    /**
     * @var float
     */
    protected $order_amount;

    /**
     * @var integer
     */
    protected $order_items;

    /**
     * @var string
     */
    protected $carrier_name = null;

    /**
     * @var string
     */
    protected $carrier_method = null;

    /**
     * @var string
     */
    protected $tracking_number = null;

    /**
     * @var boolean
     */
    protected $shipped_by_mp = false;

    /**
     * @var LengowAddress
     */
    protected $shipping_address;

    /**
     * @var string
     */
    protected $relay_id = null;


    /**
     * Construct the import manager
     *
     * @param array params optional options
     *
     * integer  $shop_id        Id shop for current order
     * integer  $id_shop_group  Id shop group for current order
     * integer  $id_lang        Id lang for current order
     * mixed    $context        Context for current order
     * boolean  $force_product  force import of products
     * boolean  $preprod_mode   preprod mode
     * boolean  $log_output     display log messages
     */
    public function __construct($params = array())
    {
        $this->id_shop              = $params['id_shop'];
        $this->id_shop_group        = $params['id_shop_group'];
        $this->id_lang              = $params['id_lang'];
        $this->context              = $params['context'];
        $this->force_product        = $params['force_product'];
        $this->preprod_mode         = $params['preprod_mode'];
        $this->log_output           = $params['log_output'];
        $this->marketplace_sku      = $params['marketplace_sku'];
        $this->delivery_address_id  = $params['delivery_address_id'];
        $this->order_data           = $params['order_data'];
        $this->package_data         = $params['package_data'];
        $this->first_package        = $params['first_package'];

        // get marketplace and Lengow order state
        $this->marketplace = LengowMain::getMarketplaceSingleton(
            (string)$this->order_data->marketplace,
            $this->id_shop
        );
        $this->order_state_marketplace = (string)$this->order_data->marketplace_status;
        $this->order_state_lengow = $this->marketplace->getStateLengow($this->order_state_marketplace);
    }

    /**
     * Create or update order
     *
     * @return mixed
     */
    public function importOrder()
    {
        // if log import exist and not finished
        $import_log = LengowOrder::orderIsInError($this->marketplace_sku, $this->delivery_address_id, 'import');
        if ($import_log) {
            $message = $import_log['message'].' (created on the '.$import_log['date'].')';
            LengowMain::log('Import', $message, $this->log_output, $this->marketplace_sku);
            return false;
        }
        // recovery id if the command has already been imported
        $order_id = LengowOrder::getOrderIdFromLengowOrders(
            $this->marketplace_sku,
            (string)$this->marketplace->name,
            $this->delivery_address_id
        );
        // update order state if already imported
        if ($order_id) {
            $order_updated = $this->checkAndUpdateOrder($order_id);
            if ($order_updated && isset($order_updated['update'])) {
                return $this->returnResult('update', $order_updated['lengow_order_id'], $order_id);
            }
            if (!$this->is_reimported) {
                return false;
            }
        }
        // checks if an external id already exists
        $id_order_prestashop = $this->checkExternalIds($this->order_data->merchant_order_id);
        if ($id_order_prestashop && !$this->preprod_mode && !$this->is_reimported) {
            LengowMain::log(
                'Import',
                'already imported in Prestashop with order ID '.$id_order_prestashop,
                $this->log_output,
                $this->marketplace_sku
            );
            return false;
        }
        // if order is cancelled or new -> skip
        if (!LengowImport::checkState($this->order_state_marketplace, $this->marketplace)) {
            LengowMain::log(
                'Import',
                'current order\'s state ['.$this->order_state_marketplace
                .'] makes it unavailable to import for marketplace '.$this->marketplace->name,
                $this->log_output,
                $this->marketplace_sku
            );
            return false;
        }
        // get a record in the lengow order table
        $this->id_order_lengow = LengowOrder::getIdFromLengowOrders($this->marketplace_sku, $this->delivery_address_id);
        if (!$this->id_order_lengow) {
            // created a record in the lengow order table
            if (!$this->createLengowOrder()) {
                LengowMain::log(
                    'Import',
                    'WARNING ! Order could NOT be saved in lengow orders table',
                    $this->log_output,
                    $this->marketplace_sku
                );
                return false;
            } else {
                LengowMain::log(
                    'Import',
                    'order saved in lengow orders table',
                    $this->log_output,
                    $this->marketplace_sku
                );
            }
        }
        // checks if the required order data is present
        if (!$this->checkOrderData()) {
            return $this->returnResult('error', $this->id_order_lengow);
        }
        // get order amount and load processing fees and shipping cost
        $this->order_amount = $this->getOrderAmount();
        // load tracking data
        $this->loadTrackingData();
        // get customer name
        $customer_name = $this->getCustomerName();
        // update Lengow order with new informations
        LengowOrder::updateOrderLengow(
            $this->id_order_lengow,
            array(
                'total_paid'            => $this->order_amount,
                'order_item'            => $this->order_items,
                'customer_name'         => pSQL($customer_name),
                'carrier'               => pSQL($this->carrier_name),
                'method'                => pSQL($this->carrier_method),
                'tracking'              => pSQL($this->tracking_number),
                'sent_marketplace'      => $this->shipped_by_mp,
                'delivery_country_iso'  => pSQL((string)$this->package_data->delivery->common_country_iso_a2),
                'order_lengow_state'    => pSQL($this->order_state_lengow)
            )
        );
        // try to import order
        try {
            // check if the order is shipped by marketplace
            if ($this->shipped_by_mp) {
                $message = 'order shipped by '.$this->marketplace->name;
                LengowMain::log(
                    'Import',
                    $message,
                    $this->log_output,
                    $this->marketplace_sku
                );
                if (!LengowConfiguration::getGlobalValue('LENGOW_IMPORT_SHIP_MP_ENABLED')) {
                    LengowOrder::updateOrderLengow(
                        $this->id_order_lengow,
                        array(
                            'order_process_state'   => 2,
                            'extra'                 => pSQL(Tools::jsonEncode($this->order_data))
                        )
                    );
                    return false;
                }
            }
            // get products
            $products = $this->getProducts();
            // create a cart with customer, billing address and shipping address
            $cart_data = $this->getCartData();
            if (_PS_VERSION_ < '1.5') {
                $cart = new LengowCart($this->context->cart->id);
            } else {
                $cart = new LengowCart();
            }
            $cart->assign($cart_data);
            $cart->validateLengow();
            $cart->force_product = $this->force_product;
            // add products to cart
            $cart->addProducts($products, $this->force_product);
            // add cart to context
            $this->context->cart = $cart;
            // create payment
            $order_list = $this->createAndValidatePayment($cart, $products);
            // if no order in list
            if (empty($order_list)) {
                throw new Exception('order could not be saved');
            } else {
                foreach ($order_list as $order) {
                    // add order comment from marketplace to prestashop order
                    if (_PS_VERSION_ >= '1.5') {
                        $this->addCommentOrder((int)$order->id);
                    }
                    $success_message = 'order successfully imported (ID '.$order->id.')';
                    $success = LengowOrder::updateOrderLengow(
                        $this->id_order_lengow,
                        array(
                            'id_order'              => (int)$order->id,
                            'order_process_state'   => LengowOrder::getOrderProcessState($this->order_state_lengow),
                            'extra'                 => pSQL(Tools::jsonEncode($this->order_data)),
                            'order_lengow_state'    => pSQL($this->order_state_lengow),
                            'is_reimported'         => 0
                        )
                    );
                    if (!$success) {
                        LengowMain::log(
                            'Import',
                            'WARNING ! Order could NOT be updated in lengow orders table',
                            $this->log_output,
                            $this->marketplace_sku
                        );
                    } else {
                        LengowMain::log(
                            'Import',
                            'order updated in lengow orders table',
                            $this->log_output,
                            $this->marketplace_sku
                        );
                    }
                    // Save order line id in lengow_order_line table
                    $order_line_saved = $this->saveLengowOrderLine($order, $products);
                    LengowMain::log(
                        'Import',
                        'save order lines product : '.$order_line_saved,
                        $this->log_output,
                        $this->marketplace_sku
                    );
                    // if more than one order (different warehouses)
                    LengowMain::log('Import', $success_message, $this->log_output, $this->marketplace_sku);
                }
                // ensure carrier compatibility with SoColissimo & Mondial Relay
                $this->checkCarrierCompatibility($order);
            }
            if ($this->is_reimported
                || ($this->shipped_by_mp && !LengowConfiguration::getGlobalValue('LENGOW_IMPORT_STOCK_SHIP_MP'))
            ) {
                if ($this->is_reimported) {
                    $log_message = 'adding quantity back to stock (order is re-imported)';
                } else {
                    $log_message = 'adding quantity back to stock (order shipped by marketplace)';
                }
                LengowMain::log('Import', $log_message, $this->log_output, $this->marketplace_sku);
                $this->addQuantityBack($products);
            }
            if ($this->is_reimported && !is_null($this->old_order)) {
                $this->old_order->setStateToError();
            }
        } catch (InvalidLengowObjectException $iloe) {
            $error_message = $iloe->getMessage();
        } catch (LengowImportException $lie) {
            $error_message = $lie->getMessage();
        } catch (PrestashopException $pe) {
            $error_message = $pe->getMessage();
        } catch (Exception $e) {
            $error_message = $e->getMessage().'"'.$e->getFile().'|'.$e->getLine();
        }
        if (isset($error_message)) {
            if (isset($cart)) {
                $cart->delete();
            }
            LengowMain::log(
                'Import',
                'order import failed: '.$error_message,
                $this->log_output,
                $this->marketplace_sku
            );
            LengowOrder::addOrderLog($this->id_order_lengow, $error_message, $type = 'import');
            LengowOrder::updateOrderLengow(
                $this->id_order_lengow,
                array(
                    'extra'                 => pSQL(Tools::jsonEncode($this->order_data)),
                    'order_lengow_state'    => pSQL($this->order_state_lengow),
                    'is_reimported'         => 0
                )
            );
            return $this->returnResult('error', $this->id_order_lengow);
        }
        return $this->returnResult('new', $this->id_order_lengow, (int)$order->id);
    }

    /**
     * Return an array of result for each order
     *
     * @param string    $type_result        Type of result (new, update, error)
     * @param integer   $id_order_lengow    ID of the lengow order record
     * @param integer   $order_id           Order ID Prestashop
     *
     * @return array
     */
    protected function returnResult($type_result, $id_order_lengow, $order_id = null)
    {
        $result = array(
            'order_id'              => $order_id,
            'id_order_lengow'       => $id_order_lengow,
            'marketplace_sku'       => $this->marketplace_sku,
            'marketplace_name'      => (string)$this->marketplace->name,
            'lengow_state'          => $this->order_state_lengow,
            'order_new'             => ($type_result == 'new' ? true : false),
            'order_update'          => ($type_result == 'update' ? true : false),
            'order_error'           => ($type_result == 'error' ? true : false)
        );
        return $result;
    }

    /**
     * Check the command and updates data if necessary
     *
     * @param integer $order_id Order ID Prestashop
     *
     * @return boolean
     */
    protected function checkAndUpdateOrder($order_id)
    {
        LengowMain::log(
            'Import',
            'order already imported (ORDER '.$order_id.')',
            $this->log_output,
            $this->marketplace_sku
        );
        $order = new LengowOrder($order_id);
        $result = array('id_order_lengow' => $order->lengow_id);
        // Lengow -> Cancel and reimport order
        if ($order->lengow_is_reimported) {
            LengowMain::log(
                'Import',
                'order is disabled (ORDER '.$order_id.')',
                $this->log_output,
                $this->marketplace_sku
            );
            $this->old_order = $order;
            $this->is_reimported = true;
            return false;
        } else {
            try {
                if ($order->updateState(
                    $this->order_state_lengow,
                    $this->order_data,
                    (count($this->package_data->delivery->trackings) > 0
                        ? $this->package_data->delivery->trackings[0]->number
                        : null
                    )
                )) {
                    $available_states = LengowMain::getOrderStates($this->id_lang);
                    foreach ($available_states as $state) {
                        if ($state['id_order_state'] === LengowMain::getOrderState($this->order_state_lengow)) {
                            $state_name = $state['name'];
                        }
                    }
                    LengowMain::log(
                        'Import',
                        'order\'s state has been updated to "'.$state_name.'"',
                        $this->log_output,
                        $this->marketplace_sku
                    );
                    $result['update'] = true;
                }
                // update lengow order state
                LengowOrder::updateOrderLengow(
                    (int)$order->lengow_id,
                    array('order_lengow_state' => pSQL($this->order_state_lengow))
                );
            } catch (Exception $e) {
                LengowMain::log(
                    'Import',
                    'error while updating state: '.$e->getMessage(),
                    $this->log_output,
                    $this->marketplace_sku
                );
            }
            unset($order);
            return $result;
        }
    }

    /**
     * Checks if order data are present
     *
     * @param mixed     $order_data
     * @param mixed     $package
     *
     * @return boolean
     */
    protected function checkOrderData()
    {
        $error_messages = array();
        if (count($this->package_data->cart) == 0) {
            $error_messages[] = 'no product in the order';
        }
        if (!isset($this->order_data->currency->iso_a3)) {
            $error_messages[] = 'no currency in the order';
        } else {
            $currencyId = Currency::getIdByIsoCode($this->order_data->currency->iso_a3);
            if (!$currencyId) {
                $error_messages[] = 'currency '.$this->order_data->currency->iso_a3.' is not available in your shop';
            }
        }
        if ($this->order_data->total_order == -1) {
            $error_messages[] = 'prices can\'t be calculated in the correct currency';
        }
        if (is_null($this->order_data->billing_address)) {
            $error_messages[] = 'no billing address in the order';
        } elseif (is_null($this->order_data->billing_address->common_country_iso_a2)) {
            $error_messages[] = 'billing address doesn\'t have country';
        }
        if (is_null($this->package_data->delivery->common_country_iso_a2)) {
            $error_messages[] = 'delivery address doesn\'t have country';
        }
        if (count($error_messages) > 0) {
            foreach ($error_messages as $error_message) {
                LengowOrder::addOrderLog($this->id_order_lengow, $error_message, $type = 'import');
                LengowMain::log(
                    'Import',
                    'order import failed: '.$error_message,
                    $this->log_output,
                    $this->marketplace_sku
                );
            };
            return false;
        }
        return true;
    }

    /**
     * Checks if an external id already exists
     *
     * @param array $external_ids
     *
     * @return mixed
     */
    protected function checkExternalIds($external_ids)
    {
        $line_id = false;
        $id_order_prestashop = false;
        if (!is_null($external_ids) && count($external_ids) > 0) {
            foreach ($external_ids as $external_id) {
                $line_id = LengowOrder::getIdFromLengowDeliveryAddress(
                    (int)$external_id,
                    (int)$this->delivery_address_id
                );
                if ($line_id) {
                    $id_order_prestashop = $external_id;
                    break;
                }
            }
        }
        return $id_order_prestashop;
    }

    /**
     * Get order amount
     *
     * @return float
     */
    protected function getOrderAmount()
    {
        $this->processing_fee = (float)$this->order_data->processing_fee;
        $this->shipping_cost = (float)$this->order_data->shipping;
        // rewrite processing fees and shipping cost
        if (!LengowConfiguration::getGlobalValue('LENGOW_IMPORT_PROCESSING_FEE') || $this->first_package == false) {
            $this->processing_fee = 0;
            LengowMain::log(
                'Import',
                'rewrite amount without processing fee',
                $this->log_output,
                $this->marketplace_sku
            );
        }
        if ($this->first_package == false) {
            $this->shipping_cost = 0;
            LengowMain::log(
                'Import',
                'rewrite amount without shipping cost',
                $this->log_output,
                $this->marketplace_sku
            );
        }
        // get total amount and the number of items
        $nb_items = 0;
        $total_amount = 0;
        foreach ($this->package_data->cart as $product) {
            // check whether the product is canceled for amount
            if (!is_null($product->marketplace_status)) {
                $state_product = $this->marketplace->getStateLengow((string)$product->marketplace_status);
                if ($state_product == 'canceled' || $state_product == 'refused') {
                    continue;
                }
            }
            $nb_items += (int)$product->quantity;
            $total_amount += (float)$product->amount;
        }
        $this->order_items = $nb_items;
        $order_amount = $total_amount + $this->processing_fee + $this->shipping_cost;
        return $order_amount;
    }

    /**
     * Get tracking data and update Lengow order record
     *
     * @param mixed $package
     *
     * @return mixed
     */
    protected function loadTrackingData()
    {
        $trackings = $this->package_data->delivery->trackings;
        if (count($trackings) > 0) {
            $this->carrier_name     = (!is_null($trackings[0]->carrier) ? (string)$trackings[0]->carrier : null);
            $this->carrier_method   = (!is_null($trackings[0]->method) ? (string)$trackings[0]->method : null);
            $this->tracking_number  = (!is_null($trackings[0]->number) ? (string)$trackings[0]->number : null);
            $this->shipped_by_mp    = (!is_null($trackings[0]->is_delivered_by_marketplace) ? true : false);
            $this->relay_id         = (!is_null($trackings[0]->relay->id) ? (string)$trackings[0]->relay->id : null);
        }
    }

    /**
     * Get customer name
     *
     * @return string
     */
    protected function getCustomerName()
    {
        $firstname = (string)$this->order_data->billing_address->first_name;
        $lastname = (string)$this->order_data->billing_address->last_name;
        $firstname = Tools::ucfirst(Tools::strtolower($firstname));
        $lastname = Tools::ucfirst(Tools::strtolower($lastname));
        return $firstname.' '.$lastname;
    }

    /**
     * Create or load customer based on API data
     *
     * @param array $customer_data API data
     *
     * @return LengowCustomer
     */
    protected function getCustomer($customer_data = array())
    {
        $customer = new LengowCustomer();
        // check if customer already exists in Prestashop
        $customer->getByEmailAndShop($customer_data['email'], $this->id_shop);
        if ($customer->id) {
            return $customer;
        }
        // create new customer
        $customer->assign($customer_data);
        return $customer;
    }

    /**
     * Create and load cart data
     *
     * @return array
     */
    protected function getCartData()
    {
        $cart_data = array();
        $cart_data['id_lang'] = $this->id_lang;
        $cart_data['id_shop'] = $this->id_shop;
        // get billing datas
        $billing_data = LengowAddress::extractAddressDataFromAPI($this->order_data->billing_address);
        // create customer based on billing data
        if (LengowConfiguration::getGlobalValue('LENGOW_IMPORT_FAKE_EMAIL')
            || $this->preprod_mode
            || empty($billing_data['email'])
        ) {
            $billing_data['email'] = 'generated-email+'.$this->marketplace_sku.'@'.LengowMain::getHost();
            LengowMain::log(
                'Import',
                'generate unique email : '.$billing_data['email'],
                $this->log_output,
                $this->marketplace_sku
            );
        }
        // update Lengow order with customer name
        $customer = $this->getCustomer($billing_data);
        if (!$customer->id) {
            $customer->validateLengow();
        }
        $cart_data['id_customer'] = $customer->id;
        // create addresses from API data
        // billing
        $billing_address = $this->getAddress($billing_data);
        $billing_address->id_customer = $customer->id;
        $billing_address->validateLengow();
        $cart_data['id_address_invoice'] = $billing_address->id;
        // shipping
        $shipping_data = LengowAddress::extractAddressDataFromAPI($this->package_data->delivery);
        $this->shipping_address = $this->getAddress($shipping_data, true);
        $this->shipping_address->id_customer = $customer->id;
        $this->shipping_address->validateLengow();
        // get billing phone numbers if empty in shipping address
        if (empty($this->shipping_address->phone) && !empty($billing_address->phone)) {
            $this->shipping_address->phone = $billing_address->phone;
            $this->shipping_address->update();
        }
        if (empty($this->shipping_address->phone_mobile) && !empty($billing_address->phone_mobile)) {
            $this->shipping_address->phone_mobile = $billing_address->phone_mobile;
            $this->shipping_address->update();
        }
        $cart_data['id_address_delivery'] = $this->shipping_address->id;
        // get currency
        $cart_data['id_currency'] = (int)Currency::getIdByIsoCode((string)$this->order_data->currency->iso_a3);
        // get carrier
        $cart_data['id_carrier'] = $this->getCarrierId($this->shipping_address);
        return $cart_data;
    }

    /**
     * Create and validate order
     *
     * @param $cart
     * @param $products
     *
     * @return
     */
    protected function createAndValidatePayment($cart, $products)
    {
        $id_order_state = LengowMain::getPrestahopStateId(
            $this->order_state_marketplace,
            $this->marketplace,
            $this->shipped_by_mp
        );
        $payment = new LengowPaymentModule();
        $payment->setContext($this->context);
        $payment->active = true;
        $payment_method = (string)$this->order_data->marketplace;
        $message = 'Import Lengow | '."\r\n"
            .'ID order : '.(string)$this->order_data->marketplace_order_id.' | '."\r\n"
            .'Marketplace : '.(string)$this->order_data->marketplace.' | '."\r\n"
            .'Total paid : '.(float)$this->order_amount.' | '."\r\n"
            .'Shipping : '.(float)$this->shipping_cost.' | '."\r\n"
            .'Message : '.(string)$this->order_data->comments."\r\n";
        // validate order
        $order_list = array();
        if (_PS_VERSION_ >= '1.5') {
            $order_list = $payment->makeOrder(
                $cart->id,
                $id_order_state,
                $payment_method,
                $message,
                $products,
                $this->shipping_cost,
                $this->processing_fee,
                $this->tracking_number
            );
        } else {
            $order_list = $payment->makeOrder14(
                $cart->id,
                $id_order_state,
                $this->order_amount,
                $payment_method,
                $message,
                $products,
                (float)$this->shipping_cost,
                (float)$this->processing_fee,
                $this->tracking_number
            );
        }
        return $order_list;
    }

    /**
     * Create or load address based on API data
     *
     * @param array     $address_data   API data
     * @param boolean   $shipping_data
     *
     * @return LengowAddress
     */
    protected function getAddress($address_data = array(), $shipping_data = false)
    {
        $address_data['address_full'] = '';
        // construct field address_full
        $address_data['address_full'] .= !empty($address_data['first_line']) ? $address_data['first_line'].' ' : '';
        $address_data['address_full'] .= !empty($address_data['second_line']) ? $address_data['second_line'].' ' : '';
        $address_data['address_full'] .= !empty($address_data['complement']) ? $address_data['complement'].' ' : '';
        $address_data['address_full'] .= !empty($address_data['zipcode']) ? $address_data['zipcode'].' ' : '';
        $address_data['address_full'] .= !empty($address_data['city']) ? $address_data['city'].' ' : '';
        $address_data['address_full'] .= !empty($address_data['common_country_iso_a2'])
            ? $address_data['common_country_iso_a2'].' '
            : '';
        // if tracking_informations exist => get id_relay
        if ($shipping_data && !is_null($this->relay_id)) {
            $address_data['id_relay'] = $this->relay_id;
        }
        // construct LengowAddress and assign values
        $address = new LengowAddress();
        $address->assign($address_data);
        return $address;
    }

    /**
     * Get products from API data
     *
     * @return array list of products
     */
    protected function getProducts()
    {
        $products = array();
        foreach ($this->package_data->cart as $product) {
            $product_data = LengowProduct::extractProductDataFromAPI($product);
            if (!is_null($product_data['marketplace_status'])) {
                $state_product = $this->marketplace->getStateLengow((string)$product_data['marketplace_status']);
                if ($state_product == 'canceled' || $state_product == 'refused') {
                    LengowMain::log(
                        'Import',
                        'product '.$product_data['merchant_product_id']->id
                        .' could not be added to cart - status: '.$state_product,
                        $this->log_output,
                        $this->marketplace_sku
                    );
                    continue;
                }
            }
            $ids = false;
            $product_ids = array(
                'idMerchant' => (string)$product_data['merchant_product_id']->id,
                'idMP' => (string)$product_data['marketplace_product_id']
            );
            $found = false;
            foreach ($product_ids as $attribute_name => $attribute_value) {
                // remove _FBA from product id
                $attribute_value = preg_replace('/_FBA$/', '', $attribute_value);

                if (empty($attribute_value)) {
                    continue;
                }
                $ids = LengowProduct::matchProduct($attribute_name, $attribute_value, $this->id_shop, $product_ids);
                // no product found in the "classic" way => use advanced search
                if (!$ids) {
                    LengowMain::log(
                        'Import',
                        'product not found with field '.$attribute_name
                        .' ('.$attribute_value.'). Using advanced search.',
                        $this->log_output,
                        $this->marketplace_sku
                    );
                    $ids = LengowProduct::advancedSearch($attribute_value, $this->id_shop, $product_ids);
                }
                // for testing => replace values
//TEMP DATA
//                if (_PS_VERSION_ < '1.6') {
//                    $ids['id_product'] = '1';
//                    $ids['id_product_attribute'] = '27';
//                } else {
//                    $ids['id_product'] = '1';
//                    $ids['id_product_attribute'] = '1';
//                }
                $product_data['amount'] = 10;
                $product_data['price_unit'] = 10;
//TEMP DATA
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
                        'Import',
                        'product id '.$id_full
                        .' found with field '.$attribute_name.' ('.$attribute_value.')',
                        $this->log_output,
                        $this->marketplace_sku
                    );
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                LengowMain::log(
                    'Import',
                    'product '
                    .(!is_null($product_data['merchant_product_id']->id)
                        ? (string)$product_data['merchant_product_id']->id
                        : (string)$product_data['marketplace_product_id']
                    )
                    .' could not be found',
                    $this->log_output,
                    $this->marketplace_sku
                );
            }
        }
        return $products;
    }
 
    /**
     * Get carrier id according to the tracking informations given in the API
     *
     * @param LengowAddress $shipping_address Lengow Address
     *
     * @return integer
     */
    protected function getCarrierId($shipping_address)
    {


        $carrier_id = false;
        if (!LengowConfiguration::getGlobalValue('LENGOW_IMPORT_CARRIER_MP_ENABLED')
            || (is_null($this->carrier_name) && is_null($this->carrier_method))
        ) {
            $carrier_id = (int)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_CARRIER_DEFAULT');
        }
        // get by tracking carrier
        if (!$carrier_id && !is_null($this->carrier_name)) {
            $carrier = Tools::strtolower((string)$this->carrier_name);
            if (!empty($carrier)) {
                $carrier_id = LengowCarrier::matchCarrier(
                    $carrier,
                    $this->marketplace,
                    $this->id_lang,
                    $shipping_address
                );
            }
        }
        // get by tracking method
        if (!$carrier_id && !is_null($this->carrier_method)) {
            $carrier = Tools::strtolower((string)$this->carrier_method);
            if (!empty($carrier)) {
                $carrier_id = LengowCarrier::matchCarrier(
                    $carrier,
                    $this->marketplace,
                    $this->id_lang,
                    $shipping_address
                );
            }
        }
        // assign default carrier if no carrier is found
        if (!$carrier_id) {
            $carrier_id = (int)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_CARRIER_DEFAULT');
            LengowMain::log(
                'Import',
                'no matching carrier found. Default carrier assigned.',
                $this->log_output,
                $this->marketplace_sku
            );
        } else {
            // check if module is active and has not been deleted
            $carrier = new LengowCarrier($carrier_id);
            if (!$carrier->active || $carrier->deleted) {
                LengowMain::log(
                    'Import',
                    'carrier '.$carrier->name.' is inactive or marked as deleted. Default carrier assigned.',
                    $this->log_output,
                    $this->marketplace_sku
                );
                $carrier_id = (int)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_CARRIER_DEFAULT');
            } elseif ($carrier->is_module) {
                if (!LengowMain::isModuleInstalled($carrier->external_module_name)) {
                    $carrier_id = (int)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_CARRIER_DEFAULT');
                    LengowMain::log(
                        'Import',
                        'carrier module '.$carrier->external_module_name.' not installed. Default carrier assigned.',
                        $this->log_output,
                        $this->marketplace_sku
                    );
                }
            }
            // if carrier is SoColissimo -> check if module version is compatible
            if ($carrier_id == Configuration::get('SOCOLISSIMO_CARRIER_ID')) {
                if (!LengowMain::isSoColissimoAvailable()) {
                    $carrier_id = (int)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_CARRIER_DEFAULT');
                    LengowMain::log(
                        'Import',
                        'module version '.$carrier->external_module_name.' not supported. Default carrier assigned.',
                        $this->log_output,
                        $this->marketplace_sku
                    );
                }
            }
            // if carrier is mondialrelay -> check if module version is compatible
            if ($carrier->external_module_name == 'mondialrelay') {
                if (!LengowMain::isMondialRelayAvailable()) {
                    $carrier_id = (int)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_CARRIER_DEFAULT');
                    LengowMain::log(
                        'Import',
                        'module version '.$carrier->external_module_name.' not supported. Default carrier assigned.',
                        $this->log_output,
                        $this->marketplace_sku
                    );
                }
            }
        }
        return $carrier_id;
    }

    /**
     * Ensure carrier compatibility with SoColissimo & Mondial Relay
     *
     * @param LengowOrder $order order imported
     */
    protected function checkCarrierCompatibility($order)
    {
        try {
            $carrier_name = 'none';
            if (!is_null($this->carrier_name)) {
                $carrier_name = $this->carrier_name;
            } elseif (!is_null($this->carrier_method)) {
                $carrier_name = $this->carrier_method;
            }
            $carrier_compatibility = LengowCarrier::carrierCompatibility(
                $order->id_customer,
                $order->id_cart,
                $order->id_carrier,
                $this->shipping_address
            );
            if ($carrier_compatibility < 0) {
                throw new LengowCarrierException('carrier '.$carrier_name.' could not be found in your Prestashop');
            } elseif ($carrier_compatibility > 0) {
                LengowMain::log(
                    'Import',
                    'carrier compatibility ensured with carrier '.$carrier_name,
                    $this->log_output,
                    $this->marketplace_sku
                );
            }
        } catch (LengowCarrierException $lce) {
            LengowMain::log('Import', $lce->getMessage(), $this->log_output, $this->marketplace_sku);
        }
    }

    /**
     * Add a comment to the order
     *
     * @param integer $order_id Order ID Prestashop
     */
    protected function addCommentOrder($order_id)
    {
        $comment = (string)$this->order_data->comments;
        if (!empty($comment)) {
            $msg = new Message();
            $msg->id_order = $order_id;
            $msg->private = 1;
            $msg->message = $comment;
            $msg->add();
        }
    }

    /**
     * Add quantity back to stock
     * @param array     $products   list of products
     * @param integer   $id_shop    shop id
     *
     * @return boolean
     */
    protected function addQuantityBack($products)
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
                    $this->id_shop
                );
            }
        }
    }

    /**
     * Create a order in lengow orders table
     *
     * @return boolean
     */
    protected function createLengowOrder()
    {
        if (!is_null($this->order_data->marketplace_order_date)) {
            $order_date = (string)$this->order_data->marketplace_order_date;
        } else {
            $order_date = (string)$this->order_data->imported_at;
        }

        $params = array(
            'marketplace_sku'       => pSQL($this->marketplace_sku),
            'id_shop'               => $this->id_shop,
            'id_shop_group'         => $this->id_shop_group,
            'id_lang'               => $this->id_lang,
            'marketplace_name'      => pSQL(Tools::strtolower((string)$this->order_data->marketplace)),
            'delivery_address_id'   => $this->delivery_address_id,
            'order_date'            => date('Y-m-d H:i:s', strtotime($order_date)),
            'order_lengow_state'    => pSQL($this->order_state_lengow),
            'date_add'              => date('Y-m-d H:i:s'),
            'order_process_state'   => 0,
            'is_reimported'         => 0,
        );
        if (isset($this->order_data->currency->iso_a3)) {
            $params['currency'] = $this->order_data->currency->iso_a3;
        }
        if (isset($this->order_data->comments) && is_array($this->order_data->comments)) {
            $params['message'] = pSQL(join(',', $this->order_data->comments));
        } else {
            $params['message'] = pSQL((string)$this->order_data->comments);
        }

        $result = Db::getInstance()->autoExecute(
            _DB_PREFIX_.'lengow_orders',
            $params,
            'INSERT'
        );
        if ($result) {
            $this->id_order_lengow = LengowOrder::getIdFromLengowOrders(
                $this->marketplace_sku,
                $this->delivery_address_id
            );
            return true;
        } else {
            return false;
        }
    }

    /**
     * Save order line in lengow orders line table
     *
     * @param LengowOrder $order order imported
     *
     * @return boolean
     */
    protected function saveLengowOrderLine($order, $products)
    {
        $order_line_saved = false;

        foreach ($products as $product_id => $values) {
            $order_line_id =  $values['marketplace_order_line_id'];
            $id_order_detail = LengowOrderDetail::findByOrderIdProductId($order->id, $product_id);
            $result = Db::getInstance()->autoExecute(
                _DB_PREFIX_.'lengow_order_line',
                array(
                    'id_order'          => (int)$order->id,
                    'id_order_line'     => pSQL($order_line_id),
                    'id_order_detail'   => (int)$id_order_detail,
                ),
                'INSERT'
            );
            if ($result) {
                $order_line_saved .= (!$order_line_saved ? $order_line_id : ' / '.$order_line_id);
            }
        }
        return $order_line_saved;
    }
}
