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

(function ($) {
    $(document).ready(function () {



        function checkShop() {
            var status = $('.lengow_check_shop');
            var href = status.attr('data-href');
            var data = {
                action: 'check_shop'
            };

            status.html('<i class="fa fa-circle-o-notch fa-spin"></i>');

            $.getJSON(href, data, function(content) {
                $.each(content, function(index, shop) {
                    var selector = lengow_jquery("#block_" + shop['shop_id'] + " .lengow_check_shop");
                    selector.attr("data-original-title", shop['tooltip']);

                    var title = shop['original_title'];

                    if (shop['check_shop'] === true) {
                        status.removeClass('lengow_check_shop_no_sync').addClass('lengow_check_shop_sync');
                        selector.attr("id", "lengow_shop_sync");
                    } else {
                        selector.attr("id", "lengow_shop_no_sync");
                        lengow_jquery("#block_" + shop['shop_id']
                            +  " .lengow_feed_block_header_title").append(shop['header_title']);
                        title = shop['header_title'];
                    }
                    selector.html("");

                    $('.lengow_shop_status_label').html(title);

                    init_tooltip()
                });
            });
        }

        /**
         * Refresh total product/product exported
         * @param data Number of products exported and total products
         * @param id_shop Shop id
         */
        function reloadTotal(data, id_shop) {
            lengow_jquery("#block_" + id_shop + " .lengow_exported").html(data['total_export_product']);
            lengow_jquery("#block_" + id_shop + " .lengow_total").html(data['total_product']);
        }

        checkShop();

        $('.lgw-container').on('change', '.lengow_switch_option', function () {
            var href = $(this).attr('data-href');
            var action = $(this).attr('data-action');
            var id_shop = $(this).attr('data-id_shop');
            var state = $(this).prop('checked');
            var data = {
                state: state ? 1 : 0,
                action: action,
                id_shop: id_shop
            };

            $.getJSON(href, data, function(content) {
                var selector = lengow_jquery('#block_' + id_shop + ' .lengow_feed_block_footer_content');

                reloadTotal(content, id_shop);

                if (content['state'] != null) {
                    if (content['state'] === true) {
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
            var id_shop = $(this).attr('data-id_shop');
            var id_product = $(this).attr('data-id_product');
            var state = $(this).prop('checked');
            var data = {
                 state: state ? 1 : 0,
                 action: action,
                 id_shop: id_shop,
                 id_product: id_product
            };

             $.getJSON(href, data, function(content) {
                 reloadTotal(content, id_shop);
             });
        });


        $('.lgw-container').on('click', '.lgw-pagination a', function () {
            if ($(this).parent().hasClass('disabled')) {
                return false;
            }
            var href = $(this).attr('data-href');
            var id_shop = $(this).parents('.lgw-pagination').attr('id').split('_')[2];

            $('#lengow_feed_wrapper #form_table_shop_' + id_shop + ' input[name="p"]').val($(this).attr('data-page'));
            $('#lengow_feed_wrapper #form_table_shop_' + id_shop).submit();
            return false;
        });

        $('#lengow_feed_wrapper').on('click', '.lengow_form_table .table_order', function () {
            var id_shop = $(this).parents('table').attr('id').split('_')[2];
            $('#lengow_feed_wrapper #form_table_shop_' + id_shop + ' input[name="order_value"]').val($(this).attr('data-order'));
            $('#lengow_feed_wrapper #form_table_shop_' + id_shop + ' input[name="order_column"]').val($(this).attr('data-column'));
            $('#lengow_feed_wrapper #form_table_shop_' + id_shop).submit();
            return false;
        });

        // UPDATE BY INPUT

        var typingTimer;
        var id_shop;
        $('#lengow_feed_wrapper').on('keyup', 'thead input[type="text"]', function () {
            id_shop = $(this).closest('table').attr('id').split('_')[2];
            clearTimeout(typingTimer);
            typingTimer = setTimeout(doneTyping, 750);
        });
        $('#lengow_feed_wrapper').on('keydown', 'thead input[type="text"]', function () {
            clearTimeout(typingTimer);
        });
        function doneTyping (){
            $('#lengow_feed_wrapper #form_table_shop_' + id_shop).submit();
        }

        $('#lengow_feed_wrapper').on('submit', '.lengow_form_table', function () {
            var href = $(this).attr('data-href');
            var id_shop = $(this).attr('id').split('_')[3];
            var form = $(this).serialize();
            var url = href + "&" + form;
            var data = {
                action: 'load_table',
                id_shop: id_shop
            };

            $.getJSON(url, data, function(content) {
                lengow_jquery("#block_" + content['shop_id']
                    + " .lengow_feed_block_footer_content").html(content['footer_content']);

                if (content['bootstrap_switch_readonly']) {
                    lengow_jquery(".lengow_switch").bootstrapSwitch({readonly: true});
                }
            });

            return false;
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_select_all', function () {
            var id_shop = $(this).attr('id').split('_')[2];
            if ($(this).prop('checked')) {
                $('#table_shop_' + id_shop + ' tbody .lengow_selection').prop('checked', true);
                $('#table_shop_' + id_shop + ' tbody tr').addClass('select');
                $('#block_' + id_shop + ' .lengow_toolbar a').show();
                $('#block_' + id_shop + ' .lengow_toolbar .lengow_select_all_shop').show();
            } else {
                $('#table_shop_' + id_shop + ' tbody .lengow_selection').prop('checked', false);
                $('#table_shop_' + id_shop + ' tbody tr').removeClass('select');
                $('#block_' + id_shop + ' .lengow_toolbar a').hide();
                $('#block_' + id_shop + ' .lengow_toolbar .lengow_select_all_shop').hide();
            }
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_selection', function () {
            var id_shop = $(this).parents('table').attr('id').split('_')[2];
            $('#block_' + id_shop + ' .lengow_toolbar a').show();

            if ($(this).prop('checked')) {
                $(this).parents('tr').addClass('select');
            } else {
                $('#block_' + id_shop + ' .lengow_toolbar .lengow_select_all_shop input').prop('checked', false);
                $(this).parents('tr').removeClass('select');

            }
            var findProductSelected = false;
            $(this).parents('table').find('.lengow_selection').each(function (index) {
                if ($(this).prop('checked')) {
                    findProductSelected = true;
                }
            });
            if (!findProductSelected) {
                $('#block_' + id_shop + ' .lengow_toolbar a').hide();
            }
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_add_to_export', function () {
            var href = $(this).attr('data-href');
            var id_shop = $(this).attr('data-id_shop');
            var message = $(this).attr('data-message');
            var form = $('#form_table_shop_' + id_shop).serialize();
            var url = href + "&" + form;
            var check = $('#select_all_shop_' + id_shop).prop('checked');
            var data = {
                action: 'add_to_export',
                id_shop: id_shop,
                select_all: check
            };
            if (!check || (check && confirm(message))) {
                $.getJSON(url, data, function(content) {
                    if (content['message']) {
                        alert(content['message']);
                    } else {
                        $.each(content['product_id'], function(idx, p_id) {
                            lengow_jquery("#shop_" + id_shop + "_" + p_id + " .lgw-switch").addClass("checked");
                        });
                        reloadTotal(content, id_shop);
                    }
                });
            }
            return false;
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_remove_from_export', function () {
            var href = $(this).attr('data-href');
            var id_shop = $(this).attr('data-id_shop');
            var message = $(this).attr('data-message');
            var form = $('#form_table_shop_' + id_shop).serialize();
            var url = href + '&' + form;
            var check = $('#select_all_shop_' + id_shop).prop('checked');
            var data = {
                action: 'remove_from_export',
                id_shop: id_shop,
                select_all: check
            };
            if (!check || (check && confirm(message))) {
                $.getJSON(url, data, function(content) {
                    if (content['message']) {
                        alert(content['message']);
                    } else {
                        $.each(content['product_id'], function(idx, p_id) {
                            lengow_jquery("#shop_" + id_shop + "_" + p_id + " .lgw-switch").removeClass("checked");
                        });
                        reloadTotal(content, id_shop);
                    }
                });
            }
            return false;
        });

        $('#lengow_feed_wrapper').on('click', '.lengow_select_all_shop input', function () {
            var id_shop = $('.lengow_select_all').attr('id').split('_')[2];
            if ($(this).prop('checked')) {
                $('#table_shop_' + id_shop + ' tbody .lengow_selection').prop('checked', true);
                $('.lengow_selection').parents('tr').addClass('select');
            }
        });

        $('.lengow_table').on('click', '.table_row td:not(.no-link)', function(){
            var url = $(this).closest('.table_row').find('.feed_name a').attr('href');
            window.open(url, '_blank');
            return false;
        });
    });
})(lengow_jquery);
