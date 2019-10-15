(function($){

    $(document).on("ready",function () {

        $("body").on("click",".edit-user",function (e) {

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

        let theTable =  $(partnerArea.tableId).DataTable(
            {
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    'url' : partnerArea.url,
                    'type' : "GET",
                    'beforeSend': function ( xhr ) {
                        xhr.setRequestHeader( 'X-WP-Nonce', partnerArea.nonce );
                    },
                    "complete": function () {
                        $("[name='_wp_http_referer']").val(partnerArea.referer);
                    }
                },
                columns: [
                    {"data": "id"},
                    {"data": "name"},
                    {"data": "info"},
                    {"data" : "partner"},
                    {"data" : "id",render: function (dataField) {
                            return '<a href="#" class="edit-user" data-id="'+dataField+'">'+partnerArea.editText+'</a>';
                        }},
                    {"data": "discount"}
                ],
            }
        );

        theTable.columns().every( function () {
            var that = this;

            $("select#searchByPartner").on('change', function () {
                if (that.search() !== this.value) {
                    that
                        .search(this.value)
                        .draw();
                }
            });

        });


        $("body").on("submit",".editUser",function (e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            let id = $(this).attr("data-id");

            $(this).hide();
            $("#info-user-"+id).before("<div id='userEditLoading'><b>"+partnerArea.savingText+"</b></div>");

            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(), // serializes the form's elements.
                success: function(data)
                {
                    $("#userEditLoading").remove();

                    Swal.fire({
                        title: partnerArea.successTextTitle,
                        text: partnerArea.successText,
                        type: 'success',
                        confirmButtonText: 'Ok'
                    });

                    if($(partnerArea.tableId).length){
                        var info = theTable.page.info();

                        theTable.ajax.reload(function () {
                           theTable.page(info.page+1);
                        });
                    }else{
                        // Legacy support for older tables
                        location.reload();
                    }
                }
            });


        });

        $("body").on("submit",".editPartner",function (e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            let id = $(this).attr("data-id");

            $(this).hide();
            $("#info-user-"+id).before("<div id='userEditLoading'><b>"+partnerArea.savingText+"</b></div>");

            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(), // serializes the form's elements.
                success: function(data)
                {
                    $("#userEditLoading").remove();

                    Swal.fire({
                        title: partnerArea.successTextTitle,
                        text: partnerArea.successTextPartner,
                        type: 'success',
                        confirmButtonText: 'Ok'
                    });

                        // Legacy support for older tables
                        location.reload();
                }
            });


        });

        $("body").on("submit",".editDiscount",function (e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            var amount = form.children(".customer_rate").val();

            $(this).hide().before("<div id='userEditLoading'><b>"+partnerArea.savingText+"</b></div>");

            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(), // serializes the form's elements.
                success: function(data)
                {
                    $("#userEditLoading").remove();

                    Swal.fire({
                        title: partnerArea.successTextTitle,
                        text: partnerArea.succesTextDiscount,
                        type: 'success',
                        confirmButtonText: 'Ok'
                    });

                    form.show();
                    form.children(".customer_rate").val(amount);
                }
            });


        });

    });


})(jQuery);
