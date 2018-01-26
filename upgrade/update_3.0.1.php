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

if (!LengowInstall::isInstallationInProgress()) {
    exit();
}

// *********************************************************
//                         lengow_product
// *********************************************************

if (LengowInstall::checkTableExists('lengow_product')) {
    if (!LengowInstall::checkIndexExists('lengow_product', 'id_product')) {
        Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'lengow_product ADD INDEX(`id_product`)');
    }
}

// *********************************************************
//                         lengow_log_import
// *********************************************************

if (LengowInstall::checkTableExists('lengow_logs_import')) {
    if (!LengowInstall::checkIndexExists('lengow_logs_import', 'id_order_lengow')) {
        Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'lengow_logs_import ADD INDEX(`id_order_lengow`)');
    }
}

// *********************************************************
//                         lengow_orders
// *********************************************************

if (LengowInstall::checkTableExists('lengow_orders')) {
    if (!LengowInstall::checkIndexExists('lengow_orders', 'id_order')) {
        Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD INDEX(`id_order`)');
    }
}
