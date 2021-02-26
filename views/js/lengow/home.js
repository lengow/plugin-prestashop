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

(function ($) {
    $(document).ready(function () {

        var connectionContent = $('#lgw-connection-content');

        // go to credentials form
        connectionContent.on('click', '.js-go-to-credentials', function () {
            var href = $('#lengow_ajax_link').val();
            var data = {action: 'go_to_credentials'};
            $.getJSON(href, data, function(response) {
                $('#lgw-connection-content').html(response['content']);
            });
        });

        // go to catalog form
        connectionContent.on('click', '.js-go-to-catalog', function () {
            var retry = $(this).attr('data-retry') !== 'false';
            var href = $('#lengow_ajax_link').val();
            var data = {action: 'go_to_catalog', retry: retry};
            $.getJSON(href, data, function(response) {
                $('#lgw-connection-content').html(response['content']);
                $('#lgw-connection-content select').select2();
            });
        });

        // active check credentials button
        connectionContent.on('change', '.js-credentials-input', function () {
            var accessToken = $('input[name=lgwAccessToken]').val();
            var secret = $('input[name=lgwSecret]').val();
            if(accessToken !== '' && secret !== ''){
                $('.js-connect-cms')
                    .removeClass('lgw-btn-disabled')
                    .addClass('lgw-btn-green');
            } else{
                $('.js-connect-cms')
                    .addClass('lgw-btn-disabled')
                    .removeClass('lgw-btn-green');
            }
        });

        // check api credentials
        connectionContent.on('click', '.js-connect-cms', function () {
            var accessToken = $('input[name=lgwAccessToken]');
            var secret = $('input[name=lgwSecret]');
            $('.js-connect-cms').addClass('loading');
            accessToken.prop('disabled', true );
            secret.prop('disabled', true );
            var href = $('#lengow_ajax_link').val();
            var data = {
                action: 'connect_cms',
                accessToken: accessToken.val(),
                secret: secret.val()
            };
            $.getJSON(href, data, function(response) {
                $('#lgw-connection-content').html(response['content']);
            });
        });

        // disable catalog option in select
        connectionContent.on('change', '.js-catalog-linked', function () {
            var currentShopId = $(this).attr('name');
            // get all catalogs selected by shop
            var catalogSelected = [];
            var shopSelect = $('.js-catalog-linked');
            shopSelect.each(function() {
                var shopId = $(this).attr('name');
                var catalogIds = $(this).val();
                if (catalogIds !== null) {
                    $.each(catalogIds, function (key, value) {
                        catalogSelected.push({
                            shopId: shopId,
                            catalogId: value
                        })
                    });
                }
            });
            // disable catalog option for other shop
            shopSelect.each(function() {
                var shopId = $(this).attr('name');
                if (shopId !== currentShopId) {
                    var catalogLinked = [];
                    $.each(catalogSelected, function(key, value) {
                        if (value.shopId !== shopId) {
                            catalogLinked.push(value.catalogId);
                        }
                    });
                    var options = $(this).find('option');
                    options.each(function() {
                        if (catalogLinked.includes($(this).val())) {
                            $(this).attr('disabled', true);
                        } else {
                            $(this).attr('disabled', false);
                        }
                    });
                    $(this).select2();
                }
            });
        });

        // link catalog ids
        connectionContent.on('click', '.js-link-catalog', function () {
            var catalogSelected = [];
            var shopSelect = $('.js-catalog-linked');
            shopSelect.each(function() {
                if ($(this).val() !== null) {
                    var catalogIds = $(this).val();
                    var catalogIdsCleaned = [];
                    $.each(catalogIds, function(key, value) {
                        catalogIdsCleaned.push(parseInt(value, 10))
                    })
                    catalogSelected.push({
                        shopId: parseInt($(this).attr('name'), 10),
                        catalogId: catalogIdsCleaned
                    });
                }
            });
            $('.js-link-catalog').addClass('loading');
            shopSelect.prop('disabled', true );
            var href = $('#lengow_ajax_link').val();
            var data = {
                action: 'link_catalogs',
                catalogSelected: catalogSelected
            };
            $.getJSON(href, data, function(response) {
                if (response['success']) {
                    location.reload();
                } else {
                    $('#lgw-connection-content').html(response['content']);
                }
            });
        });
    });
})(lengow_jquery);