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
 * List params
 * string  toolbox_action   Toolbox specific action
 * string  type             Type of data to display
 * string  created_from     Synchronization of orders since
 * string  created_to       Synchronization of orders until
 * string  date             Log date to download
 * string  marketplace_name Lengow marketplace name to synchronize
 * string  marketplace_sku  Lengow marketplace order id to synchronize
 * string  process          Type of process for order action
 * boolean force            Force synchronization order even if there are errors (1) or not (0)
 * integer shop_id          Shop id to synchronize
 * integer days             Synchronization interval time
 */
@set_time_limit(0);
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
$token = Tools::getValue(LengowToolbox::PARAM_TOKEN, '');
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

$action = Tools::getValue(LengowToolbox::PARAM_TOOLBOX_ACTION, LengowToolbox::ACTION_DATA);
// check if toolbox action is valid
if (!in_array($action, LengowToolbox::$toolboxActions, true)) {
    header('HTTP/1.1 400 Bad Request');
    exit('Action: ' . $action . ' is not a valid action');
}

switch ($action) {
    case LengowToolbox::ACTION_LOG:
        $date = Tools::getValue(LengowToolbox::PARAM_DATE, null);
        LengowToolbox::downloadLog($date);
        break;
    case LengowToolbox::ACTION_ORDER:
        $process = Tools::getValue(LengowToolbox::PARAM_PROCESS, LengowToolbox::PROCESS_TYPE_SYNC);
        $error = false;
        if ($process === LengowToolbox::PROCESS_TYPE_GET_DATA) {
            $result = LengowToolbox::getOrderData(
                Tools::getValue(LengowToolbox::PARAM_MARKETPLACE_SKU, null),
                Tools::getValue(LengowToolbox::PARAM_MARKETPLACE_NAME, null),
                Tools::getValue(LengowToolbox::PARAM_TYPE, null)
            );
        } else {
            $result = LengowToolbox::syncOrders(
                [
                    LengowToolbox::PARAM_CREATED_TO => Tools::getValue(LengowToolbox::PARAM_CREATED_TO, null),
                    LengowToolbox::PARAM_CREATED_FROM => Tools::getValue(LengowToolbox::PARAM_CREATED_FROM, null),
                    LengowToolbox::PARAM_DAYS => Tools::getValue(LengowToolbox::PARAM_DAYS, null),
                    LengowToolbox::PARAM_FORCE => Tools::getValue(LengowToolbox::PARAM_FORCE, null),
                    LengowToolbox::PARAM_MARKETPLACE_NAME => Tools::getValue(
                        LengowToolbox::PARAM_MARKETPLACE_NAME,
                        null
                    ),
                    LengowToolbox::PARAM_MARKETPLACE_SKU => Tools::getValue(LengowToolbox::PARAM_MARKETPLACE_SKU, null),
                    LengowToolbox::PARAM_SHOP_ID => Tools::getValue(LengowToolbox::PARAM_SHOP_ID, null),
                ]
            );
        }
        if (isset($result[LengowToolbox::ERRORS][LengowToolbox::ERROR_CODE])) {
            $error = true;
            if ($result[LengowToolbox::ERRORS][LengowToolbox::ERROR_CODE] === LengowConnector::CODE_404) {
                header('HTTP/1.1 404 Not Found');
            } else {
                header('HTTP/1.1 403 Forbidden');
            }
        }
        if (!$error) {
            header('Content-Type: application/json');
        }
        echo json_encode($result);
        break;
    default:
        header('Content-Type: application/json');
        $type = Tools::getValue(LengowToolbox::PARAM_TYPE, null);
        echo json_encode(LengowToolbox::getData($type));
        break;
}
