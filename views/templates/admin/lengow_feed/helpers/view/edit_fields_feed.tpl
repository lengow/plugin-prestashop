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
    <div id="open-modal" class="config_field_button">Configuration des champs</div>
</div>

<img src="{$lengowPathUri|escape:'htmlall':'UTF-8'}views/img/settings-product.svg"
     class="lgw-module-illu-module"
     alt="prestashop">

<div id="modal-edit-fields" class="modal fade-scale" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="lengow-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier les Champs du catalogue exporté</h5>
                <button type="button" class="close-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="lengow-modal-body">
                <form id="edit-fields-form" method="post">
                    <ul class="fields-container">
                        {foreach from=$fields item=field key=key}
                            <li>
                                <div class="lgw-row">
                                    <div class="lgw-col-5">
                                        <input type="text"
                                               name="fields[{$key}][lengow_field]"
                                               class="feed-field lengow_input"
                                               placeholder="Nom du champ"
                                               value="{$field.lengow_field}"
                                               pattern="[A-Za-z0-9_-]+"
                                               title="Veuillez entrer un nom de champ valide (lettres, chiffres, _ et - uniquement)"
                                               data-action="update_field_name"
                                               data-default="{$key}">
                                    </div>
                                    <div class="lgw-col-2">
                                        <div class="lgw-arrow-right"></div>
                                    </div>
                                    <div class="lgw-col-5">
                                        <select name="fields[{$key}][prestashop_value]" class="filed lengow_select required"
                                                data-action="update_field_value">
                                            <option value="">Veuillez sélectionner une valeur pour le champ</option>
                                            <option value="{$field.prestashop_value}" selected>
                                                {$field.prestashop_value}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </li>
                        {/foreach}
                    </ul>
                    <input type="hidden" name="action" value="update_fields">
                    <div class="form__buttons uk-tile uk-tile-muted uk-margin-top">
                        <input type="submit" class="uk-button uk-button-secondary" value="Sauvegarder">
                        <button id="reset-all-fields" class="btn btn-warning">Réinitialiser Tous les Champs</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('#open-modal').addEventListener('click', function() {
            $('#modal-edit-fields').modal('show');
        });

        document.querySelector('#reset-all-fields').addEventListener('click', function(event) {
            event.preventDefault();

            document.querySelectorAll('input[data-default], select[data-default]').forEach(function(field) {
                var defaultValue = field.getAttribute('data-default');
                field.value = defaultValue;
            });
        });
    });
</script>
