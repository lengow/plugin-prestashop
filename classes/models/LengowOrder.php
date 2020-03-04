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
 * Lengow Order Class
 */
class LengowOrder extends Order
{
    /**
     * @var integer order log import type
     */
    const TYPE_LOG_IMPORT = 1;

    /**
     * @var integer order log send type
     */
    const TYPE_LOG_SEND = 2;

    /**
     * @var integer order process state for order imported
     */
    const PROCESS_STATE_IMPORT = 1;

    /**
     * @var integer order process state for order finished
     */
    const PROCESS_STATE_FINISH = 2;

    /**
     * @var string order state accepted
     */
    const STATE_ACCEPTED = 'accepted';

    /**
     * @var string order state waiting_shipment
     */
    const STATE_WAITING_SHIPMENT = 'waiting_shipment';

    /**
     * @var string order state shipped
     */
    const STATE_SHIPPED = 'shipped';

    /**
     * @var string order state closed
     */
    const STATE_CLOSED = 'closed';

    /**
     * @var string order state refused
     */
    const STATE_REFUSED = 'refused';

    /**
     * @var string order state canceled
     */
    const STATE_CANCELED = 'canceled';

    /**
     * @var string order state refunded
     */
    const STATE_REFUNDED = 'refunded';

    /**
     * @var string Lengow order record id
     */
    public $lengowId;

    /**
     * @var integer Prestashop shop ID
     */
    public $lengowIdShop;

    /**
     * @var integer Lengow flux id
     */
    public $lengowIdFlux;

    /**
     * @var integer id of the delivery address
     */
    public $lengowDeliveryAddressId;

    /**
     * @var string ISO code for country
     */
    public $lengowDeliveryCountryIso;

    /**
     * @var string Lengow order id
     */
    public $lengowMarketplaceSku;

    /**
     * @var string marketplace's code
     */
    public $lengowMarketplaceName;

    /**
     * @var string marketplace's label
     */
    public $lengowMarketplaceLabel;

    /**
     * @var string current Lengow state
     */
    public $lengowState;

    /**
     * @var integer Lengow process state (0 => error, 1 => imported, 2 => finished)
     */
    public $lengowProcessState;

    /**
     * @var string marketplace order date
     */
    public $lengowOrderDate;

    /**
     * @var integer number of items
     */
    public $lengowOrderItem;

    /**
     * @var string order currency
     */
    public $lengowCurrency;

    /**
     * @var float total paid on marketplace
     */
    public $lengowTotalPaid;

    /**
     * @var float commission on marketplace
     */
    public $lengowCommission;

    /**
     * @var string the name of the customer
     */
    public $lengowCustomerName;

    /**
     * @var string email of the customer
     */
    public $lengowCustomerEmail;

    /**
     * @var string carrier from marketplace
     */
    public $lengowCarrier;

    /**
     * @var string carrier Method from marketplace
     */
    public $lengowMethod;

    /**
     * @var string tracking
     */
    public $lengowTracking;

    /**
     * @var string id relay
     */
    public $lengowIdRelay;

    /**
     * @var boolean order shipped by marketplace
     */
    public $lengowSentMarketplace;

    /**
     * @var boolean order is reimported (ready to be reimported)
     */
    public $lengowIsReimported;

    /**
     * @var string message
     */
    public $lengowMessage;

    /**
     * @var string creation order date
     */
    public $lengowDateAdd;

    /**
     * @var string extra information (json node form import)
     */
    public $lengowExtra;

    /**
     * Construct a Lengow order based on Prestashop order
     *
     * @param integer|null $id Lengow order id
     * @param integer|null $idLang Prestashop id lang
     */
    public function __construct($id = null, $idLang = null)
    {
        parent::__construct($id, $idLang);
        $this->loadLengowFields();
    }

    /**
     * Load information from lengow_orders table
     *
     * @return boolean
     */
    protected function loadLengowFields()
    {
        $query = 'SELECT
            lo.`id`,
            lo.`id_shop`,
            lo.`id_flux`,
            lo.`delivery_address_id`,
            lo.`delivery_country_iso`,
            lo.`marketplace_sku`,
            lo.`marketplace_name`,
            lo.`marketplace_label`,
            lo.`order_lengow_state`,
            lo.`order_process_state`,
            lo.`order_date`,
            lo.`order_item`,
            lo.`currency`,
            lo.`total_paid`,
            lo.`commission`,
            lo.`customer_name`,
            lo.`customer_email`,
            lo.`carrier`,
            lo.`method`,
            lo.`tracking`,
            lo.`id_relay`,
            lo.`sent_marketplace`,
            lo.`is_reimported`,
            lo.`message`,
            lo.`date_add`,
            lo.`extra`
            FROM `' . _DB_PREFIX_ . 'lengow_orders` lo
            WHERE lo.id_order = \'' . (int)$this->id . '\'
        ';
        if ($result = Db::getInstance()->getRow($query)) {
            $this->lengowId = $result['id'];
            $this->lengowIdShop = (int)$result['id_shop'];
            $this->lengowIdFlux = $result['id_flux'];
            $this->lengowDeliveryAddressId = (int)$result['delivery_address_id'];
            $this->lengowDeliveryCountryIso = $result['delivery_country_iso'];
            $this->lengowMarketplaceSku = $result['marketplace_sku'];
            $this->lengowMarketplaceName = $result['marketplace_name'];
            $this->lengowMarketplaceLabel = $result['marketplace_label'];
            $this->lengowState = $result['order_lengow_state'];
            $this->lengowProcessState = (int)$result['order_process_state'];
            $this->lengowOrderDate = $result['order_date'];
            $this->lengowOrderItem = (int)$result['order_item'];
            $this->lengowCurrency = $result['currency'];
            $this->lengowTotalPaid = $result['total_paid'];
            $this->lengowCommission = $result['commission'];
            $this->lengowCustomerName = $result['customer_name'];
            $this->lengowCustomerEmail = $result['customer_email'];
            $this->lengowCarrier = $result['carrier'];
            $this->lengowMethod = $result['method'];
            $this->lengowTracking = $result['tracking'];
            $this->lengowIdRelay = $result['id_relay'];
            $this->lengowSentMarketplace = (bool)$result['sent_marketplace'];
            $this->lengowIsReimported = (bool)$result['is_reimported'];
            $this->lengowMessage = $result['message'];
            $this->lengowDateAdd = $result['date_add'];
            $this->lengowExtra = $result['extra'];
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Prestashop order id
     *
     * @param string $marketplaceSku Lengow order id
     * @param string $marketplace marketplace name
     * @param integer $deliveryAddressId Lengow delivery address id
     * @param string $marketplaceLegacy old marketplace name for v2 compatibility
     *
     * @return integer|false
     */
    public static function getOrderIdFromLengowOrders(
        $marketplaceSku,
        $marketplace,
        $deliveryAddressId,
        $marketplaceLegacy
    ) {
        // v2 compatibility
        $in = ($marketplaceLegacy === null
            ? '\'' . pSQL(Tools::strtolower($marketplace)) . '\''
            : '\'' . pSQL(Tools::strtolower($marketplace)) . '\', \''
            . pSQL(Tools::strtolower($marketplaceLegacy)) . '\''
        );
        $query = 'SELECT `id_order`, `delivery_address_id`,`id_flux` 
            FROM `' . _DB_PREFIX_ . 'lengow_orders`
            WHERE `marketplace_sku` = \'' . pSQL($marketplaceSku) . '\'
            AND `marketplace_name` IN (' . $in . ')
            AND `order_process_state` != 0';
        try {
            $results = Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        if (empty($results)) {
            return false;
        }
        foreach ($results as $result) {
            if ($result['delivery_address_id'] === null && $result['id_flux'] !== null) {
                return $result['id_order'];
            } elseif ((int)$result['delivery_address_id'] === $deliveryAddressId) {
                return $result['id_order'];
            }
        }
        return false;
    }

    /**
     * Get ID record from lengow orders table
     *
     * @param string $marketplaceSku Lengow order id
     * @param string $marketplace marketplace name
     * @param integer $deliveryAddressId Lengow delivery address id
     *
     * @return integer|false
     */
    public static function getIdFromLengowOrders($marketplaceSku, $marketplace, $deliveryAddressId)
    {
        $query = 'SELECT `id` FROM `' . _DB_PREFIX_ . 'lengow_orders`
            WHERE `marketplace_sku` = \'' . pSQL($marketplaceSku) . '\'
            AND `marketplace_name` = \'' . pSQL($marketplace) . '\'
            AND `delivery_address_id` = \'' . (int)$deliveryAddressId . '\'';
        $result = Db::getInstance()->getRow($query);
        if ($result) {
            return (int)$result['id'];
        }
        return false;
    }

    /**
     * Check if a lengow order
     *
     * @param integer $idOrder Prestashop order id
     *
     * @return boolean
     */
    public static function isFromLengow($idOrder)
    {
        $query = 'SELECT `marketplace_sku`
            FROM `' . _DB_PREFIX_ . 'lengow_orders`
            WHERE `id_order` = \'' . (int)$idOrder . '\'';
        try {
            $result = Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        if (empty($result) || $result[0]['marketplace_sku'] == '') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get Id from Lengow delivery address id
     *
     * @param integer $idOrder Prestashop order id
     * @param integer $deliveryAddressId Lengow delivery address id
     *
     * @return integer|false
     */
    public static function getIdFromLengowDeliveryAddress($idOrder, $deliveryAddressId)
    {
        $query = 'SELECT `id` FROM `' . _DB_PREFIX_ . 'lengow_orders`
            WHERE `id_order` = \'' . (int)$idOrder . '\'
            AND `delivery_address_id` = \'' . (int)$deliveryAddressId . '\'';
        $result = Db::getInstance()->getRow($query);
        if ($result) {
            return $result['id'];
        }
        return false;
    }

    /**
     * Retrieves all the order ids for an order number Lengow
     *
     * @param string $marketplaceSku Lengow order id
     * @param string $marketplace marketplace name
     *
     * @return array
     */
    public static function getAllOrderIdsFromLengowOrder($marketplaceSku, $marketplace)
    {
        $query = 'SELECT `id_order` FROM `' . _DB_PREFIX_ . 'lengow_orders`
            WHERE `marketplace_sku` = \'' . pSQL($marketplaceSku) . '\'
            AND `marketplace_name` = \'' . pSQL(Tools::strtolower($marketplace)) . '\'
            AND `order_process_state` != \'0\'';
        try {
            return Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return array();
        }
    }

    /**
     * Update order Lengow
     *
     * @param integer $id Id of the record
     * @param array $params Fields update
     *
     * @return boolean
     */
    public static function updateOrderLengow($id, $params)
    {
        if (_PS_VERSION_ < '1.5') {
            try {
                return Db::getInstance()->autoExecute(
                    _DB_PREFIX_ . 'lengow_orders',
                    $params,
                    'UPDATE',
                    '`id` = \'' . (int)$id . '\''
                );
            } catch (PrestaShopDatabaseException $e) {
                return false;
            }
        } else {
            return Db::getInstance()->update('lengow_orders', $params, '`id` = \'' . (int)$id . '\'');
        }
    }

    /**
     * Update order status
     *
     * @param string $orderStateLengow marketplace state
     * @param mixed $packageData package data
     *
     * @throws Exception
     *
     * @return string|false
     */
    public function updateState($orderStateLengow, $packageData)
    {
        $orderProcessState = self::getOrderProcessState($orderStateLengow);
        $trackingNumber = !empty($packageData->delivery->trackings)
            ? (string)$packageData->delivery->trackings[0]->number
            : null;
        // update Lengow order if necessary
        $params = array();
        if ($this->lengowState !== $orderStateLengow) {
            $params['order_lengow_state'] = pSQL($orderStateLengow);
            $params['tracking'] = pSQL($trackingNumber);
        }
        if ($orderProcessState === self::PROCESS_STATE_FINISH) {
            // finish actions and order log if lengow order is shipped, closed, cancel or refunded
            LengowAction::finishAllActions((int)$this->id);
            self::finishOrderLogs((int)$this->lengowId, 'send');
            if ($this->lengowProcessState !== $orderProcessState) {
                $params['order_process_state'] = (int)$orderProcessState;
            }
        }
        if (!empty($params)) {
            self::updateOrderLengow((int)$this->lengowId, $params);
        }
        // get prestashop equivalent state id to Lengow API state
        $idOrderState = LengowMain::getOrderState($orderStateLengow);
        // if state is different between API and Prestashop
        if ((int)$this->getCurrentState() !== $idOrderState) {
            // change state process to shipped
            if ((int)$this->getCurrentState() === LengowMain::getOrderState(self::STATE_ACCEPTED)
                && ($orderStateLengow === self::STATE_SHIPPED || $orderStateLengow === self::STATE_CLOSED)
            ) {
                // create a new order history
                $history = new OrderHistory();
                $history->id_order = $this->id;
                if (_PS_VERSION_ < '1.5') {
                    $history->changeIdOrderState(LengowMain::getOrderState(self::STATE_SHIPPED), $this->id);
                } else {
                    $history->changeIdOrderState(LengowMain::getOrderState(self::STATE_SHIPPED), $this, true);
                }
                $history->validateFields();
                $history->add();
                if ($trackingNumber !== null) {
                    $this->shipping_number = $trackingNumber;
                    $this->validateFields();
                    $this->update();
                }
                return 'Shipped';
            } elseif (((int)$this->getCurrentState() === LengowMain::getOrderState(self::STATE_ACCEPTED)
                    || (int)$this->getCurrentState() === LengowMain::getOrderState(self::STATE_SHIPPED)
                ) && ($orderStateLengow === self::STATE_CANCELED || $orderStateLengow === self::STATE_REFUSED)
            ) {
                // create a new order history
                $history = new OrderHistory();
                $history->id_order = $this->id;
                if (_PS_VERSION_ < '1.5') {
                    $history->changeIdOrderState(LengowMain::getOrderState(self::STATE_CANCELED), $this->id);
                } else {
                    $history->changeIdOrderState(LengowMain::getOrderState(self::STATE_CANCELED), $this, true);
                }
                $history->validateFields();
                $history->add();
                return 'Canceled';
            }
        }
        return false;
    }

    /**
     * Cancel and re-import order
     *
     * @return integer|false
     */
    public function cancelAndreImportOrder()
    {
        if (!$this->isReimported()) {
            return false;
        }
        $import = new LengowImport(
            array(
                'id_order_lengow' => $this->lengowId,
                'marketplace_sku' => $this->lengowMarketplaceSku,
                'marketplace_name' => $this->lengowMarketplaceName,
                'delivery_address_id' => $this->lengowDeliveryAddressId,
                'shop_id' => $this->lengowIdShop,
            )
        );
        $result = $import->exec();
        if ((isset($result['order_id']) && (int)$result['order_id'] !== (int)$this->id)
            && (isset($result['order_new']) && $result['order_new'])
        ) {
            $this->setStateToError();
            return (int)$result['order_id'];
        }
        return false;
    }

    /**
     * Mark order as is_reimported in lengow_orders table
     *
     * @return boolean
     */
    public function isReimported()
    {
        $query = 'UPDATE ' . _DB_PREFIX_ . 'lengow_orders
            SET `is_reimported` = 1
            WHERE `id_order`= \'' . (int)$this->id . '\'';
        return DB::getInstance()->execute($query);
    }

    /**
     * Sets order state to Lengow technical error
     */
    public function setStateToError()
    {
        $idErrorLengowState = LengowMain::getLengowErrorStateId();
        // update order to Lengow error state if not already updated
        if ($idErrorLengowState && (int)$this->getCurrentState() !== $idErrorLengowState) {
            $this->setCurrentState($idErrorLengowState, Context::getContext()->employee->id);
        }
    }

    /**
     * Get all unset orders
     *
     * @return array|false
     */
    public static function getUnsentOrders()
    {
        $date = date('Y-m-d H:i:s', strtotime('-5 days', time()));
        $sql = 'SELECT lo.`id`, oh.`id_order_state`, oh.`id_order`
            FROM ' . _DB_PREFIX_ . 'lengow_orders lo
            INNER JOIN ' . _DB_PREFIX_ . 'order_history oh ON (oh.id_order = lo.id_order)
            WHERE lo.`order_process_state` = ' . (int)self::PROCESS_STATE_IMPORT
            . ' AND oh.`id_order_state` IN ('
            . LengowMain::getOrderState(self::STATE_SHIPPED) . ',' . LengowMain::getOrderState(self::STATE_CANCELED)
            . ') AND oh.`date_add` >= "' . $date . '"';
        try {
            $results = Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $results = false;
        }
        if ($results) {
            $unsentOrders = array();
            foreach ($results as $result) {
                $activeAction = LengowAction::getActiveActionByOrderId($result['id_order']);
                $orderLogs = self::getOrderLogs($result['id'], 'send', false);
                if (!$activeAction && empty($orderLogs) && !array_key_exists($result['id_order'], $unsentOrders)) {
                    $action = (int)$result['id_order_state'] === LengowMain::getOrderState(self::STATE_CANCELED)
                        ? LengowAction::TYPE_CANCEL
                        : LengowAction::TYPE_SHIP;
                    $unsentOrders[$result['id_order']] = $action;
                }
            }
            if (!empty($unsentOrders)) {
                return $unsentOrders;
            }
        }
        return false;
    }

    /**
     * Synchronize order with Lengow API
     *
     * @param LengowConnector|null $connector Lengow connector instance
     * @param boolean $logOutput see log or not
     *
     * @return boolean
     */
    public function synchronizeOrder($connector = null, $logOutput = false)
    {
        list($accountId, $accessToken, $secretToken) = LengowConfiguration::getAccessIds();
        // get connector
        if ($connector === null) {
            if (LengowConnector::isValidAuth($logOutput)) {
                $connector = new LengowConnector($accessToken, $secretToken);
            } else {
                return false;
            }
        }
        // get all order ids for a Lengow order
        $orderIds = self::getAllOrderIdsFromLengowOrder($this->lengowMarketplaceSku, $this->lengowMarketplaceName);
        if (!empty($orderIds)) {
            $prestaIds = array();
            foreach ($orderIds as $orderId) {
                $prestaIds[] = $orderId['id_order'];
            }
            // compatibility V2
            if ($this->lengowIdFlux !== null) {
                $this->checkAndChangeMarketplaceName($connector, $logOutput);
            }
            try {
                $result = $connector->patch(
                    LengowConnector::API_ORDER_MOI,
                    array(
                        'account_id' => $accountId,
                        'marketplace_order_id' => $this->lengowMarketplaceSku,
                        'marketplace' => $this->lengowMarketplaceName,
                        'merchant_order_id' => $prestaIds,
                    ),
                    LengowConnector::FORMAT_JSON,
                    '',
                    $logOutput
                );
            } catch (Exception $e) {
                $message = LengowMain::decodeLogMessage($e->getMessage(), LengowTranslation::DEFAULT_ISO_CODE);
                $error = LengowMain::setLogMessage(
                    'log.connector.error_api',
                    array(
                        'error_code' => $e->getCode(),
                        'error_message' => $message,
                    )
                );
                LengowMain::log(LengowLog::CODE_CONNECTOR, $error, $logOutput);
                return false;
            }
            if ($result === null
                || (isset($result['detail']) && $result['detail'] === 'Pas trouvé.')
                || isset($result['error'])
            ) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Check and change the name of the marketplace for v3 compatibility
     *
     * @param LengowConnector|null $connector Lengow connector instance
     * @param boolean $logOutput see log or not
     *
     * @return boolean
     */
    public function checkAndChangeMarketplaceName($connector = null, $logOutput = false)
    {
        list($accountId, $accessToken, $secretToken) = LengowConfiguration::getAccessIds();
        // get connector
        if ($connector === null) {
            if (LengowConnector::isValidAuth($logOutput)) {
                $connector = new LengowConnector($accessToken, $secretToken);
            } else {
                return false;
            }
        }
        try {
            $results = $connector->get(
                LengowConnector::API_ORDER,
                array(
                    'marketplace_order_id' => $this->lengowMarketplaceSku,
                    'marketplace' => $this->lengowMarketplaceName,
                    'account_id' => $accountId,
                ),
                LengowConnector::FORMAT_STREAM,
                '',
                $logOutput
            );
        } catch (Exception $e) {
            $message = LengowMain::decodeLogMessage($e->getMessage(), LengowTranslation::DEFAULT_ISO_CODE);
            $error = LengowMain::setLogMessage(
                'log.connector.error_api',
                array(
                    'error_code' => $e->getCode(),
                    'error_message' => $message,
                )
            );
            LengowMain::log('Connector', $error, $logOutput);
            return false;
        }
        if ($results === null) {
            return false;
        }
        $results = Tools::jsonDecode($results);
        if (isset($results->error)) {
            return false;
        }
        foreach ($results->results as $order) {
            if ($this->lengowMarketplaceName !== (string)$order->marketplace) {
                $update = 'UPDATE ' . _DB_PREFIX_ . 'lengow_orders
                    SET `marketplace_name` = \'' . pSQL(Tools::strtolower((string)$order->marketplace)) . '\'
                    WHERE `id_order` = \'' . (int)$this->id . '\'
                ';
                DB::getInstance()->execute($update);
                $this->loadLengowFields();
            }
        }
        return true;
    }

    /**
     * Get order process state
     *
     * @param string $state state to be matched
     *
     * @return integer
     */
    public static function getOrderProcessState($state)
    {
        switch ($state) {
            case self::STATE_ACCEPTED:
            case self::STATE_WAITING_SHIPMENT:
                return self::PROCESS_STATE_IMPORT;
            case self::STATE_SHIPPED:
            case self::STATE_CLOSED:
            case self::STATE_REFUSED:
            case self::STATE_CANCELED:
            case self::STATE_REFUNDED:
                return self::PROCESS_STATE_FINISH;
            default:
                return false;
        }
    }

    /**
     * Return type value
     *
     * @param string|null $type order log type (import or send)
     *
     * @return integer|null
     */
    public static function getOrderLogType($type = null)
    {
        switch ($type) {
            case 'import':
                $logType = self::TYPE_LOG_IMPORT;
                break;
            case 'send':
                $logType = self::TYPE_LOG_SEND;
                break;
            default:
                $logType = null;
                break;
        }
        return $logType;
    }

    /**
     * Check if an order has an error
     *
     * @param string $marketplaceSku Lengow order id
     * @param integer $deliveryAddressId Lengow delivery address id
     * @param string $type order log type (import or send)
     *
     * @return array|false
     */
    public static function orderIsInError($marketplaceSku, $deliveryAddressId, $type = 'import')
    {
        $logType = self::getOrderLogType($type);
        // check if log already exists for the given order id
        $query = 'SELECT lli.`message`, lli.`date` FROM `' . _DB_PREFIX_ . 'lengow_logs_import` lli
            LEFT JOIN `' . _DB_PREFIX_ . 'lengow_orders` lo ON lli.`id_order_lengow` = lo.`id`
            WHERE lo.`marketplace_sku` = \'' . pSQL($marketplaceSku) . '\'
            AND lo.`delivery_address_id` = \'' . (int)$deliveryAddressId . '\'
            AND lli.`type` = \'' . (int)$logType . '\'
            AND lli.`is_finished` = 0';
        return Db::getInstance()->getRow($query);
    }

    /**
     * Check if log already exists for the given order
     *
     * @param string $idOrderLengow Lengow order id
     * @param string|null $type order log type (import or send)
     * @param boolean|null $finished log finished (true or false)
     *
     * @return array|false
     */
    public static function getOrderLogs($idOrderLengow, $type = null, $finished = null)
    {
        $logType = self::getOrderLogType($type);
        if ($logType !== null) {
            $andType = ' AND `type` = \'' . (int)$logType . '\'';
        } else {
            $andType = '';
        }
        if ($finished !== null) {
            $andFinished = $finished ? ' AND `is_finished` = 1' : ' AND `is_finished` = 0';
        } else {
            $andFinished = '';
        }
        // check if log already exists for the given order id
        $query = 'SELECT `id`, `is_finished`, `message`, `date`, `type` FROM `' . _DB_PREFIX_ . 'lengow_logs_import`
            WHERE `id_order_lengow` = \'' . (int)$idOrderLengow . '\'' . $andType . $andFinished;
        try {
            return Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return array();
        }
    }

    /**
     * Add log information in lengow_logs_import table
     *
     * @param integer $idOrderLengow Lengow order id
     * @param string $message error message
     * @param string $type order log type (import or send)
     * @param integer $finished error is finished
     *
     * @return boolean
     */
    public static function addOrderLog($idOrderLengow, $message = '', $type = 'import', $finished = 0)
    {
        $logType = self::getOrderLogType($type);
        try {
            if (_PS_VERSION_ < '1.5') {
                return Db::getInstance()->autoExecute(
                    _DB_PREFIX_ . 'lengow_logs_import',
                    array(
                        'is_finished' => (int)$finished,
                        'date' => date('Y-m-d H:i:s'),
                        'message' => pSQL($message),
                        'type' => (int)$logType,
                        'id_order_lengow' => (int)$idOrderLengow,
                    ),
                    'INSERT'
                );
            } else {
                return Db::getInstance()->insert(
                    'lengow_logs_import',
                    array(
                        'is_finished' => (int)$finished,
                        'date' => date('Y-m-d H:i:s'),
                        'message' => pSQL($message),
                        'type' => (int)$logType,
                        'id_order_lengow' => (int)$idOrderLengow,
                    )
                );
            }
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
    }

    /**
     * Removes all order logs
     *
     * @param integer $idOrderLengow Lengow order id
     * @param string $type order log type (import or send)
     *
     * @return boolean
     */
    public static function finishOrderLogs($idOrderLengow, $type = 'import')
    {
        $logType = self::getOrderLogType($type);
        $query = 'SELECT `id` FROM `' . _DB_PREFIX_ . 'lengow_logs_import`
            WHERE `id_order_lengow` = \'' . (int)$idOrderLengow . '\'
            AND `type` = \'' . (int)$logType . '\'';
        try {
            $orderLogs = Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        $updateSuccess = 0;
        foreach ($orderLogs as $orderLog) {
            if (_PS_VERSION_ < '1.5') {
                try {
                    $result = Db::getInstance()->autoExecute(
                        _DB_PREFIX_ . 'lengow_logs_import',
                        array('is_finished' => 1),
                        'UPDATE',
                        '`id` = \'' . (int)$orderLog['id'] . '\''
                    );
                } catch (PrestaShopDatabaseException $e) {
                    $result = false;
                }
            } else {
                $result = Db::getInstance()->update(
                    'lengow_logs_import',
                    array('is_finished' => 1),
                    '`id` = \'' . (int)$orderLog['id'] . '\''
                );
            }
            if ($result) {
                $updateSuccess++;
            }
        }
        return count($orderLogs) === $updateSuccess ? true : false;
    }

    /**
     * Get all order errors not yet sent by email
     *
     * @return array
     */
    public static function getAllOrderLogsNotSent()
    {
        try {
            $sqlLogs = 'SELECT lo.`marketplace_sku`, lli.`message`, lli.`id`
                FROM `' . _DB_PREFIX_ . 'lengow_logs_import` lli
                INNER JOIN `' . _DB_PREFIX_ . 'lengow_orders` lo 
                ON lli.`id_order_lengow` = lo.`id`
                WHERE lli.`is_finished` = 0 AND lli.`mail` = 0
            ';
            $orderLogs = Db::getInstance()->ExecuteS($sqlLogs);
        } catch (PrestaShopDatabaseException $e) {
            $orderLogs = array();
        }
        return $orderLogs;
    }

    /**
     * Mark log as sent by email
     *
     * @param integer $idOrderLog Lengow order log id
     *
     * @return boolean
     */
    public static function logSent($idOrderLog)
    {
        try {
            if (_PS_VERSION_ < '1.5') {
                return Db::getInstance()->autoExecute(
                    _DB_PREFIX_ . 'lengow_logs_import',
                    array('mail' => 1),
                    'UPDATE',
                    '`id` = \'' . (int)$idOrderLog . '\'',
                    1
                );
            } else {
                return Db::getInstance()->update(
                    'lengow_logs_import',
                    array('mail' => 1),
                    '`id` = \'' . (int)$idOrderLog . '\'',
                    1
                );
            }
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
    }

    /**
     * Find Lengow Order by Lengow order id
     *
     * @param integer $idOrderLengow Lengow order id
     *
     * @return boolean
     */
    public static function find($idOrderLengow)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'lengow_orders` WHERE id = ' . (int)$idOrderLengow;
        return Db::getInstance()->getRow($sql);
    }

    /**
     * Get Order Lines by Prestashop order id
     *
     * @param integer $idOrder Prestashop order id
     *
     * @return array
     */
    public static function findOrderLineIds($idOrder)
    {
        $sql = 'SELECT id_order_line FROM `' . _DB_PREFIX_ . 'lengow_order_line` WHERE id_order = ' . (int)$idOrder;
        try {
            return Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            return array();
        }
    }

    /**
     * Check if order is already imported
     *
     * @param integer $idOrderLengow Lengow order id
     *
     * @return boolean
     */
    public static function isOrderImport($idOrderLengow)
    {
        $sql = 'SELECT id_order FROM `' . _DB_PREFIX_ . 'lengow_orders` WHERE id = ' . (int)$idOrderLengow;
        try {
            $result = Db::getInstance()->ExecuteS($sql);
            return !empty($result) ? true : false;
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
    }

    /**
     * Re Import Order
     *
     * @param integer $idOrderLengow Lengow order id
     *
     * @return array|false
     */
    public static function reImportOrder($idOrderLengow)
    {
        if (self::isOrderImport($idOrderLengow)) {
            Db::getInstance()->Execute(
                'UPDATE `' . _DB_PREFIX_ . 'lengow_orders` SET id_order = NULL WHERE id = ' . (int)$idOrderLengow
            );
            $lengowOrder = self::find($idOrderLengow);
            $import = new LengowImport(
                array(
                    'id_order_lengow' => $idOrderLengow,
                    'marketplace_sku' => $lengowOrder['marketplace_sku'],
                    'marketplace_name' => $lengowOrder['marketplace_name'],
                    'delivery_address_id' => $lengowOrder['delivery_address_id'],
                    'shop_id' => $lengowOrder['id_shop'],
                )
            );
            return $import->exec();
        }
        return false;
    }

    /**
     * Check if can resend action order
     *
     * @return boolean
     */
    public function canReSendOrder()
    {
        $orderActions = LengowAction::getActiveActionByOrderId((int)$this->id);
        if ($orderActions) {
            return false;
        }
        if ($this->lengowProcessState !== self::PROCESS_STATE_FINISH &&
            ((int)$this->getCurrentState() === LengowMain::getOrderState(self::STATE_SHIPPED)
                || (int)$this->getCurrentState() === LengowMain::getOrderState(self::STATE_CANCELED)
            )
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if can add tracking
     *
     * @return boolean
     */
    public function canAddTracking()
    {
        if (_PS_VERSION_ < '1.5' && $this->shipping_number === '') {
            return true;
        }
        return false;
    }

    /**
     * Re Send Order
     *
     * @param integer $idOrderLengow Lengow order id
     *
     * @return boolean
     */
    public static function reSendOrder($idOrderLengow)
    {
        if (self::isOrderImport($idOrderLengow)) {
            $lengowOrder = self::find($idOrderLengow);
            if ((int)$lengowOrder['id_order'] > 0) {
                $order = new LengowOrder($lengowOrder['id_order']);
                $action = LengowAction::getLastOrderActionType($lengowOrder['id_order']);
                if (!$action) {
                    $action = (int)$order->getCurrentState() === LengowMain::getOrderState(self::STATE_CANCELED)
                        ? LengowAction::TYPE_CANCEL
                        : LengowAction::TYPE_SHIP;
                }
                return $order->callAction($action);
            }
            return false;
        }
        return false;
    }

    /**
     * Send Order action
     *
     * @param string $action Lengow Actions type (ship or cancel)
     *
     * @return boolean
     */
    public function callAction($action)
    {
        $success = true;
        LengowMain::log(
            'API-OrderAction',
            LengowMain::setLogMessage(
                'log.order_action.try_to_send_action',
                array(
                    'action' => $action,
                    'order_id' => $this->id,
                )
            ),
            false,
            $this->lengowMarketplaceSku
        );
        if ((int)$this->id === 0) {
            LengowMain::log(
                'API-OrderAction',
                LengowMain::setLogMessage('log.order_action.can_not_load_order'),
                true
            );
            $success = false;
        }
        if ($success) {
            // finish all order logs send
            self::finishOrderLogs($this->lengowId, 'send');
            try {
                // compatibility V2
                if ($this->lengowIdFlux !== null) {
                    $this->checkAndChangeMarketplaceName();
                }
                $marketplace = LengowMain::getMarketplaceSingleton($this->lengowMarketplaceName);
                if ($marketplace->containOrderLine($action)) {
                    $orderLineCollection = self::findOrderLineIds($this->id);
                    // compatibility V2 and security
                    if (empty($orderLineCollection)) {
                        $orderLineCollection = $this->getOrderLineByApi();
                    }
                    if (!$orderLineCollection) {
                        throw new LengowException(
                            LengowMain::setLogMessage('lengow_log.exception.order_line_required')
                        );
                    }
                    $results = array();
                    foreach ($orderLineCollection as $row) {
                        $results[] = $marketplace->callAction($action, $this, $row['id_order_line']);
                    }
                    $success = !in_array(false, $results);
                } else {
                    $success = $marketplace->callAction($action, $this);
                }
            } catch (LengowException $e) {
                $errorMessage = $e->getMessage();
            } catch (Exception $e) {
                $errorMessage = '[Prestashop error] "' . $e->getMessage()
                    . '" ' . $e->getFile() . ' | ' . $e->getLine();
            }
            if (isset($errorMessage)) {
                if ($this->lengowProcessState !== self::PROCESS_STATE_FINISH) {
                    self::addOrderLog($this->lengowId, $errorMessage, 'send');
                }
                $decodedMessage = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
                LengowMain::log(
                    'API-OrderAction',
                    LengowMain::setLogMessage(
                        'log.order_action.call_action_failed',
                        array('decoded_message' => $decodedMessage)
                    ),
                    false,
                    $this->lengowMarketplaceSku
                );
                $success = false;
            }
        }
        if ($success) {
            $message = LengowMain::setLogMessage(
                'log.order_action.action_send',
                array(
                    'action' => $action,
                    'order_id' => $this->id,
                )
            );
        } else {
            $message = LengowMain::setLogMessage(
                'log.order_action.action_not_send',
                array(
                    'action' => $action,
                    'order_id' => $this->id,
                )
            );
        }
        LengowMain::log('API-OrderAction', $message, false, $this->lengowMarketplaceSku);
        return $success;
    }

    /**
     * Get order line by API
     *
     * @return array|false
     */
    public function getOrderLineByApi()
    {
        $orderLines = array();
        $results = LengowConnector::queryApi(
            LengowConnector::GET,
            LengowConnector::API_ORDER,
            array(
                'marketplace_order_id' => $this->lengowMarketplaceSku,
                'marketplace' => $this->lengowMarketplaceName,
            )
        );
        if (isset($results->count) && (int)$results->count === 0) {
            return false;
        }
        $orderData = $results->results[0];
        foreach ($orderData->packages as $package) {
            $productLines = array();
            foreach ($package->cart as $product) {
                $productLines[] = array('id_order_line' => (string)$product->marketplace_order_line_id);
            }
            if ($this->lengowDeliveryAddressId == 0) {
                return !empty($productLines) ? $productLines : false;
            } else {
                $orderLines[(int)$package->delivery->id] = $productLines;
            }
        }
        $return = $orderLines[$this->lengowDeliveryAddressId];
        return !empty($return) ? $return : false;
    }

    /**
     * Get Total Order By Statuses
     *
     * @param string $status Lengow order state
     *
     * @return integer
     */
    public static function getTotalOrderByStatus($status)
    {
        $sql = 'SELECT COUNT(*) as total FROM `' . _DB_PREFIX_ . 'lengow_orders`
        WHERE order_lengow_state = "' . pSQL($status) . '"';
        $row = Db::getInstance()->getRow($sql);
        return (int)$row['total'];
    }
}
