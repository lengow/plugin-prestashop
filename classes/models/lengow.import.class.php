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
* Lengow Import class
*/
class LengowImport
{
    /**
     * Version.
     */
    const VERSION = '1.0.1';

    /**
     * @var integer lang id
     */
    protected $id_lang;

    /**
     * @var integer shop id
     */
    protected $id_shop = null;

    /**
     * @var integer shop group id
     */
    protected $id_shop_group;

    /**
     * @var boolean use debug mode
     */
    protected $debug = false;

    /**
     * @var boolean display log messages
     */
    protected $log_output = false;

    /**
     * @var string order id
     */
    protected $order_id = null;

    /**
     * @var string markeplace name
     */
    protected $marketplace_name = null;

    /**
     * @var integer number of orders to import
     */
    protected $limit = 0;

    /**
     * @var boolean import inactive & out of stock products
     */
    protected $force_product = true;

    /**
     * @var string start import date
     */
    protected $date_from = null;

    /**
     * @var string end import date
     */
    protected $date_to = null;

    /**
     * @var string account ID
     */
    protected $account_id;

    /**
     * @var string access token
     */
    protected $access_token;

    /**
     * @var string secret
     */
    protected $secret;

    /**
     * @var LengowConnector Lengow connector
     */
    protected $connector;

    /**
     * @var Context Context for import order
     */
    protected $context;

    /**
     * @var string order id being imported
     */
    public static $current_order = -1;

    /**
     * @var array valid states lengow to import
     */
    public static $LENGOW_STATES = array(
        'accepted',
        'waiting_shipment',
        'shipped',
        'closed',
    );

    /**
     * Construct the import manager
     *
     * @param array params optional options
     * string    $order_id           lengow order id to import
     * string    $marketplace_name   lengow marketplace name to import
     * integer   $shop_id            Id shop for current import
     * boolean   $force_product      force import of products
     * boolean   $debug              debug mode
     * string    $date_from          starting import date
     * string    $date_to            ending import date
     * integer   $limit              number of orders to import
     * boolean   $log_output         display log messages
     */
    public function __construct($params = array())
    {
        $this->context = Context::getContext();
        if (isset($params['shop_id'])) {
            $this->id_shop = (int)$params['shop_id'];
            if (_PS_VERSION_ >= '1.5') {
                if ($shop = new Shop($this->id_shop)) {
                    $this->context->shop = $shop;
                }
            }
        }
        $this->id_lang          = $this->context->language->id;
        $this->id_shop_group    = $this->context->shop->id_shop_group;
        $this->force_product    = (isset($params['force_product']) ? $params['force_product'] : true);
        $this->debug            = (isset($params['debug']) ? $params['debug'] : false);
        $this->limit            = (isset($params['limit']) ? $params['limit'] : 0);
        $this->log_output       = (isset($params['log_output']) ? $params['log_output'] : false);
        $this->date_from        = (isset($params['date_from']) ? $params['date_from'] : null);
        $this->date_to          = (isset($params['date_to']) ? $params['date_to'] : null);
        if ((isset($params['order_id']) && !is_null($params['order_id']))
            && ((isset($params['marketplace_name'])) && !is_null($params['marketplace_name']))
        ) {
            $this->order_id         = $params['order_id'];
            $this->marketplace_name = $params['marketplace_name'];
        }
    }

    /**
     * Excute import : fetch orders and import them
     */
    public function exec()
    {
        try {
            // check account ID, Access Token and Secret
            $this->account_id = LengowMain::getIdAccount($this->id_shop);
            $this->access_token = LengowMain::getAccessToken($this->id_shop);
            $this->secret = LengowMain::getSecretCustomer($this->id_shop);
            if (!$this->account_id || !$this->access_token || !$this->secret) {
                throw new LengowImportException(
                    'Please checks your plugin configuration. ID account, access token or secret is empty in store '
                    .$this->id_shop
                );
            }
            // start of actual import process
            // get orders from Lengow API
            $orders = $this->getOrdersFromApi();
            $total_orders = count($orders);
            if ($this->order_id) {
                LengowMain::log(
                    $total_orders
                    .' order found for order ID: '.$this->order_id
                    .' and markeplace: '.$this->marketplace_name
                    .' with account ID: '.$this->account_id,
                    $this->log_output
                );
            } else {
                LengowMain::log(
                    $total_orders.' order'.($total_orders > 1 ? 's ' : ' ')
                    .'found with account ID: '.$this->account_id,
                    $this->log_output
                );
            }
            if ($total_orders <= 0) {
                return false;
            }
            // import orders in prestashop
            $result = $this->importOrders($orders);
        } catch (Exception $e) {
            LengowMain::log('Error: '.$e->getMessage(), $this->log_output);
            return false;
        }
        return $result;
    }

    /**
     * Call Lengow order API
     *
     * @return mixed
     */
    protected function getOrdersFromApi()
    {
        $page = 1;
        $orders = array();

        if (LengowCheck::isValidAuth($this->id_shop)) {
            $this->connector  = new LengowConnector($this->access_token, $this->secret);
            if ($this->order_id && $this->marketplace_name) {
                LengowMain::log(
                    'Connector: get order with order id: '.$this->order_id
                    .' and marketplace: '.$this->marketplace_name,
                    $this->log_output
                );
            } else {
                LengowMain::log(
                    'Connector: get orders between '.date('Y-m-d', strtotime((string)$this->date_from))
                    .' and '.date('Y-m-d', strtotime((string)$this->date_to))
                    .' with account ID: '.$this->account_id,
                    $this->log_output
                );
            }
            do {
                if ($this->order_id && $this->marketplace_name) {
                    $results = $this->connector->get(
                        '/v3.0/orders',
                        array(
                            'marketplace_order_id'  => $this->order_id,
                            'marketplace'           => $this->marketplace_name,
                            'account_id'            => $this->account_id,
                            'page'                  => $page
                        ),
                        'stream'
                    );
                } else {
                    $results = $this->connector->get(
                        '/v3.0/orders',
                        array(
                            'account_id'            => $this->account_id,
                            'updated_from'          => $this->date_from,
                            'updated_to'            => $this->date_to,
                            'page'                  => $page
                        ),
                        'stream'
                    );
                }
                if (is_null($results)) {
                    throw new LengowImportException('the connection didn\'t work with the Lengow webservice');
                }
                $results = Tools::jsonDecode($results);
                if (isset($results->error)) {
                    throw new LengowImportException(
                        'Lengow webservice : '.$results->error->code.' - '.$results->error->message
                    );
                }
                // Construct array orders
                foreach ($results->results as $order) {
                    $orders[] = $order;
                }
                $page++;
            } while ($results->next != null);
        } else {
            throw new LengowImportException('Account ID, Token access or Secret are not valid');
        }
        return $orders;
    }

    /**
     * Create or update order in prestashop
     *
     * @param mixed $orders API orders
     *
     * @return mixed
     */
    protected function importOrders($orders)
    {
        $count_orders_updated = 0;
        $count_orders_added = 0;
        foreach ($orders as $order_data) {
            $nb_package = 0;
            $lengow_id = (string)$order_data->marketplace_order_id;
            if ($this->debug) {
                $lengow_id .= '--'.time();
            }

            if ($lengow_id != '1300435581913-A') {
                continue;
            }

            // set current order to cancel hook updateOrderStatus
            LengowImport::$current_order = $lengow_id;
            // if order contains no package
            if (count($order_data->packages) == 0) {
                LengowMain::log('create order fail: no package in the order', $this->log_output, $this->lengow_id);
                continue;
            }

            foreach ($order_data->packages as $package_data) {
                $nb_package++;
                // check whether the package contains a shipping address
                if (!isset($package_data->delivery->id)) {
                    LengowMain::log(
                        'create order fail: no delivery address in the order',
                        $this->log_output,
                        $this->lengow_id
                    );
                    continue;
                }
                $delivery_address_id = (int)$package_data->delivery->id;
                $first_package = ($nb_package > 1 ? false : true);
                // try to import or update order
                $import_order = new LengowImportOrder(
                    array(
                        'context'               => $this->context,
                        'id_shop'               => $this->id_shop,
                        'id_shop_group'         => $this->id_shop_group,
                        'id_lang'               => $this->id_lang,
                        'force_product'         => $this->force_product,
                        'debug'                 => $this->debug,
                        'log_output'            => $this->log_output,
                        'lengow_id'             => $lengow_id,
                        'delivery_address_id'   => $delivery_address_id,
                        'order_data'            => $order_data,
                        'package_data'          => $package_data,
                        'first_package'         => $first_package
                    )
                );
                $result = $import_order->exec();
                if ($result) {
                    if ($result = 'new') {
                        $count_orders_added++;
                    } else {
                        $count_orders_updated++;
                    }
                }
                unset($import_order);
                die();
            }
        }
        return array('new' => $count_orders_added,'update' => $count_orders_updated);
    }

    /**
     * Check if order status is valid and is available for import
     *
     * @param string            $order_state_marketplace    order state
     * @param LengowMarketplace $marketplace                order marketplace
     *
     * @return boolean
     */
    public static function checkState($order_state_marketplace, $marketplace)
    {
        if (empty($order_state_marketplace)) {
            return false;
        }
        if (!in_array($marketplace->getStateLengow($order_state_marketplace), LengowImport::$LENGOW_STATES)) {
            return false;
        }
        return true;
    }
}
