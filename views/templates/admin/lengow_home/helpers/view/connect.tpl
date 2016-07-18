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

<div class="lgw-container">
    {if $lengow_configuration->getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED') eq '1'}
        <div id="lgw-preprod" class="adminlengowhome">
            {$locale->t('menu.preprod_active')|escape:'htmlall':'UTF-8'}
        </div>
    {/if}
    {if $merchantStatus['type'] == 'free_trial' && $merchantStatus['day'] neq 0}
        <span class="lengow_float_right" id="menucountertrial">
            {$locale->t('menu.counter', ['counter' => $merchantStatus['day']])|escape:'htmlall':'UTF-8'}
            <a href="http://www.lengow.com/" target="_blank">
                {$locale->t('menu.upgrade_account')|escape:'htmlall':'UTF-8'}
            </a>
        </span>
    {/if}
    <div class="lgw-box lgw-home-header text-center">
        <img src="/modules/lengow/views/img/lengow-white-big.png" alt="lengow">
        <h1>{$locale->t('dashboard.screen.welcome_back')|escape:'htmlall':'UTF-8'}</h1>
        <a href="http://solution.lengow.com" class="lgw-btn" target="_blank">
            {$locale->t('dashboard.screen.go_to_lengow')|escape:'htmlall':'UTF-8'}
        </a>
    </div>
    <div class="lgw-row lgw-home-menu text-center">
        <div class="lgw-col-4">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')|escape:'htmlall':'UTF-8'}" class="lgw-box-link">
                <div class="lgw-box">
                    <img src="/modules/lengow/views/img/home-products.png" class="img-responsive">
                    <h2>{$locale->t('dashboard.screen.products_title')|escape:'htmlall':'UTF-8'}</h2>
                    <p>{$locale->t('dashboard.screen.products_text')|escape:'htmlall':'UTF-8'}</p>
                </div>
            </a>
        </div>
        <div class="lgw-col-4">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder')|escape:'htmlall':'UTF-8'}" class="lgw-box-link">
                <div class="lgw-box">
                     <img src="/modules/lengow/views/img/home-orders.png" class="img-responsive">
                    <h2>
                        {$locale->t('dashboard.screen.orders_title')|escape:'htmlall':'UTF-8'}
                        <span class="lgw-label lgw-label_red">{$total_pending_order|escape:'htmlall':'UTF-8'}</span>
                    </h2>
                    <p>{$locale->t('dashboard.screen.orders_text')|escape:'htmlall':'UTF-8'}</p>
                </div>
            </a>
        </div>
        <div class="lgw-col-4">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|escape:'htmlall':'UTF-8'}" class="lgw-box-link">
                <div class="lgw-box">
                     <img src="/modules/lengow/views/img/home-settings.png" class="img-responsive">
                    <h2>{$locale->t('dashboard.screen.settings_title')|escape:'htmlall':'UTF-8'}</h2>
                    <p>{$locale->t('dashboard.screen.settings_text')|escape:'htmlall':'UTF-8'}</p>
                </div>
            </a>
        </div>
    </div>
    <div class="lgw-box text-center">
        <h2>{$locale->t('dashboard.screen.partner_business')|escape:'htmlall':'UTF-8'}</h2>
        <div class="lgw-row lgw-home-stats">
            <div class="lgw-col-6">
                <h5>{$locale->t('dashboard.screen.stat_turnover')|escape:'htmlall':'UTF-8'}</h5>
                <span class="stats-big-value">{$stats['total_order']|escape:'htmlall':'UTF-8'}</span>
            </div>
            <div class="lgw-col-6">
                <h5>{$locale->t('dashboard.screen.stat_nb_orders')|escape:'htmlall':'UTF-8'}</h5>
                <span class="stats-big-value">{$stats['nb_order']|escape:'htmlall':'UTF-8'}</span>
            </div>
        </div>
        <p>
            <a href="http://solution.lengow.com" target="_blank" class="lgw-btn lgw-btn-white">
                {$locale->t('dashboard.screen.stat_more_stats')|escape:'htmlall':'UTF-8'}
            </a>
        </p>
    </div>
    <div class="lgw-box">
        <h2>{$locale->t('dashboard.screen.some_help_title')|escape:'htmlall':'UTF-8'}</h2>
        <p>
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowHelp')|escape:'htmlall':'UTF-8'}">
                {$locale->t('dashboard.screen.get_in_touch')|escape:'htmlall':'UTF-8'}
            </a>
        </p>
        <p>
            <a href="https://en.knowledgeowl.com/help/article/link/prestashopv2" target="_blank">{$locale->t('dashboard.screen.visit_help_center')|escape:'htmlall':'UTF-8'}</a>
            {$locale->t('dashboard.screen.configure_plugin')|escape:'htmlall':'UTF-8'}
        </p>
    </div>
</div>
