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
$(function () {
    $.widget("custom.autocompleteCat", $.ui.autocomplete, {
        _create: function () {
            this._super();
            this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
        },
        _renderMenu: function (ul, items) {
            var that = this,
                currentCategory = "";
            $.each(items, function (index, item) {
                var li;
                if (item.category != currentCategory) {
                    if (item.category !== undefined) {
                        ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
                    }
                    currentCategory = item.category;
                }
                li = that._renderItemData(ul, item);
                if (item.category) {
                    li.attr("aria-label", item.category + " : " + item.label);
                }
            });
        }
    });
});
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




(function ($) {

    $(document).on("cmck", function (e, params) {


        switch (params.type) {
            case 'editForm.init':
            case 'sequenceForm.init':
            case 'sequenceForm.refresh':
                $('.datepicker').each(function () {


                    $(this).datepicker({
                        changeMonth: true,
                        changeYear: true,
                        dateFormat: 'yy-mm-dd',
                        showWeek: false,
                        firstDay: 1,
                        numberOfMonths: 1,
                        showButtonPanel: false
                    });


                });
                break;
        }

    });

})(jQuery);




(function ($) {

    /* ---------------------------------------- */

    $.fn.cmck_fe_email = function () {


        // http://stackoverflow.com/questions/46155/validate-email-address-in-javascript

        var validateEmail = function (val) {
            var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return regex.test(val);
        };

        var checkInput = function (e) {

            $(this).removeClass('alert-success');
            $(this).removeClass('alert-danger');

            email = $(this).val();

            if (email != '') {
                var formelement = $(this);

                if (validateEmail(email)) {
                    formelement.addClass('alert-success');
                    formelement.removeClass('alert-danger');
                }
                else
                {
                    formelement.addClass('alert-danger');
                    formelement.removeClass('alert-success');
                }

            }
        };

        /* ---------------------------------------- */

        $(document).on("cmck", function (e, params) {


            switch (params.type) {
                case 'editForm.init':
                case 'sequenceForm.init':
                case 'sequenceForm.refresh':
                    $('div.formelement-email input').each(function () {

                        val = $(this).val();
                        $(this).on('focus', checkInput);
                        $(this).on('blur', checkInput);

                    });
                    break;
            }

        });
    };

    $(document).cmck_fe_email();

    /* ---------------------------------------- */

})(jQuery);


(function ($) {

    /* ---------------------------------------- */

    $.fn.cmck_fe_link = function () {

        var checkInput = function (e) {

            $(this).removeClass('alert-success');
            $(this).removeClass('alert-danger');

            url = $(this).val();

            if (url != '') {


                var formelement = $(this);

                $.ajax({

                    url: '/anycontent/formelement/link/check?url=' + url,
                    success: function (result) {

                        formelement.removeClass('alert-success');
                        formelement.removeClass('alert-warning');
                        formelement.removeClass('alert-danger');
                        if (result == 200) {
                            formelement.addClass('alert-success');
                        }
                        else {
                            if (result > 400 || result === 0) {
                                formelement.addClass('alert-danger');
                            }
                            else {
                                formelement.addClass('alert-warning');
                            }
                        }

                    }
                });
            }
        };

        /* ---------------------------------------- */

        $(document).on("cmck", function (e, params) {


            switch (params.type) {
                case 'editForm.init':
                case 'sequenceForm.init':
                case 'sequenceForm.refresh':
                    $('div.formelement-link input').each(function () {

                        $(this).off('blur');
                        $(this).on('blur', checkInput);

                    });
                    break;
            }

        });
    };


    $(document).cmck_fe_link();

    /* ---------------------------------------- */

})(jQuery);


(function ($) {

    $(document).on("cmck", function (e, params) {

        switch (params.type) {
            case 'editForm.init':
            case 'sequenceForm.init':
            case 'sequenceForm.refresh':
                $('.formelement-password input[type=text]').each(function () {


                    $(this).change(function(){
                            $(this).next('input[type=hidden]').val(1);
                        }
                    );


                });

                $('.formelement-password-generate-button').each(function(){

                    $(this).click(function(){
                            target =$(this).attr('data-target');
                            $(target).val(Math.random().toString(36).slice(-8));
                            $(target).next('input[type=hidden]').val(1);
                        }
                    );
                });

                $('.formelement-password-clear-button').each(function(){

                    $(this).click(function(){
                            target =$(this).attr('data-target');
                            $(target).val('');
                            $(target).next('input[type=hidden]').val(1);
                            alert ('Password cleared.');
                        }
                    );
                });
                break;
        }

    });

})(jQuery);
(function ($) {


    $(document).on("cmck", function (e, params) {


        switch (params.type) {


            case 'editForm.init':
            case 'sequenceForm.init':
            case 'sequenceForm.refresh':


                $(".formelement-color input").minicolors({
                    control: 'wheel',
                    letterCase: 'uppercase',
                    theme: 'bootstrap'
                });

                $('.formelement-color select').on('change', function () {
                    value = $(this).val();
                    value.replace(/[^0-9A-F]/g, '');
                    target = $(this).attr('data-target');

                    $('#' + target).minicolors('value', value);
                });
                break;
        }
    });


    /* ---------------------------------------- */
})(jQuery);


$('.file-select-item').click(function () {

    var value = $(this).attr('data-src');
    input = parent.cmck_get_var('fe_file_property');
    $(input).val(value).trigger('change');
    top.cmck_trigger_change(input);

    parent.cmck_modal_hide();

});
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
//
// jQuery MiniColors: A tiny color picker built on jQuery
//
// Developed by Cory LaViska for A Beautiful Site, LLC
//
// Licensed under the MIT license: http://opensource.org/licenses/MIT
//
(function (factory) {
  if(typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if(typeof exports === 'object') {
    // Node/CommonJS
    module.exports = factory(require('jquery'));
  } else {
    // Browser globals
    factory(jQuery);
  }
}(function ($) {
  'use strict';

  // Defaults
  $.minicolors = {
    defaults: {
      animationSpeed: 50,
      animationEasing: 'swing',
      change: null,
      changeDelay: 0,
      control: 'hue',
      defaultValue: '',
      format: 'hex',
      hide: null,
      hideSpeed: 100,
      inline: false,
      keywords: '',
      letterCase: 'lowercase',
      opacity: false,
      position: 'bottom',
      show: null,
      showSpeed: 100,
      theme: 'default',
      swatches: []
    }
  };

  // Public methods
  $.extend($.fn, {
    minicolors: function(method, data) {

      switch(method) {
        // Destroy the control
        case 'destroy':
          $(this).each(function() {
            destroy($(this));
          });
          return $(this);

        // Hide the color picker
        case 'hide':
          hide();
          return $(this);

        // Get/set opacity
        case 'opacity':
          // Getter
          if(data === undefined) {
            // Getter
            return $(this).attr('data-opacity');
          } else {
            // Setter
            $(this).each(function() {
              updateFromInput($(this).attr('data-opacity', data));
            });
          }
          return $(this);

        // Get an RGB(A) object based on the current color/opacity
        case 'rgbObject':
          return rgbObject($(this), method === 'rgbaObject');

        // Get an RGB(A) string based on the current color/opacity
        case 'rgbString':
        case 'rgbaString':
          return rgbString($(this), method === 'rgbaString');

        // Get/set settings on the fly
        case 'settings':
          if(data === undefined) {
            return $(this).data('minicolors-settings');
          } else {
            // Setter
            $(this).each(function() {
              var settings = $(this).data('minicolors-settings') || {};
              destroy($(this));
              $(this).minicolors($.extend(true, settings, data));
            });
          }
          return $(this);

        // Show the color picker
        case 'show':
          show($(this).eq(0));
          return $(this);

        // Get/set the hex color value
        case 'value':
          if(data === undefined) {
            // Getter
            return $(this).val();
          } else {
            // Setter
            $(this).each(function() {
              if(typeof(data) === 'object' && data !== null) {
                if(data.opacity !== undefined) {
                  $(this).attr('data-opacity', keepWithin(data.opacity, 0, 1));
                }
                if(data.color) {
                  $(this).val(data.color);
                }
              } else {
                $(this).val(data);
              }
              updateFromInput($(this));
            });
          }
          return $(this);

        // Initializes the control
        default:
          if(method !== 'create') data = method;
          $(this).each(function() {
            init($(this), data);
          });
          return $(this);

      }

    }
  });

  // Initialize input elements
  function init(input, settings) {
    var minicolors = $('<div class="minicolors" />');
    var defaults = $.minicolors.defaults;
    var name;
    var size;
    var swatches;
    var swatch;
    var swatchString;
    var panel;
    var i;

    // Do nothing if already initialized
    if(input.data('minicolors-initialized')) return;

    // Handle settings
    settings = $.extend(true, {}, defaults, settings);

    // The wrapper
    minicolors
      .addClass('minicolors-theme-' + settings.theme)
      .toggleClass('minicolors-with-opacity', settings.opacity);

    // Custom positioning
    if(settings.position !== undefined) {
      $.each(settings.position.split(' '), function() {
        minicolors.addClass('minicolors-position-' + this);
      });
    }

    // Input size
    if(settings.format === 'rgb') {
      size = settings.opacity ? '25' : '20';
    } else {
      size = settings.keywords ? '11' : '7';
    }

    // The input
    input
      .addClass('minicolors-input')
      .data('minicolors-initialized', false)
      .data('minicolors-settings', settings)
      .prop('size', size)
      .wrap(minicolors)
      .after(
        '<div class="minicolors-panel minicolors-slider-' + settings.control + '">' +
                '<div class="minicolors-slider minicolors-sprite">' +
                  '<div class="minicolors-picker"></div>' +
                '</div>' +
                '<div class="minicolors-opacity-slider minicolors-sprite">' +
                  '<div class="minicolors-picker"></div>' +
                '</div>' +
                '<div class="minicolors-grid minicolors-sprite">' +
                  '<div class="minicolors-grid-inner"></div>' +
                  '<div class="minicolors-picker"><div></div></div>' +
                '</div>' +
              '</div>'
      );

    // The swatch
    if(!settings.inline) {
      input.after('<span class="minicolors-swatch minicolors-sprite minicolors-input-swatch"><span class="minicolors-swatch-color"></span></span>');
      input.next('.minicolors-input-swatch').on('click', function(event) {
        event.preventDefault();
        input.trigger('focus');
      });
    }

    // Prevent text selection in IE
    panel = input.parent().find('.minicolors-panel');
    panel.on('selectstart', function() { return false; }).end();

    // Swatches
    if(settings.swatches && settings.swatches.length !== 0) {
      panel.addClass('minicolors-with-swatches');
      swatches = $('<ul class="minicolors-swatches"></ul>')
        .appendTo(panel);
      for(i = 0; i < settings.swatches.length; ++i) {
        // allow for custom objects as swatches
        if(typeof settings.swatches[i] === 'object') {
          name = settings.swatches[i].name;
          swatch = settings.swatches[i].color;
        } else {
          name = '';
          swatch = settings.swatches[i];
        }
        swatchString = swatch;
        swatch = isRgb(swatch) ? parseRgb(swatch, true) : hex2rgb(parseHex(swatch, true));
        $('<li class="minicolors-swatch minicolors-sprite"><span class="minicolors-swatch-color"></span></li>')
          .attr("title", name)
          .appendTo(swatches)
          .data('swatch-color', swatchString)
          .find('.minicolors-swatch-color')
          .css({
            backgroundColor: ((swatchString !== 'transparent') ? rgb2hex(swatch) : 'transparent'),
            opacity: String(swatch.a)
          });
        settings.swatches[i] = swatch;
      }
    }

    // Inline controls
    if(settings.inline) input.parent().addClass('minicolors-inline');

    updateFromInput(input, false);

    input.data('minicolors-initialized', true);
  }

  // Returns the input back to its original state
  function destroy(input) {
    var minicolors = input.parent();

    // Revert the input element
    input
      .removeData('minicolors-initialized')
      .removeData('minicolors-settings')
      .removeProp('size')
      .removeClass('minicolors-input');

    // Remove the wrap and destroy whatever remains
    minicolors.before(input).remove();
  }

  // Shows the specified dropdown panel
  function show(input) {
    var minicolors = input.parent();
    var panel = minicolors.find('.minicolors-panel');
    var settings = input.data('minicolors-settings');

    // Do nothing if uninitialized, disabled, inline, or already open
    if(
      !input.data('minicolors-initialized') ||
      input.prop('disabled') ||
      minicolors.hasClass('minicolors-inline') ||
      minicolors.hasClass('minicolors-focus')
    ) return;

    hide();

    minicolors.addClass('minicolors-focus');
    if (panel.animate) {
      panel
        .stop(true, true)
        .fadeIn(settings.showSpeed, function () {
          if (settings.show) settings.show.call(input.get(0));
        });
    } else {
      panel.show();
      if (settings.show) settings.show.call(input.get(0));
    }
  }

  // Hides all dropdown panels
  function hide() {
    $('.minicolors-focus').each(function() {
      var minicolors = $(this);
      var input = minicolors.find('.minicolors-input');
      var panel = minicolors.find('.minicolors-panel');
      var settings = input.data('minicolors-settings');

      if (panel.animate) {
        panel.fadeOut(settings.hideSpeed, function () {
          if (settings.hide) settings.hide.call(input.get(0));
          minicolors.removeClass('minicolors-focus');
        });
      } else {
        panel.hide();
        if (settings.hide) settings.hide.call(input.get(0));
        minicolors.removeClass('minicolors-focus');
      }
    });
  }

  // Moves the selected picker
  function move(target, event, animate) {
    var input = target.parents('.minicolors').find('.minicolors-input');
    var settings = input.data('minicolors-settings');
    var picker = target.find('[class$=-picker]');
    var offsetX = target.offset().left;
    var offsetY = target.offset().top;
    var x = Math.round(event.pageX - offsetX);
    var y = Math.round(event.pageY - offsetY);
    var duration = animate ? settings.animationSpeed : 0;
    var wx, wy, r, phi, styles;

    // Touch support
    if(event.originalEvent.changedTouches) {
      x = event.originalEvent.changedTouches[0].pageX - offsetX;
      y = event.originalEvent.changedTouches[0].pageY - offsetY;
    }

    // Constrain picker to its container
    if(x < 0) x = 0;
    if(y < 0) y = 0;
    if(x > target.width()) x = target.width();
    if(y > target.height()) y = target.height();

    // Constrain color wheel values to the wheel
    if(target.parent().is('.minicolors-slider-wheel') && picker.parent().is('.minicolors-grid')) {
      wx = 75 - x;
      wy = 75 - y;
      r = Math.sqrt(wx * wx + wy * wy);
      phi = Math.atan2(wy, wx);
      if(phi < 0) phi += Math.PI * 2;
      if(r > 75) {
        r = 75;
        x = 75 - (75 * Math.cos(phi));
        y = 75 - (75 * Math.sin(phi));
      }
      x = Math.round(x);
      y = Math.round(y);
    }

    // Move the picker
    styles = {
      top: y + 'px'
    };
    if(target.is('.minicolors-grid')) {
      styles.left = x + 'px';
    }
    if (picker.animate) {
      picker
        .stop(true)
        .animate(styles, duration, settings.animationEasing, function() {
          updateFromControl(input, target);
        });
    } else {
      picker
        .css(styles);
      updateFromControl(input, target);
    }
  }

  // Sets the input based on the color picker values
  function updateFromControl(input, target) {

    function getCoords(picker, container) {
      var left, top;
      if(!picker.length || !container) return null;
      left = picker.offset().left;
      top = picker.offset().top;

      return {
        x: left - container.offset().left + (picker.outerWidth() / 2),
        y: top - container.offset().top + (picker.outerHeight() / 2)
      };
    }

    var hue, saturation, brightness, x, y, r, phi;
    var hex = input.val();
    var opacity = input.attr('data-opacity');

    // Helpful references
    var minicolors = input.parent();
    var settings = input.data('minicolors-settings');
    var swatch = minicolors.find('.minicolors-input-swatch');

    // Panel objects
    var grid = minicolors.find('.minicolors-grid');
    var slider = minicolors.find('.minicolors-slider');
    var opacitySlider = minicolors.find('.minicolors-opacity-slider');

    // Picker objects
    var gridPicker = grid.find('[class$=-picker]');
    var sliderPicker = slider.find('[class$=-picker]');
    var opacityPicker = opacitySlider.find('[class$=-picker]');

    // Picker positions
    var gridPos = getCoords(gridPicker, grid);
    var sliderPos = getCoords(sliderPicker, slider);
    var opacityPos = getCoords(opacityPicker, opacitySlider);

    // Handle colors
    if(target.is('.minicolors-grid, .minicolors-slider, .minicolors-opacity-slider')) {

      // Determine HSB values
      switch(settings.control) {
        case 'wheel':
          // Calculate hue, saturation, and brightness
          x = (grid.width() / 2) - gridPos.x;
          y = (grid.height() / 2) - gridPos.y;
          r = Math.sqrt(x * x + y * y);
          phi = Math.atan2(y, x);
          if(phi < 0) phi += Math.PI * 2;
          if(r > 75) {
            r = 75;
            gridPos.x = 69 - (75 * Math.cos(phi));
            gridPos.y = 69 - (75 * Math.sin(phi));
          }
          saturation = keepWithin(r / 0.75, 0, 100);
          hue = keepWithin(phi * 180 / Math.PI, 0, 360);
          brightness = keepWithin(100 - Math.floor(sliderPos.y * (100 / slider.height())), 0, 100);
          hex = hsb2hex({
            h: hue,
            s: saturation,
            b: brightness
          });

          // Update UI
          slider.css('backgroundColor', hsb2hex({ h: hue, s: saturation, b: 100 }));
          break;

        case 'saturation':
          // Calculate hue, saturation, and brightness
          hue = keepWithin(parseInt(gridPos.x * (360 / grid.width()), 10), 0, 360);
          saturation = keepWithin(100 - Math.floor(sliderPos.y * (100 / slider.height())), 0, 100);
          brightness = keepWithin(100 - Math.floor(gridPos.y * (100 / grid.height())), 0, 100);
          hex = hsb2hex({
            h: hue,
            s: saturation,
            b: brightness
          });

          // Update UI
          slider.css('backgroundColor', hsb2hex({ h: hue, s: 100, b: brightness }));
          minicolors.find('.minicolors-grid-inner').css('opacity', saturation / 100);
          break;

        case 'brightness':
          // Calculate hue, saturation, and brightness
          hue = keepWithin(parseInt(gridPos.x * (360 / grid.width()), 10), 0, 360);
          saturation = keepWithin(100 - Math.floor(gridPos.y * (100 / grid.height())), 0, 100);
          brightness = keepWithin(100 - Math.floor(sliderPos.y * (100 / slider.height())), 0, 100);
          hex = hsb2hex({
            h: hue,
            s: saturation,
            b: brightness
          });

          // Update UI
          slider.css('backgroundColor', hsb2hex({ h: hue, s: saturation, b: 100 }));
          minicolors.find('.minicolors-grid-inner').css('opacity', 1 - (brightness / 100));
          break;

        default:
          // Calculate hue, saturation, and brightness
          hue = keepWithin(360 - parseInt(sliderPos.y * (360 / slider.height()), 10), 0, 360);
          saturation = keepWithin(Math.floor(gridPos.x * (100 / grid.width())), 0, 100);
          brightness = keepWithin(100 - Math.floor(gridPos.y * (100 / grid.height())), 0, 100);
          hex = hsb2hex({
            h: hue,
            s: saturation,
            b: brightness
          });

          // Update UI
          grid.css('backgroundColor', hsb2hex({ h: hue, s: 100, b: 100 }));
          break;
      }

      // Handle opacity
      if(settings.opacity) {
        opacity = parseFloat(1 - (opacityPos.y / opacitySlider.height())).toFixed(2);
      } else {
        opacity = 1;
      }

      updateInput(input, hex, opacity);
    }
    else {
      // Set swatch color
      swatch.find('span').css({
        backgroundColor: hex,
        opacity: String(opacity)
      });

      // Handle change event
      doChange(input, hex, opacity);
    }
  }

  // Sets the value of the input and does the appropriate conversions
  // to respect settings, also updates the swatch
  function updateInput(input, value, opacity) {
    var rgb;

    // Helpful references
    var minicolors = input.parent();
    var settings = input.data('minicolors-settings');
    var swatch = minicolors.find('.minicolors-input-swatch');

    if(settings.opacity) input.attr('data-opacity', opacity);

    // Set color string
    if(settings.format === 'rgb') {
      // Returns RGB(A) string

      // Checks for input format and does the conversion
      if(isRgb(value)) {
        rgb = parseRgb(value, true);
      }
      else {
        rgb = hex2rgb(parseHex(value, true));
      }

      opacity = input.attr('data-opacity') === '' ? 1 : keepWithin(parseFloat(input.attr('data-opacity')).toFixed(2), 0, 1);
      if(isNaN(opacity) || !settings.opacity) opacity = 1;

      if(input.minicolors('rgbObject').a <= 1 && rgb && settings.opacity) {
        // Set RGBA string if alpha
        value = 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', ' + parseFloat(opacity) + ')';
      } else {
        // Set RGB string (alpha = 1)
        value = 'rgb(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ')';
      }
    } else {
      // Returns hex color

      // Checks for input format and does the conversion
      if(isRgb(value)) {
        value = rgbString2hex(value);
      }

      value = convertCase(value, settings.letterCase);
    }

    // Update value from picker
    input.val(value);

    // Set swatch color
    swatch.find('span').css({
      backgroundColor: value,
      opacity: String(opacity)
    });

    // Handle change event
    doChange(input, value, opacity);
  }

  // Sets the color picker values from the input
  function updateFromInput(input, preserveInputValue) {
    var hex, hsb, opacity, keywords, alpha, value, x, y, r, phi;

    // Helpful references
    var minicolors = input.parent();
    var settings = input.data('minicolors-settings');
    var swatch = minicolors.find('.minicolors-input-swatch');

    // Panel objects
    var grid = minicolors.find('.minicolors-grid');
    var slider = minicolors.find('.minicolors-slider');
    var opacitySlider = minicolors.find('.minicolors-opacity-slider');

    // Picker objects
    var gridPicker = grid.find('[class$=-picker]');
    var sliderPicker = slider.find('[class$=-picker]');
    var opacityPicker = opacitySlider.find('[class$=-picker]');

    // Determine hex/HSB values
    if(isRgb(input.val())) {
      // If input value is a rgb(a) string, convert it to hex color and update opacity
      hex = rgbString2hex(input.val());
      alpha = keepWithin(parseFloat(getAlpha(input.val())).toFixed(2), 0, 1);
      if(alpha) {
        input.attr('data-opacity', alpha);
      }
    } else {
      hex = convertCase(parseHex(input.val(), true), settings.letterCase);
    }

    if(!hex){
      hex = convertCase(parseInput(settings.defaultValue, true), settings.letterCase);
    }
    hsb = hex2hsb(hex);

    // Get array of lowercase keywords
    keywords = !settings.keywords ? [] : $.map(settings.keywords.split(','), function(a) {
      return a.toLowerCase().trim();
    });

    // Set color string
    if(input.val() !== '' && $.inArray(input.val().toLowerCase(), keywords) > -1) {
      value = convertCase(input.val());
    } else {
      value = isRgb(input.val()) ? parseRgb(input.val()) : hex;
    }

    // Update input value
    if(!preserveInputValue) input.val(value);

    // Determine opacity value
    if(settings.opacity) {
      // Get from data-opacity attribute and keep within 0-1 range
      opacity = input.attr('data-opacity') === '' ? 1 : keepWithin(parseFloat(input.attr('data-opacity')).toFixed(2), 0, 1);
      if(isNaN(opacity)) opacity = 1;
      input.attr('data-opacity', opacity);
      swatch.find('span').css('opacity', String(opacity));

      // Set opacity picker position
      y = keepWithin(opacitySlider.height() - (opacitySlider.height() * opacity), 0, opacitySlider.height());
      opacityPicker.css('top', y + 'px');
    }

    // Set opacity to zero if input value is transparent
    if(input.val().toLowerCase() === 'transparent') {
      swatch.find('span').css('opacity', String(0));
    }

    // Update swatch
    swatch.find('span').css('backgroundColor', hex);

    // Determine picker locations
    switch(settings.control) {
      case 'wheel':
        // Set grid position
        r = keepWithin(Math.ceil(hsb.s * 0.75), 0, grid.height() / 2);
        phi = hsb.h * Math.PI / 180;
        x = keepWithin(75 - Math.cos(phi) * r, 0, grid.width());
        y = keepWithin(75 - Math.sin(phi) * r, 0, grid.height());
        gridPicker.css({
          top: y + 'px',
          left: x + 'px'
        });

        // Set slider position
        y = 150 - (hsb.b / (100 / grid.height()));
        if(hex === '') y = 0;
        sliderPicker.css('top', y + 'px');
        
        // Update panel color
        slider.css('backgroundColor', hsb2hex({ h: hsb.h, s: hsb.s, b: 100 }));
        break;

      case 'saturation':
        // Set grid position
        x = keepWithin((5 * hsb.h) / 12, 0, 150);
        y = keepWithin(grid.height() - Math.ceil(hsb.b / (100 / grid.height())), 0, grid.height());
        gridPicker.css({
          top: y + 'px',
          left: x + 'px'
        });

        // Set slider position
        y = keepWithin(slider.height() - (hsb.s * (slider.height() / 100)), 0, slider.height());
        sliderPicker.css('top', y + 'px');

        // Update UI
        slider.css('backgroundColor', hsb2hex({ h: hsb.h, s: 100, b: hsb.b }));
        minicolors.find('.minicolors-grid-inner').css('opacity', hsb.s / 100);
        break;

      case 'brightness':
        // Set grid position
        x = keepWithin((5 * hsb.h) / 12, 0, 150);
        y = keepWithin(grid.height() - Math.ceil(hsb.s / (100 / grid.height())), 0, grid.height());
        gridPicker.css({
          top: y + 'px',
          left: x + 'px'
        });

        // Set slider position
        y = keepWithin(slider.height() - (hsb.b * (slider.height() / 100)), 0, slider.height());
        sliderPicker.css('top', y + 'px');

        // Update UI
        slider.css('backgroundColor', hsb2hex({ h: hsb.h, s: hsb.s, b: 100 }));
        minicolors.find('.minicolors-grid-inner').css('opacity', 1 - (hsb.b / 100));
        break;

      default:
        // Set grid position
        x = keepWithin(Math.ceil(hsb.s / (100 / grid.width())), 0, grid.width());
        y = keepWithin(grid.height() - Math.ceil(hsb.b / (100 / grid.height())), 0, grid.height());
        gridPicker.css({
          top: y + 'px',
          left: x + 'px'
        });

        // Set slider position
        y = keepWithin(slider.height() - (hsb.h / (360 / slider.height())), 0, slider.height());
        sliderPicker.css('top', y + 'px');

        // Update panel color
        grid.css('backgroundColor', hsb2hex({ h: hsb.h, s: 100, b: 100 }));
        break;
    }

    // Fire change event, but only if minicolors is fully initialized
    if(input.data('minicolors-initialized')) {
      doChange(input, value, opacity);
    }
  }

  // Runs the change and changeDelay callbacks
  function doChange(input, value, opacity) {
    var settings = input.data('minicolors-settings');
    var lastChange = input.data('minicolors-lastChange');
    var obj, sel, i;

    // Only run if it actually changed
    if(!lastChange || lastChange.value !== value || lastChange.opacity !== opacity) {

      // Remember last-changed value
      input.data('minicolors-lastChange', {
        value: value,
        opacity: opacity
      });

      // Check and select applicable swatch
      if(settings.swatches && settings.swatches.length !== 0) {
        if(!isRgb(value)) {
          obj = hex2rgb(value);
        }
        else {
          obj = parseRgb(value, true);
        }
        sel = -1;
        for(i = 0; i < settings.swatches.length; ++i) {
          if(obj.r === settings.swatches[i].r && obj.g === settings.swatches[i].g && obj.b === settings.swatches[i].b && obj.a === settings.swatches[i].a) {
            sel = i;
            break;
          }
        }

        input.parent().find('.minicolors-swatches .minicolors-swatch').removeClass('selected');
        if(sel !== -1) {
          input.parent().find('.minicolors-swatches .minicolors-swatch').eq(i).addClass('selected');
        }
      }

      // Fire change event
      if(settings.change) {
        if(settings.changeDelay) {
          // Call after a delay
          clearTimeout(input.data('minicolors-changeTimeout'));
          input.data('minicolors-changeTimeout', setTimeout(function() {
            settings.change.call(input.get(0), value, opacity);
          }, settings.changeDelay));
        } else {
          // Call immediately
          settings.change.call(input.get(0), value, opacity);
        }
      }
      input.trigger('change').trigger('input');
    }
  }

  // Generates an RGB(A) object based on the input's value
  function rgbObject(input) {
    var rgb,
      opacity = $(input).attr('data-opacity');
    if( isRgb($(input).val()) ) {
      rgb = parseRgb($(input).val(), true);
    } else {
      var hex = parseHex($(input).val(), true);
      rgb = hex2rgb(hex);
    }
    if( !rgb ) return null;
    if( opacity !== undefined ) $.extend(rgb, { a: parseFloat(opacity) });
    return rgb;
  }

  // Generates an RGB(A) string based on the input's value
  function rgbString(input, alpha) {
    var rgb,
      opacity = $(input).attr('data-opacity');
    if( isRgb($(input).val()) ) {
      rgb = parseRgb($(input).val(), true);
    } else {
      var hex = parseHex($(input).val(), true);
      rgb = hex2rgb(hex);
    }
    if( !rgb ) return null;
    if( opacity === undefined ) opacity = 1;
    if( alpha ) {
      return 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', ' + parseFloat(opacity) + ')';
    } else {
      return 'rgb(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ')';
    }
  }

  // Converts to the letter case specified in settings
  function convertCase(string, letterCase) {
    return letterCase === 'uppercase' ? string.toUpperCase() : string.toLowerCase();
  }

  // Parses a string and returns a valid hex string when possible
  function parseHex(string, expand) {
    string = string.replace(/^#/g, '');
    if(!string.match(/^[A-F0-9]{3,6}/ig)) return '';
    if(string.length !== 3 && string.length !== 6) return '';
    if(string.length === 3 && expand) {
      string = string[0] + string[0] + string[1] + string[1] + string[2] + string[2];
    }
    return '#' + string;
  }

  // Parses a string and returns a valid RGB(A) string when possible
  function parseRgb(string, obj) {
    var values = string.replace(/[^\d,.]/g, '');
    var rgba = values.split(',');

    rgba[0] = keepWithin(parseInt(rgba[0], 10), 0, 255);
    rgba[1] = keepWithin(parseInt(rgba[1], 10), 0, 255);
    rgba[2] = keepWithin(parseInt(rgba[2], 10), 0, 255);
    if(rgba[3] !== undefined) {
      rgba[3] = keepWithin(parseFloat(rgba[3], 10), 0, 1);
    }

    // Return RGBA object
    if( obj ) {
      if (rgba[3] !== undefined) {
        return {
          r: rgba[0],
          g: rgba[1],
          b: rgba[2],
          a: rgba[3]
        };
      } else {
        return {
          r: rgba[0],
          g: rgba[1],
          b: rgba[2]
        };
      }
    }

    // Return RGBA string
    if(typeof(rgba[3]) !== 'undefined' && rgba[3] <= 1) {
      return 'rgba(' + rgba[0] + ', ' + rgba[1] + ', ' + rgba[2] + ', ' + rgba[3] + ')';
    } else {
      return 'rgb(' + rgba[0] + ', ' + rgba[1] + ', ' + rgba[2] + ')';
    }

  }

  // Parses a string and returns a valid color string when possible
  function parseInput(string, expand) {
    if(isRgb(string)) {
      // Returns a valid rgb(a) string
      return parseRgb(string);
    } else {
      return parseHex(string, expand);
    }
  }

  // Keeps value within min and max
  function keepWithin(value, min, max) {
    if(value < min) value = min;
    if(value > max) value = max;
    return value;
  }

  // Checks if a string is a valid RGB(A) string
  function isRgb(string) {
    var rgb = string.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
    return (rgb && rgb.length === 4) ? true : false;
  }

  // Function to get alpha from a RGB(A) string
  function getAlpha(rgba) {
    rgba = rgba.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+(\.\d{1,2})?|\.\d{1,2})[\s+]?/i);
    return (rgba && rgba.length === 6) ? rgba[4] : '1';
  }

  // Converts an HSB object to an RGB object
  function hsb2rgb(hsb) {
    var rgb = {};
    var h = Math.round(hsb.h);
    var s = Math.round(hsb.s * 255 / 100);
    var v = Math.round(hsb.b * 255 / 100);
    if(s === 0) {
      rgb.r = rgb.g = rgb.b = v;
    } else {
      var t1 = v;
      var t2 = (255 - s) * v / 255;
      var t3 = (t1 - t2) * (h % 60) / 60;
      if(h === 360) h = 0;
      if(h < 60) { rgb.r = t1; rgb.b = t2; rgb.g = t2 + t3; }
      else if(h < 120) {rgb.g = t1; rgb.b = t2; rgb.r = t1 - t3; }
      else if(h < 180) {rgb.g = t1; rgb.r = t2; rgb.b = t2 + t3; }
      else if(h < 240) {rgb.b = t1; rgb.r = t2; rgb.g = t1 - t3; }
      else if(h < 300) {rgb.b = t1; rgb.g = t2; rgb.r = t2 + t3; }
      else if(h < 360) {rgb.r = t1; rgb.g = t2; rgb.b = t1 - t3; }
      else { rgb.r = 0; rgb.g = 0; rgb.b = 0; }
    }
    return {
      r: Math.round(rgb.r),
      g: Math.round(rgb.g),
      b: Math.round(rgb.b)
    };
  }

  // Converts an RGB string to a hex string
  function rgbString2hex(rgb){
    rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
    return (rgb && rgb.length === 4) ? '#' +
      ('0' + parseInt(rgb[1],10).toString(16)).slice(-2) +
      ('0' + parseInt(rgb[2],10).toString(16)).slice(-2) +
      ('0' + parseInt(rgb[3],10).toString(16)).slice(-2) : '';
  }

  // Converts an RGB object to a hex string
  function rgb2hex(rgb) {
    var hex = [
      rgb.r.toString(16),
      rgb.g.toString(16),
      rgb.b.toString(16)
    ];
    $.each(hex, function(nr, val) {
      if(val.length === 1) hex[nr] = '0' + val;
    });
    return '#' + hex.join('');
  }

  // Converts an HSB object to a hex string
  function hsb2hex(hsb) {
    return rgb2hex(hsb2rgb(hsb));
  }

  // Converts a hex string to an HSB object
  function hex2hsb(hex) {
    var hsb = rgb2hsb(hex2rgb(hex));
    if(hsb.s === 0) hsb.h = 360;
    return hsb;
  }

  // Converts an RGB object to an HSB object
  function rgb2hsb(rgb) {
    var hsb = { h: 0, s: 0, b: 0 };
    var min = Math.min(rgb.r, rgb.g, rgb.b);
    var max = Math.max(rgb.r, rgb.g, rgb.b);
    var delta = max - min;
    hsb.b = max;
    hsb.s = max !== 0 ? 255 * delta / max : 0;
    if(hsb.s !== 0) {
      if(rgb.r === max) {
        hsb.h = (rgb.g - rgb.b) / delta;
      } else if(rgb.g === max) {
        hsb.h = 2 + (rgb.b - rgb.r) / delta;
      } else {
        hsb.h = 4 + (rgb.r - rgb.g) / delta;
      }
    } else {
      hsb.h = -1;
    }
    hsb.h *= 60;
    if(hsb.h < 0) {
      hsb.h += 360;
    }
    hsb.s *= 100/255;
    hsb.b *= 100/255;
    return hsb;
  }

  // Converts a hex string to an RGB object
  function hex2rgb(hex) {
    hex = parseInt(((hex.indexOf('#') > -1) ? hex.substring(1) : hex), 16);
    return {
      r: hex >> 16,
      g: (hex & 0x00FF00) >> 8,
      b: (hex & 0x0000FF)
    };
  }

  // Handle events
  $([document])
    // Hide on clicks outside of the control
    .on('mousedown.minicolors touchstart.minicolors', function(event) {
      if(!$(event.target).parents().add(event.target).hasClass('minicolors')) {
        hide();
      }
    })
    // Start moving
    .on('mousedown.minicolors touchstart.minicolors', '.minicolors-grid, .minicolors-slider, .minicolors-opacity-slider', function(event) {
      var target = $(this);
      event.preventDefault();
      $(event.delegateTarget).data('minicolors-target', target);
      move(target, event, true);
    })
    // Move pickers
    .on('mousemove.minicolors touchmove.minicolors', function(event) {
      var target = $(event.delegateTarget).data('minicolors-target');
      if(target) move(target, event);
    })
    // Stop moving
    .on('mouseup.minicolors touchend.minicolors', function() {
      $(this).removeData('minicolors-target');
    })
    // Selected a swatch
    .on('click.minicolors', '.minicolors-swatches li', function(event) {
      event.preventDefault();
      var target = $(this), input = target.parents('.minicolors').find('.minicolors-input'), color = target.data('swatch-color');
      updateInput(input, color, getAlpha(color));
      updateFromInput(input);
    })
    // Show panel when swatch is clicked
    .on('mousedown.minicolors touchstart.minicolors', '.minicolors-input-swatch', function(event) {
      var input = $(this).parent().find('.minicolors-input');
      event.preventDefault();
      show(input);
    })
    // Show on focus
    .on('focus.minicolors', '.minicolors-input', function() {
      var input = $(this);
      if(!input.data('minicolors-initialized')) return;
      show(input);
    })
    // Update value on blur
    .on('blur.minicolors', '.minicolors-input', function() {
      var input = $(this);
      var settings = input.data('minicolors-settings');
      var keywords;
      var hex;
      var rgba;
      var swatchOpacity;
      var value;

      if(!input.data('minicolors-initialized')) return;

      // Get array of lowercase keywords
      keywords = !settings.keywords ? [] : $.map(settings.keywords.split(','), function(a) {
        return a.toLowerCase().trim();
      });

      // Set color string
      if(input.val() !== '' && $.inArray(input.val().toLowerCase(), keywords) > -1) {
        value = input.val();
      } else {
        // Get RGBA values for easy conversion
        if(isRgb(input.val())) {
          rgba = parseRgb(input.val(), true);
        } else {
          hex = parseHex(input.val(), true);
          rgba = hex ? hex2rgb(hex) : null;
        }

        // Convert to format
        if(rgba === null) {
          value = settings.defaultValue;
        } else if(settings.format === 'rgb') {
          value = settings.opacity ?
            parseRgb('rgba(' + rgba.r + ',' + rgba.g + ',' + rgba.b + ',' + input.attr('data-opacity') + ')') :
            parseRgb('rgb(' + rgba.r + ',' + rgba.g + ',' + rgba.b + ')');
        } else {
          value = rgb2hex(rgba);
        }
      }

      // Update swatch opacity
      swatchOpacity = settings.opacity ? input.attr('data-opacity') : 1;
      if(value.toLowerCase() === 'transparent') swatchOpacity = 0;
      input
        .closest('.minicolors')
        .find('.minicolors-input-swatch > span')
        .css('opacity', String(swatchOpacity));

      // Set input value
      input.val(value);

      // Is it blank?
      if(input.val() === '') input.val(parseInput(settings.defaultValue, true));

      // Adjust case
      input.val(convertCase(input.val(), settings.letterCase));

    })
    // Handle keypresses
    .on('keydown.minicolors', '.minicolors-input', function(event) {
      var input = $(this);
      if(!input.data('minicolors-initialized')) return;
      switch(event.which) {
        case 9: // tab
          hide();
          break;
        case 13: // enter
        case 27: // esc
          hide();
          input.blur();
          break;
      }
    })
    // Update on keyup
    .on('keyup.minicolors', '.minicolors-input', function() {
      var input = $(this);
      if(!input.data('minicolors-initialized')) return;
      updateFromInput(input, true);
    })
    // Update on paste
    .on('paste.minicolors', '.minicolors-input', function() {
      var input = $(this);
      if(!input.data('minicolors-initialized')) return;
      setTimeout(function() {
        updateFromInput(input, true);
      }, 1);
    });
}));

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