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

/**
* The Lengow Cron Class.
*
*/
class LengowCron
{
    /**
     * Get select cron.
     *
     * @return string The select html
     */
    private function getFormCron()
    {
        $links = LengowCore::getWebservicesLinks();
        if (Module::getInstanceByName('cron')) {
            $form = '<p>'.$this->l('You can use the Crontab Module to import orders from Lengow').'</p>';
            $cron_value = Configuration::get('LENGOW_CRON');
            $form .= '<select id="cron-delay" name="cron-delay">';
            $form .= '<option value="NULL">'.$this->l('No cron configured').'</option>';
            foreach (self::$_CRON_SELECT as $value) {
                $form .= '<option value="' . $value . '"' . ($cron_value == $value ? ' selected="selected"' : '') . '>' . $value . ' ' . $this->l('min') . '</option>';
            }
            $form .= '</select>';
            if (!self::getCron()) {
                $form .= '<span class="lengow-no">' . $this->l('Cron Import is not configured on your Prestashop') . '</span>';
            } else {
                $form .= '<span class="lengow-yes">' . $this->l('Cron Import exists on your Prestashop') . '</span>';
            }
            $form .= '<p> - '.$this->l('or').' - </p>';
        } else {
            $form = '<p>'.$this->l('You can install "Crontab" Prestashop Plugin').'</p>';
            $form .= '<p> - '.$this->l('or').' - </p>';
        }
        $form .= '<p>'.$this->l('If you are using an unix system, you can use unix crontab like this :').'</p>';
        $form .= '<strong><code>*/15 * * * * wget '.$links['url_feed_import'].'</code></strong><br /><br />';
        return '<div class="lengow-margin">'.$form.'</div>';
    }

    /**
     * Update Cron with module Crontab
     *
     * @param varchar The delay in minutes
     *
     * @return boolean
     */
    public static function updateCron($delay)
    {
        $module_cron = Module::getInstanceByName('cron');
        $module_lengow = Module::getInstanceByName('lengow');
        if (Validate::isLoadedObject($module_cron)) {
            if ($delay > 1 && $delay < 60) {
                $delays = '';
                for ($i = 0; $i < 60; $i = $i + $delay) {
                    $delays .= $i . ',';
                }
                $delays = rtrim($delays, ',');
                $module_cron->deleteCron($module_lengow->id, 'cronImport');
                $module_cron->addCron($module_lengow->id, 'cronImport', $delays.' * * * *');
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * Get the cron of import orders from Lengow
     *
     * @return boolean Result of add tab on database.
     */
    public static function getCron()
    {
        $module_cron = Module::getInstanceByName('cron');
        $module_lengow = Module::getInstanceByName('lengow');
        if (Validate::isLoadedObject($module_cron) && $module_cron->cronExists($module_lengow->id, 'cronImport') != false) {
            return true;
        }
        return false;
    }

    /**
     * Get the cron of import orders from Lengow
     *
     * @return boolean Result of add tab on database.
     */
    public function cronImport()
    {
        @set_time_limit(0);
        $import = new LengowImport();
        $import->force_log_output = false;
        $date_to = date('Y-m-d');
        $days = (integer)LengowCore::getCountDaysToImport();
        $date_from = date('Y-m-d', strtotime(date('Y-m-d').' -'.$days.'days'));
        LengowCore::log('Cron import', null, -1);
        $result = $import->exec(
            'commands',
            array(
                'dateFrom' => $date_from,
                'dateTo' => $date_to)
        );
        return $result;
    }

}