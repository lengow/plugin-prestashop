<form class="lengow_form" method="POST">
<input type="hidden" name="action" value="process">
	<div class="container">
		<h2>Order Status</h2>
		<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod	tempor incididunt ut labore et dolore magna aliqua.</p><br/>
		{$matching}
	</div>
	<div class="container2">
		<h2>Carrier Management</h2>
		<h3>Default carrier</h3>
		<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod	tempor incididunt ut labore et dolore magna aliqua.</p><br/>
		<div id="add_country">
		{foreach item=itemCarrier from=$listCarrier}
			{include file='./default_carrier.tpl'}
		{/foreach}
		</div>
		<div class="select_country">
			{include file='./select_country.tpl'}
		</div>
		{$matching2}
		<h3>Marketplace carrier management</h3>
		{$matching3}
	</div>
	<div class="container2">
		<h2>Orders importation</h2>
		<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod	tempor incididunt ut labore et dolore magna aliqua.</p><br/>
		{$matching4}
		<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod	tempor incididunt ut labore et dolore magna aliqua.</p><br/>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn lengow_btn">Save</button>
		</div>
	</div>
</form>