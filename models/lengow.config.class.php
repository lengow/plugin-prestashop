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
            if (Tools::getIsset('reset-import-lengow')) {
                LengowImport::setEnd();
                $html .= $this->displayConfirmation($this->module->l('Import has been resetted'));
            } else {
                Configuration::updateValue('LENGOW_AUTHORIZED_IP', Tools::getValue('lengow_authorized_ip'));
                Configuration::updateValue('LENGOW_TRACKING', Tools::getValue('lengow_tracking'));
                Configuration::updateValue('LENGOW_TRACKING_ID', Tools::getValue('lengow_tracking_id'));
                Configuration::updateValue('LENGOW_ID_CUSTOMER', Tools::getValue('lengow_customer_id'));
                Configuration::updateValue('LENGOW_ID_GROUP', Tools::getValue('lengow_group_id'));
                Configuration::updateValue('LENGOW_TOKEN', Tools::getValue('lengow_token'));
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

                // Send to Lengow versions
                if (LengowCore::getTokenCustomer() && LengowCore::getIdCustomer() && LengowCore::getGroupCustomer()) {
                    $lengow_connector = new LengowConnector((integer)LengowCore::getIdCustomer(), LengowCore::getTokenCustomer());
                    $lengow_connector->api('updateEcommerceSolution', array('type' => 'Prestashop',
                        'version' => _PS_VERSION_,
                        'idClient' => LengowCore::getIdCustomer(),
                        'idGroup' => LengowCore::getGroupCustomer(),
                        'module' => $this->version));
                }

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
                //$html .= $this->displayConfirmation($this->l('Configuration saved'));
            }
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


        echo Configuration::get('LENGOW_ID_CUSTOMER');
        $links = LengowCore::getWebservicesLinks();
        $this->context->smarty->assign(
            array(
                'lengow_customer_id' => Configuration::get('LENGOW_ID_CUSTOMER'),
                'lengow_group_id' => Configuration::get('LENGOW_ID_GROUP'),
                'lengow_token' => Configuration::get('LENGOW_TOKEN'),
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
                'url_feed_export' => $links['link_feed_export'],
                'url_feed_import' => $links['link_feed_import'],
                'lengow_flow' => $this->getFormFeeds(),
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
                'lengow_export_select_features' => (array)Tools::jsonDecode(Configuration::get('LENGOW_EXPORT_SELECT_FEATURES')),
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
     * Get the form flows.
     *
     * @return string The form flow
     */
    private function getFormFeeds()
    {
        $display = '';
        if (!LengowCheck::isCurlActivated()) {
            return '<p>' . $this->module->l('Function unavailable with your configuration, please install PHP CURL extension.') . '</p>';
        }
        $flows = LengowCore::getFlows();
        if (!$flows || $flows['return'] == 'KO') {
            return '<div clas="lengow-margin">' . $this->module->l('Please provide your Customer ID, Group ID and API Token ') . '</div>';
        }
        $data_flows_array = array();
        $data_flows = Tools::jsonDecode(Configuration::get('LENGOW_FLOW_DATA'));
        if ($data_flows) {
            foreach ($data_flows as $key => $value) {
                $data_flows_array[$key] = get_object_vars($value);
            }
        }
        if (_PS_VERSION_ < '1.5') {
            $controller = '/modules/lengow/v14/ajax.php?';
        } else {
            $controller = 'index.php?controller=AdminLengow&ajax&action=updateFlow&token=' . Tools::getAdminTokenLite('AdminLengow') . '';
        }
        if ($flows['return'] == 'OK') {
            $display = '<div class="table-responsive"><table id="table-flows" class="table table-condensed">';
            $display .= '<tr>'
                . '<th>' . $this->module->l('Feed ID') . '</th>'
                . '<th>' . $this->module->l('Feed name') . '</th>'
                . '<th>' . $this->module->l('Current feed') . '</th>'
                . '<th>' . $this->module->l('Format') . '</th>'
                . '<th>' . $this->module->l('Full mode') . '</th>'
                . '<th>' . $this->module->l('All products') . '</th>'
                . '<th>' . $this->module->l('Currency') . '</th>'
                . '<th>' . $this->module->l('Shop') . '</th>'
                . '<th>' . $this->module->l('Language') . '</th>'
                . '<th></th>'
                . '<td>';
            foreach ($flows['feeds'] as $key => $flow) {
                $display .= '<tr><td>' . $key . '</td><td>' . $flow['name'] . '</td><td><span id="lengow-flux-' . $key . '" class="lengow-flux">';
                $display .= $flow['url'] . '</td>';
                $display .= $this->_formFeed($key, $data_flows_array);
                $display .= '<td>'
                    . '<button id="lengow-migrate-action-' . $key . '" data-url="' . $controller . '" data-flow="' . $key . '" class="lengow-migrate-action">'
                    . $this->module->l('Migrate this flow') . '</button> '
                    . '<button id="lengow-migrate-action-all-' . $key . '" data-url="' . $controller . '" data-flow="' . $key
                    . '" class="lengow-migrate-action-all">' . $this->module->l('Migrate all flows') . '</button>'
                    . '</span> </td>';
                $display .= '</tr>';
            }
            $display .= '</table></div>';
        }
        return $display;
    }

    /**
     * Get inputs to config a flow.
     *
     * @param integer $id_flow The ID of flow to config
     * @param array $data_flows The array of flows's configuration
     *
     * @return string The inputs html
     */
    private function _formFeed($id_flow, &$data_flows)
    {
        $form = '';
        // Init
        $formats = LengowCore::getExportFormats();
        $currencies = Currency::getCurrencies();
        $shops = Shop::getShops();
        $languages = Language::getLanguages();
        if (!isset($data_flows[$id_flow])) {
            $data_flows[$id_flow] = array('format' => $formats[0]->id,
                'mode' => 1,
                'all' => 1,
                'currency' => $currencies[0]['iso_code'],
                'shop' => (array_key_exists(1, $shops) ? $shops[1]['id_shop'] : 1),
                'language' => $languages[0]['iso_code'],
            );
            Configuration::updateValue('LENGOW_FLOW_DATA', Tools::jsonEncode($data_flows));
        }
        $data = $data_flows[$id_flow];
        // Format
        $form .= '<td><select name="format-' . $id_flow . '" id="format-' . $id_flow . '">';
        foreach ($formats as $format) {
            $form .= '<option id="' . $format->id . '"' . ($data['format'] == $format->id ? ' selected="selected"' : '') . '> ' . $format->name . '</option>';
        }
        $form .= '<select></td>';
        // Mode
        $form .= '<td><select name="mode-' . $id_flow . '" id="mode-' . $id_flow . '">';
        $form .= '<option id="1"' . ($data['mode'] == 1 ? ' selected="selected"' : '') . ' value="full"> ' . $this->module->l('yes') . '</option>';
        $form .= '<option id="0"' . ($data['mode'] == 0 ? ' selected="selected"' : '') . ' value="simple"> ' . $this->module->l('no') . '</option>';
        $form .= '<select></td>';

        // All
        $form .= '<td><select name="all-' . $id_flow . '" id="all-' . $id_flow . '">';
        $form .= '<option id="1"' . ($data['all'] == 1 ? ' selected="selected"' : '') . ' value="true"> ' . $this->module->l('yes') . '</option>';
        $form .= '<option id="0"' . ($data['all'] == 0 ? ' selected="selected"' : '') . ' value="false"> ' . $this->module->l('no') . '</option>';
        $form .= '<select></td>';

        // Currency
        $form .= '<td><select name="currency-' . $id_flow . '" id="currency-' . $id_flow . '">';
        foreach ($currencies as $currency) {
            $form .= '<option id="' . $currency['iso_code'] . '"' . ($data['currency'] == $currency['iso_code'] ? ' selected="selected"' : '') . ' value="' . $currency['iso_code'] . '"> ' . $currency['name'] . '</option>';
        }
        $form .= '</select></td>';

        // Shop
        $form .= '<td><select name="shop-' . $id_flow . '" id="shop-' . $id_flow . '">';
        foreach ($shops as $shop) {
            $form .= '<option id="' . $shop['id_shop'] . '"' . ($data['shop'] == $shop['id_shop'] ? ' selected="selected"' : '') . ' value="' . $shop['id_shop'] . '"> ' . $shop['name'] . '</option>';
        }
        $form .= '</select></td>';

        // Langage
        $form .= '<td><select name="lang-' . $id_flow . '" id="lang-' . $id_flow . '">';
        foreach ($languages as $language) {
            $form .= '<option id="' . $language['iso_code'] . '"' . ($data['language'] == $language['iso_code'] ? ' selected="selected"' : '') . ' value="' . $language['iso_code'] . '"> ' . $language['name'] . '</option>';
        }
        $form .= '</select></td>';
        return $form;
    }

    /**
     * Get select cron.
     *
     * @return string The select html
     */
    private function getFormCron()
    {
        $links = LengowCore::getWebservicesLinks();
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
        $form .= '<strong><code>*/15 * * * * wget ' . $links['url_feed_import'] . '</code></strong><br /><br />';
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
