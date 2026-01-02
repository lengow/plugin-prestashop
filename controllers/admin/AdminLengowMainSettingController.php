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
 * Admin Lengow main setting Controller Class
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminLengowMainSettingController extends ModuleAdminController
{
    /**
     * Construct
     */
    public function __construct()
    {
        $this->lang = true;
        $this->explicitSelect = true;
        $this->lite_display = true;
        $this->meta_title = 'Configuration';
        $this->list_no_link = true;
        $this->display = false;

        parent::__construct();
    }
    
    /**
     * Redirect to Symfony controller
     */
    public function initContent()
    {
        $router = $this->get('router');
        $url = $router->generate('lengow_admin_main_setting');
        Tools::redirect($url);
    }
}
