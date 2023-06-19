$(document).ready(function () {

    $('.admin_button_delete_content_type').click(function () {
        return (confirm('Warning! All records of this content type will get deleted irrevocably. Continue?'));
    });

    $('#form_admin_button_submit').click(function () {

        $.blockUI({message: null});

        $.post($('#form_admin').attr('action'), $('#form_admin').serialize()).fail(function (data) {
            cmck_message_error('Failed to update definition. Please try again later or contact your administrator.');
            $.unblockUI();
        }).done(function (response) {
            console.log(response);
            if (response.success != undefined) {

                if (response.success == true) {
                    cmck_message_info('Definition update successful.');
                    $.unblockUI();
                    return false;
                }
                else {
                    if (response.message != undefined) {
                        cmck_message_alert(response.message);
                        $.unblockUI();
                        return false;
                    }
                }
            }
            cmck_message_error('Failed to update definition. Please try again later or contact your administrator.');
            $.unblockUI();
        });

        return false;
    });


    // capture CTRL+S, CMD+S
    $(document).keydown(function (e) {
        if ((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            $('#form_admin_button_submit').click();
            return false;
        }
        return true;
    });

    $('.admin_button_create_content_type').click(function () {

        $('#modal_admin_create_content_type form').attr('action', $(this).attr('href'));
        var options = {};
        $('#modal_admin_create_content_type').modal(options);
        $('input[name=create_content_type]').val('');
        return false;
    });

    $('.admin_button_create_config_type').click(function () {

        $('#modal_admin_create_config_type form').attr('action', $(this).attr('href'));
        var options = {};
        $('#modal_admin_create_config_type').modal(options);
        $('input[name=create_config_type]').val('');
        return false;
    });
});