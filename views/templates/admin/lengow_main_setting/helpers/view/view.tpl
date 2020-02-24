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
<div class="cms-global">
    <div class="lgw-container" id="lengow_mainsettings_wrapper" xmlns="http://www.w3.org/1999/html">
        {if $lengow_configuration->debugModeIsActive()}
            <div id="lgw-debug" class="adminlengowmainsetting">
                {$locale->t('menu.debug_active')|escape:'htmlall':'UTF-8'}
            </div>
        {/if}
        <form class="lengow_form" method="POST">
            <input type="hidden" name="action" value="process">
            <div class="lgw-box">
                <h2>{$locale->t('global_setting.screen.notification_alert_title')|escape:'htmlall':'UTF-8'}</h2>
                {html_entity_decode($mail_report|escape:'htmlall':'UTF-8')}
            </div>
            <div class="lgw-box">
                <h2>{$locale->t('global_setting.screen.default_export_carrier_title')|escape:'htmlall':'UTF-8'}</h2>
                <p>{$locale->t('global_setting.screen.default_export_carrier_description')|escape:'htmlall':'UTF-8'}</p>
                {html_entity_decode($defaultExportCarrier|escape:'htmlall':'UTF-8')}
            </div>
            <div class="lgw-box">
                <h2>{$locale->t('global_setting.screen.tracker_title')|escape:'htmlall':'UTF-8'}</h2>
                <p>{$locale->t('global_setting.screen.tracker_description')|escape:'htmlall':'UTF-8'}</p>
                {html_entity_decode($tracker|escape:'htmlall':'UTF-8')}
            </div>
            <div class="lgw-box">
                <h2>{$locale->t('global_setting.screen.debug_mode_title')|escape:'htmlall':'UTF-8'}</h2>
                <p>{$locale->t('global_setting.screen.debug_mode_description')|escape:'htmlall':'UTF-8'}</p>
                {html_entity_decode($debug_report|escape:'htmlall':'UTF-8')}
                <div id="lengow_wrapper_debug" style="display:none;">
                    {html_entity_decode($debug_wrapper|escape:'htmlall':'UTF-8')}
                </div>
            </div>
            <div class="lgw-box">
                <h2>{$locale->t('global_setting.screen.log_file_title')|escape:'htmlall':'UTF-8'}</h2>
                <p>{html_entity_decode($locale->t('global_setting.screen.log_file_description')|escape:'htmlall':'UTF-8')}</p>
                <select id="select_log" class="lengow_select">
                    <option value="" disabled selected hidden>
                        {$locale->t('global_setting.screen.please_choose_log')|escape:'htmlall':'UTF-8'}
                    </option>
                    {foreach from=$list_file item=file}
                        <option value="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting', true)|escape:'htmlall':'UTF-8'}&action=download&file={$file['short_path']|escape:'htmlall':'UTF-8'}">
                        {assign var=file_name value="."|explode:$file['name']}
                        {$file_name[0]|date_format:"%A %e %B %Y"|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                    <option value="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting', true)|escape:'htmlall':'UTF-8'}&action=download_all" >
                        {$locale->t('global_setting.screen.download_all_files')|escape:'htmlall':'UTF-8'}
                    </option>
                </select>
                <button type="button" id="download_log" class="lgw-btn lgw-btn-white">
                    <i class="fa fa-download"></i> {$locale->t('global_setting.screen.button_download_file')|escape:'htmlall':'UTF-8'}
                </button>
            </div>
            <div class="form-group container">
                <div class="lengow_main_setting_block_content">
                    <div class="pull-left">
                        <button type="submit" class="lgw-btn lgw-btn-progression lengow_submit_main_setting">
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
                    </div>
                    <!--ICI-->
                    <div class="pull-right">
                        <a href="#" class="lgw-modal-delete">
                        <button type="button" data-toggle="modal" data-target="#openDeleteModal"
                                class="lgw-btn lgw-btn-red lengow_delete_module" name="delete_module">
                            {$locale->t('global_setting.screen.button_i_want_uninstall')|escape:'htmlall':'UTF-8'}
                        </button>
                        </a>
                        <div class="lgw-modal">
                            <a href="#" class="modal-close js-close-this-modal"></a>
                            <div class="lgw-modal-inner">
                                <div class="scrollable no-margin">
                                    <div class="scrollable-in">
                                        <div class="lgw-content-section text-center">
                                            <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/laser-gun.png">
                                            <h2><span>{$locale->t('global_setting.screen.title_modal_uninstall')|escape:'htmlall':'UTF-8'}</span></h2>
                                            <p>
                                                {$locale->t('global_setting.screen.all_data_will_be_lost')|escape:'htmlall':'UTF-8'}<br/><br/>
                                                {$locale->t('global_setting.screen.you_will_find_a_backup')|escape:'htmlall':'UTF-8'}
                                                <a href="{$lengow_link->getAbsoluteAdminLink('AdminBackup')|escape:'htmlall':'UTF-8'}">
                                                    {$locale->t('global_setting.screen.prestashop_backup')|escape:'htmlall':'UTF-8'}
                                                </a>
                                            </p>
                                        </div>
                                        <div id="lengow_wrapper_delete">
                                            <div class="form-group">
                                                <label class="control-label">
                                                    {$locale->t('global_setting.screen.to_uninstall_type')|escape:'htmlall':'UTF-8'}
                                                    : {$locale->t('global_setting.screen.i_am_sure')|escape:'htmlall':'UTF-8'}
                                                </label>
                                                <input type="text" name="uninstall_textbox" class="form-control js-confirm-delete" placeholder="" value="" data-confirm="{$locale->t('global_setting.screen.i_am_sure')|escape:'htmlall':'UTF-8'}" autocomplete="off">
                                                <button type="submit" class="lgw-btn lgw-btn-progression lgw-btn-disabled lengow_submit_delete_module">
                                                    <div class="btn-inner">
                                                        <div class="btn-step default">
                                                            {$locale->t('global_setting.screen.button_i_want_uninstall')|escape:'htmlall':'UTF-8'}
                                                        </div>
                                                        <div class="btn-step loading">
                                                            {$locale->t('global_setting.screen.plugin_deleting')|escape:'htmlall':'UTF-8'}
                                                        </div>
                                                        <div class="btn-step done" data-success="Saved!" data-error="Error">
                                                            {$locale->t('global_setting.screen.plugin_deleted')|escape:'htmlall':'UTF-8'}
                                                        </div>
                                                    </div>
                                                </button>
                                                <button type="button" class="btn-link js-close-this-modal">
                                                    {$locale->t('global_setting.screen.cancel_i_want_uninstall')|escape:'htmlall':'UTF-8'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/lengow/main_setting.js"></script>
