<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap.min.css.map">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-theme.min.css.map">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-switch.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/admin.css">

{assign var='controllerName' value=$smarty.get.controller}


<ul class="nav nav-pills menuLengow">
    <li role="presentation" id="logoLengow">
        <img src="/modules/lengow/views/img/lengow-white.png" alt="lengow">
    </li>
    <li role="presentation" class="{if $controllerName == 'AdminLengowHome'}active{/if}" id="home"><a href="
            {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
            {$link->getAdminLink('AdminLengowHome')}
            {else}#
            {/if}">Home</a></li>
    <li role="presentation" class="{if $controllerName == 'AdminLengow'}active{/if}"><a href="
            {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
            {$link->getAdminLink('AdminLengow')}
            {else}
            index.php?tab=AdminLengow14&token={$token}
            {/if}
            ">Lengow Products</a></li>
    <li role="presentation" class="{if $controllerName == 'AdminLengowLog'}active{/if}"><a href="
            {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
            {$link->getAdminLink('AdminLengowLog')}
            {else}
            index.php?tab=AdminLengowLog14&token={$token2}
            {/if}
            ">Logs</a></li>
    <li role="presentation" class="{if $controllerName == 'AdminLengowConfig'}active{/if}"><a href="
            {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
            {$link->getAdminLink('AdminLengowConfig')}
            {else}#
            {/if}">Configuration</a></li>
    <li role="presentation" class="{if $controllerName == 'AdminLengowLogConfig'}active{/if}"><a href="
            {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
            {$link->getAdminLink('AdminLengowLogConfig')}
            {else}#
            {/if}">Configuration Logs</a></li>
</ul>

<ol class="breadcrumb breadcrumbLengow">

    <li><a href="
    {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
    {$link->getAdminLink('AdminLengowHome')}
    {else}#
    {/if}">Lengow</a></li>
    {if $controllerName != 'AdminLengowHome'}
        <li class="active">{$meta_title}</li>
    {/if}

</ol>