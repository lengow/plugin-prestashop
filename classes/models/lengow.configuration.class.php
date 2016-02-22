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

class LengowConfiguration extends Configuration
{

    public static function getKeys()
    {
        static $keys = null;
        if ($keys === null) {

            $langId = (int)Context::getContext()->cookie->id_lang;
            
            $locale = new LengowTranslation();

            $orderStates = array();
            $os = OrderState::getOrderStates($langId);
            foreach ($os as $state) {
                $orderStates[] = array('id' => $state['id_order_state'], 'text' => $state['name']);
            }
            $carriers = array();
            if (_PS_VERSION_ <= '1.4.4.0') {
                $cs = LengowCarrier::getCarriers($langId, true, false, false, null, ALL_CARRIERS);
            } else {
                $cs = LengowCarrier::getCarriers($langId, true, false, false, null, LengowCarrier::ALL_CARRIERS);
            }
            foreach ($cs as $c) {
                $carriers[] = array('id' => $c['id_carrier'], 'text' => $c['name']);
            }
            $exportFormats = array();
            foreach (LengowFeed::$AVAILABLE_FORMATS as $value) {
                $exportFormats[] = array('id' => $value, 'text' => $value);
            }
            $trackerIds = array();
            foreach (LengowMain::$TRACKER_CHOICE_ID as $key => $value) {
                $trackerIds[] = array('id' => $key, 'text' => $value);
            }

            $keys = array(
                'LENGOW_ACCOUNT_ID' => array(
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_account_id_title')
                ),
                'LENGOW_ACCESS_TOKEN' => array(
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_access_token_title')
                ),
                'LENGOW_SECRET_TOKEN' => array(
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_secret_token_title')
                ),
                'LENGOW_SHOP_ACTIVE' => array(
                    'type'          => 'checkbox',
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_shop_active_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_shop_active_legend')
                ),
                'LENGOW_SHOP_TOKEN' => array(
                    'readonly'      => true,
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_shop_token_title')
                ),
                'LENGOW_EXPORT_SELECTION_ENABLED' => array(
                    'type'          => 'checkbox',
                    'readonly'      => true,
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_export_selection_enabled_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_export_selection_enabled_legend')
                ),
                'LENGOW_EXPORT_VARIATION_ENABLED' => array(
                    'type'          => 'checkbox',
                    'readonly'      => true,
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_export_variation_enabled_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_export_variation_enabled_legend'),
                    'default_value' => true
                ),
                'LENGOW_EXPORT_FORMAT' => array(
                    'type'          => 'select',
                    'label'         => $locale->t('lengow_setting.lengow_export_format_title'),
                    'default_value' => 'csv',
                    'collection'    => $exportFormats
                ),
                'LENGOW_EXPORT_FILE_ENABLED' => array(
                    'type'          => 'checkbox',
                    'readonly'      => true,
                    'label'         => $locale->t('lengow_setting.lengow_export_file_enabled_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_export_file_enabled_legend'),
                ),
                'LENGOW_CARRIER_DEFAULT' => array(
                    'type' => 'select',
                    'readonly' => true,
                    'label' => 'Export Carrier',
                    'collection' => $carriers,
                ),
                'LENGOW_LAST_EXPORT' => array(
                    'readonly'      => true,
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_last_export_title')
                ),
                'LENGOW_ORDER_ID_PROCESS' => array(
                    'type'          => 'select',
                    'label'         => $locale->t('lengow_setting.lengow_order_id_process_title'),
                    'default_value' => 2,
                    'collection'    => $orderStates,
                ),
                'LENGOW_ORDER_ID_SHIPPED' => array(
                    'type'          => 'select',
                    'label'         => $locale->t('lengow_setting.lengow_order_id_shipped_title'),
                    'default_value' => 4,
                    'collection'    => $orderStates,
                ),
                'LENGOW_ORDER_ID_CANCEL' => array(
                    'type'          => 'select',
                    'label'         => $locale->t('lengow_setting.lengow_order_id_cancel_title'),
                    'default_value' => 6,
                    'collection'    => $orderStates,
                ),
                'LENGOW_ORDER_ID_SHIPPEDBYMP' => array(
                    'type'          => 'select',
                    'label'         => $locale->t('lengow_setting.lengow_order_id_shippedbymp_title'),
                    'default_value' => 4,
                    'collection'    => $orderStates,
                ),
                'LENGOW_IMPORT_FORCE_PRODUCT' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_import_force_product_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_force_product_legend'),
                ),
                'LENGOW_IMPORT_DAYS' => array(
                    'label'         => $locale->t('lengow_setting.lengow_import_days_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_days_legend'),
                    'default_value' => 5
                ),
                'LENGOW_IMPORT_PROCESSING_FEE' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_import_processing_fee_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_processing_fee_legend')
                ),
                'LENGOW_IMPORT_CARRIER_DEFAULT' => array(
                    'type'          => 'select',
                    'label'         => 'Import Carrier',
                    'collection'    => $carriers,
                ),
                'LENGOW_IMPORT_PREPROD_ENABLED' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_import_preprod_enabled_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_preprod_enabled_legend')
                ),
                'LENGOW_IMPORT_FAKE_EMAIL' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_import_fake_mail_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_fake_mail_legend'),
                    'default_value' => true
                ),
                'LENGOW_IMPORT_SHIP_MP_ENABLED' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('Import orders shipped by marketplace'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_ship_mp_enabled_legend'),
                    'default_value' => false,
                ),
                'LENGOW_IMPORT_STOCK_SHIP_MP' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_import_stock_ship_mp_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_stock_ship_mp_legend'),
                    'default_value' => false
                ),
                'LENGOW_REPORT_MAIL_ENABLED' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('I want to be noticed when issues encountered on orders
                    importation & catalog exportation'),
                    'default_value' => true,
                    'legend'        => $locale->t('we will send you this once a day')
                ),
                'LENGOW_REPORT_MAIL_ADDRESS' => array(
                    'type'          => 'tag',
                    'label'         => $locale->t('Mail Report email')
                ),
                'LENGOW_IMPORT_SINGLE_ENABLED' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('Import Order One By One'),
                    'default_value' => version_compare(_PS_VERSION_, '1.5.2', '>') &&
                        version_compare(_PS_VERSION_, '1.5.5', '<')
                ),
                'LENGOW_IMPORT_IN_PROGRESS' => array(
                    'readonly'      => true,
                    'label'         => $locale->t('Import In Progress')
                ),
                'LENGOW_LAST_IMPORT_CRON' => array(
                    'readonly'      => true,
                    'label'         => $locale->t('Last Cron Import')
                ),
                'LENGOW_LAST_IMPORT_MANUAL' => array(
                    'readonly'      => true,
                    'label'         => $locale->t('Last Manuel Import')
                ),
                'LENGOW_GLOBAL_TOKEN' => array(
                    'readonly'      => true,
                    'label'         => $locale->t('Lengow Global Token')
                ),
                'LENGOW_AUTHORIZED_IP' => array(
                    'label'         => $locale->t('Lengow Authorized IP'),
                ),
                'LENGOW_TRACKING_ENABLED' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('Lengow Tracking'),
                    'default_value' => true,
                ),
                'LENGOW_TRACKING_ID' => array(
                    'type'          => 'select',
                    'label'         => $locale->t('Lengow Tracking Id'),
                    'default_value' => 'id',

                    'collection'    => $trackerIds
                ),
                'LENGOW_ORDER_STAT' => array(
                    'type' => 'json',
                    'label' => 'Order Statistic',
                ),
                'LENGOW_ORDER_STAT_UPDATE' => array(
                    'type' => 'datetime',
                    'label' => 'Order Statistic Update',
                ),
            );
        }
        return $keys;
    }

    public static function getGlobalValue($key, $id_lang = null)
    {
        if (_PS_VERSION_ < '1.5') {
            return parent::get($key, $id_lang);
        } else {
            return parent::getGlobalValue($key, $id_lang);
        }
    }

    public static function get($key, $id_lang = null, $id_shop_group = null, $id_shop = null)
    {
        if (_PS_VERSION_ < '1.5') {
            return parent::get($key, $id_lang);
        } else {
            return parent::get($key, $id_lang, $id_shop_group, $id_shop);
        }
    }

    public static function updateGlobalValue($key, $values, $html = false)
    {
        if (_PS_VERSION_ < '1.5') {
            parent::updateValue($key, $values, $html);
        } else {
            parent::updateGlobalValue($key, $values, $html);
        }
    }

    public static function updateValue($key, $values, $html = false, $id_shop_group = null, $id_shop = null)
    {
        if (_PS_VERSION_ < '1.5') {
            parent::updateValue($key, $values, $html);
        } else {
            parent::updateValue($key, $values, $html, $id_shop_group, $id_shop);
        }


    }

    public static function getReportEmailAddress()
    {
        $emails = explode(',', self::get('LENGOW_REPORT_MAIL_ADDRESS'));
        if ($emails[0] == '') {
            $emails[0] = self::get('PS_SHOP_EMAIL');
        }
        return $emails;
    }
}
