<div class="lengow_default_carrier"
     data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting', true)}"
     id="lengow_country_{$defaultCarrierCountries[$current_id_country]['lengow_country_id']}">
    <h3>Default carrier</h3>
    <select name="default_carrier[{$defaultCarrierCountries[$current_id_country]['lengow_country_id']}]" class="carrier defaultCarrier lengow_select">
        <option value=""></option>
        {foreach from=$listCarrierByCountry[$current_id_country] key=k item=c}
            {if $defaultCarrierCountries[$current_id_country]['id_carrier'] eq $k}
                <option value="{$k}" selected="selected">{$c}</option>
            {else}
                <option value="{$k}">{$c}</option>
            {/if}
        {/foreach}
    </select>
    <div id="default_carrier_missing"></div>
</div>