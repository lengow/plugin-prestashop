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
<div id="lgw-connection-cms">
    <div class="lgw-content-section">
        <h2>{$locale->t('connection.cms.credentials_title')|escape:'htmlall':'UTF-8'}</h2>
    </div>
    <div class="lgw-content-input">
        <input type="text"
               name="lgwAccessToken"
               class="js-credentials-input"
               placeholder="{$locale->t('connection.cms.credentials_placeholder_access_token')|escape:'htmlall':'UTF-8'}">
        <input type="text"
               name="lgwSecret"
               class="js-credentials-input"
               placeholder="{$locale->t('connection.cms.credentials_placeholder_secret')|escape:'htmlall':'UTF-8'}">
    </div>
    <div class="lgw-content-section">
        <p>{$locale->t('connection.cms.credentials_description')|escape:'htmlall':'UTF-8'}</p>
        <p>
            {$locale->t('connection.cms.credentials_help')|escape:'htmlall':'UTF-8'}
            <a href="{$locale->t('connection.cms.credentials_help_center_url')|escape:'htmlall':'UTF-8'}" target="_blank">
                {$locale->t('connection.cms.credentials_help_center')|escape:'htmlall':'UTF-8'}
            </a>
        </p>
    </div>
    <div>
        <button class="lgw-btn lgw-btn-progression lgw-btn-disabled js-connect-cms">
            <div class="btn-inner">
                <div class="btn-step default">
                    {$locale->t('connection.cms.credentials_button')|escape:'htmlall':'UTF-8'}
                </div>
                <div class="btn-step loading">
                    {$locale->t('connection.cms.credentials_button_loading')|escape:'htmlall':'UTF-8'}
                </div>
            </div>
        </button>
    </div>
</div>
