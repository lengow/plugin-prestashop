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
 * @category  Webservice
 * @package   Export
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

@set_time_limit(0);
@ini_set('memory_limit', '512M');

$currentDirectory = str_replace('modules/lengow/webservice/', '', dirname($_SERVER['SCRIPT_FILENAME'])."/");
$sep = DIRECTORY_SEPARATOR;
require_once $currentDirectory.'config'.$sep.'config.inc.php';
Configuration::set('PS_SHOP_ENABLE', true);
require_once $currentDirectory.'init.php';
require_once $currentDirectory.'modules/lengow/lengow.php';

$lengow = new Lengow();
// check if Lengow is installed and enabled
if (!Module::isInstalled($lengow->name)) {
    if (_PS_VERSION_ >= 1.5 && !Module::isEnabled($lengow->name)) {
        header('HTTP/1.1 400 Bad Request');
        die('Lengow module is not active');
    }
    header('HTTP/1.1 400 Bad Request');
    die('Lengow module is not installed');
}
// CheckIP
$token = Tools::getIsset('token') ? Tools::getValue('token') : '';
if (!LengowMain::checkWebservicesAccess($token, Context::getContext()->shop->id)) {
    if (Tools::strlen($token) > 0) {
        header('HTTP/1.1 403 Forbidden');
        die('Unauthorized access for this token : '.$token);
    } else {
        header('HTTP/1.1 403 Forbidden');
        die('Unauthorized access for IP : '.$_SERVER['REMOTE_ADDR']);
    }
}
// get params data
$getParams = Tools::getIsset('get_params') ? (bool)Tools::getValue('get_params') : false;
// get mode 
$mode = (Tools::getIsset('mode') && (Tools::getValue('mode') == 'size' || Tools::getValue('mode') == 'total'))
    ? Tools::getValue('mode')
    : null;
// export format (csv, yaml, xml, json)
$format = Tools::getIsset('format')
    ? Tools::getValue('format')
    : LengowConfiguration::getGlobalValue('LENGOW_EXPORT_FORMAT');
// export in file or not
$stream = Tools::getIsset('stream')
    ? (bool)Tools::getValue('stream')
    : !(bool)LengowConfiguration::getGlobalValue('LENGOW_EXPORT_FILE_ENABLED');
// export offset
$offset = Tools::getIsset('offset') ? (int)Tools::getValue('offset') : null;
// export limit
$limit = Tools::getIsset('limit') ? (int)Tools::getValue('limit') : null;
// export lengow selection
$selection = Tools::getIsset('all') ? !(bool)Tools::getValue('all') : null;
if (Tools::getIsset('selection') || !is_null($selection)) {
    $selection = !is_null($selection) ? $selection : (bool)Tools::getValue('selection');
} else {
    $selection = (bool)LengowConfiguration::get('LENGOW_EXPORT_SELECTION_ENABLED');
}
// export out of stock products
$outOfStock = Tools::getIsset('out_stock') ? (bool)Tools::getValue('out_stock') : null;
if (Tools::getIsset('out_of_stock') || !is_null($outOfStock)) {
    $outOfStock = !is_null($outOfStock) ? $outOfStock : (bool)Tools::getValue('out_of_stock');
} else {
    $outOfStock = (bool)LengowConfiguration::get('LENGOW_EXPORT_OUT_STOCK');
}
// export specific products
$productIds = array();
$ids = Tools::getIsset('ids') ? Tools::getValue('ids') : null;
if (Tools::getIsset('product_ids') || !is_null($ids)) {
    $ids = !is_null($ids) ? $ids : Tools::getValue('product_ids');
    if (Tools::strlen($ids) > 0) {
        $ids = str_replace(array(';','|',':'), ',', $ids);
        $ids = preg_replace('/[^0-9\,]/', '', $ids);
        $productIds = explode(',', $ids);
    }
}
// export product variation
$variation = null;
if (Tools::getIsset('mode')) {
    if (Tools::getValue('mode') == 'simple') {
        $variation = false;
    } elseif (Tools::getValue('mode') == 'full') {
        $variation = true;
    }
}
if (Tools::getIsset('variation') || !is_null($variation)) {
    $variation = !is_null($variation) ? $variation : (bool)Tools::getValue('variation');
} else {
    $variation = (bool)LengowConfiguration::get('LENGOW_EXPORT_VARIATION_ENABLED');
}
// export inactive products
$inactive = null;
if (Tools::getValue('active')) {
    if (Tools::getValue('active') == 'enabled') {
        $inactive = false;
    } elseif (Tools::getValue('active') == 'all') {
        $inactive = true;
    }
}
if (Tools::getIsset('inactive') || !is_null($inactive)) {
    $inactive = !is_null($inactive) ? $inactive : (bool)Tools::getValue('inactive');
} else {
    $inactive = false;
}
// shop
if (Tools::getIsset('shop')) {
    if ($shop = new Shop((int)Tools::getValue('shop'))) {
        Context::getContext()->shop = $shop;
    }
}
// currency
$currency = Tools::getIsset('cur') ? Tools::getValue('cur') : null;
if (Tools::getIsset('currency') || !is_null($currency)) {
    $currency = !is_null($currency) ? $currency : Tools::getValue('currency');
    $idCurrency = Currency::getIdByIsoCode($currency);
    if ($idCurrency != 0) {
        Context::getContext()->currency = new Currency($idCurrency);
    }
}
// define language
$language = Tools::getIsset('lang') ? Tools::getValue('lang') : null;
if (Tools::getIsset('language') || !is_null($language)) {
    $language = !is_null($language) ? $language : Tools::getValue('language');
    $languageId = (int)Language::getIdByIso($language);
    if ($languageId == 0) {
        $languageId = Context::getContext()->language->id;
    }
} else {
    $languageId = Context::getContext()->language->id;
}
// get legacy fields
$legacyFields = Tools::getIsset('legacy_fields') ? (bool)Tools::getValue('legacy_fields') : null;
// update export date
$updateExportDate = Tools::getIsset('update_export_date') ? (bool)Tools::getValue('update_export_date') : true;
// See logs or not
$logOutput = Tools::getIsset('log_output') ? (bool)Tools::getValue('log_output') : true;

$export = new LengowExport(
    array(
        'format'             => $format,
        'stream'             => $stream,
        'product_ids'        => $productIds,
        'limit'              => $limit,
        'offset'             => $offset,
        'out_of_stock'       => $outOfStock,
        'variation'          => $variation,
        'inactive'           => $inactive,
        'legacy_fields'      => $legacyFields,
        'selection'          => $selection,
        'language_id'        => $languageId,
        'update_export_date' => $updateExportDate,
        'log_output'         => $logOutput,
    )
);

if ($getParams) {
    echo $export->getExportParams();
} elseif ($mode == 'size') {
    echo $export->getTotalExportProduct();
} elseif ($mode == 'total') {
    echo $export->getTotalProduct();
} else {
    $export->exec();
}
