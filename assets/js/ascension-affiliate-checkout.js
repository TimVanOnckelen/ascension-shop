(function($){

    let clientsForm = "#ascension-clients-form";
    let formCache = null;

    // Do a api call to the HEPPIE api
    function ascensionApiCall(call, data, method, callback, errorCallback){

        addLoader();

        // DO API REQUEST TO FIND CUSTOMER
        $.ajax({
            url: ascension.root + call,
            method: method,
            data: JSON.stringify(data),
            crossDomain: true,
            contentType: 'application/json',
            xhrFields: { withCredentials: true },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', ascension.nonce);
            },
            success: function (data) {

                // Remove loader
                removeLoader();

                //console.log(data);
                callback(data);


            },
            error: function (error) {

                // console.log("error:"+error);

                errorCallback(error);

            }
        });

    }

    function loadCustomer(customer_id,who_pays) {

        // Do a a api call
        ascensionApiCall("ascension/v1/add/order/customer", {data: {customer: customer_id,who_pays:who_pays}}, "POST", function (data) {

            // reset form data
            $("form.checkout").find("input, textarea").val("");

            $("#ascension-who-pays-container").show();

            $( document.body ).trigger( 'update_checkout' );



            if (data.status == true) {

                //  $( document.body ).trigger( 'update_checkout' );

                var shallowEncoded = $.param( data.customer, true );
                var shallowDecoded = decodeURIComponent( shallowEncoded );

                // Reload price :)

                $("form.checkout").unserializeForm(shallowDecoded);



            } else { // error found!
                $(".woocommerce-checkout").unserializeForm(formCache);
                // Reload price :)
                $( document.body ).trigger( 'update_checkout' );
                // $( document.body ).trigger( 'update_checkout' );

            }

        }, function (error) {

            // Reload price :)
            $( document.body ).trigger( 'update_checkout' );

            alert("Something got wrong, try again");
        });
    }
    
    function changeTrigger() {
        // Add the loader
        addLoader();

        let customer_id = $("#ascension-clients").val();
        let whoPays = $("[name='ascension-who-pays']:checked").val();

        // No user id? Return
        if(customer_id < 0){
            loadCustomer(0,0);
            $( document.body ).trigger( 'update_checkout' );
            return;
        }

        if(formCache == null) {
            // cache the form data
            formCache = $("form.checkout").serialize();

        }

        loadCustomer(customer_id,whoPays);
    }

    function loadCustomerData() {

        $('#ascension-clients').on("change",function () {

            changeTrigger();

        });

        $('[name="ascension-who-pays"]').on("change",function () {

            changeTrigger();

        });

    }

    function toggleWatcher(){

        $('[name="ascension-order-for"]').on("change",function () {

            let isChecked = $("[name='ascension-order-for']:checked").val();

            if(isChecked === "client"){
                $(clientsForm).show();
            }else{
                $(clientsForm).hide();

                $('#ascension-clients').val(1);

                $("#ascension-who-pays-container").hide();

                // Reset customer
                loadCustomer(0,0);

                if(formCache){
                    $(".woocommerce-checkout").unserializeForm(formCache);
                }
            }
        });

    }


    function addLoader(){
        $(".woocommerce").prepend('<div class="ascension-loader"></div>');
    }

    function removeLoader(){
        $(".ascension-loader").remove();
    }


    $(document).on("ready",function () {

        $("#ascension-clients").select2({width:'100%'});

        // Reset on every refresh
        loadCustomer(0,0);

        // Toggle watcher
        toggleWatcher();

        // Trigger customer data on load
        loadCustomerData();
    });


})(jQuery);
