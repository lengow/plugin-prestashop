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
    public $lengow_id;

    /**
     * @var string Lengow order id
     */
    public $lengow_marketplace_sku;

    /**
     * @var string Marketplace's name
     */
    public $lengow_marketplace_name;

    /**
     * @var string Message
     */
    public $lengow_message;

    /**
     * @var integer Shop ID
     */
    public $lengow_id_shop;

    /**
     * @var integer Lengow flux id
     */
    public $lengow_id_flux;

    /**
     * @var decimal Total paid on marketplace
     */
    public $lengow_total_paid;

    /**
    * @var string Carrier from marketplace
    */
    public $lengow_carrier;

    /**
    * @var string Carrier Method from marketplace
    */
    public $lengow_method;

    /**
    * @var string Tracking
    */
    public $lengow_tracking;

    /**
    * @var boolean Shipped by markeplace
    */
    public $lengow_sent_marketplace;

    /**
    * @var string Extra information (json node form import)
    */
    public $lengow_extra;

    /**
     * @var boolean order is reimported (ready to be reimported)
     */
    public $lengow_is_reimported;

    /**
     * @var integer lengow process state (0 => error, 1 => imported, 2 => finished)
     */
    public $lengow_process_state;

    /**
     * @var date marketplace order date
     */
    public $lengow_order_date;

    /**
     * @var integer id of the delivery address
     */
    public $lengow_delivery_address_id;

    /**
     * @var string ISO code for country
     */
    public $lengow_delivery_country_iso;

    /**
     * @var string the name of the customer
     */
    public $lengow_customer_name;

    /**
     * @var string email of the customer
     */
    public $lengow_customer_email;

    /**
     * @var string current lengow state
     */
    public $lengow_state;

    /**
     * @var integer number of items
     */
    public $lengow_order_item;

    /**
    * @var boolean Is importing, prevent multiple import
    */
    public $is_import;

    /**
     * @var SimpleXmlElement Data of lengow order
     */
    public $data;

    /**
     * @var bool Order is already fully imported
     */
    public $is_finished = false;

    /**
     * @var bool First time order is being processed
     */
    public $first_import = true;

    /**
     * @var string Log message saved in DB
     */
    public $log_message = null;

    /**
     * @var LengowMarketplace Order marketplace
     */
    protected $marketplace;

    /**
    * Construct a Lengow order based on Prestashop order.
    *
    * @param integer $id      Lengow order id
    * @param integer $id_lang id lang
    */
    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
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
            $this->lengow_id                    = $result['id'];
            $this->lengow_marketplace_sku       = $result['marketplace_sku'];
            $this->lengow_id_shop               = (int)$result['id_shop'];
            $this->lengow_id_flux               = $result['id_flux'];
            $this->lengow_marketplace_name      = $result['marketplace_name'];
            $this->lengow_message               = $result['message'];
            $this->lengow_total_paid            = $result['total_paid'];
            $this->lengow_carrier               = $result['carrier'];
            $this->lengow_method                = $result['method'];
            $this->lengow_tracking              = $result['tracking'];
            $this->lengow_sent_marketplace      = (bool)$result['sent_marketplace'];
            $this->lengow_extra                 = $result['extra'];
            $this->lengow_is_reimported         = (bool)$result['is_reimported'];
            $this->lengow_process_state         = (int)$result['order_process_state'];
            $this->lengow_order_date            = $result['order_date'];
            $this->lengow_delivery_address_id   = (int)$result['delivery_address_id'];
            $this->lengow_delivery_country_iso  = $result['delivery_country_iso'];
            $this->lengow_customer_name         = $result['customer_name'];
            $this->lengow_customer_email        = $result['customer_email'];
            $this->lengow_state                 = $result['order_lengow_state'];
            $this->lengow_order_item            = (int)$result['order_item'];
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Prestashop order id
     *
     * @param string  $marketplace_sku     Lengow order id
     * @param string  $marketplace         marketplace name
     * @param integer $delivery_address_id devivery address id
     *
     * @return mixed
     */
    public static function getOrderIdFromLengowOrders($marketplace_sku, $marketplace, $delivery_address_id)
    {
        $query = 'SELECT `id_order`, `delivery_address_id`,`id_flux` 
            FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `marketplace_sku` = \''.pSQL($marketplace_sku).'\'
            AND `marketplace_name` = \''.pSQL(Tools::strtolower($marketplace)).'\'
            AND `order_process_state` != 0';
        $results = Db::getInstance()->executeS($query);
        if (count($results) == 0) {
            return false;
        }
        foreach ($results as $result) {
            if (is_null($result['delivery_address_id']) && !is_null($result['id_flux'])) {
                return $result['id_order'];
            } elseif ($result['delivery_address_id'] == $delivery_address_id) {
                return $result['id_order'];
            }
        }
        return false;
    }

    /**
     * Get ID record from lengow orders table
     *
     * @param string  $marketplace_sku     lengow order id
     * @param integer $delivery_address_id delivery address id
     *
     * @return mixed
     */
    public static function getIdFromLengowOrders($marketplace_sku, $delivery_address_id)
    {
        $query = 'SELECT `id` FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `marketplace_sku` = \''.pSQL($marketplace_sku).'\'
            AND `delivery_address_id` = \''.(int)$delivery_address_id.'\'';
        $result = Db::getInstance()->getRow($query);
        if ($result) {
            return (int)$result['id'];
        }
        return false;
    }

    /**
     * Check if a lengow order
     *
     * @param integer $order_id prestashop order id
     *
     * @return boolean
     */
    public static function isFromLengow($order_id)
    {
        $query = 'SELECT `marketplace_sku`
            FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `id_order` = \''.(int)$order_id.'\'';
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
     * @param integer $order_id            Prestashop order id
     * @param integer $delivery_address_id Lengow delivery address id
     *
     * @return mixed
     */
    public static function getIdFromLengowDeliveryAddress($order_id, $delivery_address_id)
    {
        $query = 'SELECT `id` FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `id_order` = \''.(int)$order_id.'\'
            AND `delivery_address_id` = \''.pSQL((int)$delivery_address_id).'\'';
        $result = Db::getInstance()->getRow($query);
        if ($result) {
            return $result['id'];
        }
        return false;
    }

    /**
     * Retrieves all the order ids for an order number Lengow
     *
     * @param string $marketplace_sku Lengow order id
     * @param string $marketplace     marketplace name
     *
     * @return array
     */
    public static function getAllOrderIdsFromLengowOrder($marketplace_sku, $marketplace)
    {
        $query = 'SELECT `id_order` FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `marketplace_sku` = \''.pSQL($marketplace_sku).'\'
            AND `marketplace_name` = \''.pSQL(Tools::strtolower($marketplace)).'\'
            AND `order_process_state` != \'0\'';
        return Db::getInstance()->executeS($query);
    }

    /**
     * Retrieves all the order lines of a order PrestaShop
     *
     * @param integer $order_id prestashop order id
     *
     * @return array
     */
    public static function getAllOrderLinesFromLengowOrder($order_id)
    {
        $query = 'SELECT `id_order_line` FROM `'._DB_PREFIX_.'lengow_order_line`
            WHERE `id_order` = \''.(int)$order_id.'\'';
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
     * @param string $order_state_lengow marketplace state
     * @param mixed  $order_data         order data
     * @param mixed  $package_data       package data
     *
     * @return mixed (Shipped, Canceled or false)
     */
    public function updateState($order_state_lengow, $order_data, $package_data)
    {
        $order_process_state = self::getOrderProcessState($order_state_lengow);
        $tracking_number = (
            count($package_data->delivery->trackings) > 0 ? (string)$package_data->delivery->trackings[0]->number : null
        );
        // Update Lengow order if necessary
        $params = array();
        if ($this->lengow_state != $order_state_lengow) {
            $params['order_lengow_state'] = pSQL($order_state_lengow);
            $params['extra'] = pSQL(Tools::jsonEncode($order_data));
            $params['tracking'] = pSQL($tracking_number);
        }
        if ($order_process_state == self::PROCESS_STATE_FINISH) {
            // Finish actions if lengow order is shipped, closed or cancel
            LengowAction::finishAllActions((int)$this->id);
            if ((int)$this->lengow_process_state != $order_process_state) {
                $params['order_process_state'] = (int)$order_process_state;
            }
        }
        if (count($params) > 0) {
            self::updateOrderLengow((int)$this->lengow_id, $params);
        }
        // get prestashop equivalent state id to Lengow API state
        $id_order_state = LengowMain::getOrderState($order_state_lengow);
        // if state is different between API and Prestashop
        if ($this->getCurrentState() != $id_order_state) {
            // Change state process to shipped
            if ($this->getCurrentState() == LengowMain::getOrderState('accepted')
                && ($order_state_lengow == 'shipped'|| $order_state_lengow == 'closed')
            ) {
                // create a new order history
                $history = new OrderHistory();
                $history->id_order = $this->id;
                $history->changeIdOrderState(LengowMain::getOrderState('shipped'), $this, true);
                $history->validateFields();
                $history->add();
                if (!is_null($tracking_number)) {
                    $this->shipping_number = $tracking_number;
                    $this->validateFields();
                    $this->update();
                }
                return 'Shipped';
            } elseif (($this->getCurrentState() == LengowMain::getOrderState('accepted')
                    || $this->getCurrentState() == LengowMain::getOrderState('shipped')
                ) && ($order_state_lengow == 'canceled' || $order_state_lengow == 'refused')
            ) {
                // create a new order history
                $history = new OrderHistory();
                $history->id_order = $this->id;
                $history->changeIdOrderState(LengowMain::getOrderState('canceled'), $this, true);
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
                'id_order_lengow'     => $this->lengow_id,
                'marketplace_sku'     => $this->lengow_marketplace_sku,
                'marketplace_name'    => $this->lengow_marketplace_name,
                'delivery_address_id' => $this->lengow_delivery_address_id,
                'shop_id'             => $this->lengow_id_shop,
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
        $id_error_lengow_state = LengowMain::getLengowErrorStateId();
        // update order to Lengow error state if not already updated
        if ($this->getCurrentState() !== $id_error_lengow_state) {
            $this->setCurrentState($id_error_lengow_state, Context::getContext()->employee->id);
        }
    }

    /**
     * Get unset order by shop
     *
     * @param integer $id_shop Prestashop shop id
     *
     * @return mixed
     */
    public static function getUnsentOrderByStore($id_shop)
    {
        $results = Db::getInstance()->executeS(
            'SELECT lo.`id`, o.`id_shop`, o.`id_order`, oh.`id_order_state` FROM '._DB_PREFIX_.'lengow_orders lo
            INNER JOIN '._DB_PREFIX_.'orders o ON (o.id_order = lo.id_order)
            INNER JOIN '._DB_PREFIX_.'order_history oh ON (oh.id_order = lo.id_order)
            WHERE o.`id_shop` ='.(int)$id_shop
            .' AND lo.`order_process_state` = '.(int)self::PROCESS_STATE_IMPORT
            .' AND oh.`id_order_state` IN ('
                .LengowMain::getOrderState('shipped').','.LengowMain::getOrderState('canceled')
            .')'
        );
        if ($results) {
            $unsent_orders = array();
            foreach ($results as $result) {
                $active_action = LengowAction::getActiveActionByOrderId($result['id_order']);
                $order_logs = LengowOrder::getOrderLogs($result['id'], 'send', false);
                if (!$active_action
                    && count($order_logs) == 0
                    && !array_key_exists($result['id_order'], $unsent_orders)
                ) {
                    $action_type = $result['state'] == LengowMain::getOrderState('canceled') ? 'cancel' : 'ship';
                    $unsent_orders[$result['id_order']] = $action_type;
                }
            }
            if (count($unsent_orders) > 0) {
                return $unsent_orders;
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
        $id_shop = (_PS_VERSION_ < 1.5 ? null : (int)$this->lengow_id_shop);
        // Get connector
        if (is_null($connector)) {
            if (LengowCheck::isValidAuth($id_shop)) {
                $connector = new LengowConnector(
                    LengowMain::getAccessToken($id_shop),
                    LengowMain::getSecretCustomer($id_shop)
                );
            } else {
                return false;
            }
        }
        // Get all order id
        $order_ids = self::getAllOrderIdsFromLengowOrder(
            $this->lengow_marketplace_sku,
            $this->lengow_marketplace_name
        );
        if (count($order_ids) > 0) {
            $presta_ids = array();
            foreach ($order_ids as $order_id) {
                $presta_ids[] = $order_id['id_order'];
            }
            // compatibility V2
            if ($this->lengow_id_flux != null) {
                $this->checkAndChangeMarketplaceName($connector);
            }
            $result = $connector->patch(
                '/v3.0/orders',
                array(
                    'account_id'           => LengowMain::getIdAccount($id_shop),
                    'marketplace_order_id' => $this->lengow_marketplace_sku,
                    'marketplace'          => $this->lengow_marketplace_name,
                    'merchant_order_id'    => $presta_ids
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
        $id_shop = (_PS_VERSION_ < 1.5 ? null : (int)$this->lengow_id_shop);
        // get connector
        if (is_null($connector)) {
            if (LengowCheck::isValidAuth($id_shop)) {
                $connector = new LengowConnector(
                    LengowMain::getAccessToken($id_shop),
                    LengowMain::getSecretCustomer($id_shop)
                );
            } else {
                return false;
            }
        }
        $results = $connector->get(
            '/v3.0/orders',
            array(
                'marketplace_order_id' => $this->lengow_marketplace_sku,
                'marketplace'          => $this->lengow_marketplace_name,
                'account_id'           => LengowMain::getIdAccount($id_shop)
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
            if ($this->lengow_marketplace_name != (string)$order->marketplace) {
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
                $log_type = self::TYPE_LOG_IMPORT;
                break;
            case 'send':
                $log_type = self::TYPE_LOG_SEND;
                break;
            default:
                $log_type = null;
                break;
        }
        return $log_type;
    }

    /**
     * Check if an order has an error
     *
     * @param string  $marketplace_sku     Lengow order id
     * @param integer $delivery_address_id Id delivery address
     * @param string  $type                Type (import or send)
     *
     * @return mixed
     */
    public static function orderIsInError($marketplace_sku, $delivery_address_id, $type = 'import')
    {
        $log_type = self::getOrderLogType($type);
        // check if log already exists for the given order id
        $query = 'SELECT lli.`message`, lli.`date` FROM `'._DB_PREFIX_.'lengow_logs_import` lli
            LEFT JOIN `'._DB_PREFIX_.'lengow_orders` lo ON lli.`id_order_lengow` = lo.`id`
            WHERE lo.`marketplace_sku` = \''.pSQL($marketplace_sku).'\'
            AND lo.`delivery_address_id` = \''.(int)$delivery_address_id.'\'
            AND lli.`type` = \''.(int)$log_type.'\'
            AND lli.`is_finished` = 0';
        return Db::getInstance()->getRow($query);
    }

    /**
     * Check if log already exists for the given order
     *
     * @param string  $id_order_lengow id lengow order
     * @param string  $type            type (import or send)
     * @param boolean $finished        log finished (true or false)
     *
     * @return mixed
     */
    public static function getOrderLogs($id_order_lengow, $type = null, $finished = null)
    {
        $log_type = self::getOrderLogType($type);
        if (!is_null($log_type)) {
            $and_type = ' AND `type` = \''.(int)$log_type.'\'';
        } else {
            $and_type = '';
        }
        if (!is_null($finished)) {
            $and_finished = ($finished ? ' AND `is_finished` = 1' :  ' AND `is_finished` = 0');
        } else {
            $and_finished = '';
        }
        // check if log already exists for the given order id
        $query = 'SELECT `id`, `is_finished`, `message`, `date`, `type` FROM `'._DB_PREFIX_.'lengow_logs_import`
            WHERE `id_order_lengow` = \''.(int)$id_order_lengow.'\''.$and_type.$and_finished;
        return Db::getInstance()->executeS($query);
    }

    /**
     * Add log information in lengow_logs_import table
     *
     * @param integer $id_order_lengow id lengow order
     * @param string  $message         error message
     * @param string  $type            type (import or send)
     * @param integer $finished        error is finished
     *
     */
    public static function addOrderLog($id_order_lengow, $message = '', $type = 'import', $finished = 0)
    {
        $log_type = self::getOrderLogType($type);
        if (_PS_VERSION_ < '1.5') {
            return Db::getInstance()->autoExecute(
                _DB_PREFIX_.'lengow_logs_import',
                array(
                    'is_finished'     => (int)$finished,
                    'date'            => date('Y-m-d H:i:s'),
                    'message'         => pSQL($message),
                    'type'            => (int)$log_type,
                    'id_order_lengow' => (int)$id_order_lengow
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
                    'type'            => (int)$log_type,
                    'id_order_lengow' => (int)$id_order_lengow
                )
            );
        }
    }

    /**
     * Removes all order logs
     *
     * @param integer $id       id_order_lengow
     * @param string  $log_type type (import or send)
     *
     * @return boolean
     */
    public static function finishOrderLogs($id, $type = 'import')
    {
        $log_type = self::getOrderLogType($type);
        $query = 'SELECT `id` FROM `'._DB_PREFIX_.'lengow_logs_import`
            WHERE `id_order_lengow` = \''.(int)$id.'\'
            AND `type` = \''.(int)$log_type.'\'';
        $order_logs = Db::getInstance()->executeS($query);
        $update_success = 0;
        foreach ($order_logs as $order_log) {
            if (_PS_VERSION_ < '1.5') {
                $result = Db::getInstance()->autoExecute(
                    _DB_PREFIX_.'lengow_logs_import',
                    array('is_finished' => 1),
                    'UPDATE',
                    '`id` = \''.(int)$order_log['id'].'\''
                );
            } else {
                $result = Db::getInstance()->update(
                    'lengow_logs_import',
                    array('is_finished' => 1),
                    '`id` = \''.(int)$order_log['id'].'\''
                );
            }
            if ($result) {
                $update_success++;
            }
        }
        return (count($order_logs) == $update_success ? true : false);
    }

    /**
     * Find Lengow Order
     *
     * @param integer $id_order_lengow (id of table lengow_orders)
     *
     * @return boolean
     */
    public static function find($id_order_lengow)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'lengow_orders` WHERE id = '.(int)$id_order_lengow;
        return Db::getInstance()->getRow($sql);
    }


    /**
     * Find Lengow Order
     *
     * @param integer $id_order
     *
     * @return boolean
     */
    public static function findByOrder($id_order)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'lengow_orders` WHERE id_order = '.(int)$id_order;
        return Db::getInstance()->getRow($sql);
    }

    /**
     * Get Order Lines
     *
     * @param integer $id_order Prestashop order id
     *
     * @return array list of order line
     */
    public static function findOrderLineIds($id_order)
    {
        $sql = 'SELECT id_order_line FROM `'._DB_PREFIX_.'lengow_order_line` WHERE id_order = '.(int)$id_order;
        return Db::getInstance()->ExecuteS($sql);
    }

    /**
     * Re Import Order
     *
     * @param integer $id (id of table lengow_orders)
     *
     * @return boolean
     */
    public static function isOrderImport($id_order_lengow)
    {
        $sql = 'SELECT id_order FROM `'._DB_PREFIX_.'lengow_orders` WHERE id = '.(int)$id_order_lengow;
        $result = Db::getInstance()->ExecuteS($sql);
        return (bool)count($result);
    }

    /**
     * Re Import Order
     *
     * @param integer $id_order_lengow (id of table lengow_orders)
     *
     * @return mixed
     */
    public static function reImportOrder($id_order_lengow)
    {
        if (self::isOrderImport($id_order_lengow)) {
            //TEMP DATA
            Db::getInstance()->Execute(
                'UPDATE `'._DB_PREFIX_.'lengow_orders` SET id_order = NULL WHERE id = '.(int)$id_order_lengow
            );
            $lengowOrder = self::find($id_order_lengow);
            $import = new LengowImport(array(
                'id_order_lengow'     => $id_order_lengow,
                'type'                => 'import',
                'marketplace_sku'     => $lengowOrder['marketplace_sku'],
                'marketplace_name'    => $lengowOrder['marketplace_name'],
                'delivery_address_id' => $lengowOrder['delivery_address_id'],
                'shop_id'             => $lengowOrder['id_shop'],
            ));
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
        $order_actions = LengowAction::getActiveActionByOrderId((int)$this->id);
        if (count($order_actions) > 0) {
            return false;
        }
        if ($this->lengow_process_state != self::PROCESS_STATE_FINISH &&
            ($this->getCurrentState() == LengowMain::getOrderState('shipped')
                || $this->getCurrentState() == LengowMain::getOrderState('canceled')
            )
        ) {
            return true;
        }
        return false;
    }

    /**
     * Re Send Order
     *
     * @param integer $id_order_lengow (id of table lengow_orders)
     *
     * @return mixed
     */
    public static function reSendOrder($id_order_lengow)
    {
        if (self::isOrderImport($id_order_lengow)) {
            $lengowOrder = self::find($id_order_lengow);
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
            LengowMain::setLogMessage('log.order_action.try_to_send_action', array(
                'action'   => $action,
                'order_id' => $this->id
            )),
            false,
            $this->lengow_marketplace_sku
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
            self::finishOrderLogs($this->lengow_id, 'send');
            try {
                // Compatibility V2
                if ((int)$this->lengow_id_flux > 0) {
                    $this->checkAndChangeMarketplaceName();
                }
                $marketplace = LengowMain::getMarketplaceSingleton(
                    $this->lengow_marketplace_name,
                    $this->lengow_id_shop
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
                $error_message = $e->getMessage();
            } catch (Exception $e) {
                $error_message = '[Prestashop error] "'.$e->getMessage().'" '.$e->getFile().' | '.$e->getLine();
            }
            if (isset($error_message)) {
                if ($this->lengow_process_state != self::PROCESS_STATE_FINISH) {
                    self::addOrderLog($this->lengow_id, $error_message, 'send');
                }
                $decoded_message = LengowMain::decodeLogMessage($error_message, 'en');
                LengowMain::log(
                    'API-OrderAction',
                    LengowMain::setLogMessage('log.order_action.call_action_failed', array(
                        'decoded_message' => $decoded_message
                    )),
                    false,
                    $this->lengow_marketplace_sku
                );
                $success = false;
            }
        }
        if ($success) {
            $message = LengowMain::setLogMessage('log.order_action.action_send', array(
                'action'   => $action,
                'order_id' => $this->id
            ));
        } else {
            $message = LengowMain::setLogMessage('log.order_action.action_not_send', array(
                'action'   => $action,
                'order_id' => $this->id
            ));
        }
        LengowMain::log('API-OrderAction', $message, false, $this->lengow_marketplace_sku);
        return $success;
    }

    /**
     * Get order line by API
     *
     * @return mixed
     */
    public function getOrderLineByApi()
    {
        $order_lines = array();
        $results = LengowConnector::queryApi(
            'get',
            '/v3.0/orders',
            $this->lengow_id_shop,
            array(
                'marketplace_order_id' => $this->lengow_marketplace_sku,
                'marketplace'          => $this->lengow_marketplace_name,
            )
        );
        if (isset($results->count) && $results->count == 0) {
            return false;
        }
        $order_data = $results->results[0];
        foreach ($order_data->packages as $package) {
            $product_lines = array();
            foreach ($package->cart as $product) {
                $product_lines[] = array('id_order_line' => (string)$product->marketplace_order_line_id);
            }
            if ($this->lengow_delivery_address_id == 0) {
                return count($product_lines) > 0 ? $product_lines : false;
            } else {
                $order_lines[(int)$package->delivery->id] = $product_lines;
            }
        }
        $return = $order_lines[$this->lengow_delivery_address_id];
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
            if (Tools::strlen($country['iso_code'])>0 && Tools::strlen($row['id'])>0) {
                Db::getInstance()->Execute(
                    'UPDATE '._DB_PREFIX_.'lengow_orders SET delivery_country_iso = "'.pSQL($country['iso_code']).'"'.
                    ' WHERE id = '.(int)$row['id']
                );
            }
        }
        //check country in order
        $states = Db::getInstance()->getRow('SELECT id_order_state FROM '._DB_PREFIX_.'order_state_lang
                WHERE name = "Erreur technique - Lengow"');
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
                if (count($orderCollection) ==0) {
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
                if ($findOtherState && count($orderToDelete)>0) {
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
