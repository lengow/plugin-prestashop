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
 * List params
 * string  sync                Number of products exported
 * integer days                Import period
 * integer limit               Number of orders to import
 * integer shop_id             Shop id to import
 * string  marketplace_sku     Lengow marketplace order id to import
 * string  marketplace_name    Lengow marketplace name to import
 * string  created_from        import of orders since
 * string  created_to          import of orders until
 * integer delivery_address_id Lengow delivery address id to import
 * boolean force_product       Force import product when quantity is insufficient (1) or not (0)
 * boolean debug_mode          Activate debug mode
 * boolean log_output          See logs (1) or not (0)
 * boolean get_sync            See synchronisation parameters in json format (1) or not (0)
 */

@set_time_limit(0);

$currentDirectory = str_replace('modules/lengow/webservice/', '', dirname($_SERVER['SCRIPT_FILENAME']) . '/');
$sep = DIRECTORY_SEPARATOR;
require_once $currentDirectory . 'config' . $sep . 'config.inc.php';
Configuration::set('PS_SHOP_ENABLE', true);
require_once $currentDirectory . 'init.php';
require_once $currentDirectory . 'modules' . $sep . 'lengow' . $sep . 'lengow.php';
// check if Lengow is installed and enabled
$lengow = new Lengow();
if (!Module::isInstalled($lengow->name)) {
    $errorMessage = (_PS_VERSION_ >= 1.5 && !Module::isEnabled($lengow->name))
        ? 'Lengow module is not active'
        : 'Lengow module is not installed';
    header('HTTP/1.1 400 Bad Request');
    die($errorMessage);
}
// check IP access and Token
$token = Tools::getIsset(LengowImport::PARAM_TOKEN) ? Tools::getValue(LengowImport::PARAM_TOKEN) : '';
if (!LengowMain::checkWebservicesAccess($token)) {
    if ($token === '' || (bool) LengowConfiguration::get(LengowConfiguration::AUTHORIZED_IP_ENABLED)) {
        $errorMessage = 'Unauthorized access for IP: ' . $_SERVER['REMOTE_ADDR'];
    } else {
        $errorMessage = 'Unauthorized access for this token : ' . $token;
    }
    header('HTTP/1.1 403 Forbidden');
    die($errorMessage);
}

if (Tools::getIsset(LengowImport::PARAM_GET_SYNC) && Tools::getValue(LengowImport::PARAM_GET_SYNC) == 1) {
    echo Tools::jsonEncode(LengowSync::getSyncData());
} else {
    $force = Tools::getIsset(LengowImport::PARAM_FORCE) ? (bool) Tools::getValue(LengowImport::PARAM_FORCE) : false;
    $logOutput = Tools::getIsset(LengowImport::PARAM_LOG_OUTPUT)
        ? (bool) Tools::getValue(LengowImport::PARAM_LOG_OUTPUT)
        : false;
    // get sync action if exists
    $sync = Tools::getIsset(LengowImport::PARAM_SYNC) ? Tools::getValue(LengowImport::PARAM_SYNC) : false;
    // sync catalogs id between Lengow and Prestashop
    if (!$sync || $sync === LengowSync::SYNC_CATALOG) {
        LengowSync::syncCatalog($force, $logOutput);
    }
    // sync marketplace and marketplace carrier between Lengow and Prestashop
    if (!$sync || $sync === LengowSync::SYNC_CARRIER) {
        LengowSync::syncCarrier($force, $logOutput);
    }
    // sync orders between Lengow and Prestashop
    if (!$sync || $sync === LengowSync::SYNC_ORDER) {
        // array of params for import order
        $params = array(
            LengowImport::PARAM_TYPE => LengowImport::TYPE_CRON,
            LengowImport::PARAM_LOG_OUTPUT => $logOutput,
        );
        // check if the GET parameters are available
        if (Tools::getIsset(LengowImport::PARAM_FORCE_PRODUCT)) {
            $params[LengowImport::PARAM_FORCE_PRODUCT] = (bool) Tools::getValue(LengowImport::PARAM_FORCE_PRODUCT);
        }
        if (Tools::getIsset(LengowImport::PARAM_DEBUG_MODE)) {
            $params[LengowImport::PARAM_DEBUG_MODE] = (bool) Tools::getValue(LengowImport::PARAM_DEBUG_MODE);
        }
        if (Tools::getIsset(LengowImport::PARAM_DAYS) && is_numeric(Tools::getValue(LengowImport::PARAM_DAYS))) {
            $params[LengowImport::PARAM_DAYS] = (int) Tools::getValue(LengowImport::PARAM_DAYS);
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
    // sync actions between Lengow and Prestashop
    if (!$sync || $sync === LengowSync::SYNC_ACTION) {
        LengowAction::checkFinishAction($logOutput);
        LengowAction::checkOldAction($logOutput);
        LengowAction::checkActionNotSent($logOutput);
    }
    // sync options between Lengow and Prestashop
    if (!$sync || $sync === LengowSync::SYNC_CMS_OPTION) {
        LengowSync::setCmsOption($force, $logOutput);
    }
    // sync marketplaces between Lengow and Prestashop
    if ($sync === LengowSync::SYNC_MARKETPLACE) {
        LengowSync::getMarketplaces($force, $logOutput);
    }
    // sync status account between Lengow and Prestashop
    if ($sync === LengowSync::SYNC_STATUS_ACCOUNT) {
        LengowSync::getStatusAccount($force, $logOutput);
    }
    // sync plugin data between Lengow and Prestashop
    if ($sync === LengowSync::SYNC_PLUGIN_DATA) {
        LengowSync::getPluginData($force, $logOutput);
    }
    // sync option is not valid
    if ($sync && !in_array($sync, LengowSync::$syncActions, true)) {
        header('HTTP/1.1 400 Bad Request');
        die('Action: ' . $sync . ' is not a valid action');
    }
}
