<?php
/**
 * Copyright 2020 Lengow SAS.
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
 * @copyright 2018 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

if (!LengowInstall::isInstallationInProgress()) {
    exit;
}

// *********************************************************
//                     lengow_orders
// *********************************************************

if (LengowInstall::checkTableExists(LengowOrder::TABLE_ORDER)) {
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_REFUND_REASON)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . LengowOrder::TABLE_ORDER.' ADD `'.LengowOrder::FIELD_REFUND_MODE.'` VARCHAR(100) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_REFUND_MODE)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . LengowOrder::TABLE_ORDER.' ADD `'.LengowOrder::FIELD_REFUND_MODE.'` VARCHAR(100) NULL'
        );
    }
}

if (LengowInstall::checkTableExists(LengowOrder::TABLE_ORDER_LINE)) {
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER_LINE, LengowOrder::FIELD_REFUNDED)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . LengowOrder::TABLE_ORDER_LINE.' ADD `'.LengowOrder::FIELD_REFUNDED.'` TINYINT(1) NOT NULL DEFAULT 0'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER_LINE, LengowOrder::FIELD_QUANTITY_REFUNDED)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . LengowOrder::TABLE_ORDER_LINE.' ADD `'.LengowOrder::FIELD_QUANTITY_REFUNDED.'` INT NOT NULL DEFAULT 0'
        );
    }
}
