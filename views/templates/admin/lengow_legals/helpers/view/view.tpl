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
            <div id="lgw-debug" class="adminlengowlegals">
                {$locale->t('menu.debug_active')|escape:'htmlall':'UTF-8'}
            </div>
        {/if}
        <div class="lgw-box lengow_legals_wrapper">
            <h3>SAS Lengow</h3>
            {$locale->t('legals.screen.simplified_company')|escape:'htmlall':'UTF-8'}
            <br />
            {$locale->t('legals.screen.social_capital')|escape:'htmlall':'UTF-8'}
            368 778 €
            <br />
            {$locale->t('legals.screen.cnil_declaration')|escape:'htmlall':'UTF-8'}
            1748784 v 0
            <br />
            {$locale->t('legals.screen.company_registration_number')|escape:'htmlall':'UTF-8'}
            513 381 434
            <br />
            {$locale->t('legals.screen.vat_identification_number')|escape:'htmlall':'UTF-8'}
            FR42513381434
            <h3>{$locale->t('legals.screen.address')|escape:'htmlall':'UTF-8'}</h3>
            6 rue René Viviani<br />
            44200 Nantes
            <h3>{$locale->t('legals.screen.contact')|escape:'htmlall':'UTF-8'}</h3>
            contact@lengow.com<br />
            +33 (0)2 85 52 64 14
            <h3>{$locale->t('legals.screen.hosting')|escape:'htmlall':'UTF-8'}</h3>
            OXALIDE<br />
            RCS Paris : 803 816 529<br />
            25 Boulevard de Strasbourg – 75010 Paris<br />
            +33 (0)1 75 77 16 66
        </div>
    </div>
</div>
