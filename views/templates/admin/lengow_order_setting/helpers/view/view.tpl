{*
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
 *  @author    Team Connector <team-connector@lengow.com>
 *  @copyright 2016 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
 
<div id="lengow_form_wrapper">
<form class="lengow_form" method="POST" id="lengow_form_order_setting">
    <input type="hidden" name="action" value="process">
    <div id="matching_order_status" class="container">
        <h2>{$locale->t('order_setting.screen.order_status_title')|escape:'htmlall':'UTF-8'}</h2>
        <p>{$locale->t('order_setting.screen.order_status_description')|escape:'htmlall':'UTF-8'}</p><br/>
        {html_entity_decode($matching|escape:'htmlall':'UTF-8')}
    </div>
    <div class="container">
        <h2>{$locale->t('order_setting.screen.carrier_management_title')|escape:'htmlall':'UTF-8'}</h2>
        <p>{$locale->t('order_setting.screen.carrier_management_description')|escape:'htmlall':'UTF-8'}</p>
        <p>{$locale->t('order_setting.screen.country_wt_carrier')|escape:'htmlall':'UTF-8'}
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminCarriers', false, true)|escape:'htmlall':'UTF-8'}">
                {$locale->t('order_setting.screen.country_wt_carrier_link')|escape:'htmlall':'UTF-8'}
            </a>
        </p><br/>
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
    <div id="import_setting" class="container">
        <h2>{$locale->t('order_setting.screen.import_setting_title')|escape:'htmlall':'UTF-8'}</h2>
        <p>{$locale->t('order_setting.screen.import_setting_description')|escape:'htmlall':'UTF-8'}</p><br/>
        {html_entity_decode($import_params|escape:'htmlall':'UTF-8')}
    </div>
    <div id="cron_setting" class="container">
        <h2>{$locale->t('order_setting.screen.cron_title')|escape:'htmlall':'UTF-8'}</h2>
        {html_entity_decode($formCron|escape:'htmlall':'UTF-8')}
        {if isset($moduleCron) && $moduleCron}
            {html_entity_decode($cron_param|escape:'htmlall':'UTF-8')}
        {/if}
        <p>-- {$locale->t('order_setting.screen.cron_if_not_exists')|escape:'htmlall':'UTF-8'} --</p>
        <p>{$locale->t('order_setting.screen.cron_manual_installation')|escape:'htmlall':'UTF-8'}</p>
        <strong><code>*/15 * * * * wget {$import_url|escape:'htmlall':'UTF-8'}</code></strong>
    </div>
    <div class="form-group container">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn lengow_btn lengow_submit_order_setting">
                {$locale->t('global_setting.screen.button_save')|escape:'htmlall':'UTF-8'}
            </button>
        </div>
    </div>
</form>
</div>

<script type="text/javascript" src="/modules/lengow/views/js/lengow/order_setting.js"></script>