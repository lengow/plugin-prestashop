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

if (!$installation) {
    exit();
}

Configuration::deleteByName('LENGOW_ID_CUSTOMER');
Configuration::deleteByName('LENGOW_ID_GROUP');
Configuration::deleteByName('LENGOW_TOKEN');
Configuration::deleteByName('LENGOW_SWITCH_V3');
Configuration::deleteByName('LENGOW_EXPORT_FIELDS');
Configuration::deleteByName('LENGOW_EXPORT_FEATURES');
Configuration::deleteByName('LENGOW_IMAGE_TYPE');

// alter log import table
if (Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'lengow_product\'')) {
    if (!$this->_checkFieldExists('lengow_product', 'id')) {
        Db::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'lengow_product DROP PRIMARY KEY');
        Db::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'lengow_product ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }
}
