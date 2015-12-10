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
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->template = 'layout.tpl';
        $this->lite_display = true;
        $this->meta_title = 'Config Logs';
        $this->lang = false;

        /*$this->bootstrap =true;*/

        parent::__construct();

    }

    private function getLogFiles()
    {
        $logs_links = LengowLog::getLinks();
        if (!$logs_links)
            return $this->l('No logs available');
        $logs_links = array_reverse($logs_links);
        $output = '';
        foreach ($logs_links as $link)
        {
            $output .= $link;
            break;

        }
        $test = fopen($output, "r");
        $tab = array();
        while(!feof($test)){
            $read = fgets($test);

            $tab[] = array(
                'date' => substr($read, 0 , 10),
                'hour' => substr($read, 13, 8),
                'message' =>  substr($read, 24)

            );

        }
        return $tab;
    }

    public function RenderList(){

        $this->fields_list = array(
            'date' => array(
                'title' => $this->l('Date'),
                'width' => 'auto'

            ),
            'hour' => array(
                'title' => $this->l('Heure')
            ),
            'message' => array(
                'title' => $this->l('Message'),
                'width' => 'auto',
                'align' => 'center',
            )
        );
        $tab = $this->getLogFiles();
        $helper = new HelperList();
        $helper->token = Tools::getAdminTokenLite('AdminLengowLogConfig');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper->generateList($tab, $this->fields_list);
    }

}