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

require 'conf.inc.php';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
$accountId = isset($_REQUEST['account_id']) ? $_REQUEST['account_id'] : null;
$secretToken = isset($_REQUEST['secret_token']) ? $_REQUEST['secret_token'] : null;
$blockedIp = isset($_REQUEST['blockedIP']) ? $_REQUEST['blockedIP'] : false;
$lengowTool = new LengowTool();

switch ($action) {
    case 'login':
        $lengowTool->processLogin($accountId, $secretToken);
        break;
}

require 'views/header.php';
if ($blockedIp) {
    echo '<div class="alert alert-danger" role="alert">Your IP Address has ben blocked</div>';
}
?>
    <div class="container">
        <form action="<?php echo __PS_BASE_URI__; ?>modules/lengow/toolbox/login.php" method="POST">
            <input type="hidden" name="action" value="login"/>
            <div class="form-group">
                <label for="exampleInputEmail1">Account Id</label>
                <input type="text" class="form-control" name="account_id" id="account_id" placeholder="AccountId">
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Secret Token</label>
                <input type="password" class="form-control" name="secret_token" id="secret_token"
                       placeholder="SecretToken">
            </div>
            <button type="submit" class="btn btn-default">Log In</button>
        </form>
    </div><!-- /.container -->
<?php
require 'views/footer.php';
