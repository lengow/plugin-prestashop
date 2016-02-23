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

<div id="lengow_form_wrapper" class="lengow_main_setting">
    <form class="lengow_form" method="POST">
        <input type="hidden" name="action" value="process">
        <div class="container">
            <h2>Notifications & alerts</h2>
            <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p>
            {$mail_report}

            <div class="lengow_clear"></div>
        </div>
        <div class="container">
            <h2>Pre-Production Mode</h2>
            <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p>
            {$preprod_report}
            <div id="lengow_wrapper_preprod" class="vertical" style="display:none;">
                {$preprod_wrapper}
            </div>
            <div class="lengow_clear"></div>
        </div>

        <div class="container">
            <h2>Log Files</h2>
            <p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p>
            <ul class="list-group">
                {foreach from=$list_file item=file}
                    <li class="list-group-item">
                        <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|escape:'htmlall':'UTF-8'}&action=download&file={$file['short_path']}">
                            <i class="fa fa-download"></i> {$file['name']}
                        </a>
                    </li>
                {/foreach}
                <li class="list-group-item">
                    <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|escape:'htmlall':'UTF-8'}&action=download_all">
                        <i class="fa fa-download"></i> Download all files
                    </a>
                </li>
            </ul>
        </div>


        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn lengow_btn">Save changes</button>
            </div>
        </div>
    </form>
</div>



<script type="text/javascript" src="/modules/lengow/views/js/lengow/main_setting.js"></script>