/**
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
 */
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.sticky-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const shopId = this.dataset.shopId;
            const stickySwitches = document.querySelectorAll(`.sticky-switch-${shopId}`);

            stickySwitches.forEach(function(switchElem) {
                const isSwitchVisible = switchElem.classList.contains('show-switch');
                if (isSwitchVisible) {
                    switchElem.classList.remove('show-switch');
                } else {
                    switchElem.classList.add('show-switch');
                }
            });
        });
    });

    document.addEventListener('click', function(event) {
        const clickedElement = event.target;
        const isStickySwitch = clickedElement.closest('.sticky-switch');
        const isStickyIcon = clickedElement.closest('.sticky-icon');

        if (!isStickySwitch && !isStickyIcon) {
            document.querySelectorAll('.sticky-switch.show-switch').forEach(function(switchElem) {
                switchElem.classList.remove('show-switch');
            });
        }
    });
});

(function ($) {
    $(document).ready(function () {
        function reloadTotal(data, idShop) {
            lengow_jquery("#block_" + idShop + " .lengow_exported").html(data['total_export_product']);
            lengow_jquery("#block_" + idShop + " .lengow_total").html(data['total_product']);
        }

        $('.lgw-container').on('change', '.lengow_switch_option', function () {
            var href = $(this).attr('data-href');
            var action = $(this).attr('data-action');
            var idShop = $(this).attr('data-id_shop');

            var className = $(this).attr('class').replace('lengow_switch_option ', '');

            switch(className) {
                case 'option-selection':
                    lengow_jquery('.option-out-of-stock').prop('checked', true);
                    lengow_jquery('.option-variation').prop('checked', true);
                    lengow_jquery('.option-inactive').prop('checked', false);
                    break;
                default:
                    lengow_jquery('.option-selection').prop('checked', false);
                    break;
            }

            var state_selection = lengow_jquery(`.option-selection-${idShop}`).prop('checked');
            var state_out_of_stock = lengow_jquery(`.option-out-of-stock-${idShop}`).prop('checked');
            var state_variation = lengow_jquery(`.option-variation-${idShop}`).prop('checked');
            var state_inactive = lengow_jquery(`.option-inactive-${idShop}`).prop('checked');

            var data = {
                state_selection: state_selection ? 1 : 0,
                state_variation: state_variation ? 1 : 0,
                state_out_of_stock: state_out_of_stock ? 1 : 0,
                state_inactive: state_inactive ? 1 : 0,
                action: action,
                id_shop: idShop
            };

            $.getJSON(href, data, function(content) {
                var selector = lengow_jquery('#block_' + idShop + ' .lengow_feed_block_footer_content');

                reloadTotal(content, idShop);

                if (content['option'] !== 'selection') {
                    selector.slideUp(150);
                    lengow_jquery('.switch-selection').removeClass('checked');
                } else {
                    if (content['state'] === true) {
                        lengow_jquery('.switch-variation').addClass('checked');
                        lengow_jquery('.switch-out-of-stock').addClass('checked');
                        lengow_jquery('.switch-inactive').removeClass('checked');
                    }
                }

                if (content['state'] != null) {
                    if (content['state'] === true && content['option'] === 'selection') {
                        selector.slideDown(150);
                    } else {
                        selector.slideUp(150);
                    }
                }
            });
        });

        $('.lgw-container').on('change', '.lengow_switch_product', function () {
            var href = $(this).attr('data-href');
            var action = $(this).attr('data-action');
            var idShop = $(this).attr('data-id_shop');
            var idProduct = $(this).attr('data-id_product');
            var state = $(this).prop('checked');

            var data = {
                state: state ? 1 : 0,
                action: action,
                id_shop: idShop,
                id_product: idProduct
            };

            $.getJSON(href, data, function(content) {
                reloadTotal(content, idShop);
            });
        });

        $('.lgw-container').on('click', '.lgw-pagination a', function () {
            if ($(this).parent().hasClass('disabled')) {
                return false;
            }
            var href = $(this).attr('data-href');
            var idShop = $(this).parents('.lgw-pagination').attr('id').split('_')[2];

            $('#lengow_feed_wrapper #form_table_shop_' + idShop + ' input[name="p"]').val($(this).attr('data-page'));
            $('#lengow_feed_wrapper #form_table_shop_' + idShop).submit();
            return false;
        });

        $('#lengow_feed_wrapper').on('click', '.lengow_form_table .table_order', function () {
            var idShop = $(this).parents('table').attr('id').split('_')[2];
            $('#lengow_feed_wrapper #form_table_shop_' + idShop + ' input[name="order_value"]').val($(this).attr('data-order'));
            $('#lengow_feed_wrapper #form_table_shop_' + idShop + ' input[name="order_column"]').val($(this).attr('data-column'));
            $('#lengow_feed_wrapper #form_table_shop_' + idShop).submit();
        });

        pluginsRender();

    });
})(lengow_jquery);

function pluginsRender() {
    lengow_jquery('.lgw-pagination-select-item').select2({minimumResultsForSearch: Infinity});
}
