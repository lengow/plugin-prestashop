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
if (!defined('_PS_VERSION_')) {
    $_GET['fc'] = 'module';
    $_GET['module'] = 'lengow';
    $_GET['controller'] = 'export';

    // This file lives in modules/lengow/webservice/, so the shop root is three levels up and
    // is the only path we actually know. DOCUMENT_ROOT is a fallback: on a host serving several
    // webroots it can point at a different application that also has an index.php, and booting
    // that one is worse than not booting at all.
    $index = dirname(__DIR__, 3) . '/index.php';
    if (!is_file($index)) {
        $documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
        // only when it is set: an empty document root would probe /index.php at the filesystem root
        $index = $documentRoot === '' ? $index : $documentRoot . '/index.php';
    }
    if (!is_file($index)) {
        header('HTTP/1.1 500 Internal Server Error');
        exit('Unable to dispatch Lengow front controller');
    }

    require $index;
}
