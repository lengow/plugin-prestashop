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
            $id_shop = $row['id_shop'];
            $lengowExport = new LengowExport(array("shop_id" => $id_shop));
            $shop = new LengowShop($id_shop);
            $data['shops'][$row['id_shop']]['token']                   = LengowMain::getToken($id_shop);
            $data['shops'][$row['id_shop']]['name']                    = $shop->name;
            $data['shops'][$row['id_shop']]['domain']                  = $shop->domain;
            $data['shops'][$row['id_shop']]['feed_url']                = LengowMain::getExportUrl($shop->id);
            $data['shops'][$row['id_shop']]['cron_url']                = LengowMain::getImportUrl($shop->id);
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
        foreach ($params as $shop_token => $values) {
            if ($shop = LengowShop::findByToken($shop_token)) {
                $list_key = array(
                    'account_id'   => false,
                    'access_token' => false,
                    'secret_token' => false
                );
                foreach ($values as $k => $v) {
                    if (!in_array($k, array_keys($list_key))) {
                        continue;
                    }
                    if (Tools::strlen($v) > 0) {
                        $list_key[$k] = true;
                        LengowConfiguration::updateValue('LENGOW_'.Tools::strtoupper($k), $v, false, null, $shop->id);
                    }
                }
                $findFalseValue = false;
                foreach ($list_key as $k => $v) {
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
     * @param $id_shop
     *
     * @return boolean
     */
    public static function checkSyncShop($id_shop)
    {
        $id_shop = $id_shop;
        return LengowConfiguration::get('LENGOW_SHOP_ACTIVE', null, false, $id_shop)
            && LengowCheck::isValidAuth($id_shop);
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
            $id_shop = $row['id_shop'];
            $lengowExport = new LengowExport(array("shop_id" => $id_shop));
            $shop = new LengowShop($id_shop);
            $data['shops'][] = array(
                'enabled'                 => LengowConfiguration::get('LENGOW_SHOP_ACTIVE', null, false, $shop->id),
                'token'                   => LengowMain::getToken($id_shop),
                'store_name'              => $shop->name,
                'domain_url'              => $shop->domain,
                'feed_url'                => LengowMain::getExportUrl($shop->id),
                'cron_url'                => LengowMain::getImportUrl($shop->id),
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
        if (LengowMain::isNewMerchant()) {
            return false;
        }
        if (!$force) {
            $updated_at =  LengowConfiguration::getGlobalValue('LENGOW_OPTION_CMS_UPDATE');
            if (!is_null($updated_at) && (time() - strtotime($updated_at)) < self::$cacheTime) {
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
            $updated_at =  LengowConfiguration::getGlobalValue('LENGOW_ACCOUNT_STATUS_UPDATE');
            if (!is_null($updated_at) && (time() - strtotime($updated_at)) < self::$cacheTime) {
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
        $currency_id = 0;
        //get stats by shop
        $shopCollection = LengowShop::findAll(true);
        $i = 0;
        $account_ids = array();
        foreach ($shopCollection as $s) {
            $account_id = LengowMain::getIdAccount($s['id_shop']);
            if (!$account_id || in_array($account_id, $account_ids) || empty($account_id)) {
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
            }
            $account_ids[] = $account_id;
            $i++;
        }
        if ($return['currency']) {
            $currency_id = LengowCurrency::getIdBySign($return['currency']);
        }
        if ($currency_id > 0) {
            $return['total_order'] = Tools::displayPrice($return['total_order'], new Currency($currency_id));
        } else {
            $return['total_order'] = number_format($return['total_order'], 2, ',', ' ');
        }
        $return['nb_order'] = (int)$return['nb_order'];
        LengowConfiguration::updateGlobalValue('LENGOW_ORDER_STAT', Tools::JsonEncode($return));
        LengowConfiguration::updateGlobalValue('LENGOW_ORDER_STAT_UPDATE', date('Y-m-d H:i:s'));
        return $return;
    }
}
