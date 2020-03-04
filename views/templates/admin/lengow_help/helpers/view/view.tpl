{*
 * Copyright 2017 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 *  @author	   Team Connector <team-connector@lengow.com>
 *  @copyright 2017 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
<div class="cms-global">
    <div class="lgw-container">
        {if $lengow_configuration->debugModeIsActive()}
            <div id="lgw-debug" class="adminlengowhelp">
                {$locale->t('menu.debug_active')|escape:'htmlall':'UTF-8'}
            </div>
        {/if}
        <div class="lgw-box lengow_help_wrapper text-center">
            <h2>{$locale->t('help.screen.title')|escape:'htmlall':'UTF-8'}</h2>
            <p>
                {$locale->t('help.screen.contain_text_support')|escape:'htmlall':'UTF-8'}
                {*{html_entity_decode($mailto|escape:'htmlall':'UTF-8')}*}
                <a href="{$locale->t('help.screen.link_lengow_support')|escape:'htmlall':'UTF-8'}"
                   target="_blank"
                   title="Lengow Support">
                    {$locale->t('help.screen.title_lengow_support')|escape:'htmlall':'UTF-8'}</a>
            </p>
            <p>{$locale->t('help.screen.contain_text_support_hour')|escape:'htmlall':'UTF-8'}</p>
            <p>
                {$locale->t('help.screen.find_answer')|escape:'htmlall':'UTF-8'}
                <a href="{$locale->t('help.screen.knowledge_link_url')|escape:'htmlall':'UTF-8'}"
                    target="_blank"
                    title="Help Center">
                    {$locale->t('help.screen.link_prestashop_guide')|escape:'htmlall':'UTF-8'}
                </a>
            </p>
        </div>
    </div>
    <input type="hidden" id="lengow_ajax_link" value="{$lengow_ajax_link|escape:'htmlall':'UTF-8'}">
</div>
