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

<div class="lgw-content-section text-center">
  <div id="frame_loader">
      <i class="fa fa-circle-o-notch fa-spin" style="font-size:100px;margin-top:100px;color:white;"></i>
  </div>
  <iframe id="lengow_iframe" scrolling="yes" style="display: none; overflow-y: hidden;' width='580' height='400' frameborder='0' seamless='seamless'" frameBorder="0"></iframe>
</div>

<input type="hidden" id="lengow_ajax_link" value="{$lengow_ajax_link|escape:'htmlall':'UTF-8'}">
<input type="hidden" id="lengow_sync_link" value="{$isSync|escape:'htmlall':'UTF-8'}">
<input type="hidden" id="lengow_url" value="{$lengowUrl|escape:'htmlall':'UTF-8'}">
<input type="hidden" id="lengow_lang_iso" value="{$localeIsoCode|escape:'htmlall':'UTF-8'}">
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/lengow/home.js"></script>
