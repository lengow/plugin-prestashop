<?php
/**
 * Copyright 2021 Lengow SAS.
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
 * @copyright 2021 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
/**
 * Lengow Import Class
 */
class LengowImport
{
    /* Import GET params */
    public const PARAM_TOKEN = 'token';
    public const PARAM_TYPE = 'type';
    public const PARAM_SHOP_ID = 'shop_id';
    public const PARAM_MARKETPLACE_SKU = 'marketplace_sku';
    public const PARAM_MARKETPLACE_NAME = 'marketplace_name';
    public const PARAM_DELIVERY_ADDRESS_ID = 'delivery_address_id';
    public const PARAM_DAYS = 'days';
    public const PARAM_CREATED_FROM = 'created_from';
    public const PARAM_CREATED_TO = 'created_to';
    public const PARAM_ID_ORDER_LENGOW = 'id_order_lengow';
    public const PARAM_LIMIT = 'limit';
    public const PARAM_LOG_OUTPUT = 'log_output';
    public const PARAM_DEBUG_MODE = 'debug_mode';
    public const PARAM_FORCE = 'force';
    public const PARAM_FORCE_SYNC = 'force_sync';
    public const PARAM_FORCE_PRODUCT = 'force_product';
    public const PARAM_SYNC = 'sync';
    public const PARAM_GET_SYNC = 'get_sync';

    /* Import API arguments */
    public const ARG_ACCOUNT_ID = 'account_id';
    public const ARG_CATALOG_IDS = 'catalog_ids';
    public const ARG_MARKETPLACE = 'marketplace';
    public const ARG_MARKETPLACE_ORDER_DATE_FROM = 'marketplace_order_date_from';
    public const ARG_MARKETPLACE_ORDER_DATE_TO = 'marketplace_order_date_to';
    public const ARG_MARKETPLACE_ORDER_ID = 'marketplace_order_id';
    public const ARG_MERCHANT_ORDER_ID = 'merchant_order_id';
    public const ARG_NO_CURRENCY_CONVERSION = 'no_currency_conversion';
    public const ARG_PAGE = 'page';
    public const ARG_UPDATED_FROM = 'updated_from';
    public const ARG_UPDATED_TO = 'updated_to';

    /* Import types */
    public const TYPE_MANUAL = 'manual';
    public const TYPE_CRON = 'cron';
    public const TYPE_TOOLBOX = 'toolbox';

    /* Import Data */
    public const NUMBER_ORDERS_PROCESSED = 'number_orders_processed';
    public const NUMBER_ORDERS_CREATED = 'number_orders_created';
    public const NUMBER_ORDERS_UPDATED = 'number_orders_updated';
    public const NUMBER_ORDERS_FAILED = 'number_orders_failed';
    public const NUMBER_ORDERS_IGNORED = 'number_orders_ignored';
    public const NUMBER_ORDERS_NOT_FORMATTED = 'number_orders_not_formatted';
    public const ORDERS_CREATED = 'orders_created';
    public const ORDERS_UPDATED = 'orders_updated';
    public const ORDERS_FAILED = 'orders_failed';
    public const ORDERS_IGNORED = 'orders_ignored';
    public const ORDERS_NOT_FORMATTED = 'orders_not_formatted';
    public const ERRORS = 'errors';

    /**
     * @var int max interval time for order synchronisation old versions (1 day)
     */
    public const MIN_INTERVAL_TIME = 86400;

    /**
     * @var int max import days for old versions (10 days)
     */
    public const MAX_INTERVAL_TIME = 864000;

    /**
     * @var int security interval time for cron synchronisation (2 hours)
     */
    public const SECURITY_INTERVAL_TIME = 7200;

    /**
     * @var int interval of months for cron synchronisation
     */
    public const MONTH_INTERVAL_TIME = 3;

    /**
     * @var int interval of minutes for cron synchronisation
     */
    public const MINUTE_INTERVAL_TIME = 1;

    /**
     * @var bool import is processing
     */
    public static $processing;

    /**
     * @var string order id being imported
     */
    public static $currentOrder = -1;

    /**
     * @var array valid states lengow to create a Lengow order
     */
    public static $lengowStates = [
        LengowOrder::STATE_WAITING_SHIPMENT,
        LengowOrder::STATE_SHIPPED,
        LengowOrder::STATE_CLOSED,
        LengowOrder::STATE_PARTIALLY_REFUNDED,
    ];

    /**
     * @var int PrestaShop lang id
     */
    private $idLang;

    /**
     * @var int|null PrestaShop shop id
     */
    private $idShop;

    /**
     * @var int PrestaShop shop group id
     */
    private $idShopGroup;

    /**
     * @var bool use debug mode
     */
    private $debugMode;

    /**
     * @var bool display log messages
     */
    private $logOutput;

    /**
     * @var string|null marketplace order sku
     */
    private $marketplaceSku;

    /**
     * @var string|null marketplace name
     */
    private $marketplaceName;

    /**
     * @var int|null delivery address id
     */
    private $deliveryAddressId;

    /**
     * @var int maximum number of new orders created
     */
    private $limit;

    /**
     * @var bool force import order even if there are errors
     */
    private $forceSync;

    /**
     * @var bool import inactive & out of stock products
     */
    private $forceProduct;

    /**
     * @var int|false imports orders updated since (timestamp)
     */
    private $updatedFrom = false;

    /**
     * @var int|false imports orders updated until (timestamp)
     */
    private $updatedTo = false;

    /**
     * @var int|false imports orders created since (timestamp)
     */
    private $createdFrom = false;

    /**
     * @var int|false imports orders created until (timestamp)
     */
    private $createdTo = false;

    /**
     * @var string Lengow account id
     */
    private $accountId;

    /**
     * @var LengowConnector Lengow connector
     */
    private $connector;

    /**
     * @var Context Context for import order
     */
    private $context;

    /**
     * @var string type import (manual or cron)
     */
    private $typeImport;

    /**
     * @var bool import one order
     */
    private $importOneOrder = false;

    /**
     * @var array shop catalog ids for import
     */
    private $shopCatalogIds = [];

    /**
     * @var array catalog ids already imported
     */
    private $catalogIds = [];

    /**
     * @var int id of lengow order record
     */
    private $idOrderLengow;

    /**
     * @var array all orders created during the process
     */
    private $ordersCreated = [];

    /**
     * @var array all orders updated during the process
     */
    private $ordersUpdated = [];

    /**
     * @var array all orders failed during the process
     */
    private $ordersFailed = [];

    /**
     * @var array all orders ignored during the process
     */
    private $ordersIgnored = [];

    /**
     * @var array all incorrectly formatted orders that cannot be processed
     */
    private $ordersNotFormatted = [];

    /**
     * @var array all synchronization error (global or by shop)
     */
    private $errors = [];

    /**
     * Construct the import manager
     *
     * @param array $params optional options
     *                      string  marketplace_sku     Lengow marketplace order id to synchronize
     *                      string  marketplace_name    Lengow marketplace name to synchronize
     *                      string  type                Type of current synchronization
     *                      string  created_from        Synchronization of orders since
     *                      string  created_to          Synchronization of orders until
     *                      integer delivery_address_id Lengow delivery address id to synchronize
     *                      integer id_order_lengow     Lengow order id in PrestaShop
     *                      integer shop_id             Shop id for current synchronization
     *                      integer days                Synchronization interval time
     *                      integer limit               Maximum number of new orders created
     *                      boolean log_output          Display log messages
     *                      boolean debug_mode          Activate debug mode
     *                      boolean force_sync          Force synchronization order even if there are errors
     *                      boolean force_product       Force import product when quantity is insufficient
     */
    public function __construct($params = [])
    {
        // get generic params for synchronisation
        $this->debugMode = isset($params[self::PARAM_DEBUG_MODE])
            ? (bool) $params[self::PARAM_DEBUG_MODE]
            : LengowConfiguration::debugModeIsActive();
        $this->typeImport = isset($params[self::PARAM_TYPE]) ? $params[self::PARAM_TYPE] : self::TYPE_MANUAL;
        $this->forceSync = isset($params[self::PARAM_FORCE_SYNC]) && $params[self::PARAM_FORCE_SYNC];
        $this->forceProduct = isset($params[self::PARAM_FORCE_PRODUCT])
            ? (bool) $params[self::PARAM_FORCE_PRODUCT]
            : (bool) LengowConfiguration::getGlobalValue(LengowConfiguration::FORCE_PRODUCT_ENABLED);
        $this->logOutput = isset($params[self::PARAM_LOG_OUTPUT]) && $params[self::PARAM_LOG_OUTPUT];
        $this->idShop = isset($params[self::PARAM_SHOP_ID]) ? (int) $params[self::PARAM_SHOP_ID] : null;
        // get params for synchronise one or all orders
        if (array_key_exists(self::PARAM_MARKETPLACE_SKU, $params)
            && array_key_exists(self::PARAM_MARKETPLACE_NAME, $params)
            && array_key_exists(self::PARAM_SHOP_ID, $params)
        ) {
            $this->marketplaceSku = (string) $params[self::PARAM_MARKETPLACE_SKU];
            $this->marketplaceName = (string) $params[self::PARAM_MARKETPLACE_NAME];
            $this->limit = 1;
            $this->importOneOrder = true;
            if (array_key_exists(self::PARAM_DELIVERY_ADDRESS_ID, $params)
                && $params[self::PARAM_DELIVERY_ADDRESS_ID] !== ''
            ) {
                $this->deliveryAddressId = $params[self::PARAM_DELIVERY_ADDRESS_ID];
            }
            if (isset($params[self::PARAM_ID_ORDER_LENGOW])) {
                $this->idOrderLengow = (int) $params[self::PARAM_ID_ORDER_LENGOW];
                $this->forceSync = true;
            }
        } else {
            $this->marketplaceSku = null;
            // set the time interval
            $this->setIntervalTime(
                isset($params[self::PARAM_DAYS]) ? (int) $params[self::PARAM_DAYS] : null,
                isset($params[self::PARAM_CREATED_FROM]) ? $params[self::PARAM_CREATED_FROM] : null,
                isset($params[self::PARAM_CREATED_TO]) ? $params[self::PARAM_CREATED_TO] : null
            );
            $this->limit = isset($params[self::PARAM_LIMIT]) ? (int) $params[self::PARAM_LIMIT] : 0;
        }
        LengowMain::log(
            LengowLog::CODE_IMPORT,
            LengowMain::setLogMessage(
                'log.import.init_params',
                ['init_params' => json_encode($params)]
            ),
            $this->logOutput
        );
    }

    /**
     * Execute import : fetch orders and import them
     *
     * @return array
     */
    public function exec()
    {
        $syncOk = true;
        // get initial context type
        $initialContextShop = Context::getContext()->shop;
        $initialContextType = $initialContextShop::getContext();
        // checks if a synchronization is not already in progress
        if (!$this->canExecuteSynchronization()) {
            return $this->getResult();
        }
        // starts some processes necessary for synchronization
        $this->setupSynchronization();
        // get all active shops in Lengow for order synchronization
        $activeShops = LengowShop::getActiveShops(true, $this->idShop);
        foreach ($activeShops as $shop) {
            // clean PrestaShop context
            $this->context = null;
            // synchronize all orders for a specific shop
            if (!$this->synchronizeOrdersByShop($shop)) {
                $syncOk = false;
            }
        }
        // get order synchronization result
        $result = $this->getResult();
        LengowMain::log(
            LengowLog::CODE_IMPORT,
            LengowMain::setLogMessage(
                'log.import.sync_result',
                [
                    'number_orders_processed' => $result[self::NUMBER_ORDERS_PROCESSED],
                    'number_orders_created' => $result[self::NUMBER_ORDERS_CREATED],
                    'number_orders_updated' => $result[self::NUMBER_ORDERS_UPDATED],
                    'number_orders_failed' => $result[self::NUMBER_ORDERS_FAILED],
                    'number_orders_ignored' => $result[self::NUMBER_ORDERS_IGNORED],
                    'number_orders_not_formatted' => $result[self::NUMBER_ORDERS_NOT_FORMATTED],
                ]
            ),
            $this->logOutput
        );
        // update last synchronization date only if importation succeeded
        if (!$this->importOneOrder && $syncOk) {
            LengowMain::updateDateImport($this->typeImport);
        }
        // clean Context type with initial type if different
        $currentContextShop = Context::getContext()->shop;
        if (isset($initialContextType) && $initialContextType !== $currentContextShop::getContext()) {
            try {
                $currentContextShop::setContext($initialContextType);
            } catch (Exception $e) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage('log.import.clean_context_failed'),
                    $this->logOutput
                );
            }
            Context::getContext()->shop = $currentContextShop;
        }
        // complete synchronization and start all necessary processes
        $this->finishSynchronization();

        return $result;
    }

    /**
     * Check if order status is valid for import
     *
     * @param string $orderStateMarketplace Marketplace order state
     * @param LengowMarketplace $marketplace Lengow marketplace instance
     *
     * @return bool
     */
    public static function checkState($orderStateMarketplace, $marketplace)
    {
        if (empty($orderStateMarketplace)) {
            return false;
        }

        return in_array($marketplace->getStateLengow($orderStateMarketplace), self::$lengowStates, true);
    }

    /**
     * Check if order synchronization is already in process
     *
     * @return bool
     */
    public static function isInProcess()
    {
        $timestamp = LengowConfiguration::getGlobalValue(LengowConfiguration::SYNCHRONIZATION_IN_PROGRESS);
        if ($timestamp <= 0) {
            return false;
        }
        // security check : if last import is more than 60 seconds old => authorize new import to be launched
        if (($timestamp + (60 * self::MINUTE_INTERVAL_TIME)) < time()) {
            self::setEnd();

            return false;
        }

        return true;
    }

    /**
     * Get Rest time to make a new order synchronization
     *
     * @return int
     */
    public static function restTimeToImport()
    {
        $timestamp = LengowConfiguration::getGlobalValue(LengowConfiguration::SYNCHRONIZATION_IN_PROGRESS);

        return $timestamp > 0 ? $timestamp + (60 * self::MINUTE_INTERVAL_TIME) - time() : 0;
    }

    /**
     * Set interval time for order synchronisation
     *
     * @param int|null $days Import period
     * @param string|null $createdFrom Import of orders since
     * @param string|null $createdTo Import of orders until
     */
    private function setIntervalTime($days = null, $createdFrom = null, $createdTo = null)
    {
        if ($createdFrom && $createdTo) {
            // retrieval of orders created from ... until ...
            $this->createdFrom = strtotime($createdFrom);
            $createdToTimestamp = strtotime($createdTo) + 86399;
            $intervalTime = $createdToTimestamp - $this->createdFrom;
            $this->createdTo = $intervalTime > self::MAX_INTERVAL_TIME
                ? $this->createdFrom + self::MAX_INTERVAL_TIME
                : $createdToTimestamp;

            return;
        }
        if ($days) {
            $intervalTime = $days * 86400;
            $intervalTime = $intervalTime > self::MAX_INTERVAL_TIME ? self::MAX_INTERVAL_TIME : $intervalTime;
        } else {
            // order recovery updated since ... days
            $importDays = (int) LengowConfiguration::getGlobalValue(
                LengowConfiguration::SYNCHRONIZATION_DAY_INTERVAL
            );
            $intervalTime = $importDays * 86400;
            // add security for older versions of the plugin
            $intervalTime = $intervalTime < self::MIN_INTERVAL_TIME ? self::MIN_INTERVAL_TIME : $intervalTime;
            $intervalTime = $intervalTime > self::MAX_INTERVAL_TIME ? self::MAX_INTERVAL_TIME : $intervalTime;
            // get dynamic interval time for cron synchronisation
            $lastImport = LengowMain::getLastImport();
            $lastSettingUpdate = (int) LengowConfiguration::getGlobalValue(
                LengowConfiguration::LAST_UPDATE_SETTING
            );
            if ($this->typeImport === self::TYPE_CRON
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

    /**
     * Checks if a synchronization is not already in progress
     *
     * @return bool
     */
    private function canExecuteSynchronization()
    {
        $globalError = false;
        // checks if the process can start
        if (!$this->debugMode && !$this->importOneOrder && self::isInProcess()) {
            $globalError = LengowMain::setLogMessage(
                'lengow_log.error.rest_time_to_import',
                ['rest_time' => self::restTimeToImport()]
            );
            LengowMain::log(LengowLog::CODE_IMPORT, $globalError, $this->logOutput);
        } elseif (!$this->checkCredentials()) {
            $globalError = LengowMain::setLogMessage('lengow_log.error.credentials_not_valid');
            LengowMain::log(LengowLog::CODE_IMPORT, $globalError, $this->logOutput);
        }
        // if we have a global error, we stop the process directly
        if ($globalError) {
            $this->errors[0] = $globalError;
            if (isset($this->idOrderLengow) && $this->idOrderLengow) {
                LengowOrderError::finishOrderLogs($this->idOrderLengow);
                LengowOrderError::addOrderLog($this->idOrderLengow, $globalError);
            }

            return false;
        }

        return true;
    }

    /**
     * Starts some processes necessary for synchronization
     */
    private function setupSynchronization()
    {
        // suppress log files when too old
        LengowMain::cleanLog();
        if (!$this->importOneOrder) {
            self::setInProcess();
        }
        // checks Lengow catalogs and carriers for order synchronization
        if (!$this->importOneOrder && $this->typeImport === self::TYPE_MANUAL) {
            LengowSync::syncCatalog();
            LengowSync::syncCarrier();
        }
        LengowMain::log(
            LengowLog::CODE_IMPORT,
            LengowMain::setLogMessage('log.import.start', ['type' => $this->typeImport]),
            $this->logOutput
        );
        if ($this->debugMode) {
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage('log.import.debug_mode_active'),
                $this->logOutput
            );
        }
        // disabling automatic emails when creating orders
        LengowMain::disableMail();
    }

    /**
     * Check credentials and get Lengow connector
     *
     * @return bool
     */
    private function checkCredentials()
    {
        if (LengowConnector::isValidAuth($this->logOutput)) {
            list($this->accountId, $accessToken, $secretToken) = LengowConfiguration::getAccessIds();
            $this->connector = new LengowConnector($accessToken, $secretToken);

            return true;
        }

        return false;
    }

    /**
     * Return the synchronization result
     *
     * @return array
     */
    private function getResult()
    {
        $nbOrdersCreated = count($this->ordersCreated);
        $nbOrdersUpdated = count($this->ordersUpdated);
        $nbOrdersFailed = count($this->ordersFailed);
        $nbOrdersIgnored = count($this->ordersIgnored);
        $nbOrdersNotFormatted = count($this->ordersNotFormatted);
        $nbOrdersProcessed = $nbOrdersCreated
            + $nbOrdersUpdated
            + $nbOrdersFailed
            + $nbOrdersIgnored
            + $nbOrdersNotFormatted;

        return [
            self::NUMBER_ORDERS_PROCESSED => $nbOrdersProcessed,
            self::NUMBER_ORDERS_CREATED => $nbOrdersCreated,
            self::NUMBER_ORDERS_UPDATED => $nbOrdersUpdated,
            self::NUMBER_ORDERS_FAILED => $nbOrdersFailed,
            self::NUMBER_ORDERS_IGNORED => $nbOrdersIgnored,
            self::NUMBER_ORDERS_NOT_FORMATTED => $nbOrdersNotFormatted,
            self::ORDERS_CREATED => $this->ordersCreated,
            self::ORDERS_UPDATED => $this->ordersUpdated,
            self::ORDERS_FAILED => $this->ordersFailed,
            self::ORDERS_IGNORED => $this->ordersIgnored,
            self::ORDERS_NOT_FORMATTED => $this->ordersNotFormatted,
            self::ERRORS => $this->errors,
        ];
    }

    /**
     * Synchronize all orders for a specific shop
     *
     * @param LengowShop $shop Lengow shop instance
     *
     * @return bool
     */
    private function synchronizeOrdersByShop($shop)
    {
        LengowMain::log(
            LengowLog::CODE_IMPORT,
            LengowMain::setLogMessage(
                'log.import.start_for_shop',
                ['name_shop' => $shop->name, 'id_shop' => $shop->id]
            ),
            $this->logOutput
        );
        // check shop catalog ids
        if (!$this->checkCatalogIds($shop)) {
            return true;
        }
        try {
            // change context with current shop id
            $this->changeContext((int) $shop->id);
            // get orders from Lengow API
            $orders = $this->getOrdersFromApi($shop);
            $numberOrdersFound = count($orders);
            if ($this->importOneOrder) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage(
                        'log.import.find_one_order',
                        [
                            'nb_order' => $numberOrdersFound,
                            'marketplace_sku' => $this->marketplaceSku,
                            'marketplace_name' => $this->marketplaceName,
                            'account_id' => $this->accountId,
                        ]
                    ),
                    $this->logOutput
                );
            } else {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage(
                        'log.import.find_all_orders',
                        [
                            'nb_order' => $numberOrdersFound,
                            'account_id' => $this->accountId,
                        ]
                    ),
                    $this->logOutput
                );
            }
            if ($numberOrdersFound <= 0 && $this->importOneOrder) {
                throw new LengowException('lengow_log.error.order_not_found');
            }
            if ($numberOrdersFound > 0) {
                // import orders in PrestaShop
                $this->importOrders($orders, (int) $shop->id);
            }
        } catch (LengowException $e) {
            $errorMessage = $e->getMessage();
        } catch (Exception $e) {
            $errorMessage = '[PrestaShop error]: "' . $e->getMessage()
                . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
        }
        if (isset($errorMessage)) {
            if (isset($this->idOrderLengow) && $this->idOrderLengow) {
                LengowOrderError::finishOrderLogs($this->idOrderLengow);
                LengowOrderError::addOrderLog($this->idOrderLengow, $errorMessage);
            }
            $decodedMessage = LengowMain::decodeLogMessage(
                $errorMessage,
                LengowTranslation::DEFAULT_ISO_CODE
            );
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.import_failed',
                    ['decoded_message' => $decodedMessage]
                ),
                $this->logOutput
            );
            $this->errors[(int) $shop->id] = $errorMessage;
            unset($errorMessage);

            return false;
        }

        return true;
    }

    /**
     * Check catalog ids for a shop
     *
     * @param LengowShop $shop Lengow shop instance
     *
     * @return bool
     */
    private function checkCatalogIds($shop)
    {
        if ($this->importOneOrder) {
            return true;
        }
        $shopCatalogIds = [];
        $catalogIds = LengowConfiguration::getCatalogIds((int) $shop->id);
        foreach ($catalogIds as $catalogId) {
            if (array_key_exists($catalogId, $this->catalogIds)) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage(
                        'log.import.catalog_id_already_used',
                        [
                            'catalog_id' => $catalogId,
                            'name_shop' => $this->catalogIds[$catalogId]['name'],
                            'id_shop' => $this->catalogIds[$catalogId]['shopId'],
                        ]
                    ),
                    $this->logOutput
                );
            } else {
                $this->catalogIds[$catalogId] = [
                    'shopId' => (int) $shop->id,
                    'name' => (string) $shop->name,
                ];
                $shopCatalogIds[] = $catalogId;
            }
        }
        if (!empty($shopCatalogIds)) {
            $this->shopCatalogIds = $shopCatalogIds;

            return true;
        }
        $message = LengowMain::setLogMessage(
            'lengow_log.error.no_catalog_for_shop',
            [
                'name_shop' => $shop->name,
                'id_shop' => $shop->id,
            ]
        );
        LengowMain::log(LengowLog::CODE_IMPORT, $message, $this->logOutput);
        $this->errors[(int) $shop->id] = $message;

        return false;
    }

    /**
     * Change Context for import
     *
     * @param int $idShop PrestaShop shop Id
     *
     * @throws Exception
     */
    private function changeContext($idShop)
    {
        $this->context = Context::getContext()->cloneContext();
        if ($shop = new Shop($idShop)) {
            $shop::setContext(Shop::CONTEXT_SHOP, $shop->id);
            $this->context->shop = $shop;
        }
        $this->idLang = $this->context->language->id;
        $this->idShopGroup = $this->context->shop->id_shop_group;
    }

    /**
     * Call Lengow order API
     *
     * @param LengowShop $shop
     *
     * @return array
     *
     * @throws LengowException no connection with the webservice / credentials not valid
     */
    private function getOrdersFromApi($shop)
    {
        $page = 1;
        $orders = [];
        $currencyConversion = !(bool) LengowConfiguration::get(LengowConfiguration::CURRENCY_CONVERSION_ENABLED);
        if ($this->importOneOrder) {
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.connector_get_order',
                    [
                        'marketplace_sku' => $this->marketplaceSku,
                        'marketplace_name' => $this->marketplaceName,
                    ]
                ),
                $this->logOutput
            );
        } else {
            $dateFrom = $this->createdFrom ?: $this->updatedFrom;
            $dateTo = $this->createdTo ?: $this->updatedTo;
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    'log.import.connector_get_all_order',
                    [
                        'date_from' => date(LengowMain::DATE_FULL, $dateFrom),
                        'date_to' => date(LengowMain::DATE_FULL, $dateTo),
                        'catalog_id' => implode(', ', $this->shopCatalogIds),
                    ]
                ),
                $this->logOutput
            );
        }
        do {
            try {
                if ($this->importOneOrder) {
                    $results = $this->connector->get(
                        LengowConnector::API_ORDER,
                        [
                            self::ARG_MARKETPLACE_ORDER_ID => $this->marketplaceSku,
                            self::ARG_MARKETPLACE => $this->marketplaceName,
                            self::ARG_NO_CURRENCY_CONVERSION => $currencyConversion,
                            self::ARG_ACCOUNT_ID => $this->accountId,
                        ],
                        LengowConnector::FORMAT_STREAM,
                        '',
                        $this->logOutput
                    );
                } else {
                    if ($this->createdFrom && $this->createdTo) {
                        $timeParams = [
                            self::ARG_MARKETPLACE_ORDER_DATE_FROM => date(
                                LengowMain::DATE_ISO_8601,
                                $this->createdFrom
                            ),
                            self::ARG_MARKETPLACE_ORDER_DATE_TO => date(
                                LengowMain::DATE_ISO_8601,
                                $this->createdTo
                            ),
                        ];
                    } else {
                        $timeParams = [
                            self::ARG_UPDATED_FROM => date(LengowMain::DATE_ISO_8601, $this->updatedFrom),
                            self::ARG_UPDATED_TO => date(LengowMain::DATE_ISO_8601, $this->updatedTo),
                        ];
                    }
                    $results = $this->connector->get(
                        LengowConnector::API_ORDER,
                        array_merge(
                            $timeParams,
                            [
                                self::ARG_CATALOG_IDS => implode(',', $this->shopCatalogIds),
                                self::ARG_ACCOUNT_ID => $this->accountId,
                                self::ARG_PAGE => $page,
                                self::ARG_NO_CURRENCY_CONVERSION => $currencyConversion,
                            ]
                        ),
                        LengowConnector::FORMAT_STREAM,
                        '',
                        $this->logOutput
                    );
                }
            } catch (Exception $e) {
                throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.error_lengow_webservice', ['error_code' => $e->getCode(), 'error_message' => LengowMain::decodeLogMessage($e->getMessage(), LengowTranslation::DEFAULT_ISO_CODE), 'name_shop' => $shop->name, 'id_shop' => (int) $shop->id]));
            }
            if ($results === null) {
                throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.no_connection_webservice', ['name_shop' => $shop->name, 'id_shop' => (int) $shop->id]));
            }
            $results = json_decode($results);
            if (!is_object($results)) {
                throw new LengowException(LengowMain::setLogMessage('lengow_log.exception.no_connection_webservice', ['name_shop' => $shop->name, 'id_shop' => (int) $shop->id]));
            }
            // construct array orders
            foreach ($results->results as $order) {
                $orders[] = $order;
            }
            ++$page;
            $finish = $results->next === null || $this->importOneOrder;
        } while ($finish !== true);

        return $orders;
    }

    /**
     * Create or update order in prestashop
     *
     * @param mixed $orders API orders
     * @param int $idShop PrestaShop shop Id
     */
    private function importOrders($orders, $idShop)
    {
        $importFinished = false;
        foreach ($orders as $orderData) {
            if (!$this->importOneOrder) {
                self::setInProcess();
            }
            $nbPackage = 0;
            $marketplaceSku = (string) $orderData->marketplace_order_id;
            if ($this->debugMode) {
                $marketplaceSku .= '--' . time();
            }
            // set current order to cancel hook updateOrderStatus
            self::$currentOrder = $marketplaceSku;
            // if order contains no package
            if (empty($orderData->packages)) {
                $message = LengowMain::setLogMessage('log.import.error_no_package');
                LengowMain::log(LengowLog::CODE_IMPORT, $message, $this->logOutput, $marketplaceSku);
                $this->addOrderNotFormatted($marketplaceSku, $message, $orderData);
                continue;
            }
            // start import
            foreach ($orderData->packages as $packageData) {
                ++$nbPackage;
                // check whether the package contains a shipping address
                if (!isset($packageData->delivery->id)) {
                    $message = LengowMain::setLogMessage('log.import.error_no_delivery_address');
                    LengowMain::log(LengowLog::CODE_IMPORT, $message, $this->logOutput, $marketplaceSku);
                    $this->addOrderNotFormatted($marketplaceSku, $message, $orderData);
                    continue;
                }
                $packageDeliveryAddressId = (int) $packageData->delivery->id;
                $firstPackage = !($nbPackage > 1);
                // check the package for re-import order
                if ($this->importOneOrder
                    && $this->deliveryAddressId !== null
                    && $this->deliveryAddressId != $packageDeliveryAddressId
                ) {
                    $message = LengowMain::setLogMessage('log.import.error_no_delivery_address');
                    LengowMain::log(LengowLog::CODE_IMPORT, $message, $this->logOutput, $marketplaceSku);
                    $this->addOrderNotFormatted($marketplaceSku, $message, $orderData);
                    continue;
                }
                try {
                    // try to import or update order
                    $importOrder = new LengowImportOrder(
                        [
                            LengowImportOrder::PARAM_CONTEXT => $this->context,
                            LengowImportOrder::PARAM_SHOP_ID => $idShop,
                            LengowImportOrder::PARAM_SHOP_GROUP_ID => $this->idShopGroup,
                            LengowImportOrder::PARAM_LANG_ID => $this->idLang,
                            LengowImportOrder::PARAM_FORCE_SYNC => $this->forceSync,
                            LengowImportOrder::PARAM_FORCE_PRODUCT => $this->forceProduct,
                            LengowImportOrder::PARAM_DEBUG_MODE => $this->debugMode,
                            LengowImportOrder::PARAM_LOG_OUTPUT => $this->logOutput,
                            LengowImportOrder::PARAM_MARKETPLACE_SKU => $marketplaceSku,
                            LengowImportOrder::PARAM_DELIVERY_ADDRESS_ID => $packageDeliveryAddressId,
                            LengowImportOrder::PARAM_ORDER_DATA => $orderData,
                            LengowImportOrder::PARAM_PACKAGE_DATA => $packageData,
                            LengowImportOrder::PARAM_FIRST_PACKAGE => $firstPackage,
                            LengowImportOrder::PARAM_IMPORT_ONE_ORDER => $this->importOneOrder,
                        ]
                    );
                    $result = $importOrder->importOrder();
                    // synchronize the merchant order id with Lengow
                    $this->synchronizeMerchantOrderId($result);
                    // save the result of the order synchronization by type
                    $this->saveSynchronizationResult($result);
                    // clean import order process
                    unset($importOrder, $result);
                } catch (Exception $e) {
                    $errorMessage = '[PrestaShop error]: "' . $e->getMessage()
                        . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
                    LengowMain::log(
                        LengowLog::CODE_IMPORT,
                        LengowMain::setLogMessage(
                            'log.import.order_import_failed',
                            ['decoded_message' => $errorMessage]
                        ),
                        $this->logOutput,
                        $marketplaceSku
                    );
                    unset($errorMessage);
                    continue;
                }
                // if limit is set
                if ($this->limit > 0 && count($this->ordersCreated) === $this->limit) {
                    $importFinished = true;
                    break;
                }
            }
            // clean current order
            self::$currentOrder = -1;
            if ($importFinished) {
                break;
            }
        }
    }

    /**
     * Return an array of result for order not formatted
     *
     * @param string $marketplaceSku id lengow of current order
     * @param string $errorMessage Error message
     * @param mixed $orderData API order data
     */
    private function addOrderNotFormatted($marketplaceSku, $errorMessage, $orderData)
    {
        $messageDecoded = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
        $this->ordersNotFormatted[] = [
            LengowImportOrder::MERCHANT_ORDER_ID => null,
            LengowImportOrder::MERCHANT_ORDER_REFERENCE => null,
            LengowImportOrder::LENGOW_ORDER_ID => $this->idOrderLengow,
            LengowImportOrder::MARKETPLACE_SKU => $marketplaceSku,
            LengowImportOrder::MARKETPLACE_NAME => (string) $orderData->marketplace,
            LengowImportOrder::DELIVERY_ADDRESS_ID => null,
            LengowImportOrder::SHOP_ID => $this->idShop,
            LengowImportOrder::CURRENT_ORDER_STATUS => (string) $orderData->lengow_status,
            LengowImportOrder::PREVIOUS_ORDER_STATUS => (string) $orderData->lengow_status,
            LengowImportOrder::ERRORS => [$messageDecoded],
        ];
    }

    /**
     * Synchronize the merchant order id with Lengow
     *
     * @param array $result synchronization order result
     */
    private function synchronizeMerchantOrderId($result)
    {
        if (!$this->debugMode && $result[LengowImportOrder::RESULT_TYPE] === LengowImportOrder::RESULT_CREATED) {
            $lengowOrder = new LengowOrder((int) $result[LengowImportOrder::MERCHANT_ORDER_ID]);
            $success = $lengowOrder->synchronizeOrder($this->connector, $this->logOutput);
            $messageKey = $success
                ? 'log.import.order_synchronized_with_lengow'
                : 'log.import.order_not_synchronized_with_lengow';
            LengowMain::log(
                LengowLog::CODE_IMPORT,
                LengowMain::setLogMessage(
                    $messageKey,
                    ['order_id' => $result[LengowImportOrder::MERCHANT_ORDER_ID]]
                ),
                $this->logOutput,
                $result[LengowImportOrder::MARKETPLACE_SKU]
            );
        }
    }

    /**
     * Save the result of the order synchronization by type
     *
     * @param array $result synchronization order result
     */
    private function saveSynchronizationResult($result)
    {
        $resultType = $result[LengowImportOrder::RESULT_TYPE];
        unset($result[LengowImportOrder::RESULT_TYPE]);
        switch ($resultType) {
            case LengowImportOrder::RESULT_CREATED:
                $this->ordersCreated[] = $result;
                break;
            case LengowImportOrder::RESULT_UPDATED:
                $this->ordersUpdated[] = $result;
                break;
            case LengowImportOrder::RESULT_FAILED:
                $this->ordersFailed[] = $result;
                break;
            case LengowImportOrder::RESULT_IGNORED:
                $this->ordersIgnored[] = $result;
                break;
        }
    }

    /**
     * Complete synchronization and start all necessary processes
     */
    private function finishSynchronization()
    {
        // finish synchronization process
        self::setEnd();
        LengowMain::log(
            LengowLog::CODE_IMPORT,
            LengowMain::setLogMessage('log.import.end', ['type' => $this->typeImport]),
            $this->logOutput
        );
        // check if order action is finish (ship or cancel)
        if (!$this->debugMode
            && !$this->importOneOrder
            && ($this->typeImport === self::TYPE_MANUAL || $this->typeImport === self::TYPE_CRON)
        ) {
            LengowAction::checkFinishAction($this->logOutput);
            LengowAction::checkOldAction($this->logOutput);
            LengowAction::checkActionNotSent($this->logOutput);
        }
        // sending email in error for orders (import and send errors)
        LengowMain::enableMail();
        if (!$this->debugMode
            && !$this->importOneOrder
            && LengowConfiguration::getGlobalValue(LengowConfiguration::REPORT_MAIL_ENABLED)
        ) {
            LengowMain::sendMailAlert($this->logOutput);
        }
    }

    /**
     * Set import to "in process" state
     */
    private static function setInProcess()
    {
        self::$processing = true;
        LengowConfiguration::updateGlobalValue(LengowConfiguration::SYNCHRONIZATION_IN_PROGRESS, time());
    }

    /**
     * Set import to finished
     */
    private static function setEnd()
    {
        self::$processing = false;
        LengowConfiguration::updateGlobalValue(LengowConfiguration::SYNCHRONIZATION_IN_PROGRESS, -1);
    }
}
