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
 * Lengow Controller Class
 */
class LengowController
{
    /**
     * @var Lengow Lengow module instance
     */
    protected $module;

    /**
     * @var Context PrestaShop context instance
     */
    protected $context;

    /**
     * @var LengowLink Lengow link instance
     */
    protected $lengowLink;

    /**
     * @var LengowTranslation Lengow translation instance
     */
    protected $locale;

    /**
     * @var bool Check if is a new merchant
     */
    protected $isNewMerchant;

    /**
     * Construct the main Lengow controller
     */
    public function __construct()
    {
        $this->module = Module::getInstanceByName('lengow');
        $this->context = Context::getContext();
        $this->lengowLink = new LengowLink();
        $this->locale = new LengowTranslation();
        $this->isNewMerchant = LengowConfiguration::isNewMerchant();
        $this->context->smarty->assign('locale', $this->locale);
        $lengowPathUri = $this->module->getPathUri();
        $this->context->smarty->assign('lengowPathUri', $lengowPathUri);
    }

    /**
     * Process Post Parameters
     */
    public function postProcess()
    {
        $this->prepareDisplay();
    }

    /**
     * Display data page
     */
    public function display()
    {
        $this->prepareDisplay();
        $this->context->smarty->assign('total_pending_order', LengowOrder::countOrderToBeSent());
    }

    /**
     * Force Display data page
     */
    public function forceDisplay()
    {
        $module = Module::getInstanceByName('lengow');
        $lengowMain = new LengowMain();
        $className = get_class($this);
        $path = $lengowMain->fromCamelCase(Tools::substr($className, 0, Tools::strlen($className) - 10));
        $this->prepareDisplay();
        echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/' . $path . '/helpers/view/view.tpl');
    }

    /**
     * Checks if the plugin upgrade modal should be displayed or not
     *
     * @return bool
     */
    private function showPluginUpgradeModal()
    {
        // never display the upgrade modal during the connection process
        $className = get_class($this);
        if (Tools::substr($className, 0, 10) === 'LengowHome') {
            return false;
        }
        $updatedAt = LengowConfiguration::getGlobalValue(LengowConfiguration::LAST_UPDATE_PLUGIN_MODAL);
        if ($updatedAt !== null && (time() - (int) $updatedAt) < 86400) {
            return false;
        }
        LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_PLUGIN_MODAL, time());

        return true;
    }

    /**
     * affect variables to template display
     */
    protected function prepareDisplay()
    {
        $localeIsoCode = Tools::substr(Context::getContext()->language->language_code, 0, 2);
        $multiShop = Shop::isFeatureActive();
        $debugMode = LengowConfiguration::debugModeIsActive();
        $merchantStatus = LengowSync::getStatusAccount();
        // show header or not
        if ($this->isNewMerchant || ($merchantStatus['type'] === 'free_trial' && $merchantStatus['expired'])) {
            $displayToolbar = false;
        } else {
            $displayToolbar = true;
        }
        // recovery of all plugin data for plugin update
        $pluginIsUpToDate = true;
        $showPluginUpgradeModal = false;
        $lengowModalAjaxLink = $this->lengowLink->getAbsoluteAdminLink('AdminLengowDashboard');
        $pluginData = LengowSync::getPluginData();
        if ($pluginData && version_compare($pluginData['version'], $this->module->version, '>')) {
            $pluginIsUpToDate = false;
            // show upgrade plugin modal or not
            $showPluginUpgradeModal = $this->showPluginUpgradeModal();
        }
        // get actual plugin urls in current language
        $pluginLinks = LengowSync::getPluginLinks($localeIsoCode, true);
        // assignment of all smarty variables for the entire plugin

        $this->context->smarty->assign('current_controller', get_class($this));
        $this->context->smarty->assign('lengow_link', $this->lengowLink);
        $this->context->smarty->assign('localeIsoCode', $localeIsoCode);
        $this->context->smarty->assign('version', _PS_VERSION_);
        $this->context->smarty->assign('lengowVersion', $this->module->version);
        $this->context->smarty->assign('lengowUrl', LengowConfiguration::getLengowUrl());
        $this->context->smarty->assign('displayToolbar', $displayToolbar);
        $this->context->smarty->assign('pluginData', $pluginData);
        $this->context->smarty->assign('pluginIsUpToDate', $pluginIsUpToDate);
        $this->context->smarty->assign('showPluginUpgradeModal', $showPluginUpgradeModal);
        $this->context->smarty->assign('lengowModalAjaxLink', $lengowModalAjaxLink);
        $this->context->smarty->assign('helpCenterLink', $pluginLinks[LengowSync::LINK_TYPE_HELP_CENTER]);
        $this->context->smarty->assign('updateGuideLink', $pluginLinks[LengowSync::LINK_TYPE_UPDATE_GUIDE]);
        $this->context->smarty->assign('changelogLink', $pluginLinks[LengowSync::LINK_TYPE_CHANGELOG]);
        $this->context->smarty->assign('supportLink', $pluginLinks[LengowSync::LINK_TYPE_SUPPORT]);
        $this->context->smarty->assign('multiShop', $multiShop);
        $this->context->smarty->assign('debugMode', $debugMode);
        $this->context->smarty->assign('isNewMerchant', $this->isNewMerchant);
        $this->context->smarty->assign('merchantStatus', $merchantStatus);
    }
}
