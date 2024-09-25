{*
 * Copyright 2017 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 *}

<div class="config_field_button-container">
    <div data-original-title="{$locale->t('product.screen.button_fields_setting')|escape:'htmlall':'UTF-8'}" id="open-modal" class="config_field_button hover-target lengow_link_tooltip">
        <i style="font-size: 20px; color: #555; position: relative" class="fa fa-wrench feed-settong-icon"></i>
    </div>
</div>

<div id="modal-edit-fields" class="modal fade-scale" tabindex="-1" role="dialog">
    <div class="lengow-modal-dialog" role="document">
        <div class="lengow-modal-content">
            <div class="modal-header-lengow">
                <div class="container-close-button">
                    <span type="button" class="close-button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </span>
                </div>
                <h3 class="modal-title text-center">{$locale->t('product.screen.title_fields_settings')|escape:'htmlall':'UTF-8'}</h3>
                <hr>
                <div class="product-setting-header-row">
                    <div class="select-product-container">
                        <p>Select product</p>
                        <select id="product-select">
                            {foreach from=$productsData item=product}
                                <option value="{$product.id}">{$product.id}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="export-feature">
                        <p>{html_entity_decode($export_params|escape:'htmlall':'UTF-8')}</p>
                    </div>
                </div>

                <div class="grid-header">
                    <div class="grid-item header-item new-column">
                        <strong>Exporter</strong>
                    </div>
                    <div class="grid-item header-item field-name">
                        <strong>{$locale->t('product.screen.title_column_prestashop_value')|escape:'htmlall':'UTF-8'}</strong>
                    </div>
                    <div class="grid-item header-item presta-field">
                        <strong>Exemples de valeurs exportées</strong>
                    </div>
                    <div class="grid-item header-item presta-field">
                        <strong>{$locale->t('product.screen.title_column_name_fields')|escape:'htmlall':'UTF-8'}</strong>
                    </div>
                </div>
            </div>
            <div class="lengow-modal-body">
                <form id="edit-fields-form" method="post">
                    <div class="fields-container">
                        {foreach from=$fields item=field key=key}
                            <div class="grid-row">
                                <div class="grid-item new-column">
                                    <input type="hidden" name="fields[{$key}][exported]" value="0">
                                    <input type="checkbox"
                                           name="fields[{$key}][exported]"
                                           class="exported-checkbox"
                                           {if $field.exported == '1'}checked{/if}
                                            {if in_array($field.prestashop_value, ['id', 'category', 'name', 'price_incl_tax', 'language'])}disabled{/if}
                                           value="1">
                                </div>
                                <div class="grid-item">
                                    <p class="field-value" data-prestashop-value="{$field.prestashop_value}">{$field.prestashop_value}</p>
                                    <input type="hidden" name="fields[{$key}][prestashop_value]" value="{$field.prestashop_value}">
                                </div>
                                <div class="grid-item line-name-field">
                                    {if strpos($field.prestashop_value, 'image') !== false}
                                        <span style="width: 100%" id="field-value-{$field.prestashop_value}" class="field-value image-container">
                                        </span>
                                    {elseif strpos($field.prestashop_value, 'description') !== false
                                    || strpos($field.prestashop_value, 'url') !== false
                                    || strpos($field.prestashop_value, 'url_rewrite') !== false
                                    || strpos($field.prestashop_value, 'short_description') !== false
                                    || strpos($field.prestashop_value, 'short_description_html') !== false
                                    || strpos($field.prestashop_value, 'description_html') !== false}
                                        <div style="display: flex; justify-content: center; width: 100%">
                                            <p id="field-value-{$field.prestashop_value}" class="field-value"></p>
                                        </div>
                                    {else}
                                        <p style="width: 100%" id="field-value-{$field.prestashop_value}" class="field-value"></p>
                                    {/if}
                                    <div style="width: 20%" class="lgw-arrow-left"></div>
                                </div>
                                <div class="grid-item">
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
                            </div>
                        {/foreach}
                    </div>
                    <input type="hidden" name="action" value="update_fields">
                    <div class="form__buttons">
                        <button type="submit" class="lgw-btn">{$locale->t('global_setting.screen.button_save')|escape:'htmlall':'UTF-8'}</button>
                        <button id="reset-all-fields" class="lgw-btn">{$locale->t('product.screen.button_fields_reset')|escape:'htmlall':'UTF-8'}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var products = {$json_products};

        // Fonction pour mettre à jour les informations de produit
        function updateProductInfo(productId) {
            var product = products.find(function(p) { return p.id == productId; });
            if (product) {
                document.querySelectorAll(".field-value").forEach(function(fieldElement) {
                    var prestashopValue = fieldElement.getAttribute("data-prestashop-value");
                    var value = product[prestashopValue] || "";
                    var fieldValueElement = document.querySelector("#field-value-" + prestashopValue);
                    if (fieldValueElement) {
                        if (fieldValueElement.tagName === "SPAN") {
                            fieldValueElement.innerHTML = value
                                ? "<img src=\"" + value + "\" alt=\"Product Image\" style=\"width: 100px; height: 60px;\">"
                                : "<p>Pas d'image disponible</p>";
                        } else {
                            fieldValueElement.textContent = value || "Aucune donnée";
                        }
                    }
                });
            } else {
                console.warn("Produit non trouvé:", productId);
            }
        }

        // Sélection de l'élément select
        var productSelect = document.querySelector("#product-select");

        // Fonction pour ajouter dynamiquement des options au select
        function populateProductSelect() {
            products.forEach(function(product) {
                var option = document.createElement("option");
                option.value = product.id;
                option.textContent = product.id;
                productSelect.appendChild(option);
            });
        }

        // Appel de la fonction pour ajouter les options dynamiquement
        populateProductSelect();

        // Initialisation de Select2 après avoir ajouté les options
        lengow_jquery("#product-select").select2({
            placeholder: "Sélectionnez un produit",
            minimumResultsForSearch: -1,
            dropdownAutoWidth: true
        });

        // Définir la valeur par défaut si des produits sont présents
        if (products.length > 0) {
            var defaultProductId = products[0].id;
            lengow_jquery("#product-select").val(defaultProductId).trigger("change"); // Utilisation de jQuery pour Select2
            updateProductInfo(defaultProductId);
        }

        // Gérer les changements de sélection via Select2
        lengow_jquery("#product-select").on("change", function() {
            var selectedProductId = lengow_jquery(this).val(); // Utilisation de jQuery pour récupérer la valeur
            updateProductInfo(selectedProductId);
        });

        // Ouverture de la modal
        document.querySelector("#open-modal").addEventListener("click", function() {
            lengow_jquery("#modal-edit-fields").modal("show");
        });

        // Gestion du bouton de réinitialisation
        document.querySelector("#reset-all-fields").addEventListener("click", function(event) {
            event.preventDefault();
            document.querySelectorAll("input[data-default], select[data-default]").forEach(function(field) {
                var defaultValue = field.getAttribute("data-default");
                field.value = defaultValue;
            });
        });
    });

</script>
