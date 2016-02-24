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

/**
 * The Lengow Import Class.
 *
 */
class LengowInstall
{

    private $lengowModule;
    private $lengowHook;
    protected static $installationStatus;

    static private $tabs = array(
        'Home' => array('name' => 'AdminLengowHome', 'active' => false),
        'Product' => array('name' => 'AdminLengowFeed', 'active' => false),
        'Orders' => array('name' => 'AdminLengowOrder', 'active' => false),
        'Parameters' => array('name' => 'AdminLengowOrderSetting', 'active' => false),
        'Help' => array('name' => 'AdminLengowHelp', 'active' => false),
        'MainSetting' => array('name' => 'AdminLengowMainSetting', 'active' => false)
    );

    static public $tables = array(
        'lengow_actions',
        'lengow_carrier_country',
        'lengow_logs_import',
        'lengow_marketplace_carrier',
        'lengow_orders',
        'lengow_order_line',
        'lengow_product',
    );

    public function __construct($module)
    {
        $this->lengowModule = $module;
        $this->lengowHook = new LengowHook($module);
    }

    public function reset()
    {
        return LengowConfiguration::resetAll(true);
    }

    public function install()
    {
        return $this->createTab() &&
        $this->lengowHook->registerHooks() &&
        $this->setDefaultValues() &&
        $this->addStatusError() &&
        $this->update();
    }

    public function uninstall()
    {
        return $this->uninstallTab();
    }

    /**
     * Add admin Tab (Controller)
     *
     * @return boolean Result of add tab on database.
     */
    private function createTab()
    {
        if (LengowMain::compareVersion()) {
            $tab_parent = new Tab();
            $tab_parent->name[Configuration::get('PS_LANG_DEFAULT')] = 'Lengow';
            $tab_parent->module = 'lengow';
            $tab_parent->class_name = 'AdminLengowHome';
            $tab_parent->id_parent = 0;
            $tab_parent->add();

        } else {
            $tab_parent = new Tab(Tab::getIdFromClassName('AdminCatalog'));
            $tab = new Tab();
            $tab->name[Configuration::get('PS_LANG_DEFAULT')] = 'Lengow';
            $tab->module = 'lengow';
            $tab->class_name = 'AdminLengowHome14';
            $tab->id_parent = $tab_parent->id;
            $tab->add();

            $tab_parent = $tab;
        }
        foreach (self::$tabs as $name => $values) {
            $tab = new Tab();
            if (_PS_VERSION_ < '1.5') {
                $tab->class_name = $values['name'] . "14";
                $tab->id_parent = $tab_parent->id;
            } else {
                $tab->class_name = $values['name'];
                $tab->id_parent = $tab_parent->id;
                $tab->active = $values['active'];
            }
            $tab->module = $this->lengowModule->name;
            $tab->name[Configuration::get('PS_LANG_DEFAULT')] = $this->lengowModule->l($name);
            $tab->add();
        }
        return true;
    }

    /**
     * Remove admin tab
     *
     * @return boolean Result of tab uninstallation
     */
    private static function uninstallTab()
    {
        $sql = 'SELECT `id_tab`, `class_name` FROM `' . _DB_PREFIX_ . 'tab` WHERE `module` = \'lengow\'';
        $tabs = Db::getInstance()->executeS($sql);
        // remove all tabs Lengow
        foreach ($tabs as $value) {
            $tab = new Tab((int)$value['id_tab']);
            if ($tab->id != 0) {
                $result = $tab->delete();
            }
            LengowMain::log('Install', 'Uninstall tab ' . $value['class_name']);
        }
        return true;
    }

    private static function setDefaultValues()
    {
        return LengowConfiguration::resetAll(false);
    }

    /**
     * Add error status to reimport order
     *
     * @return void
     */
    public function addStatusError()
    {
        // Add Lengow order error status
        if (_PS_VERSION_ >= '1.5') {
            $states = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'order_state
                WHERE module_name = \'' . $this->lengowModule->name . '\'');
            if (empty($states)) {
                $lengow_state = new OrderState();
                $lengow_state->send_email = false;
                $lengow_state->module_name = $this->lengowModule->name;
                $lengow_state->invoice = false;
                $lengow_state->delivery = false;
                $lengow_state->shipped = false;
                $lengow_state->paid = false;
                $lengow_state->unremovable = false;
                $lengow_state->logable = false;
                $lengow_state->color = '#205985';
                $lengow_state->name[1] = 'Erreur technique - Lengow';
                $languages = Language::getLanguages(false);
                foreach ($languages as $language) {
                    if ($language['iso_code'] == 'fr') {
                        $lengow_state->name[$language['id_lang']] = 'Erreur technique - Lengow';
                    } else {
                        $lengow_state->name[$language['id_lang']] = 'Technical error - Lengow';
                    }
                }
                $lengow_state->add();
                Configuration::updateValue('LENGOW_STATE_ERROR', $lengow_state->id);
            } else {
                Configuration::updateValue('LENGOW_STATE_ERROR', $states[0]['id_order_state']);
            }
        } else {
            $states = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'order_state_lang
                WHERE name = \'Erreur technique - Lengow\' LIMIT 1');
            if (empty($states)) {
                $lengow_state = new OrderState();
                $lengow_state->send_email = false;
                $lengow_state->invoice = false;
                $lengow_state->delivery = false;
                $lengow_state->shipped = false;
                $lengow_state->paid = false;
                $lengow_state->unremovable = false;
                $lengow_state->logable = false;
                $lengow_state->color = '#205985';
                $lengow_state->name[1] = 'Erreur technique - Lengow';
                $languages = Language::getLanguages(false);
                foreach ($languages as $language) {
                    $lengow_state->name[$language['id_lang']] = 'Erreur technique - Lengow';
                }
                $lengow_state->add();
                Configuration::updateValue('LENGOW_STATE_ERROR', $lengow_state->id);
            } else {
                Configuration::updateValue('LENGOW_STATE_ERROR', $states[0]['id_order_state']);
            }
        }
        return true;
    }

    /**
     * Update process
     *
     * @return void
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
        // update lengow tabs
        $this->uninstallTab();
        $this->createTab();
        // update lengow version
        Configuration::updateValue('LENGOW_VERSION', $numberVersion);
        self::setInstallationStatus(false);
        return true;
    }

    /**
     * Checks if a field exists in BDD
     *
     * @param string $table
     * @param string $field
     *
     * @return boolean
     */
    public static function checkFieldExists($table, $field)
    {
        $sql = 'SHOW COLUMNS FROM ' . _DB_PREFIX_ . $table . ' LIKE \'' . $field . '\'';
        $result = Db::getInstance()->executeS($sql);
        $exists = count($result) > 0 ? true : false;
        return $exists;
    }

    /**
     * Checks if a field exists in BDD and Dropped It
     *
     * @param string $table
     * @param string $field
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

    public static function renameConfigurationKey($oldName, $newName)
    {
        $tempValue = LengowConfiguration::get($oldName);
        LengowConfiguration::updatevalue($newName, $tempValue);
        LengowConfiguration::deleteByName($oldName);
    }

    /**
     * v3
     * Set Installation Status
     *
     * @param boolean $status Installation Status
     */
    public static function setInstallationStatus($status)
    {
        self::$installationStatus = $status;
    }

    /**
     * v3
     * Is Installation In Progress
     * @return boolean
     */
    public static function isInstallationInProgress()
    {
        return self::$installationStatus;
    }

    /**
     * v3
     * Drop Lengow tables
     * @return bool
     */
    public static function dropTable()
    {
        foreach (self::$tables as $table) {
            Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.$table);
        }
        return true;
    }
}
