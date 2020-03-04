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
 * Lengow Configuration Class
 */
class LengowConfiguration extends Configuration
{
    /**
     * Get all Lengow configuration keys
     *
     * @return array
     */
    public static function getKeys()
    {
        static $keys = null;
        if ($keys === null) {
            $langId = (int)Context::getContext()->cookie->id_lang;
            $locale = new LengowTranslation();
            $orderStates = array();
            $allOrderStates = OrderState::getOrderStates($langId);
            foreach ($allOrderStates as $orderState) {
                $orderStates[] = array(
                    'id' => $orderState['id_order_state'],
                    'text' => $orderState['name'],
                );
            }
            $exportFormats = array();
            foreach (LengowFeed::$availableFormats as $value) {
                $exportFormats[] = array(
                    'id' => $value,
                    'text' => $value,
                );
            }
            $trackers = array();
            foreach (LengowMain::$trackerChoiceId as $idTracker => $tracker) {
                $trackers[] = array(
                    'id' => $idTracker,
                    'text' => $tracker,
                );
            }
            $carriers = array();
            $activeCarriers = LengowCarrier::getActiveCarriers();
            foreach ($activeCarriers as $idCarrier => $carrier) {
                $carriers[] = array(
                    'id' => $idCarrier,
                    'text' => $carrier['name'],
                );
            }
            $keys = array(
                'LENGOW_ACCOUNT_ID' => array(
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_account_id_title'),
                ),
                'LENGOW_ACCESS_TOKEN' => array(
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_access_token_title'),
                    'secret' => true,
                ),
                'LENGOW_SECRET_TOKEN' => array(
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_secret_token_title'),
                    'secret' => true,
                ),
                'LENGOW_AUTH_TOKEN' => array(
                    'global' => true,
                    'export' => false,
                ),
                'LENGOW_LAST_AUTH_TOKEN_UPDATE' => array(
                    'global' => true,
                    'export' => false,
                ),
                'LENGOW_SHOP_ACTIVE' => array(
                    'type' => 'checkbox',
                    'shop' => true,
                    'label' => $locale->t('lengow_setting.lengow_shop_active_title'),
                ),
                'LENGOW_CATALOG_ID' => array(
                    'shop' => true,
                    'label' => $locale->t('lengow_setting.lengow_catalog_id_title'),
                    'legend' => $locale->t('lengow_setting.lengow_catalog_id_legend'),
                    'update' => true,
                ),
                'LENGOW_SHOP_TOKEN' => array(
                    'readonly' => true,
                    'shop' => true,
                    'label' => $locale->t('lengow_setting.lengow_shop_token_title'),
                ),
                'LENGOW_EXPORT_SELECTION_ENABLED' => array(
                    'type' => 'checkbox',
                    'readonly' => true,
                    'shop' => true,
                    'label' => $locale->t('lengow_setting.lengow_export_selection_enabled_title'),
                    'legend' => $locale->t('lengow_setting.lengow_export_selection_enabled_legend'),
                    'default_value' => 0,
                ),
                'LENGOW_EXPORT_VARIATION_ENABLED' => array(
                    'type' => 'checkbox',
                    'readonly' => true,
                    'shop' => true,
                    'label' => $locale->t('lengow_setting.lengow_export_variation_enabled_title'),
                    'legend' => $locale->t('lengow_setting.lengow_export_variation_enabled_legend'),
                    'default_value' => 1,
                ),
                'LENGOW_EXPORT_OUT_STOCK' => array(
                    'type' => 'checkbox',
                    'readonly' => true,
                    'shop' => true,
                    'label' => $locale->t('lengow_setting.lengow_export_out_stock_title'),
                    'legend' => $locale->t('lengow_setting.lengow_export_out_stock_legend'),
                    'default_value' => 1,
                ),
                'LENGOW_EXPORT_INACTIVE' => array(
                    'type' => 'checkbox',
                    'readonly' => true,
                    'shop' => true,
                    'label' => $locale->t('lengow_setting.lengow_export_inactive_title'),
                    'legend' => $locale->t('lengow_setting.lengow_export_inactive_legend'),
                    'default_value' => 0,
                ),
                'LENGOW_EXPORT_FORMAT' => array(
                    'type' => 'select',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_export_format_title'),
                    'default_value' => LengowFeed::FORMAT_CSV,
                    'collection' => $exportFormats,
                ),
                'LENGOW_EXPORT_FILE_ENABLED' => array(
                    'type' => 'checkbox',
                    'readonly' => false,
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_export_file_enabled_title'),
                    'legend' => $locale->t('lengow_setting.lengow_export_file_enabled_legend'),
                    'default_value' => 0,
                ),
                'LENGOW_EXPORT_CARRIER_DEFAULT' => array(
                    'type' => 'select',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_export_carrier_default_title'),
                    'default_value' => !empty($carriers) ? (int)$carriers[0]['id'] : '',
                    'collection' => $carriers,
                ),
                'LENGOW_LAST_EXPORT' => array(
                    'readonly' => true,
                    'shop' => true,
                    'label' => $locale->t('lengow_setting.lengow_last_export_title'),
                ),
                'LENGOW_ORDER_ID_PROCESS' => array(
                    'type' => 'select',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_order_id_process_title'),
                    'default_value' => 2,
                    'collection' => $orderStates,
                ),
                'LENGOW_ORDER_ID_SHIPPED' => array(
                    'type' => 'select',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_order_id_shipped_title'),
                    'default_value' => 4,
                    'collection' => $orderStates,
                ),
                'LENGOW_ORDER_ID_CANCEL' => array(
                    'type' => 'select',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_order_id_cancel_title'),
                    'default_value' => 6,
                    'collection' => $orderStates,
                ),
                'LENGOW_ORDER_ID_SHIPPEDBYMP' => array(
                    'type' => 'select',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_order_id_shippedbymp_title'),
                    'default_value' => 4,
                    'collection' => $orderStates,
                ),
                'LENGOW_IMPORT_FORCE_PRODUCT' => array(
                    'type' => 'checkbox',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_import_force_product_title'),
                    'legend' => $locale->t('lengow_setting.lengow_import_force_product_legend'),
                    'default_value' => 1,
                ),
                'LENGOW_CARRIER_SEMANTIC_ENABLE' => array(
                    'type' => 'checkbox',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_carrier_semantic_enable_title'),
                    'legend' => $locale->t('lengow_setting.lengow_carrier_semantic_enable_legend'),
                    'default_value' => 0,
                ),
                'LENGOW_IMPORT_DAYS' => array(
                    'type' => 'day',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_import_days_title'),
                    'default_value' => 3,
                    'update' => true,
                ),
                'LENGOW_IMPORT_PROCESSING_FEE' => array(
                    'type' => 'checkbox',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_import_processing_fee_title'),
                    'legend' => $locale->t('lengow_setting.lengow_import_processing_fee_legend'),
                    'default_value' => 1,
                ),
                'LENGOW_IMPORT_DEBUG_ENABLED' => array(
                    'type' => 'checkbox',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_import_debug_enabled_title'),
                    'default_value' => 0,
                ),
                'LENGOW_IMPORT_SHIP_MP_ENABLED' => array(
                    'type' => 'checkbox',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_import_ship_mp_enabled_title'),
                    'default_value' => 0,
                ),
                'LENGOW_IMPORT_STOCK_SHIP_MP' => array(
                    'type' => 'checkbox',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_import_stock_ship_mp_title'),
                    'legend' => $locale->t('lengow_setting.lengow_import_stock_ship_mp_legend'),
                    'default_value' => 0,
                ),
                'LENGOW_REPORT_MAIL_ENABLED' => array(
                    'type' => 'checkbox',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_report_mail_enabled_title'),
                    'default_value' => 1,
                ),
                'LENGOW_REPORT_MAIL_ADDRESS' => array(
                    'type' => 'text',
                    'global' => true,
                    'placeholder' => $locale->t('lengow_setting.lengow_report_mail_address_title'),
                    'legend' => $locale->t('lengow_setting.lengow_report_mail_address_legend'),
                    'default_value' => '',
                ),
                'LENGOW_IMPORT_SINGLE_ENABLED' => array(
                    'type' => 'checkbox',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_import_single_enabled_title'),
                    'legend' => $locale->t('lengow_setting.lengow_import_single_enabled_legend'),
                    'default_value' => (int)(version_compare(_PS_VERSION_, '1.5.2', '>') &&
                        version_compare(_PS_VERSION_, '1.5.5', '<')),
                ),
                'LENGOW_IMPORT_IN_PROGRESS' => array(
                    'readonly' => true,
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_import_in_progress_title'),
                ),
                'LENGOW_LAST_IMPORT_CRON' => array(
                    'readonly' => true,
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_last_import_cron_title'),
                ),
                'LENGOW_LAST_IMPORT_MANUAL' => array(
                    'readonly' => true,
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_last_import_manual_title'),
                ),
                'LENGOW_GLOBAL_TOKEN' => array(
                    'readonly' => true,
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_global_token_title'),
                ),
                'LENGOW_AUTHORIZED_IP' => array(
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_authorized_ip_title'),
                    'legend' => $locale->t('lengow_setting.lengow_authorized_ip_legend'),
                ),
                'LENGOW_TRACKING_ENABLED' => array(
                    'type' => 'checkbox',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_tracking_enabled_title'),
                    'default_value' => 0,
                ),
                'LENGOW_TRACKING_ID' => array(
                    'type' => 'select',
                    'global' => true,
                    'label' => $locale->t('lengow_setting.lengow_tracking_id_title'),
                    'legend' => $locale->t('lengow_setting.lengow_tracking_id_legend'),
                    'default_value' => 'id',
                    'collection' => $trackers,
                ),
                'LENGOW_VERSION' => array(
                    'type' => 'text',
                    'global' => true,
                ),
                'LENGOW_CATALOG_UPDATE' => array(
                    'global' => true,
                ),
                'LENGOW_MARKETPLACE_UPDATE' => array(
                    'global' => true,
                ),
                'LENGOW_INSTALLATION_IN_PROGRESS' => array(
                    'export' => false,
                    'global' => true,
                ),
                'LENGOW_ACCOUNT_STATUS' => array(
                    'export' => false,
                    'global' => true,
                ),
                'LENGOW_ACCOUNT_STATUS_UPDATE' => array(
                    'global' => true,
                ),
                'LENGOW_OPTION_CMS_UPDATE' => array(
                    'global' => true,
                ),
                'LENGOW_LIST_MARKET_UPDATE' => array(
                    'global' => true,
                ),
                'LENGOW_LAST_SETTING_UPDATE' => array(
                    'global' => true,
                ),
                'LENGOW_LAST_ACTION_SYNC' => array(
                    'global' => true,
                ),
                'LENGOW_PLUGIN_DATA' => array(
                    'export' => false,
                    'global' => true,
                ),
                'LENGOW_PLUGIN_DATA_UPDATE' => array(
                    'export' => false,
                    'global' => true,
                ),
                'LENGOW_STATE_ERROR' => array(
                    'export' => false,
                    'global' => true,
                ),
            );
        }
        return $keys;
    }

    /**
     * Get Lengow global value
     *
     * @param string $key Lengow configuration key
     * @param integer|null $idLang Prestashop lang id
     *
     * @return mixed
     */
    public static function getGlobalValue($key, $idLang = null)
    {
        if (_PS_VERSION_ < '1.5') {
            return parent::get($key, $idLang);
        } else {
            return parent::getGlobalValue($key, $idLang);
        }
    }

    /**
     * Get Lengow value by shop
     *
     * @param string $key Lengow configuration key
     * @param integer|null $idLang Prestashop lang id
     * @param integer|null $idShopGroup Prestashop shop group id
     * @param integer|null $idShop Prestashop shop id
     * @param boolean $default default value (compatibility version 1.7)
     *
     * @return mixed
     */
    public static function get($key, $idLang = null, $idShopGroup = null, $idShop = null, $default = false)
    {
        if (_PS_VERSION_ < '1.5') {
            return parent::get($key, $idLang);
        } else {
            if (Shop::isFeatureActive() && $idShop > 1) {
                $sql = 'SELECT `value` FROM ' . _DB_PREFIX_ . 'configuration
                   WHERE `name` = \'' . pSQL($key) . '\'
                   AND `id_shop` = \'' . (int)$idShop . '\'
                ';
                $value = Db::getInstance()->getRow($sql);
                if ($value) {
                    return $value['value'];
                } else {
                    return null;
                }
            } else {
                return parent::get($key, $idLang, $idShopGroup, $idShop, $default);
            }
        }
    }

    /**
     * Update Lengow global value
     *
     * @param string $key Lengow configuration key
     * @param mixed $values Lengow configuration value
     * @param boolean $html compatibility new version
     */
    public static function updateGlobalValue($key, $values, $html = false)
    {
        if (_PS_VERSION_ < '1.5') {
            parent::updateValue($key, $values, $html);
        } else {
            parent::updateGlobalValue($key, $values, $html);
        }
    }

    /**
     * Update Lengow value by shop
     *
     * @param string $key Lengow configuration key
     * @param mixed $values Lengow configuration value
     * @param boolean $html compatibility new version
     * @param integer|null $idShopGroup Prestashop shop group id
     * @param integer|null $idShop Prestashop shop id
     */
    public static function updateValue($key, $values, $html = false, $idShopGroup = null, $idShop = null)
    {
        if (_PS_VERSION_ < '1.5') {
            parent::updateValue($key, $values, $html);
        } else {
            parent::updateValue($key, $values, $html, $idShopGroup, $idShop);
        }
    }

    /**
     * Check if Lengow configuration key exist
     *
     * @param string $key Lengow configuration key
     *
     * @return boolean
     */
    public static function checkKeyExists($key)
    {
        $sql = 'SELECT `value` FROM ' . _DB_PREFIX_ . 'configuration WHERE `name` = \'' . pSQL($key) . '\'';
        $value = Db::getInstance()->getRow($sql);
        return $value ? true : false;
    }

    /**
     * Get Valid Account / Access token / Secret token
     *
     * @return array
     */
    public static function getAccessIds()
    {
        $accountId = self::getGlobalValue('LENGOW_ACCOUNT_ID');
        $accessToken = self::getGlobalValue('LENGOW_ACCESS_TOKEN');
        $secretToken = self::getGlobalValue('LENGOW_SECRET_TOKEN');
        if (Tools::strlen($accountId) > 0 && Tools::strlen($accessToken) > 0 && Tools::strlen($secretToken) > 0) {
            return array($accountId, $accessToken, $secretToken);
        } else {
            return array(null, null, null);
        }
    }

    /**
     * Set Valid Account id / Access token / Secret token
     *
     * @param array $accessIds Account id / Access token / Secret token
     */
    public static function setAccessIds($accessIds)
    {
        $listKey = array(
            'LENGOW_ACCOUNT_ID',
            'LENGOW_ACCESS_TOKEN',
            'LENGOW_SECRET_TOKEN',
        );
        foreach ($accessIds as $key => $value) {
            if (!in_array($key, array_keys($listKey))) {
                continue;
            }
            if (Tools::strlen($value) > 0) {
                self::updateGlobalValue($key, $value);
            }
        }
    }

    /**
     * Reset access ids for old customer
     */
    public static function resetAccessIds()
    {
        $accessIds = array(
            'LENGOW_ACCOUNT_ID',
            'LENGOW_ACCESS_TOKEN',
            'LENGOW_SECRET_TOKEN',
        );
        foreach ($accessIds as $accessId) {
            $value = self::getGlobalValue($accessId);
            if (Tools::strlen($value) > 0) {
                self::updateGlobalValue($accessId, '');
            }
        }
    }

    /**
     * Check if is a new merchant
     *
     * @return boolean
     */
    public static function isNewMerchant()
    {
        list($accountId, $accessToken, $secretToken) = self::getAccessIds();
        if ($accountId !== null && $accessToken !== null && $secretToken !== null) {
            return false;
        }
        return true;
    }

    /**
     * Get catalog ids for a specific shop
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return array
     */
    public static function getCatalogIds($idShop)
    {
        $catalogIds = array();
        $shopCatalogIds = self::get('LENGOW_CATALOG_ID', null, null, $idShop);
        if (Tools::strlen($shopCatalogIds) > 0 && $shopCatalogIds != 0) {
            $ids = trim(str_replace(array("\r\n", ',', '-', '|', ' ', '/'), ';', $shopCatalogIds), ';');
            $ids = array_filter(explode(';', $ids));
            foreach ($ids as $id) {
                if (is_numeric($id) && $id > 0) {
                    $catalogIds[] = (int)$id;
                }
            }
        }
        return $catalogIds;
    }

    /**
     * Set catalog ids for a specific shop
     *
     * @param array $catalogIds Lengow catalog ids
     * @param integer $idShop Prestashop shop id
     *
     * @return boolean
     */
    public static function setCatalogIds($catalogIds, $idShop)
    {
        $valueChange = false;
        $shopCatalogIds = self::getCatalogIds($idShop);
        foreach ($catalogIds as $catalogId) {
            if (!in_array($catalogId, $shopCatalogIds) && is_numeric($catalogId) && $catalogId > 0) {
                $shopCatalogIds[] = (int)$catalogId;
                $valueChange = true;
            }
        }
        self::updateValue('LENGOW_CATALOG_ID', implode(';', $shopCatalogIds), false, null, $idShop);
        return $valueChange;
    }

    /**
     * Recovers if a shop is active or not
     *
     * @param integer|null $idShop Prestashop shop id
     *
     * @return boolean
     */
    public static function shopIsActive($idShop = null)
    {
        return (bool)self::get('LENGOW_SHOP_ACTIVE', null, null, $idShop);
    }

    /**
     * Set active shop or not
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return boolean
     */
    public static function setActiveShop($idShop)
    {
        $shopIsActive = self::shopIsActive($idShop);
        $catalogIds = self::getCatalogIds($idShop);
        $shopHasCatalog = !empty($catalogIds);
        self::updateValue('LENGOW_SHOP_ACTIVE', $shopHasCatalog, false, null, $idShop);
        return $shopIsActive !== $shopHasCatalog ? true : false;
    }

    /**
     * Recovers if Debug Mode is active or not
     *
     * @return boolean
     */
    public static function debugModeIsActive()
    {
        return (bool)self::get('LENGOW_IMPORT_DEBUG_ENABLED');
    }

    /**
     * Get Report Email Address for error report
     *
     * @return array
     */
    public static function getReportEmailAddress()
    {
        $emails = explode(';', self::get('LENGOW_REPORT_MAIL_ADDRESS'));
        if ($emails[0] === '') {
            $emails[0] = self::get('PS_SHOP_EMAIL');
        }
        return $emails;
    }

    /**
     * Reset all Lengow settings
     *
     * @param boolean $overwrite rewrite all Lengow settings
     *
     * @return boolean
     */
    public static function resetAll($overwrite = false)
    {
        $shops = LengowShop::findAll(true);
        $keys = self::getKeys();
        foreach ($keys as $key => $value) {
            $val = isset($value['default_value']) ? $value['default_value'] : '';
            if (isset($value['shop']) && $value['shop']) {
                foreach ($shops as $shop) {
                    if ($overwrite) {
                        if (isset($value['default_value'])) {
                            self::updateValue($key, $val, false, null, $shop['id_shop']);
                        }
                    } else {
                        $oldValue = self::get($key, false, null, $shop['id_shop']);
                        if (!$oldValue || $oldValue === null) {
                            self::updateValue($key, $val, false, null, $shop['id_shop']);
                        }
                    }
                }
            } else {
                if ($overwrite) {
                    if (isset($value['default_value'])) {
                        self::updateValue($key, $val);
                    }
                } else {
                    $oldValue = self::get($key);
                    if (!$oldValue || $oldValue === null) {
                        self::updateValue($key, $val);
                    }
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
     * @return boolean
     */
    public static function deleteAll()
    {
        $keys = self::getKeys();
        LengowMain::log(LengowLog::CODE_SETTING, LengowMain::setLogMessage('log.setting.setting_delete'));
        foreach ($keys as $key => $value) {
            // this line is useless, but Prestashop validator require it
            $value = $value;
            self::deleteByName($key);
        }
        return true;
    }

    /**
     * Get Values by shop or global
     *
     * @param integer|null $idShop Prestashop shop id
     *
     * @return array
     */
    public static function getAllValues($idShop = null)
    {
        $rows = array();
        $keys = self::getKeys();
        foreach ($keys as $key => $value) {
            if (isset($value['export']) && !$value['export']) {
                continue;
            }
            if ($idShop) {
                if (isset($value['shop']) && $value['shop']) {
                    $rows[$key] = self::get($key, null, false, $idShop);
                }
            } else {
                if (isset($value['global']) && $value['global']) {
                    $rows[$key] = self::getGlobalValue($key);
                }
            }
        }
        return $rows;
    }
}
