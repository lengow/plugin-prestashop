<div>
    <form class="lengow_form_search_order" method="POST">
        <h4>Search order</h4>
        <select name="" id="select_shop">
            <option value="">choose shop...</option>
            {foreach from=$shop item=shopItem}
                <option value="{$shopItem->id}">{$shopItem->name}</option>
            {/foreach}
        </select>
        <select name="" id="select_mkp">
            <option value="">choose markeplace...</option>
            {foreach from=$markeplaces item=mkpItem}
                <option value="{$mkpItem['id']}">{$mkpItem['text']}</option>
            {/foreach}
        </select>
        <input type="text" id="sku_order" placeholder="sku order...">
        <input type="text" id="delivery_adress_id" placeholder="delivery adress id...">
        <button type="button" class="btn search_order" id="lengow_search_order"
                data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
            Update
        </button>
        <div id="error_search_order"></div>
    </form>
</div>
<div>
    <form class="lengow_form_search_some_orders" method="POST">
        <h4>Search some orders</h4>
        <select name="" id="shop">
            <option value="">choose shop...</option>
            {foreach from=$shop item=shopItem}
                <option value="{$shopItem->id}">{$shopItem->name}</option>
            {/foreach}
        </select>
        <input type="text" class="import_days" placeholder="import days...">
        <button type="button" class="btn search_some_orders" id="lengow_search_some_orders"
                data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
            Update
        </button>
    </form>
</div>
<div class="lengow_order_block_content_right">
    <h4>All orders</h4>
    <a id="lengow_import_orders" class="lengow_btn btn btn-success"
       data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
        {$locale->t('order.button_update_orders')}</a>
</div>