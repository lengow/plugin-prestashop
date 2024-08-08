{*
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
 *}

<div class="btn-container text-center">
    <button id="open-modal" class="btn btn-primary">Configuration des champs</button>
</div>

<div id="modal-edit-fields" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier les Champs du catalogue exporté</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-fields-form" method="post">
                    <ul>
                        {foreach from=$fields item=field key=key}
                            <li>
                                <div class="lgw-row">
                                    <!-- Nom du champ Lengow -->
                                    <div class="lgw-col-6">
                                        <input type="text"
                                               name="fields[{$key}][name]"
                                               class="feed-field lengow_input"
                                               placeholder="Nom du champ"
                                               value="{$key}"
                                               pattern="[A-Za-z0-9_-]+"
                                               title="Veuillez entrer un nom de champ valide (lettres, chiffres, _ et - uniquement)"
                                               data-action="update_field_name">
                                    </div>
                                    <!-- Flèche entre le texte et le champ de texte -->
                                    <div class="lgw-col-1">
                                        <div class="lgw-arrow-right"></div>
                                    </div>
                                    <!-- Valeur du champ -->
                                    <div class="lgw-col-5">
                                        <select name="fields[{$key}][value]" class="filed lengow_select required"
                                                data-action="update_field_value">
                                            <option value="">Veuillez sélectionner une valeur pour le champ</option>
                                            <option value="test">test</option>
                                            {foreach from=$availableValues item=value}
                                                <option value="{$value}" selected="selected">{$value}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </li>
                        {/foreach}
                    </ul>
                    <input type="hidden" name="action" value="update_fields">
                    <input type="submit" class="btn btn-primary" value="Sauvegarder">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('#open-modal').addEventListener('click', function() {
            $('#modal-edit-fields').modal('show');
        });

        // Gestion des actions des champs
        document.querySelectorAll('[data-action]').forEach(function(element) {
            element.addEventListener('change', function() {
                const action = this.getAttribute('data-action');
                // Traiter les actions selon la valeur de l'attribut data-action
                console.log('Action:', action);
                console.log('Value:', this.value);
                // Vous pouvez envoyer une requête AJAX ici pour traiter les actions si nécessaire
            });
        });
    });
</script>
