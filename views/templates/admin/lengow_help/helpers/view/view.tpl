{*
 * Copyright 2016 Lengow SAS.
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
 *  @copyright 2016 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}

<div class="lgw-container">
    <div class="lgw-box lengow_help_wrapper text-center">
        <img src="/modules/lengow/views/img/cosmo-yoga.png" class="img-circle" alt="lengow">
        <h2>{$locale->t('help.screen.title')|escape:'htmlall':'UTF-8'}</h2>
        <p>
            {$locale->t('help.screen.contain_text_support')|escape:'htmlall':'UTF-8'}
            {html_entity_decode($mailto|escape:'htmlall':'UTF-8')}
        </p>
        <p>{$locale->t('help.screen.contain_text_support_hour')|escape:'htmlall':'UTF-8'}</p>
        <p>
            {$locale->t('help.screen.find_answer')|escape:'htmlall':'UTF-8'}
            <a href="https://en.knowledgeowl.com/help/article/link/prestashopv2"
                target="_blank"
                title="Help Center">
                {$locale->t('help.screen.link_prestashop_guide')|escape:'htmlall':'UTF-8'}
            </a>
        </p>
    </div>
</div>
<input type="hidden" id="lengow_ajax_link" value="{$lengow_ajax_link|escape:'htmlall':'UTF-8'}">
<script type="text/javascript" src="/modules/lengow/views/js/lengow/help.js"></script>
