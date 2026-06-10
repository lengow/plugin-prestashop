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
$_GET['fc'] = 'module';
$_GET['module'] = 'lengow';
$_GET['controller'] = 'export';

$index = rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\') . '/index.php';
if (!is_file($index)) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Unable to dispatch Lengow front controller');
}

require $index;
