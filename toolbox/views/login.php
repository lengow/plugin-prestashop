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

if ($blockedIP) {
    echo '<div class="alert alert-danger" role="alert">Your IP Address has ben blocked</div>';
}
?>
<form action="/modules/lengow/toolbox/login.php" method="POST">
    <input type="hidden" name="action" value="login" />
    <div class="form-group">
        <label for="exampleInputEmail1">Access Token</label>
        <input type="text" class="form-control" name="access_token" id="access_token" placeholder="AccessToken">
    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">Secret Token</label>
        <input type="password" class="form-control" name="secret_token"  id="secret_token" placeholder="SecretToken">
    </div>
    <button type="submit" class="btn btn-default">Se Connecter</button>
</form>