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
    if (!LengowInstall::checkFieldExists('lengow_product', 'id')) {
        Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'lengow_product DROP PRIMARY KEY');
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_product
            ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
        );
    }
}
// Data migration to the new system
if (LengowInstall::checkTableExists('lengow_product')
    && LengowInstall::checkFieldExists('lengow_product', 'id_product')
    && LengowInstall::checkFieldExists('lengow_product', 'id_shop')
    && LengowInstall::checkFieldExists('lengow_product', 'id_shop_group')
    && LengowInstall::checkFieldExists('lengow_product', 'id_lang')
) {
    try {
        $idProducts = Db::getInstance()->executeS(
            'SELECT DISTINCT id_product FROM `' . _DB_PREFIX_ . 'lengow_product`'
        );
    } catch (PrestaShopDatabaseException $e) {
        $idProducts = array();
    }
    if (!empty($idProducts)) {
        Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'lengow_product SET id_shop = 1');
        if (LengowShop::isFeatureActive()) {
            $shops = LengowShop::findAll(true);
            $insertValues = array();
            foreach ($idProducts as $idProduct) {
                if (isset($idProduct['id_product'])) {
                    $insertValues[] = '(' . (int)$idProduct['id_product'] . ', :idShop)';
                }
            }
            if (!empty($insertValues)) {
                $insertValueStr = join(', ', $insertValues);
                foreach ($shops as $shop) {
                    if (!isset($shop['id_shop']) || $shop['id_shop'] == 1) {
                        continue;
                    }
                    $values = str_replace(':idShop', $shop['id_shop'], $insertValueStr);
                    $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'lengow_product (id_product, id_shop) VALUES ' . $values;
                    Db::getInstance()->execute($sql);
                }
            }
        }
    }
}
// Drop old column from lengow_product table
LengowInstall::checkFieldAndDrop('lengow_product', 'id_shop_group');
LengowInstall::checkFieldAndDrop('lengow_product', 'id_lang');


// *********************************************************
//                         lengow_order_line
// *********************************************************

if (LengowInstall::checkTableExists('lengow_order_line')) {
    if (!LengowInstall::checkFieldExists('lengow_order_line', 'id_order_detail')) {
        Db::getInstance()->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'lengow_order_line`
            ADD `id_order_detail` INTEGER(11) UNSIGNED NULL AFTER `id_order_line`'
        );
    }
}

// *********************************************************
//                         lengow_log_import
// *********************************************************

if (LengowInstall::checkTableExists('lengow_logs_import')) {
    if (LengowInstall::checkFieldExists('lengow_logs_import', 'is_finished')) {
        Db::getInstance()->execute(
            'ALTER TABLE  ' . _DB_PREFIX_ . 'lengow_logs_import CHANGE `is_finished` `is_finished` TINYINT(1) DEFAULT 0'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_logs_import', 'message')) {
        Db::getInstance()->execute(
            'ALTER TABLE  ' . _DB_PREFIX_ . 'lengow_logs_import CHANGE `message` `message` TEXT NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'mail')) {
        $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_logs_import ADD `mail` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0';
        Db::getInstance()->execute($sql);
    }
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'id')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_logs_import DROP PRIMARY KEY'
        );
        Db::getInstance()->execute(
            'ALTER TABLE   ' . _DB_PREFIX_ . 'lengow_logs_import
            ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'id_order_lengow')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_logs_import ADD `id_order_lengow` INTEGER(11) NOT NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_logs_import', 'type')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_logs_import ADD `type` TINYINT(1) NOT NULL'
        );
    }
}
// data migration to the new system
if (LengowInstall::checkTableExists('lengow_orders') && LengowInstall::checkTableExists('lengow_logs_import')) {
    if (LengowInstall::checkFieldExists('lengow_logs_import', 'lengow_order_id')
        && LengowInstall::checkFieldExists('lengow_logs_import', 'delivery_address_id')
        && LengowInstall::checkFieldExists('lengow_orders', 'delivery_address_id')
    ) {
        // clean order line data (if empty => NULL)
        Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'lengow_logs_import
            SET delivery_address_id = NULL WHERE delivery_address_id = \'\''
        );
        Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'lengow_orders SET delivery_address_id = NULL WHERE delivery_address_id = \'\''
        );
        $results = Db::getInstance()->executeS(
            'SELECT `lengow_order_id`, `delivery_address_id` FROM `' . _DB_PREFIX_ . 'lengow_logs_import`'
        );
        foreach ($results as $result) {
            if (LengowInstall::checkFieldExists('lengow_orders', 'marketplace_sku')) {
                $orderLineQuery = $result['delivery_address_id'] === null
                    ? ' IS NULL'
                    : ' = \'' . $result['delivery_address_id'] . '\'';
                $idOrder = Db::getInstance()->getRow(
                    'SELECT `id` FROM `' . _DB_PREFIX_ . 'lengow_orders`
                    WHERE `marketplace_sku` = \'' . $result['lengow_order_id'] . '\'
                    AND `delivery_address_id`' . $orderLineQuery
                );
                if ($idOrder) {
                    Db::getInstance()->execute(
                        'UPDATE ' . _DB_PREFIX_ . 'lengow_logs_import
                        SET `id_order_lengow` = \'' . (int)$idOrder['id'] . '\', `type` = 1
                        WHERE `lengow_order_id` = \'' . $result['lengow_order_id'] . '\'
                        AND `delivery_address_id`' . $orderLineQuery
                    );
                } else {
                    Db::getInstance()->execute(
                        'DELETE FROM ' . _DB_PREFIX_ . 'lengow_logs_import
                        WHERE `lengow_order_id` = \'' . $result['lengow_order_id'] . '\'
                        AND `delivery_address_id`' . $orderLineQuery
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

if (LengowInstall::checkTableExists('lengow_orders')) {
    if (LengowInstall::checkFieldExists('lengow_orders', 'id_flux')) {
        Db::getInstance()->execute(
            'ALTER TABLE  ' . _DB_PREFIX_ . 'lengow_orders CHANGE `id_flux` `id_flux` INTEGER(11) UNSIGNED NULL'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'id_order')) {
        Db::getInstance()->execute(
            'ALTER TABLE  ' . _DB_PREFIX_ . 'lengow_orders CHANGE `id_order` `id_order` INTEGER(11) UNSIGNED NULL'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'id_order_lengow')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders
            CHANGE `id_order_lengow` `marketplace_sku` VARCHAR(100) NOT NULL'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'marketplace')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders CHANGE `marketplace` `marketplace_name` VARCHAR(100) NULL'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'total_paid')) {
        Db::getInstance()->execute(
            'ALTER TABLE  ' . _DB_PREFIX_ . 'lengow_orders CHANGE `total_paid` `total_paid` DECIMAL(17,2) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'is_reimported')) {
        $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `is_reimported` TINYINT(1) UNSIGNED DEFAULT 0';
        Db::getInstance()->execute($sql);
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'is_reimported')
        && LengowInstall::checkFieldExists('lengow_orders', 'is_disabled')
    ) {
        Db::getInstance()->execute('DELETE FROM  ' . _DB_PREFIX_ . 'lengow_orders WHERE is_disabled = 1');
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders
            CHANGE `is_disabled` `is_reimported` TINYINT(1) UNSIGNED DEFAULT 0'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'marketplace_label')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `marketplace_label` VARCHAR(100) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'delivery_address_id')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `delivery_address_id` INTEGER(11) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'method')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `method` VARCHAR(100) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'sent_marketplace')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `sent_marketplace` TINYINT(1) UNSIGNED DEFAULT 0'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'commission')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `commission` DECIMAL(17,2) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'currency')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `currency` VARCHAR(3) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'order_process_state')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `order_process_state` TINYINT(1) UNSIGNED NOT NULL'
        );
        Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'lengow_orders SET `order_process_state` = 2'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'order_date')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `order_date` DATETIME NOT NULL'
        );
        Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'lengow_orders SET `order_date` = `date_add`'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'order_item')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `order_item` INTEGER(11) UNSIGNED NULL'
        );
        Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'lengow_orders lo
            INNER JOIN
            (SELECT id_order, sum(product_quantity) as total
            FROM ' . _DB_PREFIX_ . 'order_detail GROUP BY id_order) as tmp
            ON (tmp.id_order = lo.id_order)
            SET lo.order_item = tmp.total'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'delivery_country_iso')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `delivery_country_iso` VARCHAR(3) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'id_relay')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `id_relay` VARCHAR(100) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'customer_name')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `customer_name` VARCHAR(255) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'customer_email')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `customer_email` VARCHAR(255) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'order_lengow_state')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `order_lengow_state` VARCHAR(32) NOT NULL'
        );
    }
    if (!LengowInstall::checkFieldExists('lengow_orders', 'id')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders DROP PRIMARY KEY'
        );
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders
            ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
        );
    }
}

// *********************************************************
//              Other install specific process
// *********************************************************

if (LengowInstall::$oldVersion && LengowInstall::$oldVersion < '3.0.0') {
    // Rename old settings
    LengowInstall::renameConfigurationKey('LENGOW_EXPORT_SELECTION', 'LENGOW_EXPORT_SELECTION_ENABLED', true);
    LengowInstall::renameConfigurationKey('LENGOW_EXPORT_ALL_VARIATIONS', 'LENGOW_EXPORT_VARIATION_ENABLED', true);
    LengowInstall::renameConfigurationKey('LENGOW_EXPORT_DISABLED', 'LENGOW_EXPORT_INACTIVE', true);
    LengowInstall::renameConfigurationKey('LENGOW_EXPORT_FILE', 'LENGOW_EXPORT_FILE_ENABLED');
    LengowInstall::renameConfigurationKey('LENGOW_CARRIER_DEFAULT', 'LENGOW_EXPORT_CARRIER_DEFAULT');
    LengowInstall::renameConfigurationKey('LENGOW_DEBUG', 'LENGOW_IMPORT_DEBUG_ENABLED');
    LengowInstall::renameConfigurationKey('LENGOW_IMPORT_SHIPPED_BY_MP', 'LENGOW_IMPORT_SHIP_MP_ENABLED');
    LengowInstall::renameConfigurationKey('LENGOW_REPORT_MAIL', 'LENGOW_REPORT_MAIL_ENABLED');
    LengowInstall::renameConfigurationKey('LENGOW_EMAIL_ADDRESS', 'LENGOW_REPORT_MAIL_ADDRESS');
    LengowInstall::renameConfigurationKey('LENGOW_IMPORT_SINGLE', 'LENGOW_IMPORT_SINGLE_ENABLED');
    LengowInstall::renameConfigurationKey('LENGOW_TRACKING', 'LENGOW_TRACKING_ENABLED');
    // Reset access id for old customer v2
    LengowConfiguration::resetAccessIds();
}
