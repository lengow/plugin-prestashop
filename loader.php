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

define('_PS_MODULE_LENGOW_DIR_', _PS_MODULE_DIR_ . 'lengow' . DIRECTORY_SEPARATOR);
$sep = DIRECTORY_SEPARATOR;

$notInPresta14 = array('LengowGender.php');

if (_PS_VERSION_ < '1.5') {
    require_once _PS_MODULE_LENGOW_DIR_ . 'backward_compatibility' . $sep . 'backward.php';
}

if (_PS_VERSION_ < '1.5') {
    $directory = _PS_MODULE_LENGOW_DIR_ . 'classes/models/';
    $listClassFile = array_diff(scandir($directory), array('..', '.'));
    foreach ($listClassFile as $list) {
        if (in_array($list, $notInPresta14) && _PS_VERSION_ < '1.5') {
            continue;
        }
        require_once $directory . $list;
    }
    $directory = _PS_MODULE_LENGOW_DIR_ . 'classes/controllers/';
    $listClassFile = array_diff(scandir($directory), array('..', '.'));
    foreach ($listClassFile as $list) {
        require_once $directory . $list;
    }
} else {
    spl_autoload_register('lengowAutoloader');
}

function lengowAutoloader($className)
{
    if (Tools::substr($className, 0, 6) === 'Lengow') {
        if (Tools::substr($className, -10) === 'Controller') {
            $directory = _PS_MODULE_LENGOW_DIR_ . 'classes/controllers/';
            include $directory . $className . '.php';
        } else {
            $directory = _PS_MODULE_LENGOW_DIR_ . 'classes/models/';
            include $directory . $className . '.php';
        }
    }
}
