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
 * Lengow Check Class
 */
class LengowCheck
{
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
        $checklist = array();
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.curl_message'),
            'help' => $this->locale->t('toolbox.index.curl_help'),
            'help_link' => $this->locale->t('toolbox.index.curl_help_link'),
            'help_label' => $this->locale->t('toolbox.index.curl_help_label'),
            'state' => (int)self::isCurlActivated(),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.simple_xml_message'),
            'help' => $this->locale->t('toolbox.index.simple_xml_help'),
            'help_link' => $this->locale->t('toolbox.index.simple_xml_help_link'),
            'help_label' => $this->locale->t('toolbox.index.simple_xml_help_label'),
            'state' => (int)self::isSimpleXMLActivated(),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.json_php_message'),
            'help' => $this->locale->t('toolbox.index.json_php_help'),
            'help_link' => $this->locale->t('toolbox.index.json_php_help_link'),
            'help_label' => $this->locale->t('toolbox.index.json_php_help_label'),
            'state' => (int)self::isJsonActivated(),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.shop_functionality_message'),
            'help' => $this->locale->t('toolbox.index.shop_functionality_help'),
            'state' => (int)self::isShopActivated(),
        );
        $mailCheck = $this->getMailConfiguration();
        $checklist[] = array(
            'title' => $mailCheck['message'],
            'state' => $mailCheck['state'],
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.checksum_message'),
            'help' => $this->locale->t('toolbox.index.checksum_help'),
            'help_link' => __PS_BASE_URI__ . 'modules/lengow/toolbox/checksum.php',
            'help_label' => $this->locale->t('toolbox.index.checksum_help_label'),
            'state' => (int)self::getFileModified(),
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
        $checklist = array();
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.prestashop_version'),
            'message' => _PS_VERSION_,
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.plugin_version'),
            'message' => LengowConfiguration::getGlobalValue('LENGOW_VERSION'),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.ip_server'),
            'message' => $_SERVER['SERVER_ADDR'],
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.ip_authorized'),
            'message' => LengowConfiguration::get('LENGOW_AUTHORIZED_IP'),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.debug_disabled'),
            'state' => LengowConfiguration::debugModeIsActive() ? 0 : 1,
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
        $lastImport = LengowMain::getLastImport();
        $lastImportDate = $lastImport['timestamp'] === 'none'
            ? $this->locale->t('toolbox.index.last_import_none')
            : LengowMain::getDateInCorrectFormat($lastImport['timestamp'], true);
        if ($lastImport['type'] === 'none') {
            $lastImportType = $this->locale->t('toolbox.index.last_import_none');
        } elseif ($lastImport['type'] === LengowImport::TYPE_CRON) {
            $lastImportType = $this->locale->t('toolbox.index.last_import_cron');
        } else {
            $lastImportType = $this->locale->t('toolbox.index.last_import_manual');
        }
        if (LengowImport::isInProcess()) {
            $importInProgress = LengowMain::decodeLogMessage(
                'toolbox.index.rest_time_to_import',
                null,
                array('rest_time' => LengowImport::restTimeToImport())
            );
        } else {
            $importInProgress = $this->locale->t('toolbox.index.no_import');
        }
        $checklist = array();
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.global_token'),
            'message' => LengowConfiguration::get('LENGOW_GLOBAL_TOKEN'),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.url_import'),
            'message' => LengowMain::getImportUrl(),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.import_in_progress'),
            'message' => $importInProgress,
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.shop_last_import'),
            'message' => $lastImportDate,
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.shop_type_import'),
            'message' => $lastImportType,
        );
        return $this->getAdminContent($checklist);
    }


    /**
     * Get all shop information for toolbox
     *
     * @param LengowShop $shop Lengow shop instance
     *
     * @return string
     */
    public function getInformationByStore($shop)
    {
        $lengowExport = new LengowExport(array('shop_id' => $shop->id));
        $lastExportDate = LengowConfiguration::get('LENGOW_LAST_EXPORT', null, null, $shop->id);
        if ($lastExportDate !== null && $lastExportDate !== '') {
            $lastExport = LengowMain::getDateInCorrectFormat(strtotime($lastExportDate), true);
        } else {
            $lastExport = $this->locale->t('toolbox.index.last_import_none');
        }
        $checklist = array();
        $checklist[] = array(
            'header' => $shop->name . ' (' . $shop->id . ')' . ' - http://' . $shop->domain,
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.shop_active'),
            'state' => (int)LengowConfiguration::shopIsActive($shop->id),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.shop_catalogs_id'),
            'message' => LengowConfiguration::get('LENGOW_CATALOG_ID', null, null, $shop->id),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.shop_product_total'),
            'message' => $lengowExport->getTotalProduct(),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.shop_product_exported'),
            'message' => $lengowExport->getTotalExportProduct(),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.export_variation_enabled'),
            'state' => (int)LengowConfiguration::get('LENGOW_EXPORT_VARIATION_ENABLED', null, null, $shop->id),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.export_out_stock_enabled'),
            'state' => (int)LengowConfiguration::get('LENGOW_EXPORT_OUT_STOCK', null, null, $shop->id),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.export_selection_enabled'),
            'state' => (int)LengowConfiguration::get('LENGOW_EXPORT_SELECTION_ENABLED', null, null, $shop->id),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.shop_export_token'),
            'message' => LengowConfiguration::get('LENGOW_SHOP_TOKEN', null, null, $shop->id),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.url_export'),
            'message' => LengowMain::getExportUrl($shop->id),
        );
        $checklist[] = array(
            'title' => $this->locale->t('toolbox.index.shop_last_export'),
            'message' => $lastExport,
        );
        return $this->getAdminContent($checklist);
    }

    /**
     * Get files checksum informations
     *
     * @return string
     */
    public function checkFileMd5()
    {
        $checklist = array();
        $fileName = _PS_MODULE_DIR_ . 'lengow' . DIRECTORY_SEPARATOR . 'toolbox' . DIRECTORY_SEPARATOR . 'checkmd5.csv';
        $html = '<h3><i class="fa fa-commenting"></i> ' . $this->locale->t('toolbox.checksum.summary') . '</h3>';
        $fileCounter = 0;
        if (file_exists($fileName)) {
            $fileErrors = array();
            $fileDeletes = array();
            if (($file = fopen($fileName, 'r')) !== false) {
                while (($data = fgetcsv($file, 1000, '|')) !== false) {
                    $fileCounter++;
                    $filePath = _PS_MODULE_DIR_ . 'lengow' . $data[0];
                    if (file_exists($filePath)) {
                        $fileMd = md5_file($filePath);
                        if ($fileMd !== $data[1]) {
                            $fileErrors[] = array(
                                'title' => $filePath,
                                'state' => 0,
                            );
                        }
                    } else {
                        $fileDeletes[] = array(
                            'title' => $filePath,
                            'state' => 0,
                        );
                    }
                }
                fclose($file);
            }
            $checklist[] = array(
                'title' => $this->locale->t('toolbox.checksum.file_checked', array('nb_file' => $fileCounter)),
                'state' => 1,
            );
            $checklist[] = array(
                'title' => $this->locale->t('toolbox.checksum.file_modified', array('nb_file' => count($fileErrors))),
                'state' => !empty($fileErrors) ? 0 : 1,
            );
            $checklist[] = array(
                'title' => $this->locale->t('toolbox.checksum.file_deleted', array('nb_file' => count($fileDeletes))),
                'state' => !empty($fileDeletes) ? 0 : 1,
            );
            $html .= $this->getAdminContent($checklist);
            if (!empty($fileErrors)) {
                $html .= '<h3><i class="fa fa-list"></i> '
                    . $this->locale->t('toolbox.checksum.list_modified_file') . '</h3>';
                $html .= $this->getAdminContent($fileErrors);
            }
            if (!empty($fileDeletes)) {
                $html .= '<h3><i class="fa fa-list"></i> '
                    . $this->locale->t('toolbox.checksum.list_deleted_file') . '</h3>';
                $html .= $this->getAdminContent($fileDeletes);
            }
        } else {
            $checklist[] = array(
                'title' => $this->locale->t('toolbox.checksum.file_not_exists'),
                'state' => 0,
            );
            $html .= $this->getAdminContent($checklist);
        }
        return $html;
    }

    /**
     * Get checksum errors
     *
     * @return boolean
     */
    public static function getFileModified()
    {
        $fileName = _PS_MODULE_DIR_ . 'lengow' . DIRECTORY_SEPARATOR . 'toolbox' . DIRECTORY_SEPARATOR . 'checkmd5.csv';
        if (file_exists($fileName)) {
            if (($file = fopen($fileName, 'r')) !== false) {
                while (($data = fgetcsv($file, 1000, '|')) !== false) {
                    $filePath = _PS_MODULE_DIR_ . 'lengow' . $data[0];
                    $fileMd = md5_file($filePath);
                    if ($fileMd !== $data[1]) {
                        return false;
                    }
                }
                fclose($file);
                return true;
            }
        }
        return false;
    }

    /**
     * Get mail configuration informations
     *
     * @return array
     */
    public function getMailConfiguration()
    {
        $mailMethod = (int)Configuration::get('PS_MAIL_METHOD');
        if ($mailMethod === 2) {
            return array(
                'message' => $this->locale->t('toolbox.index.mail_configuration_enabled'),
                'state' => 0,
            );
        } elseif ($mailMethod === 3 && _PS_VERSION_ >= '1.5.0') {
            return array(
                'message' => $this->locale->t('toolbox.index.email_desactived'),
                'state' => 0,
            );
        } elseif ($mailMethod === 3) {
            return array(
                'message' => $this->locale->t('toolbox.index.error_mail_setting'),
                'state' => 0,
            );
        } else {
            return array(
                'message' => $this->locale->t('toolbox.index.email_using_php_mail'),
                'state' => 1,
            );
        }
    }

    /**
     * Check if PHP Curl is activated
     *
     * @return boolean
     */
    public static function isCurlActivated()
    {
        return function_exists('curl_version');
    }

    /**
     * Check if SimpleXML Extension is activated
     *
     * @return boolean
     */
    public static function isSimpleXMLActivated()
    {
        return function_exists('simplexml_load_file');
    }

    /**
     * Check if SimpleXML Extension is activated
     *
     * @return boolean
     */
    public static function isJsonActivated()
    {
        return function_exists('json_decode');
    }

    /**
     * Check if shop functionality are enabled
     *
     * @return boolean
     */
    public static function isShopActivated()
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
            if (isset($check['header'])) {
                $out .= '<td colspan="2" align="center" style="border:0"><h4>' . $check['header'] . '</h4></td>';
            } else {
                $out .= '<td><b>' . $check['title'] . '</b></td>';
                if (isset($check['state'])) {
                    if ($check['state'] === 1) {
                        $out .= '<td align="right"><i class="fa fa-check lengow-green"></i></td>';
                    } else {
                        $out .= '<td align="right"><i class="fa fa-times lengow-red"></i></td>';
                    }
                    if ($check['state'] === 0) {
                        if (isset($check['help']) && isset($check['help_link']) && isset($check['help_label'])) {
                            $out .= '<tr><td colspan="2"><p>' . $check['help'];
                            if (array_key_exists('help_link', $check) && $check['help_link'] !== '') {
                                $out .= '<br /><a target="_blank" href="'
                                    . $check['help_link'] . '">' . $check['help_label'] . '</a>';
                            }
                            $out .= '</p></td></tr>';
                        }
                    }
                } else {
                    $out .= '<td align="right"><b>' . $check['message'] . '</b></td>';
                }
            }
            $out .= '</tr>';
        }
        $out .= '</table>';
        return $out;
    }
}
