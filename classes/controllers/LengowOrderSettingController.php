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

if (!defined('_PS_VERSION_')) {
    exit;
}


class LengowOrderSettingController extends LengowController
{
    /**
     * Display data page
     */
    public function display()
    {

        $import_url = LengowMain::getImportUrl();

        $this->context->smarty->assign('import_url', $import_url);

        $default_country = Configuration::get('PS_COUNTRY_DEFAULT');

        $countries = LengowCarrierCountry::getCountries();
        $listCarrier = LengowCarrierCountry::listCarrierByCountry();
        $id_countries = LengowCarrierCountry::getIdCountries($listCarrier);

        $carrierItem= array();
        $defaultCarrierCountries = array();
        $listCarrierByCountry = array();
        foreach ($id_countries as $id_country) {
            foreach ($listCarrier as $carrier) {
                if ($carrier['id_country'] == $id_country) {
                    $defaultCarrierCountries[$id_country]['lengow_country_id'] = $carrier['id'];
                    $defaultCarrierCountries[$id_country]['id_carrier'] = $carrier['id_carrier'];
                    $defaultCarrierCountries[$id_country]['iso_code'] =  $carrier['iso_code'];
                    $defaultCarrierCountries[$id_country]['name'] =  $carrier['name'];
                }
            }
            $listCarrierByCountry[$id_country] = LengowCarrier::getActiveCarriers($id_country);
        }

        $mkp_carriers = LengowCarrier::getListMarketplaceCarrier();
        if (count($mkp_carriers) >0) {
            foreach ($mkp_carriers as $row) {
                $marketplace_carriers[$row['id_country']][]= $row;
            }
        } else {
            $marketplace_carriers = array();
        }

        $carriers = LengowCarrier::getActiveCarriers();

        $form = new LengowConfigurationForm(
            array(
                "fields" => LengowConfiguration::getKeys(),
            )
        );

        $matching = $form->buildInputs(
            array(
                'LENGOW_ORDER_ID_PROCESS',
                'LENGOW_ORDER_ID_SHIPPED',
                'LENGOW_ORDER_ID_CANCEL',
                'LENGOW_ORDER_ID_SHIPPEDBYMP'
            )
        );

        $matching2 = $form->buildInputs(array('LENGOW_IMPORT_CARRIER_MP_ENABLED'));

        $import_params = $form->buildInputs(
            array(
                'LENGOW_IMPORT_DAYS',
                'LENGOW_IMPORT_SHIP_MP_ENABLED',
                'LENGOW_IMPORT_STOCK_SHIP_MP'
            )
        );
        $cron_param = $form->buildInputs(array('LENGOW_CRON_ENABLED'));

        $form = LengowCron::getFormCron();
        $this->context->smarty->assign('formCron', $form);

        $this->context->smarty->assign('default_country', $default_country);
        $this->context->smarty->assign('countries', $countries);
        $this->context->smarty->assign('cron_param', $cron_param);

        $this->context->smarty->assign('carriers', $carriers);
        $this->context->smarty->assign('defaultCarrierCountries', $defaultCarrierCountries);
        $this->context->smarty->assign('listCarrierByCountry', $listCarrierByCountry);
        $this->context->smarty->assign('marketplace_carriers', $marketplace_carriers);

        $this->context->smarty->assign('id_countries', $id_countries);
        $this->context->smarty->assign('listCarrier', $listCarrier);
        $this->context->smarty->assign('matching', $matching);
        $this->context->smarty->assign('matching2', $matching2);
        $this->context->smarty->assign('import_params', $import_params);
        parent::display();
    }

    public function postProcess()
    {
        $default_country = Configuration::get('PS_COUNTRY_DEFAULT');
        if (Tools::getIsset('default_carrier')) {
            $default_carriers = Tools::getValue('default_carrier');
        } else {
            $default_carriers = array();
        }
        if (Tools::getIsset('default_marketplace_carrier')) {
            $default_marketplace_carriers = Tools::getValue('default_marketplace_carrier');
        } else {
            $default_marketplace_carriers = array();
        }
        $action = Tools::getValue('action');

        switch ($action) {
            case 'add_country':
                $id_country = Tools::getValue('id_country');

                LengowCarrierCountry::insert($id_country);
                LengowCarrier::insert($id_country);

                $carriers = LengowCarrier::getActiveCarriers($id_country);

                $countries = LengowCarrierCountry::getCountries();

                $mkp_carriers = LengowCarrier::getListMarketplaceCarrier();
                if (count($mkp_carriers) >0) {
                    foreach ($mkp_carriers as $row) {
                        $marketplace_carriers[$row['id_country']][]= $row;
                    }
                } else {
                    $marketplace_carriers = array();
                }

                $new_mkp_carriers = LengowCarrier::getListMarketplaceCarrier();
                $id_countries = LengowCarrierCountry::getIdCountries($new_mkp_carriers);
                $listCarrier = LengowCarrierCountry::listCarrierByCountry();

                $carrierItem= array();
                $listCarrierByCountry= array();
                $defaultCarrierCountries = array();
                $listCarrierByCountry[$id_country] = LengowCarrier::getActiveCarriers($id_country);
                foreach ($listCarrier as $carrier) {
                    if ($carrier['id_country'] == $id_country) {
                        $defaultCarrierCountries[$id_country]['lengow_country_id'] = $carrier['id'];
                        $defaultCarrierCountries[$id_country]['id_carrier'] = $carrier['id_carrier'];
                        $defaultCarrierCountries[$id_country]['iso_code'] =  $carrier['iso_code'];
                        $defaultCarrierCountries[$id_country]['name'] =  $carrier['name'];
                    }
                }
                $this->context->smarty->assign('defaultCarrierCountries', $defaultCarrierCountries);
                $this->context->smarty->assign('listCarrierByCountry', $listCarrierByCountry);
                $this->context->smarty->assign('carriers', $carriers);
                $this->context->smarty->assign('carrierItem', $carrierItem);
                $this->context->smarty->assign('countries', $countries);
                $this->context->smarty->assign('marketplace_carriers', $marketplace_carriers);
                $this->context->smarty->assign('id_countries', $id_countries);
                $this->context->smarty->assign('default_country', $default_country);

                $module = Module::getInstanceByName('lengow');

                $display_marketplace_carrier = $module->display(
                    _PS_MODULE_LENGOW_DIR_,
                    'views/templates/admin/lengow_order_setting/helpers/view/marketplace_carrier.tpl'
                );
                $display_countries = $module->display(
                    _PS_MODULE_LENGOW_DIR_,
                    'views/templates/admin/lengow_order_setting/helpers/view/select_country.tpl'
                );
                echo '$("#marketplace_country").append("'.
                    preg_replace('/\r|\n/', '', addslashes($display_marketplace_carrier)).'");';
                echo '$("#select_country").html("'.preg_replace('/\r|\n/', '', addslashes($display_countries)).'");';
                echo 'addScoreCarrier();';
                echo 'lengow_jquery(\'.lengow_select\').select2({ minimumResultsForSearch: 16});';
                exit();
                break;
            case 'delete_country':
                 $id_country = Tools::getValue('id_country');

                LengowCarrierCountry::delete($id_country);

                $countries = LengowCarrierCountry::getCountries();



                LengowCarrier::deleteMarketplaceCarrier($id_country);

                $mkp_carriers = LengowCarrier::getListMarketplaceCarrier();
                foreach ($mkp_carriers as $row) {
                    $carrierItem[$row['id_country']] = $row;
                }
                $id_countries = LengowCarrierCountry::getIdCountries($mkp_carriers);

                $this->context->smarty->assign('countries', $countries);
                $this->context->smarty->assign('id_countries', $id_countries);
                $this->context->smarty->assign('mkp_carriers', $mkp_carriers);
                $this->context->smarty->assign('default_country', $default_country);


                $module = Module::getInstanceByName('lengow');
                $display_countries = $module->display(
                    _PS_MODULE_LENGOW_DIR_,
                    'views/templates/admin/lengow_order_setting/helpers/view/select_country.tpl'
                );

                echo '$("#select_country").html("'.preg_replace('/\r|\n/', '', addslashes($display_countries)).'");';
                echo '$("#lengow_marketplace_carrier_country_'.$id_country.'").remove();';
                exit();
                break;
            case 'process':
                foreach ($default_carriers as $key => $value) {
                    Db::getInstance()->autoExecute(
                        _DB_PREFIX_ . 'lengow_carrier_country',
                        array('id_carrier' => (int)$value > 0  ? (int)$value : null),
                        'UPDATE',
                        'id = '.(int)$key,
                        0,
                        true,
                        true
                    );
                }

                foreach ($default_marketplace_carriers as $key => $value) {
                    Db::getInstance()->autoExecute(
                        _DB_PREFIX_.'lengow_marketplace_carrier',
                        array('id_carrier' => (int)$value > 0  ? (int)$value : null),
                        'UPDATE',
                        'id = '.(int)$key,
                        0,
                        true,
                        true
                    );
                }

                $form = new LengowConfigurationForm(
                    array(
                        "fields" => LengowConfiguration::getKeys(),
                    )
                );

                if (isset($_REQUEST['LENGOW_CRON_ENABLED'])) {
                    $result = LengowCron::addCronTasks();
                    if (!$result) {
                        unset($_REQUEST['LENGOW_CRON_ENABLED']);
                    }
                } else {
                    $moduleCron = Module::getInstanceByName('cronjobs');
                    if ($moduleCron && $moduleCron->active) {
                        LengowCron::removeCronTasks();
                    }
                }
                $form->postProcess(
                    array(
                        'LENGOW_IMPORT_CARRIER_MP_ENABLED',
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
