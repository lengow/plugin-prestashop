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
     * @var string Marketplace's name
     */
    public $lengow_marketplace;

    /**
     * @var string Message
     */
    public $lengow_message;

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
    * @var boolean Is importing, prevent multiple import
    */
    public $is_import;

    /**
     * @var string Lengow order id
     */
    public $id_lengow;

    /**
     * @var integer Lengow flux id
     */
    public $id_flux;

    /**
     * @var string Lengow order line id
     */
    public $id_order_line;

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
     * @var boolean order is disabled (ready to be reimported)
     */
    public $is_disabled;

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
     * Get order id
     *
     * @param string    $lengow_id          Lengow order id
     * @param integer   $order_line_id      order line id
     * @param string    $marketplace        marketplace name
     *
     * @return mixed
     */
    public static function getOrderIdFromLengowOrders($lengow_id, $order_line_id, $marketplace)
    {
        $query = 'SELECT lo.`id_order` FROM `'._DB_PREFIX_.'lengow_orders` lo
            LEFT JOIN `'._DB_PREFIX_.'lengow_order_line` lol ON lol.`id_order` = lo.`id_order`
            WHERE lo.`id_order_lengow` = \''.pSQL($lengow_id).'\'
            AND lo.`marketplace` = \''.pSQL(Tools::strtolower($marketplace)).'\'
            AND lol.`id_order_line` = \''.pSQL($order_line_id).'\'
            ORDER BY lo.`id_order` DESC
        ';
        $r = Db::getInstance()->getRow($query);
        if ($r) {
            return $r['id_order'];
        }
        return false;
    }

    /**
     * Get Id from Lengow order line
     *
     * @param integer   $order_id           prestashop order id
     * @param string    $order_line_id  order line id
     *
     * @return mixed
     */
    public static function getIdFromLengowOrderLine($order_id, $order_line_id)
    {
        $query = 'SELECT `id` FROM `'._DB_PREFIX_.'lengow_order_line`
            WHERE `id_order` = \''.pSQL($order_id).'\'
            AND `id_order_line` = \''.pSQL($order_line_id).'\'
        ';
        $r = Db::getInstance()->getRow($query);
        if ($r) {
            return $r['id'];
        }
        return false;
    }

    /**
     * Get order id from Lengow Order
     *
     * @param string    $lengow_id      Lengow order id
     * @param string    $marketplace    marketplace name
     *
     * @return array
     */
    public static function getOrderIdFromLengowOrder($lengow_id, $marketplace)
    {
        $query = 'SELECT `id_order` FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `id_order_lengow` = \''.pSQL($lengow_id).'\'
            AND `marketplace` = \''.pSQL(Tools::strtolower($marketplace)).'\'
            AND `is_disabled` = \'0\'
        ';
        return Db::getInstance()->executeS($query);
    }

    /**
     * Get order line from Lengow Order
     *
     * @param integer $order_id prestashop order id
     *
     * @return array
     */
    public static function getOrderLineFromLengowOrder($order_id)
    {
        $query = 'SELECT `id_order_line` FROM `'._DB_PREFIX_.'lengow_order_line`
            WHERE `id_order` = \''.pSQL($order_id).'\'
        ';
        return Db::getInstance()->executeS($query);
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
            lo.`id_flux`,
            lo.`id_order_line`,
            lo.`marketplace`,
            lo.`message`,
            lo.`total_paid`,
            lo.`carrier`,
            lo.`method`,
            lo.`tracking`,
            lo.`sent_marketplace`,
            lo.`extra`,
            lo.`is_disabled`
            FROM `'._DB_PREFIX_.'lengow_orders` lo
            WHERE lo.id_order = \''.(int)$this->id.'\'
        ';
        if ($result = Db::getInstance()->getRow($query)) {
            $this->id_lengow = $result['id_order_lengow'];
            $this->id_flux = $result['id_flux'];
            $this->id_order_line = $result['id_order_line'];
            $this->lengow_marketplace = $result['marketplace'];
            $this->lengow_message = $result['message'];
            $this->lengow_total_paid = $result['total_paid'];
            $this->lengow_carrier = $result['carrier'];
            $this->lengow_method = $result['method'];
            $this->lengow_tracking = $result['tracking'];
            $this->lengow_sent_marketplace = (bool)$result['sent_marketplace'];
            $this->lengow_extra = $result['extra'];
            $this->is_disabled = (bool)$result['is_disabled'];
            return true;
        } else {
            return false;
        }
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
                 &&  ($marketplace->getStateLengow($api_state) == 'shipped' || $marketplace->getStateLengow($api_state) == 'closed')
            ) {
                $history = new OrderHistory();
                $history->id_order = $this->id;
                $history->changeIdOrderState(LengowMain::getOrderState('shipped'), $this, true);
                $history->validateFields();
                $history->add();
                if (!empty($tracking_number)) {
                    $this->shipping_number = $tracking_number;
                    $this->validateFields();
                    $this->update();
                }
                LengowMain::getLogInstance()->write('state updated to shipped', true, $this->id_lengow);
                return true;
            } elseif (($this->getCurrentState() == LengowMain::getOrderState('accepted') || $this->getCurrentState() == LengowMain::getOrderState('shipped'))
                 && ($marketplace->getStateLengow($api_state) == 'canceled' || $marketplace->getStateLengow($api_state) == 'refused')
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
     * Get the shipping price with current method
     *
     * @param float $total The total of order
     *
     * @return float The shipping price.
     */
    public static function getShippingPrice($total)
    {
        $context = Context::getContext();
        $carrier = LengowMain::getExportCarrier();
        $id_zone = $context->country->id_zone;
        $id_currency = $context->cart->id_currency;
        $shipping_method = $carrier->getShippingMethod();
        if ($shipping_method != Carrier::SHIPPING_METHOD_FREE) {
            if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT) {
                return LengowMain::formatNumber($carrier->getDeliveryPriceByWeight($total, (int)$id_zone));
            } else {
                return LengowMain::formatNumber($carrier->getDeliveryPriceByPrice($total, (int)$id_zone, (int)$id_currency));
            }
        }
        return 0;
    }

    /**
     * Rebuild OrderCarrier after validateOrder
     *
     * @param int $id_carrier
     *
     * @return void
     */
    public function forceCarrier($id_carrier)
    {
        if ($id_carrier == '') {
            return null;
        }
        $this->id_carrier = $id_carrier;
        $this->update();
        if ($this->getIdOrderCarrier() != '') {
            $order_carrier = new OrderCarrier($this->getIdOrderCarrier());
            $order_carrier->id_carrier = $id_carrier;
            $order_carrier->update();
        } else {
            $order_carrier = new OrderCarrier();
            $order_carrier->id_order = $this->id;
            $order_carrier->id_carrier = $id_carrier;
            $order_carrier->add();
        }
    }

    public function getIdOrderCarrier()
    {
        if (_PS_VERSION_ < '1.5.5') {
            return (int)Db::getInstance()->getValue(
                'SELECT `id_order_carrier`
                FROM `'._DB_PREFIX_.'order_carrier`
                WHERE `id_order` = '.(int)$this->id
            );
        } else {
            return parent::getIdOrderCarrier();
        }
    }

    /**
     * Check if a lengow order
     *
     * @param integer   $id prestashop order id
     *
     * @return boolean
     */
    public static function isFromLengow($id)
    {
        $r = Db::getInstance()->executeS(
            'SELECT `id_order_lengow`
            FROM `'._DB_PREFIX_.'lengow_orders`
            WHERE `id_order` = '.(int)$id
        );
        if (empty($r) || $r[0]['id_order_lengow'] == '') {
            return false;
        } else {
            return true;
        }
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
     * Mark order as disabled in lengow_orders table
     *
     * @param integer   $id prestashop order id
     *
     * @return boolean
     */
    public static function disable($id)
    {
        $update = 'UPDATE '._DB_PREFIX_.'lengow_orders
            SET `is_disabled` = 1
            WHERE `id_order`= '.(int)$id;
        return DB::getInstance()->execute($update);
    }

    /**
     * Check and change the name of the marketplace for v3 compatibility
     */
    public function checkAndChangeMarketplaceName()
    {
        if (LengowCheck::isValidAuth()) {
            $connector = new LengowConnector(LengowMain::getAccessToken(), LengowMain::getSecretCustomer());
            $results = $connector->get(
                '/v3.0/orders',
                array(
                    'marketplace_order_id'  => $this->id_lengow,
                    'marketplace'           => $this->lengow_marketplace,
                    'account_id'            => LengowMain::getIdAccount()
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
                        WHERE `id_order` = \''.(int)$this->id.'\'
                    ';
                    DB::getInstance()->execute($update);
                    $this->loadLengowFields();
                }
            }
        }
    }
}
