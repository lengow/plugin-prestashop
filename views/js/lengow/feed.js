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
    const stickyIcon = document.getElementById('sticky-icon');
    const stickySwitches = document.querySelectorAll('.sticky-switch');

    stickyIcon.addEventListener('click', function() {
        stickySwitches.forEach(function(switchElem) {
            const isSwitchVisible = switchElem.classList.contains('show-switch');
            if (isSwitchVisible) {
                switchElem.classList.remove('show-switch');
            } else {
                switchElem.classList.add('show-switch');
            }
        });
    });

    document.addEventListener('click', function(event) {
        const clickedElement = event.target;
        const isStickySwitch = clickedElement.closest('.sticky-switch');
        const isStickyIcon = clickedElement.closest('#sticky-icon');

        if (!isStickySwitch && !isStickyIcon) {
            stickySwitches.forEach(function(switchElem) {
                switchElem.classList.remove('show-switch');
            });
        }
    });
});



(function ($) {
    $(document).ready(function () {

        /**
         * Refresh total product/product exported
         * @param data Number of products exported and total products
         * @param idShop Shop id
         */
        function reloadTotal(data, idShop) {
            lengow_jquery("#block_" + idShop + " .lengow_exported").html(data['total_export_product']);
            lengow_jquery("#block_" + idShop + " .lengow_total").html(data['total_product']);
        }

        $('.lgw-container').on('change', '.lengow_switch_option', function () {
            var href = $(this).attr('data-href');
            var action = $(this).attr('data-action');
            var idShop = $(this).attr('data-id_shop');


            var className = $(this).attr('class').replace('lengow_switch_option ','');
            switch(className)
            {
                case 'option-selection':
                    lengow_jquery('.option-out-of-stock').prop('checked', true);
                    lengow_jquery('.option-variation').prop('checked', true);
                    lengow_jquery('.option-inactive').prop('checked', false);
                    break;
                default:
                    lengow_jquery('.option-selection').prop('checked', false);
                    break;
            }

            var state_selection =  lengow_jquery('.option-selection').prop('checked');
            var state_out_of_stock =  lengow_jquery('.option-out-of-stock').prop('checked');
            var state_variation =  lengow_jquery('.option-variation').prop('checked');
            var state_inactive =  lengow_jquery('.option-inactive').prop('checked');


            var data = {
                state_selection: state_selection ? 1 : 0,
                state_variation: state_variation ? 1 : 0,
                state_out_of_stock : state_out_of_stock ? 1 :0,
                state_inactive : state_inactive ? 1 : 0,
                action: action,
                id_shop: idShop
            };


            $.getJSON(href, data, function(content) {
                var selector = lengow_jquery('#block_' + idShop + ' .lengow_feed_block_footer_content');

                reloadTotal(content, idShop);

                if (content['option'] !== 'selection'){
                    selector.slideUp(150);
                    lengow_jquery('.switch-selection').removeClass('checked');
                } else {
                    //window.location.reload();
                    if (content['state'] === true) {
                        console.log('add class checked');
                        lengow_jquery('.switch-variation').addClass('checked');
                        lengow_jquery('.switch-out-of-stock').addClass('checked');
                        lengow_jquery('.switch-inactive').removeClass('checked');
                    }
                }


                if (content['state'] != null) {
                    if (content['state'] === true
                        && content['option'] === 'selection') {
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
            return false;
        });

        $('#lengow_feed_wrapper').on('change', '.lgw-pagination-select-item', function () {
            $('#lengow_feed_wrapper .lengow_form_table input[name="nb_per_page"]').val($(this).val());
            $('#lengow_feed_wrapper .lengow_form_table').submit();
            return false;
        });

        // update by input

        var typingTimer;
        var idShop;
        $('#lengow_feed_wrapper').on('keyup', 'thead input[type="text"]', function () {
            idShop = $(this).closest('table').attr('id').split('_')[2];
            clearTimeout(typingTimer);
            typingTimer = setTimeout(doneTyping, 750);
        });
        $('#lengow_feed_wrapper').on('keydown', 'thead input[type="text"]', function () {
            clearTimeout(typingTimer);
        });
        function doneTyping (){
            $('#lengow_feed_wrapper #form_table_shop_' + idShop).submit();
        }

        $('#lengow_feed_wrapper').on('submit', '.lengow_form_table', function () {
            var href = $(this).attr('data-href');
            var idShop = $(this).attr('id').split('_')[3];
            var form = $(this).serialize();
            var url = href + "&" + form;
            var data = {
                action: 'load_table',
                id_shop: idShop
            };

            $.getJSON(url, data, function(content) {
                lengow_jquery("#block_" + content['shop_id']
                    + " .lengow_feed_block_footer_content").html(content['footer_content']);
                pluginsRender();
            });

            return false;
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_select_all', function () {
            var idShop = $(this).attr('id').split('_')[2];
            if ($(this).prop('checked')) {
                $('#table_shop_' + idShop + ' tbody .lengow_selection').prop('checked', true);
                $('#table_shop_' + idShop + ' tbody tr').addClass('select');
                $('#block_' + idShop + ' .lengow_toolbar a').show();
                $('#block_' + idShop + ' .lengow_toolbar .lengow_select_all_shop').show();
            } else {
                $('#table_shop_' + idShop + ' tbody .lengow_selection').prop('checked', false);
                $('#table_shop_' + idShop + ' tbody tr').removeClass('select');
                $('#block_' + idShop + ' .lengow_toolbar a').hide();
                $('#block_' + idShop + ' .lengow_toolbar .lengow_select_all_shop').hide();
            }
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_selection', function () {
            var idShop = $(this).parents('table').attr('id').split('_')[2];
            $('#block_' + idShop + ' .lengow_toolbar a').show();

            if ($(this).prop('checked')) {
                $(this).parents('tr').addClass('select');
            } else {
                $('#block_' + idShop + ' .lengow_toolbar .lengow_select_all_shop input').prop('checked', false);
                $(this).parents('tr').removeClass('select');

            }
            var findProductSelected = false;
            $(this).parents('table').find('.lengow_selection').each(function (index) {
                if ($(this).prop('checked')) {
                    findProductSelected = true;
                }
            });
            if (!findProductSelected) {
                $('#block_' + idShop + ' .lengow_toolbar a').hide();
            }
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_add_to_export, .lengow_remove_from_export', function () {
            var href = $(this).attr('data-href');
            var idShop = $(this).attr('data-id_shop');
            var message = $(this).attr('data-message');
            var action = $(this).attr('data-action');
            var exportAction = $(this).attr('data-export-action');
            var form = $('#form_table_shop_' + idShop).serialize();
            var url = href + "&" + form;
            var check = $('#select_all_shop_' + idShop).prop('checked');
            var data = {
                action: action,
                id_shop: idShop,
                select_all: check,
                export_action: exportAction
            };
            if (!check || (check && confirm(message))) {
                $.getJSON(url, data, function(content) {
                    if (content['message']) {
                        alert(content['message']);
                    } else {
                        $.each(content['product_id'], function(idx, productId) {
                            if (exportAction == 'lengow_add_to_export') {
                                lengow_jquery("#shop_" + idShop + "_" + productId + " .lgw-switch").addClass("checked");
                                lengow_jquery(".lengow_switch_product").prop("checked", true);
                            } else {
                                lengow_jquery("#shop_" + idShop + "_" + productId + " .lgw-switch").removeClass("checked");
                                lengow_jquery(".lengow_switch_product").prop("checked", false);
                            }
                        });
                        reloadTotal(content, idShop);
                    }
                });
            }
            return false;
        });

        $('#lengow_feed_wrapper').on('click', '.lengow_select_all_shop input', function () {
            var idShop = $('.lengow_select_all').attr('id').split('_')[2];
            if ($(this).prop('checked')) {
                $('#table_shop_' + idShop + ' tbody .lengow_selection').prop('checked', true);
                $('.lengow_selection').parents('tr').addClass('select');
            }
        });

        $('.lengow_table').on('click', '.table_row td:not(.no-link)', function(){
            var url = $(this).closest('.table_row').find('.feed_name a').attr('href');
            if (url) {
                window.open(url, '_blank');
            };
            return false;
        });

        pluginsRender();

    });
})(lengow_jquery);

function pluginsRender(){
    // Selects
    lengow_jquery('.lgw-pagination-select-item').select2({minimumResultsForSearch: Infinity});
}
