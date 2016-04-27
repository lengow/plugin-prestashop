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
    <div class="lgw-box">
        <div id="lengow_order_wrapper" class="lengow_order">
            <div class="lengow_order_block">
                <div id="lengow_charge_import_order_background" style="display:none"></div>
                <div id="lengow_charge_import_order" style="display:none">
                    <p id="lengow_charge_lign1">{$locale->t('order.screen.import_charge_first')|escape:'htmlall':'UTF-8'}</p>
                    <p id="lengow_charge_lign2">{$locale->t('order.screen.import_charge_second')|escape:'htmlall':'UTF-8'}</p>
                </div>
                <div class="lengow_order_block_header">
                    {if isset($toolbox) && $toolbox}
                        {include file='./header_toolbox.tpl'}
                    {else}
                        {if $lengow_configuration->getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED') eq '1'}
                            <div class="lengow_alert lengow_center">
                                {$locale->t('order.screen.preprod_warning_message',
                                ['url' => {$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|cat:'#preprod_setting'|escape:'htmlall':'UTF-8'}]
                            )}
                            </div>
                        {/if}
                        <div class="lengow_order_block_header_content">
                            <div id="lengow_last_importation" class="lengow_order_block_content_left">
                                {include file='./last_importation.tpl'}
                            </div>
                            <div class="lengow_order_block_content_right">
                                <a id="lengow_import_orders" class="lengow_btn btn btn-success" data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
                                    {$locale->t('order.screen.button_update_orders')|escape:'htmlall':'UTF-8'}</a>
                            </div>
                            <div class="lengow_clear"></div>
                        </div>
                        <div id="lengow_wrapper_messages"></div>
                    {/if}
                </div>
                <div>
                    <div id="lengow_order_table_wrapper">
                        {if $nb_order_imported eq '0'}
                            {include file='./no_order.tpl'}
                        {else}
                            {html_entity_decode($lengow_table|escape:'htmlall':'UTF-8')}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="/modules/lengow/views/js/lengow/order.js"></script>