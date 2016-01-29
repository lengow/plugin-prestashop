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
                }
            });
            return false;
        });
        $('#lengow_order_wrapper').on('click', '.lengow_select_all', function () {
            if ($(this).prop('checked')) {
                $('#table_order tbody .lengow_selection').prop('checked', true);
                $('#lengow_order_wrapper .lengow_toolbar a').show();
            } else {
                $('#table_order tbody .lengow_selection').prop('checked', false);
                $('#lengow_order_wrapper .lengow_toolbar a').hide();
            }
        });
        $('#lengow_order_wrapper').on('click', '.lengow_selection', function () {
            var id_shop = $(this).parents('table').attr('id').split('_')[2];
            $('#lengow_order_wrapper .lengow_toolbar a').show();

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
    });
})(lengow_jquery);