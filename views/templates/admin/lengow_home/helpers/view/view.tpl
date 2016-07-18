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

<script type="text/javascript">$(document.body).addClass("adminlengowhome");</script>
<div id="lengow_home_wrapper">
{if $isNewMerchant || $isSync }
    {include file='./new.tpl'}
{elseif ($merchantStatus['type'] == 'free_trial' && $merchantStatus['day'] eq 0) || $merchantStatus['type'] == 'bad_payer'}
    {include file='./status.tpl'}
{else}
    {include file='./connect.tpl'}
{/if}
</div>
<script type="text/javascript" src="/modules/lengow/views/js/lengow/home.js"></script>