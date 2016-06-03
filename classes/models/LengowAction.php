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
 * Lengow Action class
 *
 */
class LengowAction
{
    /**
    * integer action state for new action
    */
    const STATE_NEW = 0;

    /**
    * integer action state for action finished
    */
    const STATE_FINISH = 1;

    /**
     * Construct
     */
    public function __construct()
    {

    }

    /**
     * Load action data
     *
     * @param array $row
     */
    public function load($row)
    {
        $this->id = (int)$row['id'];
        $this->id_order = (int)$row['id_order'];
        $this->action_id = (int)$row['action_id'];
        $this->action_type = $row['action_type'];
        $this->retry = $row['retry'];
        $this->parameters = $row['parameters'];
        $this->state = $row['state'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }

    /**
     * Find by ID
     *
     * @param integer $action_id
     *
     * @return boolean
     */
    public function findByActionId($action_id)
    {
        $row = Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'lengow_actions la WHERE action_id = '.(int)$action_id
        );
        if ($row) {
            $this->load($row);
            return true;
        }
        return false;
    }

    /**
     * Find by ID
     *
     * @param integer $action_id
     *
     * @return boolean
     */
    public static function getOrderActiveAction($id_order, $type)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM '._DB_PREFIX_.'lengow_actions la
            WHERE state = '.self::STATE_NEW.' AND  action_type = "'.pSQL($type).'" AND id_order='.(int)$id_order
        );
        $actions = array();
        foreach ($rows as $row) {
            $action = new LengowAction;
            $actions[] = $action->load($row);
        }
        return $actions;
    }

    /**
     * Get active action by shop
     *
     * @param integer   $id_shop
     * @param boolean   $load
     *
     * @return array
     */
    public static function getActiveActionByShop($id_shop, $load = true)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT la.*, o.id_shop FROM '._DB_PREFIX_.'lengow_actions la
            INNER JOIN '._DB_PREFIX_.'orders o ON (o.id_order = la.id_order)
            WHERE id_shop='.(int)$id_shop.' AND state = '.(int)self::STATE_NEW
        );
        if ($load) {
            $actions = array();
            foreach ($rows as $row) {
                $action = new LengowAction;
                $actions[] = $action->load($row);
            }
            return $actions;
        } else {
            return $rows;
        }
    }

    /**
     * Get last order action type to re-send action
     *
     * @param integer $id_order
     *
     * @return mixed
     */
    public static function getLastOrderActionType($id_order)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT action_type FROM '._DB_PREFIX_.'lengow_actions
            WHERE state = '.self::STATE_NEW.' AND id_order='.(int)$id_order
        );
        if (count($rows) > 0) {
            $last_action = end($rows);
            return (string)$last_action['action_type'];
        }
        return false;
    }

    /**
     * Find
     *
     * @param integer $id
     *
     * @return boolean
     */
    public function find($id)
    {
        $row = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'lengow_actions la WHERE id = '.(int)$id);
        if ($row) {
            $this->load($row);
            return true;
        }
        return false;
    }

    /**
     * Create action
     *
     * @param array $params
     */
    public static function createAction($params)
    {
        $insertParams = array(
            'parameters'  => pSQL(Tools::JsonEncode($params['parameters'])),
            'id_order'    => (int)$params['id_order'],
            'action_id'   => (int)$params['action_id'],
            'action_type' => pSQL($params['action_type']),
            'state'       => (int)self::STATE_NEW,
            'created_at'  => date('Y-m-d h:m:i'),
            'updated_at'  => date('Y-m-d h:m:i'),
        );
        if (isset($params['parameters']['line'])) {
            $insertParams['order_line_sku'] = $params['parameters']['line'];
        }
        if (_PS_VERSION_ < '1.5') {
            Db::getInstance()->autoExecute(_DB_PREFIX_.'lengow_actions', $insertParams, 'INSERT');
        } else {
            Db::getInstance()->insert('lengow_actions', $insertParams);
        }
        LengowMain::log(
            'API',
            LengowMain::setLogMessage('log.order_action.call_tracking'),
            false,
            $params['id_order']
        );
    }

    /**
     * Update action
     *
     * @param array $params
     */
    public static function updateAction($params)
    {
        $action = new LengowAction();
        if ($action->findByActionId($params['action_id'])) {
            if ($action->state == self::STATE_NEW) {
                if (_PS_VERSION_ < '1.5') {
                    Db::getInstance()->autoExecute(
                        _DB_PREFIX_.'lengow_actions',
                        array(
                            'retry'      => $action->retry + 1,
                            'updated_at' => date('Y-m-d h:m:i'),
                        ),
                        'UPDATE',
                        'id = ' . $action->id
                    );
                } else {
                    Db::getInstance()->update(
                        'lengow_actions',
                        array(
                            'retry'      => $action->retry + 1,
                            'updated_at' => date('Y-m-d h:m:i'),
                        ),
                        'id = ' . $action->id
                    );
                }
            }
        }
    }

    /**
     * Finish action
     *
     * @param integer $id
     */
    public static function finishAction($id)
    {
        if (_PS_VERSION_ < '1.5') {
            Db::getInstance()->autoExecute(
                _DB_PREFIX_.'lengow_actions',
                array(
                    'state'      => (int)self::STATE_FINISH,
                    'updated_at' => date('Y-m-d h:m:i'),
                ),
                'UPDATE',
                'id = '.(int)$id
            );
        } else {
            Db::getInstance()->update(
                'lengow_actions',
                array(
                    'state'      => (int)self::STATE_FINISH,
                    'updated_at' => date('Y-m-d h:m:i'),
                ),
                'id = '.(int)$id
            );
        }
    }

    /**
     * Removes all actions for one order Prestashop
     *
     * @param integer $id_order     Prestashop order id
     * @param string  $action_type  type (null, ship or cancel)
     *
     * @return boolean
     */
    public static function finishAllActions($id_order, $action_type = null)
    {
        $sql_action_type = is_null($action_type) ? '' : ' AND action_type = "'.pSQL($action_type).'"';
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM '._DB_PREFIX_.'lengow_actions
            WHERE id_order ='.(int)$id_order.' AND state = '.(int)self::STATE_NEW.$sql_action_type
        );
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                self::finishAction($row['id']);
            }
            return true;
        }
        return false;
    }

    /**
     * Remove old actions > 3 days
     *
     * @param string  $action_type  type (null, ship or cancel)
     *
     * @return boolean
     */
    public static function finishAllOldActions($action_type = null)
    {
        // get all old order action (+ 3 days)
        $sql_action_type = is_null($action_type) ? '' : ' AND action_type = "'.pSQL($action_type).'"';
        $date = date('Y-m-d h:m:i', strtotime('-3 days', time()));
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM '._DB_PREFIX_.'lengow_actions
            WHERE created_at <= "'.$date.'" AND state = '.(int)self::STATE_NEW.$sql_action_type
        );
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                // Finish action in lengow_action table
                self::finishAction($row['id']);
                $order_lengow = new LengowOrder($row['id_order']);
                if ($order_lengow->lengow_process_state != LengowOrder::PROCESS_STATE_FINISH) {
                    // If action is denied -> create order error
                    $error_message = LengowMain::setLogMessage('lengow_log.exception.action_is_too_old');
                    LengowOrder::addOrderLog($order_lengow->lengow_id, $error_message, 'send');
                    $decoded_message = LengowMain::decodeLogMessage($error_message, 'en');
                    LengowMain::log(
                        'API-OrderAction',
                        LengowMain::setLogMessage('log.order_action.call_action_failed', array(
                            'decoded_message' => $decoded_message
                        )),
                        false,
                        $order_lengow->lengow_marketplace_sku
                    );
                }
                unset($order_lengow);
            }
            return true;
        }
        return false;
    }

    /**
     * Check if active actions are finished
     *
     * @return bool
     */
    public static function checkFinishAction()
    {
        if (LengowConfiguration::getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED')) {
            return false;
        }

        $shops = LengowShop::findAll();
        foreach ($shops as $shop) {
            if (LengowMain::getShopActive((int)$shop['id_shop'])) {
                $shop = new LengowShop((int)$shop['id_shop']);
                LengowMain::log(
                    'API-OrderAction',
                    LengowMain::setLogMessage('log.order_action.start_for_shop', array(
                        'name_shop' => $shop->name,
                        'id_shop'   => (int)$shop->id
                    ))
                );
                // Get all active actions by shop
                $shop_actions = self::getActiveActionByShop((int)$shop->id, false);
                if (count($shop_actions) == 0) {
                    continue;
                }
                // Get all actions with API for 3 days
                $page = 1;
                $api_actions = array();
                do {
                    $results = LengowConnector::queryApi(
                        'get',
                        '/v3.0/orders/actions/',
                        (int)$shop->id,
                        array(
                            'updated_from' => date('c', strtotime(date('Y-m-d').' -3days')),
                            'updated_to'   => date('c'),
                            'page'         => $page
                        )
                    );
                    if (!is_object($results) || isset($results->error)) {
                        break;
                    }
                    // Construct array actions
                    foreach ($results->results as $action) {
                        if (isset($action->id)) {
                            $api_actions[$action->id] = $action;
                        }
                    }
                    $page++;
                } while ($results->next != null);
                if (count($api_actions) == 0) {
                    continue;
                }
                // Check foreach action if is complete
                foreach ($shop_actions as $action) {
                    if (!isset($api_actions[$action['action_id']])) {
                        continue;
                    }
                    if (isset($api_actions[$action['action_id']]->queued)
                        && isset($api_actions[$action['action_id']]->processed)
                        && isset($api_actions[$action['action_id']]->errors)
                    ) {
                        if ($api_actions[$action['action_id']]->queued == false) {
                            // Finish action in lengow_action table
                            self::finishAction($action['id']);
                            $order_lengow = new LengowOrder($action['id_order']);
                            // Finish all order logs send
                            LengowOrder::finishOrderLogs($order_lengow->lengow_id, 'send');
                            if ($order_lengow->lengow_process_state != LengowOrder::PROCESS_STATE_FINISH) {
                                // If action is accepted -> close order and finish all order actions
                                if ($api_actions[$action['action_id']]->processed == true) {
                                    LengowOrder::updateOrderLengow($order_lengow->lengow_id, array(
                                        'order_process_state' => LengowOrder::PROCESS_STATE_FINISH
                                    ));
                                    self::finishAllActions($order_lengow->id);
                                } else {
                                    // If action is denied -> create order logs and finish all order actions
                                    LengowOrder::addOrderLog(
                                        $order_lengow->lengow_id,
                                        $api_actions[$action['action_id']]->errors,
                                        'send'
                                    );
                                    LengowMain::log(
                                        'API-OrderAction',
                                        LengowMain::setLogMessage('log.order_action.call_action_failed', array(
                                            'decoded_message' => $api_actions[$action['action_id']]->errors
                                        )),
                                        false,
                                        $order_lengow->lengow_marketplace_sku
                                    );
                                }
                            }
                            unset($order_lengow);
                        }
                    }
                }
            }
        }
        // Clean actions after 3 days
        self::finishAllOldActions();
        return true;
    }
}
