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
 * @category  Lengow_Controller
 * @package   LengowOrderSettingController
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

class LengowOrderSettingController extends LengowController
{
    /**
     * Display data page
     */
    public function display()
    {
        $importUrl = LengowMain::getImportUrl();
        $this->context->smarty->assign('import_url', $importUrl);
        $defaultCountry = Configuration::get('PS_COUNTRY_DEFAULT');
        $countries = LengowCarrierCountry::getCountries();
        $listCarrier = LengowCarrierCountry::listCarrierByCountry();
        $idCountries = LengowCarrierCountry::getIdCountries($listCarrier);
        $defaultCarrierCountries = array();
        $listCarrierByCountry = array();
        foreach ($idCountries as $idCountry) {
            foreach ($listCarrier as $carrier) {
                if ($carrier['id_country'] == $idCountry) {
                    $defaultCarrierCountries[$idCountry]['lengow_country_id'] = $carrier['id'];
                    $defaultCarrierCountries[$idCountry]['id_carrier'] = $carrier['id_carrier'];
                    $defaultCarrierCountries[$idCountry]['iso_code'] =  $carrier['iso_code'];
                    $defaultCarrierCountries[$idCountry]['name'] =  $carrier['name'];
                }
            }
            $listCarrierByCountry[$idCountry] = LengowCarrier::getActiveCarriers($idCountry);
        }
        $mkpCarriers = LengowCarrier::getListMarketplaceCarrier();
        $marketplaceCarriers = array();
        if (count($mkpCarriers) >0) {
            foreach ($mkpCarriers as $row) {
                $marketplaceCarriers[$row['id_country']][]= $row;
            }
        }
        $form = new LengowConfigurationForm(array("fields" => LengowConfiguration::getKeys()));
        $matching = $form->buildInputs(
            array(
                'LENGOW_ORDER_ID_PROCESS',
                'LENGOW_ORDER_ID_SHIPPED',
                'LENGOW_ORDER_ID_CANCEL',
                'LENGOW_ORDER_ID_SHIPPEDBYMP'
            )
        );
        $importParams = $form->buildInputs(
            array(
                'LENGOW_IMPORT_DAYS',
                'LENGOW_IMPORT_SHIP_MP_ENABLED',
                'LENGOW_IMPORT_STOCK_SHIP_MP'
            )
        );
        $cronParam = $form->buildInputs(array('LENGOW_CRON_ENABLED'));
        $form = LengowCron::getFormCron();
        $this->context->smarty->assign('formCron', $form);
        $this->context->smarty->assign('default_country', $defaultCountry);
        $this->context->smarty->assign('countries', $countries);
        $this->context->smarty->assign('cron_param', $cronParam);
        $this->context->smarty->assign('defaultCarrierCountries', $defaultCarrierCountries);
        $this->context->smarty->assign('listCarrierByCountry', $listCarrierByCountry);
        $this->context->smarty->assign('marketplace_carriers', $marketplaceCarriers);
        $this->context->smarty->assign('id_countries', $idCountries);
        $this->context->smarty->assign('matching', $matching);
        $this->context->smarty->assign('import_params', $importParams);
        parent::display();
    }

    /**
     * Process Post Parameters
     */
    public function postProcess()
    {
        $defaultCountry = Configuration::get('PS_COUNTRY_DEFAULT');
        if (Tools::getIsset('default_carrier')) {
            $defaultCarriers = Tools::getValue('default_carrier');
        } else {
            $defaultCarriers = array();
        }
        if (Tools::getIsset('default_marketplace_carrier')) {
            $defaultMarketplaceCarriers = Tools::getValue('default_marketplace_carrier');
        } else {
            $defaultMarketplaceCarriers = array();
        }
        $action = Tools::getValue('action');

        switch ($action) {
            case 'add_country':
                $idCountry = Tools::getValue('id_country');
                LengowCarrierCountry::insert($idCountry);
                LengowCarrier::insert($idCountry);
                $countries = LengowCarrierCountry::getCountries();
                $mkpCarriers = LengowCarrier::getListMarketplaceCarrier();
                $marketplaceCarriers = array();
                if (count($mkpCarriers) >0) {
                    foreach ($mkpCarriers as $row) {
                        $marketplaceCarriers[$row['id_country']][]= $row;
                    }
                }
                $newMkpCarriers = LengowCarrier::getListMarketplaceCarrier();
                $idCountries = LengowCarrierCountry::getIdCountries($newMkpCarriers);
                $listCarrier = LengowCarrierCountry::listCarrierByCountry();
                $carrierItem= array();
                $listCarrierByCountry= array();
                $defaultCarrierCountries = array();
                $listCarrierByCountry[$idCountry] = LengowCarrier::getActiveCarriers($idCountry);
                foreach ($listCarrier as $carrier) {
                    if ($carrier['id_country'] == $idCountry) {
                        $defaultCarrierCountries[$idCountry]['lengow_country_id'] = $carrier['id'];
                        $defaultCarrierCountries[$idCountry]['id_carrier'] = $carrier['id_carrier'];
                        $defaultCarrierCountries[$idCountry]['iso_code'] =  $carrier['iso_code'];
                        $defaultCarrierCountries[$idCountry]['name'] =  $carrier['name'];
                    }
                }
                $this->context->smarty->assign('defaultCarrierCountries', $defaultCarrierCountries);
                $this->context->smarty->assign('listCarrierByCountry', $listCarrierByCountry);
                $this->context->smarty->assign('carrierItem', $carrierItem);
                $this->context->smarty->assign('countries', $countries);
                $this->context->smarty->assign('marketplace_carriers', $marketplaceCarriers);
                $this->context->smarty->assign('id_countries', $idCountries);
                $this->context->smarty->assign('default_country', $defaultCountry);
                $module = Module::getInstanceByName('lengow');
                $displayMarketplaceCarrier = $module->display(
                    _PS_MODULE_LENGOW_DIR_,
                    'views/templates/admin/lengow_order_setting/helpers/view/marketplace_carrier.tpl'
                );
                $displayCountries = $module->display(
                    _PS_MODULE_LENGOW_DIR_,
                    'views/templates/admin/lengow_order_setting/helpers/view/select_country.tpl'
                );
                $data = array();
                $data['marketplace_carrier'] = preg_replace('/\r|\n/', '', $displayMarketplaceCarrier);
                $data['countries'] = $displayCountries;
                echo Tools::jsonEncode($data);
                exit();
            case 'delete_country':
                $idCountry = Tools::getValue('id_country');
                LengowCarrierCountry::delete($idCountry);
                $countries = LengowCarrierCountry::getCountries();
                LengowCarrier::deleteMarketplaceCarrier($idCountry);
                $mkpCarriers = LengowCarrier::getListMarketplaceCarrier();
                foreach ($mkpCarriers as $row) {
                    $carrierItem[$row['id_country']] = $row;
                }
                $idCountries = LengowCarrierCountry::getIdCountries($mkpCarriers);
                $this->context->smarty->assign('countries', $countries);
                $this->context->smarty->assign('id_countries', $idCountries);
                $this->context->smarty->assign('mkp_carriers', $mkpCarriers);
                $this->context->smarty->assign('default_country', $defaultCountry);
                $module = Module::getInstanceByName('lengow');
                $displayCountries = $module->display(
                    _PS_MODULE_LENGOW_DIR_,
                    'views/templates/admin/lengow_order_setting/helpers/view/select_country.tpl'
                );
                $data = array();
                $data['countries'] = $displayCountries;
                $data['id_country'] = $idCountry;
                echo Tools::jsonEncode($data);
                exit();
            case 'process':
                foreach ($defaultCarriers as $key => $value) {
                    if (_PS_VERSION_ < '1.5') {
                        Db::getInstance()->autoExecute(
                            _DB_PREFIX_.'lengow_carrier_country',
                            array('id_carrier' => (int)$value > 0  ? (int)$value : null),
                            'UPDATE',
                            'id = '.(int)$key,
                            0,
                            true,
                            true
                        );
                    } else {
                        Db::getInstance()->update(
                            'lengow_carrier_country',
                            array('id_carrier' => (int)$value > 0  ? (int)$value : null),
                            'id = '.(int)$key,
                            0,
                            true,
                            true
                        );
                    }
                }
                foreach ($defaultMarketplaceCarriers as $key => $value) {
                    if (_PS_VERSION_ < '1.5') {
                        Db::getInstance()->autoExecute(
                            _DB_PREFIX_.'lengow_marketplace_carrier',
                            array('id_carrier' => (int)$value > 0  ? (int)$value : null),
                            'UPDATE',
                            'id = '.(int)$key,
                            0,
                            true,
                            true
                        );
                    } else {
                        Db::getInstance()->update(
                            'lengow_marketplace_carrier',
                            array('id_carrier' => (int)$value > 0  ? (int)$value : null),
                            'id = '.(int)$key,
                            0,
                            true,
                            true
                        );
                    }
                }
                $form = new LengowConfigurationForm(array("fields" => LengowConfiguration::getKeys()));
                if (isset($_REQUEST['LENGOW_CRON_ENABLED'])) {
                    LengowCron::addCronTasks();
                } else {
                    $moduleCron = Module::getInstanceByName('cronjobs');
                    if ($moduleCron && $moduleCron->active) {
                        LengowCron::removeCronTasks();
                    }
                }
                $form->postProcess(
                    array(
                        'LENGOW_IMPORT_SHIP_MP_ENABLED',
                        'LENGOW_IMPORT_STOCK_SHIP_MP',
                        'LENGOW_CRON_ENABLED'
                    )
                );
                break;
            default:
                LengowCarrier::syncListMarketplace();
                LengowCarrierCountry::createDefaultCarrier();
                LengowCarrierCountry::listCarrierByCountry();
                break;
        }
        
    }
}
