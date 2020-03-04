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
     * @var Context Prestashop context instance
     */
    protected $context;

    /**
     * @var LengowTranslation Lengow translation instance
     */
    protected $locale;

    /**
     * @var boolean Toolbox is open or not
     */
    protected $toolbox;

    /**
     * Construct the main Lengow controller
     */
    public function __construct()
    {
        $this->module = Module::getInstanceByName('lengow');
        $this->context = Context::getContext();
        $this->context->smarty->assign('current_controller', get_class($this));
        $this->context->smarty->assign('lengow_configuration', new LengowConfiguration());
        $this->context->smarty->assign('locale', new LengowTranslation());
        $localeIsoCode = Tools::substr(Context::getContext()->language->language_code, 0, 2);
        $this->context->smarty->assign('localeIsoCode', $localeIsoCode);
        $this->context->smarty->assign('version', _PS_VERSION_);
        $this->context->smarty->assign('lengowVersion', $this->module->version);
        $isNewMerchant = LengowConfiguration::isNewMerchant();
        $this->context->smarty->assign('isNewMerchant', $isNewMerchant);
        $merchantStatus = LengowSync::getStatusAccount();
        $this->context->smarty->assign('merchantStatus', $merchantStatus);
        $pluginData = LengowSync::getPluginData();
        $this->context->smarty->assign('pluginData', $pluginData);
        $this->locale = new LengowTranslation();
        $this->context->smarty->assign('lengow_link', new LengowLink());
        $this->toolbox = Context::getContext()->smarty->getVariable('toolbox')->value;
        // get module path uri
        $lengowPathUri = _PS_VERSION_ < '1.5' ? __PS_BASE_URI__ . 'modules/lengow/' : $this->module->getPathUri();
        $this->context->smarty->assign('lengowPathUri', $lengowPathUri);
        $this->context->smarty->assign('lengowUrl', LengowConnector::LENGOW_URL);
        // show header or not
        if ($isNewMerchant || ($merchantStatus['type'] === 'free_trial' && $merchantStatus['expired'])) {
            $displayToolbar = false;
        } else {
            $displayToolbar = true;
        }
        $this->context->smarty->assign('displayToolbar', $displayToolbar);
    }

    /**
     * Process Post Parameters
     */
    public function postProcess()
    {
    }

    /**
     * Display data page
     */
    public function display()
    {
        $this->context->smarty->assign(
            'total_pending_order',
            LengowOrder::getTotalOrderByStatus(LengowOrder::STATE_WAITING_SHIPMENT)
        );
        if (_PS_VERSION_ < '1.5') {
            if (!$this->toolbox) {
                $module = Module::getInstanceByName('lengow');
                echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/header.tpl');
                $lengowMain = new LengowMain();
                $className = get_class($this);
                if (Tools::substr($className, 0, 11) === 'LengowOrder') {
                    echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/header_order.tpl');
                }
                $path = $lengowMain->fromCamelCase(Tools::substr($className, 0, Tools::strlen($className) - 10));
                echo $module->display(
                    _PS_MODULE_LENGOW_DIR_,
                    'views/templates/admin/' . $path . '/helpers/view/view.tpl'
                );
                echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/footer.tpl');
            }
        }
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
        echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/' . $path . '/helpers/view/view.tpl');
    }
}
