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

if (Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_orders\'')) {
    $result = Db::getInstance()->execute("SHOW COLUMNS FROM "._DB_PREFIX_."lengow_logs_import LIKE 'is_disabled' ");
    $exists = count($result) > 0 ? true : false;
    if (!$exists) {
        $sql = 'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `is_disabled` tinyint(1) UNSIGNED DEFAULT \'0\'';
        Db::getInstance()->execute($sql);
    }
}
Configuration::updateValue('LENGOW_IMPORT_SHIPPED_BY_MP', false);
Configuration::updateValue('LENGOW_EXPORT_ALL_VARIATIONS', Configuration::get('LENGOW_EXPORT_ALL_ATTRIBUTES'));
Configuration::deleteByName('LENGOW_IMPORT_MARKETPLACES');
Configuration::deleteByName('LENGOW_EXPORT_ALL_ATTRIBUTES');
Configuration::updateValue('LENGOW_EXPORT_SELECTION', !Configuration::get('LENGOW_EXPORT_ALL'));
Configuration::updateValue('LENGOW_FEED_MANAGEMENT', false);

$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lengow_orders` (
        `id_order` INTEGER(10) UNSIGNED NOT NULL ,
        `id_order_lengow` VARCHAR(32) ,
        `id_shop` INTEGER(11) UNSIGNED NOT NULL DEFAULT \'1\' ,
        `id_shop_group` INTEGER(11) UNSIGNED NOT NULL DEFAULT \'1\' ,
        `id_lang` INTEGER(10) UNSIGNED NOT NULL DEFAULT \'1\' ,
        `id_flux` INTEGER(11) UNSIGNED NOT NULL ,
        `marketplace` VARCHAR(100) ,
        `message` TEXT ,
        `total_paid` DECIMAL(17,2) NOT NULL ,
        `carrier` VARCHAR(100) ,
        `tracking` VARCHAR(100) ,
        `extra` TEXT ,
        `date_add` DATETIME NOT NULL ,
        `is_disabled` TINYINT(1) UNSIGNED DEFAULT \'0\',
        PRIMARY KEY(id_order) ,
        INDEX (`id_order_lengow`) ,
        INDEX (`id_flux`) ,
        INDEX (`id_shop`) ,
        INDEX (`id_shop_group`) ,
        INDEX (`marketplace`) ,
        INDEX (`date_add`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

Db::getInstance()->execute($sql);

$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lengow_product` (
				`id_product` INTEGER UNSIGNED NOT NULL ,
				`id_shop` INTEGER(11) UNSIGNED NOT NULL DEFAULT \'1\' ,
				`id_shop_group` INTEGER(11) UNSIGNED NOT NULL DEFAULT \'1\' ,
				`id_lang` INTEGER(10) UNSIGNED NOT NULL DEFAULT \'1\' ,
				PRIMARY KEY ( `id_product` ) ,
				INDEX (`id_shop`) ,
				INDEX (`id_shop_group`) ,
				INDEX (`id_lang`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

// Products exports
Db::getInstance()->execute($sql);
