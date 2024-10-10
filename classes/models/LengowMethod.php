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
/*
 * Lengow Method Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowMethod
{
    /**
     * @var string Lengow method marketplace table name
     */
    public const TABLE_METHOD_MARKETPLACE = 'lengow_method_marketplace';

    /**
     * @var string Lengow marketplace method marketplace table name
     */
    public const TABLE_MARKETPLACE_METHOD_MARKETPLACE = 'lengow_marketplace_method_marketplace';

    /**
     * @var string Lengow marketplace method country table name
     */
    public const TABLE_MARKETPLACE_METHOD_COUNTRY = 'lengow_marketplace_method_country';

    /* Marketplace method fields */
    public const FIELD_ID = 'id';
    public const FIELD_METHOD_MARKETPLACE_NAME = 'method_marketplace_name';
    public const FIELD_METHOD_MARKETPLACE_LABEL = 'method_marketplace_label';
    public const FIELD_METHOD_LENGOW_CODE = 'method_lengow_code';
    public const FIELD_COUNTRY_ID = 'id_country';
    public const FIELD_MARKETPLACE_ID = 'id_marketplace';
    public const FIELD_CARRIER_ID = 'id_carrier';
    public const FIELD_METHOD_MARKETPLACE_ID = 'id_method_marketplace';

    /**
     * Get method marketplace id
     *
     * @param string $methodMarketplaceName Lengow method marketplace name
     *
     * @return int|false
     */
    public static function getIdMethodMarketplace($methodMarketplaceName)
    {
        try {
            $results = Db::getInstance()->ExecuteS(
                'SELECT id, method_marketplace_name FROM ' . _DB_PREFIX_ . 'lengow_method_marketplace
                WHERE method_marketplace_name = "' . pSQL($methodMarketplaceName) . '"'
            );
        } catch (PrestaShopDatabaseException $e) {
            $results = [];
        }
        // additional verification for non-case sensitive Databases
        if (!empty($results)) {
            foreach ($results as $result) {
                if ($result[self::FIELD_METHOD_MARKETPLACE_NAME] === $methodMarketplaceName) {
                    return (int) $result[self::FIELD_ID];
                }
            }
        }

        return false;
    }

    /**
     * Get all methods marketplace by marketplace id
     *
     * @param int $idMarketplace Lengow marketplace id
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
                WHERE lm.id = "' . (int) $idMarketplace . '"'
            );
        } catch (PrestaShopDatabaseException $e) {
            $results = false;
        }

        return is_array($results) ? $results : [];
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
                            $params = [];
                            if ($method->label !== null && Tools::strlen($method->label) > 0) {
                                $params[self::FIELD_METHOD_MARKETPLACE_LABEL] = pSQL($method->label);
                            }
                            if (isset($method->lengow_code)
                                && $method->lengow_code !== null
                                && Tools::strlen($method->lengow_code) > 0
                            ) {
                                $params[self::FIELD_METHOD_LENGOW_CODE] = pSQL($method->lengow_code);
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
     * @return int|false
     */
    public static function insertMethodMarketplace($methodMarketplaceName, $methodMarketplaceLabel, $methodLengowCode = null)
    {
        $params = [
            self::FIELD_METHOD_MARKETPLACE_NAME => pSQL($methodMarketplaceName),
            self::FIELD_METHOD_MARKETPLACE_LABEL => pSQL($methodMarketplaceLabel),
        ];
        if ($methodLengowCode !== null && Tools::strlen($methodLengowCode) > 0) {
            $params[self::FIELD_METHOD_LENGOW_CODE] = pSQL($methodLengowCode);
        }
        $db = Db::getInstance();
        try {
            $success = $db->insert(self::TABLE_METHOD_MARKETPLACE, $params);
        } catch (PrestaShopDatabaseException $e) {
            $success = false;
        }

        return $success ? self::getIdMethodMarketplace($methodMarketplaceName) : false;
    }

    /**
     * Update a method marketplace
     *
     * @param int $idMethodMarketplace Lengow method marketplace id
     * @param array $params all parameters to update a carrier method
     *
     * @return int|false
     */
    public static function updateMethodMarketplace($idMethodMarketplace, $params)
    {
        $db = Db::getInstance();
        $success = $db->update(self::TABLE_METHOD_MARKETPLACE, $params, 'id = ' . (int) $idMethodMarketplace);

        return $success ? $idMethodMarketplace : false;
    }

    /**
     * Match method marketplace with one marketplace
     *
     * @param int $idMarketplace Lengow marketplace id
     * @param int $idMethodMarketplace Lengow method marketplace id
     *
     * @return bool
     */
    public static function matchMethodMarketplaceWithMarketplace($idMarketplace, $idMethodMarketplace)
    {
        $db = Db::getInstance();
        try {
            $result = Db::getInstance()->ExecuteS(
                'SELECT id FROM ' . _DB_PREFIX_ . 'lengow_marketplace_method_marketplace
                WHERE id_marketplace = ' . (int) $idMarketplace . '
                AND id_method_marketplace = ' . (int) $idMethodMarketplace
            );
        } catch (PrestaShopDatabaseException $e) {
            $result = [];
        }
        if (empty($result)) {
            $params = [
                self::FIELD_MARKETPLACE_ID => (int) $idMarketplace,
                self::FIELD_METHOD_MARKETPLACE_ID => (int) $idMethodMarketplace,
            ];
            try {
                $success = $db->insert(self::TABLE_MARKETPLACE_METHOD_MARKETPLACE, $params);
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
     * @param int $idMarketplaceMethodMarketplace Lengow marketplace method marketplace id
     *
     * @return bool
     */
    public static function deleteMarketplaceMethodMarketplace($idMarketplaceMethodMarketplace)
    {
        return Db::getInstance()->delete(
            self::TABLE_MARKETPLACE_METHOD_MARKETPLACE,
            'id = ' . (int) $idMarketplaceMethodMarketplace
        );
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
                    $currentMethodMarketplaces = [];
                    if (isset($marketplace->orders->shipping_methods)) {
                        foreach ($marketplace->orders->shipping_methods as $methodMarketplaceName => $method) {
                            $currentMethodMarketplaces[$methodMarketplaceName] = $method->label;
                        }
                    }
                    // if the method is no longer on the marketplace, removal of matching
                    foreach ($methodMarketplaces as $methodMarketplace) {
                        if (!array_key_exists(
                            $methodMarketplace[self::FIELD_METHOD_MARKETPLACE_NAME],
                            $currentMethodMarketplaces
                        )) {
                            // delete marketplace method matching
                            self::deleteMarketplaceMethodMarketplace(
                                (int) $methodMarketplace['id_marketplace_method_marketplace']
                            );
                            // delete method marketplace id from marketplace method country if is matched
                            self::cleanMarketplaceMethodCountryByIdMarketplace(
                                $idMarketplace,
                                (int) $methodMarketplace[self::FIELD_METHOD_MARKETPLACE_ID]
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
     * @param int $idCountry PrestaShop country id
     * @param int $idMarketplace Lengow marketplace id
     * @param int $idMethodMarketplace Lengow method marketplace id
     *
     * @return int|false
     */
    public static function getIdMarketplaceMethodCountry($idCountry, $idMarketplace, $idMethodMarketplace)
    {
        try {
            $result = Db::getInstance()->ExecuteS(
                'SELECT id FROM ' . _DB_PREFIX_ . 'lengow_marketplace_method_country
                WHERE id_country = ' . (int) $idCountry . '
                AND id_marketplace = ' . (int) $idMarketplace . '
                AND id_method_marketplace = ' . (int) $idMethodMarketplace
            );

            return !empty($result) ? (int) $result[0][self::FIELD_ID] : false;
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
    }

    /**
     * Clean table when match method marketplace is deleted
     *
     * @param int $idMarketplace Lengow marketplace id
     * @param int $idMethodMarketplace Lengow method marketplace id
     */
    public static function cleanMarketplaceMethodCountryByIdMarketplace($idMarketplace, $idMethodMarketplace)
    {
        try {
            $results = Db::getInstance()->ExecuteS(
                'SELECT id FROM ' . _DB_PREFIX_ . 'lengow_marketplace_method_country
                WHERE id_method_marketplace = ' . (int) $idMethodMarketplace . '
                AND id_marketplace = ' . (int) $idMarketplace
            );
        } catch (PrestaShopDatabaseException $e) {
            $results = [];
        }
        if (!empty($results)) {
            foreach ($results as $result) {
                if (isset($result[self::FIELD_ID]) && $result[self::FIELD_ID] > 0) {
                    self::deleteMarketplaceMethodCountry((int) $result[self::FIELD_ID]);
                }
            }
        }
    }

    /**
     * Get carrier id by country id, marketplace id and method marketplace name
     *
     * @param int $idCountry PrestaShop country id
     * @param string $idMarketplace Lengow marketplace id
     * @param string $methodMarketplaceName Lengow marketplace method name
     *
     * @return int|false
     */
    public static function getIdCarrierByMethodMarketplaceName($idCountry, $idMarketplace, $methodMarketplaceName)
    {
        if ($methodMarketplaceName) {
            // find in lengow marketplace method country table
            $result = Db::getInstance()->getRow(
                'SELECT lmmc.id_carrier FROM ' . _DB_PREFIX_ . 'lengow_marketplace_method_country as lmmc
                INNER JOIN ' . _DB_PREFIX_ . 'lengow_method_marketplace as lmm
                    ON lmm.id = lmmc.id_method_marketplace
                WHERE lmmc.id_country = ' . (int) $idCountry . '
                AND lmmc.id_marketplace = "' . (int) $idMarketplace . '"
                AND lmm.method_marketplace_name = "' . pSQL($methodMarketplaceName) . '"'
            );
            if ($result) {
                return LengowCarrier::getIdActiveCarrierByIdCarrier($result['id_carrier'], (int) $idCountry);
            }
        }

        return false;
    }

    /**
     * Get
     *
     * @param int $idCountry PrestaShop country id
     * @param int $idMarketplace Lengow marketplace id
     *
     * @return array
     */
    public static function getAllMarketplaceMethodCountryByIdMarketplace($idCountry, $idMarketplace)
    {
        $methods = [];
        try {
            $results = Db::getInstance()->ExecuteS(
                'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_marketplace_method_country
                WHERE id_country = ' . (int) $idCountry . '
                AND id_marketplace = ' . (int) $idMarketplace
            );
        } catch (PrestaShopDatabaseException $e) {
            $results = [];
        }
        if (!empty($results)) {
            foreach ($results as $result) {
                $methods[(int) $result[self::FIELD_METHOD_MARKETPLACE_ID]] = (int) $result[self::FIELD_CARRIER_ID];
            }
        }

        return $methods;
    }

    /**
     * Insert a new marketplace method country
     *
     * @param int $idCountry PrestaShop country id
     * @param int $idMarketplace Lengow marketplace id
     * @param int $idCarrier PrestaShop carrier id
     * @param int $idMethodMarketplace Lengow method marketplace id
     *
     * @return int|false
     */
    public static function insertMarketplaceMethodCountry($idCountry, $idMarketplace, $idCarrier, $idMethodMarketplace)
    {
        $params = [
            self::FIELD_COUNTRY_ID => $idCountry,
            self::FIELD_MARKETPLACE_ID => $idMarketplace,
            self::FIELD_CARRIER_ID => $idCarrier,
            self::FIELD_METHOD_MARKETPLACE_ID => $idMethodMarketplace,
        ];
        $db = Db::getInstance();
        try {
            $success = $db->insert(self::TABLE_MARKETPLACE_METHOD_COUNTRY, $params);
        } catch (PrestaShopDatabaseException $e) {
            $success = false;
        }

        return $success ? self::getIdMarketplaceMethodCountry($idCountry, $idMarketplace, $idMethodMarketplace) : false;
    }

    /**
     * Update a marketplace method country
     *
     * @param int $idMarketplaceMethodCountry Lengow marketplace method country id
     * @param int $idCarrier PrestaShop carrier id
     *
     * @return int|false
     */
    public static function updateMarketplaceMethodCountry($idMarketplaceMethodCountry, $idCarrier)
    {
        $db = Db::getInstance();
        $success = $db->update(
            self::TABLE_MARKETPLACE_METHOD_COUNTRY,
            [self::FIELD_CARRIER_ID => $idCarrier],
            'id = ' . (int) $idMarketplaceMethodCountry
        );

        return $success ? $idMarketplaceMethodCountry : false;
    }

    /**
     * Delete marketplace method country matching
     *
     * @param int $idMarketplaceMethodCountry Lengow marketplace method country id
     *
     * @return bool
     */
    public static function deleteMarketplaceMethodCountry($idMarketplaceMethodCountry)
    {
        return Db::getInstance()->delete(
            self::TABLE_MARKETPLACE_METHOD_COUNTRY,
            'id = ' . (int) $idMarketplaceMethodCountry
        );
    }
}
