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
    <div class="lgw-box">
        <div class="lgw-row">
        {if $merchantStatus['type'] == 'free_trial' && $merchantStatus['day'] eq 0}
            <div class="lgw-col-6 display-inline-block">
                <h2 class="text-center">{$locale->t('status.screen.title_end_free_trial')|escape:'htmlall':'UTF-8'}</h2>
                <h3 class="text-center">{$locale->t('status.screen.subtitle_end_free_trial')|escape:'htmlall':'UTF-8'}</h3>
                <p class="text-center">{$locale->t('status.screen.first_description_end_free_trial')|escape:'htmlall':'UTF-8'}</p>
                <p class="text-center">{$locale->t('status.screen.second_description_end_free_trial')|escape:'htmlall':'UTF-8'}</p>
                <p class="text-center">{html_entity_decode($locale->t('status.screen.third_description_end_free_trial')|escape:'htmlall':'UTF-8')}</p>
                <div class="text-center">
                    <a href="http://my.lengow.io/" class="lgw-btn" target="_blank">
                        {$locale->t('status.screen.upgrade_account_button')|escape:'htmlall':'UTF-8'}
                    </a>
                </div>
                <div class="text-center">
                    <a href="{$refresh_status|escape:'htmlall':'UTF-8'}"
                       class="lgw-box-link">
                        {$locale->t('status.screen.refresh_action')|escape:'htmlall':'UTF-8'}
                    </a>
                </div>
            </div>
            <div class="lgw-col-6">
                <div class="vertical-center">
                    <img src="/modules/lengow/views/img/logo-blue.png" class="center-block" alt="lengow"/>
                </div>
            </div>
        {else}
            <div class="lgw-col-6">
                <h3 class="text-center">{$locale->t('status.screen.subtitle_bad_payer')|escape:'htmlall':'UTF-8'}</h3>
                <p class="text-center">{$locale->t('status.screen.first_description_bad_payer')|escape:'htmlall':'UTF-8'}</p>
                <p class="text-center">{$locale->t('status.screen.second_description_bad_payer')|escape:'htmlall':'UTF-8'}
                    <a href="mailto:backoffice@lengow.com">backoffice@lengow.com</a>
                    {$locale->t('status.screen.phone_bad_payer')|escape:'htmlall':'UTF-8'}
                </p>
                <p class="text-center">{$locale->t('status.screen.third_description_bad_payer')|escape:'htmlall':'UTF-8'}</p>
                <div class="text-center">
                    <a href="http://my.lengow.io/" class="lgw-btn" target="_blank">
                        {$locale->t('status.screen.facturation_button')|escape:'htmlall':'UTF-8'}
                    </a>
                </div>
                <div class="text-center">
                    <a href="{$refresh_status|escape:'htmlall':'UTF-8'}"
                       class="lgw-box-link">
                        {$locale->t('status.screen.refresh_action')|escape:'htmlall':'UTF-8'}
                    </a>
                </div>
            </div>
            <div class="lgw-col-6">
                <div class="vertical-center">
                    <img src="/modules/lengow/views/img/logo-blue.png" class="center-block" alt="lengow"/>
                </div>
            </div>
        {/if}
        </div>
    </div>
</div>
