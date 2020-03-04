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

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
$accessToken = isset($_REQUEST['access_token']) ? $_REQUEST['access_token'] : null;
$secretToken = isset($_REQUEST['secret_token']) ? $_REQUEST['secret_token'] : null;
$fullAccess = isset($_REQUEST['access']) ? $_REQUEST['access'] : null;

$locale = new LengowTranslation();
$form = new LengowConfigurationForm(array('fields' => LengowConfiguration::getKeys()));
$lengowPathUri = __PS_BASE_URI__ . 'modules/lengow/';
$actionGetDefaultSetting =  $lengowPathUri . 'toolbox/config.php?action=get_default_settings&access=admin';
$actionUpdateSetting =  $lengowPathUri . 'toolbox/config.php?action=update_settings&access=admin';

if (_PS_VERSION_ < '1.5') {
    $shopCollection = array(array('id_shop' => 1));
} else {
    $sql = 'SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop WHERE active = 1';
    $shopCollection = Db::getInstance()->ExecuteS($sql);
}

switch ($action) {
    case 'update':
        $form->postProcess(
            array(
                'LENGOW_SHOP_ACTIVE',
                'LENGOW_EXPORT_VARIATION_ENABLED',
                'LENGOW_EXPORT_OUT_STOCK',
                'LENGOW_EXPORT_INACTIVE',
                'LENGOW_EXPORT_SELECTION_ENABLED',
                'LENGOW_EXPORT_FILE_ENABLED',
                'LENGOW_IMPORT_FORCE_PRODUCT',
                'LENGOW_IMPORT_PROCESSING_FEE',
                'LENGOW_IMPORT_DEBUG_ENABLED',
                'LENGOW_IMPORT_SHIP_MP_ENABLED',
                'LENGOW_IMPORT_STOCK_SHIP_MP',
                'LENGOW_REPORT_MAIL_ENABLED',
                'LENGOW_IMPORT_SINGLE_ENABLED',
                'LENGOW_TRACKING_ENABLED',
            )
        );
        Tools::redirect(_PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/lengow/toolbox/config.php', '');
        break;
    case 'get_default_settings':
        if ($fullAccess && $fullAccess === 'admin') {
            LengowConfiguration::resetAll(true);
        }
        Tools::redirect(_PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/lengow/toolbox/config.php', '');
        break;
    case 'update_settings':
        if ($fullAccess && $fullAccess === 'admin') {
            if (_PS_VERSION_ < '1.5') {
                $tempProfile = Context::getContext()->cookie->profile;
                Context::getContext()->cookie->profile = 1;
            }
            LengowTranslation::$forceIsoCode = null;
            $module = Module::getInstanceByName('lengow');
            $install = new LengowInstall($module);
            $install->update();
            LengowTranslation::$forceIsoCode = LengowTranslation::DEFAULT_ISO_CODE;
            if (_PS_VERSION_ < '1.5') {
                Context::getContext()->cookie->profile = $tempProfile;
            }
        }
        Tools::redirect(_PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/lengow/toolbox/config.php', '');
        break;
}

require 'views/header.php';
?>

    <div class="container">
        <h1><?php echo $locale->t('toolbox.menu.configuration'); ?></h1>
        <form class="form-horizontal" method="POST">
            <input type="hidden" name="action" value="update"/>
            <fieldset>
                <h3 class="start-block"><?php echo $locale->t('toolbox.configuration.shop_credentials'); ?></h3>
                <?php
                echo $form->buildInputs(
                    array(
                        'LENGOW_ACCOUNT_ID',
                        'LENGOW_ACCESS_TOKEN',
                        'LENGOW_SECRET_TOKEN',
                    )
                );
                echo '<br/>';
                foreach ($shopCollection as $row) {
                    $shop = new LengowShop($row['id_shop']);
                    echo '<h4 style="text-align:center">' . $shop->name . ' (' . $shop->id . ')</h4>';
                    echo $form->buildShopInputs(
                        $shop->id,
                        array(
                            'LENGOW_SHOP_ACTIVE',
                            'LENGOW_CATALOG_ID',
                        )
                    );
                    echo '<br/>';
                }
                echo '</fieldset>';
                echo '<fieldset><h3 class="start-block">'
                    . $locale->t('toolbox.configuration.global_setting') . '</h3>';
                echo $form->buildInputs(
                    array(
                        'LENGOW_AUTHORIZED_IP',
                        'LENGOW_IMPORT_DEBUG_ENABLED',
                        'LENGOW_REPORT_MAIL_ENABLED',
                        'LENGOW_REPORT_MAIL_ADDRESS',
                        'LENGOW_EXPORT_CARRIER_DEFAULT',
                        'LENGOW_TRACKING_ENABLED',
                        'LENGOW_TRACKING_ID',
                    )
                );
                echo '</fieldset>';
                echo '<fieldset><h3 class="start-block">'
                    . $locale->t('toolbox.configuration.export_setting') . '</h3>';
                echo $form->buildInputs(
                    array(
                        'LENGOW_EXPORT_FORMAT',
                        'LENGOW_EXPORT_FILE_ENABLED',
                    )
                );
                echo '<br/>';
                foreach ($shopCollection as $row) {
                    $shop = new LengowShop($row['id_shop']);
                    echo '<h4 style="text-align:center">' . $shop->name . ' (' . $shop->id . ')</h4>';
                    echo $form->buildShopInputs(
                        $shop->id,
                        array(
                            'LENGOW_EXPORT_VARIATION_ENABLED',
                            'LENGOW_EXPORT_OUT_STOCK',
                            'LENGOW_EXPORT_SELECTION_ENABLED',
                            'LENGOW_EXPORT_INACTIVE',
                        )
                    );
                    echo '<br/>';
                }
                echo '</fieldset>';
                echo '<fieldset><h3 class="start-block">'
                    . $locale->t('toolbox.configuration.import_setting') . '</h3>';
                echo $form->buildInputs(
                    array(
                        'LENGOW_ORDER_ID_PROCESS',
                        'LENGOW_ORDER_ID_SHIPPED',
                        'LENGOW_ORDER_ID_SHIPPEDBYMP',
                        'LENGOW_ORDER_ID_CANCEL',
                        'LENGOW_IMPORT_FORCE_PRODUCT',
                        'LENGOW_IMPORT_PROCESSING_FEE',
                        'LENGOW_IMPORT_DAYS',
                        'LENGOW_IMPORT_SHIP_MP_ENABLED',
                        'LENGOW_IMPORT_STOCK_SHIP_MP',
                        'LENGOW_IMPORT_SINGLE_ENABLED',
                    )
                );
                echo '</fieldset>';
                ?>
                <div class="form-group">
                    <button type="submit" class="btn-success lgw-btn">
                        <?php echo $locale->t('toolbox.configuration.button_save'); ?>
                    </button>
                    <?php
                    if ($fullAccess && $fullAccess === 'admin') {
                        ?>
                        <a class="lgw-btn btn-success"
                           href="<?php echo $actionGetDefaultSetting; ?>"
                           onclick="return confirm(
                               '<?php echo $locale->t('toolbox.configuration.check_get_default_settings'); ?>'
                               )">
                            <?php echo $locale->t('toolbox.configuration.get_default_settings'); ?>
                        </a>
                        <a class="lgw-btn btn-success"
                           href="<?php echo $actionUpdateSetting; ?>"
                           onclick="return confirm(
                               '<?php echo $locale->t('toolbox.configuration.check_update_settings'); ?>'
                               )">
                            <?php echo $locale->t('toolbox.configuration.update_settings'); ?>
                        </a>
                        <?php
                    }
                    ?>
                </div>
        </form>
    </div>
<?php
require 'views/footer.php';
