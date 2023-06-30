// set timeouts for feedback messages

$(document).ready(function () {

    $('.feedback .alert-success').delay(2000).fadeOut(500);
    $('.feedback .alert-info').delay(2500).fadeOut(500);
    $('.feedback .alert-warning').delay(3000).fadeOut(500);
    $('.feedback .alert-danger').delay(4500).fadeOut(500);

    $('.timeshift-blink').each(function() {
        var elem = $(this);
        setInterval(function() {
            if (elem.css('visibility') == 'hidden') {
                elem.css('visibility', 'visible');
            } else {
                elem.css('visibility', 'hidden');
            }
        }, 500);
    });
});



