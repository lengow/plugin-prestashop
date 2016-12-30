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
 * @package   LengowMarketplace
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2017 Lengow SAS
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
    public static $validActions = array(
        'ship',
        'cancel'
    );

    /**
     * @var array all markeplaces allowed for an account ID
     */
    public static $markeplaces = array();
    
    /**
     * @var mixed the current marketplace
     */
    public $marketplace;
    
    /**
     * @var string the code of the marketplace
     */
    public $name;

    /**
     * @var string the old code of the markeplace for v2 compatibility
     */
    public $legacyCode;

    /**
     * @var string the name of the marketplace
     */
    public $labelName;

    /**
     * @var integer ID Shop
     */
    public $idShop;
    
    /**
     * @var boolean if the marketplace is loaded
     */
    public $isLoaded = false;
    
    /**
     * @var array Lengow states => marketplace states
     */
    public $statesLengow = array();
    
    /**
     * @var array marketplace states => Lengow states
     */
    public $states = array();
    
    /**
     * @var array all possible actions of the marketplace
     */
    public $actions = array();

    /**
     * @var array all possible values for actions of the marketplace
     */
    public $argValues = array();
   
    /**
     * @var array all carriers of the marketplace
     */
    public $carriers = array();

    /**
    * Construct a new Markerplace instance with marketplace API
    *
    * @param string  $name   name of the marketplace
    * @param integer $idShop Prestashop shop id
    */
    public function __construct($name, $idShop = null)
    {
        $this->idShop = $idShop;
        $this->loadApiMarketplace();
        $this->name = Tools::strtolower($name);
        if (!isset(self::$markeplaces[$this->idShop]->{$this->name})) {
            throw new LengowException(
                LengowMain::setLogMessage(
                    'lengow_log.exception.marketplace_not_present',
                    array('marketplace_name' => $this->name)
                )
            );
        }
        $this->marketplace = self::$markeplaces[$this->idShop]->{$this->name};
        if (!empty($this->marketplace)) {
            $this->legacyCode = $this->marketplace->legacy_code;
            $this->labelName = $this->marketplace->name;
            foreach ($this->marketplace->orders->status as $key => $state) {
                foreach ($state as $value) {
                    $this->statesLengow[(string)$value] = (string)$key;
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
                foreach ($action->optional_args as $optionalArg) {
                    $this->actions[(string)$key]['optional_args'][(string)$optionalArg] = $optionalArg;
                }
                foreach ($action->args_description as $key => $argDescription) {
                    $validValues = array();
                    if (isset($argDescription->valid_values)) {
                        foreach ($argDescription->valid_values as $code => $validValue) {
                            $validValues[(string)$code] = (string)$validValue->label;
                        }
                    }
                    $this->argValues[(string)$key] = array(
                        'default_value'      => (string)$argDescription->default_value,
                        'accept_free_values' => (bool)$argDescription->accept_free_values,
                        'valid_values'       => $validValues
                    );
                }
            }
            if (isset($this->marketplace->orders->carriers)) {
                foreach ($this->marketplace->orders->carriers as $key => $carrier) {
                    $this->carriers[(string)$key] = (string)$carrier->label;
                }
            }
            $this->isLoaded = true;
        }
    }

    /**
     * Load the json configuration of all marketplaces
     */
    public function loadApiMarketplace()
    {
        if (!array_key_exists($this->idShop, self::$markeplaces)) {
            $result = LengowConnector::queryApi('get', '/v3.0/marketplaces', $this->idShop);
            self::$markeplaces[$this->idShop] = $result;
        }
    }

    /**
    * If marketplace exist in xml configuration file
    *
    * @return boolean
    */
    public function isLoaded()
    {
        return $this->isLoaded;
    }

    /**
    * Get the real lengow's state
    *
    * @param string $name marketplace order state
    *
    * @return mixed (string or false)
    */
    public function getStateLengow($name)
    {
        if (array_key_exists($name, $this->statesLengow)) {
            return $this->statesLengow[$name];
        }
        return false;
    }

    /**
    * Get the marketplace's state
    *
    * @param string $name Lengow order state
    *
    * @return mixed (string or false)
    */
    public function getState($name)
    {
        if (array_key_exists($name, $this->states)) {
            return $this->states[$name];
        }
        return false;
    }

    /**
    * Get the action with parameters
    *
    * @param string $name action's name
    *
    * @return mixed (array or false)
    */
    public function getAction($name)
    {
        if (array_key_exists($name, $this->actions)) {
            return $this->actions[$name];
        }
        return false;
    }

    /**
    * Get the default value for argument
    *
    * @param string $name argument's name
    *
    * @return mixed (string or false)
    */
    public function getDefaultValue($name)
    {
        if (array_key_exists($name, $this->argValues)) {
            $defaultValue = $this->argValues[$name]['default_value'];
            if (!empty($defaultValue)) {
                return $defaultValue;
            }
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
     * @param string      $action      Lengow order actions type (ship or cancel)
     * @param LengowOrder $order       Lengow order instance
     * @param string      $idOrderLine Lengow order line id
     *
     * @throws Exception marketplace action not present / shop id required / marketplace name required
     *                   argument is required / action not created
     *
     * @return boolean
     */
    public function callAction($action, $order, $idOrderLine = null)
    {
        try {
            if (!in_array($action, self::$validActions)) {
                throw new LengowException(
                    LengowMain::setLogMessage('lengow_log.exception.action_not_valid', array('action' => $action))
                );
            }
            if (!$this->getAction($action)) {
                throw new LengowException(
                    LengowMain::setLogMessage(
                        'lengow_log.exception.marketplace_action_not_present',
                        array('action' => $action)
                    )
                );
            }
            if ((int)$order->lengowIdShop == 0) {
                throw new LengowException(
                    LengowMain::setLogMessage('lengow_log.exception.shop_id_require')
                );
            }
            if (Tools::strlen($order->lengowMarketplaceName) == 0) {
                throw new LengowException(
                    LengowMain::setLogMessage('lengow_log.exception.marketplace_name_require')
                );
            }
            $params = array();
            $actions = $this->getAction($action);
            if (isset($actions['args']) && isset($actions['optional_args'])) {
                $allArgs = array_merge($actions['args'], $actions['optional_args']);
            } elseif (isset($actions['args'])) {
                $allArgs = $actions['args'];
            } else {
                $allArgs = array();
            }
            // get delivery address for carrier, shipping method and tracking url
            $deliveryAddress = new Address($order->id_address_delivery);
            // get tracking number for tracking number and tracking url
            if (_PS_VERSION_ >= '1.5') {
                $idOrderCarrier = $order->getIdOrderCarrier();
                $orderCarrier = new OrderCarrier($idOrderCarrier);
                $trackingNumber = $orderCarrier->tracking_number;
                if ($trackingNumber == '') {
                    $trackingNumber = $order->shipping_number;
                }
            } else {
                $trackingNumber = $order->shipping_number;
            }
            foreach ($allArgs as $arg) {
                switch ($arg) {
                    case 'tracking_number':
                        $params[$arg] = $trackingNumber;
                        break;
                    case 'carrier':
                    case 'shipping_method':
                        if (!isset($deliveryAddress->id_country) || $deliveryAddress->id_country == 0) {
                            if (isset($actions['optional_args']) && in_array('carrier', $actions['optional_args'])) {
                                continue;
                            }
                            throw new LengowException(
                                LengowMain::setLogMessage('lengow_log.exception.no_delivery_country_in_order')
                            );
                        }
                        if ($order->lengowCarrier != '') {
                            $carrierName = (string) $order->lengowCarrier;
                        } else {
                            $carrier = new Carrier($order->id_carrier);
                            $carrierName = $carrier->name;
                        }
                        $params[$arg] = $carrierName;
                        break;
                    case 'tracking_url':
                        if ($trackingNumber != '') {
                            $idActiveCarrier = LengowCarrier::getActiveCarrierByCarrierId(
                                $order->id_carrier,
                                $deliveryAddress->id_country
                            );
                            $idCarrier = $idActiveCarrier ? $idActiveCarrier : $order->id_carrier;
                            $carrier = new Carrier($idCarrier);
                            $trackingUrl = str_replace('@', $trackingNumber, $carrier->url);
                            // Add default value if tracking url is empty
                            if (Tools::strlen($trackingUrl) == 0) {
                                $defaultValue = $this->getDefaultValue((string)$arg);
                                $trackingUrl = $defaultValue ? $defaultValue : $arg.' not available';
                            }
                            $params[$arg] = $trackingUrl;
                        }
                        break;
                    case 'shipping_price':
                        $params[$arg] = $order->total_shipping;
                        break;
                    case 'shipping_date':
                        $params[$arg] = date('c');
                        break;
                    default:
                        $defaultValue = $this->getDefaultValue((string)$arg);
                        $paramValue = $defaultValue ? $defaultValue : $arg.' not available';
                        $params[$arg] = $paramValue;
                        break;
                }
            }
            if ($idOrderLine) {
                $params['line'] = $idOrderLine;
            }
            if (isset($actions['args'])) {
                foreach ($actions['args'] as $arg) {
                    if (!isset($params[$arg]) || Tools::strlen($params[$arg]) == 0) {
                        throw new LengowException(
                            LengowMain::setLogMessage(
                                'lengow_log.exception.arg_is_required',
                                array('arg_name' => $arg)
                            )
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
            $params['marketplace_order_id'] = $order->lengowMarketplaceSku;
            $params['marketplace'] = $order->lengowMarketplaceName;
            $params['action_type'] = $action;
            $result = LengowConnector::queryApi(
                'get',
                '/v3.0/orders/actions/',
                $order->lengowIdShop,
                array_merge($params, array("queued" => "True"))
            );
            if (isset($result->error) && isset($result->error->message)) {
                throw new LengowException($result->error->message);
            }
            if (isset($result->count) && $result->count > 0) {
                foreach ($result->results as $row) {
                    $update = LengowAction::updateAction(
                        array(
                            'id_order'    => $order->id,
                            'action_type' => $action,
                            'action_id'   => $row->id,
                            'parameters'  => $params
                        )
                    );
                    // if update doesn't work, create new action
                    if (!$update) {
                        LengowAction::createAction(
                            array(
                                'id_order'        => $order->id,
                                'action_type'     => $action,
                                'action_id'       => $row->id,
                                'parameters'      => $params,
                                'marketplace_sku' => $order->lengowMarketplaceSku
                            )
                        );
                    }
                }
            } else {
                if (!LengowConfiguration::get('LENGOW_IMPORT_PREPROD_ENABLED')) {
                    $result = LengowConnector::queryApi(
                        'post',
                        '/v3.0/orders/actions/',
                        $order->lengowIdShop,
                        $params
                    );
                    if (isset($result->id)) {
                        LengowAction::createAction(
                            array(
                                'id_order'        => $order->id,
                                'action_type'     => $action,
                                'action_id'       => $result->id,
                                'parameters'      => $params,
                                'marketplace_sku' => $order->lengowMarketplaceSku
                            )
                        );
                    } else {
                        throw new LengowException(
                            LengowMain::setLogMessage(
                                'lengow_log.exception.action_not_created',
                                array('error_message' => Tools::jsonEncode($result))
                            )
                        );
                    }
                }
                // Create log for call action
                $paramList = false;
                foreach ($params as $param => $value) {
                    $paramList.= !$paramList ? '"'.$param.'": '.$value : ' -- "'.$param.'": '.$value;
                }
                LengowMain::log(
                    'API-OrderAction',
                    LengowMain::setLogMessage('log.order_action.call_tracking', array('parameters' => $paramList)),
                    false,
                    $order->lengowMarketplaceSku
                );
            }
            return true;
        } catch (LengowException $e) {
            $errorMessage = $e->getMessage();
        } catch (Exception $e) {
            $errorMessage = '[Prestashop Error] "'.$e->getMessage().'" '.$e->getFile().' | '.$e->getLine();
        }
        if (isset($errorMessage)) {
            if ($order->lengowProcessState != LengowOrder::PROCESS_STATE_FINISH) {
                LengowOrder::addOrderLog($order->lengowId, $errorMessage, 'send');
            }
            $decodedMessage = LengowMain::decodeLogMessage($errorMessage, 'en');
            LengowMain::log(
                'API-OrderAction',
                LengowMain::setLogMessage(
                    'log.order_action.call_action_failed',
                    array('decoded_message' => $decodedMessage)
                ),
                false,
                $order->lengowMarketplaceSku
            );
            return false;
        }
    }

    /**
     * Get Marketplace Sku by Shop
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return array
     */
    public static function getMarketplacesByShop($idShop)
    {
        $marketplaceCollection = array();
        $result = LengowConnector::queryApi('get', '/v3.0/marketplaces', $idShop);
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
