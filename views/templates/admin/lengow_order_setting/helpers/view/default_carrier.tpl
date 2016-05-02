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

<div class="lengow_default_carrier"
     data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting', true)|escape:'htmlall':'UTF-8'}"
     id="lengow_country_{$defaultCarrierCountries[$id_country]['lengow_country_id']|escape:'htmlall':'UTF-8'}">
    <p>{$locale->t('order_setting.screen.default_carrier')|escape:'htmlall':'UTF-8'}</p>
    <select name="default_carrier[{$defaultCarrierCountries[$id_country]['lengow_country_id']|escape:'htmlall':'UTF-8'}]" class="carrier defaultCarrier lengow_select">
        <option value=""></option>
        {foreach from=$listCarrierByCountry[$id_country] key=k item=c}
            {if $defaultCarrierCountries[$id_country]['id_carrier'] eq $k}
                <option value="{$k|escape:'htmlall':'UTF-8'}" selected="selected">{$c|escape:'htmlall':'UTF-8'}</option>
            {else}
                <option value="{$k|escape:'htmlall':'UTF-8'}">{$c|escape:'htmlall':'UTF-8'}</option>
            {/if}
        {/foreach}
    </select>
    <div class="default_carrier_missing" style="display:none;">
        {$locale->t('order_setting.screen.no_default_carrier_selected')|escape:'htmlall':'UTF-8'}
    </div>
</div>