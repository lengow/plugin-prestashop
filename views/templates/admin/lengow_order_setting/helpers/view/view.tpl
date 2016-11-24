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
<div id="lengow_form_order_setting" class="cms-global">
    <div class="lgw-container">
        {if $lengow_configuration->getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED') eq '1'}
            <div id="lgw-preprod" class="adminlengowordersetting">
                {$locale->t('menu.preprod_active')|escape:'htmlall':'UTF-8'}
            </div>
        {/if}
        <form method="POST">
            <div class="lgw-box">
                <input type="hidden" name="action" value="process">
                <h2>{$locale->t('order_setting.screen.order_status_title')|escape:'htmlall':'UTF-8'}</h2>
                <div>
                    <p>{$locale->t('order_setting.screen.order_status_description')|escape:'htmlall':'UTF-8'}</p>
                    <p>{html_entity_decode($matching|escape:'htmlall':'UTF-8')}</p>
                </div>

            </div>
            <div class="lgw-box">
                <h2>{$locale->t('order_setting.screen.carrier_management_title')|escape:'htmlall':'UTF-8'}</h2>
                <p>{$locale->t('order_setting.screen.carrier_management_description')|escape:'htmlall':'UTF-8'}</p>
                <p>{$locale->t('order_setting.screen.country_wt_carrier')|escape:'htmlall':'UTF-8'}
                    <a href="{$lengow_link->getAbsoluteAdminLink('AdminCarriers', false, true)|escape:'htmlall':'UTF-8'}">
                        {$locale->t('order_setting.screen.please_setup_then')|escape:'htmlall':'UTF-8'}
                    </a>
                </p>
                <div id="error_select_country"></div>
                <div id="add_marketplace_country">
                    <ul id="marketplace_country" class="accordion">
                        {include file='./marketplace_carrier.tpl'}
                    </ul>
                </div>
                <a href="#" class="add-country">
                    <i class="fa fa-plus"></i>
                    {$locale->t('order_setting.screen.add_new_country')|escape:'htmlall':'UTF-8'}
                </a>
                <div class="select_country" style="display:none">
                    {include file='./select_country.tpl'}
                </div>
            </div>
            <div class="lgw-box">
                <h2>{$locale->t('order_setting.screen.import_setting_title')|escape:'htmlall':'UTF-8'}</h2>
                {$locale->t('order_setting.screen.import_setting_description')|escape:'htmlall':'UTF-8'}
                {html_entity_decode($import_params|escape:'htmlall':'UTF-8')}
            </div>
            <div class="lgw-box" id="cron_setting">
                <h2>{$locale->t('order_setting.screen.cron_title')|escape:'htmlall':'UTF-8'}</span></h2>
                {html_entity_decode($formCron|escape:'htmlall':'UTF-8')}
                {if isset($moduleCron) && $moduleCron}
                    {html_entity_decode($cron_param|escape:'htmlall':'UTF-8')}
                {/if}
                <p>-- {$locale->t('order_setting.screen.cron_if_not_exists')|escape:'htmlall':'UTF-8'} --</p>
                <p>{$locale->t('order_setting.screen.cron_manual_installation')|escape:'htmlall':'UTF-8'}</p>
                <code>*/15 * * * * wget {$import_url|escape:'htmlall':'UTF-8'}</code>
            </div>
            <button type="submit" class="lgw-btn lgw-btn-progression lengow_submit_order_setting">
                <div class="btn-inner">
                    <div class="btn-step default">
                        {$locale->t('global_setting.screen.button_save')|escape:'htmlall':'UTF-8'}
                    </div>
                    <div class="btn-step loading">
                        {$locale->t('global_setting.screen.setting_saving')|escape:'htmlall':'UTF-8'}
                    </div>
                    <div class="btn-step done" data-success="Saved!" data-error="Error">
                        {$locale->t('global_setting.screen.setting_saved')|escape:'htmlall':'UTF-8'}
                    </div>
                </div>
            </button>
        </form>
    </div>
</div>
<script type="text/javascript" src="/modules/lengow/views/js/lengow/order_setting.js"></script>
