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
 * Lengow Statistic Class
 */
class LengowStatistic
{
    /**
     * Get Statistic with all shop
     */
    protected static $cacheTime = 10800;

    /**
     * Get Statistic with all shop
     *
     * @param boolean $force Force cache Update
     *
     * @return array
     */
    public static function get($force = false)
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
        $return['average_order'] = 0;
        $return['currency'] = '';
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
                '/v3.0/numbers',
                $s['id_shop']
            );
            if (isset($result->revenues)) {
                $return['total_order'] += $result->revenues;
                $return['nb_order'] += $result->transactions;
                $return['average_order'] += $result->average_order;
                $return['currency'] = $result->currency->iso_a3;
            }
            $account_ids[] = $account_id;
            $i++;
        }
        if ($i > 0) {
            $return['average_order'] = round($return['average_order'] / $i, 2);
        }
        if ($return['currency']) {
            $currency_id = LengowCurrency::getIdBySign($return['currency']);
            if ($currency_id > 0) {
                $return['total_order'] = Tools::displayPrice($return['total_order'], new Currency($currency_id));
                $return['average_order'] = Tools::displayPrice($return['average_order'], new Currency($currency_id));
            } else {
                $return['total_order'] = number_format($return['total_order'], 2, ',', ' ');
                $return['average_order'] = number_format($return['average_order'], 2, ',', ' ');
            }
        }
        $return['nb_order'] = (int)$return['nb_order'];
        LengowConfiguration::updateGlobalValue('LENGOW_ORDER_STAT', Tools::JsonEncode($return));
        LengowConfiguration::updateGlobalValue('LENGOW_ORDER_STAT_UPDATE', date('Y-m-d H:i:s'));
        return $return;
    }
}
