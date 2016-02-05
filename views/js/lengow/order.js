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
        $('#lengow_order_wrapper').on('click', '.lengow_feed_pagination a', function () {
            if ($(this).hasClass('disabled')) {
                return false;
            }
            $('#lengow_order_wrapper .lengow_form_table input[name="p"]').val($(this).attr('data-page'));
            $('#lengow_order_wrapper .lengow_form_table').submit();
            return false;
        });
        $('#lengow_order_wrapper').on('change', '.lengow_form_table select', function () {
            $('#lengow_order_wrapper .lengow_form_table').submit();
            return false;
        });
        $('#lengow_order_wrapper').on('click', '.lengow_form_table .table_order', function () {
            $('#lengow_order_wrapper .lengow_form_table input[name="order_value"]').val($(this).attr('data-order'));
            $('#lengow_order_wrapper .lengow_form_table input[name="order_column"]').val($(this).attr('data-column'));
            $('#lengow_order_wrapper .lengow_form_table').submit();
            return false;
        });
        reload_table_js();

        $('#lengow_order_wrapper').on('submit', '.lengow_form_table', function () {
            var href = $(this).attr('data-href');
            var form = $(this).serialize();
            $.ajax({
                url: href + '&' + form,
                method: 'POST',
                data: {action: 'load_table'},
                dataType: 'script',
                success: function() {
                    init_tooltip();
                    reload_table_js();
                }
            });
            return false;
        });
        $('#lengow_order_wrapper').on('click', '.lengow_select_all', function () {
            if ($(this).prop('checked')) {
                $('#table_order tbody .lengow_selection').prop('checked', true);
                $('#table_order tbody ').find('.lengow_selection').each(function (index) {
                     $(this).parents('tr').addClass('select');
                });
                $('#lengow_order_wrapper .lengow_toolbar a').show();
            } else {
                $('#table_order tbody .lengow_selection').prop('checked', false);

                $('#table_order tbody ').find('.lengow_selection').each(function (index) {
                    $(this).parents('tr').removeClass('select');
                });
                $('#lengow_order_wrapper .lengow_toolbar a').hide();
            }
        });
        $('#lengow_order_wrapper').on('click', '.lengow_selection', function () {
            var id_shop = $(this).parents('table').attr('id').split('_')[2];
            $('#lengow_order_wrapper .lengow_toolbar a').show();

            if ($(this).prop('checked')) {
                $(this).parents('tr').addClass('select');
            } else {
                $(this).parents('tr').removeClass('select');
            }
            var findProductSelected = false;
            $(this).parents('table').find('.lengow_selection').each(function (index) {
                if ($(this).prop('checked')) {
                    findProductSelected = true;
                }
            });
            if (!findProductSelected) {
                $('#lengow_order_wrapper .lengow_toolbar a').hide();
            }
        });
        $('#lengow_order_wrapper').on('click', '.lengow_re_import', function () {
            var href = $(this).data('href');
            var action = $(this).data('action');
            var id = $(this).data('order');
            var type= $(this).data('type');
            var td = $(this).parents('td');
            $.ajax({
                url: href,
                method: 'POST',
                data: {action: action, id: id, type: type},
                dataType: 'script',
                success: function() {
                    init_tooltip();
                    reload_table_js();
                },
                beforeSend: function() {
                    td.html('<i class="fa fa-circle-o-notch fa-spin"></i>');
                }
            });
            return false;
        });

        $('#lengow_order_wrapper').on('click', '.lengow_mass_re_import', function() {
            $('#table_order').find('.lengow_selection').each(function (index) {
                if ($(this).prop('checked')) {
                    console.log('test');
                }
            });
            return false;
        });

    });
})(lengow_jquery);

function reload_table_js() {
    lengow_jquery('.lengow_datepicker').datepicker({
        format : 'dd/mm/yyyy',
        autoclose: true,
        clearBtn: true
    }).on('changeDate', function(e) {
        lengow_jquery('#lengow_order_wrapper .lengow_form_table').submit();
    });

}
