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

// *********************************************************
//                        NEW DATA
// *********************************************************
// create table lengow_carrier_country
$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'lengow_carrier_country (
    `id` INTEGER(10) NOT NULL AUTO_INCREMENT ,
    `id_carrier` INTEGER(10) NULL ,
    `id_country` INTEGER(10) NOT NULL UNIQUE,
    PRIMARY KEY(`id`),
    INDEX (`id_carrier`) ,
    INDEX (`id_country`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
Db::getInstance()->execute($sql);

// create table lengow_actions
$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lengow_actions` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_order` int(11) UNSIGNED NOT NULL,
    `order_line_sku` varchar(32) NULL,
    `action_id` int(11) UNSIGNED NOT NULL,
    `action_type` varchar(32) NOT NULL,
    `retry` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\',
    `parameters` text NOT NULL,
    `state` TINYINT(1) UNSIGNED NOT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY(`id`),
    INDEX (`id_order`) ,
    INDEX (`action_type`)
);';
Db::getInstance()->execute($sql);
//create table lengow_marketplace_carrier
$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lengow_marketplace_carrier` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_country` int(11) UNSIGNED NOT NULL,
    `id_carrier` int(11) UNSIGNED NULL,
    `marketplace_carrier_sku` varchar(32) NOT NULL,
    `marketplace_carrier_name` VARCHAR(32) NOT NULL,
    PRIMARY KEY(`id`),
    INDEX (`id_country`) ,
    INDEX (`id_carrier`) ,
    INDEX (`marketplace_carrier_sku`)
);';
Db::getInstance()->execute($sql);


// *********************************************************
//                         lengow_product
// *********************************************************
$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lengow_product` (
				`id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
				`id_product` INTEGER(11) UNSIGNED NOT NULL ,
				`id_shop` INTEGER(11) UNSIGNED NOT NULL DEFAULT \'1\' ,
				`id_shop_group` INTEGER(11) UNSIGNED NOT NULL DEFAULT \'1\' ,
				`id_lang` INTEGER(10) UNSIGNED NOT NULL DEFAULT \'1\' ,
				PRIMARY KEY ( `id` ) ,
				INDEX (`id_shop`) ,
				INDEX (`id_shop_group`) ,
				INDEX (`id_lang`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
Db::getInstance()->execute($sql);
// alter product table for old versions
if (Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_product\'')) {
    if (!LengowInstall::checkFieldExists('lengow_product', 'id')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_product DROP PRIMARY KEY'
        );
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_product ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
        );
    }
}

// *********************************************************
//                         lengow_order_line
// *********************************************************
// order line table
$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'lengow_order_line (
        `id` INTEGER(11) NOT NULL AUTO_INCREMENT ,
        `id_order` INTEGER(11) UNSIGNED NOT NULL ,
        `id_order_line` VARCHAR(255) NOT NULL ,
        `id_order_detail` INTEGER(11) UNSIGNED NULL ,
        PRIMARY KEY(`id`));';
Db::getInstance()->execute($sql);
// alter order_line table for old versions
if (!LengowInstall::checkFieldExists('lengow_order_line', 'id_order_detail')) {
    Db::getInstance()->execute(
        'ALTER TABLE `'._DB_PREFIX_.'lengow_order_line`
        ADD `id_order_detail` INT(11) UNSIGNED NULL AFTER `id_order_line`'
    );
}

// *********************************************************
//                         lengow_log_import
// *********************************************************
// Create table lengow log import
$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'lengow_logs_import ('
    .' `id` INT(11) NOT NULL AUTO_INCREMENT,'
    .' `is_finished` int(11) DEFAULT 0,'
    .' `message` TEXT DEFAULT NULL,'
    .' `date` datetime DEFAULT NULL,'
    .' `mail` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,'
    .' `id_order_lengow` INT(11) NOT NULL,'
    .' `type` TINYINT(1) NOT NULL,'
    .' PRIMARY KEY(id));';
Db::getInstance()->execute($sql);

//add missing field for old plugins
if (Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_logs_import\'')) {
    if (LengowInstall::checkFieldExists('lengow_logs_import', 'message')) {
        Db::getInstance()->execute(
            'ALTER TABLE  '._DB_PREFIX_.'lengow_logs_import CHANGE `message` `message` TEXT NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'mail')) {
        $sql = 'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import ADD `mail` TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\'';
        Db::getInstance()->execute($sql);
    }
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'id')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import DROP PRIMARY KEY'
        );
        Db::getInstance()->execute(
            'ALTER TABLE   '._DB_PREFIX_.'lengow_logs_import ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'delivery_address_id')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import ADD `delivery_address_id` INT(10) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'id_order_lengow')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import ADD `id_order_lengow` INT(10) NOT NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'type')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import ADD `type` TINYINT(1) NOT NULL'
        );
    }
}

// data migration to the new system
if (Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_orders\'')
    && Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_logs_import\'')
) {
    if (LengowInstall::checkFieldExists('lengow_logs_import', 'lengow_order_id')
        && LengowInstall::checkFieldExists('lengow_logs_import', 'delivery_address_id')
        && LengowInstall::checkFieldExists('lengow_orders', 'delivery_address_id')
    ) {
        // clean order line data (if empty => NULL)
        Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.'lengow_logs_import SET delivery_address_id = NULL WHERE delivery_address_id = \'\''
        );
        Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.'lengow_orders SET delivery_address_id = NULL WHERE delivery_address_id = \'\''
        );
        $results = Db::getInstance()->executeS(
            'SELECT `lengow_order_id`, `delivery_address_id` FROM `'._DB_PREFIX_.'lengow_logs_import`'
        );
        foreach ($results as $result) {
            if (LengowInstall::checkFieldExists('lengow_orders', 'marketplace_sku')) {
                $orderLineQuery = is_null($result['delivery_address_id'])
                    ? ' IS NULL'
                    : ' = \''.$result['delivery_address_id'].'\'';
                $id_order = Db::getInstance()->getRow(
                    'SELECT `id` FROM `'._DB_PREFIX_.'lengow_orders`
                    WHERE `marketplace_sku` = \''.$result['lengow_order_id'].'\'
                    AND `delivery_address_id`'.$orderLineQuery
                );
                if ($id_order) {
                    Db::getInstance()->execute(
                        'UPDATE '._DB_PREFIX_.'lengow_logs_import
                        SET `id_order_lengow` = \''.(int)$id_order['id'].'\', `type` = 1
                        WHERE `lengow_order_id` = \''.$result['lengow_order_id'].'\'
                        AND `delivery_address_id`'.$orderLineQuery
                    );
                } else {
                    Db::getInstance()->execute(
                        'DELETE FROM '._DB_PREFIX_.'lengow_logs_import
                        WHERE `lengow_order_id` = \''.$result['lengow_order_id'].'\'
                        AND `delivery_address_id`'.$orderLineQuery
                    );
                }
            }
        }
    }
}

// drop old column from log import table
LengowInstall::checkFieldAndDrop('lengow_logs_import', 'lengow_order_id');
LengowInstall::checkFieldAndDrop('lengow_logs_import', 'is_processing');
LengowInstall::checkFieldAndDrop('lengow_logs_import', 'extra');
LengowInstall::checkFieldAndDrop('lengow_logs_import', 'delivery_address_id');


// *********************************************************
//                         lengow_orders
// *********************************************************

// Orders lengow
$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lengow_orders` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `id_order` INTEGER(10) UNSIGNED NULL ,
        `marketplace_sku` VARCHAR(32) NOT NULL ,
        `id_shop` INTEGER(11) UNSIGNED NOT NULL DEFAULT \'1\' ,
        `id_shop_group` INTEGER(11) UNSIGNED NOT NULL DEFAULT \'1\' ,
        `id_lang` INTEGER(10) UNSIGNED NOT NULL DEFAULT \'1\' ,
        `id_flux` INTEGER(11) UNSIGNED NULL ,
        `marketplace_name` VARCHAR(100) NULL ,
        `marketplace_label` VARCHAR(40) NULL ,
        `message` TEXT ,
        `total_paid` DECIMAL(17,2) UNSIGNED NULL ,
        `carrier` VARCHAR(100) ,
        `tracking` VARCHAR(100) ,
        `extra` TEXT ,
        `date_add` DATETIME NOT NULL ,
        `is_reimported` TINYINT(1) UNSIGNED DEFAULT \'0\',
        `method` VARCHAR(100) NULL,
        `sent_marketplace` TINYINT(1) UNSIGNED DEFAULT \'0\',
        `order_process_state` TINYINT(1) UNSIGNED NOT NULL,
        `order_date` DATETIME NOT NULL,
        `order_item` INT(10) UNSIGNED NULL,
        `delivery_address_id` INT(11) UNSIGNED NULL,
        `delivery_country_iso` VARCHAR(3) NULL,
        `customer_name` VARCHAR(255) NULL,
        `order_lengow_state` VARCHAR(32) NOT NULL,
        `currency` VARCHAR(3) NULL,
        PRIMARY KEY(id) ,
        INDEX (`id_flux`) ,
        INDEX (`id_shop`) ,
        INDEX (`id_shop_group`) ,
        INDEX (`marketplace_sku`) ,
        INDEX (`marketplace_name`) ,
        INDEX (`date_add`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
Db::getInstance()->execute($sql);


if (Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_orders\'')) {

    if (LengowInstall::checkFieldExists('lengow_orders', 'id_flux')) {
        Db::getInstance()->execute(
            'ALTER TABLE  '._DB_PREFIX_.'lengow_orders CHANGE `id_flux` `id_flux` INT(11) UNSIGNED NULL'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'id_order')) {
        Db::getInstance()->execute(
            'ALTER TABLE  '._DB_PREFIX_.'lengow_orders CHANGE `id_order` `id_order` INT(10) UNSIGNED NULL'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'id_order_lengow')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders CHANGE `id_order_lengow` `marketplace_sku` VARCHAR(32) NOT NULL'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'marketplace')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders CHANGE `marketplace` `marketplace_name` VARCHAR(100) NULL'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'total_paid')) {
        Db::getInstance()->execute(
            'ALTER TABLE  '._DB_PREFIX_.'lengow_orders CHANGE `total_paid` `total_paid` DECIMAL(17,2) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'is_reimported')) {
        $sql = 'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `is_reimported` TINYINT(1) UNSIGNED DEFAULT \'0\'';
        Db::getInstance()->execute($sql);
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'is_reimported')
        && LengowInstall::checkFieldExists('lengow_orders', 'is_disabled')
    ) {
        Db::getInstance()->execute('DELETE FROM  '._DB_PREFIX_.'lengow_orders WHERE is_disabled = 1');
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders CHANGE `is_disabled` `is_reimported` TINYINT(1) UNSIGNED
            DEFAULT \'0\''
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'marketplace_label')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `marketplace_label` VARCHAR(40) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'delivery_address_id')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `delivery_address_id` INT(11) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'method')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `method` VARCHAR(100) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'sent_marketplace')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `sent_marketplace` TINYINT(1) UNSIGNED DEFAULT \'0\''
        );
    }

    if (!LengowInstall::checkFieldExists('lengow_orders', 'currency')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `currency` VARCHAR(3) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'order_process_state')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `order_process_state` TINYINT(1) UNSIGNED NOT NULL'
        );
        Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.'lengow_orders SET `order_process_state` = 2'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'order_date')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `order_date` DATETIME NOT NULL'
        );
        Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.'lengow_orders SET `order_date` = `date_add`'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'order_item')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `order_item` INT(10) UNSIGNED NULL'
        );
        Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.'lengow_orders lo
            INNER JOIN
            (SELECT id_order, sum(product_quantity) as total FROM '._DB_PREFIX_.'order_detail GROUP BY id_order) as tmp
            ON (tmp.id_order = lo.id_order)
            SET lo.order_item = tmp.total'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'delivery_country_iso')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `delivery_country_iso` VARCHAR(3) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'customer_name')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `customer_name` VARCHAR(255) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'order_lengow_state')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `order_lengow_state` VARCHAR(32) NOT NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'id')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders DROP PRIMARY KEY'
        );
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
        );
    }
}

//insert default_country in lengow carrier country table
LengowCarrierCountry::createDefaultCarrier();

LengowInstall::renameConfigurationKey('LENGOW_DEBUG', 'LENGOW_IMPORT_PREPROD_ENABLED');
LengowInstall::renameConfigurationKey('LENGOW_CRON', 'LENGOW_CRON_ENABLED');
LengowInstall::renameConfigurationKey('LENGOW_IMPORT_SHIPPED_BY_MP', 'LENGOW_IMPORT_SHIP_MP_ENABLED');
LengowInstall::renameConfigurationKey('LENGOW_REPORT_MAIL', 'LENGOW_REPORT_MAIL_ENABLED');
LengowInstall::renameConfigurationKey('LENGOW_EMAIL_ADDRESS', 'LENGOW_REPORT_MAIL_ADDRESS');
LengowInstall::renameConfigurationKey('LENGOW_IMPORT_SINGLE', 'LENGOW_IMPORT_SINGLE_ENABLED');
LengowInstall::renameConfigurationKey('LENGOW_TRACKING', 'LENGOW_TRACKING_ENABLED');

$configurationToDelete = array(
    'LENGOW_MIGRATE',
    'LENGOW_MP_CONF',
    'LENGOW_ID_CUSTOMER',
    'LENGOW_ID_GROUP',
    'LENGOW_TOKEN',
    'LENGOW_SWITCH_V3',
    'LENGOW_IMAGE_TYPE',
    'LENGOW_FEED_MANAGEMENT',
    'LENGOW_FORCE_PRICE',
    'LENGOW_LOGO_URL',
    'LENGOW_EXPORT_NEW',
    'LENGOW_EXPORT_FIELDS',
    'LENGOW_EXPORT_FULLNAME',
    'LENGOW_IMAGES_COUNT',
    'LENGOW_IMPORT_METHOD_NAME',
    'LENGOW_EXPORT_FEATURES',
    'LENGOW_EXPORT_SELECT_FEATURES',
    'LENGOW_IMPORT_CARRIER_MP_ENABLED',
    'LENGOW_FLOW_DATA',
    'LENGOW_CRON_EDITOR',
    'LENGOW_EXPORT_TIMEOUT',
    'LENGOW_EXPORT_DISABLED',
    'LENGOW_PARENT_IMAGE',
    'LENGOW_IMPORT_MARKETPLACES',
    'LENGOW_SWITCH_V3',
    'LENGOW_IMPORT_SHIPPED_BY_MP',
    'LENGOW_EXPORT_ALL_ATTRIBUTES',
    'LENGOW_EXPORT_ALL_VARIATIONS',
    'LENGOW_PLG_CONF',
);
foreach ($configurationToDelete as $configName) {
    Configuration::deleteByName($configName);
}

LengowInstall::saveOverride();

LengowInstall::removeFiles(array(
    'config/',
    'interface/',
    'override/',
    'models/',
    'translations/es.php',
    'translations/fr.php',
    'translations/it.php',
    'v14/',
    'controllers/AdminLengowController.php',
    'controllers/AdminLengowLogController.php',
    'controllers/TabLengowLogController.php',
    'controllers/TabLengowLogController.php',
    'translations/es.php',
    'translations/fr.php',
    'translations/it.php',
    'views/img/process-icon-export-csv.png',
    'views/img/view-lengow-en.png',
    'views/img/view-lengow-es.png',
    'views/img/view-lengow-fr.png',
    'views/img/view-lengow-it.png',
    'views/js/admin.js',
    'views/js/chart.min.js',
    'views/templates/admin/dashboard/',
    'views/templates/admin/form.tpl',
    'webservice/lengow.php',
    'AdminLengow14.php',
    'AdminLengowLog14.php',
    'config_fr.xml',
    'config_it.xml',
    'config_es.xml',
    'config_gb.xml',
    'config_de.xml',
));
