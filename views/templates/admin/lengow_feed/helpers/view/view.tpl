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

<div id="lengow_feed_wrapper">
    {foreach from=$shopCollection  item=shop}
        <div class="lengow_feed_block" id="block_{$shop['shop']->id|escape:'htmlall':'UTF-8'}">
            <div class="lengow_feed_block_header">
                <div class="lengow_feed_block_header_title">
                    <span class="title">{$shop['shop']->name|escape:'htmlall':'UTF-8'}</span><span class="url">http://{$shop['shop']->domain|escape:'htmlall':'UTF-8'}</span>
                </div>
                <div class="lengow_feed_block_header_content">
                    <div class="lengow_feed_block_content_right">
                        <span class="lengow_exported">{$shop['total_export_product']|escape:'htmlall':'UTF-8'}</span> exported<br/>
                        <span class="lengow_total">{$shop['total_product']|escape:'htmlall':'UTF-8'}</span> available<br/>
                        <input type="checkbox" data-size="mini" data-on-text="Yes" data-off-text="No"
                               name="lengow_export_selection" class="lengow_switch lengow_switch_option"
                               data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true)}|escape:'htmlall':'UTF-8'"
                               data-action="change_option_product_variation"
                               data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                               value="1" {if $shop['option_variation'] == 1} checked="checked"{/if}>
                        <span class="lengow_select_text">Include product variation</span>
                        <i class="fa fa-info-circle lengow_link_tooltip" title="Dum apud Persas, ut supra narravimus, perfidia regis motus agitat insperatos, et in eois tractibus bella rediviva consurgunt, anno sexto decimo et eo diutius pos"></i>
                        <br/>
                        <input type="checkbox" data-size="mini" data-on-text="Yes" data-off-text="No"
                               name="lengow_export_selection" class="lengow_switch lengow_switch_option"
                               data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true)|escape:'htmlall':'UTF-8'}"
                               data-action="change_option_product_out_of_stock"
                               data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                               value="1" {if $shop['option_product_out_of_stock'] == 1} checked="checked"{/if}>
                        <span class="lengow_select_text">Include out of stock product</span>
                        <i class="fa fa-info-circle lengow_link_tooltip" title="Dum apud Persas, ut supra narravimus, perfidia regis motus agitat insperatos, et in eois tractibus bella rediviva consurgunt, anno sexto decimo et eo diutius pos"></i>
                        <br/>
                        <input type="checkbox" data-size="mini" data-on-text="Yes" data-off-text="No"
                               name="lengow_export_selection" class="lengow_switch lengow_switch_option"
                               data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true)|escape:'htmlall':'UTF-8'}"
                               data-action="change_option_selected"
                               data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                               value="1" {if $shop['option_selected'] == 1} checked="checked"{/if}>
                        <span class="lengow_select_text">Select specific products</span>
                        <i class="fa fa-info-circle lengow_link_tooltip" title="Dum apud Persas, ut supra narravimus, perfidia regis motus agitat insperatos, et in eois tractibus bella rediviva consurgunt, anno sexto decimo et eo diutius pos"></i>
                    </div>
                    <div class="lengow_feed_block_content_left">
                        This is your exported catalog. Copy this link in your Lengow platform<br/>
                        <input id="link_shop_{$shop['shop']->id}" value="{$shop['link']|escape:'htmlall':'UTF-8'}" readonly>
                        <a class="lengow_copy" data-clipboard-target="#link_shop_{$shop['shop']->id|escape:'htmlall':'UTF-8'}">Copy</a>
                        <a href="{$shop['link']|escape:'htmlall':'UTF-8'}&stream=1" target="_blank">Download</a><br/>
                        {if $shop['last_export']}
                            Last exportation {$shop['last_export']|escape:'htmlall':'UTF-8'}<br/>
                        {else}
                            No export<br/>
                        {/if}
                        <a class="lengow_btn" href="{$shop['link']|escape:'htmlall':'UTF-8'}" target="_blank">Launch new Export</a>
                    </div>
                    <div class="lengow_clear"></div>
                </div>
            </div>

            <div class="lengow_feed_block_footer">
                <div class="lengow_feed_block_footer_content" style="{if !$shop['option_selected']}display:none;{/if}">
                    {html_entity_decode($shop['list']|escape:'htmlall':'UTF-8')}
                </div>
            </div>
        </div>
    {/foreach}
</div>

<script type="text/javascript" src="/modules/lengow/views/js/lengow/feed.js"></script>
