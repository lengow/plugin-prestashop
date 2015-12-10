<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap.min.css.map">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-theme.min.css.map">
<link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/admin.css">

{assign var='controllerName' value=$smarty.get.controller}

<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <ul class="nav nav-pills">
            <li role="presentation" id="logoLengow">
            <img src="/modules/lengow/views/img/logo_lengow_V3.png" alt="lengow">
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
            ">Produits Lengow</a></li>
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
    </div>
</nav>

<ol class="breadcrumb">

    <li><a href="
    {if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
    {$link->getAdminLink('AdminLengowHome')}
    {else}#
    {/if}">Lengow</a></li>
    {if $controllerName != 'AdminLengowHome'}
    <li class="active">{$meta_title}</li>
    {/if}

</ol>