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
 * The Lengow's Log Configuration Admin Controller.
 *
 */
class AdminLengowLogConfigController extends ModuleAdminController
{
    private $count;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->template = 'layout.tpl';
        $this->lite_display = true;
        $this->explicitSelect = true;
        $this->list_no_link = true;
        $this->meta_title = 'Configuration Logs';
        $this->lang = false;
        $this->bootstrap = true;
        $this->show_toolbar = false;

        parent::__construct();
    }

    private function getLogFiles($orderBy = null, $orderWay = null, $pagination = null, $filter = null, $message = null)
    {
        $logs_links = LengowLog::getLinks();
        if (!$logs_links) {
            return $this->l('No logs available');
        }
        $logs_links = array_reverse($logs_links);
        $tab = array();
        $this->count = 0;
        foreach ($logs_links as $link) {

            $tab = array_merge($tab, $this->openText($link, $message));
        }



        if ($orderBy == 'date' && $orderWay == 'desc') {
            $tab = $this->sksort($tab, 'id', true);
        } else {
            $tab = $this->sksort($tab, 'id');
        }

        $tab = array_chunk($tab, $pagination);

        return $tab[$filter - 1];
    }

    public function openText($link, $message = '')
    {
        $test = fopen($link, "r");
        $tab = array();


        while (!feof($test)) {
            $read = fgets($test);

            $row = array(
                'id' => $this->count,
                'date' => substr($read, 0, 21),
                'message' => substr($read, 24)

            );
            if ($message != '') {
                if (stripos($row['message'], $message) !== false) {
                    array_push($tab, $row);
                    $this->count += 1;

                }

            } else {
                array_push($tab, $row);
                $this->count += 1;
            }

        }
        return $tab;
    }

    public function sksort(&$array, $subkey = 'id', $sort_ascending = false)
    {
        if (count($array)) {
            $temp_array[key($array)] = array_shift($array);
        }

        foreach ($array as $key => $val) {
            $offset = 0;
            $found = false;

            foreach ($temp_array as $tmp_key => $tmp_val) {
                if (!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
                    $temp_array = array_merge((array)array_slice($temp_array, 0, $offset), array($key => $val), array_slice($temp_array, $offset));
                    $found = true;
                }
                $offset++;
            }

            if (!$found) {
                $temp_array = array_merge($temp_array, array($key => $val));
            }
        }

        if ($sort_ascending) {
            $array = array_reverse($temp_array);
        } else {
            $array = $temp_array;
        }

        return $array;
    }

    public function RenderList()
    {

        $this->fields_list = array(
            'date' => array(
                'title' => $this->l('Date'),
                'search' => false,
                'align' => 'text-center',
                'width' => 'auto'

            ),
            'message' => array(
                'title' => $this->l('Message'),
                'orderby' => false,
                'search' => true,
                'width' => 'auto'


            )
        );
        $helper = new HelperList();

        $orderBy = Tools::getValue('configurationOrderby');
        $orderWay = Tools::getValue('configurationOrderway');
        $pagination = Tools::getValue('configuration_pagination') == null ? $helper->_default_pagination : Tools::getValue('configuration_pagination');
        $filter = Tools::getValue('submitFilterconfiguration') == null ? 1 : Tools::getValue('submitFilterconfiguration');
        if ($filter < 1) {
            $filter = 1;
        }

        $helper->controller_name = 'AdminLengowLogConfigController';
        $message = $this->context->cookie->{'lengowlogconfigconfigurationFilter_message'};

        $tab = $this->getLogFiles($orderBy, $orderWay, $pagination, $filter, $message);

        $helper->listTotal = $this->count;
        $helper->simple_header = false;
        $helper->token = Tools::getAdminTokenLite('AdminLengowLogConfig');
        $helper->currentIndex = AdminController::$currentIndex;

        return $helper->generateList($tab, $this->fields_list);
    }
}
