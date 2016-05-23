;$(document).ready(function() {
    /**
     * Ajax update
     */
    var notify_update = function() {
        $.ajax({
            url: Routing.generate('lengow_notify_get'),
            cache: false,
            method: 'POST',
            dataType: 'json',
        }).done(function(data) {
            if(data.count > 0) {
                $.each(data.notifications, function(account, notifications) {
                    $.each(notifications, function(i, notification) {
                        var id = notification.id;
                        var $notification = $('.notification[data-id="'+id+'"]');
                        if($notification.length == 0) {
                            add_notif(id);
                        }
                    });
                });
            }
        }).fail(function() {
            console.error('Error while retrieving notifications');
        });
    };
    // Updates the notifications
    // if the notification button is found
    if ($('.notify-button').length > 0) {
      setInterval(notify_update, 1000 * 60);
    }

    /**
     * UI Update
     */
    var total_class = '.notify-total',
        add_notif = function(notification_id) {
                        var current_total = parseInt($(total_class).eq(0).text());
                        var new_total = current_total + 1;
                        $(total_class).removeClass('hidden');
                        $(total_class).text(new_total);
                        $.ajax({
                            url: Routing.generate('lengow_notify_render', {notification_id: notification_id})
                        }).done(function(data) {
                            $('.notif-box').prepend(data);
                            $('.no-notif').addClass('hidden');
                        });
                    },
        remove_notif = function(notification_id) {
                        $.ajax({
                            url: Routing.generate('lengow_notify_read', {notification_id: notification_id}),
                            dataType: 'json',
                        }).done(function(data) {
                            var current_total = parseInt($(total_class).eq(0).text());
                            var new_total = current_total - 1;
                            if(new_total == 0) {
                                // Hide red circle on button
                                $('.new.notify-total').fadeOut(300);
                                $('.no-notif').removeClass('hidden');
                                $(total_class).text(new_total);
                            } else {
                                $(total_class).text(new_total);
                            }
                        });
                    };

    // Events listeners
    $('.dropdown.keep-open').on({
        "shown.bs.dropdown": function() { this.closable = true; },
        "click":             function(e) {
            var target = $(e.target);
            if(target.hasClass("notify-button"))
                this.closable = true;
            else
               this.closable = false;
        },
        "hide.bs.dropdown":  function() { return this.closable; }
    });
    $(document).on('click', '.notify-read', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var $notification = $('.notification[data-id="'+id+'"]');
        var notification = $(this).parent().parent();
        $notification.slideToggle(150);
        setTimeout(function () {
            remove_notif(id);
            $notification.remove();
        }, 200);
    });
});
