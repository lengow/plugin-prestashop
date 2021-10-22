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
 * Lengow Connector Class
 */
class LengowConnector
{
    /**
     * @var string url of Lengow solution
     */
    // const LENGOW_URL = 'lengow.io';
    const LENGOW_URL = 'lengow.net';

    /**
     * @var string url of the Lengow API
     */
    // const LENGOW_API_URL = 'https://api.lengow.io';
    const LENGOW_API_URL = 'https://api.lengow.net';

    /* Lengow API routes */
    const API_ACCESS_TOKEN = '/access/get_token';
    const API_ORDER = '/v3.0/orders';
    const API_ORDER_MOI = '/v3.0/orders/moi/';
    const API_ORDER_ACTION = '/v3.0/orders/actions/';
    const API_MARKETPLACE = '/v3.0/marketplaces';
    const API_PLAN = '/v3.0/plans';
    const API_CMS = '/v3.1/cms';
    const API_CMS_CATALOG = '/v3.1/cms/catalogs/';
    const API_CMS_MAPPING = '/v3.1/cms/mapping/';
    const API_PLUGIN = '/v3.0/plugins';

    /* Request actions */
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';

    /* Return formats */
    const FORMAT_JSON = 'json';
    const FORMAT_STREAM = 'stream';

    /* Http codes */
    const CODE_200 = 200;
    const CODE_201 = 201;
    const CODE_401 = 401;
    const CODE_403 = 403;
    const CODE_404 = 404;
    const CODE_500 = 500;
    const CODE_504 = 504;

    /**
     * @var array success HTTP codes for request
     */
    protected $successCodes = array(
        self::CODE_200,
        self::CODE_201,
    );

    /**
     * @var array authorization HTTP codes for request
     */
    protected $authorizationCodes = array(
        self::CODE_401,
        self::CODE_403,
    );

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
        self::API_CMS => 5,
        self::API_CMS_CATALOG => 10,
        self::API_CMS_MAPPING => 10,
        self::API_PLUGIN => 5,
    );

    /**
     * @var array API requiring no arguments in the call url
     */
    protected $apiWithoutUrlArgs = array(
        self::API_ACCESS_TOKEN,
        self::API_ORDER_ACTION,
        self::API_ORDER_MOI,
    );

    /**
     * @var array API requiring no authorization for the call url
     */
    protected static $apiWithoutAuthorizations = array(
        self::API_PLUGIN,
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
        if (!LengowToolbox::isCurlActivated()) {
            return false;
        }
        list($accountId, $accessToken, $secret) = LengowConfiguration::getAccessIds();
        if ($accountId === null) {
            return false;
        }
        $connector = new LengowConnector($accessToken, $secret);
        try {
            $connector->connect(false, $logOutput);
        } catch (LengowException $e) {
            $message = LengowMain::decodeLogMessage($e->getMessage(), LengowTranslation::DEFAULT_ISO_CODE);
            $error = LengowMain::setLogMessage(
                'log.connector.error_api',
                array(
                    'error_code' => $e->getCode(),
                    'error_message' => $message,
                )
            );
            LengowMain::log(LengowLog::CODE_CONNECTOR, $error, $logOutput);
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
     * @return array|false
     */
    public static function queryApi($type, $api, $args = array(), $body = '', $logOutput = false)
    {
        if (!in_array($type, array(self::GET, self::POST, self::PUT, self::PATCH))) {
            return false;
        }
        try {
            $authorizationRequired = !in_array($api, self::$apiWithoutAuthorizations, true);
            list($accountId, $accessToken, $secret) = LengowConfiguration::getAccessIds();
            if ($accountId === null && $authorizationRequired) {
                return false;
            }
            $connector = new LengowConnector($accessToken, $secret);
            $type = (string) Tools::strtolower($type);
            $args = $authorizationRequired
                ? array_merge(array(LengowImport::ARG_ACCOUNT_ID => $accountId), $args)
                : $args;
            $results = $connector->$type($api, $args, self::FORMAT_STREAM, $body, $logOutput);
        } catch (LengowException $e) {
            $message = LengowMain::decodeLogMessage($e->getMessage(), LengowTranslation::DEFAULT_ISO_CODE);
            $error = LengowMain::setLogMessage(
                'log.connector.error_api',
                array(
                    'error_code' => $e->getCode(),
                    'error_message' => $message,
                )
            );
            LengowMain::log(LengowLog::CODE_CONNECTOR, $error, $logOutput);

            return false;
        }

        return Tools::jsonDecode($results);
    }

    /**
     * Get account id by credentials from Middleware
     *
     * @param string $accessToken access token for api
     * @param string $secret secret for api
     * @param boolean $logOutput see log or not
     *
     * @return int|null
     */
    public static function getAccountIdByCredentials($accessToken, $secret, $logOutput = false)
    {
        $connector = new LengowConnector($accessToken, $secret);
        try {
            $data = $connector->callAction(
                self::API_ACCESS_TOKEN,
                array(
                    'access_token' => $accessToken,
                    'secret' => $secret,
                ),
                self::POST,
                self::FORMAT_JSON,
                '',
                $logOutput
            );
        } catch (LengowException $e) {
            $message = LengowMain::decodeLogMessage($e->getMessage(), LengowTranslation::DEFAULT_ISO_CODE);
            $error = LengowMain::setLogMessage(
                'log.connector.error_api',
                array(
                    'error_code' => $e->getCode(),
                    'error_message' => $message,
                )
            );
            LengowMain::log(LengowLog::CODE_CONNECTOR, $error, $logOutput);
            return null;
        }
        return $data['account_id'] ? (int) $data['account_id'] : null;
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
        $token = LengowConfiguration::getGlobalValue(LengowConfiguration::AUTHORIZATION_TOKEN);
        $updatedAt = LengowConfiguration::getGlobalValue(LengowConfiguration::LAST_UPDATE_AUTHORIZATION_TOKEN);
        if (!$force
            && $token !== null
            && $updatedAt !== null
            && $token !== ''
            && (time() - $updatedAt) < $this->tokenLifetime
        ) {
            $authorizationToken = $token;
        } else {
            $authorizationToken = $this->getAuthorizationToken($logOutput);
            LengowConfiguration::updateGlobalValue(LengowConfiguration::AUTHORIZATION_TOKEN, $authorizationToken);
            LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_AUTHORIZATION_TOKEN, time());
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
            if (!in_array($api, self::$apiWithoutAuthorizations, true)) {
                $this->connect(false, $logOutput);
            }
            $data = $this->callAction($api, $args, $type, $format, $body, $logOutput);
        } catch (LengowException $e) {
            if (in_array($e->getCode(), $this->authorizationCodes, true)) {
                LengowMain::log(
                    LengowLog::CODE_CONNECTOR,
                    LengowMain::setLogMessage('log.connector.retry_get_token'),
                    $logOutput
                );
                if (!in_array($api, self::$apiWithoutAuthorizations, true)) {
                    $this->connect(true, $logOutput);
                }
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
        // reset temporary token for the new authorization
        $this->token = null;
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
        }
        if (Tools::strlen($data['token']) === 0) {
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
        // get default curl options
        $opts = $this->curlOpts;
        // get special timeout for specific Lengow API
        if (array_key_exists($api, $this->lengowUrls)) {
            $opts[CURLOPT_TIMEOUT] = $this->lengowUrls[$api];
        }
        // get base url for a specific environment
        $url = self::LENGOW_API_URL . $api;
        $opts[CURLOPT_CUSTOMREQUEST] = Tools::strtoupper($type);
        $url = parse_url($url);
        if (isset($url['port'])) {
            $opts[CURLOPT_PORT] = $url['port'];
        }
        $opts[CURLOPT_HEADER] = false;
        $opts[CURLOPT_VERBOSE] = false;
        if (!empty($token)) {
            $opts[CURLOPT_HTTPHEADER] = array('Authorization: ' . $token);
        }
        // get call url with the mandatory parameters
        $opts[CURLOPT_URL] = $url['scheme'] . '://' . $url['host'] . $url['path'];
        if (!empty($args) && ($type === self::GET || !in_array($api, $this->apiWithoutUrlArgs, true))) {
            $opts[CURLOPT_URL] .= '?' . http_build_query($args);
        }
        if ($type !== self::GET) {
            if (!empty($body)) {
                // sending data in json format for new APIs
                $opts[CURLOPT_HTTPHEADER] = array_merge(
                    $opts[CURLOPT_HTTPHEADER],
                    array(
                        'Content-Type: application/json',
                        'Content-Length: ' . Tools::strlen($body),
                    )
                );
                $opts[CURLOPT_POSTFIELDS] = $body;
            } else {
                // sending data in string format for legacy APIs
                $opts[CURLOPT_POST] = count($args);
                $opts[CURLOPT_POSTFIELDS] = http_build_query($args);
            }
        }
        LengowMain::log(
            LengowLog::CODE_CONNECTOR,
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
            if (in_array($curlErrorNumber, array(CURLE_OPERATION_TIMEDOUT, CURLE_OPERATION_TIMEOUTED), true)) {
                throw new LengowException(LengowMain::setLogMessage('log.connector.timeout_api'), self::CODE_504);
            }
            throw new LengowException(
                LengowMain::setLogMessage(
                    'log.connector.error_curl',
                    array(
                        'error_code' => $curlErrorNumber,
                        'error_message' => $curlError,
                    )
                ),
                self::CODE_500
            );
        }
        if (!in_array($httpCode, $this->successCodes, true)) {
            $result = $this->format($result);
            // recovery of Lengow Api errors
            if (isset($result['error']['message'])) {
                throw new LengowException($result['error']['message'], $httpCode);
            }
            throw new LengowException(LengowMain::setLogMessage('log.connector.api_not_available'), $httpCode);
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
