/**
 * Copyright 2022 Lengow SAS.
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
 * @copyright 2022 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

(function( $ ) {
    $(document).ready(function () {

        var globalContentSelector = $('input[name="see_global_content"]');
        var exportContentSelector = $('input[name="see_export_content"]');
        var checksumContentSelector = $('input[name="see_checksum_content"]');

        displayGlobalContent();
        globalContentSelector.change(function () {
            displayGlobalContent();
        });

        displayExportContent();
        exportContentSelector.change(function () {
            displayExportContent();
        });

        displayChecksumContent();
        checksumContentSelector.change(function () {
            displayChecksumContent();
        });
        
        function displayGlobalContent() {
            var selector = $('.js-lgw-global-content');
            if (globalContentSelector.prop('checked')) {
                selector.slideDown(150);
            } else {
                selector.slideUp(150);
            }
        }

        function displayExportContent() {
            var selector = $('.js-lgw-export-content');
            if (exportContentSelector.prop('checked')) {
                selector.slideDown(150);
            } else {
                selector.slideUp(150);
            }
        }

        function displayChecksumContent() {
            var selector = $('.js-lgw-checksum-content');
            if (checksumContentSelector.prop('checked')) {
                selector.slideDown(150);
            } else {
                selector.slideUp(150);
            }
        }
    });
})(lengow_jquery);
