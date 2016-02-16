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
                $('#error_select_country').html('');
            } else {
                $('#error_select_country').html('<span>No country selected.</span>');
            }

            return false;
        });

        $('#marketplace_country').on('click', '.delete_lengow_default_carrier', function () {
            if (confirm('Are you sure ?')) {
                var href = $('.lengow_default_carrier').attr('data-href');
                $.ajax({
                    url: href,
                    method: 'POST',
                    data: {action: 'delete_country', id_country: $(this).attr('data-id-country')},
                    dataType: 'script'
                });
            }

            return false;
        });

        $('#add_marketplace_country').on('change', '.carrier', function () {
            if ($(this).val() !== "") {
                $(this).parents('.lengow_default_carrier').removeClass('no_carrier');
            } else {
                $(this).parents('.lengow_default_carrier').addClass('no_carrier');
            }

            return false;

        });

        $('#add_marketplace_country').on('change', '.carrier', function () {
            if ($(this).val() !== "") {
                $(this).parents('.marketplace_carrier ').removeClass('no_carrier');
            } else {
                $(this).parents('.marketplace_carrier ').addClass('no_carrier');
            }

            return false;

        });

        $(".navigation ul.subMenu").hide();


        $(".navigation li.toggleSubMenu > a").click( function () {

            if ($(this).next("ul.subMenu:visible").length != 0) {
                $(this).next("ul.subMenu").slideUp("normal");
            }

            else {
                $(".navigation ul.subMenu").slideUp("normal");
                $(this).next("ul.subMenu").slideDown("normal");
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