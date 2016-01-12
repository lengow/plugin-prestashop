lengow_jquery( document ).ready(function() {
  $('#lengow_feed_wrapper').on('switchChange.bootstrapSwitch', '.lengow_switch_option', function(event, state) {
    if (event.type == "switchChange") {
      var href = $(this).attr('data-href');
      var action = $(this).attr('data-action');
      var id_shop = $(this).attr('data-id_shop');
      $.ajax({
        url: href,
        method: 'POST',
        data: { state : state ? 1 : 0, action : action, id_shop: id_shop },
        dataType: 'script'
      });
    }
  });
  $('#lengow_feed_wrapper').on('switchChange.bootstrapSwitch', '.lengow_switch_product', function(event, state) {
    if (event.type == "switchChange") {
      var href = $(this).attr('data-href');
      var action = $(this).attr('data-action');
      var id_shop = $(this).attr('data-id_shop');
      var id_product = $(this).attr('data-id_product');
      $.ajax({
        url: href,
        method: 'POST',
        data: { state : state ? 1 : 0, action : action, id_shop: id_shop, id_product: id_product },
        dataType: 'script'
      });
    }
  });
  $('#lengow_feed_wrapper').on('click', '.lengow_feed_pagination a', function() {
    var href = $(this).attr('data-href');
    var id_shop = $(this).parents('.lengow_feed_pagination').attr('id').split('_')[2];
    $.ajax({
      url: href,
      method: 'POST',
      data: { action: 'load_table', id_shop: id_shop },
      dataType: 'script',
      success: function() {
        $(".lengow_switch").bootstrapSwitch();
      }
    });
    return false;
  });

  $('#lengow_feed_wrapper').on('submit', '.lengow_form_table', function() {
    var href = $(this).attr('data-href');
    var id_shop = $(this).attr('id').split('_')[3];
    var form = $(this).serialize();
    $.ajax({
      url: href+'&'+form,
      method: 'POST',
      data: { action: 'load_table', id_shop: id_shop},
      dataType: 'script',
      success: function() {
        $(".lengow_switch").bootstrapSwitch();
      }
    });
    return false;
  });
});