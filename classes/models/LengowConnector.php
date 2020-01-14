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
 * Lengow Connector Class
 */
class LengowConnector
{
    /**
     * @var string url of the API Lengow
     */
    // const LENGOW_API_URL = 'https://api.lengow.io';
    // const LENGOW_API_URL = 'https://api.lengow.net';
    const LENGOW_API_URL = 'http://api.lengow.rec';
    // const LENGOW_API_URL = 'http://10.100.1.82:8081';

    /**
     * @var string url of access token API
     */
    const API_ACCESS_TOKEN = '/access/get_token';

    /**
     * @var string url of order API
     */
    const API_ORDER = '/v3.0/orders';

    /**
     * @var string url of order merchant order id API
     */
    const API_ORDER_MOI = '/v3.0/orders/moi/';

    /**
     * @var string url of order action API
     */
    const API_ORDER_ACTION = '/v3.0/orders/actions/';

    /**
     * @var string url of marketplace API
     */
    const API_MARKETPLACE = '/v3.0/marketplaces';

    /**
     * @var string url of plan API
     */
    const API_PLAN = '/v3.0/plans';

    /**
     * @var string url of statistic API
     */
    const API_STATISTIC = '/v3.0/stats';

    /**
     * @var string url of cms API
     */
    const API_CMS = '/v3.1/cms';

    /**
     * @var string request GET
     */
    const GET = 'GET';

    /**
     * @var string request POST
     */
    const POST = 'POST';

    /**
     * @var string request PUT
     */
    const PUT = 'PUT';

    /**
     * @var string request PATCH
     */
    const PATCH = 'PATCH';

    /**
     * @var string json format return
     */
    const FORMAT_JSON = 'json';

    /**
     * @var string stream format return
     */
    const FORMAT_STREAM = 'stream';

    /**
     * @var string success code
     */
    const CODE_200 = 200;

    /**
     * @var string forbidden access code
     */
    const CODE_403 = 403;

    /**
     * @var string error server code
     */
    const CODE_500 = 500;

    /**
     * @var string timeout server code
     */
    const CODE_504 = 504;

    /**
     * @var array|string fixture for test
     */
    public static $testFixturePath;

    /**
     * @var integer Authorization token lifetime
     */
    protected $tokenLifetime = 3000;

    /**
     * @var array default options for curl
     */
    protected $curlOpts = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'lengow-cms-prestashop',
    );

    /**
     * @var string the access token to connect
     */
    protected $accessToken;

    /**
     * @var string the secret to connect
     */
    protected $secret;

    /**
     * @var string temporary token for the authorization
     */
    protected $token;

    /**
     * @var array lengow url for curl timeout
     */
    protected $lengowUrls = array(
        self::API_ORDER => 20,
        self::API_ORDER_MOI => 10,
        self::API_ORDER_ACTION => 15,
        self::API_MARKETPLACE => 15,
        self::API_PLAN => 5,
        self::API_STATISTIC => 5,
        self::API_CMS => 5,
    );

    /**
     * Make a new Lengow API Connector.
     *
     * @param string $accessToken your access token
     * @param string $secret your secret
     */
    public function __construct($accessToken, $secret)
    {
        $this->accessToken = $accessToken;
        $this->secret = $secret;
    }

    /**
     * Check API Authentication
     *
     * @param boolean $logOutput see log or not
     *
     * @return boolean
     */
    public static function isValidAuth($logOutput = false)
    {
        if (LengowMain::inTest()) {
            return true;
        }
        if (!LengowCheck::isCurlActivated()) {
            return false;
        }
        list($accountId, $accessToken, $secretToken) = LengowConfiguration::getAccessIds();
        if ($accountId === null || $accountId == 0 || !is_numeric($accountId)) {
            return false;
        }
        $connector = new LengowConnector($accessToken, $secretToken);
        try {
            $connector->connect(false, $logOutput);
        } catch (LengowException $e) {
            $message = LengowMain::decodeLogMessage($e->getMessage(), 'en');
            $error = LengowMain::setLogMessage(
                'log.connector.error_api',
                array(
                    'error_code' => $e->getCode(),
                    'error_message' => $message,
                )
            );
            LengowMain::log('Connector', $error, $logOutput);
            return false;
        }
        return true;
    }

    /**
     * Get result for a query Api
     *
     * @param string $type request type (GET / POST / PUT / PATCH)
     * @param string $api request api
     * @param array $args request params
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @return mixed
     */
    public static function queryApi($type, $api, $args = array(), $body = '', $logOutput = false)
    {
        if (!in_array($type, array(self::GET, self::POST, self::PUT, self::PATCH))) {
            return false;
        }
        try {
            list($accountId, $accessToken, $secretToken) = LengowConfiguration::getAccessIds();
            if ($accountId === null) {
                return false;
            }
            $connector = new LengowConnector($accessToken, $secretToken);
            $type = Tools::strtolower($type);
            $results = $connector->$type(
                $api,
                array_merge(array('account_id' => $accountId), $args),
                self::FORMAT_STREAM,
                $body,
                $logOutput
            );
        } catch (LengowException $e) {
            $message = LengowMain::decodeLogMessage($e->getMessage(), 'en');
            $error = LengowMain::setLogMessage(
                'log.connector.error_api',
                array(
                    'error_code' => $e->getCode(),
                    'error_message' => $message,
                )
            );
            LengowMain::log('Connector', $error, $logOutput);

            return false;
        }

        return Tools::jsonDecode($results);
    }

    /**
     * Connection to the API
     *
     * @param boolean $force Force cache Update
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     */
    public function connect($force = false, $logOutput = false)
    {
        $token = LengowConfiguration::getGlobalValue('LENGOW_AUTH_TOKEN');
        $updatedAt = LengowConfiguration::getGlobalValue('LENGOW_LAST_AUTH_TOKEN_UPDATE');
        if (!$force
            && $token !== null
            && Tools::strlen($token) > 0
            && $updatedAt !== null
            && (time() - $updatedAt) < $this->tokenLifetime
        ) {
            $authorizationToken = $token;
        } else {
            $authorizationToken = $this->getAuthorizationToken($logOutput);
            LengowConfiguration::updateGlobalValue('LENGOW_AUTH_TOKEN', $authorizationToken);
            LengowConfiguration::updateGlobalValue('LENGOW_LAST_AUTH_TOKEN_UPDATE', time());
        }
        $this->token = $authorizationToken;
    }

    /**
     * Get API call
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    public function get($api, $args = array(), $format = self::FORMAT_JSON, $body = '', $logOutput = false)
    {
        if (LengowMain::inTest() && self::$testFixturePath) {
            if (is_array(self::$testFixturePath)) {
                $content = Tools::file_get_contents(self::$testFixturePath[0]);
                array_shift(self::$testFixturePath);
            } else {
                $content = Tools::file_get_contents(self::$testFixturePath);
                self::$testFixturePath = null;
            }
            return $content;
        }
        return $this->call($api, $args, self::GET, $format, $body, $logOutput);
    }

    /**
     * Post API call
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    public function post($api, $args = array(), $format = self::FORMAT_JSON, $body = '', $logOutput = false)
    {
        if (LengowMain::inTest() && self::$testFixturePath) {
            if (is_array(self::$testFixturePath)) {
                $content = Tools::file_get_contents(self::$testFixturePath[0]);
                array_shift(self::$testFixturePath);
            } else {
                $content = Tools::file_get_contents(self::$testFixturePath);
                self::$testFixturePath = null;
            }
            return $content;
        }
        return $this->call($api, $args, self::POST, $format, $body, $logOutput);
    }

    /**
     * Put API call
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    public function put($api, $args = array(), $format = self::FORMAT_JSON, $body = '', $logOutput = false)
    {
        return $this->call($api, $args, self::PUT, $format, $body, $logOutput);
    }

    /**
     * Patch API call
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    public function patch($api, $args = array(), $format = self::FORMAT_JSON, $body = '', $logOutput = false)
    {
        return $this->call($api, $args, self::PATCH, $format, $body, $logOutput);
    }

    /**
     * The API method
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $type type of request GET|POST|PUT|PATCH
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    private function call($api, $args, $type, $format, $body, $logOutput)
    {
        try {
            $this->connect(false, $logOutput);
            $data = $this->callAction($api, $args, $type, $format, $body, $logOutput);
        } catch (LengowException $e) {
            if ($e->getCode() === self::CODE_403) {
                LengowMain::log(
                    'Connector',
                    LengowMain::setLogMessage('log.connector.retry_get_token'),
                    $logOutput
                );
                $this->connect(true, $logOutput);
                $data = $this->callAction($api, $args, $type, $format, $body, $logOutput);
            } else {
                throw new LengowException($e->getMessage(), $e->getCode());
            }
        }
        return $data;
    }

    /**
     * Call API action
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $type type of request GET|POST|PUT|PATCH
     * @param string $format return format of API
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    private function callAction($api, $args, $type, $format, $body, $logOutput)
    {
        $result = $this->makeRequest($type, $api, $args, $this->token, $body, $logOutput);
        return $this->format($result, $format);
    }

    /**
     * Get authorization token from Middleware
     *
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return string
     */
    private function getAuthorizationToken($logOutput)
    {
        $data = $this->callAction(
            self::API_ACCESS_TOKEN,
            array(
                'access_token' => $this->accessToken,
                'secret' => $this->secret,
            ),
            self::POST,
            self::FORMAT_JSON,
            '',
            $logOutput
        );
        // return a specific error for get_token
        if (!isset($data['token'])) {
            throw new LengowException(
                LengowMain::setLogMessage('log.connector.token_not_return'),
                self::CODE_500
            );
        } elseif (Tools::strlen($data['token']) === 0) {
            throw new LengowException(
                LengowMain::setLogMessage('log.connector.token_is_empty'),
                self::CODE_500
            );
        }
        return $data['token'];
    }

    /**
     * Make Curl request
     *
     * @param string $type type of request GET|POST|PUT|PATCH
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $token temporary authorization token
     * @param string $body body data for request
     * @param boolean $logOutput see log or not
     *
     * @throws LengowException
     *
     * @return mixed
     */
    private function makeRequest($type, $api, $args, $token, $body, $logOutput)
    {
        // define CURLE_OPERATION_TIMEDOUT for old php versions
        defined('CURLE_OPERATION_TIMEDOUT') || define('CURLE_OPERATION_TIMEDOUT', CURLE_OPERATION_TIMEOUTED);
        $ch = curl_init();
        // define generic Curl options
        $opts = $this->curlOpts;
        // get special timeout for specific Lengow API
        if (array_key_exists($api, $this->lengowUrls)) {
            $opts[CURLOPT_TIMEOUT] = $this->lengowUrls[$api];
        }
        // get url for a specific environment
        $url = self::LENGOW_API_URL . $api;
        $opts[CURLOPT_CUSTOMREQUEST] = Tools::strtoupper($type);
        $url = parse_url($url);
        if (isset($url['port'])) {
            $opts[CURLOPT_PORT] = $url['port'];
        }
        $opts[CURLOPT_HEADER] = false;
        $opts[CURLOPT_VERBOSE] = false;
        if (isset($token)) {
            $opts[CURLOPT_HTTPHEADER] = array('Authorization: ' . $token);
        }
        $url = $url['scheme'] . '://' . $url['host'] . $url['path'];
        switch ($type) {
            case self::GET:
                $opts[CURLOPT_URL] = $url . (!empty($args) ? '?' . http_build_query($args) : '');
                break;
            case self::PUT:
                if (isset($token)) {
                    $opts[CURLOPT_HTTPHEADER] = array_merge(
                        $opts[CURLOPT_HTTPHEADER],
                        array(
                            'Content-Type: application/json',
                            'Content-Length: ' . Tools::strlen($body),
                        )
                    );
                }
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($args);
                $opts[CURLOPT_POSTFIELDS] = $body;
                break;
            case self::PATCH:
                if (isset($token)) {
                    $opts[CURLOPT_HTTPHEADER] = array_merge(
                        $opts[CURLOPT_HTTPHEADER],
                        array('Content-Type: application/json')
                    );
                }
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = count($args);
                $opts[CURLOPT_POSTFIELDS] = Tools::jsonEncode($args);
                break;
            default:
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = count($args);
                $opts[CURLOPT_POSTFIELDS] = http_build_query($args);
                break;
        }
        LengowMain::log(
            'Connector',
            LengowMain::setLogMessage(
                'log.connector.call_api',
                array(
                    'call_type' => $type,
                    'curl_url' => $opts[CURLOPT_URL],
                )
            ),
            $logOutput
        );
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrorNumber = curl_errno($ch);
        curl_close($ch);
        $this->checkReturnRequest($result, $httpCode, $curlError, $curlErrorNumber);
        return $result;
    }

    /**
     * Check return request and generate exception if needed
     *
     * @param string $result Curl return call
     * @param integer $httpCode request http code
     * @param string $curlError Curl error
     * @param string $curlErrorNumber Curl error number
     *
     * @throws LengowException
     *
     */
    private function checkReturnRequest($result, $httpCode, $curlError, $curlErrorNumber)
    {
        if ($result === false) {
            // recovery of Curl errors
            if (in_array($curlErrorNumber, array(CURLE_OPERATION_TIMEDOUT, CURLE_OPERATION_TIMEOUTED))) {
                throw new LengowException(
                    LengowMain::setLogMessage('log.connector.timeout_api'),
                    self::CODE_504
                );
            } else {
                $error = LengowMain::setLogMessage(
                    'log.connector.error_curl',
                    array(
                        'error_code' => $curlErrorNumber,
                        'error_message' => $curlError,
                    )
                );
                throw new LengowException($error, self::CODE_500);
            }
        } else {
            if ($httpCode !== self::CODE_200) {
                $result = $this->format($result);
                // recovery of Lengow Api errors
                if (isset($result['error'])) {
                    throw new LengowException($result['error']['message'], $httpCode);
                } else {
                    throw new LengowException(
                        LengowMain::setLogMessage('log.connector.api_not_available'),
                        $httpCode
                    );
                }
            }
        }
    }

    /**
     * Get data in specific format
     *
     * @param mixed $data Curl response data
     * @param string $format return format of API
     *
     * @return mixed
     */
    private function format($data, $format = self::FORMAT_JSON)
    {
        switch ($format) {
            case self::FORMAT_STREAM:
                return $data;
            default:
            case self::FORMAT_JSON:
                return Tools::jsonDecode($data, true);
        }
    }
}
