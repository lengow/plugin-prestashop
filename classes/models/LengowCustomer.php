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
 * Lengow Customer Class
 */
class LengowCustomer extends Customer
{
    /**
     * @var array definition array for prestashop 1.4
     */
    public static $definitionLengow = array(
        'lastname' => array('required' => true, 'size' => 32),
        'firstname' => array('required' => true, 'size' => 32),
        'email' => array('required' => true, 'size' => 128),
        'passwd' => array('required' => true, 'size' => 32),
    );

    /**
     * @var string customer full name
     */
    public $fullName;

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
     * @param array $data API data
     *
     * @return LengowCustomer
     */
    public function assign($data = array())
    {
        $this->company = LengowAddress::cleanName((string)$data['company']);
        $this->email = $data['email'];
        $this->firstname = $data['first_name'];
        $this->lastname = $data['last_name'];
        $this->fullName = $data['full_name'];
        $this->passwd = md5(rand());
        if (_PS_VERSION_ >= '1.5') {
            $this->id_gender = LengowGender::getGender((string)$data['civility']);
        }
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
            if (isset($constraints['required']) && $constraints['required']) {
                if (!$this->{$fieldName}) {
                    $this->validateFieldLengow($fieldName, LengowAddress::LENGOW_EMPTY_ERROR);
                }
            }
            if (isset($constraints['size'])) {
                if (Tools::strlen($this->{$fieldName}) > $constraints['size']) {
                    $this->validateFieldLengow($fieldName, LengowAddress::LENGOW_SIZE_ERROR);
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
            case LengowAddress::LENGOW_EMPTY_ERROR:
                $this->validateEmptyLengow($fieldName);
                break;
            case LengowAddress::LENGOW_SIZE_ERROR:
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
                $names = LengowAddress::extractNames($this->{$fieldName});
                $this->firstname = $names['firstname'];
                $this->lastname = $names['lastname'];
                // check full name if last_name and first_name are empty
                if (empty($this->firstname) && empty($this->lastname)) {
                    $names = LengowAddress::extractNames($this->fullName);
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
            case '':
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
                $addressFullArray = explode(' ', $this->address_full);
                if (count($addressFullArray) < 1) {
                    $definition = self::getFieldDefinition();
                    $address1MaxLength = $definition['address1']['size'];
                    $address2MaxLength = $definition['address1']['size'];
                    $otherMaxLength = $definition['other']['size'];
                    $this->address1 = '';
                    $this->address2 = '';
                    $this->other = '';
                    foreach ($addressFullArray as $addressPart) {
                        if (Tools::strlen($this->address1) < $address1MaxLength) {
                            if (!empty($this->address1)) {
                                $this->address1 .= ' ';
                            }
                            $this->address1 .= $addressPart;
                            continue;
                        } elseif (Tools::strlen($this->address2) < $address2MaxLength) {
                            if (!empty($this->address2)) {
                                $this->address2 .= ' ';
                            }
                            $this->address2 .= $addressPart;
                            continue;
                        } elseif (Tools::strlen($this->other) < $otherMaxLength) {
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

    /**
     * Retrieve customers by email address and id shop
     *
     * @param string $email customer email
     * @param integer $idShop Prestashop shop id
     *
     * @return LengowCustomer|false
     */
    public function getByEmailAndShop($email, $idShop)
    {
        $sql = 'SELECT *
            FROM `' . _DB_PREFIX_ . 'customer`
            WHERE `email` = \'' . pSQL($email) . '\'
            ' . (_PS_VERSION_ < 1.5 ? '' : ' AND `id_shop` = \'' . $idShop . '\'') . '
            AND `deleted` = \'0\'';
        $result = Db::getInstance()->getRow($sql);
        if (!$result) {
            return false;
        }
        $this->id = $result['id_customer'];
        foreach ($result as $key => $value) {
            if (key_exists($key, $this)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }
}
