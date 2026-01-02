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
 * Admin Lengow dashboard Controller Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminLengowDashboardController extends ModuleAdminController
{
    /**
     * Construct
     */
    public function __construct()
    {
        $this->lang = false;
        $this->explicitSelect = true;
        $this->lite_display = true;
        $this->meta_title = 'Configuration';
        $this->list_no_link = true;
        $this->display = false;

        parent::__construct();
    }
    
    /**
     * Render the page with Twig
     */
    public function initContent()
    {
        parent::initContent();
        
        // Handle refresh_status action
        $action = Tools::getValue('action');
        if ($action === 'refresh_status') {
            LengowSync::getStatusAccount(true);
            $lengowLink = new LengowLink();
            Tools::redirect($lengowLink->getAbsoluteAdminLink('AdminLengowDashboard'));
            return;
        }
        
        // Prepare data for Twig template
        $locale = new LengowTranslation();
        $lengowLink = new LengowLink();
        $module = Module::getInstanceByName('lengow');
        
        $this->context->smarty->assign([
            'locale' => $locale,
            'lengowPathUri' => $module->getPathUri(),
            'lengowUrl' => LengowConfiguration::getLengowUrl(),
            'lengow_link' => $lengowLink,
            'merchantStatus' => LengowSync::getStatusAccount(),
            'pluginData' => LengowSync::getPluginData(),
            'pluginIsUpToDate' => LengowSync::isPluginUpToDate(),
            'displayToolbar' => 1,
            'current_controller' => 'LengowDashboardController',
            'total_pending_order' => LengowOrder::countOrderToBeSent(),
            'refresh_status' => $lengowLink->getAbsoluteAdminLink('AdminLengowDashboard') . '&action=refresh_status',
        ]);
        
        // Use Twig template
        $this->setTemplate('module:lengow/views/templates/admin/dashboard/index.html.twig');
    }
}
