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
<div id="lengow_form_order_setting" class="cms-global">
    <div class="lgw-container">
        {if $lengow_configuration->debugModeIsActive()}
            <div id="lgw-debug" class="adminlengowordersetting">
                {$locale->t('menu.debug_active')|escape:'htmlall':'UTF-8'}
            </div>
        {/if}
        <form class="lengow_form" method="POST">
            {if $countries neq false}
                <div class="lgw-box" id="carrier-matching">
                    <div id="country_selector">
                        {include file='./country_selector.tpl'}
                        <p>{html_entity_decode($semantic_search|escape:'htmlall':'UTF-8')}</p>
                    </div>
                    <div id="marketplace_matching"></div>
                    <div class="ajax-loading mod-matching-carrier" style="display: none">
                        <div class="ajax-loading-ball1"></div>
                        <div class="ajax-loading-ball2"></div>
                    </div>
                </div>
            {/if}
            <div class="lgw-box">
                <input type="hidden" name="action" value="process">
                <h2>{$locale->t('order_setting.screen.order_status_title')|escape:'htmlall':'UTF-8'}</h2>
                <div>
                    <p>{$locale->t('order_setting.screen.order_status_description')|escape:'htmlall':'UTF-8'}</p>
                    <p>{html_entity_decode($matching|escape:'htmlall':'UTF-8')}</p>
                </div>

            </div>
            <div class="lgw-box">
                <h2>{$locale->t('order_setting.screen.import_setting_title')|escape:'htmlall':'UTF-8'}</h2>
                <p>{$locale->t('order_setting.screen.import_setting_description')|escape:'htmlall':'UTF-8'}</p>
                <p>{html_entity_decode($import_params|escape:'htmlall':'UTF-8')}</p>
            </div>
            <button type="submit" class="lgw-btn lgw-btn-progression lengow_submit_order_setting">
                <div class="btn-inner">
                    <div class="btn-step default">
                        {$locale->t('global_setting.screen.button_save')|escape:'htmlall':'UTF-8'}
                    </div>
                    <div class="btn-step loading">
                        {$locale->t('global_setting.screen.setting_saving')|escape:'htmlall':'UTF-8'}
                    </div>
                    <div class="btn-step done" data-success="Saved!" data-error="Error">
                        {$locale->t('global_setting.screen.setting_saved')|escape:'htmlall':'UTF-8'}
                    </div>
                </div>
            </button>
        </form>
    </div>
</div>
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/lengow/order_setting.js"></script>
