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
 * Lengow Home Controller Class
 */
class LengowHomeController extends LengowController
{
    /**
     * Process Post Parameters
     */
    public function postProcess()
    {
        $isSync = isset($_REQUEST['isSync']) ? $_REQUEST['isSync'] : false;
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
                    $data = isset($_REQUEST['data']) ? $_REQUEST['data'] : false;
                    LengowSync::sync($data);
                    LengowSync::getStatusAccount(true);
                    break;
                case 'refresh_status':
                    LengowSync::getStatusAccount(true);
                    $lengowLink = new LengowLink();
                    Tools::redirectAdmin($lengowLink->getAbsoluteAdminLink('AdminLengowHome'));
                    break;
            }
            exit();
        }
        $this->context->smarty->assign('isSync', $isSync);
    }

    /**
     * Display data page
     */
    public function display()
    {
        $lengowLink = new LengowLink();
        $this->context->smarty->assign('lengow_ajax_link', $lengowLink->getAbsoluteAdminLink('AdminLengowHome', true));
        $refreshStatus = $lengowLink->getAbsoluteAdminLink('AdminLengowHome') . '&action=refresh_status';
        $this->context->smarty->assign('refresh_status', $refreshStatus);
        parent::display();
    }
}
