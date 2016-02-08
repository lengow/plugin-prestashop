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

class LengowCheck
{

    static private $_module = '';

    public static $DOM;

    private static $_FILES_TO_CHECK = array(
        'lengow.customer.class.php',
        'lengow.carrier.class.php',
        'lengow.feed.class.php',
        'lengow.file.class.php',
    );

    /**
     * Get header table
     *
     * @return string
     */
    private static function getAdminHeader()
    {
        return '<table class="table" cellpadding="0" cellspacing="0"><tbody>';
    }

    /**
     * Get HTML Table content of checklist
     *
     * @param array $checklist
     * @return string|null PS_MAIL_METHOD
     */
    private static function getAdminContent($checklist = array())
    {
        if (empty($checklist)) {
            return null;
        }

        $out = '';
        foreach ($checklist as $check) {
            $out .= '<tr>';
            $out .= '<td><b>' . $check['message'] . '</b></td>';
            if ($check['state'] == 1) {
                $out .= '<td><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'/img/admin/enabled.gif" alt="ok"></td>';
            } elseif ($check['state'] == 2) {
                $out .= '<td><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'/img/admin/error.png" alt="warning"></td>';
            } else {
                $out .= '<td><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'/img/admin/disabled.gif" alt="nok"></td>';
            }
            $out .= '</tr>';

            if ($check['state'] === 0 || $check['state'] === 2) {
                $out .= '<tr><td colspan="2"><p>' . $check['help'];
                if (array_key_exists('help_link', $check) && $check['help_link'] != '') {
                    $out .= '<br /><a target="_blank" href="'.$check['help_link'].'">'.$check['help_label'].'</a>';
                }
                $out .= '</p></td></tr>';
            }

            if (array_key_exists('additional_infos', $check)) {
                $out .= '<tr><td colspan="2"><p>';
                $out .= $check['additional_infos'];
                $out .= '</p></td></tr>';
            }
        }

        return $out;
    }

    /**
     * Get footer table
     *
     * @return string
     */
    private static function getAdminFooter()
    {
        return '</tbody></table>';
    }

    /**
     * Get mail configuration informations
     *
     * @return string
     */
    public static function getMailConfiguration()
    {
        $mail_method = Configuration::get('PS_MAIL_METHOD');
        if ($mail_method == 2) {
            return self::$_module->l('Email are enabled with custom settings.', 'lengow.check.class');
        } elseif ($mail_method == 3 && _PS_VERSION_ >= '1.5.0') {
            return self::$_module->l('Email are desactived.', 'lengow.check.class');
        } elseif ($mail_method == 3) {
            return self::$_module->l(
                'Error mail settings, PS_MAIL_METHOD is 3 but this value is not allowed in Prestashop 1.4',
                'lengow.check.class'
            );
        } else {
            return self::$_module->l('Email using php mail function.', 'lengow.check.class');
        }
    }

    /**
     * Check if PHP Curl is activated
     *
     * @return boolean
     */
    public static function isCurlActivated()
    {
        return function_exists('curl_version');
    }

    /**
     * Check if SimpleXML Extension is activated
     *
     * @return boolean
     */
    public static function isSimpleXMLActivated()
    {
        return function_exists('simplexml_load_file');
    }

    /**
     * Check if SimpleXML Extension is activated
     *
     * @return boolean
     */
    public static function isJsonActivated()
    {
        return function_exists('json_decode');
    }

    /**
     * Check if shop functionality are enabled
     *
     * @return boolean
     */
    public static function isShopActivated()
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            return false;
        }
        return true;
    }

    /**
    * Check API Authentification
    *
    * @param integer Shop ID
    *
    * @return boolean
    */
    public static function isValidAuth($id_shop = null)
    {
        if (LengowMain::inTest()) {
            return true;
        }

        if (!self::isCurlActivated()) {
            return false;
        }
        
        $account_id = (integer)LengowMain::getIdAccount($id_shop);
        $connector  = new LengowConnector(
            LengowMain::getAccessToken($id_shop),
            LengowMain::getSecretCustomer($id_shop)
        );
        $result = $connector->connect();
        if (isset($result['token']) && $account_id != 0 && is_integer($account_id)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get array of requirements and their status
     *
     * @return array
     */
    private static function getCheckListArray()
    {
        $checklist = array();

        self::$_module = new Lengow();

        $checklist[] = array(
            'message' => self::$_module->l('Lengow needs the CURL PHP extension', 'lengow.check.class'),
            'help' => self::$_module->l('The CURL extension is not installed or enabled in your PHP installation.Check the manual for information on how to install or enable CURL on your system.', 'lengow.check.class'),
            'help_link' => 'http://www.php.net/manual/en/curl.setup.php',
            'help_label' => self::$_module->l('Go to Curl PHP extension manual', 'lengow.check.class'),
            'state' => (int)self::isCurlActivated()
        );
        $checklist[] = array(
            'message' => self::$_module->l('Lengow needs the SimpleXML PHP extension', 'lengow.check.class'),
            'help' => self::$_module->l('The SimpleXML extension is not installed or enabled in your PHP installation. Check the manual for information on how to install or enable SimpleXML on your system.', 'lengow.check.class'),
            'help_link' => 'http://www.php.net/manual/en/book.simplexml.php',
            'help_label' => self::$_module->l('Go to SimpleXML PHP extension manual', 'lengow.check.class'),
            'state' => (int)self::isSimpleXMLActivated()
        );
        $checklist[] = array(
            'message' => self::$_module->l('Lengow needs the JSON PHP extension', 'lengow.check.class'),
            'help' => self::$_module->l('The JSON extension is not installed or enabled in your PHP installation. Check the manual for information on how to install or enable JSON on your system.', 'lengow.check.class'),
            'help_link' => 'http://www.php.net/manual/fr/book.json.php',
            'help_label' => self::$_module->l('Go to JSON PHP extension manual', 'lengow.check.class'),
            'state' => (int)self::isJsonActivated()
        );
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $checklist[] = array(
            'message' => self::$_module->l('Lengow authentification', 'lengow.check.class'),
            'help' => self::$_module->l('For this step, you need to have a Lengow account to get your Client ID, Group ID and API key.', 'lengow.check.class') . '<br/>'
                .self::$_module->l('Contact us if you don\'t have a Lengow account :', 'lengow.check.class') . '<br/>'
                .self::$_module->l('By email :', 'lengow.check.class').' <a href="mailto:'
                .self::$_module->l('contact@lengow.com', 'lengow.check.class').'" target="_blank">'
                .self::$_module->l('contact@lengow.com', 'lengow.check.class') . '</a><br/>'
                .self::$_module->l('By phone : +44 2033182631', 'lengow.check.class') . '<br/>'
                .self::$_module->l('Already a client :', 'lengow.check.class')
                .'<a href="https://solution.lengow.com/api/" target="_blank">'
                .self::$_module->l('go to Lengow dashboard', 'lengow.check.class'),
            'state' => (int)self::isValidAuth((int)Context::getContext()->shop->id) == 1 ? 1 : 0,
            'additional_infos' => sprintf(self::$_module->l(
                'Make sure your website IP (%s) address is filled in your Lengow Dashboard.',
                'lengow.check.class'
            ), $ip)
        );
        $checklist[] = array(
            'message' => self::$_module->l('Shop functionality', 'lengow.check.class'),
            'help' => self::$_module->l(
                'Shop functionality are disabled, order import will be impossible, please enable them in your products settings.',
                'lengow.check.class'
            ),
            'state' => (int)self::isShopActivated()
        );
        if (Configuration::get('LENGOW_IMPORT_PREPROD_ENABLED')) {
            $checklist[] = array(
                'message' => self::$_module->l(
                    'Mail configuration (Be careful, debug mode is activated)',
                    'lengow.check.class'
                ),
                'help' => self::getMailConfiguration(),
                'state' => 2
            );
        }
        return $checklist;
    }

    /**
     * Get admin table html
     *
     * @return string Html table
     */
    public static function getHtmlCheckList()
    {
        $out = '';
        $out .= self::getAdminHeader();
        $out .= self::getAdminContent(self::getCheckListArray());
        $out .= self::getAdminFooter();
        return $out;
    }

    /**
     * Get check list json
     *
     * @return string Json
     */
    public static function getJsonCheckList()
    {
        return Tools::jsonEncode(self::_getCheckListArray());
    }

//    /**
//     * Show import logs
//     *
//     * @return string Html Content
//     */
//    public static function getHtmlLogs($days = 10, $show_extra = false)
//    {
//        if (Tools::getValue('delete') != '') {
//            LengowLog::deleteLog(Tools::getValue('delete'));
//        }
//
//        $db = Db::getInstance();
//
//        $sql_logs = 'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_logs_import '
//            . ' WHERE TO_DAYS(NOW()) - TO_DAYS(date) <= ' . (int)$days
//            . ' ORDER BY `date` DESC';
//
//        $results = $db->ExecuteS($sql_logs);
//
//        echo '<style type="text/css">
//			table.gridtable {
//				font-family: verdana,arial,sans-serif;
//				font-size:11px;
//				color:#333333;
//				border-width: 1px;
//				border-color: #666666;
//				border-collapse: collapse;
//			}
//			table.gridtable th {
//				border-width: 1px;
//				padding: 8px;
//				border-style: solid;
//				border-color: #666666;
//				background-color: #dedede;
//			}
//			table.gridtable td {
//				border-width: 1px;
//				padding: 8px;
//				border-style: solid;
//				border-color: #666666;
//				background-color: #ffffff;
//			}
//			</style>';
//
//        if (!empty($results)) {
//            echo '<table class="gridtable">';
//            echo '<tr>';
//            echo '<th>Lengow Order ID</th>';
//            echo '<th>Is finished</th>';
//            echo '<th>Message</th>';
//            echo '<th>Date</th>';
//            echo '<th>Action</th>';
//            if ($show_extra == true) {
//                echo '<th>Extra</th>';
//            }
//            echo '</tr>';
//            foreach ($results as $row) {
//                echo '<tr>';
//                echo '<td>' . $row['lengow_order_id'] . '</td>';
//                echo '<td>' . ($row['is_finished'] == 1 ? 'Yes' : 'No') . '</td>';
//                echo '<td>' . $row['message'] . '</td>';
//                echo '<td>' . $row['date'] . '</td>';
//                if ($show_extra == true) {
//                    echo '<td>' . $row['extra'] . '</td>';
//                }
//                echo '<td><a href="?action=logs&delete=' . $row['lengow_order_id'] . '">Supprimer</a></td>';
//                echo '</tr>';
//            }
//            echo '</table>';
//        }
//    }
}
