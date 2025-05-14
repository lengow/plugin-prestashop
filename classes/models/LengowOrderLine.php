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
/*
 * Lengow Order Line Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowOrderLine
{
    /**
     * @var string Lengow order line table name
     */
    public const TABLE_ORDER_LINE = 'lengow_order_line';

    /* Order line fields */
    public const FIELD_ID = 'id';
    public const FIELD_ORDER_ID = 'id_order';
    public const FIELD_ORDER_LINE_ID = 'id_order_line';
    public const FIELD_ORDER_DETAIL_ID = 'id_order_detail';

    /**
     * Get Order Lines by PrestaShop order id
     *
     * @param int $idOrder PrestaShop order id
     *
     * @return array
     */
    public static function findOrderLineIds($idOrder)
    {
        $sql = 'SELECT id_order_line FROM `' . _DB_PREFIX_ . 'lengow_order_line`
            WHERE id_order = ' . (int) $idOrder;
        try {
            return Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            return [];
        }
    }

    /**
     * Get Order Line by PrestaShop order detail id
     *
     * @param int $idOrderDetail PrestaShop order detail id
     *
     * @return array
     */
    public static function findOrderLineByOrderDetailId(int $idOrderDetail): array
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'lengow_order_line`
            WHERE id_order_detail = ' . (int) $idOrderDetail;
        try {
            return Db::getInstance()->getRow($sql);
        } catch (PrestaShopDatabaseException $e) {
            return [];
        }
    }

    /**
     * Get Order Line by Lengow order line id
     *
     * @param string $idOrderLine Lengow order line id
     *
     * @return array
     */
    public static function findOrderLineByOrderLineIdAndOrderId(string $idOrderLine, int $idOrder): array
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'lengow_order_line`
        WHERE id_order_line = "' . pSQL($idOrderLine) . '"
        AND id_order = ' . (int) $idOrder;

        try {
            $result = Db::getInstance()->getRow($sql);

            return $result ?: [];
        } catch (PrestaShopDatabaseException $e) {
            LengowMain::log(LengowLog::CODE_ACTION, 'Database error: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Set refunded status Order Line by PrestaShop order detail id to true and add quantity refunded
     *
     * @param int $idOrderDetail PrestaShop order detail id
     * @param string $idOrderLine Lengow order line id
     * @param int $cancelQuantity Quantity refunded
     *
     * @return bool
     */
    public static function setRefunded(int $idOrderDetail, $idOrderLine, int $cancelQuantity)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . self::TABLE_ORDER_LINE . '`
            SET refunded = 1, quantity_refunded = ' . (int) $cancelQuantity . '
            WHERE id_order_detail = ' . (int) $idOrderDetail . '
            AND id_order_line = "' . pSQL($idOrderLine) . '"';
        try {
            return Db::getInstance()->execute($sql);
        } catch (PrestaShopDatabaseException $e) {
            LengowMain::log(LengowLog::CODE_ACTION, 'Error updating refunded field: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get the quantity refunded for a specific order line
     *
     * @param string $idOrderLine Lengow order line id
     *
     * @return int|null
     */
    public static function getQuantityRefunded($idOrderLine)
    {
        $sql = 'SELECT quantity_refunded FROM `' . _DB_PREFIX_ . self::TABLE_ORDER_LINE . '`
            WHERE id_order_line = "' . pSQL($idOrderLine) . '"';
        try {
            $result = Db::getInstance()->getValue($sql);

            return $result !== false ? (int) $result : null;
        } catch (PrestaShopDatabaseException $e) {
            LengowMain::log(LengowLog::CODE_ACTION, 'Error fetching quantity_refunded: ' . $e->getMessage());

            return null;
        }
    }
}
