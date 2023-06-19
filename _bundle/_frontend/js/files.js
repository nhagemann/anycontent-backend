$(document).ready(function () {

    $('#form_files_button_create_folder').click(function () {

        var options = {};
        $('#modal_files_create_folder').modal(options);

        return false;
    });

    $('#form_files_button_upload_file').click(function () {

        var options = {};
        $('#modal_files_upload_file').modal(options);

        return false;
    });

    $('#form_files_button_delete_folder').click(function () {

        var options = {};
        $('#modal_files_delete_folder').modal(options);

        return false;
    });


    $('.files-file-zoom').click(function () {

        $('#modal_files_file_zoom_title').html($(this).attr('data-title'));
        $('#modal_files_file_zoom_iframe').attr('src', $(this).attr('href'));

        var options = {keyboard: true};
        $('#modal_files_file_zoom').modal(options);

        return false;
    });

    $('.files-file-edit').click(function () {

        $('#modal_files_file_original_id').val($(this).attr('data-title'));
        $('#modal_files_file_rename_id').val($(this).attr('data-title'));

        var options = {keyboard: true};
        $('#modal_files_file_edit').modal(options);


        return false;
    });

    $('.files-delete-file').click(function () {

        $('#modal_files_file_delete_title').html($(this).attr('data-title'));
        $('#modal_files_file_delete_id').val($(this).attr('data-title'));

        var options = {keyboard: true};
        $('#modal_files_delete_file').modal(options);


        return false;
    });


});
