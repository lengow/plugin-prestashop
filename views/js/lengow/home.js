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
        var href = $('#lengow_ajax_link').val();
        var syncLink = $('#lengow_sync_link').val();
        var isoCode = $('#lengow_lang_iso').val();
        var syncIframe = document.getElementById('lengow_iframe');
        if (syncIframe) {
            syncIframe.onload = function () {
                $.ajax({
                    url: href,
                    method: 'POST',
                    data: {action: 'get_sync_data'},
                    dataType: 'json',
                    success: function (data) {
                        var targetFrame = document.getElementById("lengow_iframe").contentWindow;
                        targetFrame.postMessage(data, '*');
                    }
                });
            };
            if (syncLink) {
                // syncIframe.src = '//cms.lengow.io/sync/';
                // syncIframe.src = '//cms.lengow.net/sync/';
                syncIframe.src = '//cms.lengow.rec/sync/';
                // syncIframe.src = '//cms.lengow.dev/sync/';
            } else {
                // syncIframe.src = '//cms.lengow.io/';
                // syncIframe.src = '//cms.lengow.net/';
                syncIframe.src = '//cms.lengow.rec/';
                // syncIframe.src = '//cms.lengow.dev/';
            }
            syncIframe.src = syncIframe.src+'?lang='+isoCode+'&clientType=prestashop';
            $('#frame_loader').hide();
            $('#lengow_iframe').show();
        }

        window.addEventListener('message', receiveMessage, false);

        function receiveMessage(event) {
            switch (event.data.function) {
                case 'sync':
                    // Store lengow information into Prestashop :
                    // account_id
                    // access_token
                    // secret_token
                    $.ajax({
                        url: href,
                        method: 'POST',
                        data: {action: 'sync', data: event.data.parameters},
                        dataType: 'script'
                    });
                    break;
                case 'sync_and_reload':
                    // Store lengow information into Prestashop and reload it
                    // account_id
                    // access_token
                    // secret_token
                    $.ajax({
                        url: href,
                        method: 'POST',
                        data: {action: 'sync', data: event.data.parameters},
                        dataType: 'script',
                        success: function() {
                            location.reload();
                        }
                    });
                    break;
                case 'reload':
                    // Reload the parent page (after sync is ok)
                    location.reload();
                    break;
                case 'cancel':
                    // Reload Dashboard page
                    var hrefCancel = location.href.replace('&isSync=true', '');
                    window.location.replace(hrefCancel);
                    break;
            }
        }
    });
})(lengow_jquery);