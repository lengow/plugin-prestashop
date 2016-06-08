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
    public static $ADDRESS_API_NODES = array(
        'company',
        'civility',
        'email',
        'last_name',
        'first_name',
        'first_line',
        'second_line',
        'complement',
        'zipcode',
        'city',
        'common_country_iso_a2',
        'phone_home',
        'phone_office',
        'phone_mobile',
    );

    /**
     * Definition array for prestashop 1.4
     *
     * @var array
     */
    public static $definition_lengow = array(
        'id_country'    =>  array('required' => true),
        'alias'         =>  array('required' => true, 'size' => 32),
        'company'       =>  array('size' => 32),
        'lastname'      =>  array('required' => true, 'size' => 32),
        'firstname'     =>  array('required' => true, 'size' => 32),
        'address1'      =>  array('required' => true, 'size' => 128),
        'address2'      =>  array('size' => 128),
        'postcode'      =>  array('size' => 12),
        'city'          =>  array('required' => true, 'size' => 64),
        'other'         =>  array('size' => 300),
        'phone'         =>  array('check' => true, 'size' => 16),
        'phone_mobile'  =>  array('check' => true, 'size' => 16),
        'phone_office'  =>  array('check' => true),
    );

    /**
     * @var string phone_office given in API
     */
    public $phone_office;

    /**
     * @var string full address
     */
    public $address_full = '';

    /**
     * @var string Relay id (so colissimo, Mondial Relay)
     */
    public $id_relay;

    /**
    * Specify if an address is already in base
    *
    * @param string $alias address alias
    *
    * @return mixed LengowAddress or false
    */
    public static function getByAlias($alias)
    {
        $row = Db::getInstance()->getRow('
                 SELECT `id_address`
                 FROM '._DB_PREFIX_.'address a
                 WHERE a.`alias` = "'.pSQL($alias).'"');
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
    * @return mixed Address or false
    */
    public static function getByHash($alias)
    {
        return self::getByAlias(self::hash($alias));
    }

    /**
     * Extract firstname and lastname from a name field
     *
     * @param string $fullname
     *
     * @return array
     */
    public static function extractNames($fullname)
    {
        LengowAddress::cleanName($fullname);
        $array_name = explode(' ', $fullname);
        $lastname = $array_name[0];
        $firstname = str_replace($lastname.' ', '', $fullname);
        $lastname = empty($lastname) ? '' : self::cleanName($lastname);
        $firstname = empty($firstname) ? '' : self::cleanName($firstname);
        return array(
            'firstname' => Tools::ucfirst(Tools::strtolower($firstname)),
            'lastname' => Tools::ucfirst(Tools::strtolower($lastname))
        );
    }

    /**
     * Clean firstname or lastname to Prestashop
     *
     * @param string $name Name
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
     * @param string $address Full address
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
        foreach (LengowAddress::$ADDRESS_API_NODES as $node) {
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
            return LengowAddress::$definition_lengow;
        }
        return LengowAddress::$definition['fields'];
    }

    /**
     * Assign API data
     *
     * @param array $data API data
     *
     * @return LengowAddress
     */
    public function assign($data = array())
    {
        $this->company = $data['company'];
        $this->lastname = $data['last_name'];
        $this->firstname = $data['first_name'];
        $this->address1 = preg_replace('/[!<>?=+@{}_$%]/sim', '', $data['first_line']);
        $this->address2 = preg_replace('/[!<>?=+@{}_$%]/sim', '', $data['second_line']);
        $this->other = preg_replace('/[!<>?=+@{}_$%]/sim', '', $data['complement']);
        if (isset($data['id_relay'])) {
            $this->id_relay = $data['id_relay'];
            $this->other .= 'Relay id: '.$this->id_relay;
        }
        $this->postcode = $data['zipcode'];
        $this->city = preg_replace('/[!<>?=+@{}_$%]/sim', '', $data['city']);
        $this->id_country = Country::getByIso($data['common_country_iso_a2']);
        $this->phone = $data['phone_home'];
        $this->phone_mobile = $data['phone_mobile'];
        $this->phone_office = $data['phone_office'];
        $this->address_full = $data['address_full'];
        $this->alias = LengowAddress::hash($this->address_full);
        return $this;
    }

    /**
     * Validate Lengow
     *
     * @return bool true if object is valid
     */
    public function validateLengow()
    {
        $definition = LengowAddress::getFieldDefinition();
        foreach ($definition as $field_name => $constraints) {
            if (isset($constraints['required']) && $constraints['required']
                || isset($constraints['check']) && $constraints['check']
                || $field_name == 'phone'
                || $field_name == 'phone_mobile'
            ) {
                if (empty($this->{$field_name})) {
                    $this->validateFieldLengow($field_name, LengowAddress::LENGOW_EMPTY_ERROR);
                }
            }
            if (isset($constraints['size'])) {
                if (Tools::strlen($this->{$field_name}) > $constraints['size']) {
                    $this->validateFieldLengow($field_name, LengowAddress::LENGOW_SIZE_ERROR);
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
     * @param string $error_type type of error
     * @param string $field incorrect field
     */
    public function validateFieldLengow($field, $error_type)
    {
        switch ($error_type) {
            case LengowAddress::LENGOW_EMPTY_ERROR:
                $this->validateEmptyLengow($field);
                break;
            case LengowAddress::LENGOW_SIZE_ERROR:
                $this->validateSizeLengow($field);
                break;
            default:
                break;
        }
    }

    /**
     * Modify an empty field
     *
     * @param string $field field name
     */
    public function validateEmptyLengow($field)
    {
        switch ($field) {
            case 'lastname':
            case 'firstname':
                if ($field == 'lastname') {
                    $field = 'firstname';
                } else {
                    $field = 'lastname';
                }
                $names = LengowAddress::extractNames($this->{$field});
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
                $this->phone_office = LengowMain::cleanPhone($this->phone_office);
                if ($field == 'phone') {
                    if (!empty($this->phone_office)) {
                        $this->phone = $this->phone_office;
                    } elseif (!empty($this->phone_mobile)) {
                        $this->phone = $this->phone_mobile;
                    }
                }
                if ($field == 'phone_mobile') {
                    if (!empty($this->phone_office)) {
                        $this->phone_mobile = $this->phone_office;
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
     * @param string $field field name
     */
    public function validateSizeLengow($field)
    {
        switch ($field) {
            case 'address1':
            case 'address2':
            case 'other':
                $address_full_array = explode(' ', $this->address_full);
                if (count($address_full_array) < 1) {
                    $definition = LengowAddress::getFieldDefinition();
                    $address1_maxlength = $definition['address1']['size'];
                    $address2_maxlength = $definition['address1']['size'];
                    $other_maxlength = $definition['other']['size'];
                    $this->address1 = '';
                    $this->address2 = '';
                    $this->other = '';
                    foreach ($address_full_array as $address_part) {
                        if (Tools::strlen($this->address1) < $address1_maxlength) {
                            if (!empty($this->address1)) {
                                $this->address1 .= ' ';
                            }
                            $this->address1 .= $address_part;
                            continue;
                        } elseif (Tools::strlen($this->address2) < $address2_maxlength) {
                            if (!empty($this->address2)) {
                                $this->address2 .= ' ';
                            }
                            $this->address2 .= $address_part;
                            continue;
                        } elseif (Tools::strlen($this->other) < $other_maxlength) {
                            if (!empty($this->other)) {
                                $this->other .= ' ';
                            }
                            $this->other .= $address_part;
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
