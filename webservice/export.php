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

/**
 * List params
 * string  mode               Number of products exported
 * string  format             Format of exported files ('csv','yaml','xml','json')
 * boolean stream             Stream file (1) or generate a file on server (0)
 * integer offset             Offset of total product
 * integer limit              Limit number of exported product
 * boolean selection          Export product selection (1) or all products (0)
 * boolean out_of_stock       Export out of stock product (1) Export only product in stock (0)
 * string  product_ids        List of product id separate with comma (1,2,3)
 * boolean variation          Export product Variation (1) Export parent product only (0)
 * boolean inactive           Export inactive product (1) or not (0)
 * integer shop               Export a specific shop
 * string  currency           Convert prices with a specific currency
 * string  language           Translate content with a specific language
 * boolean legacy_fields      Export feed with v2 fields (1) or v3 fields (0)
 * boolean log_output         See logs (1) or not (0)
 * boolean update_export_date Change last export date in data base (1) or not (0)
 * boolean get_params         See export parameters and authorized values in json format (1) or not (0)
 */

@set_time_limit(0);
@ini_set('memory_limit', '512M');

$currentDirectory = str_replace('modules/lengow/webservice/', '', dirname($_SERVER['SCRIPT_FILENAME']) . '/');
$sep = DIRECTORY_SEPARATOR;
require_once $currentDirectory . 'config' . $sep . 'config.inc.php';
Configuration::set('PS_SHOP_ENABLE', true);
require_once $currentDirectory . 'init.php';
require_once $currentDirectory . 'modules/lengow/lengow.php';

$lengow = new Lengow();
// check if Lengow is installed and enabled
if (!Module::isInstalled($lengow->name)) {
    $errorMessage = (!Module::isEnabled($lengow->name))
        ? 'Lengow module is not active'
        : 'Lengow module is not installed';
    header('HTTP/1.1 400 Bad Request');
    die($errorMessage);
}
// CheckIP
$token = Tools::getIsset(LengowExport::PARAM_TOKEN) ? Tools::getValue(LengowExport::PARAM_TOKEN) : '';
if (!LengowMain::checkWebservicesAccess($token, Context::getContext()->shop->id)) {
    if ((bool) LengowConfiguration::get(LengowConfiguration::AUTHORIZED_IP_ENABLED)) {
        $errorMessage = 'Unauthorized access for IP: ' . $_SERVER['REMOTE_ADDR'];
    } else {
        $errorMessage = $token !== ''
            ? 'Unauthorised access for this token: ' . $token
            : 'Unauthorised access: token parameter is empty';
    }
    header('HTTP/1.1 403 Forbidden');
    die($errorMessage);
}
// get params data
$getParams = Tools::getIsset(LengowExport::PARAM_GET_PARAMS)
    ? (bool) Tools::getValue(LengowExport::PARAM_GET_PARAMS)
    : false;
// get mode
$mode = (
    Tools::getIsset(LengowExport::PARAM_MODE)
    && (Tools::getValue(LengowExport::PARAM_MODE) === 'size' || Tools::getValue(LengowExport::PARAM_MODE) === 'total')
) ? Tools::getValue(LengowExport::PARAM_MODE) : null;
// export format (csv, yaml, xml, json)
$format = Tools::getIsset(LengowExport::PARAM_FORMAT)
    ? Tools::getValue(LengowExport::PARAM_FORMAT)
    : LengowConfiguration::getGlobalValue(LengowConfiguration::EXPORT_FORMAT);
// export in file or not
$stream = Tools::getIsset(LengowExport::PARAM_STREAM)
    ? (bool) Tools::getValue(LengowExport::PARAM_STREAM)
    : !(bool) LengowConfiguration::getGlobalValue(LengowConfiguration::EXPORT_FILE_ENABLED);
// export offset
$offset = Tools::getIsset(LengowExport::PARAM_OFFSET) ? (int) Tools::getValue(LengowExport::PARAM_OFFSET) : null;
// export limit
$limit = Tools::getIsset(LengowExport::PARAM_LIMIT) ? (int) Tools::getValue(LengowExport::PARAM_LIMIT) : null;
// export specific shop
if (Tools::getIsset(LengowExport::PARAM_SHOP)) {
    $shop = new Shop((int) Tools::getValue(LengowExport::PARAM_SHOP));
    if ($shop->id) {
        $shop::setContext(Shop::CONTEXT_SHOP, $shop->id);
        Context::getContext()->shop = $shop;
    }
}
$idShop = (int) Context::getContext()->shop->id;
// export lengow selection
$selection = Tools::getIsset(LengowExport::PARAM_LEGACY_SELECTION)
    ? !(bool) Tools::getValue(LengowExport::PARAM_LEGACY_SELECTION)
    : null;
if ($selection !== null || Tools::getIsset(LengowExport::PARAM_SELECTION)) {
    $selection = $selection !== null ? $selection : (bool) Tools::getValue(LengowExport::PARAM_SELECTION);
} else {
    $selection = (bool) LengowConfiguration::get(LengowConfiguration::SELECTION_ENABLED, null, null, $idShop);
}
// export out of stock products
$outOfStock = Tools::getIsset(LengowExport::PARAM_LEGACY_OUT_OF_STOCK)
    ? (bool) Tools::getValue(LengowExport::PARAM_LEGACY_OUT_OF_STOCK)
    : null;
if ($outOfStock !== null || Tools::getIsset(LengowExport::PARAM_OUT_OF_STOCK)) {
    $outOfStock = $outOfStock !== null ? $outOfStock : (bool) Tools::getValue(LengowExport::PARAM_OUT_OF_STOCK);
} else {
    $outOfStock = (bool) LengowConfiguration::get(LengowConfiguration::OUT_OF_STOCK_ENABLED, null, null, $idShop);
}
// export specific products
$productIds = [];
$ids = Tools::getIsset(LengowExport::PARAM_LEGACY_PRODUCT_IDS)
    ? Tools::getValue(LengowExport::PARAM_LEGACY_PRODUCT_IDS)
    : null;
if ($ids !== null || Tools::getIsset(LengowExport::PARAM_PRODUCT_IDS)) {
    $ids = $ids !== null ? $ids : Tools::getValue(LengowExport::PARAM_PRODUCT_IDS);
    if (Tools::strlen($ids) > 0) {
        $ids = str_replace([';', '|', ':'], ',', $ids);
        $ids = preg_replace('/[^0-9\,]/', '', $ids);
        $productIds = explode(',', $ids);
    }
}
// export product variation
$variation = null;
if (Tools::getIsset(LengowExport::PARAM_LEGACY_VARIATION)) {
    if (Tools::getValue(LengowExport::PARAM_LEGACY_VARIATION) === 'simple') {
        $variation = false;
    } elseif (Tools::getValue(LengowExport::PARAM_LEGACY_VARIATION) === 'full') {
        $variation = true;
    }
}
if ($variation !== null || Tools::getIsset(LengowExport::PARAM_VARIATION)) {
    $variation = $variation !== null ? $variation : (bool) Tools::getValue(LengowExport::PARAM_VARIATION);
} else {
    $variation = (bool) LengowConfiguration::get(LengowConfiguration::VARIATION_ENABLED, null, null, $idShop);
}
// export inactive products
$inactive = null;
if (Tools::getValue(LengowExport::PARAM_LEGACY_INACTIVE)) {
    if (Tools::getValue(LengowExport::PARAM_LEGACY_INACTIVE) === 'enabled') {
        $inactive = false;
    } elseif (Tools::getValue(LengowExport::PARAM_LEGACY_INACTIVE) === 'all') {
        $inactive = true;
    }
}
if ($inactive !== null || Tools::getIsset(LengowExport::PARAM_INACTIVE)) {
    $inactive = $inactive !== null ? $inactive : (bool) Tools::getValue(LengowExport::PARAM_INACTIVE);
} else {
    $inactive = (bool) LengowConfiguration::get(LengowConfiguration::INACTIVE_ENABLED, null, null, $idShop);
}
// convert price for a specific currency
$currency = Tools::getIsset(LengowExport::PARAM_LEGACY_CURRENCY)
    ? Tools::getValue(LengowExport::PARAM_LEGACY_CURRENCY)
    : null;
if ($currency !== null || Tools::getIsset(LengowExport::PARAM_CURRENCY)) {
    $currency = $currency !== null ? $currency : Tools::getValue(LengowExport::PARAM_CURRENCY);
    $idCurrency = (int) Currency::getIdByIsoCode($currency);
    if ($idCurrency !== 0) {
        Context::getContext()->currency = new Currency($idCurrency);
    }
}
// define language
$language = Tools::getIsset(LengowExport::PARAM_LEGACY_LANGUAGE)
    ? Tools::getValue(LengowExport::PARAM_LEGACY_LANGUAGE)
    : null;
if ($language !== null || Tools::getIsset(LengowExport::PARAM_LANGUAGE)) {
    $language = $language !== null ? $language : Tools::getValue(LengowExport::PARAM_LANGUAGE);
    $languageId = (int) Language::getIdByIso($language);
    if ($languageId === 0) {
        $languageId = Context::getContext()->language->id;
    }
} else {
    $languageId = Context::getContext()->language->id;
}
// get legacy fields
$legacyFields = Tools::getIsset(LengowExport::PARAM_LEGACY_FIELDS)
    ? (bool) Tools::getValue(LengowExport::PARAM_LEGACY_FIELDS)
    : null;
// update export date
$updateExportDate = Tools::getIsset(LengowExport::PARAM_UPDATE_EXPORT_DATE)
    ? (bool) Tools::getValue(LengowExport::PARAM_UPDATE_EXPORT_DATE)
    : true;
// See logs or not
$logOutput = Tools::getIsset(LengowExport::PARAM_LOG_OUTPUT)
    ? (bool) Tools::getValue(LengowExport::PARAM_LOG_OUTPUT)
    : true;

$export = new LengowExport(
    [
        LengowExport::PARAM_FORMAT => $format,
        LengowExport::PARAM_STREAM => $stream,
        LengowExport::PARAM_PRODUCT_IDS => $productIds,
        LengowExport::PARAM_LIMIT => $limit,
        LengowExport::PARAM_OFFSET => $offset,
        LengowExport::PARAM_OUT_OF_STOCK => $outOfStock,
        LengowExport::PARAM_VARIATION => $variation,
        LengowExport::PARAM_INACTIVE => $inactive,
        LengowExport::PARAM_LEGACY_FIELDS => $legacyFields,
        LengowExport::PARAM_SELECTION => $selection,
        LengowExport::PARAM_LANGUAGE_ID => $languageId,
        LengowExport::PARAM_UPDATE_EXPORT_DATE => $updateExportDate,
        LengowExport::PARAM_LOG_OUTPUT => $logOutput,
    ]
);

if ($getParams) {
    echo $export->getExportParams();
} elseif ($mode === 'size') {
    echo $export->getTotalExportProduct();
} elseif ($mode === 'total') {
    echo $export->getTotalProduct();
} else {
    $export->exec();
}
