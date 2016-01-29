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

if (!$installation) {
    exit();
}

// order line table
$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'lengow_order_line (
        `id` INTEGER(11) NOT NULL AUTO_INCREMENT ,
        `id_order` INTEGER(11) UNSIGNED NOT NULL ,
        `id_order_line` VARCHAR(255) NOT NULL ,
        PRIMARY KEY(`id`));';
Db::getInstance()->execute($sql);

// alter lengow order table
if (Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_orders\'')) {
    if (LengowInstall::checkFieldExists('lengow_orders', 'id_flux')) {
        Db::getInstance()->execute(
            'ALTER TABLE  '._DB_PREFIX_.'lengow_orders CHANGE `id_flux` `id_flux` INT(11) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'delivery_id_address')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `delivery_id_address` INT(10) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'method')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `method` VARCHAR(100) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'sent_marketplace')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `sent_marketplace` tinyint(1) UNSIGNED DEFAULT \'0\''
        );
    }
}

// alter log import table
if (Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_logs_import\'')) {
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'id')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import DROP PRIMARY KEY'
        );
        Db::getInstance()->execute(
            'ALTER TABLE   '._DB_PREFIX_.'lengow_logs_import ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'delivery_id_address')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import ADD `delivery_id_address` INT(10) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'lengow_order_line')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import ADD `lengow_order_line` VARCHAR(255)'
        );
    }
}
Configuration::updateValue('LENGOW_SWITCH_V3', false);
