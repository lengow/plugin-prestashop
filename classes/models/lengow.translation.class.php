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


class LengowTranslation
{
    protected static $translation = null;
    protected static $fallbackTranslation = null;
    public $fallbackIsoCode = 'en';

    public function __construct()
    {

    }

    public function t($message, $args)
    {
        if (self::$translation === null) {
            $this->loadFile();
        }
        if (isset(self::$translation[$message])) {

            if ($args) {
                return self::$translation[$message];
            } else {
                return self::$translation[$message];
            }
        } else {
            if (self::$fallbackTranslation === null) {
                $this->loadFile(true);
            }
            if (isset(self::$fallbackTranslation[$message])) {
                return self::$fallbackTranslation[$message];
            } else {
                return $message;
            }
        }
    }

    public function loadFile($fallback = false)
    {
        $isoCode = $fallback ? $this->fallbackIsoCode : Context::getContext()->language->iso_code;
        $filename = _PS_MODULE_DIR_.'lengow'.DIRECTORY_SEPARATOR.'translations'.
            DIRECTORY_SEPARATOR.$isoCode.'.csv';

        $translation = array();
        if (file_exists($filename)) {
            if (($handle = fopen($filename, "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, "|")) !== false) {
                    $translation[$data[0]] = $data[1];
                }
                fclose($handle);
            }
        }

        if ($fallback) {
            self::$fallbackTranslation = $translation;
        } else {
            self::$translation = $translation;
        }
    }
}
