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
        function validateEmail(email) {
            var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }

        // SUMBIT FORM

        $( ".lengow_form" ).submit(function( event ) {
            event.preventDefault();
            var form = this;
          $('.lengow_form button[type="submit"]').addClass('loading');
          setTimeout(function () {
            $('.lengow_form button[type="submit"]').removeClass('loading');
            $('.lengow_form button[type="submit"]').addClass('success');
            form.submit();
           }, 1000);
        });


        // MODAL

        // Open modal
        $('.lgw-modal-delete').click(function(){
            window.location.hash = 'delete';
            return false;
        });

        // Open modal on loading

        if(window.location.hash) {
            openModal();
        }

        // Delete modal
        $('.js-close-this-modal').click(function(){
            window.location.hash = '';
            return false;
        });

        var hash = window.location.hash;
        setInterval(function(){
            if (window.location.hash != hash) {
                hash = window.location.hash;
                if( hash.length < 1){
                    killModal();
                }
                else{
                    if( $('.lgw-modal.open').length == 0 ){
                        openModal();
                    }
                }
            }
        }, 100);

        function killModal(){
            window.location.hash = '';
            $('body').removeClass('unscrollable');
            $('.lgw-modal').removeClass('open');
            $('.js-confirm-delete').val('');
            $('.lengow_submit_delete_module')
                    .addClass('lgw-btn-disabled')
                    .removeClass('lgw-btn-red');
        }

        function openModal(){
            window.location.hash = 'delete';
            $('body').addClass('unscrollable');
            $('.lgw-modal').addClass('open');
        }


        // CONFIRM DELETE

        $('.js-confirm-delete').keyup(function(){
            var confirm = $(this).data('confirm');
            if( $(this).val() == confirm ){
                $('.lengow_submit_delete_module')
                    .removeClass('lgw-btn-disabled')
                    .addClass('lgw-btn-red');
            }
            else{
                $('.lengow_submit_delete_module')
                    .addClass('lgw-btn-disabled')
                    .removeClass('lgw-btn-red');
            }
        });

        displayReportMail();
        $('input[name="LENGOW_REPORT_MAIL_ENABLED"]').on('change', function(){
            displayReportMail();
        });

        function displayReportMail() {
            var selector = $('.lengow_report_mail_address');
            if($('input[name="LENGOW_REPORT_MAIL_ENABLED"]').prop('checked')){
                selector.slideDown(150);
                var divLegend = selector.next('.legend');
                    divLegend.addClass("blue-frame");
                    divLegend.css('display', 'block');
                    divLegend.show();
            }
            else{
                selector.slideUp(150);
                selector.next('.legend').hide();
            }
        }

        displayPreProdMode();
        $("input[name='LENGOW_IMPORT_PREPROD_ENABLED']").on('change', function () {
            displayPreProdMode();
        });

        function displayPreProdMode() {
            if ($("input[name='LENGOW_IMPORT_PREPROD_ENABLED']").prop('checked')) {
                $('#lengow_wrapper_preprod').slideDown(150);
            } else {
                $('#lengow_wrapper_preprod').slideUp(150);
            }
        }

        $('#select_log').change(function(){
            if ($('#select_log').val() !== null) {
                $("#download_log" ).show();
            }
        });

        $('#download_log').on('click', function() {
            if ($('#select_log').val() !== null) {
                window.location.href = $('#select_log').val();
            }
        });

    });

})(lengow_jquery);
