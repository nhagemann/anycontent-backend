(function ($) {


    $('.formelement-reference select').on('change', function () {
        value = $(this).val();
        button = $('#' + ($(this).attr('id')) + '_edit_button');

        if (value != '') {
            $(button).removeClass('disabled');
            url = $(button).attr('data-url');
            console.log(url);
            url = url.replace('recordId', value);
            $(button).attr('href', url);
        }
        else {
            $(button).addClass('disabled');
        }
    })

})(jQuery);