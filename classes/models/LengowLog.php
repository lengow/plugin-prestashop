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
     * @param string $category Category
     * @param string $message log message
     * @param boolean $display display on screen
     * @param string $marketplace_sku lengow order id
     */
    public function write($category, $message = "", $display = false, $marketplace_sku = null)
    {
        $decoded_message = LengowMain::decodeLogMessage($message, 'en');
        $log = date('Y-m-d H:i:s').Tools::substr((string)microtime(), 1, 8);
        $log .= ' - '.(empty($category) ? '' : '['.$category.'] ');
        $log .= ''.(empty($marketplace_sku) ? '' : 'order '.$marketplace_sku.' : ');
        $log .= $decoded_message."\r\n";
        if ($display) {
            echo $log.'<br />';
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
}
