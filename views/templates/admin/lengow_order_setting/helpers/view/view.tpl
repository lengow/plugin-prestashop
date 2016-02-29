{*
 * Copyright 2015 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 *  @author	   Team Connector <team-connector@lengow.com>
 *  @copyright 2015 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
<div id="lengow_form_wrapper">
<form class="lengow_form" method="POST">
    <input type="hidden" name="action" value="process">
    <div class="container">
        <h2>{$locale->t('order_setting.screen.order_status_title')}</h2>
        <p>{$locale->t('order_setting.screen.order_status_description')}</p><br/>
        {$matching}
    </div>
    <div class="container2">
        <h2>{$locale->t('order_setting.screen.carrier_management_title')}</h2>
        <p>{$locale->t('order_setting.screen.carrier_management_description')}</p>
        <p>{$locale->t('order_setting.screen.country_wt_carrier')}
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminCarriers', false, true)}">
                {$locale->t('order_setting.screen.country_wt_carrier_link')}
            </a>
        </p>
        <br/>
        <div class="select_country">
            {include file='./select_country.tpl'}
        </div>
        <div id="error_select_country"></div>
        <div id="add_marketplace_country">
            <ul id="marketplace_country" class="accordion">
                {include file='./marketplace_carrier.tpl'}
            </ul>
        </div>
    </div>
    <div class="container2">
        <h2>{$locale->t('order_setting.screen.import_setting_title')}</h2>
        <p>{$locale->t('order_setting.screen.import_setting_description')}</p><br/>
        {$import_params}
    </div>
    <div id="cron_setting" class="container2">
        <h2>Cron setting</h2>
        {$formCron}
        {if $moduleCron}
        {$cron_param}
        {/if}
        <p>-- {$locale->t('order_setting.screen.cron_if_not_exists')} --</p>
        <p>{$locale->t('order_setting.screen.cron_manual_installation')}</p>
        <strong><code>{$locale->t('order_setting.screen.command_unix_crontab')} {$import_url}</code></strong><br /><br />
    </div>
    <br/>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn lengow_btn lengow_submit_order_setting">{$locale->t('order_setting.screen.button_save')}</button>
        </div>
    </div>
</form>
</div>

<script type="text/javascript" src="/modules/lengow/views/js/lengow/order_setting.js"></script>