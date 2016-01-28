(function ($) {
    $(document).ready(function () {
        $('#lengow_order_wrapper').on('click', '.lengow_feed_pagination a', function () {
            if ($(this).hasClass('disabled')) {
                return false;
            }
            var href = $(this).attr('data-href');
            $.ajax({
                url: href,
                method: 'POST',
                data: {action: 'load_table'},
                dataType: 'script',
                success: function() {
                    init_tooltip();
                }
            });
            return false;
        });
        $('#lengow_order_wrapper').on('submit', '.lengow_form_table', function () {
            var href = $(this).attr('data-href');
            var form = $(this).serialize();
            $.ajax({
                url: href + '&' + form,
                method: 'POST',
                data: {action: 'load_table'},
                dataType: 'script'
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
    });
})(lengow_jquery);