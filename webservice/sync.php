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

    <link href="//fonts.googleapis.com/css?family=Lato:300,400,700,900,300italic,400italic,700italic,900italic|Open+Sans:700,600,800,400,300" type="text/css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/lengow-layout.css">
    <link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/lengow-components.css">
    <link rel="stylesheet" type="text/css" href="/modules/lengow/views/css/lengow-pages.css">
</head>
<body class="lgw-home-iframe">
<div id="call">
        <a id="link_call" href="#">Send Information To Prestashop</a>
        &nbsp;&nbsp;&nbsp;&nbsp;
    </div>
    <!-- <pre><code id="parameters" style="display: block;overflow: hidden;"></code></pre> -->
<div class="lgw-container">
    <div class="lgw-row">
        <div class="lgw-col-7 lgw-connect">
            <div class="lgw-box">
                <div class="lgw-content-section">
                    <img src="/modules/lengow/views/img/logo-blue.png" alt="lengow" class="center-block img-responsive"/>
                    <div class="marginTB">
                        <h3>{$locale->t('connect.lengow')|escape:'htmlall':'UTF-8'}</h3>
                        <p>
                            {$locale->t('connect.lengow_presentation')|escape:'htmlall':'UTF-8'}
                        </p>
                        <p>
                            {$locale->t('connect.lengow_platform')|escape:'htmlall':'UTF-8'}
                        </p>
                    </div>
                    <div class="lgw-container">
                        <div class="lgw-row">
                            <button href="#" class="lgw-btn lgw-btn-green">
                                {$locale->t('connect.create_account')|escape:'htmlall':'UTF-8'}
                            </button>
                        </div>
                        <div class="lgw-row">
                            <button href="#" class="lgw-btn lgw-btn-white">
                                {$locale->t('connect.connect_account')|escape:'htmlall':'UTF-8'}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="lgw-container">
                    <div class="lgw-row">
                        <div class="lgw-col-4">
                            <img src="https://www.placehold.it/55x55" class="img-circle">
                            <h4>{$locale->t('connect.free_days_title')|escape:'htmlall':'UTF-8'}</h4>
                            <p>{$locale->t('connect.free_days_text')|escape:'htmlall':'UTF-8'}</p>
                        </div>
                        <div class="lgw-col-4">
                            <img src="https://www.placehold.it/55x55" class="img-circle">
                            <h4>{$locale->t('connect.marketing_channels_title')|escape:'htmlall':'UTF-8'}</h4>
                            <p>{$locale->t('connect.marketing_channels_text')|escape:'htmlall':'UTF-8'}</p>
                        </div>
                        <div class="lgw-col-4">
                            <img src="https://www.placehold.it/55x55" class="img-circle">
                            <h4>{$locale->t('connect.retailers_title')|escape:'htmlall':'UTF-8'}</h4>
                            <p>{$locale->t('connect.retailers_text')|escape:'htmlall':'UTF-8'}</p>
                        </div>
            </div>
        </div>
    </div>
</div>

    <!-- <h1>Lengow Page</h1>
    <div id="call">
        <a id="link_call" href="#">Send Information To Prestashop</a>
        &nbsp;&nbsp;&nbsp;&nbsp;
    </div>
    <pre><code id="parameters" style="display: block;overflow: hidden;"></code></pre> -->
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
