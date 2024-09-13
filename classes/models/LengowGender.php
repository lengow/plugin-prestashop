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
 * Lengow Gender Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowGender extends Gender
{
    /**
     * @var array current alias of mister
     */
    public static $currentMale = [
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
    ];

    /**
     * @var array current alias of miss
     */
    public static $currentFemale = [
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
    ];

    /**
     * Get the real gender
     *
     * @param string $name the gender text
     *
     * @return string
     */
    public static function getGender($name)
    {
        if (empty($name)) {
            return '';
        }
        if (in_array($name, self::$currentMale, true)) {
            return '1';
        }
        if (in_array($name, self::$currentFemale, true)) {
            return '2';
        }
        $query = 'SELECT `id_gender` FROM `' . _DB_PREFIX_ . 'gender_lang`
            WHERE `name` = \'' . pSQL($name) . '\' LIMIT 1;';
        if ($result = Db::getInstance()->getRow($query)) {
            if (!is_array($result)) {
                return '';
            }
            return (string) $result['id_gender'] ?? '';
        }

        return '';
    }
}
