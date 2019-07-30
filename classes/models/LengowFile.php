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
 * Lengow File Class
 */
class LengowFile
{
    /**
     * @var string file name
     */
    public $fileName;

    /**
     * @var string folder name that contains the file
     */
    public $folderName;

    /**
     * @var string file link
     */
    public $link;

    /**
     * @var resource a file pointer resource
     */
    public $instance;

    /**
     * Construct
     *
     * @param string $folderName Lengow folder name
     * @param string|null $fileName Lengow file name
     * @param string $mode type of access
     *
     * @throws LengowException unable to create file
     */
    public function __construct($folderName, $fileName = null, $mode = 'a+')
    {
        $this->fileName = $fileName;
        $this->folderName = $folderName;
        $this->instance = self::getResource($this->getPath(), $mode);
        if (!is_resource($this->instance)) {
            throw new LengowException(
                LengowMain::setLogMessage(
                    'log.export.error_unable_to_create_file',
                    array(
                        'file_name' => $fileName,
                        'folder_name' => $folderName,
                    )
                )
            );
        }
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Write content in file
     *
     * @param string $txt text to be written
     */
    public function write($txt)
    {
        if (!$this->instance) {
            $this->instance = fopen($this->getPath(), 'a+');
        }
        fwrite($this->instance, $txt);
    }

    /**
     * Delete file
     */
    public function delete()
    {
        if ($this->exists()) {
            if ($this->instance) {
                $this->close();
            }
            unlink($this->getPath());
        }
    }

    /**
     * Get resource of a given stream
     *
     * @param string $path path to the file
     * @param string $mode type of access
     *
     * @return resource
     */
    public static function getResource($path, $mode = 'a+')
    {
        return fopen($path, $mode);
    }

    /**
     * Get file link
     *
     * @return string
     */
    public function getLink()
    {
        if (empty($this->link)) {
            if (!$this->exists()) {
                $this->link = null;
            }
            $base = LengowMain::getLengowBaseUrl();
            $this->link = $base . $this->folderName . '/' . $this->fileName;
        }
        return $this->link;
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function getPath()
    {
        $sep = DIRECTORY_SEPARATOR;
        return LengowMain::getLengowFolder() . $sep . $this->folderName . $sep . $this->fileName;
    }

    /**
     * Get folder path of current file
     *
     * @return string
     */
    public function getFolderPath()
    {
        $sep = DIRECTORY_SEPARATOR;
        return LengowMain::getLengowFolder() . $sep . $this->folderName;
    }

    /**
     * Rename file
     *
     * @param string $newName new file name
     *
     * @return boolean
     */
    public function rename($newName)
    {
        return rename($this->getPath(), $newName);
    }

    /**
     * Close file handle
     */
    public function close()
    {
        if (is_resource($this->instance)) {
            fclose($this->instance);
        }
    }

    /**
     * Check if current file exists
     *
     * @return boolean
     */
    public function exists()
    {
        return file_exists($this->getPath());
    }

    /**
     * Get a file list for a given folder
     *
     * @param string $folder folder name
     *
     * @return array|false
     */
    public static function getFilesFromFolder($folder)
    {
        $sep = DIRECTORY_SEPARATOR;
        $folderPath = LengowMain::getLengowFolder() . $sep . $folder;
        if (!file_exists($folderPath)) {
            return false;
        }
        $folderContent = scandir($folderPath);
        $files = array();
        foreach ($folderContent as $file) {
            try {
                if (!preg_match('/^\.[a-zA-Z\.]+$|^\.$|index\.php/', $file)) {
                    $files[] = new LengowFile($folder, $file);
                }
            } catch (LengowException $e) {
                continue;
            }
        }
        return $files;
    }
}
