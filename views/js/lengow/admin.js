/**
 * Copyright 2016 Lengow SAS.
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
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

(function ($) {
    $(document).ready(function () {
        // Reimport Order
        $('#reimport-order').click(function (e) {
            var url = $(this).data('url');
            var orderid = $(this).data('orderid');
            var lengoworderid = $(this).data('lengoworderid');
            var feed_id = $(this).data('feedid');
            var version = $(this).data('version');

            var datas = {};
            datas['url'] = url;
            datas['orderid'] = orderid;
            datas['lengoworderid'] = lengoworderid;
            datas['feed_id'] = feed_id;
            if (version < '1.5')
                datas['action'] = 'reimport_order';

            // Show loading div
            $('#ajax_running').fadeIn(300);
            $.getJSON(url, datas, function (data) {
                $('#ajax_running').fadeOut(0);
                if (data.status == 'success') {
                    window.location.replace(data.new_order_url);
                } else {
                    alert(data.msg);
                }

            });
            return false;
        });
        $('.lengow_switch').bootstrapSwitch();
        $('.lengow_select').select2({ minimumResultsForSearch: 16});

        init_tooltip();
        var clipboard = new Clipboard('.lengow_copy');
    });
})(lengow_jquery);

function init_tooltip() {
    lengow_jquery('.lengow_link_tooltip').tooltip({
        'template': '<div class="lengow_tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
    });
}