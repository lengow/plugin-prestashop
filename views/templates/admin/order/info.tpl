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
	<legend><img src="../img/admin/tab-stats.gif" /> {l s='Import Lengow' mod='lengow'}</legend>
	<h4>{l s='This order has been imported from Lengow' mod='lengow'}</h4>
	<ul>
		<li>{l s='Lengow order ID' mod='lengow'} : <strong>{$id_order_lengow|escape:'htmlall':'UTF-8'}</strong></li>
		<li>{l s='Marketplace' mod='lengow'} : <strong>{$marketplace|escape:'htmlall':'UTF-8'}</strong></li>
		{if $id_flux != 0}
			<li>{l s='Feed ID' mod='lengow'} : <strong>{$id_flux|escape:'htmlall':'UTF-8'}</strong></li>
		{else}
			<li>{l s='ID order line' mod='lengow'} : <strong>{$id_order_line|escape:'htmlall':'UTF-8'}</strong></li>
		{/if}
		<li>{l s='Total amount paid on Marketplace' mod='lengow'} : <strong>{$total_paid|escape:'htmlall':'UTF-8'}</strong></li>
		<li>{l s='Carrier from marketplace' mod='lengow'} : <strong>{$tracking_carrier|escape:'htmlall':'UTF-8'}</strong></li>
		<li>{l s='Shipping method' mod='lengow'} : <strong>{$tracking_method|escape:'htmlall':'UTF-8'}</strong></li>
		<li>{l s='Tracking number' mod='lengow'} : <strong>{$tracking|escape:'htmlall':'UTF-8'}</strong></li>
		<li>{l s='Message' mod='lengow'} : <strong>{$message|escape:'htmlall':'UTF-8'}</strong></li>
		<li>{l s='Shipping by marketplace' mod='lengow'} : <strong>{$sent_markeplace|escape:'htmlall':'UTF-8'}</strong>
	</ul>
	<br />
	<div class"button-command-prev-next">
		{if $version < 1.5}
			<button id="reimport-order" class="button" data-url="{$action_reimport|escape:'htmlall':'UTF-8'}" data-orderid="{$order_id|escape:'htmlall':'UTF-8'}" data-lengoworderid="{$id_order_lengow|escape:'htmlall':'UTF-8'}" data-version="{$version|escape:'htmlall':'UTF-8'}">{l s='Cancel and re-import order' mod='lengow'}</button>
		{else}
			<a class="button" href="{$action_reimport|escape:'htmlall':'UTF-8'}">{l s='Cancel and re-import order' mod='lengow'}</a>
		{/if}
		<a class="button" href="{$action_synchronize|escape:'htmlall':'UTF-8'}">{l s='Synchronize ID' mod='lengow'}</a>
	</div>
</fieldset>
{if $add_script == true}
<script type="text/javascript" src="{$url_script|escape:'htmlall':'UTF-8'}"></script>
{/if}