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
 * Lengow Shop Class
 */
class LengowShop extends Shop
{
    /**
     * Construct
     *
     * @param integer|null $id Prestashop shop id
     * @param integer|null $idLang Prestashop lang id
     * @param integer|null $idShop Prestashop shop id
     */
    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        if (_PS_VERSION_ < '1.5') {
            $this->id = 1;
            $this->name = Configuration::get('PS_SHOP_NAME');
            $this->domain = Configuration::get('PS_SHOP_DOMAIN');
        }
    }

    /**
     * Is feature active
     *
     * @return boolean
     */
    public static function isFeatureActive()
    {
        if (_PS_VERSION_ < '1.5') {
            return false;
        } else {
            return parent::isFeatureActive();
        }
    }

    /**
     * Find shop by token
     *
     * @param string $token Lengow shop token
     *
     * @return LengowShop|false
     */
    public static function findByToken($token)
    {
        if (_PS_VERSION_ < '1.5') {
            $results = array(array('id_shop' => 1));
        } else {
            try {
                $sql = 'SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop WHERE active = 1';
                $results = Db::getInstance()->ExecuteS($sql);
            } catch (PrestaShopDatabaseException $e) {
                return false;
            }
        }
        foreach ($results as $row) {
            if ($token === LengowMain::getToken($row['id_shop'])) {
                $shop = new LengowShop($row['id_shop']);
                return $shop;
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
        if (_PS_VERSION_ < '1.5') {
            $results = array(array('id_shop' => 1));
        } else {
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
        }
        return $results;
    }
}
