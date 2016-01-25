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
?>
<h1>Configuration</h1>
<form class="form-horizontal" method="POST">
    <input type="hidden" name="action" value="update"/>
    <?php
    foreach ($shopCollection as $row) {
        $shop = new LengowShop($row['id_shop']);
        echo '<fieldset><legend>Boutique : '.$shop->name.' > Import</legend>';
        echo $form->buildShopInputs($shop->id, array(
            'LENGOW_ACCOUNT_ID',
            'LENGOW_ACCESS_TOKEN',
            'LENGOW_SECRET_TOKEN',
            'LENGOW_SHOP_ACTIVE',
        ));
        echo '</fieldset>';
        echo '<fieldset><legend>Boutique : '.$shop->name.' > Export</legend>';
        echo $form->buildShopInputs($shop->id, array(
            'LENGOW_SHOP_TOKEN',
            'LENGOW_EXPORT_SELECTION_ENABLED',
            'LENGOW_EXPORT_VARIATION_ENABLED',
            'LENGOW_LAST_EXPORT',
        ));
        echo '</fieldset>';
    }
    echo '<fieldset><legend>Import</legend>';
    echo $form->buildInputs(array(
        'LENGOW_ORDER_ID_PROCESS',
        'LENGOW_ORDER_ID_SHIPPED',
        'LENGOW_ORDER_ID_SHIPPEDBYMP',
        'LENGOW_ORDER_ID_CANCEL',
        'LENGOW_IMPORT_CARRIER_DEFAULT',
        'LENGOW_IMPORT_FORCE_PRODUCT',
        'LENGOW_IMPORT_DAYS',
        'LENGOW_IMPORT_PREPROD_ENABLED',
        'LENGOW_IMPORT_FAKE_EMAIL',
        'LENGOW_IMPORT_SHIP_MP_ENABLED',
        'LENGOW_IMPORT_CARRIER_MP_ENABLED',
        'LENGOW_REPORT_MAIL_ENABLED',
        'LENGOW_REPORT_MAIL_ADDRESS',
        'LENGOW_IMPORT_SINGLE_ENABLED',
        'LENGOW_IMPORT_IN_PROGRESS',
        'LENGOW_LAST_IMPORT_CRON',
        'LENGOW_LAST_IMPORT_MANUAL',
        'LENGOW_GLOBAL_TOKEN',
        'LENGOW_AUTHORIZED_IP',
        'LENGOW_TRACKING_ENABLED',
        'LENGOW_TRACKING_ID',
    ));
    echo '</fieldset>';
    echo '<fieldset><legend>Export</legend>';
    echo $form->buildInputs(array(
        'LENGOW_EXPORT_FORMAT',
        'LENGOW_EXPORT_FILE_ENABLED',
        'LENGOW_CARRIER_DEFAULT',
    ));
    echo '</fieldset>';
    ?>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-success">Save</button>
        </div>
    </div>
</form>