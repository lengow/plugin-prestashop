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

// check if Lengow is installed and enabled
$lengow = new Lengow();
if (!Module::isInstalled($lengow->name)) {
    if (_PS_VERSION_ >= 1.5 && !Module::isEnabled($lengow->name)) {
        die('Lengow module is not active');
    }
    die('Lengow module is not installed');
}
// check IP access and Token
$token = Tools::getIsset('token') ? Tools::getValue('token') : '';
if (!LengowMain::checkWebservicesAccess($token)) {
    if (strlen($token) > 0) {
        die('Unauthorized access for this token : ' . $token);
    } else {
        die('Unauthorized access for IP : ' . $_SERVER['REMOTE_ADDR']);
    }
}
// array of params for import
$params = array();
// check if the GET parameters are availables
if (Tools::getIsset('forceProduct')) {
    $params['force_product'] = (bool)Tools::getValue('forceProduct');
}
if (Tools::getIsset('lengowDebug')) {
    $params['debug'] = (bool)Tools::getValue('lengowDebug');
}
if (Tools::getIsset('days') && is_numeric(Tools::getValue('days'))) {
    $params['days'] = (int)Tools::getValue('days');
}
if (Tools::getIsset('limit') && is_numeric(Tools::getValue('limit'))) {
    $params['limit'] = (int)Tools::getValue('limit');
}
if (Tools::getIsset('idOrder')) {
    $params['order_id'] = (string)Tools::getValue('idOrder');
}
if (Tools::getIsset('marketplace')) {
    $params['marketplace_name'] = (string)Tools::getValue('marketplace');
}
if (Tools::getIsset('shop') && is_numeric(Tools::getValue('shop'))) {
    $params['shop'] = (int)Tools::getValue('shop');
}
$params['type'] = (count($params) > 0 ? 'manual' : 'cron');
// import orders
LengowMain::importOrders($params);
