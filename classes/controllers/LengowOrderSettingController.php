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
/*
 * Lengow Order Setting Controller Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowOrderSettingController extends LengowController
{
    /**
     * Display data page
     *
     * @return void
     */
    public function display(): void
    {
        $countries = LengowCarrier::getCountries();
        $marketplaceCounters = LengowMarketplace::getMarketplaceCounters();
        $defaultCarrierNotMatched = LengowCarrier::getDefaultCarrierNotMatched();
        $showCarrierNotification = !empty($defaultCarrierNotMatched);
        $form = new LengowConfigurationForm(['fields' => LengowConfiguration::getKeys()]);
        $matching = $form->buildInputs(
            [
                LengowConfiguration::WAITING_SHIPMENT_ORDER_ID,
                LengowConfiguration::SHIPPED_ORDER_ID,
                LengowConfiguration::CANCELED_ORDER_ID,
                LengowConfiguration::SHIPPED_BY_MARKETPLACE_ORDER_ID,
                LengowConfiguration::SEND_EMAIL_DISABLED,
            ]
        );
        $importParams = $form->buildInputs(
            [
                LengowConfiguration::SYNCHRONIZATION_DAY_INTERVAL,
                LengowConfiguration::SHIPPED_BY_MARKETPLACE_ENABLED,
                LengowConfiguration::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED,
                LengowConfiguration::ANONYMIZE_EMAIL,
                LengowConfiguration::TYPE_ANONYMIZE_EMAIL,
                LengowConfiguration::ACTIVE_NEW_ORDER_HOOK,
                LengowConfiguration::ORDER_CUSTOMER_GROUP,
            ]
        );
        $currencyConversion = $form->buildInputs([LengowConfiguration::CURRENCY_CONVERSION_ENABLED]);
        $semanticSearch = $form->buildInputs([LengowConfiguration::SEMANTIC_MATCHING_CARRIER_ENABLED]);
        $this->templateVars['matching'] = $matching;
        $this->templateVars['semantic_search'] = $semanticSearch;
        $this->templateVars['import_params'] = $importParams;
        $this->templateVars['currency_conversion'] = $currencyConversion;
        $this->templateVars['countries'] = $countries;
        $this->templateVars['marketplaceCounters'] = $marketplaceCounters;
        $this->templateVars['defaultCarrierNotMatched'] = $defaultCarrierNotMatched;
        $this->templateVars['showCarrierNotification'] = $showCarrierNotification;
        parent::display();
    }

    /**
     * Process Post Parameters
     *
     * @return void
     */
    public function postProcess(): void
    {
        $action = Tools::getValue('action');
        $idCountry = Tools::getIsset('id_country') ? (int) Tools::getValue('id_country') : false;
        $defaultCarriers = Tools::getIsset('default_carriers') ? Tools::getValue('default_carriers') : [];
        $methodMarketplaces = Tools::getIsset('method_marketplaces')
            ? Tools::getValue('method_marketplaces')
            : [];
        $carrierMarketplaces = Tools::getIsset('carrier_marketplaces')
            ? Tools::getValue('carrier_marketplaces')
            : [];
        switch ($action) {
            case 'open_marketplace_matching':
                $idCountry = (int) Tools::getValue('idCountry');
                $country = LengowCountry::getCountry($idCountry);
                $marketplaces = LengowMarketplace::getAllMarketplaceDataByCountry($idCountry);
                $carriers = LengowCarrier::getActiveCarriers($idCountry);
                $displayMarketplaceMatching = $this->twig->render(
                    '@Modules/lengow/views/templates/admin/lengow_order_setting/helpers/view/marketplace_matching.html.twig',
                    array_merge($this->templateVars, [
                        'country' => $country,
                        'marketplaces' => $marketplaces,
                        'carriers' => $carriers,
                    ])
                );
                $data = [
                    'marketplace_matching' => preg_replace('/\r|\n/', '', $displayMarketplaceMatching),
                ];
                $this->respondJson($data);
                $this->finishPostProcess();

                return;
            case 'process':
                // save carrier matching
                if ($idCountry) {
                    // save default carriers
                    foreach ($defaultCarriers as $idMarketplace => $value) {
                        $idCarrier = isset($value['carrier']) ? (int) $value['carrier'] : null;
                        $idCarrierMarketplace = isset($value['carrier_marketplace'])
                            ? (int) $value['carrier_marketplace']
                            : null;
                        $params = [
                            LengowCarrier::FIELD_CARRIER_ID => $idCarrier,
                            LengowCarrier::FIELD_CARRIER_MARKETPLACE_ID => $idCarrierMarketplace,
                        ];
                        $id = LengowCarrier::getIdDefaultCarrier($idCountry, (int) $idMarketplace);
                        if ($id) {
                            LengowCarrier::updateDefaultCarrier($id, $params);
                        } else {
                            LengowCarrier::insertDefaultCarrier($idCountry, (int) $idMarketplace, $params);
                        }
                    }
                    // save marketplace methods
                    foreach ($methodMarketplaces as $idMarketplace => $value) {
                        foreach ($value as $idMethodMarketplace => $idCarrier) {
                            $idCarrier = (int) $idCarrier > 0 ? (int) $idCarrier : null;
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
                            $idCarrierMarketplace = (int) $idCarrierMarketplace > 0
                                ? (int) $idCarrierMarketplace
                                : null;
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
                $form = new LengowConfigurationForm(['fields' => LengowConfiguration::getKeys()]);
                $form->postProcess(
                    [
                        LengowConfiguration::SHIPPED_BY_MARKETPLACE_ENABLED,
                        LengowConfiguration::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED,
                        LengowConfiguration::SEMANTIC_MATCHING_CARRIER_ENABLED,
                        LengowConfiguration::CURRENCY_CONVERSION_ENABLED,
                        LengowConfiguration::ANONYMIZE_EMAIL,
                        LengowConfiguration::ACTIVE_NEW_ORDER_HOOK,
                    ]
                );
                break;
            default:
                LengowSync::syncCarrier(true);
                break;
        }
    }
}
