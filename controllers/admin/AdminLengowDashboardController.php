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
        $this->template = 'layout.tpl';
        $this->display = 'view';

        parent::__construct();
    }
    
    /**
     * Initialize page content
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
        
        // Prepare data for template
        $locale = new LengowTranslation();
        $lengowLink = new LengowLink();
        $module = Module::getInstanceByName('lengow');
        $localeIsoCode = Tools::substr(Context::getContext()->language->language_code, 0, 2);
        $multiShop = Shop::isFeatureActive();
        $debugMode = LengowConfiguration::debugModeIsActive();
        $merchantStatus = LengowSync::getStatusAccount();
        $isNewMerchant = LengowConfiguration::isNewMerchant();
        
        // Show header or not
        if ($isNewMerchant || (is_array($merchantStatus) && $merchantStatus['type'] === 'free_trial' && $merchantStatus['expired'])) {
            $displayToolbar = false;
        } else {
            $displayToolbar = true;
        }
        
        // Check if plugin is up to date
        $pluginData = LengowSync::getPluginData();
        $pluginIsUpToDate = true;
        $showPluginUpgradeModal = false;
        if ($pluginData && version_compare($pluginData['version'], $module->version, '>')) {
            $pluginIsUpToDate = false;
        }
        
        // Get plugin links
        $pluginLinks = LengowSync::getPluginLinks($localeIsoCode, true);
        
        $this->context->smarty->assign([
            'locale' => $locale,
            'lengowPathUri' => $module->getPathUri(),
            'lengowUrl' => LengowConfiguration::getLengowUrl(),
            'lengow_link' => $lengowLink,
            'current_controller' => 'LengowDashboardController',
            'localeIsoCode' => $localeIsoCode,
            'version' => _PS_VERSION_,
            'lengowVersion' => $module->version,
            'displayToolbar' => $displayToolbar,
            'total_pending_order' => LengowOrder::countOrderToBeSent(),
            'refresh_status' => $lengowLink->getAbsoluteAdminLink('AdminLengowDashboard') . '&action=refresh_status',
            'pluginData' => $pluginData,
            'pluginIsUpToDate' => $pluginIsUpToDate,
            'showPluginUpgradeModal' => $showPluginUpgradeModal,
            'lengowModalAjaxLink' => $lengowLink->getAbsoluteAdminLink('AdminLengowDashboard'),
            'helpCenterLink' => $pluginLinks[LengowSync::LINK_TYPE_HELP_CENTER],
            'updateGuideLink' => $pluginLinks[LengowSync::LINK_TYPE_UPDATE_GUIDE],
            'changelogLink' => $pluginLinks[LengowSync::LINK_TYPE_CHANGELOG],
            'supportLink' => $pluginLinks[LengowSync::LINK_TYPE_SUPPORT],
            'multiShop' => $multiShop,
            'debugMode' => $debugMode,
            'isNewMerchant' => $isNewMerchant,
            'merchantStatus' => $merchantStatus,
        ]);
    }
}
