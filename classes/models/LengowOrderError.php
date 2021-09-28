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
 * Lengow Order Error Class
 */
class LengowOrderError
{
    /**
     * @var string Lengow order error table name
     */
    const TABLE_ORDER_ERROR = 'lengow_logs_import';

    /* Order error fields */
    const FIELD_ID = 'id';
    const FIELD_ORDER_LENGOW_ID = 'id_order_lengow';
    const FIELD_TYPE = 'type';
    const FIELD_MESSAGE = 'message';
    const FIELD_IS_FINISHED = 'is_finished';
    const FIELD_MAIL = 'mail';
    const FIELD_CREATED_AT = 'date';

    /* Log order types */
    const TYPE_ERROR_IMPORT = 1;
    const TYPE_ERROR_SEND = 2;

    /**
     * Check if a Lengow order is in error
     *
     * @param integer $idLengowOrder Lengow order id
     *
     * @return boolean
     */
    public static function lengowOrderIsInError($idLengowOrder)
    {
        $query = 'SELECT lli.id FROM ' . _DB_PREFIX_ . 'lengow_logs_import lli
            LEFT JOIN ' . _DB_PREFIX_ . 'lengow_orders lo ON lli.id_order_lengow = lo.id
            WHERE lo.id = ' . (int) $idLengowOrder . ' AND lli.is_finished = 0 AND lo.order_process_state != 2';
        try {
            $results = Db::getInstance()->executeS($query);
            return !empty($results);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
    }

    /**
     * Get last error not finished from a marketplace reference
     *
     * @param string $marketplaceSku Lengow order id
     * @param integer $deliveryAddressId Lengow delivery address id
     * @param integer $type order log type (import or send)
     *
     * @return array|false
     */
    public static function getLastImportLogNotFinished(
        $marketplaceSku,
        $deliveryAddressId,
        $type = self::TYPE_ERROR_IMPORT
    ) {
        // check if log already exists for the given order id
        $query = 'SELECT lli.`message`, lli.`date` FROM `' . _DB_PREFIX_ . 'lengow_logs_import` lli
            LEFT JOIN `' . _DB_PREFIX_ . 'lengow_orders` lo ON lli.`id_order_lengow` = lo.`id`
            WHERE lo.`marketplace_sku` = \'' . pSQL($marketplaceSku) . '\'
            AND lo.`delivery_address_id` = \'' . (int) $deliveryAddressId . '\'
            AND lli.`type` = \'' . $type . '\'
            AND lli.`is_finished` = 0';
        return Db::getInstance()->getRow($query);
    }

    /**
     * Check if log already exists for the given order
     *
     * @param string $idOrderLengow Lengow order id
     * @param integer|null $type order log type (import or send)
     * @param boolean|null $finished log finished (true or false)
     *
     * @return array|false
     */
    public static function getOrderLogs($idOrderLengow, $type = null, $finished = null)
    {
        $andType = $type !== null ? ' AND `type` = \'' . $type . '\'' : '';
        $andFinished = '';
        if ($finished !== null) {
            $andFinished = $finished ? ' AND `is_finished` = 1' : ' AND `is_finished` = 0';
        }
        // check if log already exists for the given order id
        $query = 'SELECT `id`, `mail`, `is_finished`, `message`, `date`, `type`
            FROM `' . _DB_PREFIX_ . 'lengow_logs_import`
            WHERE `id_order_lengow` = \'' . (int) $idOrderLengow . '\'' . $andType . $andFinished;
        try {
            return Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return array();
        }
    }

    /**
     * Add log information in lengow_logs_import table
     *
     * @param integer $idOrderLengow Lengow order id
     * @param string $message error message
     * @param string $type order log type (import or send)
     * @param integer $finished error is finished
     *
     * @return boolean
     */
    public static function addOrderLog($idOrderLengow, $message = '', $type = self::TYPE_ERROR_IMPORT, $finished = 0)
    {
        try {
            return Db::getInstance()->insert(
                self::TABLE_ORDER_ERROR,
                array(
                    self::FIELD_MESSAGE => pSQL($message),
                    self::FIELD_TYPE => $type,
                    self::FIELD_IS_FINISHED => (int) $finished,
                    self::FIELD_ORDER_LENGOW_ID => (int) $idOrderLengow,
                    self::FIELD_CREATED_AT => date(LengowMain::DATE_FULL),
                )
            );
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
    }

    /**
     * Removes all order logs
     *
     * @param integer $idOrderLengow Lengow order id
     * @param string $type order log type (import or send)
     *
     * @return boolean
     */
    public static function finishOrderLogs($idOrderLengow, $type = self::TYPE_ERROR_IMPORT)
    {
        $query = 'SELECT `id` FROM `' . _DB_PREFIX_ . 'lengow_logs_import`
            WHERE `id_order_lengow` = \'' . (int) $idOrderLengow . '\'
            AND `type` = \'' . $type . '\'';
        try {
            $orderLogs = Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        $updateSuccess = 0;
        foreach ($orderLogs as $orderLog) {
            $result = Db::getInstance()->update(
                self::TABLE_ORDER_ERROR,
                array(self::FIELD_IS_FINISHED => 1),
                '`id` = \'' . (int) $orderLog[self::FIELD_ID] . '\''
            );
            if ($result) {
                $updateSuccess++;
            }
        }
        return count($orderLogs) === $updateSuccess;
    }

    /**
     * Get all order errors not yet sent by email
     *
     * @return array
     */
    public static function getAllOrderLogsNotSent()
    {
        try {
            $sqlLogs = 'SELECT lo.`marketplace_sku`, lli.`message`, lli.`id`
                FROM `' . _DB_PREFIX_ . 'lengow_logs_import` lli
                INNER JOIN `' . _DB_PREFIX_ . 'lengow_orders` lo 
                ON lli.`id_order_lengow` = lo.`id`
                WHERE lli.`is_finished` = 0 AND lli.`mail` = 0
            ';
            $orderLogs = Db::getInstance()->ExecuteS($sqlLogs);
        } catch (PrestaShopDatabaseException $e) {
            $orderLogs = array();
        }
        return $orderLogs;
    }

    /**
     * Mark log as sent by email
     *
     * @param integer $idOrderLog Lengow order log id
     *
     * @return boolean
     */
    public static function logSent($idOrderLog)
    {
        try {
            return Db::getInstance()->update(
                self::TABLE_ORDER_ERROR,
                array(self::FIELD_MAIL => 1),
                '`id` = \'' . (int) $idOrderLog . '\'',
                1
            );
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
    }
}
