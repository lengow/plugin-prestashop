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
 * Lengow Order Class
 */
class LengowOrder extends Order
{
    /**
    * string Version
    */
    const VERSION = '1.0.0';

    /**
    * integer order log import type
    */
    const TYPE_LOG_IMPORT = 1;

    /**
    * integer order log send type
    */
    const TYPE_LOG_SEND = 2;

    /**
    * integer order process state for order imported
    */
    const PROCESS_STATE_IMPORT = 1;

    /**
    * integer order process state for order finished
    */
    const PROCESS_STATE_FINISH = 2;

    /**
     * @var string Lengow order record id
     */
    public $lengowId;

    /**
     * @var string Lengow order id
     */
    public $lengowMarketplaceSku;

    /**
     * @var string Marketplace's name
     */
    public $lengowMarketplaceName;

    /**
     * @var string Message
     */
    public $lengowMessage;

    /**
     * @var integer Shop ID
     */
    public $lengowIdShop;

    /**
     * @var integer Lengow flux id
     */
    public $lengowIdFlux;

    /**
     * @var decimal Total paid on marketplace
     */
    public $lengowTotalPaid;

    /**
    * @var string Carrier from marketplace
    */
    public $lengowCarrier;

    /**
    * @var string Carrier Method from marketplace
    */
    public $lengowMethod;

    /**
    * @var string Tracking
    */
    public $lengowTracking;

    /**
    * @var string Id relay
    */
    public $lengowIdRelay;

    /**
    * @var boolean Shipped by markeplace
    */
    public $lengowSentMarketplace;

    /**
    * @var string Extra information (json node form import)
    */
    public $lengowExtra;

    /**
     * @var boolean order is reimported (ready to be reimported)
     */
    public $lengowIsReimported;

    /**
     * @var integer lengow process state (0 => error, 1 => imported, 2 => finished)
     */
    public $lengowProcessState;

    /**
     * @var date marketplace order date
     */
    public $lengowOrderDate;

    /**
     * @var integer id of the delivery address
     */
    public $lengowDeliveryAddressId;

    /**
     * @var string ISO code for country
     */
    public $lengowDeliveryCountryIso;

    /**
     * @var string the name of the customer
     */
    public $lengowCustomerName;

    /**
     * @var string email of the customer
     */
    public $lengowCustomerEmail;

    /**
     * @var string current lengow state
     */
    public $lengowState;

    /**
     * @var integer number of items
     */
    public $lengowOrderItem;

    /**
    * Construct a Lengow order based on Prestashop order.
    *
    * @param integer $id     Lengow order id
    * @param integer $idLang id lang
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
            lo.`marketplace_sku`,
            lo.`id_shop`,
            lo.`id_flux`,
            lo.`marketplace_name`,
            lo.`message`,
            lo.`total_paid`,
            lo.`carrier`,
            lo.`method`,
            lo.`tracking`,
            lo.`id_relay`,
            lo.`sent_marketplace`,
            lo.`extra`,
            lo.`is_reimported`,
            lo.`order_process_state`,
            lo.`order_date`,
            lo.`delivery_address_id`,
            lo.`delivery_country_iso`,
            lo.`customer_name`,
            lo.`customer_email`,
            lo.`order_lengow_state`,
            lo.`order_item`
            FROM `'._DB_PREFIX_.'lengow_orders` lo
            WHERE lo.id_order = \''.(int)$this->id.'\'
        ';
        if ($result = Db::getInstance()->getRow($query)) {
            $this->lengowId                 = $result['id'];
            $this->lengowMarketplaceSku     = $result['marketplace_sku'];
            $this->lengowIdShop             = (int)$result['id_shop'];
            $this->lengowIdFlux             = $result['id_flux'];
            $this->lengowMarketplaceName    = $result['marketplace_name'];
            $this->lengowMessage            = $result['message'];
            $this->lengowTotalPaid          = $result['total_paid'];
            $this->lengowCarrier            = $result['carrier'];
            $this->lengowMethod             = $result['method'];
            $this->lengowTracking           = $result['tracking'];
            $this->lengowIdRelay            = $result['id_relay'];
            $this->lengowSentMarketplace    = (bool)$result['sent_marketplace'];
            $this->lengowExtra              = $result['extra'];
            $this->lengowIsReimported       = (bool)$result['is_reimported'];
            $this->lengowProcessState       = (int)$result['order_process_state'];
            $this->lengowOrderDate          = $result['order_date'];
            $this->lengowDeliveryAddressId  = (int)$result['delivery_address_id'];
            $this->lengowDeliveryCountryIso = $result['delivery_country_iso'];
            $this->lengowCustomerName       = $result['customer_name'];
            $this->lengowCustomerEmail      = $result['customer_email'];
            $this->lengowState              = $result['order_lengow_state'];
            $this->lengowOrderItem          = (int)$result['order_item'];
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Prestashop order id
     *
     * @param string  $marketplaceSku    Lengow order id
     * @param string  $marketplace       marketplace name
     * @param integer $deliveryAddressId devivery address id
     * @param string  $marketplaceLegacy old marketplace name for v2 compatibility
     *
     * @return mixed
     */
    public static function getOrderIdFromLengowOrders(
        $marketplaceSku,
        $marketplace,
        $deliveryAddressId,
        $marketplaceLegacy
    ) {
        // V2 compatibility
        $in = (is_null($marketplaceLegacy)
            ? '\''.pSQL(Tools::strtolower($marketplace)).'\''
            : '\''.pSQL(Tools::strtolower($marketplace)).'\', \''.pSQL(Tools::strtolower($marketplaceLegacy)).'\''
        );
        $query = 'SELECT `id_order`, `delivery_address_id`,`id_flux` 
            FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `marketplace_sku` = \''.pSQL($marketplaceSku).'\'
            AND `marketplace_name` IN ('.$in.')
            AND `order_process_state` != 0';
        $results = Db::getInstance()->executeS($query);
        if (count($results) == 0) {
            return false;
        }
        foreach ($results as $result) {
            if (is_null($result['delivery_address_id']) && !is_null($result['id_flux'])) {
                return $result['id_order'];
            } elseif ($result['delivery_address_id'] == $deliveryAddressId) {
                return $result['id_order'];
            }
        }
        return false;
    }

    /**
     * Get ID record from lengow orders table
     *
     * @param string  $marketplaceSku    lengow order id
     * @param integer $deliveryAddressId delivery address id
     *
     * @return mixed
     */
    public static function getIdFromLengowOrders($marketplaceSku, $deliveryAddressId)
    {
        $query = 'SELECT `id` FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `marketplace_sku` = \''.pSQL($marketplaceSku).'\'
            AND `delivery_address_id` = \''.(int)$deliveryAddressId.'\'';
        $result = Db::getInstance()->getRow($query);
        if ($result) {
            return (int)$result['id'];
        }
        return false;
    }

    /**
     * Check if a lengow order
     *
     * @param integer $idOrder prestashop order id
     *
     * @return boolean
     */
    public static function isFromLengow($idOrder)
    {
        $query = 'SELECT `marketplace_sku`
            FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `id_order` = \''.(int)$idOrder.'\'';
        $result = Db::getInstance()->executeS($query);
        if (empty($result) || $result[0]['marketplace_sku'] == '') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get Id from Lengow delivery address id
     *
     * @param integer $idOrder          Prestashop order id
     * @param integer $deliveryAddressId Lengow delivery address id
     *
     * @return mixed
     */
    public static function getIdFromLengowDeliveryAddress($idOrder, $deliveryAddressId)
    {
        $query = 'SELECT `id` FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `id_order` = \''.(int)$idOrder.'\'
            AND `delivery_address_id` = \''.pSQL((int)$deliveryAddressId).'\'';
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
     * @param string $marketplace     marketplace name
     *
     * @return array
     */
    public static function getAllOrderIdsFromLengowOrder($marketplaceSku, $marketplace)
    {
        $query = 'SELECT `id_order` FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `marketplace_sku` = \''.pSQL($marketplaceSku).'\'
            AND `marketplace_name` = \''.pSQL(Tools::strtolower($marketplace)).'\'
            AND `order_process_state` != \'0\'';
        return Db::getInstance()->executeS($query);
    }

    /**
     * Retrieves all the order lines of a order PrestaShop
     *
     * @param integer $idOrder prestashop order id
     *
     * @return array
     */
    public static function getAllOrderLinesFromLengowOrder($idOrder)
    {
        $query = 'SELECT `id_order_line` FROM `'._DB_PREFIX_.'lengow_order_line`
            WHERE `id_order` = \''.(int)$idOrder.'\'';
        return Db::getInstance()->executeS($query);
    }

    /**
     * Update order Lengow
     *
     * @param integer $id     Id of the record
     * @param array   $params Fields update
     *
     * @return bool true if order has been updated
     */
    public static function updateOrderLengow($id, $params)
    {
        if (_PS_VERSION_ < '1.5') {
            return Db::getInstance()->autoExecute(
                _DB_PREFIX_.'lengow_orders',
                $params,
                'UPDATE',
                '`id` = \''.(int)$id.'\''
            );
        } else {
            return Db::getInstance()->update(
                'lengow_orders',
                $params,
                '`id` = \''.(int)$id.'\''
            );
        }
    }

    /**
     * Update order status
     *
     * @param string $orderStateLengow marketplace state
     * @param mixed  $orderData         order data
     * @param mixed  $packageData       package data
     *
     * @return mixed (Shipped, Canceled or false)
     */
    public function updateState($orderStateLengow, $orderData, $packageData)
    {
        $orderProcessState = self::getOrderProcessState($orderStateLengow);
        $trackingNumber = (
            count($packageData->delivery->trackings) > 0 ? (string)$packageData->delivery->trackings[0]->number : null
        );
        // Update Lengow order if necessary
        $params = array();
        if ($this->lengowState != $orderStateLengow) {
            $params['order_lengow_state'] = pSQL($orderStateLengow);
            $params['extra'] = pSQL(Tools::jsonEncode($orderData));
            $params['tracking'] = pSQL($trackingNumber);
        }
        if ($orderProcessState == self::PROCESS_STATE_FINISH) {
            // Finish actions if lengow order is shipped, closed or cancel
            LengowAction::finishAllActions((int)$this->id);
            if ((int)$this->lengowProcessState != $orderProcessState) {
                $params['order_process_state'] = (int)$orderProcessState;
            }
        }
        if (count($params) > 0) {
            self::updateOrderLengow((int)$this->lengowId, $params);
        }
        // get prestashop equivalent state id to Lengow API state
        $idOrderState = LengowMain::getOrderState($orderStateLengow);
        // if state is different between API and Prestashop
        if ($this->getCurrentState() != $idOrderState) {
            // Change state process to shipped
            if ($this->getCurrentState() == LengowMain::getOrderState('accepted')
                && ($orderStateLengow == 'shipped'|| $orderStateLengow == 'closed')
            ) {
                // create a new order history
                $history = new OrderHistory();
                $history->id_order = $this->id;
                if (_PS_VERSION_ < '1.5') {
                    $history->changeIdOrderState(LengowMain::getOrderState('shipped'), $this->id);
                } else {
                    $history->changeIdOrderState(LengowMain::getOrderState('shipped'), $this, true);
                }
                $history->validateFields();
                $history->add();
                if (!is_null($trackingNumber)) {
                    $this->shipping_number = $trackingNumber;
                    $this->validateFields();
                    $this->update();
                }
                return 'Shipped';
            } elseif (($this->getCurrentState() == LengowMain::getOrderState('accepted')
                    || $this->getCurrentState() == LengowMain::getOrderState('shipped')
                ) && ($orderStateLengow == 'canceled' || $orderStateLengow == 'refused')
            ) {
                // create a new order history
                $history = new OrderHistory();
                $history->id_order = $this->id;
                if (_PS_VERSION_ < '1.5') {
                    $history->changeIdOrderState(LengowMain::getOrderState('canceled'), $this->id);
                } else {
                    $history->changeIdOrderState(LengowMain::getOrderState('canceled'), $this, true);
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
     * @return mixed
     */
    public function cancelAndreImportOrder()
    {
        if (!$this->isReimported()) {
            return false;
        }
        $import = new LengowImport(
            array(
                'id_order_lengow'     => $this->lengowId,
                'marketplace_sku'     => $this->lengowMarketplaceSku,
                'marketplace_name'    => $this->lengowMarketplaceName,
                'delivery_address_id' => $this->lengowDeliveryAddressId,
                'shop_id'             => $this->lengowIdShop,
            )
        );
        $result = $import->exec();
        if ((isset($result['order_id']) && $result['order_id'] != $this->id)
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
        $query = 'UPDATE '._DB_PREFIX_.'lengow_orders
            SET `is_reimported` = 1
            WHERE `id_order`= \''.(int)$this->id.'\'';
        return DB::getInstance()->execute($query);
    }

    /**
     * Sets order state to Lengow technical error
     */
    public function setStateToError()
    {
        $idErrorLengowState = LengowMain::getLengowErrorStateId();
        // update order to Lengow error state if not already updated
        if ($this->getCurrentState() !== $idErrorLengowState) {
            $this->setCurrentState($idErrorLengowState, Context::getContext()->employee->id);
        }
    }

    /**
     * Get unset order by shop
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return mixed
     */
    public static function getUnsentOrderByStore($idShop)
    {
        $results = Db::getInstance()->executeS(
            'SELECT lo.`id`, o.`id_shop`, o.`id_order`, oh.`id_order_state` FROM '._DB_PREFIX_.'lengow_orders lo
            INNER JOIN '._DB_PREFIX_.'orders o ON (o.id_order = lo.id_order)
            INNER JOIN '._DB_PREFIX_.'order_history oh ON (oh.id_order = lo.id_order)
            WHERE o.`id_shop` ='.(int)$idShop
            .' AND lo.`order_process_state` = '.(int)self::PROCESS_STATE_IMPORT
            .' AND oh.`id_order_state` IN ('
            .LengowMain::getOrderState('shipped').','.LengowMain::getOrderState('canceled')
            .')'
        );
        if ($results) {
            $unsentOrders = array();
            foreach ($results as $result) {
                $activeAction = LengowAction::getActiveActionByOrderId($result['id_order']);
                $orderLogs = LengowOrder::getOrderLogs($result['id'], 'send', false);
                if (!$activeAction
                    && count($orderLogs) == 0
                    && !array_key_exists($result['id_order'], $unsentOrders)
                ) {
                    $action = $result['id_order_state'] == LengowMain::getOrderState('canceled') ? 'cancel' : 'ship';
                    $unsentOrders[$result['id_order']] = $action;
                }
            }
            if (count($unsentOrders) > 0) {
                return $unsentOrders;
            }
        }
        return false;
    }

    /**
     * Synchronize order with Lengow API
     *
     * @param LengowConnector $connector Lengow Connector for API calls
     *
     * @return boolean
     */
    public function synchronizeOrder($connector = null)
    {
        $idShop = (_PS_VERSION_ < 1.5 ? null : (int)$this->lengowIdShop);
        // Get connector
        if (is_null($connector)) {
            if (LengowCheck::isValidAuth($idShop)) {
                $connector = new LengowConnector(
                    LengowMain::getAccessToken($idShop),
                    LengowMain::getSecretCustomer($idShop)
                );
            } else {
                return false;
            }
        }
        // Get all order id
        $orderIds = self::getAllOrderIdsFromLengowOrder(
            $this->lengowMarketplaceSku,
            $this->lengowMarketplaceName
        );
        if (count($orderIds) > 0) {
            $prestaIds = array();
            foreach ($orderIds as $orderId) {
                $prestaIds[] = $orderId['id_order'];
            }
            // compatibility V2
            if ($this->lengowIdFlux != null) {
                $this->checkAndChangeMarketplaceName($connector);
            }
            $result = $connector->patch(
                '/v3.0/orders/moi/',
                array(
                    'account_id'           => LengowMain::getIdAccount($idShop),
                    'marketplace_order_id' => $this->lengowMarketplaceSku,
                    'marketplace'          => $this->lengowMarketplaceName,
                    'merchant_order_id'    => $prestaIds
                )
            );
            if (is_null($result)
                || (isset($result['detail']) && $result['detail'] == 'Pas trouvé.')
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
     * @param LengowConnector $connector Lengow Connector for API calls
     *
     * @return boolean
     */
    public function checkAndChangeMarketplaceName($connector = null)
    {
        $idShop = (_PS_VERSION_ < 1.5 ? null : (int)$this->lengowIdShop);
        // get connector
        if (is_null($connector)) {
            if (LengowCheck::isValidAuth($idShop)) {
                $connector = new LengowConnector(
                    LengowMain::getAccessToken($idShop),
                    LengowMain::getSecretCustomer($idShop)
                );
            } else {
                return false;
            }
        }
        $results = $connector->get(
            '/v3.0/orders',
            array(
                'marketplace_order_id' => $this->lengowMarketplaceSku,
                'marketplace'          => $this->lengowMarketplaceName,
                'account_id'           => LengowMain::getIdAccount($idShop)
            ),
            'stream'
        );
        if (is_null($results)) {
            return false;
        }
        $results = Tools::jsonDecode($results);
        if (isset($results->error)) {
            return false;
        }
        foreach ($results->results as $order) {
            if ($this->lengowMarketplaceName != (string)$order->marketplace) {
                $update = 'UPDATE '._DB_PREFIX_.'lengow_orders
                    SET `marketplace_name` = \''.pSQL(Tools::strtolower((string)$order->marketplace)).'\'
                    WHERE `id_order` = \''.(int)$this->id.'\'
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
            case 'accepted':
            case 'waiting_shipment':
                return self::PROCESS_STATE_IMPORT;
            case 'shipped':
            case 'closed':
            case 'refused':
            case 'canceled':
                return self::PROCESS_STATE_FINISH;
            default:
                return false;
        }
    }

    /**
     * Return type value
     *
     * @param string $type Type (import or send)
     *
     * @return mixed
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
     * @param string  $marketplaceSku    Lengow order id
     * @param integer $deliveryAddressId Id delivery address
     * @param string  $type              Type (import or send)
     *
     * @return mixed
     */
    public static function orderIsInError($marketplaceSku, $deliveryAddressId, $type = 'import')
    {
        $logType = self::getOrderLogType($type);
        // check if log already exists for the given order id
        $query = 'SELECT lli.`message`, lli.`date` FROM `'._DB_PREFIX_.'lengow_logs_import` lli
            LEFT JOIN `'._DB_PREFIX_.'lengow_orders` lo ON lli.`id_order_lengow` = lo.`id`
            WHERE lo.`marketplace_sku` = \''.pSQL($marketplaceSku).'\'
            AND lo.`delivery_address_id` = \''.(int)$deliveryAddressId.'\'
            AND lli.`type` = \''.(int)$logType.'\'
            AND lli.`is_finished` = 0';
        return Db::getInstance()->getRow($query);
    }

    /**
     * Check if log already exists for the given order
     *
     * @param string  $idOrderLengow id lengow order
     * @param string  $type          type (import or send)
     * @param boolean $finished      log finished (true or false)
     *
     * @return mixed
     */
    public static function getOrderLogs($idOrderLengow, $type = null, $finished = null)
    {
        $logType = self::getOrderLogType($type);
        if (!is_null($logType)) {
            $andType = ' AND `type` = \''.(int)$logType.'\'';
        } else {
            $andType = '';
        }
        if (!is_null($finished)) {
            $andFinished = ($finished ? ' AND `is_finished` = 1' :  ' AND `is_finished` = 0');
        } else {
            $andFinished = '';
        }
        // check if log already exists for the given order id
        $query = 'SELECT `id`, `is_finished`, `message`, `date`, `type` FROM `'._DB_PREFIX_.'lengow_logs_import`
            WHERE `id_order_lengow` = \''.(int)$idOrderLengow.'\''.$andType.$andFinished;
        return Db::getInstance()->executeS($query);
    }

    /**
     * Add log information in lengow_logs_import table
     *
     * @param integer $idOrderLengow id lengow order
     * @param string  $message       error message
     * @param string  $type          type (import or send)
     * @param integer $finished      error is finished
     *
     */
    public static function addOrderLog($idOrderLengow, $message = '', $type = 'import', $finished = 0)
    {
        $logType = self::getOrderLogType($type);
        if (_PS_VERSION_ < '1.5') {
            return Db::getInstance()->autoExecute(
                _DB_PREFIX_.'lengow_logs_import',
                array(
                    'is_finished'     => (int)$finished,
                    'date'            => date('Y-m-d H:i:s'),
                    'message'         => pSQL($message),
                    'type'            => (int)$logType,
                    'id_order_lengow' => (int)$idOrderLengow
                ),
                'INSERT'
            );
        } else {
            return Db::getInstance()->insert(
                'lengow_logs_import',
                array(
                    'is_finished'     => (int)$finished,
                    'date'            => date('Y-m-d H:i:s'),
                    'message'         => pSQL($message),
                    'type'            => (int)$logType,
                    'id_order_lengow' => (int)$idOrderLengow
                )
            );
        }
    }

    /**
     * Removes all order logs
     *
     * @param integer $id       id_order_lengow
     * @param string  $type type (import or send)
     *
     * @return boolean
     */
    public static function finishOrderLogs($id, $type = 'import')
    {
        $logType = self::getOrderLogType($type);
        $query = 'SELECT `id` FROM `'._DB_PREFIX_.'lengow_logs_import`
            WHERE `id_order_lengow` = \''.(int)$id.'\'
            AND `type` = \''.(int)$logType.'\'';
        $orderLogs = Db::getInstance()->executeS($query);
        $updateSuccess = 0;
        foreach ($orderLogs as $orderLog) {
            if (_PS_VERSION_ < '1.5') {
                $result = Db::getInstance()->autoExecute(
                    _DB_PREFIX_.'lengow_logs_import',
                    array('is_finished' => 1),
                    'UPDATE',
                    '`id` = \''.(int)$orderLog['id'].'\''
                );
            } else {
                $result = Db::getInstance()->update(
                    'lengow_logs_import',
                    array('is_finished' => 1),
                    '`id` = \''.(int)$orderLog['id'].'\''
                );
            }
            if ($result) {
                $updateSuccess++;
            }
        }
        return (count($orderLogs) == $updateSuccess ? true : false);
    }

    /**
     * Find Lengow Order
     *
     * @param integer $idOrderLengow (id of table lengow_orders)
     *
     * @return boolean
     */
    public static function find($idOrderLengow)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'lengow_orders` WHERE id = '.(int)$idOrderLengow;
        return Db::getInstance()->getRow($sql);
    }


    /**
     * Find Lengow Order
     *
     * @param integer $idOrder
     *
     * @return boolean
     */
    public static function findByOrder($idOrder)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'lengow_orders` WHERE id_order = '.(int)$idOrder;
        return Db::getInstance()->getRow($sql);
    }

    /**
     * Get Order Lines
     *
     * @param integer $idOrder Prestashop order id
     *
     * @return array list of order line
     */
    public static function findOrderLineIds($idOrder)
    {
        $sql = 'SELECT id_order_line FROM `'._DB_PREFIX_.'lengow_order_line` WHERE id_order = '.(int)$idOrder;
        return Db::getInstance()->ExecuteS($sql);
    }

    /**
     * Re Import Order
     *
     * @param integer $id (id of table lengow_orders)
     *
     * @return boolean
     */
    public static function isOrderImport($idOrderLengow)
    {
        $sql = 'SELECT id_order FROM `'._DB_PREFIX_.'lengow_orders` WHERE id = '.(int)$idOrderLengow;
        $result = Db::getInstance()->ExecuteS($sql);
        return (bool)count($result);
    }

    /**
     * Re Import Order
     *
     * @param integer $idOrderLengow (id of table lengow_orders)
     *
     * @return mixed
     */
    public static function reImportOrder($idOrderLengow)
    {
        if (self::isOrderImport($idOrderLengow)) {
            //TEMP DATA
            Db::getInstance()->Execute(
                'UPDATE `'._DB_PREFIX_.'lengow_orders` SET id_order = NULL WHERE id = '.(int)$idOrderLengow
            );
            $lengowOrder = self::find($idOrderLengow);
            $import = new LengowImport(
                array(
                    'id_order_lengow'     => $idOrderLengow,
                    'type'                => 'import',
                    'marketplace_sku'     => $lengowOrder['marketplace_sku'],
                    'marketplace_name'    => $lengowOrder['marketplace_name'],
                    'delivery_address_id' => $lengowOrder['delivery_address_id'],
                    'shop_id'             => $lengowOrder['id_shop'],
                )
            );
            return $import->exec();
        }
    }

    /**
     * Check if can resend action order
     *
     * @return boolean
     */
    public function canReSendOrder()
    {
        $orderActions = LengowAction::getActiveActionByOrderId((int)$this->id);
        if (count($orderActions) > 0) {
            return false;
        }
        if ($this->lengowProcessState != self::PROCESS_STATE_FINISH &&
            ($this->getCurrentState() == LengowMain::getOrderState('shipped')
                || $this->getCurrentState() == LengowMain::getOrderState('canceled')
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
        if (_PS_VERSION_ < '1.5' && $this->shipping_number == '') {
            return true;
        }
        return false;
    }

    /**
     * Re Send Order
     *
     * @param integer $idOrderLengow (id of table lengow_orders)
     *
     * @return mixed
     */
    public static function reSendOrder($idOrderLengow)
    {
        if (self::isOrderImport($idOrderLengow)) {
            $lengowOrder = self::find($idOrderLengow);
            if ((int)$lengowOrder['id_order'] > 0) {
                $action = LengowAction::getLastOrderActionType($lengowOrder['id_order']);
                $action = $action ? $action : 'ship';
                $order = new LengowOrder($lengowOrder['id_order']);
                return $order->callAction($action);
            }
            return false;
        }
    }

    /**
     * Send Order action
     *
     * @param string $action Lengow Actions (ship or cancel)
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
                    'action'   => $action,
                    'order_id' => $this->id
                )
            ),
            false,
            $this->lengowMarketplaceSku
        );
        if ((int)$this->id == 0) {
            LengowMain::log(
                'API-OrderAction',
                LengowMain::setLogMessage('log.order_action.can_not_load_order'),
                true
            );
            $success = false;
        }
        if ($success) {
            // Finish all order logs send
            self::finishOrderLogs($this->lengowId, 'send');
            try {
                // Compatibility V2
                if ((int)$this->lengowIdFlux > 0) {
                    $this->checkAndChangeMarketplaceName();
                }
                $marketplace = LengowMain::getMarketplaceSingleton(
                    $this->lengowMarketplaceName,
                    $this->lengowIdShop
                );
                if ($marketplace->containOrderLine($action)) {
                    $orderLineCollection = self::findOrderLineIds($this->id);
                    // compatibility V2 and security
                    if (count($orderLineCollection) == 0) {
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
                $errorMessage = '[Prestashop error] "'.$e->getMessage().'" '.$e->getFile().' | '.$e->getLine();
            }
            if (isset($errorMessage)) {
                if ($this->lengowProcessState != self::PROCESS_STATE_FINISH) {
                    self::addOrderLog($this->lengowId, $errorMessage, 'send');
                }
                $decodedMessage = LengowMain::decodeLogMessage($errorMessage, 'en');
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
                    'action'   => $action,
                    'order_id' => $this->id
                )
            );
        } else {
            $message = LengowMain::setLogMessage(
                'log.order_action.action_not_send',
                array(
                    'action'   => $action,
                    'order_id' => $this->id
                )
            );
        }
        LengowMain::log('API-OrderAction', $message, false, $this->lengowMarketplaceSku);
        return $success;
    }

    /**
     * Get order line by API
     *
     * @return mixed
     */
    public function getOrderLineByApi()
    {
        $orderLines = array();
        $results = LengowConnector::queryApi(
            'get',
            '/v3.0/orders',
            $this->lengowIdShop,
            array(
                'marketplace_order_id' => $this->lengowMarketplaceSku,
                'marketplace'          => $this->lengowMarketplaceName,
            )
        );
        if (isset($results->count) && $results->count == 0) {
            return false;
        }
        $orderData = $results->results[0];
        foreach ($orderData->packages as $package) {
            $productLines = array();
            foreach ($package->cart as $product) {
                $productLines[] = array('id_order_line' => (string)$product->marketplace_order_line_id);
            }
            if ($this->lengowDeliveryAddressId == 0) {
                return count($productLines) > 0 ? $productLines : false;
            } else {
                $orderLines[(int)$package->delivery->id] = $productLines;
            }
        }
        $return = $orderLines[$this->lengowDeliveryAddressId];
        return count($return) > 0 ? $return : false;
    }

    /**
     * Get Total Order By Statuses
     *
     * @param string $status
     */
    public static function getTotalOrderByStatus($status)
    {
        $sql = 'SELECT COUNT(*) as total FROM `'._DB_PREFIX_.'lengow_orders`
        WHERE order_lengow_state = "'.pSQL($status).'"';
        $row = Db::getInstance()->getRow($sql);
        return $row['total'];
    }

    /**
     * Test function not finish
     * Sync old data
     */
    public static function syncOldData()
    {
        //delete row with is_disabled = 1 IN 3.0.0 UPDATE
        //get country when empty
        $sql = "SELECT id, id_address_delivery FROM "._DB_PREFIX_."lengow_orders lo
        INNER JOIN "._DB_PREFIX_."orders o ON (o.id_order = lo.id_order)
        WHERE (delivery_country_iso IS NULL OR delivery_country_iso='')";
        $collection = Db::getInstance()->ExecuteS($sql);
        foreach ($collection as $row) {
            $sql = "SELECT c.iso_code FROM "._DB_PREFIX_."address a
                INNER JOIN "._DB_PREFIX_."country c ON (c.id_country = a.id_country)
                WHERE a.id_address = ".(int)$row['id_address_delivery'];
            $country = Db::getInstance()->getRow($sql);
            if (Tools::strlen($country['iso_code']) > 0 && Tools::strlen($row['id']) > 0) {
                Db::getInstance()->Execute(
                    'UPDATE '._DB_PREFIX_.'lengow_orders SET delivery_country_iso = "'.pSQL($country['iso_code']).'"'.
                    ' WHERE id = '.(int)$row['id']
                );
            }
        }
        //check country in order
        $states = Db::getInstance()->getRow(
            'SELECT id_order_state FROM '._DB_PREFIX_.'order_state_lang WHERE name = "Erreur technique - Lengow"'
        );
        $errorState = $states['id_order_state'];
        if ($errorState > 0) {
            $sql = "SELECT COUNT(*) as total, marketplace_sku FROM "._DB_PREFIX_."lengow_orders
            GROUP BY marketplace_sku HAVING total > 1";
            $marketplaceSkuCollection = Db::getInstance()->ExecuteS($sql);
            foreach ($marketplaceSkuCollection as $marketplaceRow) {
                $orderCollection = Db::getInstance()->ExecuteS(
                    "SELECT o.current_state, lo.id FROM `"._DB_PREFIX_."lengow_orders` lo
                    INNER JOIN "._DB_PREFIX_."orders o ON (o.id_order = lo.id_order)
                    WHERE marketplace_sku = '".$marketplaceRow['marketplace_sku']."'"
                );
                if (count($orderCollection) == 0) {
                    continue;
                }
                $findOtherState = false;
                $orderToDelete = array();
                foreach ($orderCollection as $order) {
                    if ($order['current_state'] == $errorState) {
                        $orderToDelete[] = $errorState;
                    } else {
                        $findOtherState = true;
                    }
                }
                if ($findOtherState && count($orderToDelete) > 0) {
                    foreach ($orderToDelete as $id) {
                        Db::getInstance()->Execute(
                            'DELETE FROM '._DB_PREFIX_.'lengow_orders WHERE id = '.(int)$id
                        );
                    }
                }
            }
        }
    }
}
