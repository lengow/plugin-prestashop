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
    public static function getFormCron()
    {
        if (Module::getInstanceByName('cronjobs')) {
            $form = '<p>You can use the Crontab Module to import orders from Lengow</p>';

            if (!self::getCron()) {
                $form .= '<span class="lengow-no">Cron Import is not configured on your Prestashop</span>';
            } else {
                $form .= '<span class="lengow-yes">Cron Import exists on your Prestashop</span>';
            }
            $form .= '<p> - or - </p>';
        } else {
            $form = '<p>You can install "Crontab" Prestashop Plugin</p>';
            $form .= '<p> - or - </p>';
        }
        $form .= '<p>If you are using an unix system, you can use unix crontab like this :</p>';
        $form .= '<strong><code>*/15 * * * * wget url_feed_import</code></strong><br /><br />';

        return $form;
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
     * Add cron tasks to cronjobs table
     *
     * @param integer $id_shop shop id
     *
     * @return boolean
     */
    public static function addCronTasks()
    {
        if (!Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'cronjobs\'')) {
            return;
        }
        $shops = LengowShop::findAll(true);
        foreach ($shops as $s) {
            $id_shop = $s['id_shop'];
            break;
        }
        $shop = new Shop((int)$id_shop);
        $description_import = 'Lengow Import - ' . $shop->name;

        $query_import_select = 'SELECT 1 FROM ' . pSQL(_DB_PREFIX_ . 'cronjobs') . ' '
            . 'WHERE `description` = \'' . pSQL($description_import) . '\' '
            . 'AND `id_shop` = ' . (int)$id_shop . ' '
            . 'AND `id_shop_group` =' . (int)$shop->id_shop_group;

        $query_import_insert = 'INSERT INTO ' . pSQL(_DB_PREFIX_ . 'cronjobs') . ' '
            . '(`description`, `task`, `hour`, `day`, `month`, `day_of_week`,
            `updated_at`, `active`, `id_shop`, `id_shop_group`) '
            . 'VALUES (\''
            . pSQL($description_import)
            . '\', \''
            . pSQL(LengowMain::getImportUrl())
            . '\', \'-1\', \'-1\', \'-1\', \'-1\', NULL, TRUE, '
            . (int)$id_shop
            . ', '
            . (int)$shop->id_shop_group
            . ')';

        $result = array();
        if (!Db::getInstance()->executeS($query_import_select)) {
            $add_import = Db::getInstance()->execute($query_import_insert);
        }

        if (isset($add_import)) {
            if ($add_import) {
                $result['success'][] = LengowMain::log(
                    'Cron',
                    LengowMain::setLogMessage('Lengow import cron task sucessfully created.')
                );
            } else {
                $result['error'][] = LengowMain::log(
                    'Cron',
                    LengowMain::setLogMessage('Lengow import cron task could not be created.')
                );
            }
        }

        return $result;
    }

    /**
     * Remove cron tasks from cronjobs table
     *
     * @param integer $id_shop shop id
     *
     * @return boolean
     */
    public static function removeCronTasks()
    {
        if (!Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.'cronjobs\'')) {
            return;
        }
        $shops = LengowShop::findAll(true);
        foreach ($shops as $s) {
            $id_shop = $s['id_shop'];
            break;
        }
        $shop = new Shop((int)$id_shop);
        $description_import = 'Lengow Import - ' . $shop->name;

        $query_import_select = 'SELECT 1 FROM ' . pSQL(_DB_PREFIX_ . 'cronjobs') . ' '
            . 'WHERE `description` = \'' . pSQL($description_import) . '\' '
            . 'AND `id_shop` = ' . (int)$id_shop . ' '
            . 'AND `id_shop_group` =' . (int)$shop->id_shop_group;

        $result = array();
        if (Db::getInstance()->executeS($query_import_select)) {
            $query = 'DELETE FROM ' . pSQL(_DB_PREFIX_ . 'cronjobs') . ' '
                . 'WHERE `description` IN (\'' . pSQL($description_import) . '\')'
                . 'AND `id_shop` = ' . (int)$id_shop . ' '
                . 'AND `id_shop_group` =' . (int)$shop->id_shop_group;
            if (Db::getInstance()->execute($query)) {
                $result['success'] = LengowMain::log(
                    'Cron',
                    LengowMain::setLogMessage('Cron tasks sucessfully removed.')
                );
            } else {
                $result['error'] = LengowMain::log(
                    'Cron',
                    LengowMain::setLogMessage('Import and/or export cron task(s) could not be removed.')
                );
            }
        }
        return $result;
    }

}