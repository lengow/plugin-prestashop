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

class LengowSync extends SpecificPrice
{
    public function __construct()
    {

    }

    /**
     * Get Sync Data (Inscription / Update)
     * @return array
     */
    public static function getSyncData()
    {
        $data = array();
        $data['domain_name'] = $_SERVER["SERVER_NAME"];
        $data['global_token'] = LengowMain::getToken();
        $data['email'] = LengowConfiguration::get('PS_SHOP_EMAIL');


        $shopCollection = LengowShop::findAll();
        foreach ($shopCollection as $row) {
            $shopId = $row['id_shop'];

            $lengowExport = new LengowExport(array("shop_id" => $shopId));
            $shop = new LengowShop($shopId);
            $data['shops'][$row['id_shop']]['token'] = LengowMain::getToken($shopId);
            $data['shops'][$row['id_shop']]['name'] = $shop->name;
            $data['shops'][$row['id_shop']]['domain'] = $shop->domain;
            $data['shops'][$row['id_shop']]['feed_url'] = LengowMain::getExportUrl($shop->id);
            $data['shops'][$row['id_shop']]['import_url'] = LengowMain::getImportUrl($shop->id);
            $data['shops'][$row['id_shop']]['nb_product_total'] = $lengowExport->getTotalProduct();
            $data['shops'][$row['id_shop']]['nb_product_exported'] = $lengowExport->getTotalExportProduct();
        }
        return $data;
    }

    /**
     * Store Configuration Key From Lengow
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
     * Get Help Form Date to send to lengow solution
     * @return array
     */
    public static function getHelpData()
    {
        $data = array();
        $data['cms'] = 'prestashop';
        $data['cms_version'] = _PS_VERSION_;
        $data['plugin_version'] = LengowConfiguration::getGlobalValue('LENGOW_VERSION');
        $data['email'] = LengowConfiguration::get('PS_SHOP_EMAIL');
        $shopCollection = LengowShop::findAll();
        foreach ($shopCollection as $row) {
            $shopId = $row['id_shop'];
            $lengowExport = new LengowExport(array("shop_id" => $shopId));
            $shop = new LengowShop($shopId);
            $data['shops'][$row['id_shop']]['token'] = LengowMain::getToken($shopId);
            $data['shops'][$row['id_shop']]['name'] = $shop->name;
            $data['shops'][$row['id_shop']]['domain'] = $shop->domain;
            $data['shops'][$row['id_shop']]['feed_url'] = LengowMain::getExportUrl($shop->id);
            $data['shops'][$row['id_shop']]['import_url'] = LengowMain::getImportUrl($shop->id);
            $data['shops'][$row['id_shop']]['nb_product_total'] = $lengowExport->getTotalProduct();
            $data['shops'][$row['id_shop']]['nb_product_exported'] = $lengowExport->getTotalExportProduct();
        }
        return $data;
    }
}
