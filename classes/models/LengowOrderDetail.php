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
 * Lengow Order Detail Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowOrderDetail extends OrderDetail
{
    /**
     * Get Order Lines
     *
     * @param int $idOrder PrestaShop order id
     * @param int|string $idProduct PrestaShop product id
     *
     * @return int
     */
    public static function findByOrderIdProductId(int $idOrder, $idProduct): int
    {
        $whereArr = [
            '`id_order`=' . (int) $idOrder,
            '`product_id`=' . (int) $idProduct,
        ];

        // Divide the product ID using the underscore character as a separator
        $ids = explode('_', (string) $idProduct);

        // If a second element exists in the $ids array, it is considered the ID of the product attribute
        if (isset($ids[1])) {
            $productAttributeId = (int) $ids[1];
            // Add condition for product attribute ID
            $whereArr[] = '`product_attribute_id`=' . $productAttributeId;
        }

        $sql = 'SELECT `id_order_detail` FROM `' . _DB_PREFIX_ . 'order_detail` WHERE ' . implode(' AND ', $whereArr);

        return (int) Db::getInstance()->getValue($sql);
    }

    /**
     * @param string $returnTrackingNumber
     * @param int $orderId
     *
     * @return void
     */
    public static function updateOrderReturnTrackingNumber(string $returnTrackingNumber, int $orderId): void
    {
        $idOrderCarrier = self::getIdOrderCarrier($orderId);
        if (!$idOrderCarrier) {
            return;
        }
        Db::getInstance()->update(
            'order_carrier',
            ['return_tracking_number' => pSQL($returnTrackingNumber)],
            'id_order_carrier = ' . $idOrderCarrier
        );
    }

    /**
     * @param string $returnCarrier
     * @param int $orderId
     *
     * @return void
     */
    public static function updateOrderReturnCarrier(string $returnCarrier, int $orderId): void
    {
        $idOrderCarrier = self::getIdOrderCarrier($orderId);
        if (!$idOrderCarrier) {
            return;
        }
        Db::getInstance()->update(
            'order_carrier',
            ['return_carrier' => pSQL($returnCarrier)],
            'id_order_carrier = ' . $idOrderCarrier
        );
    }

    /**
     * @param int $orderId
     *
     * @return string
     */
    public static function getOrderReturnTrackingNumber(int $orderId): string
    {
        $idOrderCarrier = self::getIdOrderCarrier($orderId);
        if (!$idOrderCarrier) {
            return '';
        }
        $result = Db::getInstance()->getValue(
            'SELECT return_tracking_number FROM ' . _DB_PREFIX_ . 'order_carrier WHERE id_order_carrier = ' . $idOrderCarrier
        );

        return $result !== false ? (string) $result : '';
    }

    /**
     * @param int $orderId
     *
     * @return string
     */
    public static function getOrderReturnCarrier(int $orderId): string
    {
        $idOrderCarrier = self::getIdOrderCarrier($orderId);
        if (!$idOrderCarrier) {
            return '';
        }
        $result = Db::getInstance()->getValue(
            'SELECT return_carrier FROM ' . _DB_PREFIX_ . 'order_carrier WHERE id_order_carrier = ' . $idOrderCarrier
        );

        return $result !== false ? (string) $result : '';
    }

    /**
     * @param int $orderId
     *
     * @return int
     */
    private static function getIdOrderCarrier(int $orderId): int
    {
        $result = Db::getInstance()->getValue(
            'SELECT id_order_carrier FROM ' . _DB_PREFIX_ . 'order_carrier WHERE id_order = ' . $orderId . ' ORDER BY id_order_carrier DESC'
        );

        return $result !== false ? (int) $result : 0;
    }

    /**
     * @param int $orderId
     *
     * @return string
     */
    public static function getOrderReturnCarrierName(int $orderId): string
    {
        try {
            $order = new Order($orderId);
            $orderCarrier = new LengowOrderCarrier((int) $order->getIdOrderCarrier());
            $carrier = new LengowCarrier((int) $orderCarrier->return_carrier);

            return (string) $carrier->name;
        } catch (Exception $e) {
            LengowOrderError::addOrderLog(
                $orderId,
                '[PrestaShop error]: ' . $e->getMessage(),
                LengowOrderError::TYPE_ERROR_SEND
            );
        }

        return '';
    }
}
