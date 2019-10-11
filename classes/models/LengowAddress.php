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
 * Lengow Address Class
 */
class LengowAddress extends Address
{
    /**
     * @var string constant billing
     */
    const BILLING = 'billing';

    /**
     * @var string constant delivery
     */
    const SHIPPING = 'delivery';

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
        'common_country_iso_a2',
        'phone_home',
        'phone_office',
        'phone_mobile',
        'vat_number',
    );

    /**
     * @var array definition array for prestashop 1.4
     */
    public static $definitionLengow = array(
        'id_country' => array('required' => true),
        'alias' => array('required' => true, 'size' => 32),
        'company' => array('size' => 32),
        'lastname' => array('required' => true, 'size' => 32),
        'firstname' => array('required' => true, 'size' => 32),
        'address1' => array('required' => true, 'size' => 128),
        'address2' => array('size' => 128),
        'postcode' => array('size' => 12),
        'city' => array('required' => true, 'size' => 64),
        'other' => array('size' => 300),
        'phone' => array('check' => true, 'size' => 16),
        'phone_mobile' => array('check' => true, 'size' => 16),
        'phone_office' => array('check' => true),
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
        if ($row['id_address'] > 0) {
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
     * Clean firstname or lastname to Prestashop
     *
     * @param string $name customer name
     *
     * @return string
     */
    public static function cleanName($name)
    {
        return LengowMain::replaceAccentedChars(trim(preg_replace('/[0-9!<>,;?=+()@#"�{}_$%:]/', '', $name)));
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
            $temp[$node] = (string)$api->{$node};
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
        if (_PS_VERSION_ < 1.5) {
            return self::$definitionLengow;
        }
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
        $this->lastname = $data['last_name'];
        $this->firstname = $data['first_name'];
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
            if (isset($constraints['required']) && $constraints['required']
                || isset($constraints['check']) && $constraints['check']
                || $fieldName === 'phone'
                || $fieldName === 'phone_mobile'
            ) {
                if (empty($this->{$fieldName})) {
                    $this->validateFieldLengow($fieldName, self::LENGOW_EMPTY_ERROR);
                }
            }
            if (isset($constraints['size'])) {
                if (Tools::strlen($this->{$fieldName}) > $constraints['size']) {
                    $this->validateFieldLengow($fieldName, self::LENGOW_SIZE_ERROR);
                }
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
                }
                $this->firstname = $names['firstname'];
                $this->lastname = $names['lastname'];
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
                if ($fieldName === 'phone_mobile') {
                    if (!empty($this->phoneOffice)) {
                        $this->phone_mobile = $this->phoneOffice;
                    }
                }
                break;
            default:
                return;
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
                        } elseif (Tools::strlen($this->address2) < $address2Maxlength) {
                            if (!empty($this->address2)) {
                                $this->address2 .= ' ';
                            }
                            $this->address2 .= $addressPart;
                            continue;
                        } elseif (Tools::strlen($this->other) < $otherMaxlength) {
                            if (!empty($this->other)) {
                                $this->other .= ' ';
                            }
                            $this->other .= $addressPart;
                            continue;
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
                return;
        }
    }
}
