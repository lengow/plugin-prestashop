{*
 * Copyright 2022 Lengow SAS.
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
 *  @copyright 2022 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
<div class="cms-global">
    <div class="lgw-container lgw-toolbox-wrapper">
        {if $debugMode}
            <div id="lgw-debug" class="adminlengowtoolbox {if $multiShop}multi-shop{/if}">
                {$locale->t('menu.debug_active')|escape:'htmlall':'UTF-8'}
            </div>
        {/if}
        <h2>
            <i class="fa fa-rocket"></i>
            {$locale->t('toolbox.screen.title')|escape:'htmlall':'UTF-8'}
        </h2>
        <div class="lgw-box">
            <div class="lgw-switch checked js-lgw-global">
                <label>
                    <div>
                        <span></span>
                        <input type="checkbox" name="see_global_content" checked>
                    </div>
                    {$locale->t('toolbox.screen.global_information')|escape:'htmlall':'UTF-8'}
                </label>
            </div>
            <div class="js-lgw-global-content">
                <div class="lgw-box-content">
                    <h3>
                        <i class="fa fa-check"></i>
                        {$locale->t('toolbox.screen.checklist_information')|escape:'htmlall':'UTF-8'}
                    </h3>
                    {html_entity_decode($checklist|escape:'htmlall':'UTF-8')}
                </div>
                <div class="lgw-box-content">
                    <h3>
                        <i class="fa fa-cog"></i>
                        {$locale->t('toolbox.screen.plugin_information')|escape:'htmlall':'UTF-8'}
                    </h3>
                    {html_entity_decode($globalInformation|escape:'htmlall':'UTF-8')}
                </div>
                <div class="lgw-box-content">
                    <h3>
                        <i class="fa fa-download"></i>
                        {$locale->t('toolbox.screen.synchronization_information')|escape:'htmlall':'UTF-8'}
                    </h3>
                    {html_entity_decode($synchronizationInformation|escape:'htmlall':'UTF-8')}
                </div>
            </div>
        </div>
        <div class="lgw-box">
            <div class="lgw-switch js-lgw-export">
                <label>
                    <div>
                        <span></span>
                        <input type="checkbox" name="see_export_content">
                    </div>
                    {$locale->t('toolbox.screen.shop_information')|escape:'htmlall':'UTF-8'}
                </label>
            </div>
            <div class="js-lgw-export-content">
                <div class="lgw-box-content">
                    <h3>
                        <i class="fa fa-upload"></i>
                        {$locale->t('toolbox.screen.export_information')|escape:'htmlall':'UTF-8'}
                    </h3>
                    {html_entity_decode($exportInformation|escape:'htmlall':'UTF-8')}
                </div>
                <div class="lgw-box-content">
                    <h3>
                        <i class="fa fa-list"></i>
                        {$locale->t('toolbox.screen.content_folder_media')|escape:'htmlall':'UTF-8'}
                    </h3>
                    {html_entity_decode($fileInformation|escape:'htmlall':'UTF-8')}
                </div>
            </div>
        </div>
        <div class="lgw-box">
            <div class="lgw-switch js-lgw-checksum">
                <label>
                    <div>
                        <span></span>
                        <input type="checkbox" name="see_checksum_content">
                    </div>
                    {$locale->t('toolbox.screen.checksum_integrity')|escape:'htmlall':'UTF-8'}
                </label>
            </div>
            <div class="lgw-box-content js-lgw-checksum-content">
                {html_entity_decode($checksum|escape:'htmlall':'UTF-8')}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/lengow/toolbox.js"></script>
