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
 * Lengow Main Setting Controller Class
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
                $security = LengowMain::decodeLogMessage('global_setting.screen.i_am_sure');
                if (isset($_REQUEST['uninstall_textbox'])
                    && trim($_REQUEST['uninstall_textbox']) === $security
                ) {
                    try {
                        $backup = new LengowBackup();
                        $success = $backup->add();
                    } catch (Exception $e) {
                        $success = false;
                    }
                    if ($success) {
                        LengowMain::log(
                            LengowLog::CODE_UNINSTALL,
                            LengowMain::setLogMessage('log.uninstall.dump_sql_created')
                        );
                        LengowConfiguration::deleteAll();
                        LengowInstall::dropTable();
                        $module = Module::getInstanceByName('lengow');
                        $module->uninstall();
                        $configLink = $this->lengowLink->getAbsoluteAdminLink('AdminModules');
                        Tools::redirect($configLink . '&conf=13', '');
                    }
                }
                $form = new LengowConfigurationForm(
                    [
                        'fields' => LengowConfiguration::getKeys(),
                    ]
                );
                $form->postProcess(
                    [
                        LengowConfiguration::REPORT_MAIL_ENABLED,
                        LengowConfiguration::TRACKING_ENABLED,
                        LengowConfiguration::AUTHORIZED_IP_ENABLED,
                        LengowConfiguration::DEBUG_MODE_ENABLED,
                        LengowConfiguration::SHOP_ACTIVE,
                    ]
                );
                break;
            case 'download':
                $date = isset($_REQUEST[LengowLog::LOG_DATE]) ? $_REQUEST[LengowLog::LOG_DATE] : null;
                LengowLog::download($date);
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
        $form = new LengowConfigurationForm(['fields' => LengowConfiguration::getKeys()]);
        $form->fields[LengowConfiguration::REPORT_MAILS][LengowConfiguration::PARAM_LABEL] = '';
        $mailReport = $form->buildInputs(
            [
                LengowConfiguration::REPORT_MAIL_ENABLED,
                LengowConfiguration::REPORT_MAILS,
            ]
        );
        $defaultExportCarrier = $form->buildInputs([LengowConfiguration::DEFAULT_EXPORT_CARRIER_ID]);
        $tracker = $form->buildInputs(
            [
                LengowConfiguration::TRACKING_ENABLED,
                LengowConfiguration::TRACKING_ID,
            ]
        );
        $ipSecurity = $form->buildInputs(
            [
                LengowConfiguration::AUTHORIZED_IP_ENABLED,
                LengowConfiguration::AUTHORIZED_IPS,
            ]
        );
        $debugReport = $form->buildInputs([LengowConfiguration::DEBUG_MODE_ENABLED]);
        $credentials = $form->buildInputs(
            [
                LengowConfiguration::PLUGIN_ENV,
                LengowConfiguration::ACCOUNT_ID,
                LengowConfiguration::ACCESS_TOKEN,
                LengowConfiguration::SECRET,
            ]
        );
        $debugWrapper = '<div class="grey-frame">' . $credentials . '</div>';
        $shopCatalog = '';
        $shops = LengowShop::findAll(true);
        foreach ($shops as $s) {
            $shop = new LengowShop($s['id_shop']);
            $shopCatalog .= '<h4>' . $shop->name . '</h4>';
            $shopCatalog .= '<div class="grey-frame">' . $form->buildShopInputs(
                $shop->id,
                [
                    LengowConfiguration::SHOP_ACTIVE,
                    LengowConfiguration::CATALOG_IDS,
                ]
            ) . '</div>';
        }
        $listFile = LengowLog::getPaths();
        $this->context->smarty->assign('list_file', $listFile);
        $this->context->smarty->assign('mail_report', $mailReport);
        $this->context->smarty->assign('defaultExportCarrier', $defaultExportCarrier);
        $this->context->smarty->assign('ipSecurity', $ipSecurity);
        $this->context->smarty->assign('debug_report', $debugReport);
        $this->context->smarty->assign('debug_wrapper', $debugWrapper);
        $this->context->smarty->assign('shopCatalog', $shopCatalog);
        parent::display();
    }
}
