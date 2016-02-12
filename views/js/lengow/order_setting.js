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

        $('#add_country').on('change', '.carrier', function () {
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

        $(".navigation li.toggleSubMenu span").each( function () {
            $(this).replaceWith('<a href="" title="Afficher le sous-menu">Hello<\/a>') ;
        } ) ;

        // On modifie l'évènement "click" sur les liens dans les items de liste
        // qui portent la classe "toggleSubMenu" :
        $(".navigation li.toggleSubMenu > a").click( function () {
            // Si le sous-menu était déjà ouvert, on le referme :
            if ($(this).next("ul.subMenu:visible").length != 0) {
                $(this).next("ul.subMenu").slideUp("normal");
            }
            // Si le sous-menu est caché, on ferme les autres et on l'affiche :
            else {
                $(".navigation ul.subMenu").slideUp("normal");
                $(this).next("ul.subMenu").slideDown("normal");
            }
            // On empêche le navigateur de suivre le lien :
            return false;
        });
    });

})(lengow_jquery);