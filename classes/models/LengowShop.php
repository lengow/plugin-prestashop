<?php
/**
 * Copyright 2021 Lengow SAS.
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
 * @copyright 2021 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * Lengow Shop Class
 */
class LengowShop extends Shop
{
    /**
     * Find shop by token
     *
     * @param string $token Lengow shop token
     *
     * @return LengowShop|false
     */
    public static function findByToken($token)
    {
        try {
            $sql = 'SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop WHERE active = 1';
            $results = Db::getInstance()->ExecuteS($sql);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        foreach ($results as $row) {
            if ($token === LengowMain::getToken($row['id_shop'])) {
                return new self($row['id_shop']);
            }
        }
        return false;
    }

    /**
     * Find all shop
     *
     * @param boolean $forceContext force context to get all shops
     *
     * @return array
     */
    public static function findAll($forceContext = false)
    {
        if (!$forceContext && $currentShop = Shop::getContextShopID()) {
            $results = array(array('id_shop' => $currentShop));
        } else {
            $sql = 'SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop WHERE active = 1 ORDER BY id_shop';
            try {
                $results = Db::getInstance()->ExecuteS($sql);
            } catch (PrestaShopDatabaseException $e) {
                $results = array();
            }
        }
        return $results;
    }

    /**
     * Get list of PrestaShop shops that have been activated in Lengow
     *
     * @param boolean $activeInLengow get only shop active in Lengow
     * @param integer $idShop PrestaShop shop id
     *
     * @return array
     */
    public static function getActiveShops($activeInLengow = false, $idShop = null)
    {
        $result = array();
        $shops = self::findAll(true);
        foreach ($shops as $shop) {
            if ($idShop && (int) $shop['id_shop'] !== $idShop) {
                continue;
            }
            if (!$activeInLengow || LengowConfiguration::shopIsActive((int) $shop['id_shop'])) {
                $result[] = new LengowShop((int) $shop['id_shop']);
            }
        }
        return $result;
    }
}
