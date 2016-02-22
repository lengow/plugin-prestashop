<div id="lengow_statistic">
    <h2>With Lengow, you've made</h2>
    <ul>
        <li><span class="lengow_number">{$stats['total_order']}</span><span class="lengow_description">Turnover</span></li>
        <li><span class="lengow_number">{$stats['nb_order']}</span><span class="lengow_description">Orders</span></li>
        <li><span class="lengow_number">{$stats['average_order']}</span><span class="lengow_description">Avg. Order</span></li>
    </ul>
    <a href="http://solution.lengow.com" target="_blank">Want more stats ? Go to Lengow</a>
    <br/><br/>
    <div id="lengow_ads">
        <img src="http://fakeimg.pl/360x360/">
    </div>
</div>

<div id="lengow_dashboard_center">
    <div id="lengow_center_block">
        Good Job ! You have<br/>
        <span class="lengow_pending_order">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder')|escape:'htmlall':'UTF-8'}">
                {$total_pending_order} Pending Orders
            </a>
        </span><br/>
        <span class="lengow_pending_message">What about go event hurther and start selling everywhere ?</span><br/>
    </div>
</div>


<div class="lengow_clear"></div>