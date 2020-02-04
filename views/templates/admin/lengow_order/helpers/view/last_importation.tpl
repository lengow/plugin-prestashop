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
 *  @author	   Team Connector <team-connector@lengow.com>
 *  @copyright 2017 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}

{if $orderCollection['last_import_type'] != 'none'}
	<p>
		{$locale->t('order.screen.last_order_importation')|escape:'htmlall':'UTF-8'}
	 	: <b>{$orderCollection['last_import_date']|escape:'htmlall':'UTF-8'}</b>
{else}
	{$locale->t('order.screen.no_order_importation')|escape:'htmlall':'UTF-8'}
{/if}
</p>
{if $lengow_configuration->getGlobalValue('LENGOW_REPORT_MAIL_ENABLED') eq '1'}
    <p>
    	{$locale->t('order.screen.all_order_will_be_sent_to')|escape:'htmlall':'UTF-8'} {', '|implode:$report_mail_address|escape:'htmlall':'UTF-8'}
    	(<a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|escape:'htmlall':'UTF-8'}">{$locale->t('order.screen.change_this')|escape:'htmlall':'UTF-8'}</a>)
    </p>
{/if}
