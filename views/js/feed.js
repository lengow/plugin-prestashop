(function ($) {
    $(document).ready(function () {
        $('#lengow_feed_wrapper').on('switchChange.bootstrapSwitch', '.lengow_switch_option', function (event, state) {
            if (event.type == "switchChange") {
                var href = $(this).attr('data-href');
                var action = $(this).attr('data-action');
                var id_shop = $(this).attr('data-id_shop');
                $.ajax({
                    url: href,
                    method: 'POST',
                    data: {state: state ? 1 : 0, action: action, id_shop: id_shop},
                    dataType: 'script'
                });
            }
        });
        $('#lengow_feed_wrapper').on('switchChange.bootstrapSwitch', '.lengow_switch_product', function (event, state) {
            if (event.type == "switchChange") {
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
            }
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_feed_pagination a', function () {
            if ($(this).hasClass('disabled')) {
                return false;
            }
            var href = $(this).attr('data-href');
            var id_shop = $(this).parents('.lengow_feed_pagination').attr('id').split('_')[2];

            $('#lengow_feed_wrapper #form_table_shop_'+id_shop+' input[name="p"]').val($(this).attr('data-page'));
            $('#lengow_feed_wrapper #form_table_shop_'+id_shop).submit();
            return false;
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_form_table .table_order', function () {
            var id_shop = $(this).parents('table').attr('id').split('_')[2];
            $('#lengow_feed_wrapper #form_table_shop_'+id_shop+' input[name="order_value"]').val($(this).attr('data-order'));
            $('#lengow_feed_wrapper #form_table_shop_'+id_shop+' input[name="order_column"]').val($(this).attr('data-column'));
            $('#lengow_feed_wrapper #form_table_shop_'+id_shop).submit();
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
                $('#block_' + id_shop + ' .lengow_toolbar a').show();
            } else {
                $('#table_shop_' + id_shop + ' tbody .lengow_selection').prop('checked', false);
                $('#block_' + id_shop + ' .lengow_toolbar a').hide();
            }
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_selection', function () {
            var id_shop = $(this).parents('table').attr('id').split('_')[2];
            $('#block_' + id_shop + ' .lengow_toolbar a').show();

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
            var form = $('#form_table_shop_' + id_shop).serialize();
            $.ajax({
                url: href + '&' + form,
                method: 'POST',
                data: {action: 'add_to_export', id_shop: id_shop},
                dataType: 'script'
            });
            return false;
        });
        $('#lengow_feed_wrapper').on('click', '.lengow_remove_from_export', function () {
            var href = $(this).attr('data-href');
            var id_shop = $(this).attr('data-id_shop');
            var form = $('#form_table_shop_' + id_shop).serialize();
            $.ajax({
                url: href + '&' + form,
                method: 'POST',
                data: {action: 'remove_from_export', id_shop: id_shop},
                dataType: 'script'
            });
            return false;
        });
    });
})(lengow_jquery);