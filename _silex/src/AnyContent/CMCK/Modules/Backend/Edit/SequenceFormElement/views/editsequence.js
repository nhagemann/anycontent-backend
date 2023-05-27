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



