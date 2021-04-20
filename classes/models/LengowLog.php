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
 * Lengow Log Class
 */
class LengowLog extends LengowFile
{
    /* Log category codes */
    const CODE_INSTALL = 'Install';
    const CODE_UNINSTALL = 'Uninstall';
    const CODE_CONNECTION = 'Connection';
    const CODE_SETTING = 'Setting';
    const CODE_CONNECTOR = 'Connector';
    const CODE_EXPORT = 'Export';
    const CODE_IMPORT = 'Import';
    const CODE_ACTION = 'Action';
    const CODE_MAIL_REPORT = 'Mail Report';

    /* Log params for export */
    const LOG_DATE = 'date';
    const LOG_LINK = 'link';

    /**
     * @var LengowFile Lengow file instance
     */
    protected $file;

    /**
     * Construct
     *
     * @param string $fileName log file name
     *
     * @throws LengowException
     */
    public function __construct($fileName = null)
    {
        if (empty($fileName)) {
            $this->fileName = 'logs-' . date('Y-m-d') . '.txt';
        } else {
            $this->fileName = $fileName;
        }
        $this->file = new LengowFile(LengowMain::FOLDER_LOG, $this->fileName);
    }

    /**
     * Write log
     *
     * @param string $category log category
     * @param string $message log message
     * @param boolean $logOutput display on screen
     * @param string|null $marketplaceSku Lengow order id
     */
    public function write($category, $message = '', $logOutput = false, $marketplaceSku = null)
    {
        $decodedMessage = LengowMain::decodeLogMessage($message, LengowTranslation::DEFAULT_ISO_CODE);
        $log = date('Y-m-d H:i:s');
        $log .= ' - ' . (empty($category) ? '' : '[' . $category . '] ');
        $log .= '' . (empty($marketplaceSku) ? '' : 'order ' . $marketplaceSku . ': ');
        $log .= $decodedMessage . "\r\n";
        if ($logOutput) {
            echo $log . '<br />';
            flush();
        }
        $this->file->write($log);
    }

    /**
     * Get log files path
     *
     * @return array
     */
    public static function getPaths()
    {
        $logs = array();
        $files = self::getFiles();
        if (empty($files)) {
            return $logs;
        }
        foreach ($files as $file) {
            preg_match('/^logs-([0-9]{4}-[0-9]{2}-[0-9]{2})\.txt$/', $file->fileName, $match);
            $date = $match[1];
            $logs[] = array(
                self::LOG_DATE => $date,
                self::LOG_LINK => LengowMain::getToolboxUrl()
                    . '&' . LengowToolbox::PARAM_ACTION . '=' . LengowToolbox::ACTION_LOG
                    . '&' . LengowToolbox::PARAM_DATE . '=' . urlencode($date),
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
        $sep = DIRECTORY_SEPARATOR;
        return _PS_MODULE_LENGOW_DIR_ . LengowMain::FOLDER_LOG . $sep . $this->fileName;
    }

    /**
     * Get log files
     *
     * @return array
     */
    public static function getFiles()
    {
        return LengowFile::getFilesFromFolder(LengowMain::FOLDER_LOG);
    }

    /**
     * Download log file
     *
     * @param string|null $date date for a specific log file
     */
    public static function download($date = null)
    {
        /** @var LengowFile[] $logFiles */
        if ($date && preg_match('/^(\d{4}-\d{2}-\d{2})$/', $date, $match)) {
            $logFiles = false;
            $file = 'logs-' . $date . '.txt';
            $fileName = $date . '.txt';
            $sep = DIRECTORY_SEPARATOR;
            $filePath = _PS_MODULE_LENGOW_DIR_ . LengowMain::FOLDER_LOG . $sep . $file;
            if (file_exists($filePath)) {
                try {
                    $logFiles = array(new LengowFile(LengowMain::FOLDER_LOG, $file));
                } catch (LengowException $e) {
                    $logFiles = array();
                }
            }
        } else {
            $fileName = 'logs.txt';
            $logFiles = self::getFiles();
        }
        $contents = '';
        if ($logFiles) {
            foreach ($logFiles as $logFile) {
                $filePath = $logFile->getPath();
                $handle = fopen($filePath, 'r');
                $fileSize = filesize($filePath);
                if ($fileSize > 0) {
                    $contents .= fread($handle, $fileSize);
                }
            }
        }
        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        echo $contents;
        exit();
    }
}
