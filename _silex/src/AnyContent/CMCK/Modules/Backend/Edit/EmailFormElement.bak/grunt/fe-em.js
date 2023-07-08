(function ($) {

    /* ---------------------------------------- */

    $.fn.cmck_fe_email = function () {


        // http://stackoverflow.com/questions/46155/validate-email-address-in-javascript

        var validateEmail = function (val) {
            var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return regex.test(val);
        };

        var checkInput = function (e) {

            $(this).removeClass('alert-success');
            $(this).removeClass('alert-danger');

            email = $(this).val();

            if (email != '') {
                var formelement = $(this);

                if (validateEmail(email)) {
                    formelement.addClass('alert-success');
                    formelement.removeClass('alert-danger');
                }
                else
                {
                    formelement.addClass('alert-danger');
                    formelement.removeClass('alert-success');
                }

            }
        };

        /* ---------------------------------------- */

        $(document).on("cmck", function (e, params) {


            switch (params.type) {
                case 'editForm.init':
                case 'sequenceForm.init':
                case 'sequenceForm.refresh':
                    $('div.formelement-email input').each(function () {

                        val = $(this).val();
                        $(this).on('focus', checkInput);
                        $(this).on('blur', checkInput);

                    });
                    break;
            }

        });
    };

    $(document).cmck_fe_email();

    /* ---------------------------------------- */

})(jQuery);

