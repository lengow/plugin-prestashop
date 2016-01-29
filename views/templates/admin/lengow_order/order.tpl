<ul class="nav nav-pills lengow-nav">
	<li role="presentation" class="{if $current_controller == 'AdminLengowOverviewOrder'}active{/if}"><a href="#">Overview</a></li>
	<li role="presentation" class="{if $current_controller == 'AdminLengowOrderSetting'}active{/if}"><a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting')}">Parameters</a></li>
</ul>