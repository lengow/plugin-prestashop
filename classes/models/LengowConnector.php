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
     * @var string url of the SANDBOX Lengow
     */
    const LENGOW_API_SANDBOX_URL = 'https://api.lengow.net';

    /**
     * @var array|string fixture for test
     */
    public static $testFixturePath;

    /**
     * @var array default options for curl
     */
    public static $curlOpts = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'lengow-php-sdk',
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
        '/v3.0/orders' => 20,
        '/v3.0/orders/moi/' => 10,
        '/v3.0/orders/actions/' => 15,
        '/v3.0/marketplaces' => 15,
        '/v3.0/plans' => 5,
        '/v3.0/stats' => 3,
        '/v3.1/cms' => 5,
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
     * Connection to the API
     *
     * @throws LengowException get Curl error
     *
     * @return array|false
     */
    public function connect()
    {
        $data = $this->callAction(
            '/access/get_token',
            array(
                'access_token' => $this->accessToken,
                'secret' => $this->secret
            ),
            'POST'
        );
        if (isset($data['token'])) {
            $this->token = $data['token'];
            return $data;
        } else {
            return false;
        }
    }

    /**
     * The API method
     *
     * @param string $method Lengow method API call
     * @param array $array Lengow method API parameters
     * @param string $type type of request GET|POST|PUT|HEAD|DELETE|PATCH
     * @param string $format return format of API
     * @param string $body body datas for request
     *
     * @throws LengowException get Curl error
     *
     * @return mixed
     */
    public function call($method, $array = array(), $type = 'GET', $format = 'json', $body = '')
    {
        $this->connect();
        try {
            $data = $this->callAction($method, $array, $type, $format, $body);
        } catch (LengowException $e) {
            return $e->getMessage();
        }
        return $data;
    }

    /**
     * Get API call
     *
     * @param string $method Lengow method API call
     * @param array $array Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body datas for request
     *
     * @throws LengowException get Curl error
     *
     * @return mixed
     */
    public function get($method, $array = array(), $format = 'json', $body = '')
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
        return $this->call($method, $array, 'GET', $format, $body);
    }

    /**
     * Post API call
     *
     * @param string $method Lengow method API call
     * @param array $array Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body datas for request
     *
     * @throws LengowException get Curl error
     *
     * @return mixed
     */
    public function post($method, $array = array(), $format = 'json', $body = '')
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
        return $this->call($method, $array, 'POST', $format, $body);
    }

    /**
     * Head API call
     *
     * @param string $method Lengow method API call
     * @param array $array Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body datas for request
     *
     * @throws LengowException get Curl error
     *
     * @return mixed
     */
    public function head($method, $array = array(), $format = 'json', $body = '')
    {
        return $this->call($method, $array, 'HEAD', $format, $body);
    }

    /**
     * Put API call
     *
     * @param string $method Lengow method API call
     * @param array $array Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body datas for request
     *
     * @throws LengowException get Curl error
     *
     * @return mixed
     */
    public function put($method, $array = array(), $format = 'json', $body = '')
    {
        return $this->call($method, $array, 'PUT', $format, $body);
    }

    /**
     * Delete API call
     *
     * @param string $method Lengow method API call
     * @param array $array Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body datas for request
     *
     * @throws LengowException get Curl error
     *
     * @return mixed
     */
    public function delete($method, $array = array(), $format = 'json', $body = '')
    {
        return $this->call($method, $array, 'DELETE', $format, $body);
    }

    /**
     * Patch API call
     *
     * @param string $method Lengow method API call
     * @param array $array Lengow method API parameters
     * @param string $format return format of API
     * @param string $body body datas for request
     *
     * @throws LengowException get Curl error
     *
     * @return mixed
     */
    public function patch($method, $array = array(), $format = 'json', $body = '')
    {
        return $this->call($method, $array, 'PATCH', $format, $body);
    }

    /**
     * Call API action
     *
     * @param string $api Lengow method API call
     * @param array $args Lengow method API parameters
     * @param string $type type of request GET|POST|PUT|HEAD|DELETE|PATCH
     * @param string $format return format of API
     * @param string $body body datas for request
     *
     * @throws LengowException get Curl error
     *
     * @return mixed
     */
    private function callAction($api, $args, $type, $format = 'json', $body = '')
    {
        $result = $this->makeRequest($type, $api, $args, $this->token, $body);
        return $this->format($result, $format);
    }

    /**
     * Get data in specific format
     *
     * @param mixed $data Curl response data
     * @param string $format return format of API
     *
     * @return mixed
     */
    private function format($data, $format)
    {
        switch ($format) {
            case 'json':
                return Tools::jsonDecode($data, true);
            case 'csv':
                return $data;
            case 'xml':
                return simplexml_load_string($data);
            case 'stream':
                return $data;
        }
    }

    /**
     * Make Curl request
     *
     * @param string $type Lengow method API call
     * @param string $url Lengow API url
     * @param array $args Lengow method API parameters
     * @param string $token temporary access token
     * @param string $body body datas for request
     *
     * @throws LengowException get Curl error
     *
     * @return array
     */
    protected function makeRequest($type, $url, $args, $token, $body = '')
    {
        // Define CURLE_OPERATION_TIMEDOUT for old php versions
        defined('CURLE_OPERATION_TIMEDOUT') || define('CURLE_OPERATION_TIMEDOUT', CURLE_OPERATION_TIMEOUTED);
        $ch = curl_init();
        // Options
        $opts = self::$curlOpts;
        // get special timeout for specific Lengow API
        if (array_key_exists($url, $this->lengowUrls)) {
            $opts[CURLOPT_TIMEOUT] = $this->lengowUrls[$url];
        }
        // get url for a specific environment
        $url = self::LENGOW_API_URL . $url;
        $opts[CURLOPT_CUSTOMREQUEST] = Tools::strtoupper($type);
        $url = parse_url($url);
        if (isset($url['port'])) {
            $opts[CURLOPT_PORT] = $url['port'];
        }
        $opts[CURLOPT_HEADER] = false;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_VERBOSE] = false;
        if (isset($token)) {
            $opts[CURLOPT_HTTPHEADER] = array(
                'Authorization: ' . $token,
            );
        }
        $url = $url['scheme'] . '://' . $url['host'] . $url['path'];
        switch ($type) {
            case 'GET':
                $opts[CURLOPT_URL] = $url . (!empty($args) ? '?' . http_build_query($args) : '');
                LengowMain::log(
                    'Connector',
                    LengowMain::setLogMessage('log.connector.call_api', array('curl_url' => $opts[CURLOPT_URL]))
                );
                break;
            case 'PUT':
                if (isset($token)) {
                    $opts[CURLOPT_HTTPHEADER] = array_merge(
                        $opts[CURLOPT_HTTPHEADER],
                        array(
                            'Content-Type: application/json',
                            'Content-Length: ' . Tools::strlen($body)
                        )
                    );
                }
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($args);
                $opts[CURLOPT_POSTFIELDS] = $body;
                break;
            case 'PATCH':
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
        // Execute url request
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $errorNumber = curl_errno($ch);
        $errorText = curl_error($ch);
        curl_close($ch);
        if ($result === false) {
            if (in_array($errorNumber, array(CURLE_OPERATION_TIMEDOUT, CURLE_OPERATION_TIMEOUTED))) {
                $errorCurl = LengowMain::setLogMessage('lengow_log.exception.timeout_api');
            } else {
                $errorCurl = LengowMain::setLogMessage(
                    'lengow_log.exception.error_curl',
                    array(
                        'error_code' => $errorNumber,
                        'error_message' => $errorText
                    )
                );
            }
            $errorMessage = LengowMain::setLogMessage(
                'log.connector.error_api',
                array('error_code' => LengowMain::decodeLogMessage($errorCurl, 'en'))
            );
            LengowMain::log('Connector', $errorMessage);
            throw new LengowException($errorCurl);
        }
        return $result;
    }

    /**
     * Check if is a new merchant
     *
     * @return boolean
     */
    public static function isNewMerchant()
    {
        list($accountId, $accessToken, $secretToken) = LengowConfiguration::getAccessIds();
        if (!is_null($accountId) && !is_null($accessToken) && !is_null($secretToken)) {
            return false;
        }
        return true;
    }

    /**
     * Check API Authentication
     *
     * @return boolean
     */
    public static function isValidAuth()
    {
        if (LengowMain::inTest()) {
            return true;
        }
        if (!LengowCheck::isCurlActivated()) {
            return false;
        }
        list($accountId, $accessToken, $secretToken) = LengowConfiguration::getAccessIds();
        if (is_null($accountId) || $accountId == 0 || !is_numeric($accountId)) {
            return false;
        }
        $connector = new LengowConnector($accessToken, $secretToken);
        try {
            $result = $connector->connect();
        } catch (LengowException $e) {
            return false;
        }
        if (isset($result['token'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get result for a query Api
     *
     * @param string $type request type (GET / POST / PUT / PATCH)
     * @param string $url request url
     * @param array $params request params
     * @param string $body body datas for request
     *
     * @return mixed
     */
    public static function queryApi($type, $url, $params = array(), $body = '')
    {
        if (!in_array($type, array('get', 'post', 'put', 'patch'))) {
            return false;
        }
        try {
            list($accountId, $accessToken, $secretToken) = LengowConfiguration::getAccessIds();
            if (is_null($accountId)) {
                return false;
            }
            $connector = new LengowConnector($accessToken, $secretToken);
            $results = $connector->$type(
                $url,
                array_merge(array('account_id' => $accountId), $params),
                'stream',
                $body
            );
        } catch (LengowException $e) {
            return false;
        }
        return Tools::jsonDecode($results);
    }
}
