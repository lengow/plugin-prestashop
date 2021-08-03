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
 * List params
 * string toolbox_action toolbox specific action
 * string type           type of data to display
 * string date           date of the log to export
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
$token = Tools::getIsset(LengowToolbox::PARAM_TOKEN) ? Tools::getValue(LengowToolbox::PARAM_TOKEN) : '';
if (!LengowMain::checkWebservicesAccess($token)) {
    if ($token === '' || (bool) LengowConfiguration::get(LengowConfiguration::AUTHORIZED_IP_ENABLED)) {
        $errorMessage = 'Unauthorized access for IP: ' . $_SERVER['REMOTE_ADDR'];
    } else {
        $errorMessage = 'Unauthorized access for this token : ' . $token;
    }
    header('HTTP/1.1 403 Forbidden');
    die($errorMessage);
}

$action = Tools::getIsset(LengowToolbox::PARAM_TOOLBOX_ACTION)
    ? Tools::getValue(LengowToolbox::PARAM_TOOLBOX_ACTION)
    : LengowToolbox::ACTION_DATA;
// check if toolbox action is valid
if (!in_array($action, LengowToolbox::$toolboxActions, true)) {
    header('HTTP/1.1 400 Bad Request');
    die('Action: ' . $action . ' is not a valid action');
}

switch ($action) {
    case LengowToolbox::ACTION_LOG:
        $date = Tools::getIsset(LengowToolbox::PARAM_DATE) ? Tools::getValue(LengowToolbox::PARAM_DATE) : null;
        LengowToolbox::downloadLog($date);
        break;
    case LengowToolbox::ACTION_ORDER:
        $result = LengowToolbox::syncOrders(
            array(
                LengowToolbox::PARAM_CREATED_TO => Tools::getValue(LengowToolbox::PARAM_CREATED_TO, null),
                LengowToolbox::PARAM_CREATED_FROM => Tools::getValue(LengowToolbox::PARAM_CREATED_FROM, null),
                LengowToolbox::PARAM_DAYS => Tools::getValue(LengowToolbox::PARAM_DAYS, null),
                LengowToolbox::PARAM_FORCE => Tools::getValue(LengowToolbox::PARAM_FORCE, null),
                LengowToolbox::PARAM_MARKETPLACE_NAME => Tools::getValue(LengowToolbox::PARAM_MARKETPLACE_NAME, null),
                LengowToolbox::PARAM_MARKETPLACE_SKU => Tools::getValue(LengowToolbox::PARAM_MARKETPLACE_SKU, null),
                LengowToolbox::PARAM_SHOP_ID => Tools::getValue(LengowToolbox::PARAM_SHOP_ID, null),
            )
        );
        if (isset($result['error'])) {
            header('HTTP/1.1 403 Forbidden');
        }
        echo Tools::jsonEncode($result);
        break;
    default:
        $type = Tools::getIsset(LengowToolbox::PARAM_TYPE) ? Tools::getValue(LengowToolbox::PARAM_TYPE) : null;
        echo Tools::jsonEncode(LengowToolbox::getData($type));
        break;
}
