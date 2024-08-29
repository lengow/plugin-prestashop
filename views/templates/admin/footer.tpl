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

<div class="lgw-container lgw-footer-vold clear">
    <div class="lgw-content-section text-center">
        <div id="lgw-footer">
            <p class="text-center">
                <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|escape:'htmlall':'UTF-8'}" class="sub-link" title="{$locale->t('footer.settings')|escape:'htmlall':'UTF-8'}">{$locale->t('footer.settings')|escape:'htmlall':'UTF-8'}</a>
                | <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowToolbox')|escape:'htmlall':'UTF-8'}" class="sub-link" title="{$locale->t('footer.toolbox')|escape:'htmlall':'UTF-8'}">{$locale->t('footer.toolbox')|escape:'htmlall':'UTF-8'}</a>
                | <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowLegals')|escape:'htmlall':'UTF-8'}" class="sub-link" title="{$locale->t('footer.legals')|escape:'htmlall':'UTF-8'}">{$locale->t('footer.legals')|escape:'htmlall':'UTF-8'}</a>
                | {$locale->t('footer.plugin_lengow')|escape:'htmlall':'UTF-8'} - v.{$lengowVersion|escape:'htmlall':'UTF-8'}
                {if $isDeveloperMode}
                    <span class="lgw-label-preprod">developer-mode</span>
                {elseif $lengowUrl === 'lengow.net'}
                    <span class="lgw-label-preprod">preprod</span>
                {/if}
                | copyright © {$smarty.now|date_format:'%Y'|escape:'htmlall':'UTF-8'} <a href="{$locale->t('footer.lengow_link_url')|escape:'htmlall':'UTF-8'}" target="_blank" class="sub-link" title="Lengow.com">Lengow</a>
            </p>
        </div>
    </div>
    {if !$pluginIsUpToDate}
        <!-- Modal Update plugin -->
        <div id="upgrade-plugin" class="lgw-modalbox mod-size-medium {if $showPluginUpgradeModal }is-open{/if}">
            <div class="lgw-modalbox-content">
                <span class="lgw-modalbox-close js-upgrade-plugin-modal-close"></span>
                <div class="lgw-modalbox-body">
                    <div class="lgw-row flexbox-vertical-center">
                        <div class="lgw-col-5 text-center">
                            <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/plugin-update.png" alt="">
                        </div>
                        <div class="lgw-col-7">
                            <h1>{$locale->t('update.version_available')|escape:'htmlall':'UTF-8'}</h1>
                            <p>
                                {$locale->t('update.start_now')|escape:'htmlall':'UTF-8'}
                                <a href="{$changelogLink|escape:'htmlall':'UTF-8'}" target="_blank">
                                    {$locale->t('update.link_changelog')|escape:'htmlall':'UTF-8'}
                                </a>
                            </p>
                            <div class="lgw-content-section mod-small">
                                <h2 class="no-margin-bottom">{$locale->t('update.step_one')|escape:'htmlall':'UTF-8'}</h2>
                                <p class="no-margin-bottom">
                                    {$locale->t('update.download_last_version')|escape:'htmlall':'UTF-8'}
                                </p>
                                <p class="text-lesser text-italic">
                                    {$locale->t('update.plugin_compatibility', ['cms_min_version' => $pluginData['cms_min_version'], 'cms_max_version' => $pluginData['cms_max_version']])|escape:'htmlall':'UTF-8'}
                                    {foreach from=$pluginData['extensions'] item=extension}
                                        <br />
                                        {$locale->t('update.extension_required', ['name' => $extension['name'], 'min_version' => $extension['min_version'], 'max_version' => $extension['max_version']])|escape:'htmlall':'UTF-8'}
                                    {/foreach}
                                </p>
                            </div>
                            <div class="lgw-content-section mod-small">
                                <h2 class="no-margin-bottom">{$locale->t('update.step_two')|escape:'htmlall':'UTF-8'}</h2>
                                <p class="no-margin-bottom">
                                    <a href="{$updateGuideLink|escape:'htmlall':'UTF-8'}" target="_blank">
                                        {$locale->t('update.link_follow')|escape:'htmlall':'UTF-8'}
                                    </a>
                                    {$locale->t('update.update_procedure')|escape:'htmlall':'UTF-8'}
                                </p>
                                <p class="text-lesser text-italic">
                                    {$locale->t('update.not_working')|escape:'htmlall':'UTF-8'}
                                    <a href="{$supportLink|escape:'htmlall':'UTF-8'}" target="_blank">
                                        {$locale->t('update.customer_success_team')|escape:'htmlall':'UTF-8'}
                                    </a>
                                </p>
                            </div>
                            <div class="flexbox-vertical-center margin-standard">
                                <a class="lgw-btn no-margin-top" href="https://my.{$lengowUrl|escape:'htmlall':'UTF-8'}{$pluginData['download_link']|escape:'htmlall':'UTF-8'}" target="_blank">
                                    {$locale->t('update.button_download_version', ['version' => $pluginData['version']])|escape:'htmlall':'UTF-8'}
                                </a>
                                {if $showPluginUpgradeModal}
                                    <button class="btn-link sub-link no-margin-top text-small js-upgrade-plugin-modal-remind-me">
                                        {$locale->t('update.button_remind_me_later')|escape:'htmlall':'UTF-8'}
                                    </button>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="lengow_modal_ajax_link" value="{$lengowModalAjaxLink|escape:'htmlall':'UTF-8'}">
        </div>
    {/if}
</div>
