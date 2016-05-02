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
    <div class="lgw-box lgw-home-header text-center">
        <img src="/modules/lengow/views/img/lengow-white-big.png" alt="lengow">
        <h1>Hi Johnny! Welcome back!</h1>
        <a href="http://solution.lengow.com" class="lgw-btn" target="_blank" title="Lengow Solution">Go to Lengow</a>
    </div>
    <div class="lgw-row lgw-home-menu text-center">
        <div class="lgw-col-4">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')|escape:'htmlall':'UTF-8'}" class="lgw-box-link">
                <div class="lgw-box">
                    <img src="/modules/lengow/views/img/home-products.png" class="img-responsive">
                    <h2>Products</h2>
                    <p>Take a look at all your products available to prepare your catalogue</p>
                </div>
            </a>
        </div>
        <div class="lgw-col-4">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder')|escape:'htmlall':'UTF-8'}" class="lgw-box-link">
                <div class="lgw-box">
                     <img src="/modules/lengow/views/img/home-orders.png" class="img-responsive">
                    <h2>Orders <span class="lgw-label lgw-label_red">{$total_pending_order|escape:'htmlall':'UTF-8'}</span></h2>
                    <p>Manage your orders directly on your PrestaShop dashboard</p>
                </div>
            </a>
        </div>
        <div class="lgw-col-4">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|escape:'htmlall':'UTF-8'}" class="lgw-box-link">
                <div class="lgw-box">
                     <img src="/modules/lengow/views/img/home-settings.png" class="img-responsive">
                    <h2>Settings</h2>
                    <p>Configure the main settings of your PrestaShop plugin</p>
                </div>
            </a>
        </div>
    </div>
    <div class="lgw-box text-center">
        <h3>Lengow, partner of your business</h3>
        <div class="lgw-row margin-standard">
            <div class="lgw-col-4">
                <h5>{$locale->t('dashboard.screen.stat_turnover')|escape:'htmlall':'UTF-8'}</h5>
                <span class="stats-big-value">$23.345.339</span>
            </div>
            <div class="lgw-col-4">
                <h5>{$locale->t('dashboard.screen.stat_nb_orders')|escape:'htmlall':'UTF-8'}</h5>
                <span class="stats-big-value">3.456.455</span>
            </div>
            <div class="lgw-col-4">
                <h5>{$locale->t('dashboard.screen.stat_avg_order')|escape:'htmlall':'UTF-8'}</h5>
                <span class="stats-big-value">$345</span>
            </div>
        </div>
        <p><a href="http://solution.lengow.com" target="_blank" title="Lengow solution" class="lgw-btn lgw-btn-white">Want more stats ?</a></p>
    </div>
    <div class="lgw-box">
        <h3>Need some help?</h3>
        <p><a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowHelp')|escape:'htmlall':'UTF-8'}" title="Need some help?">Get in touch with Lengow.</a></p>
        <p><a href="https://en.knowledgeowl.com/help/article/link/prestashopv2" target="_blank" title="Help Center" class="sub-link">Visit our Help Center</a> for detailed information on how to configure properly your PrestaShop plugin</p>

        </div>
    </div>

