<?php
/**
 * Copyright 2016 Lengow SAS.
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
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

@set_time_limit(0);
$sep = DIRECTORY_SEPARATOR;

$currentDirectory = str_replace('modules/lengow/webservice/', '', dirname($_SERVER['SCRIPT_FILENAME']) . "/");
$sep = DIRECTORY_SEPARATOR;
require_once $currentDirectory.'config'.$sep.'config.inc.php';
require_once $currentDirectory.'init.php';
require_once $currentDirectory.'modules'.$sep.'lengow'.$sep.'lengow.php';

/* check if Lengow is installed and enabled */
$lengow = new Lengow();
if (!Module::isInstalled($lengow->name)) {
    if (_PS_VERSION_ >= 1.5 && !Module::isEnabled($lengow->name)) {
        die('Lengow module is not active');
    }
    die('Lengow module is not installed');
}

// CheckIP and Token
$token = Tools::getIsset('token') ? Tools::getValue('token') : '';
if (!LengowMain::checkWebservicesAccess($token)) {
    if (strlen($token) > 0) {
        die('Unauthorized access for this token : ' . $token);
    } else {
        die('Unauthorized access for IP : ' . $_SERVER['REMOTE_ADDR']);
    }
}

$force_product = (bool)Configuration::get('LENGOW_IMPORT_FORCE_PRODUCT');
if (Tools::getIsset('forceProduct')) {
     $force_product = (bool)Tools::getValue('forceProduct');
}

/* check if debug is active in module config */
$debug = (bool)Configuration::get('LENGOW_DEBUG');
/* check if debug param is passed in URL */
if (Tools::getIsset('lengow_debug')) {
    $debug = (bool)Tools::getValue('lengow_debug');
}

/* get start and end dates of import */
$days = (int)Configuration::get('LENGOW_IMPORT_DAYS');
if (Tools::getIsset('days') && is_numeric(Tools::getValue('days'))) {
    $days = (int)Tools::getValue('days');
}
$date_from = date('c', strtotime(date('Y-m-d').' -'.$days.'days'));
$date_to = date('c');

$limit = 0;
if (Configuration::get('LENGOW_IMPORT_SINGLE')) {
    $limit = 1;
} elseif (Tools::getIsset('limit')) {
    $limit = (int)Tools::getValue('limit');
}

$result_new = 0;
$result_update = 0;

if (Tools::getIsset('idOrder') && Tools::getIsset('marketplace') && Tools::getIsset('shop')) {
    $import = new LengowImport(
        Tools::getValue('idOrder'),
        Tools::getValue('marketplace'),
        Tools::getValue('shop'),
        true,
        $debug
    );
    $result = $import->exec();
} else {
    if (_PS_VERSION_ < '1.5') {
        $shops = array('id_shop' => 1);
    } else {
        $sql = 'SELECT id_shop FROM '._DB_PREFIX_.'shop WHERE active = 1';
        $shops = Db::getInstance()->ExecuteS($sql);
    }
    foreach ($shops as $shop) {
        $import = new LengowImport(
            null,
            null,
            $shop['id_shop'],
            $force_product,
            $debug,
            $date_from,
            $date_to,
            $limit,
            true
        );
        $result = $import->exec();
        $result_new += $result['new'];
        $result_update += $result['update'];
    }
    if ($result_new > 0) {
        LengowMain::log($result_new.' order'.($result_new > 1 ? 's ' : ' ').'imported', true);
    }
    if ($result_update > 0) {
        LengowMain::log($result_update.' order'.($result_update > 1 ? 's ' : ' ').'updated', true);
    }
    if ($result_new == 0 && $result_update == 0) {
        LengowMain::log('No order available to import', true);
    }
}
