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

<script type="text/javascript">$(document.body).addClass('lengow_body');</script>


<!-- PLUGINS -->
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-switch.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-datepicker.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/font-awesome.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/select2.css">


<!-- STYLE LENGOW -->

<!--
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/lengow_bootstrap.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/admin.css">
-->
<link href="//fonts.googleapis.com/css?family=Lato:300,400,700,900,300italic,400italic,700italic,900italic|Open+Sans:700,600,800,400,300" type="text/css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/lengow-layout.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/lengow-components.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/lengow-pages.css">

{if version_compare($smarty.const._PS_VERSION_,'1.5','<')&&version_compare($smarty.const._PS_VERSION_,'1.4','>=')}
    <link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/lengow_bootstrap_14.css">
    <!--<script type="text/javascript">alert('version 1.4');</script>-->

{/if}
{if version_compare($smarty.const._PS_VERSION_,'1.6','<')&&version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
    <link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/lengow_bootstrap_15.css">
    <!--<script type="text/javascript">alert('version 1.5');</script>-->
{/if}


{if !$isNewMerchant}
    {if $lengow_configuration->getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED') eq '1'}
        <div id="lgw-preprod">
            {$locale->t('menu.preprod_active')|escape:'htmlall':'UTF-8'}
        </div>
    {/if}
    <ul class="nav nav-pills lengow-nav lengow-nav-top {if $lengow_configuration->getGlobalValue('LENGOW_IMPORT_PREPROD_ENABLED') eq '1'}preprod{/if}">
        <li role="presentation" id="lengow_logo">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowHome')|escape:'htmlall':'UTF-8'}">
                <img src="/modules/lengow/views/img/lengow-white.png" alt="lengow">
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
                {if $total_pending_order}
                    <span class="lengow-nav-notif">{$total_pending_order|escape:'htmlall':'UTF-8'}</span>
                {/if}
            </a>
        </li>

        <li class="lengow_float_right {if $current_controller == 'LengowMainSettingController'}active{/if}" id="menugotosetting">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowMainSetting')|escape:'htmlall':'UTF-8'}">
                <i class="fa fa-cog fa-2x"></i>
            </a>
        </li>
        <li class="lengow_float_right" id="menugotosolution">
            <a href="http://solution.lengow.com" target="_blank">
                <i class="fa fa-external-link fa-2x"></i>
            </a>
        </li>
        <li class="lengow_float_right  {if $current_controller == 'LengowHelpController'}active{/if}" id="menugotohelp">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowHelp')|escape:'htmlall':'UTF-8'}">
                <i class="fa fa-life-ring fa-2x"></i>
            </a>
        </li>
    </ul>
{/if}
<script type="text/javascript" src="/modules/lengow/views/js/jquery.1.12.0.min.js"></script>
<script type="text/javascript">
    var lengow_jquery = $.noConflict(true);
</script>
<script type="text/javascript" src="/modules/lengow/views/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/modules/lengow/views/js/lengow/admin.js"></script>
<script type="text/javascript" src="/modules/lengow/views/js/bootstrap-switch.js"></script>
<script type="text/javascript" src="/modules/lengow/views/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="/modules/lengow/views/js/clipboard.js"></script>
<script type="text/javascript" src="/modules/lengow/views/js/select2.js"></script>
