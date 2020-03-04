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
 * Lengow Order Setting Controller Class
 */
class LengowOrderSettingController extends LengowController
{
    /**
     * Display data page
     */
    public function display()
    {
        $countries = LengowCarrier::getCountries();
        $marketplaceCounters = LengowMarketplace::getMarketplaceCounters();
        $defaultCarrierNotMatched = LengowCarrier::getDefaultCarrierNotMatched();
        $showCarrierNotification = !empty($defaultCarrierNotMatched) ? true : false;
        $form = new LengowConfigurationForm(array('fields' => LengowConfiguration::getKeys()));
        $matching = $form->buildInputs(
            array(
                'LENGOW_ORDER_ID_PROCESS',
                'LENGOW_ORDER_ID_SHIPPED',
                'LENGOW_ORDER_ID_CANCEL',
                'LENGOW_ORDER_ID_SHIPPEDBYMP',
            )
        );
        $importParams = $form->buildInputs(
            array(
                'LENGOW_IMPORT_DAYS',
                'LENGOW_IMPORT_SHIP_MP_ENABLED',
                'LENGOW_IMPORT_STOCK_SHIP_MP',
            )
        );
        $semanticSearch = $form->buildInputs(array('LENGOW_CARRIER_SEMANTIC_ENABLE'));
        $this->context->smarty->assign('matching', $matching);
        $this->context->smarty->assign('semantic_search', $semanticSearch);
        $this->context->smarty->assign('import_params', $importParams);
        $this->context->smarty->assign('countries', $countries);
        $this->context->smarty->assign('marketplaceCounters', $marketplaceCounters);
        $this->context->smarty->assign('defaultCarrierNotMatched', $defaultCarrierNotMatched);
        $this->context->smarty->assign('showCarrierNotification', $showCarrierNotification);
        parent::display();
    }

    /**
     * Process Post Parameters
     */
    public function postProcess()
    {
        $action = Tools::getValue('action');
        $idCountry = Tools::getIsset('id_country') ? (int)Tools::getValue('id_country') : false;
        $defaultCarriers = Tools::getIsset('default_carriers') ? Tools::getValue('default_carriers') : array();
        $methodMarketplaces = Tools::getIsset('method_marketplaces')
            ? Tools::getValue('method_marketplaces')
            : array();
        $carrierMarketplaces = Tools::getIsset('carrier_marketplaces')
            ? Tools::getValue('carrier_marketplaces')
            : array();
        switch ($action) {
            case 'open_marketplace_matching':
                $idCountry = (int)Tools::getValue('idCountry');
                $country = LengowCountry::getCountry($idCountry);
                $marketplaces = LengowMarketplace::getAllMarketplaceDataByCountry($idCountry);
                $carriers = LengowCarrier::getActiveCarriers($idCountry);
                $this->context->smarty->assign('country', $country);
                $this->context->smarty->assign('marketplaces', $marketplaces);
                $this->context->smarty->assign('carriers', $carriers);
                $module = Module::getInstanceByName('lengow');
                $displayMarketplaceMatching = $module->display(
                    _PS_MODULE_LENGOW_DIR_,
                    'views/templates/admin/lengow_order_setting/helpers/view/marketplace_matching.tpl'
                );
                $data = array('marketplace_matching' => preg_replace('/\r|\n/', '', $displayMarketplaceMatching));
                echo Tools::jsonEncode($data);
                exit();
            case 'process':
                // save carrier matching
                if ($idCountry) {
                    // save default carriers
                    foreach ($defaultCarriers as $idMarketplace => $value) {
                        $idCarrier = isset($value['carrier']) ? (int)$value['carrier'] : null;
                        $idCarrierMarketplace = isset($value['carrier_marketplace'])
                            ? (int)$value['carrier_marketplace']
                            : null;
                        $params = array(
                            'id_carrier' => $idCarrier,
                            'id_carrier_marketplace' => $idCarrierMarketplace,
                        );
                        $id = LengowCarrier::getIdDefaultCarrier($idCountry, (int)$idMarketplace);
                        if ($id) {
                            LengowCarrier::updateDefaultCarrier($id, $params);
                        } else {
                            LengowCarrier::insertDefaultCarrier($idCountry, (int)$idMarketplace, $params);
                        }
                    }
                    // save marketplace methods
                    foreach ($methodMarketplaces as $idMarketplace => $value) {
                        foreach ($value as $idMethodMarketplace => $idCarrier) {
                            $idCarrier = (int)$idCarrier > 0 ? (int)$idCarrier : null;
                            $id = LengowMethod::getIdMarketplaceMethodCountry(
                                $idCountry,
                                $idMarketplace,
                                $idMethodMarketplace
                            );
                            if ($id) {
                                LengowMethod::updateMarketplaceMethodCountry($id, $idCarrier);
                            } else {
                                LengowMethod::insertMarketplaceMethodCountry(
                                    $idCountry,
                                    $idMarketplace,
                                    $idCarrier,
                                    $idMethodMarketplace
                                );
                            }
                        }
                    }
                    // save marketplace carriers
                    foreach ($carrierMarketplaces as $idMarketplace => $value) {
                        foreach ($value as $idCarrier => $idCarrierMarketplace) {
                            $idCarrierMarketplace = (int)$idCarrierMarketplace > 0 ? (int)$idCarrierMarketplace : null;
                            $id = LengowCarrier::getIdMarketplaceCarrierCountry(
                                $idCountry,
                                $idMarketplace,
                                $idCarrier
                            );
                            if ($id) {
                                LengowCarrier::updateMarketplaceCarrierCountry($id, $idCarrierMarketplace);
                            } else {
                                LengowCarrier::insertMarketplaceCarrierCountry(
                                    $idCountry,
                                    $idMarketplace,
                                    $idCarrier,
                                    $idCarrierMarketplace
                                );
                            }
                        }
                    }
                }
                // save other settings
                $form = new LengowConfigurationForm(array('fields' => LengowConfiguration::getKeys()));
                $form->postProcess(
                    array(
                        'LENGOW_IMPORT_SHIP_MP_ENABLED',
                        'LENGOW_IMPORT_STOCK_SHIP_MP',
                        'LENGOW_CRON_ENABLED',
                        'LENGOW_CARRIER_SEMANTIC_ENABLE',
                    )
                );
                break;
            default:
                LengowSync::syncCarrier(true);
                break;
        }
    }
}
