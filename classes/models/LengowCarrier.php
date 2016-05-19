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

class LengowCarrier extends Carrier
{

    const COMPATIBILITY_OK = 1;
    const NO_COMPATIBLITY = 0;
    const COMPATIBILITY_KO = -1;

    /**
     * v3
     * Returns carrier id according to the module name given
     *
     * @param string        $module_name        Module name
     * @param integer       $id_lang            lang id
     * @param LengowAddress $shipping_address   Lengow Address
     *
     * @return integer carrier id
     */
    public static function getIdByModuleName($module_name, $id_lang, $shipping_address)
    {
        $country = new Country($shipping_address->id_country);
        $id_zone = (integer)$country->id_zone;

        $module_name = Tools::strtolower(str_replace(' ', '', $module_name));
        if ($module_name == 'laposte') {
            $module_name = 'socolissimo';
        }
        if (strpos($module_name, 'mondialrelay') !== false || strpos($module_name, 'mondialrelais') !== false) {
            $module_name = 'mondialrelay';
        }

        $carrier_list = self::getCarriers($id_lang, true, false, $id_zone, null, self::ALL_CARRIERS);
        $id_carriers = array();
        foreach ($carrier_list as $c) {
            if (Tools::strtolower(str_replace(' ', '', $c['external_module_name'])) == $module_name) {
                $id_carriers[] = $c['id_carrier'];
            }
        }
        if (count($id_carriers) > 0) {
            if ($module_name == 'mondialrelay') {
                $sql = 'SELECT `id_carrier` FROM `'._DB_PREFIX_.'mr_method`';
                $sql .= empty($shipping_address->id_relay)
                    ? ' WHERE `dlv_mode` = \'LD1\''
                    : ' WHERE `dlv_mode` = \'24R\'';
                $carriers = Db::getInstance()->executeS($sql);
                foreach ($carriers as $carrier) {
                    if (in_array($carrier['id_carrier'], $id_carriers)) {
                        return $carrier['id_carrier'];
                    }
                }
            }
            return $id_carriers[0];
        }
    }

    /**
     * v3
     * Get carrier id for a given name
     *
     * @param string    $name       Carrier name
     * @param integer   $id_lang    lang id
     *
     * @return mixed
     */
    public static function getIdByCarrierName($name, $id_lang)
    {
        $name = Tools::strtolower(str_replace(' ', '', $name));
        foreach (self::getCarriers($id_lang, true, false, false, null, self::ALL_CARRIERS) as $c) {
            if (Tools::strtolower(str_replace(' ', '', $c['name'])) == $name) {
                return $c['id_carrier'];
            }
        }
        return false;
    }

    /**
     * v3
     * Returns the carrier received from the marketplace and returns the matched prestashop carrier if found
     *
     * @param string            $carrier_code
     * @param LengowMarketplace $marketplace
     * @param integer           $id_lang
     * @param LengowAddress     $shipping_address
     *
     * @return integer carrier id
     */
    public static function matchCarrier($carrier_code, $marketplace, $id_lang, $shipping_address)
    {
        $id_carrier = null;
        if ($carrier_name = $marketplace->getCarrierByCode($carrier_code)) {
            $id_carrier = self::getIdByCarrierName($carrier_name, $id_lang);
            if (!$id_carrier) {
                $id_carrier = self::getIdByModuleName($carrier_name, $id_lang, $shipping_address);
            }
        } else {
            $id_carrier = self::getIdByCarrierName($carrier_code, $id_lang);
            if (!$id_carrier) {
                $id_carrier = self::getIdByModuleName($carrier_code, $id_lang, $shipping_address);
            }
        }
        return $id_carrier;
    }

    /**
     * v3
     * Ensure carrier compatibility with SoColissimo and MondialRelay Modules
     *
     * @param integer       $id_customer        customer id
     * @param integer       $id_cart            cart id
     * @param integer       $id_carrier         carrier id
     * @param LengowAddress $shipping_address   order shipping address
     *
     * @return integer    -1 = compatibility not ensured, 0 = not a carrier module, 1 = compatibility ensured
     */
    public static function carrierCompatibility($id_customer, $id_cart, $id_carrier, $shipping_address)
    {
        // SoColissimo
        if ($id_carrier == Configuration::get('SOCOLISSIMO_CARRIER_ID')) {
            if (!LengowMain::isSoColissimoAvailable()) {
                return LengowCarrier::COMPATIBILITY_KO;
            }
            return LengowCarrier::addSoColissimo(
                $id_cart,
                $id_customer,
                $shipping_address
            ) ? LengowCarrier::COMPATIBILITY_OK : LengowCarrier::COMPATIBILITY_KO;
        } else {
            // Mondial Relay
            if (!LengowMain::isMondialRelayAvailable()) {
                return LengowCarrier::COMPATIBILITY_KO;
            }
            $mr = new MondialRelay();
            if ($mr->isMondialRelayCarrier($id_carrier)) {
                $relay = LengowCarrier::getMRRelay($shipping_address->id, $shipping_address->id_relay, $mr);
                if (!$relay) {
                    throw new LengowException(
                        LengowMain::setLogMessage('log.import.error_mondial_relay_not_found', array(
                            'id_relay' => $shipping_address->id_relay
                        ))
                    );
                }
                return LengowCarrier::addMondialRelay(
                    $relay,
                    $id_customer,
                    $id_carrier,
                    $id_cart
                ) ? LengowCarrier::COMPATIBILITY_OK : LengowCarrier::COMPATIBILITY_KO;
            }
        }
        return LengowCarrier::NO_COMPATIBLITY;
    }

    /**
     * v3
     * Save order in SoColissimo table
     *
     * @param integer       $id_cart            cart id
     * @param integer       id_customer         customer id
     * @param LengowAddress $shipping_adddress  shipping address
     *
     * @return bool
     */
    public static function addSoColissimo($id_cart, $id_customer, $shipping_address)
    {
        $sep = DIRECTORY_SEPARATOR;
        $loaded = include_once _PS_MODULE_DIR_.'socolissimo'.$sep.'classes'.$sep.'SCFields.php';
        if (!$loaded) {
            throw new LengowException(
                LengowMain::setLogMessage('log.import.error_colissimo_missing_file', array(
                    'ps_module_dir' => _PS_MODULE_DIR_
                ))
            );
        }

        $customer = new LengowCustomer($id_customer);
        $params = array();
        if (!empty($shipping_address->id_relay)) {
            $delivery_mode = 'A2P';
            $so_colissimo = new SCFields($delivery_mode);

            $params['PRID']          = (string)$shipping_address->id_relay;
            $params['PRCOMPLADRESS'] = (string)$shipping_address->other;
            $params['PRADRESS1']     = (string)$shipping_address->address1;
            // not a param in SoColissimo -> error ?
            $params['PRADRESS2']     = (string)$shipping_address->address2;
            $params['PRADRESS3']     = (string)$shipping_address->address2;
            $params['PRZIPCODE']     = (string)$shipping_address->postcode;
            $params['PRTOWN']        = (string)$shipping_address->city;
            $params['CEEMAIL']       = (string)$customer->email;
        } else {
            $delivery_mode = 'DOM';
            $so_colissimo = new SCFields($delivery_mode);

            $params['CECOMPLADRESS'] = (string)$shipping_address->other;
            $params['CEADRESS1']     = (string)$shipping_address->address1;
            $params['CEADRESS2']     = (string)$shipping_address->address2;
            $params['CEADRESS3']     = (string)$shipping_address->address2;
        }

        // common params
        $params['DELIVERYMODE']  = $delivery_mode;
        $params['CENAME']        = (string)$shipping_address->lastname;
        $params['CEFIRSTNAME']   = (string)$shipping_address->firstname;
        $params['CEPHONENUMBER'] = (string)$shipping_address->phone;
        $params['CECOMPANYNAME'] = (string)$shipping_address->company;
        $params['CEZIPCODE']     = (string)$shipping_address->postcode;
        $params['CETOWN']        = (string)$shipping_address->city;
        $params['PRPAYS']        = (string)Country::getIsoById($shipping_address->id_country);

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
			VALUES ('.(int)$id_cart.', '.(int)$id_customer.',';
        if ($so_colissimo->delivery_mode == SCFields::RELAY_POINT) {
            $sql .= '\''.pSQL($delivery_mode).'\',
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
            $sql .= '\''.pSQL($delivery_mode).'\',\'\',
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
     * v3
     * Check if relay ID is correct
     *
     * @param integer   $id_address_delivery    shipping address id
     * @param string    $id_relay               relay id
     *
     * @return boolean
     */
    public static function getMRRelay($id_address_delivery, $id_relay, $mr)
    {
        $sep = DIRECTORY_SEPARATOR;
        if (empty($id_relay)) {
            return false;
        }
        $loaded = include_once _PS_MODULE_DIR_.'mondialrelay'.$sep.'classes'.$sep.'MRRelayDetail.php';
        if (!$loaded) {
            throw new LengowException(
                LengowMain::setLogMessage('log.import.error_mondial_relay_missing_file', array(
                    'ps_module_dir' => _PS_MODULE_DIR_
                ))
            );
        }
        $params = array(
            'id_address_delivery' => (int)$id_address_delivery,
            'relayPointNumList' => array($id_relay),
        );
        $mr_rd = new MRRelayDetail($params, $mr);
        $mr_rd->init();
        $mr_rd->send();
        $result = $mr_rd->getResult();
        if (empty($result['error'][0]) && array_key_exists($id_relay, $result['success'])) {
            return $result['success'][$id_relay];
        }
        return false;
    }

    /**
     * v3
     * Save order in MR table
     *
     * @param array     $relay          relay info
     * @param integer   $id_customer    customer id
     * @param integer   $id_carrier     carrier id
     * @param integer   $insurance      insurance
     *
     * @return boolean
     */
    public static function addMondialRelay($relay, $id_customer, $id_carrier, $id_cart, $insurance = 0)
    {
        $db = Db::getInstance();
        $query = 'INSERT INTO `'._DB_PREFIX_.'mr_selected` (`id_customer`, `id_method`, `id_cart`, MR_insurance, ';
        if (is_array($relay)) {
            foreach ($relay as $nameKey => $value) {
                $query .= '`MR_Selected_'.MRTools::bqSQL($nameKey).'`, ';
            }
        }
        $query = rtrim($query, ', ').') VALUES ('
            .(int)$id_customer.', '
            .(int)$id_carrier.', '
            .(int)$id_cart.', '
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
     * v3
     * Get Marketplace By Carrier And Country
     *
     * @param integer $id_carrier
     * @param integer $id_country
     *
     * @return mixed
     */
    public static function getMarketplaceCarrier($id_carrier, $id_country)
    {
        //Find in lengow marketplace carrier
        $sql = 'SELECT marketplace_carrier_sku FROM '._DB_PREFIX_.'lengow_marketplace_carrier lmc
        WHERE id_country = '.(int)$id_country.' AND id_carrier ='.(int)$id_carrier;
        $row = Db::getInstance()->getRow($sql);
        if ($row) {
            return $row['marketplace_carrier_sku'];
        }
    }

    /**
     * v3
     * Get Marketplace By Carrier And Country
     *
     * @param string  $marketplace_carrier_sku
     * @param integer $id_country
     *
     * @return mixed
     */
    public static function getMarketplaceByCarrierSku($marketplace_carrier_sku, $id_country)
    {
        if ($marketplace_carrier_sku != '') {
            //Find in lengow marketplace carrier
            $sql = 'SELECT id_carrier FROM '._DB_PREFIX_.'lengow_marketplace_carrier lmc
            WHERE id_country = '.(int)$id_country.' AND marketplace_carrier_sku = "'.pSQL($marketplace_carrier_sku).'"';
            $row = Db::getInstance()->getRow($sql);
            if ($row) {
                return $row['id_carrier'];
            }
        }
        return false;
    }

    /**
     * v3-test
     * Get List Carrier in all Lengow Marketplace API
     *
     * @param boolean $force Force Update
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
     * v3-test
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
     * v3
     * Insert Data into lengow marketplace carrier table
     *
     * @param string $code
     * @param string $name
     *
     * @param integer $countryId
     */
    public static function insertCountryInMarketplace($code, $name, $countryId)
    {
        $result = Db::getInstance()->ExecuteS(
            'SELECT id_country FROM '._DB_PREFIX_.'lengow_marketplace_carrier
                    WHERE marketplace_carrier_sku = "'.pSQL($code).'" AND
                    id_country = '.(int)$countryId
        );
        if (count($result) == 0) {
            Db::getInstance()->autoExecute(
                _DB_PREFIX_.'lengow_marketplace_carrier',
                array(
                    'id_country' => (int)$countryId,
                    'marketplace_carrier_sku' => pSQL($code),
                    'marketplace_carrier_name' => pSQL($name),
                ),
                'INSERT'
            );
        }
    }

    /**
     * v3
     * Get all active carriers
     *
     * @param integer $id_country
     *
     * @return array
     */
    public static function getActiveCarriers($id_country = null)
    {
        $carriers = array();
        if ($id_country) {
            $sql = 'SELECT * FROM '._DB_PREFIX_.'carrier c
                    INNER JOIN '._DB_PREFIX_.'carrier_zone cz ON (cz.id_carrier = c.id_carrier)
                    INNER JOIN '._DB_PREFIX_.'country co ON (co.id_zone = cz.id_zone)
                    WHERE c.active = 1 AND deleted = 0 AND co.id_country = '.(int)$id_country;
        } else {
            $sql = 'SELECT id_carrier, name FROM '._DB_PREFIX_.'carrier WHERE active = 1 AND deleted=0';
        }
        $collection = Db::getInstance()->ExecuteS($sql);
        foreach ($collection as $row) {
            $carriers[$row['id_carrier']] = $row['name'];
        }
        return $carriers;
    }


    /**
     * v3
     * Get active carrier
     *
     * @param integer $id_country
     * @param boolean $force_country
     *
     * @return Carrier
     */
    public static function getActiveCarrier($id_country = null, $force_country = false)
    {
        if (!$id_country && Configuration::get('PS_COUNTRY_DEFAULT') && !$force_country) {
            $sql = 'SELECT lcc.id_carrier FROM '._DB_PREFIX_.'lengow_carrier_country lcc
            INNER JOIN '._DB_PREFIX_.'carrier c ON (c.id_carrier = lcc.id_carrier AND c.active = 1 AND deleted =0)
            WHERE lcc.id_country = '.(int)Configuration::get('PS_COUNTRY_DEFAULT');
            $row = Db::getInstance()->getRow($sql);
            $carrier = new Carrier($row["id_carrier"]);
            if ($carrier->id) {
                return $carrier;
            }
        }
        if ($id_country) {
            $sql = 'SELECT lcc.id_carrier FROM '._DB_PREFIX_.'lengow_carrier_country lcc
            INNER JOIN '._DB_PREFIX_.'carrier c ON (c.id_carrier = lcc.id_carrier AND c.active = 1 AND deleted =0)
            WHERE lcc.id_country = '.(int)$id_country;
            $row = Db::getInstance()->getRow($sql);
            $carrier = new Carrier($row["id_carrier"]);
            if ($carrier->id) {
                return $carrier;
            }
        }
        if (!$id_country && !$force_country) {
            $id_country = Configuration::get('PS_COUNTRY_DEFAULT');
            $sql = 'SELECT * FROM '._DB_PREFIX_.'carrier c
                    INNER JOIN '._DB_PREFIX_.'carrier_zone cz ON (cz.id_carrier = c.id_carrier)
                    INNER JOIN '._DB_PREFIX_.'country co ON (co.id_zone = cz.id_zone)
                    WHERE c.active = 1 AND deleted = 0 AND co.id_country = '.(int)$id_country;
            $row = Db::getInstance()->getRow($sql);
            $carrier = new Carrier($row["id_carrier"]);
            if ($carrier->id) {
                return $carrier;
            }
        }
        return false;
    }

    /**
     * v3
     *
     * @param integer $carrier_id
     * @param integer $id_country
     *
     * @return boolean
     */
    public static function getActiveCarrierByCarrierId($carrier_id, $id_country)
    {
        if (_PS_VERSION_ < '1.5') {
            return $carrier_id;
        }
        $sql = 'SELECT * FROM '._DB_PREFIX_.'carrier c
                    INNER JOIN '._DB_PREFIX_.'carrier_zone cz ON (cz.id_carrier = c.id_carrier)
                    INNER JOIN '._DB_PREFIX_.'country co ON (co.id_zone = cz.id_zone)
                    WHERE c.id_carrier = '.(int)$carrier_id.' AND co.id_country = '.(int)$id_country;
        $row = Db::getInstance()->getRow($sql);
        if ($row) {
            if ((int)$row['deleted'] == 1) {
                $sql = 'SELECT * FROM '._DB_PREFIX_.'carrier c
                    INNER JOIN '._DB_PREFIX_.'carrier_zone cz ON (cz.id_carrier = c.id_carrier)
                    INNER JOIN '._DB_PREFIX_.'country co ON (co.id_zone = cz.id_zone)
                    WHERE c.deleted = 0 AND c.active = 1 AND co.id_country = '.(int)$id_country. ' AND id_reference= '.
                    (int)$row['id_reference'] ;
                $row2 = Db::getInstance()->getRow($sql);
                if ($row2) {
                    return $row2['id_carrier'];
                }
            } else {
                return $row['id_carrier'];
            }
        }
    }

    /**
     * v3
     * Get List Carrier in all Lengow Marketplace
     *
     * @param integer $id_country
     *
     * @return array
     */
    public static function getListMarketplaceCarrier($id_country = null)
    {
        $default_country = Configuration::get('PS_COUNTRY_DEFAULT');

        $condition = $id_country ? 'WHERE lmc.id_country = '.(int)$id_country : '';

        $sql = 'SELECT lmc.id, lmc.id_carrier, co.iso_code, cl.name, lmc.id_country, lmc.marketplace_carrier_sku,
            lmc.marketplace_carrier_name FROM '
            ._DB_PREFIX_.'lengow_marketplace_carrier lmc INNER JOIN '
            ._DB_PREFIX_.'country co ON lmc.id_country=co.id_country INNER JOIN '
            ._DB_PREFIX_.'country_lang cl ON co.id_country=cl.id_country
            AND cl.id_lang= '.(int)Context::getContext()->language->id
            .' '.$condition.' ORDER BY CASE WHEN co.id_country = '.(int)$default_country.' THEN 1 ELSE cl.name END ASC
            , marketplace_carrier_sku ASC;';

        $collection = Db::getInstance()->ExecuteS($sql);

        return $collection;
    }

    /**
     * v3
     * Insert a new marketplace carrier country in the table
     *
     * @param integer $id_country
     *
     * @return boolean
     */
    public static function insert($id_country)
    {
        $default_country = Configuration::get('PS_COUNTRY_DEFAULT');

        $sql = 'SELECT marketplace_carrier_sku, marketplace_carrier_name 
                FROM '._DB_PREFIX_.'lengow_marketplace_carrier WHERE id_country = '.(int)$default_country;

        $marketplace_carriers = Db::getInstance()->executeS($sql);

        foreach ($marketplace_carriers as $key) {
            DB::getInstance()->autoExecute(
                _DB_PREFIX_.'lengow_marketplace_carrier',
                array(
                    'id_country'                => (int)$id_country,
                    'marketplace_carrier_sku'   => pSQL($key['marketplace_carrier_sku']),
                    'marketplace_carrier_name'  => pSQL($key['marketplace_carrier_name'])
                ),
                'INSERT'
            );
        }
        return true;
    }

    /**
     * v3
     * Delete a marketplace carrier country
     *
     * @param integer $id_country
     *
     * @return action
     */
    public static function deleteMarketplaceCarrier($id_country)
    {
        $db = DB::getInstance();
        $db->delete(_DB_PREFIX_.'lengow_marketplace_carrier', 'id_country = '.(int)$id_country);
        return $db;
    }
}
