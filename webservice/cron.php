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
    if (_PS_VERSION_ >= 1.5 && !Module::isEnabled($lengow->name)) {
        header('HTTP/1.1 400 Bad Request');
        die('Lengow module is not active');
    }
    header('HTTP/1.1 400 Bad Request');
    die('Lengow module is not installed');
}
// check IP access and Token
$token = Tools::getIsset('token') ? Tools::getValue('token') : '';
if (!LengowMain::checkWebservicesAccess($token)) {
    if (Tools::strlen($token) > 0) {
        header('HTTP/1.1 403 Forbidden');
        die('Unauthorized access for this token : ' . $token);
    } else {
        header('HTTP/1.1 403 Forbidden');
        die('Unauthorized access for IP : ' . $_SERVER['REMOTE_ADDR']);
    }
}

if (Tools::getIsset('get_sync') && Tools::getValue('get_sync') == 1) {
    echo Tools::jsonEncode(LengowSync::getSyncData());
} else {
    $force = Tools::getIsset('force') ? (bool)Tools::getValue('force') : false;
    $logOutput = Tools::getIsset('log_output') ? (bool)Tools::getValue('log_output') : false;
    // get sync action if exists
    $sync = Tools::getIsset('sync') ? Tools::getValue('sync') : false;
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
            'type' => LengowImport::TYPE_CRON,
            'log_output' => $logOutput,
        );
        // check if the GET parameters are available
        if (Tools::getIsset('force_product')) {
            $params['force_product'] = (bool)Tools::getValue('force_product');
        }
        if (Tools::getIsset('debug_mode')) {
            $params['debug_mode'] = (bool)Tools::getValue('debug_mode');
        }
        if (Tools::getIsset('days') && is_numeric(Tools::getValue('days'))) {
            $params['days'] = (int)Tools::getValue('days');
        }
        if (Tools::getIsset('created_from')) {
            $params['created_from'] = Tools::getValue('created_from');
        }
        if (Tools::getIsset('created_to')) {
            $params['created_to'] = Tools::getValue('created_to');
        }
        if (Tools::getIsset('limit') && is_numeric(Tools::getValue('limit'))) {
            $params['limit'] = (int)Tools::getValue('limit');
        }
        if (Tools::getIsset('marketplace_sku')) {
            $params['marketplace_sku'] = (string)Tools::getValue('marketplace_sku');
        }
        if (Tools::getIsset('marketplace_name')) {
            $params['marketplace_name'] = (string)Tools::getValue('marketplace_name');
        }
        if (Tools::getIsset('delivery_address_id')) {
            $params['delivery_address_id'] = (int)Tools::getValue('delivery_address_id');
        }
        if (Tools::getIsset('shop_id') && is_numeric(Tools::getValue('shop_id'))) {
            $params['shop_id'] = (int)Tools::getValue('shop_id');
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
    if ($sync && !in_array($sync, LengowSync::$syncActions)) {
        header('HTTP/1.1 400 Bad Request');
        die('Action: ' . $sync . ' is not a valid action');
    }
}
