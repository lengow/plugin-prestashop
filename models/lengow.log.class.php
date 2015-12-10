<?php
/**
 * Copyright 2016 Lengow SAS.
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
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */


/**
 * The Lengow Log class
 *
 */
class LengowLog extends LengowFile
{

    public static $LENGOW_LOGS_FOLDER = 'logs';

    protected $file;

    public function __construct($file_name = null)
    {
        if (empty($file_name)) {
            $file_name = 'logs-' . date('Y-m-d') . '.txt';
        }
        $this->file = new LengowFile(LengowLog::$LENGOW_LOGS_FOLDER, $file_name);
    }

    /**
     * Write log
     *
     * @param string $message log message
     * @param boolean $display display on screen
     * @param string $id_order_lengow lengow order id
     */
    public function write($message, $display = false, $id_order_lengow = null)
    {
        $log = date('Y-m-d : H:i:s') . ' - ' . (empty($id_order_lengow) ? '' : 'Order ' . $id_order_lengow . ': ');
        $log .= $message . "\r\n";
        if ($display) {
            echo $log . '<br />';
            flush();
        }
        $this->file->write($log);
    }

    /**
     * Get log files links
     *
     * @return mixed
     *
     */
    public static function getLinks()
    {
        $files = LengowLog::getFiles();
        if (empty($files)) {
            return false;
        }
        $logs = array();
        foreach ($files as $file) {
            $logs[] = $file->getLink();
        }
        return $logs;
    }

    /**
     * Add log information in lengow_logs_import table
     *
     * @param string $lengow_id Lengow order id
     * @param integer $finished order state
     * @param string $message error message
     */
    public static function addLog($order_data, $lengow_id, $message = '', $finished = 0)
    {
        $db = Db::getInstance();
        // check if log already exists for the given order id
        $sql_exist = 'SELECT 1 FROM `' . _DB_PREFIX_ . 'lengow_logs_import` '
            . 'WHERE `lengow_order_id` = \'' . pSQL($lengow_id) . '\'';
        $r = $db->getRow($sql_exist);
        if (!$r) {
            LengowLog::insert($lengow_id, $finished, $message, $order_data);
        } else {
            LengowLog::update($lengow_id, $finished, $message);
        }
    }


    /**
     * Update order log from lengow_logs_import table
     *
     * @param string $lengow_order_id lengow order id
     * @param integer $finished order finished
     * @param string $message log content
     *
     * @return boolean
     */
    public static function update($lengow_id, $finished, $message)
    {
        $db = Db::getInstance();
        if (_PS_VERSION_ >= '1.5') {
            return $db->update(
                'lengow_logs_import',
                array(
                    'is_finished' => (int)$finished,
                    'message' => pSQL($message),
                ),
                '`lengow_order_id` = \'' . pSQL($lengow_id) . '\'',
                1
            );
        } else {
            return $db->autoExecute(
                _DB_PREFIX_ . 'lengow_logs_import',
                array(
                    'is_finished' => (int)$finished,
                    'message' => pSQL($message),
                ),
                'UPDATE',
                '`lengow_order_id` = \'' . pSQL($lengow_id) . '\'',
                1
            );
        }
    }

    /**
     * Insert order log from lengow_logs_import table
     *
     * @param string $lengow_order_id lengow order id
     * @param integer $finished order finished
     * @param string $message log content
     *
     * @return boolean
     */
    public static function insert($lengow_id, $finished, $message, $order_data)
    {
        $db = Db::getInstance();
        if (_PS_VERSION_ >= '1.5') {
            return $db->insert(
                'lengow_logs_import',
                array(
                    'lengow_order_id' => pSQL($lengow_id),
                    'is_finished' => (int)$finished,
                    'extra' => pSQL(Tools::jsonEncode($order_data)),
                    'date' => date('Y-m-d H:i:s'),
                    'message' => pSQL($message),
                )
            );
        } else {
            return $db->autoExecute(
                _DB_PREFIX_ . 'lengow_logs_import',
                array(
                    'lengow_order_id' => pSQL($lengow_id),
                    'is_finished' => (int)$finished,
                    'extra' => pSQL(Tools::jsonEncode($order_data)),
                    'date' => date('Y-m-d H:i:s'),
                    'message' => pSQL($message),
                ),
                'INSERT'
            );
        }
    }

    /**
     * Delete order log from lengow_logs_import table
     *
     * @param string $lengow_order_id lengow order id
     *
     * @return boolean
     */
    public static function deleteLog($lengow_order_id = null)
    {
        if (is_null($lengow_order_id)) {
            return false;
        }
        $db = Db::getInstance();
        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'lengow_logs_import WHERE lengow_order_id = \'' .
            pSQL(Tools::substr($lengow_order_id, 0, 32)) . '\' LIMIT 1';
        return $db->execute($sql);
    }

    /**
     * Get log files
     *
     * @return array
     */
    public static function getFiles()
    {
        return LengowFile::getFilesFromFolder(LengowLog::$LENGOW_LOGS_FOLDER);
    }
}
