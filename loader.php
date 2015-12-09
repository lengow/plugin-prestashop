<?php
/**
 * Copyright 2015 Lengow SAS.
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
 * @author    Mathieu Sabourin <mathieu.sabourin@lengow.com> Romain Le Polh <romain@lengow.com>
 * @copyright 2015 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

$GLOBALS['OVERRIDE_FOLDER'] = 'override';
$GLOBALS['INSTALL_FOLDER'] = 'install';
$GLOBALS['MODELS_FOLDER'] = 'models';
$GLOBALS['FILES'] = array();

/**
 * Searches file and feeds array with its location
 * @param string $file_name
 * @return bool file found
 *
 */
function fetch($file_name)
{
    $sep = DIRECTORY_SEPARATOR;
    if (file_exists(_PS_MODULE_DIR_ . 'lengow' . $sep . $GLOBALS['OVERRIDE_FOLDER'] . $sep . 'lengow.' . $file_name . '.class.php')) {
        require_once $GLOBALS['OVERRIDE_FOLDER'] . $sep . 'lengow.' . $file_name . '.class.php';
        return true;
    } elseif (file_exists(_PS_MODULE_DIR_ . 'lengow/' . $GLOBALS['INSTALL_FOLDER'] . $sep . 'lengow.' . $file_name . '.class.php')) {
        require_once $GLOBALS['INSTALL_FOLDER'] . $sep . 'lengow.' . $file_name . '.class.php';
        return true;
    }

    $file = explode('_', $file_name);
    if (file_exists(_PS_MODULE_DIR_ . 'lengow/' . $GLOBALS['MODELS_FOLDER'] . $sep . 'lengow.' . $file[0] . '.class.php')) {
        $file = explode('_', $file_name);
        require_once $GLOBALS['MODELS_FOLDER'] . $sep . 'lengow.' . $file[0] . '.class.php';
        return true;
    }
    return false;
}

/**
 * Loads file and includes it
 * @param string $file_name
 * @return none
 */
function loadFile($file_name)
{
    if (!array_key_exists($file_name, $GLOBALS['FILES'])) {
        if (!fetch($file_name)) {
            throw new Exception('Missing file : ' . $file_name);
        }
    }
    //require_once $GLOBALS['FILES'][$file_name];
}

