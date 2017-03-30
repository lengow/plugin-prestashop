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
 * Lengow Translation Class
 */
class LengowTranslation
{
    /**
     * @var array all translations
     */
    protected static $translation = null;

    /**
     * @var string fallback iso code
     */
    public $fallbackIsoCode = 'en';

    /**
     * @var string iso code
     */
    protected $isoCode = null;

    /**
     * @var string force iso code for log and toolbox
     */
    public static $forceIsoCode = null;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->isoCode = Context::getContext()->language->iso_code;
    }

    /**
     * Translate message
     *
     * @param string $message localization key
     * @param array  $args    argurments to replace word in string
     * @param array  $isoCode translation iso code
     *
     * @return string
     */
    public function t($message, $args = array(), $isoCode = null)
    {
        if (!is_null(LengowTranslation::$forceIsoCode)) {
            $isoCode = LengowTranslation::$forceIsoCode;
        }
        if (is_null($isoCode)) {
            $isoCode = $this->isoCode;
        }
        if (!isset(self::$translation[$isoCode])) {
            $this->loadFile($isoCode);
        }
        if (isset(self::$translation[$isoCode][$message])) {
            return $this->translateFinal(self::$translation[$isoCode][$message], $args);
        } else {
            if (!isset(self::$translation[$this->fallbackIsoCode])) {
                $this->loadFile($this->fallbackIsoCode);
            }
            if (isset(self::$translation[$this->fallbackIsoCode][$message])) {
                return $this->translateFinal(self::$translation[$this->fallbackIsoCode][$message], $args);
            } else {
                return 'Missing Translation ['.$message.']';
            }
        }
    }

    /**
     * Translate string
     *
     * @param string $text localization key
     * @param array  $args argurments to replace word in string
     *
     * @return string
     */
    protected function translateFinal($text, $args)
    {
        if ($args) {
            $params = array();
            $values = array();
            foreach ($args as $key => $value) {
                $params[] = '%{'.$key.'}';
                $values[] = $value;
            }
            return str_replace($params, $values, $text);
        } else {
            return $text;
        }
    }

    /**
     * Load csv file
     *
     * @param string $isoCode  translation iso code
     * @param string $filename file location
     *
     * @return boolean
     */
    public function loadFile($isoCode, $filename = null)
    {
        if (!$filename) {
            $filename = _PS_MODULE_DIR_.'lengow'.DIRECTORY_SEPARATOR.'translations'.
                DIRECTORY_SEPARATOR.$isoCode.'.csv';
        }
        $translation = array();
        if (file_exists($filename)) {
            if (($handle = fopen($filename, "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, "|")) !== false) {
                    $translation[$data[0]] = $data[1];
                }
                fclose($handle);
            }
        }
        self::$translation[$isoCode] = $translation;
        return count($translation) > 0;
    }
}
