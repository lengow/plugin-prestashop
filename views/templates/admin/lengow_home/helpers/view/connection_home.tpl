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

<div id="lgw-connection-home">
    <div class="lgw-content-section">
        <p>{$locale->t('connection.home.description_first')|escape:'htmlall':'UTF-8'}</p>
        <p>{$locale->t('connection.home.description_second')|escape:'htmlall':'UTF-8'}</p>
        <p>{$locale->t('connection.home.description_third')|escape:'htmlall':'UTF-8'}</p>
    </div>
    <div class="lgw-module-illu">
        <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/connected-prestashop.png"
             class="lgw-module-illu-module"
             alt="">
        <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/connected-lengow.png"
             class="lgw-module-illu-lengow"
             alt="">
        <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/plug-grey.png"
             class="lgw-module-illu-plug"
             alt="">
    </div>
    <p>{$locale->t('connection.home.description_fourth')|escape:'htmlall':'UTF-8'}</p>
    <div>
        <button class="lgw-btn lgw-btn-green js-go-to-credentials">
            {$locale->t('connection.home.button')|escape:'htmlall':'UTF-8'}
        </button>
        <br/>
        <p>
            {$locale->t('connection.home.no_account')|escape:'htmlall':'UTF-8'}
            <a href="https://my.{$lengowUrl|escape:'htmlall':'UTF-8'}" target="_blank">
                {$locale->t('connection.home.no_account_sign_up')|escape:'htmlall':'UTF-8'}
            </a>
        </p>
    </div>
</div>
