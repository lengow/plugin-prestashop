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

/**
 * list params
 * string format        : Format of exported files ('csv','yaml','xml','json',
 * boolean stream       : Stream file (1) or generate a file on server (0)
 * string product_ids   : List of product id separate with comma (1,2,3)
 * string title         : Export combination title with parent title (full)
 * int limit            : Limit number of exported product
 * int offset           : Offset of total product
 * boolean out_stock    : Export out of stock product (1) Export only product in stock (0)
 * boolean variation    : Export product Variation (1) Export parent product only (0)
 */

@set_time_limit(0);
@ini_set('memory_limit', '512M');

$currentDirectory = str_replace('modules/lengow/webservice/', '', dirname($_SERVER['SCRIPT_FILENAME']) . "/");

$sep = DIRECTORY_SEPARATOR;
require_once $currentDirectory . 'config' . $sep . 'config.inc.php';
require_once $currentDirectory . 'init.php';
require_once $currentDirectory . 'modules/lengow/lengow.php';

$lengow = new Lengow();

// check if Lengow is installed and enabled
if (!Module::isInstalled($lengow->name)) {
    if (_PS_VERSION_ >= 1.5 && !Module::isEnabled($lengow->name)) {
        die('Lengow module is not active');
    }
    die('Lengow module is not installed');
}

// CheckIP
$token = Tools::getIsset('token') ? Tools::getValue('token') : '';
if (!LengowMain::checkWebservicesAccess($token, Context::getContext()->shop->id)) {
    if (Tools::strlen($token) > 0) {
        die('Unauthorized access for this token : ' . $token);
    } else {
        die('Unauthorized access for IP : ' . $_SERVER['REMOTE_ADDR']);
    }
}

// Set import parameters
//backward compatibility
if (isset($_REQUEST["all"])) {
    $_REQUEST["selection"] = !(bool)$_REQUEST["all"];
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

// export format (csv, yaml, xml, json)
$format = isset($_REQUEST["format"]) ? $_REQUEST["format"] : Configuration::get('LENGOW_EXPORT_FORMAT');

//define language
if (isset($_REQUEST["lang"])) {
    $languageId = Language::getIdByIso($_REQUEST["lang"]);
} else {
    $languageId = Context::getContext()->language->id;
}

// export limit
$limit = isset($_REQUEST["limit"]) ? (int)$_REQUEST["limit"] : null;

// export offset
$offset = isset($_REQUEST["offset"]) ? (int)$_REQUEST["offset"] : null;

// export lengow selection
$selection = isset($_REQUEST["selection"]) ? (bool)$_REQUEST["selection"] :
    Configuration::get('LENGOW_EXPORT_SELECTION_ENABLED');

// export in file or no
$stream = isset($_REQUEST["stream"]) ? (bool)$_REQUEST["stream"] : (bool)Configuration::get('LENGOW_EXPORT_FILE_ENABLED');

// export out of stock products
$out_stock = isset($_REQUEST["out_stock"]) ? (bool)$_REQUEST["out_stock"] :
    (bool)Configuration::get('LENGOW_EXPORT_OUT_STOCK');

// export product variation
$exportVariation = isset($_REQUEST["variation"]) ? (bool)$_REQUEST["variation"] :
    (bool)Configuration::get('LENGOW_EXPORT_VARIATION_ENABLED');

// export certain products
$product_ids = array();
$ids = isset($_REQUEST["product_ids"]) ? $_REQUEST["product_ids"] : null;
if (Tools::strlen($ids) > 0) {
    $ids = str_replace(array(';','|',':'), ',', $ids);
    $ids = preg_replace('/[^0-9\,]/', '', $ids);
    $product_ids = explode(',', $ids);
}

$export = new LengowExport(array(
    'format' => $format,
    'stream' => $stream,
    'product_ids' => $product_ids,
    'limit' => $limit,
    'offset' => $offset,
    'out_stock' => $out_stock,
    'export_variation' => $exportVariation,
    'selection' => $selection,
    'language_id' => $languageId,
));

$export->exec();
