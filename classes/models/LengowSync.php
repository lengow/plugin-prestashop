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
 * Lengow Sync Class
 */
class LengowSync
{
    /**
     * @var string cms type
     */
    const CMS_TYPE = 'prestashop';

    /* Sync actions */
    const SYNC_CATALOG = 'catalog';
    const SYNC_CARRIER = 'carrier';
    const SYNC_CMS_OPTION = 'cms_option';
    const SYNC_STATUS_ACCOUNT = 'status_account';
    const SYNC_MARKETPLACE = 'marketplace';
    const SYNC_ORDER = 'order';
    const SYNC_ACTION = 'action';
    const SYNC_PLUGIN_DATA = 'plugin';

    /* Plugin link types */
    const LINK_TYPE_HELP_CENTER = 'help_center';
    const LINK_TYPE_CHANGELOG = 'changelog';
    const LINK_TYPE_UPDATE_GUIDE = 'update_guide';
    const LINK_TYPE_SUPPORT = 'support';

    /* Default plugin links */
    const LINK_HELP_CENTER = 'https://support.lengow.com/kb/guide/en/ob0HZ6F7so';
    const LINK_CHANGELOG = 'https://support.lengow.com/kb/guide/en/clDRVyFUBk';
    const LINK_UPDATE_GUIDE = 'https://support.lengow.com/kb/guide/en/ob0HZ6F7so/Steps/25864,121742';
    const LINK_SUPPORT = 'https://help-support.lengow.com/hc/en-us/requests/new';

    /* Api iso codes */
    const API_ISO_CODE_EN = 'en';
    const API_ISO_CODE_FR = 'fr';
    const API_ISO_CODE_ES = 'es';
    const API_ISO_CODE_IT = 'it';

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
     * @var array iso code correspondence for plugin links
     */
    public static $genericIsoCodes = array(
        self::API_ISO_CODE_EN => LengowTranslation::ISO_CODE_EN,
        self::API_ISO_CODE_FR => LengowTranslation::ISO_CODE_FR,
        self::API_ISO_CODE_ES => LengowTranslation::ISO_CODE_ES,
        self::API_ISO_CODE_IT => LengowTranslation::ISO_CODE_IT,
    );

    /**
     * @var array default plugin links when the API is not available
     */
    public static $defaultPluginLinks = array(
        self::LINK_TYPE_HELP_CENTER => self::LINK_HELP_CENTER,
        self::LINK_TYPE_CHANGELOG => self::LINK_CHANGELOG,
        self::LINK_TYPE_UPDATE_GUIDE => self::LINK_UPDATE_GUIDE,
        self::LINK_TYPE_SUPPORT => self::LINK_SUPPORT,
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
            'plugin_version' => LengowConfiguration::getGlobalValue(LengowConfiguration::PLUGIN_VERSION),
            'email' => LengowConfiguration::get('PS_SHOP_EMAIL'),
            'cron_url' => LengowMain::getCronUrl(),
            'toolbox_url' => LengowMain::getToolboxUrl(),
            'shops' => array(),
        );
        $shopCollection = LengowShop::findAll(true);
        foreach ($shopCollection as $row) {
            $idShop = $row['id_shop'];
            $lengowExport = new LengowExport(array(LengowExport::PARAM_SHOP_ID => $idShop));
            $shop = new LengowShop($idShop);
            $data['shops'][] = array(
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
     * Sync Lengow catalogs for order synchronisation
     *
     * @param boolean $force force cache update
     * @param boolean $logOutput see log or not
     *
     * @return boolean
     */
    public static function syncCatalog($force = false, $logOutput = false)
    {
        $success = false;
        $settingUpdated = false;
        if (LengowConfiguration::isNewMerchant()) {
            return $success;
        }
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue(LengowConfiguration::LAST_UPDATE_CATALOG);
            if ($updatedAt !== null && (time() - (int) $updatedAt) < self::$cacheTimes[self::SYNC_CATALOG]) {
                return $success;
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
                                (int) $shop->id
                            );
                            $activeStoreChange = LengowConfiguration::setActiveShop((int) $shop->id);
                            if (!$settingUpdated && ($catalogIdsChange || $activeStoreChange)) {
                                $settingUpdated = true;
                            }
                        }
                    }
                    $success = true;
                    break;
                }
            }
        }
        // save last update date for a specific settings (change synchronisation interval time)
        if ($settingUpdated) {
            LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_SETTING, time());
        }
        LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_CATALOG, time());
        return $success;
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
            $updatedAt = LengowConfiguration::getGlobalValue(LengowConfiguration::LAST_UPDATE_MARKETPLACE_LIST);
            if ($updatedAt !== null && (time() - (int) $updatedAt) < self::$cacheTimes[self::SYNC_CARRIER]) {
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
        LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_MARKETPLACE_LIST, time());
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
            'plugin_version' => LengowConfiguration::getGlobalValue(LengowConfiguration::PLUGIN_VERSION),
            'options' => LengowConfiguration::getAllValues(),
            'shops' => array(),
        );
        $shopCollection = LengowShop::findAll(true);
        foreach ($shopCollection as $row) {
            $idShop = $row['id_shop'];
            $lengowExport = new LengowExport(array(LengowExport::PARAM_SHOP_ID => $idShop));
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
            $updatedAt = LengowConfiguration::getGlobalValue(LengowConfiguration::LAST_UPDATE_OPTION_CMS);
            if ($updatedAt !== null && (time() - (int) $updatedAt) < self::$cacheTimes[self::SYNC_CMS_OPTION]) {
                return false;
            }
        }
        $options = Tools::jsonEncode(self::getOptionData());
        LengowConnector::queryApi(LengowConnector::PUT, LengowConnector::API_CMS, array(), $options, $logOutput);
        LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_OPTION_CMS, time());
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
            $updatedAt = LengowConfiguration::getGlobalValue(LengowConfiguration::LAST_UPDATE_ACCOUNT_STATUS_DATA);
            if ($updatedAt !== null && (time() - (int) $updatedAt) < self::$cacheTimes[self::SYNC_STATUS_ACCOUNT]) {
                return Tools::jsonDecode(
                    LengowConfiguration::getGlobalValue(LengowConfiguration::ACCOUNT_STATUS_DATA),
                    true
                );
            }
        }
        $result = LengowConnector::queryApi(LengowConnector::GET, LengowConnector::API_PLAN, array(), '', $logOutput);
        if (isset($result->isFreeTrial)) {
            $status = array(
                'type' => $result->isFreeTrial ? 'free_trial' : '',
                'day' => (int) $result->leftDaysBeforeExpired < 0 ? 0 : (int) $result->leftDaysBeforeExpired,
                'expired' => (bool) $result->isExpired,
                'legacy' => $result->accountVersion === 'v2'
            );
            LengowConfiguration::updateGlobalValue(
                LengowConfiguration::ACCOUNT_STATUS_DATA,
                Tools::jsonEncode($status)
            );
            LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_ACCOUNT_STATUS_DATA, time());
            return $status;
        }
        if (LengowConfiguration::getGlobalValue(LengowConfiguration::ACCOUNT_STATUS_DATA)) {
            return Tools::jsonDecode(
                LengowConfiguration::getGlobalValue(LengowConfiguration::ACCOUNT_STATUS_DATA),
                true
            );
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
            $updatedAt = LengowConfiguration::getGlobalValue(LengowConfiguration::LAST_UPDATE_MARKETPLACE);
            if ($updatedAt !== null
                && (time() - (int) $updatedAt) < self::$cacheTimes[self::SYNC_MARKETPLACE]
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
                    LengowMain::FOLDER_CONFIG,
                    LengowMarketplace::FILE_MARKETPLACE,
                    'w'
                );
                $marketplaceFile->write(Tools::jsonEncode($result));
                $marketplaceFile->close();
                LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_MARKETPLACE, time());
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
        }
        if (file_exists($filePath)) {
            $marketplacesData = Tools::file_get_contents($filePath);
            if ($marketplacesData) {
                return Tools::jsonDecode($marketplacesData);
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
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue(LengowConfiguration::LAST_UPDATE_PLUGIN_DATA);
            if ($updatedAt !== null && (time() - (int) $updatedAt) < self::$cacheTimes[self::SYNC_PLUGIN_DATA]) {
                return Tools::jsonDecode(LengowConfiguration::getGlobalValue(LengowConfiguration::PLUGIN_DATA), true);
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
                    $cmsMinVersion = '';
                    $cmsMaxVersion = '';
                    $pluginLinks = array();
                    $currentVersion = $plugin->version;
                    if (!empty($plugin->versions)) {
                        foreach ($plugin->versions as $version) {
                            if ($version->version === $currentVersion) {
                                $cmsMinVersion = $version->cms_min_version;
                                $cmsMaxVersion = $version->cms_max_version;
                                break;
                            }
                        }
                    }
                    if (!empty($plugin->links)) {
                        foreach ($plugin->links as $link) {
                            if (array_key_exists($link->language->iso_a2, self::$genericIsoCodes)) {
                                $genericIsoCode = self::$genericIsoCodes[$link->language->iso_a2];
                                $pluginLinks[$genericIsoCode][$link->link_type] = $link->link;
                            }
                        }
                    }
                    $pluginData = array(
                        'version' => $currentVersion,
                        'download_link' => $plugin->archive,
                        'cms_min_version' => $cmsMinVersion,
                        'cms_max_version' => $cmsMaxVersion,
                        'links' => $pluginLinks,
                        'extensions' => $plugin->extensions,
                    );
                    break;
                }
            }
            if ($pluginData) {
                LengowConfiguration::updateGlobalValue(
                    LengowConfiguration::PLUGIN_DATA,
                    Tools::jsonEncode($pluginData)
                );
                LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_PLUGIN_DATA, time());
                return $pluginData;
            }
        } elseif (LengowConfiguration::getGlobalValue(LengowConfiguration::PLUGIN_DATA)) {
            return Tools::jsonDecode(LengowConfiguration::getGlobalValue(LengowConfiguration::PLUGIN_DATA), true);
        }
        return false;
    }

    /**
     * Get an array of plugin links for a specific iso code
     *
     * @param string|null $isoCode
     *
     * @return array
     */
    public static function getPluginLinks($isoCode = null)
    {
        $pluginData = self::getPluginData();
        if (!$pluginData) {
            return self::$defaultPluginLinks;
        }
        // check if the links are available in the locale
        $isoCode = $isoCode ?: LengowTranslation::DEFAULT_ISO_CODE;
        $localeLinks = isset($pluginData['links'][$isoCode]) ? $pluginData['links'][$isoCode] : false;
        $defaultLocaleLinks = isset($pluginData['links'][LengowTranslation::DEFAULT_ISO_CODE])
            ? $pluginData['links'][LengowTranslation::DEFAULT_ISO_CODE]
            : false;
        // for each type of link, we check if the link is translated
        $pluginLinks = array();
        foreach (self::$defaultPluginLinks as $linkType => $defaultLink) {
            if ($localeLinks && isset($localeLinks[$linkType])) {
                $link = $localeLinks[$linkType];
            } elseif ($defaultLocaleLinks && isset($defaultLocaleLinks[$linkType])) {
                $link = $defaultLocaleLinks[$linkType];
            } else {
                $link = $defaultLink;
            }
            $pluginLinks[$linkType] = $link;
        }
        return $pluginLinks;
    }
}
