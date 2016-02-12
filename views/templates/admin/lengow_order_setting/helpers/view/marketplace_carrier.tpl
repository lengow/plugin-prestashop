{counter assign='i' start='0' print=false}
{assign var='current_id_country' value={$mkp_carriers.$i.id_country}}

{while $i < {count($mkp_carriers)}}
    <li class="has-sub lengow_marketplace_carrier"
        id="lengow_marketplace_carrier_country_{$mkp_carriers.$i.id_country}">
        <h4><label for="menu{$i}"><img src="/modules/lengow/views/img/flag/{$mkp_carriers.$i.iso_code}.png"
                                       alt="{$mkp_carriers.$i.name}">
                {$mkp_carriers.$i.name} {if {$mkp_carriers.$i.id_country} eq $default_country}
                <span>(default)</span>{/if}</label></h4><input id="menu{$i}" name="menu" type="checkbox"/>
        <ul class="sub">
            {while $current_id_country eq {$mkp_carriers.$i.id_country} && $i < {count($mkp_carriers)}}
                <li class="marketplace_carrier {if empty({$mkp_carriers.$i.id_carrier})}no_carrier{/if}">
                    <h3>{$mkp_carriers.$i.marketplace_carrier_sku}</h3>
                    <select name="default_marketplace_carrier[{$mkp_carriers.$i.id}]" class="carrier">
                        <option value=""></option>

                        {foreach from=$carriers key=k item=c}
                            {if {$mkp_carriers.$i.id_carrier} eq $k}
                                <option value="{$k}" selected="selected">{$c}</option>
                            {else}
                                <option value="{$k}">{$c}</option>
                            {/if}
                        {/foreach}

                    </select>
                </li>
                {counter}
                {assign var='current_id_country' value={$mkp_carriers.$i.id_country}}

            {/while}
        </ul>
    </li>
{/while}

