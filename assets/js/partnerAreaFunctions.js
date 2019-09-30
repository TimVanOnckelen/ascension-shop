(function($){


    $(document).on("ready",function () {

        $(".edit-user").on("click",function (e) {

            e.preventDefault();
            let id = $(this).attr("data-id");

            $("#edit-user-"+id).show();
            $("#info-user-"+id).hide();

        });

        $(".downloadOverview").on("click",function(){

            /*
            html2canvas(document.getElementById("affwp-affiliate-dashboard-referrals-table"),{scale: '1',async: false}).then(function(canvas) {
                var imgData = canvas.toDataURL("image/png", 1.0);
                var pdf = new jsPDF();

                pdf.addImage(imgData, 'PNG', 0, 0);
                pdf.save("download.pdf");
            });

*/
            $("#affwp-table").removeClass("affwp-table-responsive");

           $(".printArea").printThis(
               {
                   importCSS: true,
                   importStyle: true,
                   debug: true
               }
           );

        });

    });



})(jQuery);
