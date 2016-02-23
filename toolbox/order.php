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

require 'conf.inc.php';
$context = Context::getContext();
$action = isset($_REQUEST['action']) ?  $_REQUEST['action'] : null;
$accountId = isset($_REQUEST['account_id']) ?  $_REQUEST['account_id'] : null;
$secretToken = isset($_REQUEST['secret_token']) ?  $_REQUEST['secret_token'] : null;
$blockedIP = isset($_REQUEST['blockedIP']) ?  $_REQUEST['blockedIP'] : false;
$lengowTool = new LengowTool();

$controller = new LengowOrderController();
$controller->postProcess();
$controller->display();

$shops = LengowShop::findAll();
foreach ($shops as $s) {
    $shop[$s['id_shop']] = new LengowShop($s['id_shop']);
}
$marketplaces = array();
$days = LengowConfiguration::get('LENGOW_IMPORT_DAYS');
$context->smarty->assign('shop', $shop);
$context->smarty->assign('marketplaces', $marketplaces);
$context->smarty->assign('days', $days);


require 'views/header.php';
echo '<div class="full-container">';
echo $controller->forceDisplay();
echo '</div><!-- /.container -->';
require 'views/footer.php';