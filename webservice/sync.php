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
<body style="background: #CDCDCD;font-family: Open Sans;margin: 0;">
<div style="background: #f7f7f7;float:right;width: 400px;padding:20px;">
    <h1 style="font-size: 4.9em;text-align: center;color: #31353d;margin-bottom: 5px;">Lengow</h1>
    <p style="width: 13%;padding: 5px;padding-bottom: 5px;padding-right: 20px;padding-left: 20px;
        margin-top: -20px;background-color: #45bf7b;color: white;
        font-size: 0.6em;font-weight: bold;border-radius: 2px;margin-left: 250px;">
        OFFICIAL
    </p>
    <p style="font-size: 0.9em;">
        Lorem ipsum dolor sit amet, consectetur adipisicing elit.
        Aliquam excepturi facilis, illo inventore molestias nam
        nesciunt non nulla porro quo,
        vero?
    </p>
    <p style="font-size: 0.9em;">
        Lorem ipsum dolor sit amet, consectetur adipisicing elit.
        Aliquam excepturi facilis, illo inventore molestias nam
        nesciunt non nulla porro
    </p>
    <button href="#" style="width: 100%;padding: 15px;margin-top: 50px;border: none;
        background-color: #45bf7b;color: white;font-size: 0.9em;font-weight: bold;border-radius: 2px;"
        onmouseover="this.style.background='#47ce83';"
        onmouseout="this.style.background='#45bf7b';">
        Create my 15 days-free account
    </button>
    <button href="#" style="width: 100%;border-radius: 2px;padding: 10px;border: none;
        background: none;font-size: 0.9em;color: #6c6c6c;"
        onmouseover="this.style.background='#CDCDCD';"
        onmouseout="this.style.background='none';">
        or Connect to your account
    </button>
    <div style="width: 50%;display: inline-block;vertical-align: top;margin-left: 25px;">
        <div style="height: 55px;width: 55px;background-color: #CDCDCD;border-radius: 35px;
        margin-top: 50px;margin-left: 25px;margin-right: 25px;">
        </div>
        <p style="font-size: 0.8em;margin-bottom:0;">
            Lorem ipsum dolor.
        </p>
        <p style="font-size: 0.7em;margin-top:0;">
            Aliquam excepturi
        </p>
    </div>
    <div style="width: 40%;display: inline-block;">
        <div style="height: 55px;width: 55px;background-color: #CDCDCD;
        border-radius: 35px;margin-top: 50px;margin-left: 25px;margin-right: 25px;">
        </div>
        <p style="font-size: 0.8em;">
            Lorem ipsum dolor.
            <span style="display: block;font-size: 0.7em;">
                Aliquam excepturi facilis, illo inventore molestias
            </span>
        </p>
    </div>
</div>

    <h1>Lengow Page</h1>
    <div id="call">
        <a id="link_call" href="#">Send Information To Prestashop</a>
        &nbsp;&nbsp;&nbsp;&nbsp;
    </div>
    <pre><code id="parameters" style="display: block;overflow: hidden;"></code></pre>
</body>

<script type="text/javascript">
    window.addEventListener("message", receiveMessage, false);

    function receiveMessage(event) {
        //if (event.origin !== "http://solution.lengow.com")
        //    return;
        switch (event.data.function) {
            case 'sync':
                global_parameters = event.data.parameters;
                document.getElementById("parameters").innerHTML = 'Parameters : <br/><br/>';
                document.getElementById("parameters").appendChild(
                    document.createTextNode(JSON.stringify(event.data.parameters, null, 4))
                );
                break;
        }
    }


    $('#link_call').click(function () {

        var return_data = {
            "function": "sync",
            "parameters": {}
        };
        $i = 0;
        jQuery.each(global_parameters.shops, function (i, shop) {
            return_data.parameters[shop.token] = {
                "account_id": $i == 1 ? "952" : "953",
                "access_token": "6a9dafde15618125e7797922cab5678fc76621e50634466e2da921e9a521c1e6",
                "secret_token": "de2f0a8fcbeae67fa57ee787f9b856ac1f8d78be69a707129dbafc1b1e0f9f9a"
            }
            $i++;
        });
        parent.postMessage(return_data, "*");
    });

</script>
</html>