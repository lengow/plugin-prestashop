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

/**
 * Lengow Import Order Class
 */
class LengowImportOrder
{
    /* Import Order construct params */
    const PARAM_SHOP_ID = 'shop_id';
    const PARAM_SHOP_GROUP_ID = 'shop_group_id';
    const PARAM_LANG_ID = 'lang_id';
    const PARAM_CONTEXT = 'context';
    const PARAM_FORCE_SYNC = 'force_sync';
    const PARAM_FORCE_PRODUCT = 'force_product';
    const PARAM_DEBUG_MODE = 'debug_mode';
    const PARAM_LOG_OUTPUT = 'log_output';
    const PARAM_MARKETPLACE_SKU = 'marketplace_sku';
    const PARAM_DELIVERY_ADDRESS_ID = 'delivery_address_id';
    const PARAM_ORDER_DATA = 'order_data';
    const PARAM_PACKAGE_DATA = 'package_data';
    const PARAM_FIRST_PACKAGE = 'first_package';
    const PARAM_IMPORT_ONE_ORDER = 'import_one_order';

    /* Import Order data */
    const MERCHANT_ORDER_ID = 'merchant_order_id';
    const MERCHANT_ORDER_REFERENCE = 'merchant_order_reference';
    const LENGOW_ORDER_ID = 'lengow_order_id';
    const MARKETPLACE_SKU = 'marketplace_sku';
    const MARKETPLACE_NAME = 'marketplace_name';
    const DELIVERY_ADDRESS_ID = 'delivery_address_id';
    const SHOP_ID = 'shop_id';
    const CURRENT_ORDER_STATUS = 'current_order_status';
    const PREVIOUS_ORDER_STATUS = 'previous_order_status';
    const ERRORS = 'errors';
    const RESULT_TYPE = 'result_type';

    /* Synchronisation results */
    const RESULT_CREATED = 'created';
    const RESULT_UPDATED = 'updated';
    const RESULT_FAILED = 'failed';
    const RESULT_IGNORED = 'ignored';

    /**
     * @var integer|null PrestaShop shop id
     */
    private $idShop;

    /**
     * @var integer PrestaShop shop group id
     */
    private $idShopGroup;

    /**
     * @var integer PrestaShop lang id
     */
    private $idLang;

    /**
     * @var Context PrestaShop Context for import order
     */
    private $context;

    /**
     * @var boolean force import order even if there are errors
     */
    private $forceSync;

    /**
     * @var boolean import inactive & out of stock products
     */
    private $forceProduct;

    /**
     * @var boolean use debug mode
     */
    private $debugMode;

    /**
     * @var boolean display log messages
     */
    private $logOutput;

    /**
     * @var string id lengow of current order
     */
    private $marketplaceSku;

    /**
     * @var string marketplace label
     */
    private $marketplaceLabel;

    /**
     * @var integer id of delivery address for current order
     */
    private $deliveryAddressId;

    /**
     * @var mixed API order data
     */
    private $orderData;

    /**
     * @var mixed API package data
     */
    private $packageData;

    /**
     * @var boolean is first package
     */
    private $firstPackage;

    /**
     * @var boolean import one order var from lengow import
     */
    private $importOneOrder;

    /**
     * @var boolean re-import order
     */
    private $isReimported;

    /**
     * @var integer id of the record Lengow order table
     */
    private $idOrderLengow;

    /**
     * @var integer id of the record PrestaShop order table
     */
    private $idOrder;

    /**
     * @var integer PrestaShop order reference
     */
    private $orderReference;

    /**
     * @var string order types data
     */
    private $orderTypes;

    /**
     * @var LengowMarketplace Lengow marketplace instance
     */
    private $marketplace;

    /**
     * @var string marketplace order state
     */
    private $orderStateMarketplace;

    /**
     * @var string Lengow order state
     */
    private $orderStateLengow;

    /**
     * @var string Previous Lengow order state
     */
    private $previousOrderStateLengow;

    /**
     * @var float order processing fee
     */
    private $processingFee;

    /**
     * @var float order shipping cost
     */
    private $shippingCost;

    /**
     * @var float order total amount
     */
    private $orderAmount;

    /**
     * @var integer number of order items
     */
    private $orderItems;

    /**
     * @var string|null carrier name
     */
    private $carrierName;

    /**
     * @var string|null carrier method
     */
    private $carrierMethod;

    /**
     * @var string|null carrier tracking number
     */
    private $trackingNumber;

    /**
     * @var string|null carrier relay id
     */
    private $relayId;

    /**
     * @var boolean if order shipped by marketplace
     */
    private $shippedByMp = false;

    /**
     * @var LengowAddress Lengow Address instance
     */
    private $shippingAddress;

    /**
     * @var string Marketplace order comment
     */
    private $orderComment;

    /**
     * @var array order errors
     */
    private $errors = array();

    /**
     * Construct the import manager
     *
     * @param array $params optional options
     *
     * integer  shop_id             Id shop for current order
     * integer  shop_group_id       Id shop group for current order
     * integer  lang_id             Id lang for current order
     * mixed    context             Context for current order
     * boolean  force_sync          force import order even if there are errors
     * boolean  force_product       force import of products
     * boolean  debug_mode          debug mode
     * boolean  log_output          display log messages
     * string   marketplace_sku     order marketplace sku
     * integer  delivery_address_id order delivery address id
     * mixed    order_data          order data
     * mixed    package_data        package data
     * boolean  first_package       it is the first package
     */
    public function __construct($params = array())
    {
        $this->idShop = $params[self::PARAM_SHOP_ID];
        $this->idShopGroup = $params[self::PARAM_SHOP_GROUP_ID];
        $this->idLang = $params[self::PARAM_LANG_ID];
        $this->context = $params[self::PARAM_CONTEXT];
        $this->forceSync = $params[self::PARAM_FORCE_SYNC];
        $this->forceProduct = $params[self::PARAM_FORCE_PRODUCT];
        $this->debugMode = $params[self::PARAM_DEBUG_MODE];
        $this->logOutput = $params[self::PARAM_LOG_OUTPUT];
        $this->marketplaceSku = $params[self::PARAM_MARKETPLACE_SKU];
        $this->deliveryAddressId = $params[self::PARAM_DELIVERY_ADDRESS_ID];
        $this->orderData = $params[self::PARAM_ORDER_DATA];
        $this->packageData = $params[self::PARAM_PACKAGE_DATA];
        $this->firstPackage = $params[self::PARAM_FIRST_PACKAGE];
        $this->importOneOrder = $params[self::PARAM_IMPORT_ONE_ORDER];
    }

    /**
     * Create or update order
     *
     * @return array
     */
    public function importOrder()
    {
        // load marketplace singleton and marketplace data
        if (!$this->loadMarketplaceData()) {
            return $this->returnResult(self::RESULT_IGNORED);
        }
        // checks if a record already exists in the lengow order table
        $this->idOrderLengow = LengowOrder::getIdFromLengowOrders(
            $this->marketplaceSku,
            $this->marketplace->name,
            $this->deliveryAddressId
        );
        // checks if an order already has an error in progress
        if ($this->idOrderLengow && $this->orderErrorAlreadyExist()) {
            return $this->returnResult(self::RESULT_IGNORED);
        }
        // recovery id if the order has already been imported
        $idOrder = LengowOrder::getOrderIdFromLengowOrders(
            $this->marketplaceSku,
            $this->marketplace->name,
            $this->deliveryAddressId,
            $this->marketplace->legacyCode
        );
        // update order state if already imported
        if ($idOrder) {
            $orderUpdated = $this->checkAndUpdateOrder($idOrder);
            if ($orderUpdated) {
                return $this->returnResult(self::RESULT_UPDATED);
            }
            if (!$this->isReimported) {
                return $this->returnResult(self::RESULT_IGNORED);
            }
        }
        // checks if the order is not anonymized or too old
        if (!$this->idOrderLengow && !$this->canCreateOrder()) {
            return $this->returnResult(self::RESULT_IGNORED);
        }
        // checks if an external id already exists
        if (!$this->idOrderLengow && $this->externalIdAlreadyExist()) {
            return $this->returnResult(self::RESULT_IGNORED);
        }
        // Checks if the order status is valid for order creation
        if (!$this->orderStatusIsValid()) {
            return $this->returnResult(self::RESULT_IGNORED);
        }
        // load data and create a new record in lengow order table if not exist
        if (!$this->createLengowOrder()) {
            return $this->returnResult(self::RESULT_IGNORED);
        }
        // checks if the required order data is present and update Lengow order record
        if (!$this->checkAndUpdateLengowOrderData()) {
            return $this->returnResult(self::RESULT_FAILED);
        }
        // checks if an order sent by the marketplace must be created or not
        if (!$this->canCreateOrderShippedByMarketplace()) {
            return $this->returnResult(self::RESULT_IGNORED);
        }
        // create PrestaShop order
        if (!$this->createOrder()) {
            return $this->returnResult(self::RESULT_FAILED);
        }
        return $this->returnResult(self::RESULT_CREATED);
    }

    /**
     * Load marketplace singleton and marketplace data
     *
     * @return boolean
     */
    private function loadMarketplaceData()
    {
        try {
            // get marketplace and Lengow order state
            $this->marketplace = LengowMain::getMarketplaceSingleton((string) $this->orderData->marketplace);
            $this->marketplaceLabel = $this->marketplace->labelName;
            $this->orderStateMarketplace = (string) $this->orderData->marketplace_status;
            $this->orderStateLengow = $this->marketplace->getStateLengow($this->orderStateMarketplace);
            $this->previousOrderStateLengow = $this->orderStateLengow;
            return true;
        } catch (LengowException $e) {
            $this->errors[] = LengowMain::decodeLogMessage($e->getMessage(), LengowTranslation::DEFAULT_ISO_CODE);
            LengowMain::log(LengowLog::CODE_IMPORT, $e->getMessage(), $this->logOutput, $this->marketplaceSku);
        }
        return false;
    }

    /**
     * Return an array of result for each order
     *
     * @param string $resultType Type of result (created, updated, failed or ignored)
     *
     * @return array
     */
    private function returnResult($resultType)
    {
        return array(
            self::MERCHANT_ORDER_ID => $this->idOrder,
            self::MERCHANT_ORDER_REFERENCE => $this->orderReference,
            self::LENGOW_ORDER_ID => $this->idOrderLengow,
            self::MARKETPLACE_SKU => $this->marketplaceSku,
            self::MARKETPLACE_NAME => $this->marketplace ? $this->marketplace->name : null,
            self::DELIVERY_ADDRESS_ID => $this->deliveryAddressId,
            self::SHOP_ID => $this->idShop,
            self::CURRENT_ORDER_STATUS => $this->orderStateLengow,
            self::PREVIOUS_ORDER_STATUS => $this->previousOrderStateLengow,
            self::ERRORS => $this->errors,
            self::RESULT_TYPE => $resultType,
        );
    }

    /**
     * Checks if an order already has an error in progress
     *
     * @return boolean
     */
    private function orderErrorAlreadyExist()
    {
        // if log import exist and not finished
        $importLog = LengowOrderError::getLastImportLogNotFinished($this->marketplaceSku, $this->deliveryAddressId);
        if (!$importLog) {
            return false;
        }
        // force order synchronization by removing pending errors
        if ($this->forceSync) {
            LengowOrderError::finishOrderLogs($this->idOrderLengow);
            return false;
        }
        $decodedMessage = LengowMain::decodeLogMessage(
            $importLog[LengowOrderError::FIELD_MESSAGE],
            LengowTranslation::DEFAULT_ISO_CODE
        );
        $message = LengowMain::setLogMessage(
            'log.import.error_already_created',
            array(
                'decoded_message' => $decodedMessage,
                'date_message' => $importLog[LengowOrderError::FIELD_CREATED_AT],
            )
        );
        $this->errors[] = LengowMain::decodeLogMessage($message, LengowTranslation::DEFAULT_ISO_CODE);
        LengowMain::log(LengowLog::CODE_IMPORT, $message, $this->logOutput, $this->marketplaceSku);
        return true;
    }

    /**
     * Check the command and updates data if necessary
     *
     * @param integer $idOrder PrestaShop order id
     *
     * @return boolean
     */
    private function checkAndUpdateOrder($idOrder)
    {
        $orderUpdated = false;
        LengowMain::log(
            LengowLog::CODE_IMPORT,
            LengowMain::setLogMessage('log.import.order_already_imported', array('order_id' => $idOrder)),
            $this->logOutput,
            $this->marketplaceSku
        );
        $order = new LengowOrder($idOrder);
        // Lengow -> cancel and reimport order
        if ($order->lengowIsReimported) {
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage('log.import.order_ready_to_reimport', array('order_id' => $idOrder)),
                $this->logOutput,
                $this->marketplaceSku
            );
            $this->isReimported = true;
            return $orderUpdated;
        }
        // load data for return
        $this->idOrder = (int) $idOrder;
        $this->orderReference = $order->reference;
        $this->previousOrderStateLengow = $order->lengowState;
        try {
            $orderUpdated = $order->updateState($this->orderStateLengow, $this->packageData);
            if ($orderUpdated) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage('log.import.state_updated_to', array('state_name' => $orderUpdated)),
                    $this->logOutput,
                    $this->marketplaceSku
                );
                $orderUpdated = true;
                $stateName = '';
                $availableStates = LengowMain::getOrderStates($this->idLang);
                foreach ($availableStates as $state) {
                    if ((int) $state['id_order_state'] === LengowMain::getOrderState($this->orderStateLengow)) {
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
            $vatNumberData = $this->getVatNumberFromOrderData();
            if ($order->lengowCustomerVatNumber !== $vatNumberData) {
                $this->checkAndUpdateLengowOrderData();
                $orderUpdated = true;
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage('log.import.lengow_order_updated'),
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
        return $orderUpdated;
    }

    /**
     * Checks if the order is not anonymized or too old
     *
     * @return boolean
     */
    private function canCreateOrder()
    {
        if ($this->importOneOrder) {
            return true;
        }
        // skip import if the order is anonymized
        if ($this->orderData->anonymized) {
            $message = LengowMain::setLogMessage('log.import.anonymized_order');
            $this->errors[] = LengowMain::decodeLogMessage($message, LengowTranslation::DEFAULT_ISO_CODE);
            LengowMain::log(LengowLog::CODE_IMPORT, $message, $this->logOutput, $this->marketplaceSku);
            return false;
        }
        // skip import if the order is older than 3 months
        try {
            $dateTimeOrder = new DateTime($this->orderData->marketplace_order_date);
            $interval = $dateTimeOrder->diff(new DateTime());
            $monthsInterval = $interval->m + ($interval->y * 12);
            if ($monthsInterval >= LengowImport::MONTH_INTERVAL_TIME) {
                $message = LengowMain::setLogMessage('log.import.old_order');
                $this->errors[] = LengowMain::decodeLogMessage($message, LengowTranslation::DEFAULT_ISO_CODE);
                LengowMain::log(LengowLog::CODE_IMPORT, $message, $this->logOutput, $this->marketplaceSku);
                return false;
            }
        } catch (Exception $e) {
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage('log.import.unable_verify_date'),
                $this->logOutput,
                $this->marketplaceSku
            );
        }
        return true;
    }

    /**
     * Checks if an external id already exists
     *
     * @return boolean
     */
    private function externalIdAlreadyExist()
    {
        if (empty($this->orderData->merchant_order_id) || $this->debugMode || $this->isReimported) {
            return false;
        }
        foreach ($this->orderData->merchant_order_id as $externalId) {
            if (LengowOrder::getIdFromLengowDeliveryAddress((int) $externalId, $this->deliveryAddressId)) {
                $message = LengowMain::setLogMessage('log.import.external_id_exist', array('order_id' => $externalId));
                $this->errors[] = LengowMain::decodeLogMessage($message, LengowTranslation::DEFAULT_ISO_CODE);
                LengowMain::log(LengowLog::CODE_IMPORT, $message, $this->logOutput, $this->marketplaceSku);
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if the order status is valid for order creation
     *
     * @return boolean
     */
    private function orderStatusIsValid()
    {
        if (LengowImport::checkState($this->orderStateMarketplace, $this->marketplace)) {
            return true;
        }
        $orderProcessState = LengowOrder::getOrderProcessState($this->orderStateLengow);
        // check and complete an order not imported if it is canceled or refunded
        if ($this->idOrderLengow && $orderProcessState === LengowOrder::PROCESS_STATE_FINISH) {
            LengowOrderError::finishOrderLogs($this->idOrderLengow);
            LengowOrder::updateOrderLengow(
                $this->idOrderLengow,
                array(
                    LengowOrder::FIELD_ORDER_LENGOW_STATE => $this->orderStateLengow,
                    LengowOrder::FIELD_ORDER_PROCESS_STATE => $orderProcessState,
                )
            );
        }
        $message = LengowMain::setLogMessage(
            'log.import.current_order_state_unavailable',
            array(
                'order_state_marketplace' => $this->orderStateMarketplace,
                'marketplace_name' => $this->marketplace->name,
            )
        );
        $this->errors[] = LengowMain::decodeLogMessage($message, LengowTranslation::DEFAULT_ISO_CODE);
        LengowMain::log(LengowLog::CODE_IMPORT, $message, $this->logOutput, $this->marketplaceSku);
        return false;
    }

    /**
     * Create an order in lengow orders table
     *
     * @return boolean
     */
    private function createLengowOrder()
    {
        // load order comment from marketplace
        $this->loadOrderComment();
        // load order types data
        $this->loadOrderTypesData();
        // If the Lengow order already exists do not recreate it
        if ($this->idOrderLengow) {
            return true;
        }
        $params = array(
            LengowOrder::FIELD_SHOP_ID => (int) $this->idShop,
            LengowOrder::FIELD_SHOP_GROUP_ID => (int) $this->idShopGroup,
            LengowOrder::FIELD_LANG_ID => (int) $this->idLang,
            LengowOrder::FIELD_MARKETPLACE_SKU => pSQL($this->marketplaceSku),
            LengowOrder::FIELD_MARKETPLACE_NAME => pSQL($this->marketplace->name),
            LengowOrder::FIELD_MARKETPLACE_LABEL => pSQL((string) $this->marketplaceLabel),
            LengowOrder::FIELD_DELIVERY_ADDRESS_ID => (int) $this->deliveryAddressId,
            LengowOrder::FIELD_ORDER_DATE => $this->getOrderDate(),
            LengowOrder::FIELD_ORDER_LENGOW_STATE => pSQL($this->orderStateLengow),
            LengowOrder::FIELD_ORDER_TYPES => $this->orderTypes,
            LengowOrder::FIELD_CUSTOMER_VAT_NUMBER => $this->getVatNumberFromOrderData(),
            LengowOrder::FIELD_MESSAGE => pSQL($this->orderComment),
            LengowOrder::FIELD_EXTRA => pSQL(json_encode($this->orderData)),
            LengowOrder::FIELD_CREATED_AT => date(LengowMain::DATE_FULL),
            LengowOrder::FIELD_ORDER_PROCESS_STATE => 0,
            LengowOrder::FIELD_IS_REIMPORTED => 0,
        );
        try {
            $result = Db::getInstance()->insert(LengowOrder::TABLE_ORDER, $params);
            if ($result) {
                $this->idOrderLengow = LengowOrder::getIdFromLengowOrders(
                    $this->marketplaceSku,
                    $this->marketplace->name,
                    $this->deliveryAddressId
                );
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage('log.import.lengow_order_saved'),
                    $this->logOutput,
                    $this->marketplaceSku
                );
                return true;
            }
        } catch (Exception $e) {
            $errorMessage = '[PrestaShop error]: "' . $e->getMessage()
                . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.unable_create_order',
                    array('error_message' => $errorMessage)
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
        }
        $message = LengowMain::setLogMessage('log.import.lengow_order_not_saved');
        $this->errors[] = LengowMain::decodeLogMessage($message, LengowTranslation::DEFAULT_ISO_CODE);
        LengowMain::log(LengowLog::CODE_IMPORT, $message, $this->logOutput, $this->marketplaceSku);
        return false;
    }

    /**
     * Load order comment from marketplace
     */
    private function loadOrderComment()
    {
        if (isset($this->orderData->comments) && is_array($this->orderData->comments)) {
            $orderComment = implode(',', $this->orderData->comments);
        } else {
            $orderComment = (string) $this->orderData->comments;
        }
        $this->orderComment = $orderComment;
    }

    /**
     * Load order types data and update Lengow order record
     */
    private function loadOrderTypesData()
    {
        $orderTypes = array();
        if ($this->orderData->order_types !== null && !empty($this->orderData->order_types)) {
            foreach ($this->orderData->order_types as $orderType) {
                $orderTypes[$orderType->type] = $orderType->label;
                if ($orderType->type === LengowOrder::TYPE_DELIVERED_BY_MARKETPLACE) {
                    $this->shippedByMp = true;
                }
            }
        }
        $this->orderTypes = json_encode($orderTypes);
    }

    /**
     * Get order date in correct format
     *
     * @return string
     */
    private function getOrderDate()
    {
        $orderDate = $this->orderData->marketplace_order_date !== null
            ? (string) $this->orderData->marketplace_order_date
            : (string) $this->orderData->imported_at;
        return date(LengowMain::DATE_FULL, strtotime($orderDate));
    }

    /**
     * Get vat_number from lengow order data
     *
     * @return string|null
     */
    private function getVatNumberFromOrderData()
    {
        if (isset($this->orderData->billing_address->vat_number)) {
            return $this->orderData->billing_address->vat_number;
        }
        if (isset($this->packageData->delivery->vat_number)) {
            return $this->packageData->delivery->vat_number;
        }
        return null;
    }

    /**
     * Checks if the required order data is present and update Lengow order record
     *
     * @return boolean
     */
    private function checkAndUpdateLengowOrderData()
    {
        // Checks if all necessary order data are present
        if (!$this->checkOrderData()) {
            return false;
        }
        // load order amount, processing fees and shipping costs
        $this->loadOrderAmount();
        // load tracking data
        $this->loadTrackingData();
        // update Lengow order record with new data
        LengowOrder::updateOrderLengow(
            $this->idOrderLengow,
            array(
                LengowOrder::FIELD_CURRENCY => (string) $this->orderData->currency->iso_a3,
                LengowOrder::FIELD_TOTAL_PAID => $this->orderAmount,
                LengowOrder::FIELD_ORDER_ITEM => $this->orderItems,
                LengowOrder::FIELD_CUSTOMER_NAME => pSQL($this->getCustomerName()),
                LengowOrder::FIELD_CUSTOMER_EMAIL => pSQL($this->getCustomerEmail()),
                LengowOrder::FIELD_CUSTOMER_VAT_NUMBER => pSQL($this->getVatNumberFromOrderData()),
                LengowOrder::FIELD_CARRIER => pSQL($this->carrierName),
                LengowOrder::FIELD_CARRIER_METHOD => pSQL($this->carrierMethod),
                LengowOrder::FIELD_CARRIER_TRACKING => pSQL($this->trackingNumber),
                LengowOrder::FIELD_CARRIER_RELAY_ID => pSQL($this->relayId),
                LengowOrder::FIELD_SENT_MARKETPLACE => (int) $this->shippedByMp,
                LengowOrder::FIELD_DELIVERY_COUNTRY_ISO => pSQL(
                    (string) $this->packageData->delivery->common_country_iso_a2
                ),
                LengowOrder::FIELD_ORDER_LENGOW_STATE => pSQL($this->orderStateLengow),
                LengowOrder::FIELD_EXTRA => pSQL(json_encode($this->orderData)),
            )
        );
        return true;
    }

    /**
     * Checks if all necessary order data are present
     *
     * @return boolean
     */
    private function checkOrderData()
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
        if (empty($errorMessages)) {
            return true;
        }
        foreach ($errorMessages as $errorMessage) {
            LengowOrderError::addOrderLog($this->idOrderLengow, $errorMessage);
            $decodedMessage = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
            $this->errors[] = $decodedMessage;
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.order_import_failed',
                    array('decoded_message' => $decodedMessage)
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
        }
        return false;
    }

    /**
     * Load order amount, processing fees and shipping costs
     */
    private function loadOrderAmount()
    {
        $this->processingFee = (float) $this->orderData->processing_fee;
        $this->shippingCost = (float) $this->orderData->shipping;
        // rewrite processing fees and shipping cost
        if (!$this->firstPackage
            || !(bool) LengowConfiguration::getGlobalValue(LengowConfiguration::IMPORT_PROCESSING_FEE_ENABLED)
        ) {
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
                $stateProduct = $this->marketplace->getStateLengow((string) $product->marketplace_status);
                if ($stateProduct === LengowOrder::STATE_CANCELED || $stateProduct === LengowOrder::STATE_REFUSED) {
                    continue;
                }
            }
            $nbItems += (int) $product->quantity;
            $totalAmount += (float) $product->amount;
        }
        $this->orderItems = $nbItems;
        $this->orderAmount = $totalAmount + $this->processingFee + $this->shippingCost;
    }

    /**
     * Load tracking data
     */
    private function loadTrackingData()
    {
        $tracks = $this->packageData->delivery->trackings;
        if (!empty($tracks)) {
            $tracking = $tracks[0];
            $this->carrierName = $tracking->carrier;
            $this->carrierMethod = $tracking->method;
            $this->trackingNumber = $tracking->number;
            $this->relayId = $tracking->relay->id;
        }
    }

    /**
     * Get customer name
     *
     * @return string
     */
    private function getCustomerName()
    {
        $firstName = (string) $this->orderData->billing_address->first_name;
        $lastName = (string) $this->orderData->billing_address->last_name;
        $firstName = Tools::ucfirst(Tools::strtolower($firstName));
        $lastName = Tools::ucfirst(Tools::strtolower($lastName));
        if (empty($firstName) && empty($lastName)) {
            return (string) $this->orderData->billing_address->full_name;
        }
        if (empty($firstName)) {
            return $lastName;
        }
        if (empty($lastName)) {
            return $firstName;
        }
        return $firstName . ' ' . $lastName;
    }

    /**
     * Get customer email
     *
     * @return string
     */
    private function getCustomerEmail()
    {
        return $this->orderData->billing_address->email !== null
            ? (string) $this->orderData->billing_address->email
            : (string) $this->packageData->delivery->email;
    }

    /**
     * Checks if an order sent by the marketplace must be created or not
     *
     * @return boolean
     */
    private function canCreateOrderShippedByMarketplace()
    {
        // check if the order is shipped by marketplace
        if ($this->shippedByMp) {
            $message = LengowMain::setLogMessage(
                'log.import.order_shipped_by_marketplace',
                array('marketplace_name' => $this->marketplace->name)
            );
            LengowMain::log(LengowLog::CODE_IMPORT, $message, $this->logOutput, $this->marketplaceSku);
            if (!LengowConfiguration::getGlobalValue(LengowConfiguration::SHIPPED_BY_MARKETPLACE_ENABLED)) {
                $this->errors[] = LengowMain::decodeLogMessage($message, LengowTranslation::DEFAULT_ISO_CODE);
                LengowOrder::updateOrderLengow(
                    $this->idOrderLengow,
                    array(LengowOrder::FIELD_ORDER_PROCESS_STATE => LengowOrder::PROCESS_STATE_FINISH)
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Create a PrestaShop order
     *
     * @return boolean
     */
    private function createOrder()
    {
        try {
            // search and get all products
            $products = $this->getProducts();
            // create PrestaShop cart
            $cart = $this->createCart($products);
            // create and validate PrestaShop order
            $orders = $this->createAndValidatePayment($cart, $products);
            // if no order in list
            if (empty($orders)) {
                throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.order_list_is_empty'));
            }
            foreach ($orders as $order) {
                // load order data for return
                $this->idOrder = (int) $order->id;
                $this->orderReference = $order->reference;
                // add a comment to the PrestaShop order
                $this->addCommentOrder((int) $order->id, $this->orderComment);
                // save order line id in lengow_order_line table
                $this->saveLengowOrderLine($order, $products);
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
            // add quantity back for re-import order and order shipped by marketplace
            $this->addQuantityBack($products);
        } catch (LengowException $e) {
            $errorMessage = $e->getMessage();
        } catch (Exception $e) {
            $errorMessage = '[PrestaShop error]: "' . $e->getMessage()
                . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
        }
        if (!isset($errorMessage)) {
            return true;
        }
        if (isset($cart)) {
            try {
                $cart->delete();
            } catch (Exception $e) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage('log.import.unable_delete_cart'),
                    $this->logOutput,
                    $this->marketplaceSku
                );
            }
        }
        LengowOrderError::addOrderLog($this->idOrderLengow, $errorMessage);
        $decodedMessage = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
        $this->errors[] = $decodedMessage;
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
                LengowOrder::FIELD_ORDER_LENGOW_STATE => pSQL($this->orderStateLengow),
                LengowOrder::FIELD_IS_REIMPORTED => 0,
            )
        );
        return false;
    }

    /**
     * Get products from API data
     *
     * @throws Exception|LengowException product is a parent / product no be found
     *
     * @return array
     */
    private function getProducts()
    {
        $products = array();
        foreach ($this->packageData->cart as $product) {
            $productData = LengowProduct::extractProductDataFromAPI($product);
            if ($productData['marketplace_status'] !== null) {
                $stateProduct = $this->marketplace->getStateLengow((string) $productData['marketplace_status']);
                if ($stateProduct === LengowOrder::STATE_CANCELED || $stateProduct === LengowOrder::STATE_REFUSED) {
                    $idProduct = $productData['merchant_product_id']->id !== null
                        ? (string) $productData['merchant_product_id']->id
                        : (string) $productData['marketplace_product_id'];
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
                'idMerchant' => (string) $productData['merchant_product_id']->id,
                'idMP' => (string) $productData['marketplace_product_id'],
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
                        $products[$idFull]['quantity'] += (int) $productData['quantity'];
                        $products[$idFull]['amount'] += (float) $productData['amount'];
                        $products[$idFull]['order_line_ids'][] = $productData['marketplace_order_line_id'];
                    } else {
                        $products[$idFull] = array(
                            'quantity' => (int) $productData['quantity'],
                            'amount' => (float) $productData['amount'],
                            'price_unit' => $productData['price_unit'],
                            'order_line_ids' => array($productData['marketplace_order_line_id']),
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
                $idProduct = $productData['merchant_product_id']->id !== null
                    ? (string) $productData['merchant_product_id']->id
                    : (string) $productData['marketplace_product_id'];
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
     * Create a PrestaShop cart
     *
     * @throws Exception|LengowException
     *
     * @return LengowCart
     */
    private function createCart($products)
    {
        // create PrestaShop Customer, addresses and load cart data
        $cartData = $this->getCartData();
        $cart = new LengowCart();
        $cart->assign($cartData);
        $cart->validateLengow();
        $cart->force_product = $this->forceProduct;
        // add products to cart
        $cart->addProducts($products);
        // removes non-Lengow products from cart
        $cart->cleanCart($products);
        // add cart to context
        $this->context->cart = $cart;
        return $cart;
    }

    /**
     * Create PrestaShop Customer, addresses and load cart data
     *
     * @throws LengowException
     *
     * @return array
     */
    private function getCartData()
    {
        $cartData = array();
        $cartData['id_lang'] = $this->idLang;
        $cartData['id_shop'] = $this->idShop;
        // get billing data
        $billingData = LengowAddress::extractAddressDataFromAPI($this->orderData->billing_address);
        // create customer based on billing data
        // generation of fictitious email
        $domain = !LengowMain::getHost() ? 'prestashop.shop' : LengowMain::getHost();
        $billingData['email'] = $this->marketplaceSku . '-' . $this->marketplace->name . '@' . $domain;
        LengowMain::log(
            LengowLog::CODE_IMPORT,
            LengowMain::setLogMessage('log.import.generate_unique_email', array('email' => $billingData['email'])),
            $this->logOutput,
            $this->marketplaceSku
        );
        // update Lengow order with customer name
        $customer = $this->getCustomer($billingData);
        if (!$customer->id) {
            $customer->validateLengow();
        }
        $cartData['id_customer'] = $customer->id;
        // create addresses from API data
        // billing
        $billingAddress = $this->getAddress($customer->id, $billingData);
        if (!$billingAddress->id) {
            $billingAddress->id_customer = $customer->id;
            $billingAddress->validateLengow();
        }
        $cartData['id_address_invoice'] = $billingAddress->id;
        // shipping
        $shippingData = LengowAddress::extractAddressDataFromAPI($this->packageData->delivery);
        $this->shippingAddress = $this->getAddress($customer->id, $shippingData, true);
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
        $cartData['id_address_delivery'] = $this->shippingAddress->id;
        // get currency
        $cartData['id_currency'] = (int) Currency::getIdByIsoCode((string) $this->orderData->currency->iso_a3);
        // get carrier
        $cartData['id_carrier'] = $this->getCarrierId();
        return $cartData;
    }

    /**
     * Create or load customer based on API data
     *
     * @param array $customerData API data
     *
     * @return LengowCustomer
     */
    private function getCustomer($customerData = array())
    {
        $customer = new LengowCustomer();
        // check if customer already exists in PrestaShop
        $customer->getByEmailAndShop($customerData['email'], $this->idShop);
        if ($customer->id) {
            return $customer;
        }
        // create new customer
        $customer->assign($customerData);
        return $customer;
    }

    /**
     * Create or load address based on API data
     *
     * @param integer $idCustomer PrestaShop customer id
     * @param array $addressData address data
     * @param boolean $shippingData is shipping address
     *
     * @return LengowAddress
     */
    private function getAddress($idCustomer, $addressData = array(), $shippingData = false)
    {
        // if tracking_information exist => get id_relay
        if ($shippingData && $this->relayId !== null) {
            $addressData['id_relay'] = $this->relayId;
        }
        $addressData['address_full'] = '';
        // construct field address_full
        $addressData['address_full'] .= !empty($addressData['first_line']) ? $addressData['first_line'] . ' ' : '';
        $addressData['address_full'] .= !empty($addressData['second_line']) ? $addressData['second_line'] . ' ' : '';
        $addressData['address_full'] .= !empty($addressData['complement']) ? $addressData['complement'] . ' ' : '';
        $addressData['address_full'] .= !empty($addressData['zipcode']) ? $addressData['zipcode'] . ' ' : '';
        $addressData['address_full'] .= !empty($addressData['city']) ? $addressData['city'] . ' ' : '';
        $addressData['address_full'] .= !empty($addressData['common_country_iso_a2'])
            ? $addressData['common_country_iso_a2']
            : '';
        $address = LengowAddress::getByHash(trim($addressData['address_full']));
        // if address exists => check if names are the same
        if ($address && $address->id_customer === $idCustomer
            && $address->lastname === $addressData['last_name']
            && $address->firstname === $addressData['first_name']
        ) {
            // Add specific phone number when shipping and billing are the same
            if (empty($address->phone_mobile) || $address->phone_mobile === $address->phone) {
                $newPhone = false;
                $phoneHome = LengowMain::cleanPhone($addressData['phone_home']);
                $phoneMobile = LengowMain::cleanPhone($addressData['phone_mobile']);
                $phoneOffice = LengowMain::cleanPhone($addressData['phone_office']);
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
            if (isset($addressData['id_relay'])) {
                $address->idRelay = $addressData['id_relay'];
            }
            return $address;
        }
        // construct LengowAddress and assign values
        $address = new LengowAddress();
        $address->assign($addressData);
        return $address;
    }

    /**
     * Get carrier id according to the tracking data given in the API
     *
     * @throws LengowException shipping country no country / no default carrier for country
     *
     * @return integer
     */
    private function getCarrierId()
    {
        $idCarrier = false;
        $matchingFound = false;
        if (!isset($this->shippingAddress->id_country)) {
            throw new LengowException(
                LengowMain::setLogMessage('lengow_log.exception.carrier_shipping_address_no_country')
            );
        }
        $idCountry = (int) $this->shippingAddress->id_country;
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
        if ((bool) LengowConfiguration::getGlobalValue(LengowConfiguration::SEMANTIC_MATCHING_CARRIER_ENABLED)) {
            // semantic search first on the method for marketplaces working with custom shipping methods
            if ($this->carrierMethod && !$hasShippingMethods) {
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
     * Create and validate PrestaShop order
     *
     * @param LengowCart $cart Lengow cart instance
     * @param array $products List of Lengow products
     *
     * @throws LengowException
     *
     * @return array
     */
    private function createAndValidatePayment($cart, $products)
    {
        $idOrderState = LengowMain::getPrestashopStateId(
            $this->orderStateMarketplace,
            $this->marketplace,
            $this->shippedByMp
        );
        $payment = new LengowPaymentModule();
        $payment->setContext($this->context);
        $payment->active = true;
        $paymentMethod = (string) $this->orderData->marketplace;
        $message = 'Import Lengow | ' . "\r\n"
            . 'ID order : ' . $this->orderData->marketplace_order_id . ' | ' . "\r\n"
            . 'Marketplace : ' . $this->orderData->marketplace . ' | ' . "\r\n"
            . 'Delivery address id : ' . (int) $this->packageData->delivery->id . ' | ' . "\r\n"
            . 'Total paid : ' . $this->orderAmount . ' | ' . "\r\n"
            . 'Shipping : ' . $this->shippingCost . ' | ' . "\r\n"
            . 'Message : ' . $this->orderComment . "\r\n";
        // validate order
        return $payment->makeOrder(
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
    }

    /**
     * Ensure carrier compatibility with SoColissimo & Mondial Relay
     *
     * @param LengowOrder $order order imported
     */
    private function checkCarrierCompatibility($order)
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
     * Add a comment to the PrestaShop order
     *
     * @param integer $idOrder PrestaShop order id
     * @param string $comment order comment
     *
     * @throws Exception
     */
    private function addCommentOrder($idOrder, $comment)
    {
        if (!empty($comment)) {
            $msg = new Message();
            $msg->id_order = $idOrder;
            $msg->private = 1;
            $msg->message = $comment;
            $msg->add();
        }
    }

    /**
     * Save order line in lengow orders line table
     *
     * @param LengowOrder $order Lengow order instance
     * @param array $products order products
     */
    private function saveLengowOrderLine($order, $products)
    {
        $orderLineSaved = false;
        foreach ($products as $idProduct => $values) {
            foreach ($values['order_line_ids'] as $idOrderLine) {
                $idOrderDetail = LengowOrderDetail::findByOrderIdProductId($order->id, $idProduct);
                try {
                    $result = Db::getInstance()->insert(
                        LengowOrderLine::TABLE_ORDER_LINE,
                        array(
                            LengowOrderLine::FIELD_ORDER_ID => (int) $order->id,
                            LengowOrderLine::FIELD_ORDER_LINE_ID => pSQL($idOrderLine),
                            LengowOrderLine::FIELD_ORDER_DETAIL_ID => (int) $idOrderDetail,
                        )
                    );
                } catch (PrestaShopDatabaseException $e) {
                    $result = false;
                }
                if ($result) {
                    $orderLineSaved .= !$orderLineSaved ? $idOrderLine : ' / ' . $idOrderLine;
                }
            }
        }
        if ($orderLineSaved) {
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.lengow_order_line_saved',
                    array('order_line_saved' => $orderLineSaved)
                ),
                $this->logOutput,
                $this->marketplaceSku
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage('log.import.lengow_order_line_not_saved'),
                $this->logOutput,
                $this->marketplaceSku
            );
        }
    }

    /**
     * Add quantity back to stock
     *
     * @param array $products list of products
     *
     * @throws Exception
     */
    private function addQuantityBack($products)
    {
        // add quantity back for re-import order and order shipped by marketplace
        if ($this->isReimported
            || ($this->shippedByMp && !(bool) LengowConfiguration::getGlobalValue(
                LengowConfiguration::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED
            ))
        ) {
            $logMessage = $this->isReimported
                ? LengowMain::setLogMessage('log.import.quantity_back_reimported_order')
                : LengowMain::setLogMessage('log.import.quantity_back_shipped_by_marketplace');
            LengowMain::log(LengowLog::CODE_IMPORT, $logMessage, $this->logOutput, $this->marketplaceSku);
            foreach ($products as $sku => $productData) {
                $idsProduct = explode('_', $sku);
                $idProductAttribute = isset($idsProduct[1]) ? $idsProduct[1] : null;
                StockAvailable::updateQuantity(
                    (int) $idsProduct[0],
                    $idProductAttribute,
                    $productData['quantity'],
                    $this->idShop
                );
            }
        }
    }

    /**
     * Launch validateOrder hook for carrier plugins
     *
     * @param Order $order PrestaShop order instance
     */
    private function launchValidateOrderHook($order)
    {
        LengowMain::log(
            LengowLog::CODE_IMPORT,
            LengowMain::setLogMessage('log.import.launch_validate_order_hook'),
            $this->logOutput,
            $this->marketplaceSku
        );
        try {
            $cart = new Cart((int) $order->id_cart);
            $customer = new Customer((int) $order->id_customer);
            $currency = new Currency((int) $order->id_currency, null, (int) $this->context->shop->id);
            $orderStatus = new OrderState((int) $order->current_state, $this->idLang);
            // Hook validate order
            Hook::exec('actionValidateOrder', array(
                'cart' => $cart,
                'order' => $order,
                'customer' => $customer,
                'currency' => $currency,
                'orderStatus' => $orderStatus,
            ));
        } catch (Exception $e) {
            $errorMessage = '[PrestaShop error]: "' . $e->getMessage()
                . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
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
