(function ($) {
    $(document).ready(function () {

        function changeStockMP() {
            if ($("input[name='LENGOW_IMPORT_SHIP_MP_ENABLED']").prop('checked')) {
                $('.lengow_import_stock_ship_mp').show();
            } else {
                $('.lengow_import_stock_ship_mp').hide();
            }
        }

        changeStockMP();

        $('.add_lengow_default_carrier').on('click', function () {
            if ($('#select_country').val() !== "") {
                var href = $(this).attr('data-href');

                $.ajax({
                    url: href,
                    method: 'POST',
                    data: {action: 'add_country', id_country: $('#select_country').val()},
                    dataType: 'script'
                })
            } else {
                $('.select_country').append('<span>No country selected.</span>');
            }

            return false;
        });

        $('#add_country').on('click', '.delete_lengow_default_carrier', function () {
            var href = $('.lengow_default_carrier').attr('data-href');
            $.ajax({
                url: href,
                method: 'POST',
                data: {action: 'delete_country', id_country: $(this).attr('data-id-country')},
                dataType: 'script'
            });

            return false;
        });

        $('#add_country').on('change', '.carrier', function () {
            if ($(this).val() !== "") {
                $(this).parents('.lengow_default_carrier').removeClass('no_carrier');
            } else {
                $(this).parents('.lengow_default_carrier').addClass('no_carrier');
            }

            return false;

        });

        $("input[name='LENGOW_IMPORT_SHIP_MP_ENABLED']").on('switchChange.bootstrapSwitch', function (event, state) {
            if (event.type == "switchChange") {
                changeStockMP();
            }
        });

    });

})(lengow_jquery);