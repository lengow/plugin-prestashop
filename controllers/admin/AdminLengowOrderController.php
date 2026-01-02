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
 * Admin Lengow order Controller Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminLengowOrderController extends ModuleAdminController
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
            'current_controller' => 'LengowOrderController',
            'localeIsoCode' => $localeIsoCode,
            'version' => _PS_VERSION_,
            'lengowVersion' => $module->version,
            'displayToolbar' => $displayToolbar,
            'total_pending_order' => LengowOrder::countOrderToBeSent(),
            'pluginData' => $pluginData,
            'pluginIsUpToDate' => $pluginIsUpToDate,
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
