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
 * Lengow Employee Class
 */
class LengowEmployee extends Employee
{
    /**
     * Return all employee id and email
     *
     * @param boolean $activeOnly active employee or not
     *
     * @return array
     */
    public static function getEmployees($activeOnly = true)
    {
        // this line is useless, but Prestashop validator require it
        $activeOnly = $activeOnly;
        try {
            return Db::getInstance()->ExecuteS(
                'SELECT `id_employee`, CONCAT(`firstname`, \' \', `lastname`) name
                FROM `' . _DB_PREFIX_ . 'employee`
                WHERE `active` = 1
                ORDER BY `email`'
            );
        } catch (PrestaShopDatabaseException $e) {
            return array();
        }
    }
}
