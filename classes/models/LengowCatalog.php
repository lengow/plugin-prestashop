<?php
/**
 * Copyright 2021 Lengow SAS.
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
 * @copyright 2021 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
/**
 * Lengow Catalog Class
 */
class LengowCatalog
{
    /**
     * Check if the account has catalogs not linked to a cms
     *
     * @return bool
     */
    public static function hasCatalogNotLinked()
    {
        $lengowCatalogs = LengowConnector::queryApi(LengowConnector::GET, LengowConnector::API_CMS_CATALOG);
        if (!$lengowCatalogs) {
            return false;
        }
        foreach ($lengowCatalogs as $catalog) {
            if (!is_object($catalog) || $catalog->shop) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * Get all catalogs available in Lengow
     *
     * @return array
     */
    public static function getCatalogList()
    {
        $catalogList = [];
        $lengowCatalogs = LengowConnector::queryApi(LengowConnector::GET, LengowConnector::API_CMS_CATALOG);
        if (!$lengowCatalogs) {
            return $catalogList;
        }
        foreach ($lengowCatalogs as $catalog) {
            if (!is_object($catalog) || $catalog->shop) {
                continue;
            }
            $name = $catalog->name ?: LengowMain::decodeLogMessage(
                'lengow_log.connection.catalog',
                null,
                ['catalog_id' => $catalog->id]
            );
            $status = $catalog->is_active
                ? LengowMain::decodeLogMessage('lengow_log.connection.status_active')
                : LengowMain::decodeLogMessage('lengow_log.connection.status_draft');
            $label = LengowMain::decodeLogMessage(
                'lengow_log.connection.catalog_label',
                null,
                [
                    'catalog_id' => $catalog->id,
                    'catalog_name' => $name,
                    'nb_products' => $catalog->products ?: 0,
                    'catalog_status' => $status,
                ]
            );
            $catalogList[] = [
                'label' => $label,
                'value' => $catalog->id,
            ];
        }
        return $catalogList;
    }

    /**
     * Link all catalogs by API
     *
     * @param array $catalogsByShops all catalog ids organised by shops
     *
     * @return bool
     */
    public static function linkCatalogs(array $catalogsByShops)
    {
        $catalogsLinked = false;
        $hasCatalogToLink = false;
        if (empty($catalogsByShops)) {
            return $catalogsLinked;
        }
        $linkCatalogData = [
            'cms_token' => LengowMain::getToken(),
            'shops' => [],
        ];
        foreach ($catalogsByShops as $idShop => $catalogIds) {
            if (empty($catalogIds)) {
                continue;
            }
            $hasCatalogToLink = true;
            $shopToken = LengowMain::getToken($idShop);
            $linkCatalogData['shops'][] = [
                'shop_token' => $shopToken,
                'catalogs_id' => $catalogIds,
            ];
            LengowMain::log(
                LengowLog::CODE_CONNECTION,
                LengowMain::setLogMessage(
                    'log.connection.try_link_catalog',
                    [
                        'catalog_ids' => implode(', ', $catalogIds),
                        'shop_token' => $shopToken,
                        'shop_id' => $idShop,
                    ]
                )
            );
        }
        if ($hasCatalogToLink) {
            $result = LengowConnector::queryApi(
                LengowConnector::POST,
                LengowConnector::API_CMS_MAPPING,
                [],
                json_encode($linkCatalogData)
            );
            if (isset($result->cms_token)) {
                $catalogsLinked = true;
            }
        }
        return $catalogsLinked;
    }
}
