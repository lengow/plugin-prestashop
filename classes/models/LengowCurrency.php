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
 * Lengow Currency Class
 */
class LengowCurrency
{
    /**
     * Get id currency by iso_code
     *
     * @param string  $sign
     * @param integer $id_shop
     *
     * @return integer
     */
    public static function getIdBySign($sign, $id_shop = 0)
    {
        if (_PS_VERSION_ < '1.5') {
            $sql = "SELECT id_currency FROM "._DB_PREFIX_."currency WHERE iso_code = '".pSQL($sign)."' ";
            $result = Db::getInstance()->getRow($sql);
            return $result['id_currency'];
        } else {
            $cache_id = 'Currency::getIdBySign_'.pSQL($sign).'-'.(int)$id_shop;
            if (!Cache::isStored($cache_id)) {
                $query = Currency::getIdByQuery($id_shop);
                $query->where('iso_code = \''.pSQL($sign).'\'');
                $result = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query->build());
                Cache::store($cache_id, $result);
                return $result;
            }
            return Cache::retrieve($cache_id);
        }
    }
}
