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

require 'conf.inc.php';
require 'views/header.php';
?>
    <div class="container">
        <h1><?php echo $locale->t('toolbox.menu.lengow_toolbox'); ?></h1>

        <h2><?php echo $locale->t('toolbox.index.checklist_information'); ?></h2>
        <?php echo LengowCheck::getHtmlCheckList(); ?>

        <h2><?php echo $locale->t('toolbox.index.global_information'); ?></h2>
        <table class="table">
            <tr>
              <td><b><?php echo $locale->t('toolbox.index.prestashop_version'); ?></b></td>
              <td><?php echo _PS_VERSION_; ?></td>
            </tr>
            <tr>
              <td><b><?php echo $locale->t('toolbox.index.plugin_version'); ?></b></td>
              <td><?php echo Configuration::get('LENGOW_VERSION'); ?></td>
            </tr>
        </table>
        
        <h2><?php echo $locale->t('toolbox.index.store_information'); ?></h2>

    </div>
<?php
require 'views/footer.php';
