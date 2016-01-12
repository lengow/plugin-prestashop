<div id="lengow_feed_wrapper">
    {foreach from=$shopCollection  item=shop}
        <div class="lengow_feed_block" id="block_{$shop['shop']->id}">
            <div class="lengow_feed_block_header">
                <div class="lengow_feed_block_header_title">
                    <span class="title">{$shop['shop']->name}</span><span class="url">http://{$shop['shop']->domain}</span>
                </div>
                <div class="lengow_feed_block_header_content">
                    <div class="lengow_feed_block_content_right">
                        <span class="lengow_exported">{$shop['total_export_product']}</span> exported<br/>
                        <span class="lengow_total">{$shop['total_product']}</span> available<br/>
                        <input type="checkbox" data-size="mini" data-on-text="Yes" data-off-text="No"
                               name="lengow_export_selection" class="lengow_switch lengow_switch_option"
                               data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')}"
                               data-action="change_option_selected"
                               data-id_shop="{$shop['shop']->id}"
                               value="1" {if $shop['option_selected'] == 1} checked="checked"{/if}>
                        <span class="lengow_select_text">Select specific products</span><br/>
                        <input type="checkbox" data-size="mini" data-on-text="Yes" data-off-text="No"
                               name="lengow_export_selection" class="lengow_switch lengow_switch_option"
                               data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')}"
                               data-action="change_option_product_variation"
                               data-id_shop="{$shop['shop']->id}"
                               value="1" {if $shop['option_variation'] == 1} checked="checked"{/if}>
                        <span class="lengow_select_text">Include product variation</span><br/>
                        <input type="checkbox" data-size="mini" data-on-text="Yes" data-off-text="No"
                               name="lengow_export_selection" class="lengow_switch lengow_switch_option"
                               data-href="{$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')}"
                               data-action="change_option_product_out_of_stock"
                               data-id_shop="{$shop['shop']->id}"
                               value="1" {if $shop['option_product_out_of_stock'] == 1} checked="checked"{/if}>
                        <span class="lengow_select_text">Include out of stock product</span>
                    </div>
                    <div class="lengow_feed_block_content_left">
                        This is your exported catalog. Copy this link in your Lengow platform<br/>
                        {$shop['link']}<br/>
                        {if $shop['last_export']}
                            Last exportation {$shop['last_export']}<br/>
                        {else}
                            No export<br/>
                        {/if}
                        <a class="btn btn-primary" href="{$shop['link']}" target="_blank">Launch new Export</a>
                    </div>
                    <div class="lengow_clear"></div>
                </div>
            </div>

            <div class="lengow_feed_block_footer">
                <div class="lengow_feed_block_footer_content" style="{if !$shop['option_selected']}display:none;{/if}">
                    {$shop['list']}
                </div>
            </div>
        </div>
    {/foreach}
</div>

<script type="text/javascript" src="/modules/lengow/views/js/feed.js"></script>
