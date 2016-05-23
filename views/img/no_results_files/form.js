
/**
 * Sets of functions used to manage forms (validation,...)
 */


/**
 * Set error on tag in a tagit component
 * @param tag
 * @param options
 */
function printTagError(tag, options) {

    var defaultOptions = {
        message: null
    };

    //If parameter is the message
    if (typeof options === 'string') {
        var message = options;
        options = {
            message: message
        }
    }

    // Merge default options and parameters
    options = $.extend(defaultOptions, options);


    //First, check if element is a tag in a tagit element
    //If element is invalid, return false
    if (!tag.hasClass('tagit-choice') || !tag.parent().hasClass('tagit')) {
        return false;
    }

    // Set error
    tag.addClass('state error');

    // If message exists, set tooltip
    if (options.message != null) {
        tag.attr('title', options.message).addClass('small-tooltip');
    }

    return true;
}




/**
 * Get parent container and wrapper according to item type and options
 * @param item
 * @param options
 */
function getInputMessageParents(item, options) {

    var isTagIt = item.hasClass('tagit') || item.hasClass('tagit-hidden-field'),
        isChosenSelect = item.hasClass('chosen-select'),
        parentWrapper = null, //Container on which classes "state" and <state type> will be added.
        superContainer //Supercontainer (contain input, parentWrapper, label and alert.
        ;

    //Get parentWrapper div. We will set "state error" class on this element
    if (options.parentWrapper != null) {
        //Case 1 parentWrapper element is specified
        parentWrapper = options.parentWrapper;
        superContainer = parentWrapper;

    } else if (isTagIt) {
        //Case 2 Input is a tagit element
        parentWrapper = item.closest('.tagit-group').find('ul.tagit');

        if (parentWrapper.length == 0) {
            parentWrapper = item.siblings('ul.tagit');
        }

        superContainer = parentWrapper.parent();

    } else if (options.wrap || isChosenSelect) {
        //Case 3 Input must be wrapped and the parentWrapper will be the new wrapper
        if( !item.parent().is('div') || (!item.parent().hasClass('noerror') && !item.parent().hasClass("state")) ){
            //If item isn't already wrapped, wrap the input
            item.wrap( "<div class='state'></div>" );
        }

        parentWrapper = item.parent();

        if (isChosenSelect) {
            superContainer = parentWrapper.parent();
        } else {
            superContainer = parentWrapper;
        }
    } else {
        parentWrapper = item.parent();
        superContainer = parentWrapper;
    }

    return {
        parentWrapper: parentWrapper,
        superContainer: superContainer
    }
}


/***
 * Display input status
 * @param item
 * @param options
 */
function printInputMessage(item, options) {

    if (!options) {
        options = {};
    }

    var defaultOptions = {
        type: "error",
        wrap: true,
        icon: null,
        noticeMessage: '',
        noticeMessageType: 'info',
        message: '',
        parentWrapper: null,
        noticeMessageClose: true,
        setBorderColor: true
    };

    options = $.extend(defaultOptions, options);

    var isTagIt = item.hasClass('tagit') || item.hasClass('tagit-hidden-field'),
        isChosenSelect = item.hasClass('chosen-select'),
        stateClass = "state " + options.type;


    //Get parents
    var parents = getInputMessageParents(item, options);
    var parentWrapper = parents.parentWrapper,
        superContainer = parents.superContainer;

    //Set the class in parentWrapper
    parentWrapper.removeClass('success error warning');
    if (options.setBorderColor) {
        parentWrapper.addClass(stateClass);

        if (isChosenSelect) {
            superContainer.addClass(stateClass);
        }
    } else {
        parentWrapper.addClass("state");
    }


    //Tagit
    if (isTagIt) {
        superContainer.addClass('state');
    }

    //If notice message option defined, append alert block with notice message
    if (options.noticeMessage != null && options.noticeMessage.length > 0) {
        var alertClass = options.noticeMessageType;
        if (superContainer.find('.alert').length == 0) {
            var alert = '<div class="alert alert-' + alertClass + ' margin-top-tiny">';

            if (options.noticeMessageClose) {
                alert += '<a href="#" class="close" data-dismiss="alert" aria-label="close"> </a>';
            }

            alert += options.noticeMessage + '</div>';

            superContainer.append(alert);

        } else {
            superContainer.find('.alert-info').removeClass('alert-danger alert-info alert-success alert-warning')
                .addClass('alert-' + alertClass);
        }
    }


    //Add or change error label
    if (options.message != '') {

        //Add label element if it not already exists
        if (superContainer.find('.form_label').length == 0) {
            item.after('<span class="form_label">' + options.message + '</span>');
        } else {
            superContainer.find('.form_label').text(options.message);
        }

        //Add icon if option is defined
        if (options.icon != null) {

            if (superContainer.find('.form_label').children('i.glyphicon').length == 0) {
                superContainer.find('.form_label').prepend('<i class="glyphicon"></i>');
            }

            superContainer.find('.form_label').children('i.glyphicon').addClass('glyphicon-' + options.icon);
        } else {
            if( superContainer.find('.form_label').children('i.glyphicon').length > 0 ){
                superContainer.find('.form_label').children('i.glyphicon').remove();
            }
        }

    } else {
        //If no message defined, remove label
        if (superContainer.find('.form_label').length > 0) {
            superContainer.find('.form_label').remove();
        }
    }
}



/**
 * Print success message for input given in parameters
 * @param item
 * @param message
 * @param options
 */
function printInputSuccess(item, message, options) {

    if (!options) {
        options = {};
    }

    var defaultOptions = {
        wrap: true,
        noticeMessage: '',
        hasIcon: false,
        parentWrapper: null,
        setBorderColor: true
    };

    //When message is undefined
    message = message || '';

    //Merge default options with parameters
    options = $.extend(defaultOptions, options);


    //Print state
    printInputMessage(item, {
        message: message,
        noticeMessage: options.noticeMessage,
        wrap: options.wrap,
        type: "ok",
        icon: (options.hasIcon) ? "ok" : null,
        parentWrapper: options.parentWrapper,
        setBorderColor: options.setBorderColor
    });
}


/**
 * Print errors message for input given in parameters
 * @param item
 * @param message
 * @param options
 */
function printInputError(item, message, options) {

    if (!options) {
        options = {};
    }

    var defaultOptions = {
        wrap: true,
        noticeMessage: '',
        hasIcon: false,
        parentWrapper: null,
        setBorderColor: true
    };

    //When message is undefined
    message = message || '';

    //Merge default options with parameters
    options = $.extend(defaultOptions, options);


    //Print state
    printInputMessage(item, {
        message: message,
        noticeMessage: options.noticeMessage,
        wrap: options.wrap,
        type: "error",
        icon: (options.hasIcon) ? "remove" : null,
        parentWrapper: options.parentWrapper,
        setBorderColor: options.setBorderColor
    });
}


/**
 * Hide Input message
 * @param item
 * @param options
 */
function hideInputMessage(item, options) {
    if (!options) {
        options = {};
    }

    var defaultOptions = {
        parentWrapper: null
    };

    //Merge default options with parameters
    options = $.extend(defaultOptions, options);

    var isTagIt = item.hasClass('tagit') || item.hasClass('tagit-hidden-field'),
        isChosenSelect = item.hasClass('chosen-select')
        ;

    //Get parents
    var parents = getInputMessageParents(item, options);
    var parentWrapper = parents.parentWrapper,
        superContainer = parents.superContainer;

    if(superContainer.find('.form_label').length > 0){
        superContainer.find('.form_label').remove();
    }

    if(parentWrapper.hasClass('state')){
        parentWrapper.removeClass('state error ok').addClass('noerror');
    }

    if (isTagIt || isChosenSelect) {
        superContainer.removeClass('state');
    }

    if (superContainer.find('.alert-info').length > 0) {
        superContainer.find('.alert-info').remove();
    }
}



/**
 * Hide errors message input
 * @param item
 * @param options
 * @deprecated
 */
function hideInputError(item, options){
    hideInputMessage(item, options);
}

/**
 * Check if value is email
 * @param  string  myVar
 * @return Boolean
 */
function isEmail(myVar){
    var regEmail = new RegExp('^[0-9a-z._-]+@{1}[0-9a-z.-]{2,}[.]{1}[a-z]{2,5}$','i');
    return regEmail.test(myVar);
}


/**
 * Submit form using progress button
 * @param button
 * @param form
 * @returns {boolean}
 */
function submit(button, form) {
    var error = false;
    $(form).find('input:required').each(function(index, element) {
        if ($(element).val().length < 1 ) {
            error = true;
            printInputError($(element), 'Required');
        }
    });
    if (error == true) {
        return false;
    }
    $(button).progressButton('loading');
    setTimeout(function() {
        $(button).progressButton('success');
        setTimeout(function() {
            $(form).submit();
        }, 200);
    }, 2000);
}





/***
 * Widget Form collection field type
 */
(function($){

    // Translations
    $.extend($.validator.messages, {
        required: Translator.trans("form.required_field"),
        remote: Translator.trans("form.fix_field"),
        email: Translator.trans('valid_email_message'),
        url: Translator.trans("valid_url_message"),
        date: Translator.trans("form.valid_date"),
        dateISO: Translator.trans("form.valid_date_iso"),
        number: Translator.trans("form.enter_valid_number"),
        digits: Translator.trans("form.only_digits"),
        creditcard: Translator.trans("form.enter_valid_credit_number"),
        equalTo: Translator.trans("form.enter_name"),
        maxlength:  Translator.trans("form.more_than_zero_characters"),
        minlength: Translator.trans("form.at_least_zero_characters"),
        rangelength: Translator.trans("form.between_zero_one_characters"),
        range: Translator.trans("form.between_zero_one_characters_second"),
        max: Translator.trans("form.lower_equal_zero"),
        min: Translator.trans("form.equal_greater_zero")
    });


    // Validator methods
    $.validator.addMethod("pattern", function(value, element, regexp) {
        // If not enough data
        if (typeof regexp !== 'string' && !$(element).attr('pattern')) {
            return this.optional(element);
        }

        // If no param
        if (!regexp) {
            regexp = $(element).attr('pattern');
        }

        // If flag found
        var flag = "";
        if ('undefined' !== typeof $(element).attr('pattern-flag') && $(element).attr('pattern-flag').length > 0) {
            flag = $(element).attr('pattern-flag');
        }

        var reg = new RegExp(regexp, flag);
        return this.optional(element) || reg.test(value);
    }, Translator.trans("invalid_format"));

    // Add specific rule for class decimal
    $.validator.addClassRules({
        decimal: {
            number: true,
        }
    });

    // Extends plugin to use custom labels
    $.extend($.validator.defaults, {
        errorPlacement: function(error, element) {
            var errorMessage = $(error).text();
            printInputError($(element), errorMessage);
        },
        success: function(label, element) {
            hideInputMessage($(element));
        },
        invalidHandler: function(form, validator) {
            if (!validator.numberOfInvalids()) {
                return;
            }

            var item = $(validator.errorList[0].element).closest('.form-group'),
                topAdd = 0
            ;

            if (item) {
                topAdd = item.height() + 20;
            } else {
                topAdd = 100;
            }

            $('html, body').animate({
                scrollTop: $(validator.errorList[0].element).offset().top - topAdd
            }, 300);
        }
    });

    /**
     * Setup some events when document is ready
     */
    $(document).ready(function() {

        // Transform each "," to "." on decimal field
        $('.decimal').each(function(index, input) {
            $(input).keyup(function(e) {
                var number = $(this).val();
                if (number.search(',')) {
                    $(this).val(number.replace(',', '.'));
                }
            });
        });

        $(".js-validate-form").validate({
            onkeyup: false
        });
    });

    /**
     * Init form collection
     * @param $field
     */
    var initFormCollection = function($field){

        //Seq for indexing new blocks in each section
        var $formItemSequence = $field.children('.form-group').length;

        /**
         * Refresh item labels iteration (to display a list of #1 #2...)
         */
        function reorderItem() {
            var index = 1;
            $field.children('.form-group').each(function() {
                var labelSpan = $(this).find('.js-form-collection-item-label');
                if (labelSpan) {
                    labelSpan.text(index);
                }
                index++;
            });
        }

        /**
         * Delete item
         * @param event
         */
        function deleteItem(event) {
            event.preventDefault();

            var $this = $(event.currentTarget);
            $this.parents('.form-group').remove();
            reorderItem();
        }

        /**
         * Add item
         * @param event
         */
        function addItem(event) {
            event.preventDefault();

            var prototype = $field.attr('data-prototype');
            var indexLabel = $field.children('.form-group').length + 1;
            var index = $formItemSequence++;

            //prototype
            prototype = prototype.replace(/__name__label__/g, indexLabel);
            prototype = prototype.replace(/__name__/g, index);
            prototype = $(prototype);
            $field.append(prototype);

            prototype.children('label.control-label').children('a.js-form-collection-item-delete').on('click', deleteItem);
        }

        /**
         * Swap 2 items with animation.
         * @param item1 Item to place in first position
         * @param item2 Item to place in second position
         * @param callback
         */
        function swapItems(item1, item2, callback) {

            //Default callback
            if (!callback) {
                callback = function(){};
            }

            var item1H = item1.height();
            var item2H = item2.height();
            var item1OffsetTop = item2.offset().top - 30;

            //Fake swap with animtation
            item1.animate({
                top: -item2H
            }, 200, function(){

                //Stop all animations
                item2.stop().css('top', '');
                item1.stop().css('top', '');

                //Proceed to real swap
                item1.detach();
                item1.insertBefore(item2);

                //Callback
                callback();
            });

            item2.animate({
                top: item1H
            }, 200);
        }

        /**
         * Move item upwards
         * @param event
         */
        function moveItemUp(event) {
            event.preventDefault();

            var $this = $(event.currentTarget);
            var div1 = $this.parents('.form-group');
            var div2 = div1.prev('.form-group');

            if (div2.length > 0) {
                swapItems(div1, div2, function(){
                    reorderItem();
                });
            }
        }

        /**
         * Move item upwards
         * @param event
         */
        function moveItemDown(event) {
            event.preventDefault();

            var $this = $(event.currentTarget);
            var current = $this.parents('.form-group');
            var next = current.next('.form-group');

            if (next.length > 0) {
                swapItems(next, current, function(){
                    reorderItem();
                });
            }
        }

        /**
         * Add item button
         */
        $field.siblings('a.js-form-collection-add').on('click', addItem);

        /**
         * Delete item button
         */
        $field.find('.form-group').each(function(){
            var deleteButton = $(this).find('a.js-form-collection-item-delete');
            var moveUpButton = $(this).find('a.js-form-collection-item-moveup');
            var moveDownButton = $(this).find('a.js-form-collection-item-movedown');

            deleteButton.on('click', deleteItem);
            moveUpButton.on('click', moveItemUp);
            moveDownButton.on('click', moveItemDown);
        });

        /**
         * Sortable
         */
        $field.sortable({
            stop: function(event, ui) {
                if(ui.position != ui.originalPosition) {
                    reorderItem(ui.item.parent());
                }
            }
        });
    };

    /**
     * Templating js classes
     * .js-form-collection
     *      .js-form-collection-item-label
     *      .js-form-collection-item-delete
     * .js-form-collection-add
     */
    if($('.js-form-collection').length > 0) {
        $('.js-form-collection').each(function(){
            initFormCollection($(this));
        });
    }

}(jQuery));
