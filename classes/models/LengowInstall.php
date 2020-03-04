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

/**
 * Lengow Install Class
 */
class LengowInstall
{
    /**
     * @var array all module tables
     */
    public static $tables = array(
        'lengow_orders',
        'lengow_order_line',
        'lengow_logs_import',
        'lengow_product',
        'lengow_actions',
        'lengow_marketplace',
        'lengow_carrier_marketplace',
        'lengow_method_marketplace',
        'lengow_marketplace_carrier_marketplace',
        'lengow_marketplace_method_marketplace',
        'lengow_default_carrier',
        'lengow_marketplace_carrier_country',
        'lengow_marketplace_method_country',
    );

    /**
     * @var string old version for update scripts
     */
    public static $oldVersion;

    /**
     * @var boolean installation status
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
    private $tabs = array(
        'tab.home' => array('name' => 'AdminLengowHome', 'active' => true),
        'tab.product' => array('name' => 'AdminLengowFeed', 'active' => false),
        'tab.order' => array('name' => 'AdminLengowOrder', 'active' => false),
        'tab.order_setting' => array('name' => 'AdminLengowOrderSetting', 'active' => false),
        'tab.help' => array('name' => 'AdminLengowHelp', 'active' => false),
        'tab.main_setting' => array('name' => 'AdminLengowMainSetting', 'active' => false),
        'tab.legals' => array('name' => 'AdminLengowLegals', 'active' => false),
    );

    /**
     * @var array all old files to remove
     */
    private $oldFiles = array(
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
    );

    /**
     * @var array old configuration keys to remove
     */
    private $oldConfigurationKeys = array(
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
    );

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
     * @return boolean
     */
    public function reset()
    {
        return LengowConfiguration::resetAll(true);
    }

    /**
     * Install options
     *
     * @return boolean
     */
    public function install()
    {
        LengowMain::log(
            LengowLog::CODE_INSTALL,
            LengowMain::setLogMessage('log.install.install_start', array('version' => $this->lengowModule->version))
        );
        $oldVersion = LengowConfiguration::getGlobalValue('LENGOW_VERSION');
        $oldVersion = $oldVersion ? $oldVersion : false;
        $this->setDefaultValues();
        $this->update($oldVersion);
        LengowMain::log(
            LengowLog::CODE_INSTALL,
            LengowMain::setLogMessage('log.install.install_end', array('version' => $this->lengowModule->version))
        );
        return true;
    }

    /**
     * Uninstall option
     *
     * @return boolean
     */
    public function uninstall()
    {
        LengowMain::log(
            LengowLog::CODE_UNINSTALL,
            LengowMain::setLogMessage('log.uninstall.uninstall_start', array('version' => $this->lengowModule->version))
        );
        $this->uninstallTab();
        LengowMain::log(
            LengowLog::CODE_UNINSTALL,
            LengowMain::setLogMessage('log.uninstall.uninstall_end', array('version' => $this->lengowModule->version))
        );
        return true;
    }

    /**
     * Update process
     *
     * @param boolean|string $oldVersion old version for update
     *
     * @return boolean
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
                    array('old_version' => $oldVersion, 'new_version' => $this->lengowModule->version)
                )
            );
        }
        // check if update is in progress
        self::setInstallationStatus(true);
        // create all Lengow tables
        $this->createLengowTables();
        // run sql script and configuration upgrade for specific version
        $upgradeFiles = array_diff(scandir(_PS_MODULE_LENGOW_DIR_ . 'upgrade'), array('..', '.', 'index.php'));
        foreach ($upgradeFiles as $file) {
            include _PS_MODULE_LENGOW_DIR_ . 'upgrade/' . $file;
            $numberVersion = preg_replace('/update_|\.php$/', '', $file);
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.add_upgrade_version', array('version' => $numberVersion))
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
        // copy AdminLengowHome.gif for version 1.5
        $this->createTabImage();
        // update Lengow version for install process
        LengowConfiguration::updateGlobalValue('LENGOW_VERSION', $this->lengowModule->version);
        self::setInstallationStatus(false);
        if ($oldVersion) {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage(
                    'log.install.update_end',
                    array('old_version' => $oldVersion, 'new_version' => $this->lengowModule->version)
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
     * @return boolean
     */
    public static function checkTableExists($table)
    {
        $sql = 'SHOW TABLES LIKE \'' . _DB_PREFIX_ . $table . '\'';
        try {
            $result = Db::getInstance()->executeS($sql);
            return !empty($result) ? true : false;
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
     * @return boolean
     */
    public static function checkIndexExists($table, $index)
    {
        $sql = 'SHOW INDEXES FROM ' . _DB_PREFIX_ . $table . ' WHERE `Column_name` = \'' . $index . '\'';
        try {
            $result = Db::getInstance()->executeS($sql);
            return !empty($result) ? true : false;
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
     * @return boolean
     */
    public static function checkFieldExists($table, $field)
    {
        $sql = 'SHOW COLUMNS FROM ' . _DB_PREFIX_ . $table . ' LIKE \'' . $field . '\'';
        try {
            $result = Db::getInstance()->executeS($sql);
            return !empty($result) ? true : false;
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
     * @return boolean
     */
    public static function dropTable()
    {
        foreach (self::$tables as $table) {
            LengowMain::log(
                LengowLog::CODE_UNINSTALL,
                LengowMain::setLogMessage('log.uninstall.table_dropped', array('name' => $table))
            );
            Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . $table);
        }
        return true;
    }

    /**
     * Set Installation Status
     *
     * @param boolean $status installation status
     */
    public static function setInstallationStatus($status)
    {
        LengowConfiguration::updateGlobalValue('LENGOW_INSTALLATION_IN_PROGRESS', (int)$status);
        self::$installationStatus = $status;
    }

    /**
     * Is Installation in progress
     *
     * @return boolean
     */
    public static function isInstallationInProgress()
    {
        $sql = 'SELECT `value` FROM ' . _DB_PREFIX_ . 'configuration
            WHERE `name` = \'LENGOW_INSTALLATION_IN_PROGRESS\'';
        $value = Db::getInstance()->getRow($sql);
        return $value ? (bool)$value['value'] : false;
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
     * @return boolean
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
     * @param boolean $shopConfiguration configuration by shop or global
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
     * @return boolean
     */
    private function createLengowTables()
    {
        // create table lengow_product
        $name = 'lengow_product';
        if (!self::checkTableExists('lengow_product')) {
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_orders
        $name = 'lengow_orders';
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
                `currency` VARCHAR(3) NULL,
                `total_paid` DECIMAL(17,2) UNSIGNED NULL,
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_order_line
        $name = 'lengow_order_line';
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_logs_import
        $name = 'lengow_logs_import';
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_actions
        $name = 'lengow_actions';
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_marketplace
        $name = 'lengow_marketplace';
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_carrier_marketplace
        $name = 'lengow_carrier_marketplace';
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_method_marketplace
        $name = 'lengow_method_marketplace';
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_marketplace_carrier_marketplace
        $name = 'lengow_marketplace_carrier_marketplace';
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_marketplace_method_marketplace
        $name = 'lengow_marketplace_method_marketplace';
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_default_carrier
        $name = 'lengow_default_carrier';
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_marketplace_carrier_country
        $name = 'lengow_marketplace_carrier_country';
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'lengow_marketplace_carrier_country (
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }
        // create table lengow_marketplace_method_country
        $name = 'lengow_marketplace_method_country';
        if (!self::checkTableExists($name)) {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'lengow_marketplace_method_country (
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
                LengowMain::setLogMessage('log.install.table_created', array('name' => $name))
            );
        } else {
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.table_already_created', array('name' => $name))
            );
        }

        return true;
    }

    /**
     * Add admin Tab (Controller)
     *
     * @return boolean
     */
    private function createTab()
    {
        try {
            if (LengowMain::compareVersion()) {
                $tabParent = new Tab();
                $tabParent->name[Configuration::get('PS_LANG_DEFAULT')] = 'Lengow';
                $tabParent->module = 'lengow';
                $tabParent->class_name = 'AdminLengow';
                $tabParent->id_parent = 0;
                $tabParent->add();
            } else {
                $tabParent = new Tab(Tab::getIdFromClassName('AdminCatalog'));
                $tab = new Tab();
                $tab->name[Configuration::get('PS_LANG_DEFAULT')] = 'Lengow';
                $tab->module = 'lengow';
                $tab->class_name = 'AdminLengowHome14';
                $tab->id_parent = $tabParent->id;
                $tab->add();
                $tabParent = $tab;
            }
            foreach ($this->tabs as $name => $values) {
                if (_PS_VERSION_ < '1.5' && $values['name'] === 'AdminLengowHome') {
                    continue;
                }
                $tab = new Tab();
                if (_PS_VERSION_ < '1.5') {
                    $tab->class_name = $values['name'] . '14';
                    $tab->id_parent = $tabParent->id;
                } else {
                    $tab->class_name = $values['name'];
                    $tab->id_parent = $tabParent->id;
                    $tab->active = $values['active'];
                }
                $tab->module = $this->lengowModule->name;
                $languages = Language::getLanguages(false);
                foreach ($languages as $language) {
                    $tab->name[$language['id_lang']] = LengowMain::decodeLogMessage($name, $language['iso_code']);
                }
                $tab->add();
                LengowMain::log(
                    LengowLog::CODE_INSTALL,
                    LengowMain::setLogMessage('log.install.install_tab', array('class_name' => $tab->class_name))
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
     * @return boolean
     */
    private function uninstallTab()
    {
        try {
            $sql = 'SELECT `id_tab`, `class_name` FROM `' . _DB_PREFIX_ . 'tab` WHERE `module` = \'lengow\'';
            $tabs = Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $tabs = array();
        }
        // remove all tabs Lengow
        foreach ($tabs as $value) {
            try {
                $tab = new Tab((int)$value['id_tab']);
                if ($tab->id != 0) {
                    $tab->delete();
                }
                LengowMain::log(
                    LengowLog::CODE_UNINSTALL,
                    LengowMain::setLogMessage(
                        'log.uninstall.uninstall_tab',
                        array('class_name' => $value['class_name'])
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
     * @return boolean
     */
    private function setDefaultValues()
    {
        return LengowConfiguration::resetAll();
    }

    /**
     * Add error status to reimport order
     *
     * @return boolean
     */
    private function addStatusError()
    {
        // add Lengow order error status
        try {
            if (_PS_VERSION_ >= '1.5') {
                $states = Db::getInstance()->ExecuteS(
                    'SELECT * FROM ' . _DB_PREFIX_ . 'order_state
                    WHERE module_name = \'' . pSQL($this->lengowModule->name) . '\''
                );
            } else {
                $states = Db::getInstance()->ExecuteS(
                    'SELECT * FROM ' . _DB_PREFIX_ . 'order_state_lang
                    WHERE name = \'Technical error - Lengow\' OR name = \'Erreur technique - Lengow\' LIMIT 1'
                );
            }
        } catch (PrestaShopDatabaseException $e) {
            $states = array();
        }
        if (empty($states)) {
            try {
                $lengowState = new OrderState();
                $lengowState->send_email = false;
                if (_PS_VERSION_ >= '1.5') {
                    $lengowState->module_name = $this->lengowModule->name;
                }
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
                LengowConfiguration::updateValue('LENGOW_STATE_ERROR', $lengowState->id);
                LengowMain::log('Install', LengowMain::setLogMessage('log.install.add_technical_error_status'));
            } catch (Exception $e) {
                LengowMain::log(
                    LengowLog::CODE_INSTALL,
                    LengowMain::setLogMessage(
                        'log.install.add_technical_error_status_failed',
                        array('error_message' => $e->getMessage())
                    )
                );
            }
        } else {
            $idOrderState = $states[0]['id_order_state'];
            LengowConfiguration::updateValue('LENGOW_STATE_ERROR', $idOrderState);
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $name = LengowMain::decodeLogMessage('module.state_technical_error', $language['iso_code']);
                if (_PS_VERSION_ < '1.5') {
                    try {
                        Db::getInstance()->autoExecute(
                            _DB_PREFIX_ . 'order_state_lang',
                            array('name' => $name),
                            'UPDATE',
                            '`id_order_state` = \'' . (int)$idOrderState
                            . '\' AND `id_lang` = \'' . (int)$language['id_lang'] . '\''
                        );
                    } catch (PrestaShopDatabaseException $e) {
                        continue;
                    }
                } else {
                    Db::getInstance()->update(
                        'order_state_lang',
                        array('name' => $name),
                        '`id_order_state` = \'' . (int)$idOrderState
                        . '\' AND `id_lang` = \'' . (int)$language['id_lang'] . '\''
                    );
                }
            }
            LengowMain::log(
                LengowLog::CODE_INSTALL,
                LengowMain::setLogMessage('log.install.update_technical_error_status')
            );
        }
        return true;
    }

    /**
     * Create tab image for version 1.5
     */
    private function createTabImage()
    {
        $filePath = _PS_MODULE_LENGOW_DIR_ . 'views/img/AdminLengow.gif';
        $fileDest = _PS_MODULE_LENGOW_DIR_ . 'AdminLengow.gif';
        if (!file_exists($fileDest) && LengowMain::compareVersion('1.5') == 0) {
            copy($filePath, $fileDest);
        }
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
            $listFile = array_diff(scandir($directory), array('..', '.'));
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
