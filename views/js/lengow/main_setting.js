(function ($) {
    $(document).ready(function () {
        function validateEmail(email) {
            var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }

        $(".lengow_report_mail_address select").select2({
            tags: true,
            width: '100%',
            selectOnClose: true,
            closeOnSelect: false,
            tokenSeparators: [",", " "],
            createTag: function(term, data) {
                var value = term.term;
                if(validateEmail(value)) {
                    return {
                        id: value,
                        text: value
                    };
                }
                return null;
            }
        }).on("select2:open", function (e) {
            $('.select2-dropdown--below').hide();
        }).on("select2:selecting", function(e) {
            $('.select2-search__field').val('');
        });

        $("#lengow_uninstall_checkbox").on('switchChange.bootstrapSwitch', function (event, state) {
            if (event.type == "switchChange") {
                displayDeleteData();
            }
        });

        function displayDeleteData() {
            if ($("#lengow_uninstall_checkbox").prop('checked')) {
                $('#lengow_wrapper_delete').show();
            } else {
                $('#lengow_wrapper_delete').hide();
            }
        }
        displayDeleteData();

        function displayPreProdMode() {
            if ($("input[name='LENGOW_IMPORT_PREPROD_ENABLED']").prop('checked')) {
                $('#lengow_wrapper_preprod').show();
            } else {
                $('#lengow_wrapper_preprod').hide();
            }
        }
        displayPreProdMode();

        $("input[name='LENGOW_IMPORT_PREPROD_ENABLED']").on('switchChange.bootstrapSwitch', function (event, state) {
            if (event.type == "switchChange") {
                displayPreProdMode();
            }
        });
    });

})(lengow_jquery);