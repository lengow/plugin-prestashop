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
 * string  $marketplace_sku    Lengow marketplace order id to import
 * string  marketplace_name    Lengow marketplace name to import
 * integer delivery_address_id Lengow delivery address id to import
 * boolean force_product       Force import product when quantity is insufficient (1) or not (0)
 * boolean preprod_mode        Activate preprod mode
 * boolean log_output          See logs (1) or not (0)
 * boolean get_sync            See synchronisation parameters in json format (1) or not (0)
 */

@set_time_limit(0);

$currentDirectory = str_replace('modules/lengow/webservice/', '', dirname($_SERVER['SCRIPT_FILENAME']) . "/");
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
// get all shop informations
if (Tools::getIsset('get_sync') && Tools::getValue('get_sync') == 1) {
    echo Tools::jsonEncode(LengowSync::getSyncData());
} else {
    // get sync action if exists
    $sync = false;
    if (Tools::getIsset('sync')) {
        if (Tools::strlen((string)Tools::getValue('sync')) > 0) {
            $sync = (string)Tools::getValue('sync');
        }
    }
    $force = false;
    if (Tools::getIsset('force')) {
        $force = (bool)Tools::getValue('force');
    }
    // sync catalogs id between Lengow and Prestashop
    if (!$sync || $sync === 'catalog') {
        LengowSync::syncCatalog();
    }
    // sync marketplace and marketplace carrier between Lengow and Prestashop
    if (!$sync || $sync === 'carrier') {
        LengowSync::syncCarrier($force);
    }
    // sync orders between Lengow and Prestashop
    if (!$sync || $sync === 'order') {
        // array of params for import order
        $params = array();
        // check if the GET parameters are available
        if (Tools::getIsset('force_product')) {
            $params['force_product'] = (bool)Tools::getValue('force_product');
        }
        if (Tools::getIsset('preprod_mode')) {
            $params['preprod_mode'] = (bool)Tools::getValue('preprod_mode');
        }
        if (Tools::getIsset('log_output')) {
            $params['log_output'] = (bool)Tools::getValue('log_output');
        }
        if (Tools::getIsset('days') && is_numeric(Tools::getValue('days'))) {
            $params['days'] = (int)Tools::getValue('days');
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
        $params['type'] = 'cron';
        // import orders
        $import = new LengowImport($params);
        $import->exec();
    }
    // sync actions between Lengow and Prestashop
    if (!$sync || $sync === 'action') {
        LengowAction::checkFinishAction();
        LengowAction::checkOldAction();
        LengowAction::checkActionNotSent();
    }
    // sync options between Lengow and Prestashop
    if (!$sync || $sync === 'cms_option') {
        LengowSync::setCmsOption($force);
    }
    // sync status account between Lengow and Prestashop
    if ($sync === 'status_account') {
        LengowSync::getStatusAccount($force);
    }
    // sync statistics between Lengow and Prestashop
    if ($sync === 'statistic') {
        LengowSync::getStatistic($force);
    }
    // sync option is not valid
    if ($sync && !in_array($sync, LengowSync::$syncActions)) {
        header('HTTP/1.1 400 Bad Request');
        die('Action: ' . $sync . ' is not a valid action');
    }
}
