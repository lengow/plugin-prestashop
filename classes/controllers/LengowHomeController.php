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

class LengowHomeController extends LengowController
{

    /**
     * Process Post Parameters
     */
    public function postProcess()
    {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
        if ($action) {
            switch ($action) {
                case 'get_sync_data':
                    $data = array();
                    $data['function'] = 'sync';
                    $data['parameters'] = LengowSync::getSyncData();
                    echo Tools::jsonEncode($data);
                    break;
                case 'sync':
                    $action = isset($_REQUEST['data']) ?$_REQUEST['data'] : false;
                    LengowSync::sync($action);
                    echo "$('#lengow_home_content').show();";
                    echo "$('#lengow_home_frame').hide();";
                    echo "$('#lengow_home_iframe').attr('src','');";
                    break;
            }
            exit();
        }
    }

    /**
     * Display data page
     */
    public function display()
    {
        $lengowLink = new LengowLink();
        $this->context->smarty->assign('sync_link', $lengowLink->getAbsoluteAdminLink("AdminLengowHome", true));
        parent::display();
    }
}
