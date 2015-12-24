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


try {
    if (!function_exists('curl_init')) {
        throw new LengowApiException('Lengow needs the CURL PHP extension.', -1);
    }
    if (!function_exists('json_decode')) {
        throw new LengowApiException('Lengow needs the JSON PHP extension.', -2);
    }
    if (!function_exists('simplexml_load_string')) {
        throw new LengowApiException('Lengow needs the SIMPLE XML PHP extension.', -3);
    }
} catch (LengowApiException $e) {
    echo $e->getMessage();
}

/**
 * The Lengow connector API.
 *
 */
class LengowConnector
{

    /**
     * Version.
     */
    const VERSION = '1.0.1';

    /**
     * Error.
     */
    public $error;

    /**
     * Default options for curl.
     */
    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 300,
        CURLOPT_USERAGENT => 'lengow-api-1.0',
    );

    /**
     * Lengow webservices domains.
     */
    public static $DOMAIN_LENGOW = array(
        'solution' => array(
            'protocol' => 'https',
            'url' => 'solution.lengow.com',
            'format' => 'json'
        ),
        'api' => array(
            'protocol' => 'http',
            'url' => 'api.lengow.com',
            'format' => 'xml'
        ),
        'statistics' => array(
            'protocol' => 'http',
            'url' => 'statistics.lengow.com',
            'format' => 'xml'
        ),
    );

    /**
     * Lengow methods API.
     */
    public static $API_METHODS_LENGOW = array(
        'getListingFeeds' => array('service' => 'solution'),
        'updateFeed' => array('service' => 'solution'),
        'getHistoryFeed' => array('service' => 'solution'),
        'updateInformationsClient' => array('service' => 'solution'),
        'getListingGroups' => array('service' => 'solution'),
        'createGroup' => array('service' => 'solution'),
        'updateGroup' => array('service' => 'solution'),
        'getListingVip' => array('service' => 'solution'),
        'createVip' => array('service' => 'solution'),
        'updateVip' => array('service' => 'solution'),
        'getLeads' => array('service' => 'solution'),
        'statusLead' => array('service' => 'solution'),
        'updatePrestaInternalOrderId' => array('service' => 'solution'),
        'updateTrackingMagento' => array('service' => 'solution'),
        'updateRootFeed' => array('service' => 'solution'),
        'getRootFeed' => array('service' => 'solution'),
        'updateEcommerceSolution' => array('service' => 'solution'),
        'statistics' => array('service' => 'statistics'),
        'commands' => array('service' => 'api'),
        'authentification' => array('service' => 'solution'),
    );

    /**
     * Lengow token.
     */
    public $token;

    /**
     * Lengow ID customer.
     */
    public $id_customer;

    /**
     * Make a new Lengow API Connector.
     *
     * @param integer $id_customer Your customer ID.
     * @param varchar $token Your token Lengow API.
     */
    public function __construct($id_customer, $token)
    {
        try {
            if (is_integer($id_customer)) {
                $this->id_customer = $id_customer;
            } else {
                throw new LengowApiException('Error Lengow Customer ID', 1);
            }
            if (Tools::strlen($token) > 10) {
                $this->token = $token;
            } else {
                throw new LengowApiException('Error Lengow Token API', 2);
            }
        } catch (LengowApiException $e) {
            $this->error = $e;
            return false;
        }
        return true;
    }

    /**
     * The API method.
     *
     * @param varchar $method Lengow method API call.
     * @param varchar $array Lengow method API parameters
     *
     * @return array The formated data response
     */
    public function api($method, $array = array())
    {
        if (!$api = $this->getMethod($method)) {
            throw new LengowApiException('Error unknown API method', 3);
        } else {
            try {
                $data = $this->callAction($api['service'], $method, $array);
            } catch (LengowApiException $lae) {
                LengowCore::log($lae->getMessage());
                return false;
            }

        }
        return $data;
    }

    /**
     * Call the Lengow service with accepted method.
     *
     * @param varchar $service Lengow service name
     * @param varchar $method Lengow method API call.
     * @param varchar $array Lengow method API parameters
     *
     * @return array The formated data response
     */
    private function callAction($service, $method, $array)
    {
        switch ($service) {
            case 'solution':
                $url = $this->getUrlService($service, $method, $array);
                break;
            case 'api':
                if (!empty($array['order_id'])) {
                    $url = $this->getUrlOrder($service, $array);
                } else {
                    $url = $this->getUrlOrders($service, $array);
                }
                break;
            case 'statistics':
                $url = $this->getUrlStatistics($service, $array);
                break;
        }
        $result = LengowConnector::makeRequest($url);
        return LengowConnector::format($result, self::$DOMAIN_LENGOW[$service]['format']);
    }

    /**
     * Makes the Service API Url.
     *
     * @param string $service The URL to make the request to
     * @param string $array The array of query parameters
     *
     * @return string The url
     */
    private function getUrlService($service, $method, $array)
    {
        $url = self::$DOMAIN_LENGOW[$service]['protocol']
            . '://'
            . self::$DOMAIN_LENGOW[$service]['url']
            . '/wsdl/connector/call.json?'
            . 'token=' . $this->token
            . '&idClient=' . $this->id_customer
            . '&method=' . $method
            . '&array=' . urlencode(serialize($array));
        return $url;
    }

    /**
     * Makes the Orders API Url.
     *
     * @param string $service The URL to make the request to
     * @param string $array The array of query parameters
     *
     * @return string The url
     */
    private function getUrlOrders($service, $array)
    {
        $url = self::$DOMAIN_LENGOW[$service]['protocol']
            . '://'
            . self::$DOMAIN_LENGOW[$service]['url'] . '/'
            . 'v2/'
            . $array['date_from'] . '/'
            . $array['date_to'] . '/'
            . $this->id_customer . '/'
            . $array['group_id'] . '/'
            . (isset($array['id']) && !empty($array['id']) ? $array['id'] : 'orders')
            . '/commands/'
            . (isset($array['state']) && !empty($array['state']) ? $array['state'] . '/' : '');
        return $url;
    }

    /**
     * Makes url to give one commands
     *
     * @param string $service The URL to make the request to
     * @param string $array The array of query parameters
     *
     * @return string The url
     */
    private function getUrlOrder($service, $array)
    {
        $url = self::$DOMAIN_LENGOW[$service]['protocol']
            . '://'
            . self::$DOMAIN_LENGOW[$service]['url'] . '/'
            . 'v2/'
            . $this->id_customer . '/'
            . $array['feed_id'] . '/'
            . 'orderid/'
            . $array['order_id'] . '/';
        return $url;
    }

    /**
     * Makes the Statisctics API Url.
     *
     * @param string $service The URL to make the request to
     * @param string $array The array of query parameters
     *
     * @return string The url
     */
    private function getUrlStatistics($service, $array)
    {
        $url = self::$DOMAIN_LENGOW[$service]['protocol']
            . '://'
            . self::$DOMAIN_LENGOW[$service]['url']
            . $array['dateFrom'] . '/'
            . $array['dateTo'] . '/'
            . $this->id_customer . '/'
            . $array['id']
            . '/total-All/';
        return $url;
    }

    /**
     * Get the method of Lengow API if exist.
     *
     * @param string $method The method's name
     *
     * @return string The method with service
     */
    private function getMethod($method)
    {
        if (self::$API_METHODS_LENGOW[$method]) {
            return self::$API_METHODS_LENGOW[$method];
        } else {
            return false;
        }
    }

    /**
     * Format data with good format.
     *
     * @param string $data the data's response of method request
     * @param string $format the return format
     *
     * @return string Data formated
     */
    private static function format($data, $format)
    {
        switch ($format) {
            case 'xml':
                return simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
            case 'json':
                return Tools::jsonDecode($data, true);
        }
        return null;
    }

    /**
     * Makes an HTTP request.
     *
     * @param string $url The URL to make the request to
     *
     * @return string The response text
     */
    protected static function makeRequest($url)
    {
        LengowCore::log('Connector ' . $url);
        $ch = curl_init();
        // Options
        $opts = self::$CURL_OPTS;
        $opts[CURLOPT_URL] = $url;
        // Exectute url request
        curl_setopt_array($ch, $opts);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if ($result === false) {
            LengowCore::log('Connector Error (' . curl_error($ch) . ')' . $result);
            throw new LengowApiException(
                array(
                    'message' => curl_error($ch),
                    'type' => 'CurlException'),
                curl_errno($ch)
            );
        }
        curl_close($ch);
        if (is_object(Tools::jsonDecode($result))) {
            if (Tools::strtolower(Tools::jsonDecode($result)->return) == 'ko') {
                LengowCore::log('API Error : ' . Tools::jsonDecode($result)->error);
                throw new LengowApiException(Tools::jsonDecode($result)->error, 4);
            }
        }
        return $result;
    }

}

/**
 * Thrown when an API call returns an exception.
 *
 * @author Ludovic Drin <ludovic@lengow.com>
 */
class LengowApiException extends Exception
{

    /**
     * The result from the API server that represents the exception information.
     */
    protected $result;

    /**
     * Make a new API Exception with the given result.
     *
     * @param array $result The error result
     */
    public function __construct($result, $noerror)
    {
        $this->result = $result;
        if (is_array($result)) {
            $msg = $result['message'];
        } else {
            $msg = $result;
        }
        parent::__construct($msg, $noerror);
    }

    /**
     * Return the associated result object returned by the API server.
     *
     * @return array The result from the API server
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Returns the associated type for the error.
     *
     * @return string
     */
    public function getType()
    {
        if (isset($this->result['type'])) {
            return $this->result['type'];
        }
        return 'LengowApiException';
    }

    /**
     * To make debugging easier.
     *
     * @return string The string representation of the error
     */
    public function __toString()
    {
        if (isset($this->result['message'])) {
            return $this->result['message'];
        }
        return $this->message;
    }

}
