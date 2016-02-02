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
 *
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
    * integer order log wsdl type
    */
    const TYPE_LOG_WSDL = 2;

    /**
     * @var string Lengow order id
     */
    public $id_lengow;

    /**
     * @var string Marketplace's name
     */
    public $lengow_marketplace;

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
    public $id_flux;

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
     * @var boolean order is disabled (ready to be reimported)
     */
    public $is_disabled;

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
    public $lengow_delivery_id_address;

    /**
     * @var string ISO code for country
     */
    public $lengow_delivery_country_iso;

    /**
     * @var string the name of the customer
     */
    public $lengow_customer_name;

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
    * @param integer $id        Lengow order id
    * @param integer $id_lang   id lang
    */
    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
        $this->loadLengowFields();
    }

    /**
     * Load information from lengow_orders table
     *
     * @return boolean.
     */
    protected function loadLengowFields()
    {
        $query = 'SELECT
            lo.`id_order_lengow`,
            lo.`id_shop`,
            lo.`id_flux`,
            lo.`marketplace`,
            lo.`message`,
            lo.`total_paid`,
            lo.`carrier`,
            lo.`method`,
            lo.`tracking`,
            lo.`sent_marketplace`,
            lo.`extra`,
            lo.`is_disabled`,
            lo.`order_process_state`,
            lo.`order_date`,
            lo.`delivery_id_address`,
            lo.`delivery_country_iso`,
            lo.`customer_name`,
            lo.`order_lengow_state`,
            lo.`order_item`
            FROM `'._DB_PREFIX_.'lengow_orders` lo
            WHERE lo.id_order = \''.(int)$this->id.'\'
        ';
        if ($result = Db::getInstance()->getRow($query)) {
            $this->id_lengow                    = $result['id_order_lengow'];
            $this->lengow_id_shop               = (int)$result['id_shop'];
            $this->id_flux                      = $result['id_flux'];
            $this->lengow_marketplace           = $result['marketplace'];
            $this->lengow_message               = $result['message'];
            $this->lengow_total_paid            = $result['total_paid'];
            $this->lengow_carrier               = $result['carrier'];
            $this->lengow_method                = $result['method'];
            $this->lengow_tracking              = $result['tracking'];
            $this->lengow_sent_marketplace      = (bool)$result['sent_marketplace'];
            $this->lengow_extra                 = $result['extra'];
            $this->is_disabled                  = (bool)$result['is_disabled'];
            $this->lengow_process_state         = (int)$result['order_process_state'];
            $this->lengow_order_date            = $result['order_date'];
            $this->lengow_delivery_id_address   = (int)$result['delivery_id_address'];
            $this->lengow_delivery_country_iso  = $result['delivery_country_iso'];
            $this->lengow_customer_name         = $result['customer_name'];
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
     * @param string    $lengow_id              Lengow order id
     * @param string    $marketplace            marketplace name
     * @param integer   $delivery_address_id    devivery address id
     *
     * @return mixed
     */
    public static function getOrderIdFromLengowOrders($lengow_id, $marketplace, $delivery_address_id)
    {
        $query = 'SELECT `id_order`, `delivery_id_address`
            FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `id_order_lengow` = \''.pSQL($lengow_id).'\'
            AND `marketplace` = \''.pSQL(Tools::strtolower($marketplace)).'\'
            AND `order_process_state` != 0';
        $results = Db::getInstance()->executeS($query);
        if (count($results) == 0) {
            return false;
        }
        foreach ($results as $result) {
            if (is_null($result['delivery_id_address'])) {
                return $result['id_order'];
            } elseif ($result['delivery_id_address'] == $delivery_address_id) {
                return $result['id_order'];
            }
        }
        return false;
    }

    /**
     * Get ID record from lengow orders table
     *
     * @param string   $lengow_id               lengow order id
     * @param integer  $delivery_address_id     delivery address id
     *
     * @return mixed
     */
    public static function getIdFromLengowOrders($lengow_id, $delivery_address_id)
    {
        $query = 'SELECT `id` FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `id_order_lengow` = \''.pSQL($lengow_id).'\'
            AND `delivery_id_address` = \''.pSQL((int)$delivery_address_id).'\'';
        $result = Db::getInstance()->getRow($query);
        if ($result) {
            return $result['id'];
        }
        return false;
    }

    /**
     * Check if a lengow order
     *
     * @param integer   $order_id prestashop order id
     *
     * @return boolean
     */
    public static function isFromLengow($order_id)
    {
        $query = 'SELECT `id_order_lengow`
            FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `id_order` = \''.pSQL((int)$order_id).'\'';
        $result = Db::getInstance()->executeS($query);
        if (empty($result) || $result[0]['id_order_lengow'] == '') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get Id from Lengow delivery address id
     *
     * @param integer   $order_id               Prestashop order id
     * @param integer   $delivery_address_id    Lengow delivery address id
     *
     * @return mixed
     */
    public static function getIdFromLengowDeliveryAddress($order_id, $delivery_address_id)
    {
        $query = 'SELECT `id` FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `id_order` = \''.pSQL((int)$order_id).'\'
            AND `delivery_id_address` = \''.pSQL((int)$delivery_address_id).'\'';
        $result = Db::getInstance()->getRow($query);
        if ($result) {
            return $result['id'];
        }
        return false;
    }

    /**
     * Retrieves all the order ids for an order number Lengow
     *
     * @param string    $lengow_id      Lengow order id
     * @param string    $marketplace    marketplace name
     *
     * @return array
     */
    public static function getAllOrderIdsFromLengowOrder($lengow_id, $marketplace)
    {
        $query = 'SELECT `id_order` FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `id_order_lengow` = \''.pSQL($lengow_id).'\'
            AND `marketplace` = \''.pSQL(Tools::strtolower($marketplace)).'\'
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
            WHERE `id_order` = \''.pSQL((int)$order_id).'\'';
        return Db::getInstance()->executeS($query);
    }

    /**
     * Update order Lengow
     *
     * @param integer $id       Id of the record
     * @param array   $params   Fields update
     *
     * @return bool true if order has been updated
     */
    public static function updateOrderLengow($id, $params)
    {
        return Db::getInstance()->autoExecute(
            _DB_PREFIX_.'lengow_orders',
            $params,
            'UPDATE',
            '`id` = \''.pSQL((int)$id).'\'',
            1
        );
    }

    /**
     * Mark order as disabled in lengow_orders table
     *
     * @param integer   $order_id prestashop order id
     *
     * @return boolean
     */
    public static function disable($order_id)
    {
        $query = 'UPDATE '._DB_PREFIX_.'lengow_orders
            SET `is_disabled` = 1
            WHERE `id_order`= \''.pSQL((int)$order_id).'\'';
        return DB::getInstance()->execute($query);
    }

    /**
     * Update order status
     *
     * @param LengowMarketplace $marketplace    Lengow marketplace
     * @param string            $api_state      marketplace state
     * @param string            $lengow_id      tracking number
     *
     * @return bool true if order has been updated
     */
    public function updateState(LengowMarketplace $marketplace, $api_state, $tracking_number)
    {
        // get prestashop equivalent state id to Lengow API state
        $id_order_state = LengowMain::getOrderState($marketplace->getStateLengow($api_state));

        // if state is different between API and Prestashop
        if ($this->getCurrentState() != $id_order_state) {
            // Change state process to shipped
            if ($this->getCurrentState() == LengowMain::getOrderState('accepted')
                && ($marketplace->getStateLengow($api_state) == 'shipped'
                    || $marketplace->getStateLengow($api_state) == 'closed'
                )
            ) {
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
                LengowMain::getLogInstance()->write('state updated to shipped', true, $this->id_lengow);
                return true;
            } elseif (($this->getCurrentState() == LengowMain::getOrderState('accepted')
                    || $this->getCurrentState() == LengowMain::getOrderState('shipped')
                ) && ($marketplace->getStateLengow($api_state) == 'canceled'
                    || $marketplace->getStateLengow($api_state) == 'refused'
                )
            ) {
                $history = new OrderHistory();
                $history->id_order = $this->id;
                $history->changeIdOrderState(LengowMain::getOrderState('canceled'), $this, true);
                $history->validateFields();
                $history->add();
                LengowMain::getLogInstance()->write('state updated to canceled', true, $this->id_lengow);
                return true;
            }
        }
        return false;
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
     * Check and change the name of the marketplace for v3 compatibility
     */
    public function checkAndChangeMarketplaceName()
    {
        $id_shop = (_PS_VERSION_ < 1.5 ? null : (int)$lengow_order->id_shop);
        if (LengowCheck::isValidAuth($id_shop)) {
            $connector = new LengowConnector(
                LengowMain::getAccessToken($id_shop),
                LengowMain::getSecretCustomer($id_shop)
            );
            $results = $connector->get(
                '/v3.0/orders',
                array(
                    'marketplace_order_id'  => $this->id_lengow,
                    'marketplace'           => $this->lengow_marketplace,
                    'account_id'            => LengowMain::getIdAccount($id_shop)
                ),
                'stream'
            );
            if (is_null($results)) {
                return;
            }
            $results = Tools::jsonDecode($results);
            if (isset($results->error)) {
                return;
            }
            foreach ($results->results as $order) {
                if ($this->lengow_marketplace != (string)$order->marketplace) {
                    $update = 'UPDATE '._DB_PREFIX_.'lengow_orders
                        SET `marketplace` = \''.pSQL(Tools::strtolower((string)$order->marketplace)).'\'
                        WHERE `id_order` = \''.pSQL((int)$this->id).'\'
                    ';
                    DB::getInstance()->execute($update);
                    $this->loadLengowFields();
                }
            }
        }
    }

    /**
     * Return type value
     *
     * @param string $type Type (import or wsdl)
     *
     * @return mixed
     */
    public static function getOrderLogType($type = null)
    {
        switch ($type) {
            case 'import':
                $log_type = self::TYPE_LOG_IMPORT;
                break;
            case 'wsdl':
                $log_type = self::TYPE_LOG_WSDL;
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
     * @param string    $lengow_id              Lengow order id
     * @param integer    $delivery_address_id    Id delivery address
     * @param string    $type                   Type (import or wsdl)
     *
     * @return mixed
     */
    public static function orderIsInError($lengow_id, $delivery_address_id, $type = 'import')
    {
        $log_type = LengowOrder::getOrderLogType($type);
        // check if log already exists for the given order id
        $query = 'SELECT lli.`message`, lli.`date` FROM `'._DB_PREFIX_.'lengow_logs_import` lli
            LEFT JOIN `'._DB_PREFIX_.'lengow_orders` lo ON lli.`id_order_lengow` = lo.`id`
            WHERE lo.`id_order_lengow` = \''.pSQL($lengow_id).'\'
            AND lo.`delivery_id_address` = \''.pSQL((int)$delivery_address_id).'\'
            AND lli.`type` = \''.pSQL($log_type).'\'
            AND lli.`is_finished` = 0';
        return Db::getInstance()->getRow($query);
    }

    /**
     * Check if log already exists for the given order
     *
     * @param string    $id_order_lengow        id lengow order
     * @param string    $type                   type (import or wsdl)
     * @param boolean   $finished               log finished (true or false)
     *
     * @return mixed
     */
    public static function getOrderLogs($id_order_lengow, $type = null, $finished = null)
    {
        $log_type = LengowOrder::getOrderLogType($type);
        if (!is_null($log_type)) {
            $and_type = ' AND `id_order_lengow` = \''.pSQL((int)$log_type).'\'';
        } else {
            $and_type = '';
        }
        if (!is_null($finished)) {
            $and_finished = ($finished ? ' AND `id_order_lengow` = 1' :  'AND `id_order_lengow` = 0');
        } else {
            $and_finished = '';
        }
        // check if log already exists for the given order id
        $query = 'SELECT `id`, `is_finished`, `message`, `date`, `type` FROM `'._DB_PREFIX_.'lengow_logs_import`
            WHERE `id_order_lengow` = \''.pSQL((int)$id_order_lengow).'\''.$and_type.$and_finished;
        return Db::getInstance()->executeS($query);
    }

    /**
     * Add log information in lengow_logs_import table
     *
     * @param integer   $id_order_lengow    id lengow order
     * @param string    $message            error message
     * @param string    $type               type (import or wsdl)
     * @param integer   $finished           error is finished
     *
     */
    public static function addOrderLog($id_order_lengow, $message = '', $type = 'import', $finished = 0)
    {
        $log_type = LengowOrder::getOrderLogType($type);
        return Db::getInstance()->autoExecute(
            _DB_PREFIX_.'lengow_logs_import',
            array(
                'is_finished'       => (int)$finished,
                'date'              => date('Y-m-d H:i:s'),
                'message'           => pSQL($message),
                'type'              => pSQL((int)$log_type),
                'id_order_lengow'   => pSQL((int)$id_order_lengow)
            ),
            'INSERT'
        );
    }

    /**
     * Removes all order logs
     *
     * @param integer   $id_order_lengow    id_order_lengow
     * @param string    $log_type           type (import or wsdl)
     *
     * @return boolean
     */
    public static function finishOrderLogs($id_order_lengow, $type = 'import')
    {
        $log_type = LengowOrder::getOrderLogType($type);
        $query = 'SELECT `id` FROM `'._DB_PREFIX_.'lengow_logs_import`
            WHERE `id_order_lengow` = \''.pSQL((int)$id_order_lengow).'\'
            AND `type` = \''.pSQL((int)$log_type).'\'';
        $order_logs = Db::getInstance()->executeS($query);

        $update_success = 0;
        foreach ($order_logs as $order_log) {
            $result = Db::getInstance()->autoExecute(
                _DB_PREFIX_.'lengow_logs_import',
                array('is_finished' => 1),
                'UPDATE',
                '`id` = \''.pSQL((int)$order_log['id']).'\''
            );
            if ($result) {
                $update_success++;
            }
        }

        return (count($order_logs) == $update_success ? true : false);
    }
}
