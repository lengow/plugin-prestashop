/**
 * Copyright 2017 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

function addScoreCarrier() {
    nbs = 0;
    var tooltipMessage = $('#marketplace-list').attr('data-tooltip-carrier');
    $('.score').removeClass('red').removeClass('green').removeClass('orange');
    $('.sub').each(function() {
        var total = $(this).find('.carrier').length;
        var required = false;

        $(this).find('.carrier').each(function(){
            if ($(this).val() !== "") {
                nbs += 1 ;
            } else {
                if ($(this).hasClass('required')) {
                    required = true;
                }
            }

        });
        $(this).parents('li.lengow_marketplace').find('.score').html(nbs+' / '+total);
        if (nbs == total){
            $(this).parents('li.lengow_marketplace')
                .find('.score')
                .addClass('green')
                .removeClass('lengow_link_tooltip')
                .removeAttr('data-original-title');
        } else if (required){
            $(this).parents('li.lengow_marketplace')
                .find('.score')
                .addClass('red lengow_link_tooltip')
                .attr('data-original-title', tooltipMessage);
        } else {
            $(this).parents('li.lengow_marketplace')
                .find('.score')
                .addClass('orange')
                .removeClass('lengow_link_tooltip')
                .removeAttr('data-original-title');
        }
        nbs = 0;
    });
    init_tooltip();
}

function toggleCountry($head) {
    var $sub = $head.closest('li').find('.sub');
    $head.toggleClass('active');
    $sub.slideToggle(150);
    disableCarrierMarketplace($head.attr('data-marketplace'));
}

function pluginsRender() {
    // selects
    lengow_jquery('#marketplace_matching select').select2();
}

function disableCarrierMarketplace(idMarketplace) {
    var carrierClass = 'select.js-carrier-'+idMarketplace;
    var carrierMarketplaceSelected = [];
    // get all selected carrier marketplace
    $(carrierClass).each(function() {
        if ($(this).val() != "") {
            carrierMarketplaceSelected.push(parseInt($(this).val()));
        }
    });
    // for each select disable selected carrier marketplace
    $(carrierClass).each(function() {
        var idSelected = parseInt($(this).val());
        // reset disabled option
        $(this).find('option:disabled').each(function() {
            $(this).prop('disabled', false);
        });
        // set disabled option
        for(var i in carrierMarketplaceSelected) {
            if (carrierMarketplaceSelected[i] !== idSelected) {
                $(this).find('option[value=' + carrierMarketplaceSelected[i] + ']').prop('disabled', true);
            }
        }
        // reload select
        $(this).trigger('change');
    });
    // render new select
    pluginsRender();
}

function changeStockMP() {
    var selector = $('.lengow_import_stock_ship_mp');
    if ($("input[name='LENGOW_IMPORT_SHIP_MP_ENABLED']").prop('checked')) {
        selector.slideDown(150);
        var divLegend = selector.next('.legend');
        divLegend.css('display', 'block');
        divLegend.show();
    } else {
        selector.slideUp(150);
        selector.next('.legend').hide();
    }
}

function formatState(state) {
    var image = $(state.element).data('image');
    if (!state.id) { return state.text; }
    if (!image) {
        return state.text;
    } else {
        var $state = $(
            '<span><img width="22" height="15" src="'+ image +'" class="img-flag" /> ' + state.text + '</span>'
        );
        return $state;
    }
}

function initDefaultCarrier(idCarrier) {
    var refresh = false;
    $('.lengow_default_carrier select').each(function() {
        if ($(this).val() == '') {
            $(this).val(idCarrier);
            // reload select
            $(this).trigger('change');
            refresh = true;
        }
    });
    if (refresh) {
        addScoreCarrier();
        // render new select
        pluginsRender();
    }
}

function checkRequiredSelect() {
    var sendForm = true;
    $('.default_carrier_missing').hide();
    $('select.required').each(function () {
        // if Carrier not matched
        if ($(this).val() == '') {
            sendForm = false;
            $(this).parents('.sub').show();
            $('html, body').stop().animate({scrollTop: $(this).parents('.has-sub').offset().top - 200}, 100);
            $(this).closest('.js-default-carrier').find('.default_carrier_missing').show();
        }
    });
    return sendForm;
}

(function ($) {
    $(document).ready(function () {
        // close stock mp div
        changeStockMP();
        // open marketplace list
        $('#lengow_form_order_setting').on('click', '.js-lengow-open-matching', function () {
            $("#country_selector").hide();
            $('.ajax-loading').show();
            var href = $(this).attr('data-href');
            var data = {action: 'open_marketplace_matching', idCountry: $(this).attr('data-id-country')};
            $.getJSON(href, data, function(content) {
                $("#marketplace_matching").html(content['marketplace_matching']);
                addScoreCarrier();
                toggleCountry($('#lengow_form_order_setting .lengow_marketplace:eq(0)')); // First one
                pluginsRender();
                $('.ajax-loading').hide();
            });
        });
        // close marketplace list
        $('#lengow_form_order_setting').on('click', '.js-lengow-back-country', function () {
            $("#marketplace_matching").empty();
            $("#country_selector").show();
        });
        // toggle countries
        $("#lengow_form_order_setting").on('click', '.js-marketplace',function() {
            toggleCountry($(this).closest('.lengow_marketplace'));
        });
        // close stock mp div
        $("input[name='LENGOW_IMPORT_SHIP_MP_ENABLED']").on('change', function () {
            changeStockMP();
        });
        // disable carrier marketplace options
        $(document).on('change', 'select.js-match-carrier', function () {
            var idMarketplace = $(this).closest('.js-match-carrier').attr('data-marketplace');
            disableCarrierMarketplace(idMarketplace);
        });
        // change score carrier
        $(document).on('change', '#marketplace_matching select', function () {
            addScoreCarrier();
        });
        // load default carrier for each marketplace if carrier is empty
        $(document).on('change', '.lengow_default_carrier select', function () {
            if ($(this).val() != "") {
                initDefaultCarrier($(this).val());
            }
        });
        // submit form
        $('.lengow_form').submit(function(event) {
            event.preventDefault();
            var form = this;
            // show warning message for required values
            var sendForm = checkRequiredSelect();
            if(sendForm == true){
                $('#lengow_form_order_setting button[type="submit"]').addClass('loading');
                setTimeout(function () {
                    $('#lengow_form_order_setting button[type="submit"]').removeClass('loading');
                    $('#lengow_form_order_setting button[type="submit"]').addClass('success');
                    form.submit();
                }, 1000);
            }
        });
        // load select 2 format
        $(".lengow_select").select2({
            templateResult: formatState
        });
    });
})(lengow_jquery);
