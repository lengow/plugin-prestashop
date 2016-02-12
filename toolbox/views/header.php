<?php
/**
 * Copyright 2016 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lengow Toolbox</title>
    <link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/bootstrap-switch.min.css" rel="stylesheet">
    <link href="/modules/lengow/views/css/bootstrap-3.3.6.css" rel="stylesheet">
    <link href="/modules/lengow/views/css/toolbox.css" rel="stylesheet">
    <link rel="stylesheet" href="/modules/lengow/views/css/font-awesome.css">
    <script type="text/javascript" src="/modules/lengow/views/js/jquery.1.12.0.min.js"></script>
    <script type="text/javascript">
        var lengow_jquery = $.noConflict(true);
    </script>
    <script type="text/javascript" src="/modules/lengow/views/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/modules/lengow/views/js/lengow/admin.js"></script>
    <script type="text/javascript" src="/modules/lengow/views/js/bootstrap-switch.js"></script>
    <script type="text/javascript" src="/modules/lengow/views/js/bootstrap-select.min.js"></script>
    <script type="text/javascript" src="/modules/lengow/views/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="/modules/lengow/views/js/clipboard.js"></script>
</head>

<body>
<?php
$lengowTool = new LengowTool();
if ($lengowTool->isLogged()) {
    ?>
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="/modules/lengow/toolbox/index.php">
                    <i class="fa fa-rocket"></i> Lengow Toolbox
                </a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li><a href="/modules/lengow/toolbox/config.php"><i class="fa fa-cog"></i> Configuration</a></li>
                    <li><a href="/modules/lengow/toolbox/log.php"><i class="fa fa-file-text-o"></i> Logs</a></li>
                    <li>
                        <a href="/modules/lengow/toolbox/order.php"><i class="fa fa-shopping-basket"></i> Orders</a>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php
                    echo '<li><a href="/modules/lengow/toolbox/logoff.php">
                        <i class="fa fa-sign-out"></i> Log Off</a></li>';
                    ?>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>
    <?php
}
?>
<div class="container">
