{*
 * Copyright 2021 Lengow SAS.
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
 *  @copyright 2021 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}

<div class="lgw-content-section">
    <h2>{$locale->t('connection.catalog.link_title')|escape:'htmlall':'UTF-8'}</h2>
    <p>{$locale->t('connection.catalog.link_description')|escape:'htmlall':'UTF-8'}</p>
    <p>
        <span>{$catalogList|@count|escape:'htmlall':'UTF-8'}</span>
        {$locale->t('connection.catalog.link_catalog_avalaible')|escape:'htmlall':'UTF-8'}
    </p>
</div>
<div>
    {foreach from=$shopCollection item=shop}
        <div class="lgw-catalog-select">
            <label class="control-label" for="select_catalog_{$shop->id|escape:'htmlall':'UTF-8'}">
                {$shop->name|escape:'htmlall':'UTF-8'}
            </label>
            <select class="form-control lengow_select js-catalog-linked"
                    id="select_catalog_{$shop->id|escape:'htmlall':'UTF-8'}"
                    name="{$shop->id|escape:'htmlall':'UTF-8'}"
                    multiple="multiple"
                    data-placeholder="{$locale->t('connection.catalog.link_placeholder_catalog')|escape:'htmlall':'UTF-8'}"
                    data-allow-clear="true">
                {foreach from=$catalogList item=catalog}
                    <option value="{$catalog['value']|escape:'htmlall':'UTF-8'}">
                        {$catalog['label']|escape:'htmlall':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </div>
    {/foreach}
</div>
<div>
    <button class="lgw-btn lgw-btn-green lgw-btn-progression js-link-catalog">
        <div class="btn-inner">
            <div class="btn-step default">
                {$locale->t('connection.catalog.link_button')|escape:'htmlall':'UTF-8'}
            </div>
            <div class="btn-step loading">
                {$locale->t('global_setting.screen.setting_saving')|escape:'htmlall':'UTF-8'}
            </div>
        </div>
    </button>
</div>
