(function ($) {

    $.fn.cmck_fe_table = function () {


        var reset = function () {
            $('.formelement-table a').off();
        };

        var init = function () {

            $('.formelement-table tbody').sortable({
                handle: '.handle'
            });


            //$('.formelement-table textarea').autosize();

            // somebody clicked into the cell and missed the textarea
            $('.formelement-table td').click(function () {
                var textarea = $(this).children('textarea');
                textarea.focus();
            });

            $('.formelement-table a[data-action=plus]').click(function () {

                var r = getSelectedRow(this);
                var name = getTableName(this);
                addRow(name, r);
                return false;
            });

            $('.formelement-table a[data-action=minus]').click(function () {

                var r = getSelectedRow(this);
                var name = getTableName(this);
                deleteRow(name, r);
                return false;

            });

            $('.formelement-table textarea').keydown(function (e) {


                if (e.keyCode === 9) {

                    var row = $(this).closest('tr').nextAll().length;
                    var col = $(this).closest('td').nextAll().length;

                    if (row == 0 && col == 1) {

                        var r = getSelectedRow(this);
                        var name = getTableName(this);
                        addRow(name, r);
                    }

                }
            });

            $('.formelement-table table').each(function (index) {
                hideMinusButtonsIfNecessary($(this).attr('name'));
            })

        };


        var getSelectedRow = function (o) {
            var table = $(o).closest('table');
            var row = $(o).closest('tr');
            var rows = $(table).find('tr');
            return ($(rows).index(row));
        };

        var getTableName = function (o) {
            var table = $(o).closest('table');

            return ($(table).attr('name'));
        };

        var addRow = function (name, r) {

            var count = parseInt($('table[name=' + name + ']').attr('data-rows'));
            $('table[name=' + name + ']').attr('data-rows', ++count);

            var nrOfColumns = $('table[name=' + name + '] th').length - 2;

            var html = '<tr><td class="handle" width="2%">' + count + '</td>';

            html += Array(nrOfColumns + 1).join('<td><textarea name="' + name + '[]"></textarea></td>');

            html += '<td width="3%"><a class="btn btn-default btn-xs" href="#" tabindex="-1" data-action="plus">+</button></a>  <a class="btn btn-default btn-xs" href="#" tabindex="-1" data-action="minus">-</button></a></td></tr>';


            var rows = ($('table[name=' + name + ']').find('tr'));
            row = rows[r];
            $(row).after(html);
            reset();
            init();
        };

        var deleteRow = function (name, r) {
            var rows = ($('table[name=' + name + ']').find('tr'));
            row = rows[r];
            $(row).remove();
            hideMinusButtonsIfNecessary(name);
        };


        var hideMinusButtonsIfNecessary = function (name){
            var minusButtons = $('table[name=' + name + '] a[data-action=minus]');

            $(minusButtons).each(function (index) {
                $(this).show();
            });
            if (minusButtons.length == 1) {
                $(minusButtons[0]).hide();
            }
        };

        $(document).on("cmck", function (e, params) {


            switch (params.type) {


                case 'editForm.init':

                    //init();
                    break;
            }
        });
        init();

    };

    $(document).ready(function () {
        $(document).cmck_fe_table();
    });

    /* ---------------------------------------- */
})
(jQuery);
