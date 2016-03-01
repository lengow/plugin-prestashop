{foreach from=$defaultCarrierCountries key=id_country item=values}
    <li class="has-sub lengow_marketplace_carrier"
        id="lengow_marketplace_carrier_country_{$id_country}">
        <h4 class="country">
            <label for="menu{$id_country}"><img src="/modules/lengow/views/img/flag/{$values["iso_code"]}.png" alt="{$values["name"]}">
                {$values["name"]}
                {if $id_country eq $default_country}
                    <span>({$locale->t('order_setting.screen.default_country')})</span>
                {else}
                    <button type="button" class="btn delete_lengow_default_carrier"
                            data-id-country="{$id_country}">X
                    </button>
                {/if}
            <span class="score"></span><i class="fa fa-chevron-down"></i>
        </h4>
        </label><input id="menu{$id_country}" name="menu" type="checkbox"/>
        <ul class="sub">
            <li class="add_country {if empty($defaultCarrierCountries[$id_country]['id_carrier'])}no_carrier{/if}">
                {include file='./default_carrier.tpl'}
            </li>
            {if isset($marketplace_carriers[$id_country])}
                {foreach from=$marketplace_carriers[$id_country] item=marketplace_carrier}
                    <li class="marketplace_carrier {if empty({$marketplace_carrier['id_carrier']})}no_carrier{/if}">
                        <h3>{$marketplace_carrier['marketplace_carrier_name']}</h3>
                        <select name="default_marketplace_carrier[{$marketplace_carrier["id"]}]" class="carrier lengow_select">
                            <option value=""></option>
                            {foreach from=$listCarrierByCountry[$id_country] key=k item=c}
                                {if {$marketplace_carrier["id_carrier"]} eq $k}
                                    <option value="{$k}" selected="selected">{$c}</option>
                                {else}
                                    <option value="{$k}">{$c}</option>
                                {/if}
                            {/foreach}
                        </select>
                    </li>
                {/foreach}
            {/if}
        </ul>
        <div class="lengow_clear"></div>
    </li>
{/foreach}

