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
 * Lengow Address Class
 */
class LengowAddress extends Address
{
    /**
     * @var string code ISO A2 for Spain
     */
    const ISO_A2_ES = 'ES';

    /**
     * @var string code ISO A2 for Italy
     */
    const ISO_A2_IT = 'IT';

    /**
     * @var integer constant error empty
     */
    const LENGOW_EMPTY_ERROR = 1;

    /**
     * @var integer constant error size
     */
    const LENGOW_SIZE_ERROR = 2;

    /**
     * @var array API fields for an address
     */
    public static $addressApiNodes = array(
        'company',
        'civility',
        'email',
        'last_name',
        'first_name',
        'first_line',
        'full_name',
        'second_line',
        'complement',
        'zipcode',
        'city',
        'state_region',
        'common_country_iso_a2',
        'phone_home',
        'phone_office',
        'phone_mobile',
        'vat_number',
    );

    /**
     * @var string phone_office given in API
     */
    public $phoneOffice;

    /**
     * @var string full address
     */
    public $fullAddress = '';

    /**
     * @var string full name
     */
    public $fullName;

    /**
     * @var string Relay id (so colissimo, Mondial Relay)
     */
    public $idRelay;

    /**
     * @var array All region codes for correspondence
     */
    protected $regionCodes = array(
        self::ISO_A2_ES => array(
            '01' => 'ES-VI',
            '02' => 'ES-AB',
            '03' => 'ES-A',
            '04' => 'ES-AL',
            '05' => 'ES-AV',
            '06' => 'ES-BA',
            '07' => 'ES-PM',
            '08' => 'ES-B',
            '09' => 'ES-BU',
            '10' => 'ES-CC',
            '11' => 'ES-CA',
            '12' => 'ES-CS',
            '13' => 'ES-CR',
            '14' => 'ES-CO',
            '15' => 'ES-C',
            '16' => 'ES-CU',
            '17' => 'ES-GI',
            '18' => 'ES-GR',
            '19' => 'ES-GU',
            '20' => 'ES-SS',
            '21' => 'ES-H',
            '22' => 'ES-HU',
            '23' => 'ES-J',
            '24' => 'ES-LE',
            '25' => 'ES-L',
            '26' => 'ES-LO',
            '27' => 'ES-LU',
            '28' => 'ES-M',
            '29' => 'ES-MA',
            '30' => 'ES-MU',
            '31' => 'ES-NA',
            '32' => 'ES-OR',
            '33' => 'ES-O',
            '34' => 'ES-P',
            '35' => 'ES-CG',
            '36' => 'ES-PO',
            '37' => 'ES-SA',
            '38' => 'ES-TF',
            '39' => 'ES-S',
            '40' => 'ES-SG',
            '41' => 'ES-SE',
            '42' => 'ES-SO',
            '43' => 'ES-T',
            '44' => 'ES-TE',
            '45' => 'ES-TO',
            '46' => 'ES-V',
            '47' => 'ES-VA',
            '48' => 'ES-BI',
            '49' => 'ES-ZA',
            '50' => 'ES-Z',
            '51' => 'ES-CE',
            '52' => 'ES-ML',
        ),
        self::ISO_A2_IT => array(
            '00' => 'RM',
            '01' => 'VT',
            '02' => 'RI',
            '03' => 'FR',
            '04' => 'LT',
            '05' => 'TR',
            '06' => 'PG',
            '07' => array(
                '07000-07019' => 'SS',
                '07020-07029' => 'OT',
                '07030-07049' => 'SS',
                '07050-07999' => 'SS',
            ),
            '08' => array(
                '08000-08010' => 'OR',
                '08011-08012' => 'NU',
                '08013-08013' => 'OR',
                '08014-08018' => 'NU',
                '08019-08019' => 'OR',
                '08020-08020' => 'OT',
                '08021-08029' => 'NU',
                '08030-08030' => 'OR',
                '08031-08032' => 'NU',
                '08033-08033' => 'CA',
                '08034-08034' => 'OR',
                '08035-08035' => 'CA',
                '08036-08039' => 'NU',
                '08040-08042' => 'OG',
                '08043-08043' => 'CA',
                '08044-08049' => 'OG',
                '08050-08999' => 'NU',
            ),
            '09' => array(
                '09000-09009' => 'CA',
                '09010-09017' => 'CI',
                '09018-09019' => 'CA',
                '09020-09041' => 'VS',
                '09042-09069' => 'CA',
                '09070-09099' => 'OR',
                '09100-09169' => 'CA',
                '09170-09170' => 'OR',
                '09171-09999' => 'CA',
            ),
            '10' => 'TO',
            '11' => 'AO',
            '12' => array(
                '12000-12070' => 'CN',
                '12071-12071' => 'SV',
                '12072-12999' => 'CN',
            ),
            '13' => array(
                '13000-13799' => 'VC',
                '13800-13999' => 'BI',
            ),
            '14' => 'AT',
            '15' => 'AL',
            '16' => 'GE',
            '17' => 'SV',
            '18' => array(
                '18000-18024' => 'IM',
                '18025-18025' => 'CN',
                '18026-18999' => 'IM',
            ),
            '19' => 'SP',
            '20' => array(
                '20000-20799' => 'MI',
                '20800-20999' => 'MB',
            ),
            '21' => 'VA',
            '22' => 'CO',
            '23' => array(
                '23000-23799' => 'SO',
                '23800-23999' => 'LC',
            ),
            '24' => 'BG',
            '25' => 'BS',
            '26' => array(
                '26000-26799' => 'CR',
                '26800-26999' => 'LO',
            ),
            '27' => 'PV',
            '28' => array(
                '28000-28799' => 'NO',
                '28800-28999' => 'VB',
            ),
            '29' => 'PC',
            '30' => 'VE',
            '31' => 'TV',
            '32' => 'BL',
            '33' => array(
                '33000-33069' => 'UD',
                '33070-33099' => 'PN',
                '33100-33169' => 'UD',
                '33170-33999' => 'PN',
            ),
            '34' => array(
                '34000-34069' => 'TS',
                '34070-34099' => 'GO',
                '34100-34169' => 'TS',
                '34170-34999' => 'GO',
            ),
            '35' => 'PD',
            '36' => 'VI',
            '37' => 'VR',
            '38' => 'TN',
            '39' => 'BZ',
            '40' => 'BO',
            '41' => 'MO',
            '42' => 'RE',
            '43' => 'PR',
            '44' => 'FE',
            '45' => 'RO',
            '46' => 'MN',
            '47' => array(
                '47000-47799' => 'FC',
                '47800-47999' => 'RN',
            ),
            '48' => 'RA',
            '50' => 'FI',
            '51' => 'PT',
            '52' => 'AR',
            '53' => 'SI',
            '54' => 'MS',
            '55' => 'LU',
            '56' => 'PI',
            '57' => 'LI',
            '58' => 'GR',
            '59' => 'PO',
            '60' => 'AN',
            '61' => 'PU',
            '62' => 'MC',
            '63' => array(
                '63000-63799' => 'AP',
                '63800-63999' => 'FM',
            ),
            '64' => 'TE',
            '65' => 'PE',
            '66' => 'CH',
            '67' => 'AQ',
            '70' => 'BA',
            '71' => 'FG',
            '72' => 'BR',
            '73' => 'LE',
            '74' => 'TA',
            '75' => 'MT',
            '76' => 'BT',
            '80' => 'NA',
            '81' => 'CE',
            '82' => 'BN',
            '83' => 'AV',
            '84' => 'SA',
            '85' => 'PZ',
            '86' => array(
                '86000-86069' => 'CB',
                '86070-86099' => 'IS',
                '86100-86169' => 'CB',
                '86170-86999' => 'IS',
            ),
            '87' => 'CS',
            '88' => array(
                '88000-88799' => 'CZ',
                '88800-88999' => 'KR',
            ),
            '89' => array(
                '89000-89799' => 'RC',
                '89800-89999' => 'VV',
            ),
            '90' => 'PA',
            '91' => 'TP',
            '92' => 'AG',
            '93' => 'CL',
            '94' => 'EN',
            '95' => 'CT',
            '96' => 'SR',
            '97' => 'RG',
            '98' => 'ME',
        ),
    );

    /**
     * Specify if an address is already in base
     *
     * @param string $alias address alias
     *
     * @return LengowAddress|false
     */
    public static function getByAlias($alias)
    {
        $row = Db::getInstance()->getRow(
            'SELECT `id_address`
            FROM ' . _DB_PREFIX_ . 'address a
            WHERE a.`alias` = "' . pSQL($alias) . '"'
        );
        if ($row && $row['id_address'] > 0) {
            return new LengowAddress($row['id_address']);
        }
        return false;
    }

    /**
     * Hash an alias and get the address with unique hash
     *
     * @param string $alias address alias
     *
     * @return LengowAddress|false
     */
    public static function getByHash($alias)
    {
        return self::getByAlias(self::hash($alias));
    }

    /**
     * Extract first name and last name from a name field
     *
     * @param string $fullName customer full name
     *
     * @return array
     */
    public static function extractNames($fullName)
    {
        self::cleanName($fullName);
        $arrayName = explode(' ', $fullName);
        $lastName = $arrayName[0];
        $firstName = str_replace($lastName . ' ', '', $fullName);
        $lastName = empty($lastName) ? '' : self::cleanName($lastName);
        $firstName = empty($firstName) ? '' : self::cleanName($firstName);
        return array(
            'firstname' => Tools::ucfirst(Tools::strtolower($firstName)),
            'lastname' => Tools::ucfirst(Tools::strtolower($lastName)),
        );
    }

    /**
     * Clean firstname or lastname to PrestaShop
     *
     * @param string $name customer name
     *
     * @return string
     */
    public static function cleanName($name)
    {
        return LengowMain::replaceAccentedChars(trim(preg_replace('/[0-9!<>,;?=+()@#"�{}_$%:\/\\\]/', '', $name)));
    }

    /**
     * Hash address with md5
     *
     * @param string $address customer full address
     *
     * @return string Hash
     */
    public static function hash($address)
    {
        return md5($address);
    }

    /**
     * Extract address data from API
     *
     * @param array $api API nodes containing the data
     *
     * @return array
     */
    public static function extractAddressDataFromAPI($api)
    {
        $temp = array();
        foreach (self::$addressApiNodes as $node) {
            $temp[$node] = (string) $api->{$node};
        }
        return $temp;
    }

    /**
     * Get definition array
     *
     * @return array
     */
    public static function getFieldDefinition()
    {
        return self::$definition['fields'];
    }

    /**
     * Assign API data
     *
     * @param array $data API datas
     *
     * @return LengowAddress
     */
    public function assign($data = array())
    {
        $this->company = $data['company'];
        $this->lastname = self::cleanName($data['last_name']);
        $this->firstname = self::cleanName($data['first_name']);
        $this->fullName = $data['full_name'];
        $this->address1 = preg_replace('/[!<>?=+@{}_$%]/sim', '', $data['first_line']);
        $this->address2 = preg_replace('/[!<>?=+@{}_$%]/sim', '', $data['second_line']);
        $this->other = preg_replace('/[!<>?=+@{}_$%]/sim', '', $data['complement']);
        if (isset($data['id_relay'])) {
            $this->idRelay = $data['id_relay'];
            $this->other .= empty($this->other) ? 'Relay id: ' . $this->idRelay : ' Relay id: ' . $this->idRelay;
        }
        $this->postcode = $data['zipcode'];
        $this->city = preg_replace('/[!<>?=+@{}_$%]/sim', '', $data['city']);
        $this->id_country = Country::getByIso($data['common_country_iso_a2']);
        $this->id_state = $this->getIdState($this->id_country, $data);
        $this->phone = $data['phone_home'];
        $this->phone_mobile = $data['phone_mobile'];
        $this->phoneOffice = $data['phone_office'];
        $this->vat_number = $data['vat_number'];
        $this->fullAddress = $data['address_full'];
        $this->alias = self::hash($this->fullAddress);
        return $this;
    }

    /**
     * Validate Lengow
     *
     * @throws Exception|LengowException invalid object
     *
     * @return boolean
     */
    public function validateLengow()
    {
        $definition = self::getFieldDefinition();
        foreach ($definition as $fieldName => $constraints) {
            if (((isset($constraints['required']) && $constraints['required'])
                    || (isset($constraints['check']) && $constraints['check'])
                    || $fieldName === 'phone'
                    || $fieldName === 'phone_mobile')
                && empty($this->{$fieldName})
            ) {
                $this->validateFieldLengow($fieldName, self::LENGOW_EMPTY_ERROR);
            }
            if (isset($constraints['size']) && Tools::strlen($this->{$fieldName}) > $constraints['size']) {
                $this->validateFieldLengow($fieldName, self::LENGOW_SIZE_ERROR);
            }
        }
        // validateFields
        $return = $this->validateFields(false, true);
        if (is_string($return)) {
            throw new LengowException($return);
        }
        $this->add();
        return true;
    }

    /**
     * Modify a field according to the type of error
     *
     * @param string $fieldName incorrect field
     * @param string $errorType type of error
     */
    public function validateFieldLengow($fieldName, $errorType)
    {
        switch ($errorType) {
            case self::LENGOW_EMPTY_ERROR:
                $this->validateEmptyLengow($fieldName);
                break;
            case self::LENGOW_SIZE_ERROR:
                $this->validateSizeLengow($fieldName);
                break;
            default:
                break;
        }
    }

    /**
     * Modify an empty field
     *
     * @param string $fieldName field name
     */
    public function validateEmptyLengow($fieldName)
    {
        switch ($fieldName) {
            case 'lastname':
            case 'firstname':
                if ($fieldName === 'lastname') {
                    $fieldName = 'firstname';
                } else {
                    $fieldName = 'lastname';
                }
                $names = self::extractNames($this->{$fieldName});
                $this->firstname = $names['firstname'];
                $this->lastname = $names['lastname'];
                // check full name if last_name and first_name are empty
                if (empty($this->firstname) && empty($this->lastname)) {
                    $names = self::extractNames($this->fullName);
                    $this->firstname = $names['firstname'];
                    $this->lastname = $names['lastname'];
                }
                if (empty($this->firstname)) {
                    $this->firstname = '--';
                }
                if (empty($this->lastname)) {
                    $this->lastname = '--';
                }
                break;
            case 'address1':
                if (!empty($this->address2)) {
                    $this->address1 = $this->address2;
                    $this->address2 = null;
                } elseif (!empty($this->other)) {
                    $this->address1 = $this->other;
                    $this->other = null;
                }
                break;
            case 'phone':
            case 'phone_mobile':
                $this->phone = LengowMain::cleanPhone($this->phone);
                $this->phone_mobile = LengowMain::cleanPhone($this->phone_mobile);
                $this->phoneOffice = LengowMain::cleanPhone($this->phoneOffice);
                if ($fieldName === 'phone') {
                    if (!empty($this->phoneOffice)) {
                        $this->phone = $this->phoneOffice;
                    } elseif (!empty($this->phone_mobile)) {
                        $this->phone = $this->phone_mobile;
                    }
                }
                if (($fieldName === 'phone_mobile') && !empty($this->phoneOffice)) {
                    $this->phone_mobile = $this->phoneOffice;
                }
                break;
            default:
                break;
        }
    }

    /**
     * Modify a field to fit its size
     *
     * @param string $fieldName field name
     */
    public function validateSizeLengow($fieldName)
    {
        switch ($fieldName) {
            case 'address1':
            case 'address2':
            case 'other':
                $fullAddressArray = explode(' ', $this->fullAddress);
                if (count($fullAddressArray) < 1) {
                    $definition = self::getFieldDefinition();
                    $address1Maxlength = $definition['address1']['size'];
                    $address2Maxlength = $definition['address1']['size'];
                    $otherMaxlength = $definition['other']['size'];
                    $this->address1 = '';
                    $this->address2 = '';
                    $this->other = '';
                    foreach ($fullAddressArray as $addressPart) {
                        if (Tools::strlen($this->address1) < $address1Maxlength) {
                            if (!empty($this->address1)) {
                                $this->address1 .= ' ';
                            }
                            $this->address1 .= $addressPart;
                            continue;
                        }
                        if (Tools::strlen($this->address2) < $address2Maxlength) {
                            if (!empty($this->address2)) {
                                $this->address2 .= ' ';
                            }
                            $this->address2 .= $addressPart;
                            continue;
                        }
                        if (Tools::strlen($this->other) < $otherMaxlength) {
                            if (!empty($this->other)) {
                                $this->other .= ' ';
                            }
                            $this->other .= $addressPart;
                        }
                    }
                }
                break;
            case 'phone':
                $this->phone = LengowMain::cleanPhone($this->phone);
                break;
            case 'phone_mobile':
                $this->phone_mobile = LengowMain::cleanPhone($this->phone_mobile);
                break;
            default:
                break;
        }
    }

    /**
     * Get country state if exist
     *
     * @param integer $idCountry PrestaShop country id
     * @param array $addressData API address data
     *
     * @return integer
     */
    protected function getIdState($idCountry, $addressData)
    {
        $idState = 0;
        $countryIsoA2 = $addressData['common_country_iso_a2'];
        $stateRegion = $addressData['state_region'];
        if (in_array($countryIsoA2, array(self::ISO_A2_ES, self::ISO_A2_IT), true)) {
            $idState = $this->searchIdStateByPostcode($idCountry, $countryIsoA2, $addressData['zipcode']);
        } elseif (!empty($stateRegion)) {
            $idState = $this->searchIdStateByStateRegion($idCountry, $stateRegion);
        }
        return $idState;
    }

    /**
     * Search state by postcode for specific countries
     *
     * @param integer $idCountry PrestaShop country id
     * @param string $countryIsoA2 country iso a2 code
     * @param string $postcode address postcode
     *
     * @return integer
     */
    protected function searchIdStateByPostcode($idCountry, $countryIsoA2, $postcode)
    {
        $idState = 0;
        $postcodeSubstr = Tools::substr(str_pad($postcode, 5, '0', STR_PAD_LEFT), 0, 2);
        switch ($countryIsoA2) {
            case self::ISO_A2_ES:
                $isoCode = isset($this->regionCodes[$countryIsoA2][$postcodeSubstr])
                    ? $this->regionCodes[$countryIsoA2][$postcodeSubstr]
                    : false;
                break;
            case self::ISO_A2_IT:
                $isoCode = isset($this->regionCodes[$countryIsoA2][$postcodeSubstr])
                    ? $this->regionCodes[$countryIsoA2][$postcodeSubstr]
                    : false;
                if ($isoCode && is_array($isoCode) && !empty($isoCode)) {
                    $isoCode = $this->getIsoCodeFromIntervalPostcodes((int) $postcode, $isoCode);
                }
                break;
            default:
                $isoCode = false;
                break;
        }
        if ($isoCode) {
            $idState = $this->getIdStateByIsoAndCountry($isoCode, $idCountry);
        }
        return $idState;
    }

    /**
     * Get iso code from interval postcodes
     *
     * @param integer $postcode address postcode
     * @param array $intervalPostcodes postcode intervals
     *
     * @return string|false
     */
    protected function getIsoCodeFromIntervalPostcodes($postcode, $intervalPostcodes)
    {
        foreach ($intervalPostcodes as $intervalPostcode => $isoCode) {
            $intervalPostcodes = explode('-', $intervalPostcode);
            if (!empty($intervalPostcodes) && count($intervalPostcodes) === 2) {
                $minPostcode = is_numeric($intervalPostcodes[0]) ? (int) $intervalPostcodes[0] : false;
                $maxPostcode = is_numeric($intervalPostcodes[1]) ? (int) $intervalPostcodes[1] : false;
                if (($minPostcode && $maxPostcode) && ($postcode >= $minPostcode && $postcode <= $maxPostcode)) {
                    return $isoCode;
                }
            }
        }
        return false;
    }

    /**
     * Get a state id with its iso code
     *
     * @param string $isoCode State iso code
     * @param integer $idCountry PrestaShop country id
     *
     * @return integer
     */
    protected function getIdStateByIsoAndCountry($isoCode, $idCountry)
    {
        $idState = Db::getInstance()->getValue(
            'SELECT `id_state`
            FROM `' . _DB_PREFIX_ . 'state`
            WHERE `iso_code` = \'' . pSQL($isoCode) . '\' AND `id_country` = ' . $idCountry
        );
        return (int) $idState;
    }

    /**
     * Search state id by state return by api
     *
     * @param integer $idCountry PrestaShop country id
     * @param string $stateRegion address state region
     *
     * @return integer
     */
    protected function searchIdStateByStateRegion($idCountry, $stateRegion)
    {
        $idState = 0;
        $countryStates = State::getStatesByIdCountry($idCountry);
        $stateRegionCleaned = $this->cleanString($stateRegion);
        if (!empty($countryStates) && !empty($stateRegion)) {
            // strict search on the region code
            foreach ($countryStates as $countryState) {
                $isoCodeCleaned = $this->cleanString($countryState['iso_code']);
                if ($stateRegionCleaned === $isoCodeCleaned) {
                    $idState = (int) $countryState['id_state'];
                    break;
                }
            }
            // approximate search on the state name
            if (!$idState) {
                $results = array();
                foreach ($countryStates as $countryState) {
                    $nameCleaned = $this->cleanString($countryState['name']);
                    similar_text($stateRegionCleaned, $nameCleaned, $percent);
                    if ($percent > 70) {
                        $results[(int) $percent] = (int) $countryState['id_state'];
                    }
                }
                if (!empty($results)) {
                    krsort($results);
                    $idState = current($results);
                }
            }
        }
        return $idState;
    }

    /**
     * Cleaning a string before search
     *
     * @param string $string string to clean
     *
     * @return string
     */
    protected function cleanString($string)
    {
        $string = Tools::strtolower(str_replace(array(' ', '-', '_', '.'), '', trim($string)));
        return LengowMain::replaceAccentedChars(html_entity_decode($string));
    }
}
