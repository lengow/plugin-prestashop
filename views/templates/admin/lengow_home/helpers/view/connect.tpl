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
    </div>
</div>

<div id="lengow_dashboard_center">
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
</div>


<div class="lengow_clear"></div>