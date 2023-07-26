(function ($) {
    $(document).on("cmck", function (e, params) {
        switch (params.type) {
            case 'editForm.init':
            case 'sequenceForm.init':
            case 'sequenceForm.refresh':

                $('.formelement-file-modal-button').off();
                $('.formelement-file-modal-button').click(function () {
                    // get the input field
                    var input = $($(this).attr('data-input'));

                    parent.cmck_set_var('fe_file_property', input);

                    parent.cmck_modal($(this).attr('href'));

                    return false;
                });

                $('.formelement-file-modal-button-view').off();
                $('.formelement-file-modal-button-view').click(function () {
                    var id = $(this).attr('data-input');
                    var value = $(id).val();

                    $(parent.document).find('#modal_files_file_zoom_title').html(value);
                    var url = $(id).attr('data-url-view') + value;
                    $(parent.document).find('#modal_files_file_zoom_iframe').attr('src', url);

                    $.ajax({
                        url : url,
                        type: 'HEAD'
                    }).always(function (o) {
                        if (o != undefined && o.status == 404) { // ignore all other errors, especially missing Access-Control-Allow-Origin header
                            alert('File not found. Please check file path.');
                        } else {
                            parent.cmck_modal_id('modal_files_file_zoom');
                        }
                        parent.cmck_modal_id('modal_files_file_zoom');
                    });

                    return false;
                });

                $('.formelement-file-modal-button-download').off();
                $('.formelement-file-modal-button-download').click(function () {
                    var id = $(this).attr('data-input');
                    var value = $(id).val();

                    if (value.trim() != '') {
                        value = $(this).attr('href') + value;

                        $.ajax({
                            url : value,
                            type: 'HEAD'
                        }).always(function (o) {
                            if (o != undefined && o.status == 404) { // ignore all other errors, especially missing Access-Control-Allow-Origin header
                                alert('File not found. Please check file path.');
                            } else {
                                window.location.href = value;
                                return false;
                            }
                        });
                    }

                    return false;
                });

                $('.formelement-file input, .formelement-image input').on('change', function () {
                    var id = '#' + $(this).attr('id') + '_preview';
                    var value = $(this).val();

                    $(id).hide();
                    $(id + ' a').attr('href', '');
                    $(id + ' img').attr('src', '');

                    if (value) {
                        if (value.toLowerCase().match(/\.(jpeg|jpg|gif|png)$/) != null) {
                            var url_view = $(this).attr('data-url-view') + value;

                            $.ajax({
                                url    : url_view,
                                type   : 'HEAD',
                                error  : function () {
                                    $(id).hide();
                                },
                                success: function () {
                                    $(id).show();
                                    $(id + ' a').attr('href', url_view);
                                    $(id + ' img').attr('src', url_view);
                                }
                            });
                        }
                    }

                });
                break;
        }

    });

    /* ---------------------------------------- */
})(jQuery);



