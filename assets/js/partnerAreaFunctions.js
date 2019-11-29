(function($){

    $(document).on("ready",function () {


        $("#searchByPartner").select2({width:'100%',  allowClear: true, placeholder: "*"});
        $(".searchByPartner").select2({width:'100%',  allowClear: true, placeholder: "*"});
        $("#ascension-clients").select2({width:'100%',  allowClear: true, placeholder: "*"});

        $("#searchOrderByClient").select2({
            ajax: {
                url: getClients.url,
                'beforeSend': function ( xhr ) {
                    xhr.setRequestHeader( 'X-WP-Nonce', OrderArea.nonce );
                },
                data: function (params) {
                    var query = {
                        search: params.term
                    }

                    // Query parameters will be ?search=[term]&type=public
                    return query;
                },
                processResults: function (data) {
                    return {
                        results: $.map(data.items, function (item) {
                            return {
                                text: item.text,
                                id: item.id
                            }
                        })
                    };
                }
            }
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

        // set standard client
        var theClientId = getQueryVariable("id");
        var clientSearch = '';
        console.log("Client Id:".theClientId);

        if(theClientId !== false){

            $("#searchOrderByClient").val(theClientId);
            clientSearch = {"sSearch": theClientId};

        }else{
            clientSearch = null;
        }



        let OrdersTable =  $(OrderArea.tableId).DataTable(
            {
                language: {
                  processing: partnerArea.processingText,

                },
                autoWidth: false,
                processing: true,
                serverSide: true,
                ordering: false,
                "aoSearchCols":
                    [null,
                        null,
                        null,
                        null,
                        clientSearch,
                        null,
                        null],
                ajax: {
                    'url' : OrderArea.url,
                    'type' : "GET",
                    'beforeSend': function ( xhr ) {
                        xhr.setRequestHeader( 'X-WP-Nonce', OrderArea.nonce );
                    },
                    "complete": function () {
                        $("[name='_wp_http_referer']").val(OrderArea.referer);
                    }
                },
                columns: [
                    {"data": "id"},
                    {"data": "date"},
                    {"data": "client"},
                    {"data": "partner"},
                    {"data": "status"},
                    {"data" : "amount"},
                    {"data" : "actions"},
                ],
            }

        );


        /*
        Search on order table if init
         */
        OrdersTable.columns().every(function (index) {
            var that = this;

            if (index === 0) {

                $("#order-id-search").on('keyup change', function () {

                    theId = $(this).val();

                    if (that.search() !== this.value) {
                        that
                            .search(theId)
                            .draw();
                    }

                });
            }

            if (index === 1) {
                $("#orders-to,#orders-from").on('change', function () {

                    var from = $("#orders-from").val();
                    var to = $("#orders-to").val();

                    if (from !== '' && to !== '') {


                        var search = from + '...' + to;

                        if (that.search() !== this.value) {
                            that
                                .search(search)
                                .draw();
                        }
                    }
                });
            }


            if (index === 4) {

                $("#searchOrderByClient").on('change', function () {

                    if (that.search() !== this.value) {
                        that
                            .search(this.value)
                            .draw();
                    }
                });
            }

        });




        let theTable =  $(partnerArea.tableId).DataTable(
            {
                autoWidth: false,
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
                    {"data" : "id",render: function (dataField) {
                            return '#'+dataField+'<br /><a href="#user-edit-'+dataField+'" rel="modal:open" class="edit-user">'+partnerArea.editText+'</a>';
                        }},
                    {"data": "info"},
                    {"data" : "partner"},
                    {"data": "discount"}
                ],
            }
        );


        theTable.columns().every(function (index) {
            var that = this;

            if (index === 0) {
                $("select#searchByPartner").on('change', function () {
                    if (that.search() !== this.value) {
                        that.settings()[0].jqXHR.abort();

                        that
                            .search(this.value)
                            .draw();
                    }
                });

            }

            if (index === 1) {

                $("#searchByName").on('keyup change', function () {

                    theId = $(this).val();

                    console.log(theId);
                    if (that.search() !== this.value) {

                        that.settings()[0].jqXHR.abort();

                        that
                            .search(theId)
                            .draw();
                    }

                });
            }

        });



        $("body").on("submit",".editUser",function (e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');
            let id = $(this).attr("data-id");

            $(this).hide();
            $(this).parent().html("<div id='userEditLoading'><b>"+partnerArea.savingText+"</b></div>")
            // $("#info-user-"+id).before("<div id='userEditLoading'><b>"+partnerArea.savingText+"</b></div>");

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

                        $.modal.close();

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


    function getQueryVariable(variable)
    {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i=0;i<vars.length;i++) {
            var pair = vars[i].split("=");
            if(pair[0] == variable){return pair[1];}
        }
        return(false);
    }

})(jQuery);