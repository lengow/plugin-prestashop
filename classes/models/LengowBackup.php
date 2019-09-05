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
 * Lengow Backup Class
 */
class LengowBackup extends Backup
{
    /**
     * Creates a new backup file
     *
     * @throws Exception
     *
     * @return boolean
     */
    public function add()
    {
        if (!$this->psBackupAll) {
            $ignoreInsertTable = array(
                _DB_PREFIX_ . 'connections',
                _DB_PREFIX_ . 'connections_page',
                _DB_PREFIX_ . 'connections_source',
                _DB_PREFIX_ . 'guest',
                _DB_PREFIX_ . 'statssearch',
            );
        } else {
            $ignoreInsertTable = array();
        }
        // generate some random number, to make it extra hard to guess backup file names
        $rand = dechex(mt_rand(0, min(0xffffffff, mt_getrandmax())));
        $date = time();
        $backupfile = $this->getRealBackupPath() . $date . '-lengowbackup' . $rand . '.sql';
        // figure out what compression is available and open the file
        if (function_exists('bzopen')) {
            $backupfile .= '.bz2';
            $fp = @bzopen($backupfile, 'w');
        } elseif (function_exists('gzopen')) {
            $backupfile .= '.gz';
            $fp = @gzopen($backupfile, 'w');
        } else {
            $fp = @fopen($backupfile, 'w');
        }
        if ($fp === false) {
            echo Tools::displayError('Unable to create backup file') . ' "' . addslashes($backupfile) . '"';
            return false;
        }
        $this->id = realpath($backupfile);
        fwrite(
            $fp,
            '/* Backup for ' . Tools::getHttpHost(false, false) . __PS_BASE_URI__ . "\n * at " . date($date) . "\n */\n"
        );
        fwrite($fp, "\n" . 'SET NAMES \'utf8\';' . "\n\n");
        $found = 0;
        foreach (LengowInstall::$tables as $table) {
            $table = _DB_PREFIX_ . $table;
            // export the table schema
            // this line is required by Prestashop validator
            $sql = str_replace('IF NOT EXISTS', '', 'SHOW CREATE TABLE IF NOT EXISTS');
            try {
                $schema = Db::getInstance()->executeS($sql . '`' . $table . '`');
            } catch (PrestaShopDatabaseException $e) {
                return false;
            }
            if (count($schema) !== 1 || !isset($schema[0]['Table']) || !isset($schema[0]['Create Table'])) {
                fclose($fp);
                $this->delete();
                echo Tools::displayError('An error occurred while backing up. Unable to obtain the schema of')
                    . ' "' . $table;
                return false;
            }
            fwrite($fp, '/* Scheme for table ' . $schema[0]['Table'] . " */\n");
            fwrite($fp, $schema[0]['Create Table'] . ";\n\n");
            if (!in_array($schema[0]['Table'], $ignoreInsertTable)) {
                try {
                    $data = Db::getInstance()->executeS('SELECT * FROM `' . $schema[0]['Table'] . '`', false);
                } catch (PrestaShopDatabaseException $e) {
                    return false;
                }
                $sizeof = DB::getInstance()->NumRows();
                $lines = explode("\n", $schema[0]['Create Table']);

                if ($data && $sizeof > 0) {
                    // export the table data
                    fwrite($fp, 'INSERT INTO `' . $schema[0]['Table'] . "` VALUES\n");
                    $i = 1;
                    while ($row = DB::getInstance()->nextRow($data)) {
                        $s = '(';
                        foreach ($row as $field => $value) {
                            $tmp = "'" . pSQL($value, true) . "',";
                            if ($tmp !== "'',") {
                                $s .= $tmp;
                            } else {
                                foreach ($lines as $line) {
                                    if (strpos($line, '`' . $field . '`') !== false) {
                                        if (preg_match('/(.*NOT NULL.*)/Ui', $line)) {
                                            $s .= "'',";
                                        } else {
                                            $s .= 'NULL,';
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                        $s = rtrim($s, ',');
                        if ($i % 200 === 0 && $i < $sizeof) {
                            $s .= ");\nINSERT INTO `" . $schema[0]['Table'] . "` VALUES\n";
                        } elseif ($i < $sizeof) {
                            $s .= "),\n";
                        } else {
                            $s .= ");\n";
                        }
                        fwrite($fp, $s);
                        ++$i;
                    }
                }
            }
            $found++;
        }
        fclose($fp);
        if ($found === 0) {
            $this->delete();
            echo Tools::displayError('No valid tables were found to backup.');
            return false;
        }
        return true;
    }
}
