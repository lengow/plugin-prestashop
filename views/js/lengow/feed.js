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
            var href = $('.lengow_check_shop').attr('data-href');
            $.ajax({
                url: href,
                method: 'POST',
                data: {action: 'check_shop'},
                dataType: 'script',
                beforeSend: function () {
                    $('.lengow_check_shop').html('<i class="fa fa-circle-o-notch fa-spin"></i>');
                },
                success: function () {
                    init_tooltip()
                }
            });
        }

        checkShop();

        $('.lgw-container').on('change', '.lengow_switch_option', function () {
            var href = $(this).attr('data-href');
            var action = $(this).attr('data-action');
            var id_shop = $(this).attr('data-id_shop');
            var state = $(this).prop('checked') ;
            $.ajax({
                url: href,
                method: 'POST',
                data: {state: state ? 1 : 0, action: action, id_shop: id_shop},
                dataType: 'script'
            });
        });

         $('.lgw-container').on('change', '.lengow_switch_product', function () {
            var href = $(this).attr('data-href');
            var action = $(this).attr('data-action');
            var id_shop = $(this).attr('data-id_shop');
            var id_product = $(this).attr('data-id_product');
            $.ajax({
                url: href,
                method: 'POST',
                data: {state: state ? 1 : 0, action: action, id_shop: id_shop, id_product: id_product},
                dataType: 'script'
            });
        });
        $('#lengow_feed_wrapper').on('click', '.lgw-pagination a', function () {
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
        $('#lengow_feed_wrapper').on('submit', '.lengow_form_table', function () {
            var href = $(this).attr('data-href');
            var id_shop = $(this).attr('id').split('_')[3];
            var form = $(this).serialize();
            $.ajax({
                url: href + '&' + form,
                method: 'POST',
                data: {action: 'load_table', id_shop: id_shop},
                dataType: 'script',
                success: function () {
                    $(".lengow_switch").bootstrapSwitch();
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
            var check = $('#select_all_shop_' + id_shop).prop('checked');
            if (!check || (check && confirm(message))) {
                $.ajax({
                    url: href + '&' + form,
                    method: 'POST',
                    data: {action: 'add_to_export', id_shop: id_shop, select_all: check},
                    dataType: 'script'
                });
            }
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_remove_from_export', function () {
            var href = $(this).attr('data-href');
            var id_shop = $(this).attr('data-id_shop');
            var message = $(this).attr('data-message');
            var form = $('#form_table_shop_' + id_shop).serialize();
            var check = $('#select_all_shop_' + id_shop).prop('checked');
            if (!check || (check && confirm(message))) {
                $.ajax({
                    url: href + '&' + form,
                    method: 'POST',
                    data: {action: 'remove_from_export', id_shop: id_shop, select_all: check},
                    dataType: 'script'
                });
            }
        });

        $('#lengow_feed_wrapper').on('click', '.lengow_select_all_shop input', function () {
            var id_shop = $('.lengow_select_all').attr('id').split('_')[2];
            if ($(this).prop('checked')) {
                $('#table_shop_' + id_shop + ' tbody .lengow_selection').prop('checked', true);
                $('.lengow_selection').parents('tr').addClass('select');
            }
        });


    });
})(lengow_jquery);