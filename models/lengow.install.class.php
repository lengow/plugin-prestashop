<?php
/**
 * Copyright 2014 Lengow SAS.
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
 *  @author    Team Connector <team-connector@lengow.com>
 *  @copyright 2016 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * The Lengow Import Class.
 *
 */


$sep = DIRECTORY_SEPARATOR;
require_once dirname(__FILE__).$sep.'..'.$sep.'loader.php';

loadFile('export');

class LengowInstall
{

    var $lengowModule;

    static private $_tabs = array(
        'Lengow' => 'AdminLengow',
        'Logs import Lengow' => 'AdminLengowLog',
    );

    public function __construct($module)
    {
        $this->lengowModule = $module;
    }


    public function install()
    {
        $this->createTab();
        $this->setDefaultValues();
        $this->addStatusError();
        return true;
    }

    public function uninstall()
    {
        $configurations = array(
            'LENGOW_LOGO_URL',
            'LENGOW_AUTHORIZED_IP',
            'LENGOW_TRACKING',
            'LENGOW_ID_CUSTOMER',
            'LENGOW_ID_GROUP',
            'LENGOW_TOKEN',
            'LENGOW_EXPORT_SELECTION',
            'LENGOW_EXPORT_NEW',
            'LENGOW_EXPORT_ALL_VARIATIONS',
            'LENGOW_EXPORT_FULLNAME',
            'LENGOW_EXPORT_FIELDS',
            'LENGOW_ORDER_ID_PROCESS',
            'LENGOW_ORDER_ID_SHIPPED',
            'LENGOW_ORDER_ID_CANCEL',
            'LENGOW_IMAGE_TYPE',
            'LENGOW_IMAGES_COUNT',
            'LENGOW_IMPORT_METHOD_NAME',
            'LENGOW_IMPORT_FORCE_PRODUCT',
            'LENGOW_IMPORT_DAYS',
            'LENGOW_EXPORT_FEATURES',
            'LENGOW_EXPORT_FORMAT',
            'LENGOW_EXPORT_FILE',
            'LENGOW_CARRIER_DEFAULT',
            'LENGOW_IMPORT_CARRIER_DEFAULT',
            'LENGOW_FLOW_DATA',
            'LENGOW_MIGRATE',
            'LENGOW_CRON',
            'LENGOW_DEBUG',
            'LENGOW_IMPORT_FAKE_EMAIL',
            'LENGOW_MP_SHIPPING_METHOD',
            'LENGOW_REPORT_MAIL',
            'LENGOW_IMPORT_SINGLE',
            'LENGOW_EXPORT_TIMEOUT',
            'LENGOW_EMAIL_ADDRESS',
            'LENGOW_ORDER_ID_SHIPPEDBYMP',
            'LENGOW_CRON_EDITOR'
        );
        foreach($configurations as $configuration){
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
        foreach (self::$_tabs as $name => $controllerName) {
            if (_PS_VERSION_ < '1.5') {
                $tab_name = $controllerName . "14";
            } else {
                $tab_name = $controllerName;
            }

            if (Tab::getIdFromClassName($tab_name) !== false) {
                continue;
            }

            $tab = new Tab();
            if (_PS_VERSION_ < '1.5') {
                $tab->class_name = $controllerName . "14";
                $tab->position = 10;
                $tab->id_parent = 1;
            } else {
                $tab->class_name = $controllerName;
                $tab->position = 1;
                $tab->id_parent = 9;
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

        foreach (self::$_tabs as $name => $controllerName) {
            if (_PS_VERSION_ < '1.5') {
                $tab_name = $controllerName . "14";
            } else {
                $tab_name = $controllerName;
            }
            if (_PS_VERSION_ >= '1.5') {
                $tab = Tab::getInstanceFromClassName($tab_name);
            } else {
                $tab_id = Tab::getIdFromClassName($tab_name);
                $tab = new Tab($tab_id);
            }
            if ($tab->id != 0) {
                $tab->delete();
            }
            //LengowCore::log('Uninstall tab '.$name, null, -1);
        }
        return true;
    }

    private static function setDefaultValues()
    {
        return
            Configuration::updateValue('LENGOW_AUTHORIZED_IP', $_SERVER['REMOTE_ADDR']) &&
            Configuration::updateValue('LENGOW_TRACKING', '') &&
            Configuration::updateValue('LENGOW_ID_CUSTOMER', '') &&
            Configuration::updateValue('LENGOW_ID_GROUP', '') &&
            Configuration::updateValue('LENGOW_TOKEN', '') &&
            Configuration::updateValue('LENGOW_EXPORT_SELECTION', false) &&
            Configuration::updateValue('LENGOW_EXPORT_DISABLED', false) &&
            Configuration::updateValue('LENGOW_EXPORT_NEW', false) &&
            Configuration::updateValue('LENGOW_EXPORT_ALL_VARIATIONS', true) &&
            Configuration::updateValue('LENGOW_EXPORT_FULLNAME', true) &&
            Configuration::updateValue('LENGOW_EXPORT_FEATURES', false) &&
            Configuration::updateValue('LENGOW_EXPORT_FORMAT', 'csv') &&
            Configuration::updateValue('LENGOW_EXPORT_FIELDS', Tools::jsonEncode(LengowExport::$DEFAULT_FIELDS)) &&
            Configuration::updateValue('LENGOW_IMAGE_TYPE', 3) &&
            Configuration::updateValue('LENGOW_IMAGES_COUNT', 3) &&
            Configuration::updateValue('LENGOW_ORDER_ID_PROCESS', 2) &&
            Configuration::updateValue('LENGOW_ORDER_ID_SHIPPED', 4) &&
            Configuration::updateValue('LENGOW_ORDER_ID_CANCEL', 6) &&
            Configuration::updateValue('LENGOW_IMPORT_METHOD_NAME', false) &&
            Configuration::updateValue('LENGOW_IMPORT_FORCE_PRODUCT', false) &&
            Configuration::updateValue('LENGOW_IMPORT_DAYS', 3) &&
            Configuration::updateValue('LENGOW_FORCE_PRICE', true) &&
            Configuration::updateValue('LENGOW_CARRIER_DEFAULT', Configuration::get('PS_CARRIER_DEFAULT')) &&
            Configuration::updateValue('LENGOW_IMPORT_CARRIER_DEFAULT', Configuration::get('PS_CARRIER_DEFAULT')) &&
            Configuration::updateValue('LENGOW_FLOW_DATA', '') &&
            Configuration::updateValue('LENGOW_MIGRATE', false) &&
            Configuration::updateValue('LENGOW_MP_CONF', false) &&
            Configuration::updateValue('LENGOW_CRON', false) &&
            Configuration::updateValue('LENGOW_FEED_MANAGEMENT', false) &&
            Configuration::updateValue('LENGOW_DEBUG', false) &&
            Configuration::updateValue('LENGOW_IMPORT_FAKE_EMAIL', false) &&
            Configuration::updateValue('LENGOW_REPORT_MAIL', true) &&
            Configuration::updateValue('LENGOW_EXPORT_TIMEOUT', 0) &&
            Configuration::updateValue('LENGOW_IMPORT_SINGLE', version_compare(_PS_VERSION_, '1.5.2', '>') && version_compare(_PS_VERSION_, '1.5.5', '<')) &&
            Configuration::updateValue('LENGOW_EMAIL_ADDRESS', '') &&
            Configuration::updateValue('LENGOW_ORDER_ID_SHIPPEDBYMP', 4) &&
            Configuration::updateValue('LENGOW_CRON_EDITOR', false) &&
            Configuration::updateValue('LENGOW_IMPORT_SHIPPED_BY_MP', false);
    }


    /**
     * Add error status to reimport order
     *
     * @return void
     */
    public function addStatusError()
    {
        // Add Lengow order error status
        if (_PS_VERSION_ >= '1.5')
        {
            $states = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'order_state WHERE module_name = \''.$this->lengowModule->name.'\'');
            if (empty($states))
            {
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
                foreach ($languages as $language)
                {
                    if ($language['iso_code'] == 'fr')
                        $lengow_state->name[$language['id_lang']] = 'Erreur technique - Lengow';
                    else
                        $lengow_state->name[$language['id_lang']] = 'Technical error - Lengow';
                }
                $lengow_state->add();
                Configuration::updateValue('LENGOW_STATE_ERROR', $lengow_state->id);
            }
            else
                Configuration::updateValue('LENGOW_STATE_ERROR', $states[0]['id_order_state']);
        }
        else
        {
            $states = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'order_state_lang WHERE name = \'Erreur technique - Lengow\' LIMIT 1');
            if (empty($states))
            {
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
                foreach ($languages as $language)
                    $lengow_state->name[$language['id_lang']] = 'Erreur technique - Lengow';
                $lengow_state->add();
                Configuration::updateValue('LENGOW_STATE_ERROR', $lengow_state->id);
            }
        }
    }



}
