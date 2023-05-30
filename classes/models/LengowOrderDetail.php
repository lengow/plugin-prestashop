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
 * Lengow Order Detail Class
 */
class LengowOrderDetail extends OrderDetail
{
    /**
     * Get Order Lines
     *
     * @param integer $idOrder PrestaShop order id
     * @param integer|string $idProduct PrestaShop product id
     *
     * @return integer
     */
    public static function findByOrderIdProductId($idOrder, $idProduct)
    {
        $whereArr = [
            '`id_order`=' . (int)$idOrder,
            '`product_id`=' . (int)$idProduct
        ];

        $ids = explode('_', (string)$idProduct);

        if (isset($ids[1])) {
            $productAttributeId = (int)$ids[1];
            $whereArr[] = '`product_attribute_id`=' . $productAttributeId;
        }

        $sql = 'SELECT `id_order_detail` FROM `' . _DB_PREFIX_ . 'order_detail` WHERE ' . implode(' AND ', $whereArr);

        return (int)Db::getInstance()->getValue($sql);
    }
}
