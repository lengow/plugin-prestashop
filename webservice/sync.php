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

@set_time_limit(0);
@ini_set('memory_limit', '512M');

$currentDirectory = str_replace('modules/lengow/webservice/', '', dirname($_SERVER['SCRIPT_FILENAME']) . "/");

$sep = DIRECTORY_SEPARATOR;
require_once $currentDirectory . 'config' . $sep . 'config.inc.php';
require_once $currentDirectory . 'init.php';
require_once $currentDirectory . 'modules/lengow/lengow.php';


?>
<html>
<head>
    <script type="text/javascript" src="/modules/lengow/views/js/jquery.1.12.0.min.js"></script>
</head>
<body>
<h1>Lengow Page</h1>
<div id="call">
    <a id="link_call" href="#">Send Information To Prestashop</a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <a id="link_cancel" href="#">Cancel Link</a>
</div>
<div id="parameters">

</div>
</body>

<script type="text/javascript">
    window.addEventListener("message", receiveMessage, false);

    function receiveMessage(event) {
        //if (event.origin !== "http://solution.lengow.com")
        //    return;
        switch (event.data.function) {
            case 'sync':
                global_parameters = event.data.parameters;
                document.getElementById("parameters").innerHTML = 'Parameters : <br/><br/>' +
                    JSON.stringify(event.data.parameters);
                break;
        }
    }

    $('#link_cancel').click(function () {
        parent.postMessage({"function": "back"}, "*");
    });

    $('#link_call').click(function () {

        var return_data = {
            "function": "sync",
            "parameters": {}
        };
        jQuery.each(global_parameters.shops, function (i, shop) {
            return_data.parameters[shop.token] = {
                "account_id": "155",
                "access_token": "09da83db3f332320858e7dff7514f947f3b4860417714c44a1e7c55db336a22d",
                "secret_token": "8eac31d7ee9a4acea0a16df12c004bc6b821c4bd2eafbc8281c31796fd88723d"
            }
        });
        parent.postMessage(return_data, "*");
    });

</script>
</html>