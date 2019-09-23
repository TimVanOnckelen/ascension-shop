(function($){


    $(document).on("ready",function () {

        $(".edit-user").on("click",function (e) {

            e.preventDefault();
            let id = $(this).attr("data-id");

            $("#edit-user-"+id).show();
            $("#info-user-"+id).hide();

        });

    });



})(jQuery);
