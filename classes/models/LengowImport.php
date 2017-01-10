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
 * @category    Lengow
 * @package     lengow
 * @subpackage  classes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 */

/**
* Lengow Import Class
*/
class LengowImport
{
    /**
     * @var boolean import is processing
     */
    public static $processing;

    /**
     * @var string order id being imported
     */
    public static $currentOrder = -1;

    /**
     * @var array valid states lengow to create a Lengow order
     */
    public static $lengowStates = array(
        'accepted',
        'waiting_shipment',
        'shipped',
        'closed'
    );

    /**
     * @var integer Prestashop lang id
     */
    protected $idLang;

    /**
     * @var integer Prestashop shop id
     */
    protected $idShop = null;

    /**
     * @var integer Prestashop shop group id
     */
    protected $idShopGroup;

    /**
     * @var boolean use preprod mode
     */
    protected $preprodMode = false;

    /**
     * @var boolean display log messages
     */
    protected $logOutput = false;

    /**
     * @var string marketplace order sku
     */
    protected $marketplaceSku = null;

    /**
     * @var string markeplace name
     */
    protected $marketplaceName = null;

    /**
     * @var integer delivery address id
     */
    protected $deliveryAddressId = null;

    /**
     * @var integer number of orders to import
     */
    protected $limit = 0;

    /**
     * @var boolean import inactive & out of stock products
     */
    protected $forceProduct = true;

    /**
     * @var string start import date
     */
    protected $dateFrom = null;

    /**
     * @var string end import date
     */
    protected $dateTo = null;

    /**
     * @var string account ID
     */
    protected $accountId;

    /**
     * @var string access token
     */
    protected $accessToken;

    /**
     * @var string secret
     */
    protected $secret;

    /**
     * @var LengowConnector Lengow connector
     */
    protected $connector;

    /**
     * @var Context Context for import order
     */
    protected $context;

    /**
     * @var string type import (manual or cron)
     */
    protected $typeImport;

    /**
     * @var boolean import one order
     */
    protected $importOneOrder = false;

    /**
     * @var array account ids already imported
     */
    protected $accountIds = array();

    /**
     * @var integer id of lengow order record
     */
    protected $idOrderLengow;

    /**
     * Construct the import manager
     *
     * @param array params optional options
     * string  marketplace_sku     lengow marketplace order id to import
     * string  marketplace_name    lengow marketplace name to import
     * string  type                type of current import
     * integer delivery_address_id Lengow delivery address id to import
     * integer id_order_lengow     Lengow order id in Magento
     * integer shop_id             shop id for current import
     * integer days                import period
     * integer limit               number of orders to import
     * boolean log_output          display log messages
     * boolean preprod_mode        preprod mode
     */
    public function __construct($params = array())
    {
        // params for re-import order
        if (array_key_exists('marketplace_sku', $params)
            && array_key_exists('marketplace_name', $params)
            && array_key_exists('shop_id', $params)
        ) {
            $this->marketplaceSku  = (string)$params['marketplace_sku'];
            $this->marketplaceName = (string)$params['marketplace_name'];
            $this->limit            = 1;
            $this->importOneOrder = true;
            if (array_key_exists('delivery_address_id', $params) && $params['delivery_address_id'] != '') {
                $this->deliveryAddressId = $params['delivery_address_id'];
            }
            if (isset($params['id_order_lengow'])) {
                $this->idOrderLengow = (int)$params['id_order_lengow'];
            }
        } else {
            $this->marketplaceSku = null;
            // recovering the time interval
            $days = (
                isset($params['days'])
                ? (int)$params['days']
                : (int)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_DAYS')
            );
            $this->dateFrom = date('c', strtotime(date('Y-m-d').' -'.$days.'days'));
            $this->dateTo = date('c');
            if (LengowConfiguration::getGlobalValue('LENGOW_IMPORT_SINGLE_ENABLED')) {
                $this->limit = 1;
            } else {
                $this->limit = (isset($params['limit']) ? (int)$params['limit'] : 0);
            }
        }
        // get other params
        $this->preprodMode = (
            isset($params['preprod_mode'])
            ? (bool)$params['preprod_mode']
            : (bool)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED')
        );
        $this->typeImport = (isset($params['type']) ? $params['type'] : 'manual');
        $this->forceProduct = (
            isset($params['force_product'])
            ? (bool)$params['force_product']
            : (bool)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_FORCE_PRODUCT')
        );
        $this->logOutput = (isset($params['log_output']) ? (bool)$params['log_output'] : false);
        $this->idShop = (isset($params['shop_id']) ? (int)$params['shop_id'] : null);
    }

    /**
     * Execute import : fetch orders and import them
     *
     * @return array
     */
    public function exec()
    {
        $orderNew    = 0;
        $orderUpdate = 0;
        $orderError  = 0;
        $error       = array();
        // clean logs
        LengowMain::cleanLog();
        // Check if default carrier is active
        if (!LengowCarrier::isDefaultCarrierActive()) {
            $message = LengowMain::setLogMessage('log.import.error_default_carrier_not_active');
            LengowMain::log('Import', $message, $this->logOutput);
            return array('error' => LengowMain::decodeLogMessage($message, 'en'));
        }
        if (LengowImport::isInProcess() && !$this->preprodMode && !$this->importOneOrder) {
            $globalError = LengowMain::setLogMessage('lengow_log.error.import_in_progress');
            LengowMain::log('Import', $globalError, $this->logOutput);
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage(
                    'lengow_log.error.rest_time_to_import',
                    array('rest_time' => LengowImport::restTimeToImport())
                ),
                $this->logOutput
            );
            $error[0] = $globalError;
            if (isset($this->idOrderLengow) && $this->idOrderLengow) {
                LengowOrder::finishOrderLogs($this->idOrderLengow, 'import');
                LengowOrder::addOrderLog($this->idOrderLengow, $globalError, 'import');
            }
        } else {
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage('log.import.start', array('type' => $this->typeImport)),
                $this->logOutput
            );
            if ($this->preprodMode) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage('log.import.preprod_mode_active'),
                    $this->logOutput
                );
            }
            if (!$this->importOneOrder) {
                LengowImport::setInProcess();
                // udpate last import date
                lengowMain::updateDateImport($this->typeImport);
            }
            LengowMain::disableMail();
            // get all shops for import
            $shops = LengowShop::findAll(true);
            foreach ($shops as $shop) {
                // clean context
                $this->context = null;
                if (!is_null($this->idShop) && (int)$shop['id_shop'] != $this->idShop) {
                    continue;
                }
                $shop = new LengowShop((int)$shop['id_shop']);
                if (LengowMain::getShopActive((int)$shop->id)) {
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage(
                            'log.import.start_for_shop',
                            array(
                                'name_shop' => $shop->name,
                                'id_shop'   => (int)$shop->id
                            )
                        ),
                        $this->logOutput
                    );
                    try {
                        // check account ID, Access Token and Secret
                        $errorCredential = $this->checkCredentials((int)$shop->id, $shop->name);
                        if ($errorCredential !== true) {
                            LengowMain::log('Import', $errorCredential, $this->logOutput);
                            $error[(int)$shop->id] = $errorCredential;
                            continue;
                        }
                        // change context with current shop id
                        $this->changeContext((int)$shop->id);
                        // get orders from Lengow API
                        $orders = $this->getOrdersFromApi($shop);
                        $totalOrders = count($orders);
                        if ($this->importOneOrder) {
                            LengowMain::log(
                                'Import',
                                LengowMain::setLogMessage(
                                    'log.import.find_one_order',
                                    array(
                                        'nb_order'         => $totalOrders,
                                        'marketplace_sku'  => $this->marketplaceSku,
                                        'marketplace_name' => $this->marketplaceName,
                                        'account_id'       => $this->accountId
                                    )
                                ),
                                $this->logOutput
                            );
                        } else {
                            LengowMain::log(
                                'Import',
                                LengowMain::setLogMessage(
                                    'log.import.find_all_orders',
                                    array(
                                        'nb_order'   => $totalOrders,
                                        'account_id' => $this->accountId
                                    )
                                ),
                                $this->logOutput
                            );
                        }
                        if ($totalOrders <= 0 && $this->importOneOrder) {
                            throw new LengowException('lengow_log.error.order_not_found');
                        } elseif ($totalOrders <= 0) {
                            continue;
                        }
                        if (isset($this->idOrderLengow) && $this->idOrderLengow) {
                            LengowOrder::finishOrderLogs($this->idOrderLengow, 'import');
                        }
                        // import orders in prestashop
                        $result = $this->importOrders($orders, (int)$shop->id);
                        if (!$this->importOneOrder) {
                            $orderNew    += $result['order_new'];
                            $orderUpdate += $result['order_update'];
                            $orderError  += $result['order_error'];
                        }
                    } catch (LengowException $e) {
                        $errorMessage = $e->getMessage();
                    } catch (Exception $e) {
                        $errorMessage = '[Prestashop error] "'.$e->getMessage().'" '.$e->getFile().' | '.$e->getLine();
                    }
                    if (isset($errorMessage)) {
                        if (isset($this->idOrderLengow) && $this->idOrderLengow) {
                            LengowOrder::finishOrderLogs($this->idOrderLengow, 'import');
                            LengowOrder::addOrderLog($this->idOrderLengow, $errorMessage, 'import');
                        }
                        $decodedMessage = LengowMain::decodeLogMessage($errorMessage, 'en');
                        LengowMain::log(
                            'Import',
                            LengowMain::setLogMessage(
                                'log.import.import_failed',
                                array('decoded_message' => $decodedMessage)
                            ),
                            $this->logOutput
                        );
                        $error[(int)$shop->id] = $errorMessage;
                        unset($errorMessage);
                        continue;
                    }
                }
                unset($shop);
            }
            if (!$this->importOneOrder) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage(
                        'lengow_log.error.nb_order_imported',
                        array('nb_order' => $orderNew)
                    ),
                    $this->logOutput
                );
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage(
                        'lengow_log.error.nb_order_updated',
                        array('nb_order' => $orderUpdate)
                    ),
                    $this->logOutput
                );
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage(
                        'lengow_log.error.nb_order_with_error',
                        array('nb_order' => $orderError)
                    ),
                    $this->logOutput
                );
            }
            // finish import process
            LengowImport::setEnd();
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage('log.import.end', array('type' => $this->typeImport)),
                $this->logOutput
            );
            // sending email in error for orders
            if (LengowConfiguration::getGlobalValue('LENGOW_REPORT_MAIL_ENABLED')
                && !$this->preprodMode
                && !$this->importOneOrder
            ) {
                LengowMain::sendMailAlert($this->logOutput);
            }
            //check if order action is finish (Ship / Cancel)
            if (!LengowMain::inTest()
                && !$this->preprodMode
                && !$this->importOneOrder
                && $this->typeImport == 'manual'
            ) {
                LengowAction::checkFinishAction();
                LengowAction::checkActionNotSent();
            }
        }
        if ($this->importOneOrder) {
            $result['error'] = $error;
            return $result;
        } else {
            return array(
                'order_new'    => $orderNew,
                'order_update' => $orderUpdate,
                'order_error'  => $orderError,
                'error'        => $error
            );
        }
    }

    /**
     * Check credentials for a shop
     *
     * @param integer $idShop   Prestashop shop Id
     * @param string  $nameShop Prestashop shop name
     *
     * @return boolean
     */
    protected function checkCredentials($idShop, $nameShop)
    {
        $this->accountId = LengowMain::getIdAccount($idShop);
        $this->accessToken = LengowMain::getAccessToken($idShop);
        $this->secret = LengowMain::getSecretCustomer($idShop);
        if (!$this->accountId || !$this->accessToken || !$this->secret) {
            $message = LengowMain::setLogMessage(
                'lengow_log.error.account_id_empty',
                array(
                    'name_shop' => $nameShop,
                    'id_shop'   => $idShop
                )
            );
            return $message;
        }
        if (array_key_exists($this->accountId, $this->accountIds)) {
            $message = LengowMain::setLogMessage(
                'lengow_log.error.account_id_already_used',
                array(
                    'account_id' => $this->accountId,
                    'name_shop'  => $this->accountIds[$this->accountId]['name'],
                    'id_shop'    => $this->accountIds[$this->accountId]['id_shop'],
                )
            );
            return $message;
        }
        $this->accountIds[$this->accountId] = array('id_shop' => $idShop, 'name' => $nameShop);
        return true;
    }

    /**
     * Change Context for import
     *
     * @param integer $idShop Prestashop shop Id
     */
    protected function changeContext($idShop)
    {
        $this->context = Context::getContext()->cloneContext();
        if (_PS_VERSION_ >= '1.5') {
            if ($shop = new Shop($idShop)) {
                $this->context->shop = $shop;
            }
        }
        $this->idLang = $this->context->language->id;
        $this->idShopGroup = $this->context->shop->id_shop_group;
    }

    /**
     * Call Lengow order API
     *
     * @param LengowShop $shop
     *
     * @throws LengowException no connection with the webservice / credentials not valid
     *
     * @return array
     */
    protected function getOrdersFromApi($shop)
    {
        $page = 1;
        $orders = array();

        if (LengowCheck::isValidAuth((int)$shop->id)) {
            $this->connector  = new LengowConnector($this->accessToken, $this->secret);
            if ($this->importOneOrder) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage(
                        'log.import.connector_get_order',
                        array(
                            'marketplace_sku'  => $this->marketplaceSku,
                            'marketplace_name' => $this->marketplaceName
                        )
                    ),
                    $this->logOutput
                );
            } else {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage(
                        'log.import.connector_get_all_order',
                        array(
                            'date_from'  => date('Y-m-d', strtotime((string)$this->dateFrom)),
                            'date_to'    => date('Y-m-d', strtotime((string)$this->dateTo)),
                            'account_id' => $this->accountId
                        )
                    ),
                    $this->logOutput
                );
            }
            do {
                if ($this->importOneOrder) {
                    $results = $this->connector->get(
                        '/v3.0/orders',
                        array(
                            'marketplace_order_id' => $this->marketplaceSku,
                            'marketplace'          => $this->marketplaceName,
                            'account_id'           => $this->accountId,
                            'page'                 => $page
                        ),
                        'stream'
                    );
                } else {
                    $results = $this->connector->get(
                        '/v3.0/orders',
                        array(
                            'updated_from' => $this->dateFrom,
                            'updated_to'   => $this->dateTo,
                            'account_id'   => $this->accountId,
                            'page'         => $page
                        ),
                        'stream'
                    );
                }
                if (is_null($results)) {
                    throw new LengowException(
                        LengowMain::setLogMessage(
                            'lengow_log.exception.no_connection_webservice',
                            array(
                                'name_shop' => $shop->name,
                                'id_shop'   => (int)$shop->id
                            )
                        )
                    );
                }
                $results = Tools::jsonDecode($results);
                if (!is_object($results)) {
                    throw new LengowException(
                        LengowMain::setLogMessage(
                            'lengow_log.exception.no_connection_webservice',
                            array(
                                'name_shop' => $shop->name,
                                'id_shop'   => (int)$shop->id
                            )
                        )
                    );
                }
                if (isset($results->error)) {
                    throw new LengowException(
                        LengowMain::setLogMessage(
                            'lengow_log.exception.error_lengow_webservice',
                            array(
                                'error_code'    => $results->error->code,
                                'error_message' => $results->error->message,
                                'name_shop'     => $shop->name,
                                'id_shop'       => (int)$shop->id
                            )
                        )
                    );
                }
                // Construct array orders
                foreach ($results->results as $order) {
                    $orders[] = $order;
                }
                $page++;
            } while ($results->next != null);
        } else {
            throw new LengowException(
                LengowMain::setLogMessage(
                    'lengow_log.exception.crendentials_not_valid',
                    array(
                        'name_shop' => $shop->name,
                        'id_shop'   => (int)$shop->id
                    )
                )
            );
        }
        return $orders;
    }

    /**
     * Create or update order in prestashop
     *
     * @param mixed   $orders API orders
     * @param integer $idShop Prestashop shop Id
     *
     * @return array|false
     */
    protected function importOrders($orders, $idShop)
    {
        $orderNew       = 0;
        $orderUpdate    = 0;
        $orderError     = 0;
        $importFinished = false;
        foreach ($orders as $orderData) {
            if (!$this->importOneOrder) {
                LengowImport::setInProcess();
            }
            $nbPackage = 0;
            $marketplaceSku = (string)$orderData->marketplace_order_id;
            if ($this->preprodMode) {
                $marketplaceSku .= '--'.time();
            }
            // set current order to cancel hook updateOrderStatus
            LengowImport::$currentOrder = $marketplaceSku;
            // if order contains no package
            if (count($orderData->packages) == 0) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage('log.import.error_no_package'),
                    $this->logOutput,
                    $marketplaceSku
                );
                continue;
            }
            // start import
            foreach ($orderData->packages as $packageData) {
                $nbPackage++;
                // check whether the package contains a shipping address
                if (!isset($packageData->delivery->id)) {
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage('log.import.error_no_delivery_address'),
                        $this->logOutput,
                        $marketplaceSku
                    );
                    continue;
                }
                $packageDeliveryAddressId = (int)$packageData->delivery->id;
                $firstPackage = ($nbPackage > 1 ? false : true);
                // check the package for re-import order
                if ($this->importOneOrder) {
                    if (!is_null($this->deliveryAddressId)
                        && $this->deliveryAddressId != $packageDeliveryAddressId
                    ) {
                        LengowMain::log(
                            'Import',
                            LengowMain::setLogMessage('log.import.error_wrong_package_number'),
                            $this->logOutput,
                            $marketplaceSku
                        );
                        continue;
                    }
                }
                try {
                    // try to import or update order
                    $importOrder = new LengowImportOrder(
                        array(
                            'context'             => $this->context,
                            'id_shop'             => $idShop,
                            'id_shop_group'       => $this->idShopGroup,
                            'id_lang'             => $this->idLang,
                            'force_product'       => $this->forceProduct,
                            'preprod_mode'        => $this->preprodMode,
                            'log_output'          => $this->logOutput,
                            'marketplace_sku'     => $marketplaceSku,
                            'delivery_address_id' => $packageDeliveryAddressId,
                            'order_data'          => $orderData,
                            'package_data'        => $packageData,
                            'first_package'       => $firstPackage
                        )
                    );
                    $order = $importOrder->importOrder();
                } catch (LengowException $e) {
                    $errorMessage = $e->getMessage();
                } catch (Exception $e) {
                    $errorMessage = '[Prestashop error]: "'.$e->getMessage().'" '.$e->getFile().' | '.$e->getLine();
                }
                if (isset($errorMessage)) {
                    $decodedMessage = LengowMain::decodeLogMessage($errorMessage, 'en');
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage(
                            'log.import.order_import_failed',
                            array('decoded_message' => $decodedMessage)
                        ),
                        $this->logOutput,
                        $marketplaceSku
                    );
                    unset($errorMessage);
                    continue;
                }
                // Sync to lengow if no preprod_mode
                if (!$this->preprodMode && isset($order['order_new']) && $order['order_new'] == true) {
                    $lengowOrder = new LengowOrder((int)$order['order_id']);
                    $synchro = $lengowOrder->synchronizeOrder($this->connector, $this->logOutput);
                    if ($synchro) {
                        $synchroMessage = LengowMain::setLogMessage(
                            'log.import.order_synchronized_with_lengow',
                            array('order_id' => $order['order_id'])
                        );
                    } else {
                        $synchroMessage = LengowMain::setLogMessage(
                            'log.import.order_not_synchronized_with_lengow',
                            array('order_id' => $order['order_id'])
                        );
                    }
                    LengowMain::log('Import', $synchroMessage, $this->logOutput, $marketplaceSku);
                    unset($lengowOrder);
                }
                // if re-import order -> return order informations
                if ($this->importOneOrder) {
                    return $order;
                }
                if ($order) {
                    if (isset($order['order_new']) && $order['order_new'] == true) {
                        $orderNew++;
                    } elseif (isset($order['order_update']) && $order['order_update'] == true) {
                        $orderUpdate++;
                    } elseif (isset($order['order_error']) && $order['order_error'] == true) {
                        $orderError++;
                    }
                }
                // clean process
                LengowImport::$currentOrder = -1;
                unset($importOrder, $order);
                // if limit is set
                if ($this->limit > 0 && $orderNew == $this->limit) {
                    $importFinished = true;
                    break;
                }
            }
            if ($importFinished) {
                break;
            }
        }
        return array(
            'order_new'    => $orderNew,
            'order_update' => $orderUpdate,
            'order_error'  => $orderError
        );
    }

    /**
     * Check if order status is valid for import
     *
     * @param string            $orderStateMarketplace Marketplace order state
     * @param LengowMarketplace $marketplace           Lengow marketplace instance
     *
     * @return boolean
     */
    public static function checkState($orderStateMarketplace, $marketplace)
    {
        if (empty($orderStateMarketplace)) {
            return false;
        }
        if (!in_array($marketplace->getStateLengow($orderStateMarketplace), LengowImport::$lengowStates)) {
            return false;
        }
        return true;
    }

    /**
     * Check if import is already in process
     *
     * @return boolean
     */
    public static function isInProcess()
    {
        $timestamp = LengowConfiguration::getGlobalValue('LENGOW_IMPORT_IN_PROGRESS');
        if ($timestamp > 0) {
            // security check : if last import is more than 60 seconds old => authorize new import to be launched
            if (($timestamp + (60 * 1)) < time()) {
                LengowImport::setEnd();
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Get Rest time to make re import order
     *
     * @return boolean
     */
    public static function restTimeToImport()
    {
        $timestamp = LengowConfiguration::getGlobalValue('LENGOW_IMPORT_IN_PROGRESS');
        if ($timestamp > 0) {
            return $timestamp + (60 * 1) - time();
        }
        return false;
    }

    /**
     * Set import to "in process" state
     *
     * @return boolean
     */
    public static function setInProcess()
    {
        LengowImport::$processing = true;
        return LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', time());
    }

    /**
     * Set import to finished
     *
     * @return boolean
     */
    public static function setEnd()
    {
        LengowImport::$processing = false;
        return LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', -1);
    }
}
