$(document).on("cmck", function (e, params) {

    switch (params.type) {
        case 'editForm.init':
        case 'sequenceForm.init':
        case 'sequenceForm.refresh':
            $('div.formelement-richtext textarea').each(function () {

                id = $(this).attr('id');
                rows = $(this).attr('rows');
                h = 90 + rows * 18;

                readonly = false;
                if($(this).attr('disabled')=='disabled'){
                    readonly = true;
                }

                tinymce.init({
                    selector: '#' + id, height: h, setup: function (editor) {
                        editor.on('change', function () {
                            tinymce.triggerSave();
                        })

                    },
                    //content_css: "/css/tinymce.css",
                    readonly: readonly,
                    plugins: ["code", "link", "anchor"], //"paste"],
                    toolbar: "undo redo | bold italic | bullist numlist | link unlink anchor | styleselect |  alignleft aligncenter alignright alignjustify | indent outdent | removeformat | code  ",

                    // http://www.tinymce.com/wiki.php/Configuration:style_formats

                    style_formats: [

                        {
                            title: "Headers", items: [
                            {title: "Header 1", format: "h1"},
                            {title: "Header 2", format: "h2"},
                            {title: "Header 3", format: "h3"},
                            {title: "Header 4", format: "h4"}
                        ]
                        },
                        {
                            title: "Inline", items: [
                            {title: "Bold", icon: "bold", format: "bold"},
                            {title: "Italic", icon: "italic", format: "italic"},
                            {title: "Underline", icon: "underline", format: "underline"},
                            {title: "Strikethrough", icon: "strikethrough", format: "strikethrough"},
                            {title: "Superscript", icon: "superscript", format: "superscript"},
                            {title: "Subscript", icon: "subscript", format: "subscript"},
                            {title: "Code", icon: "code", format: "code"}
                        ]
                        },
                        {
                            title: "Blocks", items: [
                            {title: "Paragraph", format: "p"},
                            {title: "Blockquote", format: "blockquote"},
                            {title: "Div", format: "div"},
                            {title: "Pre", format: "pre"}
                        ]
                        },
                        {
                            title: "Alignment", items: [
                            {title: "Left", icon: "alignleft", format: "alignleft"},
                            {title: "Center", icon: "aligncenter", format: "aligncenter"},
                            {title: "Right", icon: "alignright", format: "alignright"},
                            {title: "Justify", icon: "alignjustify", format: "alignjustify"}
                        ]
                        }
                    ]
                });

            });
            break;
    }
});
