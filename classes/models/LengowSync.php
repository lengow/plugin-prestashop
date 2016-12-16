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

/**
 * Lengow Sync Class
 */
class LengowSync
{
    /**
     * Get Account Status every 5 hours
     */
    protected static $cacheTime = 18000;

    /**
     * Get Sync Data (Inscription / Update)
     *
     * @return array
     */
    public static function getSyncData()
    {
        $data = array();
        $data['domain_name']    = $_SERVER["SERVER_NAME"];
        $data['token']          = LengowMain::getToken();
        $data['type']           = 'prestashop';
        $data['version']        = _PS_VERSION_;
        $data['plugin_version'] = LengowConfiguration::getGlobalValue('LENGOW_VERSION');
        $data['email']          = LengowConfiguration::get('PS_SHOP_EMAIL');
        $data['return_url']     = 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        $shopCollection = LengowShop::findAll(true);
        foreach ($shopCollection as $row) {
            $idShop = $row['id_shop'];
            $lengowExport = new LengowExport(array("shop_id" => $idShop));
            $shop = new LengowShop($idShop);
            $data['shops'][$row['id_shop']]['token']                   = LengowMain::getToken($idShop);
            $data['shops'][$row['id_shop']]['name']                    = $shop->name;
            $data['shops'][$row['id_shop']]['domain']                  = $shop->domain;
            $data['shops'][$row['id_shop']]['feed_url']                = LengowMain::getExportUrl($shop->id);
            $data['shops'][$row['id_shop']]['cron_url']                = LengowMain::getImportUrl();
            $data['shops'][$row['id_shop']]['total_product_number']    = $lengowExport->getTotalProduct();
            $data['shops'][$row['id_shop']]['exported_product_number'] = $lengowExport->getTotalExportProduct();
            $data['shops'][$row['id_shop']]['configured']              = self::checkSyncShop($shop->id);
        }
        return $data;
    }

    /**
     * Store Configuration Key From Lengow
     *
     * @param $params
     */
    public static function sync($params)
    {
        foreach ($params as $shopToken => $values) {
            if ($shop = LengowShop::findByToken($shopToken)) {
                $listKey = array(
                    'account_id'   => false,
                    'access_token' => false,
                    'secret_token' => false
                );
                foreach ($values as $k => $v) {
                    if (!in_array($k, array_keys($listKey))) {
                        continue;
                    }
                    if (Tools::strlen($v) > 0) {
                        $listKey[$k] = true;
                        LengowConfiguration::updateValue('LENGOW_'.Tools::strtoupper($k), $v, false, null, $shop->id);
                    }
                }
                $findFalseValue = false;
                foreach ($listKey as $k => $v) {
                    if (!$v) {
                        $findFalseValue = true;
                        break;
                    }
                }
                if (!$findFalseValue) {
                    LengowConfiguration::updateValue('LENGOW_SHOP_ACTIVE', true, false, null, $shop->id);
                } else {
                    LengowConfiguration::updateValue('LENGOW_SHOP_ACTIVE', false, false, null, $shop->id);
                }
            }
        }
    }

    /**
     * Check Synchronisation shop
     *
     * @param $idShop
     *
     * @return boolean
     */
    public static function checkSyncShop($idShop)
    {
        return LengowConfiguration::get('LENGOW_SHOP_ACTIVE', null, false, $idShop)
            && LengowCheck::isValidAuth($idShop);
    }

    /**
     * Get Sync Data (Inscription / Update)
     *
     * @return array
     */
    public static function getOptionData()
    {
        $data = array();
        $data['cms'] = array(
            'token'          => LengowMain::getToken(),
            'type'           => 'prestashop',
            'version'        => _PS_VERSION_,
            'plugin_version' => LengowConfiguration::getGlobalValue('LENGOW_VERSION'),
            'options'        => LengowConfiguration::getAllValues()
        );
        $shopCollection = LengowShop::findAll(true);
        foreach ($shopCollection as $row) {
            $idShop = $row['id_shop'];
            $lengowExport = new LengowExport(array("shop_id" => $idShop));
            $shop = new LengowShop($idShop);
            $data['shops'][] = array(
                'enabled'                 => LengowConfiguration::get('LENGOW_SHOP_ACTIVE', null, false, $shop->id),
                'token'                   => LengowMain::getToken($idShop),
                'store_name'              => $shop->name,
                'domain_url'              => $shop->domain,
                'feed_url'                => LengowMain::getExportUrl($shop->id),
                'cron_url'                => LengowMain::getImportUrl(),
                'total_product_number'    => $lengowExport->getTotalProduct(),
                'exported_product_number' => $lengowExport->getTotalExportProduct(),
                'options'                 => LengowConfiguration::getAllValues($shop->id)
            );
        }
        return $data;
    }

    /**
     * Set CMS options
     *
     * @param boolean $force Force cache Update
     *
     * @return boolean
     */
    public static function setCmsOption($force = false)
    {
        if (LengowMain::isNewMerchant()
            || LengowConfiguration::getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED')
        ) {
            return false;
        }
        if (!$force) {
            $updatedAt =  LengowConfiguration::getGlobalValue('LENGOW_OPTION_CMS_UPDATE');
            if (!is_null($updatedAt) && (time() - strtotime($updatedAt)) < self::$cacheTime) {
                return false;
            }
        }
        $options = Tools::JsonEncode(Self::getOptionData());
        LengowConnector::queryApi('put', '/v3.0/cms', null, array(), $options);
        LengowConfiguration::updateGlobalValue('LENGOW_OPTION_CMS_UPDATE', date('Y-m-d H:i:s'));
        return true;
    }

    /**
     * Get Status Account
     *
     * @param boolean $force Force cache Update
     *
     * @return mixed
     */
    public static function getStatusAccount($force = false)
    {
        if (!$force) {
            $updatedAt =  LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS_UPDATE');
            if (!is_null($updatedAt) && (time() - strtotime($updatedAt)) < self::$cacheTime) {
                return Tools::JsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS'), true);
            }
        }
        $result = LengowConnector::queryApi(
            'get',
            '/v3.0/subscriptions'
        );
        if (isset($result->subscription)) {
            $status = array();
            $status['type'] = $result->subscription->billing_offer->type;
            $status['day'] = - round((strtotime(date("c")) - strtotime($result->subscription->renewal)) / 86400);
            if ($status['day'] < 0) {
                $status['day'] = 0;
            }
            if ($status) {
                LengowConfiguration::updateGlobalValue('LENGOW_ACCOUNT_STATUS', Tools::JsonEncode($status));
                LengowConfiguration::updateGlobalValue('LENGOW_ACCOUNT_STATUS_UPDATE', date('Y-m-d H:i:s'));
                return $status;
            }
        }
        return false;
    }

    /**
     * Get Statistic with all shop
     *
     * @param boolean $force Force cache Update
     *
     * @return array
     */
    public static function getStatistic($force = false)
    {
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_ORDER_STAT_UPDATE');
            if ((time() - strtotime($updatedAt)) < self::$cacheTime) {
                return Tools::JsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ORDER_STAT'), true);
            }
        }
        $return = array();
        $return['total_order'] = 0;
        $return['nb_order'] = 0;
        $return['currency'] = '';
        $return['available'] = false;
        $currencyId = 0;
        //get stats by shop
        $shopCollection = LengowShop::findAll(true);
        $i = 0;
        $accountIds = array();
        foreach ($shopCollection as $s) {
            $accountId = LengowMain::getIdAccount($s['id_shop']);
            if (!$accountId || in_array($accountId, $accountIds) || empty($accountId)) {
                continue;
            }
            $result = LengowConnector::queryApi(
                'get',
                '/v3.0/stats',
                $s['id_shop'],
                array(
                    'date_from' => date('c', strtotime(date('Y-m-d').' -10 years')),
                    'date_to'   => date('c'),
                    'metrics'   => 'year',
                )
            );
            if (isset($result->level0)) {
                $stats = $result->level0[0];
                $return['total_order'] += $stats->revenue;
                $return['nb_order'] += $stats->transactions;
                $return['currency'] = $result->currency->iso_a3;
            } else {
                if (LengowConfiguration::getGlobalValue('LENGOW_ORDER_STAT_UPDATE')) {
                    return Tools::JsonDecode(LengowConfiguration::getGlobalValue('LENGOW_ORDER_STAT'), true);
                } else {
                    return array(
                        'total_order' => 0,
                        'nb_order'    => 0,
                        'currency'    => '',
                        'available'   => false
                    );
                }
            }
            $accountIds[] = $accountId;
            $i ++;
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
        $return['nb_order'] = (int)$return['nb_order'];
        LengowConfiguration::updateGlobalValue('LENGOW_ORDER_STAT', Tools::JsonEncode($return));
        LengowConfiguration::updateGlobalValue('LENGOW_ORDER_STAT_UPDATE', date('Y-m-d H:i:s'));
        return $return;
    }
}
