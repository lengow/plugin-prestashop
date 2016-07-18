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
     * Get Account Status
     */
    protected static $cacheTime = 10800;

    /**
     * Get Sync Data (Inscription / Update)
     *
     * @return array
     */
    public static function getSyncData()
    {
        $data = array();
        $data['domain_name'] = $_SERVER["SERVER_NAME"];
        $data['token'] = LengowMain::getToken();
        $data['type'] = 'prestashop';
        $data['version'] = _PS_VERSION_;
        $data['plugin_version'] = LengowConfiguration::getGlobalValue('LENGOW_VERSION');
        $data['email'] = LengowConfiguration::get('PS_SHOP_EMAIL');
        $data['return_url'] = 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        $shopCollection = LengowShop::findAll(true);
        foreach ($shopCollection as $row) {
            $shopId = $row['id_shop'];
            $lengowExport = new LengowExport(array("shop_id" => $shopId));
            $shop = new LengowShop($shopId);
            $data['shops'][$row['id_shop']]['token'] = LengowMain::getToken($shopId);
            $data['shops'][$row['id_shop']]['name'] = $shop->name;
            $data['shops'][$row['id_shop']]['domain'] = $shop->domain;
            $data['shops'][$row['id_shop']]['feed_url'] = LengowMain::getExportUrl($shop->id);
            $data['shops'][$row['id_shop']]['cron_url'] = LengowMain::getImportUrl($shop->id);
            $data['shops'][$row['id_shop']]['nb_product_total'] = $lengowExport->getTotalProduct();
            $data['shops'][$row['id_shop']]['nb_product_exported'] = $lengowExport->getTotalExportProduct();
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
                    'account_id' => false,
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
            $shop_id = $row['id_shop'];
            $shop = new LengowShop($shop_id);
            $data['shops'][] = array(
                'enabled'    => LengowConfiguration::get('LENGOW_SHOP_ACTIVE', null, false, $shop->id),
                'token'      => LengowMain::getToken($shop_id),
                'store_name' => $shop->name,
                'domain_url' => $shop->domain,
                'feed_url'   => LengowMain::getExportUrl($shop->id),
                'cron_url'   => LengowMain::getImportUrl($shop->id),
                'options'    => LengowConfiguration::getAllValues($shop->id)
            );
        }
        return $data;
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
        //TODO call API for return a customer id or false
        //$result = LengowConnector::queryApi('get', '/v3.0/cms');
        $result = true;
        if ($result) {
            //TODO call API with customer id parameter for return status account
            //$status = LengowConnector::queryApi('get', '/v3.0/cms');
            $status = array();
            $status['type'] = 'free_trial';
            $status['day'] = 10;
            if ($status) {
                LengowConfiguration::updateGlobalValue('LENGOW_ACCOUNT_STATUS', Tools::JsonEncode($status));
                LengowConfiguration::updateGlobalValue('LENGOW_ACCOUNT_STATUS_UPDATE', date('Y-m-d H:i:s'));
                return $status;
            }
        }
        return false;
    }
}
