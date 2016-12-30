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
 * @category  Model
 * @package   LengowImportOrder
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
* Lengow Import Order Class
*/
class LengowImportOrder
{
    /**
     * @var integer Prestashop shop id
     */
    protected $idShop = null;

    /**
     * @var integer Prestashop shop group id
     */
    protected $idShopGroup;

    /**
     * @var integer Prestashop lang id
     */
    protected $idLang;

    /**
     * @var Context Prestashop Context for import order
     */
    protected $context;

    /**
     * @var boolean import inactive & out of stock products
     */
    protected $forceProduct = true;

    /**
     * @var boolean use preprod mode
     */
    protected $preprodMode = false;

    /**
     * @var boolean display log messages
     */
    protected $logOutput = false;

    /**
     * @var string id lengow of current order
     */
    protected $marketplaceSku;

    /**
     * @var string marketplace label
     */
    protected $marketplaceLabel;

    /**
     * @var integer id of delivery address for current order
     */
    protected $deliveryAddressId;

    /**
     * @var mixed API order data
     */
    protected $orderData;

    /**
     * @var mixed API package data
     */
    protected $packageData;

    /**
     * @var boolean is first package
     */
    protected $firstPackage;

    /**
     * @var boolean re-import order
     */
    protected $isReimported = false;

    /**
     * @var integer id of the record Lengow order table
     */
    protected $idOrderLengow;

    /**
     * @var LengowMarketplace Lengow marketplace instance
     */
    protected $marketplace;

    /**
     * @var string marketplace order state
     */
    protected $orderStateMarketplace;

    /**
     * @var string Lengow order state
     */
    protected $orderStateLengow;

    /**
     * @var float order processing fee
     */
    protected $processingFee;

    /**
     * @var float order shipping cost
     */
    protected $shippingCost;

    /**
     * @var float order total amount
     */
    protected $orderAmount;

    /**
     * @var integer number of order items
     */
    protected $orderItems;

    /**
     * @var string carrier name
     */
    protected $carrierName = null;

    /**
     * @var string carrier method
     */
    protected $carrierMethod = null;

    /**
     * @var string carrier tracking number
     */
    protected $trackingNumber = null;

    /**
     * @var string carrier relay id
     */
    protected $relayId = null;

    /**
     * @var boolean if order shipped by marketplace
     */
    protected $shippedByMp = false;

    /**
     * @var LengowAddress Lengow Address instance
     */
    protected $shippingAddress;

    /**
     * Construct the import manager
     *
     * @param array params optional options
     *
     * integer  id_shop             Id shop for current order
     * integer  id_shop_group       Id shop group for current order
     * integer  id_lang             Id lang for current order
     * mixed    context             Context for current order
     * boolean  force_product       force import of products
     * boolean  preprod_mode        preprod mode
     * boolean  log_output          display log messages
     * string   marketplace_sku     order marketplace sku
     * integer  delivery_address_id order delivery address id
     * mixed    order_data          order data
     * mixed    package_data        package data
     * boolean  first_package       it is the first package
     */
    public function __construct($params = array())
    {
        $this->idShop            = $params['id_shop'];
        $this->idShopGroup       = $params['id_shop_group'];
        $this->idLang            = $params['id_lang'];
        $this->context           = $params['context'];
        $this->forceProduct      = $params['force_product'];
        $this->preprodMode       = $params['preprod_mode'];
        $this->logOutput         = $params['log_output'];
        $this->marketplaceSku    = $params['marketplace_sku'];
        $this->deliveryAddressId = $params['delivery_address_id'];
        $this->orderData         = $params['order_data'];
        $this->packageData       = $params['package_data'];
        $this->firstPackage      = $params['first_package'];
        // get marketplace and Lengow order state
        $this->marketplace = LengowMain::getMarketplaceSingleton(
            (string)$this->orderData->marketplace,
            $this->idShop
        );
        $this->marketplaceLabel      = $this->marketplace->labelName;
        $this->orderStateMarketplace = (string)$this->orderData->marketplace_status;
        $this->orderStateLengow      = $this->marketplace->getStateLengow($this->orderStateMarketplace);
    }

    /**
     * Create or update order
     *
     * @throws LengowException order list is empty
     *
     * @return array|false
     */
    public function importOrder()
    {
        // if log import exist and not finished
        $importLog = LengowOrder::orderIsInError($this->marketplaceSku, $this->deliveryAddressId, 'import');
        if ($importLog) {
            $decodedMessage = LengowMain::decodeLogMessage($importLog['message'], 'en');
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage(
                    'log.import.error_already_created',
                    array(
                        'decoded_message' => $decodedMessage,
                        'date_message'    => $importLog['date']
                    )
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
            return false;
        }
        // recovery id if the command has already been imported
        $idOrder = LengowOrder::getOrderIdFromLengowOrders(
            $this->marketplaceSku,
            $this->marketplace->name,
            $this->deliveryAddressId,
            $this->marketplace->legacyCode
        );
        // update order state if already imported
        if ($idOrder) {
            $orderUpdated = $this->checkAndUpdateOrder($idOrder);
            if ($orderUpdated && isset($orderUpdated['update'])) {
                return $this->returnResult('update', $orderUpdated['id_order_lengow'], $idOrder);
            }
            if (!$this->isReimported) {
                return false;
            }
        }
        // checks if an external id already exists
        $idOrderPrestashop = $this->checkExternalIds($this->orderData->merchant_order_id);
        if ($idOrderPrestashop && !$this->preprodMode && !$this->isReimported) {
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage(
                    'log.import.external_id_exist',
                    array('order_id' => $idOrderPrestashop)
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
            return false;
        }
        // if order is cancelled or new -> skip
        if (!LengowImport::checkState($this->orderStateMarketplace, $this->marketplace)) {
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage(
                    'log.import.current_order_state_unavailable',
                    array(
                        'order_state_marketplace' => $this->orderStateMarketplace,
                        'marketplace_name'        => $this->marketplace->name
                    )
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
            return false;
        }
        // get a record in the lengow order table
        $this->idOrderLengow = LengowOrder::getIdFromLengowOrders($this->marketplaceSku, $this->deliveryAddressId);
        if (!$this->idOrderLengow) {
            // created a record in the lengow order table
            if (!$this->createLengowOrder()) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage('log.import.lengow_order_not_saved'),
                    $this->logOutput,
                    $this->marketplaceSku
                );
                return false;
            } else {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage('log.import.lengow_order_saved'),
                    $this->logOutput,
                    $this->marketplaceSku
                );
            }
        }
        // checks if the required order data is present
        if (!$this->checkOrderData()) {
            return $this->returnResult('error', $this->idOrderLengow);
        }
        // get order amount and load processing fees and shipping cost
        $this->orderAmount = $this->getOrderAmount();
        // load tracking data
        $this->loadTrackingData();
        // get customer name
        $customerName = $this->getCustomerName();
        $customerEmail = (!is_null($this->orderData->billing_address->email)
            ? (string)$this->orderData->billing_address->email
            : (string)$this->packageData->delivery->email
        );
        // update Lengow order with new informations
        LengowOrder::updateOrderLengow(
            $this->idOrderLengow,
            array(
                'total_paid'           => $this->orderAmount,
                'order_item'           => $this->orderItems,
                'customer_name'        => pSQL($customerName),
                'customer_email'       => pSQL($customerEmail),
                'carrier'              => pSQL($this->carrierName),
                'method'               => pSQL($this->carrierMethod),
                'tracking'             => pSQL($this->trackingNumber),
                'id_relay'             => pSQL($this->relayId),
                'sent_marketplace'     => (int)$this->shippedByMp,
                'delivery_country_iso' => pSQL((string)$this->packageData->delivery->common_country_iso_a2),
                'order_lengow_state'   => pSQL($this->orderStateLengow)
            )
        );
        // try to import order
        try {
            // check if the order is shipped by marketplace
            if ($this->shippedByMp) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage(
                        'log.import.order_shipped_by_marketplace',
                        array('marketplace_name' => $this->marketplace->name)
                    ),
                    $this->logOutput,
                    $this->marketplaceSku
                );
                if (!LengowConfiguration::getGlobalValue('LENGOW_IMPORT_SHIP_MP_ENABLED')) {
                    LengowOrder::updateOrderLengow(
                        $this->idOrderLengow,
                        array(
                            'order_process_state' => 2,
                            'extra'               => pSQL(Tools::jsonEncode($this->orderData))
                        )
                    );
                    return false;
                }
            }
            // get products
            $products = $this->getProducts();
            // create a cart with customer, billing address and shipping address
            $cartDatas = $this->getCartData();
            if (_PS_VERSION_ < '1.5') {
                $cart = new LengowCart($this->context->cart->id);
            } else {
                $cart = new LengowCart();
            }
            $cart->assign($cartDatas);
            $cart->validateLengow();
            $cart->force_product = $this->forceProduct;
            // add products to cart
            $cart->addProducts($products);
            // Clean cart products
            $cart->cleanCart($products);
            // add cart to context
            $this->context->cart = $cart;
            // create payment
            $orderLists = $this->createAndValidatePayment($cart, $products);
            // if no order in list
            if (empty($orderLists)) {
                throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.order_list_is_empty'));
            } else {
                foreach ($orderLists as $order) {
                    // add order comment from marketplace to prestashop order
                    if (_PS_VERSION_ >= '1.5') {
                        $this->addCommentOrder((int)$order->id, $this->orderData->comments);
                    }
                    $successMessage = LengowMain::setLogMessage(
                        'log.import.order_successfully_imported',
                        array('order_id' => $order->id)
                    );
                    $success = LengowOrder::updateOrderLengow(
                        $this->idOrderLengow,
                        array(
                            'id_order'            => (int)$order->id,
                            'order_process_state' => LengowOrder::getOrderProcessState($this->orderStateLengow),
                            'extra'               => pSQL(Tools::jsonEncode($this->orderData)),
                            'order_lengow_state'  => pSQL($this->orderStateLengow),
                            'is_reimported'       => 0
                        )
                    );
                    if (!$success) {
                        LengowMain::log(
                            'Import',
                            LengowMain::setLogMessage('log.import.lengow_order_not_updated'),
                            $this->logOutput,
                            $this->marketplaceSku
                        );
                    } else {
                        LengowMain::log(
                            'Import',
                            LengowMain::setLogMessage('log.import.lengow_order_updated'),
                            $this->logOutput,
                            $this->marketplaceSku
                        );
                    }
                    // Save order line id in lengow_order_line table
                    $orderLineSaved = $this->saveLengowOrderLine($order, $products);
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage(
                            'log.import.lengow_order_line_saved',
                            array('order_line_saved' => $orderLineSaved)
                        ),
                        $this->logOutput,
                        $this->marketplaceSku
                    );
                    // if more than one order (different warehouses)
                    LengowMain::log('Import', $successMessage, $this->logOutput, $this->marketplaceSku);
                }
                // ensure carrier compatibility with SoColissimo & Mondial Relay
                $this->checkCarrierCompatibility($order);
            }
            // add quantity back for re-import order and order shipped by marketplace
            if ($this->isReimported
                || ($this->shippedByMp && !LengowConfiguration::getGlobalValue('LENGOW_IMPORT_STOCK_SHIP_MP'))
            ) {
                if ($this->isReimported) {
                    $logMessage = LengowMain::setLogMessage('log.import.quantity_back_reimported_order');
                } else {
                    $logMessage = LengowMain::setLogMessage('log.import.quantity_back_shipped_by_marketplace');
                }
                LengowMain::log('Import', $logMessage, $this->logOutput, $this->marketplaceSku);
                $this->addQuantityBack($products);
            }
        } catch (LengowException $e) {
            $errorMessage = $e->getMessage();
        } catch (Exception $e) {
            $errorMessage = '[Prestashop error] "'.$e->getMessage().'" '.$e->getFile().' | '.$e->getLine();
        }
        if (isset($errorMessage)) {
            if (isset($cart)) {
                $cart->delete();
            }
            LengowOrder::addOrderLog($this->idOrderLengow, $errorMessage, 'import');
            $decodedMessage = LengowMain::decodeLogMessage($errorMessage, 'en');
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage(
                    'log.import.order_import_failed',
                    array('decoded_message' => $decodedMessage)
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
            LengowOrder::updateOrderLengow(
                $this->idOrderLengow,
                array(
                    'extra'              => pSQL(Tools::jsonEncode($this->orderData)),
                    'order_lengow_state' => pSQL($this->orderStateLengow),
                    'is_reimported'      => 0
                )
            );
            return $this->returnResult('error', $this->idOrderLengow);
        }
        return $this->returnResult('new', $this->idOrderLengow, (int)$order->id);
    }

    /**
     * Return an array of result for each order
     *
     * @param string  $typeResult    Type of result (new, update, error)
     * @param integer $idOrderLengow Lengow order id
     * @param integer $idOrder       Prestashop order id
     *
     * @return array
     */
    protected function returnResult($typeResult, $idOrderLengow, $idOrder = null)
    {
        $result = array(
            'order_id'         => $idOrder,
            'id_order_lengow'  => $idOrderLengow,
            'marketplace_sku'  => $this->marketplaceSku,
            'marketplace_name' => (string)$this->marketplace->name,
            'lengow_state'     => $this->orderStateLengow,
            'order_new'        => ($typeResult == 'new' ? true : false),
            'order_update'     => ($typeResult == 'update' ? true : false),
            'order_error'      => ($typeResult == 'error' ? true : false)
        );
        return $result;
    }

    /**
     * Check the command and updates data if necessary
     *
     * @param integer $idOrder Prestashop order id
     *
     * @return boolean
     */
    protected function checkAndUpdateOrder($idOrder)
    {
        LengowMain::log(
            'Import',
            LengowMain::setLogMessage('log.import.order_already_imported', array('order_id' => $idOrder)),
            $this->logOutput,
            $this->marketplaceSku
        );
        $order = new LengowOrder($idOrder);
        $result = array('id_order_lengow' => $order->lengowId);
        // Lengow -> Cancel and reimport order
        if ($order->lengowIsReimported) {
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage('log.import.order_ready_to_reimport', array('order_id' => $idOrder)),
                $this->logOutput,
                $this->marketplaceSku
            );
            $this->isReimported = true;
            return false;
        } else {
            try {
                $orderUpdated = $order->updateState($this->orderStateLengow, $this->orderData, $this->packageData);
                if ($orderUpdated) {
                    $result['update'] = true;
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage('log.import.state_updated_to', array('state_name' => $orderUpdated)),
                        $this->logOutput,
                        $this->marketplaceSku
                    );
                    $stateName = '';
                    $availableStates = LengowMain::getOrderStates($this->idLang);
                    foreach ($availableStates as $state) {
                        if ($state['id_order_state'] == LengowMain::getOrderState($this->orderStateLengow)) {
                            $stateName = $state['name'];
                            break;
                        }
                    }
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage('log.import.order_state_updated', array('state_name' => $stateName)),
                        $this->logOutput,
                        $this->marketplaceSku
                    );
                }
            } catch (Exception $e) {
                $errorMessage = $e->getMessage().'"'.$e->getFile().'|'.$e->getLine();
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage(
                        'log.import.error_order_state_updated',
                        array('error_message' => $errorMessage)
                    ),
                    $this->logOutput,
                    $this->marketplaceSku
                );
            }
            unset($order);
            return $result;
        }
    }

    /**
     * Checks if order data are present
     *
     * @return boolean
     */
    protected function checkOrderData()
    {
        $errorMessages = array();
        if (count($this->packageData->cart) == 0) {
            $errorMessages[] = LengowMain::setLogMessage('lengow_log.error.no_product');
        }
        if (!isset($this->orderData->currency->iso_a3)) {
            $errorMessages[] = LengowMain::setLogMessage('lengow_log.error.no_currency');
        } else {
            $currencyId = Currency::getIdByIsoCode($this->orderData->currency->iso_a3);
            if (!$currencyId) {
                $errorMessages[] = LengowMain::setLogMessage(
                    'lengow_log.error.currency_not_available',
                    array('currency_iso' => $this->orderData->currency->iso_a3)
                );
            }
        }
        if ($this->orderData->total_order == -1) {
            $errorMessages[] = LengowMain::setLogMessage('lengow_log.error.no_change_rate');
        }
        if (is_null($this->orderData->billing_address)) {
            $errorMessages[] = LengowMain::setLogMessage('lengow_log.error.no_billing_address');
        } elseif (is_null($this->orderData->billing_address->common_country_iso_a2)) {
            $errorMessages[] = LengowMain::setLogMessage('lengow_log.error.no_country_for_billing_address');
        }
        if (is_null($this->packageData->delivery->common_country_iso_a2)) {
            $errorMessages[] = LengowMain::setLogMessage('lengow_log.error.no_country_for_delivery_address');
        }
        if (count($errorMessages) > 0) {
            foreach ($errorMessages as $errorMessage) {
                LengowOrder::addOrderLog($this->idOrderLengow, $errorMessage, 'import');
                $decodedMessage = LengowMain::decodeLogMessage($errorMessage, 'en');
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage(
                        'log.import.order_import_failed',
                        array('decoded_message' => $decodedMessage)
                    ),
                    $this->logOutput,
                    $this->marketplaceSku
                );
            };
            return false;
        }
        return true;
    }

    /**
     * Checks if an external id already exists
     *
     * @param array $externalIds external ids return by API
     *
     * @return integer|false
     */
    protected function checkExternalIds($externalIds)
    {
        $lineId = false;
        $idOrderPrestashop = false;
        if (!is_null($externalIds) && count($externalIds) > 0) {
            foreach ($externalIds as $externalId) {
                $lineId = LengowOrder::getIdFromLengowDeliveryAddress(
                    (int)$externalId,
                    (int)$this->deliveryAddressId
                );
                if ($lineId) {
                    $idOrderPrestashop = $externalId;
                    break;
                }
            }
        }
        return $idOrderPrestashop;
    }

    /**
     * Get order amount
     *
     * @return float
     */
    protected function getOrderAmount()
    {
        $this->processingFee = (float)$this->orderData->processing_fee;
        $this->shippingCost = (float)$this->orderData->shipping;
        // rewrite processing fees and shipping cost
        if (!LengowConfiguration::getGlobalValue('LENGOW_IMPORT_PROCESSING_FEE') || $this->firstPackage == false) {
            $this->processingFee = 0;
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage('log.import.rewrite_processing_fee'),
                $this->logOutput,
                $this->marketplaceSku
            );
        }
        if ($this->firstPackage == false) {
            $this->shippingCost = 0;
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage('log.import.rewrite_shipping_cost'),
                $this->logOutput,
                $this->marketplaceSku
            );
        }
        // get total amount and the number of items
        $nbItems = 0;
        $totalAmount = 0;
        foreach ($this->packageData->cart as $product) {
            // check whether the product is canceled for amount
            if (!is_null($product->marketplace_status)) {
                $stateProduct = $this->marketplace->getStateLengow((string)$product->marketplace_status);
                if ($stateProduct == 'canceled' || $stateProduct == 'refused') {
                    continue;
                }
            }
            $nbItems += (int)$product->quantity;
            $totalAmount += (float)$product->amount;
        }
        $this->orderItems = $nbItems;
        $orderAmount = $totalAmount + $this->processingFee + $this->shippingCost;
        return $orderAmount;
    }

    /**
     * Get tracking data and update Lengow order record
     */
    protected function loadTrackingData()
    {
        $trackings = $this->packageData->delivery->trackings;
        if (count($trackings) > 0) {
            $this->carrierName    = (!is_null($trackings[0]->carrier) ? (string)$trackings[0]->carrier : null);
            $this->carrierMethod  = (!is_null($trackings[0]->method) ? (string)$trackings[0]->method : null);
            $this->trackingNumber = (!is_null($trackings[0]->number) ? (string)$trackings[0]->number : null);
            $this->relayId        = (!is_null($trackings[0]->relay->id) ? (string)$trackings[0]->relay->id : null);
            if (!is_null($trackings[0]->is_delivered_by_marketplace) && $trackings[0]->is_delivered_by_marketplace) {
                $this->shippedByMp = true;
            }
        }
    }

    /**
     * Get customer name
     *
     * @return string
     */
    protected function getCustomerName()
    {
        $firstname = (string)$this->orderData->billing_address->first_name;
        $lastname = (string)$this->orderData->billing_address->last_name;
        $firstname = Tools::ucfirst(Tools::strtolower($firstname));
        $lastname = Tools::ucfirst(Tools::strtolower($lastname));
        if (empty($firstname) && empty($lastname)) {
            return (string)$this->orderData->billing_address->full_name;
        } else {
            return $firstname.' '.$lastname;
        }
    }

    /**
     * Create or load customer based on API data
     *
     * @param array $customerDatas API data
     *
     * @return LengowCustomer
     */
    protected function getCustomer($customerDatas = array())
    {
        $customer = new LengowCustomer();
        // check if customer already exists in Prestashop
        $customer->getByEmailAndShop($customerDatas['email'], $this->idShop);
        if ($customer->id) {
            return $customer;
        }
        // create new customer
        $customer->assign($customerDatas);
        return $customer;
    }

    /**
     * Create and load cart data
     *
     * @return array
     */
    protected function getCartData()
    {
        $cartDatas = array();
        $cartDatas['id_lang'] = $this->idLang;
        $cartDatas['id_shop'] = $this->idShop;
        // get billing datas
        $billingDatas = LengowAddress::extractAddressDataFromAPI($this->orderData->billing_address);
        // create customer based on billing data
        // generation of fictitious email
        $domain = !LengowMain::getHost() ? 'prestashop.shop' : LengowMain::getHost();
        $billingDatas['email'] = $this->marketplaceSku.'-'.$this->marketplace->name.'@'.$domain;
        LengowMain::log(
            'Import',
            LengowMain::setLogMessage('log.import.generate_unique_email', array('email' => $billingDatas['email'])),
            $this->logOutput,
            $this->marketplaceSku
        );
        // update Lengow order with customer name
        $customer = $this->getCustomer($billingDatas);
        if (!$customer->id) {
            $customer->validateLengow();
        }
        $cartDatas['id_customer'] = $customer->id;
        // create addresses from API data
        // billing
        $billingAddress = $this->getAddress($customer->id, $billingDatas);
        if (!$billingAddress->id) {
            $billingAddress->id_customer = $customer->id;
            $billingAddress->validateLengow();
        }
        $cartDatas['id_address_invoice'] = $billingAddress->id;
        // shipping
        $shippingDatas = LengowAddress::extractAddressDataFromAPI($this->packageData->delivery);
        $this->shippingAddress = $this->getAddress($customer->id, $shippingDatas, true);
        if (!$this->shippingAddress->id) {
            $this->shippingAddress->id_customer = $customer->id;
            $this->shippingAddress->validateLengow();
        }
        // get billing phone numbers if empty in shipping address
        if (empty($this->shippingAddress->phone) && !empty($billingAddress->phone)) {
            $this->shippingAddress->phone = $billingAddress->phone;
            $this->shippingAddress->update();
        }
        if (empty($this->shippingAddress->phone_mobile) && !empty($billingAddress->phone_mobile)) {
            $this->shippingAddress->phone_mobile = $billingAddress->phone_mobile;
            $this->shippingAddress->update();
        }
        $cartDatas['id_address_delivery'] = $this->shippingAddress->id;
        // get currency
        $cartDatas['id_currency'] = (int)Currency::getIdByIsoCode((string)$this->orderData->currency->iso_a3);
        // get carrier
        $cartDatas['id_carrier'] = $this->getCarrierId();
        return $cartDatas;
    }

    /**
     * Create and validate order
     *
     * @param LengowCart $cart     Lengow cart instance
     * @param array      $products List of Lengow products
     *
     * @return
     */
    protected function createAndValidatePayment($cart, $products)
    {
        $idOrderState = LengowMain::getPrestahopStateId(
            $this->orderStateMarketplace,
            $this->marketplace,
            $this->shippedByMp
        );
        $payment = new LengowPaymentModule();
        $payment->setContext($this->context);
        $payment->active = true;
        $paymentMethod = (string)$this->orderData->marketplace;
        $message = 'Import Lengow | '."\r\n"
            .'ID order : '.(string)$this->orderData->marketplace_order_id.' | '."\r\n"
            .'Marketplace : '.(string)$this->orderData->marketplace.' | '."\r\n"
            .'Total paid : '.(float)$this->orderAmount.' | '."\r\n"
            .'Shipping : '.(float)$this->shippingCost.' | '."\r\n"
            .'Message : '.(string)$this->orderData->comments."\r\n";
        // validate order
        $orderLists = array();
        if (_PS_VERSION_ >= '1.5') {
            $orderLists = $payment->makeOrder(
                $cart->id,
                $idOrderState,
                $paymentMethod,
                $message,
                $products,
                $this->shippingCost,
                $this->processingFee,
                $this->trackingNumber
            );
        } else {
            $orderLists = $payment->makeOrder14(
                $cart->id,
                $idOrderState,
                $this->orderAmount,
                $paymentMethod,
                $message,
                $products,
                (float)$this->shippingCost,
                (float)$this->processingFee,
                $this->trackingNumber
            );
        }
        return $orderLists;
    }

    /**
     * Create or load address based on API data
     *
     * @param integer $idCustomer   Prestashop customer id
     * @param array   $addressDatas address datas
     * @param boolean $shippingData is shipping address 
     *
     * @return LengowAddress
     */
    protected function getAddress($idCustomer, $addressDatas = array(), $shippingData = false)
    {
        // if tracking_informations exist => get id_relay
        if ($shippingData && !is_null($this->relayId)) {
            $addressDatas['id_relay'] = $this->relayId;
        }
        $addressDatas['address_full'] = '';
        // construct field address_full
        $addressDatas['address_full'] .= !empty($addressDatas['first_line']) ? $addressDatas['first_line'].' ' : '';
        $addressDatas['address_full'] .= !empty($addressDatas['second_line']) ? $addressDatas['second_line'].' ' : '';
        $addressDatas['address_full'] .= !empty($addressDatas['complement']) ? $addressDatas['complement'].' ' : '';
        $addressDatas['address_full'] .= !empty($addressDatas['zipcode']) ? $addressDatas['zipcode'].' ' : '';
        $addressDatas['address_full'] .= !empty($addressDatas['city']) ? $addressDatas['city'].' ' : '';
        $addressDatas['address_full'] .= !empty($addressDatas['common_country_iso_a2'])
            ? $addressDatas['common_country_iso_a2'].' '
            : '';
        $address = LengowAddress::getByHash($addressDatas['address_full']);
        // if address exists => check if names are the same
        if ($address) {
            if ($address->id_customer == $idCustomer
                && $address->lastname == $addressDatas['last_name']
                && $address->firstname == $addressDatas['first_name']
            ) {
                if (isset($addressDatas['id_relay'])) {
                    $address->idRelay = $addressDatas['id_relay'];
                }
                return $address;
            }
        }
        // construct LengowAddress and assign values
        $address = new LengowAddress();
        $address->assign($addressDatas);
        return $address;
    }

    /**
     * Get products from API data
     *
     * @throws LengowException product is a parent / product no be found
     *
     * @return array list of products
     */
    protected function getProducts()
    {
        $products = array();
        foreach ($this->packageData->cart as $product) {
            $productDatas = LengowProduct::extractProductDataFromAPI($product);
            if (!is_null($productDatas['marketplace_status'])) {
                $stateProduct = $this->marketplace->getStateLengow((string)$productDatas['marketplace_status']);
                if ($stateProduct == 'canceled' || $stateProduct == 'refused') {
                    $idProduct = (!is_null($productDatas['merchant_product_id']->id)
                        ? (string)$productDatas['merchant_product_id']->id
                        : (string)$productDatas['marketplace_product_id']
                    );
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage(
                            'log.import.product_state_canceled',
                            array(
                                'product_id'    => $idProduct,
                                'state_product' => $stateProduct
                            )
                        ),
                        $this->logOutput,
                        $this->marketplaceSku
                    );
                    continue;
                }
            }
            $ids = false;
            $idsProduct = array(
                'idMerchant' => (string)$productDatas['merchant_product_id']->id,
                'idMP' => (string)$productDatas['marketplace_product_id']
            );
            $found = false;
            foreach ($idsProduct as $attributeName => $attributeValue) {
                // remove _FBA from product id
                $attributeValue = preg_replace('/_FBA$/', '', $attributeValue);
                if (empty($attributeValue)) {
                    continue;
                }
                $ids = LengowProduct::matchProduct($attributeName, $attributeValue, $this->idShop, $idsProduct);
                // no product found in the "classic" way => use advanced search
                if (!$ids) {
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage(
                            'log.import.product_advanced_search',
                            array(
                                'attribute_name'  => $attributeName,
                                'attribute_value' => $attributeValue
                            )
                        ),
                        $this->logOutput,
                        $this->marketplaceSku
                    );
                    $ids = LengowProduct::advancedSearch($attributeValue, $this->idShop, $idsProduct);
                }
                // for testing => replace values
                // $ids['id_product'] = '1';
                // $ids['id_product_attribute'] = '1';
                if (!empty($ids)) {
                    $idFull = $ids['id_product'];
                    if (!isset($ids['id_product_attribute'])) {
                        $p = new Product($ids['id_product']);
                        if ($p->hasAttributes()) {
                            throw new LengowException(
                                LengowMain::setLogMessage(
                                    'lengow_log.exception.product_is_a_parent',
                                    array('product_id' => $p->id)
                                )
                            );
                        }
                    }
                    $idFull .= isset($ids['id_product_attribute']) ? '_'.$ids['id_product_attribute'] : '';
                    if (array_key_exists($idFull, $products)) {
                        $products[$idFull]['quantity'] += (integer)$productDatas['quantity'];
                        $products[$idFull]['amount'] += (float)$productDatas['amount'];
                    } else {
                        $products[$idFull] = $productDatas;
                    }
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage(
                            'log.import.product_be_found',
                            array(
                                'id_full'         => $idFull,
                                'attribute_name'  => $attributeName,
                                'attribute_value' => $attributeValue
                            )
                        ),
                        $this->logOutput,
                        $this->marketplaceSku
                    );
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $idProduct = (!is_null($productDatas['merchant_product_id']->id)
                    ? (string)$productDatas['merchant_product_id']->id
                    : (string)$productDatas['marketplace_product_id']
                );
                $errorMessage = LengowMain::setLogMessage(
                    'lengow_log.exception.product_not_be_found',
                    array('product_id' => $idProduct)
                );
                LengowMain::log('Import', $errorMessage, $this->logOutput, $this->marketplaceSku);
                throw new LengowException($errorMessage);
            }
        }
        return $products;
    }
 
    /**
     * Get carrier id according to the tracking informations given in the API
     *
     * @throws LengowException shipping country no country / no default carrier for country
     *
     * @return integer
     */
    protected function getCarrierId()
    {
        $carrier = false;
        if (!isset($this->shippingAddress->id_country)) {
            throw new LengowException(
                LengowMain::setLogMessage('lengow_log.exception.carrier_shipping_address_no_country')
            );
        }
        $orderIdCountry = $this->shippingAddress->id_country;
        if ((int)$orderIdCountry == 0) {
            throw new LengowException(
                LengowMain::setLogMessage('lengow_log.exception.carrier_shipping_address_no_country')
            );
        }
        // get carrier id by carrier code
        $carrierName = is_null($this->relayId) ? $this->carrierName : $this->carrierName.'_RELAY';
        $idCarrier = LengowCarrier::getIdCarrierByMarketplaceCarrierSku($carrierName, $orderIdCountry);
        if ($idCarrier && $idCarrier > 0) {
            $carrier = new Carrier($idCarrier);
        }
        if (!$carrier) {
            // get default carrier by country
            $carrier = LengowCarrier::getDefaultCarrier($orderIdCountry);
            if (!$carrier) {
                $countryName = Country::getNameById($this->context->language->id, $orderIdCountry);
                throw new LengowException(
                    LengowMain::setLogMessage(
                        'lengow_log.exception.no_default_carrier_for_country',
                        array('country_name' => $countryName)
                    )
                );
            }
        }
        return $carrier->id;
    }

    /**
     * Ensure carrier compatibility with SoColissimo & Mondial Relay
     *
     * @param LengowOrder $order order imported
     */
    protected function checkCarrierCompatibility($order)
    {
        try {
            $carrierName = 'none';
            if (!is_null($this->carrierName)) {
                $carrierName = $this->carrierName;
            } elseif (!is_null($this->carrierMethod)) {
                $carrierName = $this->carrierMethod;
            }
            $carrierCompatibility = LengowCarrier::carrierCompatibility(
                $order->id_customer,
                $order->id_cart,
                $order->id_carrier,
                $this->shippingAddress
            );
            if ($carrierCompatibility < 0) {
                throw new LengowException(
                    LengowMain::setLogMessage(
                        'log.import.error_carrier_not_found',
                        array('carrier_name' => $carrierName)
                    )
                );
            } elseif ($carrierCompatibility > 0) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage(
                        'log.import.carrier_compatibility_ensured',
                        array('carrier_name' => $carrierName)
                    ),
                    $this->logOutput,
                    $this->marketplaceSku
                );
            }
        } catch (LengowException $e) {
            LengowMain::log('Import', $e->getMessage(), $this->logOutput, $this->marketplaceSku);
        }
    }

    /**
     * Add a comment to the order
     *
     * @param integer $idOrder Prestashop order id
     * @param string  $comment order comment
     */
    protected function addCommentOrder($idOrder, $comment)
    {
        if (!empty($comment) && !is_null($comment)) {
            $msg = new Message();
            $msg->id_order = $idOrder;
            $msg->private = 1;
            $msg->message = $comment;
            $msg->add();
        }
    }

    /**
     * Add quantity back to stock
     *
     * @param array $products list of products
     *
     * @return boolean
     */
    protected function addQuantityBack($products)
    {
        foreach ($products as $sku => $product) {
            $idsProduct = explode('_', $sku);
            $idProductAttribute = isset($idsProduct[1]) ? $idsProduct[1] : null;
            if (_PS_VERSION_ < '1.5') {
                $p = new Product($idsProduct[0]);
                return $p->addStockMvt($product['quantity'], (int)_STOCK_MOVEMENT_ORDER_REASON_, $idProductAttribute);
            } else {
                StockAvailable::updateQuantity(
                    (int)$idsProduct[0],
                    $idProductAttribute,
                    $product['quantity'],
                    $this->idShop
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
        if (!is_null($this->orderData->marketplace_order_date)) {
            $orderDate = (string)$this->orderData->marketplace_order_date;
        } else {
            $orderDate = (string)$this->orderData->imported_at;
        }
        $params = array(
            'marketplace_sku'     => pSQL($this->marketplaceSku),
            'id_shop'             => (int)$this->idShop,
            'id_shop_group'       => (int)$this->idShopGroup,
            'id_lang'             => (int)$this->idLang,
            'marketplace_name'    => pSQL(Tools::strtolower((string)$this->orderData->marketplace)),
            'marketplace_label'   => pSQL((string)$this->marketplaceLabel),
            'delivery_address_id' => (int)$this->deliveryAddressId,
            'order_date'          => date('Y-m-d H:i:s', strtotime($orderDate)),
            'order_lengow_state'  => pSQL($this->orderStateLengow),
            'date_add'            => date('Y-m-d H:i:s'),
            'order_process_state' => 0,
            'is_reimported'       => 0,
        );
        if (isset($this->orderData->currency->iso_a3)) {
            $params['currency'] = $this->orderData->currency->iso_a3;
        }
        if (isset($this->orderData->comments) && is_array($this->orderData->comments)) {
            $params['message'] = pSQL(join(',', $this->orderData->comments));
        } else {
            $params['message'] = pSQL((string)$this->orderData->comments);
        }
        if (_PS_VERSION_ < '1.5') {
            $result = Db::getInstance()->autoExecute(
                _DB_PREFIX_.'lengow_orders',
                $params,
                'INSERT'
            );
        } else {
            $result = Db::getInstance()->insert(
                'lengow_orders',
                $params
            );
        }
        if ($result) {
            $this->idOrderLengow = LengowOrder::getIdFromLengowOrders(
                $this->marketplaceSku,
                $this->deliveryAddressId
            );
            return true;
        } else {
            return false;
        }
    }

    /**
     * Save order line in lengow orders line table
     *
     * @param LengowOrder $order    Lengow order instance
     * @param array       $products order products
     *
     * @return boolean
     */
    protected function saveLengowOrderLine($order, $products)
    {
        $orderLineSaved = false;
        foreach ($products as $idProduct => $values) {
            $idOrderLine = $values['marketplace_order_line_id'];
            $idOrderDetail = LengowOrderDetail::findByOrderIdProductId($order->id, $idProduct);
            if (_PS_VERSION_ < '1.5') {
                $result = Db::getInstance()->autoExecute(
                    _DB_PREFIX_.'lengow_order_line',
                    array(
                        'id_order'        => (int)$order->id,
                        'id_order_line'   => pSQL($idOrderLine),
                        'id_order_detail' => (int)$idOrderDetail,
                    ),
                    'INSERT'
                );
            } else {
                $result = Db::getInstance()->insert(
                    'lengow_order_line',
                    array(
                        'id_order'        => (int)$order->id,
                        'id_order_line'   => pSQL($idOrderLine),
                        'id_order_detail' => (int)$idOrderDetail,
                    )
                );
            }
            if ($result) {
                $orderLineSaved .= (!$orderLineSaved ? $idOrderLine : ' / '.$idOrderLine);
            }
        }
        return $orderLineSaved;
    }
}
