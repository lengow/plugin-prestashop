lengow_jquery( document ).ready(function() {
  $('.lengow_switch_option').on('switchChange.bootstrapSwitch', function(event, state) {
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
  $('.lengow_switch_product').on('switchChange.bootstrapSwitch', function(event, state) {
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
});