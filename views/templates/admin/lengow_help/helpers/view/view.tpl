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

      <h2>{$locale->t('help.title')|escape:'htmlall':'UTF-8'}</h2>
      <p>

        Ask us anything about Lengow or share your feedback at <a href="mailto:chose@bidule.fr?subject=Hey%20Dude. %20You're%20Cool.&subject=Le%20sujet%20du%20mail&body=ID%20account%3A%20tata%0D%0AModule%20type%3A%20Prestashop%0D%0AModule%20version%3A%201.5.3.2%0D%0APlugin%20type%3A%206.5%0D%0A" title="Need some help?">support@lengow.com</a>.
      <br>
        We’ll do our best to get back to you during regular business hours (Monday to Friday – 9 pm to 9 am / France Timezone).
      </p>
      <p>
        You can also find answers in our <a href="https://en.knowledgeowl.com/help/article/link/prestashopv2" class="sub-link" target="_blank" title="Help Center">PrestaShop dedicated guide</a>.
      </p>
    </div>



</div>


<input type="hidden" id="lengow_ajax_link" value="{$lengow_ajax_link|escape:'htmlall':'UTF-8'}">

<script type="text/javascript" src="/modules/lengow/views/js/lengow/help.js"></script>
