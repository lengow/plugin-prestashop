
<div class="lgw-container">
    <div class="lgw-content-section text-center">
        <div id="lgw-footer">
            {if $isNewMerchant || $isSync }
                <p class="text-center"><a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowLegals')|escape:'htmlall':'UTF-8'}" class="sub-link" title="Legal">Legals</a> | Copyright 2016</p>
            {else}
                <p class="pull-right"><a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowLegals')|escape:'htmlall':'UTF-8'}" class="sub-link" title="Legal">Legals</a> | Copyright 2016</p>
            {/if}

        </div>
    </div>
</div>
