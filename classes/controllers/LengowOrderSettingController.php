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

        $default_country = Configuration::get('PS_COUNTRY_DEFAULT');
        $carriers = LengowCarrier::getActiveCarriers();
        $countries = LengowCarrierCountry::getCountries();
        $listCarrier = LengowCarrierCountry::listCarrierByCountry();
        $mkp_carriers = LengowCarrier::getListMarketplaceCarrier();
        $id_countries = LengowCarrierCountry::getIdCountries($mkp_carriers);

        $carrierItem= array();
        $carrierCountry = array();
        foreach ($listCarrier as $row) {
            $carrierCountry[$row['id_country']] = $row;
        }
        foreach ($mkp_carriers as $row) {
            $carrierItem[$row['id_country']] = $row;

        }
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

        $this->context->smarty->assign('default_country', $default_country);
        $this->context->smarty->assign('carriers', $carriers);
        $this->context->smarty->assign('carrierCountry', $carrierCountry);
        $this->context->smarty->assign('carrierItem', $carrierItem);
        $this->context->smarty->assign('countries', $countries);
        $this->context->smarty->assign('mkp_carriers', $mkp_carriers);
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
        $default_carriers = Tools::getValue('default_carrier');
        $default_marketplace_carriers = Tools::getValue('default_marketplace_carrier');
        $action = Tools::getValue('action');

        switch ($action) {
            case 'add_country':
                $id_country = Tools::getValue('id_country');

                LengowCarrierCountry::insert($id_country);
                LengowCarrier::insert($id_country);

                $carriers = LengowCarrier::getActiveCarriers($id_country);

                $countries = LengowCarrierCountry::getCountries();

                $mkp_carriers = LengowCarrier::getListMarketplaceCarrier($id_country);
                foreach ($mkp_carriers as $row) {
                    $carrierItem[$row['id_country']] = $row;
                }
                $id_countries = LengowCarrierCountry::getIdCountries($mkp_carriers);


                $this->context->smarty->assign('carriers', $carriers);
                $this->context->smarty->assign('carrierItem', $carrierItem);
                $this->context->smarty->assign('countries', $countries);
                $this->context->smarty->assign('mkp_carriers', $mkp_carriers);
                $this->context->smarty->assign('id_countries', $id_countries);
                $this->context->smarty->assign('default_country', $default_country);

                $module = Module::getInstanceByName('lengow');

                $display_marketplace_carrier = $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/lengow_order_setting/helpers/view/marketplace_carrier.tpl');

                echo '$("#select_country option:selected").remove();';
                echo '$("#marketplace_country").append("'.preg_replace('/\r|\n/', '', addslashes($display_marketplace_carrier)).'");';
                exit();
                break;
            case 'delete_country':
                 $id_country = Tools::getValue('id_country');

                LengowCarrierCountry::delete($id_country);

                $countries = LengowCarrierCountry::getCountries();



                LengowCarrier::deleteMarketplaceCarrier($id_country);

                $mkp_carriers = LengowCarrier::getListMarketplaceCarrier($id_country);
                foreach ($mkp_carriers as $row) {
                    $carrierItem[$row['id_country']] = $row;
                }
                $id_countries = LengowCarrierCountry::getIdCountries($mkp_carriers);
                $this->context->smarty->assign('countries', $countries);
                $this->context->smarty->assign('id_countries', $id_countries);
                $this->context->smarty->assign('mkp_carriers', $mkp_carriers);
                $this->context->smarty->assign('default_country', $default_country);


                $module = Module::getInstanceByName('lengow');
                $display_countries = $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/lengow_order_setting/helpers/view/select_country.tpl');

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

                $form->postProcess(
                    array(
                        'LENGOW_IMPORT_CARRIER_MP_ENABLED',
                        'LENGOW_IMPORT_SHIP_MP_ENABLED',
                        'LENGOW_IMPORT_STOCK_SHIP_MP'
                    )
                );
                break;
            default:
                LengowCarrierCountry::createDefaultCarrier();
                LengowCarrierCountry::listCarrierByCountry();
                break;
        }
        
    }
}
