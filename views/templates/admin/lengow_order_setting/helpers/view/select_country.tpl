<select id="select_country" data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting', true)}" class="lengow_select">
	<option value="" ></option>
		{foreach from=$countries item=country}
			{if not in_array($country['id_country'],$id_countries)}
				<option value="{$country['id_country']}">{$country['name']}</option>
			{/if}
		{/foreach}
</select>
<button type="button" class="btn add_lengow_default_carrier">+ Ajouter</button>