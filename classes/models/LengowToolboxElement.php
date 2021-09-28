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

/**
 * Lengow Toolbox Element Class
 */
class LengowToolboxElement
{
    /* Array data for toolbox content creation */
    const DATA_HEADER = 'header';
    const DATA_TITLE = 'title';
    const DATA_STATE = 'state';
    const DATA_MESSAGE = 'message';
    const DATA_HELP = 'help';
    const DATA_HELP_LINK = 'help_link';
    const DATA_HELP_LABEL = 'help_label';

    /**
     * @var LengowTranslation $locale Lengow translation instance
     */
    protected $locale;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->locale = new LengowTranslation();
    }

    /**
     * Get array of requirements for toolbox
     *
     * @return string
     */
    public function getCheckList()
    {
        $checklistData = LengowToolbox::getData(LengowToolbox::DATA_TYPE_CHECKLIST);
        $mailCheck = $this->getMailConfiguration();
        $checklist = array(
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.curl_message'),
                self::DATA_HELP => $this->locale->t('toolbox.index.curl_help'),
                self::DATA_HELP_LINK => $this->locale->t('toolbox.index.curl_help_link'),
                self::DATA_HELP_LABEL => $this->locale->t('toolbox.index.curl_help_label'),
                self::DATA_STATE => (int) $checklistData[LengowToolbox::CHECKLIST_CURL_ACTIVATED],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.simple_xml_message'),
                self::DATA_HELP => $this->locale->t('toolbox.index.simple_xml_help'),
                self::DATA_HELP_LINK => $this->locale->t('toolbox.index.simple_xml_help_link'),
                self::DATA_HELP_LABEL => $this->locale->t('toolbox.index.simple_xml_help_label'),
                self::DATA_STATE => (int) $checklistData[LengowToolbox::CHECKLIST_SIMPLE_XML_ACTIVATED],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.json_php_message'),
                self::DATA_HELP => $this->locale->t('toolbox.index.json_php_help'),
                self::DATA_HELP_LINK => $this->locale->t('toolbox.index.json_php_help_link'),
                self::DATA_HELP_LABEL => $this->locale->t('toolbox.index.json_php_help_label'),
                self::DATA_STATE => (int) $checklistData[LengowToolbox::CHECKLIST_JSON_ACTIVATED],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.shop_functionality_message'),
                self::DATA_HELP => $this->locale->t('toolbox.index.shop_functionality_help'),
                self::DATA_STATE => (int) $this->isShopActivated(),
            ),
            array(
                self::DATA_TITLE => $mailCheck[self::DATA_MESSAGE],
                self::DATA_STATE => $mailCheck[self::DATA_STATE],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.checksum_message'),
                self::DATA_HELP => $this->locale->t('toolbox.index.checksum_help'),
                self::DATA_HELP_LINK => __PS_BASE_URI__ . 'modules/lengow/toolbox/checksum.php',
                self::DATA_HELP_LABEL => $this->locale->t('toolbox.index.checksum_help_label'),
                self::DATA_STATE => (int) $checklistData[LengowToolbox::CHECKLIST_MD5_SUCCESS],
            ),
        );
        return $this->getAdminContent($checklist);
    }

    /**
     * Get all global information for toolbox
     *
     * @return string
     */
    public function getGlobalInformation()
    {

        $pluginData = LengowToolbox::getData(LengowToolbox::DATA_TYPE_PLUGIN);
        $checklist = array(
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.prestashop_version'),
                self::DATA_MESSAGE => $pluginData[LengowToolbox::PLUGIN_CMS_VERSION],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.plugin_version'),
                self::DATA_MESSAGE => $pluginData[LengowToolbox::PLUGIN_VERSION],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.ip_server'),
                self::DATA_MESSAGE => $pluginData[LengowToolbox::PLUGIN_SERVER_IP],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.authorized_ip_enable'),
                self::DATA_STATE => (int) $pluginData[LengowToolbox::PLUGIN_AUTHORIZED_IP_ENABLE],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.ip_authorized'),
                self::DATA_MESSAGE => implode(', ', $pluginData[LengowToolbox::PLUGIN_AUTHORIZED_IPS]),
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.debug_disabled'),
                self::DATA_STATE => (int) $pluginData[LengowToolbox::PLUGIN_DEBUG_MODE_DISABLE],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.write_permission'),
                self::DATA_STATE => (int) $pluginData[LengowToolbox::PLUGIN_WRITE_PERMISSION],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.toolbox_url'),
                self::DATA_MESSAGE => $pluginData[LengowToolbox::PLUGIN_TOOLBOX_URL],
            ),
        );
        return $this->getAdminContent($checklist);
    }

    /**
     * Get all import information for toolbox
     *
     * @return string
     */
    public function getImportInformation()
    {
        $synchronizationData = LengowToolbox::getData(LengowToolbox::DATA_TYPE_SYNCHRONIZATION);
        $lastSynchronization = $synchronizationData[LengowToolbox::SYNCHRONIZATION_LAST_SYNCHRONIZATION];
        if ($lastSynchronization === 0) {
            $lastImportDate = $this->locale->t('toolbox.index.last_import_none');
            $lastImportType = $this->locale->t('toolbox.index.last_import_none');
        } else {
            $lastImportDate = LengowMain::getDateInCorrectFormat($lastSynchronization, true);
            $lastSynchronizationType = $synchronizationData[LengowToolbox::SYNCHRONIZATION_LAST_SYNCHRONIZATION_TYPE];
            $lastImportType = $lastSynchronizationType === LengowImport::TYPE_CRON
                ? $this->locale->t('toolbox.index.last_import_cron')
                : $this->locale->t('toolbox.index.last_import_manual');
        }
        if ($synchronizationData[LengowToolbox::SYNCHRONIZATION_SYNCHRONIZATION_IN_PROGRESS]) {
            $importInProgress = LengowMain::decodeLogMessage(
                'toolbox.index.rest_time_to_import',
                null,
                array('rest_time' => LengowImport::restTimeToImport())
            );
        } else {
            $importInProgress = $this->locale->t('toolbox.index.no_import');
        }
        $checklist = array(
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.global_token'),
                self::DATA_MESSAGE => $synchronizationData[LengowToolbox::SYNCHRONIZATION_CMS_TOKEN],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.url_import'),
                self::DATA_MESSAGE => $synchronizationData[LengowToolbox::SYNCHRONIZATION_CRON_URL],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.nb_order_imported'),
                self::DATA_MESSAGE => $synchronizationData[LengowToolbox::SYNCHRONIZATION_NUMBER_ORDERS_IMPORTED],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.nb_order_to_be_sent'),
                self::DATA_MESSAGE => $synchronizationData[
                    LengowToolbox::SYNCHRONIZATION_NUMBER_ORDERS_WAITING_SHIPMENT
                ],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.nb_order_with_error'),
                self::DATA_MESSAGE => $synchronizationData[LengowToolbox::SYNCHRONIZATION_NUMBER_ORDERS_IN_ERROR],
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.import_in_progress'),
                self::DATA_MESSAGE => $importInProgress,
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.shop_last_import'),
                self::DATA_MESSAGE => $lastImportDate,
            ),
            array(
                self::DATA_TITLE => $this->locale->t('toolbox.index.shop_type_import'),
                self::DATA_MESSAGE => $lastImportType,
            ),
        );
        return $this->getAdminContent($checklist);
    }

    /**
     * Get all shop information for toolbox
     *
     * @return string
     */
    public function getExportInformation()
    {
        $content = '';
        $exportData = LengowToolbox::getData(LengowToolbox::DATA_TYPE_SHOP);
        foreach ($exportData as $data) {
            if ($data[LengowToolbox::SHOP_LAST_EXPORT] !== 0) {
                $lastExport = LengowMain::getDateInCorrectFormat($data[LengowToolbox::SHOP_LAST_EXPORT], true);
            } else {
                $lastExport = $this->locale->t('toolbox.index.last_import_none');
            }
            $shopOptions = $data[LengowToolbox::SHOP_OPTIONS];
            $selectionEnabledKey = LengowConfiguration::$genericParamKeys[LengowConfiguration::SELECTION_ENABLED];
            $variationEnabledKey = LengowConfiguration::$genericParamKeys[LengowConfiguration::VARIATION_ENABLED];
            $outOfStockEnabledKey = LengowConfiguration::$genericParamKeys[LengowConfiguration::OUT_OF_STOCK_ENABLED];
            $inactiveEnabledKey = LengowConfiguration::$genericParamKeys[LengowConfiguration::INACTIVE_ENABLED];
            $checklist = array(
                array(
                    self::DATA_HEADER => $data[LengowToolbox::SHOP_NAME]
                        . ' (' . $data[LengowToolbox::SHOP_ID] . ')'
                        . ' - ' . $data[LengowToolbox::SHOP_DOMAIN_URL],
                ),
                array(
                    self::DATA_TITLE => $this->locale->t('toolbox.index.shop_active'),
                    self::DATA_STATE => (int) $data[LengowToolbox::SHOP_ENABLED],
                ),
                array(
                    self::DATA_TITLE => $this->locale->t('toolbox.index.shop_catalogs_id'),
                    self::DATA_MESSAGE => implode(', ', $data[LengowToolbox::SHOP_CATALOG_IDS]),
                ),
                array(
                    self::DATA_TITLE => $this->locale->t('toolbox.index.shop_product_total'),
                    self::DATA_MESSAGE => $data[LengowToolbox::SHOP_NUMBER_PRODUCTS_AVAILABLE],
                ),
                array(
                    self::DATA_TITLE => $this->locale->t('toolbox.index.shop_product_exported'),
                    self::DATA_MESSAGE => $data[LengowToolbox::SHOP_NUMBER_PRODUCTS_EXPORTED],
                ),
                array(
                    self::DATA_TITLE => $this->locale->t('toolbox.index.export_selection_enabled'),
                    self::DATA_STATE => (int) $shopOptions[$selectionEnabledKey],
                ),
                array(
                    self::DATA_TITLE => $this->locale->t('toolbox.index.export_variation_enabled'),
                    self::DATA_STATE => (int) $shopOptions[$variationEnabledKey],
                ),
                array(
                    self::DATA_TITLE => $this->locale->t('toolbox.index.export_out_stock_enabled'),
                    self::DATA_STATE => (int) $shopOptions[$outOfStockEnabledKey],
                ),
                array(
                    self::DATA_TITLE => $this->locale->t('toolbox.index.export_inactive_enabled'),
                    self::DATA_STATE => (int) $shopOptions[$inactiveEnabledKey],
                ),
                array(
                    self::DATA_TITLE => $this->locale->t('toolbox.index.shop_export_token'),
                    self::DATA_MESSAGE => $data[LengowToolbox::SHOP_TOKEN],
                ),
                array(
                    self::DATA_TITLE => $this->locale->t('toolbox.index.url_export'),
                    self::DATA_MESSAGE => $data[LengowToolbox::SHOP_FEED_URL],
                ),
                array(
                    self::DATA_TITLE => $this->locale->t('toolbox.index.shop_last_export'),
                    self::DATA_MESSAGE => $lastExport,
                ),
            );
            $content .= $this->getAdminContent($checklist);
        }
        return $content;
    }

    /**
     * Get files checksum information
     *
     * @return string
     */
    public function checkFileMd5()
    {
        $checklist = array();
        $checksumData = LengowToolbox::getData(LengowToolbox::DATA_TYPE_CHECKSUM);
        $html = '<h3><i class="fa fa-commenting"></i> ' . $this->locale->t('toolbox.checksum.summary') . '</h3>';
        if ($checksumData[LengowToolbox::CHECKSUM_AVAILABLE]) {
            $checklist[] = array(
                self::DATA_TITLE => $this->locale->t(
                    'toolbox.checksum.file_checked',
                    array('nb_file' => $checksumData[LengowToolbox::CHECKSUM_NUMBER_FILES_CHECKED])
                ),
                self::DATA_STATE => 1,
            );
            $checklist[] = array(
                self::DATA_TITLE => $this->locale->t(
                    'toolbox.checksum.file_modified',
                    array('nb_file' => $checksumData[LengowToolbox::CHECKSUM_NUMBER_FILES_MODIFIED])
                ),
                self::DATA_STATE => (int) ($checksumData[LengowToolbox::CHECKSUM_NUMBER_FILES_MODIFIED] === 0),
            );
            $checklist[] = array(
                self::DATA_TITLE => $this->locale->t(
                    'toolbox.checksum.file_deleted',
                    array('nb_file' => $checksumData[LengowToolbox::CHECKSUM_NUMBER_FILES_DELETED])
                ),
                self::DATA_STATE => (int) ($checksumData[LengowToolbox::CHECKSUM_NUMBER_FILES_DELETED] === 0),
            );
            $html .= $this->getAdminContent($checklist);
            if (!empty($checksumData[LengowToolbox::CHECKSUM_FILE_MODIFIED])) {
                $fileModified = array();
                foreach ($checksumData[LengowToolbox::CHECKSUM_FILE_MODIFIED] as $file) {
                    $fileModified[] = array(
                        self::DATA_TITLE => $file,
                        self::DATA_STATE => 0,
                    );
                }
                $html .= '<h3><i class="fa fa-list"></i> '
                    . $this->locale->t('toolbox.checksum.list_modified_file') . '</h3>';
                $html .= $this->getAdminContent($fileModified);
            }
            if (!empty($checksumData[LengowToolbox::CHECKSUM_FILE_DELETED])) {
                $fileDeleted = array();
                foreach ($checksumData[LengowToolbox::CHECKSUM_FILE_DELETED] as $file) {
                    $fileDeleted[] = array(
                        self::DATA_TITLE => $file,
                        self::DATA_STATE => 0,
                    );
                }
                $html .= '<h3><i class="fa fa-list"></i> '
                    . $this->locale->t('toolbox.checksum.list_deleted_file') . '</h3>';
                $html .= $this->getAdminContent($fileDeleted);
            }
        } else {
            $checklist[] = array(
                self::DATA_TITLE => $this->locale->t('toolbox.checksum.file_not_exists'),
                self::DATA_STATE => 0,
            );
            $html .= $this->getAdminContent($checklist);
        }
        return $html;
    }

    /**
     * Get mail configuration information
     *
     * @return array
     */
    private function getMailConfiguration()
    {
        $mailMethod = (int) Configuration::get('PS_MAIL_METHOD');
        if ($mailMethod === 2) {
            return array(
                self::DATA_MESSAGE => $this->locale->t('toolbox.index.mail_configuration_enabled'),
                self::DATA_STATE => 0,
            );
        }
        if ($mailMethod === 3) {
            return array(
                self::DATA_MESSAGE => $this->locale->t('toolbox.index.email_disable'),
                self::DATA_STATE => 0,
            );
        }
        return array(
            self::DATA_MESSAGE => $this->locale->t('toolbox.index.email_using_php_mail'),
            self::DATA_STATE => 1,
        );
    }

    /**
     * Check if shop functionality are enabled
     *
     * @return boolean
     */
    private function isShopActivated()
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            return false;
        }
        return true;
    }

    /**
     * Get HTML Table content of checklist
     *
     * @param array $checklist all information for toolbox
     *
     * @return string
     */
    private function getAdminContent($checklist = array())
    {
        if (empty($checklist)) {
            return null;
        }
        $out = '<table class="table" cellpadding="0" cellspacing="0">';
        foreach ($checklist as $check) {
            $out .= '<tr>';
            if (isset($check[self::DATA_HEADER])) {
                $out .= '<td colspan="2" align="center" style="border:0"><h4>'
                    . $check[self::DATA_HEADER] . '</h4></td>';
            } else {
                $out .= '<td><b>' . $check[self::DATA_TITLE] . '</b></td>';
                if (isset($check[self::DATA_STATE])) {
                    if ($check[self::DATA_STATE] === 1) {
                        $out .= '<td align="right"><i class="fa fa-check lengow-green"></i></td>';
                    } else {
                        $out .= '<td align="right"><i class="fa fa-times lengow-red"></i></td>';
                    }
                    if (($check[self::DATA_STATE] === 0)
                        && isset(
                            $check[self::DATA_HELP],
                            $check[self::DATA_HELP_LINK],
                            $check[self::DATA_HELP_LABEL]
                        )
                    ) {
                        $out .= '<tr><td colspan="2"><p>' . $check[self::DATA_HELP];
                        if (array_key_exists(self::DATA_HELP_LINK, $check) && $check[self::DATA_HELP_LINK] !== '') {
                            $out .= '<br /><a target="_blank" href="'
                                . $check[self::DATA_HELP_LINK] . '">' . $check[self::DATA_HELP_LABEL] . '</a>';
                        }
                        $out .= '</p></td></tr>';
                    }
                } else {
                    $out .= '<td align="right"><b>' . $check[self::DATA_MESSAGE] . '</b></td>';
                }
            }
            $out .= '</tr>';
        }
        $out .= '</table>';
        return $out;
    }
}
