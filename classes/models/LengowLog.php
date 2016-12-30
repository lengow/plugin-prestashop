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
 * @category  Model
 * @package   LengowLog
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * Lengow Log Class
 */
class LengowLog extends LengowFile
{
    /**
     * @var string name of logs folder
     */
    public static $lengowLogFolder = 'logs';

    /**
     * @var LengowFile Lengow file instance
     */
    protected $file;

    /**
     * Construct
     *
     * @param string $fileName log file name
     */
    public function __construct($fileName = null)
    {
        if (empty($fileName)) {
            $this->fileName = 'logs-'.date('Y-m-d').'.txt';
        } else {
            $this->fileName = $fileName;
        }
        $this->file = new LengowFile(LengowLog::$lengowLogFolder, $this->fileName);
    }

    /**
     * Write log
     *
     * @param string  $category       log category
     * @param string  $message        log message
     * @param boolean $logOutput      display on screen
     * @param string  $marketplaceSku Lengow order id
     */
    public function write($category, $message = "", $logOutput = false, $marketplaceSku = null)
    {
        $decodedMessage = LengowMain::decodeLogMessage($message, 'en');
        $log = date('Y-m-d H:i:s').Tools::substr((string)microtime(), 1, 8);
        $log .= ' - '.(empty($category) ? '' : '['.$category.'] ');
        $log .= ''.(empty($marketplaceSku) ? '' : 'order '.$marketplaceSku.' : ');
        $log .= $decodedMessage."\r\n";
        if ($logOutput) {
            echo $log.'<br />';
            flush();
        }
        $this->file->write($log);
    }

    /**
     * Get log files path
     *
     * @return mixed (array or false)
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
                'full_path'  => $file->getPath(),
                'short_path' => 'logs-'.$match[1].'.txt',
                'name'       => $match[1].'.txt'
            );
        }
        return array_reverse($logs);
    }

    /**
     * Get current file
     *
     * @return string
     */
    public function getFileName()
    {
        return _PS_MODULE_LENGOW_DIR_.LengowLog::$lengowLogFolder.'/'.$this->fileName;
    }

    /**
     * Get log files
     *
     * @return array
     */
    public static function getFiles()
    {
        return LengowFile::getFilesFromFolder(LengowLog::$lengowLogFolder);
    }

    /**
     * Download log file
     *
     * @param string $file file name for a specific log file
     */
    public static function download($file = null)
    {
        if ($file && preg_match('/^logs-([0-9]{4}-[0-9]{2}-[0-9]{2})\.txt$/', $file, $match)) {
            $filename = _PS_MODULE_LENGOW_DIR_.LengowLog::$lengowLogFolder.'/'.$file;
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
