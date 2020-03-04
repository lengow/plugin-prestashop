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
     * @var string marketplace file name
     */
    public static $marketplaceJson = 'marketplaces.json';

    /**
     * @var array all valid actions
     */
    public static $validActions = array(
        LengowAction::TYPE_SHIP,
        LengowAction::TYPE_CANCEL,
    );

    /**
     * @var array|false all marketplaces
     */
    public static $marketplaces = false;

    /**
     * @var mixed the current marketplace
     */
    public $marketplace;

    /**
     * @var string the code of the marketplace
     */
    public $name;

    /**
     * @var string the old code of the marketplace for v2 compatibility
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
     * @var array all shipping methods of the marketplace
     */
    public $shippingMethods = array();

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
                        'valid_values' => $validValues,
                    );
                }
            }
            if (isset($this->marketplace->orders->carriers)) {
                foreach ($this->marketplace->orders->carriers as $key => $carrier) {
                    $this->carriers[(string)$key] = (string)$carrier->label;
                }
            }
            if (isset($this->marketplace->orders->shipping_methods)) {
                foreach ($this->marketplace->orders->shipping_methods as $key => $shippingMethods) {
                    $this->shippingMethods[(string)$key] = (string)$shippingMethods->label;
                }
            }
            $this->isLoaded = true;
        }
    }

    /**
     * Load the json configuration of all marketplaces
     *
     * @param boolean $force force cache update
     * @param boolean $logOutput see log or not
     */
    public static function loadApiMarketplace($force = false, $logOutput = false)
    {
        if (!self::$marketplaces || $force) {
            self::$marketplaces = LengowSync::getMarketplaces($force, $logOutput);
        }
    }

    /**
     * Get marketplaces.json path
     *
     * @return string
     */
    public static function getFilePath()
    {
        $sep = DIRECTORY_SEPARATOR;
        return LengowMain::getLengowFolder() . $sep . LengowMain::$lengowConfigFolder . $sep . self::$marketplaceJson;
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
                if (in_array(LengowAction::ARG_LINE, $actions['args'])) {
                    return true;
                }
            }
            if (isset($actions['optional_args']) && is_array($actions['optional_args'])) {
                if (in_array(LengowAction::ARG_LINE, $actions['optional_args'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Is marketplace has carriers
     *
     * @return boolean
     */
    public function hasCarriers()
    {
        return !empty($this->carriers) ? true : false;
    }

    /**
     * Is marketplace has shipping methods
     *
     * @return boolean
     */
    public function hasShippingMethods()
    {
        return !empty($this->shippingMethods) ? true : false;
    }

    /**
     * Call API action and create action in lengow_actions table
     *
     * @param string $action Lengow order actions type (ship or cancel)
     * @param LengowOrder $lengowOrder Lengow order instance
     * @param string|null $idOrderLine Lengow order line id
     *
     * @return boolean
     */
    public function callAction($action, $lengowOrder, $idOrderLine = null)
    {
        try {
            // check the action and order data
            $this->checkAction($action);
            $this->checkOrderData($lengowOrder);
            // get all required and optional arguments for a specific marketplace
            $marketplaceArguments = $this->getMarketplaceArguments($action);
            // get all available values from an order
            $params = $this->getAllParams($action, $lengowOrder, $marketplaceArguments);
            // check required arguments and clean value for empty optionals arguments
            $params = $this->checkAndCleanParams($action, $params);
            // complete the values with the specific values of the account
            if ($idOrderLine !== null) {
                $params[LengowAction::ARG_LINE] = $idOrderLine;
            }
            $params['marketplace_order_id'] = $lengowOrder->lengowMarketplaceSku;
            $params['marketplace'] = $lengowOrder->lengowMarketplaceName;
            $params[LengowAction::ARG_ACTION_TYPE] = $action;
            // checks whether the action is already created to not return an action
            $canSendAction = LengowAction::canSendAction($params, $lengowOrder);
            if ($canSendAction) {
                // send a new action on the order via the Lengow API
                LengowAction::sendAction($params, $lengowOrder);
            }
        } catch (LengowException $e) {
            $errorMessage = $e->getMessage();
        } catch (Exception $e) {
            $errorMessage = '[Prestashop Error] "' . $e->getMessage() . '" ' . $e->getFile() . ' | ' . $e->getLine();
        }
        if (isset($errorMessage)) {
            if ($lengowOrder->lengowProcessState != LengowOrder::PROCESS_STATE_FINISH) {
                LengowOrder::addOrderLog($lengowOrder->lengowId, $errorMessage, 'send');
            }
            $decodedMessage = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
            LengowMain::log(
                LengowLog::CODE_ACTION,
                LengowMain::setLogMessage(
                    'log.order_action.call_action_failed',
                    array('decoded_message' => $decodedMessage)
                ),
                false,
                $lengowOrder->lengowMarketplaceSku
            );
            return false;
        }
        return true;
    }

    /**
     * Check if the action is valid and present on the marketplace
     *
     * @param string $action Lengow order actions type (ship or cancel)
     *
     * @throws LengowException action not valid / marketplace action not present
     */
    protected function checkAction($action)
    {
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
    }

    /**
     * Check if the essential data of the order are present
     *
     * @param LengowOrder $lengowOrder Lengow order instance
     *
     * @throws LengowException marketplace sku is required / marketplace name is required
     */
    protected function checkOrderData($lengowOrder)
    {
        if (Tools::strlen($lengowOrder->lengowMarketplaceSku) === 0) {
            throw new LengowException(
                LengowMain::setLogMessage('lengow_log.exception.marketplace_sku_require')
            );
        }
        if (Tools::strlen($lengowOrder->lengowMarketplaceName) === 0) {
            throw new LengowException(
                LengowMain::setLogMessage('lengow_log.exception.marketplace_name_require')
            );
        }
    }

    /**
     * Get all marketplace arguments for a specific action
     *
     * @param string $action Lengow order actions type (ship or cancel)
     *
     * @return array
     */
    protected function getMarketplaceArguments($action)
    {
        $actions = $this->getAction($action);
        if (isset($actions['args']) && isset($actions['optional_args'])) {
            $marketplaceArguments = array_merge($actions['args'], $actions['optional_args']);
        } elseif (!isset($actions['args']) && isset($actions['optional_args'])) {
            $marketplaceArguments = $actions['optional_args'];
        } elseif (isset($actions['args'])) {
            $marketplaceArguments = $actions['args'];
        } else {
            $marketplaceArguments = array();
        }
        return $marketplaceArguments;
    }

    /**
     * Get all available values from an order
     *
     * @param string $action Lengow order actions type (ship or cancel)
     * @param LengowOrder $lengowOrder Lengow order instance
     * @param array $marketplaceArguments All marketplace arguments for a specific action
     *
     * @throws Exception|LengowException no delivery country in order
     *
     * @return array
     */
    protected function getAllParams($action, $lengowOrder, $marketplaceArguments)
    {
        $params = array();
        $actions = $this->getAction($action);
        // get delivery address for carrier, shipping method and tracking url
        $deliveryAddress = new Address($lengowOrder->id_address_delivery);
        // get tracking number for tracking number and tracking url
        if (_PS_VERSION_ >= '1.5') {
            $idOrderCarrier = $lengowOrder->getIdOrderCarrier();
            $orderCarrier = new OrderCarrier($idOrderCarrier);
            $trackingNumber = $orderCarrier->tracking_number;
            if ($trackingNumber == '') {
                $trackingNumber = $lengowOrder->shipping_number;
            }
        } else {
            $trackingNumber = $lengowOrder->shipping_number;
        }
        foreach ($marketplaceArguments as $arg) {
            switch ($arg) {
                case LengowAction::ARG_TRACKING_NUMBER:
                    $params[$arg] = $trackingNumber;
                    break;
                case LengowAction::ARG_CARRIER:
                case LengowAction::ARG_CARRIER_NAME:
                case LengowAction::ARG_SHIPPING_METHOD:
                case LengowAction::ARG_CUSTOM_CARRIER:
                    if ($lengowOrder->lengowCarrier != '') {
                        $carrierName = (string)$lengowOrder->lengowCarrier;
                    } else {
                        if (!isset($deliveryAddress->id_country) || (int)$deliveryAddress->id_country === 0) {
                            if (isset($actions['optional_args']) && in_array($arg, $actions['optional_args'])) {
                                break;
                            }
                            throw new LengowException(
                                LengowMain::setLogMessage('lengow_log.exception.no_delivery_country_in_order')
                            );
                        }
                        // get marketplace id by marketplace name
                        $idMarketplace = LengowMarketplace::getIdMarketplace($lengowOrder->lengowMarketplaceName);
                        $carrierName = LengowCarrier::getCarrierMarketplaceCode(
                            (int)$deliveryAddress->id_country,
                            $idMarketplace,
                            (int)$lengowOrder->id_carrier
                        );
                    }
                    $params[$arg] = $carrierName;
                    break;
                case LengowAction::ARG_TRACKING_URL:
                    if ($trackingNumber != '') {
                        $idActiveCarrier = LengowCarrier::getIdActiveCarrierByIdCarrier(
                            (int)$lengowOrder->id_carrier,
                            (int)$deliveryAddress->id_country
                        );
                        $idCarrier = $idActiveCarrier ? $idActiveCarrier : (int)$lengowOrder->id_carrier;
                        $carrier = new Carrier($idCarrier);
                        $trackingUrl = str_replace('@', $trackingNumber, $carrier->url);
                        // add default value if tracking url is empty
                        if (Tools::strlen($trackingUrl) === 0) {
                            if (isset($actions['optional_args']) && in_array($arg, $actions['optional_args'])) {
                                break;
                            }
                            $defaultValue = $this->getDefaultValue((string)$arg);
                            $trackingUrl = $defaultValue ? $defaultValue : $arg . ' not available';
                        }
                        $params[$arg] = $trackingUrl;
                    }
                    break;
                case LengowAction::ARG_SHIPPING_PRICE:
                    $params[$arg] = $lengowOrder->total_shipping;
                    break;
                case LengowAction::ARG_SHIPPING_DATE:
                case LengowAction::ARG_DELIVERY_DATE:
                    $params[$arg] = date('c');
                    break;
                default:
                    if (isset($actions['optional_args']) && in_array($arg, $actions['optional_args'])) {
                        break;
                    }
                    $defaultValue = $this->getDefaultValue((string)$arg);
                    $paramValue = $defaultValue ? $defaultValue : $arg . ' not available';
                    $params[$arg] = $paramValue;
                    break;
            }
        }
        return $params;
    }

    /**
     * Check required parameters and delete empty parameters
     *
     * @param string $action Lengow order actions type (ship or cancel)
     * @param array $params all available values
     *
     * @throws Exception argument is required
     *
     * @return array
     */
    protected function checkAndCleanParams($action, $params)
    {
        $actions = $this->getAction($action);
        if (isset($actions['args'])) {
            foreach ($actions['args'] as $arg) {
                if (!isset($params[$arg]) || Tools::strlen($params[$arg]) === 0) {
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
                if (isset($params[$arg]) && Tools::strlen($params[$arg]) === 0) {
                    unset($params[$arg]);
                }
            }
        }
        return $params;
    }

    /**
     * Sync Lengow marketplaces
     */
    public static function syncMarketplaces()
    {
        self::loadApiMarketplace();
        if (self::$marketplaces && !empty(self::$marketplaces)) {
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
                    'methods' => LengowMethod::getAllMethodMarketplaceByIdMarketplace($idMarketplace),
                    'id_carrier' => LengowCarrier::getDefaultIdCarrier($idCountry, $idMarketplace),
                    'id_carrier_marketplace' => LengowCarrier::getDefaultIdCarrierMarketplace(
                        $idCountry,
                        $idMarketplace
                    ),
                    'carrier_matched' => LengowCarrier::getAllMarketplaceCarrierCountryByIdMarketplace(
                        $idCountry,
                        $idMarketplace
                    ),
                    'method_matched' => LengowMethod::getAllMarketplaceMethodCountryByIdMarketplace(
                        $idCountry,
                        $idMarketplace
                    ),
                    'carrier_required' => (bool)$marketplace['carrier_required'],
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
        return !empty($result) ? (int)$result[0]['id'] : false;
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
                        'carrier_required' => $carrierRequired,
                    ),
                    'INSERT'
                );
            } else {
                $success = $db->insert(
                    'lengow_marketplace',
                    array(
                        'marketplace_name' => pSQL($marketplaceName),
                        'marketplace_label' => pSQL($marketplaceLabel),
                        'carrier_required' => $carrierRequired,
                    )
                );
            }
        } catch (PrestaShopDatabaseException $e) {
            $success = false;
        }
        return $success ? self::getIdMarketplace($marketplaceName) : false;
    }
}
