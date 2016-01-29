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

class LengowOrderSettingController extends LengowController
{
    /**
     * Display data page
     */
    public function display()
    {
        $form = new LengowConfigurationForm(array(
            "fields" => LengowConfiguration::getKeys(),
            ));

        $matching = $form->buildInputs(array(
            'LENGOW_ORDER_ID_PROCESS',
            'LENGOW_ORDER_ID_SHIPPED',
            'LENGOW_ORDER_ID_CANCEL',
            'LENGOW_ORDER_ID_SHIPPEDBYMP'
            ));

        $matching2 = $form->buildInputs(array('LENGOW_IMPORT_CARRIER_DEFAULT'));

        $matching3 = $form->buildInputs(array('LENGOW_IMPORT_CARRIER_MP_ENABLED'));

        $matching4 = $form->buildInputs(array(
            'LENGOW_IMPORT_DAYS'
            ));

        $this->context->smarty->assign('matching', $matching);
        $this->context->smarty->assign('matching2', $matching2);
        $this->context->smarty->assign('matching3', $matching3);
        $this->context->smarty->assign('matching4', $matching4);

        parent::display();
    }

    public function postProcess() {


        $form = new LengowConfigurationForm(array(
            "fields" => LengowConfiguration::getKeys(),
            ));

        $form->postProcess(array('LENGOW_IMPORT_CARRIER_MP_ENABLED'));

    }
}
