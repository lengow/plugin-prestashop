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
 * List params
 * string  sync                Data type to synchronize
 * integer days                Synchronization interval time
 * integer limit               Maximum number of new orders created
 * integer shop_id             Shop id to import
 * string  marketplace_sku     Lengow marketplace order id to synchronize
 * string  marketplace_name    Lengow marketplace name to synchronize
 * string  created_from        Synchronization of orders since
 * string  created_to          Synchronization of orders until
 * integer delivery_address_id Lengow delivery address id to synchronize
 * boolean force_product       Force import product when quantity is insufficient (1) or not (0)
 * boolean force_sync          Force synchronization order even if there are errors (1) or not (0)
 * boolean debug_mode          Activate debug mode (1) or not (0)
 * boolean log_output          Display log messages (1) or not (0)
 * boolean get_sync            See synchronization parameters in json format (1) or not (0)
 */
@set_time_limit(0);
@ini_set('memory_limit', '1024M');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
$currentDirectory = str_replace('modules/lengow/webservice/', '', dirname($_SERVER['SCRIPT_FILENAME']) . '/');
$sep = DIRECTORY_SEPARATOR;
require_once $currentDirectory . 'config' . $sep . 'config.inc.php';
if (!defined('_PS_VERSION_')) {
    exit;
}
Configuration::set('PS_SHOP_ENABLE', true);
require_once $currentDirectory . 'init.php';
require_once $currentDirectory . 'modules' . $sep . 'lengow' . $sep . 'lengow.php';

LengowLog::registerShutdownFunction();
// check if Lengow is installed and enabled
$lengow = new Lengow();
if (!Module::isInstalled($lengow->name)) {
    $errorMessage = (!Module::isEnabled($lengow->name))
        ? 'Lengow module is not active'
        : 'Lengow module is not installed';
    header('HTTP/1.1 400 Bad Request');
    exit($errorMessage);
}
// check IP access and Token
$token = Tools::getIsset(LengowImport::PARAM_TOKEN) ? Tools::getValue(LengowImport::PARAM_TOKEN) : '';
if (!LengowMain::checkWebservicesAccess($token)) {
    if ((bool) LengowConfiguration::get(LengowConfiguration::AUTHORIZED_IP_ENABLED)) {
        $errorMessage = 'Unauthorized access for IP: ' . $_SERVER['REMOTE_ADDR'];
    } else {
        $errorMessage = $token !== ''
            ? 'Unauthorised access for this token: ' . $token
            : 'Unauthorised access: token parameter is empty';
    }
    header('HTTP/1.1 403 Forbidden');
    exit($errorMessage);
}

if (Tools::getIsset(LengowImport::PARAM_GET_SYNC) && Tools::getValue(LengowImport::PARAM_GET_SYNC) == 1) {
    echo json_encode(LengowSync::getSyncData());
} else {
    $force = Tools::getIsset(LengowImport::PARAM_FORCE) ? (bool) Tools::getValue(LengowImport::PARAM_FORCE) : false;
    $logOutput = Tools::getIsset(LengowImport::PARAM_LOG_OUTPUT)
        ? (bool) Tools::getValue(LengowImport::PARAM_LOG_OUTPUT)
        : false;
    // get sync action if exists
    $sync = Tools::getIsset(LengowImport::PARAM_SYNC) ? Tools::getValue(LengowImport::PARAM_SYNC) : false;
    // sync catalogs id between Lengow and PrestaShop
    if (!$sync || $sync === LengowSync::SYNC_CATALOG) {
        LengowSync::syncCatalog($force, $logOutput);
    }
    // sync marketplace and marketplace carrier between Lengow and PrestaShop
    if (!$sync || $sync === LengowSync::SYNC_CARRIER) {
        LengowSync::syncCarrier($force, $logOutput);
    }
    // sync orders between Lengow and PrestaShop
    if (!$sync || $sync === LengowSync::SYNC_ORDER) {
        // array of params for import order
        $params = [
            LengowImport::PARAM_TYPE => LengowImport::TYPE_CRON,
            LengowImport::PARAM_LOG_OUTPUT => $logOutput,
        ];
        // check if the GET parameters are available
        if (Tools::getIsset(LengowImport::PARAM_FORCE_PRODUCT)) {
            $params[LengowImport::PARAM_FORCE_PRODUCT] = (bool) Tools::getValue(LengowImport::PARAM_FORCE_PRODUCT);
        }
        if (Tools::getIsset(LengowImport::PARAM_FORCE_SYNC)) {
            $params[LengowImport::PARAM_FORCE_SYNC] = (bool) Tools::getValue(LengowImport::PARAM_FORCE_SYNC);
        }
        if (Tools::getIsset(LengowImport::PARAM_DEBUG_MODE)) {
            $params[LengowImport::PARAM_DEBUG_MODE] = (bool) Tools::getValue(LengowImport::PARAM_DEBUG_MODE);
        }
        if (Tools::getIsset(LengowImport::PARAM_MINUTES) && is_numeric(Tools::getValue(LengowImport::PARAM_MINUTES))) {
            $params[LengowImport::PARAM_MINUTES] = (float) Tools::getValue(LengowImport::PARAM_MINUTES);
        }
        if (Tools::getIsset(LengowImport::PARAM_DAYS) && is_numeric(Tools::getValue(LengowImport::PARAM_DAYS))) {
            $params[LengowImport::PARAM_DAYS] = (float) Tools::getValue(LengowImport::PARAM_DAYS);
        }
        if (Tools::getIsset(LengowImport::PARAM_CREATED_FROM)) {
            $params[LengowImport::PARAM_CREATED_FROM] = Tools::getValue(LengowImport::PARAM_CREATED_FROM);
        }
        if (Tools::getIsset(LengowImport::PARAM_CREATED_TO)) {
            $params[LengowImport::PARAM_CREATED_TO] = Tools::getValue(LengowImport::PARAM_CREATED_TO);
        }
        if (Tools::getIsset(LengowImport::PARAM_LIMIT) && is_numeric(Tools::getValue(LengowImport::PARAM_LIMIT))) {
            $params[LengowImport::PARAM_LIMIT] = (int) Tools::getValue(LengowImport::PARAM_LIMIT);
        }
        if (Tools::getIsset(LengowImport::PARAM_MARKETPLACE_SKU)) {
            $params[LengowImport::PARAM_MARKETPLACE_SKU] = Tools::getValue(LengowImport::PARAM_MARKETPLACE_SKU);
        }
        if (Tools::getIsset(LengowImport::PARAM_MARKETPLACE_NAME)) {
            $params[LengowImport::PARAM_MARKETPLACE_NAME] = Tools::getValue(LengowImport::PARAM_MARKETPLACE_NAME);
        }
        if (Tools::getIsset(LengowImport::PARAM_DELIVERY_ADDRESS_ID)) {
            $params[LengowImport::PARAM_DELIVERY_ADDRESS_ID] = (int) Tools::getValue(
                LengowImport::PARAM_DELIVERY_ADDRESS_ID
            );
        }
        if (Tools::getIsset(LengowImport::PARAM_SHOP_ID) && is_numeric(Tools::getValue(LengowImport::PARAM_SHOP_ID))) {
            $params[LengowImport::PARAM_SHOP_ID] = (int) Tools::getValue(LengowImport::PARAM_SHOP_ID);
        }
        // import orders
        $import = new LengowImport($params);
        $import->exec();
    }
    // sync actions between Lengow and PrestaShop
    if (!$sync || $sync === LengowSync::SYNC_ACTION) {
        LengowAction::checkFinishAction($logOutput);
        LengowAction::checkOldAction($logOutput);
        LengowAction::checkActionNotSent($logOutput);
    }
    // sync options between Lengow and PrestaShop
    if (!$sync || $sync === LengowSync::SYNC_CMS_OPTION) {
        LengowSync::setCmsOption($force, $logOutput);
    }
    // sync marketplaces between Lengow and PrestaShop
    if ($sync === LengowSync::SYNC_MARKETPLACE) {
        LengowSync::getMarketplaces($force, $logOutput);
    }
    // sync status account between Lengow and PrestaShop
    if ($sync === LengowSync::SYNC_STATUS_ACCOUNT) {
        LengowSync::getStatusAccount($force, $logOutput);
    }
    // sync plugin data between Lengow and PrestaShop
    if ($sync === LengowSync::SYNC_PLUGIN_DATA) {
        LengowSync::getPluginData($force, $logOutput);
    }
    // sync option is not valid
    if ($sync && !in_array($sync, LengowSync::$syncActions, true)) {
        header('HTTP/1.1 400 Bad Request');
        exit('Action: ' . $sync . ' is not a valid action');
    }
}
