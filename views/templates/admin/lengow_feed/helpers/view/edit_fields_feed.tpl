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

<div class="config_field_button-container">
    <div data-original-title="Edit exported fields" id="open-modal" class="config_field_button hover-target lengow_link_tooltip ">
        <i style="font-size: 20px;color: #555; position: relative " class="fa fa-wrench feed-settong-icon"></i>
    </div>
</div>

<div id="modal-edit-fields" class="modal fade-scale" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="lengow-modal-content">
            <div class="modal-header-lengow">
                <div class="container-close-button">
                    <span type="button" class="close-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </span>
                </div>
                <h3 class="modal-title text-center">Modifier les noms Champs de base du catalogue exporté</h3>
                <div class="lgw-row header-row">
                    <div class="lgw-col-6 text-center field-name">
                        <strong>Nom du Champ</strong>
                    </div>
                    <div class="lgw-col-6 text-center presta-field">
                        <strong>Valeur PrestaShop</strong>
                    </div>
                </div>
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
                                        <p class="field-value">
                                            {$field.prestashop_value}
                                        </p>
                                        <input type="hidden" name="fields[{$key}][prestashop_value]" value="{$field.prestashop_value}">
                                    </div>
                                </div>
                            </li>
                        {/foreach}
                    </ul>
                    <input type="hidden" name="action" value="update_fields">
                    <div class="form__buttons uk-tile uk-tile-muted uk-margin-top">
                        <button type="submit" class="lgw-btn" >Sauvegarder</button>
                        <button id="reset-all-fields" class="lgw-btn">Réinitialiser Tous les Champs</button>
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
