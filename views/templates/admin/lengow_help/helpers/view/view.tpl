{*
 * Copyright 2016 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 *  @author	   Team Connector <team-connector@lengow.com>
 *  @copyright 2016 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}

<div class="lgw-container">
    <div class="lgw-box lengow_help_wrapper text-center">
     <img src="/modules/lengow/views/img/cosmo-yoga.png" class="img-circle" alt="lengow">

      <h2>Get a little help from your support team !</h2>
      <p>
        Ask us anything about Lengow or share your feedback at <a href="mailto:chose@bidule.fr?subject=Hey%20Dude. %20You're%20Cool." title="Need some help?">support@lengow.com</a>.
      <br>
        We’ll do our best to get back to you during regular business hours (Monday to Friday – 9 pm to 9 am / France Timezone).
      </p>
      <p>
        You can also find answers in our <a href="https://en.knowledgeowl.com/help/article/link/prestashopv2" class="sub-link" target="_blank" title="Help Center">PrestaShop dedicated guide</a>.
      </p>
    </div>



</div>


<input type="hidden" id="lengow_ajax_link" value="{$lengow_ajax_link|escape:'htmlall':'UTF-8'}">

<script type="text/javascript" src="/modules/lengow/views/js/lengow/help.js"></script>



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
                "account_id": $i == 1 ? "155" : "557",
                "access_token": "09da83db3f332320858e7dff7514f947f3b4860417714c44a1e7c55db336a22d",
                "secret_token": "8eac31d7ee9a4acea0a16df12c004bc6b821c4bd2eafbc8281c31796fd88723d"
            }
            $i++;
        });
        parent.postMessage(return_data, "*");
    });

</script>
