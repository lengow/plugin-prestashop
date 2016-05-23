//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// FORM ELEMENTS BEHAVIOUR

// Toggle Checkbox
$('body').on('change', '.checkbox, .checkbox-inline, .switch', function(event) {
    var check = $(this);
    var checked = check.find('input').prop('checked');

    check.toggleClass('checked');
    //Select / deselect all
    if( check.hasClass('select-all') ){
      var name = check.find('input').attr('name');
      if(checked == true){
        $('input[name="'+name+'"]').closest('.checkbox').addClass('checked');
      }
      else{
        $('input[name="'+name+'"]').closest('.checkbox').removeClass('checked');
      }
      $('input[name="'+name+'"]').prop('checked', checked);
    }
});

$('body').on('change', '.switch-toggle-input', function(event) {
  var id = $(this).attr('data-switch');
  var target = $('div[data-switch-target="'+id+'"]');
  target.animate({ height: 'toggle', opacity: 'toggle' }, 100);
});


// Link toggle
$('body').on('click', '[data-link-toggle]', function(){
  var id = $(this).data('link-toggle');
  var target = $('[data-link-target="'+id+'"]');
  target.fadeToggle(100);
  return false;
});
// Se focus in another item
$('body').on('click', '.js-focus-in', function(event){
  var id = $(this).data('target');
  var target = $('#'+id);
  target.focus();
});

// Search reset cross
$('body').on('input', 'div.search input', function(event){
  var btn = $(this).siblings('.btn-deny');
  if(this.value){
    btn.addClass('active');
  } else {
    btn.removeClass('active');
  }
}).on('touchstart click', '.btn-deny', function(event){
  // On deny cross press trigger some events
  $(this).siblings('input').val('')
      .trigger('input')
      .trigger("keyup")
      .trigger('blur')
      .trigger('keydown')
      .trigger('change');
});

// Toggle btn-group radio
// e.g. Permission tiles, btn-groups...
$('body').on('click', '.btn-radios a', function(event) {
  var btn = $(this);
  var radio = btn.find('input[type="radio"]');
  var name = radio.attr('name');
  $('.btn-radios input[name="'+name+'"]').closest('a').removeClass('active');
  btn.addClass('active');
  radio.prop('checked', true).trigger("change");
  return false;
});

// Toggle btn-group checkbox
$('body').on('click', '.btn-checkbox a', function(event) {
  var btn = $(this);
  var radio = btn.find('input[type="checkbox"]');
  var name = radio.attr('name');
  btn.toggleClass('active');
  radio.prop("checked", !radio.prop("checked")).trigger("change");
  return false;
});



// Toggle Radios
$('body').on('change', '.radio, .radio-inline', function() {
  var name = $(this).find('input').attr('name');
  $('.radio input[name="' + name + '"]').closest('.radio').removeClass('checked');
  $(this).addClass('checked');
});

// Toogle alerts
$('.alert .toggle').click(function() {
  $(this).closest('li').toggleClass('open');
  return false;
});

// Don't close dropdown on inside click
$('body').on( "click", ".dontClose ul", function(e) {
  e.stopPropagation();
});

// Custom select options
$('.selectpicker').selectpicker({
  container: 'body'
});


// range-picker
function rangePicker(dom){
  var dateToday = new Date();
  dom.find( "#from" ).datepicker({
    altField: "#altFrom",
    altFormat: "yy-mm-dd",
    defaultDate: "+1w",
    minDate: dateToday,
    showAnim: '',
    option: $.datepicker.regional[$('html').attr('lang')],
    onClose: function( selectedDate ) {
      dom.find( "#to" ).datepicker( "option", "minDate", selectedDate );
    },
    onSelect: function(selectedDate) {
      $('#to').datepicker('option', 'minDate', selectedDate);
      setTimeout(function() { $('#to').focus(); }, 0);
    }
  });
  dom.find( "#to" ).datepicker({
    altField: "#altTo",
    altFormat: "yy-mm-dd",
    defaultDate: "+1w",
    showAnim: '',
    option: $.datepicker.regional[$('html').attr('lang')],
    onClose: function( selectedDate ) {
      dom.find( "#from" ).datepicker( "option", "maxDate", selectedDate );
    }
  });
}

$('body').on( "click", ".toggle-picker .toggle-link", function() {
  var toggle = $(this).closest('.toggle-picker');
  toggle.toggleClass('active');

  if( toggle.hasClass('active') ){
    $('#from').focus();
  }
  return false;
});


// Accordion Toggle
$('.accordion .acc-toggle').click(function() {
  var parent = $(this).closest('.accordion');
  var name = parent.attr('name');
  var height = parent.find('.acc-content>div').outerHeight();
  // Close all
  $('.accordion[name="'+name+'"]').removeClass('open');
  $('.accordion[name="'+name+'"] .acc-content').height(0);
  // Open clicked
  parent.addClass('open');
  parent.find('.acc-content').height(height);
  return false;
});

// Select option (chosen)
$('.chosen-select').chosen({
  width:'100%',
  search_contains: true
});
// Select option (chosen > filters)
$('.chosen-select.no-search').chosen({
    width:'100%',
    search_contains: false
});
// Preselect chosen-picker
$('.chosen-select-preselect').chosen({
    search_contains: true
});
// Select option (chosen > filters)
$('.chosen-filter').chosen({
  search_contains: true
});




$('.chosen-select').on('chosen:showing_dropdown', function(evt, params) {
  var dropHeight = params.chosen.dropdown.height();
  var selectPos = params.chosen.container.offset().top;
  var bodyHeight = $('body').outerHeight();
  var toBottom = bodyHeight - selectPos - dropHeight;
  if( toBottom < 100 ){
    $('body').css('padding-bottom', dropHeight);
  }
});

// Select option inside Modale

$('#modal').on('.chosen-select chosen:showing_dropdown', function(evt, params) {
  var dropHeight = params.chosen.dropdown.height();
  var selectPos = params.chosen.container.offset().top;
  var bodyHeight = $('#modal').outerHeight();
  var toBottom = bodyHeight - selectPos - dropHeight;
  if( toBottom < 100 ){
    $('#modal.with-footer .scrollable .container').css('padding-bottom', dropHeight - toBottom - 100);
  }
  return false;
});

// Tooltip icon button actions
$("body").on("mouseenter", ".btn-icon-tooltip-flag", function() {
  $(this).closest('.btn-icon-tooltip').addClass('over');
});
$("body").on("mouseleave", ".btn-icon-tooltip-flag", function() {
  $(this).closest('.btn-icon-tooltip').removeClass('over');
});

// Home button lantency
$("body").on("click", ".home-btn-latency", function() {
  var href = $(this).attr('href');
  var btn = $(this);
  btn.addClass('saving');
  btn.find('.step0').css('display','none');
  btn.find('.step1').fadeIn(250);
  setTimeout(function() {
    btn.addClass('saved');
    btn.find('.step1').css('display','none');
    btn.find('.step2').fadeIn(250);
  }, 600);
  setTimeout(function() {
    btn.addClass('latency-out');
  }, 1000);
  setTimeout(function() {
    btn.removeClass('saving saved latency-out');
    btn.find('.step0').fadeIn(250);
    btn.find('.step1, .step2').css('display','none');
    window.location.href = href;
  }, 1250);
  return false;
});

// Tooltips

$(document.body).tooltip({
  container: 'body',
  selector: '[data-toggle="tooltip-focus"]',
  trigger: 'focus',
  animation: false,
  template: '<div class="tooltip lg"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
});


$('[data-toggle="tooltip"]').tooltip({container: 'body', animation: false});

$('[data-toggle="tooltip-sm"]').tooltip({
  container: 'body',
  animation: false,
  template: '<div class="tooltip sm"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
});

$('[data-toggle="tooltip-lg"]').tooltip({
  container: 'body',
  animation: false,
  template: '<div class="tooltip lg"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
});

$('body').tooltip({
    selector: '.small-tooltip:not(.opened)',
    container: 'body',
    animation: false,
    template: '<div class="tooltip sm"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
});

$("body").on("click", ".small-tooltip", function () {
    $(this).addClass('opened');
    $('.tooltip.sm').tooltip('destroy');
});

$("body").on("mouseleave", ".small-tooltip", function () {
    $(this).removeClass('opened');
});

// Tagit

$(".tagit").each(function(){
    var $tagit = $(this);

    // Default params
    var params = {};
    params.onTagExists = function(event, ui) {
        ui.existingTag.addClass('highlight');
        setTimeout(function(){
            ui.existingTag.removeClass('highlight');
        }, 200);
        return false;
    };

    // If choices
    var choices = $tagit.data('choices');
    if (typeof choices !== 'undefined' && choices.length > 0) {
        params.autocomplete = {delay: 0, minLength: 0, source: choices}
        params.showAutocompleteOnFocus = true;

        if (typeof $tagit.data('allow-all-tag') === 'undefined') {
            params.beforeTagAdded = function(event, ui) {
                if ($.inArray(ui.tagLabel, choices) === -1) {
                    return false;
                }
            };
        }
    }

    // INIT tagit
    $tagit.tagit(params);
});



$('.tagit-group .dropdown-menu a').click(function(){
    var tagit = $(this).closest('.tagit-group').find('input.tagit');

    tagit.tagit('createTag', $(this).text() );
    return false;
});

$(document).ready(function(){
    //Checkbox synchronization (on page refresh)
    $('.checkbox, .checkbox-inline, .switch').each(function(){
        var checkbox = $(this).find('input[type="checkbox"]');
        // If checkbox state != checkbox wrapper state, trigger event "change" to checkbox to synchronize both states
        if (checkbox.length > 0) {
            if (checkbox.is(':checked') != $(this).hasClass('checked')) {
                checkbox.trigger('change');
            }
        }
    });
});



//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// MULTIBOX CONTENT
// e.g "Find & replace"

$('body').on( "click", ".js-multi-box", function() {
  var href = $(this).attr('href');
  var content = $(href);
  var name = content.attr('data-multibox');
  $('[data-multibox="'+name+'"]')
    .css('display', 'none')
    .removeClass('load-multi-box show-multi-box');
  content
    .css('display', 'block')
    .addClass('load-multi-box');
  setTimeout(function() {
    content.addClass('show-multi-box');
  }, 50);
  return false;
});


//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// DOCK FILTERS ON SCROLL

if( $('.dockable-box').length > 0 ){
  var scroll = $(window).scrollTop();
  isScrolled(scroll);
  $(window).scroll(function() {
    var scroll = $(window).scrollTop();
    isScrolled(scroll);
  });
}

function isScrolled(scroll) {
  var navtop = 0;
  if($('.global').hasClass('nav-top')){
    navtop = 64;
  }
  if (scroll > $('.dockable-box').offset().top - navtop ) {
    $('body').addClass('docked');
  } else {
    $('body').removeClass('docked');
  }
}


// Vertical navbar pos

if( $('.container-nav').length > 0 ){
  var scroll = $(window).scrollTop();
  navScroll(scroll);
  $(window).scroll(function() {
    var scroll = $(window).scrollTop();
    navScroll(scroll);
  });
  $( window ).resize(function() {
    var scroll = $(window).scrollTop();
    navScroll(scroll);
  });
}


// Calculate lateral nav position

function navScroll(scroll) {
  var $nav = $('.container-nav');
  var $container = $('.container-nav-spacing');
  var $footer = $('.footer-standard');
  var h = $nav.height();
  var H = $(window).height();
  var marginBottom = 60;
  var offset = $container.offset().top;

  if( $container.height() < h ){
    $container.css('min-height', h)
  }

  if( h + marginBottom + offset < H){
    if(h + offset > $footer.offset().top - scroll - marginBottom){
      $nav.removeClass('fixed fixed-bottom');
      $nav.addClass('bottom');
    }
    else{
      $nav.removeClass('bottom fixed-bottom');
      $nav.addClass('fixed');
    }
  }
  else if( h+offset-scroll < H - marginBottom){
    if( H + scroll > $footer.offset().top ){
      $nav.removeClass('fixed fixed-bottom');
      $nav.addClass('bottom');
    }
    else{
      $nav.removeClass('fixed bottom');
      $nav.addClass('fixed-bottom');
    }
  }
  else{
    $nav.removeClass('bottom fixed-bottom fixed');
  }
}


//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// MODAL BEHAVIOUR

function modalBox() {
  $('#modal').addClass('opening');
  setTimeout(function() {
    $("#modal").addClass('open');
    $('html').addClass('unscrollable');
    $('.scrollable-section').scrollTop(0);
  }, 150);
  return false;
}

// Modal close
function closeModal() {
  $('html').removeClass('unscrollable');
  $('#modal').removeClass('opening');
  $('.modal-info').removeClass('lightgrey');

  setTimeout(function() {
      $('#modal').removeClass('loaded open');
  }, 100);
  setTimeout(function() {
      $('#modal').html('<div class="ajax-loading"><div class="double-bounce1"></div><div class="double-bounce2"></div></div>');
  }, 500);
}
$('#modal').on('click', '#modal-close, .close-modal', function() {
    closeModal();
    return false;
});

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// JQUERY UI TABS

$( ".nav-tabs" ).tabs();

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// JQUERY UI TABS

$('.js-accordion').accordion({
    animate:        100,
    collapsible:    true,
    heightStyle:    "content",
});

//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// CUSTOM DROPDOWN

$('body').on('click', '[data-toggle="dropdown-custom"]', function(){
  var btn = $(this);
  var id = btn.attr('id');
  var dropdown = btn.closest('.dropdown');

  var isOpen = dropdown.hasClass('open');
  $('.dropdown-custom').removeClass('open');
  dropdown.toggleClass('open');

  //Send events if state has changed
  if (dropdown.hasClass('open') !== isOpen) {
    dropdown.trigger('dropdown:toggle');

    if (dropdown.hasClass('open')) {
      dropdown.trigger('dropdown:open');
    } else {
      dropdown.trigger('dropdown:close');
    }
  }
});

$('body').on('click', function (e) {
  if (!$('.dropdown-custom').is(e.target) && $('.dropdown-custom').has(e.target).length === 0 && $('.open').has(e.target).length === 0) {
    $('.dropdown-custom').each(function() {
        var isOpen = $(this).hasClass('open');
      $('.dropdown-custom').removeClass('open');

      //trigger "Toogle" event only if dropdown was open
      if (isOpen) {
        $('.dropdown-custom').trigger('dropdown:toggle');
        $('.dropdown-custom').trigger('dropdown:close');
      }
    });
  }
});

$('body').on('click', '.dropdown-custom.open .dropdown-toggle', function (){
  var isOpen = $(this).closest('.dropdown-custom').hasClass('open');
  $(this).closest('.dropdown-custom').removeClass('open');

  if (isOpen) {
    $(this).closest('.dropdown-custom').trigger('dropdown:toggle');
    $(this).closest('.dropdown-custom').trigger('dropdown:close');
  }
});


//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// EDIT TITLE

$('body').on('click', '.edit-title', function() {
  $(this).prop('contenteditable',true);
  $(this).focus();
  return false;
});

$('body').on('keydown', '.edit-title', function(e) {
  if (e.keyCode == 13) {
    $(this).prop('contenteditable',false);
    $(this).blur();
    e.preventDefault();
  }
});

$('body').on('blur', '.edit-title', function(e) {
  var dom = $(this);
  var val = dom.text();

  if(val.length < 1){
    val = "Untitled";
  }
  dom.prop('contenteditable', false);
  dom.html('Saving...');
  setTimeout(function(){
    dom.text(val);
    document.title = 'Lengow | ' + val;
  }, 400);
});


//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// Flicker effect preview products

var flickering = setInterval(function(){
    $('.flicker').toggleClass('flickering');
}, 600);


//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// SEE MORE

$('body').on('click', '.js-seemore', function(){
  var $click = $(this);
  var key = $click.data('seemore');
  $click.closest('span').css('display','none');
  $('[data-seemore-target="'+key+'"]').removeAttr('hidden');
  return false;
});


//////////////////////////////////////////////////////
//////////////////////////////////////////////////////
// ALERTS

(function ($) {
    $.alert = function (options) {

        // Default settings
        var settings = $.extend({
            type: 'alert-info',
            message: '',
            parent: '.global',
            close: true
        }, options);

        // Create alert
        var message = $('<div>')
            .addClass('alert '+settings.type)
            .append(settings.message);

        // Close button
        if(settings.close == true){
            message.append('<a href="#" class="close" data-dismiss="alert" aria-label="close"></a>')
        }

        // Write alert
        $(settings.parent).prepend(message);

    }
}(jQuery));

$('body').on('click', '.alert .close', function(){
    $(this).closest('.alert').animate({
       height: 0,
       opacity: 0,
       margin: 0,
       padding: 0
    }, 150, function(){
       $(this).hide();
    });
    return false;
});


////////////////////////////////////////////////////////
// TABLE ACCORDION

$('body').on('click', '.table-accordion', function(){
    $('.table-accordion').css('display','none');
    $('.table-toggle').css('display','table-cell');
});
$('body').on('mouseenter', '.table-accordion', function(){
    $('.table-accordion').css('opacity',.5)
});
$('body').on('mouseleave', '.table-accordion', function(){
    $('.table-accordion').css('opacity',1)
});

////////////////////////////////////////////////////////
// BUTTON PROGRESSION

$(document).ready(function(){

    /**
     * Progress button
     * @param arg
     * @returns {*}
     */
    $.fn.progressButton = function(arg) {

        var parameters = {
            messages: {
                init: Translator.trans('button.save_changes'),
                loading: Translator.trans('button.saving') + "...",
                error: Translator.trans('button.error'),
                success: Translator.trans('button.success')
            },
            containerClass: {
                all: '',
                default: '',
                loading: '',
                error: 'btn-red',
                success: 'btn-green',
            },
        };


        /**
         * Methods for progress button
         * @type {{init: methods.init, toState: methods.toState}}
         */
        /**
         * Methods for progress button
         * @type {{init: methods.init, toState: methods.toState}}
         */
        var methods = {
            init : function(options) {

                // Construct options
                if (typeof options === "undefined") {
                    options = {};
                }

                // Bind button text/messages to default options
                // Bind default message
                if ($(this).find('.btn-step.default').length > 0) {
                    parameters.messages.init = $(this).find('.btn-step.default').text();
                }

                // Bind loading message
                if ($(this).find('.btn-step.loading').length > 0) {
                    parameters.messages.loading = $(this).find('.btn-step.loading').text();
                }

                // Bind success and error message
                if ($(this).find('.btn-step.done').length > 0) {
                    if (typeof $(this).find('.btn-step.done').data('success') !== 'undefined') {
                        parameters.messages.success = $(this).find('.btn-step.done').data('success');
                    }

                    if (typeof $(this).find('.btn-step.done').data('error') !== 'undefined') {
                        parameters.messages.error = $(this).find('.btn-step.done').data('error');
                    }
                }

                // Construct option and set it on object data
                options = $.extend(true, parameters, options);
                $(this).attr('data-options', JSON.stringify(options));

                return $(this);
            },
            toState : function(state, text) {
                var btnClass = state,
                    inner = null,
                    $this = $(this);

                if (!$this.data('options')) {
                    // INIT
                    $this.progressButton();
                }

                //Init button state
                $this.removeClass('loading error success');

                var options = $this.data('options') || {};
                var containerClasses = options.containerClass || {}

                $.each(containerClasses, function(key, value){
                    $this.removeClass(value);
                });

                $this.add(containerClasses.all);

                switch (state) {
                    case "default":
                        btnClass += " " + containerClasses.default;
                        inner = $this.find('.btn-step.default');
                        break;
                    case "loading":
                        btnClass += " " + containerClasses.loading;
                        inner = $this.find('.btn-step.loading');
                        break;
                    case "success":
                        inner = $this.find('.btn-step.done');
                        btnClass += " " + containerClasses.success;
                        break;
                    case "error":
                        inner = $this.find('.btn-step.done');
                        btnClass += " " + containerClasses.error;
                        break;
                    default:
                        return $this;
                        break;
                }

                $this.addClass(btnClass);


                if (typeof text !== 'undefined') {
                    inner.text(text);
                } else if (typeof inner.data(state) !== 'undefined') {
                    inner.text(inner.data(state));
                }

                return $this;
            },
            default: function(text) {
                $(this).progressButton('toState', "default", text);
            },
            loading: function(text) {
                $(this).progressButton('toState', "loading", text);
            },
            success: function(text) {
                $(this).progressButton('toState', "success", text);
            },
            error: function(text) {
                $(this).progressButton('toState', "error", text);
            }
        };


        if (methods[arg]) {
            var argMethod = arguments;
            return this.each(function(){
                methods[arg].apply(this, Array.prototype.slice.call( argMethod, 1 ));
            });
        }  else if ( typeof arg === 'object' || ! arg ) {
            var argMethod = arguments;
            return this.each(function(){
                return methods.init.apply( this, argMethod);
            });
        } else {
            $.error( 'Method ' +  arg + ' does not exist on jQuery.tooltip' );
        }
    };

    //Progress button
    if ($('.btn-progression[data-progress-button]').length > 0) {
        $('.btn-progression[data-progress-button]').progressButton();
    }

    //Submit form
    if ($('.btn-progression[data-progress-submit-form]').length > 0) {

        // Set progress button event and set simple click event to fake submitting form
        $('.btn-progression[data-progress-submit-form]')
            .progressButton()
            .on('click', function(e){
                if ($(this).closest('form').length > 0) {
                    e.preventDefault();
                    var $button = $(this),
                        $form = $button.closest('form')
                    ;

                    $button.progressButton('loading');
                    setTimeout(function(){
                        $button.progressButton('success');
                        $form.submit();
                    }, 2500);
                }
            });
        ;


    }

});

////////////////////////////////////////////////////////
// DISABLE FOOTER BUTTON
$('.footer-btn-main').click(function(){
    var $button = $(this);
    $button.addClass('btn-waiting');
    setTimeout(function() {
        $button.removeClass('btn-waiting');
    }, 2500);
});



////////////////////////////////////////////////////////
// NOTIFY BUTTON

$('a.notify-button').on('click',function(){
  $('span.notify-total').addClass('hidden');
});

////////////////////////////////////////////////////////
// PENDING ORDERS

jQuery(function($){
    'use strict';
    // Fill Pending orders badge
    var App = {
        init: function() {
            this.$sales = $('#item-sales');
            this.fillOrderBadge();
        },
        fillOrderBadge: function() {
            if (this.$sales.length) {
                var $pendingOrders = $.ajax({
                    url: Routing.generate('lengow_dashboard_pending_orders')
                }).success(function (data) {
                    if (data > 0) {
                        var countOrder = (data > 100) ? '+100' : data;
                        App.$sales.find('a').append('<span class="badge">' + countOrder + '</span>');
                    }
                });
            }
        }
    };
    App.init();
});