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
     * @var integer cache time for statistic, account status and cms options
     */
    protected static $cacheTime = 18000;

    /**
     * @var array valid sync actions
     */
    public static $syncActions = array(
        'order',
        'action',
        'catalog',
        'carrier',
        'option'
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
            'shops' => array()
        );
        $shopCollection = LengowShop::findAll(true);
        foreach ($shopCollection as $row) {
            $idShop = $row['id_shop'];
            $lengowExport = new LengowExport(array("shop_id" => $idShop));
            $shop = new LengowShop($idShop);
            $data['shops'][$idShop] = array(
                'token' => LengowMain::getToken($idShop),
                'shop_name' =>  $shop->name,
                'domain_url' => $shop->domain,
                'feed_url' => LengowMain::getExportUrl($shop->id),
                'total_product_number' => $lengowExport->getTotalProduct(),
                'exported_product_number' => $lengowExport->getTotalExportProduct(),
                'enabled' => LengowConfiguration::shopIsActive($idShop)
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
                'LENGOW_SECRET_TOKEN' => $params['secret_token']
            )
        );
        foreach ($params['shops'] as $shopToken => $shopCatalogIds) {
            $shop = LengowShop::findByToken($shopToken);
            if ($shop) {
                LengowConfiguration::setCatalogIds($shopCatalogIds['catalog_ids'], (int)$shop->id);
                LengowConfiguration::setActiveShop((int)$shop->id);
            }
        }
    }

    /**
     * Sync Lengow catalogs for order synchronisation
     */
    public static function syncCatalog()
    {
        if (LengowConnector::isNewMerchant()) {
            return false;
        }
        $result = LengowConnector::queryApi('get', '/v3.1/cms');
        if (isset($result->cms)) {
            $cmsToken = LengowMain::getToken();
            foreach ($result->cms as $cms) {
                if ($cms->token === $cmsToken) {
                    foreach ($cms->shops as $cmsShop) {
                        $shop = LengowShop::findByToken($cmsShop->token);
                        if ($shop) {
                            LengowConfiguration::setCatalogIds($cmsShop->catalog_ids, (int)$shop->id);
                            LengowConfiguration::setActiveShop((int)$shop->id);
                        }
                    }
                    break;
                }
            }
        }
    }

    /**
     * Sync Lengow marketplaces and marketplace carriers
     *
     * @param boolean $force force cache update
     *
     * @return boolean
     */
    public static function syncCarrier($force = true)
    {
        if (LengowConnector::isNewMerchant()) {
            return false;
        }
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_LIST_MARKET_UPDATE');
            if (!is_null($updatedAt) && (time() - strtotime($updatedAt)) < self::$cacheTime) {
                return false;
            }
        }
        LengowMarketplace::syncMarketplaces();
        LengowCarrier::syncCarrierMarketplace();
        LengowCarrier::createDefaultCarrier();
        LengowCarrier::cleanCarrierMarketplaceMatching();
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
            'shops' => array()
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
                'options' => LengowConfiguration::getAllValues($idShop)
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
            if (!is_null($updatedAt) && (time() - strtotime($updatedAt)) < self::$cacheTime) {
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
            if (!is_null($updatedAt) && (time() - strtotime($updatedAt)) < self::$cacheTime) {
                return Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS'), true);
            }
        }
        $result = LengowConnector::queryApi('get', '/v3.0/plans');
        if (isset($result->isFreeTrial)) {
            $status = array();
            $status['type'] = $result->isFreeTrial ? 'free_trial' : '';
            $status['day'] = (int)$result->leftDaysBeforeExpired;
            $status['expired'] = (bool)$result->isExpired;
            if ($status['day'] < 0) {
                $status['day'] = 0;
            }
            if ($status) {
                LengowConfiguration::updateGlobalValue('LENGOW_ACCOUNT_STATUS', Tools::jsonEncode($status));
                LengowConfiguration::updateGlobalValue('LENGOW_ACCOUNT_STATUS_UPDATE', date('Y-m-d H:i:s'));
                return $status;
            }
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
            if ((time() - strtotime($updatedAt)) < self::$cacheTime) {
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
                'available' => false
            );
        } else {
            if (LengowConfiguration::getGlobalValue('LENGOW_ORDER_STAT_UPDATE')) {
                return Tools::jsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ORDER_STAT'), true);
            } else {
                return array(
                    'total_order' => 0,
                    'nb_order' => 0,
                    'currency' => '',
                    'available' => false
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
}
