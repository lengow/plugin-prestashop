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

{foreach from=$defaultCarrierCountries key=id_country item=values}
    <li class="has-sub lengow_marketplace_carrier"
        id="lengow_marketplace_carrier_country_{$id_country|escape:'htmlall':'UTF-8'}">
        <div class="country">
            <label for="menu{$id_country|escape:'htmlall':'UTF-8'}">
            <img src="/modules/lengow/views/img/flag/{$values["iso_code"]|escape:'htmlall':'UTF-8'}.png" alt="{$values["name"]|escape:'htmlall':'UTF-8'}">
            {$values["name"]|escape:'htmlall':'UTF-8'}
            {if $id_country eq $default_country}
                <span>({$locale->t('order_setting.screen.default_country')|escape:'htmlall':'UTF-8'})</span>
            {else}
                <button type="button" class="btn delete_lengow_default_carrier"
                        data-message="{$locale->t('order_setting.screen.confirmation_delete_carrier_country')|escape:'htmlall':'UTF-8'}"
                        data-id-country="{$id_country|escape:'htmlall':'UTF-8'}">
                </button>
                <div class="delete-country-confirm">Are you sure?<a href="#" class="js-delete-country-yes">Yes</a><a href="#" class="js-delete-country-no">No</a></div>
            {/if}
            <span class="score lgw-label"></span><i class="fa fa-chevron-down"></i>
        </div>
        </label><input id="menu{$id_country|escape:'htmlall':'UTF-8'}" name="menu" type="checkbox"/>
        <ul class="sub" style="display:none">
            <li class="add_country {if empty($defaultCarrierCountries[$id_country]['id_carrier']|escape:'htmlall':'UTF-8')}no_carrier{/if}">
                {include file='./default_carrier.tpl'}
            </li>
            {if isset($marketplace_carriers[$id_country])}
                {foreach from=$marketplace_carriers[$id_country] item=marketplace_carrier}
                    <li class="marketplace_carrier {if empty({$marketplace_carrier['id_carrier']|escape:'htmlall':'UTF-8'})}no_carrier{/if}">
                        <p>{$marketplace_carrier['marketplace_carrier_name']|escape:'htmlall':'UTF-8'}</p>
                        <select name="default_marketplace_carrier[{$marketplace_carrier["id"]|escape:'htmlall':'UTF-8'}]" class="carrier lengow_select">
                            <option value=""></option>
                            {foreach from=$listCarrierByCountry[$id_country] key=k item=c}
                                {if {$marketplace_carrier["id_carrier"]} eq $k}
                                    <option value="{$k|escape:'htmlall':'UTF-8'}" selected="selected">{$c|escape:'htmlall':'UTF-8'}</option>
                                {else}
                                    <option value="{$k|escape:'htmlall':'UTF-8'}">{$c|escape:'htmlall':'UTF-8'}</option>
                                {/if}
                            {/foreach}
                        </select>
                    </li>
                {/foreach}
            {/if}
            <div class="clearfix"></div>
        </ul>
        <div class="clearfix"></div>
    </li>
{/foreach}