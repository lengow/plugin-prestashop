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
 * Lengow Customer class
 *
 */
class LengowCustomer extends Customer
{
    /**
     * Definition array for prestashop 1.4
     *
     * @var array
     */
    public static $definition_lengow = array(
        'lastname'  => array('required' => true, 'size' => 32),
        'firstname' => array('required' => true, 'size' => 32),
        'email'     => array('required' => true, 'size' => 128),
        'passwd'    => array('required' => true, 'size' => 32),
    );

    /**
     * Get definition array
     *
     * @return array
     */
    public static function getFieldDefinition()
    {
        if (_PS_VERSION_ < 1.5) {
            return LengowCustomer::$definition_lengow;
        }
        return LengowCustomer::$definition['fields'];
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
        $this->passwd = md5(rand());
        if (_PS_VERSION_ >= '1.5') {
            $this->id_gender = LengowGender::getGender((string)$data['civility']);
        }
        return $this;
    }

    /**
     * Validate Lengow
     *
     * @return bool true if object is valid
     */
    public function validateLengow()
    {
        $definition = LengowCustomer::getFieldDefinition();
        foreach ($definition as $field_name => $constraints) {
            if (isset($constraints['required']) && $constraints['required']) {
                if (!$this->{$field_name}) {
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
     * @param string $field      incorrect field
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
            case '':
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
                    $definition = LengowCustomer::getFieldDefinition();
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

    /**
     * Retrieve customers by email address and id shop
     *
     * @param string  $email
     * @param integer $id_shop
     *
     * @return array
     */
    public function getByEmailAndShop($email, $id_shop)
    {
        $sql = 'SELECT *
            FROM `'._DB_PREFIX_.'customer`
            WHERE `email` = \''.pSQL($email).'\'
            '.(_PS_VERSION_ < 1.5 ? '' : ' AND `id_shop` = \''.$id_shop.'\'').'
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
