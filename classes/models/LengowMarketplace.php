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
 * Lengow Marketplace Class
 */
class LengowMarketplace
{
    /**
     * @var array all valid actions
     */
    public static $VALID_ACTIONS = array(
        'ship' ,
        'cancel'
    );

    /**
     * @var mixed all markeplaces allowed for an account ID
     */
    public static $MARKETPLACES = array();
    
    /**
     * @var mixed the current marketplace
     */
    public $marketplace;
    
    /**
     * @var string the name of the marketplace
     */
    public $name;

    /**
     * @var integer ID Shop
     */
    public $id_shop;
    
    /**
     * @var boolean if the marketplace is loaded
     */
    public $is_loaded = false;
    
    /**
     * @var array Lengow states => marketplace states
     */
    public $states_lengow = array();
    
    /**
     * @var array marketplace states => Lengow states
     */
    public $states = array();
    
    /**
     * @var array all possible actions of the marketplace
     */
    public $actions = array();
   
    /**
     * @var array all carriers of the marketplace
     */
    public $carriers = array();

    /**
    * Construct a new Markerplace instance with xml configuration.
    *
    * @param string  $name    The name of the marketplace
    * @param integer $id_shop ID Shop for connector
    */
    public function __construct($name, $id_shop = null)
    {
        $this->id_shop = $id_shop;
        $this->loadApiMarketplace();
        $this->name = Tools::strtolower($name);
        if (!isset(self::$MARKETPLACES[$this->id_shop]->{$this->name})) {
            throw new LengowException(
                LengowMain::setLogMessage('lengow_log.exception.marketplace_not_present', array(
                    'markeplace_name' => $this->name
                ))
            );
        }
        $this->marketplace = self::$MARKETPLACES[$this->id_shop]->{$this->name};
        if (!empty($this->marketplace)) {
            $this->label_name = $this->marketplace->name;
            foreach ($this->marketplace->orders->status as $key => $state) {
                foreach ($state as $value) {
                    $this->states_lengow[(string)$value] = (string)$key;
                    $this->states[(string)$key][(string)$value] = (string)$value;
                }
            }
            foreach ($this->marketplace->orders->actions as $key => $action) {
                foreach ($action->status as $state) {
                    $this->actions[(string)$key]['status'][(string)$state] = (string)$state;
                }
                foreach ($action->args as $arg) {
                    $this->actions[(string)$key]['args'][(string)$arg] = (string)$arg;
                }
                foreach ($action->optional_args as $optional_arg) {
                    $this->actions[(string)$key]['optional_args'][(string)$optional_arg] = $optional_arg;
                }
            }
            if (isset($this->marketplace->orders->carriers)) {
                foreach ($this->marketplace->orders->carriers as $key => $carrier) {
                    $this->carriers[(string)$key] = (string)$carrier->label;
                }
            }
            $this->is_loaded = true;
        }
    }

    /**
     * Load the json configuration of all marketplaces
     */
    public function loadApiMarketplace()
    {
        if (!array_key_exists($this->id_shop, self::$MARKETPLACES)) {
            $result = LengowConnector::queryApi('get', '/v3.0/marketplaces', $this->id_shop);
            self::$MARKETPLACES[$this->id_shop] = $result;
        }
    }

    /**
    * If marketplace exist in xml configuration file
    *
    * @return boolean
    */
    public function isLoaded()
    {
        return $this->is_loaded;
    }

    /**
    * Get the real lengow's state
    *
    * @param string $name The marketplace state
    *
    * @return string The lengow state
    */
    public function getStateLengow($name)
    {
        if (array_key_exists($name, $this->states_lengow)) {
            return $this->states_lengow[$name];
        }
    }

    /**
    * Get the marketplace's state
    *
    * @param string $name The lengow state
    *
    * @return array
    */
    public function getState($name)
    {
        if (array_key_exists($name, $this->states)) {
            return $this->states[$name];
        }
    }

    /**
    * Get the action with parameters
    *
    * @param string $name The action's name
    *
    * @return array
    */
    public function getAction($name)
    {
        if (array_key_exists($name, $this->actions)) {
            return $this->actions[$name];
        }
    }

    /**
     * Get carrier name by code
     *
     * @param string $code carrier given in the API
     *
     * @return mixed
     */
    public function getCarrierByCode($code)
    {
        if (array_key_exists($code, $this->carriers)) {
            return $this->carriers[$code];
        }
        if (array_key_exists(Tools::strtoupper($code), $this->carriers)) {
            return $this->carriers[Tools::strtoupper($code)];
        }
        return false;
    }

    /**
    * Check if a status is valid for action
    *
    * @param array   $action_status valid status for action
    * @param integer $id_status     curent status id
    *
    * @return boolean
    */
    public function isValidState($action_status, $id_status)
    {
        foreach ($action_status as $status) {
            if ($id_status == LengowMain::getOrderState($status)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Is carrier require when shipping
     *
     * @return boolean
     */
    public function isRequireCarrier()
    {
        if (!isset($this->actions['ship']['args'])) {
            return false;
        }
        if (in_array("carrier", $this->actions['ship']['args'])) {
            return true;
        }
        return false;
    }

    /**
     * Is marketplace contain order Line
     *
     * @param string $action (ship / cancel / refund)
     *
     * @return boolean
     */
    public function containOrderLine($action)
    {
        $actions = $this->actions[$action];
        if (isset($actions['args']) && is_array($actions['args'])) {
            if (in_array('line', $actions['args'])) {
                return true;
            }
        }
        if (isset($actions['optional_args']) && is_array($actions['optional_args'])) {
            if (in_array('line', $actions['optional_args'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Call API action and create action in lengow_actions table
     *
     * @param integer $order
     * @param string  $id_order_line
     *
     * @return boolean
     */
    public function callAction($action, $order, $id_order_line = null)
    {
        try {
            if (!in_array($action, self::$VALID_ACTIONS)) {
                throw new LengowException(
                    LengowMain::setLogMessage('lengow_log.exception.action_not_valid', array('action' => $action))
                );
            }
            if (!isset($this->actions[$action])) {
                throw new LengowException(
                    LengowMain::setLogMessage('lengow_log.exception.marketplace_action_not_present', array(
                        'action' => $action
                    ))
                );
            }
            if ((int)$order->lengow_id_shop == 0) {
                throw new LengowException(
                    LengowMain::setLogMessage('lengow_log.exception.shop_id_require')
                );
            }
            if (Tools::strlen($order->lengow_marketplace_name) == 0) {
                throw new LengowException(
                    LengowMain::setLogMessage('lengow_log.exception.marketplace_name_require')
                );
            }
            $params = array();
            $actions = $this->actions[$action];
            if (isset($actions['args']) && isset($actions['optional_args'])) {
                $all_args = array_merge($actions['args'], $actions['optional_args']);
            } elseif (isset($actions['args'])) {
                $all_args = $actions['args'];
            } else {
                $all_args = array();
            }
            foreach ($all_args as $arg) {
                switch ($arg) {
                    case 'tracking_number':
                        if (_PS_VERSION_ >= '1.5') {
                            $id_order_carrier = $order->getIdOrderCarrier();
                            $order_carrier = new OrderCarrier($id_order_carrier);
                            $tracking_number = $order_carrier->tracking_number;
                            if ($tracking_number == '') {
                                $tracking_number = $order->shipping_number;
                            }
                        } else {
                            $tracking_number = $order->shipping_number;
                        }
                        $params['tracking_number'] = $tracking_number;
                        break;
                    case 'carrier':
                        $deliveryAddress = new Address($order->id_address_delivery);
                        if (!isset($deliveryAddress->id_country) || $deliveryAddress->id_country == 0) {
                            if (isset($actions['optional_args']) && in_array('carrier', $actions['optional_args'])) {
                                continue;
                            }
                            throw new LengowException(
                                LengowMain::setLogMessage('lengow_log.exception.no_delivery_country_in_order')
                            );
                        }
                        $params['carrier'] = LengowCarrier::getMarketplaceCarrier(
                            $order->id_carrier,
                            $deliveryAddress->id_country
                        );
                        if (!$params['carrier']) {
                            if (isset($actions['optional_args']) && in_array('carrier', $actions['optional_args'])) {
                                continue;
                            }
                            if ($order->lengow_carrier != '') {
                                $countryName = Country::getNameById(
                                    Context::getContext()->language->id,
                                    $deliveryAddress->id_country
                                );
                                throw new LengowException(
                                    LengowMain::setLogMessage('lengow_log.exception.match_carrier_with_country', array(
                                        'carrier_name' => $order->lengow_carrier,
                                        'country_name' => $countryName
                                    ))
                                );
                            }
                            $carrier = new Carrier($order->id_carrier);
                            $params['carrier'] = $carrier->name;
                        }
                        break;
                    case 'tracking_url':
                        if (_PS_VERSION_ >= '1.5') {
                            $id_order_carrier = $order->getIdOrderCarrier();
                            $order_carrier = new OrderCarrier($id_order_carrier);
                            $tracking_number = $order_carrier->tracking_number;
                            if ($tracking_number == '') {
                                $tracking_number = $order->shipping_number;
                            }
                        } else {
                            $tracking_number = $order->shipping_number;
                        }
                        if ($tracking_number != '') {
                            $carrier = new Carrier($order->id_carrier);
                            $params['tracking_url'] = str_replace('@', $tracking_number, $carrier->url);
                        }
                        break;
                    case 'shipping_price':
                        $params['shipping_price'] = $order->total_shipping;
                        break;
                    default:
                        break;
                }
            }
            if ($id_order_line) {
                $params['line'] = $id_order_line;
            }
            if (isset($actions['args'])) {
                foreach ($actions['args'] as $arg) {
                    if (!isset($params[$arg]) || Tools::strlen($params[$arg]) == 0) {
                        throw new LengowException(
                            LengowMain::setLogMessage('lengow_log.exception.arg_is_required', array(
                                'arg_name' => $arg
                            ))
                        );
                    }
                }
            }
            if (isset($actions['optional_args'])) {
                foreach ($actions['optional_args'] as $arg) {
                    if (isset($params[$arg]) && Tools::strlen($params[$arg]) == 0) {
                        unset($params[$arg]);
                    }
                }
            }
            $params['marketplace_order_id'] = $order->lengow_marketplace_sku;
            $params['marketplace'] = $order->lengow_marketplace_name;
            $params['action_type'] = $action;
            $result = LengowConnector::queryApi(
                'get',
                '/v3.0/orders/actions/',
                $order->lengow_id_shop,
                array_merge($params, array("queued" => "True"))
            );
            if (isset($result->error) && isset($result->error->message)) {
                throw new LengowException($result->error->message);
            }
            if (isset($result->count) && $result->count > 0) {
                foreach ($result->results as $row) {
                    $update = LengowAction::updateAction(array(
                        'id_order'    => $order->id,
                        'action_type' => $action,
                        'action_id'   => $row->id,
                        'parameters'  => $params
                    ));
                    // if update doesn't work, create new action
                    if (!$update) {
                        LengowAction::createAction(array(
                            'id_order'        => $order->id,
                            'action_type'     => $action,
                            'action_id'       => $row->id,
                            'parameters'      => $params,
                            'marketplace_sku' => $order->lengow_marketplace_sku
                        ));
                    }
                }
            } else {
                if (!Configuration::get('LENGOW_IMPORT_PREPROD_ENABLED')) {
                    $result = LengowConnector::queryApi(
                        'post',
                        '/v3.0/orders/actions/',
                        $order->lengow_id_shop,
                        $params
                    );
                    if (isset($result->id)) {
                        LengowAction::createAction(array(
                            'id_order'        => $order->id,
                            'action_type'     => $action,
                            'action_id'       => $result->id,
                            'parameters'      => $params,
                            'marketplace_sku' => $order->lengow_marketplace_sku
                        ));
                    }
                }
                // Create log for call action
                $param_list = false;
                foreach ($params as $param => $value) {
                    $param_list.= !$param_list ? '"'.$param.'": '.$value : ' -- "'.$param.'": '.$value;
                }
                LengowMain::log(
                    'API-OrderAction',
                    LengowMain::setLogMessage('log.order_action.call_tracking', array('parameters' => $param_list)),
                    false,
                    $order->lengow_marketplace_sku
                );
            }
            return true;
        } catch (LengowException $e) {
            $error_message = $e->getMessage();
        } catch (Exception $e) {
            $error_message = '[Prestashop Error] "'.$e->getMessage().'" '.$e->getFile().' | '.$e->getLine();
        }
        if (isset($error_message)) {
            if ($order->lengow_process_state != LengowOrder::PROCESS_STATE_FINISH) {
                LengowOrder::addOrderLog($order->lengow_id, $error_message, 'send');
            }
            $decoded_message = LengowMain::decodeLogMessage($error_message, 'en');
            LengowMain::log(
                'API-OrderAction',
                LengowMain::setLogMessage('log.order_action.call_action_failed', array(
                    'decoded_message' => $decoded_message
                )),
                false,
                $order->lengow_marketplace_sku
            );
            return false;
        }
    }

    /**
     * Get Marketplace Sku by Shop
     *
     * @param $id_shop
     *
     * @return array
     */
    public static function getMarketplacesByShop($id_shop)
    {
        $marketplaceCollection = array();
        $result = LengowConnector::queryApi('get', '/v3.0/marketplaces', $id_shop);
        if ($result) {
            foreach ($result as $marketplaceSku => $value) {
                // This line is useless, but Prestashop validator require it
                $value = $value;
                $marketplaceCollection[] = $marketplaceSku;
            }
        }
        return $marketplaceCollection;
    }
}
