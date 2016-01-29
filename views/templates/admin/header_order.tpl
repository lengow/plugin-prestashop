<ul class="nav nav-pills lengow-nav lengow-nav-bottom">
	<li role="presentation" class="{if $current_controller == 'AdminLengowOrder'}active{/if}"><a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder')}">Overview</a></li>
	<li role="presentation" class="{if $current_controller == 'AdminLengowOrderSetting'}active{/if}"><a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrderSetting')}">Parameters</a></li>
</ul>