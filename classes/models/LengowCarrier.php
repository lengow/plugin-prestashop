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
 * Lengow Carrier Class
 */
class LengowCarrier extends Carrier
{
    /**
    * integer Carrier compatibility ensured
    */
    const COMPATIBILITY_OK = 1;

    /**
    * integer not a carrier module
    */
    const NO_COMPATIBLITY = 0;

    /**
    * integer Carrier compatibility not ensured
    */
    const COMPATIBILITY_KO = -1;

    /**
     * Ensure carrier compatibility with SoColissimo and MondialRelay Modules
     *
     * @param integer       $idCustomer      customer id
     * @param integer       $idCart          cart id
     * @param integer       $idCarrier       carrier id
     * @param LengowAddress $shippingAddress order shipping address
     *
     * @return integer -1 = compatibility not ensured, 0 = not a carrier module, 1 = compatibility ensured
     */
    public static function carrierCompatibility($idCustomer, $idCart, $idCarrier, $shippingAddress)
    {
        // SoColissimo
        if ($idCarrier == Configuration::get('SOCOLISSIMO_CARRIER_ID')) {
            if (!LengowMain::isSoColissimoAvailable()) {
                return LengowCarrier::COMPATIBILITY_KO;
            }
            return LengowCarrier::addSoColissimo(
                $idCart,
                $idCustomer,
                $shippingAddress
            ) ? LengowCarrier::COMPATIBILITY_OK : LengowCarrier::COMPATIBILITY_KO;
        } else {
            // Mondial Relay
            if (!LengowMain::isMondialRelayAvailable()) {
                return LengowCarrier::COMPATIBILITY_KO;
            }
            $mr = new MondialRelay();
            if ($mr->isMondialRelayCarrier($idCarrier)) {
                $relay = LengowCarrier::getMRRelay($shippingAddress->id, $shippingAddress->idRelay, $mr);
                if (!$relay) {
                    throw new LengowException(
                        LengowMain::setLogMessage(
                            'log.import.error_mondial_relay_not_found',
                            array('id_relay' => $shippingAddress->idRelay)
                        )
                    );
                }
                return LengowCarrier::addMondialRelay(
                    $relay,
                    $idCustomer,
                    $idCarrier,
                    $idCart
                ) ? LengowCarrier::COMPATIBILITY_OK : LengowCarrier::COMPATIBILITY_KO;
            }
        }
        return LengowCarrier::NO_COMPATIBLITY;
    }

    /**
     * Save order in SoColissimo table
     *
     * @param integer       $idCart          cart id
     * @param integer       $idCustomer      customer id
     * @param LengowAddress $shippingAddress shipping address
     *
     * @return bool
     */
    public static function addSoColissimo($idCart, $idCustomer, $shippingAddress)
    {
        $sep = DIRECTORY_SEPARATOR;
        $loaded = include_once _PS_MODULE_DIR_.'socolissimo'.$sep.'classes'.$sep.'SCFields.php';
        if (!$loaded) {
            throw new LengowException(
                LengowMain::setLogMessage(
                    'log.import.error_colissimo_missing_file',
                    array('ps_module_dir' => _PS_MODULE_DIR_)
                )
            );
        }
        $customer = new LengowCustomer($idCustomer);
        $params = array();
        if (!empty($shippingAddress->idRelay)) {
            $deliveryMode = 'A2P';
            $soColissimo = new SCFields($deliveryMode);
            $params['PRID']          = (string)$shippingAddress->idRelay;
            $params['PRCOMPLADRESS'] = (string)$shippingAddress->other;
            $params['PRADRESS1']     = (string)$shippingAddress->address1;
            // not a param in SoColissimo -> error ?
            $params['PRADRESS2']     = (string)$shippingAddress->address2;
            $params['PRADRESS3']     = (string)$shippingAddress->address2;
            $params['PRZIPCODE']     = (string)$shippingAddress->postcode;
            $params['PRTOWN']        = (string)$shippingAddress->city;
            $params['CEEMAIL']       = (string)$customer->email;
        } else {
            $deliveryMode = 'DOM';
            $soColissimo = new SCFields($deliveryMode);
            $params['CECOMPLADRESS'] = (string)$shippingAddress->other;
            $params['CEADRESS2']     = (string)$shippingAddress->address2;
            $params['CEADRESS3']     = (string)$shippingAddress->address1;
        }
        // common params
        $params['DELIVERYMODE']  = $deliveryMode;
        $params['CENAME']        = (string)$shippingAddress->lastname;
        $params['CEFIRSTNAME']   = (string)$shippingAddress->firstname;
        $params['CEPHONENUMBER'] = (string)$shippingAddress->phone;
        $params['CECOMPANYNAME'] = (string)$shippingAddress->company;
        $params['CEZIPCODE']     = (string)$shippingAddress->postcode;
        $params['CETOWN']        = (string)$shippingAddress->city;
        $params['PRPAYS']        = (string)Country::getIsoById($shippingAddress->id_country);
        $sql = 'INSERT INTO '._DB_PREFIX_.'socolissimo_delivery_info
            (`id_cart`,
            `id_customer`,
            `delivery_mode`,
            `prid`,
            `prname`,
            `prfirstname`,
            `prcompladress`,
            `pradress1`,
            `pradress2`,
            `pradress3`,
            `pradress4`,
            `przipcode`,
            `prtown`,
            `cecountry`,
            `cephonenumber`,
            `ceemail`,
            `cecompanyname`,
            `cedeliveryinformation`,
            `cedoorcode1`,
            `cedoorcode2`,
            `codereseau`,
            `cename`,
            `cefirstname`)
            VALUES ('.(int)$idCart.', '.(int)$idCustomer.',';
        if ($soColissimo->delivery_mode == SCFields::RELAY_POINT) {
            $sql .= '\''.pSQL($deliveryMode).'\',
                '.(isset($params['PRID']) ? '\''.pSQL($params['PRID']).'\'' : '\'\'').',
                '.(isset($params['CENAME']) ? '\''.pSQL($params['CENAME']).'\'' : '\'\'').',
                '.(isset($params['CEFIRSTNAME']) ? '\''.Tools::ucfirst(pSQL($params['CEFIRSTNAME'])).'\'' : '\'\'').',
                '.(isset($params['PRCOMPLADRESS']) ? '\''.pSQL($params['PRCOMPLADRESS']).'\'' : '\'\'').',
                '.(isset($params['PRNAME']) ? '\''.pSQL($params['PRNAME']).'\'' : '\'\'').',
                '.(isset($params['PRADRESS1']) ? '\''.pSQL($params['PRADRESS1']).'\'' : '\'\'').',
                '.(isset($params['PRADRESS3']) ? '\''.pSQL($params['PRADRESS3']).'\'' : '\'\'').',
                '.(isset($params['PRADRESS4']) ? '\''.pSQL($params['PRADRESS4']).'\'' : '\'\'').',
                '.(isset($params['PRZIPCODE']) ? '\''.pSQL($params['PRZIPCODE']).'\'' : '\'\'').',
                '.(isset($params['PRTOWN']) ? '\''.pSQL($params['PRTOWN']).'\'' : '\'\'').',
                '.(isset($params['PRPAYS']) ? '\''.pSQL($params['PRPAYS']).'\'' : '\'\'').',
                '.(isset($params['CEPHONENUMBER']) ? '\''.pSQL($params['CEPHONENUMBER']).'\'' : '\'\'').',
                '.(isset($params['CEEMAIL']) ? '\''.pSQL($params['CEEMAIL']).'\'' : '\'\'').',
                '.(isset($params['CECOMPANYNAME']) ? '\''.pSQL($params['CECOMPANYNAME']).'\'' : '\'\'').',
                '.(
                isset($params['CEDELIVERYINFORMATION']) ? '\''.pSQL($params['CEDELIVERYINFORMATION']).'\'' : '\'\''
                ).',
                '.(isset($params['CEDOORCODE1']) ? '\''.pSQL($params['CEDOORCODE1']).'\'' : '\'\'').',
                '.(isset($params['CEDOORCODE2']) ? '\''.pSQL($params['CEDOORCODE2']).'\'' : '\'\'').',
                '.(isset($params['CODERESEAU']) ? '\''.pSQL($params['CODERESEAU']).'\'' : '\'\'').',
                '.(isset($params['CENAME']) ? '\''.Tools::ucfirst(pSQL($params['CENAME'])).'\'' : '\'\'').',
                '.(
                    isset($params['CEFIRSTNAME']) ? '\''.Tools::ucfirst(pSQL($params['CEFIRSTNAME'])).'\'' : '\'\''
                ).')';
        } else {
            $sql .= '\''.pSQL($deliveryMode).'\',\'\',
                '.(isset($params['CENAME']) ? '\''.Tools::ucfirst(pSQL($params['CENAME'])).'\'' : '\'\'').',
                '.(isset($params['CEFIRSTNAME']) ? '\''.Tools::ucfirst(pSQL($params['CEFIRSTNAME'])) . '\'' : '\'\'').',
                '.(isset($params['CECOMPLADRESS']) ? '\''.pSQL($params['CECOMPLADRESS']) . '\'' : '\'\'').',
                '.(isset($params['CEADRESS1']) ? '\''.pSQL($params['CEADRESS1']).'\'' : '\'\'').',
                '.(isset($params['CEADRESS4']) ? '\''.pSQL($params['CEADRESS4']).'\'' : '\'\'').',
                '.(isset($params['CEADRESS3']) ? '\''.pSQL($params['CEADRESS3']).'\'' : '\'\'').',
                '.(isset($params['CEADRESS2']) ? '\''.pSQL($params['CEADRESS2']).'\'' : '\'\'').',
                '.(isset($params['CEZIPCODE']) ? '\''.pSQL($params['CEZIPCODE']).'\'' : '\'\'').',
                '.(isset($params['CETOWN']) ? '\''.pSQL($params['CETOWN']).'\'' : '\'\'').',
                '.(isset($params['PRPAYS']) ? '\''.pSQL($params['PRPAYS']).'\'' : '\'\'').',
                '.(isset($params['CEPHONENUMBER']) ? '\''.pSQL($params['CEPHONENUMBER']).'\'' : '\'\'').',
                '.(isset($params['CEEMAIL']) ? '\''.pSQL($params['CEEMAIL']).'\'' : '\'\'') . ',
                '.(isset($params['CECOMPANYNAME']) ? '\''.pSQL($params['CECOMPANYNAME']).'\'' : '\'\'').',
                '.(
                    isset($params['CEDELIVERYINFORMATION']) ? '\''.pSQL($params['CEDELIVERYINFORMATION']).'\'' : '\'\''
                ).',
                '.(isset($params['CEDOORCODE1']) ? '\''.pSQL($params['CEDOORCODE1']).'\'' : '\'\'').',
                '.(isset($params['CEDOORCODE2']) ? '\''.pSQL($params['CEDOORCODE2']).'\'' : '\'\'').',
                '.(isset($params['CODERESEAU']) ? '\''.pSQL($params['CODERESEAU']).'\'' : '\'\'').',
                '.(isset($params['CENAME']) ? '\''.Tools::ucfirst(pSQL($params['CENAME'])).'\'' : '\'\'').',
                '.(
                    isset($params['CEFIRSTNAME']) ? '\''.Tools::ucfirst(pSQL($params['CEFIRSTNAME'])).'\'' : '\'\''
                ).')';
        }
        return Db::getInstance()->execute($sql);
    }

    /**
     * Check if relay ID is correct
     *
     * @param integer $idAddressDelivery shipping address id
     * @param string  $idRelay           relay id
     *
     * @return boolean
     */
    public static function getMRRelay($idAddressDelivery, $idRelay, $mr)
    {
        $sep = DIRECTORY_SEPARATOR;
        if (empty($idRelay)) {
            return false;
        }
        $loaded = include_once _PS_MODULE_DIR_.'mondialrelay'.$sep.'classes'.$sep.'MRRelayDetail.php';
        if (!$loaded) {
            throw new LengowException(
                LengowMain::setLogMessage(
                    'log.import.error_mondial_relay_missing_file',
                    array('ps_module_dir' => _PS_MODULE_DIR_)
                )
            );
        }
        $params = array(
            'id_address_delivery' => (int)$idAddressDelivery,
            'relayPointNumList'   => array($idRelay),
        );
        $mrRd = new MRRelayDetail($params, $mr);
        $mrRd->init();
        $mrRd->send();
        $result = $mrRd->getResult();
        if (empty($result['error'][0]) && array_key_exists($idRelay, $result['success'])) {
            return $result['success'][$idRelay];
        }
        return false;
    }

    /**
     * Save order in MR table
     *
     * @param array   $relay       relay info
     * @param integer $idCustomer customer id
     * @param integer $idCarrier  carrier id
     * @param integer $idCart     cart id
     * @param integer $insurance   insurance
     *
     * @return boolean
     */
    public static function addMondialRelay($relay, $idCustomer, $idCarrier, $idCart, $insurance = 0)
    {
        $db = Db::getInstance();
        $query = 'INSERT INTO `'._DB_PREFIX_.'mr_selected` (`id_customer`, `id_method`, `id_cart`, MR_insurance, ';
        if (is_array($relay)) {
            foreach ($relay as $nameKey => $value) {
                $query .= '`MR_Selected_'.MRTools::bqSQL($nameKey).'`, ';
            }
        }
        $query = rtrim($query, ', ').') VALUES ('
            .(int)$idCustomer.', '
            .(int)$idCarrier.', '
            .(int)$idCart.', '
            .(int)$insurance.', ';

        if (is_array($relay)) {
            foreach ($relay as $nameKey => $value) {
                $query .= '"'.pSQL($value).'", ';
            }
        }
        $query = rtrim($query, ', ').')';
        return $db->execute($query);
    }

    /**
     * Get Id Carrier By Marketplace Carrier Sku And Country
     *
     * @param string  $marketplaceCarrierSku
     * @param integer $idCountry
     *
     * @return mixed
     */
    public static function getIdCarrierByMarketplaceCarrierSku($marketplaceCarrierSku, $idCountry)
    {
        if ($marketplaceCarrierSku != '') {
            // find in lengow marketplace carrier
            $sql = 'SELECT id_carrier FROM '._DB_PREFIX_.'lengow_marketplace_carrier lmc
            WHERE id_country = '.(int)$idCountry.' AND marketplace_carrier_sku = "'.pSQL($marketplaceCarrierSku).'"';
            $row = Db::getInstance()->getRow($sql);
            if ($row) {
                return LengowCarrier::getActiveCarrierByCarrierId($row["id_carrier"], (int)$idCountry);
            }
        }
        return false;
    }

    /**
     * Get List Carrier in all Lengow Marketplace API
     *
     * @param boolean $force Force Update
     *
     * @return array
     */
    public static function getListMarketplaceCarrierAPI($force = false)
    {
        if (!$force) {
            $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_LIST_MARKET_UPDATE');
            if ((time() - strtotime($updatedAt)) < 10800) { //3 hours
                return Tools::JsonDecode(LengowConfiguration::getGlobalValue('LENGOW_LIST_MARKETPLACE'), true);
            }
        }
        $finalCarrier = array();
        $findCarrier = array();
        $shops = LengowShop::findAll(true);
        foreach ($shops as $shop) {
            $result = LengowConnector::queryApi('get', '/v3.0/marketplaces', $shop['id_shop']);
            if (!$result) {
                continue;
            }
            $carrierCollection = array();
            foreach ($result as $values) {
                if (isset($values->orders->carriers)) {
                    foreach ($values->orders->carriers as $key => $value) {
                        $carrierCollection[$key] = $value->label;
                    }
                }
            }
            if ($carrierCollection) {
                foreach ($carrierCollection as $code => $name) {
                    if (!isset($findCarrier[$code])) {
                        $finalCarrier[] = array('code' => $code, 'name' => $name);
                        $finalCarrier[] = array('code' => $code."_RELAY", 'name' => $name.' Relay');
                    }
                    $findCarrier[$code] = true;
                }
            }
        }
        LengowConfiguration::updateGlobalValue('LENGOW_LIST_MARKETPLACE', Tools::JsonEncode($finalCarrier));
        LengowConfiguration::updateGlobalValue('LENGOW_LIST_MARKET_UPDATE', date('Y-m-d H:i:s'));
        return $finalCarrier;
    }

    /**
     * Sync Marketplace's Carrier
     */
    public static function syncListMarketplace()
    {
        $defaultCountryId = Configuration::get('PS_COUNTRY_DEFAULT');
        $carrierCollection = self::getListMarketplaceCarrierAPI(true);
        $countryCollectionId = array();
        foreach ($carrierCollection as $carrier) {
            $countryCollection = Db::getInstance()->ExecuteS(
                'SELECT DISTINCT(id_country) as id_country FROM '._DB_PREFIX_.'lengow_carrier_country'
            );
            foreach ($countryCollection as $country) {
                $countryCollectionId[] = $country['id_country'];
                self::insertCountryInMarketplace($carrier['code'], $carrier['name'], $country['id_country']);
            }
            $countryCollection = Db::getInstance()->ExecuteS(
                'SELECT DISTINCT(id_country) as id_country FROM '._DB_PREFIX_.'lengow_marketplace_carrier'
            );
            foreach ($countryCollection as $country) {
                $countryCollectionId[] = $country['id_country'];
                self::insertCountryInMarketplace($carrier['code'], $carrier['name'], $country['id_country']);
            }
            if (count($countryCollection) == 0 || !in_array($defaultCountryId, $countryCollectionId)) {
                foreach ($carrierCollection as $carrier) {
                    self::insertCountryInMarketplace($carrier['code'], $carrier['name'], $defaultCountryId);
                }
            }
        }
    }

    /**
     * Insert Data into lengow marketplace carrier table
     *
     * @param string  $code
     * @param string  $name
     * @param integer $idCountry
     *
     * @param integer
     */
    public static function insertCountryInMarketplace($code, $name, $idCountry)
    {
        $result = Db::getInstance()->ExecuteS(
            'SELECT id_country FROM '._DB_PREFIX_.'lengow_marketplace_carrier
                    WHERE marketplace_carrier_sku = "'.pSQL($code).'" AND
                    id_country = '.(int)$idCountry
        );
        if (count($result) == 0) {
            if (_PS_VERSION_ < '1.5') {
                Db::getInstance()->autoExecute(
                    _DB_PREFIX_.'lengow_marketplace_carrier',
                    array(
                        'id_country'               => (int)$idCountry,
                        'marketplace_carrier_sku'  => pSQL($code),
                        'marketplace_carrier_name' => pSQL($name),
                    ),
                    'INSERT'
                );
            } else {
                Db::getInstance()->insert(
                    'lengow_marketplace_carrier',
                    array(
                        'id_country'               => (int)$idCountry,
                        'marketplace_carrier_sku'  => pSQL($code),
                        'marketplace_carrier_name' => pSQL($name),
                    )
                );
            }
        }
    }

    /**
     * Get all active carriers
     *
     * @param integer $idCountry
     *
     * @return array
     */
    public static function getActiveCarriers($idCountry = null)
    {
        $carriers = array();
        if ($idCountry) {
            $sql = 'SELECT * FROM '._DB_PREFIX_.'carrier c
                    INNER JOIN '._DB_PREFIX_.'carrier_zone cz ON (cz.id_carrier = c.id_carrier)
                    INNER JOIN '._DB_PREFIX_.'country co ON (co.id_zone = cz.id_zone)
                    WHERE c.active = 1 AND deleted = 0 AND co.id_country = '.(int)$idCountry;
        } else {
            $sql = 'SELECT id_carrier, id_reference, name FROM '._DB_PREFIX_.'carrier WHERE active = 1 AND deleted = 0';
        }
        $collection = Db::getInstance()->ExecuteS($sql);
        foreach ($collection as $row) {
            if (_PS_VERSION_ < '1.5') {
                $carriers[$row['id_carrier']] = $row['name'];
            } else {
                $carriers[$row['id_reference']] = $row['name'];
            }
            
        }
        return $carriers;
    }


    /**
     * Get Default carrier
     *
     * @param mixed $idCountry
     *
     * @return mixed
     */
    public static function getDefaultCarrier($idCountry = false)
    {
        $idCarrier = false;
        // get default id with lengow tables for a specific country id
        if ($idCountry) {
            $sql = 'SELECT id_carrier FROM '._DB_PREFIX_.'lengow_carrier_country
                WHERE id_country = '.(int)$idCountry;
            $row = Db::getInstance()->getRow($sql);
            if ($row) {
                // get newest carrier id
                $idCarrier = LengowCarrier::getActiveCarrierByCarrierId($row["id_carrier"], $idCountry);
            }
        } else {
            $idCountry = (int)Configuration::get('PS_COUNTRY_DEFAULT');
            // get default id with lengow tables without country id
            $sql = 'SELECT id_carrier FROM '._DB_PREFIX_.'lengow_carrier_country
                WHERE id_country = '.$idCountry;
            $row = Db::getInstance()->getRow($sql);
            if ($row) {
                // get newest carrier id
                $idCarrier = LengowCarrier::getActiveCarrierByCarrierId($row["id_carrier"], $idCountry);
            }
            // get first carrier id with prestashop tables for default country
            if (!$idCarrier) {
                $sql = 'SELECT * FROM '._DB_PREFIX_.'carrier c
                    INNER JOIN '._DB_PREFIX_.'carrier_zone cz ON (cz.id_carrier = c.id_carrier)
                    INNER JOIN '._DB_PREFIX_.'country co ON (co.id_zone = cz.id_zone)
                    WHERE c.active = 1 AND deleted = 0 AND co.id_country = '.$idCountry;
                $row = Db::getInstance()->getRow($sql);
                if ($row) {
                    $idCarrier = (int)$row["id_carrier"];
                }
            }
        }
        if ($idCarrier) {
            $carrier = new Carrier($idCarrier);
            if ($carrier->id) {
                return $carrier;
            }
        }
        return false;
    }

    /**
     * Get active carrier by country
     *
     * @param integer $idCarrier
     * @param integer $idCountry
     *
     * @return mixed
     */
    public static function getActiveCarrierByCarrierId($idCarrier, $idCountry)
    {
        // search with id_carrier for Prestashop 1.4 and id_reference for other versions
        $idReference = _PS_VERSION_ < '1.5' ? 'c.id_carrier' : 'c.id_reference';
        $sql = 'SELECT * FROM '._DB_PREFIX_.'carrier c
            INNER JOIN '._DB_PREFIX_.'carrier_zone cz ON (cz.id_carrier = c.id_carrier)
            INNER JOIN '._DB_PREFIX_.'country co ON (co.id_zone = cz.id_zone)
            WHERE '.$idReference.' = '.(int)$idCarrier.' AND co.id_country = '.(int)$idCountry;
        $row = Db::getInstance()->getRow($sql);
        if ($row) {
            if ((int)$row['deleted'] == 1) {
                if (_PS_VERSION_ < '1.5') {
                    return false;
                }
                $sql = 'SELECT * FROM '._DB_PREFIX_.'carrier c
                    INNER JOIN '._DB_PREFIX_.'carrier_zone cz ON (cz.id_carrier = c.id_carrier)
                    INNER JOIN '._DB_PREFIX_.'country co ON (co.id_zone = cz.id_zone)
                    WHERE c.deleted = 0 AND c.active = 1 AND co.id_country = '.(int)$idCountry
                    . ' AND id_reference= '.(int)$row['id_reference'] ;
                $row2 = Db::getInstance()->getRow($sql);
                if ($row2) {
                    return (int)$row2['id_carrier'];
                }
            } else {
                return (int)$row['id_carrier'];
            }
        }
        return false;
    }

    /**
     * Get List Carrier in all Lengow Marketplace
     *
     * @param integer $idCountry
     *
     * @return array
     */
    public static function getListMarketplaceCarrier($idCountry = null)
    {
        $defaultCountry = Configuration::get('PS_COUNTRY_DEFAULT');
        $condition = $idCountry ? 'WHERE lmc.id_country = '.(int)$idCountry : '';
        $sql = 'SELECT lmc.id, lmc.id_carrier, co.iso_code, cl.name, lmc.id_country, lmc.marketplace_carrier_sku,
            lmc.marketplace_carrier_name FROM '
            ._DB_PREFIX_.'lengow_marketplace_carrier lmc INNER JOIN '
            ._DB_PREFIX_.'country co ON lmc.id_country=co.id_country INNER JOIN '
            ._DB_PREFIX_.'country_lang cl ON co.id_country=cl.id_country
            AND cl.id_lang= '.(int)Context::getContext()->language->id
            .' '.$condition.' ORDER BY CASE WHEN co.id_country = '.(int)$defaultCountry.' THEN 1 ELSE cl.name END ASC
            , marketplace_carrier_sku ASC;';

        $collection = Db::getInstance()->ExecuteS($sql);

        return $collection;
    }

    /**
     * Insert a new marketplace carrier country in the table
     *
     * @param integer $idCountry
     *
     * @return boolean
     */
    public static function insert($idCountry)
    {
        $defaultCountry = Configuration::get('PS_COUNTRY_DEFAULT');
        $sql = 'SELECT marketplace_carrier_sku, marketplace_carrier_name 
                FROM '._DB_PREFIX_.'lengow_marketplace_carrier WHERE id_country = '.(int)$defaultCountry;
        $marketplaceCarriers = Db::getInstance()->executeS($sql);
        foreach ($marketplaceCarriers as $key) {
            if (_PS_VERSION_ < '1.5') {
                DB::getInstance()->autoExecute(
                    _DB_PREFIX_.'lengow_marketplace_carrier',
                    array(
                        'id_country'                => (int)$idCountry,
                        'marketplace_carrier_sku'   => pSQL($key['marketplace_carrier_sku']),
                        'marketplace_carrier_name'  => pSQL($key['marketplace_carrier_name'])
                    ),
                    'INSERT'
                );
            } else {
                DB::getInstance()->insert(
                    'lengow_marketplace_carrier',
                    array(
                        'id_country'                => (int)$idCountry,
                        'marketplace_carrier_sku'   => pSQL($key['marketplace_carrier_sku']),
                        'marketplace_carrier_name'  => pSQL($key['marketplace_carrier_name'])
                    )
                );
            }
        }
        return true;
    }

    /**
     * Delete a marketplace carrier country
     *
     * @param integer $idCountry
     *
     * @return action
     */
    public static function deleteMarketplaceCarrier($idCountry)
    {
        $db = DB::getInstance();
        $db->delete(_DB_PREFIX_.'lengow_marketplace_carrier', 'id_country = '.(int)$idCountry);
        return $db;
    }

    /**
     * Is default Carrier exist
     **
     * @return bool
     */
    public static function isDefaultCarrierActive()
    {
        $idDefaultCountry = Configuration::get('PS_COUNTRY_DEFAULT');
        return LengowCarrier::getDefaultCarrier($idDefaultCountry);
    }
}
