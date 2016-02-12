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
        $listCarrier = LengowCarrierCountry::listCarrierByCountry();
        $countries = LengowCarrierCountry::getCountries();
        $id_countries = LengowCarrierCountry::getIdCountries($listCarrier);
        $mkp_carriers = LengowCarrier::getListMarketplaceCarrier();

        $form = new LengowConfigurationForm(array(
            "fields" => LengowConfiguration::getKeys(),
            ));

        $matching = $form->buildInputs(array(
            'LENGOW_ORDER_ID_PROCESS',
            'LENGOW_ORDER_ID_SHIPPED',
            'LENGOW_ORDER_ID_CANCEL',
            'LENGOW_ORDER_ID_SHIPPEDBYMP'
            ));

        $matching2 = $form->buildInputs(array('LENGOW_IMPORT_CARRIER_MP_ENABLED'));
        $matching3 = $form->buildInputs(array('LENGOW_IMPORT_DAYS'));

        $this->context->smarty->assign('default_country', $default_country);
        $this->context->smarty->assign('carriers', $carriers);
        $this->context->smarty->assign('listCarrier', $listCarrier);
        $this->context->smarty->assign('countries', $countries);
        $this->context->smarty->assign('id_countries', $id_countries);
        $this->context->smarty->assign('mkp_carriers', $mkp_carriers);
        $this->context->smarty->assign('matching', $matching);
        $this->context->smarty->assign('matching2', $matching2);
        $this->context->smarty->assign('matching3', $matching3);

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

                $db_carrier_country = LengowCarrierCountry::insert($id_country);
                $id_lengow_carrier = $db_carrier_country->insert_ID();


                $itemCarrier = LengowCarrierCountry::listCarrierById($id_lengow_carrier);
                $carriers = LengowCarrier::getActiveCarriers();

                LengowCarrier::insert($id_country);


                $mkp_carriers = LengowCarrier::getListMarketplaceCarrier($id_country);

                $this->context->smarty->assign('itemCarrier', $itemCarrier);
                $this->context->smarty->assign('carriers', $carriers);
                $this->context->smarty->assign('mkp_carriers', $mkp_carriers);
                $this->context->smarty->assign('default_country', $default_country);

                $module = Module::getInstanceByName('lengow');

                $display_default_carrier = $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/lengow_order_setting/helpers/view/default_carrier.tpl');
                $display_marketplace_carrier = $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/lengow_order_setting/helpers/view/marketplace_carrier.tpl');

                echo '$("#add_country").append("'.preg_replace('/\r|\n/', '', addslashes($display_default_carrier)).'");';
                echo '$("#select_country option:selected").remove();';
                echo '$("#add_marketplace_country").append("'.preg_replace('/\r|\n/', '', addslashes($display_marketplace_carrier)).'");';


                exit();
                break;

            case 'delete_country':
                 $id_country = Tools::getValue('id_country');

                LengowCarrierCountry::delete($id_country);

                $countries = LengowCarrierCountry::getCountries();
                $listCarrier = LengowCarrierCountry::listCarrierByCountry();
                $id_countries = LengowCarrierCountry::getIdCountries($listCarrier);

                LengowCarrier::deleteMarketplaceCarrier($id_country);

                $mkp_carriers = LengowCarrier::getListMarketplaceCarrier();

                $this->context->smarty->assign('countries', $countries);
                $this->context->smarty->assign('id_countries', $id_countries);
                $this->context->smarty->assign('mkp_carriers', $mkp_carriers);
                $this->context->smarty->assign('default_country', $default_country);


                $module = Module::getInstanceByName('lengow');
                $display_countries = $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/lengow_order_setting/helpers/view/select_country.tpl');
                $display_marketplace_carrier = $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/lengow_order_setting/helpers/view/marketplace_carrier.tpl');

                echo '$("#lengow_country_'.$id_country.'").remove();';
                echo '$("#select_country").html("'.preg_replace('/\r|\n/', '', addslashes($display_countries)).'");';
                echo '$("#lengow_marketplace_carrier_country_'.$id_country.'").remove();';
                exit();
                break;
            case 'process':
                foreach ($default_carriers as $key => $value) {
                    Db::getInstance()->autoExecute(_DB_PREFIX_ . 'lengow_carrier_country', array('id_carrier' => (int)$value), 'UPDATE', 'id = '.(int)$key);
                
                }

                foreach ($default_marketplace_carriers as $key => $value) {
                    Db::getInstance()->autoExecute(_DB_PREFIX_.'lengow_marketplace_carrier', array('id_carrier' => (int)$value), 'UPDATE', 'id = '.(int)$key);
                }


                $form = new LengowConfigurationForm(array(
                "fields" => LengowConfiguration::getKeys(),
                ));

                $form->postProcess(array('LENGOW_IMPORT_CARRIER_MP_ENABLED'));
                break;
            default:
                LengowCarrierCountry::createDefaultCarrier();
                LengowCarrierCountry::listCarrierByCountry();
                break;
        }
        
    }
}
