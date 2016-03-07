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

/**
 * Lengow Connector Class.
 */
class LengowConnector
{
    /**
     * @var string connector version
     */
    const VERSION = '1.0';

    /**
     * @var mixed error returned by the API
     */
    public $error;

    /**
     * @var string the access token to connect
     */
    protected $access_token;

    /**
     * @var string the secret to connect
     */
    protected $secret;

    /**
     * @var string temporary token for the authorization
     */
    protected $token;

    /**
     * @var integer ID account
     */
    protected $account_id;

    /**
     * @var integer the user Id
     */
    protected $user_id;

    /**
     * @var string
     */
    protected $request;

    /**
     * @var string URL of the API Lengow
     */
    const LENGOW_API_URL = 'http://10.100.1.242:8081';

    /**
     * @var string URL of the SANDBOX Lengow
     */
    const LENGOW_API_SANDBOX_URL = 'http://10.100.1.242:8081';

    public static $test_fixture_path;

    /**
     * @var array default options for curl
     */
    public static $CURL_OPTS = array (
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 300,
        CURLOPT_USERAGENT      => 'lengow-php-sdk',
    );

    /**
     * Make a new Lengow API Connector.
     *
     * @param varchar $access_token Your access token.
     * @param varchar $secret Your secret.
     */
    public function __construct($access_token, $secret)
    {
        $this->access_token = $access_token;
        $this->secret = $secret;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Connectection to the API
     *
     * @param varchar $user_token The user token if is connected
     *
     * @return mixed array [authorized token + account_id + user_id] or false
     */
    public function connect($user_token = '')
    {
        $data = $this->_callAction(
            '/access/get_token',
            array(
                'access_token' => $this->access_token,
                'secret' => $this->secret,
                'user_token' => $user_token
            ),
            'POST'
        );
        if (isset($data['token'])) {
            $this->token = $data['token'];
            $this->account_id = $data['account_id'];
            $this->user_id = $data['user_id'];
            return $data;
        } else {
            return false;
        }
    }

    /**
     * The API method.
     *
     * @param varchar $method Lengow method API call.
     * @param varchar $array Lengow method API parameters
     * @param varchar $type type of request GET|POST|PUT|HEAD|DELETE|PATCH
     * @param varchar $format return format of API
     *
     * @return array The formated data response
     */
    public function call($method, $array = array(), $type = 'GET', $format = 'json')
    {
        $this->connect();
        try {
            if (!array_key_exists('account_id', $array)) {
                $array['account_id'] = $this->account_id;
            }
            $data = $this->_callAction($method, $array, $type, $format);
        } catch (LengowException $e) {
            return $e->getMessage();
        }
        return $data;
    }

    public function get($method, $array = array(), $format = 'json')
    {
        if (LengowMain::inTest() && self::$test_fixture_path) {
            if (is_array(self::$test_fixture_path)) {
                $content = Tools::file_get_contents(self::$test_fixture_path[0]);
                array_shift(self::$test_fixture_path);
            } else {
                $content = Tools::file_get_contents(self::$test_fixture_path);
                self::$test_fixture_path = null;
            }
            return $content;
        }
        return $this->call($method, $array, 'GET', $format);
    }

    public function post($method, $array = array(), $format = 'json')
    {
        if (LengowMain::inTest() && self::$test_fixture_path) {
            if (is_array(self::$test_fixture_path)) {
                $content = Tools::file_get_contents(self::$test_fixture_path[0]);
                array_shift(self::$test_fixture_path);
            } else {
                $content = Tools::file_get_contents(self::$test_fixture_path);
                self::$test_fixture_path = null;
            }
            return $content;
        }
        return $this->call($method, $array, 'POST', $format);
    }

    public function head($method, $array = array(), $format = 'json')
    {
        return $this->call($method, $array, 'HEAD', $format);
    }

    public function put($method, $array = array(), $format = 'json')
    {
        return $this->call($method, $array, 'PUT', $format);
    }

    public function delete($method, $array = array(), $format = 'json')
    {
        return $this->call($method, $array, 'DELETE', $format);
    }

    public function patch($method, $array = array(), $format = 'json')
    {
        return $this->call($method, $array, 'PATCH', $format);
    }

    private function _callAction($api, $args, $type, $format = 'json')
    {
        if ($api == '/v1.0/numbers/') {
            $url = 'http://10.100.1.242:8083';
        } else {
            $url = self::LENGOW_API_URL;
        }
        $result = $this->_makeRequest($type, $url.$api, $args, $this->token);
        return $this->_format($result, $format);
    }

    private function _format($data, $format)
    {
        switch ($format) {
            case 'json':
                return Tools::jsonDecode($data, true);
                break;
            case 'csv':
                return $data;
                break;
            case 'xml':
                return simplexml_load_string($data);
                break;
            case 'stream':
                return $data;
                break;
        }
    }

    protected function _makeRequest($type, $url, $args, $token)
    {
        $ch = curl_init();
        // Options
        $opts = self::$CURL_OPTS;
        $opts[CURLOPT_CUSTOMREQUEST] = Tools::strtoupper($type);
        $url = parse_url($url);
        $opts[CURLOPT_PORT] = $url['port'];
        $opts[CURLOPT_HEADER] = true;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_VERBOSE] = false;
        if (isset($token)) {
            $opts[CURLOPT_HTTPHEADER] = array(
                'Authorization: '.$token,
            );
        }
        $url = $url['scheme'].'://'.$url['host'].$url['path'];
        if ($type == 'GET') {
            $opts[CURLOPT_URL] = $url.'?'.http_build_query($args);
            LengowMain::log(
                'Connector',
                LengowMain::setLogMessage('log.connector.call_api', array('curl_url' => $opts[CURLOPT_URL]))
            );
        } else {
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = count($args);
            $opts[CURLOPT_POSTFIELDS] = http_build_query($args);
        }
        // Exectute url request
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        $error = curl_errno($ch);
        list($header, $data) = explode("\r\n\r\n", $result, 2);
        $information = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        curl_close($ch);
        if ($data === false) {
            $error_message = LengowMain::setLogMessage('log.connector.error_api', array(
                'error_code' => $error['code']
            ));
            LengowMain::log('Connector', $error_message);
            throw new LengowException($error_message);
        }
        return $data;
    }

    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * v3
     * Get Valid Account / Access / Secret
     *
     * @param integer $id_shop
     *
     * @return array
     */
    public static function getAccessId($id_shop = null)
    {
        if ($id_shop) {
            $account_id = LengowMain::getIdAccount($id_shop);
            $access_token = LengowMain::getAccessToken($id_shop);
            $secret_token = LengowMain::getSecretCustomer($id_shop);
        } else {
            $shopCollection = LengowShop::findAll();
            foreach ($shopCollection as $shop) {
                $account_id = LengowMain::getIdAccount($shop['id_shop']);
                $access_token = LengowMain::getAccessToken($shop['id_shop']);
                $secret_token = LengowMain::getSecretCustomer($shop['id_shop']);
                if (Tools::strlen($account_id) > 0 &&
                    Tools::strlen($access_token) > 0 &&
                    Tools::strlen($secret_token) > 0
                ) {
                    break;
                }
            }
        }
        if (Tools::strlen($account_id) > 0 &&
            Tools::strlen($access_token) > 0 &&
            Tools::strlen($secret_token) > 0
        ) {
            return array($account_id, $access_token, $secret_token);
        } else {
            return array(null, null, null);
        }
    }

    /**
     * v3
     * Query Api
     *
     * @param string $type (GET / POST)
     * @param string $url to query
     * @param integer $shopId to query
     * @param array $params
     *
     * @return api result as array
     */
    public static function queryApi($type, $url, $shopId = null, $params = array())
    {
        if (!in_array($type, array('get', 'post'))) {
            return false;
        }

        list($account_id, $access_token, $secret_token) = self::getAccessId($shopId);
        $connector  = new LengowConnector($access_token, $secret_token);
        $results = $connector->$type(
            $url,
            array_merge(array('account_id' => $account_id), $params),
            'stream'
        );
        return Tools::JsonDecode($results);
    }
}
