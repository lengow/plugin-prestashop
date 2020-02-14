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
 * Lengow Feed Class
 */
class LengowFeed
{
    /**
     * @var string protection
     */
    const PROTECTION = '"';

    /**
     * @var string CSV separator
     */
    const CSV_SEPARATOR = '|';

    /**
     * @var string end of line
     */
    const EOL = "\r\n";

    /**
     * @var string csv format
     */
    const FORMAT_CSV = 'csv';

    /**
     * @var string yaml format
     */
    const FORMAT_YAML = 'yaml';

    /**
     * @var string xml format
     */
    const FORMAT_XML = 'xml';

    /**
     * @var string json format
     */
    const FORMAT_JSON = 'json';

    /**
     * @var string header content
     */
    const HEADER = 'header';

    /**
     * @var string body content
     */
    const BODY = 'body';

    /**
     * @var string footer content
     */
    const FOOTER = 'footer';

    /**
     * @var LengowFile Lengow file instance
     */
    protected $file;

    /**
     * @var string feed content
     */
    protected $content = '';

    /**
     * @var boolean stream or file
     */
    protected $stream;

    /**
     * @var string feed format
     */
    protected $format;

    /**
     * @var boolean Use legacy fields
     */
    protected $legacy;

    /**
     * @var string|null export shop folder
     */
    protected $shopFolder = null;

    /**
     * @var string full export folder
     */
    protected $exportFolder;

    /**
     * @var array formats available for export
     */
    public static $availableFormats = array(
        self::FORMAT_CSV,
        self::FORMAT_YAML,
        self::FORMAT_XML,
        self::FORMAT_JSON,
    );

    /**
     * @var string Lengow export folder
     */
    public static $lengowExportFolder = 'export';

    /**
     * Construct
     *
     * @param boolean $stream export streaming or in a file
     * @param string $format export format
     * @param boolean $legacy export legacy field or not
     * @param string $shopName Prestashop shop name
     *
     * @throws LengowException unable to create folder
     */
    public function __construct($stream, $format, $legacy, $shopName = null)
    {
        $this->stream = $stream;
        $this->format = $format;
        $this->legacy = $legacy;
        if ($shopName === null) {
            $shopName = Context::getContext()->shop->name;
        }
        $this->shopFolder = Tools::strtolower(
            preg_replace(
                '/[^a-zA-Z0-9_]+/',
                '',
                str_replace(array(' ', '\''), '_', LengowMain::replaceAccentedChars($shopName))
            )
        );
        if (!$this->stream) {
            $this->initExportFile();
        }
    }

    /**
     * Create export file
     *
     * @throws LengowException unable to create folder
     */
    public function initExportFile()
    {
        $sep = DIRECTORY_SEPARATOR;
        $this->exportFolder = self::$lengowExportFolder . $sep . $this->shopFolder;
        $folderPath = LengowMain::getLengowFolder() . $sep . $this->exportFolder;
        if (!file_exists($folderPath)) {
            if (!mkdir($folderPath)) {
                throw new LengowException(
                    LengowMain::setLogMessage(
                        'log.export.error_unable_to_create_folder',
                        array('folder_path' => $folderPath)
                    )
                );
            }
        }
        $fileName = 'flux-' . Context::getContext()->language->iso_code . '-' . time() . '.' . $this->format;
        $this->file = new LengowFile($this->exportFolder, $fileName);
    }

    /**
     * Write feed
     *
     * @param string $type data type (header, body or footer)
     * @param array $data export data
     * @param boolean|null $isFirst is first product
     * @param boolean|null $maxCharacter max characters for yaml format
     */
    public function write($type, $data = array(), $isFirst = null, $maxCharacter = null)
    {
        switch ($type) {
            case self::HEADER:
                if ($this->stream) {
                    header($this->getHtmlHeader());
                    if ($this->format === self::FORMAT_CSV) {
                        header('Content-Disposition: attachment; filename=feed.csv');
                    }
                }
                $header = $this->getHeader($data);
                $this->flush($header);
                break;
            case self::BODY:
                $body = $this->getBody($data, $isFirst, $maxCharacter);
                $this->flush($body);
                break;
            case self::FOOTER:
                $footer = $this->getFooter();
                $this->flush($footer);
                break;
        }
    }

    /**
     * Return feed header
     *
     * @param array $data export data
     *
     * @return string
     */
    protected function getHeader($data)
    {
        switch ($this->format) {
            case 'csv':
            default:
                $header = '';
                foreach ($data as $field) {
                    $header .= self::PROTECTION . self::formatFields($field, self::FORMAT_CSV, $this->legacy)
                        . self::PROTECTION . self::CSV_SEPARATOR;
                }
                return rtrim($header, self::CSV_SEPARATOR) . self::EOL;
            case self::FORMAT_XML:
                return '<?xml version="1.0" encoding="UTF-8"?>' . self::EOL
                . '<catalog>' . self::EOL;
            case self::FORMAT_JSON:
                return '{"catalog":[';
            case self::FORMAT_YAML:
                return '"catalog":' . self::EOL;
        }
    }

    /**
     * Get feed body
     *
     * @param array $data feed data
     * @param boolean $isFirst is first product
     * @param integer $maxCharacter max characters for yaml format
     *
     * @return string
     */
    protected function getBody($data, $isFirst, $maxCharacter)
    {
        switch ($this->format) {
            case self::FORMAT_CSV:
            default:
                $content = '';
                foreach ($data as $value) {
                    $content .= self::PROTECTION . $value . self::PROTECTION . self::CSV_SEPARATOR;
                }
                return rtrim($content, self::CSV_SEPARATOR) . self::EOL;
            case self::FORMAT_XML:
                $content = '<product>';
                foreach ($data as $field => $value) {
                    $field = self::formatFields($field, self::FORMAT_XML);
                    $content .= '<' . $field . '><![CDATA[' . $value . ']]></' . $field . '>' . self::EOL;
                }
                $content .= '</product>' . self::EOL;
                return $content;
            case self::FORMAT_JSON:
                $content = $isFirst ? '' : ',';
                $jsonArray = array();
                foreach ($data as $field => $value) {
                    $field = self::formatFields($field, self::FORMAT_JSON);
                    $jsonArray[$field] = $value;
                }
                $content .= Tools::jsonEncode($jsonArray);
                return $content;
            case self::FORMAT_YAML:
                if ($maxCharacter % 2 === 1) {
                    $maxCharacter = $maxCharacter + 1;
                } else {
                    $maxCharacter = $maxCharacter + 2;
                }
                $content = '  ' . self::PROTECTION . 'product' . self::PROTECTION . ':' . self::EOL;
                foreach ($data as $field => $value) {
                    $field = self::formatFields($field, self::FORMAT_YAML);
                    $content .= '    ' . self::PROTECTION . $field . self::PROTECTION . ':';
                    $content .= $this->indentYaml($field, $maxCharacter) . (string)$value . self::EOL;
                }
                return $content;
        }
    }

    /**
     * Return feed footer
     *
     * @return string
     */
    protected function getFooter()
    {
        switch ($this->format) {
            case self::FORMAT_XML:
                return '</catalog>';
            case self::FORMAT_JSON:
                return ']}';
            default:
                return '';
        }
    }

    /**
     * Flush feed content
     *
     * @param string $content feed content to be flushed
     */
    public function flush($content)
    {
        if ($this->stream) {
            echo $content;
            flush();
        } else {
            $this->file->write($content);
        }
    }

    /**
     * Finalize export generation
     *
     * @throws LengowException
     *
     * @return boolean
     */
    public function end()
    {
        $this->write(self::FOOTER);
        if (!$this->stream) {
            $oldFileName = 'flux-' . Context::getContext()->language->iso_code . '.' . $this->format;
            $oldFile = new LengowFile($this->exportFolder, $oldFileName);
            if ($oldFile->exists()) {
                $oldFilePath = $oldFile->getPath();
                $oldFile->delete();
            }
            if (isset($oldFilePath)) {
                $rename = $this->file->rename($oldFilePath);
                $this->file->fileName = $oldFileName;
            } else {
                $sep = DIRECTORY_SEPARATOR;
                $rename = $this->file->rename($this->file->getFolderPath() . $sep . $oldFileName);
                $this->file->fileName = $oldFileName;
            }
            return $rename;
        }
        return true;
    }

    /**
     * Get feed URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->file->getLink();
    }

    /**
     * Get file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->file->getPath();
    }

    /**
     * Return HTML header according to the given format
     *
     * @return string
     */
    protected function getHtmlHeader()
    {
        switch ($this->format) {
            case self::FORMAT_CSV:
            default:
                return 'Content-Type: text/csv; charset=UTF-8';
            case self::FORMAT_XML:
                return 'Content-Type: application/xml; charset=UTF-8';
            case self::FORMAT_JSON:
                return 'Content-Type: application/json; charset=UTF-8';
            case self::FORMAT_YAML:
                return 'Content-Type: text/x-yaml; charset=UTF-8';
        }
    }

    /**
     * Format field names according to the given format
     *
     * @param string $str field name
     * @param string $format export format
     * @param boolean $legacy export legacy field or not
     *
     * @return string
     */
    public static function formatFields($str, $format, $legacy = false)
    {
        switch ($format) {
            case self::FORMAT_CSV:
                if ($legacy) {
                    return Tools::substr(
                        Tools::strtoupper(
                            preg_replace(
                                '/[^a-zA-Z0-9_]+/',
                                '',
                                str_replace(array(' ', '\''), '_', LengowMain::replaceAccentedChars($str))
                            )
                        ),
                        0,
                        58
                    );
                } else {
                    return Tools::substr(
                        Tools::strtolower(
                            preg_replace(
                                '/[^a-zA-Z0-9_]+/',
                                '',
                                str_replace(array(' ', '\''), '_', LengowMain::replaceAccentedChars($str))
                            )
                        ),
                        0,
                        58
                    );
                }
                break;
            default:
                return Tools::strtolower(
                    preg_replace(
                        '/[^a-zA-Z0-9_]+/',
                        '',
                        str_replace(array(' ', '\''), '_', LengowMain::replaceAccentedChars($str))
                    )
                );
        }
    }

    /**
     * For YAML, add spaces to have good indentation
     *
     * @param string $name the field name
     * @param string $maxSize space limit
     *
     * @return string
     */
    protected function indentYaml($name, $maxSize)
    {
        $strlen = Tools::strlen($name);
        $spaces = '';
        for ($i = $strlen; $i < $maxSize; $i++) {
            $spaces .= ' ';
        }
        return $spaces;
    }
}
