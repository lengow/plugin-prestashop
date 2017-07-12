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
 * Lengow Main Class
 */
class LengowMain
{
    /**
     * @var LengowLog Lengow log file instance
     */
    public static $log;

    /**
     * @var array marketplace registers
     */
    public static $registers;

    /**
     * @var integer life of log files in days
     */
    public static $logLife = 20;

    /**
     * @var array product ids available to track products
     */
    public static $trackerChoiceId = array(
        'id' => 'Product ID',
        'ean' => 'Product EAN',
        'upc' => 'Product UPC',
        'ref' => 'Product Reference',
    );

    /**
     * @var array Lengow Authorized IPs
     */
    protected static $ipsLengow = array(
        '127.0.0.1',
        '10.0.4.150',
        '46.19.183.204',
        '46.19.183.218',
        '46.19.183.222',
        '89.107.175.172',
        '89.107.175.186',
        '90.63.241.226',
        '109.190.189.175',
        '185.61.176.129',
        '185.61.176.130',
        '185.61.176.131',
        '185.61.176.132',
        '185.61.176.133',
        '185.61.176.134',
        '185.61.176.137',
        '185.61.176.138',
        '185.61.176.139',
        '185.61.176.140',
        '185.61.176.141',
        '185.61.176.142',
    );

    /**
     * The Prestashop compare version with current version.
     *
     * @param string $version the version to compare
     *
     * @return boolean
     */
    public static function compareVersion($version = '1.4')
    {
        $subVersion = Tools::substr(_PS_VERSION_, 0, 3);
        return version_compare($subVersion, $version);
    }

    /**
     * Get lengow folder path
     *
     * @return string
     */
    public static function getLengowFolder()
    {
        return _PS_MODULE_DIR_ . 'lengow';
    }

    /**
     * Get Lengow ID Account
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return integer
     */
    public static function getIdAccount($idShop = null)
    {
        return LengowConfiguration::get('LENGOW_ACCOUNT_ID', null, null, $idShop);
    }

    /**
     * Get access token
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return string
     */
    public static function getAccessToken($idShop = null)
    {
        return LengowConfiguration::get('LENGOW_ACCESS_TOKEN', null, null, $idShop);
    }

    /**
     * Get the secret
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return string
     */
    public static function getSecretCustomer($idShop = null)
    {
        return LengowConfiguration::get('LENGOW_SECRET_TOKEN', null, null, $idShop);
    }

    /**
     * Recovers if a shop is active or not
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return string
     */
    public static function getShopActive($idShop = null)
    {
        return LengowConfiguration::get('LENGOW_SHOP_ACTIVE', null, null, $idShop);
    }

    /**
     * Get the matching Prestashop order state id to the one given
     *
     * @param string $state state to be matched
     *
     * @return integer
     */
    public static function getOrderState($state)
    {
        switch ($state) {
            case 'accepted':
            case 'waiting_shipment':
                return LengowConfiguration::getGlobalValue('LENGOW_ORDER_ID_PROCESS');
            case 'shipped':
            case 'closed':
                return LengowConfiguration::getGlobalValue('LENGOW_ORDER_ID_SHIPPED');
            case 'refused':
            case 'canceled':
                return LengowConfiguration::getGlobalValue('LENGOW_ORDER_ID_CANCEL');
            case 'shippedByMp':
                return LengowConfiguration::getGlobalValue('LENGOW_ORDER_ID_SHIPPEDBYMP');
        }
        return false;
    }

    /**
     * Temporary disable mail sending
     */
    public static function disableMail()
    {
        if (_PS_VERSION_ < '1.5.4.0') {
            Configuration::set('PS_MAIL_METHOD', 2);
            // Set fictive stmp server to disable mail
            Configuration::set('PS_MAIL_SERVER', 'smtp.lengow.com');
        } else {
            Configuration::set('PS_MAIL_METHOD', 3);
        }
    }

    /**
     * Record the date of the last import
     *
     * @param string $type last import type (cron or manual)
     *
     * @return boolean
     */
    public static function updateDateImport($type)
    {
        if ($type === 'cron') {
            LengowConfiguration::updateGlobalValue('LENGOW_LAST_IMPORT_CRON', time());
        } else {
            LengowConfiguration::updateGlobalValue('LENGOW_LAST_IMPORT_MANUAL', time());
        }
    }

    /**
     * Get last import (type and timestamp)
     *
     * @return array
     */
    public static function getLastImport()
    {
        $timestampCron = LengowConfiguration::getGlobalValue('LENGOW_LAST_IMPORT_CRON');
        $timestampManual = LengowConfiguration::getGlobalValue('LENGOW_LAST_IMPORT_MANUAL');
        if ($timestampCron && $timestampManual) {
            if ((int)$timestampCron > (int)$timestampManual) {
                return array('type' => 'cron', 'timestamp' => (int)$timestampCron);
            } else {
                return array('type' => 'manual', 'timestamp' => (int)$timestampManual);
            }
        } elseif ($timestampCron && !$timestampManual) {
            return array('type' => 'cron', 'timestamp' => (int)$timestampCron);
        } elseif ($timestampManual && !$timestampCron) {
            return array('type' => 'manual', 'timestamp' => (int)$timestampManual);
        }
        return array('type' => 'none', 'timestamp' => 'none');
    }

    /**
     * Get marketplace singleton
     *
     * @param string $name marketplace name
     * @param integer $idShop Prestashop shop id
     *
     * @return LengowMarketplace
     */
    public static function getMarketplaceSingleton($name, $idShop = null)
    {
        if (!isset(self::$registers[$name])) {
            self::$registers[$name] = new LengowMarketplace($name, $idShop);
        }
        return self::$registers[$name];
    }

    /**
     * Clean html
     *
     * @param string $html the html content
     *
     * @return string
     */
    public static function cleanHtml($html)
    {
        $string = str_replace('<br />', ' ', nl2br($html));
        $string = trim(strip_tags(htmlspecialchars_decode($string)));
        $string = preg_replace('`[\s]+`sim', ' ', $string);
        $string = preg_replace('`"`sim', '', $string);
        $string = nl2br($string);
        $pattern = '@<[\/\!]*?[^<>]*?>@si';
        $string = preg_replace($pattern, ' ', $string);
        $string = preg_replace('/[\s]+/', ' ', $string);
        $string = trim($string);
        $string = str_replace('&nbsp;', ' ', $string);
        $string = str_replace('|', ' ', $string);
        $string = str_replace('"', '\'', $string);
        $string = str_replace('’', '\'', $string);
        $string = str_replace('&#39;', '\' ', $string);
        $string = str_replace('&#150;', '-', $string);
        $string = str_replace(chr(9), ' ', $string);
        $string = str_replace(chr(10), ' ', $string);
        $string = str_replace(chr(13), ' ', $string);
        return $string;
    }

    /**
     * Format float
     *
     * @param float $float the float to format
     *
     * @return float
     */
    public static function formatNumber($float)
    {
        return number_format(round($float, 2), 2, '.', '');
    }

    /**
     * Get host for generated email
     *
     * @return string Hostname
     */
    public static function getHost()
    {
        $domain = defined('_PS_SHOP_DOMAIN_') ? _PS_SHOP_DOMAIN_ : _PS_BASE_URL_;
        preg_match('`([a-zàâäéèêëôöùûüîïç0-9-]+\.[a-z]+)`', $domain, $out);
        if ($out[1]) {
            return $out[1];
        }
        return $domain;
    }

    /**
     * Check webservice access (export and import)
     *
     * @param string $token shop token
     * @param integer $idShop Prestashop shop id
     *
     * @return boolean
     */
    public static function checkWebservicesAccess($token, $idShop = null)
    {
        if (self::checkToken($token, $idShop)) {
            return true;
        }
        if (self::checkIP()) {
            return true;
        }
        return false;
    }

    /**
     * Check if token is correct
     *
     * @param string $token shop token
     * @param integer $idShop Prestashop shop id
     *
     * @return boolean
     */
    public static function checkToken($token, $idShop = null)
    {
        $storeToken = self::getToken($idShop);
        if ($token == $storeToken) {
            return true;
        }
        return false;
    }

    /**
     * Generate token
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return array
     */
    public static function getToken($idShop = null)
    {
        if (is_null($idShop)) {
            $token = LengowConfiguration::getGlobalValue('LENGOW_GLOBAL_TOKEN');
            if ($token && Tools::strlen($token) > 0) {
                return $token;
            } else {
                $token = bin2hex(openssl_random_pseudo_bytes(16));
                LengowConfiguration::updateGlobalValue('LENGOW_GLOBAL_TOKEN', $token);
            }
        } else {
            $token = LengowConfiguration::get('LENGOW_SHOP_TOKEN', null, null, $idShop);
            if ($token && Tools::strlen($token) > 0) {
                return $token;
            } else {
                $token = bin2hex(openssl_random_pseudo_bytes(16));
                LengowConfiguration::updateValue('LENGOW_SHOP_TOKEN', $token, null, null, $idShop);
            }
        }
        return $token;
    }

    /**
     * Check if current IP is authorized
     *
     * @return boolean
     */
    public static function checkIP()
    {
        $ips = LengowConfiguration::getGlobalValue('LENGOW_AUTHORIZED_IP');
        $ips = trim(str_replace(array("\r\n", ',', '-', '|', ' '), ';', $ips), ';');
        $ips = array_filter(explode(';', $ips));
        $authorizedIps = count($ips) > 0 ? array_merge($ips, self::$ipsLengow) : self::$ipsLengow;
        if (!self::inTest()) {
            $authorizedIps[] = $_SERVER['SERVER_ADDR'];
        }
        $hostnameIp = $_SERVER['REMOTE_ADDR'];
        if (in_array($hostnameIp, $authorizedIps)) {
            return true;
        }
        return false;
    }

    /**
     * Check if we are in phpunit test
     *
     * @return boolean
     */
    public static function inTest()
    {
        if (defined('PS_UNIT_TEST')) {
            return true;
        }
        if (isset($_SERVER['HTTP_USER_AGENT']) && Tools::substr($_SERVER['HTTP_USER_AGENT'], 0, 10) == 'GuzzleHttp') {
            return true;
        }
        return false;
    }

    /**
     * Writes log
     *
     * @param string $category log category
     * @param string $txt log message
     * @param boolean $logOutput output on screen
     * @param string $marketplaceSku Lengow marketplace sku
     */
    public static function log($category, $txt, $logOutput = false, $marketplaceSku = null)
    {
        $log = self::getLogInstance();
        $log->write($category, $txt, $logOutput, $marketplaceSku);
    }

    /**
     * Set message with params for translation
     *
     * @param string $key log key
     * @param array $params log parameters
     *
     * @return string
     */
    public static function setLogMessage($key, $params = null)
    {
        if (is_null($params) || (is_array($params) && count($params) == 0)) {
            return $key;
        }
        $allParams = array();
        foreach ($params as $param => $value) {
            $value = str_replace(array('|', '=='), array('', ''), $value);
            $allParams[] = $param . '==' . $value;
        }
        $message = $key . '[' . join('|', $allParams) . ']';
        return $message;
    }

    /**
     * Decode message with params for translation
     *
     * @param string $message log message
     * @param string $isoCode iso code for translation
     * @param mixed $params log parameters
     *
     * @return string
     */
    public static function decodeLogMessage($message, $isoCode = null, $params = null)
    {
        if (preg_match('/^(([a-z\_]*\.){1,3}[a-z\_]*)(\[(.*)\]|)$/', $message, $result)) {
            if (isset($result[1])) {
                $key = $result[1];
                if (isset($result[4]) && is_null($params)) {
                    $strParam = $result[4];
                    $allParams = explode('|', $strParam);
                    foreach ($allParams as $param) {
                        $result = explode('==', $param);
                        $params[$result[0]] = $result[1];
                    }
                }
                $locale = new LengowTranslation();
                $message = $locale->t($key, $params, $isoCode);
            }
        }
        return $message;
    }

    /**
     * Suppress log files when too old
     */
    public static function cleanLog()
    {
        $logFiles = LengowLog::getFiles();
        $days = array();
        $days[] = 'logs-' . date('Y-m-d') . '.txt';
        for ($i = 1; $i < self::$logLife; $i++) {
            $days[] = 'logs-' . date('Y-m-d', strtotime('-' . $i . 'day')) . '.txt';
        }
        if (empty($logFiles)) {
            return;
        }
        foreach ($logFiles as $log) {
            if (!in_array($log->fileName, $days)) {
                $log->delete();
            }
        }
    }

    /**
     * Clean data
     *
     * @param string $value the content
     *
     * @return string
     */
    public static function cleanData($value)
    {
        $value = preg_replace(
            '/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' .
            '|[\x00-\x7F][\x80-\xBF]+' .
            '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' .
            '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' .
            '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
            '',
            $value
        );
        $value = preg_replace(
            '/\xE0[\x80-\x9F][\x80-\xBF]' .
            '|\xED[\xA0-\xBF][\x80-\xBF]/S',
            '',
            $value
        );
        $value = preg_replace('/[\s]+/', ' ', $value);
        $value = trim($value);
        $value = str_replace(
            array(
                '&nbsp;',
                '|',
                '"',
                '’',
                '&#39;',
                '&#150;',
                chr(9),
                chr(10),
                chr(13),
                chr(31),
                chr(30),
                chr(29),
                chr(28),
                "\n",
                "\r"
            ),
            array(
                ' ',
                ' ',
                '\'',
                '\'',
                ' ',
                '-',
                ' ',
                ' ',
                ' ',
                '',
                '',
                '',
                '',
                '',
                ''
            ),
            $value
        );
        return $value;
    }

    /**
     * Clean phone number
     *
     * @param string $phone Phone to clean
     *
     * @return string
     */
    public static function cleanPhone($phone)
    {
        $replace = array('.', ' ', '-', '/');
        if (!$phone) {
            return null;
        }
        if (Validate::isPhoneNumber($phone)) {
            return str_replace($replace, '', $phone);
        } else {
            return str_replace($replace, '', preg_replace('/[^0-9]*/', '', $phone));
        }
    }

    /**
     * Replace all accented chars by their equivalent non accented chars.
     *
     * @param string $str string to have its characters replaced
     *
     * @return string
     */
    public static function replaceAccentedChars($str)
    {
        /* One source among others:
          http://www.tachyonsoft.com/uc0000.htm
          http://www.tachyonsoft.com/uc0001.htm
        */
        $patterns = array(
            /* Lowercase */
            /* a */
            '/[\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}\x{0101}\x{0103}\x{0105}]/u',
            /* c */
            '/[\x{00E7}\x{0107}\x{0109}\x{010D}]/u',
            /* d */
            '/[\x{010F}\x{0111}]/u',
            /* e */
            '/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{0113}\x{0115}\x{0117}\x{0119}\x{011B}]/u',
            /* g */
            '/[\x{011F}\x{0121}\x{0123}]/u',
            /* h */
            '/[\x{0125}\x{0127}]/u',
            /* i */
            '/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}\x{0129}\x{012B}\x{012D}\x{012F}\x{0131}]/u',
            /* j */
            '/[\x{0135}]/u',
            /* k */
            '/[\x{0137}\x{0138}]/u',
            /* l */
            '/[\x{013A}\x{013C}\x{013E}\x{0140}\x{0142}]/u',
            /* n */
            '/[\x{00F1}\x{0144}\x{0146}\x{0148}\x{0149}\x{014B}]/u',
            /* o */
            '/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}\x{014D}\x{014F}\x{0151}]/u',
            /* r */
            '/[\x{0155}\x{0157}\x{0159}]/u',
            /* s */
            '/[\x{015B}\x{015D}\x{015F}\x{0161}]/u',
            /* ss */
            '/[\x{00DF}]/u',
            /* t */
            '/[\x{0163}\x{0165}\x{0167}]/u',
            /* u */
            '/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{0169}\x{016B}\x{016D}\x{016F}\x{0171}\x{0173}]/u',
            /* w */
            '/[\x{0175}]/u',
            /* y */
            '/[\x{00FF}\x{0177}\x{00FD}]/u',
            /* z */
            '/[\x{017A}\x{017C}\x{017E}]/u',
            /* ae */
            '/[\x{00E6}]/u',
            /* oe */
            '/[\x{0153}]/u',
            /* Uppercase */
            /* A */
            '/[\x{0100}\x{0102}\x{0104}\x{00C0}\x{00C1}\x{00C2}\x{00C3}\x{00C4}\x{00C5}]/u',
            /* C */
            '/[\x{00C7}\x{0106}\x{0108}\x{010A}\x{010C}]/u',
            /* D */
            '/[\x{010E}\x{0110}]/u',
            /* E */
            '/[\x{00C8}\x{00C9}\x{00CA}\x{00CB}\x{0112}\x{0114}\x{0116}\x{0118}\x{011A}]/u',
            /* G */
            '/[\x{011C}\x{011E}\x{0120}\x{0122}]/u',
            /* H */
            '/[\x{0124}\x{0126}]/u',
            /* I */
            '/[\x{0128}\x{012A}\x{012C}\x{012E}\x{0130}]/u',
            /* J */
            '/[\x{0134}]/u',
            /* K */
            '/[\x{0136}]/u',
            /* L */
            '/[\x{0139}\x{013B}\x{013D}\x{0139}\x{0141}]/u',
            /* N */
            '/[\x{00D1}\x{0143}\x{0145}\x{0147}\x{014A}]/u',
            /* O */
            '/[\x{00D3}\x{014C}\x{014E}\x{0150}]/u',
            /* R */
            '/[\x{0154}\x{0156}\x{0158}]/u',
            /* S */
            '/[\x{015A}\x{015C}\x{015E}\x{0160}]/u',
            /* T */
            '/[\x{0162}\x{0164}\x{0166}]/u',
            /* U */
            '/[\x{00D9}\x{00DA}\x{00DB}\x{00DC}\x{0168}\x{016A}\x{016C}\x{016E}\x{0170}\x{0172}]/u',
            /* W */
            '/[\x{0174}]/u',
            /* Y */
            '/[\x{0176}]/u',
            /* Z */
            '/[\x{0179}\x{017B}\x{017D}]/u',
            /* AE */
            '/[\x{00C6}]/u',
            /* OE */
            '/[\x{0152}]/u'
        );
        // ö to oe
        // å to aa
        // ä to ae
        $replacements = array(
            'a',
            'c',
            'd',
            'e',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'n',
            'o',
            'r',
            's',
            'ss',
            't',
            'u',
            'y',
            'w',
            'z',
            'ae',
            'oe',
            'A',
            'C',
            'D',
            'E',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'N',
            'O',
            'R',
            'S',
            'T',
            'U',
            'Z',
            'AE',
            'OE'
        );
        return preg_replace($patterns, $replacements, $str);
    }

    /**
     * Check logs table and send mail for order not imported correctly
     *
     * @param boolean $logOutput see log or not
     *
     * @return mixed
     */
    public static function sendMailAlert($logOutput = false)
    {
        $cookie = Context::getContext()->cookie;
        $subject = 'Lengow imports logs';
        $mailBody = '';
        $sqlLogs = 'SELECT lo.`marketplace_sku`, lli.`message`, lli.`id`
            FROM `' . _DB_PREFIX_ . 'lengow_logs_import` lli
            INNER JOIN `' . _DB_PREFIX_ . 'lengow_orders` lo 
            ON lli.`id_order_lengow` = lo.`id`
            WHERE lli.`is_finished` = 0 AND lli.`mail` = 0
        ';
        $logs = Db::getInstance()->ExecuteS($sqlLogs);
        if (empty($logs)) {
            return true;
        }
        foreach ($logs as $log) {
            $mailBody .= '<li>' . self::decodeLogMessage(
                'lengow_log.mail_report.order',
                null,
                array('marketplace_sku' => $log['marketplace_sku'])
            );
            if ($log['message'] != '') {
                $mailBody .= ' - ' . self::decodeLogMessage($log['message']);
            } else {
                $mailBody .= ' - ' . self::decodeLogMessage('lengow_log.mail_report.no_error_in_report_mail');
            }
            $mailBody .= '</li>';
            self::logSent($log['id']);
        }
        $datas = array(
            '{mail_title}' => 'Lengow imports logs',
            '{mail_body}' => $mailBody,
        );
        $emails = LengowConfiguration::getReportEmailAddress();
        foreach ($emails as $to) {
            if (!Mail::send(
                (int)$cookie->id_lang,
                'report',
                $subject,
                $datas,
                $to,
                null,
                null,
                null,
                null,
                null,
                _PS_MODULE_DIR_ . 'lengow/views/templates/mails/',
                true
            )
            ) {
                self::log(
                    'MailReport',
                    self::setLogMessage('log.mail_report.unable_send_mail_to', array('emails' => $to)),
                    $logOutput
                );
            } else {
                self::log(
                    'MailReport',
                    self::setLogMessage('log.mail_report.send_mail_to', array('emails' => $to)),
                    $logOutput
                );
            }
        }
    }

    /**
     * Mark log as sent by email
     *
     * @param integer $idOrderLog Lengow order log id
     */
    public static function logSent($idOrderLog)
    {
        if (_PS_VERSION_ < '1.5') {
            Db::getInstance()->autoExecute(
                _DB_PREFIX_ . 'lengow_logs_import',
                array('mail' => 1),
                'UPDATE',
                '`id` = \'' . (int)$idOrderLog . '\'',
                1
            );
        } else {
            Db::getInstance()->update(
                'lengow_logs_import',
                array('mail' => 1),
                '`id` = \'' . (int)$idOrderLog . '\'',
                1
            );
        }
    }

    /**
     * Check if a given module is installed and active
     *
     * @param string $moduleName name of module
     *
     * @return boolean
     */
    public static function isModuleInstalled($moduleName)
    {
        if (!Module::isInstalled($moduleName)) {
            return false;
        }
        if (_PS_VERSION_ >= '1.5') {
            if (!Module::isEnabled($moduleName)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if Mondial Relay is installed, active and if version is supported
     *
     * @return boolean true if installed and active
     */
    public static function isMondialRelayAvailable()
    {
        $moduleName = 'mondialrelay';
        $supportedVersion = '2.1.0';
        $sep = DIRECTORY_SEPARATOR;
        $moduleDir = _PS_MODULE_DIR_ . $moduleName . $sep;
        if (!self::isModuleInstalled($moduleName)) {
            return false;
        }
        require_once($moduleDir . $moduleName . '.php');
        $mr = new MondialRelay();
        if (version_compare($mr->version, $supportedVersion, '>=')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check is soColissimo is installed, activated and if version is supported
     *
     * @return boolean true if installed and active
     */
    public static function isSoColissimoAvailable()
    {
        $moduleName = 'socolissimo';
        $supportedVersion = '2.8.5';
        $sep = DIRECTORY_SEPARATOR;
        $moduleDir = _PS_MODULE_DIR_ . $moduleName . $sep;
        if (!self::isModuleInstalled($moduleName)) {
            return false;
        }
        require_once($moduleDir . $moduleName . '.php');
        $soColissimo = new Socolissimo();
        if (version_compare($soColissimo->version, $supportedVersion, '>=')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get prestashop state id corresponding to the current order state
     *
     * @param string $orderStateMarketplace order state marketplace
     * @param LengowMarketplace $marketplace Lengow marketplace instance
     * @param boolean $shipmentByMp order shipped by marketplace
     *
     * @return integer
     */
    public static function getPrestashopStateId($orderStateMarketplace, $marketplace, $shipmentByMp)
    {
        if ($shipmentByMp) {
            $orderState = 'shippedByMp';
        } elseif ($marketplace->getStateLengow($orderStateMarketplace) == 'shipped'
            || $marketplace->getStateLengow($orderStateMarketplace) == 'closed'
        ) {
            $orderState = 'shipped';
        } else {
            $orderState = 'accepted';
        }
        return self::getOrderState($orderState);
    }

    /**
     * Get order state list
     *
     * @param integer $idLang Prestashop lang id
     *
     * @return array
     */
    public static function getOrderStates($idLang)
    {
        $states = OrderState::getOrderStates($idLang);
        $idStateLengow = self::getLengowErrorStateId();
        $index = 0;
        foreach ($states as $state) {
            if ($state['id_order_state'] == $idStateLengow) {
                unset($states[$index]);
            }
            $index++;
        }
        return $states;
    }

    /**
     * Get log Instance
     *
     * @return LengowLog
     */
    public static function getLogInstance()
    {
        if (is_null(self::$log)) {
            self::$log = new LengowLog();
        }
        return self::$log;
    }

    /**
     * Get webservices links
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return string
     */
    public static function getExportUrl($idShop = null)
    {
        $base = self::getLengowBaseUrl($idShop);
        return $base . 'webservice/export.php?token=' . self::getToken($idShop);
    }

    /**
     * Get webservices links
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return string
     */
    public static function getImportUrl($idShop = null)
    {
        $base = self::getLengowBaseUrl($idShop);
        return $base . 'webservice/cron.php?token=' . self::getToken();
    }

    /**
     * Get base url for Lengow webservice and files
     *
     * @param integer $idShop Prestashop shop id
     *
     * @return string
     */
    public static function getLengowBaseUrl($idShop = null)
    {
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '';
        if (_PS_VERSION_ < '1.5') {
            $base = defined('_PS_SHOP_DOMAIN_') ? 'http' . $isHttps . '://' . _PS_SHOP_DOMAIN_ : _PS_BASE_URL_;
            $base .= __PS_BASE_URI__;
        } else {
            $idShop = is_null($idShop) ? Context::getContext()->shop->id : $idShop;
            $shopUrl = new ShopUrl($idShop);
            $base = 'http' . $isHttps . '://' . $shopUrl->domain . $shopUrl->physical_uri . $shopUrl->virtual_uri;
        }
        return $base . 'modules/lengow/';
    }

    /**
     * Get Lengow technical error state id
     *
     * @param integer $idLang Prestashop lang id
     *
     * @return integer|false
     */
    public static function getLengowErrorStateId($idLang = null)
    {
        $idErrorState = LengowConfiguration::getGlobalValue('LENGOW_STATE_ERROR');
        if ($idErrorState) {
            return $idErrorState;
        }
        if (_PS_VERSION_ >= '1.5') {
            if (!$idLang) {
                $idLang = Context::getContext()->language->id;
            }
            $states = OrderState::getOrderStates($idLang);
            foreach ($states as $state) {
                if ($state['module_name'] == 'lengow') {
                    return $state['id_order_state'];
                }
            }
        } else {
            $states = Db::getInstance()->ExecuteS(
                'SELECT * FROM ' . _DB_PREFIX_ . 'order_state_lang
                WHERE name = \'Technical error - Lengow\' OR name = \'Erreur technique - Lengow\' LIMIT 1'
            );
            if (!empty($states)) {
                return $states[0]['id_order_state'];
            }
        }
        return false;
    }

    /**
     * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name)
     *
     * @param string $str string in camel case format
     *
     * @return string
     */
    public function fromCamelCase($str)
    {
        $str[0] = Tools::strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
     *
     * @param string $str string in underscore format
     * @param boolean $capitaliseFirstChar if true, capitalise the first char in $str
     *
     * @return string
     */
    public function toCamelCase($str, $capitaliseFirstChar = false)
    {
        if ($capitaliseFirstChar) {
            $str[0] = Tools::strtoupper($str[0]);
        }
        $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $str);
    }

    /**
     * Check if is a new merchant
     *
     * @return boolean
     */
    public static function isNewMerchant()
    {
        $shops = LengowShop::findAll(true);
        foreach ($shops as $shop) {
            $accountId = LengowConfiguration::get('LENGOW_ACCOUNT_ID', false, null, $shop['id_shop']);
            if (Tools::strlen($accountId) > 0) {
                return false;
            }
        }
        return true;
    }
}
