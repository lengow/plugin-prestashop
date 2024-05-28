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
    public const DATA_HEADER = 'header';
    public const DATA_TITLE = 'title';
    public const DATA_STATE = 'state';
    public const DATA_MESSAGE = 'message';
    public const DATA_SIMPLE = 'simple';
    public const DATA_HELP = 'help';
    public const DATA_HELP_LINK = 'help_link';
    public const DATA_HELP_LABEL = 'help_label';

    /**
     * @var LengowTranslation Lengow translation instance
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
        $checklist = [
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.curl_message'),
                self::DATA_HELP => $this->locale->t('toolbox.screen.curl_help'),
                self::DATA_HELP_LINK => $this->locale->t('toolbox.screen.curl_help_link'),
                self::DATA_HELP_LABEL => $this->locale->t('toolbox.screen.curl_help_label'),
                self::DATA_STATE => (int) $checklistData[LengowToolbox::CHECKLIST_CURL_ACTIVATED],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.simple_xml_message'),
                self::DATA_HELP => $this->locale->t('toolbox.screen.simple_xml_help'),
                self::DATA_HELP_LINK => $this->locale->t('toolbox.screen.simple_xml_help_link'),
                self::DATA_HELP_LABEL => $this->locale->t('toolbox.screen.simple_xml_help_label'),
                self::DATA_STATE => (int) $checklistData[LengowToolbox::CHECKLIST_SIMPLE_XML_ACTIVATED],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.json_php_message'),
                self::DATA_HELP => $this->locale->t('toolbox.screen.json_php_help'),
                self::DATA_HELP_LINK => $this->locale->t('toolbox.screen.json_php_help_link'),
                self::DATA_HELP_LABEL => $this->locale->t('toolbox.screen.json_php_help_label'),
                self::DATA_STATE => (int) $checklistData[LengowToolbox::CHECKLIST_JSON_ACTIVATED],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.shop_functionality_message'),
                self::DATA_HELP => $this->locale->t('toolbox.screen.shop_functionality_help'),
                self::DATA_STATE => (int) $this->isShopActivated(),
            ],
            [
                self::DATA_TITLE => $mailCheck[self::DATA_MESSAGE],
                self::DATA_STATE => $mailCheck[self::DATA_STATE],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.checksum_message'),
                self::DATA_HELP => $this->locale->t('toolbox.screen.checksum_help'),
                self::DATA_STATE => (int) $checklistData[LengowToolbox::CHECKLIST_MD5_SUCCESS],
            ],
        ];

        return $this->getContent($checklist);
    }

    /**
     * Get all global information for toolbox
     *
     * @return string
     */
    public function getGlobalInformation()
    {
        $pluginData = LengowToolbox::getData(LengowToolbox::DATA_TYPE_PLUGIN);
        $checklist = [
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.prestashop_version'),
                self::DATA_MESSAGE => $pluginData[LengowToolbox::PLUGIN_CMS_VERSION],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.plugin_version'),
                self::DATA_MESSAGE => $pluginData[LengowToolbox::PLUGIN_VERSION],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.php_version'),
                self::DATA_MESSAGE => $pluginData[LengowToolbox::PLUGIN_PHP_VERSION],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.ip_server'),
                self::DATA_MESSAGE => $pluginData[LengowToolbox::PLUGIN_SERVER_IP],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.authorized_ip_enable'),
                self::DATA_STATE => (int) $pluginData[LengowToolbox::PLUGIN_AUTHORIZED_IP_ENABLE],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.ip_authorized'),
                self::DATA_MESSAGE => implode(', ', $pluginData[LengowToolbox::PLUGIN_AUTHORIZED_IPS]),
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.debug_disabled'),
                self::DATA_STATE => (int) $pluginData[LengowToolbox::PLUGIN_DEBUG_MODE_DISABLE],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.write_permission'),
                self::DATA_STATE => (int) $pluginData[LengowToolbox::PLUGIN_WRITE_PERMISSION],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.toolbox_url'),
                self::DATA_MESSAGE => $pluginData[LengowToolbox::PLUGIN_TOOLBOX_URL],
            ],
        ];

        return $this->getContent($checklist);
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
            $lastImportDate = $this->locale->t('toolbox.screen.last_import_none');
            $lastImportType = $this->locale->t('toolbox.screen.last_import_none');
        } else {
            $lastImportDate = LengowMain::getDateInCorrectFormat($lastSynchronization, true);
            $lastSynchronizationType = $synchronizationData[LengowToolbox::SYNCHRONIZATION_LAST_SYNCHRONIZATION_TYPE];
            $lastImportType = $lastSynchronizationType === LengowImport::TYPE_CRON
                ? $this->locale->t('toolbox.screen.last_import_cron')
                : $this->locale->t('toolbox.screen.last_import_manual');
        }
        if ($synchronizationData[LengowToolbox::SYNCHRONIZATION_SYNCHRONIZATION_IN_PROGRESS]) {
            $importInProgress = LengowMain::decodeLogMessage(
                'toolbox.screen.rest_time_to_import',
                null,
                ['rest_time' => LengowImport::restTimeToImport()]
            );
        } else {
            $importInProgress = $this->locale->t('toolbox.screen.no_import');
        }
        $checklist = [
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.global_token'),
                self::DATA_MESSAGE => $synchronizationData[LengowToolbox::SYNCHRONIZATION_CMS_TOKEN],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.url_import'),
                self::DATA_MESSAGE => $synchronizationData[LengowToolbox::SYNCHRONIZATION_CRON_URL],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.nb_order_imported'),
                self::DATA_MESSAGE => $synchronizationData[LengowToolbox::SYNCHRONIZATION_NUMBER_ORDERS_IMPORTED],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.nb_order_to_be_sent'),
                self::DATA_MESSAGE => $synchronizationData[
                    LengowToolbox::SYNCHRONIZATION_NUMBER_ORDERS_WAITING_SHIPMENT
                ],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.nb_order_with_error'),
                self::DATA_MESSAGE => $synchronizationData[LengowToolbox::SYNCHRONIZATION_NUMBER_ORDERS_IN_ERROR],
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.import_in_progress'),
                self::DATA_MESSAGE => $importInProgress,
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.shop_last_import'),
                self::DATA_MESSAGE => $lastImportDate,
            ],
            [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.shop_type_import'),
                self::DATA_MESSAGE => $lastImportType,
            ],
        ];

        return $this->getContent($checklist);
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
                $lastExport = $this->locale->t('toolbox.screen.last_import_none');
            }
            $shopOptions = $data[LengowToolbox::SHOP_OPTIONS];
            $selectionEnabledKey = LengowConfiguration::$genericParamKeys[LengowConfiguration::SELECTION_ENABLED];
            $variationEnabledKey = LengowConfiguration::$genericParamKeys[LengowConfiguration::VARIATION_ENABLED];
            $outOfStockEnabledKey = LengowConfiguration::$genericParamKeys[LengowConfiguration::OUT_OF_STOCK_ENABLED];
            $inactiveEnabledKey = LengowConfiguration::$genericParamKeys[LengowConfiguration::INACTIVE_ENABLED];
            $checklist = [
                [
                    self::DATA_HEADER => $data[LengowToolbox::SHOP_NAME]
                        . ' (' . $data[LengowToolbox::SHOP_ID] . ')'
                        . ' - ' . $data[LengowToolbox::SHOP_DOMAIN_URL],
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.shop_active'),
                    self::DATA_STATE => (int) $data[LengowToolbox::SHOP_ENABLED],
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.shop_catalogs_id'),
                    self::DATA_MESSAGE => implode(', ', $data[LengowToolbox::SHOP_CATALOG_IDS]),
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.shop_product_total'),
                    self::DATA_MESSAGE => $data[LengowToolbox::SHOP_NUMBER_PRODUCTS_AVAILABLE],
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.shop_product_exported'),
                    self::DATA_MESSAGE => $data[LengowToolbox::SHOP_NUMBER_PRODUCTS_EXPORTED],
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.export_selection_enabled'),
                    self::DATA_STATE => (int) $shopOptions[$selectionEnabledKey],
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.export_variation_enabled'),
                    self::DATA_STATE => (int) $shopOptions[$variationEnabledKey],
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.export_out_stock_enabled'),
                    self::DATA_STATE => (int) $shopOptions[$outOfStockEnabledKey],
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.export_inactive_enabled'),
                    self::DATA_STATE => (int) $shopOptions[$inactiveEnabledKey],
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.shop_export_token'),
                    self::DATA_MESSAGE => $data[LengowToolbox::SHOP_TOKEN],
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.url_export'),
                    self::DATA_MESSAGE => $data[LengowToolbox::SHOP_FEED_URL],
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.shop_last_export'),
                    self::DATA_MESSAGE => $lastExport,
                ],
            ];
            $content .= $this->getContent($checklist);
        }

        return $content;
    }

    /**
     * Get all file information for toolbox
     *
     * @return string
     */
    public function getFileInformation()
    {
        $content = '';
        $exportData = LengowToolbox::getData(LengowToolbox::DATA_TYPE_SHOP);
        foreach ($exportData as $data) {
            $sep = DIRECTORY_SEPARATOR;
            $shopNameCleaned = LengowMain::getShopNameCleaned($data[LengowToolbox::SHOP_NAME]);
            $shopPath = LengowMain::FOLDER_EXPORT . $sep . $shopNameCleaned . $sep;
            $folderPath = LengowMain::getLengowFolder() . $sep . $shopPath;
            $folderUrl = LengowMain::getLengowBaseUrl() . $shopPath;
            $files = file_exists($folderPath) ? array_diff(scandir($folderPath), ['..', '.']) : [];
            $checklist = [
                [
                    self::DATA_HEADER => $data[LengowToolbox::SHOP_NAME]
                        . ' (' . $data[LengowToolbox::SHOP_ID] . ')'
                        . ' - ' . $data[LengowToolbox::SHOP_DOMAIN_URL],
                ],
                [
                    self::DATA_TITLE => $this->locale->t('toolbox.screen.folder_path'),
                    self::DATA_MESSAGE => $folderPath,
                ],
            ];
            if (!empty($files)) {
                $checklist[] = [self::DATA_SIMPLE => $this->locale->t('toolbox.screen.file_list')];
                foreach ($files as $file) {
                    $fileTimestamp = filectime($folderPath . $file);
                    $fileLink = '<a href="' . $folderUrl . $file . '" target="_blank">' . $file . '</a>';
                    $checklist[] = [
                        self::DATA_TITLE => $fileLink,
                        self::DATA_MESSAGE => LengowMain::getDateInCorrectFormat($fileTimestamp, true),
                    ];
                }
            } else {
                $checklist[] = [
                    self::DATA_SIMPLE => $this->locale->t('toolbox.screen.no_file_exported'),
                ];
            }
            $content .= $this->getContent($checklist);
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
        $checklist = [];
        $checksumData = LengowToolbox::getData(LengowToolbox::DATA_TYPE_CHECKSUM);
        $html = '<h3><i class="fa fa-commenting"></i> ' . $this->locale->t('toolbox.screen.summary') . '</h3>';
        if ($checksumData[LengowToolbox::CHECKSUM_AVAILABLE]) {
            $checklist[] = [
                self::DATA_TITLE => $this->locale->t(
                    'toolbox.screen.file_checked',
                    ['nb_file' => $checksumData[LengowToolbox::CHECKSUM_NUMBER_FILES_CHECKED]]
                ),
                self::DATA_STATE => 1,
            ];
            $checklist[] = [
                self::DATA_TITLE => $this->locale->t(
                    'toolbox.screen.file_modified',
                    ['nb_file' => $checksumData[LengowToolbox::CHECKSUM_NUMBER_FILES_MODIFIED]]
                ),
                self::DATA_STATE => (int) ($checksumData[LengowToolbox::CHECKSUM_NUMBER_FILES_MODIFIED] === 0),
            ];
            $checklist[] = [
                self::DATA_TITLE => $this->locale->t(
                    'toolbox.screen.file_deleted',
                    ['nb_file' => $checksumData[LengowToolbox::CHECKSUM_NUMBER_FILES_DELETED]]
                ),
                self::DATA_STATE => (int) ($checksumData[LengowToolbox::CHECKSUM_NUMBER_FILES_DELETED] === 0),
            ];
            $html .= $this->getContent($checklist);
            if (!empty($checksumData[LengowToolbox::CHECKSUM_FILE_MODIFIED])) {
                $fileModified = [];
                foreach ($checksumData[LengowToolbox::CHECKSUM_FILE_MODIFIED] as $file) {
                    $fileModified[] = [
                        self::DATA_TITLE => $file,
                        self::DATA_STATE => 0,
                    ];
                }
                $html .= '<h3><i class="fa fa-list"></i> '
                    . $this->locale->t('toolbox.screen.list_modified_file') . '</h3>';
                $html .= $this->getContent($fileModified);
            }
            if (!empty($checksumData[LengowToolbox::CHECKSUM_FILE_DELETED])) {
                $fileDeleted = [];
                foreach ($checksumData[LengowToolbox::CHECKSUM_FILE_DELETED] as $file) {
                    $fileDeleted[] = [
                        self::DATA_TITLE => $file,
                        self::DATA_STATE => 0,
                    ];
                }
                $html .= '<h3><i class="fa fa-list"></i> '
                    . $this->locale->t('toolbox.screen.list_deleted_file') . '</h3>';
                $html .= $this->getContent($fileDeleted);
            }
        } else {
            $checklist[] = [
                self::DATA_TITLE => $this->locale->t('toolbox.screen.file_not_exists'),
                self::DATA_STATE => 0,
            ];
            $html .= $this->getContent($checklist);
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
            return [
                self::DATA_MESSAGE => $this->locale->t('toolbox.screen.mail_configuration_enabled'),
                self::DATA_STATE => 0,
            ];
        }
        if ($mailMethod === 3) {
            return [
                self::DATA_MESSAGE => $this->locale->t('toolbox.screen.email_disable'),
                self::DATA_STATE => 0,
            ];
        }

        return [
            self::DATA_MESSAGE => $this->locale->t('toolbox.screen.email_using_php_mail'),
            self::DATA_STATE => 1,
        ];
    }

    /**
     * Check if shop functionality are enabled
     *
     * @return bool
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
    private function getContent($checklist = [])
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
            } elseif (isset($check[self::DATA_SIMPLE])) {
                $out .= '<td colspan="2" align="center"><h5>' . $check[self::DATA_SIMPLE] . '</h5></td>';
            } else {
                $out .= '<td><b>' . $check[self::DATA_TITLE] . '</b></td>';
                if (isset($check[self::DATA_STATE])) {
                    if ($check[self::DATA_STATE] === 1) {
                        $out .= '<td align="right"><i class="fa fa-check lgw-check-green"></i></td>';
                    } else {
                        $out .= '<td align="right"><i class="fa fa-times lgw-check-red"></i></td>';
                    }
                    if (($check[self::DATA_STATE] === 0) && isset($check[self::DATA_HELP])) {
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
