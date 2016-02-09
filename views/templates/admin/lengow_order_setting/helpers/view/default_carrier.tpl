<div class="lengow_default_carrier {if empty($itemCarrier['id_carrier'])}no_carrier{/if}"
     data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting', true)}"
     id="lengow_country_{$itemCarrier['id_country']}">
    <h4><img src="/modules/lengow/views/img/flag/{$itemCarrier['iso_code']}.png"
             alt="{$itemCarrier['name']}">
        {$itemCarrier['name']} {if $itemCarrier['id_country'] neq $default_country}
        <button type="button" class="btn delete_lengow_default_carrier"
                data-id-country="{$itemCarrier['id_country']}">X</button>{else}
            <span>(default)</span>
        {/if}</h4>

    <select name="default_carrier[{$itemCarrier['id']}]" class="carrier">
        <option value=""></option>
        {foreach from=$carriers key=k item=c}
            {if $itemCarrier['id_carrier'] eq $k}
                <option value="{$k}" selected="selected">{$c}</option>
            {else}
                <option value="{$k}">{$c}</option>
            {/if}
        {/foreach}
    </select>
</div>