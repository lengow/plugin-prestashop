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
 * Lengow Toolbox Class
 */
class LengowToolbox
{
    /* Toolbox GET params */
    public const PARAM_CREATED_FROM = 'created_from';
    public const PARAM_CREATED_TO = 'created_to';
    public const PARAM_DATE = 'date';
    public const PARAM_DAYS = 'days';
    public const PARAM_FORCE = 'force';
    public const PARAM_MARKETPLACE_NAME = 'marketplace_name';
    public const PARAM_MARKETPLACE_SKU = 'marketplace_sku';
    public const PARAM_PROCESS = 'process';
    public const PARAM_SHOP_ID = 'shop_id';
    public const PARAM_TOKEN = 'token';
    public const PARAM_TOOLBOX_ACTION = 'toolbox_action';
    public const PARAM_TYPE = 'type';
    public const PARAM_SHORT_PATH = 'short_path';

    /* Toolbox actions */
    public const ACTION_DATA = 'data';
    public const ACTION_LOG = 'log';
    public const ACTION_ORDER = 'order';

    /* Toolbox data type */
    public const DATA_TYPE_ACTION = 'action';
    public const DATA_TYPE_ALL = 'all';
    public const DATA_TYPE_CHECKLIST = 'checklist';
    public const DATA_TYPE_CHECKSUM = 'checksum';
    public const DATA_TYPE_MODIFIED_FILES = 'modified_files';
    public const DATA_TYPE_CMS = 'cms';
    public const DATA_TYPE_ERROR = 'error';
    public const DATA_TYPE_EXTRA = 'extra';
    public const DATA_TYPE_LOG = 'log';
    public const DATA_TYPE_PLUGIN = 'plugin';
    public const DATA_TYPE_OPTION = 'option';
    public const DATA_TYPE_ORDER = 'order';
    public const DATA_TYPE_ORDER_STATUS = 'order_status';
    public const DATA_TYPE_SHOP = 'shop';
    public const DATA_TYPE_SYNCHRONIZATION = 'synchronization';

    /* Toolbox process type */
    public const PROCESS_TYPE_GET_DATA = 'get_data';
    public const PROCESS_TYPE_SYNC = 'sync';

    /* Toolbox data  */
    public const CHECKLIST = 'checklist';
    public const CHECKLIST_CURL_ACTIVATED = 'curl_activated';
    public const CHECKLIST_SIMPLE_XML_ACTIVATED = 'simple_xml_activated';
    public const CHECKLIST_JSON_ACTIVATED = 'json_activated';
    public const CHECKLIST_MD5_SUCCESS = 'md5_success';
    public const PLUGIN = 'plugin';
    public const PLUGIN_CMS_VERSION = 'cms_version';
    public const PLUGIN_VERSION = 'plugin_version';
    public const PLUGIN_PHP_VERSION = 'php_version';
    public const PLUGIN_DEBUG_MODE_DISABLE = 'debug_mode_disable';
    public const PLUGIN_WRITE_PERMISSION = 'write_permission';
    public const PLUGIN_SERVER_IP = 'server_ip';
    public const PLUGIN_AUTHORIZED_IP_ENABLE = 'authorized_ip_enable';
    public const PLUGIN_AUTHORIZED_IPS = 'authorized_ips';
    public const PLUGIN_TOOLBOX_URL = 'toolbox_url';
    public const SYNCHRONIZATION = 'synchronization';
    public const SYNCHRONIZATION_CMS_TOKEN = 'cms_token';
    public const SYNCHRONIZATION_CRON_URL = 'cron_url';
    public const SYNCHRONIZATION_NUMBER_ORDERS_IMPORTED = 'number_orders_imported';
    public const SYNCHRONIZATION_NUMBER_ORDERS_WAITING_SHIPMENT = 'number_orders_waiting_shipment';
    public const SYNCHRONIZATION_NUMBER_ORDERS_IN_ERROR = 'number_orders_in_error';
    public const SYNCHRONIZATION_SYNCHRONIZATION_IN_PROGRESS = 'synchronization_in_progress';
    public const SYNCHRONIZATION_LAST_SYNCHRONIZATION = 'last_synchronization';
    public const SYNCHRONIZATION_LAST_SYNCHRONIZATION_TYPE = 'last_synchronization_type';
    public const CMS_OPTIONS = 'cms_options';
    public const SHOPS = 'shops';
    public const SHOP_ID = 'shop_id';
    public const SHOP_NAME = 'shop_name';
    public const SHOP_DOMAIN_URL = 'domain_url';
    public const SHOP_TOKEN = 'shop_token';
    public const SHOP_FEED_URL = 'feed_url';
    public const SHOP_ENABLED = 'enabled';
    public const SHOP_CATALOG_IDS = 'catalog_ids';
    public const SHOP_NUMBER_PRODUCTS_AVAILABLE = 'number_products_available';
    public const SHOP_NUMBER_PRODUCTS_EXPORTED = 'number_products_exported';
    public const SHOP_LAST_EXPORT = 'last_export';
    public const SHOP_OPTIONS = 'shop_options';
    public const CHECKSUM = 'checksum';
    public const CHECKSUM_AVAILABLE = 'available';
    public const CHECKSUM_SUCCESS = 'success';
    public const CHECKSUM_NUMBER_FILES_CHECKED = 'number_files_checked';
    public const CHECKSUM_NUMBER_FILES_MODIFIED = 'number_files_modified';
    public const CHECKSUM_NUMBER_FILES_DELETED = 'number_files_deleted';
    public const CHECKSUM_FILE_MODIFIED = 'file_modified';
    public const CHECKSUM_FILE_DELETED = 'file_deleted';
    public const CHECKSUM_FILE_DETAILS = 'file_details';
    public const LOGS = 'logs';

    /* Toolbox order data  */
    public const ID = 'id';
    public const ORDERS = 'orders';
    public const ORDER_MARKETPLACE_SKU = 'marketplace_sku';
    public const ORDER_MARKETPLACE_NAME = 'marketplace_name';
    public const ORDER_MARKETPLACE_LABEL = 'marketplace_label';
    public const ORDER_MERCHANT_ORDER_ID = 'merchant_order_id';
    public const ORDER_MERCHANT_ORDER_REFERENCE = 'merchant_order_reference';
    public const ORDER_DELIVERY_ADDRESS_ID = 'delivery_address_id';
    public const ORDER_DELIVERY_COUNTRY_ISO = 'delivery_country_iso';
    public const ORDER_PROCESS_STATE = 'order_process_state';
    public const ORDER_STATUSES = 'order_statuses';
    public const ORDER_STATUS = 'order_status';
    public const ORDER_MERCHANT_ORDER_STATUS = 'merchant_order_status';
    public const ORDER_TOTAL_PAID = 'total_paid';
    public const ORDER_MERCHANT_TOTAL_PAID = 'merchant_total_paid';
    public const ORDER_COMMISSION = 'commission';
    public const ORDER_CURRENCY = 'currency';
    public const ORDER_DATE = 'order_date';
    public const ORDER_ITEMS = 'order_items';
    public const ORDER_IS_REIMPORTED = 'is_reimported';
    public const ORDER_IS_IN_ERROR = 'is_in_error';
    public const ORDER_ACTION_IN_PROGRESS = 'action_in_progress';
    public const CUSTOMER = 'customer';
    public const CUSTOMER_NAME = 'name';
    public const CUSTOMER_EMAIL = 'email';
    public const CUSTOMER_VAT_NUMBER = 'vat_number';
    public const ORDER_TYPES = 'order_types';
    public const ORDER_TYPE_EXPRESS = 'is_express';
    public const ORDER_TYPE_PRIME = 'is_prime';
    public const ORDER_TYPE_BUSINESS = 'is_business';
    public const ORDER_TYPE_DELIVERED_BY_MARKETPLACE = 'is_delivered_by_marketplace';
    public const TRACKING = 'tracking';
    public const TRACKING_CARRIER = 'carrier';
    public const TRACKING_METHOD = 'method';
    public const TRACKING_NUMBER = 'tracking_number';
    public const TRACKING_RELAY_ID = 'relay_id';
    public const TRACKING_DELIVERED_BY_MARKETPLACE = 'is_delivered_by_marketplace';
    public const TRACKING_MERCHANT_CARRIER = 'merchant_carrier';
    public const TRACKING_MERCHANT_TRACKING_NUMBER = 'merchant_tracking_number';
    public const TRACKING_MERCHANT_TRACKING_URL = 'merchant_tracking_url';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const IMPORTED_AT = 'imported_at';
    public const ERRORS = 'errors';
    public const ERROR_TYPE = 'type';
    public const ERROR_MESSAGE = 'message';
    public const ERROR_CODE = 'code';
    public const ERROR_FINISHED = 'is_finished';
    public const ERROR_REPORTED = 'is_reported';
    public const ACTIONS = 'actions';
    public const ACTION_ID = 'action_id';
    public const ACTION_PARAMETERS = 'parameters';
    public const ACTION_RETRY = 'retry';
    public const ACTION_FINISH = 'is_finished';
    public const EXTRA_UPDATED_AT = 'extra_updated_at';

    /* Process state labels */
    public const PROCESS_STATE_NEW = 'new';
    public const PROCESS_STATE_IMPORT = 'import';
    public const PROCESS_STATE_FINISH = 'finish';

    /* Error type labels */
    public const TYPE_ERROR_IMPORT = 'import';
    public const TYPE_ERROR_SEND = 'send';

    /* PHP extensions */
    public const PHP_EXTENSION_CURL = 'curl_version';
    public const PHP_EXTENSION_SIMPLEXML = 'simplexml_load_file';
    public const PHP_EXTENSION_JSON = 'json_decode';

    /* Toolbox files */
    public const FILE_CHECKMD5 = 'checkmd5.csv';
    public const FILE_TEST = 'test.txt';

    /**
     * @var array valid toolbox actions
     */
    public static $toolboxActions = [
        self::ACTION_DATA,
        self::ACTION_LOG,
        self::ACTION_ORDER,
    ];

    /**
     * Get all toolbox data
     *
     * @param string $type Toolbox data type
     *
     * @return array
     */
    public static function getData($type = self::DATA_TYPE_CMS)
    {
        switch ($type) {
            case self::DATA_TYPE_ALL:
                return self::getAllData();
            case self::DATA_TYPE_CHECKLIST:
                return self::getChecklistData();
            case self::DATA_TYPE_CHECKSUM:
                return self::getChecksumData();
            case self::DATA_TYPE_MODIFIED_FILES:
                $shortPathParam = (string) Tools::getValue(self::PARAM_SHORT_PATH);
                return self::getModifiedFilesData(base64_decode($shortPathParam));
            case self::DATA_TYPE_LOG:
                return self::getLogData();
            case self::DATA_TYPE_OPTION:
                return self::getOptionData();
            case self::DATA_TYPE_PLUGIN:
                return self::getPluginData();
            case self::DATA_TYPE_SHOP:
                return self::getShopData();
            case self::DATA_TYPE_SYNCHRONIZATION:
                return self::getSynchronizationData();
            default:
            case self::DATA_TYPE_CMS:
                return self::getCmsData();
        }
    }

    /**
     * Download log file individually or globally
     *
     * @param string|null $date name of file to download
     */
    public static function downloadLog($date = null)
    {
        LengowLog::download($date);
    }

    /**
     * Start order synchronization based on specific parameters
     *
     * @param array $params synchronization parameters
     *
     * @return array
     */
    public static function syncOrders($params = [])
    {
        // get all params for order synchronization
        $params = self::filterParamsForSync($params);
        $import = new LengowImport($params);
        $result = $import->exec();
        // if global error return error message and request http code
        if (isset($result[LengowImport::ERRORS][0])) {
            return self::generateErrorReturn(LengowConnector::CODE_403, $result[LengowImport::ERRORS][0]);
        }
        unset($result[LengowImport::ERRORS]);
        return $result;
    }

    /**
     * Get all order data from a marketplace reference
     *
     * @param string|null $marketplaceSku marketplace order reference
     * @param string|null $marketplaceName marketplace code
     * @param string $type Toolbox order data type
     *
     * @return array
     */
    public static function getOrderData($marketplaceSku = null, $marketplaceName = null, $type = self::DATA_TYPE_ORDER)
    {
        $lengowOrders = $marketplaceSku && $marketplaceName
            ? LengowOrder::getAllLengowOrders($marketplaceSku, $marketplaceName)
            : [];
        // if no reference is found, process is blocked
        if (empty($lengowOrders)) {
            return self::generateErrorReturn(
                LengowConnector::CODE_404,
                LengowMain::setLogMessage('log.import.unable_find_order')
            );
        }
        $orders = [];
        foreach ($lengowOrders as $data) {
            try {
                $lengowOrder = $data[LengowOrder::FIELD_ORDER_ID]
                    ? new LengowOrder((int) $data[LengowOrder::FIELD_ORDER_ID])
                    : null;
            } catch (Exception $e) {
                $lengowOrder = null;
            }
            if ($type === self::DATA_TYPE_EXTRA) {
                return self::getOrderExtraData($data, $lengowOrder);
            }
            $marketplaceLabel = $data[LengowOrder::FIELD_MARKETPLACE_LABEL];
            $orders[] = self::getOrderDataByType($type, $data, $lengowOrder);
            unset($lengowOrder);
        }
        return [
            self::ORDER_MARKETPLACE_SKU => $marketplaceSku,
            self::ORDER_MARKETPLACE_NAME => $marketplaceName,
            self::ORDER_MARKETPLACE_LABEL => isset($marketplaceLabel) ? $marketplaceLabel : null,
            self::ORDERS => $orders,
        ];
    }

    /**
     * Check if PHP Curl is activated
     *
     * @return bool
     */
    public static function isCurlActivated()
    {
        return function_exists(self::PHP_EXTENSION_CURL);
    }

    /**
     * Get all data
     *
     * @return array
     */
    private static function getAllData()
    {
        return [
            self::CHECKLIST => self::getChecklistData(),
            self::PLUGIN => self::getPluginData(),
            self::SYNCHRONIZATION => self::getSynchronizationData(),
            self::CMS_OPTIONS => LengowConfiguration::getAllValues(null, true),
            self::SHOPS => self::getShopData(),
            self::CHECKSUM => self::getChecksumData(),
            self::LOGS => self::getLogData(),
        ];
    }

    /**
     * Get cms data
     *
     * @return array
     */
    private static function getCmsData()
    {
        return [
            self::CHECKLIST => self::getChecklistData(),
            self::PLUGIN => self::getPluginData(),
            self::SYNCHRONIZATION => self::getSynchronizationData(),
            self::CMS_OPTIONS => LengowConfiguration::getAllValues(null, true),
        ];
    }

    /**
     * Get array of requirements
     *
     * @return array
     */
    private static function getChecklistData()
    {
        $checksumData = self::getChecksumData();
        return [
            self::CHECKLIST_CURL_ACTIVATED => self::isCurlActivated(),
            self::CHECKLIST_SIMPLE_XML_ACTIVATED => self::isSimpleXMLActivated(),
            self::CHECKLIST_JSON_ACTIVATED => self::isJsonActivated(),
            self::CHECKLIST_MD5_SUCCESS => $checksumData[self::CHECKSUM_SUCCESS],
        ];
    }

    /**
     * Get array of plugin data
     *
     * @return array
     */
    private static function getPluginData()
    {
        return [
            self::PLUGIN_CMS_VERSION => _PS_VERSION_,
            self::PLUGIN_VERSION => LengowConfiguration::getGlobalValue(LengowConfiguration::PLUGIN_VERSION),
            self::PLUGIN_PHP_VERSION => PHP_VERSION,
            self::PLUGIN_DEBUG_MODE_DISABLE => !LengowConfiguration::debugModeIsActive(),
            self::PLUGIN_WRITE_PERMISSION => self::testWritePermission(),
            self::PLUGIN_SERVER_IP => $_SERVER['SERVER_ADDR'],
            self::PLUGIN_AUTHORIZED_IP_ENABLE => (bool) LengowConfiguration::get(
                LengowConfiguration::AUTHORIZED_IP_ENABLED
            ),
            self::PLUGIN_AUTHORIZED_IPS => LengowConfiguration::getAuthorizedIps(),
            self::PLUGIN_TOOLBOX_URL => LengowMain::getToolboxUrl(),
        ];
    }

    /**
     * Get array of synchronization data
     *
     * @return array
     */
    private static function getSynchronizationData()
    {
        $lastImport = LengowMain::getLastImport();
        return [
            self::SYNCHRONIZATION_CMS_TOKEN => LengowMain::getToken(),
            self::SYNCHRONIZATION_CRON_URL => LengowMain::getCronUrl(),
            self::SYNCHRONIZATION_NUMBER_ORDERS_IMPORTED => LengowOrder::countOrderImportedByLengow(),
            self::SYNCHRONIZATION_NUMBER_ORDERS_WAITING_SHIPMENT => LengowOrder::countOrderToBeSent(),
            self::SYNCHRONIZATION_NUMBER_ORDERS_IN_ERROR => LengowOrder::countOrderWithError(),
            self::SYNCHRONIZATION_SYNCHRONIZATION_IN_PROGRESS => LengowImport::isInProcess(),
            self::SYNCHRONIZATION_LAST_SYNCHRONIZATION => $lastImport['type'] === 'none' ? 0 : $lastImport['timestamp'],
            self::SYNCHRONIZATION_LAST_SYNCHRONIZATION_TYPE => $lastImport['type'],
        ];
    }

    /**
     * Get array of export data
     *
     * @return array
     */
    private static function getShopData()
    {
        $exportData = [];
        $shops = LengowShop::getActiveShops();
        if (empty($shops)) {
            return $exportData;
        }
        foreach ($shops as $shop) {
            $idShop = $shop->id;
            $lengowExport = new LengowExport([LengowExport::PARAM_SHOP_ID => $idShop]);
            $lastExport = LengowConfiguration::get(LengowConfiguration::LAST_UPDATE_EXPORT, null, null, $idShop);
            $exportData[] = [
                self::SHOP_ID => $idShop,
                self::SHOP_NAME => $shop->name,
                self::SHOP_DOMAIN_URL => $shop->domain,
                self::SHOP_TOKEN => LengowMain::getToken($idShop),
                self::SHOP_FEED_URL => LengowMain::getExportUrl($idShop),
                self::SHOP_ENABLED => LengowConfiguration::shopIsActive($idShop),
                self::SHOP_CATALOG_IDS => LengowConfiguration::getCatalogIds($idShop),
                self::SHOP_NUMBER_PRODUCTS_AVAILABLE => $lengowExport->getTotalProduct(),
                self::SHOP_NUMBER_PRODUCTS_EXPORTED => $lengowExport->getTotalExportProduct(),
                self::SHOP_LAST_EXPORT => empty($lastExport) ? 0 : (int) $lastExport,
                self::SHOP_OPTIONS => LengowConfiguration::getAllValues($idShop, true),
            ];
        }
        return $exportData;
    }

    /**
     * Get array of export data
     *
     * @return array
     */
    private static function getOptionData()
    {
        $optionData = [
            self::CMS_OPTIONS => LengowConfiguration::getAllValues(),
            self::SHOP_OPTIONS => [],
        ];
        $shops = LengowShop::getActiveShops();
        foreach ($shops as $shop) {
            $optionData[self::SHOP_OPTIONS][] = LengowConfiguration::getAllValues($shop->id);
        }
        return $optionData;
    }

    /**
     * Get files checksum
     *
     * @return array
     */
    private static function getChecksumData()
    {
        $fileCounter = 0;
        $fileModified = [];
        $fileDeleted = [];
        $sep = DIRECTORY_SEPARATOR;
        $fileName = LengowMain::getLengowFolder() . $sep . LengowMain::FOLDER_CONFIG . $sep . self::FILE_CHECKMD5;
        if (file_exists($fileName)) {
            $md5Available = true;
            if (($file = fopen($fileName, 'rb')) !== false) {
                while (($data = fgetcsv($file, 1000, '|')) !== false) {
                    $fileCounter++;
                    $shortPath =  $data[0];
                    $filePath = LengowMain::getLengowFolder() . $data[0];
                    if (file_exists($filePath)) {
                        $fileMd = md5_file($filePath);
                        if ($fileMd !== $data[1]) {
                            $fileModified[] = $shortPath ;
                        }
                    } else {
                        $fileDeleted[] = $shortPath;
                    }
                }
                fclose($file);
            }
        } else {
            $md5Available = false;
        }
        $fileModifiedCounter = count($fileModified);
        $fileDeletedCounter = count($fileDeleted);
        $md5Success = $md5Available && !($fileModifiedCounter > 0) && !($fileDeletedCounter > 0);
        return [
            self::CHECKSUM_AVAILABLE => $md5Available,
            self::CHECKSUM_SUCCESS => $md5Success,
            self::CHECKSUM_NUMBER_FILES_CHECKED => $fileCounter,
            self::CHECKSUM_NUMBER_FILES_MODIFIED => $fileModifiedCounter,
            self::CHECKSUM_NUMBER_FILES_DELETED => $fileDeletedCounter,
            self::CHECKSUM_FILE_MODIFIED => $fileModified,
            self::CHECKSUM_FILE_DELETED => $fileDeleted,
            self::CHECKSUM_FILE_DETAILS => 1
        ];
    }

    /**
     * Get files modified details
     *
     * @string $shortPathParam the file short path
     *
     * @return array
     */
    private static function getModifiedFilesData($shortPathParam)
    {
        $fileCounter = 0;
        $fileModified = [];
        $fileDeleted = [];
        $sep = DIRECTORY_SEPARATOR;
        $fileName = LengowMain::getLengowFolder() . $sep . LengowMain::FOLDER_CONFIG . $sep . self::FILE_CHECKMD5;

        if (file_exists($fileName)) {
            $md5Available = true;
            if (($file = fopen($fileName, 'rb')) !== false) {
                while (($data = fgetcsv($file, 1000, '|')) !== false) {
                    $fileCounter++;
                    $shortPath =  $data[0];
                    $filePath = LengowMain::getLengowFolder() . $data[0];
                    if (file_exists($filePath)) {
                        $fileMd = md5_file($filePath);
                        if ($fileMd !== $data[1]) {
                            if ($shortPathParam && ($shortPathParam !== $shortPath)) {
                                continue;
                            }
                            $fileModified[] = [
                                'short_path' => $shortPath,
                                'content_encoded' => base64_encode(Tools::file_get_contents($filePath)),
                                'checksum' => $fileMd
                            ];
                        }
                    } else {
                        $fileDeleted[] = ['short_path' => $shortPath];
                    }
                }
                fclose($file);
            }
        } else {
            $md5Available = false;
        }

        return [
            self::CHECKSUM_FILE_MODIFIED => $fileModified,
            self::CHECKSUM_FILE_DELETED => $fileDeleted,
        ];
    }

    /**
     * Get all log files available
     *
     * @return array
     */
    private static function getLogData()
    {
        $logs = LengowLog::getPaths();
        if (!empty($logs)) {
            $logs[] = [
                LengowLog::LOG_DATE => null,
                LengowLog::LOG_LINK => LengowMain::getToolboxUrl()
                    . '&' . self::PARAM_TOOLBOX_ACTION . '=' . self::ACTION_LOG,
            ];
        }
        return $logs;
    }

    /**
     * Check if SimpleXML Extension is activated
     *
     * @return bool
     */
    private static function isSimpleXMLActivated()
    {
        return function_exists(self::PHP_EXTENSION_SIMPLEXML);
    }

    /**
     * Check if SimpleXML Extension is activated
     *
     * @return bool
     */
    private static function isJsonActivated()
    {
        return function_exists(self::PHP_EXTENSION_JSON);
    }

    /**
     * Test write permission for log and export in file
     *
     * @return bool
     */
    private static function testWritePermission()
    {
        $sep = DIRECTORY_SEPARATOR;
        $filePath = LengowMain::getLengowFolder() . $sep . LengowMain::FOLDER_CONFIG . $sep . self::FILE_TEST;
        try {
            $file = fopen($filePath, 'wb+');
            if (!$file) {
                return false;
            }
            unlink($filePath);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Filter parameters for order synchronization
     *
     * @param array $params synchronization params
     *
     * @return array
     */
    private static function filterParamsForSync($params = [])
    {
        $paramsFiltered = [LengowImport::PARAM_TYPE => LengowImport::TYPE_TOOLBOX];
        if (isset(
            $params[self::PARAM_MARKETPLACE_SKU],
            $params[self::PARAM_MARKETPLACE_NAME],
            $params[self::PARAM_SHOP_ID]
        )) {
            // get all parameters to synchronize a specific order
            $paramsFiltered[LengowImport::PARAM_MARKETPLACE_SKU] = $params[self::PARAM_MARKETPLACE_SKU];
            $paramsFiltered[LengowImport::PARAM_MARKETPLACE_NAME] = $params[self::PARAM_MARKETPLACE_NAME];
            $paramsFiltered[LengowImport::PARAM_SHOP_ID] = (int) $params[self::PARAM_SHOP_ID];
        } elseif (isset($params[self::PARAM_CREATED_FROM], $params[self::PARAM_CREATED_TO])) {
            // get all parameters to synchronize over a fixed period
            $paramsFiltered[LengowImport::PARAM_CREATED_FROM] = $params[self::PARAM_CREATED_FROM];
            $paramsFiltered[LengowImport::PARAM_CREATED_TO] = $params[self::PARAM_CREATED_TO];
        } elseif (isset($params[self::PARAM_DAYS])) {
            // get all parameters to synchronize over a time interval
            $paramsFiltered[LengowImport::PARAM_DAYS] = (int) $params[self::PARAM_DAYS];
        }
        // force order synchronization by removing pending errors
        if (isset($params[self::PARAM_FORCE])) {
            $paramsFiltered[LengowImport::PARAM_FORCE_SYNC] = (bool) $params[self::PARAM_FORCE];
        }
        return $paramsFiltered;
    }

    /**
     * Get array of all the data of the order
     *
     * @param string $type Toolbox order data type
     * @param array $data All Lengow order data
     * @param LengowOrder|null $lengowOrder Lengow order instance
     *
     * @return array
     */
    private static function getOrderDataByType($type, $data, $lengowOrder = null)
    {
        $orderReferences = [
            self::ID => (int) $data[LengowOrder::FIELD_ID],
            self::ORDER_MERCHANT_ORDER_ID  => $lengowOrder ? $lengowOrder->id : null,
            self::ORDER_MERCHANT_ORDER_REFERENCE  => $lengowOrder ? $lengowOrder->reference : null,
            self::ORDER_DELIVERY_ADDRESS_ID => (int) $data[LengowOrder::FIELD_DELIVERY_ADDRESS_ID],
        ];
        switch ($type) {
            case self::DATA_TYPE_ACTION:
                $orderData = [
                    self::ACTIONS => $lengowOrder ? self::getOrderActionData($lengowOrder->id) : [],
                ];
                break;
            case self::DATA_TYPE_ERROR:
                $orderData = [
                    self::ERRORS => self::getOrderErrorsData((int) $data[LengowOrder::FIELD_ID]),
                ];
                break;
            case self::DATA_TYPE_ORDER_STATUS:
                $orderData = [
                    self::ORDER_STATUSES => $lengowOrder ? self::getOrderStatusesData($lengowOrder) : [],
                ];
                break;
            case self::DATA_TYPE_ORDER:
            default:
                $orderData = self::getAllOrderData($data, $lengowOrder);
        }
        return array_merge($orderReferences, $orderData);
    }

    /**
     * Get array of all the data of the order
     *
     * @param array $data All Lengow order data
     * @param LengowOrder|null $lengowOrder Lengow order instance
     *
     * @return array
     */
    private static function getAllOrderData($data, $lengowOrder = null)
    {
        $orderTypes = json_decode($data[LengowOrder::FIELD_ORDER_TYPES], true);
        return [
            self::ORDER_DELIVERY_COUNTRY_ISO => $data[LengowOrder::FIELD_DELIVERY_COUNTRY_ISO],
            self::ORDER_PROCESS_STATE => self::getOrderProcessLabel(
                (int) $data[LengowOrder::FIELD_ORDER_PROCESS_STATE]
            ),
            self::ORDER_STATUS => $data[LengowOrder::FIELD_ORDER_LENGOW_STATE],
            self::ORDER_MERCHANT_ORDER_STATUS => $lengowOrder ? $lengowOrder->getCurrentStateName() : null,
            self::ORDER_STATUSES => $lengowOrder ? self::getOrderStatusesData($lengowOrder) : [],
            self::ORDER_TOTAL_PAID => (float) $data[LengowOrder::FIELD_TOTAL_PAID],
            self::ORDER_MERCHANT_TOTAL_PAID => $lengowOrder ? (float) $lengowOrder->total_paid : null,
            self::ORDER_COMMISSION => (float) $data[LengowOrder::FIELD_COMMISSION],
            self::ORDER_CURRENCY => $data[LengowOrder::FIELD_CURRENCY],
            self::CUSTOMER => [
                self::CUSTOMER_NAME => !empty($data[LengowOrder::FIELD_CUSTOMER_NAME])
                    ? $data[LengowOrder::FIELD_CUSTOMER_NAME]
                    : null,
                self::CUSTOMER_EMAIL => !empty($data[LengowOrder::FIELD_CUSTOMER_EMAIL])
                    ? $data[LengowOrder::FIELD_CUSTOMER_EMAIL]
                    : null,
                self::CUSTOMER_VAT_NUMBER => !empty($data[LengowOrder::FIELD_CUSTOMER_VAT_NUMBER])
                    ? $data[LengowOrder::FIELD_CUSTOMER_VAT_NUMBER]
                    : null,
            ],
            self::ORDER_DATE => strtotime($data[LengowOrder::FIELD_ORDER_DATE]),
            self::ORDER_TYPES => [
                self::ORDER_TYPE_EXPRESS => isset($orderTypes[LengowOrder::TYPE_EXPRESS]),
                self::ORDER_TYPE_PRIME => isset($orderTypes[LengowOrder::TYPE_PRIME]),
                self::ORDER_TYPE_BUSINESS => isset($orderTypes[LengowOrder::TYPE_BUSINESS]),
                self::ORDER_TYPE_DELIVERED_BY_MARKETPLACE => isset(
                    $orderTypes[LengowOrder::TYPE_DELIVERED_BY_MARKETPLACE]
                ),
            ],
            self::ORDER_ITEMS => (int) $data[LengowOrder::FIELD_ORDER_ITEM],
            self::TRACKING => [
                self::TRACKING_CARRIER => !empty($data[LengowOrder::FIELD_CARRIER])
                    ? $data[LengowOrder::FIELD_CARRIER]
                    : null,
                self::TRACKING_METHOD => !empty($data[LengowOrder::FIELD_CARRIER_METHOD])
                    ? $data[LengowOrder::FIELD_CARRIER_METHOD]
                    : null,
                self::TRACKING_NUMBER => !empty($data[LengowOrder::FIELD_CARRIER_TRACKING])
                    ? $data[LengowOrder::FIELD_CARRIER_TRACKING]
                    : null,
                self::TRACKING_RELAY_ID => !empty($data[LengowOrder::FIELD_CARRIER_RELAY_ID])
                    ? $data[LengowOrder::FIELD_CARRIER_RELAY_ID]
                    : null,
                self::TRACKING_MERCHANT_CARRIER => $lengowOrder ? $lengowOrder->getCurrentCarrierName() : null,
                self::TRACKING_MERCHANT_TRACKING_NUMBER => $lengowOrder
                    ? $lengowOrder->getCurrentTrackingNumber()
                    : null,
                self::TRACKING_MERCHANT_TRACKING_URL => $lengowOrder ? $lengowOrder->getCurrentTrackingUrl() : null,
            ],
            self::ORDER_IS_REIMPORTED => (bool) $data[LengowOrder::FIELD_IS_REIMPORTED],
            self::ORDER_IS_IN_ERROR => LengowOrderError::lengowOrderIsInError((int) $data[LengowOrder::FIELD_ID]),
            self::ERRORS => self::getOrderErrorsData((int) $data[LengowOrder::FIELD_ID]),
            self::ORDER_ACTION_IN_PROGRESS => $lengowOrder && $lengowOrder->hasAnActionInProgress(),
            self::ACTIONS => $lengowOrder ? self::getOrderActionData($lengowOrder->id) : [],
            self::CREATED_AT => strtotime($data[LengowOrder::FIELD_CREATED_AT]),
            self::UPDATED_AT => strtotime($data[LengowOrder::FIELD_CREATED_AT]),
            self::IMPORTED_AT => $lengowOrder ? strtotime($lengowOrder->date_add) : 0,
        ];
    }

    /**
     * Get array of all the errors of a Lengow order
     *
     * @param int $idOrderLengow Lengow order id
     *
     * @return array
     */
    private static function getOrderErrorsData($idOrderLengow)
    {
        $orderErrors = [];
        $errors = LengowOrderError::getOrderLogs($idOrderLengow);
        if ($errors) {
            foreach ($errors as $error) {
                $type = (int) $error[LengowOrderError::FIELD_TYPE];
                $orderErrors[] = [
                    self::ID => (int) $error[LengowOrderError::FIELD_ID],
                    self::ERROR_TYPE => $type === LengowOrderError::TYPE_ERROR_IMPORT
                        ? self::TYPE_ERROR_IMPORT
                        : self::TYPE_ERROR_SEND,
                    self::ERROR_MESSAGE => LengowMain::decodeLogMessage(
                        $error[LengowOrderError::FIELD_MESSAGE],
                        LengowTranslation::DEFAULT_ISO_CODE
                    ),
                    self::ERROR_FINISHED => (bool) $error[LengowOrderError::FIELD_IS_FINISHED],
                    self::ERROR_REPORTED => (bool) $error[LengowOrderError::FIELD_MAIL],
                    self::CREATED_AT => strtotime($error[LengowOrderError::FIELD_CREATED_AT]),
                    self::UPDATED_AT => strtotime($error[LengowOrderError::FIELD_CREATED_AT]),
                ];
            }
        }
        return $orderErrors;
    }

    /**
     * Get array of all the actions of a Lengow order
     *
     * @param int $idOrder PrestaShop order id
     *
     * @return array
     */
    private static function getOrderActionData($idOrder)
    {
        $orderActions = [];
        $actions = LengowAction::getActionsByOrderId($idOrder);
        if ($actions) {
            /** @var LengowAction[] $actions */
            foreach ($actions as $action) {
                $orderActions[] = [
                    self::ID => $action->id,
                    self::ACTION_ID => $action->actionId,
                    self::ACTION_PARAMETERS => json_decode($action->parameters, true),
                    self::ACTION_RETRY => $action->retry,
                    self::ACTION_FINISH => $action->state === LengowAction::STATE_FINISH,
                    self::CREATED_AT => strtotime($action->createdAt),
                    self::UPDATED_AT => $action->updatedAt ? strtotime($action->updatedAt) : 0,
                ];
            }
        }
        return $orderActions;
    }

    /**
     * Get array of all the statuses of an order
     *
     * @param LengowOrder $lengowOrder Lengow order instance
     *
     * @return array
     */
    private static function getOrderStatusesData($lengowOrder)
    {
        $orderStatuses = [];
        $idLang = Language::getIdByIso(LengowTranslation::ISO_CODE_EN);
        $idLang = $idLang ?: (int) Configuration::get('PS_LANG_DEFAULT');
        $statuses = $lengowOrder->getHistory($idLang);
        foreach ($statuses as $status) {
            $orderStatuses[] = [
                self::ORDER_MERCHANT_ORDER_STATUS => $status['ostate_name'],
                self::ORDER_STATUS => self::getOrderStatusCorrespondence((int) $status['id_order_state']),
                self::CREATED_AT => strtotime($status['date_add']),
            ];
        }
        return $orderStatuses;
    }

    /**
     * Get all the data of the order at the time of import
     *
     * @param array $data All Lengow order data
     * @param LengowOrder|null $lengowOrder Lengow order instance
     *
     * @return array
     */
    private static function getOrderExtraData($data, $lengowOrder = null)
    {
        $orderData = json_decode($data[LengowOrder::FIELD_EXTRA], true);
        $orderData[self::EXTRA_UPDATED_AT] = $lengowOrder
            ? strtotime($lengowOrder->date_add)
            : strtotime($data[LengowOrder::FIELD_CREATED_AT]);
        return $orderData;
    }

    /**
     * Get order process label
     *
     * @param int $orderProcess Lengow order process (new, import or finish)
     *
     * @return string
     */
    private static function getOrderProcessLabel($orderProcess)
    {
        switch ($orderProcess) {
            case LengowOrder::PROCESS_STATE_NEW:
                return self::PROCESS_STATE_NEW;
            case LengowOrder::PROCESS_STATE_IMPORT:
                return self::PROCESS_STATE_IMPORT;
            case LengowOrder::PROCESS_STATE_FINISH:
            default:
                return self::PROCESS_STATE_FINISH;
        }
    }

    /**
     * Retrieves the correspondence between the PrestaShop and Lengow status
     *
     * @param int $idOrderState PrestaShop order state id
     *
     * @return string|null
     */
    private static function getOrderStatusCorrespondence($idOrderState)
    {
        $idStatusWaitingShipment = LengowMain::getOrderState(LengowOrder::STATE_WAITING_SHIPMENT);
        $idStatusShipped = LengowMain::getOrderState(LengowOrder::STATE_SHIPPED);
        $idStatusCanceled = LengowMain::getOrderState(LengowOrder::STATE_CANCELED);
        switch ($idOrderState) {
            case $idStatusWaitingShipment:
                return LengowOrder::STATE_WAITING_SHIPMENT;
            case $idStatusShipped:
                return LengowOrder::STATE_SHIPPED;
            case $idStatusCanceled:
                return LengowOrder::STATE_CANCELED;
            default:
                return null;
        }
    }

    /**
     * Generates an error return for the Toolbox webservice
     *
     * @param int $httpCode request http code
     * @param string $error error message
     *
     * @return array
     */
    private static function generateErrorReturn($httpCode, $error)
    {
        return [
            self::ERRORS => [
                self::ERROR_MESSAGE => LengowMain::decodeLogMessage($error, LengowTranslation::DEFAULT_ISO_CODE),
                self::ERROR_CODE => $httpCode,
            ],
        ];
    }
}
