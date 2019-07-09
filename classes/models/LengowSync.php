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
 * Lengow Sync Class
 */
class LengowSync
{
    /**
     * @var array cache time for catalog, carrier, statistic, account status, cms options and marketplace synchronisation
     */
    protected static $cacheTimes = array(
        'catalog' => 21600,
        'carrier' => 86400,
        'cms_option' => 86400,
        'status_account' => 86400,
        'statistic' => 86400,
        'marketplace' => 43200,
    );

    /**
     * @var array valid sync actions
     */
    public static $syncActions = array(
        'order',
        'carrier',
        'cms_option',
        'status_account',
        'statistic',
        'action',
        'catalog',
    );

    /**
     * Get Sync Data (Inscription / Update)
     *
     * @return array
     */
    public static function getSyncData()
    {
        $data = array(
            'domain_name' => $_SERVER["SERVER_NAME"],
            'token' => LengowMain::getToken(),
            'type' => 'prestashop',
            'version' => _PS_VERSION_,
            'plugin_version' => LengowConfiguration::getGlobalValue('LENGOW_VERSION'),
            'email' => LengowConfiguration::get('PS_SHOP_EMAIL'),
            'cron_url' => LengowMain::getImportUrl(),
            'return_url' => 'http://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"],
            'shops' => array(),
        );
        $shopCollection = LengowShop::findAll(true);
        foreach ($shopCollection as $row) {
            $idShop = $row['id_shop'];
            $lengowExport = new LengowExport(array('shop_id' => $idShop));
            $shop = new LengowShop($idShop);
            $data['shops'][$idShop] = array(
                'token' => LengowMain::getToken($idShop),
                'shop_name' =>  $shop->name,
                'domain_url' => $shop->domain,
                'feed_url' => LengowMain::getExportUrl($shop->id),
                'total_product_number' => $lengowExport->getTotalProduct(),
                'exported_product_number' => $lengowExport->getTotalExportProduct(),
                'enabled' => LengowConfiguration::shopIsActive($idShop),
            );
        }
        return $data;
    }

    /**
     * Set shop configuration key from Lengow
     *
     * @param array $params Lengow API credentials
     */
    public static function sync($params)
    {
        LengowConfiguration::setAccessIds(
            array(
                'LENGOW_ACCOUNT_ID' => $params['account_id'],
                'LENGOW_ACCESS_TOKEN' => $params['access_token'],
                'LENGOW_SECRET_TOKEN' => $params['secret_token'],
            )
        );
        if (isset($params['shops'])) {
            foreach ($params['shops'] as $shopToken => $shopCatalogIds) {
                $shop = LengowShop::findByToken($shopToken);
                if ($shop) {
                    LengowConfiguration::setCatalogIds($shopCatalogIds['catalog_ids'], (int)$shop->id);
                    LengowConfiguration::setActiveShop((int)$shop->id);
                }
            }
        }
        // Save last update date for a specific settings (change synchronisation interval time)
        LengowConfiguration::updateGlobalValue('LENGOW_LAST_SETTING_UPDATE', date('Y-m-d H:i:s'));
    }

    /**
     * Sync Lengow catalogs for order synchronisation
     *
     * @param boolean $force force cache update
     *
     * @return boolean
     */
    public static function syncCatalog($force = false)
    {
        $settingUpdated = false;
        if (LengowConnector::isNewMerchant()) {
            return false;
        }
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_CATALOG_UPDATE');
            if (!is_null($updatedAt) && (time() - strtotime($updatedAt)) < self::$cacheTimes['catalog']) {
                return false;
            }
        }
        $result = LengowConnector::queryApi('get', '/v3.1/cms');
        if (isset($result->cms)) {
            $cmsToken = LengowMain::getToken();
            foreach ($result->cms as $cms) {
                if ($cms->token === $cmsToken) {
                    foreach ($cms->shops as $cmsShop) {
                        $shop = LengowShop::findByToken($cmsShop->token);
                        if ($shop) {
                            $catalogIdsChange = LengowConfiguration::setCatalogIds(
                                $cmsShop->catalog_ids,
                                (int)$shop->id
                            );
                            $activeStoreChange = LengowConfiguration::setActiveShop((int)$shop->id);
                            if (!$settingUpdated && ($catalogIdsChange || $activeStoreChange)) {
                                $settingUpdated = true;
                            }
                        }
                    }
                    break;
                }
            }
        }
        // Save last update date for a specific settings (change synchronisation interval time)
        if ($settingUpdated) {
            LengowConfiguration::updateGlobalValue('LENGOW_LAST_SETTING_UPDATE', date('Y-m-d H:i:s'));
        }
        LengowConfiguration::updateGlobalValue('LENGOW_CATALOG_UPDATE', date('Y-m-d H:i:s'));
        return true;
    }

    /**
     * Sync Lengow marketplaces and marketplace carriers
     *
     * @param boolean $force force cache update
     *
     * @return boolean
     */
    public static function syncCarrier($force = false)
    {
        if (LengowConnector::isNewMerchant()) {
            return false;
        }
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_LIST_MARKET_UPDATE');
            if (!is_null($updatedAt) && (time() - strtotime($updatedAt)) < self::$cacheTimes['carrier']) {
                return false;
            }
        }
        LengowMarketplace::loadApiMarketplace($force);
        LengowMarketplace::syncMarketplaces();
        LengowCarrier::syncCarrierMarketplace();
        LengowMethod::syncMethodMarketplace();
        LengowCarrier::createDefaultCarrier();
        LengowCarrier::cleanCarrierMarketplaceMatching();
        LengowMethod::cleanMethodMarketplaceMatching();
        LengowConfiguration::updateGlobalValue('LENGOW_LIST_MARKET_UPDATE', date('Y-m-d H:i:s'));
        return true;
    }

    /**
     * Get options for all shops
     *
     * @return array
     */
    public static function getOptionData()
    {
        $data = array(
            'token' => LengowMain::getToken(),
            'version' => _PS_VERSION_,
            'plugin_version' => LengowConfiguration::getGlobalValue('LENGOW_VERSION'),
            'options' => LengowConfiguration::getAllValues(),
            'shops' => array(),
        );
        $shopCollection = LengowShop::findAll(true);
        foreach ($shopCollection as $row) {
            $idShop = $row['id_shop'];
            $lengowExport = new LengowExport(array("shop_id" => $idShop));
            $data['shops'][] = array(
                'token' => LengowMain::getToken($idShop),
                'enabled' => LengowConfiguration::shopIsActive($idShop),
                'total_product_number' => $lengowExport->getTotalProduct(),
                'exported_product_number' => $lengowExport->getTotalExportProduct(),
                'options' => LengowConfiguration::getAllValues($idShop),
            );
        }
        return $data;
    }

    /**
     * Set CMS options
     *
     * @param boolean $force force cache update
     *
     * @return boolean
     */
    public static function setCmsOption($force = false)
    {
        if (LengowConnector::isNewMerchant() || LengowConfiguration::getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED')) {
            return false;
        }
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_OPTION_CMS_UPDATE');
            if (!is_null($updatedAt) && (time() - strtotime($updatedAt)) < self::$cacheTimes['cms_option']) {
                return false;
            }
        }
        $options = Tools::jsonEncode(self::getOptionData());
        LengowConnector::queryApi('put', '/v3.1/cms', array(), $options);
        LengowConfiguration::updateGlobalValue('LENGOW_OPTION_CMS_UPDATE', date('Y-m-d H:i:s'));
        return true;
    }

    /**
     * Get Status Account
     *
     * @param boolean $force force cache update
     *
     * @return array|false
     */
    public static function getStatusAccount($force = false)
    {
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS_UPDATE');
            if (!is_null($updatedAt) && (time() - strtotime($updatedAt)) < self::$cacheTimes['status_account']) {
                return Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS'), true);
            }
        }
        $result = LengowConnector::queryApi('get', '/v3.0/plans');
        if (isset($result->isFreeTrial)) {
            $status = array(
                'type' => $result->isFreeTrial ? 'free_trial' : '',
                'day' => (int)$result->leftDaysBeforeExpired < 0 ? 0 : (int)$result->leftDaysBeforeExpired,
                'expired' => (bool)$result->isExpired,
                'legacy' => $result->accountVersion === 'v2' ? true : false
            );
            LengowConfiguration::updateGlobalValue('LENGOW_ACCOUNT_STATUS', Tools::jsonEncode($status));
            LengowConfiguration::updateGlobalValue('LENGOW_ACCOUNT_STATUS_UPDATE', date('Y-m-d H:i:s'));
            return $status;
        } else {
            if (LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS_UPDATE')) {
                return Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS'), true);
            }
        }
        return false;
    }

    /**
     * Get Statistic with all shop
     *
     * @param boolean $force force cache update
     *
     * @return array
     */
    public static function getStatistic($force = false)
    {
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_ORDER_STAT_UPDATE');
            if (!is_null($updatedAt) && (time() - strtotime($updatedAt)) < self::$cacheTimes['statistic']) {
                return Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ORDER_STAT'), true);
            }
        }
        $currencyId = 0;
        $result = LengowConnector::queryApi(
            'get',
            '/v3.0/stats',
            array(
                'date_from' => date('c', strtotime(date('Y-m-d') . ' -10 years')),
                'date_to' => date('c'),
                'metrics' => 'year',
            )
        );
        if (isset($result->level0)) {
            $stats = $result->level0[0];
            $return = array(
                'total_order' => $stats->revenue,
                'nb_order' => (int)$stats->transactions,
                'currency' => $result->currency->iso_a3,
                'available' => false,
            );
        } else {
            if (LengowConfiguration::getGlobalValue('LENGOW_ORDER_STAT_UPDATE')) {
                return Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ORDER_STAT'), true);
            } else {
                return array(
                    'total_order' => 0,
                    'nb_order' => 0,
                    'currency' => '',
                    'available' => false,
                );
            }
        }
        if ($return['total_order'] > 0 || $return['nb_order'] > 0) {
            $return['available'] = true;
        }
        if ($return['currency']) {
            $currencyId = LengowCurrency::getIdBySign($return['currency']);
        }
        if ($currencyId > 0) {
            $return['total_order'] = Tools::displayPrice($return['total_order'], new Currency($currencyId));
        } else {
            $return['total_order'] = number_format($return['total_order'], 2, ',', ' ');
        }
        LengowConfiguration::updateGlobalValue('LENGOW_ORDER_STAT', Tools::jsonEncode($return));
        LengowConfiguration::updateGlobalValue('LENGOW_ORDER_STAT_UPDATE', date('Y-m-d H:i:s'));
        return $return;
    }

    /**
     * Get marketplace data
     *
     * @param boolean $force force cache update
     *
     * @return array|false
     */
    public static function getMarketplaces($force = false)
    {
        $filePath = LengowMarketplace::getFilePath();
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_MARKETPLACE_UPDATE');
            if (!is_null($updatedAt)
                && (time() - strtotime($updatedAt)) < self::$cacheTimes['marketplace']
                && file_exists($filePath)
            ) {
                // Recovering data with the marketplaces.json file
                $marketplacesData = Tools::file_get_contents($filePath);
                if ($marketplacesData) {
                    return Tools::jsonDecode($marketplacesData);
                }
            }
        }
        // Recovering data with the API
        $result = LengowConnector::queryApi('get', '/v3.0/marketplaces');
        if ($result && is_object($result) && !isset($result->error)) {
            // Updated marketplaces.json file
            try {
                $marketplaceFile = new LengowFile(
                    LengowMain::$lengowConfigFolder,
                    LengowMarketplace::$marketplaceJson,
                    'w'
                );
                $marketplaceFile->write(Tools::jsonEncode($result));
                $marketplaceFile->close();
                LengowConfiguration::updateGlobalValue('LENGOW_MARKETPLACE_UPDATE', date('Y-m-d H:i:s'));
            } catch (LengowException $e) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage(
                        'log.import.marketplace_update_failed',
                        array('decoded_message' => LengowMain::decodeLogMessage($e->getMessage(), 'en'))
                    )
                );
            }
            return $result;
        } else {
            // If the API does not respond, use marketplaces.json if it exists
            if (file_exists($filePath)) {
                $marketplacesData = Tools::file_get_contents($filePath);
                if ($marketplacesData) {
                    return Tools::jsonDecode($marketplacesData);
                }
            }
        }
        return false;
    }
}
