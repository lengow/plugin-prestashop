(function ($) {
    $(document).ready(function () {

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
    });

})(lengow_jquery);