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

class LengowController
{

    protected $module;
    protected $context;
    protected $locale;
    protected $isNewMerchant;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('lengow');
        $this->context = Context::getContext();
        $this->context->smarty->assign('current_controller', get_class($this));
        $this->context->smarty->assign('lengow_configuration', new LengowConfiguration());
        $this->context->smarty->assign('locale', new LengowTranslation());
        $this->isNewMerchant = LengowMain::isNewMerchant();
        $this->context->smarty->assign('isNewMerchant', $this->isNewMerchant);
        $this->locale = new LengowTranslation();
    }

    public function postProcess()
    {

    }

    public function display()
    {
        $this->context->smarty->assign(
            'total_pending_order',
            LengowOrder::getTotalOrderByStatus('waiting_shipment')
        );

        if (_PS_VERSION_ < '1.5') {

            $toolbox = Context::getContext()->smarty->getVariable('toolbox')->value;
            if (!$toolbox) {
                $module = Module::getInstanceByName('lengow');
                echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/header.tpl');
                $lengowMain = new LengowMain();
                $className = get_class($this);
                if (substr($className, 0, 11) == 'LengowOrder') {
                    echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/header_order.tpl');
                }
                $path = $lengowMain->fromCamelCase(Tools::substr($className, 0, Tools::strlen($className) - 10));
                echo $module->display(
                    _PS_MODULE_LENGOW_DIR_,
                    'views/templates/admin/' . $path . '/helpers/view/view.tpl'
                );
            }
        }
    }

    public function forceDisplay()
    {
        $module = Module::getInstanceByName('lengow');
        $lengowMain = new LengowMain();
        $className = get_class($this);
        $path = $lengowMain->fromCamelCase(Tools::substr($className, 0, Tools::strlen($className) - 10));
        echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/'.$path.'/helpers/view/view.tpl');
    }
}
