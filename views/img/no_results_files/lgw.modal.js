////
// Lengow Modal
//
(function ($) {

    "use strict";

    /*
     TODO :
     Generate auto ID if empty
     */

    var pageTitle = $(document).prop('title');
    var $body = $('body');
    var modals = [];

    /**
     * Close modal
     * @returns {boolean}
     */
    var closeModal = function () {
        $.modal('kill', {
            id: history.state.id,
            historyBack: true
        });
        return false;
    };


    // Modal events
    $.modalEvents = {
        LOADING: "lgwmodal:loading",
        LOADED: "lgwmodal:loaded",
        DONE: "lgwmodal:done",
        KILL: "lgwmodal:kill"
    };

    // Settings
    var settings = {
        id: 'modal',
        title: $(document).prop('title'),
        url: '',
        data: '',
        method: 'POST',
        class: '',
        createIn: 'body',
        loadingMessage: 'Loading...',
        style: '',
        queue: null,
        historyBack: false,
        content: null
    };

    // Method for change modal state
    var changeLoadStateMethod = function (action) {
        // If not modal found for this id, get the first modal
        if (jQuery.inArray(settings.id, modals) == -1) {
            if (modals.length > 0) {
                settings.id = modals[0]
            } else {
                return false;
            }
        }

        // Find modal
        var $modal = $('[data-modal-id="' + settings.id + '"]');
        var ajaxLoading = $modal.find('.ajax-loading');

        if (action == "remove") {
            // Set state "loaded" and trigger event to notify modal is loaded
            ajaxLoading.css('display', 'none');
            setTimeout(function () {
                $modal.addClass('loaded');
            }, 100);
            $modal.trigger($.modalEvents.LOADED);
            $(document.body).off($.modalEvents.LOADED + '.' + settings.id);
        } else if (action == "add") {
            // Set state "loading" and trigger event to notify modal is loading
            ajaxLoading.css('display', 'block');
            $modal.removeClass('loaded');

            $modal.trigger($.modalEvents.LOADING);
            $(document.body).off($.modalEvents.LOADING + '.' + settings.id);
        }
    };


    /// Modal
    $.modal = function (action, options) {
        var ajaxCall
            ;
        settings = $.extend(settings, options);


        //////////////////////////
        // CREATE MODAL

        if (action === "create" && jQuery.inArray(settings.id, modals) == -1) {
            modals.push(settings.id);
            // Handle browser history
            if (history.state === null || history.state === undefined) {
                history.replaceState({'id': '', 'next': settings.id}, 'modal', '#');
            } else if (history.state.id != settings.id) {
                var state = history.state;
                state.next = settings.id;
                history.replaceState(state, 'modal', "#" + history.state.id);
            }
            if (history.state.id != settings.id) {
                history.pushState({'id': settings.id, 'previous': history.state.id}, 'modal', '#' + settings.id);
            }

            // Page title
            $(document).prop('title', settings.title + ' | Lengow');

            // Create HTML
            $(settings.createIn).append('<div class="lgw-modal ' + settings.class + '" data-modal-id="' + settings.id + '" style="' + settings.style + '"><a href="#" class="modal-close js-close-this-modal"></a><div class="modal-inner"></div><div class="ajax-loading"><div class="double-bounce1"></div><div class="double-bounce2"></div><h3 class="title-thin">' + settings.loadingMessage + '</h3></div></div>');

            // Open modal
            setTimeout(function () {
                $('[data-modal-id="' + settings.id + '"]').addClass('opening');
            }, 50);

            // Body unscrollable
            setTimeout(function () {
                $('html').addClass('unscrollable');
            }, 150);

            /// Modal object

            var $modal = $('[data-modal-id="' + settings.id + '"]');

            /// Append content on modal
            var appendContent = function (html) {
                //$modal = $('[data-modal-id="' + settings.id + '"]');
                $modal.find('.ajax-loading').css('display', 'none');
                $modal.find('.modal-inner').html(html);
                setTimeout(function () {
                    $modal.addClass('loaded');
                }, 100);

                // EVENT "CREATED"
                $modal.trigger($.modalEvents.DONE);
                $(document.body).off($.modalEvents.DONE + '.' + settings.id);

                // EVENT "LOADED"
                $modal.trigger($.modalEvents.LOADED);
                $(document.body).off($.modalEvents.LOADED + '.' + settings.id);
            };

            if (null !== settings.content) {
                //If content provided directly (no ajax call needed)
                appendContent(settings.content);
            } else {
                // If ajax call needed
                if (null === settings.queue) {
                    // Load content
                    ajaxCall = $.ajax({
                        method: settings.method,
                        url: settings.url,
                        data: settings.data
                    });
                } else {
                    // Load content in a queue
                    ajaxCall = $.ajaxq(settings.queue, {
                        method: settings.method,
                        url: settings.url,
                        data: settings.data
                    });
                }

                //EVENT loading
                $modal.trigger($.modalEvents.LOADING);
                $(document.body).off($.modalEvents.LOADING + '.' + settings.id);

                ajaxCall
                    .done(appendContent)
                    .fail(function () {
                        $modal.find('.ajax-loading').html('<h3 class="title-thin">' + Translator.trans("modal.something_bad_occurred") + '</h3><a href="#" class="btn btn-flat js-close-this-modal">' + Translator.trans('modal_button_close') + '</a>');
                    });
            }
        }

        //////////////////////////
        // KILL MODAL

        else if (action === "kill") {
            // Find modal
            var $modal = $('[data-modal-id="' + settings.id + '"]');

            // Close
            $modal.removeClass('opening');
            $modal.find('.footer').hide(150);

            // Prevent scroll top;
            var scr = document.body.scrollTop;
            document.body.scrollTop = scr;

            // Body scrollable
            if (modals.length <= 1) {
                $('html').removeClass('unscrollable');
            }

            // Remove
            setTimeout(function () {
                $modal.remove();
            }, 350);
            $(document).prop('title', pageTitle);
            modals.splice($.inArray(settings.id, modals), 1);

            if (settings.historyBack) {
                history.back();
            }

            // trigger an event on modal kill
            $modal.trigger($.modalEvents.KILL);
            $(document.body).off($.modalEvents.KILL + '.' + settings.id);

        }

        /// KILL ALL MODALS
        else if (action === "killall") {
            if ($('[data-modal-id]').length == 0) {
                return false;
            }

            $('[data-modal-id]').each(function () {
                var id = $(this).data('modal-id');
                $.modal('kill', {
                    id: id
                });
            });
        }

        //////////////////////////
        // Load new content in modal

        else if (action == 'set_load_state') {
            changeLoadStateMethod('add');
        }

        else if (action == 'remove_load_state') {
            changeLoadStateMethod('remove');
        }

        return false;
    };


    $body.on('click', '[data-modal-close]', closeModal);
    $body.on('click', '.js-close-this-modal', closeModal);

    // Back to previous > Hide title
    $body.on('mouseenter', '.modal-back', function(){
        $('.modal-head-title').hide(0);
    });
    $body.on('mouseleave', '.modal-back', function(){
        $('.modal-head-title').fadeIn(100);
    });

    // Close modal?

    $body.on('click', '.js-footer-confirm-close', function(){
        $('.footer-confirm-close').addClass('active');
        return false;
    });
    $body.on('click', function(){
        $('.footer-confirm-close').removeClass('active');
    });

    // Nooo

    $body.on('click', '.js-close-cancel', function(){
        $('.footer-confirm-close').removeClass('active');
        return false;
    });

    /**
     * on hashchange, update history
     */
    $(window).bind('hashchange', function (e) {
        if (history.state !== null) {
            if ($.inArray(history.state.next, modals) !== -1) {
                $.modal('kill', {
                    id: history.state.next
                });
            } else {
                var $elem = $('[data-hash="' + history.state.id + '"]');
                if ($elem.length && $.inArray(history.state.id, modals) == -1) {
                    $elem.click();
                }
            }
            e.stopPropagation();
        }
    });

    /**
     * Load modal if we find hash into url
     */
    $(window).load(function () {
        if (window.location.hash && $('.lgw-modal').length == 0) {
            var hash = window.location.hash.replace('#', '');
            if (hash !== undefined) {
                var $elem = $('[data-hash="' + hash + '"]');
                // If an item match current hash, push it to history and open modal, else remove
                if ($elem.length) {
                    history.pushState({'id': hash}, 'modal', '#' + hash);
                    $elem.click();
                }
            }
        }
    });

}(jQuery));


////
// Lengow Alert Popup
//
(function ($) {
    "use strict";

    /**
     *
     * @param template Alert DOM
     * @param options
     */
    var attachAlert = function (template, options) {

        var $body = $('body'),
            overlay = $body.find('.surround');
        if (overlay.length == 0) {
            $body.append('<div class="surround"></div>');
            overlay = $body.find('.surround');
            if (null !== options.overlayClass) {
                overlay.addClass(options.overlayClass);
            }
        }

        // Add/Replace template
        overlay.html('<div class="container"></div>');
        overlay.children('.container').append(template);

        // Add events
        if (options.closeButton != '') {
            overlay.one('click', '.' + options.closeButton, function (e) {
                e.preventDefault();
                $.lgwPopup.close();
            });
        }
    };


    // Simple popup manager.
    // Open or close popup
    // On popup creation, if popup already exists, replace old popup
    // Provide promises done(), fail() and complete()

    $.lgwPopup = {
        open: function (options) {

            var def = $.Deferred();

            // Default
            var defaultOptions = {
                overlayClass: 'load',
                template: null,
                templateSrc: null,
                templateSrcData: {},
                closeButton: "js-popup-close"
            };

            options = $.extend(defaultOptions, options);

            // Checking some args
            if (null === options.templateSrc && null === options.template) {
                throw Error('lgwPopup expects at least one valid options from "template" and "templateSrc".');
            }

            // if template send directly in parameters
            if (null !== options.template && options.template.length > 0) {
                attachAlert(options.template, options);
                def.resolve();
            } else {
                $.ajax({
                        url: options.templateSrc,
                        data: options.templateSrcData,
                        dataType: 'html'
                    })
                    .done(function (data) {
                        attachAlert($(data), options);
                        def.resolve();
                    })
                    .fail(function () {
                        console.error('An error occurred');
                        def.reject();
                    });
            }

            return def.promise();
        },
        close: function () {
            var $body = $('body'),
                overlay = $body.find('.surround');

            if (overlay.length > 0) {
                overlay.remove();
            }
        }
    };
}(jQuery));
