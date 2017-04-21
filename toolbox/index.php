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

require 'conf.inc.php';
require 'views/header.php';

$check = new LengowCheck();
if (_PS_VERSION_ < '1.5') {
    $shopCollection = array(array('id_shop' => 1));
} else {
    $sql = 'SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop WHERE active = 1';
    $shopCollection = Db::getInstance()->ExecuteS($sql);
}

?>
    <div class="container">
        <h1><?php echo $locale->t('toolbox.menu.lengow_toolbox'); ?></h1>
        <h3><i class="fa fa-check-square-o"></i> <?php echo $locale->t('toolbox.index.checklist_information'); ?></h3>
        <?php echo $check->getCheckList(); ?>
        <h3><i class="fa fa-cog"></i> <?php echo $locale->t('toolbox.index.global_information'); ?></h3>
        <?php echo $check->getGlobalInformation(); ?>
        <h3><i class="fa fa-download"></i> <?php echo $locale->t('toolbox.index.import_information'); ?></h3>
        <?php echo $check->getImportInformation(); ?>
        <h3><i class="fa fa-upload"></i> <?php echo $locale->t('toolbox.index.export_information'); ?></h3>
        <?php
        foreach ($shopCollection as $row) {
            $shop = new LengowShop($row['id_shop']);
            echo $check->getInformationByStore($shop);
        }
        ?>
    </div>
<?php
require 'views/footer.php';
