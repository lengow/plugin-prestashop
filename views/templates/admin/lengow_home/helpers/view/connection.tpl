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
    <div class="lgw-container lgw-connection text-center">
        <div class="lgw-content-section">
            <div class="lgw-logo">
                <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/lengow-blue.png" alt="lengow">
            </div>
        </div>
        <div id="lgw-connection-content">
            {include file='./connection_home.tpl'}
        </div>
    </div>
</div>

<input type="hidden" id="lengow_ajax_link" value="{$lengow_ajax_link|escape:'htmlall':'UTF-8'}">
