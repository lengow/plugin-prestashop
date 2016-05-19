{*
 * Copyright 2016 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 *  @author	   Team Connector <team-connector@lengow.com>
 *  @copyright 2016 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
<select id="select_country" data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting', true)|escape:'htmlall':'UTF-8'}" class="lengow_select">
	<option value="" disabled selected hidden>
		{$locale->t('order_setting.screen.select_a_country')|escape:'htmlall':'UTF-8'}
	</option>
		{foreach from=$countries item=country}
			{if not in_array($country['id_country'],$id_countries)}
				<option value="{$country['id_country']|escape:'htmlall':'UTF-8'}"
                data-image="/modules/lengow/views/img/flag/{$country["iso_code"]|escape:'htmlall':'UTF-8'}.png">
					{$country['name']|escape:'htmlall':'UTF-8'}</option>
			{/if}
		{/foreach}
</select>
<button type="button" data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting', true)|escape:'htmlall':'UTF-8'}"
	class="lgw-btn add_lengow_default_carrier">
	<i class="fa fa-plus"></i> {$locale->t('order_setting.screen.button_add_country')|escape:'htmlall':'UTF-8'}
</button>
<a href="#" class="sub-link js-cancel-country">Cancel</a>