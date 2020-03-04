<?php
/**
 * Copyright 2018 Lengow SAS.
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
 * @copyright 2018 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * Lengow Method Class
 */
class LengowMethod
{
    /**
     * Get method marketplace id
     *
     * @param string $methodMarketplaceName Lengow method marketplace name
     *
     * @return integer|false
     */
    public static function getIdMethodMarketplace($methodMarketplaceName)
    {
        try {
            $results = Db::getInstance()->ExecuteS(
                'SELECT id, method_marketplace_name FROM ' . _DB_PREFIX_ . 'lengow_method_marketplace
                WHERE method_marketplace_name = "' . pSQL($methodMarketplaceName) . '"'
            );
        } catch (PrestaShopDatabaseException $e) {
            $results = array();
        }
        // additional verification for non-case sensitive Databases
        if (!empty($results)) {
            foreach ($results as $result) {
                if ($result['method_marketplace_name'] === $methodMarketplaceName) {
                    return (int)$result['id'];
                }
            }
        }
        return false;
    }

    /**
     * Get all methods marketplace by marketplace id
     *
     * @param integer $idMarketplace Lengow marketplace id
     *
     * @return array
     */
    public static function getAllMethodMarketplaceByIdMarketplace($idMarketplace)
    {
        try {
            $results = Db::getInstance()->ExecuteS(
                'SELECT 
                    lmm.method_marketplace_name,
                    lmm.method_marketplace_label,
                    lmm.method_lengow_code,
                    lmm.id as id_method_marketplace,
                    lm.id as id_marketplace,
                    lmmm.id as id_marketplace_method_marketplace
                FROM ' . _DB_PREFIX_ . 'lengow_method_marketplace as lmm
                INNER JOIN ' . _DB_PREFIX_ . 'lengow_marketplace_method_marketplace as lmmm
                    ON lmm.id = lmmm.id_method_marketplace
                INNER JOIN ' . _DB_PREFIX_ . 'lengow_marketplace as lm
                    ON lm.id = lmmm.id_marketplace
                WHERE lm.id = "' . (int)$idMarketplace . '"'
            );
        } catch (PrestaShopDatabaseException $e) {
            $results = false;
        }
        return is_array($results) ? $results : array();
    }

    /**
     * Sync Lengow methods marketplace
     */
    public static function syncMethodMarketplace()
    {
        LengowMarketplace::loadApiMarketplace();
        if (LengowMarketplace::$marketplaces && !empty(LengowMarketplace::$marketplaces)) {
            foreach (LengowMarketplace::$marketplaces as $marketplaceName => $marketplace) {
                if (isset($marketplace->orders->shipping_methods)) {
                    $idMarketplace = LengowMarketplace::getIdMarketplace($marketplaceName);
                    foreach ($marketplace->orders->shipping_methods as $methodMarketplaceName => $method) {
                        $idMethodMarketplace = self::getIdMethodMarketplace($methodMarketplaceName);
                        if (!$idMethodMarketplace) {
                            $idMethodMarketplace = self::insertMethodMarketplace(
                                $methodMarketplaceName,
                                $method->label,
                                isset($method->lengow_code) ? $method->lengow_code : null
                            );
                        } else {
                            $params = array();
                            if ($method->label !== null && Tools::strlen($method->label) > 0) {
                                $params['method_marketplace_label'] = pSQL($method->label);
                            }
                            if (isset($method->lengow_code)
                                && $method->lengow_code !== null
                                && Tools::strlen($method->lengow_code) > 0
                            ) {
                                $params['method_lengow_code'] = pSQL($method->lengow_code);
                            }
                            if (!empty($params)) {
                                self::updateMethodMarketplace($idMethodMarketplace, $params);
                            }
                        }
                        if ($idMarketplace && $idMethodMarketplace) {
                            self::matchMethodMarketplaceWithMarketplace($idMarketplace, $idMethodMarketplace);
                        }
                    }
                }
            }
        }
    }

    /**
     * Insert a new method marketplace in the table
     *
     * @param string $methodMarketplaceName Lengow method marketplace name
     * @param string $methodMarketplaceLabel Lengow method marketplace label
     * @param string $methodLengowCode Lengow method lengow code
     *
     * @return integer|false
     */
    public static function insertMethodMarketplace(
        $methodMarketplaceName,
        $methodMarketplaceLabel,
        $methodLengowCode = null
    ) {
        $params = array(
            'method_marketplace_name' => pSQL($methodMarketplaceName),
            'method_marketplace_label' => pSQL($methodMarketplaceLabel),
        );
        if ($methodLengowCode !== null && Tools::strlen($methodLengowCode) > 0) {
            $params['method_lengow_code'] = pSQL($methodLengowCode);
        }
        $db = Db::getInstance();
        try {
            if (_PS_VERSION_ < '1.5') {
                $success = $db->autoExecute(_DB_PREFIX_ . 'lengow_method_marketplace', $params, 'INSERT');
            } else {
                $success = $db->insert('lengow_method_marketplace', $params);
            }
        } catch (PrestaShopDatabaseException $e) {
            $success = false;
        }
        return $success ? self::getIdMethodMarketplace($methodMarketplaceName) : false;
    }

    /**
     * Update a method marketplace
     *
     * @param integer $idMethodMarketplace Lengow method marketplace id
     * @param array $params all parameters to update a carrier method
     *
     * @return integer|false
     */
    public static function updateMethodMarketplace($idMethodMarketplace, $params)
    {
        $db = Db::getInstance();
        if (_PS_VERSION_ < '1.5') {
            try {
                $success = $db->autoExecute(
                    _DB_PREFIX_ . 'lengow_method_marketplace',
                    $params,
                    'UPDATE',
                    'id = ' . (int)$idMethodMarketplace
                );
            } catch (PrestaShopDatabaseException $e) {
                $success = false;
            }
        } else {
            $success = $db->update('lengow_method_marketplace', $params, 'id = ' . (int)$idMethodMarketplace);
        }
        return $success ? $idMethodMarketplace : false;
    }

    /**
     * Match method marketplace with one marketplace
     *
     * @param integer $idMarketplace Lengow marketplace id
     * @param integer $idMethodMarketplace Lengow method marketplace id
     *
     * @return boolean
     */
    public static function matchMethodMarketplaceWithMarketplace($idMarketplace, $idMethodMarketplace)
    {
        $db = Db::getInstance();
        try {
            $result = Db::getInstance()->ExecuteS(
                'SELECT id FROM ' . _DB_PREFIX_ . 'lengow_marketplace_method_marketplace
                WHERE id_marketplace = ' . (int)$idMarketplace . '
                AND id_method_marketplace = ' . (int)$idMethodMarketplace
            );
        } catch (PrestaShopDatabaseException $e) {
            $result = array();
        }
        if (empty($result)) {
            $params = array(
                'id_marketplace' => (int)$idMarketplace,
                'id_method_marketplace' => (int)$idMethodMarketplace,
            );
            try {
                if (_PS_VERSION_ < '1.5') {
                    $success = $db->autoExecute(
                        _DB_PREFIX_ . 'lengow_marketplace_method_marketplace',
                        $params,
                        'INSERT'
                    );
                } else {
                    $success = $db->insert('lengow_marketplace_method_marketplace', $params);
                }
            } catch (PrestaShopDatabaseException $e) {
                $success = false;
            }
        } else {
            $success = true;
        }
        return $success;
    }

    /**
     * Delete method marketplace matching
     *
     * @param integer $idMarketplaceMethodMarketplace Lengow marketplace method marketplace id
     *
     * @return boolean
     */
    public static function deleteMarketplaceMethodMarketplace($idMarketplaceMethodMarketplace)
    {
        $table = _PS_VERSION_ < '1.5'
            ? _DB_PREFIX_ . 'lengow_marketplace_method_marketplace'
            : 'lengow_marketplace_method_marketplace';
        return Db::getInstance()->delete($table, 'id = ' . (int)$idMarketplaceMethodMarketplace);
    }

    /**
     * Clean method marketplace matching for old methods
     */
    public static function cleanMethodMarketplaceMatching()
    {
        LengowMarketplace::loadApiMarketplace();
        if (LengowMarketplace::$marketplaces && !empty(LengowMarketplace::$marketplaces)) {
            foreach (LengowMarketplace::$marketplaces as $marketplaceName => $marketplace) {
                $idMarketplace = LengowMarketplace::getIdMarketplace($marketplaceName);
                if ($idMarketplace) {
                    // get all methods saved in database
                    $methodMarketplaces = self::getAllMethodMarketplaceByIdMarketplace($idMarketplace);
                    // get all current marketplace methods with api
                    $currentMethodMarketplaces = array();
                    if (isset($marketplace->orders->shipping_methods)) {
                        foreach ($marketplace->orders->shipping_methods as $methodMarketplaceName => $method) {
                            $currentMethodMarketplaces[$methodMarketplaceName] = $method->label;
                        }
                    }
                    // if the method is no longer on the marketplace, removal of matching
                    foreach ($methodMarketplaces as $methodMarketplace) {
                        if (!array_key_exists(
                            $methodMarketplace['method_marketplace_name'],
                            $currentMethodMarketplaces
                        )) {
                            // delete marketplace method matching
                            self::deleteMarketplaceMethodMarketplace(
                                (int)$methodMarketplace['id_marketplace_method_marketplace']
                            );
                            // delete method marketplace id from marketplace method country if is matched
                            self::cleanMarketplaceMethodCountryByIdMarketplace(
                                $idMarketplace,
                                (int)$methodMarketplace['id_method_marketplace']
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Get marketplace method country id
     *
     * @param integer $idCountry Prestashop country id
     * @param integer $idMarketplace Lengow marketplace id
     * @param integer $idMethodMarketplace Lengow method marketplace id
     *
     * @return integer|false
     */
    public static function getIdMarketplaceMethodCountry($idCountry, $idMarketplace, $idMethodMarketplace)
    {
        try {
            $result = Db::getInstance()->ExecuteS(
                'SELECT id FROM ' . _DB_PREFIX_ . 'lengow_marketplace_method_country
                WHERE id_country = ' . (int)$idCountry . '
                AND id_marketplace = ' . (int)$idMarketplace . '
                AND id_method_marketplace = ' . (int)$idMethodMarketplace
            );
            return !empty($result) ? (int)$result[0]['id'] : false;
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
    }

    /**
     * Clean table when match method marketplace is deleted
     *
     * @param integer $idMarketplace Lengow marketplace id
     * @param integer $idMethodMarketplace Lengow method marketplace id
     */
    public static function cleanMarketplaceMethodCountryByIdMarketplace($idMarketplace, $idMethodMarketplace)
    {
        try {
            $results = Db::getInstance()->ExecuteS(
                'SELECT id FROM ' . _DB_PREFIX_ . 'lengow_marketplace_method_country
                WHERE id_method_marketplace = ' . (int)$idMethodMarketplace . '
                AND id_marketplace = ' . (int)$idMarketplace
            );
        } catch (PrestaShopDatabaseException $e) {
            $results = array();
        }
        if (!empty($results)) {
            foreach ($results as $result) {
                if (isset($result['id']) && $result['id'] > 0) {
                    self::deleteMarketplaceMethodCountry((int)$result['id']);
                }
            }
        }
    }

    /**
     * Get carrier id by country id, marketplace id and method marketplace name
     *
     * @param integer $idCountry Prestashop country id
     * @param string $idMarketplace Lengow marketplace id
     * @param string $methodMarketplaceName Lengow marketplace method name
     *
     * @return integer|false
     */
    public static function getIdCarrierByMethodMarketplaceName($idCountry, $idMarketplace, $methodMarketplaceName)
    {
        if ($methodMarketplaceName != '') {
            // find in lengow marketplace method country table
            $result = Db::getInstance()->getRow(
                'SELECT lmmc.id_carrier FROM ' . _DB_PREFIX_ . 'lengow_marketplace_method_country as lmmc
                INNER JOIN ' . _DB_PREFIX_ . 'lengow_method_marketplace as lmm
                    ON lmm.id = lmmc.id_method_marketplace
                WHERE lmmc.id_country = ' . (int)$idCountry . '
                AND lmmc.id_marketplace = "' . (int)$idMarketplace . '"
                AND lmm.method_marketplace_name = "' . pSQL($methodMarketplaceName) . '"'
            );
            if ($result) {
                return LengowCarrier::getIdActiveCarrierByIdCarrier($result["id_carrier"], (int)$idCountry);
            }
        }
        return false;
    }

    /**
     * Get
     *
     * @param integer $idCountry Prestashop country id
     * @param integer $idMarketplace Lengow marketplace id
     *
     * @return array
     */
    public static function getAllMarketplaceMethodCountryByIdMarketplace($idCountry, $idMarketplace)
    {
        $methods = array();
        try {
            $results = Db::getInstance()->ExecuteS(
                'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_marketplace_method_country
                WHERE id_country = ' . (int)$idCountry . '
                AND id_marketplace = ' . (int)$idMarketplace
            );
        } catch (PrestaShopDatabaseException $e) {
            $results = array();
        }
        if (!empty($results)) {
            foreach ($results as $result) {
                $methods[(int)$result['id_method_marketplace']] = (int)$result['id_carrier'];
            }
        }
        return $methods;
    }

    /**
     * Insert a new marketplace method country
     *
     * @param integer $idCountry Prestashop country id
     * @param integer $idMarketplace Lengow marketplace id
     * @param integer $idCarrier Prestashop carrier id
     * @param integer $idMethodMarketplace Lengow method marketplace id
     *
     * @return integer|false
     */
    public static function insertMarketplaceMethodCountry(
        $idCountry,
        $idMarketplace,
        $idCarrier,
        $idMethodMarketplace
    ) {
        $params = array(
            'id_country' => $idCountry,
            'id_marketplace' => $idMarketplace,
            'id_carrier' => $idCarrier,
            'id_method_marketplace' => $idMethodMarketplace,
        );
        $db = Db::getInstance();
        try {
            if (_PS_VERSION_ < '1.5') {
                $success = $db->autoExecute(_DB_PREFIX_ . 'lengow_marketplace_method_country', $params, 'INSERT');
            } else {
                $success = $db->insert('lengow_marketplace_method_country', $params);
            }
        } catch (PrestaShopDatabaseException $e) {
            $success = false;
        }
        return $success ? self::getIdMarketplaceMethodCountry($idCountry, $idMarketplace, $idMethodMarketplace) : false;
    }

    /**
     * Update a marketplace method country
     *
     * @param integer $idMarketplaceMethodCountry Lengow marketplace method country id
     * @param integer $idCarrier Prestashop carrier id
     *
     * @return integer|false
     */
    public static function updateMarketplaceMethodCountry($idMarketplaceMethodCountry, $idCarrier)
    {
        $db = Db::getInstance();
        if (_PS_VERSION_ < '1.5') {
            try {
                $success = $db->autoExecute(
                    _DB_PREFIX_ . 'lengow_marketplace_method_country',
                    array('id_carrier' => $idCarrier),
                    'UPDATE',
                    'id = ' . (int)$idMarketplaceMethodCountry
                );
            } catch (PrestaShopDatabaseException $e) {
                $success = false;
            }
        } else {
            $success = $db->update(
                'lengow_marketplace_method_country',
                array('id_carrier' => $idCarrier),
                'id = ' . (int)$idMarketplaceMethodCountry
            );
        }
        return $success ? $idMarketplaceMethodCountry : false;
    }

    /**
     * Delete marketplace method country matching
     *
     * @param integer $idMarketplaceMethodCountry Lengow marketplace method country id
     *
     * @return boolean
     */
    public static function deleteMarketplaceMethodCountry($idMarketplaceMethodCountry)
    {
        $table = _PS_VERSION_ < '1.5'
            ? _DB_PREFIX_ . 'lengow_marketplace_method_country'
            : 'lengow_marketplace_method_country';
        return Db::getInstance()->delete($table, 'id = ' . (int)$idMarketplaceMethodCountry);
    }
}
