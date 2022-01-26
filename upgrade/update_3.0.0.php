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
    if (!LengowInstall::checkFieldExists(LengowProduct::TABLE_PRODUCT, LengowProduct::FIELD_ID)) {
        Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . 'lengow_product DROP PRIMARY KEY');
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_product
            ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
        );
    }
}
// Data migration to the new system
if (LengowInstall::checkTableExists(LengowProduct::TABLE_PRODUCT)
    && LengowInstall::checkFieldExists(LengowProduct::TABLE_PRODUCT, LengowProduct::FIELD_PRODUCT_ID)
    && LengowInstall::checkFieldExists(LengowProduct::TABLE_PRODUCT, LengowProduct::FIELD_SHOP_ID)
    && LengowInstall::checkFieldExists(LengowProduct::TABLE_PRODUCT, 'id_shop_group')
    && LengowInstall::checkFieldExists(LengowProduct::TABLE_PRODUCT, 'id_lang')
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
                if (isset($idProduct[LengowProduct::FIELD_PRODUCT_ID])) {
                    $insertValues[] = '(' . (int) $idProduct[LengowProduct::FIELD_PRODUCT_ID] . ', :idShop)';
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
LengowInstall::checkFieldAndDrop(LengowProduct::TABLE_PRODUCT, 'id_shop_group');
LengowInstall::checkFieldAndDrop(LengowProduct::TABLE_PRODUCT, 'id_lang');


// *********************************************************
//                         lengow_order_line
// *********************************************************

if (LengowInstall::checkTableExists(LengowOrderLine::TABLE_ORDER_LINE)) {
    if (!LengowInstall::checkFieldExists(LengowOrderLine::TABLE_ORDER_LINE, LengowOrderLine::FIELD_ORDER_DETAIL_ID)) {
        Db::getInstance()->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'lengow_order_line`
            ADD `id_order_detail` INTEGER(11) UNSIGNED NULL AFTER `id_order_line`'
        );
    }
}

// *********************************************************
//                         lengow_log_import
// *********************************************************

if (LengowInstall::checkTableExists(LengowOrderError::TABLE_ORDER_ERROR)) {
    if (LengowInstall::checkFieldExists(LengowOrderError::TABLE_ORDER_ERROR, LengowOrderError::FIELD_IS_FINISHED)) {
        Db::getInstance()->execute(
            'ALTER TABLE  ' . _DB_PREFIX_ . 'lengow_logs_import CHANGE `is_finished` `is_finished` TINYINT(1) DEFAULT 0'
        );
    }
    if (LengowInstall::checkFieldExists(LengowOrderError::TABLE_ORDER_ERROR, LengowOrderError::FIELD_MESSAGE)) {
        Db::getInstance()->execute(
            'ALTER TABLE  ' . _DB_PREFIX_ . 'lengow_logs_import CHANGE `message` `message` TEXT NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrderError::TABLE_ORDER_ERROR, LengowOrderError::FIELD_MAIL)) {
        $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_logs_import ADD `mail` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0';
        Db::getInstance()->execute($sql);
    }
    if (!LengowInstall::checkFieldExists(LengowOrderError::TABLE_ORDER_ERROR, LengowOrderError::FIELD_ID)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_logs_import DROP PRIMARY KEY'
        );
        Db::getInstance()->execute(
            'ALTER TABLE   ' . _DB_PREFIX_ . 'lengow_logs_import
            ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
        );
    }
    if (!LengowInstall::checkFieldExists(
        LengowOrderError::TABLE_ORDER_ERROR,
        LengowOrderError::FIELD_ORDER_LENGOW_ID
    )) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_logs_import ADD `id_order_lengow` INTEGER(11) NOT NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrderError::TABLE_ORDER_ERROR, LengowOrderError::FIELD_TYPE)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_logs_import ADD `type` TINYINT(1) NOT NULL'
        );
    }
}
// data migration to the new system
if (LengowInstall::checkTableExists(LengowOrder::TABLE_ORDER)
    && LengowInstall::checkTableExists(LengowOrderError::TABLE_ORDER_ERROR)
) {
    if (LengowInstall::checkFieldExists(LengowOrderError::TABLE_ORDER_ERROR, 'lengow_order_id')
        && LengowInstall::checkFieldExists(LengowOrderError::TABLE_ORDER_ERROR, 'delivery_address_id')
        && LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_DELIVERY_ADDRESS_ID)
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
            if (LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_MARKETPLACE_SKU)) {
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
                        SET `id_order_lengow` = \'' . (int) $idOrder[LengowOrder::FIELD_ID] . '\', `type` = 1
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
LengowInstall::checkFieldAndDrop(LengowOrderError::TABLE_ORDER_ERROR, 'lengow_order_id');
LengowInstall::checkFieldAndDrop(LengowOrderError::TABLE_ORDER_ERROR, 'is_processing');
LengowInstall::checkFieldAndDrop(LengowOrderError::TABLE_ORDER_ERROR, 'extra');
LengowInstall::checkFieldAndDrop(LengowOrderError::TABLE_ORDER_ERROR, 'delivery_address_id');

// *********************************************************
//                         lengow_orders
// *********************************************************

if (LengowInstall::checkTableExists(LengowOrder::TABLE_ORDER)) {
    if (LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_FLUX_ID)) {
        Db::getInstance()->execute(
            'ALTER TABLE  ' . _DB_PREFIX_ . 'lengow_orders CHANGE `id_flux` `id_flux` INTEGER(11) UNSIGNED NULL'
        );
    }
    if (LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_ORDER_ID)) {
        Db::getInstance()->execute(
            'ALTER TABLE  ' . _DB_PREFIX_ . 'lengow_orders CHANGE `id_order` `id_order` INTEGER(11) UNSIGNED NULL'
        );
    }
    if (LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, 'id_order_lengow')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders
            CHANGE `id_order_lengow` `marketplace_sku` VARCHAR(100) NOT NULL'
        );
    }
    if (LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, 'marketplace')) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders CHANGE `marketplace` `marketplace_name` VARCHAR(100) NULL'
        );
    }
    if (LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_TOTAL_PAID)) {
        Db::getInstance()->execute(
            'ALTER TABLE  ' . _DB_PREFIX_ . 'lengow_orders CHANGE `total_paid` `total_paid` DECIMAL(17,2) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_IS_REIMPORTED)) {
        $sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `is_reimported` TINYINT(1) UNSIGNED DEFAULT 0';
        Db::getInstance()->execute($sql);
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_IS_REIMPORTED)
        && LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, 'is_disabled')
    ) {
        Db::getInstance()->execute('DELETE FROM  ' . _DB_PREFIX_ . 'lengow_orders WHERE is_disabled = 1');
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders
            CHANGE `is_disabled` `is_reimported` TINYINT(1) UNSIGNED DEFAULT 0'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_MARKETPLACE_LABEL)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `marketplace_label` VARCHAR(100) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_DELIVERY_ADDRESS_ID)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `delivery_address_id` INTEGER(11) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_CARRIER_METHOD)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `method` VARCHAR(100) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_SENT_MARKETPLACE)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `sent_marketplace` TINYINT(1) UNSIGNED DEFAULT 0'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_COMMISSION)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `commission` DECIMAL(17,2) UNSIGNED NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_CURRENCY)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `currency` VARCHAR(3) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_ORDER_PROCESS_STATE)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `order_process_state` TINYINT(1) UNSIGNED NOT NULL'
        );
        Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'lengow_orders SET `order_process_state` = 2'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_ORDER_DATE)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `order_date` DATETIME NOT NULL'
        );
        Db::getInstance()->execute(
            'UPDATE ' . _DB_PREFIX_ . 'lengow_orders SET `order_date` = `date_add`'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_ORDER_ITEM)) {
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
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_DELIVERY_COUNTRY_ISO)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `delivery_country_iso` VARCHAR(3) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_CARRIER_RELAY_ID)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `id_relay` VARCHAR(100) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_CUSTOMER_NAME)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `customer_name` VARCHAR(255) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_CUSTOMER_EMAIL)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `customer_email` VARCHAR(255) NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_ORDER_LENGOW_STATE)) {
        Db::getInstance()->execute(
            'ALTER TABLE ' . _DB_PREFIX_ . 'lengow_orders ADD `order_lengow_state` VARCHAR(32) NOT NULL'
        );
    }
    if (!LengowInstall::checkFieldExists(LengowOrder::TABLE_ORDER, LengowOrder::FIELD_ID)) {
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
    LengowInstall::renameConfigurationKey('LENGOW_EXPORT_SELECTION', LengowConfiguration::SELECTION_ENABLED, true);
    LengowInstall::renameConfigurationKey('LENGOW_EXPORT_ALL_VARIATIONS', LengowConfiguration::VARIATION_ENABLED, true);
    LengowInstall::renameConfigurationKey('LENGOW_EXPORT_DISABLED', LengowConfiguration::INACTIVE_ENABLED, true);
    LengowInstall::renameConfigurationKey('LENGOW_EXPORT_FILE', LengowConfiguration::EXPORT_FILE_ENABLED);
    LengowInstall::renameConfigurationKey('LENGOW_CARRIER_DEFAULT', LengowConfiguration::DEFAULT_EXPORT_CARRIER_ID);
    LengowInstall::renameConfigurationKey('LENGOW_DEBUG', LengowConfiguration::DEBUG_MODE_ENABLED);
    LengowInstall::renameConfigurationKey(
        'LENGOW_IMPORT_SHIPPED_BY_MP',
        LengowConfiguration::SHIPPED_BY_MARKETPLACE_ENABLED
    );
    LengowInstall::renameConfigurationKey('LENGOW_REPORT_MAIL', LengowConfiguration::REPORT_MAIL_ENABLED);
    LengowInstall::renameConfigurationKey('LENGOW_EMAIL_ADDRESS', LengowConfiguration::REPORT_MAILS);
    LengowInstall::renameConfigurationKey('LENGOW_TRACKING', LengowConfiguration::TRACKING_ENABLED);
    // Reset access id for old customer v2
    LengowConfiguration::resetAccessIds();
}
