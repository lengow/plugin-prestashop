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

(function ($) {
    $(document).ready(function () {

        $('#lengow_order_wrapper').on('click', '.lgw-pagination a', function () {
            if ($(this).parent().hasClass('disabled')) {
                return false;
            }
            $('#lengow_order_wrapper .lengow_form_table input[name="p"]').val($(this).attr('data-page'));
            $('#lengow_order_wrapper .lengow_form_table').submit();
            return false;
        });

        $('#lengow_order_wrapper').on('change', '.lgw-pagination-select-item', function () {
            $('#lengow_order_wrapper .lengow_form_table input[name="nb_per_page"]').val($(this).val());
            $('#lengow_order_wrapper .lengow_form_table').submit();
            return false;
        });

        $('#lengow_order_wrapper').on('click', '.lengow_form_table .table_order', function () {
            $('#lengow_order_wrapper .lengow_form_table input[name="order_value"]').val($(this).attr('data-order'));
            $('#lengow_order_wrapper .lengow_form_table input[name="order_column"]').val($(this).attr('data-column'));
            $('#lengow_order_wrapper .lengow_form_table').submit();
            return false;
        });

        $('#lengow_order_wrapper').on('submit', '.lengow_form_table', function () {
            var href = $(this).attr('data-href');
            var form = $(this).serialize();
            var url = href + '&' + form;
            var data = {
                action: 'load_table'
            };

            $.getJSON(url, data, function(content) {
                lengow_jquery("#lengow_order_table_wrapper").html(content['order_table']);

                init_tooltip();
                reload_table_js();
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

        $('#lengow_order_wrapper').on('click', '.lengow_re_import, .lengow_re_send', function () {
            var href = $(this).data('href');
            var action = $(this).data('action');
            var id = $(this).data('order');
            var type= $(this).data('type');
            var td = $(this).parents('td');
            var trId = $(this).parents('tr').attr('id');
            var select = $(this).parents('tr').find('.lengow_selection').prop('checked');
            var data = {action: action, id: id, type: type};

            td.html('<i class="fa fa-circle-o-notch fa-spin"></i>');
            $('#lengow_import_orders').hide();

            $.getJSON(href, data, function(content) {
                lengow_jquery("#order_" + content['id_order_lengow']).replaceWith(content['html']);
                init_tooltip();
                reload_table_js();

                if (select) {
                    $('#'+trId).addClass('select').find('.lengow_selection').prop('checked', true);
                }

                if ($('.lengow_status .fa-spin').length == 0) {
                    $('#lengow_import_orders').show();
                }
            });

            return false;
        });

        $('#lengow_order_wrapper').on('click', '.lengow_mass_re_import', function() {
            $('#table_order').find('.lengow_selection').each(function (index) {
                if ($(this).prop('checked')) {
                    $(this).parents('tr').find('.lengow_re_import').trigger( "click" );
                }
            });
            return false;
        });

        $('#lengow_order_wrapper').on('click', '.lengow_mass_re_send', function() {
            $('#table_order').find('.lengow_selection').each(function (index) {
                if ($(this).prop('checked')) {
                    $(this).parents('tr').find('.lengow_re_send').trigger( "click" );
                }
            });
            return false;
        });

        $('#lengow_order_wrapper').on('click', '#lengow_import_orders', function() {
            var button = $(this);
            var href = $(this).data('href');
            var data = {action: 'import_all'};

            $('#lengow_charge_import_order').fadeIn(150);

            $.getJSON(href, data, function(content) {
                lengow_jquery("#lengow_wrapper_messages").html(content['message']);
                lengow_jquery("#lengow_warning_message").html(content['warning_message']);
                lengow_jquery("#lengow_last_importation").html(content['last_importation']);
                lengow_jquery("#lengow_import_orders").html(content['import_orders']);
                lengow_jquery("#lengow_order_table_wrapper").html(content['list_order']);
                if (content['show_carrier_notification']) {
                    $('.lengow-nav .js-alert-matching').addClass('alert-matching')
                }
                init_tooltip();
                reload_table_js();
                $('#lengow_charge_import_order').fadeOut(150);
                setTimeout(function(){
                    $('#lengow_wrapper_messages').fadeIn(250);
                }, 300);
            }).fail(function() {
                $('#lengow_charge_import_order').fadeOut(150);
            });
        });

        $('#lengow_order_wrapper').on('click', '#lengow_update_order', function() {
            var button = $(this);
            var href = $(this).data('href');
            if (($(this).parents('.lengow_form_update_order').find('#select_shop').val() != "") &&
                ($(this).parents('.lengow_form_update_order').find('#select_mkp').val() != "") &&
                ($(this).parents('.lengow_form_update_order').find('#sku_order').val() != "")) {

                var data = {
                    action: 'update_order',
                    shop_id: $(this).parents('.lengow_form_update_order').find('#select_shop').val(),
                    marketplace_name: $(this).parents('.lengow_form_update_order').find('#sku_mkp').val(),
                    marketplace_sku: $(this).parents('.lengow_form_update_order').find('#sku_order').val(),
                    delivery_address_id: $(this).parents('.lengow_form_update_order').find('#delivery_adress_id').val(),
                };

                button.html('<i class="fa fa-circle-o-notch fa-spin"></i>');

                $.getJSON(href, data, function(content) {
                    lengow_jquery("#lengow_wrapper_messages").html(content['message']);
                    lengow_jquery("#lengow_update_order").html(content['update_order']);
                    lengow_jquery("#lengow_order_table_wrapper").html(content['order_table']);

                    init_tooltip();
                    reload_table_js();
                });

                $('#error_update_order').html('');
            } else {
                $('#error_update_order').html('<p>Please complete all fields</p>');
                return false
            }
        });

        $('#lengow_order_wrapper').on('click', '#lengow_update_some_orders', function() {
            var button = $(this);
            var href = $(this).data('href');
            if (( $(this).parents('.lengow_form_update_some_orders').find('#select_shop').val() != "") &&
                ($(this).parents('.lengow_form_update_some_orders').find('#import_days').val() != "")) {
                var data = {
                    action: 'update_some_orders',
                    shop_id: $(this).parents('.lengow_form_update_some_orders').find('#select_shop').val(),
                    days: $(this).parents('.lengow_form_update_some_orders').find('#import_days').val(),
                    type: 'manuel'
                };

                button.html('<i class="fa fa-circle-o-notch fa-spin"></i>');

                $.getJSON(href, data, function(content) {
                    lengow_jquery("#lengow_wrapper_messages").html(content['message']);
                    lengow_jquery("#lengow_update_some_orders").html(content['update_some_orders']);
                    lengow_jquery("#lengow_order_table_wrapper").html(content['order_table']);

                    init_tooltip();
                    reload_table_js();
                });

                $('#error_update_some_orders').html('');
            } else {
                $('#error_update_some_orders').html('<p>Please complete all fields</p>');
                return false
            }
        });

        $('#lengow_order_wrapper').on('click', '#table_order td.link', function() {
            var link = $(this).parents('tr').find('td.reference a');
            if (link.length > 0){
                window.open(link.attr('href'));
            }
            return false;
        });

        // update by select
        $('#lengow_order_table_wrapper').on('change', 'thead select', function(){
            lengow_jquery('#lengow_order_wrapper .lengow_form_table').submit();
        });

        // update by inputs
        var typingTimer;
        $('#lengow_order_table_wrapper').on('keyup', 'thead input[type="text"]', function () {
          clearTimeout(typingTimer);
          typingTimer = setTimeout(doneTyping, 750);
        });
        $('#lengow_order_table_wrapper').on('keydown', 'thead input[type="text"]', function () {
          clearTimeout(typingTimer);
        });
        function doneTyping () {
          lengow_jquery('#lengow_order_wrapper .lengow_form_table').submit();
        }

        $('.lengow_table').on('click', '.table_row td:not(.no-link)', function(){
            var url = $(this).closest('.table_row').find('.reference a').attr('href');
            if (url) {
               window.open(url, '_blank'); 
            };
            return false;
        });

        // table header filters
        pluginsRender();

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

    lengow_jquery('#table_order td.link').hover(
        function() {
            if (lengow_jquery(this).parents('tr').find('td.reference a').length){
                lengow_jquery(this).css('cursor','pointer');
            }
        }, function () {
            /*lengow_jquery(this).css('cursor','auto');*/
        }
    );
    pluginsRender();
}

function pluginsRender(){
    // selects
    lengow_jquery('#form_table_order .table select').select2();
    lengow_jquery('.lgw-pagination-select-item').select2({minimumResultsForSearch: Infinity});
}
