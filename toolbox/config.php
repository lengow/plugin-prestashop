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

require 'conf.inc.php';

$action = isset($_REQUEST['action']) ?  $_REQUEST['action'] : null;
$accessToken = isset($_REQUEST['access_token']) ?  $_REQUEST['access_token'] : null;
$secretToken = isset($_REQUEST['secret_token']) ?  $_REQUEST['secret_token'] : null;

$form = new LengowConfigurationForm(array(
    "fields" => LengowConfiguration::getKeys()
));

if (_PS_VERSION_ < '1.5') {
    $shopCollection = array(array('id_shop' => 1));
} else {
    $sql = 'SELECT id_shop FROM '._DB_PREFIX_.'shop WHERE active = 1';
    $shopCollection = Db::getInstance()->ExecuteS($sql);
}

switch ($action) {
    case "update":
        $form->postProcess(array(
            'LENGOW_SHOP_ACTIVE',
            'LENGOW_EXPORT_FILE_ENABLED',
            'LENGOW_IMPORT_FORCE_PRODUCT',
            'LENGOW_IMPORT_PREPROD_ENABLED',
            'LENGOW_IMPORT_SHIPPED_BY_MP_ENABLED',
            'LENGOW_IMPORT_CARRIER_MP_ENABLED',
            'LENGOW_REPORT_MAIL_ENABLED',
            'LENGOW_IMPORT_SINGLE_ENABLED',
            'LENGOW_TRACKING_ENABLED'
        ));
        Tools::redirect(_PS_BASE_URL_.__PS_BASE_URI__.'modules/lengow/toolbox/config.php', '');
        break;
}

require 'views/header.php';
require 'views/config.php';
require 'views/footer.php';
