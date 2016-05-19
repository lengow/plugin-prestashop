{*
 * Copyright 2016 Lengow SAS.
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
 *  @copyright 2016 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}


<div id="lengow_home_feed_wrapper">
<div id="lengow_feed_wrapper">
    {foreach from=$shopCollection  item=shop}
        <div class="lengow_feed_block" id="block_{$shop['shop']->id|escape:'htmlall':'UTF-8'}">
            <div class="lengow_feed_block_header">
                <div class="lengow_feed_block_header_title" id="lengow_feed_block_header_title">
                    <span class="title">{$shop['shop']->name|escape:'htmlall':'UTF-8'}</span>
                    <span class="url">http://{$shop['shop']->domain|escape:'htmlall':'UTF-8'}</span>
                    <div class="lengow_check_shop lengow_link_tooltip"
                         data-original-title=""
                         data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true)|escape:'htmlall':'UTF-8'}">
                    </div>
                </div>
                <div class="lengow_feed_block_header_content">
                    <div class="lengow_feed_block_content_right">
                        <div class="lengow_feed_block_header_content_result">
                            <span class="lengow_exported">{$shop['total_export_product']|escape:'htmlall':'UTF-8'}</span>
                            {$locale->t('product.screen.nb_exported')|escape:'htmlall':'UTF-8'}<br/>
                            <span class="lengow_total">{$shop['total_product']|escape:'htmlall':'UTF-8'}</span>
                            {$locale->t('product.screen.nb_available')|escape:'htmlall':'UTF-8'}<br/>
                        </div>
                        <input
                            type="checkbox"
                            data-size="mini"
                            data-on-text="{$locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}"
                            data-off-text="{$locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}"
                            name="lengow_export_selection" class="lengow_switch lengow_switch_option"
                            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true)|escape:'htmlall':'UTF-8'}"
                            data-action="change_option_product_variation"
                            data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                            value="1" {if $shop['option_variation'] == 1} checked="checked"{/if}>
                        <span class="lengow_select_text">{$locale->t('product.screen.include_variation')|escape:'htmlall':'UTF-8'}</span>
                        <i class="fa fa-info-circle lengow_link_tooltip"
                           title="{$locale->t('product.screen.include_variation_support')|escape:'htmlall':'UTF-8'}"></i>
                        <br/>
                        <input
                            type="checkbox"
                            data-size="mini"
                            data-on-text="{$locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}"
                            data-off-text="{$locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}"
                            name="lengow_export_selection" class="lengow_switch lengow_switch_option"
                            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true)|escape:'htmlall':'UTF-8'}"
                            data-action="change_option_product_out_of_stock"
                            data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                            value="1" {if $shop['option_product_out_of_stock'] == 1} checked="checked"{/if}>
                        <span class="lengow_select_text">{$locale->t('product.screen.include_out_of_stock')|escape:'htmlall':'UTF-8'}</span>
                        <i class="fa fa-info-circle lengow_link_tooltip"
                           title="{$locale->t('product.screen.include_out_of_stock_support')|escape:'htmlall':'UTF-8'}"></i>
                        <br/>
                        <input
                            type="checkbox"
                            data-size="mini"
                            data-on-text="{$locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}"
                            data-off-text="{$locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}"
                            name="lengow_export_selection" class="lengow_switch lengow_switch_option"
                            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true)|escape:'htmlall':'UTF-8'}"
                            data-action="change_option_selected"
                            data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                            value="1" {if $shop['option_selected'] == 1} checked="checked"{/if}>
                        <span class="lengow_select_text">{$locale->t('product.screen.include_specific_product')|escape:'htmlall':'UTF-8'}</span>
                        <i class="fa fa-info-circle lengow_link_tooltip"
                           title="{$locale->t('product.screen.include_specific_product_support')|escape:'htmlall':'UTF-8'}"></i>
                    </div>
                    <div class="lengow_feed_block_content_left">
                        <p>{$locale->t('product.screen.your_exported_catalog')|escape:'htmlall':'UTF-8'}</p>
                        <p>
                            <input id="link_shop_{$shop['shop']->id|escape:'htmlall':'UTF-8'}" value="{$shop['link']|escape:'htmlall':'UTF-8'}" readonly>
                            <a class="lengow_copy lengow_link_tooltip"
                                data-original-title="{$locale->t('product.screen.button_copy')|escape:'htmlall':'UTF-8'}"
                                data-clipboard-target="#link_shop_{$shop['shop']->id|escape:'htmlall':'UTF-8'}">
                                <i class="fa fa-clone"></i></a>
                            <a href="{$shop['link']|escape:'htmlall':'UTF-8'}&stream=1"
                                class="lengow_link_tooltip"
                                data-original-title="{$locale->t('product.screen.button_download')|escape:'htmlall':'UTF-8'}"
                                target="_blank"><i class="fa fa-download"></i></a>
                        <p/>
                        {if $shop['last_export']}
                            <span class="lengow_strong">{$locale->t('product.screen.last_export')|escape:'htmlall':'UTF-8'} :</span>
                            {$shop['last_export']|date_format:"%A %e %B %Y @ %R"|escape:'htmlall':'UTF-8'}<br/>
                        {else}
                            <span class="lengow_strong">{$locale->t('product.screen.no_export')|escape:'htmlall':'UTF-8'}</span><br/>
                        {/if}
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
</div>

<script type="text/javascript" src="/modules/lengow/views/js/lengow/feed.js"></script>
