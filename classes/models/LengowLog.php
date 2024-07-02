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
 * Lengow Log Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowLog extends LengowFile
{
    /* Log category codes */
    public const CODE_INSTALL = 'Install';
    public const CODE_UNINSTALL = 'Uninstall';
    public const CODE_CONNECTION = 'Connection';
    public const CODE_SETTING = 'Setting';
    public const CODE_CONNECTOR = 'Connector';
    public const CODE_EXPORT = 'Export';
    public const CODE_IMPORT = 'Import';
    public const CODE_ACTION = 'Action';
    public const CODE_MAIL_REPORT = 'Mail Report';

    /* Log params for export */
    public const LOG_DATE = 'date';
    public const LOG_LINK = 'link';

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
            $this->fileName = 'logs-' . date(LengowMain::DATE_DAY) . '.txt';
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
     * @param bool $logOutput display on screen
     * @param string|null $marketplaceSku Lengow order id
     */
    public function write($category, $message = '', $logOutput = false, $marketplaceSku = null)
    {
        $decodedMessage = LengowMain::decodeLogMessage($message, LengowTranslation::DEFAULT_ISO_CODE);
        $log = date(LengowMain::DATE_FULL);
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
        $logs = [];
        $files = self::getFiles();
        if (empty($files)) {
            return $logs;
        }
        foreach ($files as $file) {
            preg_match('/^logs-([0-9]{4}-[0-9]{2}-[0-9]{2})\.txt$/', $file->fileName, $match);
            $date = $match[1];
            $logs[] = [
                self::LOG_DATE => $date,
                self::LOG_LINK => LengowMain::getToolboxUrl()
                    . '&' . LengowToolbox::PARAM_TOOLBOX_ACTION . '=' . LengowToolbox::ACTION_LOG
                    . '&' . LengowToolbox::PARAM_DATE . '=' . urlencode($date),
            ];
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
        /* @var LengowFile[] $logFiles */
        if ($date && preg_match('/^(\d{4}-\d{2}-\d{2})$/', $date, $match)) {
            $logFiles = false;
            $file = 'logs-' . $date . '.txt';
            $fileName = $date . '.txt';
            $sep = DIRECTORY_SEPARATOR;
            $filePath = _PS_MODULE_LENGOW_DIR_ . LengowMain::FOLDER_LOG . $sep . $file;
            if (file_exists($filePath)) {
                try {
                    $logFiles = [new LengowFile(LengowMain::FOLDER_LOG, $file)];
                } catch (LengowException $e) {
                    $logFiles = [];
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
                if (!file_exists($filePath)) {
                    continue;
                }
                $fileInfo = pathinfo($filePath);
                if ($fileInfo['extension'] !== 'txt') {
                    continue;
                }
                if (strrpos($fileInfo['basename'], 'logs') === false) {
                    continue;
                }
                $handle = fopen($filePath, 'rb');
                $fileSize = filesize($filePath);
                if ($fileSize > 0) {
                    $contents .= fread($handle, $fileSize);
                }
            }
        }
        header('Content-type: text/plain');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        echo $contents;
        exit;
    }

    /**
     * Logs potential PHP fatal error on shutdown.
     * Can be useful when the script crash silently
     */
    public static function registerShutdownFunction()
    {
        ini_set('log_errors_max_len', 10240);
        register_shutdown_function(
            function () {
                $error = error_get_last();
                if ($error) {
                    $labels = [
                        E_ERROR => 'E_ERROR',
                        E_WARNING => 'E_WARNING',
                        E_PARSE => 'E_PARSE',
                        E_NOTICE => 'E_NOTICE',
                        E_CORE_ERROR => 'E_CORE_ERROR',
                        E_CORE_WARNING => 'E_CORE_WARNING',
                        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
                        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
                        E_USER_ERROR => 'E_USER_ERROR',
                        E_USER_WARNING => 'E_USER_WARNING',
                        E_USER_NOTICE => 'E_USER_NOTICE',
                        E_STRICT => 'E_STRICT',
                        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
                        E_DEPRECATED => 'E_DEPRECATED',
                        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
                        E_ALL => 'E_ALL',
                    ];
                    LengowMain::log(
                        $labels[$error['type']] ?? 'PHP',
                        $error['message'] . PHP_EOL . 'in ' . $error['file'] . ' on line ' . $error['line']
                    );
                }
            }
        );
    }
}
