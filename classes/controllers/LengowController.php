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
 * Lengow Controller Class
 */
use Twig\Environment;

if (!defined('_PS_VERSION_')) {
    exit;
}
class LengowController
{
    /**
     * @var Lengow|false Lengow module instance
     */
    protected Lengow|false $module;

    /**
     * @var Context PrestaShop context instance
     */
    protected Context $context;

    /**
     * @var LengowLink Lengow link instance
     */
    protected LengowLink $lengowLink;

    /**
     * @var LengowTranslation Lengow translation instance
     */
    protected LengowTranslation $locale;

    /**
     * @var bool Check if is a new merchant
     */
    protected bool $isNewMerchant;

    /**
     * @var Environment|null Twig environment instance
     */
    protected ?Environment $twig = null;

    /**
     * @var array<string, mixed> Variables passed to Twig templates
     */
    protected array $templateVars = [];

    /**
     * @var bool Whether controller runs through Symfony bridge and must not echo/exit
     */
    protected bool $bridgeMode = false;

    /**
     * @var array<string, mixed>|null JSON payload produced during postProcess in bridge mode
     */
    private ?array $jsonResponsePayload = null;

    /**
     * Construct the main Lengow controller
     *
     * @param Context $context PrestaShop context (injected from Symfony controller on PS9,
     *                         or resolved via LengowContext on PS8)
     * @param Environment|null $twig Twig environment instance
     * @param bool $bridgeMode Enable bridge mode to avoid direct output side effects
     */
    public function __construct(Context $context, ?Environment $twig = null, bool $bridgeMode = false)
    {
        $this->module = Module::getInstanceByName('lengow');
        $this->context = $context;
        $this->twig = $twig;
        $this->bridgeMode = $bridgeMode;
        $this->lengowLink = new LengowLink();
        $this->locale = new LengowTranslation($this->context);
        $this->isNewMerchant = LengowConfiguration::isNewMerchant();
        $this->templateVars['locale'] = $this->locale;
        $lengowPathUri = $this->module->getPathUri();
        $this->templateVars['lengowPathUri'] = $lengowPathUri;
        $this->templateVars['lengow_link'] = $this->lengowLink;
    }

    /**
     * Process Post Parameters
     *
     * @return void
     */
    public function postProcess(): void
    {
        $this->prepareDisplay();
    }

    /**
     * Display data page
     *
     * @return void
     */
    public function display(): void
    {
        $this->prepareDisplay();
        $this->templateVars['total_pending_order'] = LengowOrder::countOrderToBeSent();
    }

    /**
     * Get all template variables
     *
     * @return array<string, mixed>
     */
    public function getTemplateVars(): array
    {
        return $this->templateVars;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function consumeJsonResponse(): ?array
    {
        $payload = $this->jsonResponsePayload;
        $this->jsonResponsePayload = null;

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function respondJson(array $payload): void
    {
        if ($this->bridgeMode) {
            $this->jsonResponsePayload = $payload;

            return;
        }

        echo json_encode($payload);
    }

    protected function finishPostProcess(): void
    {
        if ($this->bridgeMode) {
            return;
        }

        exit;
    }

    /**
     * Checks if the plugin upgrade modal should be displayed or not
     *
     * @return bool
     */
    private function showPluginUpgradeModal(): bool
    {
        // never display the upgrade modal during the connection process
        $className = get_class($this);
        if (Tools::substr($className, 0, 10) === 'LengowHome') {
            return false;
        }
        $updatedAt = LengowConfiguration::getGlobalValue(LengowConfiguration::LAST_UPDATE_PLUGIN_MODAL);
        if ($updatedAt !== '' && (time() - (int) $updatedAt) < 86400) {
            return false;
        }
        LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_PLUGIN_MODAL, time());

        return true;
    }

    /**
     * affect variables to template display
     *
     * @return void
     */
    protected function prepareDisplay(): void
    {
        $localeIsoCode = Tools::substr($this->context->language->language_code, 0, 2);
        $multiShop = Shop::isFeatureActive();
        $debugMode = LengowConfiguration::debugModeIsActive();
        $merchantStatus = LengowSync::getStatusAccount();
        // show header or not
        if ($this->isNewMerchant || (is_array($merchantStatus) && $merchantStatus['type'] === 'free_trial' && $merchantStatus['expired'])) {
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
        // assignment of all template variables for the entire plugin

        $this->templateVars['current_controller'] = get_class($this);
        $this->templateVars['lengow_link'] = $this->lengowLink;
        $this->templateVars['localeIsoCode'] = $localeIsoCode;
        $this->templateVars['version'] = _PS_VERSION_;
        $this->templateVars['lengowVersion'] = $this->module->version;
        $this->templateVars['lengowUrl'] = LengowConfiguration::getLengowUrl();
        $this->templateVars['displayToolbar'] = $displayToolbar;
        $this->templateVars['pluginData'] = $pluginData;
        $this->templateVars['pluginIsUpToDate'] = $pluginIsUpToDate;
        $this->templateVars['showPluginUpgradeModal'] = $showPluginUpgradeModal;
        $this->templateVars['lengowModalAjaxLink'] = $lengowModalAjaxLink;
        $this->templateVars['helpCenterLink'] = $pluginLinks[LengowSync::LINK_TYPE_HELP_CENTER];
        $this->templateVars['updateGuideLink'] = $pluginLinks[LengowSync::LINK_TYPE_UPDATE_GUIDE];
        $this->templateVars['changelogLink'] = $pluginLinks[LengowSync::LINK_TYPE_CHANGELOG];
        $this->templateVars['supportLink'] = $pluginLinks[LengowSync::LINK_TYPE_SUPPORT];
        $this->templateVars['multiShop'] = $multiShop;
        $this->templateVars['debugMode'] = $debugMode;
        $this->templateVars['isNewMerchant'] = $this->isNewMerchant;
        $this->templateVars['merchantStatus'] = $merchantStatus;
    }
}
