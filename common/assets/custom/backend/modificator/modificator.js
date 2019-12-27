/**
 * Created by anatoliypopov on 07.11.15.
 */

$(document).ready(function() {

    var dell_catalog_item = function () {

        var item_name = $(this).data('item-name');
        var item_id = $(this).data('item-id');

        swal({

                title: "Удалить модификатор?",
                text: "" + item_name,
                type: "warning",
                showCancelButton: true,
                closeOnConfirm: false,
                animation: "slide-from-top",
                showLoaderOnConfirm: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Удалить",
                cancelButtonText: "Отмена"


            },
            function () {

                var csrfToken = $('meta[name="csrf-token"]').attr("content");
                $.ajax({
                    type: "post",
                    url: "/modificator/item-dell.html",
                    data: {
                        'item_id': item_id,
                        _csrf: csrfToken
                    },
                    success: function (json) {


                        if (json.error) {
                            swal("Error", "%(", "error");
                        }
                        else {
                            $("#item_" + json.item_id).remove();

                            swal("Deleted!", "\n", "success");
                        }


                    },
                    dataType: 'json'
                });

                //  swal("Nice!", "You wrote: " + inputValue, "success");
            });

        return false;

    };


    $('.item-price').editable();
    $('.dell-catalog-item').click(dell_catalog_item);



    $( "#sortable" ).sortable({
        update: function( event, ui ) {
            var data = $(this).sortable('toArray',{ attribute: "sort-id" });

            var csrfToken = $('meta[name="csrf-token"]').attr("content");
            $.ajax({
                type: "POST",
                url:"/modificator/item-sort.html",
                data: {
                    item_order:data,
                    _csrf : csrfToken
                },
                success: function(json){


                    if(json.error){
                        swal("Error", "%(", "error");
                    }



                },
                dataType: 'json'
            });
        }
    });
});
