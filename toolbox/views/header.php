<?php
/**
 * Copyright 2017 Lengow SAS.
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
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

$lengowPathUri = __PS_BASE_URI__ . 'modules/lengow/';
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Lengow Toolbox</title>
        <link rel="stylesheet" href="<?php echo $lengowPathUri; ?>views/css/bootstrap-3.3.6.css">
        <link rel="stylesheet" href="<?php echo $lengowPathUri; ?>views/css/toolbox.css">
        <link rel="stylesheet" href="<?php echo $lengowPathUri; ?>views/css/font-awesome.css">
        <script type="text/javascript" src="<?php echo $lengowPathUri; ?>views/js/jquery.1.12.0.min.js"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $lengowPathUri; ?>views/css/select2.css">
        <script type="text/javascript">
            var lengow_jquery = $.noConflict(true);
        </script>
        <script type="text/javascript" src="<?php echo $lengowPathUri; ?>views/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo $lengowPathUri; ?>views/js/lengow/admin.js"></script>
        <script type="text/javascript" src="<?php echo $lengowPathUri; ?>views/js/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="<?php echo $lengowPathUri; ?>views/js/clipboard.js"></script>
        <script type="text/javascript" src="<?php echo $lengowPathUri; ?>views/js/select2.js"></script>
    </head>

<body>
<?php
$lengowTool = new LengowTool();
$locale = new LengowTranslation();
if ($lengowTool->isLogged()) {
    ?>
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href="<?php echo $lengowPathUri; ?>toolbox/index.php">
                    <i class="fa fa-rocket"></i> <?php echo $locale->t('toolbox.menu.lengow_toolbox'); ?>
                </a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="<?php echo $lengowPathUri; ?>toolbox/product.php">
                            <i class="fa fa-bicycle"></i> <?php echo $locale->t('toolbox.menu.product'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $lengowPathUri; ?>toolbox/order.php">
                            <i class="fa fa-shopping-basket"></i> <?php echo $locale->t('toolbox.menu.order'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $lengowPathUri; ?>toolbox/config.php">
                            <i class="fa fa-cog"></i> <?php echo $locale->t('toolbox.menu.configuration'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $lengowPathUri; ?>toolbox/checksum.php">
                            <i class="fa fa-search"></i> <?php echo $locale->t('toolbox.menu.checksum'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $lengowPathUri; ?>toolbox/log.php">
                            <i class="fa fa-file-text-o"></i> <?php echo $locale->t('toolbox.menu.log'); ?>
                        </a>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="<?php echo $lengowPathUri; ?>toolbox/logoff.php">
                            <i class="fa fa-sign-out"></i> <?php echo $locale->t('toolbox.menu.log_off'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php
}
