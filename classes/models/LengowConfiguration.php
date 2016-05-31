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
                    'label'         => $locale->t('lengow_setting.lengow_account_id_title'),
                ),
                'LENGOW_ACCESS_TOKEN' => array(
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_access_token_title'),
                ),
                'LENGOW_SECRET_TOKEN' => array(
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_secret_token_title'),
                ),
                'LENGOW_SHOP_ACTIVE' => array(
                    'type'          => 'checkbox',
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_shop_active_title'),
                ),
                'LENGOW_SHOP_TOKEN' => array(
                    'readonly'      => true,
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_shop_token_title'),
                ),
                'LENGOW_EXPORT_SELECTION_ENABLED' => array(
                    'type'          => 'checkbox',
                    'readonly'      => true,
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_export_selection_enabled_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_export_selection_enabled_legend'),
                    'default_value' => false
                ),
                'LENGOW_EXPORT_VARIATION_ENABLED' => array(
                    'type'          => 'checkbox',
                    'readonly'      => true,
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_export_variation_enabled_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_export_variation_enabled_legend'),
                    'default_value' => true,
                ),
                'LENGOW_EXPORT_FORMAT' => array(
                    'type'          => 'select',
                    'label'         => $locale->t('lengow_setting.lengow_export_format_title'),
                    'default_value' => 'csv',
                    'collection'    => $exportFormats,
                ),
                'LENGOW_EXPORT_FILE_ENABLED' => array(
                    'type'          => 'checkbox',
                    'readonly'      => false,
                    'label'         => $locale->t('lengow_setting.lengow_export_file_enabled_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_export_file_enabled_legend'),
                ),
                'LENGOW_LAST_EXPORT' => array(
                    'readonly'      => true,
                    'shop'          => true,
                    'label'         => $locale->t('lengow_setting.lengow_last_export_title'),
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
                    'default_value' => true,
                ),
                'LENGOW_IMPORT_DAYS' => array(
                    'type'          => 'day',
                    'label'         => $locale->t('lengow_setting.lengow_import_days_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_days_legend'),
                    'default_value' => 5,
                ),
                'LENGOW_IMPORT_PROCESSING_FEE' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_import_processing_fee_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_processing_fee_legend'),
                    'default_value' => true,
                ),
                'LENGOW_CRON_ENABLED' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_cron_enabled_title'),
                    'default_value' => false,
                ),
                'LENGOW_IMPORT_PREPROD_ENABLED' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_import_preprod_enabled_title'),
                ),
                'LENGOW_IMPORT_FAKE_EMAIL' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_import_fake_mail_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_fake_mail_legend'),
                    'default_value' => true,
                ),
                'LENGOW_IMPORT_SHIP_MP_ENABLED' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_import_ship_mp_enabled_title'),
                    'default_value' => false,
                ),
                'LENGOW_IMPORT_STOCK_SHIP_MP' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_import_stock_ship_mp_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_stock_ship_mp_legend'),
                    'default_value' => false,
                ),
                'LENGOW_REPORT_MAIL_ENABLED' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_report_mail_enabled_title'),
                    'default_value' => true
                ),
                'LENGOW_REPORT_MAIL_ADDRESS' => array(
                    'type'          => 'text',
                    'placeholder'   => $locale->t('lengow_setting.lengow_report_mail_address_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_report_mail_address_legend'),
                    'default_value' => ''
                ),
                'LENGOW_IMPORT_SINGLE_ENABLED' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_import_single_enabled_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_import_single_enabled_legend'),
                    'default_value' => version_compare(_PS_VERSION_, '1.5.2', '>') &&
                        version_compare(_PS_VERSION_, '1.5.5', '<'),
                ),
                'LENGOW_IMPORT_IN_PROGRESS' => array(
                    'readonly'      => true,
                    'label'         => $locale->t('lengow_setting.lengow_import_in_progress_title'),
                ),
                'LENGOW_LAST_IMPORT_CRON' => array(
                    'readonly'      => true,
                    'label'         => $locale->t('lengow_setting.lengow_last_import_cron_title'),
                    'export'        => false
                ),
                'LENGOW_LAST_IMPORT_MANUAL' => array(
                    'readonly'      => true,
                    'label'         => $locale->t('lengow_setting.lengow_last_import_manual_title'),
                    'export'        => false
                ),
                'LENGOW_GLOBAL_TOKEN' => array(
                    'readonly'      => true,
                    'label'         => $locale->t('lengow_setting.lengow_global_token_title'),
                ),
                'LENGOW_AUTHORIZED_IP' => array(
                    'label'         => $locale->t('lengow_setting.lengow_authorized_ip_title'),
                    'legend'        => $locale->t('lengow_setting.lengow_authorized_ip_legend'),
                ),
                'LENGOW_TRACKING_ENABLED' => array(
                    'type'          => 'checkbox',
                    'label'         => $locale->t('lengow_setting.lengow_tracking_enabled_title'),
                    'default_value' => true,
                ),
                'LENGOW_TRACKING_ID' => array(
                    'type'          => 'select',
                    'label'         => $locale->t('lengow_setting.lengow_tracking_id_title'),
                    'default_value' => 'id',
                    'collection'    => $trackerIds
                ),
                'LENGOW_ORDER_STAT' => array(
                    'type'          => 'json',
                    'label'         => $locale->t('lengow_setting.lengow_order_stat_title'),
                    'export'        => false
                ),
                'LENGOW_ORDER_STAT_UPDATE' => array(
                    'type'          => 'datetime',
                    'label'         => $locale->t('lengow_setting.lengow_order_stat_update_title'),
                    'export'        => false
                ),
                'LENGOW_VERSION' => array(
                    'type'          => 'text',
                    'default_value' => '',
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

    public static function resetAll($overwrite = false)
    {
        $shops = LengowShop::findAll(true);
        $keys = self::getKeys();
        foreach ($keys as $key => $value) {
            if (isset($value['default_value'])) {
                $val = $value['default_value'];
            } else {
                $val = '';
            }
            if (isset($value['shop']) && $value['shop']) {
                foreach ($shops as $shop) {
                    if ($overwrite) {
                        self::updateValue($key, $val, false, null, $shop["id_shop"]);
                    } else {
                        $oldValue = self::get($key, false, null, $shop["id_shop"]);
                        if ($oldValue == "") {
                            self::updateValue($key, $val, false, null, $shop["id_shop"]);
                        }
                    }
                }
            } else {
                if ($overwrite) {
                    self::updateValue($key, $val);
                } else {
                    $oldValue = self::get($key);
                    if ($oldValue == "") {
                        self::updateValue($key, $val);
                    }
                }
            }
        }
        if ($overwrite) {
            LengowMain::log('Setting', LengowMain::setLogMessage('log.setting.setting_reset'));
        } else {
            LengowMain::log('Setting', LengowMain::setLogMessage('log.setting.setting_updated'));
        }
        return true;
    }

    public static function deleteAll()
    {
        $keys = self::getKeys();
        foreach ($keys as $key => $value) {
            // This line is useless, but Prestashop validator require it
            $value = $value;
            self::deleteByName($key);
        }
        return true;
    }

    /**
     * Get Values by shop or global
     * @param null $shopId
     * @return array
     */
    public static function getAllValues($shopId = null)
    {
        $rows = array();
        $keys = self::getKeys();
        foreach ($keys as $key => $value) {
            if (isset($value['export']) && !$value['export']) {
                continue;
            }
            if ($shopId) {
                if (isset($value['shop']) && $value['shop'] == 1) {
                    $rows[$key] = LengowConfiguration::get($key, null, false, $shopId);
                }
            } else {
                $rows[$key] = LengowConfiguration::getGlobalValue($key);
            }
        }
        return $rows;
    }
}
