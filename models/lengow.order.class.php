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
 * The Lengow Order Class.
 *
 */

class LengowOrder extends Order
{

    /**
     * Version.
     */
    const VERSION = '1.0.0';

    /**
     * Marketplace's name.
     */
    public $lengow_marketplace;

    /**
     * Message.
     */
    public $lengow_message;

    /**
     * Total paid on marketplace.
     */
    public $lengow_total_paid;

    /**
     * Carrier from marketplace.
     */
    public $lengow_carrier;

    /**
     * Tracking.
     */
    public $lengow_tracking;

    /**
     * Extra information (json node form import).
     */
    public $lengow_extra;

    /**
     * Is importing, prevent multiple import
     */
    public $is_import;

    /**
     * Lengow order id
     *
     * @var string
     */
    public $id_lengow;

    /**
     * Lengow feed id
     *
     * @var integer
     */
    public $id_feed_lengow;

    /**
     * Data of lengow order
     *
     * @var SimpleXmlElement
     */
    public $data;

    /**
     * Order is already fully imported
     *
     * @var bool
     */
    public $is_finished = false;

    /**
     * First time order is being processed
     *
     * @var bool
     */
    public $first_import = true;

    /**
     * Log message saved in DB
     *
     * @var string
     */
    public $log_message = null;


    /**
     * Order marketplace
     *
     * @var LengowMarketplace
     */
    protected $marketplace;

    /**
     * @var boolean order is disabled (ready to be reimported)
     */
    public $is_disabled;

    /**
     * Construct a Lengow order based on Prestashop order.
     *
     * @param integer $id The Lengow order id
     */
    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
        $this->loadLengowFields();
    }

    /**
     * Get order id
     *
     * @return mixed
     */
    public static function getOrderIdFromLengowOrders($lengow_id, $feed_id, $marketplace)
    {
        $query = 'SELECT `id_order` FROM '
            . _DB_PREFIX_ . 'lengow_orders '
            . 'WHERE `id_order_lengow` = \'' . pSQL($lengow_id) . '\' '
            . 'AND `id_flux` = \'' . (int)$feed_id . '\' '
            . 'AND `marketplace` = \'' . pSQL(Tools::strtolower($marketplace)) . '\' '
            . 'ORDER BY `id_order` DESC';
        $r = Db::getInstance()->getRow($query);
        if ($r) {
            return $r['id_order'];
        }
        return false;
    }

    /**
     * Load information from lengow_orders table
     *
     * @param integer $id_order The order ID
     * @return boolean.
     */
    protected function loadLengowFields()
    {
        $query = 'SELECT lo.`id_order_lengow`, lo.`id_flux`, lo.`marketplace` , lo.`message` , lo.`total_paid` , lo.`carrier` , lo.`tracking` , lo.`extra`, lo.`is_disabled` '
            . 'FROM `' . _DB_PREFIX_ . 'lengow_orders` lo '
            . 'WHERE lo.id_order = \'' . (int)$this->id . '\'';

        if ($result = Db::getInstance()->getRow($query)) {
            $this->id_lengow = $result['id_order_lengow'];
            $this->id_feed_lengow = $result['id_flux'];
            $this->lengow_marketplace = $result['marketplace'];
            $this->lengow_message = $result['message'];
            $this->lengow_total_paid = $result['total_paid'];
            $this->lengow_carrier = $result['carrier'];
            $this->lengow_tracking = $result['tracking'];
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
     * @return bool true if order has been updated
     */
    public function updateState(LengowMarketplace $marketplace, $api_state, $tracking_number)
    {
        // get prestashop equivalent state id to Lengow API state
        $id_order_state = LengowCore::getOrderState($marketplace->getStateLengow($api_state));
        // if state is different between API and Prestashop
        if ($this->current_state != $id_order_state) {
            // Change state process to shipped
            if ($this->current_state == LengowCore::getOrderState('process') && $marketplace->getStateLengow($api_state) == 'shipped') {
                $history = new OrderHistory();
                $history->id_order = $this->id;
                $history->changeIdOrderState(LengowCore::getOrderState('shipped'), $this, true);
                $history->validateFields();
                $history->add();

                if (!empty($tracking_number)) {
                    $this->shipping_number = $tracking_number;
                    $this->validateFields();
                    $this->update();

                }
                LengowCore::getLogInstance()->write('state updated to shipped', true, $this->id_lengow);
                return true;
            } // Change state process or shipped to cancel
            elseif (($this->current_state == LengowCore::getOrderState('process') || $this->current_state == LengowCore::getOrderState('shipped'))
                && $marketplace->getStateLengow($api_state) == 'canceled'
            ) {
                $history = new OrderHistory();
                $history->id_order = $this->id;
                $history->changeIdOrderState(LengowCore::getOrderState('cancel'), $this, true);
                $history->validateFields();
                $history->add();
                LengowCore::getLogInstance()->write('state updated to cancel', true, $this->id_lengow);
                return true;
            }
        }
        return false;
    }

    /**
     * Loads informations contained in the module logs
     *
     * @return LengowOrderAbstract
     */
    protected function loadLengowLogsInfo()
    {
        $query = 'SELECT `is_finished`, `message` FROM '
            . _DB_PREFIX_ . 'lengow_logs_import '
            . 'WHERE `lengow_order_id` = \'' . pSQL($this->id_lengow) . '\'';
        $r = Db::getInstance()->getRow($query);
        if ($r) {
            $this->is_finished = (bool)$r['is_finished'];
            $this->log_message = $r['message'];
            $this->first_import = false;
        }
        return $this;
    }

    /**
     * Save a Lengow's order on database
     *
     * @param integer $id_order The Prestashop order ID
     * @param integer $id_order_lengow The marketplace order ID
     * @param integer $id_flux Feed id
     * @param string $marketplace Marketplace ID
     * @param string $message Message from marketplace
     * @param float $total_paid total paid on the marketplace
     * @param string $carrier Marketplace carrier
     * @param string $tracking Tracking from marketplace
     * @param string $extra Extra value (node json) of order imported
     * @param integer $id_lang Lang ID
     * @param integer $id_shop Shop ID
     * @param integer $id_shop_group Shop group ID
     *
     * @return boolean.
     */
    public static function addLengow(
        $id_order,
        $id_order_lengow,
        $id_flux,
        $marketplace,
        $message,
        $total_paid,
        $carrier,
        $tracking,
        $extra,
        $id_lang = null,
        $id_shop = null,
        $id_shop_group = null
    ) {
        $context = LengowCore::getContext();
        if (empty($id_lang)) {
            $id_lang = $context->language->id;
        }
        if (empty($id_shop)) {
            $id_shop = $context->shop->id;
        }
        $id_shop_group = $context->shop->id_shop_group;
        return Db::getInstance()->autoExecute(_DB_PREFIX_ . 'lengow_orders', array(
            'id_order' => (int)$id_order,
            'id_order_lengow' => pSQL($id_order_lengow),
            'id_shop' => (int)$id_shop,
            'id_shop_group' => (int)$id_shop_group,
            'id_lang' => (int)$id_lang,
            'id_flux' => (int)$id_flux,
            'marketplace' => pSQL($marketplace),
            'message' => pSQL($message),
            'total_paid' => (float)$total_paid,
            'carrier' => pSQL($carrier),
            'tracking' => pSQL($tracking),
            'extra' => pSQL($extra),
            'date_add' => date('Y-m-d H:i:s'),
        ), 'INSERT');
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
        $carrier = LengowCore::getExportCarrier();
        $id_zone = $context->country->id_zone;
        $id_currency = $context->cart->id_currency;
        $shipping_method = $carrier->getShippingMethod();
        if ($shipping_method != Carrier::SHIPPING_METHOD_FREE) {
            if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT) {
                return LengowCore::formatNumber($carrier->getDeliveryPriceByWeight($total, (int)$id_zone));
            } else {
                return LengowCore::formatNumber($carrier->getDeliveryPriceByPrice($total, (int)$id_zone,
                    (int)$id_currency));
            }
        }
        return 0;
    }


    /*
     * Rebuild OrderCarrier after validateOrder
     *
     * @param int $id_carrier
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
            return (int)Db::getInstance()->getValue('
					SELECT `id_order_carrier`
					FROM `' . _DB_PREFIX_ . 'order_carrier`
					WHERE `id_order` = ' . (int)$this->id);
        } else {
            return parent::getIdOrderCarrier();
        }
    }

    /**
     * Add Relay Point in Mondial Relay table
     *
     * @param array $relay informations
     * @return boolean true if success, false if not
     */
    public function addRelayPoint($relay)
    {
        if (!is_array($relay) || empty($relay)) {
            return false;
        }

        $insert_values = array(
            'id_customer' => (int)$this->id_customer,
            'id_method' => (int)$this->id_carrier,
            'id_cart' => (int)$this->id_cart,
            'id_order' => (int)$this->id,
            'MR_Selected_Num' => pSQL($relay['Num']),
            'MR_Selected_LgAdr1' => pSQL($relay['LgAdr1']),
            'MR_Selected_LgAdr2' => pSQL($relay['LgAdr2']),
            'MR_Selected_LgAdr3' => pSQL($relay['LgAdr3']),
            'MR_Selected_LgAdr4' => pSQL($relay['LgAdr4']),
            'MR_Selected_CP' => pSQL($relay['CP']),
            'MR_Selected_Ville' => pSQL($relay['Ville']),
            'MR_Selected_Pays' => pSQL($relay['Pays'])
        );

        if (_PS_VERSION_ < '1.5') {
            return Db::getInstance()->autoExecute(_DB_PREFIX_ . 'mr_selected', $insert_values, 'INSERT');
        } else {
            return DB::getInstance()->insert('mr_selected', $insert_values);
        }
    }

    public static function isFromLengow($id)
    {
        $r = Db::getInstance()->executeS(
            'SELECT `id_order_lengow` ' .
            'FROM `' . _DB_PREFIX_ . 'lengow_orders` ' .
            'WHERE `id_order` = ' . (int)$id);
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
        $id_error_lengow_state = LengowCore::getLengowErrorStateId();
        // update order to Lengow error state if not already updated
        if ($this->current_state !== $id_error_lengow_state) {
            $this->setCurrentState($id_error_lengow_state, Context::getContext()->employee->id);
        }
    }

    /**
     * Mark order as disabled in lengow_orders table
     *
     * @param integer $id prestashop order id
     *
     * @return boolean
     */
    public static function disable($id)
    {
        $update = 'UPDATE ' . _DB_PREFIX_ . 'lengow_orders '
            . 'SET `is_disabled` = 1 '
            . 'WHERE `id_order`= ' . (int)$id;
        return DB::getInstance()->execute($update);
    }
}