/////
// CHECK LOGIN
/////
(function($){

    /**
     * Check if user authentication is valid
     * and print modal if not to relogin him
     * for Yann, my sweetheart ;)
     */
    var loginChecker = (function() {

        var _isLogged = true,
            _checkDelay = 1000 * 60 * 5; //Delay between check, each 5 minutes
            $overlay = null,
            $form = null,
            _checkAuthUrl = Routing.generate('lengow_user_check_authentification'),
            checkauthInterval = null
            ;

        /**
         * This function do an ajax call on page
         * and return true or false is user is logged in
         */
        var checkAuthentification = function() {
            $.ajaxq('auth', {
                    url: _checkAuthUrl
                })
                .done(function(data) {
                    if (data.auth == false) {
                        _isLogged = false;
                        loadForm();
                    } else if (null !== $overlay && null !== $form && data.auth) {
                        hideLogForm();
                    }
                })
            ;
        };

        /**
         * Remove overlay and form
         */
        var hideLogForm = function () {
            // Set body css default properties
            $('body')
                .css({'overflow': ''})
            ;

            // Remove blur effect of page content
            $('.global, .navbar').removeClass('blur');


            // Remove content
            $overlay.remove();
            $overlay = null;
            $form.remove();
            $form = null;
        };


        /**
         * Add overlay and blur effect, then print login form
         */
        var buildLoginForm = function() {

            // If form already open
            if (null !== $overlay && null !== $form) {
                return false;
            }

            // Place overlay
            $overlay = $('<div>');
            $overlay.attr('id', 'session_expired_overlay')
                .css({
                    'position': 'absolute',
                    'top': '0',
                    'left': '0',
                    'z-index': '9999',
                    'background-color': 'rgba(255, 255, 255, 0.6)',
                    'width': '100%',
                    'height': '100vh'
                })
                .hide()
            ;

            // Block body overflow and append overlay
            $('body')
                .css({'overflow': 'hidden'})
                .append($overlay);


            // Set blur effect of page content
            $('.global, .navbar').addClass('blur');

            // Add login form
            if ($('#login-form').length === 0 ) {
                $form = $($('#session_expired_login_form').html());
                $overlay.append($form);
            }

            var formTop = (window.innerHeight - ($('#login-form').height)) / 2;
            $('#login-form').css({'top': formTop + 'px'});

            $form = $('#login-form');
            $overlay.fadeIn(300);
            $form.fadeIn(300);
        };

        /**
         * Load loggin form into modal
         */
        var loadForm = function() {
            if (null === $form) {
                buildLoginForm();
            }
        };

        $('.js-form-alert').hide().text('');

        $('body')
            .on('submit', '.check-auth-login', function(e) {
                e.preventDefault();

                var $form = $(this)
                    ;

                // Avoid double submit
                if ($form.hasClass('submitting')) {
                    return false;
                }

                // Set loading state
                $form.addClass('submitting');
                $form.find('.js-form-loader').show();
                $form.find('#submit').hide();
                $.ajax({
                        url: $form.attr('action'),
                        data: $form.serialize(),
                        type: 'post',
                        dataType: 'json',
                        xhrFields: {
                            withCredentials: true
                        }
                    })
                    .done(function() {
                        _isLogged = true;
                        hideLogForm();
                    })
                    .fail(function(err) {
                        var message = null;
                        if (401 === err.status) {
                            message = Translator.trans('bad_credential');
                        } else {
                            message = Translator.trans('common_error_occurred');
                            console.error(err);
                        }

                        $('.js-form-alert').show().text(message);
                    })
                    .always(function() {
                        // Set default state
                        $form.removeClass('submitting');
                        $form.find('.js-form-loader').hide();
                        $form.find('#submit').show();
                    })
                ;
            })
        ;

        /**
         * Script initialization, launch check function each time
         * defined by _checkDelay var
         */
        var init = function() {
            checkauthInterval = setInterval(checkAuthentification, _checkDelay);
        };

        /**
         * Return public function
         */
        return {
            init: init
        };
    })();


    ///
    // On document ready
    ///
    $(document).ready(function() {
        // If user is authenticated
        if ($('#session_expired_login_form').length > 0) {
            loginChecker.init()
        }
    });

}(jQuery));



/////
// NOTIFICATIONS
/////
(function($){

    // On offer "contact us" click
    $('body').on('click', '.js-notify-upgrade', function(e) {
        e.preventDefault();
        var button = $(this)
            ;

        button.progressButton('loading');
        $.ajax({
                url: Routing.generate('notify_upgrade_your_plan')
            })
            .done(function(){
                button.progressButton('success');
                setTimeout(function(){
                    $.lgwPopup.close();
                }, 3000);
            })
            .fail(function(){
                button.progressButton('error');
                setTimeout(function(){
                    button.progressButton('default');
                }, 2500);
            })
        ;

    });

}(jQuery));
