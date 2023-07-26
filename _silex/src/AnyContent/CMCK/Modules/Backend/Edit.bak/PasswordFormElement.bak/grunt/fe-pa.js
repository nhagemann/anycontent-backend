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