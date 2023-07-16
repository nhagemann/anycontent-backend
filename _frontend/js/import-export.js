$(document).ready(function () {


    $('#listing_button_import').click(function () {
        cmck_modal($(this).attr('href'));

        return false;
    });

    $('#listing_button_export').click(function () {
        cmck_modal($(this).attr('href'));

        return false;
    });


});
