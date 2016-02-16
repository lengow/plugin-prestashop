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
                        'LENGOW_REPORT_MAIL_ADDRESS',
                        'LENGOW_IMPORT_PREPROD_ENABLED',
                        'LENGOW_SHOP_ACTIVE',
                    )
                );
                break;
            case 'download':
                $file = isset($_REQUEST['file']) ?  $_REQUEST['file'] : null;
                LengowLog::download($file);
                break;
            case 'download_all':
                LengowLog::download();
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
        $form->fields['LENGOW_REPORT_MAIL_ADDRESS']['label'] = '';
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

        $preprod_wrapper = '';
        $shops = LengowShop::findAll();
        foreach ($shops as $s) {
            $shop = new LengowShop($s['id_shop']);
            $form->fields['LENGOW_SHOP_ACTIVE']['label'] = $shop->name;
            $preprod_wrapper.= '<div class="lengow_preprod_wrapper">'.$form->buildShopInputs($shop->id, array(
                'LENGOW_SHOP_ACTIVE',
                'LENGOW_ACCOUNT_ID',
                'LENGOW_ACCESS_TOKEN',
                'LENGOW_SECRET_TOKEN',
            )).'</div>';
        }

        $listFile = LengowLog::getPaths();

        $this->context->smarty->assign('list_file', $listFile);
        $this->context->smarty->assign('mail_report', $mail_report);
        $this->context->smarty->assign('preprod_report', $preprod_report);
        $this->context->smarty->assign('preprod_wrapper', $preprod_wrapper);
        parent::display();
    }
}
