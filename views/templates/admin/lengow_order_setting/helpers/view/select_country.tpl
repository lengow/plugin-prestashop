<select id="select_country" data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting', true)}" class="lengow_select">
	<option value="" disabled selected hidden>{$locale->t('order_setting.screen.select_a_country')}</option>
		{foreach from=$countries item=country}
			{if not in_array($country['id_country'],$id_countries)}
				<option value="{$country['id_country']}">{$country['name']}</option>
			{/if}
		{/foreach}
</select>
<button type="button" data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting', true)|escape:'htmlall':'UTF-8'}"
		class="btn add_lengow_default_carrier">+ {$locale->t('order_setting.screen.button_add_country')}</button>