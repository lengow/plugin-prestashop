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
// On PrestaShop 9+, this page is handled by the Symfony controller
// PrestaShop\Module\Lengow\Controller\Admin\LengowDashboardAdminController (see config/routes.yml).
if (version_compare(_PS_VERSION_, '9.0.0', '>=')) {
    return;
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

        $lengowController = new LengowDashboardController();
        $lengowController->postProcess();
        $lengowController->display();
    }
}
