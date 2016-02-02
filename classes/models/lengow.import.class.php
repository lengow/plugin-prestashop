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
     * @var string type import (manual or cron)
     */
    protected $type_import;

    /**
     * @var array account ids already imported
     */
    protected $account_ids = array();

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
        // params for re-import order
        if ((isset($params['order_id']) && !is_null($params['order_id']))
            && ((isset($params['marketplace_name'])) && !is_null($params['marketplace_name']))
            && ((isset($params['shop_id'])) && !is_null($params['shop_id']))
        ) {
            $this->order_id         = (string)$params['order_id'];
            $this->marketplace_name = (string)$params['marketplace_name'];
            $this->limit = 1;
        } else {
            // recovering the time interval
            $days = (
                isset($params['days'])
                ? (int)$params['days']
                : (int)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_DAYS')
            );
            $this->date_from = date('c', strtotime(date('Y-m-d').' -'.$days.'days'));
            $this->date_to = date('c');
            if (LengowConfiguration::getGlobalValue('LENGOW_IMPORT_SINGLE_ENABLED')) {
                $this->limit = 1;
            } else {
                $this->limit = (isset($params['limit']) ? (int)$params['limit'] : 0);
            }
            $this->log_output = true;
        }
        // get other params
        $this->debug = (
            isset($params['debug'])
            ? $params['debug']
            : (bool)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED')
        );
        $this->type_import = (isset($params['type']) ? $params['type'] : 'manual');
        $this->force_product = (
            isset($params['force_product'])
            ? $params['force_product']
            : (bool)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_FORCE_PRODUCT')
        );
        $this->id_shop = (isset($params['shop_id']) ? (int)$params['shop_id'] : null);
    }

    /**
     * Excute import : fetch orders and import them
     */
    public function exec()
    {
        // clean logs
        LengowMain::cleanLog();
        if (LengowMain::isInProcess() && !$debug) {
            LengowMain::log('import is already started', true);
        } else {
            LengowMain::log('## Start '.$this->type_import.' import ##', true);
            // 2nd step: start import process
            // LengowMain::setInProcess();
            // 3rd step: disable emails
            LengowMain::disableMail();
            $result_new = 0;
            $result_update = 0;
            // udpate last import date
            lengowMain::updateDateImport($this->type_import);
            // get all shops for import
            if (_PS_VERSION_ < '1.5') {
                $shops = array();
                $shops[] = array('id_shop' => 1, 'name' => 'Default shop');
            } else {
                $shops = Shop::getShops();
            }
            foreach ($shops as $shop) {
                if (!is_null($this->id_shop) && (int)$shop['id_shop'] != $this->id_shop) {
                    continue;
                }
                if (LengowMain::getShopActive((int)$shop['id_shop'])) {
                    LengowMain::log('Start import in shop '.$shop['name'].' ('.(int)$shop['id_shop'].')', true);
                    try {
                        // check account ID, Access Token and Secret
                        if (!$this->checkCredentials((int)$shop['id_shop'], $shop['name'])) {
                            continue;
                        }
                        // change context with current shop id
                        $this->changeContext((int)$shop['id_shop']);
                        // get orders from Lengow API
                        $orders = $this->getOrdersFromApi();
                        $total_orders = count($orders);
                        if ($this->order_id && $this->marketplace_name) {
                            LengowMain::log(
                                $total_orders.' order found for order ID: '.$this->order_id
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
                        $result_new += $result['new'];
                        $result_update += $result['update'];
                    } catch (Exception $e) {
                        LengowMain::log('Error: '.$e->getMessage(), $this->log_output);
                        return false;
                    }
                }
            }
            if ($result_new > 0) {
                LengowMain::log($result_new.' order'.($result_new > 1 ? 's ' : ' ').'imported', true);
            }
            if ($result_update > 0) {
                LengowMain::log($result_update.' order'.($result_update > 1 ? 's ' : ' ').'updated', true);
            }
            if ($result_new == 0 && $result_update == 0) {
                LengowMain::log('No order available to import', true);
            }
            LengowMain::setEnd();
            LengowMain::log('## End '.$this->type_import.' import ##', true);
            // sending email in error for orders
            if (LengowConfiguration::getGlobalValue('LENGOW_REPORT_MAIL_ENABLED') && !$debug) {
                // LengowMain::sendMailAlert();
            }
        }
    }

    /**
     * Check credentials for a shop
     *
     * @param integer   $id_shop      Shop Id
     * @param string    $name_shop    Shop name
     *
     * @return boolean
     */
    protected function checkCredentials($id_shop, $name_shop)
    {
        $this->account_id = LengowMain::getIdAccount($id_shop);
        $this->access_token = LengowMain::getAccessToken($id_shop);
        $this->secret = LengowMain::getSecretCustomer($id_shop);
        if (!$this->account_id || !$this->access_token || !$this->secret) {
            LengowMain::log(
                'Please checks your plugin configuration. ID account, access token or secret is empty in store '
                .$id_shop,
                true
            );
            return false;
        }
        if (array_key_exists($this->account_id, $this->account_ids)) {
            LengowMain::log(
                'Account ID '.$this->account_id.' is already used by shop '
                .$this->account_ids[$this->account_id]['name'].' ('
                .$this->account_ids[$this->account_id]['id_shop'].')',
                true
            );
            return false;
        }
        $account_ids[$account_id] = array('id_shop' => $id_shop, 'name' => $name_shop);
        return true;
    }

    /**
     * Change Context for import
     *
     * @param  integer $id_shop Shop Id
     */
    protected function changeContext($id_shop)
    {
        $this->id_shop = $id_shop;
        $this->context = Context::getContext();
        if (_PS_VERSION_ >= '1.5') {
            if ($shop = new Shop($this->id_shop)) {
                $this->context->shop = $shop;
            }
        }
        $this->id_lang       = $this->context->language->id;
        $this->id_shop_group = $this->context->shop->id_shop_group;
    }

    /**
     * Call Lengow order API
     *
     * @param integer $id_current_shop Shop Id
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

            if ($lengow_id != '1300435581913-A') {
                continue;
            }

            if ($this->debug) {
                $lengow_id .= '--'.time();
            }
            // set current order to cancel hook updateOrderStatus
            LengowImport::$current_order = $lengow_id;
            // if order contains no package
            if (count($order_data->packages) == 0) {
                LengowMain::log('create order fail: no package in the order', $this->log_output, $lengow_id);
                continue;
            }
            // start import
            foreach ($order_data->packages as $package_data) {
                $nb_package++;
                // check whether the package contains a shipping address
                if (!isset($package_data->delivery->id)) {
                    LengowMain::log(
                        'create order fail: no delivery address in the order',
                        $this->log_output,
                        $lengow_id
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
                $order = $import_order->importOrder();
                if ($order) {
                    if (isset($order['new'])) {
                        $count_orders_added++;
                    } elseif (isset($order['updated'])) {
                        $count_orders_updated++;
                    }
                }
                // clean process
                LengowImport::$current_order = -1;
                unset($import_order);
                // Sync to lengow if no debug
                if (!$this->debug && isset($order['new'])) {
                    LengowOrder::synchronisedOrder($lengow_id, $result);
                }
            }
            // if limit is set
            if ($this->limit > 0 && $count_orders_added == $this->limit
                || Configuration::get('LENGOW_IMPORT_IN_PROGRESS') <= 0
            ) {
                break;
            }
        }
        return array('new' => $count_orders_added,'update' => $count_orders_updated);
    }

    /**
     * Synchronised order with Lengow API
     *
     * @param string    $lengow_id              Lengow order ID
     * @param string    $marketplace_name       order marketplace
     * @param integer   $prestashop_order_id    Prestashop order ID
     *
     * @return boolean
     */
    protected function synchronisedOrder($lengow_id, $marketplace_name, $prestashop_order_id)
    {
        $order_ids = LengowOrder::getAllOrderIdsFromLengowOrder($lengow_id, $marketplace_name);
        if (count($order_ids) > 0) {
            $presta_ids = array();
            foreach ($order_ids as $order_id) {
                $presta_ids[] = $order_id['id_order'];
            }
            $result = $this->connector->patch(
                '/v3.0/orders',
                array(
                    'account_id'            => $this->account_id,
                    'marketplace_order_id'  => $lengow_id,
                    'marketplace'           => $marketplace_name,
                    'merchant_order_id'     => $presta_ids
                )
            );
            if (is_null($result)
                || (isset($result['detail']) && $result['detail'] == 'Pas trouvé.')
                || isset($result['error'])
            ) {
                LengowMain::log(
                    'WARNING ! Order could NOT be synchronised with Lengow webservice (ID '
                    .$prestashop_order_id.')',
                    $this->debug,
                    $lengow_id
                );
            } else {
                LengowMain::log(
                    'order successfully synchronised with Lengow webservice (ID '.$prestashop_order_id.')',
                    $this->debug,
                    $lengow_id
                );
            }
        }
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
