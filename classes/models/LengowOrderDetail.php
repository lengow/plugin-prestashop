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
 * Lengow Order Detail Class
 */
class LengowOrderDetail extends OrderDetail
{
    /**
     * Get Order Lines
     *
     * @param integer $idOrder Prestashop order id
     * @param integer|string $idProduct Prestashop product id
     *
     * @return integer
     */
    public static function findByOrderIdProductId($idOrder, $idProduct)
    {
        $sql = 'SELECT id_order_detail FROM `' . _DB_PREFIX_ . 'order_detail`
            WHERE product_id = ' . (int)$idProduct . ' AND id_order = ' . $idOrder;
        $row = Db::getInstance()->getRow($sql);
        return (int)$row['id_order_detail'];
    }
}
