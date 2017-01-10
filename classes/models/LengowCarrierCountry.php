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
 * @category    Lengow
 * @package     lengow
 * @subpackage  classes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * Lengow Carrier Country Class
 */
class LengowCarrierCountry
{
    /**
     * Create default carrier for a default country
     *
     * @return boolean
     */
    public static function createDefaultCarrier()
    {
        $defaultCountry = Configuration::get('PS_COUNTRY_DEFAULT');
        $sql = 'SELECT * FROM '._DB_PREFIX_.'lengow_carrier_country WHERE id_country = '.(int)$defaultCountry;
        $default = Db::getInstance()->ExecuteS($sql);
        if (empty($default)) {
            $insert = self::insert($defaultCountry);
            return $insert;
        }
        return false;
    }

    /**
     * Returns carrier's list by id lengow carrier
     *
     * @param integer $idLengowCarrier Lengow carrier id
     *
     * @return array
     */
    public static function listCarrierById($idLengowCarrier)
    {
        $sql = 'SELECT lc.id, lc.id_carrier, co.iso_code, cl.name, co.id_country FROM '
            ._DB_PREFIX_.'lengow_carrier_country lc INNER JOIN '
            ._DB_PREFIX_.'country co ON lc.id_country=co.id_country INNER JOIN '
            ._DB_PREFIX_.'country_lang cl ON co.id_country=cl.id_country AND cl.id_lang= '
            .(int)Context::getContext()->language->id
            .' WHERE lc.id = '.(int)$idLengowCarrier;
        $collection = Db::getInstance()->getRow($sql);
        return $collection;
    }

    /**
     * Return all carrier by country
     *
     * @return array
     */
    public static function listCarrierByCountry()
    {
        $defaultCountry = Configuration::get('PS_COUNTRY_DEFAULT');
        $sql = 'SELECT lc.id, lc.id_carrier, co.iso_code, cl.name, co.id_country FROM '
            ._DB_PREFIX_.'lengow_carrier_country lc INNER JOIN '
            ._DB_PREFIX_.'country co ON lc.id_country=co.id_country INNER JOIN '
            ._DB_PREFIX_.'country_lang cl ON co.id_country=cl.id_country AND cl.id_lang= '
            .(int)Context::getContext()->language->id
            .' ORDER BY CASE WHEN co.id_country = '.(int)$defaultCountry.' THEN 1 ELSE cl.name END ASC;';
        $collection = Db::getInstance()->ExecuteS($sql);
        return $collection;
    }

    /**
     * Find CountryCarrier By Country
     *
     * @param integer $idCountry Prestashop country id
     *
     * @return array|false
     */
    public static function findByCountry($idCountry)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'lengow_carrier_country WHERE id_country ='.(int)$idCountry
        );
    }

    /**
     * Returns all countries
     *
     * @return array
     */
    public static function getCountries()
    {
        $sql = 'SELECT cl.id_country, cl.id_lang, cl.name, c.iso_code FROM '._DB_PREFIX_.'country_lang cl
                INNER JOIN '._DB_PREFIX_.'country c ON cl.id_country=c.id_country
                WHERE id_lang = '.(int)Context::getContext()->language->id;
        $collection = Db::getInstance()->ExecuteS($sql);
        return $collection;
    }

    /**
     * Get all id countries by carriers
     *
     * @param array $listCarrier Prestashop carrier list
     *
     * @return array
     */
    public static function getIdCountries($listCarrier)
    {
        $idCountries = array();
        foreach ($listCarrier as $row) {
            $idCountries[] = $row['id_country'];
        }
        return $idCountries;
    }

    /**
     * Insert a new carrier country in the table
     *
     * @param integer $idCountry Prestashop country id
     *
     * @return action
     */
    public static function insert($idCountry)
    {
        $db = DB::getInstance();
        if (_PS_VERSION_ < '1.5') {
            $db->autoExecute(
                _DB_PREFIX_.'lengow_carrier_country',
                array('id_country' => (int)$idCountry),
                'INSERT'
            );
        } else {
            $db->insert(
                'lengow_carrier_country',
                array('id_country' => (int)$idCountry)
            );
        }
        return $db;
    }

    /**
     * Delete a carrier country
     *
     * @param integer $idCountry Prestashop country id
     *
     * @return action
     */
    public static function delete($idCountry)
    {
        $db = DB::getInstance();
        $db->delete(_DB_PREFIX_.'lengow_carrier_country', 'id_country = '.(int)$idCountry);
        return $db;
    }
}
