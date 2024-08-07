{*
 * Copyright 2017 Lengow SAS.
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
 *  @copyright 2017 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}

<div class="cms-global" id="lengow_feed_wrapper">
    <div class="lgw-container">
        {if $debugMode}
            <div id="lgw-debug" class="adminlengowfeed {if $multiShop}multi-shop{/if}">
                {$locale->t('menu.debug_active')|escape:'htmlall':'UTF-8'}
            </div>
        {/if}
        {foreach from=$shopCollection item=shop}
            <div class="filter-column">
                <div class="sticky-icon" data-shop-id="{$shop['shop']->id|escape:'htmlall':'UTF-8'}" id="sticky-icon-{$shop['shop']->id|escape:'htmlall':'UTF-8'}">
                    <a title="{$locale->t('product.screen.filter_exported')|escape:'htmlall':'UTF-8'}">
                        <i style="font-size: 20px;color: #555" class="fa fa-filter"></i>
                    </a>
                </div>
            </div>
            <div class="lgw-box" id="block_{$shop['shop']->id|escape:'htmlall':'UTF-8'}">
                <a href="{$shop['link']|escape:'htmlall':'UTF-8'}&stream=1&update_export_date=0"
                   class="lengow_export_feed lengow_link_tooltip"
                   data-original-title="{$locale->t('product.screen.button_download')|escape:'htmlall':'UTF-8'}"
                   target="_blank"><i class="fa fa-download"></i></a>
                <h2 class="text-center catalog-title lengow_link_tooltip"
                    data-original-title="{$shop['shop']->name|escape:'htmlall':'UTF-8'} ({$shop['shop']->id|escape:'htmlall':'UTF-8'})
                    {$shop['shop']->domain|escape:'htmlall':'UTF-8'}">
                    {$shop['shop']->name|escape:'htmlall':'UTF-8'}
                </h2>
                <div class="text-center">
                    <div class="margin-standard text-center">
                        <p class="products-exported">
                            <span class="lengow_exported stats-big-value">{$shop['total_export_product']|escape:'htmlall':'UTF-8'}</span>
                            {$locale->t('product.screen.nb_exported')|escape:'htmlall':'UTF-8'}
                        </p>
                        <p class="products-available small light">
                            <span class="lengow_total stats-big-value">{$shop['total_product']|escape:'htmlall':'UTF-8'}</span>
                            {$locale->t('product.screen.nb_available')|escape:'htmlall':'UTF-8'}
                        </p>
                    </div>
                    <hr>
                    <div class="sticky-switch{$shop['shop']->id|escape:'htmlall':'UTF-8'} sticky-switch" data-shop-id="{$shop['shop']->id|escape:'htmlall':'UTF-8'}" id="sticky-switch-{$shop['shop']->id|escape:'htmlall':'UTF-8'}">
                        <div class="switch-row variation-row">
                            <div class="lgw-switch switch-variation {if $shop['option_selected__variation'] == 1} checked{/if}">
                                <label>
                                    <div><span></span>
                                        <input
                                                type="checkbox"
                                                data-size="mini"
                                                data-on-text="{$locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}"
                                                data-off-text="{$locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}"
                                                name="lengow_export_variation"
                                                class="lengow_switch_option option-variation-{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                                                data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')|escape:'htmlall':'UTF-8'}"
                                                data-action="change_option_selected__variation"
                                                data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                                                value="1" {if $shop['option_selected__variation'] == 1} checked="checked"{/if}
                                        >
                                    </div> {$locale->t('product.screen.include_variation_product')|escape:'htmlall':'UTF-8'}
                                </label>
                            </div>

                        </div>
                        <br>
                        <div class="switch-row">
                            <div class="lgw-switch switch-out-of-stock {if $shop['option_selected__out_of_stock'] == 1} checked{/if}">
                                <label>
                                    <div><span></span>
                                        <input
                                                type="checkbox"
                                                data-size="mini"
                                                data-on-text="{$locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}"
                                                data-off-text="{$locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}"
                                                name="lengow_export_out_of_stock"
                                                class="lengow_switch_option option-out-of-stock-{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                                                data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')|escape:'htmlall':'UTF-8'}"
                                                data-action="change_option_selected__out_of_stock"
                                                data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                                                value="1" {if $shop['option_selected__out_of_stock'] == 1} checked="checked"{/if}
                                        >
                                    </div> {$locale->t('product.screen.include_out_of_stock_product')|escape:'htmlall':'UTF-8'}
                                </label>
                            </div>

                        </div>
                        <br>
                        <div class="switch-row">
                            <div class="lgw-switch switch-inactive {if $shop['option_selected__inactive'] == 1} checked{/if}">
                                <label>
                                    <div><span></span>
                                        <input
                                                type="checkbox"
                                                data-size="mini"
                                                data-on-text="{$locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}"
                                                data-off-text="{$locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}"
                                                name="lengow_export_inactive"
                                                class="lengow_switch_option option-inactive-{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                                                data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')|escape:'htmlall':'UTF-8'}"
                                                data-action="change_option_selected__inactive"
                                                data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                                                value="1" {if $shop['option_selected__inactive'] == 1} checked="checked"{/if}
                                        >
                                    </div> {$locale->t('product.screen.include_inactive_product')|escape:'htmlall':'UTF-8'}
                                </label>
                            </div>

                        </div>
                        <br>
                        <div class="switch-row">
                            <div class="lgw-switch switch-selection {if $shop['option_selected__selection'] == 1} checked{/if}">
                                <label>
                                    <div><span></span>
                                        <input
                                                type="checkbox"
                                                data-size="mini"
                                                data-on-text="{$locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}"
                                                data-off-text="{$locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}"
                                                name="lengow_export_selection"
                                                class="lengow_switch_option option-selection-{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                                                data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')|escape:'htmlall':'UTF-8'}"
                                                data-action="change_option_selected__selection"
                                                data-id_shop="{$shop['shop']->id|escape:'htmlall':'UTF-8'}"
                                                value="1" {if $shop['option_selected__selection'] == 1} checked="checked"{/if}
                                        >
                                    </div> {$locale->t('product.screen.include_specific_product')|escape:'htmlall':'UTF-8'}
                                </label>
                            </div>
                            <i class="fa fa-info-circle lengow_link_tooltip"
                               title="{$locale->t('product.screen.include_specific_product_support')|escape:'htmlall':'UTF-8'}"></i>
                        </div>
                    </div>
                </div>
                <div class="lengow_feed_block_footer">
                    <div class="lengow_feed_block_footer_content" style="{if !$shop['option_selected__selection']}display:none;{/if}">
                        {html_entity_decode($shop['list']|escape:'htmlall':'UTF-8')}
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
</div>
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/lengow/feed.js; ?>"></script>
