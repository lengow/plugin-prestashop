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

// alter product table
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

// alter order table
if (Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_orders\'')) {
    if (!LengowInstall::checkFieldExists('lengow_orders', 'id')) {
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders DROP PRIMARY KEY'
        );
        Db::getInstance()->execute(
            'ALTER TABLE '._DB_PREFIX_.'lengow_orders ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'id_order')) {
        Db::getInstance()->execute(
            'ALTER TABLE  '._DB_PREFIX_.'lengow_orders CHANGE `id_order` `id_order` INT(10) UNSIGNED NULL'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'id_order_lengow')) {
        Db::getInstance()->execute(
            'ALTER TABLE `ps_lengow_orders` CHANGE `id_order_lengow` `marketplace_sku` VARCHAR(32) NOT NULL;'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'marketplace')) {
        Db::getInstance()->execute(
            'ALTER TABLE `ps_lengow_orders` CHANGE `marketplace` `marketplace_name` VARCHAR(100) NULL;'
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'id_order_line')) {
        Db::getInstance()->execute(
            'ALTER TABLE  '._DB_PREFIX_.'lengow_orders CHANGE `id_order_line` `id_order_line` VARCHAR(255) NULL'
        );
        Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.'lengow_orders SET `id_order_line` = NULL WHERE `id_order_line` = \'\''
        );
    }
    if (LengowInstall::checkFieldExists('lengow_orders', 'total_paid')) {
        Db::getInstance()->execute(
            'ALTER TABLE  '._DB_PREFIX_.'lengow_orders CHANGE `total_paid` `total_paid` DECIMAL(17,2) UNSIGNED NULL'
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
}

// alter log import table
if (Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_logs_import\'')) {
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
        && LengowInstall::checkFieldExists('lengow_logs_import', 'lengow_order_line')
    ) {
        // clean order line data (if empty => NULL)
        Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.'lengow_logs_import SET `lengow_order_line` = NULL WHERE `lengow_order_line` = \'\''
        );
        Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.'lengow_orders SET `id_order_line` = NULL WHERE `id_order_line` = \'\''
        );
        $results = Db::getInstance()->executeS(
            'SELECT `lengow_order_id`, `lengow_order_line` FROM `'._DB_PREFIX_.'lengow_logs_import`'
        );
        foreach ($results as $result) {
            if (LengowInstall::checkFieldExists('lengow_orders', 'marketplace_sku')
                && LengowInstall::checkFieldExists('lengow_orders', 'id_order_line')
            ) {
                $orderLineQuery = is_null($result['lengow_order_line'])
                    ? ' IS NULL'
                    : ' = \''.$result['lengow_order_line'].'\'';
                $id_order = Db::getInstance()->getRow(
                    'SELECT `id` FROM `'._DB_PREFIX_.'lengow_orders` 
                    WHERE `marketplace_sku` = \''.$result['lengow_order_id'].'\'
                    AND `id_order_line`'.$orderLineQuery
                );
                if ($id_order) {
                    Db::getInstance()->execute(
                        'UPDATE '._DB_PREFIX_.'lengow_logs_import
                        SET `id_order_lengow` = \''.(int)$id_order['id'].'\', `type` = 1
                        WHERE `lengow_order_id` = \''.$result['lengow_order_id'].'\' 
                        AND `lengow_order_line`'.$orderLineQuery
                    );
                } else {
                    Db::getInstance()->execute(
                        'DELETE FROM '._DB_PREFIX_.'lengow_logs_import
                        WHERE `lengow_order_id` = \''.$result['lengow_order_id'].'\' 
                        AND `lengow_order_line`'.$orderLineQuery
                    );
                }
            }
        }
    }
}

// drop old column from log import table
if (LengowInstall::checkFieldExists('lengow_logs_import', 'lengow_order_id')) {
    Db::getInstance()->execute(
        'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import DROP COLUMN `lengow_order_id`'
    );
}
if (LengowInstall::checkFieldExists('lengow_logs_import', 'is_processing')) {
    Db::getInstance()->execute(
        'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import DROP COLUMN `is_processing`'
    );
}
if (LengowInstall::checkFieldExists('lengow_logs_import', 'extra')) {
    Db::getInstance()->execute(
        'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import DROP COLUMN `extra`'
    );
}
if (LengowInstall::checkFieldExists('lengow_logs_import', 'delivery_address_id')) {
    Db::getInstance()->execute(
        'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import DROP COLUMN `delivery_address_id`'
    );
}

// TODO MIGRATION SETTINGS

//LENGOW_DEBUG => LENGOW_IMPORT_PREPROD_ENABLED
//LENGOW_CRON => LENGOW_CRON_ENABLED
//LENGOW_IMPORT_SHIPPED_BY_MP => LENGOW_IMPORT_SHIP_MP_ENABLED
//LENGOW_MP_SHIPPING_METHOD => LENGOW_IMPORT_CARRIER_MP_ENABLED
//LENGOW_REPORT_MAIL => LENGOW_REPORT_MAIL_ENABLED
//LENGOW_EMAIL_ADDRESS => LENGOW_REPORT_MAIL_ADDRESS
//LENGOW_IMPORT_SINGLE => LENGOW_IMPORT_SINGLE_ENABLED
//LENGOW_IS_IMPORT => LENGOW_IMPORT_IN_PROGRESS
//LENGOW_TRACKING => LENGOW_TRACKING_ENABLED
//LENGOW_EXPORT_SELECTION => LENGOW_EXPORT_SELECTION_ENABLED
//LENGOW_EXPORT_ALL_VARIATIONS => LENGOW_EXPORT_VARIATION_ENABLED
//LENGOW_EXPORT_FILE => LENGOW_EXPORT_FILE_ENABLED

$configurations = array(
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
    'LENGOW_FLOW_DATA',
    'LENGOW_CRON_EDITOR',
    'LENGOW_EXPORT_TIMEOUT',
    'LENGOW_EXPORT_DISABLED',
    'LENGOW_PARENT_IMAGE'
);
foreach ($configurations as $configuration) {
    Configuration::deleteByName($configuration);
}
