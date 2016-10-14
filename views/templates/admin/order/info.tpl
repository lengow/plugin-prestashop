{*
 * Copyright 2015 Lengow SAS.
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
 *  @copyright 2015 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
<br />
<fieldset>
	<legend>{$lengow_locale->t('admin.order.import_lengow')|escape:'htmlall':'UTF-8'}</legend>
	<h4>{$lengow_locale->t('admin.order.imported_from_lengow')|escape:'htmlall':'UTF-8'}</h4>
	<ul>
		<li>
			{$lengow_locale->t('admin.order.lengow_order_id')|escape:'htmlall':'UTF-8'} :
			<strong>{$marketplace_sku|escape:'htmlall':'UTF-8'}</strong>
		</li>
		<li>
			{$lengow_locale->t('admin.order.marketplace')|escape:'htmlall':'UTF-8'} :
			<strong>{$marketplace_name|escape:'htmlall':'UTF-8'}</strong>
		</li>
		{if $id_flux != 0}
			<li>
				{$lengow_locale->t('admin.order.feed_id')|escape:'htmlall':'UTF-8'} :
				<strong>{$id_flux|escape:'htmlall':'UTF-8'}</strong>
			</li>
		{/if}
		<li>
			{$lengow_locale->t('admin.order.total_paid')|escape:'htmlall':'UTF-8'} :
			<strong>{$total_paid|escape:'htmlall':'UTF-8'}</strong>
		</li>
		<li>
			{$lengow_locale->t('admin.order.carrier_from_marketplace')|escape:'htmlall':'UTF-8'} :
			<strong>{$tracking_carrier|escape:'htmlall':'UTF-8'}</strong>
		</li>
		<li>
			{$lengow_locale->t('admin.order.method_from_marketplace')|escape:'htmlall':'UTF-8'} :
			<strong>{$tracking_method|escape:'htmlall':'UTF-8'}</strong>
		</li>
		<li>
			{$lengow_locale->t('admin.order.tracking_number')|escape:'htmlall':'UTF-8'} :
			<strong>{$tracking|escape:'htmlall':'UTF-8'}</strong>
		</li>
		<li>
			{$lengow_locale->t('admin.order.customer_email')|escape:'htmlall':'UTF-8'} :
			<strong>{$customer_email|escape:'htmlall':'UTF-8'}</strong>
		</li>
		<li>
			{$lengow_locale->t('admin.order.message')|escape:'htmlall':'UTF-8'} :
			<strong>{$message|escape:'htmlall':'UTF-8'}</strong>
		</li>
		<li>
			{$lengow_locale->t('admin.order.shipped_by_marketplace')|escape:'htmlall':'UTF-8'} :
			<strong>{$sent_markeplace|escape:'htmlall':'UTF-8'}</strong>
		</li>
	</ul>
	<br />
	{if !$preprod_mode}
		<div class"button-command-prev-next">
			<a class="button" 
				href="{$action_reimport|escape:'htmlall':'UTF-8'}" 
				onclick="return confirm('{$lengow_locale->t('admin.order.check_cancel_and_reimport')|escape:'htmlall':'UTF-8'}')">
				{$lengow_locale->t('admin.order.cancel_and_reimport')|escape:'htmlall':'UTF-8'}
			</a>
			<a class="button" href="{$action_synchronize|escape:'htmlall':'UTF-8'}">
				{$lengow_locale->t('admin.order.synchronize_id')|escape:'htmlall':'UTF-8'}
			</a>
			{if $can_add_tracking }
				<a class="button" onclick="getValue()">
					{$lengow_locale->t('admin.order.add_tracking')|escape:'htmlall':'UTF-8'}
				</a>
			{/if}
			{if $can_resend_action}
				<a class="button"
					href="{$action_resend|escape:'htmlall':'UTF-8'}"
					onclick="return confirm('{$check_resend_action|escape:'htmlall':'UTF-8'}')">
					{$lengow_locale->t('admin.order.resend_action')|escape:'htmlall':'UTF-8'}
				</a>
			{/if}
		</div>
	{/if}
</fieldset>

<script type="text/javascript">
    function getValue() {
        var tracking_number = prompt("{$lengow_locale->t('admin.order.add_tracking_title')|escape:'htmlall':'UTF-8'}");
        if (tracking_number.length > 0) {
        	var url = "{html_entity_decode($action_add_tracking|escape:'htmlall':'UTF-8')}" + tracking_number;
        	document.location.href=url;
        }
    }
</script>
