{*
 * Copyright 2017 Lengow SAS.
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
 *  @copyright 2017 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
<div class="row">
	<div class="col-lg-12">
		<div class="panel card">
			<div class="panel-heading card-header">
				<i class="icon-shopping-cart"></i>
				{$lengow_locale->t('admin.order.imported_from_lengow')|escape:'htmlall':'UTF-8'}
			</div>
			<div class="card-body">
				<div class="info-block mt-2">
					<div class="row">
						<div class="col-md-6">
							<dl class="well list-detail">
								<dt>{$lengow_locale->t('admin.order.lengow_order_id')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{$marketplace_sku|escape:'htmlall':'UTF-8'}</dd>
								<dt>{$lengow_locale->t('admin.order.marketplace')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{$marketplace_label|escape:'htmlall':'UTF-8'}</dd>
								<dt>{$lengow_locale->t('admin.order.currency')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{$currency|escape:'htmlall':'UTF-8'}</dd>
								<dt>{$lengow_locale->t('admin.order.total_paid')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{$total_paid|escape:'htmlall':'UTF-8'}</dd>
								<dt>{$lengow_locale->t('admin.order.commission')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{if $commission}{$commission|escape:'htmlall':'UTF-8'}{else}--{/if}</dd>
								{if $id_flux != 0}
									<dt>{$lengow_locale->t('admin.order.feed_id')|escape:'htmlall':'UTF-8'}</dt>
									<dd>{$id_flux|escape:'htmlall':'UTF-8'}</dd>
								{else}
									<dt>{$lengow_locale->t('admin.order.delivery_address_id')|escape:'htmlall':'UTF-8'}</dt>
									<dd>{$delivery_address_id|escape:'htmlall':'UTF-8'}</dd>
								{/if}
								<dt>{$lengow_locale->t('admin.order.customer_name')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{$customer_name|escape:'htmlall':'UTF-8'}</dd>
								<dt>{$lengow_locale->t('admin.order.customer_email')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{if $customer_email}{$customer_email|escape:'htmlall':'UTF-8'}{else}--{/if}</dd>
								<dt>{$lengow_locale->t('admin.order.is_express')|escape:'htmlall':'UTF-8'}</dt>
								<dd>
									{if $is_express}
										{$lengow_locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}
									{else}
										{$lengow_locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}
									{/if}
								</dd>
								<dt>{$lengow_locale->t('admin.order.is_delivered_by_marketplace')|escape:'htmlall':'UTF-8'}</dt>
								<dd>
									{if $is_delivered_by_marketplace}
										{$lengow_locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}
									{else}
										{$lengow_locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}
									{/if}
								</dd>
								<dt>{$lengow_locale->t('admin.order.is_business')|escape:'htmlall':'UTF-8'}</dt>
								<dd>
									{if $is_business}
										{$lengow_locale->t('product.screen.button_yes')|escape:'htmlall':'UTF-8'}
									{else}
										{$lengow_locale->t('product.screen.button_no')|escape:'htmlall':'UTF-8'}
									{/if}
								</dd>
								<dt>{$lengow_locale->t('admin.order.customer_vat_number')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{if $customer_vat_number}{$customer_vat_number|escape:'htmlall':'UTF-8'}{else}--{/if}</dd>
							</dl>
						</div>
						<div class="col-md-6">
							<dl class="well list-detail">
								<dt>{$lengow_locale->t('admin.order.carrier')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{if $carrier}{$carrier|escape:'htmlall':'UTF-8'}{else}--{/if}</dd>
								<dt>{$lengow_locale->t('admin.order.carrier_method')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{if $carrier_method}{$carrier_method|escape:'htmlall':'UTF-8'}{else}--{/if}</dd>
								<dt>{$lengow_locale->t('admin.order.carrier_id_relay')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{if $carrier_id_relay}{$carrier_id_relay|escape:'htmlall':'UTF-8'}{else}--{/if}</dd>
								<dt>{$lengow_locale->t('admin.order.carrier_tracking')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{if $carrier_tracking}{$carrier_tracking|escape:'htmlall':'UTF-8'}{else}--{/if}</dd>
								<dt>{$lengow_locale->t('admin.order.message')|escape:'htmlall':'UTF-8'}</dt>
								<dd>{if $message}{$message|escape:'htmlall':'UTF-8'}{else}--{/if}</dd>
								<dt>{$lengow_locale->t('admin.order.imported_at')|escape:'htmlall':'UTF-8'}</dt>
								<dd><i class="icon-calendar-o text-muted"></i> {dateFormat date=$imported_at full=true}</dd>
								<dt>{$lengow_locale->t('admin.order.json_format')|escape:'htmlall':'UTF-8'}</dt>
								<dd>
									<textarea readonly style="overflow-wrap: break-word; resize: none; height: 236px; width: 100%">{$extra|escape:'htmlall':'UTF-8'}</textarea>
								</dd>
						</div>
					</div>
				</div>
				{if !$debug_mode}
					<div class="text-left mt-3">
						<a class="btn btn-primary"
						   href="{$action_reimport|escape:'htmlall':'UTF-8'}"
						   onclick="return confirm('{$lengow_locale->t('admin.order.check_cancel_and_reimport')|escape:'htmlall':'UTF-8'}')">
							{$lengow_locale->t('admin.order.cancel_and_reimport')|escape:'htmlall':'UTF-8'}
						</a>
						<a class="btn btn-primary ml-3" href="{$action_synchronize|escape:'htmlall':'UTF-8'}">
							{$lengow_locale->t('admin.order.synchronize_id')|escape:'htmlall':'UTF-8'}
						</a>
						{if $can_resend_action}
							<a class="btn btn-primary ml-3"
							   href="{$action_resend|escape:'htmlall':'UTF-8'}"
							   onclick="return confirm('{$check_resend_action|escape:'htmlall':'UTF-8'}')">
								{$lengow_locale->t('admin.order.resend_action')|escape:'htmlall':'UTF-8'}
							</a>
						{/if}
					</div>
				{/if}
			</div>
		</div>
	</div>
</div>
