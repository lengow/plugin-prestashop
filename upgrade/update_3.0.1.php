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

if (LengowInstall::checkTableExists(LengowProduct::TABLE_PRODUCT)) {
    if (!LengowInstall::checkIndexExists(LengowProduct::TABLE_PRODUCT, LengowProduct::FIELD_PRODUCT_ID)) {
        Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'lengow_product ADD INDEX(`id_product`)');
    }
}

// *********************************************************
//                         lengow_log_import
// *********************************************************

if (LengowInstall::checkTableExists(LengowOrderError::TABLE_ORDER_ERROR)) {
    if (!LengowInstall::checkIndexExists(
        LengowOrderError::TABLE_ORDER_ERROR,
        LengowOrderError::FIELD_ORDER_LENGOW_ID
    )) {
        Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'lengow_logs_import ADD INDEX(`id_order_lengow`)');
    }
}

// *********************************************************
//                         lengow_orders
// *********************************************************

if (LengowInstall::checkTableExists(LengowOrder::TABLE_ORDER)) {
    if (!LengowInstall::checkIndexExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_ORDER_ID)) {
        Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD INDEX(`id_order`)');
    }
}
