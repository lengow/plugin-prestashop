<?php
/**
 * Copyright 2021 Lengow SAS.
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
 * @copyright 2021 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
/**
 * Lengow Install Class
 */
class LengowInstall
{
    /**
     * @var array all module tables
     */
    public static $tables = [
        LengowOrder::TABLE_ORDER,
        LengowOrderLine::TABLE_ORDER_LINE,
        LengowOrderError::TABLE_ORDER_ERROR,
        LengowProduct::TABLE_PRODUCT,
        LengowAction::TABLE_ACTION,
        LengowMarketplace::TABLE_MARKETPLACE,
        LengowCarrier::TABLE_CARRIER_MARKETPLACE,
        LengowCarrier::TABLE_DEFAULT_CARRIER,
        LengowCarrier::TABLE_MARKETPLACE_CARRIER_MARKETPLACE,
        LengowCarrier::TABLE_MARKETPLACE_CARRIER_COUNTRY,
        LengowMethod::TABLE_METHOD_MARKETPLACE,
        LengowMethod::TABLE_MARKETPLACE_METHOD_MARKETPLACE,
        LengowMethod::TABLE_MARKETPLACE_METHOD_COUNTRY,
    ];

    /**
     * @var string old version for update scripts
     */
    public static $oldVersion;

    /**
     * @var bool installation status
     */
    protected static $installationStatus;

    /**
     * @var Lengow Lengow module instance
     */
    private $lengowModule;

    /**
     * @var LengowHook Lengow hook instance
     */
    private $lengowHook;

    /**
     * @var array all module tabs
     */
    private $tabs = [
        'tab.home' => ['name' => 'AdminLengowHome', 'active' => true],
        'tab.dashboard' => ['name' => 'AdminLengowDashboard', 'active' => false],
        'tab.product' => ['name' => 'AdminLengowFeed', 'active' => false],
        'tab.order' => ['name' => 'AdminLengowOrder', 'active' => false],
        'tab.order_setting' => ['name' => 'AdminLengowOrderSetting', 'active' => false],
        'tab.help' => ['name' => 'AdminLengowHelp', 'active' => false],
        'tab.main_setting' => ['name' => 'AdminLengowMainSetting', 'active' => false],
        'tab.legals' => ['name' => 'AdminLengowLegals', 'active' => false],
        'tab.toolbox' => ['name' => 'AdminLengowToolbox', 'active' => false],
    ];

    /**
     * @var array all old files to remove
     */
    private $oldFiles = [
        'AdminLengow14.php',
        'AdminLengowLog14.php',
        'classes/models/LengowCurrency.php',
        'config/marketplaces.xml',
        'config/plugins.xml',
        'controllers/AdminLengowController.php',
        'controllers/AdminLengowLogController.php',
        'controllers/TabLengowLogController.php',
        'controllers/TabLengowLogController.php',
        'translations/es.php',
        'translations/fr.php',
        'translations/it.php',
        'interface/',
        'models/',
        'override/',
        'v14/',
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
        'webservice/import.php',
    ];

    /**
     * @var array old configuration keys to remove
     */
    private $oldConfigurationKeys = [
        'LENGOW_ID_ACCOUNT',
        'LENGOW_SECRET',
        'LENGOW_CRON',
        'LENGOW_MIGRATE',
        'LENGOW_MP_CONF',
        'LENGOW_MP_CONF_V3',
        'LENGOW_ID_CUSTOMER',
        'LENGOW_ID_GROUP',
        'LENGOW_TOKEN',
        'LENGOW_SWITCH_V3',
        'LENGOW_SWITCH_V2',
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
        'LENGOW_IMPORT_CARRIER_DEFAULT',
        'LENGOW_IMPORT_CARRIER_MP_ENABLED',
        'LENGOW_IMPORT_FAKE_EMAIL',
        'LENGOW_IMPORT_PREPROD_ENABLED',
        'LENGOW_FLOW_DATA',
        'LENGOW_CRON_EDITOR',
        'LENGOW_EXPORT_TIMEOUT',
        'LENGOW_PARENT_IMAGE',
        'LENGOW_IMPORT_MARKETPLACES',
        'LENGOW_IMPORT_SHIPPED_BY_MP',
        'LENGOW_EXPORT_ALL_ATTRIBUTES',
        'LENGOW_PLG_CONF',
        'LENGOW_MP_SHIPPING_METHOD',
        'LENGOW_IS_IMPORT',
        'LENGOW_ORDER_STAT',
        'LENGOW_ORDER_STAT_UPDATE',
        'LENGOW_IMPORT_SINGLE',
        'LENGOW_IMPORT_SINGLE_ENABLED',
    ];

    /**
     * Construct
     *
     * @param Lengow $module Lengow module instance
     */
    public function __construct($module)
    {
        $this->lengowModule = $module;
        $this->lengowHook = new LengowHook($module);
    }

    /**
     * Reset options
     *
     * @return bool
     */
    public function reset()
    {
        return LengowConfiguration::resetAll(true);
    }

    /**
     * Install options
     *
     * @return bool
     */
    public function install()
    {
        LengowMain::log(
            LengowLog::CODE_INSTALL,
            LengowMain::setLogMessage('log.install.install_start', ['version' => $this->lengowModule->version])
        );
        $oldVersion = LengowConfiguration::getGlobalValue(LengowConfiguration::PLUGIN_VERSION);
        $oldVersion = $oldVersion ?: false;
        $this->setDefaultValues();
        $this->update($oldVersion);
        LengowMain::log(
            LengowLog::CODE_INSTALL,
            LengowMain::setLogMessage('log.install.install_end', ['version' => $this->lengowModule->version])
        );
        return true;
    }

    /**
     * Uninstall option
     *
     * @return bool
     */
    public function uninstall()
    {
        LengowMain::log(
            LengowLog::CODE_UNINSTALL,
            LengowMain::setLogMessage('log.uninstall.uninstall_start', ['version' => $this->lengowModule->version])
        );
        // remove Lengow config
        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'configuration` WHERE name LIKE "LENGOW_%"');
        $this->uninstallTab();
        LengowMain::log(
            LengowLog::CODE_UNINSTALL,
            LengowMain::setLogMessage('log.uninstall.uninstall_end', ['version' => $this->lengowModule->version])
        );

        return true;
    }

    /**
     * Update process
     *
     * @param bool|string $oldVersion old version for update
     *
     * @return bool
     */
    public function update($oldVersion = false)
    {
        if (self::isInstallationInProgress()) {
            return true;
        }
        if ($oldVersion) {
            self::$oldVersion = $oldVersion;
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage(
                    'log.install.update_start',
                    ['old_version' => $oldVersion, 'new_version' => $this->lengowModule->version]
                )
            );
        }
        // check if update is in progress
        self::setInstallationStatus(true);
        // create all Lengow tables
        $this->createLengowTables();
        // run sql script and configuration upgrade for specific version
        $upgradeFiles = array_diff(scandir(_PS_MODULE_LENGOW_DIR_ . 'upgrade'), ['..', '.', 'index.php']);
        foreach ($upgradeFiles as $file) {
            include _PS_MODULE_LENGOW_DIR_ . 'upgrade/' . $file;
            $numberVersion = preg_replace('/update_|\.php$/', '', $file);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.add_upgrade_version', ['version' => $numberVersion])
            );
        }
        // register hooks
        $this->lengowHook->registerHooks();
        // create state technical error - Lengow
        $this->addStatusError();
        // update lengow tabs
        $this->uninstallTab();
        $this->createTab();
        // delete old configuration
        $this->removeOldConfigurationKeys();
        // set default value for old version
        $this->setDefaultValues();
        // save old override folder
        $this->saveOverride();
        // delete old folders and files
        $this->removeOldFiles();
        // delete config files
        $this->removeConfigFiles();
        // update Lengow version for install process
        LengowConfiguration::updateGlobalValue(LengowConfiguration::PLUGIN_VERSION, $this->lengowModule->version);
        self::setInstallationStatus(false);
        if ($oldVersion) {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage(
                    'log.install.update_end',
                    ['old_version' => $oldVersion, 'new_version' => $this->lengowModule->version]
                )
            );
        }
        return true;
    }

    /**
     * Checks if a table exists in BDD
     *
     * @param string $table Lengow table
     *
     * @return bool
     */
    public static function checkTableExists($table)
    {
        $sql = 'SHOW TABLES LIKE \'' . _DB_PREFIX_ . $table . '\'';
        try {
            $result = Db::getInstance()->executeS($sql);
            return !empty($result);
        } catch (PrestaShopDatabaseException $e) {
            return true;
        }
    }

    /**
     * Checks if index exists in table
     *
     * @param string $table Lengow table
     * @param string $index Lengow index
     *
     * @return bool
     */
    public static function checkIndexExists($table, $index)
    {
        $sql = 'SHOW INDEXES FROM ' . _DB_PREFIX_ . $table . ' WHERE `Column_name` = \'' . $index . '\'';
        try {
            $result = Db::getInstance()->executeS($sql);
            return !empty($result);
        } catch (PrestaShopDatabaseException $e) {
            return true;
        }
    }

    /**
     * Checks if a field exists in BDD
     *
     * @param string $table Lengow table
     * @param string $field Lengow field
     *
     * @return bool
     */
    public static function checkFieldExists($table, $field)
    {
        $sql = 'SHOW COLUMNS FROM ' . _DB_PREFIX_ . $table . ' LIKE \'' . $field . '\'';
        try {
            $result = Db::getInstance()->executeS($sql);
            return !empty($result);
        } catch (PrestaShopDatabaseException $e) {
            return true;
        }
    }

    /**
     * Checks if a field exists in BDD and Dropped It
     *
     * @param string $table Lengow table
     * @param string $field Lengow field
     */
    public static function checkFieldAndDrop($table, $field)
    {
        if (self::checkFieldExists($table, $field)) {
            Db::getInstance()->execute('ALTER TABLE ' . _DB_PREFIX_ . $table . ' DROP COLUMN `' . $field . '`');
        }
    }

    /**
     * Drop Lengow tables
     *
     * @return bool
     */
    public static function dropTable()
    {
        foreach (self::$tables as $table) {
            LengowMain::log(
                LengowLog::CODE_UNINSTALL,
                LengowMain::setLogMessage('log.uninstall.table_dropped', ['name' => $table])
            );
            Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . $table);
        }
        return true;
    }

    /**
     * Set Installation Status
     *
     * @param bool $status installation status
     */
    public static function setInstallationStatus($status)
    {
        LengowConfiguration::updateGlobalValue(LengowConfiguration::INSTALLATION_IN_PROGRESS, (int) $status);
        self::$installationStatus = $status;
    }

    /**
     * Is Installation in progress
     *
     * @return bool
     */
    public static function isInstallationInProgress()
    {
        $sql = 'SELECT `value` FROM ' . _DB_PREFIX_ . 'configuration
            WHERE `name` = \'LENGOW_INSTALLATION_IN_PROGRESS\'';
        $value = Db::getInstance()->getRow($sql);
        return $value && (bool) $value['value'];
    }

    /**
     * Delete old files
     *
     * @param string $file name of file to delete
     */
    public static function removeFile($file)
    {
        $filePath = _PS_MODULE_LENGOW_DIR_ . $file;
        if (file_exists($filePath)) {
            if (is_dir($filePath)) {
                self::deleteDir($filePath);
            } else {
                unlink($filePath);
            }
        }
    }

    /**
     * Delete old folders
     *
     * @param string $dirPath list of folders to delete
     *
     * @return bool
     */
    public static function deleteDir($dirPath)
    {
        $length = Tools::strlen(_PS_MODULE_LENGOW_DIR_);
        if (Tools::substr($dirPath, 0, $length) !== _PS_MODULE_LENGOW_DIR_) {
            return false;
        }
        if (Tools::substr($dirPath, Tools::strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
        return true;
    }

    /**
     * Rename configuration key
     *
     * @param string $oldKey old configuration key
     * @param string $newKey new configuration key
     * @param bool $shopConfiguration configuration by shop or global
     */
    public static function renameConfigurationKey($oldKey, $newKey, $shopConfiguration = false)
    {
        if (LengowConfiguration::checkKeyExists($oldKey)) {
            $globalValue = LengowConfiguration::getGlobalValue($oldKey);
            if ($shopConfiguration) {
                $shops = LengowShop::findAll(true);
                foreach ($shops as $shop) {
                    $shopValue = LengowConfiguration::get($oldKey, false, null, $shop['id_shop']);
                    $shopValue = $shopValue === null ? $globalValue : $shopValue;
                    LengowConfiguration::updateValue($newKey, $shopValue, false, null, $shop['id_shop']);
                }
            } else {
                LengowConfiguration::updateGlobalValue($newKey, $globalValue);
            }
            Configuration::deleteByName($oldKey);
        }
    }

    /**
     * Add Lengow tables
     *
     * @return bool
     */
    private function createLengowTables()
    {
        // create table lengow_product
        $name = LengowProduct::TABLE_PRODUCT;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_product` INTEGER(11) UNSIGNED NOT NULL,
                `id_shop` INTEGER(11) UNSIGNED NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                INDEX (`id_product`),
                INDEX (`id_shop`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_orders
        $name = LengowOrder::TABLE_ORDER;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_order` INTEGER(11) UNSIGNED NULL,
                `id_shop` INTEGER(11) UNSIGNED NOT NULL DEFAULT 1,
                `id_shop_group` INTEGER(11) UNSIGNED NOT NULL DEFAULT 1,
                `id_lang` INTEGER(11) UNSIGNED NOT NULL DEFAULT 1,
                `id_flux` INTEGER(11) UNSIGNED NULL,
                `delivery_address_id` INTEGER(11) UNSIGNED NULL,
                `delivery_country_iso` VARCHAR(3) NULL,
                `marketplace_sku` VARCHAR(100) NOT NULL,
                `marketplace_name` VARCHAR(100) NULL,
                `marketplace_label` VARCHAR(100) NULL,
                `order_lengow_state` VARCHAR(32) NOT NULL,
                `order_process_state` TINYINT(1) UNSIGNED NOT NULL,
                `order_date` DATETIME NOT NULL,
                `order_item` INTEGER(11) UNSIGNED NULL,
                `order_types` TEXT NULL,
                `currency` VARCHAR(3) NULL,
                `total_paid` DECIMAL(17,2) UNSIGNED NULL,
                `customer_vat_number` VARCHAR(100) NULL,
                `commission` DECIMAL(17,2) UNSIGNED NULL,
                `customer_name` VARCHAR(255) NULL,
                `customer_email` VARCHAR(255) NULL,
                `carrier` VARCHAR(100),
                `method` VARCHAR(100) NULL,
                `tracking` VARCHAR(100),
                `id_relay` VARCHAR(100) NULL,
                `sent_marketplace` TINYINT(1) UNSIGNED DEFAULT 0,
                `is_reimported` TINYINT(1) UNSIGNED DEFAULT 0,
                `message` TEXT,
                `date_add` DATETIME NOT NULL,
                `extra` TEXT,
                PRIMARY KEY(id),
                INDEX (`id_order`),
                INDEX (`id_shop`),
                INDEX (`id_shop_group`),
                INDEX (`id_flux`),
                INDEX (`marketplace_sku`),
                INDEX (`marketplace_name`),
                INDEX (`date_add`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_order_line
        $name = LengowOrderLine::TABLE_ORDER_LINE;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_order` INTEGER(11) UNSIGNED NOT NULL,
                `id_order_line` VARCHAR(100) NOT NULL,
                `id_order_detail` INTEGER(11) UNSIGNED NULL,
                PRIMARY KEY(`id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_logs_import
        $name = LengowOrderError::TABLE_ORDER_ERROR;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `is_finished` TINYINT(1) DEFAULT 0,
                `message` TEXT DEFAULT NULL,
                `date` DATETIME DEFAULT NULL,
                `mail` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `id_order_lengow` INTEGER(11) NOT NULL,
                `type` TINYINT(1) NOT NULL,
                PRIMARY KEY(id),
                INDEX (`id_order_lengow`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_actions
        $name = LengowAction::TABLE_ACTION;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_order` INTEGER(11) UNSIGNED NOT NULL,
                `order_line_sku` VARCHAR(100) NULL,
                `action_id` INTEGER(11) UNSIGNED NOT NULL,
                `action_type` VARCHAR(32) NOT NULL,
                `retry` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `parameters` TEXT NOT NULL,
                `state` TINYINT(1) UNSIGNED NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY(`id`),
                INDEX (`id_order`),
                INDEX (`action_type`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_marketplace
        $name = LengowMarketplace::TABLE_MARKETPLACE;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `marketplace_name` VARCHAR(100) NOT NULL,
                `marketplace_label` VARCHAR(100) NOT NULL,
                `carrier_required` TINYINT(1) DEFAULT 0,
                PRIMARY KEY(`id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_carrier_marketplace
        $name = LengowCarrier::TABLE_CARRIER_MARKETPLACE;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `carrier_marketplace_name` VARCHAR(100) NOT NULL,
                `carrier_marketplace_label` VARCHAR(100) NOT NULL,
                `carrier_lengow_code` VARCHAR(100) NULL,
                PRIMARY KEY(`id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_method_marketplace
        $name = LengowMethod::TABLE_METHOD_MARKETPLACE;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `method_marketplace_name` VARCHAR(100) NOT NULL,
                `method_marketplace_label` VARCHAR(100) NOT NULL,
                `method_lengow_code` VARCHAR(100) NULL,
                PRIMARY KEY(`id`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_marketplace_carrier_marketplace
        $name = LengowCarrier::TABLE_MARKETPLACE_CARRIER_MARKETPLACE;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_marketplace` INTEGER(11) UNSIGNED NOT NULL,
                `id_carrier_marketplace` INTEGER(11) UNSIGNED NOT NULL,
                PRIMARY KEY(`id`),
                INDEX (`id_marketplace`),
                INDEX (`id_carrier_marketplace`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_marketplace_method_marketplace
        $name = LengowMethod::TABLE_MARKETPLACE_METHOD_MARKETPLACE;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_marketplace` INTEGER(11) UNSIGNED NOT NULL,
                `id_method_marketplace` INTEGER(11) UNSIGNED NOT NULL,
                PRIMARY KEY(`id`),
                INDEX (`id_marketplace`),
                INDEX (`id_method_marketplace`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_default_carrier
        $name = LengowCarrier::TABLE_DEFAULT_CARRIER;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_country` INTEGER(11) UNSIGNED NOT NULL,
                `id_marketplace` INTEGER(11) UNSIGNED NOT NULL,
                `id_carrier` INTEGER(11) UNSIGNED NULL,
                `id_carrier_marketplace` INTEGER(11) UNSIGNED NULL,
                PRIMARY KEY(`id`),
                INDEX (`id_country`),
                INDEX (`id_marketplace`),
                INDEX (`id_carrier`),
                INDEX (`id_carrier_marketplace`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_marketplace_carrier_country
        $name = LengowCarrier::TABLE_MARKETPLACE_CARRIER_COUNTRY;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_country` INTEGER(11) UNSIGNED NOT NULL,
                `id_marketplace` INTEGER(11) UNSIGNED NOT NULL,
                `id_carrier` INTEGER(11) UNSIGNED NOT NULL,
                `id_carrier_marketplace` INTEGER(11) UNSIGNED NULL,
                PRIMARY KEY(`id`),
                INDEX (`id_country`),
                INDEX (`id_marketplace`),
                INDEX (`id_carrier`),
                INDEX (`id_carrier_marketplace`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }
        // create table lengow_marketplace_method_country
        $name = LengowMethod::TABLE_MARKETPLACE_METHOD_COUNTRY;
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . $name . ' (
                `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_country` INTEGER(11) UNSIGNED NOT NULL,
                `id_marketplace` INTEGER(11) UNSIGNED NOT NULL,
                `id_carrier` INTEGER(11) UNSIGNED NOT NULL,
                `id_method_marketplace` INTEGER(11) UNSIGNED NULL,
                PRIMARY KEY(`id`),
                INDEX (`id_country`),
                INDEX (`id_marketplace`),
                INDEX (`id_carrier`),
                INDEX (`id_method_marketplace`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
            Db::getInstance()->execute($sql);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_created', ['name' => $name])
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', ['name' => $name])
            );
        }

        return true;
    }

    /**
     * Add admin Tab (Controller)
     *
     * @return bool
     */
    private function createTab()
    {
        try {
            $tabParent = new Tab();
            $tabParent->name[Configuration::get('PS_LANG_DEFAULT')] = 'Lengow';
            $tabParent->module = 'lengow';
            $tabParent->class_name = 'AdminLengow';
            $tabParent->id_parent = 0;
            $tabParent->add();
            foreach ($this->tabs as $name => $values) {
                $tab = new Tab();
                $tab->class_name = $values['name'];
                $tab->id_parent = $tabParent->id;
                $tab->active = $values['active'];
                $tab->module = $this->lengowModule->name;
                $languages = Language::getLanguages(false);
                foreach ($languages as $language) {
                    $tab->name[$language['id_lang']] = LengowMain::decodeLogMessage($name, $language['iso_code']);
                }
                $tab->add();
                LengowMain::log(
                    LengowLog::CODE_INSTALL,
                    LengowMain::setLogMessage('log.install.install_tab', ['class_name' => $tab->class_name])
                );
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Remove admin tab
     *
     * @return bool
     */
    private function uninstallTab()
    {
        try {
            $sql = 'SELECT `id_tab`, `class_name` FROM `' . _DB_PREFIX_ . 'tab` WHERE `module` = \'lengow\'';
            $tabs = Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $tabs = [];
        }
        // remove all tabs Lengow
        foreach ($tabs as $value) {
            try {
                $tab = new Tab((int) $value['id_tab']);
                if ($tab->id != 0) {
                    $tab->delete();
                }
                LengowMain::log(
                    LengowLog::CODE_UNINSTALL,
                    LengowMain::setLogMessage(
                        'log.uninstall.uninstall_tab',
                        ['class_name' => $value['class_name']]
                    )
                );
            } catch (Exception $e) {
                continue;
            }
        }
        return true;
    }

    /**
     * Set default value for Lengow configuration
     *
     * @return bool
     */
    private function setDefaultValues()
    {
        return LengowConfiguration::resetAll();
    }

    /**
     * Add error status to reimport order
     *
     * @return bool
     */
    private function addStatusError()
    {
        // add Lengow order error status
        try {
            $states = Db::getInstance()->ExecuteS(
                'SELECT * FROM ' . _DB_PREFIX_ . 'order_state
                WHERE module_name = \'' . pSQL($this->lengowModule->name) . '\''
            );
        } catch (PrestaShopDatabaseException $e) {
            $states = [];
        }
        if (empty($states)) {
            try {
                $lengowState = new OrderState();
                $lengowState->send_email = false;
                $lengowState->module_name = $this->lengowModule->name;
                $lengowState->invoice = false;
                $lengowState->delivery = false;
                $lengowState->shipped = false;
                $lengowState->paid = false;
                $lengowState->unremovable = false;
                $lengowState->logable = false;
                $lengowState->color = '#205985';
                $languages = Language::getLanguages(false);
                foreach ($languages as $language) {
                    $lengowState->name[$language['id_lang']] = LengowMain::decodeLogMessage(
                        'module.state_technical_error',
                        $language['iso_code']
                    );
                }
                $lengowState->add();
                LengowConfiguration::updateValue(LengowConfiguration::LENGOW_ERROR_STATE_ID, $lengowState->id);
                LengowMain::log('Install', LengowMain::setLogMessage('log.install.add_technical_error_status'));
            } catch (Exception $e) {
                LengowMain::log(
                    LengowLog::CODE_INSTALL,
                    LengowMain::setLogMessage(
                        'log.install.add_technical_error_status_failed',
                        ['error_message' => $e->getMessage()]
                    )
                );
            }
        } else {
            $idOrderState = $states[0]['id_order_state'];
            LengowConfiguration::updateValue(LengowConfiguration::LENGOW_ERROR_STATE_ID, $idOrderState);
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $name = LengowMain::decodeLogMessage('module.state_technical_error', $language['iso_code']);
                Db::getInstance()->update(
                    'order_state_lang',
                    ['name' => $name],
                    '`id_order_state` = \'' . (int) $idOrderState
                    . '\' AND `id_lang` = \'' . (int) $language['id_lang'] . '\''
                );
            }
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.update_technical_error_status')
            );
        }
        return true;
    }

    /**
     * Delete old files
     */
    private function removeOldFiles()
    {
        foreach ($this->oldFiles as $file) {
            self::removeFile($file);
        }
    }

    /**
     * Delete old configuration keys
     */
    private function removeOldConfigurationKeys()
    {
        foreach ($this->oldConfigurationKeys as $configuration) {
            Configuration::deleteByName($configuration);
        }
    }

    /**
     * Delete all lengow config files
     */
    private function removeConfigFiles()
    {
        $files = scandir(_PS_MODULE_LENGOW_DIR_);
        foreach ($files as $file) {
            if (preg_match('/^config[_a-zA-Z]*\.xml$/', $file)) {
                self::removeFile($file);
            }
        }
    }

    /**
     * Save Override directory
     */
    private function saveOverride()
    {
        $directoryBackup = _PS_MODULE_LENGOW_DIR_ . 'backup/';
        $directory = _PS_MODULE_LENGOW_DIR_ . 'override/';
        if (file_exists($directory)) {
            $listFile = array_diff(scandir($directory), ['..', '.']);
            if (!empty($listFile)) {
                if (!file_exists($directoryBackup . 'override')) {
                    mkdir($directoryBackup . 'override', 0755);
                }
                foreach ($listFile as $file) {
                    copy($directory . $file, $directoryBackup . 'override/' . $file);
                }
            }
        }
    }
}
