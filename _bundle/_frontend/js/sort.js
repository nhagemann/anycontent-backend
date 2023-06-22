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