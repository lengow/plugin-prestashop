<div id="lengow_statistic">
    <h2>{$locale->t('dashboard.screen.stat_with_lengow_you_make')}</h2>
    <ul>
        <li>
            <span class="lengow_number">{$stats['total_order']}</span>
            <span class="lengow_description">{$locale->t('dashboard.screen.stat_turnover')}</span>
        </li>
        <li>
            <span class="lengow_number">{$stats['nb_order']}</span>
            <span class="lengow_description">{$locale->t('dashboard.screen.stat_nb_orders')}</span>
        </li>
        <li>
            <span class="lengow_number">{$stats['average_order']}</span>
            <span class="lengow_description">{$locale->t('dashboard.screen.stat_avg_order')}</span>
        </li>
    </ul>
    <a href="http://solution.lengow.com" target="_blank">{$locale->t('dashboard.screen.stat_more_stats')}</a>
    <br/><br/>
    <div id="lengow_ads">
        <img src="http://fakeimg.pl/360x360/">
    </div>
</div>

<div id="lengow_dashboard_center">
    <div id="lengow_center_block">
        {$locale->t('dashboard.screen.good_job')}<br/>
        <span class="lengow_pending_order">
            <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowOrder')|escape:'htmlall':'UTF-8'}">
                {$total_pending_order} {$locale->t('dashboard.screen.pending_order')}
            </a>
        </span>
        <span class="lengow_pending_message">{$locale->t('dashboard.screen.sell_everywhere')}</span><br/>
    </div>
</div>


<div class="lengow_clear"></div>