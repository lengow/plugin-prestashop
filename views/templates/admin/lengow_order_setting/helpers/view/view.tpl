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
<div id="lengow_form_wrapper">
<form class="lengow_form" method="POST">
    <input type="hidden" name="action" value="process">
    <div class="container">
        <h2>Order Status</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
            dolore magna aliqua.</p><br/>
        {$matching}
    </div>
    <div class="container2">
        <h2>Carrier Management</h2>
        <h3>Marketplace carrier management</h3>
        <p>Some countries may not have configured carriers, please go to this <a href="#">link</a></p><br/>
        <div class="select_country">
            {include file='./select_country.tpl'}
        </div>
        <div id="error_select_country"></div>
        <div id="add_marketplace_country">
            <ul id="marketplace_country" class="accordion">
                {include file='./marketplace_carrier.tpl'}
            </ul>
        </div>
    </div>
    <div class="container2">
        <h2>Orders importation</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
            dolore magna aliqua.</p><br/>
        {$import_params}
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn lengow_btn lengow_submit_order_setting">Save</button>
        </div>
    </div>
</form>
</div>

<script type="text/javascript" src="/modules/lengow/views/js/lengow/order_setting.js"></script>