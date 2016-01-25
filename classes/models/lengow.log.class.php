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

    /**
     * @var string name of logs folder
     */
    public static $LENGOW_LOGS_FOLDER = 'logs';

    /**
     * @var LengowFile
     */
    protected $file;

    public function __construct($file_name = null)
    {
        if (empty($file_name)) {
            $this->file_name = 'logs-' . date('Y-m-d') . '.txt';
        } else {
            $this->file_name = $file_name;
        }
        $this->file = new LengowFile(LengowLog::$LENGOW_LOGS_FOLDER, $this->file_name);
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
        $log = date('Y-m-d:H:i:s').substr((string)microtime(), 1, 8);
        $log.= ' - ' . (empty($id_order_lengow) ? '' : 'Order ' . $id_order_lengow . ': ');
        $log.= $message . "\r\n";
        if ($display && php_sapi_name() != "cli") {
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
     * v3
     * Get log files path
     *
     * @return mixed
     *
     */
    public static function getPaths()
    {
        $files = LengowLog::getFiles();
        if (empty($files)) {
            return false;
        }
        $logs = array();
        foreach ($files as $file) {
            preg_match('/\/lengow\/logs\/logs-([0-9]{4}-[0-9]{2}-[0-9]{2})\.txt/', $file->getPath(), $match);
            $logs[] = array(
                'full_path' => $file->getPath(),
                'short_path' => 'logs-'.$match[1].'.txt',
                'name' => $match[1].'.txt'
            );
        }
        return $logs;
    }

    /**
     * Get current file
     *
     * @return string
     *
     */
    public function getFileName()
    {
        return _PS_MODULE_LENGOW_DIR_.LengowLog::$LENGOW_LOGS_FOLDER.'/'.$this->file_name;
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

    public static function download($file = null)
    {
        if ($file && preg_match('/^logs-([0-9]{4}-[0-9]{2}-[0-9]{2})\.txt$/', $file, $match)) {
            $filename = _PS_MODULE_LENGOW_DIR_.LengowLog::$LENGOW_LOGS_FOLDER.'/'.$file;
            $handle = fopen($filename, "r");
            $contents = fread($handle, filesize($filename));
            header('Content-type: text/plain');
            header('Content-Disposition: attachment; filename="'.$match[1].'.txt"');
            echo $contents;
            exit();
        } else {
            $files = self::getPaths();
            header('Content-type: text/plain');
            header('Content-Disposition: attachment; filename="logs.txt"');
            foreach ($files as $file) {
                $handle = fopen($file['full_path'], "r");
                $contents = fread($handle, filesize($file['full_path']));
                echo $contents;
            }
            exit();
        }
    }

    /**
     * Delete order log from lengow_logs_import table
     *
     * @param string $id_log_import
     *
     * @return boolean
     */
    public static function deleteLog($id_log_import = null)
    {
        if (is_null($id_log_import)) {
            return false;
        }
        $db = Db::getInstance();
        $sql = 'DELETE FROM '._DB_PREFIX_.'lengow_logs_import
            WHERE id = \''.pSQL($id_log_import).'\'
            LIMIT 1
        ';
        return $db->execute($sql);
    }

    /**
     * Delete order log from lengow_logs_import table by order ID and order line ID
     *
     * @param string $lengow_id
     * @param string $lengow_order_line
     *
     * @return boolean
     */
    public static function deleteLogByOrderId($lengow_id = null, $lengow_order_line = null)
    {
        if (is_null($lengow_id) || is_null($lengow_order_line)) {
            return false;
        }
        $db = Db::getInstance();
        $sql = 'DELETE FROM '._DB_PREFIX_.'lengow_logs_import
            WHERE lengow_order_id = \''.pSQL($lengow_id).'\'
            AND lengow_order_line = \''.pSQL($lengow_order_line).'\'
            LIMIT 1
        ';
        return $db->execute($sql);
    }

    /**
     * Add log information in lengow_logs_import table
     *
     * @param array     $order_data         Lengow order data
     * @param string    $lengow_id          Lengow order id
     * @param string    $lengow_order_line  Lengow order line id
     * @param string    $message            error message
     * @param integer   $finished           order state
     */
    public static function addLog($order_data, $lengow_id, $lengow_order_line = null, $message = '', $finished = 0)
    {
        $db = Db::getInstance();
        // check if log already exists for the given order id
        $sql_exist = 'SELECT 1 FROM `'._DB_PREFIX_.'lengow_logs_import`
            WHERE `lengow_order_id` = \''.pSQL($lengow_id).'\'
            AND `lengow_order_line` = \''.pSQL($lengow_order_line).'\'
        ';
        $r = $db->getRow($sql_exist);
        if (!$r) {
            LengowLog::insert($lengow_id, $lengow_order_line, $finished, $message, $order_data);
        } else {
            LengowLog::update($lengow_id, $lengow_order_line, $finished, $message);
        }
    }

    /**
     * get log
     *
     * @param string    $lengow_id          Lengow order id
     * @param string    $lengow_order_line  Lengow order line id
     *
     * @return mixed
     */
    public static function loadLogInfo($lengow_id, $lengow_order_line)
    {
        $db = Db::getInstance();
        // check if log already exists for the given order id
        $sql_exist = 'SELECT `is_finished`, `message`, `date` FROM `'._DB_PREFIX_.'lengow_logs_import`
            WHERE `lengow_order_id` = \''.pSQL($lengow_id).'\'
            AND `lengow_order_line` = \''.pSQL($lengow_order_line).'\'
        ';
        $row = $db->getRow($sql_exist);
        if ($row && $row['is_finished'] == 0) {
            return $row['message'].' (created on the '.$row['date'].')';
        }
        return false;
    }

    /**
     * Update order log from lengow_logs_import table
     *
     * @param string    $lengow_order_id    lengow order id
     * @param string    $lengow_order_line  lengow order line id
     * @param integer   $finished           order finished
     * @param string    $message            log content
     *
     * @return boolean
     */
    public static function update($lengow_id, $lengow_order_line, $finished, $message)
    {
        return Db::getInstance()->autoExecute(
            _DB_PREFIX_ . 'lengow_logs_import',
            array(
                'is_finished' => (int)$finished,
                'message' => pSQL($message),
            ),
            'UPDATE',
            '`lengow_order_id` = \'' . pSQL($lengow_id) . '\' 
            AND `lengow_order_line` = \''.pSQL($lengow_order_line).'\'',
            1
        );
    }

    /**
     * Insert order log from lengow_logs_import table
     *
     * @param string    $lengow_id          lengow order id
     * @param string    $lengow_order_line  lengow order line id
     * @param integer   $finished           order finished
     * @param string    $message            log content
     * @param mixed     $order_data         the order data
     *
     * @return boolean
     */
    public static function insert($lengow_id, $lengow_order_line, $finished, $message, $order_data)
    {
        return Db::getInstance()->autoExecute(
            _DB_PREFIX_.'lengow_logs_import',
            array(
                'lengow_order_id' => pSQL($lengow_id),
                'lengow_order_line' => pSQL($lengow_order_line),
                'is_finished' => (int)$finished,
                'extra' => pSQL(Tools::jsonEncode($order_data)),
                'date' => date('Y-m-d H:i:s'),
                'message' => pSQL($message),
            ),
            'INSERT'
        );
    }
}
