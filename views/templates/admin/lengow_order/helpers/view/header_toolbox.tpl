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
 
<div class="lengow_import_order_toolbox">
    <h4>{$locale->t('toolbox.order.import_one_order')|escape:'htmlall':'UTF-8'}</h4>
    <form class="lengow_form_update_order form-inline" method="POST">
        <div class="form-group">
            <label for="select_shop">{$locale->t('toolbox.order.shop')|escape:'htmlall':'UTF-8'}</label>
            <select name="" id="select_shop" data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
                {foreach from=$shop item=shopItem}
                    <option value="{$shopItem->id|escape:'htmlall':'UTF-8'}">{$shopItem->name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <label for="sku_mkp">{$locale->t('toolbox.order.marketplace_name')|escape:'htmlall':'UTF-8'}</label>
            <input type="text" id="sku_mkp">
        </div>
        <div class="form-group">
            <label for="sku_order">{$locale->t('toolbox.order.order_sku')|escape:'htmlall':'UTF-8'}</label>
            <input type="text" id="sku_order">
        </div>
        <div class="form-group">
            <label for="delivery_adress_id">
                {$locale->t('toolbox.order.delivery_address_id')|escape:'htmlall':'UTF-8'}
            </label>
            <input type="text" id="delivery_adress_id">
        </div>
        <a id="lengow_update_order" class="lgw-btn btn-success"
            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
            {$locale->t('toolbox.order.import_one_order')|escape:'htmlall':'UTF-8'}
        </a>
        <div id="error_update_order"></div>
    </form>
</div>
<div class="lengow_import_order_toolbox">
    <h4>{$locale->t('toolbox.order.import_shop_order')|escape:'htmlall':'UTF-8'}</h4>
    <form class="lengow_form_update_some_orders form-inline" method="POST">
        <div class="form-group">
            <label for="select_shop">{$locale->t('toolbox.order.shop')|escape:'htmlall':'UTF-8'}</label>
            <select name="" id="select_shop">
                {foreach from=$shop item=shopItem}
                    <option value="{$shopItem->id|escape:'htmlall':'UTF-8'}">{$shopItem->name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <label for="import_days">{$locale->t('toolbox.order.import_days')|escape:'htmlall':'UTF-8'}</label>
            <input type="text" id="import_days" value="{$days|escape:'htmlall':'UTF-8'}">
        </div>
        <a id="lengow_update_some_orders" class="lgw-btn btn-success"
            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
            {$locale->t('toolbox.order.button_import_shop_order')|escape:'htmlall':'UTF-8'}
        </a>
        <div id="error_update_some_orders"></div>
    </form>
</div>
<div class="lengow_import_order_toolbox">
    <h4>{$locale->t('toolbox.order.import_all_order')|escape:'htmlall':'UTF-8'}</h4>
    <a id="lengow_import_orders" class="lgw-btn btn-success"
       data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
        {$locale->t('order.screen.button_update_orders')|escape:'htmlall':'UTF-8'}
    </a>
</div>
<div id="lengow_wrapper_messages"></div>