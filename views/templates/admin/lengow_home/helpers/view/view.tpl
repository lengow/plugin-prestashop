{*
 * Copyright 2015 Lengow SAS.
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
 *  @copyright 2015 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}

<div id="lengow_home_wrapper">
    <div id="lengow_home_content">
        <h1 style="text-align: center;margin-top:40px;">
            <i class="fa fa-4x fa-globe"></i> Prestashop Lengow Module Rocks
            <i class="fa fa-4x fa-rocket"></i>
        </h1>
        <a href="#" id="lengow_sync_btn" data-href="{$sync_link|escape:'htmlall':'UTF-8'}" class="lengow_btn">Sync Data</a>
    </div>
    <div id="lengow_home_frame" style="display:none;">
        <iframe id="lengow_home_iframe" style="width:100%;height:400px;"></iframe>
    </div>
</div>

<script type="text/javascript" src="/modules/lengow/views/js/lengow/home.js"></script>