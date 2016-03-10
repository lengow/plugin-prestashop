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

<div id="lengow_form_wrapper" class="lengow_main_setting" xmlns="http://www.w3.org/1999/html">
    <form class="lengow_form" method="POST">
        <input type="hidden" name="action" value="process">
        <div class="container">
            <h2>{$locale->t('global_setting.screen.notification_alert_title')|escape:'htmlall':'UTF-8'}</h2>
            {html_entity_decode($mail_report|escape:'htmlall':'UTF-8')}
            <div class="lengow_clear"></div>
        </div>
        <div id="preprod_setting" class="container">
            <h2>{$locale->t('global_setting.screen.preprod_mode_title')|escape:'htmlall':'UTF-8'}</h2>
            <p>{$locale->t('global_setting.screen.preprod_mode_description')|escape:'htmlall':'UTF-8'}</p><br/>
            {html_entity_decode($preprod_report|escape:'htmlall':'UTF-8')}
            <div id="lengow_wrapper_preprod" class="vertical" style="display:none;">
                {html_entity_decode($preprod_wrapper|escape:'htmlall':'UTF-8')}
            </div>
            <div class="lengow_clear"></div>
        </div>
        <div class="container">
            <h2>{$locale->t('global_setting.screen.log_file_title')|escape:'htmlall':'UTF-8'}</h2>
            <p>{html_entity_decode($locale->t('global_setting.screen.log_file_description')|escape:'htmlall':'UTF-8')}</p><br/>
            <select id="select_log" class="lengow_select">
                <option value="" disabled selected hidden>
                    {$locale->t('global_setting.screen.please_choose_log')|escape:'htmlall':'UTF-8'}
                </option>
                    {foreach from=$list_file item=file}
                            <option value="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting', true)|escape:'htmlall':'UTF-8'}&action=download&file={$file['short_path']|escape:'htmlall':'UTF-8'}">
                                {$file['name']|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                <option value="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting', true)|escape:'htmlall':'UTF-8'}&action=download_all" >
                    {$locale->t('global_setting.screen.download_all_files')|escape:'htmlall':'UTF-8'}
                </option>
            </select>
            <button type="button" id="download_log" class="btn lengow_btn">
                <i class="fa fa-download"></i> {$locale->t('global_setting.screen.button_download_file')|escape:'htmlall':'UTF-8'}
            </button>
        </div>
        <div id="lengow_delete_module" class="container">
            <h2>{$locale->t('global_setting.screen.uninstall_module')|escape:'htmlall':'UTF-8'}</h2>
            <p>{$locale->t('global_setting.screen.uninstall_module_description')|escape:'htmlall':'UTF-8'}</p><br/>
            <a href="#openDeleteModal">
                <button type="button" class="btn lengow_btn lengow_delete_module" name="delete_module">
                    {$locale->t('global_setting.screen.button_i_want_uninstall')|escape:'htmlall':'UTF-8'}
                 </button></a>
            <span class="lengow_label_text">
                        {$locale->t('global_setting.screen.i_want_uninstall')|escape:'htmlall':'UTF-8'}
            </span>
            <div id="openDeleteModal" class="deleteModalDialog">
                <div>
                    <h2>{$locale->t('global_setting.screen.title_modal_uninstall')|escape:'htmlall':'UTF-8'}</h2>
                    <p>
                        {$locale->t('global_setting.screen.all_data_will_be_lost')|escape:'htmlall':'UTF-8'}<br/><br/>
                        {$locale->t('global_setting.screen.you_will_find_a_backup')|escape:'htmlall':'UTF-8'}
                        <a href="{$lengow_link->getAbsoluteAdminLink('AdminBackup')|escape:'htmlall':'UTF-8'}">
                            {$locale->t('global_setting.screen.prestashop_backup')|escape:'htmlall':'UTF-8'}
                        </a>
                    </p>
                    <div id="lengow_wrapper_delete">
                        <div class="form-group lengow_account_id[1]">
                            <label class="control-label">
                                {$locale->t('global_setting.screen.to_uninstall_type')|escape:'htmlall':'UTF-8'}
                                : I WANT TO REMOVE ALL DATA
                            </label>
                            <input type="text" name="uninstall_textbox" class="form-control" placeholder="" value="">
                            <button type="submit" class="btn lengow_btn lengow_submit_delete_module">
                                {$locale->t('global_setting.screen.button_i_want_uninstall')|escape:'htmlall':'UTF-8'}
                            </button>
                            <a href="#close" title="Close" class="close">
                                {$locale->t('global_setting.screen.cancel_i_want_uninstall')|escape:'htmlall':'UTF-8'}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group container">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn lengow_btn lengow_submit_main_setting">
                    {$locale->t('global_setting.screen.button_save')|escape:'htmlall':'UTF-8'}
                </button>
            </div>
        </div>
    </form>
</div>


<script type="text/javascript" src="/modules/lengow/views/js/lengow/main_setting.js"></script>
