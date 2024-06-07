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
 * Lengow Translation Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowTranslation
{
    /* Plugin translation iso codes */
    public const ISO_CODE_EN = 'en';
    public const ISO_CODE_FR = 'fr';
    public const ISO_CODE_ES = 'es';
    public const ISO_CODE_IT = 'it';

    /**
     * @var string default iso code
     */
    public const DEFAULT_ISO_CODE = self::ISO_CODE_EN;

    /**
     * @var array|null all translations
     */
    protected static $translation;

    /**
     * @var string|null iso code
     */
    protected $isoCode;

    /**
     * @var string|null force iso code for log and toolbox
     */
    public static $forceIsoCode;

    /**
     * Construct
     *
     * @param string $isoCode
     */
    public function __construct($isoCode = '')
    {
        $this->isoCode = !empty($isoCode) ? $isoCode : $this->getCurrentIsoCode();
    }

    /**
     * Translate message
     *
     * @param string $message localization key
     * @param array $args arguments to replace word in string
     * @param string|null $isoCode translation iso code
     *
     * @return string
     */
    public function t($message, $args = [], $isoCode = null)
    {
        if (self::$forceIsoCode !== null) {
            $isoCode = self::$forceIsoCode;
        }
        if ($isoCode === null) {
            $isoCode = $this->isoCode;
        }
        if (!isset(self::$translation[$isoCode])) {
            $this->loadFile($isoCode);
        }
        if (isset(self::$translation[$isoCode][$message])) {
            return $this->translateFinal(self::$translation[$isoCode][$message], $args);
        }
        if (!isset(self::$translation[self::DEFAULT_ISO_CODE])) {
            $this->loadFile(self::DEFAULT_ISO_CODE);
        }
        if (isset(self::$translation[self::DEFAULT_ISO_CODE][$message])) {
            return $this->translateFinal(self::$translation[self::DEFAULT_ISO_CODE][$message], $args);
        }

        return 'Missing Translation [' . $message . ']';
    }

    /**
     * Translate string
     *
     * @param string $text localization key
     * @param array $args arguments to replace word in string
     *
     * @return string
     */
    protected function translateFinal($text, $args)
    {
        if ($args) {
            $params = [];
            $values = [];
            foreach ($args as $key => $value) {
                $params[] = '%{' . $key . '}';
                $values[] = $value;
            }

            return str_replace($params, $values, $text);
        }

        return $text;
    }

    /**
     * Load csv file
     *
     * @param string $isoCode translation iso code
     * @param string|null $filename file location
     *
     * @return bool
     */
    public function loadFile($isoCode, $filename = null)
    {
        if (!$filename) {
            $sep = DIRECTORY_SEPARATOR;
            $filename = LengowMain::getLengowFolder()
                . $sep . LengowMain::FOLDER_TRANSLATION . $sep . $isoCode . '.csv';
        }
        $translation = [];
        if (file_exists($filename)) {
            if (($handle = fopen($filename, 'rb')) !== false) {
                while (($data = fgetcsv($handle, 1000, '|')) !== false) {
                    if (isset($data[1])) {
                        $translation[$data[0]] = $data[1];
                    }
                }
                fclose($handle);
            }
        }
        self::$translation[$isoCode] = $translation;

        return !empty($translation);
    }

    /**
     * Returns current language is code
     *
     * @return string
     */
    protected function getCurrentIsoCode()
    {
        return Context::getContext()->language->iso_code;
    }
}
