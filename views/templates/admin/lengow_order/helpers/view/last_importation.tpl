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

{if $orderCollection['last_import_type'] != 'none'}
	<span class="lengow_strong">{$locale->t('order.screen.last_order_importation')|escape:'htmlall':'UTF-8'}</span>
	{if $orderCollection['last_import_type'] == 'cron'}
	    ({$locale->t('order.screen.import_auto')|escape:'htmlall':'UTF-8'})
	{else}
	    ({$locale->t('order.screen.import_manuel')|escape:'htmlall':'UTF-8'})
	{/if}
	{$orderCollection['last_import_date']|date_format:"%A %e %B %Y @ %R"|escape:'htmlall':'UTF-8'}
{else}
	<span class="lengow_strong">{$locale->t('order.screen.no_order_importation')|escape:'htmlall':'UTF-8'}</span>
{/if}
<br/>
<p>
	{$locale->t('order.screen.all_order_will_be_sent_to')|escape:'htmlall':'UTF-8'} {', '|implode:$report_mail_address|escape:'htmlall':'UTF-8'}
	(<a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|escape:'htmlall':'UTF-8'}">{$locale->t('order.screen.change_this')|escape:'htmlall':'UTF-8'}</a>)
</p>
{if not $cron_active}
	<p>
		<a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting')|escape:'htmlall':'UTF-8'}#cron_setting">{$locale->t('order.screen.cron')|escape:'htmlall':'UTF-8'}</a>
	</p>
{/if}
