(function ($) {


    $(document).on("cmck", function (e, params) {


        switch (params.type) {


            case 'editForm.init':
            case 'sequenceForm.init':
            case 'sequenceForm.refresh':

                $('.formelement-geolocation-modal-button').off();
                $('.formelement-geolocation-modal-button').click(function () {

                    // get the input field, which shall contain latitude and longitude upon selection
                    var lat = $($(this).attr('data-input') + '_lat').val();
                    var long = $($(this).attr('data-input') + '_long').val();

                    var url = $(this).attr('href') + '/' + lat + '/' + long;

                    // start modal on top level
                    parent.cmck_modal(url);
                    return false;
                });
                break;
        }
    });


    /* ---------------------------------------- */
})(jQuery);

