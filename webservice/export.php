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

@set_time_limit(0);
@ini_set('memory_limit', '512M');

$currentDirectory = str_replace('modules/lengow/webservice/', '', dirname($_SERVER['SCRIPT_FILENAME']) . "/");

$sep = DIRECTORY_SEPARATOR;
require_once $currentDirectory . 'config' . $sep . 'config.inc.php';
require_once $currentDirectory . 'init.php';
require_once $currentDirectory . 'modules/lengow/lengow.php';
require_once $currentDirectory . 'modules/lengow/loader.php';

$lengow = new Lengow();

// check if Lengow is installed and enabled
if (!Module::isInstalled($lengow->name)) {
    if (_PS_VERSION_ >= 1.5 && !Module::isEnabled($lengow->name)) {
        die('Lengow module is not active');
    }
    die('Lengow module is not installed');
}

// CheckIP
$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
if (!LengowCore::checkExportAccess(Context::getContext()->shop->id, $token)) {
    if (strlen($token) > 0) {
        die('Unauthorized access for this token : ' . $token);
    } else {
        die('Unauthorized access for IP : ' . $_SERVER['REMOTE_ADDR']);
    }
}

// Set import parameters
// export format (csv, yaml, xml, json)
$format = Configuration::get('LENGOW_EXPORT_FORMAT');
if (Tools::getIsset('format')) {
    $format = Tools::getValue('format');
}

// $fullMode -> including variations or not
$fullMode = Configuration::get('LENGOW_EXPORT_ALL_VARIATIONS');
if (Tools::getIsset('mode')) {
    if (Tools::getValue('mode') == 'simple') {
        $fullMode = false;
    } elseif (Tools::getValue('mode') == 'full') {
        $fullMode = true;
    }
}

// export product features
$export_feature = Configuration::get('LENGOW_EXPORT_FEATURES');
if (Tools::getIsset('feature')) {
    $export_feature = (bool)Tools::getValue('feature');
}

// export in file or no
$stream = !Configuration::get('LENGOW_EXPORT_FILE');
if (Tools::getIsset('stream')) {
    $stream = (bool)Tools::getValue('stream');
}

// export all products or only selected
$all = Configuration::get('LENGOW_EXPORT_SELECTION');
if (Tools::getIsset('all')) {
    $all = Tools::getValue('all');
}

// shop
if (Tools::getIsset('shop')) {
    if ($shop = new Shop((int)Tools::getValue('shop'))) {
        Context::getContext()->shop = $shop;
    }
}

// currency
if (Tools::getIsset('cur')) {
    if ($id_currency = Currency::getIdByIsoCode((int)Tools::getValue('cur'))) {
        Context::getContext()->currency = new Currency($id_currency);
    }
}

// export language
if (Tools::getIsset('lang')) {
    if ($id_language = Language::getIdByIso(Tools::getValue('lang'))) {
        Context::getContext()->language = new Language($id_language);
    }
}

// fields and features in title
$title = Configuration::get('LENGOW_EXPORT_FULLNAME');
if (Tools::getIsset('title')) {
    if (Tools::getValue('title') == 'full') {
        $title = true;
    } elseif (Tools::getValue('title') && Tools::getValue('title') == 'simple') {
        $title = false;
    }
}

// export inactive products
$inactive_products = Configuration::get('LENGOW_EXPORT_DISABLED');
if (Tools::getValue('active') && Tools::getValue('active') == 'enabled') {
    $inactive_products = false;
} elseif (Tools::getValue('active') && Tools::getValue('active') == 'all') {
    $inactive_products = true;
}

// export out of stock products
$out_stock = Configuration::get('LENGOW_EXPORT_OUT_STOCK');
if (Tools::getIsset('out_stock')) {
    $out_stock = (bool)Tools::getValue('out_stock');
}

// export product features
$export_features = Configuration::get('LENGOW_EXPORT_FEATURES');
if (Tools::getIsset('features')) {
    $export_features = (bool)Tools::getValue('features');
}

// export certain products
$product_ids = array();
if (Tools::getIsset('ids')) {
    $ids = Tools::getValue('ids');
    if (Tools::strlen($ids) > 0) {
        $product_ids = explode(',', $ids);
    }
}

// export limit
$limit = null;
if (Tools::getIsset('limit') && Tools::getValue('limit') > 0) {
    $limit = (int)Tools::getValue('limit');
}

$export = new LengowExport(
    $format,
    $fullMode,
    $all,
    $stream,
    $title,
    $inactive_products,
    $export_features,
    $limit,
    $out_stock,
    $product_ids
);

$export->exec();
