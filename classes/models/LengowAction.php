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
 * Lengow Action Class
 */
class LengowAction
{
    /**
     * @var integer action state for new action
     */
    const STATE_NEW = 0;

    /**
     * @var integer action state for action finished
     */
    const STATE_FINISH = 1;

    /**
     * @var integer Lengow action record id
     */
    public $id;

    /**
     * @var integer Prestashop order id
     */
    public $idOrder;

    /**
     * @var integer Lengow action id
     */
    public $actionId;

    /**
     * @var string action type (ship or cancel)
     */
    public $actionType;

    /**
     * @var integer Lengow order record id
     */
    public $retry;

    /**
     * @var string all parameters in json format
     */
    public $parameters;

    /**
     * @var integer action state
     */
    public $state;

    /**
     * @var string action created at
     */
    public $createdAt;

    /**
     * @var string updated at
     */
    public $updatedAt;

    /**
     * Load action data
     *
     * @param array $row All action datas
     */
    public function load($row)
    {
        $this->id = (int)$row['id'];
        $this->idOrder = (int)$row['id_order'];
        $this->actionId = (int)$row['action_id'];
        $this->actionType = $row['action_type'];
        $this->retry = $row['retry'];
        $this->parameters = $row['parameters'];
        $this->state = $row['state'];
        $this->createdAt = $row['created_at'];
        $this->updatedAt = $row['updated_at'];
    }

    /**
     * Find by ID
     *
     * @param integer $actionId Lengow action id
     *
     * @return boolean
     */
    public function findByActionId($actionId)
    {
        $row = Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions la WHERE action_id = ' . (int)$actionId
        );
        if ($row) {
            $this->load($row);
            return true;
        }
        return false;
    }

    /**
     * Find active actions by order id
     *
     * @param integer $idOrder Prestashop order id
     * @param string $actionType action type (null, ship or cancel)
     * @param boolean $load load LengowAction or not
     *
     * @return array|false
     */
    public static function getActiveActionByOrderId($idOrder, $actionType = null, $load = true)
    {
        $sqlType = is_null($actionType) ? '' : ' AND  action_type = "' . pSQL($actionType) . '"';
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions la
            WHERE state = ' . self::STATE_NEW . $sqlType . ' AND id_order=' . (int)$idOrder
        );
        if (count($rows) > 0) {
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
        return false;
    }

    /**
     * Get all active actions
     *
     * @param boolean $load load LengowAction or not
     *
     * @return array|false
     */
    public static function getActiveActions($load = true)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions WHERE state = ' . (int)self::STATE_NEW
        );
        if (count($rows) > 0) {
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
        return false;
    }

    /**
     * Get last order action type to re-send action
     *
     * @param integer $idOrder Prestashop order id
     *
     * @return array|false
     */
    public static function getLastOrderActionType($idOrder)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT action_type FROM ' . _DB_PREFIX_ . 'lengow_actions
            WHERE state = ' . self::STATE_NEW . ' AND id_order=' . (int)$idOrder
        );
        if (count($rows) > 0) {
            $lastAction = end($rows);
            return (string)$lastAction['action_type'];
        }
        return false;
    }

    /**
     * Find
     *
     * @param integer $id Lengow action id
     *
     * @return boolean
     */
    public function find($id)
    {
        $row = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions la WHERE id = ' . (int)$id);
        if ($row) {
            $this->load($row);
            return true;
        }
        return false;
    }

    /**
     * Create action
     *
     * @param array $params action params
     */
    public static function createAction($params)
    {
        $insertParams = array(
            'parameters' => pSQL(Tools::jsonEncode($params['parameters'])),
            'id_order' => (int)$params['id_order'],
            'action_id' => (int)$params['action_id'],
            'action_type' => pSQL($params['action_type']),
            'state' => (int)self::STATE_NEW,
            'created_at' => date('Y-m-d h:m:i'),
            'updated_at' => date('Y-m-d h:m:i'),
        );
        if (isset($params['parameters']['line'])) {
            $insertParams['order_line_sku'] = $params['parameters']['line'];
        }
        if (_PS_VERSION_ < '1.5') {
            Db::getInstance()->autoExecute(_DB_PREFIX_ . 'lengow_actions', $insertParams, 'INSERT');
        } else {
            Db::getInstance()->insert('lengow_actions', $insertParams);
        }
        LengowMain::log(
            'API-OrderAction',
            LengowMain::setLogMessage('log.order_action.action_saved'),
            false,
            $params['marketplace_sku']
        );
    }

    /**
     * Update action
     *
     * @param array $params action params
     *
     * @return boolean
     */
    public static function updateAction($params)
    {
        $action = new LengowAction();
        if ($action->findByActionId($params['action_id'])) {
            if ($action->state == self::STATE_NEW) {
                if (_PS_VERSION_ < '1.5') {
                    Db::getInstance()->autoExecute(
                        _DB_PREFIX_ . 'lengow_actions',
                        array(
                            'retry' => $action->retry + 1,
                            'updated_at' => date('Y-m-d h:m:i'),
                        ),
                        'UPDATE',
                        'id = ' . $action->id
                    );
                    return true;
                } else {
                    Db::getInstance()->update(
                        'lengow_actions',
                        array(
                            'retry' => $action->retry + 1,
                            'updated_at' => date('Y-m-d h:m:i'),
                        ),
                        'id = ' . $action->id
                    );
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Finish action
     *
     * @param integer $id Lengow action id
     */
    public static function finishAction($id)
    {
        if (_PS_VERSION_ < '1.5') {
            Db::getInstance()->autoExecute(
                _DB_PREFIX_ . 'lengow_actions',
                array(
                    'state' => (int)self::STATE_FINISH,
                    'updated_at' => date('Y-m-d h:m:i'),
                ),
                'UPDATE',
                'id = ' . (int)$id
            );
        } else {
            Db::getInstance()->update(
                'lengow_actions',
                array(
                    'state' => (int)self::STATE_FINISH,
                    'updated_at' => date('Y-m-d h:m:i'),
                ),
                'id = ' . (int)$id
            );
        }
    }

    /**
     * Removes all actions for one order Prestashop
     *
     * @param integer $idOrder Prestashop order id
     * @param string $actionType action type (null, ship or cancel)
     *
     * @return boolean
     */
    public static function finishAllActions($idOrder, $actionType = null)
    {
        $sqlActionType = is_null($actionType) ? '' : ' AND action_type = "' . pSQL($actionType) . '"';
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions
            WHERE id_order =' . (int)$idOrder . ' AND state = ' . (int)self::STATE_NEW . $sqlActionType
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
     * Check if active actions are finished
     *
     * @return boolean
     */
    public static function checkFinishAction()
    {
        if (LengowConfiguration::getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED')) {
            return false;
        }
        LengowMain::log('API-OrderAction', LengowMain::setLogMessage('log.order_action.check_completed_action'));
        // Get all active actions by shop
        $activeActions = self::getActiveActions(false);
        if (!$activeActions) {
            return true;
        }
        // Get all actions with API for 3 days
        $page = 1;
        $apiActions = array();
        do {
            $results = LengowConnector::queryApi(
                'get',
                '/v3.0/orders/actions/',
                array(
                    'updated_from' => date('c', strtotime(date('Y-m-d') . ' -3days')),
                    'updated_to' => date('c'),
                    'page' => $page
                )
            );
            if (!is_object($results) || isset($results->error)) {
                break;
            }
            // Construct array actions
            foreach ($results->results as $action) {
                if (isset($action->id)) {
                    $apiActions[$action->id] = $action;
                }
            }
            $page++;
        } while ($results->next != null);
        if (count($apiActions) == 0) {
            return false;
        }
        // Check foreach action if is complete
        foreach ($activeActions as $action) {
            if (!isset($apiActions[$action['action_id']])) {
                continue;
            }
            if (isset($apiActions[$action['action_id']]->queued)
                && isset($apiActions[$action['action_id']]->processed)
                && isset($apiActions[$action['action_id']]->errors)
            ) {
                if ($apiActions[$action['action_id']]->queued == false) {
                    // Finish action in lengow_action table
                    self::finishAction($action['id']);
                    $orderLengow = new LengowOrder($action['id_order']);
                    // Finish all order logs send
                    LengowOrder::finishOrderLogs($orderLengow->lengowId, 'send');
                    if ($orderLengow->lengowProcessState != LengowOrder::PROCESS_STATE_FINISH) {
                        // If action is accepted -> close order and finish all order actions
                        if ($apiActions[$action['action_id']]->processed == true) {
                            LengowOrder::updateOrderLengow(
                                $orderLengow->lengowId,
                                array('order_process_state' => LengowOrder::PROCESS_STATE_FINISH)
                            );
                            self::finishAllActions($orderLengow->id);
                        } else {
                            // If action is denied -> create order logs and finish all order actions
                            LengowOrder::addOrderLog(
                                $orderLengow->lengowId,
                                $apiActions[$action['action_id']]->errors,
                                'send'
                            );
                            LengowMain::log(
                                'API-OrderAction',
                                LengowMain::setLogMessage(
                                    'log.order_action.call_action_failed',
                                    array('decoded_message' => $apiActions[$action['action_id']]->errors)
                                ),
                                false,
                                $orderLengow->lengowMarketplaceSku
                            );
                        }
                    }
                    unset($orderLengow);
                }
            }
        }
        return true;
    }

    /**
     * Remove old actions > 3 days
     *
     * @param string $actionType action type (null, ship or cancel)
     *
     * @return boolean
     */
    public static function checkOldAction($actionType = null)
    {
        LengowMain::log('API-OrderAction', LengowMain::setLogMessage('log.order_action.check_old_action'));
        // get all old order action (+ 3 days)
        $sqlActionType = is_null($actionType) ? '' : ' AND action_type = "' . pSQL($actionType) . '"';
        $date = date('Y-m-d h:m:i', strtotime('-3 days', time()));
        $rows = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions
            WHERE created_at <= "' . $date . '" AND state = ' . (int)self::STATE_NEW . $sqlActionType
        );
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                // Finish action in lengow_action table
                self::finishAction($row['id']);
                $orderLengow = new LengowOrder($row['id_order']);
                if ($orderLengow->lengowProcessState != LengowOrder::PROCESS_STATE_FINISH) {
                    // If action is denied -> create order error
                    $errorMessage = LengowMain::setLogMessage('lengow_log.exception.action_is_too_old');
                    LengowOrder::addOrderLog($orderLengow->lengowId, $errorMessage, 'send');
                    $decodedMessage = LengowMain::decodeLogMessage($errorMessage, 'en');
                    LengowMain::log(
                        'API-OrderAction',
                        LengowMain::setLogMessage(
                            'log.order_action.call_action_failed',
                            array('decoded_message' => $decodedMessage)
                        ),
                        false,
                        $orderLengow->lengowMarketplaceSku
                    );
                }
                unset($orderLengow);
            }
            return true;
        }
        return false;
    }

    /**
     * Check if actions are not sent
     *
     * @return boolean
     */
    public static function checkActionNotSent()
    {
        if (LengowConfiguration::getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED')) {
            return false;
        }
        LengowMain::log('API-OrderAction', LengowMain::setLogMessage('log.order_action.check_action_not_sent'));
        // Get unsent orders by store
        $unsentOrders = LengowOrder::getUnsentOrders();
        if ($unsentOrders) {
            foreach ($unsentOrders as $idOrder => $actionType) {
                $lengowOrder = new LengowOrder($idOrder);
                $lengowOrder->callAction($actionType);
            }
        }
        return true;
    }
}
