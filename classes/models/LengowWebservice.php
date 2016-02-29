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
 * The Lengow Webservice Class.
 *
 */
class LengowWebservice
{

    public static $webservice_url = '/modules/lengow/webservice/lengow.php?action=#ACTION';

    private static $AVAILABLE_ACTION = array(
        'logs' => 'Show import logs',
        'check' => 'Show checklist configuration',
        'log' => 'Show last log file',
        'data' => 'Print data recieve by API during import',
    );

    /**
     * [_getUrlWebservice description]
     * @param  string $action
     * @return string
     */
    private static function getUrlWebservice($action)
    {
        if (array_key_exists($action, self::$AVAILABLE_ACTION)) {
            return str_replace('#ACTION', $action, self::$webservice_url);
        } else {
            return 'Action not available';
        }
    }

    /**
     * Get all action available with webservice
     * @return void
     */
    public static function showAvailableAction()
    {
        $lengow = new Lengow();
        $out = '<div style="font-size: 12px; padding: 10px; border: 1px solid #ccc; margin-bottom: 10px;">';
        $out .= '<p>Lengow Webservice - Module v' . $lengow->version . ' - Prestashop v' . _PS_VERSION_ . '</p>';
        $out .= '</div>';
        $out .= '<div><ul>';
        foreach (self::$AVAILABLE_ACTION as $action => $description) {
            $out .= '<li><a style="color: #222; font-size: 12px; text-decoration: none;" href="' .
                self::getUrlWebservice($action) . '">' . $description . '</a>';
            $out .= '</li>';
        }
        $out .= '</ul></div>';
        echo $out;
    }

    /**
     *
     * Check if action exists
     *
     * @param string $action
     *
     * @return mixed
     */
    public static function checkAction($action)
    {
        if ($action == '') {
            throw new Exception('No action specified.');
        }

        if (array_key_exists($action, self::$AVAILABLE_ACTION)) {
            return true;
        } else {
            throw new Exception('Unknow action.');
        }
    }

    public static function getApiData($id_order = null)
    {
        if (is_null($id_order)) {
            return null;
        }
        $json_data = Db::getInstance()->ExecuteS(
            'SELECT `extra` FROM ' . _DB_PREFIX_ . 'lengow_orders WHERE `id_order` = ' . (int)$id_order
        );
        if ($json_data) {
            foreach ($json_data as $data) {
                echo '<pre>';
                print_r($data['extra']);
                echo '</pre>';
            }
        }
    }

    /**
     * Execute webservice action
     * @param  string $action
     * @return void
     */
    public static function execute($action)
    {
        switch ($action) {
            case 'data':
                $id_order = Tools::getValue('id_order');
                self::getApiData($id_order);
                break;
            case 'check':
                if (Tools::getValue('format') == 'json') {
                    header('Content-Type: application/json');
                    echo LengowCheck::getJsonCheckList();
                } else {
                    echo '<h1>Lengow check configuration<h1>';
                    echo LengowCheck::getHtmlCheckList();
                }
                break;
            case 'logs':
                $days = 10;
                $show_extra = false;
                if (Tools::getValue('delete') != '') {
                    LengowLog::deleteLog(Tools::getValue('delete'));
                }
                if (Tools::getValue('days') != '') {
                    $days = Tools::getValue('days');
                }
                if (Tools::getValue('show_extra') == 1) {
                    $show_extra = true;
                }
                echo LengowCheck::getHtmlLogs($days, $show_extra);
                break;
            case 'log':
                $log_url = _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules' . DS . 'lengow' . DS . 'logs' .
                    DS . 'logs-' . date('Y-m-d') . '.txt';
                Tools::redirect($log_url);
                break;
            default:
                self::showAvailableAction();
                break;
        }
        exit();
    }
}
