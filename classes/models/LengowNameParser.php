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
 * Lengow Name Parser Class
 */
class LengowNameParser
{

    /**
     * Array of possible name languages.
     * @var array
     */
    private $languages;

    /**
     * Array of possible name titles.
     * @var array
     */
    private $titles;

    /**
     * Array of possible last name prefixes.
     * @var array
     */
    private $prefices;

    /**
     * Array of possible name suffices.
     * @var array;
     */
    private $suffices;

    /**
     * The TITLE ie. Dr., Mr. Mrs., etc...
     * @var string
     */
    private $title;

    /**
     * The FIRST Name
     * @var string
     */
    private $first;

    /**
     * The MIDDLE Name
     * @var string
     */
    private $middle;

    /**
     * The LAST Name
     * @var string
     */
    private $last;

    /**
     * Name addendum ie. III, Sr., etc...
     * @var string
     */
    private $suffix;

    /**
     * Full name string passed to class
     * @var string
     */
    private $fullName;

    /**
     * Set to false by default, but set to true if parse() is executed on a name that is not parseable
     * @var boolean
     */
    private $notParseable;

    /**
     * File name for config parser
     * @var string
     */
    public const FILE_PARSER = 'parser.json';

    /**
     * Constructor:
     * Setup the object, initialise the variables, and if instantiated with a name - parse it automagically
     *
     * @param string $initString The Name String
     *
     */
    public function __construct(string $initString = "")
    {
        $this->title = "";
        $this->first = "";
        $this->middle = "";
        $this->last = "";
        $this->suffix = "";

        $filePath = self::getParserFilePath();
        $paramsJson = Tools::file_get_contents($filePath);
        $params = json_decode($paramsJson, true);

        // added Military Titles
        $this->languages = $params["language"];
        $this->titles = $params["titles"];

        $this->prefices = $params["prefices"];
        $this->suffices = $params["suffices"];
        $this->fullName = "";
        $this->notParseable = false;

        // if initialized by value, set class variable and then parse
        if ($initString != "") {
            $this->fullName = $initString;
            $this->parse();
        }
    }

    /**
     * Destructor
     *
     */
    public function __destruct()
    {
    }

    /**
     * Access Method
     *
     */
    public function getFirstName(): string
    {
        return $this->first;
    }

    /**
     * Access Method
     *
     */
    public function getMiddleName(): string
    {
        return $this->middle;
    }

    /**
     * Access Method
     *
     */
    public function getLastName(): string
    {
        return $this->last;
    }

    /**
     * Access Method
     *
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Access Method
     *
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * Access Method
     *
     */
    public function getNotParseable(): bool
    {
        return $this->notParseable;
    }

    /**
     * Mutator Method
     * @param string $newFullName the new value to set fullName to
     */
    public function setFullName(string $newFullName): void
    {
        $this->fullName = $newFullName;
    }

    /**
     * Determine if the needle is in the haystack.
     *
     * @param mixed $needle the needle to look for
     * @param array $haystack the haystack from which to look into
     *
     */
    private function inArrayNorm($needle, array $haystack): bool
    {
        $needle = trim(strtolower(str_replace('.', '', $needle)));
        return in_array($needle, $haystack);
    }

    /**
     * Extract the elements of the full name into separate parts.
     *
     */
    public function parse(): void
    {
        // reset values
        $this->title = "";
        $this->first = "";
        $this->middle = "";
        $this->last = "";
        $this->suffix = "";
        $this->notParseable = false;

        // break up name based on number of commas
        $pieces = explode(',', preg_replace('/\s+/', ' ', trim($this->fullName)));
        $numPieces = count($pieces);

        switch ($numPieces) {

            // array(title first middle last suffix)
            case 1:
                $subPieces = explode(' ', trim($pieces[0]));
                $numSubPieces = count($subPieces);
                for ($i = 0; $i < $numSubPieces; $i++) {
                    $current = trim($subPieces[$i]);
                    if ($i < ($numSubPieces - 1)) {
                        $next = trim($subPieces[$i + 1]);
                    } else {
                        $next = "";
                    }
                    if ($i == 0 && $this->inArrayNorm($current, $this->titles)) {
                        $this->title = $current;
                        continue;
                    }
                    if ($this->first == "") {
                        $this->first = $current;
                        continue;
                    }
                    if ($i == $numSubPieces - 2 && ($next != "") && $this->inArrayNorm($next, $this->suffices)) {
                        if ($this->last != "") {
                            $this->last .= " " . $current;
                        } else {
                            $this->last = $current;
                        }
                        $this->suffix = $next;
                        break;
                    }
                    if ($i == $numSubPieces - 1) {
                        if ($this->last != "") {
                            $this->last .= " " . $current;
                        } else {
                            $this->last = $current;
                        }
                        continue;
                    }
                    if ($this->inArrayNorm($current, $this->prefices)) {
                        if ($this->last != "") {
                            $this->last .= " " . $current;
                        } else {
                            $this->last = $current;
                        }
                        continue;
                    }
                    if ($next == 'y' || $next == 'Y') {
                        if ($this->last != "") {
                            $this->last .= " " . $current;
                        } else {
                            $this->last = $current;
                        }
                        continue;
                    }
                    if ($this->last != "") {
                        $this->last .= " " . $current;
                        continue;
                    }
                    if ($this->middle != "") {
                        $this->middle .= " " . $current;
                    } else {
                        $this->middle = $current;
                    }
                }
                break;

            default:
                switch ($this->inArrayNorm($pieces[1], $this->suffices)) {

                    // array(title first middle last, suffix [, suffix])
                    case true:
                        $subPieces = explode(' ', trim($pieces[0]));
                        $numSubPieces = count($subPieces);
                        for ($i = 0; $i < $numSubPieces; $i++) {
                            $current = trim($subPieces[$i]);
                            if ($i < ($numSubPieces - 1)) {
                                $next = trim($subPieces[$i + 1]);
                            } else {
                                $next = "";
                            }
                            if ($i == 0 && $this->inArrayNorm($current, $this->titles)) {
                                $this->title = $current;
                                continue;
                            }
                            if ($this->first == "") {
                                $this->first = $current;
                                continue;
                            }
                            if ($i == $numSubPieces - 1) {
                                if ($this->last != "") {
                                    $this->last .= " " . $current;
                                } else {
                                    $this->last = $current;
                                }
                                continue;
                            }
                            if ($this->inArrayNorm($current, $this->prefices)) {
                                if ($this->last != "") {
                                    $this->last .= " " . $current;
                                } else {
                                    $this->last = $current;
                                }
                                continue;
                            }
                            if ($next == 'y' || $next == 'Y') {
                                if ($this->last != "") {
                                    $this->last .= " " . $current;
                                } else {
                                    $this->last = $current;
                                }
                                continue;
                            }
                            if ($this->last != "") {
                                $this->last .= " " . $current;
                                continue;
                            }
                            if ($this->middle != "") {
                                $this->middle .= " " . $current;
                            } else {
                                $this->middle = $current;
                            }
                        }
                        $this->suffix = trim($pieces[1]);
                        for ($i = 2; $i < $numPieces; $i++) {
                            $this->suffix .= ", " . trim($pieces[$i]);
                        }
                        break;

                    // array(last, title first middles[,] suffix [,suffix])
                    case false:
                        $subPieces = explode(' ', trim($pieces[1]));
                        $numSubPieces = count($subPieces);
                        for ($i = 0; $i < $numSubPieces; $i++) {
                            $current = trim($subPieces[$i]);
                            if ($i < ($numSubPieces - 1)) {
                                $next = trim($subPieces[$i + 1]);
                            } else {
                                $next = "";
                            }
                            if ($i == 0 && $this->inArrayNorm($current, $this->titles)) {
                                $this->title = $current;
                                continue;
                            }
                            if ($this->first == "") {
                                $this->first = $current;
                                continue;
                            }
                            if ($i == $numSubPieces - 2 && ($next != "") && $this->inArrayNorm($next, $this->suffices)) {
                                if ($this->middle != "") {
                                    $this->middle .= " " . $current;
                                } else {
                                    $this->middle = $current;
                                }
                                $this->suffix = $next;
                                break;
                            }
                            if ($i == $numSubPieces - 1 && $this->inArrayNorm($current, $this->suffices)) {
                                $this->suffix = $current;
                                continue;
                            }
                            if ($this->middle != "") {
                                $this->middle .= " " . $current;
                            } else {
                                $this->middle = $current;
                            }
                        }
                        if (isset($pieces[2]) && $pieces[2]) {
                            if ($this->last == "") {
                                $this->suffix = trim($pieces[2]);
                                for ($s = 3; $s < $numPieces; $s++) {
                                    $this->suffix .= ", " . trim($pieces[$s]);
                                }
                            } else {
                                for ($s = 2; $s < $numPieces; $s++) {
                                    $this->suffix .= ", " . trim($pieces[$s]);
                                }
                            }
                        }
                        $this->last = $pieces[0];
                        break;
                }
                unset($pieces);
                break;
        }
        if ($this->first == "" && $this->middle == "" && $this->last == "") {
            $this->notParseable = true;
        }
        $explodeMiddle = explode(' ', $this->middle);
        if (count($explodeMiddle) == 2
            && strrpos($this->middle, '.') === false) {
            $this->first .= " " . array_shift($explodeMiddle);
            $this->middle = reset($explodeMiddle);
        }
    }

    /**
     * Get parser.json path
     *
     */
    public static function getParserFilePath(): string
    {
        $sep = DIRECTORY_SEPARATOR;
        return LengowMain::getLengowFolder() . $sep . LengowMain::FOLDER_CONFIG . $sep . self::FILE_PARSER;
    }
}
