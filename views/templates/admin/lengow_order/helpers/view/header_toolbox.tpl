
            <div class="lengow_import_order_toolbox">
                <form class="lengow_form_update_order" method="POST">
                    <label for="select_shop">Shop : </label>
                    <select name="" id="select_shop" data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
                        <option value=""></option>
                        {foreach from=$shop item=shopItem}
                            <option value="{$shopItem->id}">{$shopItem->name}</option>
                        {/foreach}
                    </select>
                    <label for="select_mkp">Marketplace : </label>
                    <span id="select_marketplace">
                        {include file='./select_marketplace.tpl'}
                    </span>
                    <label for="sku_order">Sku Order : </label>
                    <input type="text" id="sku_order">
                    <label for="delivery_adress_id">Delivery adress ID : </label>
                    <input type="text" id="delivery_adress_id">
                    <button type="button" class="btn update_order" id="lengow_update_order"
                            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
                        Import One Order
                    </button>
                    <div id="error_update_order"></div>
                </form>
            </div>

            <div class="lengow_import_order_toolbox">
                <form class="lengow_form_update_some_orders" method="POST">
                    <label for="select_shop">Shop : </label>
                    <select name="" id="select_shop">
                        <option value=""></option>
                        {foreach from=$shop item=shopItem}
                            <option value="{$shopItem->id}">{$shopItem->name}</option>
                        {/foreach}
                    </select>
                    <label for="import_days">Import days : </label>
                    <input type="text" id="import_days" value="{$days}">
                    <button type="button" class="btn update_some_orders" id="lengow_update_some_orders"
                            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
                        Import Shop Order
                    </button>
                    <div id="error_update_some_orders"></div>
                </form>
            </div>

            <div class="lengow_import_order_toolbox">
                <a id="lengow_import_orders" class="lengow_btn btn btn-success"
                   data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
                    {$locale->t('order.screen.button_update_orders')}</a>
            </div>

<div id="lengow_wrapper_messages"></div>