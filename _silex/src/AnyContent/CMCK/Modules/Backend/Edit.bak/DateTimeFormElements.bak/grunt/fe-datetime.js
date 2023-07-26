(function ($) {

    $(document).on("cmck", function (e, params) {


        switch (params.type) {
            case 'editForm.init':
            case 'sequenceForm.init':
            case 'sequenceForm.refresh':
                $('.datepicker').each(function () {


                    $(this).datepicker({
                        changeMonth: true,
                        changeYear: true,
                        dateFormat: 'yy-mm-dd',
                        showWeek: false,
                        firstDay: 1,
                        numberOfMonths: 1,
                        showButtonPanel: false
                    });


                });
                break;
        }

    });

})(jQuery);



