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
     * Returns carrier id according to the module name given
     * @param string $module_name Module name
     * @param integer $id_lang lang id
     * @param LengowAddress $shipping_address Lengow Address
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
                $sql = 'SELECT `id_carrier` FROM `' . _DB_PREFIX_ . 'mr_method`';
                $sql .= empty($shipping_address->id_relay) ? ' WHERE `dlv_mode` = \'LD1\'' : ' WHERE `dlv_mode` = \'24R\'';
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
     * Get carrier id for a given name
     *
     * @param string $name Carrier name
     * @param integer $id_lang lang id
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
     * Returns the carrier received from the marketplace and returns the matched prestashop carrier if found
     *
     * @param string $carrier_code
     * @param LengowMarketplace $marketplace
     * @param integer $id_lang
     * @param LengowAddress $shipping_address
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
     * Ensure carrier compatibility with SoColissimo and MondialRelay Modules
     *
     * @param integer $id_customer customer id
     * @param integer $id_cart cart id
     * @param integer $id_carrier carrier id
     * @param LengowAddress $shipping_address order shipping address
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
                    throw new LengowCarrierException('relay ' . $shipping_address->id_relay . ' could not be found');
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
     * Save order in SoColissimo table
     *
     * @param integer $id_cart cart id
     * @param integer $id_customer customer id
     * @param LengowAddress $shipping_adddress shipping address
     *
     * @return bool
     */
    public static function addSoColissimo($id_cart, $id_customer, $shipping_address)
    {
        $sep = DIRECTORY_SEPARATOR;
        $loaded = include_once _PS_MODULE_DIR_ . 'socolissimo' . $sep . 'classes' . $sep . 'SCFields.php';
        if (!$loaded) {
            throw new LengowCarrierException(
                'missing file ' . _PS_MODULE_DIR_ . 'socolissimo' . $sep . 'classes' . $sep . 'SCFields.php'
            );
        }

        $customer = new LengowCustomer($id_customer);
        $params = array();
        if (!empty($shipping_address->id_relay)) {
            $delivery_mode = 'A2P';
            $so_colissimo = new SCFields($delivery_mode);

            $params['PRID'] = (string)$shipping_address->id_relay;
            $params['PRCOMPLADRESS'] = (string)$shipping_address->other;
            $params['PRADRESS1'] = (string)$shipping_address->address1;
            // not a param in SoColissimo -> error ?
            $params['PRADRESS2'] = (string)$shipping_address->address2;
            $params['PRADRESS3'] = (string)$shipping_address->address2;
            $params['PRZIPCODE'] = (string)$shipping_address->postcode;
            $params['PRTOWN'] = (string)$shipping_address->city;
            $params['CEEMAIL'] = (string)$customer->email;
        } else {
            $delivery_mode = 'DOM';
            $so_colissimo = new SCFields($delivery_mode);

            $params['CECOMPLADRESS'] = (string)$shipping_address->other;
            $params['CEADRESS1'] = (string)$shipping_address->address1;
            $params['CEADRESS2'] = (string)$shipping_address->address2;
            $params['CEADRESS3'] = (string)$shipping_address->address2;
        }

        // common params
        $params['DELIVERYMODE'] = $delivery_mode;
        $params['CENAME'] = (string)$shipping_address->lastname;
        $params['CEFIRSTNAME'] = (string)$shipping_address->firstname;
        $params['CEPHONENUMBER'] = (string)$shipping_address->phone;
        $params['CECOMPANYNAME'] = (string)$shipping_address->company;
        $params['CEZIPCODE'] = (string)$shipping_address->postcode;
        $params['CETOWN'] = (string)$shipping_address->city;
        $params['PRPAYS'] = (string)Country::getIsoById($shipping_address->id_country);


        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'socolissimo_delivery_info
			( `id_cart`,
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
			VALUES (' . (int)$id_cart . ', ' . (int)$id_customer . ',';

        if ($so_colissimo->delivery_mode == SCFields::RELAY_POINT) {
            $sql .= '\'' . pSQL($delivery_mode) . '\',
				' . (isset($params['PRID']) ? '\'' . pSQL($params['PRID']) . '\'' : '\'\'') . ',
				' . (isset($params['CENAME']) ? '\'' . pSQL($params['CENAME']) . '\'' : '\'\'') . ',
				' . (isset($params['CEFIRSTNAME']) ? '\'' . Tools::ucfirst(pSQL($params['CEFIRSTNAME'])) . '\'' : '\'\'') . ',
				' . (isset($params['PRCOMPLADRESS']) ? '\'' . pSQL($params['PRCOMPLADRESS']) . '\'' : '\'\'') . ',
				' . (isset($params['PRNAME']) ? '\'' . pSQL($params['PRNAME']) . '\'' : '\'\'') . ',
				' . (isset($params['PRADRESS1']) ? '\'' . pSQL($params['PRADRESS1']) . '\'' : '\'\'') . ',
				' . (isset($params['PRADRESS3']) ? '\'' . pSQL($params['PRADRESS3']) . '\'' : '\'\'') . ',
				' . (isset($params['PRADRESS4']) ? '\'' . pSQL($params['PRADRESS4']) . '\'' : '\'\'') . ',
				' . (isset($params['PRZIPCODE']) ? '\'' . pSQL($params['PRZIPCODE']) . '\'' : '\'\'') . ',
				' . (isset($params['PRTOWN']) ? '\'' . pSQL($params['PRTOWN']) . '\'' : '\'\'') . ',
				' . (isset($params['PRPAYS']) ? '\'' . pSQL($params['PRPAYS']) . '\'' : '\'\'') . ',
				' . (isset($params['CEPHONENUMBER']) ? '\'' . pSQL($params['CEPHONENUMBER']) . '\'' : '\'\'') . ',
				' . (isset($params['CEEMAIL']) ? '\'' . pSQL($params['CEEMAIL']) . '\'' : '\'\'') . ',
				' . (isset($params['CECOMPANYNAME']) ? '\'' . pSQL($params['CECOMPANYNAME']) . '\'' : '\'\'') . ',
				' . (
                isset($params['CEDELIVERYINFORMATION']) ? '\'' . pSQL($params['CEDELIVERYINFORMATION']) . '\'' : '\'\''
                ) . ',
				' . (isset($params['CEDOORCODE1']) ? '\'' . pSQL($params['CEDOORCODE1']) . '\'' : '\'\'') . ',
				' . (isset($params['CEDOORCODE2']) ? '\'' . pSQL($params['CEDOORCODE2']) . '\'' : '\'\'') . ',
                ' . (isset($params['CODERESEAU']) ? '\'' . pSQL($params['CODERESEAU']) . '\'' : '\'\'') . ',
                ' . (isset($params['CENAME']) ? '\'' . Tools::ucfirst(pSQL($params['CENAME'])) . '\'' : '\'\'') . ',
                ' . (isset($params['CEFIRSTNAME']) ? '\'' . Tools::ucfirst(pSQL($params['CEFIRSTNAME'])) . '\'' : '\'\'') . ')';
        } else {
            $sql .= '\'' . pSQL($delivery_mode) . '\',\'\',
				' . (isset($params['CENAME']) ? '\'' . Tools::ucfirst(pSQL($params['CENAME'])) . '\'' : '\'\'') . ',
				' . (isset($params['CEFIRSTNAME']) ? '\'' . Tools::ucfirst(pSQL($params['CEFIRSTNAME'])) . '\'' : '\'\'') . ',
				' . (isset($params['CECOMPLADRESS']) ? '\'' . pSQL($params['CECOMPLADRESS']) . '\'' : '\'\'') . ',
				' . (isset($params['CEADRESS1']) ? '\'' . pSQL($params['CEADRESS1']) . '\'' : '\'\'') . ',
				' . (isset($params['CEADRESS4']) ? '\'' . pSQL($params['CEADRESS4']) . '\'' : '\'\'') . ',
				' . (isset($params['CEADRESS3']) ? '\'' . pSQL($params['CEADRESS3']) . '\'' : '\'\'') . ',
				' . (isset($params['CEADRESS2']) ? '\'' . pSQL($params['CEADRESS2']) . '\'' : '\'\'') . ',
				' . (isset($params['CEZIPCODE']) ? '\'' . pSQL($params['CEZIPCODE']) . '\'' : '\'\'') . ',
				' . (isset($params['CETOWN']) ? '\'' . pSQL($params['CETOWN']) . '\'' : '\'\'') . ',
				' . (isset($params['PRPAYS']) ? '\'' . pSQL($params['PRPAYS']) . '\'' : '\'\'') . ',
				' . (isset($params['CEPHONENUMBER']) ? '\'' . pSQL($params['CEPHONENUMBER']) . '\'' : '\'\'') . ',
				' . (isset($params['CEEMAIL']) ? '\'' . pSQL($params['CEEMAIL']) . '\'' : '\'\'') . ',
				' . (isset($params['CECOMPANYNAME']) ? '\'' . pSQL($params['CECOMPANYNAME']) . '\'' : '\'\'') . ',
				' . (
                isset($params['CEDELIVERYINFORMATION']) ? '\'' . pSQL($params['CEDELIVERYINFORMATION']) . '\'' : '\'\''
                ) . ',
				' . (isset($params['CEDOORCODE1']) ? '\'' . pSQL($params['CEDOORCODE1']) . '\'' : '\'\'') . ',
				' . (isset($params['CEDOORCODE2']) ? '\'' . pSQL($params['CEDOORCODE2']) . '\'' : '\'\'') . ',
                ' . (isset($params['CODERESEAU']) ? '\'' . pSQL($params['CODERESEAU']) . '\'' : '\'\'') . ',
                ' . (isset($params['CENAME']) ? '\'' . Tools::ucfirst(pSQL($params['CENAME'])) . '\'' : '\'\'') . ',
                ' . (isset($params['CEFIRSTNAME']) ? '\'' . Tools::ucfirst(pSQL($params['CEFIRSTNAME'])) . '\'' : '\'\'') . ')';
        }

        return Db::getInstance()->execute($sql);
    }

    /**
     * Check if relay ID is correct
     *
     * @param integer $id_address_delivery shipping address id
     * @param string $id_relay relay id
     *
     * @return boolean
     */
    public static function getMRRelay($id_address_delivery, $id_relay, $mr)
    {
        $sep = DIRECTORY_SEPARATOR;
        if (empty($id_relay)) {
            return false;
        }
        $loaded = include_once _PS_MODULE_DIR_ . 'mondialrelay' . $sep . 'classes' . $sep . 'MRRelayDetail.php';
        if (!$loaded) {
            throw new LengowCarrierException(
                'missing file ' . _PS_MODULE_DIR_ . 'mondialrelay' . $sep . 'classes' . $sep . 'MRRelayDetail.php'
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
     * Save order in MR table
     *
     * @param array $relay relay info
     * @param integer $id_customer customer id
     * @param integer $id_carrier carrier id
     * @param integer $insurance insurance
     *
     * @return boolean
     */
    public static function addMondialRelay($relay, $id_customer, $id_carrier, $id_cart, $insurance = 0)
    {
        $db = Db::getInstance();
        $query = 'INSERT INTO `' . _DB_PREFIX_ . 'mr_selected` (`id_customer`, `id_method`, `id_cart`, MR_insurance, ';
        if (is_array($relay)) {
            foreach ($relay as $nameKey => $value) {
                $query .= '`MR_Selected_' . MRTools::bqSQL($nameKey) . '`, ';
            }
        }
        $query = rtrim($query, ', ') . ') VALUES ('
            . (int)$id_customer . ', '
            . (int)$id_carrier . ', '
            . (int)$id_cart . ', '
            . (int)$insurance . ', ';

        if (is_array($relay)) {
            foreach ($relay as $nameKey => $value) {
                $query .= '"' . pSQL($value) . '", ';
            }
        }
        $query = rtrim($query, ', ') . ')';

        return $db->execute($query);
    }

    /**
     * v3-test
     * Get List Carrier in all Lengow Marketplace
     * @return array
     */
    public static function getListMarketplaceCarrier()
    {
        $result = LengowConnector::queryApi('get', '/v3.0/marketplaces');

        $carrierCollection = array();
        foreach ($result as $marketplace => $values) {
            if (isset($values->orders->carriers)) {
                foreach ($values->orders->carriers as $key => $value) {
                    $carrierCollection[$key] = true;
                }
            }
        }
        if ($carrierCollection) {
            $finalCarrier = array();
            foreach (array_keys($carrierCollection) as $carrier) {
                $finalCarrier[] = $carrier;
                $finalCarrier[] = $carrier."_RELAY";
            }
            return $finalCarrier;
        } else {
            return array();
        }
    }

    /**
     * v3-test
     * Sync Marketplace's Carrier
     */
    public static function syncListMarketplace()
    {
        $defaultCountryId = Configuration::get('PS_COUNTRY_DEFAULT');
        $carrierCollection = self::getListMarketplaceCarrier();
        $countryCollectionId = array();
        foreach ($carrierCollection as $lengowMarketplaceSku) {
            $countryCollection = Db::getInstance()->ExecuteS(
                'SELECT DISTINCT(id_country) as id_country FROM ' . _DB_PREFIX_ . 'lengow_marketplace_carrier'
            );
            foreach ($countryCollection as $country) {
                $countryCollectionId[] = $country['id_country'];
                self::insertCountryInMarketplace($lengowMarketplaceSku, $country['id_country']);
            }

            if (count($countryCollection) == 0 || !in_array($defaultCountryId, $countryCollectionId)) {
                foreach ($carrierCollection as $lengowMarketplaceSku) {
                    self::insertCountryInMarketplace($lengowMarketplaceSku, $defaultCountryId);
                }
            }
        }
    }

    /**
     * v3-test
     * Insert Data into lengow marketplace carrier table
     */
    public static function insertCountryInMarketplace($lengowMarketplaceSku, $countryId)
    {
        $result = Db::getInstance()->ExecuteS(
            'SELECT id_country FROM ' . _DB_PREFIX_ . 'lengow_marketplace_carrier
                    WHERE marketplace_carrier_sku = "' . pSQL($lengowMarketplaceSku) . '" AND
                    id_country = '.(int)$countryId
        );
        if (count($result) == 0) {
            Db::getInstance()->autoExecute(
                _DB_PREFIX_ . 'lengow_marketplace_carrier',
                array(
                    'id_country' => (int)$countryId,
                    'marketplace_carrier_sku' => pSQL($lengowMarketplaceSku),
                ),
                'INSERT'
            );
        }
    }

    public static function getMarketplaceCarrier()
    {
        return "LAPOSTE";
    }
}
