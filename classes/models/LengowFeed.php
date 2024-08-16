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
 * Lengow Feed Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowFeed
{
    /* Feed formats */
    public const FORMAT_CSV = 'csv';
    public const FORMAT_YAML = 'yaml';
    public const FORMAT_XML = 'xml';
    public const FORMAT_JSON = 'json';

    /* Content types */
    public const HEADER = 'header';
    public const BODY = 'body';
    public const FOOTER = 'footer';

    /**
     * @var string protection
     */
    public const PROTECTION = '"';

    /**
     * @var string CSV separator
     */
    public const CSV_SEPARATOR = '|';

    /**
     * @var string end of line
     */
    public const EOL = "\r\n";

    /**
     * @var LengowFile Lengow file instance
     */
    protected $file;

    /**
     * @var string feed content
     */
    protected $content = '';

    /**
     * @var bool stream or file
     */
    protected $stream;

    /**
     * @var string feed format
     */
    protected $format;

    /**
     * @var bool Use legacy fields
     */
    protected $legacy;

    /**
     * @var string|null export shop folder
     */
    protected $shopFolder;

    /**
     * @var string full export folder
     */
    protected $exportFolder;

    /**
     * @var array formats available for export
     */
    public static $availableFormats = [
        self::FORMAT_CSV,
        self::FORMAT_YAML,
        self::FORMAT_XML,
        self::FORMAT_JSON,
    ];

    /**
     * Construct
     *
     * @param bool $stream export streaming or in a file
     * @param string $format export format
     * @param bool $legacy export legacy field or not
     * @param string $shopName PrestaShop shop name
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
        $this->shopFolder = LengowMain::getShopNameCleaned($shopName);
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
        $this->exportFolder = LengowMain::FOLDER_EXPORT . $sep . $this->shopFolder;
        $folderPath = LengowMain::getLengowFolder() . $sep . $this->exportFolder;
        if (!file_exists($folderPath) && !mkdir($folderPath) && !is_dir($folderPath)) {
            throw new LengowException(LengowMain::setLogMessage('log.export.error_unable_to_create_folder', ['folder_path' => $folderPath]));
        }
        $fileName = 'flux-' . Context::getContext()->language->iso_code . '-' . time() . '.' . $this->format;
        $this->file = new LengowFile($this->exportFolder, $fileName);
    }

    /**
     * Write feed
     *
     * @param string $type data type (header, body or footer)
     * @param array $data export data
     * @param bool|null $isFirst is first product
     * @param bool|null $maxCharacter max characters for yaml format
     */
    public function write($type, $data = [], $isFirst = null, $maxCharacter = null)
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
            case self::FORMAT_CSV:
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
     * @param bool $isFirst is first product
     * @param int $maxCharacter max characters for yaml format
     *
     * @return string
     */
    public function getBody($data, $isFirst, $maxCharacter)
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
                $jsonArray = [];
                foreach ($data as $field => $value) {
                    $field = self::formatFields($field, self::FORMAT_JSON);
                    $jsonArray[$field] = $value;
                }
                $content .= json_encode($jsonArray);

                return $content;
            case self::FORMAT_YAML:
                if ($maxCharacter % 2 === 1) {
                    ++$maxCharacter;
                } else {
                    $maxCharacter += 2;
                }
                $content = '  ' . self::PROTECTION . 'product' . self::PROTECTION . ':' . self::EOL;
                foreach ($data as $field => $value) {
                    $field = self::formatFields($field, self::FORMAT_YAML);
                    $content .= '    ' . self::PROTECTION . $field . self::PROTECTION . ':';
                    $content .= $this->indentYaml($field, $maxCharacter) . $value . self::EOL;
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
     * @return bool
     *
     * @throws LengowException
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
     * @param bool $legacy export legacy field or not
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
                                str_replace([' ', '\''], '_', LengowMain::replaceAccentedChars($str))
                            )
                        ),
                        0,
                        58
                    );
                }

                return Tools::substr(
                    preg_replace(
                        '/[^a-zA-Z0-9_]+/',
                        '',
                        str_replace([' ', '\''], '_', LengowMain::replaceAccentedChars($str))
                    ),
                    0,
                    58
                );
            default:
                return preg_replace(
                    '/[^a-zA-Z0-9_]+/',
                    '',
                    str_replace([' ', '\''], '_', LengowMain::replaceAccentedChars($str))
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
        for ($i = $strlen; $i < $maxSize; ++$i) {
            $spaces .= ' ';
        }

        return $spaces;
    }
}
