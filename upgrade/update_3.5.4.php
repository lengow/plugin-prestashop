<?php
/**
 * Copyright 2024 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * this file at
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
 * @copyright 2024 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!LengowInstall::isInstallationInProgress()) {
    exit;
}

// *********************************************************
//                    Create lengow_exported_fields Table
// *********************************************************

$tableExists = LengowInstall::checkTableExists('lengow_exported_fields');

if (!$tableExists) {
    $sql = 'CREATE TABLE ' . _DB_PREFIX_ . 'lengow_exported_fields (
        id INT(11) NOT NULL AUTO_INCREMENT,
        lengow_field VARCHAR(255) NOT NULL,
        prestashop_value VARCHAR(255) NOT NULL,
        default_key VARCHAR(255) NOT NULL, -- Nouvelle colonne ajoutée
        PRIMARY KEY (id)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    Db::getInstance()->execute($sql);

    // *********************************************************
    //                 Insert Default Values
    // *********************************************************

    $fields = [
        'id' => 'id',
        'sku' => 'sku',
        'sku_supplier' => 'sku_supplier',
        'ean' => 'ean',
        'upc' => 'upc',
        'isbn' => 'isbn',
        'name' => 'name',
        'quantity' => 'quantity',
        'minimal_quantity' => 'minimal_quantity',
        'availability' => 'availability',
        'is_virtual' => 'is_virtual',
        'condition' => 'condition',
        'category' => 'category',
        'status' => 'status',
        'url' => 'url',
        'url_rewrite' => 'url_rewrite',
        'price_excl_tax' => 'price_excl_tax',
        'price_incl_tax' => 'price_incl_tax',
        'price_before_discount_excl_tax' => 'price_before_discount_excl_tax',
        'price_before_discount_incl_tax' => 'price_before_discount_incl_tax',
        'price_wholesale' => 'price_wholesale',
        'discount_percent' => 'discount_percent',
        'discount_start_date' => 'discount_start_date',
        'discount_end_date' => 'discount_end_date',
        'ecotax' => 'ecotax',
        'shipping_cost' => 'shipping_cost',
        'shipping_delay' => 'shipping_delay',
        'currency' => 'currency',
        'image_url_1' => 'image_1',
        'image_url_2' => 'image_2',
        'image_url_3' => 'image_3',
        'image_url_4' => 'image_4',
        'image_url_5' => 'image_5',
        'image_url_6' => 'image_6',
        'image_url_7' => 'image_7',
        'image_url_8' => 'image_8',
        'image_url_9' => 'image_9',
        'image_url_10' => 'image_10',
        'type' => 'type',
        'parent_id' => 'parent_id',
        'variation' => 'variation',
        'language' => 'language',
        'description' => 'description',
        'description_html' => 'description_html',
        'description_short' => 'short_description',
        'description_short_html' => 'short_description_html',
        'tags' => 'tags',
        'meta_title' => 'meta_title',
        'meta_keyword' => 'meta_keywords',
        'meta_description' => 'meta_description',
        'manufacturer' => 'manufacturer',
        'supplier' => 'supplier',
        'weight' => 'weight',
        'weight_unit' => 'weight_unit',
    ];

    foreach ($fields as $lengowField => $prestashopValue) {
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'lengow_exported_fields (lengow_field, prestashop_value, default_key) 
            VALUES ("' . pSQL($lengowField) . '", "' . pSQL($prestashopValue) . '", "' . pSQL($lengowField) . '")';
        Db::getInstance()->execute($sql);
    }
}
