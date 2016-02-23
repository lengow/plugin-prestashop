<select name="" id="select_mkp">
    <option value=""></option>
    {foreach from=$marketplaces item=mkpItem key=k}
        <option value="{$k}">{$mkpItem}</option>
    {/foreach}
</select>