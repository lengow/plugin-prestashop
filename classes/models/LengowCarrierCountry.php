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
class LengowCarrierCountry
{


    public static function createDefaultCarrier()
    {
        $default_country = Configuration::get('PS_COUNTRY_DEFAULT');
        $sql = 'SELECT * FROM '._DB_PREFIX_.'lengow_carrier_country WHERE id_country = '.(int)$default_country;
        $default = Db::getInstance()->ExecuteS($sql);
        if (empty($default)) {
            $insert = self::insert($default_country);
            return $insert;
        }
        return false;
    }

    /**
     * Returns carrier's list by id lengow carrier
     *
     * @param  int $id_lengow_carrier
     *
     * @return array
     */
    public static function listCarrierById($id_lengow_carrier)
    {

        $sql = 'SELECT lc.id, lc.id_carrier, co.iso_code, cl.name, co.id_country FROM '
            ._DB_PREFIX_.'lengow_carrier_country lc INNER JOIN '
            ._DB_PREFIX_.'country co ON lc.id_country=co.id_country INNER JOIN '
            ._DB_PREFIX_.'country_lang cl ON co.id_country=cl.id_country AND cl.id_lang= '.(int)Context::getContext()->language->id
            .' WHERE lc.id = '.(int)$id_lengow_carrier;

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
        $default_country = Configuration::get('PS_COUNTRY_DEFAULT');

        $sql = 'SELECT lc.id, lc.id_carrier, co.iso_code, cl.name, co.id_country FROM '
            ._DB_PREFIX_.'lengow_carrier_country lc INNER JOIN '
            . _DB_PREFIX_.'country co ON lc.id_country=co.id_country INNER JOIN '
            .DB_PREFIX_.'country_lang cl ON co.id_country=cl.id_country AND cl.id_lang= '.(int)Context::getContext()->language->id
            .' ORDER BY CASE WHEN co.id_country = '.(int)$default_country.' THEN 1 ELSE cl.name END ASC;';

        $collection = Db::getInstance()->ExecuteS($sql);

        return $collection;

    }

    /**
     * Find CountryCarrier By Country
     *
     * @param integer $id_country
     *
     * @return mixed
     */
    public static function findByCountry($id_country)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'lengow_carrier_country WHERE id_country ='.(int)$id_country
        );
    }

    /**
     * Returns all countries
     *
     * @return array
     */
    public static function getCountries()
    {

        $sql = 'SELECT * FROM '._DB_PREFIX_.'country_lang WHERE id_lang = '.(int)Context::getContext()->language->id;

        $collection = Db::getInstance()->ExecuteS($sql);

        return $collection;
    }

    /**
     * Get all id countries by carriers
     *
     * @param  array $listCarrier
     *
     * @return array
     */
    public static function getIdCountries($listCarrier)
    {
        $id_countries = array();

        foreach ($listCarrier as $row) {
            $id_countries[] = $row['id_country'];
        }

        return $id_countries;
    }

    /**
     * Insert a new carrier country in the table
     *
     * @param  int $id_country
     *
     * @return action
     */
    public static function insert($id_country)
    {
        $db = DB::getInstance();
        $db->autoExecute(
            _DB_PREFIX_.'lengow_carrier_country',
            array('id_country' => (int)$id_country),
            'INSERT'
        );
        return $db;
    }

    /**
     * Delete a carrier country
     *
     * @param  int $id_country
     *
     * @return action
     */
    public static function delete($id_country)
    {
        $db = DB::getInstance();
        $db->delete(_DB_PREFIX_.'lengow_carrier_country', 'id_country = '.(int)$id_country);
        return $db;
    }
}
