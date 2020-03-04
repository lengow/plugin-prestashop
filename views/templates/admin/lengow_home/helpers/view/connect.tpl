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
<div id="lengow_home_wrapper" class="cms-global">
    <div class="lgw-container">
        {if $lengow_configuration->debugModeIsActive()}
            <div id="lgw-debug" class="adminlengowhome">
                {$locale->t('menu.debug_active')|escape:'htmlall':'UTF-8'}
            </div>
        {/if}
        <div class="lgw-row">
            <div class="text-left lgw-col-6" id="alert-plugin-available">
                {if $pluginData && $pluginData['version'] > $lengowVersion}
                    {$locale->t('menu.new_version_available', ['version' => $pluginData['version']])|escape:'htmlall':'UTF-8'}
                    <a href="//my.{$lengowUrl|escape:'htmlall':'UTF-8'}{$pluginData['download_link']|escape:'htmlall':'UTF-8'}" target="_blank">
                        {$locale->t('menu.download_plugin')|escape:'htmlall':'UTF-8'}
                    </a>
                {/if}
            </div>
            <div class="text-right lgw-col-6" id="alert-counter-trial">
                {if $merchantStatus['type'] == 'free_trial' && !$merchantStatus['expired']}
                    {$locale->t('menu.counter', ['counter' => $merchantStatus['day']])|escape:'htmlall':'UTF-8'}
                    <a href="http://my.{$lengowUrl|escape:'htmlall':'UTF-8'}" target="_blank">
                        {$locale->t('menu.upgrade_account')|escape:'htmlall':'UTF-8'}
                    </a>
                {/if}
            </div>
        </div>
        <div class="lgw-box lgw-home-header text-center">
            <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/lengow-white-big.png" alt="lengow">
            <h1>{$locale->t('dashboard.screen.welcome_back')|escape:'htmlall':'UTF-8'}</h1>
            <a href="//my.{$lengowUrl|escape:'htmlall':'UTF-8'}" class="lgw-btn" target="_blank">
                {$locale->t('dashboard.screen.go_to_lengow')|escape:'htmlall':'UTF-8'}
            </a>
        </div>
        <div class="lgw-row lgw-home-menu text-center">
            <div class="lgw-col-4">
                <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')|escape:'htmlall':'UTF-8'}" class="lgw-box-link">
                    <div class="lgw-box">
                        <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/home-products.png" class="img-responsive">
                        <h2>{$locale->t('dashboard.screen.products_title')|escape:'htmlall':'UTF-8'}</h2>
                        <p>{$locale->t('dashboard.screen.products_text')|escape:'htmlall':'UTF-8'}</p>
                    </div>
                </a>
            </div>
            <div class="lgw-col-4">
                <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder')|escape:'htmlall':'UTF-8'}" class="lgw-box-link">
                    <div class="lgw-box">
                        <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/home-orders.png" class="img-responsive">
                        <h2>
                            {$locale->t('dashboard.screen.orders_title')|escape:'htmlall':'UTF-8'}
                            {if $total_pending_order > 0}
                                <span class="lgw-label lgw-label red">{$total_pending_order|escape:'htmlall':'UTF-8'}</span>
                            {/if}
                        </h2>
                        <p>{$locale->t('dashboard.screen.orders_text')|escape:'htmlall':'UTF-8'}</p>
                    </div>
                </a>
            </div>
            <div class="lgw-col-4">
                <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|escape:'htmlall':'UTF-8'}" class="lgw-box-link">
                    <div class="lgw-box">
                        <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/home-settings.png" class="img-responsive">
                        <h2>{$locale->t('dashboard.screen.settings_title')|escape:'htmlall':'UTF-8'}</h2>
                        <p>{$locale->t('dashboard.screen.settings_text')|escape:'htmlall':'UTF-8'}</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="lgw-box">
            <h2>{$locale->t('dashboard.screen.some_help_title')|escape:'htmlall':'UTF-8'}</h2>
            <p>
                <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowHelp')|escape:'htmlall':'UTF-8'}">
                    {$locale->t('dashboard.screen.get_in_touch')|escape:'htmlall':'UTF-8'}
                </a>
            </p>
            <p>
                <a href="{$locale->t('help.screen.knowledge_link_url')|escape:'htmlall':'UTF-8'}" target="_blank">{$locale->t('dashboard.screen.visit_help_center')|escape:'htmlall':'UTF-8'}</a>
                {$locale->t('dashboard.screen.configure_plugin')|escape:'htmlall':'UTF-8'}
            </p>
        </div>
    </div>
</div>