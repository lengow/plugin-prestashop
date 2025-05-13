{*
 * Copyright 2017 Lengow SAS.
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
 *  @copyright 2017 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
<div class="card">
    <div class="card-header"><h2>Lengow Shipping</h2></div>
    <div class="card-body">
        <div class="form-group">
            <label for="lengow_shipping_select">Méthode de livraison envoyée par Lengow :</label>
            <select id="lengow_shipping_select" class="form-control">
                {foreach from=$shipping_methods item=method}
                    {if $method.method_lengow_code}
                        <option value="{$method.method_lengow_code|escape:'html':'UTF-8'}"
                                {if isset($lengowOrder.method) && $lengowOrder.method == $method.method_lengow_code}selected{/if}>
                            {$method.method_lengow_code|escape:'html':'UTF-8'}
                        </option>
                    {/if}
                {/foreach}
            </select>
        </div>
        <button id="save_lengow_method" class="btn btn-primary mt-3">Enregistrer la méthode de livraison</button>
        <div id="lengow_save_result" class="mt-2"></div>
    </div>
</div>

<script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('save_lengow_method');
            btn.addEventListener('click', function() {
                var method = document.getElementById('lengow_shipping_select').value;
                var idOrder = {$id_order|intval};
                // URL générée par getAdminLink (inclut token et controller)
                var url = "{$ajax_url|escape:'javascript':'UTF-8'}";
                url += (url.indexOf('?') === -1 ? '?' : '&') +
                    'action=save_shipping_method&id_order=' + idOrder +
                    '&method=' + encodeURIComponent(method);

                fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function(response) { return response.json(); })
                    .then(function(json) {
                        var res = document.getElementById('lengow_save_result');
                        if (json.success) {
                            res.innerHTML = '<div class="alert alert-success">Méthode enregistrée avec succès.</div>';
                            setTimeout(function() { res.innerHTML = ''; }, 5000);
                        } else {
                            res.innerHTML = '<div class="alert alert-danger">Erreur : ' + json.message + '</div>';
                        }
                    })
                    .catch(function(error) {
                        console.error(error);
                        document.getElementById('lengow_save_result').innerHTML = '<div class="alert alert-danger">Erreur de communication avec le serveur</div>';
                    });
            });
        });
    })();
</script>
