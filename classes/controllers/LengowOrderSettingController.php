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

include('config/config.inc.php');

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

        $form = new LengowConfigurationForm(array(
            "fields" => LengowConfiguration::getKeys(),
            ));

        $matching = $form->buildInputs(array(
            'LENGOW_ORDER_ID_PROCESS',
            'LENGOW_ORDER_ID_SHIPPED',
            'LENGOW_ORDER_ID_CANCEL',
            'LENGOW_ORDER_ID_SHIPPEDBYMP'
            ));

        $matching2 = $form->buildInputs(array('LENGOW_IMPORT_CARRIER_DEFAULT'));
        $matching3 = $form->buildInputs(array('LENGOW_IMPORT_CARRIER_MP_ENABLED'));
        $matching4 = $form->buildInputs(array('LENGOW_IMPORT_DAYS'));

        $this->context->smarty->assign('default_country', $default_country);
        $this->context->smarty->assign('carriers', $carriers);
        $this->context->smarty->assign('listCarrier', $listCarrier);
        $this->context->smarty->assign('countries', $countries);
        $this->context->smarty->assign('id_countries', $id_countries);
        $this->context->smarty->assign('matching', $matching);
        $this->context->smarty->assign('matching2', $matching2);
        $this->context->smarty->assign('matching3', $matching3);
        $this->context->smarty->assign('matching4', $matching4);

        parent::display();
    }

    public function postProcess()
    {

        $default_carriers = Tools::getValue('default_carrier');
        $action = Tools::getValue('action');

        switch ($action) {
            case 'add_country':
                $id_country = Tools::getValue('id_country');

                $db = LengowCarrierCountry::insert($id_country);
                $id_lengow_carrier = $db->insert_ID();

                $itemCarrier = LengowCarrierCountry::listCarrierById($id_lengow_carrier);

                $carriers = LengowCarrier::getActiveCarriers();


                $this->context->smarty->assign('itemCarrier', $itemCarrier);
                $this->context->smarty->assign('carriers', $carriers);

                $module = Module::getInstanceByName('lengow');

                $display_default_carrier = $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/lengow_order_setting/helpers/view/default_carrier.tpl');
           
                echo '$("#add_country").append("'.preg_replace('/\r|\n/', '', addslashes($display_default_carrier)).'");';
                echo '$("#select_country option:selected").remove();';

                
                exit();
                break;
            
            case 'delete_country':
                 $id_country = Tools::getValue('id_country');

                $db = LengowCarrierCountry::delete($id_country);

                $countries = LengowCarrierCountry::getCountries();
                $listCarrier = LengowCarrierCountry::listCarrierByCountry();
                $id_countries = LengowCarrierCountry::getIdCountries($listCarrier);


                $this->context->smarty->assign('countries', $countries);
                $this->context->smarty->assign('id_countries', $id_countries);


                $module = Module::getInstanceByName('lengow');
                $display_countries = $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/lengow_order_setting/helpers/view/select_country.tpl');

                echo '$("#lengow_country_'.$id_country.'").remove();';
                echo '$("#select_country").html("'.preg_replace('/\r|\n/', '', addslashes($display_countries)).'");';
                exit();
                break;
            case 'process':
                foreach ($default_carriers as $key => $value) {
                    Db::getInstance()->autoExecute(_DB_PREFIX_ . 'lengow_carrier_country', array('id_carrier' => (int)$value), 'UPDATE', 'id = '.(int)$key);
                
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
