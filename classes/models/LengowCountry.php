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
 * Lengow Country Class
 */
class LengowCountry
{
    /**
     * Get Name country By iso_code
     *
     * @param string $isoCode country iso code
     *
     * @return string
     */
    public static function getNameByIso($isoCode)
    {
        $idLang = (int)Context::getContext()->language->id;
        if ($idLang > 0) {
            $where = 'AND id_lang = ' . $idLang;
        } else {
            $where = '';
        }
        $sql = 'SELECT name FROM ' . _DB_PREFIX_ . 'country c
            INNER JOIN ' . _DB_PREFIX_ . 'country_lang cl ON (cl.id_country = c.id_country)
            WHERE iso_code = \'' . pSQL($isoCode) . '\' ' . $where;
        $result = Db::getInstance()->getRow($sql);
        return $result['name'];
    }

    /**
     * Get country by id
     *
     * @param integer $idCountry Prestashop country id
     *
     * @return array|false
     */
    public static function getCountry($idCountry)
    {
        $result = Db::getInstance()->getRow(
            'SELECT c.id_country, c.iso_code, cl.name FROM ' . _DB_PREFIX_ . 'country as c
            INNER JOIN ' . _DB_PREFIX_ . 'country_lang as cl ON c.id_country = cl.id_country
            AND cl.id_lang = ' . (int)Context::getContext()->language->id . '
            WHERE c.id_country = ' . (int)$idCountry
        );
        return $result;
    }
}
