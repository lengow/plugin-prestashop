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

<div class="lgw-container">
    {if $lengow_configuration->getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED') eq '1'}
        <div id="lgw-preprod" class="adminlengoworder">
            {$locale->t('menu.preprod_active')|escape:'htmlall':'UTF-8'}
        </div>
    {/if}
    <div class="lgw-box" id="lengow_order_wrapper">
        {if isset($toolbox) && $toolbox}
            {include file='./header_toolbox.tpl'}
        {else}
            {if $lengow_configuration->getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED') eq '1'}
                <p class="blue-frame" style="line-height: 20px;">
                    {$locale->t('order.screen.preprod_warning_message',
                    ['url' => {$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|cat:'#preprod_setting'|escape:'htmlall':'UTF-8'}]
                )}</p>
            {/if}
            <div class="lgw-col-8" style="padding:0;">
                <div id="lengow_last_importation">
                    {include file='./last_importation.tpl'}
                </div>
                <div id="lengow_wrapper_messages" class="blue-frame" style="display:none;"></div>
            </div>
        {/if}
        {if !isset($toolbox) || !$toolbox}
            <div class="pull-right text-right lgw-col-3">
                <a id="lengow_import_orders" class="lgw-btn btn no-margin-top"
                    data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
                    {$locale->t('order.screen.button_update_orders')|escape:'htmlall':'UTF-8'}
                </a>
                {if not $cron_active}
                    <p class="small light text-right">
                        <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting')|escape:'htmlall':'UTF-8'}#cron_setting" class="sub-link">
                            {$locale->t('order.screen.cron')|escape:'htmlall':'UTF-8'}
                        </a>
                    </p>
                {/if}
            </div>
        {/if}
        <!-- UPDATE ORDERS -->
        <div id="lengow_charge_import_order" style="display:none">
            <p id="lengow_charge_lign1">{$locale->t('order.screen.import_charge_first')|escape:'htmlall':'UTF-8'}</p>
            <p id="lengow_charge_lign2">{$locale->t('order.screen.import_charge_second')|escape:'htmlall':'UTF-8'}</p>
        </div>
        <!-- /UPDATE ORDERS -->
        <!-- TABLE -->
        <div class="clearfix"></div>
        <div id="lengow_order_table_wrapper">
            {if $nb_order_imported eq '0'}
                {include file='./no_order.tpl'}
            {else}
                {html_entity_decode($lengow_table|escape:'htmlall':'UTF-8')}
            {/if}
        </div>
        <!-- /TABLE -->
    </div>
</div>
<script type="text/javascript" src="/modules/lengow/views/js/lengow/order.js"></script>
