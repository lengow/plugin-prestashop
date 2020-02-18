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

<script type="text/javascript">$(document.body).addClass('lengow_body');</script>

<!-- PLUGINS -->
<link rel="stylesheet" type="text/css" href="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/css/bootstrap-datepicker.css">
<link rel="stylesheet" type="text/css" href="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/css/font-awesome.css">
<link rel="stylesheet" type="text/css" href="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/css/select2.css">
<!-- STYLE LENGOW -->
<link href="//fonts.googleapis.com/css?family=Lato:300,400,700,900,300italic,400italic,700italic,900italic|Open+Sans:700,600,800,400,300" type="text/css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/css/lengow-layout.css">
<link rel="stylesheet" type="text/css" href="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/css/lengow-components.css">
<link rel="stylesheet" type="text/css" href="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/css/lengow-pages.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
{if version_compare($smarty.const._PS_VERSION_,'1.5','<')&&version_compare($smarty.const._PS_VERSION_,'1.4','>=')}
    <link rel="stylesheet" type="text/css" href="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/css/lengow_bootstrap_14.css">
{/if}
{if version_compare($smarty.const._PS_VERSION_,'1.6','<')&&version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
    <link rel="stylesheet" type="text/css" href="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/css/lengow_bootstrap_15.css">
{/if}

{if $displayToolbar eq 1}
    <ul class="nav nav-pills lengow-nav lengow-nav-top">
        <li role="presentation" id="lengow_logo">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowHome')|escape:'htmlall':'UTF-8'}">
                <img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/lengow-white.png" alt="lengow">
            </a>
        </li>
        <li role="presentation" class="{if $current_controller == 'LengowFeedController'}active{/if}"><a href="
            {$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')|escape:'htmlall':'UTF-8'}">
                {$locale->t('menu.product')|escape:'htmlall':'UTF-8'}
            </a>
        </li>
        {assign var='OrderTab' value=','|explode:"LengowOrderController,LengowOrderSettingController"}
        <li role="presentation" class="{if in_array($current_controller, $OrderTab)}active{/if}">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder')|escape:'htmlall':'UTF-8'}" class="lengow_order_link">
                {$locale->t('menu.order')|escape:'htmlall':'UTF-8'}
                {if $total_pending_order > 0}
                    <span class="lengow-nav-notif">{$total_pending_order|escape:'htmlall':'UTF-8'}</span>
                {/if}
            </a>
        </li>
        <li class="lengow_float_right {if $current_controller == 'LengowMainSettingController'}active{/if}" id="menugotosetting">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|escape:'htmlall':'UTF-8'}"
                class="lengow_link_tooltip"
                data-placement="bottom"
                data-original-title="{$locale->t('menu.global_parameter')|escape:'htmlall':'UTF-8'}">
                <i class="fa fa-cog fa-2x"></i>
            </a>
        </li>
        <li class="lengow_float_right  {if $current_controller == 'LengowHelpController'}active{/if}" id="menugotohelp">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowHelp')|escape:'htmlall':'UTF-8'}"
                class="lengow_link_tooltip"
                data-placement="bottom"
                data-original-title="{$locale->t('menu.help')|escape:'htmlall':'UTF-8'}">
                <i class="fa fa-life-ring fa-2x"></i>
            </a>
        </li>
        <li class="lengow_float_right" id="menugotosolution">
            <a href="//my.{$lengowUrl|escape:'htmlall':'UTF-8'}" target="_blank">
                {$locale->t('menu.jump_to_lengow')|escape:'htmlall':'UTF-8'}
            </a>
        </li>
        {if $merchantStatus['type'] == 'free_trial' && !$merchantStatus['expired']}
            <li class="lengow_float_right" id="menucountertrial">
                <div class="lgw-block">
                    {$locale->t('menu.counter', ['counter' => $merchantStatus['day']])|escape:'htmlall':'UTF-8'}
                    <a href="//my.{$lengowUrl|escape:'htmlall':'UTF-8'}" target="_blank">
                        {$locale->t('menu.upgrade_account')|escape:'htmlall':'UTF-8'}
                    </a>
                </div>
            </li>
        {/if}
        {if $pluginData && $pluginData['version'] > $lengowVersion}
            <li class="lengow_float_right" id="menupluginavailable">
                <div class="lgw-block">
                    {$locale->t('menu.new_version_available', ['version' => $pluginData['version']])|escape:'htmlall':'UTF-8'}
                    <a href="//my.{$lengowUrl|escape:'htmlall':'UTF-8'}{$pluginData['download_link']|escape:'htmlall':'UTF-8'}" target="_blank">
                        {$locale->t('menu.download_plugin')|escape:'htmlall':'UTF-8'}
                    </a>
                </div>
            </li>
        {/if}
    </ul>
{/if}
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/jquery.1.12.0.min.js"></script>
<script type="text/javascript">
    var lengow_jquery = $.noConflict(true);
</script>
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/bootstrap.min.js"></script>
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/lengow/admin.js"></script>
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/clipboard.js"></script>
<script type="text/javascript" src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/js/select2.js"></script>
