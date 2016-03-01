<div class="lengow_import_order_toolbox">
    <h4>{$locale->t('toolbox.order.import_one_order')}</h4>
    <form class="lengow_form_update_order form-inline" method="POST">
        <div class="form-group">
            <label for="select_shop">{$locale->t('toolbox.order.shop')}</label>
            <select name="" id="select_shop" data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
                <option value=""></option>
                {foreach from=$shop item=shopItem}
                    <option value="{$shopItem->id}">{$shopItem->name}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <label for="select_mkp">{$locale->t('toolbox.order.markeplace_name')}</label>
            <span id="select_marketplace">{include file='./select_marketplace.tpl'}</span>
        </div>
        <div class="form-group">
            <label for="sku_order">{$locale->t('toolbox.order.order_sku')}</label>
            <input type="text" id="sku_order">
        </div>
        <div class="form-group">
            <label for="delivery_adress_id">{$locale->t('toolbox.order.delivery_address_id')}</label>
            <input type="text" id="delivery_adress_id">
        </div>
        <a id="lengow_update_order" class="lengow_btn btn-success"
            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
            {$locale->t('toolbox.order.import_one_order')}
        </a>
        <div id="error_update_order"></div>
    </form>
</div>

<div class="lengow_import_order_toolbox">
    <h4>{$locale->t('toolbox.order.import_shop_order')}</h4>
    <form class="lengow_form_update_some_orders form-inline" method="POST">
        <div class="form-group">
            <label for="select_shop">{$locale->t('toolbox.order.shop')}</label>
            <select name="" id="select_shop">
                <option value=""></option>
                {foreach from=$shop item=shopItem}
                    <option value="{$shopItem->id}">{$shopItem->name}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <label for="import_days">{$locale->t('toolbox.order.import_days')}</label>
            <input type="text" id="import_days" value="{$days}">
        </div>
        <a id="lengow_update_some_orders" class="lengow_btn btn-success"
            data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
            {$locale->t('toolbox.order.import_shop_order')}
        </a>
        <div id="error_update_some_orders"></div>
    </form>
</div>

<div class="lengow_import_order_toolbox">
    <h4>{$locale->t('toolbox.order.import_all_order')}</h4>
    <a id="lengow_import_orders" class="lengow_btn btn-success"
       data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder', true)|escape:'htmlall':'UTF-8'}">
        {$locale->t('order.screen.button_update_orders')}
    </a>
</div>

<div id="lengow_wrapper_messages"></div>