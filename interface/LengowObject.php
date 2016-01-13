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
 * Lengow Object Interface
 */
interface LengowObject
{
    const LENGOW_EMPTY_ERROR = 1;
    const LENGOW_SIZE_ERROR = 2;

    /**
     * Get definition array
     *
     * @return array
     */
    public static function getFieldDefinition();

    /**
     * Assign API data
     *
     * @param array $data API data
     * @return LengowAddress
     */
    public function assign($data = array());

    /**
     * Validate fields
     *
     * @return bool true if object is valid
     */
    public function validateLengow();

    /**
     * Modify a field according to the type of error
     *
     * @param string $error_type type of error
     * @param string $field incorrect field
     */
    public function validateFieldLengow($field, $error_type);

    /**
     * Modify an empty field
     *
     * @param string $field field name
     */
    public function validateEmptyLengow($field);

    /**
     * Modify a field to fit its size
     *
     * @param string $field field name
     */
    public function validateSizeLengow($field);
}
