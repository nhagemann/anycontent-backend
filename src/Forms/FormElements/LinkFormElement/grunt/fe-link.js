(function ($) {
    /* ---------------------------------------- */

    $.fn.cmck_fe_link = function () {
        var checkInput = function (e) {
            $(this).removeClass('alert-success');
            $(this).removeClass('alert-danger');

            url = $(this).val();

            if (url != '') {
                var formelement = $(this);

                $.ajax({

                    url: '/edit/check/link/' + url,
                    success: function (result) {
                        formelement.removeClass('alert-success');
                        formelement.removeClass('alert-warning');
                        formelement.removeClass('alert-danger');
                        if (result == 200) {
                            formelement.addClass('alert-success');
                        } else {
                            if (result > 400) {
                                formelement.addClass('alert-danger');
                            } else {
                                formelement.addClass('alert-warning');
                            }
                        }

                    }
                });
            }
        };

        /* ---------------------------------------- */

        $(document).on("cmck", function (e, params) {
            switch (params.type) {
                case 'editForm.init':
                case 'sequenceForm.init':
                case 'sequenceForm.refresh':
                    $('div.formelement-link input').each(function () {
                        $(this).off('blur');
                        $(this).on('blur', checkInput);

                    });
                    break;
            }

        });
    };

    $(document).cmck_fe_link();

    /* ---------------------------------------- */

})(jQuery);

