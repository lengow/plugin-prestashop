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
<a href="#" class="js-lengow-back-country ">
    <i class="fa fa-angle-left"></i>
    {$locale->t('order_setting.screen.see_delivery_countries')|escape:'htmlall':'UTF-8'}
</a>
<h2>
    <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/flag/{$country['iso_code']|escape:'htmlall':'UTF-8'}.png" alt="{$country['name']|escape:'htmlall':'UTF-8'}" class="flag">
    {$locale->t('order_setting.screen.marketplace_matching_title', ['country_name' => $country['name']])|escape:'htmlall':'UTF-8'}
</h2>
<p>
    {$locale->t('order_setting.screen.marketplace_matching_description', ['country_name' => $country['name']])|escape:'htmlall':'UTF-8'}
    {if isset($carriers) && $carriers|@count > 0}
        {$locale->t('order_setting.screen.country_wt_carrier')|escape:'htmlall':'UTF-8'}
        <a href="{$lengow_link->getAbsoluteAdminLink('AdminCarriers', false, true)|escape:'htmlall':'UTF-8'}">
            {$locale->t('order_setting.screen.please_setup_then')|escape:'htmlall':'UTF-8'}
        </a>
    {/if}
</p>
<input type="hidden" name="id_country" value="{$country['id_country']|escape:'htmlall':'UTF-8'}">
{if isset($carriers) && $carriers|@count > 0}
    <div id="marketplace-list" data-tooltip-carrier="{$locale->t('order_setting.screen.tooltip_default_carrier')|escape:'htmlall':'UTF-8'}">
        <ul class="accordion">
            {if isset($marketplaces)}
                {foreach from=$marketplaces item=marketplace}
                    <li class="has-sub lengow_marketplace" data-marketplace="{$marketplace['id']|escape:'htmlall':'UTF-8'}">
                        <div class="js-marketplace marketplace">
                            <span class="marketplace-name">{$marketplace['label']|escape:'htmlall':'UTF-8'}</span>
                            {if !$marketplace['id_carrier']}
                                <span class="alert-matching"></span>
                            {/if}
                            <span class="score lgw-label"></span>
                        </div>
                        <ul class="sub" style="display:none">
                            <li>
                                {include file='./default_carrier.tpl'}
                            </li>
                            {if isset($carriers) && isset($marketplace['methods']) && $marketplace['methods']|@count > 0}
                                {include file='./marketplace_method.tpl'}
                            {/if}
                            {if isset($carriers) && isset($marketplace['carriers']) && $marketplace['carriers']|@count > 0}
                                {include file='./marketplace_carrier.tpl'}
                            {/if}
                        </ul>
                        <div class="clearfix"></div>
                    </li>
                {/foreach}
            {/if}
        </ul>
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
    <a href="#" class="js-lengow-back-country cancel">
        {$locale->t('order_setting.screen.button_cancel')|escape:'htmlall':'UTF-8'}
    </a>
{else}
    <div class="legend blue-frame alert-carrier">
        {$locale->t('order_setting.screen.no_carrier_enabled', ['country_name' => $country['name']])|escape:'htmlall':'UTF-8'}
        <a href="{$lengow_link->getAbsoluteAdminLink('AdminCarriers', false, true)|escape:'htmlall':'UTF-8'}">
            {$locale->t('order_setting.screen.please_setup_then')|escape:'htmlall':'UTF-8'}
        </a>
    </div>
{/if}