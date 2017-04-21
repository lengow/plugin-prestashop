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
    * @var Lengow Lengow module instance
    */
    private $lengowModule;

    /**
    * @var lengowHook lengow hook class
    */
    private $lengowHook;

    /**
    * @var boolean installation status
    */
    protected static $installationStatus;

    /**
    * @var array all module tabs
    */
    static private $tabs = array(
        'tab.home'          => array('name' => 'AdminLengowHome', 'active' => true),
        'tab.product'       => array('name' => 'AdminLengowFeed', 'active' => false),
        'tab.order'         => array('name' => 'AdminLengowOrder', 'active' => false),
        'tab.order_setting' => array('name' => 'AdminLengowOrderSetting', 'active' => false),
        'tab.help'          => array('name' => 'AdminLengowHelp', 'active' => false),
        'tab.main_setting'  => array('name' => 'AdminLengowMainSetting', 'active' => false),
        'tab.legals'        => array('name' => 'AdminLengowLegals', 'active' => false)
    );

    /**
    * @var array all module tables
    */
    static public $tables = array(
        'lengow_actions',
        'lengow_carrier_country',
        'lengow_logs_import',
        'lengow_marketplace_carrier',
        'lengow_orders',
        'lengow_order_line',
        'lengow_product',
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
        return $this->setDefaultValues() && $this->update();
    }

    /**
     * Uninstall option
     *
     * @return boolean
     */
    public function uninstall()
    {
        return LengowCron::removeCronTasks() && $this->uninstallTab();
    }

    /**
     * Add admin Tab (Controller)
     *
     * @return boolean
     */
    private function createTab()
    {
        if (LengowMain::compareVersion()) {
            $tabParent = new Tab();
            $tabParent->name[Configuration::get('PS_LANG_DEFAULT')] = 'Lengow';
            $tabParent->module = 'lengow';
            $tabParent->class_name = 'AdminLengowHome';
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
        foreach (self::$tabs as $name => $values) {
            $tab = new Tab();
            if (_PS_VERSION_ < '1.5') {
                $tab->class_name = $values['name']."14";
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
                'Install',
                LengowMain::setLogMessage('log.install.install_tab', array('class_name' => $tab->class_name))
            );
        }
        return true;
    }

    /**
     * Remove admin tab
     *
     * @return boolean result of tab uninstallation
     */
    private static function uninstallTab()
    {
        $sql = 'SELECT `id_tab`, `class_name` FROM `'._DB_PREFIX_.'tab` WHERE `module` = \'lengow\'';
        $tabs = Db::getInstance()->executeS($sql);
        // remove all tabs Lengow
        foreach ($tabs as $value) {
            $tab = new Tab((int)$value['id_tab']);
            if ($tab->id != 0) {
                $tab->delete();
            }
            LengowMain::log(
                'Install',
                LengowMain::setLogMessage('log.install.uninstall_tab', array('class_name' => $value['class_name']))
            );
        }
        return true;
    }

    /**
     * Set default value for Lengow configuration
     *
     * @return boolean
     */
    private static function setDefaultValues()
    {
        return LengowConfiguration::resetAll();
    }

    /**
     * Add error status to reimport order
     *
     * @return boolean result of add status process
     */
    private function addStatusError()
    {
        // Add Lengow order error status
        if (_PS_VERSION_ >= '1.5') {
            $states = Db::getInstance()->ExecuteS(
                'SELECT * FROM '._DB_PREFIX_.'order_state WHERE module_name = \''.pSQL($this->lengowModule->name).'\''
            );
        } else {
            $states = Db::getInstance()->ExecuteS(
                'SELECT * FROM '. _DB_PREFIX_.'order_state_lang
                WHERE name = \'Technical error - Lengow\' OR name = \'Erreur technique - Lengow\' LIMIT 1'
            );
        }
        if (empty($states)) {
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
        } else {
            $idOrderState = $states[0]['id_order_state'];
            LengowConfiguration::updateValue('LENGOW_STATE_ERROR', $idOrderState);
            $languages = Language::getLanguages(false);
            foreach ($languages as $language) {
                $name = LengowMain::decodeLogMessage('module.state_technical_error', $language['iso_code']);

                if (_PS_VERSION_ < '1.5') {
                    Db::getInstance()->autoExecute(
                        _DB_PREFIX_.'order_state_lang',
                        array('name' => $name),
                        'UPDATE',
                        '`id_order_state` = \''.(int)$idOrderState
                        .'\' AND `id_lang` = \''.(int)$language['id_lang'].'\''
                    );
                } else {
                    Db::getInstance()->update(
                        'order_state_lang',
                        array('name' => $name),
                        '`id_order_state` = \''.(int)$idOrderState
                        .'\' AND `id_lang` = \''.(int)$language['id_lang'].'\''
                    );
                }
            }
        }
        return true;
    }

    /**
     * Update process
     *
     * @return boolean result of update process
     */
    public function update()
    {
        // check if update is in progress
        self::setInstallationStatus(true);
        $upgradeFiles = array_diff(scandir(_PS_MODULE_LENGOW_DIR_ . 'upgrade'), array('..', '.'));
        foreach ($upgradeFiles as $file) {
            include _PS_MODULE_LENGOW_DIR_ . 'upgrade/' . $file;
            $numberVersion = preg_replace('/update_|\.php$/', '', $file);
        }
        // Register hooks
        $this->lengowHook->registerHooks();
        // Create state technical error - Lengow
        $this->addStatusError();
        // update lengow tabs
        $this->uninstallTab();
        $this->createTab();
        // set default value for old version
        $this->setDefaultValues();
        // update lengow version
        if (isset($numberVersion)) {
            LengowConfiguration::updateGlobalValue('LENGOW_VERSION', $numberVersion);
        }
        self::setInstallationStatus(false);
        return true;
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
        $sql = 'SHOW COLUMNS FROM '._DB_PREFIX_.$table.' LIKE \''.$field.'\'';
        $result = Db::getInstance()->executeS($sql);
        $exists = count($result) > 0 ? true : false;
        return $exists;
    }

    /**
     * Checks if a field exists in BDD and Dropped It
     *
     * @param string $table Lengow table
     * @param string $field Lengow field
     *
     * @return boolean
     */
    public static function checkFieldAndDrop($table, $field)
    {
        if (self::checkFieldExists($table, $field)) {
            Db::getInstance()->execute(
                'ALTER TABLE '._DB_PREFIX_.$table.' DROP COLUMN `'.$field.'`'
            );
        }
    }

    /**
     * Rename configuration key
     *
     * @param string $oldName old configuration name
     * @param string $newName new configuration name
     */
    public static function renameConfigurationKey($oldName, $newName)
    {
        $tempValue = LengowConfiguration::get($oldName);
        LengowConfiguration::updatevalue($newName, $tempValue);
        LengowConfiguration::deleteByName($oldName);
    }

    /**
     * Set Installation Status
     *
     * @param boolean $status installation status
     */
    public static function setInstallationStatus($status)
    {
        self::$installationStatus = $status;
    }

    /**
     * Is Installation in progress
     *
     * @return boolean
     */
    public static function isInstallationInProgress()
    {
        return self::$installationStatus;
    }

    /**
     * Drop Lengow tables
     *
     * @return boolean
     */
    public static function dropTable()
    {
        foreach (self::$tables as $table) {
            Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.$table);
        }
        return true;
    }

    /**
     * Save Override directory
     */
    public static function saveOverride()
    {
        $directoryBackup = _PS_MODULE_LENGOW_DIR_ . 'backup/';
        $directory = _PS_MODULE_LENGOW_DIR_ . 'override/';
        if (file_exists($directory)) {
            $listFile = array_diff(scandir($directory), array('..', '.'));
            if (count($listFile) > 0) {
                if (!file_exists($directoryBackup . 'override')) {
                    mkdir($directoryBackup.'override', 0755);
                }
                foreach ($listFile as $file) {
                    copy($directory.$file, $directoryBackup.'override/'.$file);
                }
            }
        }
    }

    /**
     * Delete all lengow config files
     */
    public static function removeConfigFiles()
    {
        $configFiles = array();
        $files = scandir(_PS_MODULE_LENGOW_DIR_);
        foreach ($files as $file) {
            if (preg_match('/^config[_a-zA-Z]*\.xml$/', $file)) {
                $configFiles[] = $file;
            }
        }
        if (count($configFiles) > 0) {
            self::removeFiles($configFiles);
        }
    }

    /**
     * Delete old files
     *
     * @param array $listFiles list of files to delete
     */
    public static function removeFiles($listFiles)
    {
        foreach ($listFiles as $file) {
            $filePath = _PS_MODULE_LENGOW_DIR_.$file;
            if (file_exists($filePath)) {
                if (is_dir($filePath)) {
                    self::deleteDir($filePath);
                } else {
                    unlink($filePath);
                }
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
        if (Tools::substr($dirPath, 0, $length) != _PS_MODULE_LENGOW_DIR_) {
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
    }

    /**
     * Create tab image for version 1.5
     */
    public static function createTabImage()
    {
        $filePath = _PS_MODULE_LENGOW_DIR_.'views/img/AdminLengowHome.gif';
        $fileDest = _PS_MODULE_LENGOW_DIR_.'AdminLengowHome.gif';
        if (!file_exists($fileDest) && LengowMain::compareVersion('1.5') == 0) {
            copy($filePath, $fileDest);
        }
    }
}
