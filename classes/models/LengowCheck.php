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

class LengowCheck
{
    /**
     * @var $locale for translation
     */
    protected $locale;

    public function __construct()
    {
        $this->locale = new LengowTranslation();
    }

    /**
    * Check API Authentification
    *
    * @param integer $id_shop Shop ID
    *
    * @return boolean
    */
    public static function isValidAuth($id_shop = null)
    {
        if (LengowMain::inTest()) {
            return true;
        }
        if (!self::isCurlActivated()) {
            return false;
        }
        $account_id = (integer)LengowMain::getIdAccount($id_shop);
        $connector  = new LengowConnector(
            LengowMain::getAccessToken($id_shop),
            LengowMain::getSecretCustomer($id_shop)
        );
        $result = $connector->connect();
        if (isset($result['token']) && $account_id != 0 && is_integer($account_id)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get array of requirements and their status
     *
     * @return mixed
     */
    public function getCheckList()
    {
        $checklist = array();
        $checklist[] = array(
            'title'         => $this->locale->t('toolbox.index.curl_message'),
            'help'          => $this->locale->t('toolbox.index.curl_help'),
            'help_link'     => $this->locale->t('toolbox.index.curl_help_link'),
            'help_label'    => $this->locale->t('toolbox.index.curl_help_label'),
            'state'         => (int)self::isCurlActivated()
        );
        $checklist[] = array(
            'title'         => $this->locale->t('toolbox.index.simple_xml_message'),
            'help'          => $this->locale->t('toolbox.index.simple_xml_help'),
            'help_link'     => $this->locale->t('toolbox.index.simple_xml_help_link'),
            'help_label'    => $this->locale->t('toolbox.index.simple_xml_help_label'),
            'state'         => (int)self::isSimpleXMLActivated()
        );
        $checklist[] = array(
            'title'         => $this->locale->t('toolbox.index.json_php_message'),
            'help'          => $this->locale->t('toolbox.index.json_php_help'),
            'help_link'     => $this->locale->t('toolbox.index.json_php_help_link'),
            'help_label'    => $this->locale->t('toolbox.index.json_php_help_label'),
            'state'         => (int)self::isJsonActivated()
        );
        $checklist[] = array(
            'title'         => $this->locale->t('toolbox.index.shop_functionality_message'),
            'help'          => $this->locale->t('toolbox.index.shop_functionality_help'),
            'state'         => (int)self::isShopActivated()
        );
        $mail_check = $this->getMailConfiguration();
        $checklist[] = array(
            'title'         => $mail_check['message'],
            'state'         => $mail_check['state']
        );
        $checklist[] = array(
            'title'         => $this->locale->t('toolbox.index.checksum_message'),
            'help'          => $this->locale->t('toolbox.index.checksum_help'),
            'help_link'     => '/modules/lengow/toolbox/checksum.php',
            'help_label'    => $this->locale->t('toolbox.index.checksum_help_label'),
            'state'         => (int)self::getFileModified()
        );
        return $this->getAdminContent($checklist);
    }

    /**
     * Get array of requirements and their status
     *
     * @return mixed
     */
    public function getGlobalInformation()
    {
        $checklist = array();
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.prestashop_version'),
            'message'   => _PS_VERSION_
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.plugin_version'),
            'message'   => LengowConfiguration::getGlobalValue('LENGOW_VERSION')
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.ip_server'),
            'message'   => $_SERVER['SERVER_ADDR']
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.ip_authorized'),
            'message'   => LengowConfiguration::get('LENGOW_AUTHORIZED_IP')
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.preprod_disabled'),
            'state'     => (LengowConfiguration::get('LENGOW_IMPORT_PREPROD_ENABLED') ? 0 : 1)
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.cron_enable'),
            'state'   => (int)LengowCron::getCron()
        );
        return $this->getAdminContent($checklist);
    }

     /**
     * Get array of requirements and their status
     *
     * @return mixed
     */
    public function getImportInformation()
    {
        $last_import = LengowMain::getLastImport();
        $last_import_date = (
            $last_import['timestamp'] == 'none'
                ? $this->locale->t('toolbox.index.last_import_none')
                : date('Y-m-d H:i:s', $last_import['timestamp'])
        );
        if ($last_import['type'] == 'none') {
            $last_import_type = $this->locale->t('toolbox.index.last_import_none');
        } elseif ($last_import['type'] == 'cron') {
            $last_import_type = $this->locale->t('toolbox.index.last_import_cron');
        } else {
            $last_import_type = $this->locale->t('toolbox.index.last_import_manual');
        }
        if (LengowImport::isInProcess()) {
            $import_in_progress = LengowMain::decodeLogMessage('toolbox.index.rest_time_to_import', null, array(
                'rest_time' => LengowImport::restTimeToImport()
            ));
        } else {
            $import_in_progress = $this->locale->t('toolbox.index.no_import');
        }
        $checklist = array();
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.global_token'),
            'message'   => LengowConfiguration::get('LENGOW_GLOBAL_TOKEN')
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.url_import'),
            'message'   => LengowMain::getImportUrl()
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.import_in_progress'),
            'message'   => $import_in_progress
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.shop_last_import'),
            'message'   => $last_import_date
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.shop_type_import'),
            'message'   => $last_import_type
        );
        return $this->getAdminContent($checklist);
    }


    /**
     * Get array of requirements and their status
     *
     * @param LengowShop $shop
     *
     * @return mixed
     */
    public function getInformationByStore($shop)
    {
        $lengowExport = new LengowExport(array("shop_id" => $shop->id));
        if (!is_null(LengowConfiguration::get('LENGOW_LAST_EXPORT', null, null, $shop->id))
            && LengowConfiguration::get('LENGOW_LAST_EXPORT', null, null, $shop->id) != ''
        ) {
            $last_export = LengowConfiguration::get('LENGOW_LAST_EXPORT', null, null, $shop->id);
        } else {
            $last_export = $this->locale->t('toolbox.index.last_import_none');
        }
        $checklist = array();
        $checklist[] = array(
            'header'     => $shop->name.' ('.$shop->id.')'.' - http://'.$shop->domain
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.shop_active'),
            'state'     => (int)LengowConfiguration::get('LENGOW_SHOP_ACTIVE', null, null, $shop->id)
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.shop_product_total'),
            'message'   => $lengowExport->getTotalProduct()
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.shop_product_exported'),
            'message'   => $lengowExport->getTotalExportProduct()
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.shop_export_token'),
            'message'   => LengowConfiguration::get('LENGOW_SHOP_TOKEN', null, null, $shop->id)
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.url_export'),
            'message'   => LengowMain::getExportUrl($shop->id)
        );
        $checklist[] = array(
            'title'     => $this->locale->t('toolbox.index.shop_last_export'),
            'message'   => $last_export
        );
        return $this->getAdminContent($checklist);
    }

    /**
     * Get files checksum
     *
     * @return mixed
     */
    public function checkFileMd5()
    {
        $checklist = array();
        $file_name = _PS_MODULE_DIR_.'lengow'.DIRECTORY_SEPARATOR.'toolbox'.DIRECTORY_SEPARATOR.'checkmd5.csv';
        $html = '<h3><i class="fa fa-commenting"></i> '.$this->locale->t('toolbox.checksum.summary').'</h3>';
        $file_counter = 0;
        if (file_exists($file_name)) {
            $file_errors = array();
            $file_deletes = array();
            if (($file = fopen($file_name, "r")) !== false) {
                while (($data = fgetcsv($file, 1000, "|")) !== false) {
                    $file_counter++;
                    $file_path = _PS_MODULE_DIR_.'lengow'.$data[0];
                    if (file_exists($file_path)) {
                        $file_md5 = md5_file($file_path);
                        if ($file_md5 !== $data[1]) {
                            $file_errors[] = array(
                                'title' => $file_path,
                                'state' => 0
                            );
                        }
                    } else {
                        $file_deletes[] = array(
                            'title' => $file_path,
                            'state' => 0
                        );
                    }
                }
                fclose($file);
            }
            $checklist[] = array(
                'title' => $this->locale->t('toolbox.checksum.file_checked', array(
                    'nb_file' => $file_counter
                )),
                'state' => 1
            );
            $checklist[] = array(
                'title' => $this->locale->t('toolbox.checksum.file_modified', array(
                    'nb_file' => count($file_errors)
                )),
                'state' => (count($file_errors) > 0 ? 0 : 1)
            );
            $checklist[] = array(
                'title' => $this->locale->t('toolbox.checksum.file_deleted', array(
                    'nb_file' => count($file_deletes)
                )),
                'state' => (count($file_deletes) > 0 ? 0 : 1)
            );
            $html.= $this->getAdminContent($checklist);
            if (count($file_errors) > 0) {
                $html.= '<h3><i class="fa fa-list"></i> '
                    .$this->locale->t('toolbox.checksum.list_modified_file').'</h3>';
                $html.= $this->getAdminContent($file_errors);
            }
            if (count($file_deletes) > 0) {
                $html.= '<h3><i class="fa fa-list"></i> '
                    .$this->locale->t('toolbox.checksum.list_deleted_file').'</h3>';
                $html.= $this->getAdminContent($file_deletes);
            }
        } else {
            $checklist[] = array(
                'title' => $this->locale->t('toolbox.checksum.file_not_exists'),
                'state' => 0
            );
            $html.= $this->getAdminContent($checklist);
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
        $file_name = _PS_MODULE_DIR_.'lengow'.DIRECTORY_SEPARATOR.'toolbox'.DIRECTORY_SEPARATOR.'checkmd5.csv';
        if (file_exists($file_name)) {
            if (($file = fopen($file_name, "r")) !== false) {
                while (($data = fgetcsv($file, 1000, "|")) !== false) {
                    $file_path = _PS_MODULE_DIR_.'lengow'.$data[0];
                    $file_md5 = md5_file($file_path);
                    if ($file_md5 !== $data[1]) {
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
     * @return string
     */
    public function getMailConfiguration()
    {
        $mail_method = Configuration::get('PS_MAIL_METHOD');
        if ($mail_method == 2) {
            return array('message' => $this->locale->t('toolbox.index.mail_configuration_enabled'), 'state' => false);
        } elseif ($mail_method == 3 && _PS_VERSION_ >= '1.5.0') {
            return array('message' => $this->locale->t('toolbox.index.email_desactived'), 'state' => false);
        } elseif ($mail_method == 3) {
            return array('message' => $this->locale->t('toolbox.index.error_mail_setting'), 'state' => false);
        } else {
            return array('message' => $this->locale->t('toolbox.index.email_using_php_mail'), 'state' => true);
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
     * @param array $checklist
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
                $out .= '<td colspan="2" align="center" style="border:0"><h4>'.$check['header'].'</h4></td>';
            } else {
                $out .= '<td><b>'.$check['title'].'</b></td>';
                if (isset($check['state'])) {
                    if ($check['state'] == 1) {
                        $out .= '<td align="right"><i class="fa fa-check lengow-green"></i></td>';
                    } else {
                        $out .= '<td align="right"><i class="fa fa-times lengow-red"></i></i></td>';
                    }
                    if ($check['state'] === 0) {
                        if (isset($check['help']) && isset($check['help_link']) && isset($check['help_label'])) {
                            $out .= '<tr><td colspan="2"><p>' . $check['help'];
                            if (array_key_exists('help_link', $check) && $check['help_link'] != '') {
                                $out .= '<br /><a target="_blank" href="'
                                .$check['help_link'].'">'.$check['help_label'].'</a>';
                            }
                            $out .= '</p></td></tr>';
                        }
                    }
                } else {
                    $out .= '<td align="right"><b>'.$check['message'].'</b></td>';
                }
            }
            $out .= '</tr>';
        }
        $out .= '</table>';
        return $out;
    }
}
