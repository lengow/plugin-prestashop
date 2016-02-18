<div class="lengow_default_carrier"
     data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting', true)}"
     id="lengow_country_{$carrierItem.$current_id_country.id_country}">
    <h3>Default carrier</h3>
    <select name="default_carrier[{$carrierCountry.$current_id_country.id}]" class="carrier  defaultCarrier">
        <option value=""></option>
        {foreach from=$carriers key=k item=c}
            {if $carrierCountry.$current_id_country.id_carrier eq $k}
                <option value="{$k}" selected="selected">{$c}</option>
            {else}
                <option value="{$k}">{$c}</option>
            {/if}
        {/foreach}
    </select>
    <div id="default_carrier_missing"></div>
</div>