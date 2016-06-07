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
 *  @author    Team Connector <team-connector@lengow.com>
 *  @copyright 2015 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
<div class="row">
	<div class="col-lg-12">
		<div class="panel">
			<div class="panel-heading">
				<i class="icon-shopping-cart"></i>
				{$lengow_locale->t('admin.order.imported_from_lengow')|escape:'htmlall':'UTF-8'}
			</div>
			<div class="well">
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
			</div>
			<div class="btn-group">
				<a class="btn btn-default" 
					href="{$action_reimport|escape:'htmlall':'UTF-8'}" 
					onclick="return confirm('{$lengow_locale->t('admin.order.check_cancel_and_reimport')|escape:'htmlall':'UTF-8'}')">
					{$lengow_locale->t('admin.order.cancel_and_reimport')|escape:'htmlall':'UTF-8'}
				</a>
				<a class="btn btn-default" href="{$action_synchronize|escape:'htmlall':'UTF-8'}">
					{$lengow_locale->t('admin.order.synchronize_id')|escape:'htmlall':'UTF-8'}
				</a>
			</div>
		</div>
	</div>
</div>
