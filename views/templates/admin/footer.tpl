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
                <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowLegals')|escape:'htmlall':'UTF-8'}" class="sub-link" title="Legal">{$locale->t('footer.legals')|escape:'htmlall':'UTF-8'}</a>
                | {$locale->t('footer.plugin_lengow')|escape:'htmlall':'UTF-8'} - v.{$lengowVersion|escape:'htmlall':'UTF-8'} {if $lengowUrl === 'lengow.net'}<span class="lgw-label-preprod">preprod</span>{/if}
                | copyright © {$smarty.now|date_format:'%Y'|escape:'htmlall':'UTF-8'} <a href="{$locale->t('footer.lengow_link_url')|escape:'htmlall':'UTF-8'}" target="_blank" class="sub-link" title="Lengow.com">Lengow</a>
            </p>
        </div>
    </div>
</div>
