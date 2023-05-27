// This file gets included when editing content or config records. In contrast to 'edit.js' this file does NOT get included
// in sequence editing iframe.

$(document).on("cmck", function (e, params) {


    switch (params.type) {
        case 'editform.setProperty': // Used from sequences upon storing.

            $('#form_edit [name=' + params.property + ']').val(params.value);

            if (params.save == true) {
                if (parseInt($('#form_edit').attr('data-event-countdown')) == 0) {
                    $('#form_edit').submit();
                }
            }
            break;
    }
});

$(document).ready(function () {

    $('#form_edit_button_save_options a').click(function () {
        $('#form_edit_button_save').removeClass('open');
        $('#form_edit_button_save input:first').attr('value', $(this).text());
        $('#form_edit_button_save_operation').attr('value', $(this).attr('data-operation'));
        return false;
    });

    // manual saving of edit form triggers an event - in opposite to internal calls for submitting the form
    $('#form_edit_button_submit').click(function () {
        $.event.trigger('cmck', {type: 'editform.Save'});
        $('#form_edit').submit();
        return false;
    });

    $('#form_edit').submit(function () {

        // Interrupt posting of edit form to check for sequences and allow them to convert their input into
        // a json representation for the containing property

        countdown = parseInt($('#form_edit').attr('data-event-countdown'));


        if (countdown == 0) { // no more sequences to be processed

            counterrors = 0;
            $('.form-group.mandatory').each(function () {

                $(this).removeClass('has-error');
                idFormelement = $(this).attr('data-formelement');
                val = '';
                // Check value of all form fiels having a id starting with the string provided in data-formelement.
                // Usually it will be exactly one form fields, but some form elements split the input into different
                // form fields, e.g. "geolocation".
                $('[id^="' + idFormelement + '"]').each(function () {
                    val = val + $(this).val().trim();
                    console.log(val);
                });

                if (val == '') {
                    $(this).addClass('has-error');
                    counterrors++;
                }
            });
            if (counterrors > 0) {
                cmck_message_alert('Please fill in all required fields.');
                return false;
            }

            $.blockUI({message: null});

            $.post($('#form_edit').attr('action'), $('#form_edit').serialize()).fail(function (data) {
                cmck_message_error('Failed to save record. Please try again later or contact your administrator.');
                $.unblockUI();
            }).done(function (response) {

                if (response.success != undefined) {

                    if (response.success == true) {
                        location.href = response.redirect;
                        return false;
                    } else {
                        if (response.message != undefined) {
                            if (response.error != undefined && response.error == true) {
                                cmck_message_error(response.message);
                            }
                            else {
                                cmck_message_alert(response.message);
                            }

                            if (response.properties != undefined) {
                                for (i = 0; i < response.properties.length; i++) {
                                    property = response.properties[i];
                                    input = $('input[name="' + property + '"]');
                                    id = input.attr('id');
                                    $('div.form-group[data-formelement="' + id + '"]').addClass('has-error');
                                }
                            }
                            $.unblockUI();
                            return false;
                        }
                    }
                }
                cmck_message_error('Failed to save record. Please try again later or contact your administrator.');


                $.unblockUI();
            });


            return false;

        }
        return false;
    });


    $('.button_delete').click(function () {
        var url = $(this).attr('href');
        bootbox.confirm('Are you sure?', function (result) {
            if (result) {
                document.location = url;
            }
        });
        return false;
    });

    $('#form_edit_button_transfer').click(function () {
        cmck_modal($(this).attr('href'));
        return false;
    });


    // capture CTRL+S, CMD+S
    $(document).keydown(function (e) {
        if ((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            $('#form_edit_button_submit').click();
            return false;
        }
        return true;
    });

    // inform form elements about loading of the editing form
    $.event.trigger('cmck', {type: 'editForm.init'});


});



