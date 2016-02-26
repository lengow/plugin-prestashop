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
    * Check API Authentification
    *
    * @param integer Shop ID
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
     * Get admin table html
     *
     * @return string Html table
     */
    public static function getHtmlCheckList()
    {
        $out = '';
        $out .= self::getAdminHeader();
        $out .= self::getAdminContent(self::getCheckListArray());
        $out .= self::getAdminFooter();
        return $out;
    }

    /**
     * Get header table
     *
     * @return string
     */
    private static function getAdminHeader()
    {
        return '<table class="table" cellpadding="0" cellspacing="0">';
    }

    /**
     * Get HTML Table content of checklist
     *
     * @param array $checklist
     */
    private static function getAdminContent($checklist = array())
    {
        if (empty($checklist)) {
            return null;
        }
        $out = '';
        foreach ($checklist as $check) {
            $out .= '<tr>';
            $out .= '<td><b>'.$check['message'].'</b></td>';
            if ($check['state'] == 1) {
                $out .= '<td><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'/img/admin/enabled.gif" alt="ok"></td>';
            } elseif ($check['state'] == 2) {
                $out .= '<td><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'/img/admin/error.png" alt="warning"></td>';
            } else {
                $out .= '<td><img src="'._PS_BASE_URL_.__PS_BASE_URI__.'/img/admin/disabled.gif" alt="not ok"></td>';
            }
            $out .= '</tr>';
            if ($check['state'] === 0 || $check['state'] === 2) {
                $out .= '<tr><td colspan="2"><p>' . $check['help'];
                if (array_key_exists('help_link', $check) && $check['help_link'] != '') {
                    $out .= '<br /><a target="_blank" href="'.$check['help_link'].'">'.$check['help_label'].'</a>';
                }
                $out .= '</p></td></tr>';
            }
        }
        return $out;
    }

    /**
     * Get footer table
     *
     * @return string
     */
    private static function getAdminFooter()
    {
        return '</table>';
    }

    /**
     * Get mail configuration informations
     *
     * @return string
     */
    public static function getMailConfiguration()
    {
        $locale = new LengowTranslation();
        $mail_method = Configuration::get('PS_MAIL_METHOD');
        if ($mail_method == 2) {
            return array('message' => $locale->t('toolbox.index.mail_configuration_enabled'), 'state' => false);
        } elseif ($mail_method == 3 && _PS_VERSION_ >= '1.5.0') {
            return array('message' => $locale->t('toolbox.index.email_desactived'), 'state' => false);
        } elseif ($mail_method == 3) {
            return array('message' => $locale->t('toolbox.index.error_mail_setting'), 'state' => false
            );
        } else {
            return array('message' => $locale->t('toolbox.index.email_using_php_mail'), 'state' => true);
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
     * Get array of requirements and their status
     *
     * @return array
     */
    private static function getCheckListArray()
    {
        $locale = new LengowTranslation();
        $checklist = array();
        $checklist[] = array(
            'message' => $locale->t('toolbox.index.curl_message'),
            'help' => $locale->t('toolbox.index.curl_help'),
            'help_link' => $locale->t('toolbox.index.curl_help_link'),
            'help_label' => $locale->t('toolbox.index.curl_help_label'),
            'state' => (int)self::isCurlActivated()
        );
        $checklist[] = array(
            'message' => $locale->t('toolbox.index.simple_xml_message'),
            'help' => $locale->t('toolbox.index.simple_xml_help'),
            'help_link' => $locale->t('toolbox.index.simple_xml_help_link'),
            'help_label' => $locale->t('toolbox.index.simple_xml_help_label'),
            'state' => (int)self::isSimpleXMLActivated()
        );
        $checklist[] = array(
            'message' => $locale->t('toolbox.index.json_php_message'),
            'help' => $locale->t('toolbox.index.json_php_help'),
            'help_link' => $locale->t('toolbox.index.json_php_help_link'),
            'help_label' => $locale->t('toolbox.index.json_php_help_label'),
            'state' => (int)self::isJsonActivated()
        );
        $checklist[] = array(
            'message' => $locale->t('toolbox.index.shop_functionality_message'),
            'help' => $locale->t('toolbox.index.shop_functionality_help'),
            'state' => (int)self::isShopActivated()
        );
        $mail_check = self::getMailConfiguration();
        $checklist[] = array(
            'message' => $mail_check['message'],
            'state' => (int)$mail_check['state']
        );
        return $checklist;
    }
}
