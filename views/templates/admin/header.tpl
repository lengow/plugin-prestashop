<!--<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap.min.css.map">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-theme.min.css.map">-->
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-switch.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/lengow_bootstrap.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/admin.css">
<link rel="stylesheet" href="/modules/lengow/views/css/font-awesome.css">

<ul class="nav nav-pills lengow-nav">
    <li class="lengow_float_right lengow_external_link"><a href="#"><i class="fa fa-external-link"></i></a></li>
    <li class="lengow_float_right lengow_ring">
        <a href="http://solution.lengow.com" target="_blank"><i class="fa fa-life-ring"></i></a>
    </li>
    <li role="presentation" id="lengow_logo">
        <a href="{$lengow_link->getAbsoluteAdminLink('AdminLengowHome')}">
            <img src="/modules/lengow/views/img/lengow-white.png" alt="lengow">
        </a>
    </li>
    <li role="presentation" class="{if $current_controller == 'LengowFeedController'}active{/if}"><a href="
            {$lengow_link->getAbsoluteAdminLink('AdminLengowFeed')}">Product</a></li>
    <li role="presentation" class="{if $current_controller == 'AdminLengowOrder'}active{/if}"><a href="
            {$lengow_link->getAbsoluteAdminLink('AdminLengowOrder')}">Orders</a></li>
    <li role="presentation" class="{if $current_controller == 'AdminLengowLog'}active{/if}"><a href="
            {$lengow_link->getAbsoluteAdminLink('AdminLengowLog')}">Logs</a></li>
</ul>

<ol class="breadcrumb lengow_breadcrumb">
    <li><a href="
    {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
    {$link->getAdminLink('AdminLengowHome')}
    {else}#
    {/if}">Lengow</a></li>
    {if $current_controller != 'AdminLengowHome'}
        <li class="active">{$breadcrumb_title}</li>
    {/if}
</ol>
<script type="text/javascript" src="/modules/lengow/views/js/jquery.1.12.0.min.js"></script>
<script type="text/javascript">
    var lengow_jquery = $.noConflict(true);
</script>
<script type="text/javascript" src="/modules/lengow/views/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/modules/lengow/views/js/admin.js"></script>
<script type="text/javascript" src="/modules/lengow/views/js/bootstrap-switch.js"></script>
<script type="text/javascript" src="/modules/lengow/views/js/clipboard.js"></script>
