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
     * @var boolean use preprod mode
     */
    protected $preprod_mode = false;

    /**
     * @var boolean display log messages
     */
    protected $log_output = false;

    /**
     * @var string marketplace order sku
     */
    protected $marketplace_sku = null;

    /**
     * @var string markeplace name
     */
    protected $marketplace_name = null;

    /**
     * @var integer delivery address id
     */
    protected $delivery_address_id = null;

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
     * @var boolean import one order
     */
    protected $import_one_order = false;

    /**
     * @var array account ids already imported
     */
    protected $account_ids = array();

    /**
     * @var boolean import is processing
     */
    public static $processing;

    /**
     * @var string order id being imported
     */
    public static $current_order = -1;

    /**
     * @var array valid states lengow to create a Lengow order
     */
    public static $LENGOW_STATES = array(
        'accepted',
        'waiting_shipment',
        'shipped',
        'closed'
    );

    /**
     * Construct the import manager
     *
     * @param array params optional options
     * string    $marketplace_sku    lengow marketplace order id to import
     * string    $marketplace_name   lengow marketplace name to import
     * integer   $shop_id            Id shop for current import
     * boolean   $force_product      force import of products
     * boolean   $preprod_mode       preprod mode
     * string    $date_from          starting import date
     * string    $date_to            ending import date
     * integer   $limit              number of orders to import
     * boolean   $log_output         display log messages
     */
    public function __construct($params = array())
    {
        // params for re-import order
        if (array_key_exists('marketplace_sku', $params)
            && array_key_exists('marketplace_name', $params)
            && array_key_exists('delivery_address_id', $params)
            && array_key_exists('shop_id', $params)
        ) {
            if (isset($params['id_order_lengow'])) {
                $this->id_order_lengow  = (int)$params['id_order_lengow'];
            }
            $this->re_import_type       = (int)$params['type'];
            $this->marketplace_sku      = (string)$params['marketplace_sku'];
            $this->marketplace_name     = (string)$params['marketplace_name'];
            $this->delivery_address_id  = $params['delivery_address_id'];
            $this->limit                = 1;
            $this->import_one_order     = true;
        } else {
            $this->marketplace_sku = null;
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
        }
        // get other params
        $this->preprod_mode = (
            isset($params['preprod_mode'])
            ? (bool)$params['preprod_mode']
            : (bool)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED')
        );
        $this->type_import = (isset($params['type']) ? $params['type'] : 'manual');
        $this->force_product = (
            isset($params['force_product'])
            ? (bool)$params['force_product']
            : (bool)LengowConfiguration::getGlobalValue('LENGOW_IMPORT_FORCE_PRODUCT')
        );
        $this->log_output = (isset($params['log_output']) ? (bool)$params['log_output'] : false);
        $this->id_shop = (isset($params['shop_id']) ? (int)$params['shop_id'] : null);
    }

    /**
     * Excute import : fetch orders and import them
     *
     * @return array
     */
    public function exec()
    {
        $order_new      = 0;
        $order_update   = 0;
        $order_error    = 0;
        $error          = array();
        $global_error   = false;
        // clean logs
        LengowMain::cleanLog();
        if (LengowImport::isInProcess() && !$this->preprod_mode && !$this->import_one_order) {
            $global_error = LengowMain::setLogMessage('lengow_log.error.import_in_progress');
            LengowMain::log('Import', $global_error, $this->log_output);
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage('lengow_log.error.rest_time_to_import', array(
                    'rest_time' => LengowImport::restTimeToImport()
                )),
                $this->log_output
            );
            $error[0] = $global_error;
            if (isset($this->id_order_lengow) && $this->id_order_lengow) {
                LengowOrder::finishOrderLogs($this->id_order_lengow, $this->re_import_type);
                LengowOrder::addOrderLog($this->id_order_lengow, $global_error, $this->re_import_type);
            }
        } else {
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage('log.import.start', array('type' => $this->type_import)),
                $this->log_output
            );
            if ($this->preprod_mode) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage('log.import.preprod_mode_active'),
                    $this->log_output
                );
            }
            LengowImport::setInProcess();
            LengowMain::disableMail();
            // udpate last import date
            lengowMain::updateDateImport($this->type_import);
            // get all shops for import
            $shops = LengowShop::findAll(true);
            foreach ($shops as $shop) {
                if (!is_null($this->id_shop) && (int)$shop['id_shop'] != $this->id_shop) {
                    continue;
                }
                $shop = new LengowShop((int)$shop['id_shop']);
                if (LengowMain::getShopActive((int)$shop->id)) {
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage('log.import.start_for_shop', array(
                            'name_shop' => $shop->name,
                            'id_shop'   => (int)$shop->id
                        )),
                        $this->log_output
                    );
                    try {
                        // check account ID, Access Token and Secret
                        $error_credential = $this->checkCredentials((int)$shop->id, $shop->name);
                        if ($error_credential !== true) {
                            LengowMain::log('Import', $error_credential, $this->log_output);
                            $error[(int)$shop->id] = $error_credential;
                            continue;
                        }
                        // change context with current shop id
                        $this->changeContext((int)$shop->id);
                        // get orders from Lengow API
                        $orders = $this->getOrdersFromApi($shop);
                        $total_orders = count($orders);
                        if ($this->import_one_order) {
                            LengowMain::log(
                                'Import',
                                LengowMain::setLogMessage('log.import.find_one_order', array(
                                    'nb_order'          => $total_orders,
                                    'marketplace_sku'   => $this->marketplace_sku,
                                    'markeplace_name'   => $this->marketplace_name,
                                    'account_id'        => $this->account_id
                                )),
                                $this->log_output
                            );
                        } else {
                            LengowMain::log(
                                'Import',
                                LengowMain::setLogMessage('log.import.find_all_orders', array(
                                    'nb_order'   => $total_orders,
                                    'account_id' => $this->account_id
                                )),
                                $this->log_output
                            );
                        }
                        if ($total_orders<=0 && $this->import_one_order) {
                            throw new LengowException('lengow_log.error.order_not_found');
                        } elseif ($total_orders <= 0) {
                            continue;
                        }
                        if (isset($this->id_order_lengow) && $this->id_order_lengow) {
                            LengowOrder::finishOrderLogs($this->id_order_lengow, $this->re_import_type);
                        }
                        // import orders in prestashop
                        $result = $this->importOrders($orders, (int)$shop->id);
                        if (!$this->import_one_order) {
                            $order_new      += $result['order_new'];
                            $order_update   += $result['order_update'];
                            $order_error    += $result['order_error'];
                        }
                    } catch (LengowException $e) {
                        $error_message = $e->getMessage();
                    } catch (Exception $e) {
                        $error_message = '[Prestashop error] "'.$e->getMessage().'" '.$e->getFile().' | '.$e->getLine();
                    }
                    if (isset($error_message)) {
                        if (isset($this->id_order_lengow) && $this->id_order_lengow) {
                            LengowOrder::finishOrderLogs($this->id_order_lengow, $this->re_import_type);
                            LengowOrder::addOrderLog($this->id_order_lengow, $error_message, $this->re_import_type);
                        }
                        $decoded_message = LengowMain::decodeLogMessage($error_message, 'en');
                        LengowMain::log(
                            'Import',
                            LengowMain::setLogMessage('log.import.import_failed', array(
                                'decoded_message' => $decoded_message
                            )),
                            $this->log_output
                        );
                        $error[(int)$shop->id] = $e->getMessage();
                        continue;
                    }
                }
                unset($shop);
            }
            if (!$this->import_one_order) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage('lengow_log.error.nb_order_imported', array(
                        'nb_order' => $order_new
                    )),
                    $this->log_output
                );
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage('lengow_log.error.nb_order_updated', array(
                        'nb_order' => $order_update
                    )),
                    $this->log_output
                );
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage('lengow_log.error.nb_order_with_error', array(
                        'nb_order' => $order_error
                    )),
                    $this->log_output
                );
            }
            // finish import process
            LengowImport::setEnd();
            LengowMain::log(
                'Import',
                LengowMain::setLogMessage('log.import.end', array('type' => $this->type_import)),
                $this->log_output
            );
            // sending email in error for orders
            if (LengowConfiguration::getGlobalValue('LENGOW_REPORT_MAIL_ENABLED')
                && !$this->preprod_mode
                && !$this->import_one_order
            ) {
                LengowMain::sendMailAlert($this->log_output);
            }
        }
        if ($this->import_one_order) {
            $result['error'] = $error;
            return $result;
        } else {
            return array(
                'order_new'     => $order_new,
                'order_update'  => $order_update,
                'order_error'   => $order_error,
                'error'         => $error
            );
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
            $message = LengowMain::setLogMessage('lengow_log.error.account_id_empty', array(
                'name_shop' => $name_shop,
                'id_shop'   => $id_shop
            ));
            return $message;
        }
        if (array_key_exists($this->account_id, $this->account_ids)) {
            $message = LengowMain::setLogMessage('lengow_log.error.account_id_already_used', array(
                'account_id' => $this->account_id,
                'name_shop'  => $this->account_ids[$this->account_id]['name'],
                'id_shop'    => $this->account_ids[$this->account_id]['id_shop'],
            ));
            return $message;
        }
        $this->account_ids[$this->account_id] = array('id_shop' => $id_shop, 'name' => $name_shop);
        return true;
    }

    /**
     * Change Context for import
     *
     * @param  integer $id_shop Shop Id
     */
    protected function changeContext($id_shop)
    {
        $this->context = Context::getContext();
        if (_PS_VERSION_ >= '1.5') {
            if ($shop = new Shop($id_shop)) {
                $this->context->shop = $shop;
            }
        }
        $this->id_lang       = $this->context->language->id;
        $this->id_shop_group = $this->context->shop->id_shop_group;
    }

    /**
     * Call Lengow order API
     *
     * @param  LengowShop $shop
     *
     * @return mixed
     */
    protected function getOrdersFromApi($shop)
    {
        $page = 1;
        $orders = array();

        if (LengowCheck::isValidAuth((int)$shop->id)) {
            $this->connector  = new LengowConnector($this->access_token, $this->secret);
            if ($this->import_one_order) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage('log.import.connector_get_order', array(
                        'marketplace_sku' => $this->marketplace_sku,
                        'markeplace_name' => $this->marketplace_name
                    )),
                    $this->log_output
                );
            } else {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage('log.import.connector_get_all_order', array(
                        'date_from'  => date('Y-m-d', strtotime((string)$this->date_from)),
                        'date_to'    => date('Y-m-d', strtotime((string)$this->date_to)),
                        'account_id' => $this->account_id
                    )),
                    $this->log_output
                );
            }
            do {
                if ($this->import_one_order) {
                    $results = $this->connector->get(
                        '/v3.0/orders',
                        array(
                            'marketplace_order_id'  => $this->marketplace_sku,
                            'marketplace'           => $this->marketplace_name,
                            'account_id'            => $this->account_id,
                            'page'                  => $page,
                            'marketplace_order_date_from' => '2015-01-01T00:00:00+01:00',
                            ),
                        'stream'
                    );
                } else {
                    $results = $this->connector->get(
                        '/v3.0/orders',
                        array(
                            'updated_from'          => $this->date_from,
                            'marketplace_order_date_from'          => $this->date_from,
                            'updated_to'            => $this->date_to,
                            'account_id'            => $this->account_id,
                            'page'                  => $page
                        ),
                        'stream'
                    );
                }
                if (is_null($results)) {
                    throw new LengowException(
                        LengowMain::setLogMessage('lengow_log.exception.no_connection_webservice', array(
                            'name_shop' => $shop->name,
                            'id_shop'   => (int)$shop->id
                        ))
                    );
                }
                $results = Tools::jsonDecode($results);
                if (!is_object($results)) {
                    throw new LengowException(
                        LengowMain::setLogMessage('lengow_log.exception.no_connection_webservice', array(
                            'name_shop' => $shop->name,
                            'id_shop'   => (int)$shop->id
                        ))
                    );
                }
                if (isset($results->error)) {
                    throw new LengowException(
                        LengowMain::setLogMessage('lengow_log.exception.error_lengow_webservice', array(
                            'error_code'    => $results->error->code,
                            'error_message' => $results->error->message,
                            'name_shop'     => $shop->name,
                            'id_shop'       => (int)$shop->id
                        ))
                    );
                }
                // Construct array orders
                foreach ($results->results as $order) {
                    $orders[] = $order;
                }
                $page++;
            } while ($results->next != null);
        } else {
            throw new LengowException(
                LengowMain::setLogMessage('lengow_log.exception.crendentials_not_valid', array(
                    'name_shop' => $shop->name,
                    'id_shop'   => (int)$shop->id
                ))
            );
        }
        return $orders;
    }

    /**
     * Create or update order in prestashop
     *
     * @param mixed     $orders     API orders
     * @param integer   $id_shop    Shop Id
     *
     * @return mixed
     */
    protected function importOrders($orders, $id_shop)
    {
        $order_new       = 0;
        $order_update    = 0;
        $order_error     = 0;
        $import_finished = false;
        foreach ($orders as $order_data) {
            LengowImport::setInProcess();
            $nb_package = 0;
            $marketplace_sku = (string)$order_data->marketplace_order_id;
            if ($this->preprod_mode) {
                $marketplace_sku .= '--'.time();
            }
            // set current order to cancel hook updateOrderStatus
            LengowImport::$current_order = $marketplace_sku;
            // if order contains no package
            if (count($order_data->packages) == 0) {
                LengowMain::log(
                    'Import',
                    LengowMain::setLogMessage('log.import.error_no_package'),
                    $this->log_output,
                    $marketplace_sku
                );
                continue;
            }
            // start import
            foreach ($order_data->packages as $package_data) {
                $nb_package++;
                // check whether the package contains a shipping address
                if (!isset($package_data->delivery->id)) {
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage('log.import.error_no_delivery_address'),
                        $this->log_output,
                        $marketplace_sku
                    );
                    continue;
                }
                $package_delivery_address_id = (int)$package_data->delivery->id;
                $first_package = ($nb_package > 1 ? false : true);
                // check the package for re-import order
                if ($this->import_one_order) {
                    if (!is_null($this->delivery_address_id)
                        && $this->delivery_address_id != $package_delivery_address_id
                    ) {
                        LengowMain::log(
                            'Import',
                            LengowMain::setLogMessage('log.import.error_wrong_package_number'),
                            $this->log_output,
                            $marketplace_sku
                        );
                        continue;
                    }
                }
                try {
                    // try to import or update order
                    $import_order = new LengowImportOrder(
                        array(
                            'context'               => $this->context,
                            'id_shop'               => $id_shop,
                            'id_shop_group'         => $this->id_shop_group,
                            'id_lang'               => $this->id_lang,
                            'force_product'         => $this->force_product,
                            'preprod_mode'          => $this->preprod_mode,
                            'log_output'            => $this->log_output,
                            'marketplace_sku'       => $marketplace_sku,
                            'delivery_address_id'   => $package_delivery_address_id,
                            'order_data'            => $order_data,
                            'package_data'          => $package_data,
                            'first_package'         => $first_package
                        )
                    );
                    $order = $import_order->importOrder();
                } catch (LengowException $e) {
                    $error_message = $e->getMessage();
                } catch (Exception $e) {
                    $error_message = '[Prestashop error]: "'.$e->getMessage().'" '.$e->getFile().' | '.$e->getLine();
                }
                if (isset($error_message)) {
                    $decoded_message = LengowMain::decodeLogMessage($error_message, 'en');
                    LengowMain::log(
                        'Import',
                        LengowMain::setLogMessage('log.import.order_import_failed', array(
                            'decoded_message' => $decoded_message
                        )),
                        $this->log_output,
                        $marketplace_sku
                    );
                    continue;
                }
                // Sync to lengow if no preprod_mode
                if (!$this->preprod_mode && $order['order_new'] == true) {
                    $lengow_order = new LengowOrder((int)$order['order_id']);
                    $lengow_order->synchronizeOrder($this->connector, $this->log_output);
                    unset($lengow_order);
                }
                // if re-import order -> return order informations
                if ($this->import_one_order) {
                    return $order;
                }
                if ($order) {
                    if ($order['order_new'] == true) {
                        $order_new++;
                    } elseif ($order['order_update'] == true) {
                        $order_update++;
                    } elseif ($order['order_error'] == true) {
                        $order_error++;
                    }
                }
                // clean process
                LengowImport::$current_order = -1;
                unset($import_order);
                unset($order);
                // if limit is set
                if ($this->limit > 0 && $order_new == $this->limit) {
                    $import_finished = true;
                    break;
                }

                //check if order action is finish (Ship / Cancel)
                LengowMarketplace::checkFinishAction();

            }
            if ($import_finished) {
                break;
            }
        }
        return array(
            'order_new'     => $order_new,
            'order_update'  => $order_update,
            'order_error'   => $order_error
        );
    }

    /**
     * Check if order status is valid for import
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

    /**
     * Check if import is already in process
     *
     * @return boolean
     */
    public static function isInProcess()
    {
        $timestamp = LengowConfiguration::getGlobalValue('LENGOW_IMPORT_IN_PROGRESS');
        if ($timestamp > 0) {
            // security check : if last import is more than 10 min old => authorize new import to be launched
            if (($timestamp + (60 * 1)) < time()) {
                LengowImport::setEnd();
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * v3
     * Get Rest time to make re import order
     *
     * @return boolean
     */
    public static function restTimeToImport()
    {
        $timestamp = LengowConfiguration::getGlobalValue('LENGOW_IMPORT_IN_PROGRESS');
        if ($timestamp > 0) {
            return $timestamp + (60 * 1) - time();
        }
        return false;
    }

    /**
     * Set import to "in process" state
     *
     * @return boolean
     */
    public static function setInProcess()
    {
        LengowImport::$processing = true;
        return LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', time());
    }

    /**
     * Set import to finished
     *
     * @return boolean
     */
    public static function setEnd()
    {
        LengowImport::$processing = false;
        return LengowConfiguration::updateGlobalValue('LENGOW_IMPORT_IN_PROGRESS', -1);
    }
}
