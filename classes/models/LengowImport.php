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
 * Lengow Import Class
 */
class LengowImport
{
    /**
     * @var integer max interval time for order synchronisation old versions (1 day)
     */
    const MIN_INTERVAL_TIME = 86400;

    /**
     * @var integer max import days for old versions (10 days)
     */
    const MAX_INTERVAL_TIME = 864000;

    /**
     * @var integer security interval time for cron synchronisation (2 hours)
     */
    const SECURITY_INTERVAL_TIME = 7200;

    /**
     * @var integer interval of months for cron synchronisation
     */
    const MONTH_INTERVAL_TIME = 3;

    /**
     * @var string manual import type
     */
    const TYPE_MANUAL = 'manual';

    /**
     * @var string cron import type
     */
    const TYPE_CRON = 'cron';

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
        LengowOrder::STATE_WAITING_SHIPMENT,
        LengowOrder::STATE_SHIPPED,
        LengowOrder::STATE_CLOSED,
    );

    /**
     * @var integer Prestashop lang id
     */
    protected $idLang;

    /**
     * @var integer|null Prestashop shop id
     */
    protected $idShop = null;

    /**
     * @var integer Prestashop shop group id
     */
    protected $idShopGroup;

    /**
     * @var boolean use debug mode
     */
    protected $debugMode = false;

    /**
     * @var boolean display log messages
     */
    protected $logOutput = false;

    /**
     * @var string|null marketplace order sku
     */
    protected $marketplaceSku = null;

    /**
     * @var string|null marketplace name
     */
    protected $marketplaceName = null;

    /**
     * @var integer|null delivery address id
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
     * @var integer|false imports orders updated since (timestamp)
     */
    protected $updatedFrom = false;

    /**
     * @var integer|false imports orders updated until (timestamp)
     */
    protected $updatedTo = false;

    /**
     * @var integer|false imports orders created since (timestamp)
     */
    protected $createdFrom = false;

    /**
     * @var integer|false imports orders created until (timestamp)
     */
    protected $createdTo = false;

    /**
     * @var string Lengow account id
     */
    protected $accountId;

    /**
     * @var string Lengow access token
     */
    protected $accessToken;

    /**
     * @var string Lengow secret token
     */
    protected $secretToken;

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
     * @var array shop catalog ids for import
     */
    protected $shopCatalogIds = array();

    /**
     * @var array catalog ids already imported
     */
    protected $catalogIds = array();

    /**
     * @var integer id of lengow order record
     */
    protected $idOrderLengow;

    /**
     * Construct the import manager
     *
     * @param array $params optional options
     * string  marketplace_sku     lengow marketplace order id to import
     * string  marketplace_name    lengow marketplace name to import
     * string  type                type of current import
     * string  create_from         import of orders since
     * string  created_to          import of orders until
     * integer delivery_address_id Lengow delivery address id to import
     * integer id_order_lengow     Lengow order id in Magento
     * integer shop_id             shop id for current import
     * integer days                import period
     * integer limit               number of orders to import
     * boolean log_output          display log messages
     * boolean debug_mode          debug mode
     */
    public function __construct($params = array())
    {
        // get generic params for synchronisation
        $this->debugMode = isset($params['debug_mode'])
            ? (bool)$params['debug_mode']
            : LengowConfiguration::debugModeIsActive();
        $this->typeImport = isset($params['type']) ? $params['type'] : self::TYPE_MANUAL;
        $this->forceProduct = isset($params['force_product'])
            ? (bool)$params['force_product']
            : (bool)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_FORCE_PRODUCT');
        $this->logOutput = isset($params['log_output']) ? (bool)$params['log_output'] : false;
        $this->idShop = isset($params['shop_id']) ? (int)$params['shop_id'] : null;
        // get params for synchronise one or all orders
        if (array_key_exists('marketplace_sku', $params)
            && array_key_exists('marketplace_name', $params)
            && array_key_exists('shop_id', $params)
        ) {
            $this->marketplaceSku = (string)$params['marketplace_sku'];
            $this->marketplaceName = (string)$params['marketplace_name'];
            $this->limit = 1;
            $this->importOneOrder = true;
            if (array_key_exists('delivery_address_id', $params) && $params['delivery_address_id'] != '') {
                $this->deliveryAddressId = $params['delivery_address_id'];
            }
            if (isset($params['id_order_lengow'])) {
                $this->idOrderLengow = (int)$params['id_order_lengow'];
            }
        } else {
            $this->marketplaceSku = null;
            // set the time interval
            $this->setIntervalTime(
                isset($params['days']) ? (int)$params['days'] : false,
                isset($params['created_from']) ? $params['created_from'] : false,
                isset($params['created_to']) ? $params['created_to'] : false
            );
            if (LengowConfiguration::getGlobalValue('LENGOW_IMPORT_SINGLE_ENABLED')) {
                $this->limit = 1;
            } else {
                $this->limit = isset($params['limit']) ? (int)$params['limit'] : 0;
            }
        }
    }

    /**
     * Execute import : fetch orders and import them
     *
     * @return array
     */
    public function exec()
    {
        $orderNew = 0;
        $orderUpdate = 0;
        $orderError = 0;
        $error = array();
        $globalError = false;
        $syncOk = true;
        // clean logs
        LengowMain::cleanLog();
        if (self::isInProcess() && !$this->debugMode && !$this->importOneOrder) {
            $globalError = LengowMain::setLogMessage(
                'lengow_log.error.rest_time_to_import',
                array('rest_time' => self::restTimeToImport())
            );
            LengowMain::log(LengowLog::CODE_IMPORT, $globalError, $this->logOutput);
        } elseif (!self::checkCredentials()) {
            $globalError = LengowMain::setLogMessage('lengow_log.error.credentials_not_valid');
            LengowMain::log(LengowLog::CODE_IMPORT, $globalError, $this->logOutput);
        } else {
            if (!$this->importOneOrder) {
                self::setInProcess();
            }
            // check Lengow catalogs for order synchronisation
            if (!$this->importOneOrder && $this->typeImport === self::TYPE_MANUAL) {
                LengowSync::syncCatalog();
                LengowSync::syncCarrier();
            }
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage('log.import.start', array('type' => $this->typeImport)),
                $this->logOutput
            );
            if ($this->debugMode) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage('log.import.debug_mode_active'),
                    $this->logOutput
                );
            }
            LengowMain::disableMail();
            // get all shops for import
            $shops = LengowShop::findAll(true);
            foreach ($shops as $shop) {
                // clean context
                $this->context = null;
                if ($this->idShop !== null && (int)$shop['id_shop'] !== $this->idShop) {
                    continue;
                }
                $shop = new LengowShop((int)$shop['id_shop']);
                if (LengowConfiguration::shopIsActive((int)$shop->id)) {
                    LengowMain::log(
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage(
                            'log.import.start_for_shop',
                            array(
                                'name_shop' => $shop->name,
                                'id_shop' => (int)$shop->id,
                            )
                        ),
                        $this->logOutput
                    );
                    try {
                        // check shop catalog ids
                        if (!$this->checkCatalogIds($shop)) {
                            $errorCatalogIds = LengowMain::setLogMessage(
                                'lengow_log.error.no_catalog_for_shop',
                                array(
                                    'name_shop' => $shop->name,
                                    'id_shop' => (int)$shop->id,
                                )
                            );
                            LengowMain::log(LengowLog::CODE_IMPORT, $errorCatalogIds, $this->logOutput);
                            $error[(int)$shop->id] = $errorCatalogIds;
                            continue;
                        }
                        // change context with current shop id
                        $this->changeContext((int)$shop->id);
                        // get orders from Lengow API
                        $orders = $this->getOrdersFromApi($shop);
                        $totalOrders = count($orders);
                        if ($this->importOneOrder) {
                            LengowMain::log(
                                LengowLog::CODE_IMPORT,
                                LengowMain::setLogMessage(
                                    'log.import.find_one_order',
                                    array(
                                        'nb_order' => $totalOrders,
                                        'marketplace_sku' => $this->marketplaceSku,
                                        'marketplace_name' => $this->marketplaceName,
                                        'account_id' => $this->accountId,
                                    )
                                ),
                                $this->logOutput
                            );
                        } else {
                            LengowMain::log(
                                LengowLog::CODE_IMPORT,
                                LengowMain::setLogMessage(
                                    'log.import.find_all_orders',
                                    array(
                                        'nb_order' => $totalOrders,
                                        'account_id' => $this->accountId,
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
                            $orderNew += $result['order_new'];
                            $orderUpdate += $result['order_update'];
                            $orderError += $result['order_error'];
                        }
                    } catch (LengowException $e) {
                        $errorMessage = $e->getMessage();
                    } catch (Exception $e) {
                        $errorMessage = '[Prestashop error] "' . $e->getMessage()
                            . '" ' . $e->getFile() . ' | ' . $e->getLine();
                    }
                    if (isset($errorMessage)) {
                        $syncOk = false;
                        if (isset($this->idOrderLengow) && $this->idOrderLengow) {
                            LengowOrder::finishOrderLogs($this->idOrderLengow, 'import');
                            LengowOrder::addOrderLog($this->idOrderLengow, $errorMessage, 'import');
                        }
                        $decodedMessage = LengowMain::decodeLogMessage(
                            $errorMessage,
                            LengowTranslation::DEFAULT_ISO_CODE
                        );
                        LengowMain::log(
                            LengowLog::CODE_IMPORT,
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
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage(
                        'lengow_log.error.nb_order_imported',
                        array('nb_order' => $orderNew)
                    ),
                    $this->logOutput
                );
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage(
                        'lengow_log.error.nb_order_updated',
                        array('nb_order' => $orderUpdate)
                    ),
                    $this->logOutput
                );
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage(
                        'lengow_log.error.nb_order_with_error',
                        array('nb_order' => $orderError)
                    ),
                    $this->logOutput
                );
            }
            // update last import date
            if (!$this->importOneOrder && $syncOk) {
                LengowMain::updateDateImport($this->typeImport);
            }
            // finish import process
            self::setEnd();
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage('log.import.end', array('type' => $this->typeImport)),
                $this->logOutput
            );
            // check if order action is finish (ship or cancel)
            if (!$this->debugMode
                && !$this->importOneOrder
                && $this->typeImport === self::TYPE_MANUAL
            ) {
                LengowAction::checkFinishAction($this->logOutput);
                LengowAction::checkOldAction($this->logOutput);
                LengowAction::checkActionNotSent($this->logOutput);
            }
            // sending email in error for orders
            LengowMain::enableMail();
            if ((bool)LengowConfiguration::getGlobalValue('LENGOW_REPORT_MAIL_ENABLED')
                && !$this->debugMode
                && !$this->importOneOrder
            ) {
                LengowMain::sendMailAlert($this->logOutput);
            }
        }
        if ($globalError) {
            $error[0] = $globalError;
            if (isset($this->idOrderLengow) && $this->idOrderLengow) {
                LengowOrder::finishOrderLogs($this->idOrderLengow, 'import');
                LengowOrder::addOrderLog($this->idOrderLengow, $globalError, 'import');
            }
        }
        if ($this->importOneOrder) {
            $result['error'] = $error;
            return $result;
        } else {
            return array(
                'order_new' => $orderNew,
                'order_update' => $orderUpdate,
                'order_error' => $orderError,
                'error' => $error,
            );
        }
    }

    /**
     * Check credentials and get Lengow connector
     *
     * @return boolean
     */
    protected function checkCredentials()
    {
        if (LengowConnector::isValidAuth($this->logOutput)) {
            list($this->accountId, $this->accessToken, $this->secretToken) = LengowConfiguration::getAccessIds();
            $this->connector = new LengowConnector($this->accessToken, $this->secretToken);
            return true;
        }
        return false;
    }

    /**
     * Check catalog ids for a shop
     *
     * @param LengowShop $shop Lengow shop instance
     *
     * @return boolean
     */
    protected function checkCatalogIds($shop)
    {
        if ($this->importOneOrder) {
            return true;
        }
        $shopCatalogIds = array();
        $catalogIds = LengowConfiguration::getCatalogIds((int)$shop->id);
        foreach ($catalogIds as $catalogId) {
            if (array_key_exists($catalogId, $this->catalogIds)) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage(
                        'log.import.catalog_id_already_used',
                        array(
                            'catalog_id' => $catalogId,
                            'name_shop' => $this->catalogIds[$catalogId]['name'],
                            'id_shop' => $this->catalogIds[$catalogId]['shopId'],
                        )
                    ),
                    $this->logOutput
                );
            } else {
                $this->catalogIds[$catalogId] = array('shopId' => (int)$shop->id, 'name' => $shop->name);
                $shopCatalogIds[] = $catalogId;
            }
        }
        if (!empty($shopCatalogIds)) {
            $this->shopCatalogIds = $shopCatalogIds;
            return true;
        }
        return false;
    }

    /**
     * Change Context for import
     *
     * @param integer $idShop Prestashop shop Id
     *
     * @throws Exception
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
        if ($this->importOneOrder) {
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.connector_get_order',
                    array(
                        'marketplace_sku' => $this->marketplaceSku,
                        'marketplace_name' => $this->marketplaceName,
                    )
                ),
                $this->logOutput
            );
        } else {
            $dateFrom = $this->createdFrom ? $this->createdFrom : $this->updatedFrom;
            $dateTo = $this->createdTo ? $this->createdTo : $this->updatedTo;
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.connector_get_all_order',
                    array(
                        'date_from' => date('Y-m-d H:i:s', $dateFrom),
                        'date_to' => date('Y-m-d H:i:s', $dateTo),
                        'catalog_id' => implode(', ', $this->shopCatalogIds),
                    )
                ),
                $this->logOutput
            );
        }
        do {
            try {
                if ($this->importOneOrder) {
                    $results = $this->connector->get(
                        LengowConnector::API_ORDER,
                        array(
                            'marketplace_order_id' => $this->marketplaceSku,
                            'marketplace' => $this->marketplaceName,
                            'account_id' => $this->accountId,
                        ),
                        LengowConnector::FORMAT_STREAM,
                        '',
                        $this->logOutput
                    );
                } else {
                    if ($this->createdFrom && $this->createdTo) {
                        $timeParams = array(
                            'marketplace_order_date_from' => date('c', $this->createdFrom),
                            'marketplace_order_date_to' => date('c', $this->createdTo),
                        );
                    } else {
                        $timeParams = array(
                            'updated_from' => date('c', $this->updatedFrom),
                            'updated_to' => date('c', $this->updatedTo),
                        );
                    }
                    $results = $this->connector->get(
                        LengowConnector::API_ORDER,
                        array_merge(
                            $timeParams,
                            array(
                                'catalog_ids' => implode(',', $this->shopCatalogIds),
                                'account_id' => $this->accountId,
                                'page' => $page,
                            )
                        ),
                        LengowConnector::FORMAT_STREAM,
                        '',
                        $this->logOutput
                    );
                }
            } catch (Exception $e) {
                throw new LengowException(
                    LengowMain::setLogMessage(
                        'lengow_log.exception.error_lengow_webservice',
                        array(
                            'error_code' => $e->getCode(),
                            'error_message' => LengowMain::decodeLogMessage(
                                $e->getMessage(),
                                LengowTranslation::DEFAULT_ISO_CODE
                            ),
                            'name_shop' => $shop->name,
                            'id_shop' => (int)$shop->id,
                        )
                    )
                );
            }
            if ($results === null) {
                throw new LengowException(
                    LengowMain::setLogMessage(
                        'lengow_log.exception.no_connection_webservice',
                        array(
                            'name_shop' => $shop->name,
                            'id_shop' => (int)$shop->id,
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
                            'id_shop' => (int)$shop->id,
                        )
                    )
                );
            }
            // construct array orders
            foreach ($results->results as $order) {
                $orders[] = $order;
            }
            $page++;
            $finish = ($results->next === null || $this->importOneOrder) ? true : false;
        } while ($finish != true);
        return $orders;
    }

    /**
     * Create or update order in prestashop
     *
     * @param mixed $orders API orders
     * @param integer $idShop Prestashop shop Id
     *
     * @return array|false
     */
    protected function importOrders($orders, $idShop)
    {
        $orderNew = 0;
        $orderUpdate = 0;
        $orderError = 0;
        $importFinished = false;
        foreach ($orders as $orderData) {
            if (!$this->importOneOrder) {
                self::setInProcess();
            }
            $nbPackage = 0;
            $marketplaceSku = (string)$orderData->marketplace_order_id;
            if ($this->debugMode) {
                $marketplaceSku .= '--' . time();
            }
            // set current order to cancel hook updateOrderStatus
            self::$currentOrder = $marketplaceSku;
            // if order contains no package
            if (empty($orderData->packages)) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
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
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage('log.import.error_no_delivery_address'),
                        $this->logOutput,
                        $marketplaceSku
                    );
                    continue;
                }
                $packageDeliveryAddressId = (int)$packageData->delivery->id;
                $firstPackage = $nbPackage > 1 ? false : true;
                // check the package for re-import order
                if ($this->importOneOrder) {
                    if ($this->deliveryAddressId !== null
                        && $this->deliveryAddressId != $packageDeliveryAddressId
                    ) {
                        LengowMain::log(
                            LengowLog::CODE_IMPORT,
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
                            'context' => $this->context,
                            'id_shop' => $idShop,
                            'id_shop_group' => $this->idShopGroup,
                            'id_lang' => $this->idLang,
                            'force_product' => $this->forceProduct,
                            'debug_mode' => $this->debugMode,
                            'log_output' => $this->logOutput,
                            'marketplace_sku' => $marketplaceSku,
                            'delivery_address_id' => $packageDeliveryAddressId,
                            'order_data' => $orderData,
                            'package_data' => $packageData,
                            'first_package' => $firstPackage,
                            'import_one_order' => $this->importOneOrder,
                        )
                    );
                    $order = $importOrder->importOrder();
                } catch (LengowException $e) {
                    $errorMessage = $e->getMessage();
                } catch (Exception $e) {
                    $errorMessage = '[Prestashop error]: "' . $e->getMessage()
                        . '" ' . $e->getFile() . ' | ' . $e->getLine();
                }
                if (isset($errorMessage)) {
                    $decodedMessage = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
                    LengowMain::log(
                        LengowLog::CODE_IMPORT,
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
                if (isset($order)) {
                    // sync to lengow if no debug_mode
                    if (!$this->debugMode && isset($order['order_new']) && $order['order_new']) {
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
                        LengowMain::log(LengowLog::CODE_IMPORT, $synchroMessage, $this->logOutput, $marketplaceSku);
                        unset($lengowOrder);
                    }
                    // if re-import order -> return order data
                    if ($this->importOneOrder) {
                        return $order;
                    }
                    if (isset($order['order_new']) && $order['order_new']) {
                        $orderNew++;
                    } elseif (isset($order['order_update']) && $order['order_update']) {
                        $orderUpdate++;
                    } elseif (isset($order['order_error']) && $order['order_error']) {
                        $orderError++;
                    }
                }
                // clean process
                self::$currentOrder = -1;
                unset($importOrder, $order);
                // if limit is set
                if ($this->limit > 0 && $orderNew === $this->limit) {
                    $importFinished = true;
                    break;
                }
            }
            if ($importFinished) {
                break;
            }
        }
        return array(
            'order_new' => $orderNew,
            'order_update' => $orderUpdate,
            'order_error' => $orderError,
        );
    }

    /**
     * Set interval time for order synchronisation
     *
     * @param integer|false $days Import period
     * @param string|false $createdFrom Import of orders since
     * @param string|false $createdTo Import of orders until
     */
    protected function setIntervalTime($days, $createdFrom, $createdTo)
    {
        if ($createdFrom && $createdTo) {
            // retrieval of orders created from ... until ...
            $createdFromTimestamp = strtotime($createdFrom);
            $createdToTimestamp = strtotime($createdTo) + 86399;
            $intervalTime = (int)($createdToTimestamp - $createdFromTimestamp);
            $this->createdFrom = $createdFromTimestamp;
            $this->createdTo = $intervalTime > self::MAX_INTERVAL_TIME
                ? $createdFromTimestamp + self::MAX_INTERVAL_TIME
                : $createdToTimestamp;
        } else {
            if ($days) {
                $intervalTime = $days * 86400;
                $intervalTime = $intervalTime > self::MAX_INTERVAL_TIME ? self::MAX_INTERVAL_TIME : $intervalTime;
            } else {
                // order recovery updated since ... days
                $importDays = (int)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_DAYS');
                $intervalTime = $importDays * 86400;
                // add security for older versions of the plugin
                $intervalTime = $intervalTime < self::MIN_INTERVAL_TIME ? self::MIN_INTERVAL_TIME : $intervalTime;
                $intervalTime = $intervalTime > self::MAX_INTERVAL_TIME ? self::MAX_INTERVAL_TIME : $intervalTime;
                // get dynamic interval time for cron synchronisation
                $lastImport = LengowMain::getLastImport();
                $lastSettingUpdate = (int)LengowConfiguration::getGlobalValue('LENGOW_LAST_SETTING_UPDATE');
                if ($this->typeImport !== self::TYPE_MANUAL
                    && $lastImport['timestamp'] !== 'none'
                    && $lastImport['timestamp'] > $lastSettingUpdate
                ) {
                    $lastIntervalTime = (time() - $lastImport['timestamp']) + self::SECURITY_INTERVAL_TIME;
                    $intervalTime = $lastIntervalTime > $intervalTime ? $intervalTime : $lastIntervalTime;
                }
            }
            $this->updatedFrom = time() - $intervalTime;
            $this->updatedTo = time();
        }
    }

    /**
     * Check if order status is valid for import
     *
     * @param string $orderStateMarketplace Marketplace order state
     * @param LengowMarketplace $marketplace Lengow marketplace instance
     *
     * @return boolean
     */
    public static function checkState($orderStateMarketplace, $marketplace)
    {
        if (empty($orderStateMarketplace)) {
            return false;
        }
        if (!in_array($marketplace->getStateLengow($orderStateMarketplace), self::$lengowStates)) {
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
                self::setEnd();
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
        self::$processing = true;
        return LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', time());
    }

    /**
     * Set import to finished
     *
     * @return boolean
     */
    public static function setEnd()
    {
        self::$processing = false;
        return LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', -1);
    }
}
