<?php
/**
 * Copyright 2021 Lengow SAS.
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
 * @copyright 2021 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * Lengow Configuration Class
 */
class LengowConfiguration extends Configuration
{
    /* Settings database key */
    public const PLUGIN_ENV = 'LENGOW_PLUGIN_ENV';
    public const ACCOUNT_ID = 'LENGOW_ACCOUNT_ID';
    public const ACCESS_TOKEN = 'LENGOW_ACCESS_TOKEN';
    public const SECRET = 'LENGOW_SECRET_TOKEN';
    public const CMS_TOKEN = 'LENGOW_GLOBAL_TOKEN';
    public const AUTHORIZED_IP_ENABLED = 'LENGOW_IP_ENABLED';
    public const AUTHORIZED_IPS = 'LENGOW_AUTHORIZED_IP';
    public const TRACKING_ENABLED = 'LENGOW_TRACKING_ENABLED';
    public const TRACKING_ID = 'LENGOW_TRACKING_ID';
    public const DEBUG_MODE_ENABLED = 'LENGOW_IMPORT_DEBUG_ENABLED';
    public const REPORT_MAIL_ENABLED = 'LENGOW_REPORT_MAIL_ENABLED';
    public const REPORT_MAILS = 'LENGOW_REPORT_MAIL_ADDRESS';
    public const PLUGIN_VERSION = 'LENGOW_VERSION';
    public const INSTALLATION_IN_PROGRESS = 'LENGOW_INSTALLATION_IN_PROGRESS';
    public const LENGOW_ERROR_STATE_ID = 'LENGOW_STATE_ERROR';
    public const AUTHORIZATION_TOKEN = 'LENGOW_AUTH_TOKEN';
    public const PLUGIN_DATA = 'LENGOW_PLUGIN_DATA';
    public const ACCOUNT_STATUS_DATA = 'LENGOW_ACCOUNT_STATUS';
    public const SHOP_TOKEN = 'LENGOW_SHOP_TOKEN';
    public const SHOP_ACTIVE = 'LENGOW_SHOP_ACTIVE';
    public const CATALOG_IDS = 'LENGOW_CATALOG_ID';
    public const SELECTION_ENABLED = 'LENGOW_EXPORT_SELECTION_ENABLED';
    public const VARIATION_ENABLED = 'LENGOW_EXPORT_VARIATION_ENABLED';
    public const OUT_OF_STOCK_ENABLED = 'LENGOW_EXPORT_OUT_STOCK';
    public const INACTIVE_ENABLED = 'LENGOW_EXPORT_INACTIVE';
    public const EXPORT_FORMAT = 'LENGOW_EXPORT_FORMAT';
    public const EXPORT_FILE_ENABLED = 'LENGOW_EXPORT_FILE_ENABLED';
    public const DEFAULT_EXPORT_CARRIER_ID = 'LENGOW_EXPORT_CARRIER_DEFAULT';
    public const WAITING_SHIPMENT_ORDER_ID = 'LENGOW_ORDER_ID_PROCESS';
    public const SHIPPED_ORDER_ID = 'LENGOW_ORDER_ID_SHIPPED';
    public const CANCELED_ORDER_ID = 'LENGOW_ORDER_ID_CANCEL';
    public const SHIPPED_BY_MARKETPLACE_ORDER_ID = 'LENGOW_ORDER_ID_SHIPPEDBYMP';
    public const ANONYMIZE_EMAIL = 'LENGOW_ANONYMIZE_EMAIL';
    public const ACTIVE_NEW_ORDER_HOOK = 'LENGOW_ACTIVE_NEW_ORDER_HOOK';
    public const SYNCHRONIZATION_DAY_INTERVAL = 'LENGOW_IMPORT_DAYS';
    public const SEMANTIC_MATCHING_CARRIER_ENABLED = 'LENGOW_CARRIER_SEMANTIC_ENABLE';
    public const CURRENCY_CONVERSION_ENABLED = 'LENGOW_CURRENCY_CONVERSION';
    public const SHIPPED_BY_MARKETPLACE_ENABLED = 'LENGOW_IMPORT_SHIP_MP_ENABLED';
    public const SHIPPED_BY_MARKETPLACE_STOCK_ENABLED = 'LENGOW_IMPORT_STOCK_SHIP_MP';
    public const FORCE_PRODUCT_ENABLED = 'LENGOW_IMPORT_FORCE_PRODUCT';
    public const IMPORT_PROCESSING_FEE_ENABLED = 'LENGOW_IMPORT_PROCESSING_FEE';
    public const SYNCHRONIZATION_IN_PROGRESS = 'LENGOW_IMPORT_IN_PROGRESS';
    public const LAST_UPDATE_EXPORT = 'LENGOW_LAST_EXPORT';
    public const LAST_UPDATE_CRON_SYNCHRONIZATION = 'LENGOW_LAST_IMPORT_CRON';
    public const LAST_UPDATE_MANUAL_SYNCHRONIZATION = 'LENGOW_LAST_IMPORT_MANUAL';
    public const LAST_UPDATE_ACTION_SYNCHRONIZATION = 'LENGOW_LAST_ACTION_SYNC';
    public const LAST_UPDATE_CATALOG = 'LENGOW_CATALOG_UPDATE';
    public const LAST_UPDATE_MARKETPLACE = 'LENGOW_MARKETPLACE_UPDATE';
    public const LAST_UPDATE_ACCOUNT_STATUS_DATA = 'LENGOW_ACCOUNT_STATUS_UPDATE';
    public const LAST_UPDATE_OPTION_CMS = 'LENGOW_OPTION_CMS_UPDATE';
    public const LAST_UPDATE_MARKETPLACE_LIST = 'LENGOW_LIST_MARKET_UPDATE';
    public const LAST_UPDATE_SETTING = 'LENGOW_LAST_SETTING_UPDATE';
    public const LAST_UPDATE_PLUGIN_DATA = 'LENGOW_PLUGIN_DATA_UPDATE';
    public const LAST_UPDATE_AUTHORIZATION_TOKEN = 'LENGOW_LAST_AUTH_TOKEN_UPDATE';
    public const LAST_UPDATE_PLUGIN_MODAL = 'LENGOW_LAST_PLUGIN_MODAL';

    /* Configuration parameters */
    public const PARAM_COLLECTION = 'collection';
    public const PARAM_DEFAULT_VALUE = 'default_value';
    public const PARAM_EXPORT = 'export';
    public const PARAM_EXPORT_TOOLBOX = 'export_toolbox';
    public const PARAM_GLOBAL = 'global';
    public const PARAM_LABEL = 'label';
    public const PARAM_LEGEND = 'legend';
    public const PARAM_PLACEHOLDER = 'placeholder';
    public const PARAM_RESET_TOKEN = 'reset_token';
    public const PARAM_RETURN = 'return';
    public const PARAM_SECRET = 'secret';
    public const PARAM_SHOP = 'shop';
    public const PARAM_TYPE = 'type';
    public const PARAM_UPDATE = 'update';

    /* Configuration value return type */
    public const RETURN_TYPE_BOOLEAN = 'boolean';
    public const RETURN_TYPE_INTEGER = 'integer';
    public const RETURN_TYPE_ARRAY = 'array';
    public const RETURN_TYPE_STRING = 'string';

    /**
     * @var array params correspondence keys for toolbox
     */
    public static $genericParamKeys = [
        self::PLUGIN_ENV => 'plugin_env',
        self::ACCOUNT_ID => 'account_id',
        self::ACCESS_TOKEN => 'access_token',
        self::SECRET => 'secret',
        self::CMS_TOKEN => 'cms_token',
        self::AUTHORIZED_IP_ENABLED => 'authorized_ip_enabled',
        self::AUTHORIZED_IPS => 'authorized_ips',
        self::TRACKING_ENABLED => 'tracking_enabled',
        self::TRACKING_ID => 'tracking_id',
        self::DEBUG_MODE_ENABLED => 'debug_mode_enabled',
        self::REPORT_MAIL_ENABLED => 'report_mail_enabled',
        self::REPORT_MAILS => 'report_mails',
        self::PLUGIN_VERSION => 'plugin_version',
        self::INSTALLATION_IN_PROGRESS => 'installation_in_progress',
        self::LENGOW_ERROR_STATE_ID => 'lengow_error_state_id',
        self::AUTHORIZATION_TOKEN => 'authorization_token',
        self::PLUGIN_DATA => 'plugin_data',
        self::ACCOUNT_STATUS_DATA => 'account_status_data',
        self::SHOP_TOKEN => 'shop_token',
        self::SHOP_ACTIVE => 'shop_active',
        self::CATALOG_IDS => 'catalog_ids',
        self::SELECTION_ENABLED => 'selection_enabled',
        self::VARIATION_ENABLED => 'variation_enabled',
        self::OUT_OF_STOCK_ENABLED => 'out_of_stock_enabled',
        self::INACTIVE_ENABLED => 'inactive_enabled',
        self::EXPORT_FORMAT => 'export_format',
        self::EXPORT_FILE_ENABLED => 'export_file_enabled',
        self::DEFAULT_EXPORT_CARRIER_ID => 'default_export_carrier_id',
        self::WAITING_SHIPMENT_ORDER_ID => 'waiting_shipment_order_id',
        self::SHIPPED_ORDER_ID => 'shipped_order_id',
        self::CANCELED_ORDER_ID => 'canceled_order_id',
        self::ANONYMIZE_EMAIL => 'anonymize_customer_email',
        self::ACTIVE_NEW_ORDER_HOOK => 'active_new_order_hook',
        self::SHIPPED_BY_MARKETPLACE_ORDER_ID => 'shipped_by_marketplace_order_id',
        self::SYNCHRONIZATION_DAY_INTERVAL => 'synchronization_day_interval',
        self::SEMANTIC_MATCHING_CARRIER_ENABLED => 'semantic_matching_carrier_enabled',
        self::CURRENCY_CONVERSION_ENABLED => 'currency_conversion_enabled',
        self::SHIPPED_BY_MARKETPLACE_ENABLED => 'shipped_by_marketplace_enabled',
        self::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED => 'shipped_by_marketplace_stock_enabled',
        self::FORCE_PRODUCT_ENABLED => 'force_product_enabled',
        self::IMPORT_PROCESSING_FEE_ENABLED => 'import_processing_fee_enabled',
        self::SYNCHRONIZATION_IN_PROGRESS => 'synchronization_in_progress',
        self::LAST_UPDATE_EXPORT => 'last_update_export',
        self::LAST_UPDATE_CRON_SYNCHRONIZATION => 'last_update_cron_synchronization',
        self::LAST_UPDATE_MANUAL_SYNCHRONIZATION => 'last_update_manual_synchronization',
        self::LAST_UPDATE_ACTION_SYNCHRONIZATION => 'last_update_action_synchronization',
        self::LAST_UPDATE_CATALOG => 'last_update_catalog',
        self::LAST_UPDATE_MARKETPLACE => 'last_update_marketplace',
        self::LAST_UPDATE_ACCOUNT_STATUS_DATA => 'last_update_account_status_data',
        self::LAST_UPDATE_OPTION_CMS => 'last_update_option_cms',
        self::LAST_UPDATE_MARKETPLACE_LIST => 'last_update_marketplace_list',
        self::LAST_UPDATE_SETTING => 'last_update_setting',
        self::LAST_UPDATE_PLUGIN_DATA => 'last_update_plugin_data',
        self::LAST_UPDATE_AUTHORIZATION_TOKEN => 'last_update_authorization_token',
        self::LAST_UPDATE_PLUGIN_MODAL => 'last_update_plugin_modal',
    ];

    /**
     * Get all Lengow configuration keys
     *
     * @param string $key Lengow configuration key
     *
     * @return array
     */
    public static function getKeys($key = null)
    {
        static $keys = null;
        if ($keys === null) {
            $langId = (int) Context::getContext()->cookie->id_lang;
            $locale = new LengowTranslation();
            $orderStates = [];
            $allOrderStates = OrderState::getOrderStates($langId);
            foreach ($allOrderStates as $orderState) {
                $orderStates[] = [
                    'id' => $orderState['id_order_state'],
                    'text' => $orderState['name'],
                ];
            }
            $exportFormats = [];
            foreach (LengowFeed::$availableFormats as $value) {
                $exportFormats[] = [
                    'id' => $value,
                    'text' => $value,
                ];
            }
            $trackers = [];
            foreach (LengowMain::$trackerChoiceId as $idTracker => $tracker) {
                $trackers[] = [
                    'id' => $idTracker,
                    'text' => $tracker,
                ];
            }
            $carriers = [];
            $activeCarriers = LengowCarrier::getActiveCarriers();
            foreach ($activeCarriers as $idCarrier => $carrier) {
                $carriers[] = [
                    'id' => $idCarrier,
                    'text' => $carrier['name'],
                ];
            }
            $keys = [
                self::PLUGIN_ENV => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_OPTIONS,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_plugin_env_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_plugin_env_legend'),
                    self::PARAM_DEFAULT_VALUE => 'prod',
                    self::PARAM_RETURN => self::RETURN_TYPE_STRING,
                ],
                self::ACCOUNT_ID => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_account_id_title'),
                ],
                self::ACCESS_TOKEN => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_access_token_title'),
                    self::PARAM_SECRET => true,
                    self::PARAM_RESET_TOKEN => true,
                ],
                self::SECRET => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_secret_token_title'),
                    self::PARAM_SECRET => true,
                    self::PARAM_RESET_TOKEN => true,
                ],
                self::CMS_TOKEN => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT_TOOLBOX => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_global_token_title'),
                ],
                self::AUTHORIZED_IP_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT_TOOLBOX => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_ip_enable_title'),
                    self::PARAM_DEFAULT_VALUE => 0,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::AUTHORIZED_IPS => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT_TOOLBOX => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_authorized_ip_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_authorized_ip_legend'),
                    self::PARAM_DEFAULT_VALUE => '',
                    self::PARAM_RETURN => self::RETURN_TYPE_ARRAY,
                ],
                self::TRACKING_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_tracking_enabled_title'),
                    self::PARAM_DEFAULT_VALUE => 0,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::TRACKING_ID => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_SELECT,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_tracking_id_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_tracking_id_legend'),
                    self::PARAM_DEFAULT_VALUE => 'id',
                    self::PARAM_COLLECTION => $trackers,
                ],
                self::DEBUG_MODE_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT_TOOLBOX => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_import_debug_enabled_title'),
                    self::PARAM_DEFAULT_VALUE => 0,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::REPORT_MAIL_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_report_mail_enabled_title'),
                    self::PARAM_DEFAULT_VALUE => 1,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::REPORT_MAILS => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_PLACEHOLDER => $locale->t('lengow_setting.lengow_report_mail_address_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_report_mail_address_legend'),
                    self::PARAM_DEFAULT_VALUE => '',
                    self::PARAM_RETURN => self::RETURN_TYPE_ARRAY,
                ],
                self::PLUGIN_VERSION => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT => false,
                ],
                self::INSTALLATION_IN_PROGRESS => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT => false,
                ],
                self::LENGOW_ERROR_STATE_ID => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::AUTHORIZATION_TOKEN => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT => false,
                ],
                self::PLUGIN_DATA => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT => false,
                ],
                self::ACCOUNT_STATUS_DATA => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT => false,
                ],
                self::SHOP_TOKEN => [
                    self::PARAM_SHOP => true,
                    self::PARAM_EXPORT_TOOLBOX => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_shop_token_title'),
                ],
                self::SHOP_ACTIVE => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_SHOP => true,
                    self::PARAM_EXPORT_TOOLBOX => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_shop_active_title'),
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::CATALOG_IDS => [
                    self::PARAM_SHOP => true,
                    self::PARAM_EXPORT_TOOLBOX => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_catalog_id_title'),
                    self::PARAM_UPDATE => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_ARRAY,
                ],
                self::SELECTION_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_SHOP => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_export_selection_enabled_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_export_selection_enabled_legend'),
                    self::PARAM_DEFAULT_VALUE => 0,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::VARIATION_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_SHOP => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_export_variation_enabled_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_export_variation_enabled_legend'),
                    self::PARAM_DEFAULT_VALUE => 1,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::OUT_OF_STOCK_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_SHOP => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_export_out_stock_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_export_out_stock_legend'),
                    self::PARAM_DEFAULT_VALUE => 1,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::INACTIVE_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_SHOP => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_export_inactive_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_export_inactive_legend'),
                    self::PARAM_DEFAULT_VALUE => 0,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::EXPORT_FORMAT => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_SELECT,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_export_format_title'),
                    self::PARAM_DEFAULT_VALUE => LengowFeed::FORMAT_CSV,
                    self::PARAM_COLLECTION => $exportFormats,
                ],
                self::EXPORT_FILE_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_export_file_enabled_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_export_file_enabled_legend'),
                    self::PARAM_DEFAULT_VALUE => 0,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::DEFAULT_EXPORT_CARRIER_ID => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_SELECT,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_export_carrier_default_title'),
                    self::PARAM_DEFAULT_VALUE => !empty($carriers) ? (int) $carriers[0]['id'] : '',
                    self::PARAM_COLLECTION => $carriers,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::WAITING_SHIPMENT_ORDER_ID => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_SELECT,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_order_id_process_title'),
                    self::PARAM_DEFAULT_VALUE => 2,
                    self::PARAM_COLLECTION => $orderStates,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::SHIPPED_ORDER_ID => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_SELECT,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_order_id_shipped_title'),
                    self::PARAM_DEFAULT_VALUE => 4,
                    self::PARAM_COLLECTION => $orderStates,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::CANCELED_ORDER_ID => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_SELECT,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_order_id_cancel_title'),
                    self::PARAM_DEFAULT_VALUE => 6,
                    self::PARAM_COLLECTION => $orderStates,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::SHIPPED_BY_MARKETPLACE_ORDER_ID => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_SELECT,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_order_id_shippedbymp_title'),
                    self::PARAM_DEFAULT_VALUE => 4,
                    self::PARAM_COLLECTION => $orderStates,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::SYNCHRONIZATION_DAY_INTERVAL => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_DAY,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_import_days_title'),
                    self::PARAM_DEFAULT_VALUE => 3,
                    self::PARAM_UPDATE => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::ANONYMIZE_EMAIL => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_anonymize_email'),
                    self::PARAM_DEFAULT_VALUE => 0,
                    self::PARAM_UPDATE => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::ACTIVE_NEW_ORDER_HOOK => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_active_new_order_hook'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_active_new_order_hook_legend'),
                    self::PARAM_DEFAULT_VALUE => 0,
                    self::PARAM_UPDATE => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::SEMANTIC_MATCHING_CARRIER_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_carrier_semantic_enable_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_carrier_semantic_enable_legend'),
                    self::PARAM_DEFAULT_VALUE => 0,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::CURRENCY_CONVERSION_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_currency_conversion_switch'),
                    self::PARAM_DEFAULT_VALUE => 1,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::SHIPPED_BY_MARKETPLACE_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_import_ship_mp_enabled_title'),
                    self::PARAM_DEFAULT_VALUE => 0,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_import_stock_ship_mp_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_import_stock_ship_mp_legend'),
                    self::PARAM_DEFAULT_VALUE => 0,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::FORCE_PRODUCT_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_import_force_product_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_import_force_product_legend'),
                    self::PARAM_DEFAULT_VALUE => 1,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::IMPORT_PROCESSING_FEE_ENABLED => [
                    self::PARAM_TYPE => LengowConfigurationForm::TYPE_CHECKBOX,
                    self::PARAM_GLOBAL => true,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_import_processing_fee_title'),
                    self::PARAM_LEGEND => $locale->t('lengow_setting.lengow_import_processing_fee_legend'),
                    self::PARAM_DEFAULT_VALUE => 1,
                    self::PARAM_RETURN => self::RETURN_TYPE_BOOLEAN,
                ],
                self::SYNCHRONIZATION_IN_PROGRESS => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_import_in_progress_title'),
                ],
                self::LAST_UPDATE_EXPORT => [
                    self::PARAM_SHOP => true,
                    self::PARAM_EXPORT_TOOLBOX => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_last_export_title'),
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_CRON_SYNCHRONIZATION => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT_TOOLBOX => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_last_import_cron_title'),
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_MANUAL_SYNCHRONIZATION => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_EXPORT_TOOLBOX => false,
                    self::PARAM_LABEL => $locale->t('lengow_setting.lengow_last_import_manual_title'),
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_ACTION_SYNCHRONIZATION => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_CATALOG => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_MARKETPLACE => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_ACCOUNT_STATUS_DATA => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_OPTION_CMS => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_MARKETPLACE_LIST => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_SETTING => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_PLUGIN_DATA => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_AUTHORIZATION_TOKEN => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
                self::LAST_UPDATE_PLUGIN_MODAL => [
                    self::PARAM_GLOBAL => true,
                    self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
                ],
            ];
        }

        return isset($key, $keys[$key]) ? $keys[$key] : $keys;
    }

    /**
     * Get Lengow value by shop
     *
     * @param string $key Lengow configuration key
     * @param int|null $idLang PrestaShop lang id
     * @param int|null $idShopGroup PrestaShop shop group id
     * @param int|null $idShop PrestaShop shop id
     * @param bool $default default value (compatibility version 1.7)
     *
     * @return mixed
     */
    public static function get($key, $idLang = null, $idShopGroup = null, $idShop = null, $default = false)
    {
        if ($idShop > 1 && Shop::isFeatureActive()) {
            $sql = 'SELECT `value` FROM ' . _DB_PREFIX_ . 'configuration
               WHERE `name` = \'' . pSQL($key) . '\'
               AND `id_shop` = \'' . (int) $idShop . '\'
            ';
            $value = Db::getInstance()->getRow($sql);

            return $value ? $value['value'] : null;
        }

        return parent::get($key, $idLang, $idShopGroup, $idShop, $default);
    }

    /**
     * Check if Lengow configuration key exist
     *
     * @param string $key Lengow configuration key
     *
     * @return bool
     */
    public static function checkKeyExists($key)
    {
        $sql = 'SELECT `value` FROM ' . _DB_PREFIX_ . 'configuration WHERE `name` = \'' . pSQL($key) . '\'';
        $value = Db::getInstance()->getRow($sql);

        return (bool) $value;
    }

    /**
     * Get Valid Account / Access token / Secret token
     *
     * @return array
     */
    public static function getAccessIds()
    {
        $accountId = self::getGlobalValue(self::ACCOUNT_ID);
        $accessToken = self::getGlobalValue(self::ACCESS_TOKEN);
        $secretToken = self::getGlobalValue(self::SECRET);
        if (Tools::strlen($accountId) > 0 && Tools::strlen($accessToken) > 0 && Tools::strlen($secretToken) > 0) {
            return [$accountId, $accessToken, $secretToken];
        }

        return [null, null, null];
    }

    /**
     * Set Valid Account id / Access token / Secret token
     *
     * @param array $accessIds Account id / Access token / Secret token
     *
     * @return bool
     */
    public static function setAccessIds($accessIds)
    {
        $count = 0;
        $listKey = [self::ACCOUNT_ID, self::ACCESS_TOKEN, self::SECRET];
        foreach ($accessIds as $key => $value) {
            if (!in_array($key, $listKey, true)) {
                continue;
            }
            if ($value !== '') {
                ++$count;
                self::updateGlobalValue($key, $value);
            }
        }

        return $count === count($listKey);
    }

    /**
     * Reset access ids for old customer
     */
    public static function resetAccessIds()
    {
        $accessIds = [self::ACCOUNT_ID, self::ACCESS_TOKEN, self::SECRET];
        foreach ($accessIds as $accessId) {
            $value = self::getGlobalValue($accessId);
            if (Tools::strlen($value) > 0) {
                self::updateGlobalValue($accessId, '');
            }
        }
    }

    /**
     * Reset authorization token
     */
    public static function resetAuthorizationToken()
    {
        self::updateGlobalValue(self::AUTHORIZATION_TOKEN, '');
        self::updateGlobalValue(self::LAST_UPDATE_AUTHORIZATION_TOKEN, '');
    }

    /**
     * Check if is a new merchant
     *
     * @return bool
     */
    public static function isNewMerchant()
    {
        list($accountId, $accessToken, $secretToken) = self::getAccessIds();

        return !($accountId !== null && $accessToken !== null && $secretToken !== null);
    }

    /**
     * Get catalog ids for a specific shop
     *
     * @param int $idShop PrestaShop shop id
     *
     * @return array
     */
    public static function getCatalogIds($idShop)
    {
        $catalogIds = [];
        $shopCatalogIds = self::get(self::CATALOG_IDS, null, null, $idShop);
        if ($shopCatalogIds != 0 && Tools::strlen($shopCatalogIds) > 0) {
            $ids = trim(str_replace(["\r\n", ',', '-', '|', ' ', '/'], ';', $shopCatalogIds), ';');
            $ids = array_filter(explode(';', $ids));
            foreach ($ids as $id) {
                if (is_numeric($id) && $id > 0) {
                    $catalogIds[] = (int) $id;
                }
            }
        }

        return $catalogIds;
    }

    /**
     * Set catalog ids for a specific shop
     *
     * @param array $catalogIds Lengow catalog ids
     * @param int $idShop PrestaShop shop id
     *
     * @return bool
     */
    public static function setCatalogIds($catalogIds, $idShop)
    {
        $valueChange = false;
        $shopCatalogIds = self::getCatalogIds($idShop);
        foreach ($catalogIds as $catalogId) {
            if ($catalogId > 0 && is_numeric($catalogId) && !in_array($catalogId, $shopCatalogIds, true)) {
                $shopCatalogIds[] = (int) $catalogId;
                $valueChange = true;
            }
        }
        self::updateValue(self::CATALOG_IDS, implode(';', $shopCatalogIds), false, null, $idShop);

        return $valueChange;
    }

    /**
     * Reset all catalog ids
     */
    public static function resetCatalogIds()
    {
        $shops = LengowShop::getActiveShops();
        foreach ($shops as $shop) {
            if (self::shopIsActive($shop->id)) {
                self::updateValue(self::CATALOG_IDS, '', false, null, $shop->id);
                self::updateValue(self::SHOP_ACTIVE, false, false, null, $shop->id);
            }
        }
    }

    /**
     * Recovers if a shop is active or not
     *
     * @param int|null $idShop PrestaShop shop id
     *
     * @return bool
     */
    public static function shopIsActive($idShop = null)
    {
        return (bool) self::get(self::SHOP_ACTIVE, null, null, $idShop);
    }

    /**
     * Set active shop or not
     *
     * @param int $idShop PrestaShop shop id
     *
     * @return bool
     */
    public static function setActiveShop($idShop)
    {
        $shopIsActive = self::shopIsActive($idShop);
        $catalogIds = self::getCatalogIds($idShop);
        $shopHasCatalog = !empty($catalogIds);
        self::updateValue(self::SHOP_ACTIVE, $shopHasCatalog, false, null, $idShop);

        return $shopIsActive !== $shopHasCatalog;
    }

    /**
     * Recovers if Debug Mode is active or not
     *
     * @return bool
     */
    public static function debugModeIsActive()
    {
        return (bool) self::get(self::DEBUG_MODE_ENABLED);
    }

    /**
     * Get Report Email Address for error report
     *
     * @return array
     */
    public static function getReportEmailAddress()
    {
        $emails = explode(';', self::get(self::REPORT_MAILS));
        if ($emails[0] === '') {
            $emails[0] = self::get('PS_SHOP_EMAIL');
        }

        return $emails;
    }

    /**
     * Get authorized IPs
     *
     * @return array
     */
    public static function getAuthorizedIps()
    {
        $authorizedIps = [];
        $ips = self::getGlobalValue(self::AUTHORIZED_IPS);
        if (!empty($ips)) {
            $authorizedIps = trim(str_replace(["\r\n", ',', '-', '|', ' '], ';', $ips), ';');
            $authorizedIps = array_filter(explode(';', $authorizedIps));
        }

        return $authorizedIps;
    }

    /**
     * Reset all Lengow settings
     *
     * @param bool $overwrite rewrite all Lengow settings
     *
     * @return bool
     */
    public static function resetAll($overwrite = false)
    {
        $shops = LengowShop::findAll(true);
        $keys = self::getKeys();
        foreach ($keys as $key => $value) {
            $val = isset($value[self::PARAM_DEFAULT_VALUE]) ? $value[self::PARAM_DEFAULT_VALUE] : '';
            if (isset($value[self::PARAM_SHOP]) && $value[self::PARAM_SHOP]) {
                foreach ($shops as $shop) {
                    if ($overwrite) {
                        if (isset($value[self::PARAM_DEFAULT_VALUE])) {
                            self::updateValue($key, $val, false, null, $shop['id_shop']);
                        }
                    } else {
                        $oldValue = self::get($key, false, null, $shop['id_shop']);
                        if (!$oldValue) {
                            self::updateValue($key, $val, false, null, $shop['id_shop']);
                        }
                    }
                }
            } elseif ($overwrite) {
                if (isset($value[self::PARAM_DEFAULT_VALUE])) {
                    self::updateValue($key, $val);
                }
            } else {
                $oldValue = self::get($key);
                if (!$oldValue) {
                    self::updateValue($key, $val);
                }
            }
        }
        if ($overwrite) {
            LengowMain::log(LengowLog::CODE_SETTING, LengowMain::setLogMessage('log.setting.setting_reset'));
        } else {
            LengowMain::log(LengowLog::CODE_SETTING, LengowMain::setLogMessage('log.setting.setting_updated'));
        }

        return true;
    }

    /**
     * Delete all Lengow settings
     *
     * @return bool
     */
    public static function deleteAll()
    {
        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'configuration` WHERE name LIKE "LENGOW_%"');

        return true;
    }

    /**
     * Get Values by shop or global
     *
     * @param int|null $idShop PrestaShop shop id
     * @param bool $toolbox get all values for toolbox or not
     *
     * @return array
     */
    public static function getAllValues($idShop = null, $toolbox = false)
    {
        $rows = [];
        $keys = self::getKeys();
        foreach ($keys as $key => $keyParams) {
            $value = null;
            if ((isset($keyParams[self::PARAM_EXPORT]) && !$keyParams[self::PARAM_EXPORT])
                || (
                    $toolbox
                    && isset($keyParams[self::PARAM_EXPORT_TOOLBOX])
                    && !$keyParams[self::PARAM_EXPORT_TOOLBOX]
                )
            ) {
                continue;
            }
            if ($idShop) {
                if (isset($keyParams[self::PARAM_SHOP]) && $keyParams[self::PARAM_SHOP]) {
                    $value = self::get($key, null, false, $idShop);
                    $rows[self::$genericParamKeys[$key]] = self::getValueWithCorrectType($key, $value);
                }
            } elseif (isset($keyParams[self::PARAM_GLOBAL]) && $keyParams[self::PARAM_GLOBAL]) {
                $value = self::getGlobalValue($key);
                $rows[self::$genericParamKeys[$key]] = self::getValueWithCorrectType($key, $value);
            }
        }

        return $rows;
    }

    /**
     * Returns the Lengow url for solution
     *
     * @return string
     */
    public static function getLengowUrl()
    {
        $url = LengowConnector::LENGOW_URL;
        if (self::isProductionMode()) {
            $url = str_replace(
                LengowConnector::TEST_SUFFIX,
                LengowConnector::LIVE_SUFFIX,
                $url
            );
        } else {
            $url = str_replace(
                LengowConnector::LIVE_SUFFIX,
                LengowConnector::TEST_SUFFIX,
                $url
            );
        }

        return $url;
    }

    /**
     * Returns the lengow url for API
     *
     * @return string
     */
    public static function getLengowApiUrl()
    {
        $url = LengowConnector::LENGOW_API_URL;
        if (self::isProductionMode()) {
            $url = str_replace(
                LengowConnector::TEST_SUFFIX,
                LengowConnector::LIVE_SUFFIX,
                $url
            );
        } else {
            $url = str_replace(
                LengowConnector::LIVE_SUFFIX,
                LengowConnector::TEST_SUFFIX,
                $url
            );
        }

        return $url;
    }

    /**
     * Returns the plugin current environment
     *
     * @return string
     */
    public static function getPluginEnvironment()
    {
        $env = self::get(self::PLUGIN_ENV);

        if (!$env) {
            $keyParams = self::getKeys(self::PLUGIN_ENV);
            $env = $keyParams[self::PARAM_DEFAULT_VALUE] ?? 'prod';
        }

        return $env;
    }

    /**
     * @return bool
     */
    public static function isProductionMode()
    {
        return self::getPluginEnvironment() === 'prod';
    }

    /**
     * Get configuration value in correct type
     *
     * @param string $key Lengow configuration key
     * @param string|null $value configuration value for conversion
     *
     * @return array|bool|int|string|string[]|null
     */
    private static function getValueWithCorrectType($key, $value = null)
    {
        $keyParams = self::getKeys($key);
        if (isset($keyParams[self::PARAM_RETURN])) {
            switch ($keyParams[self::PARAM_RETURN]) {
                case self::RETURN_TYPE_BOOLEAN:
                    return (bool) $value;
                case self::RETURN_TYPE_INTEGER:
                    return (int) $value;
                case self::RETURN_TYPE_ARRAY:
                    return !empty($value)
                        ? explode(';', trim(str_replace(["\r\n", ',', ' '], ';', $value), ';'))
                        : [];
                case self::RETURN_TYPE_STRING:
                    return (string) $value;
            }
        }

        return $value;
    }
}
