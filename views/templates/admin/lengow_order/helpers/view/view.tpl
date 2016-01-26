<div id="lengow_order_wrapper">
    <div class="lengow_order_block_header">
        <div class="lengow_order_block_header_content">
            <div class="lengow_order_block_content_left">
                {if $orderCollection['last_import_type'] != 'none'}
                    Last order importation {($orderCollection['last_import_type'] == 'cron')?'(auto)':'(manual)'}<br/>
                    {$orderCollection['last_import_date']|date_format:"%A %e %B %Y @ %R"}
                {else}
                    No order importation for now
                {/if}
            </div>
            <div class="lengow_order_block_content_right">
                <a class="lengow_btn" href="{$orderCollection['link']}" target="_blank">Update orders</a>
            </div>
            <div class="lengow_clear"></div>
        </div>
    </div>
    <div>
        <div id="lengow_order_table_wrapper">
            {$lengow_table}
        </div>
    </div>
</div>


<script type="text/javascript" src="/modules/lengow/views/js/order.js"></script>