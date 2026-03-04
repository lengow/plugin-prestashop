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
 * Lengow Customer Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowCustomer extends Customer
{
    public const LENGOW_GROUP_NAME = 'Lengow - Marketplaces';

    /**
     * @var string customer full name
     */
    public string $fullName;

    /**
     * Get definition array
     *
     * @return array<int|string, mixed>
     */
    public static function getFieldDefinition(): array
    {
        return self::$definition['fields'];
    }

    /**
     * Assign API data
     *
     * @param array<string, mixed> $data API data
     *
     * @return LengowCustomer
     */
    public function assign(array $data = []): LengowCustomer
    {
        $this->company = LengowAddress::cleanName((string) $data['company']);
        $this->email = $data['email'];
        $this->firstname = $data['first_name'];
        $this->lastname = $data['last_name'];
        $this->fullName = $data['full_name'];
        $this->passwd = md5((string) rand());
        $this->id_gender = (int) LengowGender::getGender((string) $data['civility']);
        $this->id_default_group = LengowConfiguration::get(LengowConfiguration::ORDER_CUSTOMER_GROUP);

        return $this;
    }

    /**
     * Validate Lengow
     *
     * @return bool
     *
     * @throws Exception|LengowException invalid object
     */
    public function validateLengow(): bool
    {
        $definition = self::getFieldDefinition();
        foreach ($definition as $fieldName => $constraints) {
            if (isset($constraints['required']) && $constraints['required'] && !$this->{$fieldName}) {
                $this->validateFieldLengow($fieldName, LengowAddress::LENGOW_EMPTY_ERROR);
            }
            if (isset($constraints['size']) && Tools::strlen($this->{$fieldName}) > $constraints['size']) {
                $this->validateFieldLengow($fieldName, LengowAddress::LENGOW_SIZE_ERROR);
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
     * @param int $errorType type of error
     *
     * @return void
     */
    public function validateFieldLengow(string $fieldName, int $errorType): void
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
     *
     * @return void
     */
    public function validateEmptyLengow(string $fieldName): void
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
            default:
                break;
        }
    }

    /**
     * Modify a field to fit its size
     *
     * @param string $fieldName field name
     *
     * @return void
     */
    public function validateSizeLengow(string $fieldName): void
    {
        // Customer fields are validated by their own definition.
        // Address-specific fields (address1, address2, other, phone, phone_mobile)
        // are handled by LengowAddress::validateSizeLengow() instead.
        $definition = self::getFieldDefinition();
        if (isset($definition[$fieldName]['size'])) {
            $this->{$fieldName} = Tools::substr($this->{$fieldName}, 0, $definition[$fieldName]['size']);
        }
    }

    /**
     * Retrieve customers by email address and id shop
     *
     * @param string $email customer email
     * @param int $idShop PrestaShop shop id
     *
     * @return LengowCustomer|false
     */
    public function getByEmailAndShop(string $email, int $idShop): LengowCustomer|false
    {
        $sql = 'SELECT *
            FROM `' . _DB_PREFIX_ . 'customer`
            WHERE `email` = \'' . pSQL($email) . '\'
             AND `id_shop` = \'' . $idShop . '\'' . '
            AND `deleted` = \'0\'';
        $result = Db::getInstance()->getRow($sql);
        if (!$result) {
            return false;
        }
        $this->id = $result['id_customer'];
        foreach ($result as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }
}
