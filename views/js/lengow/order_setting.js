function addScoreCarrier(){
    nbs = 0;

    $('.score').removeClass('red').removeClass('green').removeClass('orange');

    $('.sub').each(function() {
        var total = $(this).find('.carrier').length;
        $(this).find('.carrier').each(function(){

            if ($(this).val() !== "") {
                nbs += 1 ;
            }
        });

        $(this).parents('li.lengow_marketplace_carrier').find('.score').html(nbs+' / '+total);

        if (nbs == total){

            $(this).parents('li.lengow_marketplace_carrier').find('.score').addClass('green');
        } else if (nbs <= 1){

            $(this).parents('li.lengow_marketplace_carrier').find('.score').addClass('red');
        } else {

            $(this).parents('li.lengow_marketplace_carrier').find('.score').addClass('orange');
        }
        nbs = 0;

    });
}

(function ($) {

    $(document).ready(function () {

        addScoreCarrier();

        function changeStockMP() {
            if ($("input[name='LENGOW_IMPORT_SHIP_MP_ENABLED']").prop('checked')) {
                $('.lengow_import_stock_ship_mp').show();
            } else {
                $('.lengow_import_stock_ship_mp').hide();
            }
        }

        changeStockMP();

        $('#lengow_form_wrapper').on('click', '.add_lengow_default_carrier', function () {
            if ($('#select_country').val() !== "") {
                var href = $(this).attr('data-href');

                $.ajax({
                    url: href,
                    method: 'POST',
                    data: {action: 'add_country', id_country: $('#select_country').val()},
                    dataType: 'script'
                });
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
                $(this).parents('.add_country').removeClass('no_carrier');
                addScoreCarrier();
            } else {
                $(this).parents('.add_country').addClass('no_carrier');
                addScoreCarrier();
            }

            return false;

        });

        $('#add_marketplace_country').on('change', '.carrier', function () {
            if ($(this).val() !== "") {
                $(this).parents('.marketplace_carrier ').removeClass('no_carrier');
                addScoreCarrier();
            } else {
                $(this).parents('.marketplace_carrier ').addClass('no_carrier');
                addScoreCarrier();
            }

            return false;

        });


        $(".sub").hide();
        $(".sub:first").show();
        $("#lengow_form_wrapper").on('click', '.lengow_marketplace_carrier h4',function(){
            $(this).next().next().toggle('100');
        });

        $("input[name='LENGOW_IMPORT_SHIP_MP_ENABLED']").on('switchChange.bootstrapSwitch', function (event, state) {
            if (event.type == "switchChange") {
                changeStockMP();
            }
        });


        $(".lengow_submit_order_setting").on('click', function(e){
            if ($(".carrier:first").val() == "") {
                e.preventDefault();
                $(".sub:first").show();
                $('html, body').stop().animate({scrollTop: $(".container2").offset().top}, 100);
                $('#default_carrier_missing').html('<span>No default carrier selected.</span>');
                return false;
            }

            $('#error_select_country').html('');
        });

    });

})(lengow_jquery);