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
 * The Lengow Gender Class.
 *
 */
class LengowGender extends Gender
{

    /**
     * Current alias of mister.
     */
    public static $CURRENT_MALE = array(
        'M',
        'M.',
        'Mr',
        'Mr.',
        'Mister',
        'Monsieur',
        'monsieur',
        'mister',
        'm.',
        'mr ',
    );

    /**
     * Current alias of miss.
     */
    public static $CURRENT_FEMALE = array(
        'Mme',
        'mme',
        'Mm',
        'mm',
        'Mlle',
        'mlle',
        'Madame',
        'madame',
        'Mademoiselle',
        'madamoiselle',
        'Mrs',
        'mrs',
        'Mrs.',
        'mrs.',
        'Miss',
        'miss',
        'Ms',
        'ms',
    );

    /**
     * Get the real gender
     *
     * @param $name The gender text
     *
     * @return id_gender
     */
    public static function getGender($name)
    {
        if (empty($name)) {
            return '';
        }
        if (in_array($name, self::$CURRENT_MALE)) {
            return 1;
        } elseif (in_array($name, self::$CURRENT_FEMALE)) {
            return 2;
        } else {
            $query = 'SELECT `id_gender` FROM `'._DB_PREFIX_.'gender_lang` WHERE `name` = \''.
                pSQL($name).'\' LIMIT 1;';
            if ($result = Db::getInstance()->Execute($query)) {
                return $result['id_gender'];
            }
            return '';
        }
    }
}
