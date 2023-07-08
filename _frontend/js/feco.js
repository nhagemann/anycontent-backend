(function ($) {


    $(document).on("cmck", function (e, params) {


        switch (params.type) {


            case 'editForm.init':
            case 'sequenceForm.init':
            case 'sequenceForm.refresh':


                $(".formelement-color input").minicolors({
                    control: 'wheel',
                    letterCase: 'uppercase',
                    theme: 'bootstrap'
                });

                $('.formelement-color select').on('change', function () {
                    value = $(this).val();
                    value.replace(/[^0-9A-F]/g, '');
                    target = $(this).attr('data-target');

                    $('#' + target).minicolors('value', value);
                });
                break;
        }
    });


    /* ---------------------------------------- */
})(jQuery);

