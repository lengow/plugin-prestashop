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
    public static function findByOrderIdProductId($idOrder, $idProduct)
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
     * Save the return tracking number on the order carrier read by the ship action
     *
     * @param string $returnTrackingNumber
     * @param int $orderId PrestaShop order id
     */
    public static function updateOrderReturnTrackingNumber($returnTrackingNumber, $orderId)
    {
        if (!Validate::isTrackingNumber($returnTrackingNumber)) {
            LengowMain::log(
                LengowLog::CODE_ACTION,
                'Return tracking number not saved for order ' . (int) $orderId . ': invalid value'
            );

            return;
        }
        self::updateOrderCarrierField('return_tracking_number', $returnTrackingNumber, $orderId);
    }

    /**
     * Save the return carrier on the order carrier read by the ship action
     *
     * @param int|string $returnCarrier
     * @param int $orderId PrestaShop order id
     */
    public static function updateOrderReturnCarrier($returnCarrier, $orderId)
    {
        self::updateOrderCarrierField('return_carrier', (string) $returnCarrier, $orderId);
    }

    /**
     * Write a Lengow column of order_carrier with SQL: these columns are not in the OrderCarrier
     * definition, so ObjectModel::update() never saves them
     *
     * @param string $field order_carrier column
     * @param string $value unescaped value
     * @param int $orderId PrestaShop order id
     */
    private static function updateOrderCarrierField($field, $value, $orderId)
    {
        try {
            $order = new Order((int) $orderId);
            $idOrderCarrier = (int) $order->getIdOrderCarrier();
            if (!$idOrderCarrier) {
                return;
            }
            Db::getInstance()->update(
                'order_carrier',
                [$field => pSQL($value)],
                'id_order_carrier = ' . $idOrderCarrier
            );
        } catch (Exception $e) {
            LengowOrderError::addOrderLog(
                $orderId,
                '[PrestaShop error]: ' . $e->getMessage(),
                LengowOrderError::TYPE_ERROR_SEND
            );
        }
    }

    /**
     * @param int $orderId
     *
     * @return string
     */
    public static function getOrderReturnTrackingNumber($orderId)
    {
        try {
            $order = new Order($orderId);
            $orderCarrier = new LengowOrderCarrier((int) $order->getIdOrderCarrier());

            return (string) $orderCarrier->return_tracking_number;
        } catch (Exception $e) {
            LengowOrderError::addOrderLog(
                $orderId,
                '[PrestaShop error]: ' . $e->getMessage(),
                LengowOrderError::TYPE_ERROR_SEND
            );
        }

        return '';
    }

    /**
     * @param int $orderId
     *
     * @return string
     */
    public static function getOrderReturnCarrier($orderId)
    {
        try {
            $order = new Order($orderId);
            $orderCarrier = new LengowOrderCarrier((int) $order->getIdOrderCarrier());

            return (string) $orderCarrier->return_carrier;
        } catch (Exception $e) {
            LengowOrderError::addOrderLog(
                $orderId,
                '[PrestaShop error]: ' . $e->getMessage(),
                LengowOrderError::TYPE_ERROR_SEND
            );
        }

        return '';
    }

    /**
     * @param int $orderId
     *
     * @return string
     */
    public static function getOrderReturnCarrierName($orderId)
    {
        try {
            $order = new Order($orderId);
            $orderCarrier = new LengowOrderCarrier((int) $order->getIdOrderCarrier());
            $carrier = new LengowCarrier($orderCarrier->return_carrier);

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
