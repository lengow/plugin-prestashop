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

    const STATE_NEW = 0;
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
     * Find all action data
     */
    public function findAll()
    {

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
     * @param string    $type
     * @param integer   $id_shop
     * @param boolean   $load
     *
     * @return array
     */
    public static function getActiveActionByShop($type, $id_shop, $load = true)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT la.*, o.id_shop FROM '._DB_PREFIX_.'lengow_actions la
            INNER JOIN '._DB_PREFIX_.'orders o ON (o.id_order = la.id_order)
            WHERE id_shop='.(int)$id_shop.' AND state = '.(int)self::STATE_NEW.' AND action_type = "'.pSQL($type).'"'
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
     * @param array     $params
     * @param integer   $id_order_lengow
     */
    public static function createAction($params, $id_order_lengow)
    {
        $insertParams = array(
            'parameters'    => pSQL(Tools::JsonEncode($params['parameters'])),
            'id_order'      => (int)$params['id_order'],
            'action_id'     => (int)$params['action_id'],
            'action_type'   => pSQL($params['action_type']),
            'state'         => (int)self::STATE_NEW,
            'created_at'    => date('Y-m-d h:m:i'),
            'updated_at'    => date('Y-m-d h:m:i'),
        );
        if (isset($params['parameters']['line'])) {
            $insertParams['order_line_sku'] = $params['parameters']['line'];
        }

        Db::getInstance()->autoExecute(_DB_PREFIX_.'lengow_actions', $insertParams, 'INSERT');
        LengowMain::log(
            'API',
            LengowMain::setLogMessage('log.order_action.call_tracking'),
            false,
            $params['id_order']
        );
        LengowOrder::addOrderLog(
            $id_order_lengow,
            LengowMain::setLogMessage('lengow_log.error.tracking_in_progress'),
            'ship'
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
                Db::getInstance()->autoExecute(
                    _DB_PREFIX_.'lengow_actions',
                    array(
                        'retry'         => $action->retry + 1,
                        'updated_at'    => date('Y-m-d h:m:i'),
                    ),
                    'UPDATE',
                    'id = ' . $action->id
                );
            }
        }
    }
}
