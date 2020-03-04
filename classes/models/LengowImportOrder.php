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
 * Lengow Import Order Class
 */
class LengowImportOrder
{
    /**
     * @var string result for order imported
     */
    const RESULT_NEW = 'new';

    /**
     * @var string result for order updated
     */
    const RESULT_UPDATE = 'update';

    /**
     * @var string result for order in error
     */
    const RESULT_ERROR = 'error';

    /**
     * @var integer|null Prestashop shop id
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
     * @var boolean use debug mode
     */
    protected $debugMode = false;

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
     * @var boolean import one order var from lengow import
     */
    protected $importOneOrder;

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
     * @var string|null carrier name
     */
    protected $carrierName = null;

    /**
     * @var string|null carrier method
     */
    protected $carrierMethod = null;

    /**
     * @var string|null carrier tracking number
     */
    protected $trackingNumber = null;

    /**
     * @var string|null carrier relay id
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
     * @var string Marketplace order comment
     */
    protected $orderComment;

    /**
     * Construct the import manager
     *
     * @param array $params optional options
     *
     * integer  id_shop             Id shop for current order
     * integer  id_shop_group       Id shop group for current order
     * integer  id_lang             Id lang for current order
     * mixed    context             Context for current order
     * boolean  force_product       force import of products
     * boolean  debug_mode          debug mode
     * boolean  log_output          display log messages
     * string   marketplace_sku     order marketplace sku
     * integer  delivery_address_id order delivery address id
     * mixed    order_data          order data
     * mixed    package_data        package data
     * boolean  first_package       it is the first package
     *
     * @throws LengowException
     */
    public function __construct($params = array())
    {
        $this->idShop = $params['id_shop'];
        $this->idShopGroup = $params['id_shop_group'];
        $this->idLang = $params['id_lang'];
        $this->context = $params['context'];
        $this->forceProduct = $params['force_product'];
        $this->debugMode = $params['debug_mode'];
        $this->logOutput = $params['log_output'];
        $this->marketplaceSku = $params['marketplace_sku'];
        $this->deliveryAddressId = $params['delivery_address_id'];
        $this->orderData = $params['order_data'];
        $this->packageData = $params['package_data'];
        $this->firstPackage = $params['first_package'];
        $this->importOneOrder = $params['import_one_order'];
        // get marketplace and Lengow order state
        $this->marketplace = LengowMain::getMarketplaceSingleton((string)$this->orderData->marketplace);
        $this->marketplaceLabel = $this->marketplace->labelName;
        $this->orderStateMarketplace = (string)$this->orderData->marketplace_status;
        $this->orderStateLengow = $this->marketplace->getStateLengow($this->orderStateMarketplace);
    }

    /**
     * Create or update order
     *
     * @throws Exception|LengowException
     *
     * @return array|false
     */
    public function importOrder()
    {
        // if log import exist and not finished
        $importLog = LengowOrder::orderIsInError($this->marketplaceSku, $this->deliveryAddressId, 'import');
        if ($importLog) {
            $decodedMessage = LengowMain::decodeLogMessage($importLog['message'], LengowTranslation::DEFAULT_ISO_CODE);
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.error_already_created',
                    array(
                        'decoded_message' => $decodedMessage,
                        'date_message' => $importLog['date'],
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
                return $this->returnResult(self::RESULT_UPDATE, $orderUpdated['id_order_lengow'], $idOrder);
            }
            if (!$this->isReimported) {
                return false;
            }
        }
        if (!$this->importOneOrder) {
            // skip import if the order is anonymized
            if ($this->orderData->anonymized) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage('log.import.anonymized_order'),
                    $this->logOutput,
                    $this->marketplaceSku
                );
                return false;
            }
            // skip import if the order is older than 3 months
            $dateTimeOrder = new DateTime($this->orderData->marketplace_order_date);
            $interval = $dateTimeOrder->diff(new DateTime());
            $monthsInterval = $interval->m + ($interval->y * 12);
            if ($monthsInterval >= LengowImport::MONTH_INTERVAL_TIME) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage('log.import.old_order'),
                    $this->logOutput,
                    $this->marketplaceSku
                );
                return false;
            }
        }
        // checks if an external id already exists
        $idOrderPrestashop = $this->checkExternalIds($this->orderData->merchant_order_id);
        if ($idOrderPrestashop && !$this->debugMode && !$this->isReimported) {
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.external_id_exist',
                    array('order_id' => $idOrderPrestashop)
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
            return false;
        }
        // get a record in the lengow order table
        $this->idOrderLengow = LengowOrder::getIdFromLengowOrders(
            $this->marketplaceSku,
            $this->marketplace->name,
            $this->deliveryAddressId
        );
        // if order is cancelled or new -> skip
        if (!LengowImport::checkState($this->orderStateMarketplace, $this->marketplace)) {
            $orderProcessState = LengowOrder::getOrderProcessState($this->orderStateLengow);
            // check and complete an order not imported if it is canceled or refunded
            if ($this->idOrderLengow && $orderProcessState === LengowOrder::PROCESS_STATE_FINISH) {
                LengowOrder::finishOrderLogs($this->idOrderLengow);
                LengowOrder::updateOrderLengow(
                    $this->idOrderLengow,
                    array(
                        'order_lengow_state' => $this->orderStateLengow,
                        'order_process_state' => $orderProcessState,
                    )
                );
            }
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.current_order_state_unavailable',
                    array(
                        'order_state_marketplace' => $this->orderStateMarketplace,
                        'marketplace_name' => $this->marketplace->name,
                    )
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
            return false;
        }
        // get order comment from marketplace
        $this->orderComment = $this->getOrderComment();
        // create a new record in lengow order table if not exist
        if (!$this->idOrderLengow) {
            // created a record in the lengow order table
            if (!$this->createLengowOrder()) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage('log.import.lengow_order_not_saved'),
                    $this->logOutput,
                    $this->marketplaceSku
                );
                return false;
            } else {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage('log.import.lengow_order_saved'),
                    $this->logOutput,
                    $this->marketplaceSku
                );
            }
        }
        // checks if the required order data is present
        if (!$this->checkOrderData()) {
            return $this->returnResult(self::RESULT_ERROR, $this->idOrderLengow);
        }
        // get order amount and load processing fees and shipping cost
        $this->orderAmount = $this->getOrderAmount();
        // load tracking data
        $this->loadTrackingData();
        // get customer name
        $customerName = $this->getCustomerName();
        $customerEmail = $this->orderData->billing_address->email !== null
            ? (string)$this->orderData->billing_address->email
            : (string)$this->packageData->delivery->email;
        // update Lengow order with new data
        LengowOrder::updateOrderLengow(
            $this->idOrderLengow,
            array(
                'total_paid' => $this->orderAmount,
                'order_item' => $this->orderItems,
                'customer_name' => pSQL($customerName),
                'customer_email' => pSQL($customerEmail),
                'carrier' => pSQL($this->carrierName),
                'method' => pSQL($this->carrierMethod),
                'tracking' => pSQL($this->trackingNumber),
                'id_relay' => pSQL($this->relayId),
                'sent_marketplace' => (int)$this->shippedByMp,
                'delivery_country_iso' => pSQL((string)$this->packageData->delivery->common_country_iso_a2),
                'order_lengow_state' => pSQL($this->orderStateLengow),
                'extra' => pSQL(Tools::jsonEncode($this->orderData)),
            )
        );
        // try to import order
        try {
            // check if the order is shipped by marketplace
            if ($this->shippedByMp) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
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
                        array('order_process_state' => 2)
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
            // clean cart products
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
                        $this->addCommentOrder((int)$order->id, $this->orderComment);
                    }
                    // save order line id in lengow_order_line table
                    $orderLineSaved = $this->saveLengowOrderLine($order, $products);
                    LengowMain::log(
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage(
                            'log.import.lengow_order_line_saved',
                            array('order_line_saved' => $orderLineSaved)
                        ),
                        $this->logOutput,
                        $this->marketplaceSku
                    );
                    LengowMain::log(
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage(
                            'log.import.order_successfully_imported',
                            array('order_id' => $order->id)
                        ),
                        $this->logOutput,
                        $this->marketplaceSku
                    );
                    // ensure carrier compatibility with SoColissimo & Mondial Relay
                    $this->checkCarrierCompatibility($order);
                    // launch validateOrder hook for other plugin (uncomment if needed)
                    // $this->launchValidateOrderHook($order);
                }
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
                LengowMain::log(LengowLog::CODE_IMPORT, $logMessage, $this->logOutput, $this->marketplaceSku);
                $this->addQuantityBack($products);
            }
        } catch (LengowException $e) {
            $errorMessage = $e->getMessage();
        } catch (Exception $e) {
            $errorMessage = '[Prestashop error] "' . $e->getMessage() . '" ' . $e->getFile() . ' | ' . $e->getLine();
        }
        if (isset($errorMessage)) {
            if (isset($cart)) {
                $cart->delete();
            }
            LengowOrder::addOrderLog($this->idOrderLengow, $errorMessage, 'import');
            $decodedMessage = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
            LengowMain::log(
                LengowLog::CODE_IMPORT,
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
                    'order_lengow_state' => pSQL($this->orderStateLengow),
                    'is_reimported' => 0,
                )
            );
            return $this->returnResult(self::RESULT_ERROR, $this->idOrderLengow);
        }
        return $this->returnResult(self::RESULT_NEW, $this->idOrderLengow, (int)$order->id);
    }

    /**
     * Return an array of result for each order
     *
     * @param string $typeResult Type of result (new, update, error)
     * @param integer $idOrderLengow Lengow order id
     * @param integer|null $idOrder Prestashop order id
     *
     * @return array
     */
    protected function returnResult($typeResult, $idOrderLengow, $idOrder = null)
    {
        return array(
            'order_id' => $idOrder,
            'id_order_lengow' => $idOrderLengow,
            'marketplace_sku' => $this->marketplaceSku,
            'marketplace_name' => (string)$this->marketplace->name,
            'lengow_state' => $this->orderStateLengow,
            'order_new' => $typeResult == self::RESULT_NEW ? true : false,
            'order_update' => $typeResult == self::RESULT_UPDATE ? true : false,
            'order_error' => $typeResult == self::RESULT_ERROR ? true : false,
        );
    }

    /**
     * Check the command and updates data if necessary
     *
     * @param integer $idOrder Prestashop order id
     *
     * @return array|false
     */
    protected function checkAndUpdateOrder($idOrder)
    {
        LengowMain::log(
            LengowLog::CODE_IMPORT,
            LengowMain::setLogMessage('log.import.order_already_imported', array('order_id' => $idOrder)),
            $this->logOutput,
            $this->marketplaceSku
        );
        $order = new LengowOrder($idOrder);
        $result = array('id_order_lengow' => $order->lengowId);
        // Lengow -> cancel and reimport order
        if ($order->lengowIsReimported) {
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage('log.import.order_ready_to_reimport', array('order_id' => $idOrder)),
                $this->logOutput,
                $this->marketplaceSku
            );
            $this->isReimported = true;
            return false;
        } else {
            try {
                $orderUpdated = $order->updateState($this->orderStateLengow, $this->packageData);
                if ($orderUpdated) {
                    $result['update'] = true;
                    LengowMain::log(
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage('log.import.state_updated_to', array('state_name' => $orderUpdated)),
                        $this->logOutput,
                        $this->marketplaceSku
                    );
                    $stateName = '';
                    $availableStates = LengowMain::getOrderStates($this->idLang);
                    foreach ($availableStates as $state) {
                        if ((int)$state['id_order_state'] === LengowMain::getOrderState($this->orderStateLengow)) {
                            $stateName = $state['name'];
                            break;
                        }
                    }
                    LengowMain::log(
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage('log.import.order_state_updated', array('state_name' => $stateName)),
                        $this->logOutput,
                        $this->marketplaceSku
                    );
                }
            } catch (Exception $e) {
                $errorMessage = $e->getMessage() . '"' . $e->getFile() . '|' . $e->getLine();
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
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
        if (empty($this->packageData->cart)) {
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
        if ($this->orderData->billing_address === null) {
            $errorMessages[] = LengowMain::setLogMessage('lengow_log.error.no_billing_address');
        } elseif ($this->orderData->billing_address->common_country_iso_a2 === null) {
            $errorMessages[] = LengowMain::setLogMessage('lengow_log.error.no_country_for_billing_address');
        }
        if ($this->packageData->delivery->common_country_iso_a2 === null) {
            $errorMessages[] = LengowMain::setLogMessage('lengow_log.error.no_country_for_delivery_address');
        }
        if (!empty($errorMessages)) {
            foreach ($errorMessages as $errorMessage) {
                LengowOrder::addOrderLog($this->idOrderLengow, $errorMessage, 'import');
                $decodedMessage = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
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
        $idOrderPrestashop = false;
        if ($externalIds !== null && !empty($externalIds)) {
            foreach ($externalIds as $externalId) {
                $lineId = LengowOrder::getIdFromLengowDeliveryAddress((int)$externalId, $this->deliveryAddressId);
                if ($lineId) {
                    $idOrderPrestashop = $externalId;
                    break;
                }
            }
        }
        return $idOrderPrestashop;
    }

    /**
     * Get order comment from marketplace
     *
     * @return string
     */
    protected function getOrderComment()
    {
        if (isset($this->orderData->comments) && is_array($this->orderData->comments)) {
            $orderComment = join(',', $this->orderData->comments);
        } else {
            $orderComment = (string)$this->orderData->comments;
        }
        return $orderComment;
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
        if (!(bool)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_PROCESSING_FEE') || !$this->firstPackage) {
            $this->processingFee = 0;
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage('log.import.rewrite_processing_fee'),
                $this->logOutput,
                $this->marketplaceSku
            );
        }
        if (!$this->firstPackage) {
            $this->shippingCost = 0;
            LengowMain::log(
                LengowLog::CODE_IMPORT,
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
            if ($product->marketplace_status !== null) {
                $stateProduct = $this->marketplace->getStateLengow((string)$product->marketplace_status);
                if ($stateProduct === LengowOrder::STATE_CANCELED || $stateProduct === LengowOrder::STATE_REFUSED) {
                    continue;
                }
            }
            $nbItems += (int)$product->quantity;
            $totalAmount += (float)$product->amount;
        }
        $this->orderItems = $nbItems;
        return $totalAmount + $this->processingFee + $this->shippingCost;
    }

    /**
     * Get tracking data and update Lengow order record
     */
    protected function loadTrackingData()
    {
        $trackings = $this->packageData->delivery->trackings;
        if (!empty($trackings)) {
            $tracking = $trackings[0];
            $this->carrierName = $tracking->carrier !== null ? (string)$tracking->carrier : null;
            $this->carrierMethod = $tracking->method !== null ? (string)$tracking->method : null;
            $this->trackingNumber = $tracking->number !== null ? (string)$tracking->number : null;
            $this->relayId = $tracking->relay->id !== null ? (string)$tracking->relay->id : null;
            if ($tracking->is_delivered_by_marketplace !== null && $tracking->is_delivered_by_marketplace) {
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
        $firstName = (string)$this->orderData->billing_address->first_name;
        $lastName = (string)$this->orderData->billing_address->last_name;
        $firstName = Tools::ucfirst(Tools::strtolower($firstName));
        $lastName = Tools::ucfirst(Tools::strtolower($lastName));
        if (empty($firstName) && empty($lastName)) {
            return (string)$this->orderData->billing_address->full_name;
        } else {
            return $firstName . ' ' . $lastName;
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
     * @throws LengowException
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
        $billingDatas['email'] = $this->marketplaceSku . '-' . $this->marketplace->name . '@' . $domain;
        LengowMain::log(
            LengowLog::CODE_IMPORT,
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
        // get VAT number on the billing address if is empty
        if (empty($this->shippingAddress->vat_number) && !empty($billingAddress->vat_number)) {
            $this->shippingAddress->vat_number = $billingAddress->vat_number;
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
     * @param LengowCart $cart Lengow cart instance
     * @param array $products List of Lengow products
     *
     * @throws LengowException
     *
     * @return array
     */
    protected function createAndValidatePayment($cart, $products)
    {
        $idOrderState = LengowMain::getPrestashopStateId(
            $this->orderStateMarketplace,
            $this->marketplace,
            $this->shippedByMp
        );
        $payment = new LengowPaymentModule();
        $payment->setContext($this->context);
        $payment->active = true;
        $paymentMethod = (string)$this->orderData->marketplace;
        $message = 'Import Lengow | ' . "\r\n"
            . 'ID order : ' . (string)$this->orderData->marketplace_order_id . ' | ' . "\r\n"
            . 'Marketplace : ' . (string)$this->orderData->marketplace . ' | ' . "\r\n"
            . 'Delivery address id : ' . (int)$this->packageData->delivery->id . ' | ' . "\r\n"
            . 'Total paid : ' . $this->orderAmount . ' | ' . "\r\n"
            . 'Shipping : ' . $this->shippingCost . ' | ' . "\r\n"
            . 'Message : ' . $this->orderComment . "\r\n";
        // validate order
        if (_PS_VERSION_ >= '1.5') {
            $orderLists = $payment->makeOrder(
                $cart->id,
                $idOrderState,
                $paymentMethod,
                $message,
                $products,
                $this->shippingCost,
                $this->processingFee,
                $this->trackingNumber,
                $this->idOrderLengow,
                $this->orderStateLengow,
                $this->marketplaceSku,
                $this->logOutput
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
                $this->trackingNumber,
                $this->idOrderLengow,
                $this->orderStateLengow,
                $this->marketplaceSku,
                $this->logOutput
            );
        }
        return $orderLists;
    }

    /**
     * Create or load address based on API data
     *
     * @param integer $idCustomer Prestashop customer id
     * @param array $addressDatas address datas
     * @param boolean $shippingData is shipping address
     *
     * @return LengowAddress
     */
    protected function getAddress($idCustomer, $addressDatas = array(), $shippingData = false)
    {
        // if tracking_informations exist => get id_relay
        if ($shippingData && $this->relayId !== null) {
            $addressDatas['id_relay'] = $this->relayId;
        }
        $addressDatas['address_full'] = '';
        // construct field address_full
        $addressDatas['address_full'] .= !empty($addressDatas['first_line']) ? $addressDatas['first_line'] . ' ' : '';
        $addressDatas['address_full'] .= !empty($addressDatas['second_line']) ? $addressDatas['second_line'] . ' ' : '';
        $addressDatas['address_full'] .= !empty($addressDatas['complement']) ? $addressDatas['complement'] . ' ' : '';
        $addressDatas['address_full'] .= !empty($addressDatas['zipcode']) ? $addressDatas['zipcode'] . ' ' : '';
        $addressDatas['address_full'] .= !empty($addressDatas['city']) ? $addressDatas['city'] . ' ' : '';
        $addressDatas['address_full'] .= !empty($addressDatas['common_country_iso_a2'])
            ? $addressDatas['common_country_iso_a2']
            : '';
        $address = LengowAddress::getByHash(trim($addressDatas['address_full']));
        // if address exists => check if names are the same
        if ($address) {
            if ($address->id_customer === $idCustomer
                && $address->lastname === $addressDatas['last_name']
                && $address->firstname === $addressDatas['first_name']
            ) {
                // Add specific phone number when shipping and billing are the same
                if (empty($address->phone_mobile) || $address->phone_mobile === $address->phone) {
                    $newPhone = false;
                    $phoneHome = LengowMain::cleanPhone($addressDatas['phone_home']);
                    $phoneMobile = LengowMain::cleanPhone($addressDatas['phone_mobile']);
                    $phoneOffice = LengowMain::cleanPhone($addressDatas['phone_office']);
                    if (!empty($phoneHome) && $phoneOffice !== $address->phone) {
                        $newPhone = $phoneHome;
                    } elseif (!empty($phoneMobile) && $phoneOffice !== $address->phone) {
                        $newPhone = $phoneMobile;
                    } elseif (!empty($phoneOffice) && $phoneOffice !== $address->phone) {
                        $newPhone = $phoneOffice;
                    }
                    if ($newPhone) {
                        $address->phone_mobile = $newPhone;
                        $address->update();
                    }
                }
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
     * @throws Exception|LengowException product is a parent / product no be found
     *
     * @return array
     */
    protected function getProducts()
    {
        $products = array();
        foreach ($this->packageData->cart as $product) {
            $productDatas = LengowProduct::extractProductDataFromAPI($product);
            if ($productDatas['marketplace_status'] !== null) {
                $stateProduct = $this->marketplace->getStateLengow((string)$productDatas['marketplace_status']);
                if ($stateProduct === LengowOrder::STATE_CANCELED || $stateProduct === LengowOrder::STATE_REFUSED) {
                    $idProduct = $productDatas['merchant_product_id']->id !== null
                        ? (string)$productDatas['merchant_product_id']->id
                        : (string)$productDatas['marketplace_product_id'];
                    LengowMain::log(
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage(
                            'log.import.product_state_canceled',
                            array(
                                'product_id' => $idProduct,
                                'state_product' => $stateProduct,
                            )
                        ),
                        $this->logOutput,
                        $this->marketplaceSku
                    );
                    continue;
                }
            }
            $idsProduct = array(
                'idMerchant' => (string)$productDatas['merchant_product_id']->id,
                'idMP' => (string)$productDatas['marketplace_product_id'],
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
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage(
                            'log.import.product_advanced_search',
                            array(
                                'attribute_name' => $attributeName,
                                'attribute_value' => $attributeValue,
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
                    $idFull .= isset($ids['id_product_attribute']) ? '_' . $ids['id_product_attribute'] : '';
                    if (array_key_exists($idFull, $products)) {
                        $products[$idFull]['quantity'] += (int)$productDatas['quantity'];
                        $products[$idFull]['amount'] += (float)$productDatas['amount'];
                        $products[$idFull]['order_line_ids'][] = $productDatas['marketplace_order_line_id'];
                    } else {
                        $products[$idFull] = array(
                            'quantity' => (int)$productDatas['quantity'],
                            'amount' => (float)$productDatas['amount'],
                            'price_unit' => $productDatas['price_unit'],
                            'order_line_ids' => array($productDatas['marketplace_order_line_id']),
                        );
                    }
                    LengowMain::log(
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage(
                            'log.import.product_be_found',
                            array(
                                'id_full' => $idFull,
                                'attribute_name' => $attributeName,
                                'attribute_value' => $attributeValue,
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
                $idProduct = $productDatas['merchant_product_id']->id !== null
                    ? (string)$productDatas['merchant_product_id']->id
                    : (string)$productDatas['marketplace_product_id'];
                throw new LengowException(
                    LengowMain::setLogMessage(
                        'lengow_log.exception.product_not_be_found',
                        array('product_id' => $idProduct)
                    )
                );
            }
        }
        return $products;
    }

    /**
     * Get carrier id according to the tracking data given in the API
     *
     * @throws LengowException shipping country no country / no default carrier for country
     *
     * @return integer
     */
    protected function getCarrierId()
    {
        $idCarrier = false;
        $matchingFound = false;
        if (!isset($this->shippingAddress->id_country)) {
            throw new LengowException(
                LengowMain::setLogMessage('lengow_log.exception.carrier_shipping_address_no_country')
            );
        }
        $idCountry = (int)$this->shippingAddress->id_country;
        if ($idCountry === 0) {
            throw new LengowException(
                LengowMain::setLogMessage('lengow_log.exception.carrier_shipping_address_no_country')
            );
        }
        // get marketplace id by marketplace name
        $idMarketplace = LengowMarketplace::getIdMarketplace($this->marketplace->name);
        $hasCarriers = $this->marketplace->hasCarriers();
        $hasShippingMethods = $this->marketplace->hasShippingMethods();
        // if the marketplace has neither carrier nor shipping methods, use semantic matching
        if ((bool)LengowConfiguration::getGlobalValue('LENGOW_CARRIER_SEMANTIC_ENABLE')) {
            // semantic search first on the method for marketplaces working with custom shipping methods
            if (!$idCarrier && $this->carrierMethod && !$hasShippingMethods) {
                // carrier id recovery by semantic search on the carrier marketplace shipping method
                $idCarrier = LengowCarrier::getIdCarrierBySemanticSearch(
                    $this->carrierMethod,
                    $idCountry,
                    $this->shippingAddress->idRelay
                );
                $matchingFound = $idCarrier ? 'method' : false;
            }
            if (!$idCarrier && $this->carrierName && !$hasCarriers) {
                // carrier id recovery by semantic search on the carrier marketplace code
                $idCarrier = LengowCarrier::getIdCarrierBySemanticSearch(
                    $this->carrierName,
                    $idCountry,
                    $this->shippingAddress->idRelay
                );
                $matchingFound = $idCarrier ? 'carrier' : false;
            }
        }
        // if the marketplace has carriers or shipping method, use manual matching
        if (!$idCarrier && $this->carrierName && $hasCarriers) {
            // get carrier id by carrier marketplace code
            $idCarrier = LengowCarrier::getIdCarrierByCarrierMarketplaceName(
                $idCountry,
                $idMarketplace,
                $this->carrierName
            );
            $matchingFound = $idCarrier ? 'carrier' : false;
        }
        if (!$idCarrier && $this->carrierMethod && $hasShippingMethods) {
            // get carrier id by method marketplace code
            $idCarrier = LengowMethod::getIdCarrierByMethodMarketplaceName(
                $idCountry,
                $idMarketplace,
                $this->carrierMethod
            );
            $matchingFound = $idCarrier ? 'method' : false;
        }
        if (!$idCarrier) {
            // get default carrier by country
            $idCarrier = LengowCarrier::getDefaultIdCarrier($idCountry, $idMarketplace, true);
            if (!$idCarrier) {
                LengowCarrier::createDefaultCarrier($idCountry, $idMarketplace);
                $countryName = Country::getNameById($this->context->language->id, $idCountry);
                throw new LengowException(
                    LengowMain::setLogMessage(
                        'lengow_log.exception.no_default_carrier_for_country',
                        array(
                            'country_name' => $countryName,
                            'marketplace_name' => $this->marketplace->labelName,
                        )
                    )
                );
            }
        }
        $carrier = new Carrier($idCarrier);
        if ($matchingFound) {
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.match_carrier_found',
                    array(
                        'carrier_name' => $carrier->name,
                        'id_carrier' => $carrier->id,
                        'field_name' => $matchingFound,
                        'field_value' => $matchingFound === 'carrier' ? $this->carrierName : $this->carrierMethod,
                    )
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.match_default_carrier',
                    array(
                        'carrier_name' => $carrier->name,
                        'id_carrier' => $carrier->id,
                    )
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
        }
        return $idCarrier;
    }

    /**
     * Ensure carrier compatibility with SoColissimo & Mondial Relay
     *
     * @param LengowOrder $order order imported
     */
    protected function checkCarrierCompatibility($order)
    {
        try {
            $carrierCompatibility = LengowCarrier::carrierCompatibility(
                $order->id,
                $order->id_customer,
                $order->id_cart,
                $order->id_carrier,
                $this->shippingAddress
            );
            if ($carrierCompatibility > 0) {
                $carrier = new Carrier($order->id_carrier);
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage(
                        'log.import.carrier_compatibility_ensured',
                        array('carrier_name' => $carrier->name)
                    ),
                    $this->logOutput,
                    $this->marketplaceSku
                );
            }
        } catch (LengowException $e) {
            LengowMain::log(LengowLog::CODE_IMPORT, $e->getMessage(), $this->logOutput, $this->marketplaceSku);
        }
    }

    /**
     * Add a comment to the order
     *
     * @param integer $idOrder Prestashop order id
     * @param string $comment order comment
     *
     * @throws Exception
     */
    protected function addCommentOrder($idOrder, $comment)
    {
        if (!empty($comment) && $comment !== null) {
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
     * @throws Exception
     */
    protected function addQuantityBack($products)
    {
        foreach ($products as $sku => $product) {
            $idsProduct = explode('_', $sku);
            $idProductAttribute = isset($idsProduct[1]) ? $idsProduct[1] : null;
            if (_PS_VERSION_ < '1.5') {
                $p = new Product($idsProduct[0]);
                $p->addStockMvt($product['quantity'], (int)_STOCK_MOVEMENT_ORDER_REASON_, $idProductAttribute);
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
        $orderDate = $this->orderData->marketplace_order_date !== null
            ? (string)$this->orderData->marketplace_order_date
            : (string)$this->orderData->imported_at;
        $params = array(
            'marketplace_sku' => pSQL($this->marketplaceSku),
            'id_shop' => (int)$this->idShop,
            'id_shop_group' => (int)$this->idShopGroup,
            'id_lang' => (int)$this->idLang,
            'marketplace_name' => pSQL($this->marketplace->name),
            'marketplace_label' => pSQL((string)$this->marketplaceLabel),
            'delivery_address_id' => (int)$this->deliveryAddressId,
            'order_date' => date('Y-m-d H:i:s', strtotime($orderDate)),
            'order_lengow_state' => pSQL($this->orderStateLengow),
            'message' => pSQL($this->orderComment),
            'date_add' => date('Y-m-d H:i:s'),
            'order_process_state' => 0,
            'is_reimported' => 0,
        );
        if (isset($this->orderData->currency->iso_a3)) {
            $params['currency'] = $this->orderData->currency->iso_a3;
        }
        try {
            if (_PS_VERSION_ < '1.5') {
                $result = Db::getInstance()->autoExecute(_DB_PREFIX_ . 'lengow_orders', $params, 'INSERT');
            } else {
                $result = Db::getInstance()->insert('lengow_orders', $params);
            }
        } catch (PrestaShopDatabaseException $e) {
            $result = false;
        }
        if ($result) {
            $this->idOrderLengow = LengowOrder::getIdFromLengowOrders(
                $this->marketplaceSku,
                $this->marketplace->name,
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
     * @param LengowOrder $order Lengow order instance
     * @param array $products order products
     *
     * @return boolean
     */
    protected function saveLengowOrderLine($order, $products)
    {
        $orderLineSaved = false;
        foreach ($products as $idProduct => $values) {
            foreach ($values['order_line_ids'] as $idOrderLine) {
                $idOrderDetail = LengowOrderDetail::findByOrderIdProductId($order->id, $idProduct);
                try {
                    if (_PS_VERSION_ < '1.5') {
                        $result = Db::getInstance()->autoExecute(
                            _DB_PREFIX_ . 'lengow_order_line',
                            array(
                                'id_order' => (int)$order->id,
                                'id_order_line' => pSQL($idOrderLine),
                                'id_order_detail' => (int)$idOrderDetail,
                            ),
                            'INSERT'
                        );
                    } else {
                        $result = Db::getInstance()->insert(
                            'lengow_order_line',
                            array(
                                'id_order' => (int)$order->id,
                                'id_order_line' => pSQL($idOrderLine),
                                'id_order_detail' => (int)$idOrderDetail,
                            )
                        );
                    }
                } catch (PrestaShopDatabaseException $e) {
                    $result = false;
                }
                if ($result) {
                    $orderLineSaved .= !$orderLineSaved ? $idOrderLine : ' / ' . $idOrderLine;
                }
            }
        }
        return $orderLineSaved;
    }

    /**
     * Launch validateOrder hook for carrier plugins
     *
     * @param Order $order PrestaShop order instance
     *
     */
    protected function launchValidateOrderHook($order)
    {
        LengowMain::log(
            LengowLog::CODE_IMPORT,
            LengowMain::setLogMessage('log.import.launch_validate_order_hook'),
            $this->logOutput,
            $this->marketplaceSku
        );
        try {
            $cart = new Cart((int)$order->id_cart);
            $customer = new Customer((int)$order->id_customer);
            $currency = new Currency((int)$order->id_currency, null, (int)$this->context->shop->id);
            $orderStatus = new OrderState((int)$order->current_state, $this->idLang);
            // Hook validate order
            Hook::exec('actionValidateOrder', array(
                'cart' => $cart,
                'order' => $order,
                'customer' => $customer,
                'currency' => $currency,
                'orderStatus' => $orderStatus,
            ));
        } catch (Exception $e) {
            $errorMessage = '[Prestashop error] "' . $e->getMessage() . '" ' . $e->getFile() . ' | ' . $e->getLine();
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.validate_order_hook_failed',
                    array('error_message' => $errorMessage)
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
        }
    }
}
