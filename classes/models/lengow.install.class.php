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

    static private $tabs = array(
        'Home' => array('name' => 'AdminLengowHome', 'active' => true),
        'Product' => array('name' => 'AdminLengowFeed', 'active' => true),
        'Orders' => array('name' => 'AdminLengowOrder', 'active' => true),
        'Parameters' => array('name' => 'AdminLengowOrderSetting', 'active' => false)
    );

    public function __construct($module)
    {
        $this->lengowModule = $module;
        $this->lengowHook = new LengowHook($module);

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
        $configurations = array(
            'LENGOW_VERSION',
            'LENGOW_LOGO_URL',
            'LENGOW_AUTHORIZED_IP',
            'LENGOW_TRACKING_ENABLED',
            'LENGOW_TRACKING_ID',
            'LENGOW_ACCOUNT_ID',
            'LENGOW_ACCESS_TOKEN',
            'LENGOW_SECRET_TOKEN',
            'LENGOW_SHOP_TOKEN',
            'LENGOW_GLOBAL_TOKEN',
            'LENGOW_SHOP_ACTIVE',
            'LENGOW_CARRIER_DEFAULT',
            'LENGOW_EXPORT_SELECTION_ENABLED',
            'LENGOW_EXPORT_VARIATION_ENABLED',
            'LENGOW_EXPORT_OUT_STOCK',
            'LENGOW_EXPORT_FORMAT',
            'LENGOW_EXPORT_FILE_ENABLED',
            'LENGOW_ORDER_ID_PROCESS',
            'LENGOW_ORDER_ID_SHIPPED',
            'LENGOW_ORDER_ID_CANCEL',
            'LENGOW_ORDER_ID_SHIPPEDBYMP',
            'LENGOW_IMPORT_FORCE_PRODUCT',
            'LENGOW_IMPORT_DAYS',
            'LENGOW_IMPORT_PROCESSING_FEE',
            'LENGOW_IMPORT_CARRIER_DEFAULT',
            'LENGOW_IMPORT_PREPROD_ENABLED',
            'LENGOW_IMPORT_FAKE_EMAIL',
            'LENGOW_IMPORT_CARRIER_MP_ENABLED',
            'LENGOW_IMPORT_SHIP_MP_ENABLED',
            'LENGOW_IMPORT_SINGLE_ENABLED',
            'LENGOW_IMPORT_IN_PROGRESS',
            'LENGOW_REPORT_MAIL_ENABLED',
            'LENGOW_REPORT_MAIL_ADDRESS',
            'LENGOW_STATE_ERROR',
            'LENGOW_CRON_ENABLED',
            'LENGOW_LAST_IMPORT_CRON',
            'LENGOW_LAST_EXPORT',
            'LENGOW_LAST_IMPORT_MANUAL'
            );
        foreach ($configurations as $configuration) {
            Configuration::deleteByName($configuration);
        }
        $this->uninstallTab();
        return true;
    }

    /**
     * Add admin Tab (Controller)
     *
     * @return boolean Result of add tab on database.
     */
    private function createTab()
    {

        $tab_parent = new Tab();
        $tab_parent->name[Configuration::get('PS_LANG_DEFAULT')] = 'Lengow';
        $tab_parent->module = 'lengow';

        if (_PS_VERSION_ < '1.5') {
            $tab_parent->class_name = 'AdminLengowHome14';
        } else {
            $tab_parent->class_name = 'AdminLengowHome';
        }

        $tab_parent->id_parent = 0;
        $tab_parent->add();

        foreach (self::$tabs as $name => $values) {
            if (_PS_VERSION_ < '1.5') {
                $tab_name = $values['name'] . "14";
            } else {
                $tab_name = $values['name'];
            }

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
            LengowMain::log('Uninstall tab '.$value['class_name']);
        }
        return true;
    }

    private static function setDefaultValues()
    {
        return
        Configuration::updateValue('LENGOW_AUTHORIZED_IP', $_SERVER['REMOTE_ADDR']) &&
        Configuration::updateValue('LENGOW_TRACKING_ENABLED', '') &&
        Configuration::updateValue('LENGOW_EXPORT_SELECTION_ENABLED', false) &&
        Configuration::updateValue('LENGOW_EXPORT_DISABLED', false) &&
        Configuration::updateValue('LENGOW_EXPORT_VARIATION_ENABLED', true) &&
        Configuration::updateValue('LENGOW_EXPORT_FORMAT', 'csv') &&
        Configuration::updateValue('LENGOW_ORDER_ID_PROCESS', 2) &&
        Configuration::updateValue('LENGOW_ORDER_ID_SHIPPED', 4) &&
        Configuration::updateValue('LENGOW_ORDER_ID_CANCEL', 6) &&
        Configuration::updateValue('LENGOW_IMPORT_FORCE_PRODUCT', true) &&
        Configuration::updateValue('LENGOW_IMPORT_DAYS', 5) &&
        Configuration::updateValue('LENGOW_CARRIER_DEFAULT', Configuration::get('PS_CARRIER_DEFAULT')) &&
        Configuration::updateValue('LENGOW_IMPORT_CARRIER_DEFAULT', Configuration::get('PS_CARRIER_DEFAULT')) &&
        Configuration::updateValue('LENGOW_CRON_ENABLED', false) &&
        Configuration::updateValue('LENGOW_IMPORT_PREPROD_ENABLED', false) &&
        Configuration::updateValue('LENGOW_IMPORT_FAKE_EMAIL', false) &&
        Configuration::updateValue('LENGOW_REPORT_MAIL_ENABLED', true) &&
        Configuration::updateValue('LENGOW_REPORT_MAIL_ADDRESS', '') &&
        Configuration::updateValue(
            'LENGOW_IMPORT_SINGLE_ENABLED',
            version_compare(_PS_VERSION_, '1.5.2', '>') && version_compare(_PS_VERSION_, '1.5.5', '<')
        ) &&
        Configuration::updateValue('LENGOW_ORDER_ID_SHIPPEDBYMP', 4) &&
        Configuration::updateValue('LENGOW_IMPORT_SHIP_MP_ENABLED', false);
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
            $states = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'order_state
                WHERE module_name = \''.$this->lengowModule->name.'\'');
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
            $states = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'order_state_lang
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
        $installation = true;
        $upgradeFiles = array_diff(scandir(_PS_MODULE_LENGOW_DIR_.'upgrade'), array('..', '.'));
        foreach ($upgradeFiles as $file) {
            include _PS_MODULE_LENGOW_DIR_.'upgrade/'.$file;
            $numberVersion = preg_replace('/update_|\.php$/', '', $file);
        }
        // update lengow tabs
        $this->uninstallTab();
        $this->createTab();
        // update lengow version
        Configuration::updateValue('LENGOW_VERSION', $numberVersion);
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
        $sql = 'SHOW COLUMNS FROM '._DB_PREFIX_.$table.' LIKE \''.$field.'\'';
        $result = Db::getInstance()->executeS($sql);
        $exists = count($result) > 0 ? true : false;
        return $exists;
    }
}
