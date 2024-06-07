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
/*
 * Lengow Action Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowAction
{
    /**
     * @var string Lengow action table name
     */
    public const TABLE_ACTION = 'lengow_actions';

    /* Action fields */
    public const FIELD_ID = 'id';
    public const FIELD_ORDER_ID = 'id_order';
    public const FIELD_ORDER_LINE_SKU = 'order_line_sku';
    public const FIELD_ACTION_ID = 'action_id';
    public const FIELD_ACTION_TYPE = 'action_type';
    public const FIELD_RETRY = 'retry';
    public const FIELD_PARAMETERS = 'parameters';
    public const FIELD_STATE = 'state';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_UPDATED_AT = 'updated_at';

    /* Action states */
    public const STATE_NEW = 0;
    public const STATE_FINISH = 1;

    /* Action types */
    public const TYPE_SHIP = 'ship';
    public const TYPE_CANCEL = 'cancel';

    /* Action API arguments */
    public const ARG_ACTION_TYPE = 'action_type';
    public const ARG_LINE = 'line';
    public const ARG_CARRIER = 'carrier';
    public const ARG_RETURN_CARRIER = 'return_carrier';
    public const ARG_CARRIER_NAME = 'carrier_name';
    public const ARG_CUSTOM_CARRIER = 'custom_carrier';
    public const ARG_SHIPPING_METHOD = 'shipping_method';
    public const ARG_TRACKING_NUMBER = 'tracking_number';
    public const ARG_RETURN_TRACKING_NUMBER = 'return_tracking_number';
    public const ARG_TRACKING_URL = 'tracking_url';
    public const ARG_SHIPPING_PRICE = 'shipping_price';
    public const ARG_SHIPPING_DATE = 'shipping_date';
    public const ARG_DELIVERY_DATE = 'delivery_date';

    /**
     * @var int max interval time for action synchronisation (3 days)
     */
    public const MAX_INTERVAL_TIME = 259200;

    /**
     * @var int security interval time for action synchronisation (2 hours)
     */
    public const SECURITY_INTERVAL_TIME = 7200;

    /**
     * @var array Parameters to delete for Get call
     */
    public static $getParamsToDelete = [
        self::ARG_SHIPPING_DATE,
        self::ARG_DELIVERY_DATE,
    ];

    /**
     * @var int Lengow action record id
     */
    public $id;

    /**
     * @var int PrestaShop order id
     */
    public $idOrder;

    /**
     * @var int Lengow action id
     */
    public $actionId;

    /**
     * @var string action type (ship or cancel)
     */
    public $actionType;

    /**
     * @var int Lengow order record id
     */
    public $retry;

    /**
     * @var string all parameters in json format
     */
    public $parameters;

    /**
     * @var int action state
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
     * @param array $row All action data
     */
    public function load($row)
    {
        $this->id = (int) $row[self::FIELD_ID];
        $this->idOrder = (int) $row[self::FIELD_ORDER_ID];
        $this->actionId = (int) $row[self::FIELD_ACTION_ID];
        $this->actionType = $row[self::FIELD_ACTION_TYPE];
        $this->retry = (int) $row[self::FIELD_RETRY];
        $this->parameters = $row[self::FIELD_PARAMETERS];
        $this->state = (int) $row[self::FIELD_STATE];
        $this->createdAt = $row[self::FIELD_CREATED_AT];
        $this->updatedAt = $row[self::FIELD_UPDATED_AT] === '0000-00-00 00:00:00' ? null : $row[self::FIELD_UPDATED_AT];
    }

    /**
     * Find by ID
     *
     * @param int $actionId Lengow action id
     *
     * @return bool
     */
    public function findByActionId($actionId)
    {
        $row = Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions la WHERE action_id = ' . (int) $actionId
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
     * @param int $actionId Lengow action id
     *
     * @return int|false
     */
    public static function getActionByActionId($actionId)
    {
        $row = Db::getInstance()->getRow(
            'SELECT id FROM ' . _DB_PREFIX_ . 'lengow_actions WHERE action_id = ' . (int) $actionId
        );
        if ($row) {
            return (int) $row[self::FIELD_ID];
        }

        return false;
    }

    /**
     * Find actions by order id
     *
     * @param int $idOrder PrestaShop order id
     * @param bool $onlyActive get only active actions
     * @param string|null $actionType action type (ship or cancel)
     * @param bool $load load LengowAction or not
     *
     * @return array|false
     */
    public static function getActionsByOrderId($idOrder, $onlyActive = false, $actionType = null, $load = true)
    {
        try {

            $sqlOnlyActive = $onlyActive ? ' AND  state = ' . self::STATE_NEW : '';
            $sqlType = $actionType === null ? '' : ' AND  action_type = "' . pSQL($actionType) . '"';
            $rows = Db::getInstance()->executeS(
                'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions
                WHERE id_order=' . (int) $idOrder . ' ' . $sqlOnlyActive . $sqlType
            );
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        if (!empty($rows)) {
            if ($load) {
                $actions = [];
                foreach ($rows as $row) {
                    $action = new self();
                    $action->load($row);
                    $actions[] = $action;
                }

                return $actions;
            }

            return $rows;
        }

        return false;
    }

    /**
     * Get all active actions
     *
     * @param bool $load load LengowAction or not
     *
     * @return array|false
     */
    public static function getActiveActions($load = true)
    {
        try {
            $rows = Db::getInstance()->executeS(
                'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions WHERE state = ' . self::STATE_NEW
            );
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        if (!empty($rows)) {
            if ($load) {
                $actions = [];
                foreach ($rows as $row) {
                    $action = new LengowAction();
                    $action->load($row);
                    $actions[] = $action;
                }

                return $actions;
            }

            return $rows;
        }

        return false;
    }

    /**
     * Get last order action type to re-send action
     *
     * @param int $idOrder PrestaShop order id
     *
     * @return string|false
     */
    public static function getLastOrderActionType($idOrder)
    {
        try {
            $rows = Db::getInstance()->executeS(
                'SELECT action_type FROM ' . _DB_PREFIX_ . 'lengow_actions
                WHERE state = ' . self::STATE_NEW . ' AND id_order=' . (int) $idOrder
            );
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        if (!empty($rows)) {
            $lastAction = end($rows);

            return (string) $lastAction[self::FIELD_ACTION_TYPE];
        }

        return false;
    }

    /**
     * Find
     *
     * @param int $id Lengow action id
     *
     * @return bool
     */
    public function find($id)
    {
        $row = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions la WHERE id = ' . (int) $id);
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
     * @return bool
     *
     * @throws LengowException
     */
    public static function canSendAction($params, $lengowOrder)
    {
        $sendAction = true;
        $getParams = array_merge($params, ['queued' => 'True']);
        // array key deletion for GET verification
        foreach (self::$getParamsToDelete as $param) {
            if (isset($getParams[$param])) {
                unset($getParams[$param]);
            }
        }

        $result = LengowConnector::getInstance()->requestApi(
                        LengowConnector::GET,
                        LengowConnector::API_ORDER_ACTION,
                        $getParams
                    );
        if (isset($result->error, $result->error->message)) {
            throw new LengowException($result->error->message);
        }
        if (isset($result->count) && $result->count > 0) {
            foreach ($result->results as $row) {
                $orderActionId = self::getActionByActionId($row->id);
                if ($orderActionId) {
                    $update = self::updateAction(
                        [
                            self::FIELD_ORDER_ID => $lengowOrder->id,
                            self::FIELD_ACTION_TYPE => $params[self::ARG_ACTION_TYPE],
                            self::FIELD_ACTION_ID => $row->id,
                            self::FIELD_PARAMETERS => $params,
                        ]
                    );
                    if ($update) {
                        $sendAction = false;
                    }
                } else {
                    // if update doesn't work, create new action
                    self::createAction(
                        [
                            self::FIELD_ORDER_ID => $lengowOrder->id,
                            self::FIELD_ACTION_TYPE => $params[self::ARG_ACTION_TYPE],
                            self::FIELD_ACTION_ID => $row->id,
                            self::FIELD_PARAMETERS => $params,
                            'marketplace_sku' => $lengowOrder->lengowMarketplaceSku,
                        ]
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
                    [
                        self::FIELD_ORDER_ID => $lengowOrder->id,
                        self::FIELD_ACTION_TYPE => $params[self::ARG_ACTION_TYPE],
                        self::FIELD_ACTION_ID => $result->id,
                        self::FIELD_PARAMETERS => $params,
                        'marketplace_sku' => $lengowOrder->lengowMarketplaceSku,
                    ]
                );
            } else {
                if ($result) {
                    $message = LengowMain::setLogMessage(
                        'lengow_log.exception.action_not_created',
                        ['error_message' => json_encode($result)]
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
            LengowMain::setLogMessage(
                'log.order_action.call_tracking',
                ['parameters' => $paramList]
            ),
            false,
            $lengowOrder->lengowMarketplaceSku
        );
    }

    /**
     * Create action
     *
     * @param array $params action params
     *
     * @return bool
     */
    public static function createAction($params)
    {
        $insertParams = [
            self::FIELD_PARAMETERS => pSQL(json_encode($params[self::FIELD_PARAMETERS])),
            self::FIELD_ORDER_ID => (int) $params[self::FIELD_ORDER_ID],
            self::FIELD_ACTION_ID => (int) $params[self::FIELD_ACTION_ID],
            self::FIELD_ACTION_TYPE => pSQL($params[self::FIELD_ACTION_TYPE]),
            self::FIELD_STATE => self::STATE_NEW,
            self::FIELD_CREATED_AT => date(LengowMain::DATE_FULL),
        ];
        if (isset($params[self::FIELD_PARAMETERS][self::ARG_LINE])) {
            $insertParams[self::FIELD_ORDER_LINE_SKU] = $params[self::FIELD_PARAMETERS][self::ARG_LINE];
        }
        try {
            Db::getInstance()->insert(self::TABLE_ACTION, $insertParams);
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
     * @return bool
     */
    public static function updateAction($params)
    {
        $action = new LengowAction();
        // findByActionId method can update the action state
        if ($action->findByActionId($params[self::FIELD_ACTION_ID]) && $action->state === self::STATE_NEW) {
            return Db::getInstance()->update(
                self::TABLE_ACTION,
                [
                    self::FIELD_RETRY => $action->retry + 1,
                    self::FIELD_UPDATED_AT => date(LengowMain::DATE_FULL),
                ],
                'id = ' . $action->id
            );
        }

        return false;
    }

    /**
     * Finish action
     *
     * @param int $id Lengow action id
     *
     * @return bool
     */
    public static function finishAction($id)
    {
        return Db::getInstance()->update(
            self::TABLE_ACTION,
            [
                self::FIELD_STATE => self::STATE_FINISH,
                self::FIELD_UPDATED_AT => date(LengowMain::DATE_FULL),
            ],
            'id = ' . (int) $id
        );
    }

    /**
     * Removes all actions for one order PrestaShop
     *
     * @param int $idOrder PrestaShop order id
     * @param string|null $actionType action type (null, ship or cancel)
     *
     * @return bool
     */
    public static function finishAllActions($idOrder, $actionType = null)
    {
        try {
            $sqlActionType = $actionType === null ? '' : ' AND action_type = "' . pSQL($actionType) . '"';
            $rows = Db::getInstance()->executeS(
                'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions
                WHERE id_order =' . (int) $idOrder . ' AND state = ' . self::STATE_NEW . $sqlActionType
            );
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
        if (!empty($rows)) {
            foreach ($rows as $row) {
                self::finishAction($row[self::FIELD_ID]);
            }

            return true;
        }

        return false;
    }

    /**
     * Get interval time for action synchronisation
     *
     * @return int
     */
    public static function getIntervalTime()
    {
        $intervalTime = self::MAX_INTERVAL_TIME;
        $lastActionSynchronisation = LengowConfiguration::getGlobalValue(
            LengowConfiguration::LAST_UPDATE_ACTION_SYNCHRONIZATION
        );
        if ($lastActionSynchronisation) {
            $lastIntervalTime = time() - (int) $lastActionSynchronisation;
            $lastIntervalTime += self::SECURITY_INTERVAL_TIME;
            $intervalTime = $lastIntervalTime > $intervalTime ? $intervalTime : $lastIntervalTime;
        }

        return $intervalTime;
    }

    /**
     * Check if active actions are finished
     *
     * @param bool $logOutput see log or not
     *
     * @return bool
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
        $apiActions = [];
        $intervalTime = self::getIntervalTime();
        $dateFrom = time() - $intervalTime;
        $dateTo = time();
        LengowMain::log(
            LengowLog::CODE_ACTION,
            LengowMain::setLogMessage(
                'log.import.connector_get_all_action',
                [
                    'date_from' => date(LengowMain::DATE_FULL, $dateFrom),
                    'date_to' => date(LengowMain::DATE_FULL, $dateTo),
                ]
            ),
            $logOutput
        );
        do {
            $results = LengowConnector::queryApi(
                LengowConnector::GET,
                LengowConnector::API_ORDER_ACTION,
                [
                    LengowImport::ARG_UPDATED_FROM => date(LengowMain::DATE_ISO_8601, $dateFrom),
                    LengowImport::ARG_UPDATED_TO => date(LengowMain::DATE_ISO_8601, $dateTo),
                    LengowImport::ARG_PAGE => $page,
                ],
                '',
                $logOutput
            );
            if (!is_object($results) || isset($results->error)) {
                break;
            }
            if (isset($results->results)) {
                // construct array actions
                foreach ($results->results as $action) {
                    if (isset($action->id)) {
                        $apiActions[$action->id] = $action;
                    }
                }
            }

            ++$page;
        } while (!empty($results->next));
        if (empty($apiActions)) {
            return false;
        }
        // check foreach action if is complete
        foreach ($activeActions as $action) {
            if (!isset($apiActions[$action[self::FIELD_ACTION_ID]])) {
                continue;
            }
            $apiAction = $apiActions[$action[self::FIELD_ACTION_ID]];
            if (isset($apiAction->queued, $apiAction->processed, $apiAction->errors) && $apiAction->queued == false) {
                // order action is waiting to return from the marketplace
                if ($apiAction->processed == false && empty($apiAction->errors)) {
                    continue;
                }
                // finish action in lengow_action table
                self::finishAction($action[self::FIELD_ID]);
                $orderLengow = new LengowOrder($action[self::FIELD_ORDER_ID]);
                // finish all order logs send
                LengowOrderError::finishOrderLogs($orderLengow->lengowId, LengowOrderError::TYPE_ERROR_SEND);
                if ($orderLengow->lengowProcessState !== LengowOrder::PROCESS_STATE_FINISH) {
                    // if action is accepted -> close order and finish all order actions
                    if ($apiAction->processed == true && empty($apiAction->errors)) {
                        LengowOrder::updateOrderLengow(
                            $orderLengow->lengowId,
                            [LengowOrder::FIELD_ORDER_PROCESS_STATE => LengowOrder::PROCESS_STATE_FINISH]
                        );
                        self::finishAllActions($orderLengow->id);
                    } else {
                        // if action is denied -> create order logs and finish all order actions
                        LengowOrderError::addOrderLog(
                            $orderLengow->lengowId,
                            $apiAction->errors,
                            LengowOrderError::TYPE_ERROR_SEND
                        );
                        LengowMain::log(
                            LengowLog::CODE_ACTION,
                            LengowMain::setLogMessage(
                                'log.order_action.call_action_failed',
                                ['decoded_message' => $apiAction->errors]
                            ),
                            $logOutput,
                            $orderLengow->lengowMarketplaceSku
                        );
                    }
                }
                unset($orderLengow);
            }
        }
        LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_ACTION_SYNCHRONIZATION, time());

        return true;
    }

    /**
     * Remove old actions > 3 days
     *
     * @param bool $logOutput see log or not
     *
     * @return bool
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
                self::finishAction($action[self::FIELD_ID]);
                $orderLengow = new LengowOrder($action[self::FIELD_ORDER_ID]);
                if ($orderLengow->lengowProcessState !== LengowOrder::PROCESS_STATE_FINISH) {
                    // if action is denied -> create order error
                    $errorMessage = LengowMain::setLogMessage('lengow_log.exception.action_is_too_old');
                    LengowOrderError::addOrderLog(
                        $orderLengow->lengowId,
                        $errorMessage,
                        LengowOrderError::TYPE_ERROR_SEND
                    );
                    $decodedMessage = LengowMain::decodeLogMessage($errorMessage, LengowTranslation::DEFAULT_ISO_CODE);
                    LengowMain::log(
                        LengowLog::CODE_ACTION,
                        LengowMain::setLogMessage(
                            'log.order_action.call_action_failed',
                            ['decoded_message' => $decodedMessage]
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
        $date = date(LengowMain::DATE_FULL, time() - self::MAX_INTERVAL_TIME);
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'lengow_actions
                WHERE created_at <= "' . $date . '"
                AND state = ' . self::STATE_NEW;
        try {
            $results = Db::getInstance()->executeS($query);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }

        return $results ?: false;
    }

    /**
     * Check if actions are not sent
     *
     * @param bool $logOutput see log or not
     *
     * @return bool
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
                usleep(250000);
            }
        }

        return true;
    }
}
