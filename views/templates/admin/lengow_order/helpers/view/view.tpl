{*
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
 *  @author    Team Connector <team-connector@lengow.com>
 *  @copyright 2017 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
<div id="lengow_order_wrapper" class="cms-global">
    <div class="lgw-container">
        {if $lengow_configuration->debugModeIsActive()}
            <div id="lgw-debug" class="adminlengoworder">
                {$locale->t('menu.debug_active')|escape:'htmlall':'UTF-8'}
            </div>
        {/if}
        <div class="lgw-box">
            {if isset($toolbox) && $toolbox}
                {include file='./header_toolbox.tpl'}
            {else}
                <div id="lengow_warning_message">
                    {include file='./warning_message.tpl'}
                </div>
                <div class="lgw-col-8" style="padding:0;">
                    <div id="lengow_last_importation">
                        {include file='./last_importation.tpl'}
                    </div>
                    <div id="lengow_wrapper_messages" class="blue-frame mod-order-notification" style="display:none;"></div>
                </div>
            {/if}
            {if !isset($toolbox) || !$toolbox}
                <div class="pull-right text-right lgw-col-3">
                    <a id="lengow_import_orders" class="lgw-btn btn no-margin-top"
                        data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
                        {$locale->t('order.screen.button_update_orders')|escape:'htmlall':'UTF-8'}
                    </a>
                </div>
            {/if}
            <!-- UPDATE ORDERS -->
            <div id="lengow_charge_import_order" style="display:none">
                <div class="ajax-loading mod-synchronise-order">
                    <div class="ajax-loading-ball1"></div>
                    <div class="ajax-loading-ball2"></div>
                </div>
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
                    {if $version > '1.7' && isset($toolbox) && $toolbox }
                        {$lengow_table|escape:'htmlall':'UTF-8' nofilter}
                    {else}
                        {html_entity_decode($lengow_table|escape:'htmlall':'UTF-8')}
                    {/if}
                {/if}
            </div>
            <!-- /TABLE -->
        </div>
    </div>
</div>
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/lengow/order.js"></script>
