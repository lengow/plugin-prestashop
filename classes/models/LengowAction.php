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
     * @var string action type ship
     */
    const TYPE_SHIP = 'ship';

    /**
     * @var string action type cancel
     */
    const TYPE_CANCEL = 'cancel';

    /**
     * @var string action argument action type
     */
    const ARG_ACTION_TYPE = 'action_type';

    /**
     * @var string action argument line
     */
    const ARG_LINE = 'line';

    /**
     * @var string action argument carrier
     */
    const ARG_CARRIER = 'carrier';

    /**
     * @var string action argument carrier name
     */
    const ARG_CARRIER_NAME = 'carrier_name';

    /**
     * @var string action argument custom carrier
     */
    const ARG_CUSTOM_CARRIER = 'custom_carrier';

    /**
     * @var string action argument shipping method
     */
    const ARG_SHIPPING_METHOD = 'shipping_method';

    /**
     * @var string action argument tracking number
     */
    const ARG_TRACKING_NUMBER = 'tracking_number';

    /**
     * @var string action argument tracking url
     */
    const ARG_TRACKING_URL = 'tracking_url';

    /**
     * @var string action argument shipping price
     */
    const ARG_SHIPPING_PRICE = 'shipping_price';

    /**
     * @var string action argument shipping date
     */
    const ARG_SHIPPING_DATE = 'shipping_date';

    /**
     * @var string action argument delivery date
     */
    const ARG_DELIVERY_DATE = 'delivery_date';

    /**
     * @var integer max interval time for action synchronisation (3 days)
     */
    const MAX_INTERVAL_TIME = 259200;

    /**
     * @var integer security interval time for action synchronisation (2 hours)
     */
    const SECURITY_INTERVAL_TIME = 7200;

    /**
     * @var array Parameters to delete for Get call
     */
    public static $getParamsToDelete = array(
        self::ARG_SHIPPING_DATE,
        self::ARG_DELIVERY_DATE,
    );

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
        $this->state = (int)$row['state'];
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
     * Get action by action id
     *
     * @param integer $actionId Lengow action id
     *
     * @return integer|false
     */
    public static function getActionByActionId($actionId)
    {
        $row = Db::getInstance()->getRow(
            'SELECT id FROM ' . _DB_PREFIX_ . 'lengow_actions WHERE action_id = ' . (int)$actionId
        );
        if ($row) {
            return (int)$row['id'];
        }
        return false;
    }

    /**
     * Find active actions by order id
     *
     * @param integer $idOrder Prestashop order id
     * @param string|null $actionType action type (ship or cancel)
     * @param boolean $load load LengowAction or not
     *
     * @return array|false
     */
    public static function getActiveActionByOrderId($idOrder, $actionType = null, $load = true)
    {
        try {
            $sqlType = $actionType === null ? '' : ' AND  action_type = "' . pSQL($actionType) . '"';
            $rows = Db::getInstance()->executeS(
                'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions la
                WHERE state = ' . (int)self::STATE_NEW . $sqlType . ' AND id_order=' . (int)$idOrder
            );
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        if (!empty($rows)) {
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
        try {
            $rows = Db::getInstance()->executeS(
                'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions WHERE state = ' . (int)self::STATE_NEW
            );
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        if (!empty($rows)) {
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
     * @return string|false
     */
    public static function getLastOrderActionType($idOrder)
    {
        try {
            $rows = Db::getInstance()->executeS(
                'SELECT action_type FROM ' . _DB_PREFIX_ . 'lengow_actions
                WHERE state = ' . (int)self::STATE_NEW . ' AND id_order=' . (int)$idOrder
            );
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        if (!empty($rows)) {
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
     * Indicates whether an action can be created if it does not already exist
     *
     * @param array $params all available values
     * @param LengowOrder $lengowOrder Lengow order instance
     *
     * @throws LengowException
     *
     * @return boolean
     */
    public static function canSendAction($params, $lengowOrder)
    {
        $sendAction = true;
        $getParams = array_merge($params, array('queued' => 'True'));
        // array key deletion for GET verification
        foreach (self::$getParamsToDelete as $param) {
            if (isset($getParams[$param])) {
                unset($getParams[$param]);
            }
        }
        $result = LengowConnector::queryApi(LengowConnector::GET, LengowConnector::API_ORDER_ACTION, $getParams);
        if (isset($result->error) && isset($result->error->message)) {
            throw new LengowException($result->error->message);
        }
        if (isset($result->count) && $result->count > 0) {
            foreach ($result->results as $row) {
                $orderActionId = self::getActionByActionId($row->id);
                if ($orderActionId) {
                    $update = self::updateAction(
                        array(
                            'id_order' => $lengowOrder->id,
                            'action_type' => $params[self::ARG_ACTION_TYPE],
                            'action_id' => $row->id,
                            'parameters' => $params,
                        )
                    );
                    if ($update) {
                        $sendAction = false;
                    }
                } else {
                    // if update doesn't work, create new action
                    self::createAction(
                        array(
                            'id_order' => $lengowOrder->id,
                            'action_type' => $params[self::ARG_ACTION_TYPE],
                            'action_id' => $row->id,
                            'parameters' => $params,
                            'marketplace_sku' => $lengowOrder->lengowMarketplaceSku,
                        )
                    );
                    $sendAction = false;
                }
            }
        }
        return $sendAction;
    }

    /**
     * Send a new action on the order via the Lengow API
     *
     * @param array $params all available values
     * @param LengowOrder $lengowOrder Lengow order instance
     *
     * @throws LengowException
     */
    public static function sendAction($params, $lengowOrder)
    {
        if (!LengowConfiguration::debugModeIsActive()) {
            $result = LengowConnector::queryApi(LengowConnector::POST, LengowConnector::API_ORDER_ACTION, $params);
            if (isset($result->id)) {
                self::createAction(
                    array(
                        'id_order' => $lengowOrder->id,
                        'action_type' => $params[self::ARG_ACTION_TYPE],
                        'action_id' => $result->id,
                        'parameters' => $params,
                        'marketplace_sku' => $lengowOrder->lengowMarketplaceSku,
                    )
                );
            } else {
                if ($result && $result !== null) {
                    $message = LengowMain::setLogMessage(
                        'lengow_log.exception.action_not_created',
                        array('error_message' => Tools::jsonEncode($result))
                    );
                } else {
                    // generating a generic error message when the Lengow API is unavailable
                    $message = LengowMain::setLogMessage('lengow_log.exception.action_not_created_api');
                }
                throw new LengowException($message);
            }
        }
        // create log for call action
        $paramList = false;
        foreach ($params as $param => $value) {
            $paramList .= !$paramList ? '"' . $param . '": ' . $value : ' -- "' . $param . '": ' . $value;
        }
        LengowMain::log(
            LengowLog::CODE_ACTION,
            LengowMain::setLogMessage('log.order_action.call_tracking', array('parameters' => $paramList)),
            false,
            $lengowOrder->lengowMarketplaceSku
        );
    }


    /**
     * Create action
     *
     * @param array $params action params
     *
     * @return boolean
     */
    public static function createAction($params)
    {
        $insertParams = array(
            'parameters' => pSQL(Tools::jsonEncode($params['parameters'])),
            'id_order' => (int)$params['id_order'],
            'action_id' => (int)$params['action_id'],
            'action_type' => pSQL($params['action_type']),
            'state' => (int)self::STATE_NEW,
            'created_at' => date('Y-m-d H:i:s'),
        );
        if (isset($params['parameters'][self::ARG_LINE])) {
            $insertParams['order_line_sku'] = $params['parameters'][self::ARG_LINE];
        }
        try {
            if (_PS_VERSION_ < '1.5') {
                Db::getInstance()->autoExecute(_DB_PREFIX_ . 'lengow_actions', $insertParams, 'INSERT');
            } else {
                Db::getInstance()->insert('lengow_actions', $insertParams);
            }
            LengowMain::log(
                LengowLog::CODE_ACTION,
                LengowMain::setLogMessage('log.order_action.action_saved'),
                false,
                $params['marketplace_sku']
            );
            return true;
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
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
            if ($action->state === self::STATE_NEW) {
                if (_PS_VERSION_ < '1.5') {
                    try {
                        return Db::getInstance()->autoExecute(
                            _DB_PREFIX_ . 'lengow_actions',
                            array(
                                'retry' => $action->retry + 1,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ),
                            'UPDATE',
                            'id = ' . $action->id
                        );
                    } catch (PrestaShopDatabaseException $e) {
                        return false;
                    }
                } else {
                    return Db::getInstance()->update(
                        'lengow_actions',
                        array(
                            'retry' => $action->retry + 1,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ),
                        'id = ' . $action->id
                    );
                }
            }
        }
        return false;
    }

    /**
     * Finish action
     *
     * @param integer $id Lengow action id
     *
     * @return boolean
     */
    public static function finishAction($id)
    {
        if (_PS_VERSION_ < '1.5') {
            try {
                return Db::getInstance()->autoExecute(
                    _DB_PREFIX_ . 'lengow_actions',
                    array(
                        'state' => (int)self::STATE_FINISH,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ),
                    'UPDATE',
                    'id = ' . (int)$id
                );
            } catch (PrestaShopDatabaseException $e) {
                return false;
            }
        } else {
            return Db::getInstance()->update(
                'lengow_actions',
                array(
                    'state' => (int)self::STATE_FINISH,
                    'updated_at' => date('Y-m-d H:i:s'),
                ),
                'id = ' . (int)$id
            );
        }
    }

    /**
     * Removes all actions for one order Prestashop
     *
     * @param integer $idOrder Prestashop order id
     * @param string|null $actionType action type (null, ship or cancel)
     *
     * @return boolean
     */
    public static function finishAllActions($idOrder, $actionType = null)
    {
        try {
            $sqlActionType = $actionType === null ? '' : ' AND action_type = "' . pSQL($actionType) . '"';
            $rows = Db::getInstance()->executeS(
                'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions
                WHERE id_order =' . (int)$idOrder . ' AND state = ' . (int)self::STATE_NEW . $sqlActionType
            );
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        if (!empty($rows)) {
            foreach ($rows as $row) {
                self::finishAction($row['id']);
            }
            return true;
        }
        return false;
    }

    /**
     * Get interval time for action synchronisation
     *
     * @return integer
     */
    public static function getIntervalTime()
    {
        $intervalTime = self::MAX_INTERVAL_TIME;
        $lastActionSynchronisation = LengowConfiguration::getGlobalValue('LENGOW_LAST_ACTION_SYNC');
        if ($lastActionSynchronisation) {
            $lastIntervalTime = time() - (int)$lastActionSynchronisation;
            $lastIntervalTime = $lastIntervalTime + self::SECURITY_INTERVAL_TIME;
            $intervalTime = $lastIntervalTime > $intervalTime ? $intervalTime : $lastIntervalTime;
        }
        return $intervalTime;
    }

    /**
     * Check if active actions are finished
     *
     * @param boolean $logOutput see log or not
     *
     * @return boolean
     */
    public static function checkFinishAction($logOutput = false)
    {
        if (LengowConfiguration::debugModeIsActive()) {
            return false;
        }
        LengowMain::log(
            LengowLog::CODE_ACTION,
            LengowMain::setLogMessage('log.order_action.check_completed_action'),
            $logOutput
        );
        // get all active actions
        $activeActions = self::getActiveActions(false);
        if (!$activeActions) {
            return true;
        }
        // get all actions with API (max 3 days)
        $page = 1;
        $apiActions = array();
        $intervalTime = self::getIntervalTime();
        $dateFrom = time() - $intervalTime;
        $dateTo = time();
        LengowMain::log(
            LengowLog::CODE_ACTION,
            LengowMain::setLogMessage(
                'log.import.connector_get_all_action',
                array(
                    'date_from' => date('Y-m-d H:i:s', $dateFrom),
                    'date_to' => date('Y-m-d H:i:s', $dateTo),
                )
            ),
            $logOutput
        );
        do {
            $results = LengowConnector::queryApi(
                LengowConnector::GET,
                LengowConnector::API_ORDER_ACTION,
                array(
                    'updated_from' => date('c', $dateFrom),
                    'updated_to' => date('c', $dateTo),
                    'page' => $page,
                ),
                '',
                $logOutput
            );
            if (!is_object($results) || isset($results->error)) {
                break;
            }
            // construct array actions
            foreach ($results->results as $action) {
                if (isset($action->id)) {
                    $apiActions[$action->id] = $action;
                }
            }
            $page++;
        } while ($results->next !== null);
        if (empty($apiActions)) {
            return false;
        }
        // check foreach action if is complete
        foreach ($activeActions as $action) {
            if (!isset($apiActions[$action['action_id']])) {
                continue;
            }
            if (isset($apiActions[$action['action_id']]->queued)
                && isset($apiActions[$action['action_id']]->processed)
                && isset($apiActions[$action['action_id']]->errors)
            ) {
                if ($apiActions[$action['action_id']]->queued == false) {
                    // order action is waiting to return from the marketplace
                    if ($apiActions[$action['action_id']]->processed == false
                        && empty($apiActions[$action['action_id']]->errors)
                    ) {
                        continue;
                    }
                    // finish action in lengow_action table
                    self::finishAction($action['id']);
                    $orderLengow = new LengowOrder($action['id_order']);
                    // finish all order logs send
                    LengowOrder::finishOrderLogs($orderLengow->lengowId, 'send');
                    if ($orderLengow->lengowProcessState != LengowOrder::PROCESS_STATE_FINISH) {
                        // if action is accepted -> close order and finish all order actions
                        if ($apiActions[$action['action_id']]->processed == true
                            && empty($apiActions[$action['action_id']]->errors)
                        ) {
                            LengowOrder::updateOrderLengow(
                                $orderLengow->lengowId,
                                array('order_process_state' => LengowOrder::PROCESS_STATE_FINISH)
                            );
                            self::finishAllActions($orderLengow->id);
                        } else {
                            // if action is denied -> create order logs and finish all order actions
                            LengowOrder::addOrderLog(
                                $orderLengow->lengowId,
                                $apiActions[$action['action_id']]->errors,
                                'send'
                            );
                            LengowMain::log(
                                LengowLog::CODE_ACTION,
                                LengowMain::setLogMessage(
                                    'log.order_action.call_action_failed',
                                    array('decoded_message' => $apiActions[$action['action_id']]->errors)
                                ),
                                $logOutput,
                                $orderLengow->lengowMarketplaceSku
                            );
                        }
                    }
                    unset($orderLengow);
                }
            }
        }
        LengowConfiguration::updateGlobalValue('LENGOW_LAST_ACTION_SYNC', time());
        return true;
    }

    /**
     * Remove old actions > 3 days
     *
     * @param boolean $logOutput see log or not
     *
     * @return boolean
     */
    public static function checkOldAction($logOutput = false)
    {
        if (LengowConfiguration::debugModeIsActive()) {
            return false;
        }
        LengowMain::log(
            LengowLog::CODE_ACTION,
            LengowMain::setLogMessage('log.order_action.check_old_action'),
            $logOutput
        );
        // get all old order action (+ 3 days)
        $actions = self::getOldActions();
        if ($actions) {
            foreach ($actions as $action) {
                // finish action in lengow_action table
                self::finishAction($action['id']);
                $orderLengow = new LengowOrder($action['id_order']);
                if ($orderLengow->lengowProcessState !== LengowOrder::PROCESS_STATE_FINISH) {
                    // if action is denied -> create order error
                    $errorMessage = LengowMain::setLogMessage('lengow_log.exception.action_is_too_old');
                    LengowOrder::addOrderLog($orderLengow->lengowId, $errorMessage, 'send');
                    $decodedMessage = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
                    LengowMain::log(
                        LengowLog::CODE_ACTION,
                        LengowMain::setLogMessage(
                            'log.order_action.call_action_failed',
                            array('decoded_message' => $decodedMessage)
                        ),
                        $logOutput,
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
     * Get old untreated actions of more than 3 days
     *
     * @return array|false
     */
    public static function getOldActions()
    {
        $date = date('Y-m-d H:i:s', (time() - self::MAX_INTERVAL_TIME));
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions
                WHERE created_at <= "' . $date . '"
                AND state = ' . (int)self::STATE_NEW;
        try {
            $results = Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }

        return $results ? $results : false;
    }

    /**
     * Check if actions are not sent
     *
     * @param boolean $logOutput see log or not
     *
     * @return boolean
     */
    public static function checkActionNotSent($logOutput = false)
    {
        if (LengowConfiguration::debugModeIsActive()) {
            return false;
        }
        LengowMain::log(
            LengowLog::CODE_ACTION,
            LengowMain::setLogMessage('log.order_action.check_action_not_sent'),
            $logOutput
        );
        // get unsent orders by store
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
