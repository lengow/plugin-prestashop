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

<div id="lgw-connection-cms-result">
    {if $cmsConnected}
        <div class="lgw-content-section">
            <h2>{$locale->t('connection.cms.success_title')|escape:'htmlall':'UTF-8'}</h2>
        </div>
        <div class="lgw-module-illu mod-connected">
            <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/connected-prestashop.png"
                 class="lgw-module-illu-module mod-connected"
                 alt="">
            <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/connected-lengow.png"
                 class="lgw-module-illu-lengow mod-connected"
                 alt="">
            <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/connection-module.png"
                 class="lgw-module-illu-plug mod-connected"
                 alt="">
        </div>
        {if $hasCatalogToLink}
            <div class="lgw-content-section">
                <p>{$locale->t('connection.cms.success_description_first_catalog')|escape:'htmlall':'UTF-8'}</p>
                <p>{$locale->t('connection.cms.success_description_second_catalog')|escape:'htmlall':'UTF-8'}</p>
            </div>
            <div>
                <button class="lgw-btn lgw-btn-green js-go-to-catalog" data-retry="false">
                    {$locale->t('connection.cms.success_button_catalog')|escape:'htmlall':'UTF-8'}
                </button>
            </div>
        {else}
            <div class="lgw-content-section">
                <p>{$locale->t('connection.cms.success_description_first')|escape:'htmlall':'UTF-8'}</p>
                <p>
                    {$locale->t('connection.cms.success_description_second')|escape:'htmlall':'UTF-8'}
                    <a href="https://my.{$lengowUrl|escape:'htmlall':'UTF-8'}" target="_blank">
                        {$locale->t('connection.cms.success_description_second_go_to_lengow')|escape:'htmlall':'UTF-8'}
                    </a>
                </p>
            </div>
            <div>
                <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowDashboard')|escape:'htmlall':'UTF-8'}"
                   class="lgw-btn lgw-btn-green">
                    {$locale->t('connection.cms.success_button')|escape:'htmlall':'UTF-8'}
                </a>
            </div>
        {/if}
    {else}
        <div class="lgw-content-section">
            <h2>{$locale->t('connection.cms.failed_title')|escape:'htmlall':'UTF-8'}</h2>
        </div>
        <div class="lgw-module-illu mod-disconnected">
            <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/connected-prestashop.png"
                 class="lgw-module-illu-module mod-disconnected"
                 alt="">
            <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/connected-lengow.png"
                 class="lgw-module-illu-lengow mod-disconnected"
                 alt="">
            <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/unplugged.png"
                 class="lgw-module-illu-plug mod-disconnected"
                 alt="">
        </div>
        <div class="lgw-content-section">
            {if $credentialsValid}
                <p>{$locale->t('connection.cms.failed_description')|escape:'htmlall':'UTF-8'}</p>
            {else}
                <p>{$locale->t('connection.cms.failed_description_first_credentials')|escape:'htmlall':'UTF-8'}</p>
                {if $lengowUrl === 'lengow.net'}
                    <p>{$locale->t('connection.cms.failed_description_second_credentials_preprod')|escape:'htmlall':'UTF-8'}</p>
                {else}
                    <p>{$locale->t('connection.cms.failed_description_second_credentials_prod')|escape:'htmlall':'UTF-8'}</p>
                {/if}
            {/if}
            <p>
                {$locale->t('connection.cms.failed_help')|escape:'htmlall':'UTF-8'}
                <a href="{$locale->t('help.screen.knowledge_link_url')|escape:'htmlall':'UTF-8'}" target="_blank">
                    {$locale->t('connection.cms.failed_help_center')|escape:'htmlall':'UTF-8'}
                </a>
                {$locale->t('connection.cms.failed_help_or')|escape:'htmlall':'UTF-8'}
                <a href="{$locale->t('help.screen.link_lengow_support')|escape:'htmlall':'UTF-8'}" target="_blank">
                    {$locale->t('connection.cms.failed_help_customer_success_team')|escape:'htmlall':'UTF-8'}
                </a>
            </p>
        </div>
        <div>
            <button class="lgw-btn lgw-btn-green js-go-to-credentials">
                {$locale->t('connection.cms.failed_button')|escape:'htmlall':'UTF-8'}
            </button>
        </div>
    {/if}
</div>
