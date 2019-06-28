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
<li>
    <div class="lgw-row">
        <span class="matching-subtitle">
            {$locale->t('order_setting.screen.matching_method_marketplace')|escape:'htmlall':'UTF-8'}
        </span>
    </div>
</li>
{foreach from=$marketplace['methods'] item=method}
    <li>
        <div class="lgw-row">
            <div class="lgw-col-5 text-right carrier-name">
                {$method['method_marketplace_label']|escape:'htmlall':'UTF-8'}
            </div>
            <div class="lgw-col-1">
                <div class="lgw-arrow-right"></div>
            </div>
            <div class="lgw-col-6">
                <select name="method_marketplaces[{$marketplace['id']|escape:'htmlall':'UTF-8'}][{$method['id_method_marketplace']|escape:'htmlall':'UTF-8'}]"
                        class="carrier lengow_select"
                        data-marketplace="{$marketplace['id']|escape:'htmlall':'UTF-8'}">
                    <option value="">{$locale->t('order_setting.screen.please_select_carrier_prestashop')|escape:'htmlall':'UTF-8'}</option>
                    {foreach from=$carriers key=idCarrier item=carrier}
                        {if isset($marketplace['method_matched'][$method['id_method_marketplace']]) && $marketplace['method_matched'][$method['id_method_marketplace']] eq $idCarrier}
                            <option value="{$idCarrier|escape:'htmlall':'UTF-8'}" selected="selected">{$carrier['name']|escape:'htmlall':'UTF-8'}</option>
                        {else}
                            <option value="{$idCarrier|escape:'htmlall':'UTF-8'}">{$carrier['name']|escape:'htmlall':'UTF-8'}</option>
                        {/if}
                    {/foreach}
                </select>
            </div>
        </div>
    </li>
{/foreach}
