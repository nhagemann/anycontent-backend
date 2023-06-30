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