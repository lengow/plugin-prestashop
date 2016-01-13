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

if (!defined('_PS_VERSION_')) {
    exit;
}

class LengowController {

    protected $module;
    protected $context;

    public function __construct($params = array())
    {
        $this->module = Module::getInstanceByName('lengow');
        $this->context = Context::getContext();

        $breadcrumbTitle = isset($params['breadcrumb_title']) ? $params['breadcrumb_title'] : '';
        $this->context->smarty->assign('breadcrumb_title', $breadcrumbTitle);
        $this->context->smarty->assign('current_controller', get_class($this));
    }

    public function postProcess()
    {

    }

    public function display()
    {
        if (_PS_VERSION_ < '1.5') {
            $module = Module::getInstanceByName('lengow');
            echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/header.tpl');
            $lengowMain = new LengowMain();
            $className = get_class($this);
            $path = $lengowMain->fromCamelCase(substr($className, 0, strlen($className) - 10));
            echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/'.$path.'/helpers/view/view.tpl');
        }
    }
}
