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

if (!LengowInstall::isInstallationInProgress()) {
    exit();
}

// Orders lengow
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

// Products exports
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
Db::getInstance()->execute($sql);

// Logs import
$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'lengow_logs_import ('
    .' `lengow_order_id` VARCHAR(32) NOT NULL,'
    .' `is_processing` int(11) DEFAULT 0,'
    .' `is_finished` int(11) DEFAULT 0,'
    .' `message` varchar(255) DEFAULT NULL,'
    .' `date` datetime DEFAULT NULL,'
    .' `extra` text NOT NULL,'
    .' PRIMARY KEY(lengow_order_id));';
Db::getInstance()->execute($sql);
