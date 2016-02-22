<div class="lengow_import_order_toolbox">
    <form class="lengow_form_update_order" method="POST">
        <h4>Update order</h4>
        <label for="select_shop">Shop : </label>
        <select name="" id="select_shop">
            <option value=""></option>
            {foreach from=$shop item=shopItem}
                <option value="{$shopItem->id}">{$shopItem->name}</option>
            {/foreach}
        </select><br/>
        <label for="select_mkp">Marketplace : </label>
        <select name="" id="select_mkp">
            <option value=""></option>
            {foreach from=$markeplaces item=mkpItem}
                <option value="{$mkpItem['id']}">{$mkpItem['text']}</option>
            {/foreach}
        </select><br/>
        <label for="sku_order">Sku Order : </label>
        <input type="text" id="sku_order"><br/>
        <label for="delivery_adress_id">Delivery adress ID : </label>
        <input type="text" id="delivery_adress_id"><br/>
        <button type="button" class="btn update_order" id="lengow_update_order"
                data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
            Update
        </button>
        <div id="error_update_order"></div>
    </form>
</div><br/>
<div class="lengow_import_order_toolbox">
    <form class="lengow_form_update_some_orders" method="POST">
        <h4>Update some orders</h4>
        <label for="select_shop">Shop : </label>
        <select name="" id="select_shop">
            <option value=""></option>
            {foreach from=$shop item=shopItem}
                <option value="{$shopItem->id}">{$shopItem->name}</option>
            {/foreach}
        </select><br/>
        <label for="import_days">Import days : </label>
        <input type="text" id="import_days" value="{$days}"><br/>
        <button type="button" class="btn update_some_orders" id="lengow_update_some_orders"
                data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
            Update
        </button>
        <div id="error_update_some_orders"></div>
    </form>
</div><br/>
<div class="lengow_import_order_toolbox">
    <h4>Update all orders</h4>
    <a id="lengow_import_orders" class="lengow_btn btn btn-success"
       data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
        {$locale->t('order.screen.button_update_orders')}</a>
</div>
<br/>