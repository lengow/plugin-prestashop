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
     * @var array all marketplaces allowed for an account ID
     */
    public static $marketplaces = array();

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
     * Construct a new Marketplace instance with marketplace API
     *
     * @param string $name name of the marketplace
     *
     * @throws LengowException marketplace not present
     */
    public function __construct($name)
    {
        self::loadApiMarketplace();
        $this->name = Tools::strtolower($name);
        if (!isset(self::$marketplaces->{$this->name})) {
            throw new LengowException(
                LengowMain::setLogMessage(
                    'lengow_log.exception.marketplace_not_present',
                    array('marketplace_name' => $this->name)
                )
            );
        }
        $this->marketplace = self::$marketplaces->{$this->name};
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
                foreach ($action->args_description as $argKey => $argDescription) {
                    $validValues = array();
                    if (isset($argDescription->valid_values)) {
                        foreach ($argDescription->valid_values as $code => $validValue) {
                            $validValues[(string)$code] = isset($validValue->label)
                                ? (string)$validValue->label
                                : (string)$validValue;
                        }
                    }
                    $defaultValue = isset($argDescription->default_value)
                        ? (string)$argDescription->default_value
                        : '';
                    $acceptFreeValue = isset($argDescription->accept_free_values)
                        ? (bool)$argDescription->accept_free_values
                        : true;
                    $this->argValues[(string)$argKey] = array(
                        'default_value' => $defaultValue,
                        'accept_free_values' => $acceptFreeValue,
                        'valid_values' => $validValues
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
    public static function loadApiMarketplace()
    {
        if (count(self::$marketplaces) === 0) {
            self::$marketplaces = LengowConnector::queryApi('get', '/v3.0/marketplaces');
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
     * @return string|false
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
     * @return string|false
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
     * @return array|false
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
     * @return string|false
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
        if (isset($this->actions[$action])) {
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
        }
        return false;
    }

    /**
     * Call API action and create action in lengow_actions table
     *
     * @param string $action Lengow order actions type (ship or cancel)
     * @param LengowOrder $order Lengow order instance
     * @param string $idOrderLine Lengow order line id
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
            } elseif (!isset($actions['args']) && isset($actions['optional_args'])) {
                $allArgs = $actions['optional_args'];
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
                    case 'carrier_name':
                    case 'shipping_method':
                        if ($order->lengowCarrier != '') {
                            $carrierName = (string)$order->lengowCarrier;
                        } else {
                            if (!isset($deliveryAddress->id_country) || $deliveryAddress->id_country == 0) {
                                if (isset($actions['optional_args']) && in_array($arg, $actions['optional_args'])) {
                                    continue;
                                }
                                throw new LengowException(
                                    LengowMain::setLogMessage('lengow_log.exception.no_delivery_country_in_order')
                                );
                            }
                            // get marketplace id by marketplace name
                            $idMarketplace = LengowMarketplace::getIdMarketplace($order->lengowMarketplaceName);
                            $carrierName = LengowCarrier::getCarrierMarketplaceCode(
                                (int)$deliveryAddress->id_country,
                                $idMarketplace,
                                (int)$order->id_carrier
                            );
                        }
                        $params[$arg] = $carrierName;
                        break;
                    case 'tracking_url':
                        if ($trackingNumber != '') {
                            $idActiveCarrier = LengowCarrier::getIdActiveCarrierByIdCarrier(
                                (int)$order->id_carrier,
                                (int)$deliveryAddress->id_country
                            );
                            $idCarrier = $idActiveCarrier ? $idActiveCarrier : (int)$order->id_carrier;
                            $carrier = new Carrier($idCarrier);
                            $trackingUrl = str_replace('@', $trackingNumber, $carrier->url);
                            // Add default value if tracking url is empty
                            if (Tools::strlen($trackingUrl) == 0) {
                                if (isset($actions['optional_args']) && in_array($arg, $actions['optional_args'])) {
                                    continue;
                                }
                                $defaultValue = $this->getDefaultValue((string)$arg);
                                $trackingUrl = $defaultValue ? $defaultValue : $arg . ' not available';
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
                        if (isset($actions['optional_args']) && in_array($arg, $actions['optional_args'])) {
                            continue;
                        }
                        $defaultValue = $this->getDefaultValue((string)$arg);
                        $paramValue = $defaultValue ? $defaultValue : $arg . ' not available';
                        $params[$arg] = $paramValue;
                        break;
                }
            }
            if (!is_null($idOrderLine)) {
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
            $sendAction = true;
            // check if action is already created
            $getParams = array_merge($params, array('queued' => 'True'));
            // array key deletion for verification in get
            if (isset($getParams['shipping_date'])) {
                unset($getParams['shipping_date']);
            }
            $result = LengowConnector::queryApi('get', '/v3.0/orders/actions/', $getParams);
            if (isset($result->error) && isset($result->error->message)) {
                throw new LengowException($result->error->message);
            }
            if (isset($result->count) && $result->count > 0) {
                foreach ($result->results as $row) {
                    $orderActionId = LengowAction::getActionByActionId($row->id);
                    if ($orderActionId) {
                        $update = LengowAction::updateAction(
                            array(
                                'id_order' => $order->id,
                                'action_type' => $action,
                                'action_id' => $row->id,
                                'parameters' => $params
                            )
                        );
                        if ($update) {
                            $sendAction = false;
                        }
                    } else {
                        // if update doesn't work, create new action
                        LengowAction::createAction(
                            array(
                                'id_order' => $order->id,
                                'action_type' => $action,
                                'action_id' => $row->id,
                                'parameters' => $params,
                                'marketplace_sku' => $order->lengowMarketplaceSku
                            )
                        );
                        $sendAction = false;
                    }
                }
            }
            if ($sendAction) {
                if (!LengowConfiguration::get('LENGOW_IMPORT_PREPROD_ENABLED')) {
                    $result = LengowConnector::queryApi('post', '/v3.0/orders/actions/', $params);
                    if (isset($result->id)) {
                        LengowAction::createAction(
                            array(
                                'id_order' => $order->id,
                                'action_type' => $action,
                                'action_id' => $result->id,
                                'parameters' => $params,
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
                    $paramList .= !$paramList ? '"' . $param . '": ' . $value : ' -- "' . $param . '": ' . $value;
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
            $errorMessage = '[Prestashop Error] "' . $e->getMessage() . '" ' . $e->getFile() . ' | ' . $e->getLine();
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
     * Sync Lengow marketplaces
     */
    public static function syncMarketplaces()
    {
        self::loadApiMarketplace();
        if (self::$marketplaces && count(self::$marketplaces) > 0) {
            foreach (self::$marketplaces as $marketplaceName => $marketplace) {
                if (!self::getIdMarketplace($marketplaceName) && isset($marketplace->name)) {
                    $carrierRequired = false;
                    if (isset($marketplace->orders->actions->ship)) {
                        $action = $marketplace->orders->actions->ship;
                        if (isset($action->args_description->carrier)) {
                            $carrier = $action->args_description->carrier;
                            if (isset($carrier->accept_free_values) && !$carrier->accept_free_values) {
                                $carrierRequired = true;
                            }
                        }
                    }
                    self::insertMarketplace($marketplaceName, $marketplace->name, $carrierRequired);
                }
            }
        }
    }

    /**
     * Get marketplace counters list by country id
     *
     * @return array
     */
    public static function getMarketplaceCounters()
    {
        $marketplaceCounters = array();
        try {
            $results = Db::getInstance()->executeS(
                'SELECT ldc.id_country, COUNT(lm.id) as count FROM ' . _DB_PREFIX_ . 'lengow_default_carrier as ldc
                INNER JOIN ' . _DB_PREFIX_ . 'lengow_marketplace as lm ON lm.id = ldc.id_marketplace
                GROUP By ldc.id_country'
            );
        } catch (PrestaShopDatabaseException $e) {
            $results = false;
        }
        if (is_array($results)) {
            foreach ($results as $result) {
                $marketplaceCounters[(int)$result['id_country']] = (int)$result['count'];
            }
        }
        return $marketplaceCounters;
    }

    /**
     * Get all marketplaces
     *
     * @param integer|boolean $idCountry Prestashop id country
     *
     * @return array
     */
    public static function getAllMarketplaces($idCountry = false)
    {
        if ($idCountry) {
            $sql = 'SELECT lm.id, lm.marketplace_name, lm.marketplace_label, lm.carrier_required
              FROM ' . _DB_PREFIX_ . 'lengow_marketplace as lm
              INNER JOIN ' . _DB_PREFIX_ . 'lengow_default_carrier as ldc ON ldc.id_marketplace = lm.id
              WHERE ldc.id_country = ' . (int)$idCountry . ' ORDER BY marketplace_name';
        } else {
            $sql = 'SELECT lm.id, lm.marketplace_name, lm.marketplace_label, lm.carrier_required
                FROM ' . _DB_PREFIX_ . 'lengow_marketplace as lm
                ORDER BY marketplace_name';
        }
        try {
            $results = Db::getInstance()->ExecuteS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $results = false;
        }
        return is_array($results) ? $results : array();
    }

    /**
     * Get all marketplace data for carrier matching by country id
     *
     * @param integer $idCountry Prestashop country id
     *
     * @return array
     */
    public static function getAllMarketplaceDataByCountry($idCountry)
    {
        $marketplaceData = array();
        $marketplaces = self::getAllMarketplaces($idCountry);
        if ($marketplaces) {
            foreach ($marketplaces as $marketplace) {
                $idMarketplace = (int)$marketplace['id'];
                $marketplaceData[] = array(
                    'id' => $idMarketplace,
                    'name' => $marketplace['marketplace_name'],
                    'label' => $marketplace['marketplace_label'],
                    'carriers' => LengowCarrier::getAllCarrierMarketplaceByIdMarketplace($idMarketplace),
                    'id_carrier' => LengowCarrier::getDefaultIdCarrier($idCountry, $idMarketplace),
                    'id_carrier_marketplace' => LengowCarrier::getDefaultIdCarrierMarketplace(
                        $idCountry,
                        $idMarketplace
                    ),
                    'carrier_matched' => LengowCarrier::getAllMarketplaceCarrierCountryByIdMarketplace(
                        $idCountry,
                        $idMarketplace
                    ),
                    'carrier_required' => (bool)$marketplace['carrier_required']
                );
            }
        }
        return $marketplaceData;
    }

    /**
     * Get marketplace id
     *
     * @param string $marketplaceName Lengow marketplace name
     *
     * @return integer|false
     */
    public static function getIdMarketplace($marketplaceName)
    {
        try {
            $result = Db::getInstance()->ExecuteS(
                'SELECT id FROM ' . _DB_PREFIX_ . 'lengow_marketplace
                WHERE marketplace_name = "' . pSQL($marketplaceName) . '"'
            );
        } catch (PrestaShopDatabaseException $e) {
            $result = array();
        }
        return count($result) > 0 ? (int)$result[0]['id'] : false;
    }

    /**
     * Insert a new marketplace in the table
     *
     * @param string $marketplaceName Lengow marketplace name
     * @param string $marketplaceLabel Lengow marketplace label
     * @param boolean $carrierRequired carrier is required for ship action
     *
     * @return integer|false
     */
    public static function insertMarketplace($marketplaceName, $marketplaceLabel, $carrierRequired)
    {
        $db = Db::getInstance();
        try {
            if (_PS_VERSION_ < '1.5') {
                $success = $db->autoExecute(
                    _DB_PREFIX_ . 'lengow_marketplace',
                    array(
                        'marketplace_name' => pSQL($marketplaceName),
                        'marketplace_label' => pSQL($marketplaceLabel),
                        'carrier_required' => $carrierRequired
                    ),
                    'INSERT'
                );
            } else {
                $success = $db->insert(
                    'lengow_marketplace',
                    array(
                        'marketplace_name' => pSQL($marketplaceName),
                        'marketplace_label' => pSQL($marketplaceLabel),
                        'carrier_required' => $carrierRequired
                    )
                );
            }
        } catch (PrestaShopDatabaseException $e) {
            $success = false;
        }
        return $success ? self::getIdMarketplace($marketplaceName) : false;
    }
}
