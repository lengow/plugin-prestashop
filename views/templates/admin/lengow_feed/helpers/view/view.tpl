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


<div class="lgw-container" id="lengow_feed_wrapper">
    {foreach from=$shopCollection  item=shop}
        <div  id="block_{$shop['shop']->id|escape:'htmlall':'UTF-8'}">
            <div class=" lgw-box lengow_feed_block_header_title" id="lengow_feed_block_header_title">
                <div class="lgw-container">
                    <div class="lengow_check_shop lengow_link_tooltip pull-right"
                         data-original-title=""
                         data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true)|escape:'htmlall':'UTF-8'}">
                    </div>
                    <p class="bold no-margin">
                        {$shop['shop']->name|escape:'htmlall':'UTF-8'}
                        http://{$shop['shop']->domain|escape:'htmlall':'UTF-8'}
                    </p>
                </div>
            </div>
            <div class="lgw-box">
                <div class="lengow_feed_block_header_content lgw-container">
                    <div class="lgw-row">
                        <div class="lgw-col-6">
                            <p>{$locale->t('product.screen.your_exported_catalog')|escape:'htmlall':'UTF-8'}</p>
                            <p>
                                <input type="text" id="link_shop_{$shop['shop']->id|escape:'htmlall':'UTF-8'}" value="{$shop['link']|escape:'htmlall':'UTF-8'}" readonly>
                                <a class="lengow_copy lengow_link_tooltip"
                                    data-original-title="{$locale->t('product.screen.button_copy')|escape:'htmlall':'UTF-8'}"
                                    data-clipboard-target="#link_shop_{$shop['shop']->id|escape:'htmlall':'UTF-8'}">
                                    <i class="fa fa-clone"></i></a>
                                <a href="{$shop['link']|escape:'htmlall':'UTF-8'}&stream=1"
                                    class="lengow_link_tooltip"
                                    data-original-title="{$locale->t('product.screen.button_download')|escape:'htmlall':'UTF-8'}"
                                    target="_blank"><i class="fa fa-download"></i></a>
                            </p>
                            {if $shop['last_export']}
                                <p>{$locale->t('product.screen.last_export')|escape:'htmlall':'UTF-8'} :
                                <span  class="bold">{$shop['last_export']|date_format:"%A %e %B %Y @ %R"|escape:'htmlall':'UTF-8'}</span></p>
                            {else}
                                <p>{$locale->t('product.screen.no_export')|escape:'htmlall':'UTF-8'}</p>
                            {/if}
                        </div>
                        <div class="lgw-col-6">
                            <div class="lengow_feed_block_header_content_result">
                                <div>
                                    <span class="lengow_exported stats-big-value">{$shop['total_export_product']|escape:'htmlall':'UTF-8'}</span>
                                    <p>{$locale->t('product.screen.nb_exported')|escape:'htmlall':'UTF-8'}</p>
                                </div>
                                <div>
                                    <span class="lengow_total stats-big-value">{$shop['total_product']|escape:'htmlall':'UTF-8'}</span>
                                    <p>{$locale->t('product.screen.nb_available')|escape:'htmlall':'UTF-8'}</p>
                                </div>
                            </div>

                            <div class="lgw-switch {if $shop['option_variation'] == 1} checked{/if}">
                                <label>
                                    <div><span></span>
                                        <input
                                            type="checkbox"
                                            data-size="mini"
                                            data-on-text="{$locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}"
                                            data-off-text="{$locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}"
                                            name="lengow_export_selection"
                                            class="lengow_switch_option"
                                            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true)|escape:'htmlall':'UTF-8'}"
                                            data-action="change_option_product_variation"
                                            data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                                            value="1" {if $shop['option_variation'] == 1} checked="checked"{/if}
                                        >
                                    </div> {$locale->t('product.screen.include_variation')|escape:'htmlall':'UTF-8'}
                                </label>
                            </div>
                            <i
                                class="fa fa-info-circle lengow_link_tooltip"
                                title="{$locale->t('product.screen.include_variation_support')|escape:'htmlall':'UTF-8'}"></i><br>
                            <div class="lgw-switch {if $shop['option_product_out_of_stock'] == 1} checked{/if}">
                                <label>
                                    <div><span></span>
                                        <input
                                            type="checkbox"
                                            data-size="mini"
                                            data-on-text="{$locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}"
                                            data-off-text="{$locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}"
                                            name="lengow_export_selection"
                                            class="lengow_switch_option"
                                            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true)|escape:'htmlall':'UTF-8'}"
                                            data-action="change_option_product_out_of_stock"
                                            data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                                            value="1" {if $shop['option_product_out_of_stock'] == 1} checked="checked"{/if}
                                        >
                                    </div> {$locale->t('product.screen.include_out_of_stock')|escape:'htmlall':'UTF-8'}
                                </label>
                            </div>
                            <i class="fa fa-info-circle lengow_link_tooltip"
                                title="{$locale->t('product.screen.include_out_of_stock_support')|escape:'htmlall':'UTF-8'}"></i><br>
                            <div class="lgw-switch {if $shop['option_selected'] == 1} checked{/if}">
                                <label>
                                    <div><span></span>
                                        <input
                                            type="checkbox"
                                            data-size="mini"
                                            data-on-text="{$locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}"
                                            data-off-text="{$locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}"
                                            name="lengow_export_selection"
                                            class="lengow_switch_option"
                                            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed', true)|escape:'htmlall':'UTF-8'}"
                                            data-action="change_option_selected"
                                            data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                                            value="1" {if $shop['option_selected'] == 1} checked="checked"{/if}>
                                    </div> {$locale->t('product.screen.include_specific_product')|escape:'htmlall':'UTF-8'}
                                </label>
                            </div>
                            <i class="fa fa-info-circle lengow_link_tooltip"
                               title="{$locale->t('product.screen.include_specific_product_support')|escape:'htmlall':'UTF-8'}"></i>
                        </div>
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
