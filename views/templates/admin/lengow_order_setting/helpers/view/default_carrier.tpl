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

<div class="lgw-row">
    <div class="lengow_default_carrier js-default-carrier lgw-col-6">
        <p>
            {$locale->t('order_setting.screen.default_carrier_prestashop')|escape:'htmlall':'UTF-8'}
            <span class="label-required">*<span>
        </p>
        <select name="default_carriers[{$marketplace['id']|escape:'htmlall':'UTF-8'}][carrier]" class="carrier lengow_select required">
            <option value="">{$locale->t('order_setting.screen.please_select_carrier_prestashop')|escape:'htmlall':'UTF-8'}</option>
            {foreach from=$carriers key=idCarrier item=carrier}
                {if $marketplace['id_carrier'] eq $idCarrier}
                    <option value="{$idCarrier|escape:'htmlall':'UTF-8'}" selected="selected">{$carrier['name']|escape:'htmlall':'UTF-8'}</option>
                {else}
                    <option value="{$idCarrier|escape:'htmlall':'UTF-8'}">{$carrier['name']|escape:'htmlall':'UTF-8'}</option>
                {/if}
            {/foreach}
        </select>
        <div class="default_carrier_missing" style="display:none;">
            {$locale->t('order_setting.screen.no_default_carrier_selected')|escape:'htmlall':'UTF-8'}
        </div>
    </div>
    {if isset($marketplace['carriers']) && $marketplace['carriers']|@count > 0}
        <div class="lengow_default_carrier_marketplace js-default-carrier lgw-col-6">
            <p>
                {$locale->t('order_setting.screen.default_carrier_marketplace', ['marketplace_name' => $marketplace['label']])|escape:'htmlall':'UTF-8'}
                {if $marketplace['carrier_required']}
                    <span class="label-required">*<span>
                {/if}
            </p>
            <select name="default_carriers[{$marketplace['id']|escape:'htmlall':'UTF-8'}][carrier_marketplace]" class="carrier lengow_select {if $marketplace['carrier_required']}required{/if}">
                <option value="">{$locale->t('order_setting.screen.please_select_carrier_marketplace', ['marketplace_name' => $marketplace['label']])|escape:'htmlall':'UTF-8'}</option>
                {foreach from=$marketplace['carriers'] item=marketplaceCarrier}
                    {if $marketplace['id_carrier_marketplace'] eq $marketplaceCarrier['id_carrier_marketplace']}
                        <option value="{$marketplaceCarrier['id_carrier_marketplace']|escape:'htmlall':'UTF-8'}" selected="selected">{$marketplaceCarrier['carrier_marketplace_label']|escape:'htmlall':'UTF-8'}</option>
                    {else}
                        <option value="{$marketplaceCarrier['id_carrier_marketplace']|escape:'htmlall':'UTF-8'}">{$marketplaceCarrier['carrier_marketplace_label']|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                {/foreach}
            </select>
            {if $marketplace['carrier_required']}
                <div class="default_carrier_missing" style="display:none;">
                    {$locale->t('order_setting.screen.no_default_carrier_marketplace_selected')|escape:'htmlall':'UTF-8'}
                </div>
            {/if}
        </div>
    {/if}
</div>
