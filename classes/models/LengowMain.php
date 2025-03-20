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
/*
 * Lengow Main Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowMain
{
    /* Lengow plugin folders */
    public const FOLDER_CONFIG = 'config';
    public const FOLDER_EXPORT = 'export';
    public const FOLDER_LENGOW = 'lengow';
    public const FOLDER_LOG = 'logs';
    public const FOLDER_TRANSLATION = 'translations';
    public const FOLDER_WEBSERVICE = 'webservice';

    /* Lengow webservices */
    public const WEBSERVICE_EXPORT = 'export.php';
    public const WEBSERVICE_CRON = 'cron.php';
    public const WEBSERVICE_TOOLBOX = 'toolbox.php';

    /* Date formats */
    public const DATE_FULL = 'Y-m-d H:i:s';
    public const DATE_DAY = 'Y-m-d';
    public const DATE_ISO_8601 = 'c';

    /**
     * @var int life of log files in days
     */
    public const LOG_LIFE = 20;

    /**
     * @var LengowLog Lengow log file instance
     */
    public static $log;

    /**
     * @var array marketplace registers
     */
    public static $registers;

    /**
     * @var array product ids available to track products
     */
    public static $trackerChoiceId = [
        'id' => 'Product ID',
        'ean' => 'Product EAN',
        'upc' => 'Product UPC',
        'ref' => 'Product Reference',
    ];

    /**
     * @var array Lengow Authorized IPs
     */
    protected static $ipsLengow = [
        '127.0.0.1',
        '10.0.4.150',
        '46.19.183.204',
        '46.19.183.217',
        '46.19.183.218',
        '46.19.183.219',
        '46.19.183.222',
        '52.50.58.130',
        '89.107.175.172',
        '89.107.175.185',
        '89.107.175.186',
        '89.107.175.187',
        '90.63.241.226',
        '109.190.189.175',
        '146.185.41.180',
        '146.185.41.177',
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
    ];

    /**
     * @var array PrestaShop mail configuration
     */
    protected static $mailConfigurations = [];

    /**
     * The PrestaShop compare version with current version.
     *
     * @param string $version the version to compare
     *
     * @return bool
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
        return _PS_MODULE_DIR_ . self::FOLDER_LENGOW;
    }

    /**
     * Get the matching PrestaShop order state id to the one given
     *
     * @param string $state state to be matched
     *
     * @return int
     */
    public static function getOrderState($state)
    {
        switch ($state) {
            case LengowOrder::STATE_ACCEPTED:
            case LengowOrder::STATE_WAITING_SHIPMENT:
            case LengowOrder::STATE_PARTIALLY_REFUNDED:
                return (int) LengowConfiguration::getGlobalValue(LengowConfiguration::WAITING_SHIPMENT_ORDER_ID);
            case LengowOrder::STATE_SHIPPED:
            case LengowOrder::STATE_CLOSED:
                return (int) LengowConfiguration::getGlobalValue(LengowConfiguration::SHIPPED_ORDER_ID);
            case LengowOrder::STATE_REFUSED:
            case LengowOrder::STATE_CANCELED:
                return (int) LengowConfiguration::getGlobalValue(LengowConfiguration::CANCELED_ORDER_ID);
            case 'shippedByMp':
                return (int) LengowConfiguration::getGlobalValue(LengowConfiguration::SHIPPED_BY_MARKETPLACE_ORDER_ID);
        }

        return false;
    }

    /**
     * Temporary enable mail sending
     */
    public static function enableMail()
    {
        if (isset(self::$mailConfigurations['method'])) {
            Configuration::set('PS_MAIL_METHOD', (int) self::$mailConfigurations['method']);
        }
    }

    /**
     * Temporary disable mail sending
     */
    public static function disableMail()
    {
        self::$mailConfigurations = [
            'method' => Configuration::get('PS_MAIL_METHOD'),
            'server' => Configuration::get('PS_MAIL_SERVER'),
        ];
        Configuration::set('PS_MAIL_METHOD', 3);
    }

    /**
     * Record the date of the last import
     *
     * @param string $type last import type (cron or manual)
     */
    public static function updateDateImport($type)
    {
        if ($type === LengowImport::TYPE_CRON) {
            LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_CRON_SYNCHRONIZATION, time());
        } else {
            LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_MANUAL_SYNCHRONIZATION, time());
        }
    }

    /**
     * Get last import (type and timestamp)
     *
     * @return array
     */
    public static function getLastImport()
    {
        $timestampCron = LengowConfiguration::getGlobalValue(LengowConfiguration::LAST_UPDATE_CRON_SYNCHRONIZATION);
        $timestampManual = LengowConfiguration::getGlobalValue(LengowConfiguration::LAST_UPDATE_MANUAL_SYNCHRONIZATION);
        if ($timestampCron && $timestampManual) {
            if ((int) $timestampCron > (int) $timestampManual) {
                return [
                    'type' => LengowImport::TYPE_CRON,
                    'timestamp' => (int) $timestampCron,
                ];
            }

            return [
                'type' => LengowImport::TYPE_MANUAL,
                'timestamp' => (int) $timestampManual,
            ];
        }
        if ($timestampCron && !$timestampManual) {
            return [
                'type' => LengowImport::TYPE_CRON,
                'timestamp' => (int) $timestampCron,
            ];
        }
        if ($timestampManual && !$timestampCron) {
            return [
                'type' => LengowImport::TYPE_MANUAL,
                'timestamp' => (int) $timestampManual,
            ];
        }

        return ['type' => 'none', 'timestamp' => 'none'];
    }

    /**
     * Get date in local date
     *
     * @param int $timestamp linux timestamp
     * @param bool $second see seconds or not
     *
     * @return string
     */
    public static function getDateInCorrectFormat($timestamp, $second = false)
    {
        if ($second) {
            $format = 'l d F Y @ H:i:s';
        } else {
            $format = 'l d F Y @ H:i';
        }

        return date($format, $timestamp);
    }

    /**
     * Get marketplace singleton
     *
     * @param string $name marketplace name
     *
     * @return LengowMarketplace|null
     *
     * @throws LengowException
     */
    public static function getMarketplaceSingleton($name)
    {
        if (empty($name)) {
            return null;
        }
        try {
            if (!isset(self::$registers[$name])) {
                self::$registers[$name] = new LengowMarketplace($name);
            }

            return self::$registers[$name];
        } catch (LengowException $e) {
            self::log(LengowLog::CODE_ACTION, $e->getMessage());
        }

        return null;
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

        return str_replace(
            ['&nbsp;', '|', '"', '’', '&#39;', '&#150;', chr(9), chr(10), chr(13)],
            [' ', ' ', '\'', '\'', '\' ', '-', ' ', ' ', ' '],
            $string
        );
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
     * @param int|null $idShop PrestaShop shop id
     *
     * @return bool
     */
    public static function checkWebservicesAccess($token, $idShop = null)
    {
        if (!(bool) LengowConfiguration::get(LengowConfiguration::AUTHORIZED_IP_ENABLED)
            && self::checkToken($token, $idShop)
        ) {
            return true;
        }
        if (self::checkIp()) {
            return true;
        }

        return false;
    }

    /**
     * Check if token is correct
     *
     * @param string $token shop token
     * @param int|null $idShop PrestaShop shop id
     *
     * @return bool
     */
    public static function checkToken($token, $idShop = null)
    {
        $storeToken = self::getToken($idShop);

        return $token === $storeToken;
    }

    /**
     * Generate token
     *
     * @param int|null $idShop PrestaShop shop id
     *
     * @return string
     */
    public static function getToken($idShop = null)
    {
        if ($idShop === null) {
            $token = LengowConfiguration::getGlobalValue(LengowConfiguration::CMS_TOKEN);
            if ($token && Tools::strlen($token) > 0) {
                return $token;
            }
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            LengowConfiguration::updateGlobalValue(LengowConfiguration::CMS_TOKEN, $token);
        } else {
            $token = LengowConfiguration::get(LengowConfiguration::SHOP_TOKEN, null, null, $idShop);
            if ($token && Tools::strlen($token) > 0) {
                return $token;
            }
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            LengowConfiguration::updateValue(LengowConfiguration::SHOP_TOKEN, $token, null, null, $idShop);
        }

        return $token;
    }

    /**
     * Check if current IP is authorized
     *
     * @return bool
     */
    public static function checkIp()
    {
        $authorizedIps = array_merge(LengowConfiguration::getAuthorizedIps(), self::$ipsLengow);
        if (isset($_SERVER['SERVER_ADDR'])) {
            $authorizedIps[] = $_SERVER['SERVER_ADDR'];
        }

        return in_array($_SERVER['REMOTE_ADDR'], $authorizedIps, true);
    }

    /**
     * Writes log
     *
     * @param string $category log category
     * @param string $txt log message
     * @param bool $logOutput output on screen
     * @param string|null $marketplaceSku Lengow marketplace sku
     */
    public static function log($category, $txt, $logOutput = false, $marketplaceSku = null)
    {
        $log = self::getLogInstance();
        if ($log) {
            $log->write($category, $txt, $logOutput, $marketplaceSku);
        }
    }

    /**
     * Set message with params for translation
     *
     * @param string $key log key
     * @param array|null $params log parameters
     *
     * @return string
     */
    public static function setLogMessage($key, $params = null)
    {
        if ($params === null || (is_array($params) && empty($params))) {
            return $key;
        }
        $allParams = [];
        foreach ($params as $param => $value) {
            $value = str_replace(['|', '=='], ['', ''], $value);
            $allParams[] = $param . '==' . $value;
        }

        return $key . '[' . implode('|', $allParams) . ']';
    }

    /**
     * Decode message with params for translation
     *
     * @param string $message log message
     * @param string|null $isoCode iso code for translation
     * @param array|null $params log parameters
     *
     * @return string
     */
    public static function decodeLogMessage($message, $isoCode = null, $params = null)
    {
        if (preg_match('/^(([a-z\_]*\.){1,3}[a-z\_]*)(\[(.*)\]|)$/', $message, $result)) {
            if (isset($result[1])) {
                $key = $result[1];
                if (isset($result[4]) && $params === null) {
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
        $days = [];
        $days[] = 'logs-' . date(self::DATE_DAY) . '.txt';
        for ($i = 1; $i < self::LOG_LIFE; ++$i) {
            $days[] = 'logs-' . date(self::DATE_DAY, strtotime('-' . $i . 'day')) . '.txt';
        }
        /** @var LengowFile[] $logFiles */
        $logFiles = LengowLog::getFiles();
        if (empty($logFiles)) {
            return;
        }
        foreach ($logFiles as $log) {
            if (!in_array($log->fileName, $days, true)) {
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

        return str_replace(
            [
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
                "\r",
            ],
            [
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
                '',
            ],
            $value
        );
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
        $replace = ['.', ' ', '-', '/'];
        if (!$phone) {
            return null;
        }
        if (Validate::isPhoneNumber($phone)) {
            return str_replace($replace, '', $phone);
        }

        return str_replace($replace, '', preg_replace('/[^0-9]*/', '', $phone));
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
        $patterns = [
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
            '/[\x{0152}]/u',
        ];
        // ö to oe
        // å to aa
        // ä to ae
        $replacements = [
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
            'OE',
        ];

        return preg_replace($patterns, $replacements, $str);
    }

    /**
     * Check logs table and send mail for order not imported correctly
     *
     * @param bool $logOutput see log or not
     *
     * @return bool
     */
    public static function sendMailAlert($logOutput = false)
    {
        $success = true;
        // recovery of all errors not yet sent by email
        $orderLogs = LengowOrderError::getAllOrderLogsNotSent();
        if (!empty($orderLogs)) {
            // construction of the report e-mail
            $mailBody = '';
            foreach ($orderLogs as $log) {
                $mailBody .= '<li>' . self::decodeLogMessage(
                    'lengow_log.mail_report.order',
                    null,
                    ['marketplace_sku' => $log['marketplace_sku']]
                );
                if ($log[LengowOrderError::FIELD_MESSAGE] !== '') {
                    $mailBody .= ' - ' . self::decodeLogMessage($log[LengowOrderError::FIELD_MESSAGE]);
                } else {
                    $pluginLinks = LengowSync::getPluginLinks();
                    $mailBody .= ' - ' . self::decodeLogMessage(
                        'lengow_log.mail_report.no_error_in_report_mail',
                        null,
                        ['support_link' => $pluginLinks[LengowSync::LINK_TYPE_SUPPORT]]
                    );
                }
                $mailBody .= '</li>';
                LengowOrderError::logSent((int) $log[LengowOrderError::FIELD_ID]);
            }
            $subject = 'Lengow imports logs';
            $data = [
                '{mail_title}' => $subject,
                '{mail_body}' => $mailBody,
            ];
            // send an email if the template exists for the locale
            $emails = LengowConfiguration::getReportEmailAddress();
            $idLang = (int) Context::getContext()->cookie->id_lang;
            $iso = Language::getIsoById($idLang);
            if (file_exists(_PS_MODULE_DIR_ . 'lengow/mails/' . $iso . '/report.txt')
                && file_exists(_PS_MODULE_DIR_ . 'lengow/mails/' . $iso . '/report.html')
            ) {
                foreach ($emails as $to) {
                    $mailSent = Mail::send(
                        $idLang,
                        'report',
                        $subject,
                        $data,
                        $to,
                        null,
                        null,
                        null,
                        null,
                        null,
                        _PS_MODULE_DIR_ . 'lengow/mails/',
                        true
                    );
                    if (!$mailSent) {
                        self::log(
                            LengowLog::CODE_MAIL_REPORT,
                            self::setLogMessage('log.mail_report.unable_send_mail_to', ['emails' => $to]),
                            $logOutput
                        );
                        $success = false;
                    } else {
                        self::log(
                            LengowLog::CODE_MAIL_REPORT,
                            self::setLogMessage('log.mail_report.send_mail_to', ['emails' => $to]),
                            $logOutput
                        );
                        $success = true;
                    }
                }
            } else {
                self::log(
                    LengowLog::CODE_MAIL_REPORT,
                    self::setLogMessage('log.mail_report.template_not_exist', ['iso_code' => $iso]),
                    $logOutput
                );
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Check if a given module is installed and active
     *
     * @param string $moduleName name of module
     *
     * @return bool
     */
    public static function isModuleInstalled($moduleName)
    {
        return Module::isInstalled($moduleName) && Module::isEnabled($moduleName);
    }

    /**
     * Check if Mondial Relay v2 is installed, active and if version is supported
     *
     * @return bool
     */
    public static function isMondialRelayV2Available()
    {
        return self::isMondialRelayVersionAvailable('2.1.0', '3.0.0');
    }

    private static function isMondialRelayVersionAvailable($minVersion, $maxVersion)
    {
        $moduleName = 'mondialrelay';
        $sep = DIRECTORY_SEPARATOR;
        $moduleDir = _PS_MODULE_DIR_ . $moduleName . $sep;
        if (!self::isModuleInstalled($moduleName)) {
            return false;
        }
        require_once $moduleDir . $moduleName . '.php';
        $mr = new MondialRelay();

        return version_compare($mr->version, $minVersion, '>=')
            && version_compare($mr->version, $maxVersion, '<');
    }

    /**
     * Check if Mondial Relay v3 is installed, active and if version is supported
     *
     * @return bool
     */
    public static function isMondialRelayV3Available()
    {
        return self::isMondialRelayVersionAvailable('3.0.0', '4.0.0');
    }

    /**
     * Check is soColissimo is installed, activated and if version is supported
     *
     * @return bool
     */
    public static function isSoColissimoAvailable()
    {
        $moduleName = _PS_VERSION_ < '1.7' ? 'socolissimo' : 'colissimo_simplicite';
        $supportedVersion = '2.8.5';
        $sep = DIRECTORY_SEPARATOR;
        $moduleDir = _PS_MODULE_DIR_ . $moduleName . $sep;
        if (!self::isModuleInstalled($moduleName)) {
            return false;
        }
        require_once $moduleDir . $moduleName . '.php';
        $soColissimo = _PS_VERSION_ < '1.7' ? new Socolissimo() : new Colissimo_simplicite();
        if (version_compare($soColissimo->version, $supportedVersion, '>=')) {
            return true;
        }

        return false;
    }

    /**
     * Get prestashop state id corresponding to the current order state
     *
     * @param string $orderStateMarketplace order state marketplace
     * @param LengowMarketplace $marketplace Lengow marketplace instance
     * @param bool $shipmentByMp order shipped by marketplace
     *
     * @return int
     */
    public static function getPrestashopStateId($orderStateMarketplace, $marketplace, $shipmentByMp)
    {
        if ($shipmentByMp) {
            $orderState = 'shippedByMp';
        } elseif ($marketplace->getStateLengow($orderStateMarketplace) === LengowOrder::STATE_SHIPPED
            || $marketplace->getStateLengow($orderStateMarketplace) === LengowOrder::STATE_CLOSED
        ) {
            $orderState = LengowOrder::STATE_SHIPPED;
        } else {
            $orderState = LengowOrder::STATE_ACCEPTED;
        }

        return self::getOrderState($orderState);
    }

    /**
     * Get order state list
     *
     * @param int $idLang PrestaShop lang id
     *
     * @return array
     */
    public static function getOrderStates($idLang)
    {
        $states = OrderState::getOrderStates($idLang);
        $idStateLengow = self::getLengowErrorStateId();
        $index = 0;
        foreach ($states as $state) {
            if ((int) $state['id_order_state'] === $idStateLengow) {
                unset($states[$index]);
            }
            ++$index;
        }

        return $states;
    }

    /**
     * Get log Instance
     *
     * @return LengowLog|false
     */
    public static function getLogInstance()
    {
        if (self::$log === null) {
            try {
                self::$log = new LengowLog();
            } catch (LengowException $e) {
                return false;
            }
        }

        return self::$log;
    }

    /**
     * Get export webservice links
     *
     * @param int|null $idShop PrestaShop shop id
     *
     * @return string
     */
    public static function getExportUrl($idShop = null)
    {
        $sep = DIRECTORY_SEPARATOR;

        return self::getLengowBaseUrl($idShop) . self::FOLDER_WEBSERVICE . $sep . self::WEBSERVICE_EXPORT . '?'
            . LengowExport::PARAM_TOKEN . '=' . self::getToken($idShop);
    }

    /**
     * Get cron webservice links
     *
     * @return string
     */
    public static function getCronUrl()
    {
        $sep = DIRECTORY_SEPARATOR;

        return self::getLengowBaseUrl() . self::FOLDER_WEBSERVICE . $sep . self::WEBSERVICE_CRON . '?'
            . LengowImport::PARAM_TOKEN . '=' . self::getToken();
    }

    /**
     * Get toolbox webservice links
     *
     * @return string
     */
    public static function getToolboxUrl()
    {
        $sep = DIRECTORY_SEPARATOR;

        return self::getLengowBaseUrl() . self::FOLDER_WEBSERVICE . $sep . self::WEBSERVICE_TOOLBOX . '?'
            . LengowToolbox::PARAM_TOKEN . '=' . self::getToken();
    }

    /**
     * Get base url for Lengow webservice and files
     *
     * @param int|null $idShop PrestaShop shop id
     *
     * @return string
     */
    public static function getLengowBaseUrl($idShop = null)
    {
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '';
        try {
            $idShop = $idShop === null ? Context::getContext()->shop->id : $idShop;
            $shopUrl = self::getMainShopUrl($idShop);
            $base = 'http' . $isHttps . '://' . $shopUrl->domain . $shopUrl->physical_uri . $shopUrl->virtual_uri;
        } catch (Exception $e) {
            $base = _PS_BASE_URL_ . __PS_BASE_URI__;
        }

        return $base . 'modules/lengow/';
    }

    /**
     * Get main shop url for a specific shop
     *
     * @param int $idShop PrestaShop shop id
     *
     * @return ShopUrl
     *
     * @throws Exception
     */
    public static function getMainShopUrl($idShop)
    {
        $shopUrls = ShopUrl::getShopUrls($idShop);
        /** @var ShopUrl[] $shopUrls */
        foreach ($shopUrls as $shopUrl) {
            if ($shopUrl->main) {
                return $shopUrl;
            }
        }

        return new ShopUrl($idShop);
    }

    /**
     * Get cleaned shop name for shop export folder
     *
     * @param string $shopName PrestaShop shop name
     *
     * @return string
     */
    public static function getShopNameCleaned($shopName)
    {
        return Tools::strtolower(
            preg_replace(
                '/[^a-zA-Z0-9_]+/',
                '',
                str_replace([' ', '\''], '_', self::replaceAccentedChars($shopName))
            )
        );
    }

    /**
     * Get Lengow technical error state id
     *
     * @param int|null $idLang PrestaShop lang id
     *
     * @return int|null
     */
    public static function getLengowErrorStateId($idLang = null)
    {
        $idErrorState = LengowConfiguration::getGlobalValue(LengowConfiguration::LENGOW_ERROR_STATE_ID);
        if ($idErrorState) {
            return (int) $idErrorState;
        }
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }
        $states = OrderState::getOrderStates($idLang);
        foreach ($states as $state) {
            if ($state['module_name'] === 'lengow') {
                return (int) $state['id_order_state'];
            }
        }

        return null;
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

        return preg_replace_callback(
            '/([A-Z])/',
            static function ($c) {
                return '_' . Tools::strtolower($c[1]);
            },
            $str
        );
    }
}
