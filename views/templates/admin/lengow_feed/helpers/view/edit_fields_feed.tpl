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
                <div class="search-container">
                    <input type="text" id="product-search" placeholder="Rechercher un produit" />
                    <ul id="product-results" class="search-results"></ul>
                </div>
                <div class="grid-header">
                    <div class="grid-item header-item field-name">
                        <strong>{$locale->t('product.screen.title_column_prestashop_value')|escape:'htmlall':'UTF-8'}</strong>
                    </div>
                    <div class="grid-item header-item presta-field">
                        <strong>Exemple de valeur pour le produit ()</strong>
                    </div>
                    <div class="grid-item header-item presta-field">
                        <strong>{$locale->t('product.screen.title_column_name_fields')|escape:'htmlall':'UTF-8'}</strong>
                    </div>
                </div>
            </div>
            <div class="lengow-modal-body">
                <form id="edit-fields-form" method="post">
                    <div class="fields-container">
                        <div class="grid-row">
                            <div class="grid-item">
                                <select id="product-select" class="form-control">
                                    {foreach from=$productsData item=product}
                                        <option value="{$product.id}">{$product.id}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        {foreach from=$fields item=field key=key}
                            <div class="grid-row">
                                <div class="grid-item">
                                    <p class="field-value" data-prestashop-value="{$field.prestashop_value}">{$field.prestashop_value}</p>
                                    <input type="hidden" name="fields[{$key}][prestashop_value]" value="{$field.prestashop_value}">
                                </div>
                                <div class="grid-item line-name-field">
                                    {if strpos($field.prestashop_value, 'image') !== false}
                                        <img id="field-value-{$field.prestashop_value}" src="{$product}" alt="Image" style="width: 100px; height: 60px;" class="field-value"/>
                                    {elseif strpos($field.prestashop_value, 'description') !== false
                                    || strpos($field.prestashop_value, 'url') !== false
                                    || strpos($field.prestashop_value, 'url_rewrite') !== false
                                    || strpos($field.prestashop_value, 'short_description') !== false
                                    || strpos($field.prestashop_value, 'short_description_html') !== false
                                    || strpos($field.prestashop_value, 'description_html') !== false}
                                        <div style="display: flex; justify-content: space-between; width: 100%">
                                            <label for="field-value-{$field.prestashop_value}"></label>
                                            <textarea id="field-value-{$field.prestashop_value}" class="field-value" rows="4" cols="20" readonly>
                                        </textarea>
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

        function updateProductInfo(productId) {
            var product = products.find(p => p.id == productId);
            console.log(product);

            if (product) {
                document.querySelectorAll(".field-value").forEach(function(fieldElement) {
                    var prestashopValue = fieldElement.getAttribute("data-prestashop-value");

                    var value = product[prestashopValue] || "Aucune donnée";

                    var fieldValueElement = document.querySelector("#field-value-" + prestashopValue);
                    if (fieldValueElement) {
                        if (fieldValueElement.tagName === "IMG") {
                            fieldValueElement.src = value;
                        }
                        fieldValueElement.textContent = value;
                    }
                });
            } else {
                console.warn("Produit non trouvé:", productId);
            }
        }


        var productSelect = document.querySelector("#product-select");
        products.forEach(function(product) {
            var option = document.createElement("option");
            option.value = product.id;
            option.textContent = product.id;
            productSelect.appendChild(option);
        });

        if (products.length > 0) {
            var defaultProductId = products[0].id;
            productSelect.value = defaultProductId;
            updateProductInfo(defaultProductId);
        }

        productSelect.addEventListener("change", function() {
            var selectedProductId = this.value;
            updateProductInfo(selectedProductId);
        });

        document.querySelector("#open-modal").addEventListener("click", function() {
            $("#modal-edit-fields").modal("show");
        });

        document.querySelector("#reset-all-fields").addEventListener("click", function(event) {
            event.preventDefault();
            document.querySelectorAll("input[data-default], select[data-default]").forEach(function(field) {
                var defaultValue = field.getAttribute("data-default");
                field.value = defaultValue;
            });
        });
    });
</script>
