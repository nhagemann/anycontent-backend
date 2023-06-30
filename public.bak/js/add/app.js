function cmck_modal_id(id, url, onShown) {
    id = '#' + id;

    $(id).off('shown.bs.modal');

    $(id).on('shown.bs.modal', function () {

        if (typeof onShown == 'function') {
            onShown();
        }
    });

    $(id).removeData();
    $(id+' .modal-header').html('');
    $(id+' .modal-body').html('');
    $(id+' .modal-footer').html('');
    $(id).appendTo("body");

    $(id).modal({
        keyboard: true

    });

    $(id).load(url);
}


function cmck_modal(url, onShown) {
    cmck_modal_id('modal_edit', url, onShown);
}


function cmck_modal_id_hide(id) {
    id = '#' + id;

    $(id).modal('hide');
}


function cmck_modal_hide() {
    cmck_modal_id_hide('modal_edit');

}

function cmck_modal_set_property(name, value) {
    $('#form_edit [name=' + name + ']').val(value);
}

function cmck_set_var(name, value) {
    if (typeof $.cmck != 'object') {
        $.cmck = {};
    }
    $.cmck[name] = value;
}

function cmck_get_var(name, value) {
    return $.cmck[name];
}

function cmck_trigger_change(object) {
    $(object).trigger('change');
    $('iframe').each(function (k, v) {
        if (typeof v.contentWindow.cmck_sequence_trigger_change == 'function') {
            v.contentWindow.cmck_sequence_trigger_change(object);
        }
    });
}

function cmck_message_info(message) {
    $('#messages').html('<div class="alert alert-info">' + message + '</div>');
    $(document).scrollTop(0);
    $('#messages div').delay(3000).fadeOut(500);
}

function cmck_message_alert(message) {
    $('#messages').html('<div class="alert alert-warning">' + message + '</div>');
    $(document).scrollTop(0);
    $('#messages div').delay(3000).fadeOut(500);
}

function cmck_message_error(message) {
    $('#messages').html('<div class="alert alert-danger">' + message + '</div>');
    $(document).scrollTop(0);
    $('#messages div').delay(3000).fadeOut(500);
}

function cmck_get_cookie(name) {
    var parts = window.document.cookie.split(name + "=");
    if (parts.length == 2) return parts.pop().split(";").shift();
}

function cmck_delete_cookie(name) {
    document.cookie = encodeURIComponent(name) + "=deleted; expires=" + new Date(0).toUTCString();
}

function cmck_document()
{
    return document;
}