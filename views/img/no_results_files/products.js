$(document).ready(function() {
    var structure_id = $('#main-content').data('structure-id');
    /**
     * Filter catalog-item item on channel select list change
     */
    $('#filter-catalog-by-channel').on('change', function() {
        var selected = $(this).find('option:selected'),
            id = selected.val();
        if(id.length > 0) {
            channel_id = parseInt(id);
            $('.catalog-item').each(function(index, item) {
                var channel_ids = $(item).data('channels');
                if($.inArray(channel_id, channel_ids) === -1) {
                    $(item).addClass('hidden');
                } else {
                    $(item).removeClass('hidden');
                }
            });
        } else {
            $('.catalog-item').removeClass('hidden');
        }
        checkSearch();
    });

    /**
     * Filter catalog by status
     */
    $('#filter-catalog-by-status').on('change', function() {
        var selected = $(this).find('option:selected'),
            status = selected.val();

        $.each($('.catalog-item'), function(index, item) {
            try {
                if ($(item).data(status) == 0) {
                    $(item).addClass('hidden');
                } else {
                    $(item).removeClass('hidden');
                }
            } catch (e) {
                // Reset all the filters
                $(item).removeClass('hidden');
            }
        });
        checkSearch();
    });

    /**
     * Filter catalog by draft status
     */
    $('#in-draft').click(function() {
        if($(this).is(':checked')) {
            $('.catalog-item').each(function(index, item) {
                var in_draft = $(item).data('in-draft');
                if(in_draft !== 1) {
                    $(item).addClass('hidden');
                }
            });
        } else {
            $('.catalog-item').removeClass('hidden');
        }
        checkSearch();
    });

    /**
     * Search input
     */
    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    $('#search-catalog').keyup(function() {
        delay(function(){
            var search_value = $('#search-catalog').val().toUpperCase();
            $('.catalog-item').each(function(index, item) {
                var data_search = $(item).data('search').toUpperCase();
                if(data_search.indexOf(search_value) === -1) {
                    $(item).addClass('hidden');
                } else {
                    $(item).removeClass('hidden');
                }
            });
            checkSearch();
        }, 500 );
    });

    function checkSearch() {
        var total = $('.catalog-item').not(':hidden').length;
        if (total === 0) {
            // Print no results screen
            $('.noContent').fadeIn(300);
        } else {
            // Hide no results screen
            $('.noContent').hide();
        }
    }

    var initial_name = '';
    $('#catalog-name').focus(function() {
        initial_name = $(this).text();
    }).focusout(function() {
        var new_name = $(this).text();
        if (initial_name !== $(this).text()) {
            $.ajax({
                url: Routing.generate('lengow_aviato_product_update_catalog_name', {catalog_structure_id: structure_id}),
                type: 'POST',
                dataType: 'json',
                data: {
                    value: new_name.trim()
                },
                success: function(data) {

                }
            });
        }
    });

});