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
/*
 * Admin Lengow home Controller Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminLengowHomeController extends ModuleAdminController
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
        
        // Check if we should redirect to dashboard (before processing)
        $isNewMerchant = LengowConfiguration::isNewMerchant();
        if (!$isNewMerchant) {
            $lengowLink = new LengowLink();
            Tools::redirect($lengowLink->getAbsoluteAdminLink('AdminLengowDashboard'));
            return;
        }
        
        // Process business logic
        $lengowController = new LengowHomeController();
        $lengowController->postProcess();
        
        // Prepare data for Twig template
        $locale = new LengowTranslation();
        $module = Module::getInstanceByName('lengow');
        $lengowLink = new LengowLink();
        
        $this->context->smarty->assign([
            'locale' => $locale,
            'lengowPathUri' => $module->getPathUri(),
            'lengow_ajax_link' => $lengowLink->getAbsoluteAdminLink('AdminLengowHome'),
            'displayToolbar' => 0,
        ]);
        
        // Use Twig template
        $this->setTemplate('module:lengow/views/templates/admin/home/index.html.twig');
    }
}
