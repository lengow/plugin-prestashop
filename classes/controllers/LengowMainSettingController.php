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

class LengowMainSettingController extends LengowController
{

    /**
     * Process Post Parameters
     */
    public function postProcess()
    {
        $action = Tools::getValue('action');
        switch ($action) {
            case 'process':
                $form = new LengowConfigurationForm(
                    array(
                        "fields" => LengowConfiguration::getKeys(),
                    )
                );

                $form->postProcess(
                    array(
                        'LENGOW_REPORT_MAIL_ENABLED',
                        'LENGOW_IMPORT_PREPROD_ENABLED',
                    )
                );
                break;
        }
    }

    /**
     * Display data page
     */
    public function display()
    {
        $form = new LengowConfigurationForm(
            array(
                "fields" => LengowConfiguration::getKeys(),
            )
        );

        $mail_report = $form->buildInputs(
            array(
                'LENGOW_REPORT_MAIL_ENABLED',
                'LENGOW_REPORT_MAIL_ADDRESS',
            )
        );

        $preprod_report = $form->buildInputs(
            array(
                'LENGOW_IMPORT_PREPROD_ENABLED',
            )
        );
        $this->context->smarty->assign('mail_report', $mail_report);
        $this->context->smarty->assign('preprod_report', $preprod_report);
        parent::display();
    }
}
