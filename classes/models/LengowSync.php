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
     * @var string cms type
     */
    const CMS_TYPE = 'prestashop';

    /**
     * @var string sync catalog action
     */
    const SYNC_CATALOG = 'catalog';

    /**
     * @var string sync carrier action
     */
    const SYNC_CARRIER = 'carrier';

    /**
     * @var string sync cms option action
     */
    const SYNC_CMS_OPTION = 'cms_option';

    /**
     * @var string sync status account action
     */
    const SYNC_STATUS_ACCOUNT = 'status_account';

    /**
     * @var string sync marketplace action
     */
    const SYNC_MARKETPLACE = 'marketplace';

    /**
     * @var string sync order action
     */
    const SYNC_ORDER = 'order';

    /**
     * @var string sync action action
     */
    const SYNC_ACTION = 'action';

    /**
     * @var string sync plugin version action
     */
    const SYNC_PLUGIN_DATA = 'plugin';

    /**
     * @var array cache time for catalog, carrier, account status, options and marketplace synchronisation
     */
    protected static $cacheTimes = array(
        self::SYNC_CATALOG => 21600,
        self::SYNC_CARRIER => 86400,
        self::SYNC_CMS_OPTION => 86400,
        self::SYNC_STATUS_ACCOUNT => 86400,
        self::SYNC_MARKETPLACE => 43200,
        self::SYNC_PLUGIN_DATA => 86400,
    );

    /**
     * @var array valid sync actions
     */
    public static $syncActions = array(
        self::SYNC_ORDER,
        self::SYNC_CARRIER,
        self::SYNC_CMS_OPTION,
        self::SYNC_STATUS_ACCOUNT,
        self::SYNC_MARKETPLACE,
        self::SYNC_ACTION,
        self::SYNC_CATALOG,
        self::SYNC_PLUGIN_DATA,
    );

    /**
     * Get Sync Data (Inscription / Update)
     *
     * @return array
     */
    public static function getSyncData()
    {
        $data = array(
            'domain_name' => $_SERVER['SERVER_NAME'],
            'token' => LengowMain::getToken(),
            'type' => self::CMS_TYPE,
            'version' => _PS_VERSION_,
            'plugin_version' => LengowConfiguration::getGlobalValue('LENGOW_VERSION'),
            'email' => LengowConfiguration::get('PS_SHOP_EMAIL'),
            'cron_url' => LengowMain::getImportUrl(),
            'return_url' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],
            'shops' => array(),
        );
        $shopCollection = LengowShop::findAll(true);
        foreach ($shopCollection as $row) {
            $idShop = $row['id_shop'];
            $lengowExport = new LengowExport(array('shop_id' => $idShop));
            $shop = new LengowShop($idShop);
            $data['shops'][$idShop] = array(
                'token' => LengowMain::getToken($idShop),
                'shop_name' => $shop->name,
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
        // save last update date for a specific settings (change synchronisation interval time)
        LengowConfiguration::updateGlobalValue('LENGOW_LAST_SETTING_UPDATE', time());
    }

    /**
     * Sync Lengow catalogs for order synchronisation
     *
     * @param boolean $force force cache update
     * @param boolean $logOutput see log or not
     *
     * @return boolean
     */
    public static function syncCatalog($force = false, $logOutput = false)
    {
        $settingUpdated = false;
        if (LengowConfiguration::isNewMerchant()) {
            return false;
        }
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_CATALOG_UPDATE');
            if ($updatedAt !== null && (time() - (int)$updatedAt) < self::$cacheTimes[self::SYNC_CATALOG]) {
                return false;
            }
        }
        $result = LengowConnector::queryApi(LengowConnector::GET, LengowConnector::API_CMS, array(), '', $logOutput);
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
        // save last update date for a specific settings (change synchronisation interval time)
        if ($settingUpdated) {
            LengowConfiguration::updateGlobalValue('LENGOW_LAST_SETTING_UPDATE', time());
        }
        LengowConfiguration::updateGlobalValue('LENGOW_CATALOG_UPDATE', time());
        return true;
    }

    /**
     * Sync Lengow marketplaces and marketplace carriers
     *
     * @param boolean $force force cache update
     * @param boolean $logOutput see log or not
     *
     * @return boolean
     */
    public static function syncCarrier($force = false, $logOutput = false)
    {
        if (LengowConfiguration::isNewMerchant()) {
            return false;
        }
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_LIST_MARKET_UPDATE');
            if ($updatedAt !== null && (time() - (int)$updatedAt) < self::$cacheTimes[self::SYNC_CARRIER]) {
                return false;
            }
        }
        LengowMarketplace::loadApiMarketplace($force, $logOutput);
        LengowMarketplace::syncMarketplaces();
        LengowCarrier::syncCarrierMarketplace();
        LengowMethod::syncMethodMarketplace();
        LengowCarrier::createDefaultCarrier();
        LengowCarrier::cleanCarrierMarketplaceMatching();
        LengowMethod::cleanMethodMarketplaceMatching();
        LengowConfiguration::updateGlobalValue('LENGOW_LIST_MARKET_UPDATE', time());
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
            $lengowExport = new LengowExport(array('shop_id' => $idShop));
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
     * @param boolean $logOutput see log or not
     *
     * @return boolean
     */
    public static function setCmsOption($force = false, $logOutput = false)
    {
        if (LengowConfiguration::isNewMerchant() || LengowConfiguration::debugModeIsActive()) {
            return false;
        }
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_OPTION_CMS_UPDATE');
            if ($updatedAt !== null && (time() - (int)$updatedAt) < self::$cacheTimes[self::SYNC_CMS_OPTION]) {
                return false;
            }
        }
        $options = Tools::jsonEncode(self::getOptionData());
        LengowConnector::queryApi(LengowConnector::PUT, LengowConnector::API_CMS, array(), $options, $logOutput);
        LengowConfiguration::updateGlobalValue('LENGOW_OPTION_CMS_UPDATE', time());
        return true;
    }

    /**
     * Get Status Account
     *
     * @param boolean $force force cache update
     * @param boolean $logOutput see log or not
     *
     * @return array|false
     */
    public static function getStatusAccount($force = false, $logOutput = false)
    {
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS_UPDATE');
            if ($updatedAt !== null && (time() - (int)$updatedAt) < self::$cacheTimes[self::SYNC_STATUS_ACCOUNT]) {
                return Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS'), true);
            }
        }
        $result = LengowConnector::queryApi(LengowConnector::GET, LengowConnector::API_PLAN, array(), '', $logOutput);
        if (isset($result->isFreeTrial)) {
            $status = array(
                'type' => $result->isFreeTrial ? 'free_trial' : '',
                'day' => (int)$result->leftDaysBeforeExpired < 0 ? 0 : (int)$result->leftDaysBeforeExpired,
                'expired' => (bool)$result->isExpired,
                'legacy' => $result->accountVersion === 'v2' ? true : false
            );
            LengowConfiguration::updateGlobalValue('LENGOW_ACCOUNT_STATUS', Tools::jsonEncode($status));
            LengowConfiguration::updateGlobalValue('LENGOW_ACCOUNT_STATUS_UPDATE', time());
            return $status;
        } else {
            if (LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS_UPDATE')) {
                return Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS'), true);
            }
        }
        return false;
    }

    /**
     * Get marketplace data
     *
     * @param boolean $force force cache update
     * @param boolean $logOutput see log or not
     *
     * @return array|false
     */
    public static function getMarketplaces($force = false, $logOutput = false)
    {
        $filePath = LengowMarketplace::getFilePath();
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_MARKETPLACE_UPDATE');
            if ($updatedAt !== null
                && (time() - (int)$updatedAt) < self::$cacheTimes[self::SYNC_MARKETPLACE]
                && file_exists($filePath)
            ) {
                // recovering data with the marketplaces.json file
                $marketplacesData = Tools::file_get_contents($filePath);
                if ($marketplacesData) {
                    return Tools::jsonDecode($marketplacesData);
                }
            }
        }
        // recovering data with the API
        $result = LengowConnector::queryApi(
            LengowConnector::GET,
            LengowConnector::API_MARKETPLACE,
            array(),
            '',
            $logOutput
        );
        if ($result && is_object($result) && !isset($result->error)) {
            // updated marketplaces.json file
            try {
                $marketplaceFile = new LengowFile(
                    LengowMain::$lengowConfigFolder,
                    LengowMarketplace::$marketplaceJson,
                    'w'
                );
                $marketplaceFile->write(Tools::jsonEncode($result));
                $marketplaceFile->close();
                LengowConfiguration::updateGlobalValue('LENGOW_MARKETPLACE_UPDATE', time());
            } catch (LengowException $e) {
                LengowMain::log(
                    LengowLog::CODE_IMPORT,
                    LengowMain::setLogMessage(
                        'log.import.marketplace_update_failed',
                        array(
                            'decoded_message' => LengowMain::decodeLogMessage(
                                $e->getMessage(),
                                LengowTranslation::DEFAULT_ISO_CODE
                            )
                        )
                    ),
                    $logOutput
                );
            }
            return $result;
        } else {
            // if the API does not respond, use marketplaces.json if it exists
            if (file_exists($filePath)) {
                $marketplacesData = Tools::file_get_contents($filePath);
                if ($marketplacesData) {
                    return Tools::jsonDecode($marketplacesData);
                }
            }
        }
        return false;
    }

    /**
     * Get Lengow plugin data (last version and download link)
     *
     * @param boolean $force force cache update
     * @param boolean $logOutput see log or not
     *
     * @return array|false
     */
    public static function getPluginData($force = false, $logOutput = false)
    {
        if (LengowConfiguration::isNewMerchant()) {
            return false;
        }
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_PLUGIN_DATA_UPDATE');
            if ($updatedAt !== null && (time() - (int)$updatedAt) < self::$cacheTimes[self::SYNC_PLUGIN_DATA]) {
                return Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_PLUGIN_DATA'), true);
            }
        }
        $plugins = LengowConnector::queryApi(
            LengowConnector::GET,
            LengowConnector::API_PLUGIN,
            array(),
            '',
            $logOutput
        );
        if ($plugins) {
            $pluginData = false;
            foreach ($plugins as $plugin) {
                if ($plugin->type === self::CMS_TYPE) {
                    $pluginData = array(
                        'version' => $plugin->version,
                        'download_link' => $plugin->archive,
                    );
                    break;
                }
            }
            if ($pluginData) {
                LengowConfiguration::updateGlobalValue('LENGOW_PLUGIN_DATA', Tools::jsonEncode($pluginData));
                LengowConfiguration::updateGlobalValue('LENGOW_PLUGIN_DATA_UPDATE', time());
                return $pluginData;
            }
        } else {
            if (LengowConfiguration::getGlobalValue('LENGOW_PLUGIN_DATA')) {
                return Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_PLUGIN_DATA'), true);
            }
        }
        return false;
    }
}
