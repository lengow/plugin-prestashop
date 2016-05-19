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

<!-- <div id="lengow_statistic">
    <h2>{$locale->t('dashboard.screen.stat_with_lengow_you_make')|escape:'htmlall':'UTF-8'}</h2>
    <ul>
        <li>
            <span class="lengow_number">{$stats['total_order']|escape:'htmlall':'UTF-8'}</span>
            <span class="lengow_description">
                {$locale->t('dashboard.screen.stat_turnover')|escape:'htmlall':'UTF-8'}
            </span>
        </li>
        <li>
            <span class="lengow_number">{$stats['nb_order']|escape:'htmlall':'UTF-8'}</span>
            <span class="lengow_description">
                {$locale->t('dashboard.screen.stat_nb_orders')|escape:'htmlall':'UTF-8'}
            </span>
        </li>
        <li>
            <span class="lengow_number">{$stats['average_order']|escape:'htmlall':'UTF-8'}</span>
            <span class="lengow_description">
                {$locale->t('dashboard.screen.stat_avg_order')|escape:'htmlall':'UTF-8'}
            </span>
        </li>
    </ul>
    <a href="http://solution.lengow.com" target="_blank">
        {$locale->t('dashboard.screen.stat_more_stats')|escape:'htmlall':'UTF-8'}
    </a>
    <br/><br/>
    <div id="lengow_ads">
        <img src="http://fakeimg.pl/360x360/">
    </div>
</div> -->

<!-- <div id="lengow_dashboard_center">
    <div id="lengow_center_block">
        {$locale->t('dashboard.screen.good_job')|escape:'htmlall':'UTF-8'}<br/>
        <span class="lengow_pending_order">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder')|escape:'htmlall':'UTF-8'}">
                {$total_pending_order|escape:'htmlall':'UTF-8'} {$locale->t('dashboard.screen.pending_order')|escape:'htmlall':'UTF-8'}
            </a>
        </span>
        <span class="lengow_pending_message">
            {$locale->t('dashboard.screen.sell_everywhere')|escape:'htmlall':'UTF-8'}
        </span><br/>
    </div>
</div> -->

<div id="lengow_dashboard_center">
    <div id="lengow_center_block">
        <img src="/modules/lengow/views/img/lengow-white_big.png" alt="lengow">
        <p>Hi Johnny! Welcome back!</p>
        <!-- {$locale->t('dashboard.screen.good_job')|escape:'htmlall':'UTF-8'} --><br/>
        <!-- <span class="lengow_pending_order">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder')|escape:'htmlall':'UTF-8'}">
                {$total_pending_order|escape:'htmlall':'UTF-8'} {$locale->t('dashboard.screen.pending_order')|escape:'htmlall':'UTF-8'}
            </a>
        </span>
        <span class="lengow_pending_message">
            {$locale->t('dashboard.screen.sell_everywhere')|escape:'htmlall':'UTF-8'}
        </span><br/> -->
        <a href="http://solution.lengow.com" target="_blank" title="Lengow Solution"><button>Go to Lengow</button></a>
    </div>
</div>

<a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')|escape:'htmlall':'UTF-8'}">
    <div id="dashboard_lengow_products" class="lengow_insert_dashboard">
        <div style="height: 80px;width: 80px;background-color: #CDCDCD;border-radius: 50%;margin:0 auto;
            margin-top: 30px;"></div>
        <div class="dashboard_lengow_title">Products</div>
        <div style="font-size: 0.9em; margin-bottom:30px;">
                Take a look at all your products available to prepare your catalogue
        </div>
    </div>
</a>
<a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder')|escape:'htmlall':'UTF-8'}">
    <div id="dashboard_lengow_orders" class="lengow_insert_dashboard">
        <div id="notif_order">
            <div style="height: 80px;width: 80px;background-color: #CDCDCD;border-radius: 50%;margin:0 auto;
                margin-top: 30px;"></div>
            <span class="dashboard_lengow_title">
                {$total_pending_order|escape:'htmlall':'UTF-8'}
            </span>
            <div class="dashboard_lengow_title">Orders</div>
        </div>
        <div style="font-size: 0.9em; margin-bottom:30px;">
          Manage your orders directly on your PrestaShop dashboard
        </div>
    </div>
</a>
<a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|escape:'htmlall':'UTF-8'}">
<div id="dashboard_lengow_settings" class="lengow_insert_dashboard">
    <div style="height: 80px;width: 80px;background-color: #CDCDCD;border-radius: 50%;margin:0 auto;
        margin-top: 30px;"></div>
    <div class="dashboard_lengow_title">Settings</div>
    <div style="font-size: 0.9em; margin-bottom:30px;">
        Configure the main settings of your PrestaShop plugin
    </div>
</div>
</a>
<div id="dashboard_lengow_other_info" class="lengow_insert_dashboard">
    <div class="lengow_block_other_info">
        <h2>Lengow, partner of your business</h2>
        <ul style="font-size: 0.9em; margin-bottom:30px;">
            <li><span class="number_business">$23.345.339</span> {$locale->t('dashboard.screen.stat_turnover')|escape:'htmlall':'UTF-8'}</li>
            <li><span class="number_business">3.456.455</span> {$locale->t('dashboard.screen.stat_nb_orders')|escape:'htmlall':'UTF-8'}</li>
            <li><span class="number_business">$345</span> {$locale->t('dashboard.screen.stat_avg_order')|escape:'htmlall':'UTF-8'}</li>
        </ul>
        <a href="http://solution.lengow.com" target="_blank" title="Lengow solution">Want more stats ?</a>
    </div>
    <div class="lengow_block_other_info">
        <h2>Need some help?</h2>
        <p style="font-size: 0.9em; margin-bottom:30px;text-align:left;">
            <span>
                Nulla lorem tellus, cursus eget sagittis vitae, <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowHelp')|escape:'htmlall':'UTF-8'}" title="Need some help?">Need some help?</a>.
                <br/><br/>
                <a href="https://en.knowledgeowl.com/help/article/link/prestashopv2" target="_blank" title="Help Center">Visit our Help Center</a> for detailed information on how to configure properly your PrestaShop plugin
            </span>
        </p>
    </div>

</div>
<!--
<div id="lengow_statistic">
    <h2>{$locale->t('dashboard.screen.stat_with_lengow_you_make')|escape:'htmlall':'UTF-8'}</h2>
    <ul>
        <li>
            <span class="lengow_number">{$stats['total_order']|escape:'htmlall':'UTF-8'}</span>
            <span class="lengow_description">
                {$locale->t('dashboard.screen.stat_turnover')|escape:'htmlall':'UTF-8'}
            </span>
        </li>
        <li>
            <span class="lengow_number">{$stats['nb_order']|escape:'htmlall':'UTF-8'}</span>
            <span class="lengow_description">
                {$locale->t('dashboard.screen.stat_nb_orders')|escape:'htmlall':'UTF-8'}
            </span>
        </li>
        <li>
            <span class="lengow_number">{$stats['average_order']|escape:'htmlall':'UTF-8'}</span>
            <span class="lengow_description">
                {$locale->t('dashboard.screen.stat_avg_order')|escape:'htmlall':'UTF-8'}
            </span>
        </li>
    </ul>
    <a href="http://solution.lengow.com" target="_blank">
        {$locale->t('dashboard.screen.stat_more_stats')|escape:'htmlall':'UTF-8'}
    </a>

    <br/><br/>
     <div id="lengow_ads">
        <img src="http://fakeimg.pl/360x360/">
    </div> -->
</div>




<div class="lengow_clear"></div>
