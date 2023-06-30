function cmck_modal_id(id, url, onShown) {
    id = '#' + id;

    $(id).off('shown.bs.modal');

    $(id).on('shown.bs.modal', function () {

        if (typeof onShown == 'function') {
            onShown();
        }
    });

    if (typeof url != 'undefined'){
        // clear modal content, if content should be retrieved via request
        $(id).removeData();
        $(id + ' .modal-header').html('');
        $(id + ' .modal-body').html('');
        $(id + ' .modal-footer').html('');
    }
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
$(document).on("cmck", function (e, params) {

    switch (params.type) {
        case 'editForm.init':
        case 'sequenceForm.init':
        case 'sequenceForm.refresh':
            $('.formelement-popover').popover({html: true});
            $('.formelement-tooltip').popover({html: true});
            break;
    }
});


$(document).ready(function () {

});
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




function cmck_sequence_trigger_change(object)
{
    $(object).trigger('change');
}

(function ($) {

    // This JavaScript is included on a page containing just one accordion/sortable for editing a sequence. This page
    // is included as an iframe within the parent page. The height of the iframe gets adjusted automatically.


    $.fn.cmck_editsequence = function () {

        var firstRun = true;

        var calcHeight = function () {

            // find highest item
            init = 0;
            $('.sequence-item').each(function(){
                if ($(this).height() > init) { init = $(this).height(); }
            });

            // add 35 pixel for every item
            c = $('div.sequence-item').length;
            init = init + c * 35;

            // add 40 pixel for every possible sequence element
            c = $('ul.sequence-add-item').first().find('li').length;
            init = init + c * 40;

            // generic buffer of 50 pixel
            init = init + 50;

            // minimum height of 200 pixel
            h = Math.max(200, init);

            iframe = '#form_edit_sequence_' + $('#form_sequence').attr('data-property') + '_iframe';

            if (firstRun) {
                // resize without animation effects, when called for the first time
                $(iframe, window.parent.document).height(h);
                firstRun = false;

            }
            else {
                $(iframe, window.parent.document).animate({height: h + 'px'}, 500);
            }


        };

        
        $(document).on("cmck", function (e, params) {

              var item;

              switch (params.type) {


                case 'sequenceForm.init':
                case 'sequenceForm.refresh':

                    if (params.type=='sequenceForm.refresh') {

                        $('.sequence-accordion').accordion('destroy');
                        $('.sequence-add-item li a').off('click');
                        $('.sequence-remove-item').off('click');
                        $(".sequence-accordion").sortable("refresh"); //call widget-function destroy

                    }

                    $(".sequence-accordion").accordion({
                        header: ".accordionTitle",
                        collapsible: true,
                        heightStyle: "content",
                        active: parseInt($('#form_sequence').attr('data-active-item')),
                        activate: function () {
                            calcHeight();
                        },
                        animated: 'fastslide'
                    });


                    if (params.type=='sequenceForm.init') {
                        $(".sequence-accordion").sortable({
                            axis: "y",
                            handle: ".accordionTitle",
                            stop: function (event, ui) {
                                // IE doesn't register the blur when sorting
                                // so trigger focusout handlers to remove .ui-state-focus
                                ui.item.children(".accordionTitle").triggerHandler("focusout");
                            }});
                    }


                    $(".sequence-add-item li a").click(function () {
                        insert = $(this).attr('data-insert');
                        item = $(this).closest('ul').attr('data-item');

                        $.event.trigger('cmck', {type: 'sequenceForm.add', insert: insert, item: item});
                    });

                    $(".sequence-remove-item").click(function () {


                        item = $(this).attr('data-item');

                        $.event.trigger('cmck', {type: 'sequenceForm.remove', item: item});
                    });


                    calcHeight();


                    break;

                case 'sequenceForm.add':

                    count = parseInt($('#form_sequence').attr('data-count')) + 1;

                    $('#form_sequence').attr('data-count', count);
                    $.get($('#form_sequence').attr('data-action-add') + '?insert=' + params.insert + '&count=' + count, function (data) {


                        item = params.item;
                        if (parseInt(item) > 0) {
                            $('#form_sequence_item_' + item).after(data);

                            n = $('div.sequence-item').index($('#form_sequence_item_' + item)) + 1;
                            $('#form_sequence').attr('data-active-item', n);
                        }
                        else {
                            $('.sequence-accordion').append(data);

                            n = $('div.sequence-item').length - 1;
                            $('#form_sequence').attr('data-active-item', n);
                        }

                        //$.event.trigger('cmck', {type: 'editForm.init', refresh: true});
                        $.event.trigger('cmck', {type: 'sequenceForm.refresh'});

                    });


                    break;

                case 'sequenceForm.remove':
                    item = parseInt(params.item);
                    n = $('div.sequence-item').index($('#form_sequence_item_' + item));
                    $('#form_sequence_item_' + item).remove();


                    // make sure to open the new last item, if you just removed the previous last item
                    if (n == $('div.sequence-item').length) {
                        n = n - 1;
                    }

                    $('#form_sequence').attr('data-active-item', n);


                    $.event.trigger('cmck', {type: 'editForm.init', refresh: true});

                    break;
            }


        });

        $.event.trigger('cmck', {type: 'sequenceForm.init'});
        //$.event.trigger('cmck', {type: 'editForm.init'});
    };


    $(document).ready(function () {
        $(document).cmck_editsequence();
    });

    /* ---------------------------------------- */
})(jQuery);




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

$(document).ready(function () {


    $(document).on("cmck", function (e, params) {

        switch (params.type) {

            case 'editform.Save':



                $(".sequence-iframe").each(function () {
                    var sequenceForm = $(this).contents().find("#form_sequence");

                    $('#form_edit').attr('data-event-countdown',parseInt($('#form_edit').attr('data-event-countdown'))+1);
                    $.post($(sequenceForm).attr('action'), $(sequenceForm).serialize(), function (json) {

                        $('#form_edit').attr('data-event-countdown',parseInt($('#form_edit').attr('data-event-countdown'))-1);
                        $.event.trigger('cmck', {type: 'editform.setProperty', property: $(sequenceForm).attr('data-property'), value: JSON.stringify(json.sequence), save: true});

                    }, 'json');
                });




                break;
        }

    });
});
$(document).ready(function () {

    $('#listing_filter select').change(function(){
        $('#listing_filter').submit();
    });
});
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




$(document).ready(function () {

    // https://github.com/ilikenwf/nestedSortable

    $('ol.sortable-tree').nestedSortable({

        handle: 'div',
        // excludes root and unlinked button from sortable items
        items: 'li.sortable-item',
        toleranceElement: '> div',
        protectRoot: true,
        opacity: .5,
        revert: 100,
        tabSize: 20,
        tolerance: 'pointer',
        // connects left and right tree
        connectWith: '.sortable-tree',
        // disallow drop on same level as left root
        relocate: function () {
            if($('#sortable_tree_left > li').length>1){
                return false;
            };
            $('#list_root').removeClass('sortable-emptylist');
        }
    });


    $('#form_sort_button_save').click(function () {

        // reinitialize with default li selector, otherwise the data cannot get fetched
        $('ol.sortable-tree').nestedSortable({items: 'li'});


        nested = $('ol.sortable-tree').nestedSortable('toArray', {
            startDepthCount: 0
        });

        tree = [];
        $.each(nested, function (k, node) {

            if (node.depth > 0) {
                var o = {
                    id: node.id,
                    parent_id: node.parent_id
                };
                tree.push(o);
            }
        });

        $.blockUI({message: null});
        $('#form_sort_list').val(JSON.stringify(tree));
    });

});