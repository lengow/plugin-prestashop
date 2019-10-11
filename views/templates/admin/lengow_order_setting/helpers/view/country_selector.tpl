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
<h2>
    {$locale->t('order_setting.screen.carrier_management_title')|escape:'htmlall':'UTF-8'}
</h2>
<p>{$locale->t('order_setting.screen.carrier_management_description')|escape:'htmlall':'UTF-8'}</p>
<div class="lgw-row text-center" id="country-list">
    {if isset($countries)}
        {foreach from=$countries item=country}
            <div class="lgw-col-3">
                <a href="#" class="js-lengow-open-matching lgw-box-link-matching"
                   data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting', true)|escape:'htmlall':'UTF-8'}"
                   data-id-country="{$country['id_country']|escape:'htmlall':'UTF-8'}">
                    <div class="lgw-box">
                        {if isset($defaultCarrierNotMatched[{$country['id_country']}]) && $defaultCarrierNotMatched[{$country['id_country']}]|count > 0}
                            <span class="alert-matching"></span>
                        {/if}
                        <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/flag/{$country['iso_code']|escape:'htmlall':'UTF-8'}.png"
                             alt="{$country['name']|escape:'htmlall':'UTF-8'}">
                        <p>{$country['name']|upper|escape:'htmlall':'UTF-8'}</p>
                        <p class="small light">
                            {$marketplaceCounters[{$country['id_country']|escape:'htmlall':'UTF-8'}]|escape:'htmlall':'UTF-8'} {$locale->t('order_setting.screen.marketplace')|escape:'htmlall':'UTF-8'}
                        </p>
                    </div>
                </a>
            </div>
        {/foreach}
    {/if}
</div>
