<div id="lengow_feed_wrapper">
    {foreach from=$shopCollection  item=shop}
        <div class="lengow_feed_block">
            <div class="lengow_feed_block_header">
                <div class="lengow_feed_block_header_title">
                    <span class="title">{$shop['shop']->name}</span><span class="url">{$shop['link']}</span>
                </div>
                <div class="lengow_feed_block_header_content">
                    YEAH
                </div>
            </div>
        </div>
    {/foreach}
</div>

