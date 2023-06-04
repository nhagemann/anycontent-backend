$('.file-select-item').click(function () {

    var value = $(this).attr('data-src');
    input = parent.cmck_get_var('fe_file_property');
    $(input).val(value).trigger('change');
    top.cmck_trigger_change(input);

    parent.cmck_modal_hide();

});