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
class LengowConfig
{

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('lengow');
    }

    public function checkBoxValue($string)
    {
        return Tools::getValue($string) ? Tools::getValue($string) : '0';
    }

    /**
     * Process after post admin form.
     */
    public function postProcessForm()
    {
        $html = '';
        if (Tools::getIsset('submitlengow')) {
            Configuration::updateValue('LENGOW_AUTHORIZED_IP', Tools::getValue('lengow_authorized_ip'));
            Configuration::updateValue('LENGOW_TRACKING', Tools::getValue('lengow_tracking'));
            Configuration::updateValue('LENGOW_TRACKING_ID', Tools::getValue('lengow_tracking_id'));
            Configuration::updateValue('LENGOW_ACCOUNT_ID', Tools::getValue('lengow_account_id'));
            Configuration::updateValue('LENGOW_ACCESS_TOKEN', Tools::getValue('lengow_access_token'));
            Configuration::updateValue('LENGOW_SECRET', Tools::getValue('lengow_secret'));
            Configuration::updateValue('LENGOW_EXPORT_SELECTION', $this->checkBoxValue('lengow_export_selection'));
            Configuration::updateValue('LENGOW_EXPORT_NEW', $this->checkBoxValue('lengow_export_new'));
            Configuration::updateValue('LENGOW_EXPORT_ALL_VARIATIONS', $this->checkBoxValue('lengow_export_all_variations'));
            Configuration::updateValue('LENGOW_EXPORT_FEATURES', $this->checkBoxValue('lengow_export_features'));
            Configuration::updateValue('LENGOW_EXPORT_FULLNAME', $this->checkBoxValue('lengow_export_fullname'));
            Configuration::updateValue('LENGOW_EXPORT_FIELDS', Tools::jsonEncode(Tools::getValue('lengow_export_fields')));
            Configuration::updateValue('LENGOW_ORDER_ID_PROCESS', Tools::getValue('lengow_order_process'));
            Configuration::updateValue('LENGOW_ORDER_ID_SHIPPED', Tools::getValue('lengow_order_shipped'));
            Configuration::updateValue('LENGOW_ORDER_ID_CANCEL', Tools::getValue('lengow_order_cancel'));
            Configuration::updateValue('LENGOW_IMAGE_TYPE', Tools::getValue('lengow_image_type'));
            Configuration::updateValue('LENGOW_IMAGES_COUNT', Tools::getValue('lengow_images_count'));
            Configuration::updateValue('LENGOW_IMPORT_METHOD_NAME', Tools::getValue('lengow_method_name'));
            Configuration::updateValue('LENGOW_IMPORT_FORCE_PRODUCT', Tools::getValue('lengow_import_force_product'));
            Configuration::updateValue('LENGOW_IMPORT_DAYS', Tools::getValue('lengow_import_days'));
            Configuration::updateValue('LENGOW_FORCE_PRICE', Tools::getValue('lengow_force_price'));
            Configuration::updateValue('LENGOW_EXPORT_FORMAT', Tools::getValue('lengow_export_format'));
            Configuration::updateValue('LENGOW_EXPORT_FILE', $this->checkBoxValue('lengow_export_file'));
            Configuration::updateValue('LENGOW_CARRIER_DEFAULT', Tools::getValue('lengow_carrier_default'));
            Configuration::updateValue('LENGOW_IMPORT_CARRIER_DEFAULT', Tools::getValue('lengow_import_carrier_default'));
            Configuration::updateValue('LENGOW_DEBUG', Tools::getValue('lengow_debug'));
            Configuration::updateValue('LENGOW_PARENT_IMAGE', Tools::getValue('lengow_parent_image'));
            Configuration::updateValue('LENGOW_FEED_MANAGEMENT', Tools::getValue('lengow_feed_management'));
            Configuration::updateValue('LENGOW_EXPORT_DISABLED', $this->checkBoxValue('lengow_export_disabled'));
            Configuration::updateValue('LENGOW_EXPORT_OUT_STOCK', $this->checkBoxValue('lengow_export_out_stock'));
            Configuration::updateValue('LENGOW_IMPORT_PROCESSING_FEE', Tools::getValue('lengow_import_processing_fee'));
            Configuration::updateValue('LENGOW_IMPORT_FAKE_EMAIL', Tools::getValue('lengow_import_fake_email'));
            Configuration::updateValue('LENGOW_MP_SHIPPING_METHOD', Tools::getValue('lengow_mp_shipping_method'));
            Configuration::updateValue('LENGOW_REPORT_MAIL', Tools::getValue('lengow_report_mail'));
            Configuration::updateValue('LENGOW_IMPORT_SINGLE', Tools::getValue('lengow_import_single'));
            Configuration::updateValue('LENGOW_EXPORT_TIMEOUT', Tools::getValue('lengow_export_timeout'));
            Configuration::updateValue('LENGOW_EMAIL_ADDRESS', Tools::getValue('lengow_email_address'));
            Configuration::updateValue('LENGOW_ORDER_ID_SHIPPEDBYMP', Tools::getValue('lengow_order_shippedByMp'));
            Configuration::updateValue('LENGOW_CRON_EDITOR', Tools::getValue('lengow_cron_editor'));
            Configuration::updateValue('LENGOW_IMPORT_SHIPPED_BY_MP', Tools::getValue('lengow_import_shipped_by_mp'));
            Configuration::updateValue('LENGOW_EXPORT_SELECT_FEATURES', Tools::jsonEncode(Tools::getValue('lengow_export_select_features')));

            if (Tools::getValue('cron-delay') > 0) {
                Configuration::updateValue('LENGOW_CRON', Tools::getValue('cron-delay'));
                self::updateCron(Tools::getValue('cron-delay'));
            }
            if (Module::isInstalled('cronjobs') && Configuration::get('LENGOW_CRON_EDITOR')) {
                $result = LengowCore::addCronTasks(Context::getContext()->shop->id, $this);
                if (!empty($result)) {
                    if (isset($result['success'])) {
                        foreach ($result['success'] as $message) {
                            $html .= $this->displayConfirmation($message);
                        }
                    }

                    if (isset($result['error'])) {
                        foreach ($result['error'] as $message) {
                            $html .= $this->displayConfirmation($message);
                        }
                    }
                }
            } else {
                $result = LengowCore::removeCronTasks(Context::getContext()->shop->id, $this);
                if (!empty($result)) {
                    if (isset($result['success'])) {
                        $html .= $this->displayConfirmation($result['success']);
                    }
                    if (isset($result['error'])) {
                        $html .= $this->displayConfirmation($result['error']);
                    }
                }
            }
            // $html .= $this->displayConfirmation($this->l('Configuration saved'));
        } elseif (Tools::getIsset('reset-import-lengow')) {
            LengowImport::setEnd();
            // $html .= $this->displayConfirmation($this->module->l('Import has been resetted'));
        }
        return $html;
    }


    public function displayForm($firstcall = true)
    {
        if (_PS_VERSION_ <= '1.4.4.0') {
            $options = array(
                'carriers' => LengowCarrier::getCarriers($this->context->cookie->id_lang, true, false, false, null, ALL_CARRIERS),
            );
        } else {
            $options = array(
                'carriers' => LengowCarrier::getCarriers($this->context->cookie->id_lang, true, false, false, null, LengowCarrier::ALL_CARRIERS),
            );
        }

        $options['export_fields'] = LengowExport::getDefaultFields();
        $options['shippings'] = LengowCore::getShippingName();
        $options['formats'] = LengowCore::getExportFormats();
        $options['states'] = OrderState::getOrderStates((int)$this->context->cookie->id_lang);
        $options['trackers'] = LengowCore::getTrackers();
        $options['images'] = ImageType::getImagesTypes('products');
        $options['export_features'] = LengowCore::getFeaturesOptions();
        $options['images_count'] = LengowCore::getImagesCount();
        $this->context->smarty->assign(
            array(
                'lengow_account_id' => Configuration::get('LENGOW_ACCOUNT_ID'),
                'lengow_access_token' => Configuration::get('LENGOW_ACCESS_TOKEN'),
                'lengow_secret' => Configuration::get('LENGOW_SECRET'),
                'lengow_authorized_ip' => Configuration::get('LENGOW_AUTHORIZED_IP'),
                'lengow_export_selection' => Configuration::get('LENGOW_EXPORT_SELECTION'),
                'lengow_export_disabled' => Configuration::get('LENGOW_EXPORT_DISABLED'),
                'lengow_export_new' => Configuration::get('LENGOW_EXPORT_NEW'),
                'lengow_export_all_variations' => Configuration::get('LENGOW_EXPORT_ALL_VARIATIONS'),
                'lengow_export_fullname' => Configuration::get('LENGOW_EXPORT_FULLNAME'),
                'lengow_export_features' => Configuration::get('LENGOW_EXPORT_FEATURES'),
                'lengow_export_file' => Configuration::get('LENGOW_EXPORT_FILE'),
                'lengow_export_fields' => (array)Tools::jsonDecode(Configuration::get('LENGOW_EXPORT_FIELDS')),
                'lengow_tracking' => Configuration::get('LENGOW_TRACKING'),
                'lengow_tracking_id' => Configuration::get('LENGOW_TRACKING_ID'),
                'lengow_order_process' => Configuration::get('LENGOW_ORDER_ID_PROCESS'),
                'lengow_order_shipped' => Configuration::get('LENGOW_ORDER_ID_SHIPPED'),
                'lengow_order_cancel' => Configuration::get('LENGOW_ORDER_ID_CANCEL'),
                'lengow_image_type' => Configuration::get('LENGOW_IMAGE_TYPE'),
                'lengow_images_count' => Configuration::get('LENGOW_IMAGES_COUNT'),
                'lengow_method_name' => Configuration::get('LENGOW_IMPORT_METHOD_NAME'),
                'lengow_import_days' => Configuration::get('LENGOW_IMPORT_DAYS'),
                'lengow_export_format' => Configuration::get('LENGOW_EXPORT_FORMAT'),
                'lengow_import_force_product' => Configuration::get('LENGOW_IMPORT_FORCE_PRODUCT'),
                'lengow_carrier_default' => Configuration::get('LENGOW_CARRIER_DEFAULT'),
                'lengow_force_price' => Configuration::get('LENGOW_FORCE_PRICE'),
                'lengow_debug' => Configuration::get('LENGOW_DEBUG'),
                'lengow_feed_management' => Configuration::get('LENGOW_FEED_MANAGEMENT'),
                'lengow_parent_image' => Configuration::get('LENGOW_PARENT_IMAGE'),
                'lengow_export_out_stock' => Configuration::get('LENGOW_EXPORT_OUT_STOCK'),
                'lengow_import_processing_fee' => Configuration::get('LENGOW_IMPORT_PROCESSING_FEE'),
                'url_feed_export' => LengowCore::getExportUrl(),
                'url_feed_import' => LengowCore::getImportUrl(),
                'lengow_cron' => $this->getFormCron(),
                'lengow_is_import' => $this->getFormIsImport(),
                'options' => $options,
                'checklist' => LengowCheck::getHtmlCheckList(),
                'log_files' => $this->getLogFiles(), 'help_credentials' => $this->getHelpSolutionIds(),
                'lengow_import_fake_email' => Configuration::get('LENGOW_IMPORT_FAKE_EMAIL'),
                'lengow_mp_shipping_method' => Configuration::get('LENGOW_MP_SHIPPING_METHOD'),
                'lengow_report_mail' => Configuration::get('LENGOW_REPORT_MAIL'),
                'lengow_import_single' => Configuration::get('LENGOW_IMPORT_SINGLE'),
                'lengow_export_timeout' => Configuration::get('LENGOW_EXPORT_TIMEOUT'),
                'lengow_email_address' => Configuration::get('LENGOW_EMAIL_ADDRESS'),
                'lengow_order_shippedByMp' => Configuration::get('LENGOW_ORDER_ID_SHIPPEDBYMP'),
                'lengow_import_carrier_default' => Configuration::get('LENGOW_IMPORT_CARRIER_DEFAULT'),
                'lengow_export_feed_files' => $this->getExportFeeds(),
                'lengow_import_shipped_by_mp' => Configuration::get('LENGOW_IMPORT_SHIPPED_BY_MP'),
                'lengow_export_select_features' => (array)Tools::jsonDecode(
                    Configuration::get('LENGOW_EXPORT_SELECT_FEATURES')
                ),
            )
        );
    }


    /**
     * Get export files links
     *
     * @return string
     */
    private function getExportFeeds()
    {
        $feed_links = LengowFeed::getLinks();
        if (!$feed_links) {
            return '<p class="preference_description">'.$this->module->l('No export file available').'</p>';
        }
        $output = '';
        foreach ($feed_links as $link) {
            $output .= '<a href="' . $link . '" target="_blank">' . $link . '</a><br />';
        }
        return $output;
    }


    private function getHelpSolutionIds()
    {
        $out = '';
        $out .= '<p class="preference_description">';
        $out .= sprintf($this->module->l('You can find credentials on %s.'), '<a href="https://solution.lengow.com/api/" target="_blank">' . $this->module->l('your Lengow Dashboard') . '</a>');
        $out .= '<br />';
        $out .= $this->module->l('You can add more than 1 group, must be separated by <b>,</b>');
        $out .= '<br />';
        $out .= sprintf($this->module->l('Make sure your website IP (%s) address is filled in your Lengow Dashboard.', 'lengow.check.class'), $_SERVER['REMOTE_ADDR']);
        $out .= '<br />';
        $out .= sprintf($this->module->l('%s for assistance.'), '<a href="' . $this->module->l('https://en.helpgizmo.com/help/article/link/prestashopv2') . '" target="_blank">' . $this->module->l('Click here') . '</a>');
        $out .= '</p>';
        return $out;
    }

    /**
     * Get logs files
     *
     * @return string
     */
    private function getLogFiles()
    {
        $logs_links = LengowLog::getLinks();
        if (!$logs_links) {
            return $this->module->l('No logs available');
        }
        $logs_links = array_reverse($logs_links);
        $output = '';
        foreach ($logs_links as $link) {
            $file_names = explode('/', $link);
            $output .= '<a href="' . $link . '" target="_blank">' . end($file_names) . '</a><br />';

        }
        return $output;
    }

    /**
     * Get select cron.
     *
     * @return string The select html
     */
    private function getFormCron()
    {
        if (Module::getInstanceByName('cron')) {
            $form = '<p>' . $this->module->l('You can use the Crontab Module to import orders from Lengow') . '</p>';
            $cron_value = Configuration::get('LENGOW_CRON');
            $form .= '<select id="cron-delay" name="cron-delay">';
            $form .= '<option value="NULL">' . $this->module->l('No cron configured') . '</option>';
            foreach (self::$_CRON_SELECT as $value) {
                $form .= '<option value="' . $value . '"' . ($cron_value == $value ? ' selected="selected"' : '') . '>' . $value . ' ' . $this->l('min') . '</option>';
            }
            $form .= '</select>';
            if (!self::getCron()) {
                $form .= '<span class="lengow-no">' . $this->module->l('Cron Import is not configured on your Prestashop') . '</span>';
            } else {
                $form .= '<span class="lengow-yes">' . $this->module->l('Cron Import exists on your Prestashop') . '</span>';
            }
            $form .= '<p> - ' . $this->module->l('or') . ' - </p>';
        } else {
            $form = '<p>' . $this->module->l('You can install "Crontab" Prestashop Plugin') . '</p>';
            $form .= '<p> - ' . $this->module->l('or') . ' - </p>';
        }
        $form .= '<p>' . $this->module->l('If you are using an unix system, you can use unix crontab like this :') . '</p>';
        $form .= '<strong><code>*/15 * * * * wget ' . LengowCore::getImportUrl() . '</code></strong><br /><br />';
        return '<div class="lengow-margin">' . $form . '</div>';
    }

    /**
     *
     * Get state of import process
     *
     * @return string Html content
     */
    private function getFormIsImport()
    {
        $content = '';
        if (LengowImport::isInProcess()) {
            $content .= '<p class="preference_description">' . $this->module->l(sprintf('Import seems to be currently running (last launch: %s). Click on the button below to reset it', date('Y-m-d H:i:s', Configuration::get('LENGOW_IS_IMPORT')))) . '</p>';
            $content .= '<input type="submit" value="' . $this->module->l('Reset import') . '"" name="reset-import-lengow" id="reset-import-lengow" />';
        } else {
            $content .= '<p class="preference_description">' . $this->module->l('No import process currently running.') . '</p>';
        }
        return $content;
    }
}
