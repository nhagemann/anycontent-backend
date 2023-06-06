$(document).ready(function () {

    $('#listing_filter select').change(function(){
        $('#listing_filter').submit();
    });
});