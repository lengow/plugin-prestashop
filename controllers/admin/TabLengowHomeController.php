<?php
/**
 * Copyright 2014 Lengow SAS.
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
 *  @author    Ludovic Drin <ludovic@lengow.com> Romain Le Polh <romain@lengow.com>
 *  @copyright 2014 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * The AdminTab Lengow Class.
 *
 * @author Ludovic Drin <ludovic@lengow.com>
 * @copyright 2013 Lengow SAS
 */

class AdminLengowHome14 extends AdminTab
{
    public function __construct()
    {
        //$this->view = true;
        parent::__construct();

        $_GET['controller'] = 'AdminLengowHome';

        $module = Module::getInstanceByName('lengow');
        echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/header.tpl');
        echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/lengow_home/helpers/view/view.tpl');
    }

    public function display()
    {

    }
}
