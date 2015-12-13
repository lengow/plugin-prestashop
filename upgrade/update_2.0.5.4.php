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


$result = Db::getInstance()->execute("SHOW COLUMNS FROM "._DB_PREFIX_."lengow_logs_import LIKE 'mail' ");
$exists = count($result) > 0 ? true : false;
if (!$exists) {
    $sql = 'ALTER TABLE '._DB_PREFIX_.'lengow_logs_import ADD `mail` tinyint(1) UNSIGNED NOT NULL DEFAULT \'0\'';
    Db::getInstance()->execute($sql);
}
