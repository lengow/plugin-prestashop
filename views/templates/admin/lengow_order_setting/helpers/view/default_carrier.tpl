<div class="lengow_default_carrier {if empty($carrierItem.$current_id_country.id_carrier)}no_carrier{/if}"
     data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting', true)}"
     id="lengow_country_{$carrierItem.$current_id_country.id_country}">
    <h3>Default carrier</h3>
    <select name="default_carrier[{$carrierCountry.$current_id_country.id}]" class="carrier">
        <option value=""></option>
        {foreach from=$carriers key=k item=c}
            {if $carrierCountry.$current_id_country.id_carrier eq $k}
                <option value="{$k}" selected="selected">{$c}</option>
            {else}
                <option value="{$k}">{$c}</option>
            {/if}
        {/foreach}
    </select>
</div>