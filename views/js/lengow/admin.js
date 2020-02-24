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
        /* switch toggle */
        lengow_jquery('body').on('change', '.lgw-switch', function(event) {
            var check = lengow_jquery(this);
            var checked = check.find('input').prop('checked');
            check.toggleClass('checked');
        });

        $('.lengow_select').select2({ minimumResultsForSearch: 16});

        init_tooltip();
        var clipboard = new Clipboard('.lengow_copy');

        var debugExist=$('#lgw-debug').length;
        if (debugExist > 0){
            $("#lengow_feed_wrapper").addClass('activeDebug');
            $("#lengow_order_wrapper").addClass('activeDebug');
            $("#lengow_form_order_setting").addClass('activeDebug');
            $("#lengow_mainsettings_wrapper").addClass('activeDebug');
            $(".lengow_help_wrapper").addClass('activeDebug');
        }
    });
})(lengow_jquery);

function init_tooltip() {
    lengow_jquery('.lengow_link_tooltip').tooltip({
        'template': '<div class="lengow_tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
    });
}
