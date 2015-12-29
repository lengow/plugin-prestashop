<!--<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap.min.css.map">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-theme.min.css.map">-->
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-switch.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/lengow_bootstrap.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/admin.css">
{if version_compare($smarty.const._PS_VERSION_,'1.5','<')}
    <link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/admin14.css">
{/if}
<link rel="stylesheet" href="/modules/lengow/views/css/font-awesome.css">


{assign var='controllerName' value=$smarty.get.controller}


<ul class="nav nav-pills lengow-nav">
    <li role="presentation" id="logoLengow">
        <img src="/modules/lengow/views/img/lengow-white.png" alt="lengow">
    </li>
    <li role="presentation" class="{if $controllerName == 'AdminLengowHome'}active{/if}" id="home"><a href="
            {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
            {$link->getAdminLink('AdminLengowHome')}
            {else}
            index.php?tab=AdminLengowHome14&token={Tools::getAdminTokenLite('AdminLengowHome14')}
            {/if}">Home</a></li>
    <li role="presentation" class="{if $controllerName == 'AdminLengowProduct'}active{/if}"><a href="
            {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
            {$link->getAdminLink('AdminLengowProduct')}
            {else}
            index.php?tab=AdminLengowProduct14&token={Tools::getAdminTokenLite('AdminLengowProduct14')}
            {/if}
            ">Lengow Products</a></li>
    <li role="presentation" class="{if $controllerName == 'AdminLengowLog'}active{/if}"><a href="
            {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
            {$link->getAdminLink('AdminLengowLog')}
            {else}
            index.php?tab=AdminLengowLog14&token={Tools::getAdminTokenLite('AdminLengowLog14')}
            {/if}
            ">Logs</a></li>
    <li role="presentation" class="{if $controllerName == 'AdminLengowConfig'}active{/if}"><a href="
            {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
            {$link->getAdminLink('AdminLengowConfig')}
            {else}
            index.php?tab=AdminLengowConfig14&token={Tools::getAdminTokenLite('AdminLengowConfig14')}
            {/if}">Configuration</a></li>
    <li role="presentation" class="{if $controllerName == 'AdminLengowLogConfig'}active{/if}"><a href="
            {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
            {$link->getAdminLink('AdminLengowLogConfig')}
            {else}
            index.php?tab=AdminLengowLogConfig14&token={Tools::getAdminTokenLite('AdminLengowLogConfig14')}
            {/if}">Configuration Logs</a></li>
</ul>

<ol class="breadcrumb lengow_breadcrumb">

    <li><a href="
    {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
    {$link->getAdminLink('AdminLengowHome')}
    {else}#
    {/if}">Lengow</a></li>
    {if $controllerName != 'AdminLengowHome'}
        <li class="active">{$meta_title}</li>
    {/if}

</ol>

<script type="text/javascript" src="/modules/lengow/views/js/jquery.1.11.3.min.js"></script>
<script type="text/javascript">
    var jQuery_1_11_3 = $.noConflict(true);
</script>
<script type="text/javascript" src="/modules/lengow/views/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/modules/lengow/views/js/admin.js"></script>
<script src="/modules/lengow/views/js/bootstrap-switch.js"></script>
