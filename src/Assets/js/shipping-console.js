(function($){

    // Do a api call to the HEPPIE api
    function _callApi(call, data, method, callback, errorCallback){

        // DO API REQUEST TO ADD PRICE
        $.ajax({
            url: ascensionshop.root + call,
            method: method,
            data: JSON.stringify(data),
            crossDomain: true,
            contentType: 'application/json',
            xhrFields: { withCredentials: true },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', ascensionshop.nonce);
            },
            success: function (data) {

                //console.log(data);
                callback(data);

            },
            error: function (error) {

                //console.log("error:"+error);

                errorCallback(error);

            }
        });

    }


    function trackingCodeSave(){

        $(".tracking_code").on("focusout",function () {

            let tc = $(this).val();
            let postId = $(this).attr("data-id");

            $(this).parent().append('<div id="as-loading">Opslaan...</div>');


            // Do an api call
            _callApi("ascension-admin/v1/add/tracking",{postId:postId,tc:tc},"POST",
                function (response) { // Succes callback

                // remove loading
                    $("#as-loading").html("Succesvol opgeslagen!").delay(4000).remove();


                },function () { // error callback

                    console.log();
                    alert("error");

                });


        });

    }

    function as_completeTrackingOrder(){
        $(".closeTracking").on("click",function () {

            // Get post id
            let postId = $(this).attr("data-id");
            let theTr = $(this).closest('tr');

            $(this).prop('disabled', true);

            // Do an api call
            _callApi("ascension-admin/v1/order/complete",{postId:postId},"POST",
                function (response) { // Succes callback

                    theTr.remove();
                    alert("Order succesvol afgerond in het shipping console.");


                },function () { // error callback

                    console.log();
                    alert("error");

                });

        });
    }

    function sendTrackingCode(){
        $(".sendTracking").on("click",function () {

            // Get post id
            let postId = $(this).attr("data-id");
            let button = $(this);

            button.html("Aan het verzenden...");

            $(this).prop('disabled', true);

            // Do an api call
            _callApi("ascension-admin/v1/order/send_trackingcode",{postId:postId},"POST",
                function (response) { // Succes callback

                    button.html("Trackingcode verzonden");


                },function () { // error callback

                    console.log();
                    alert("error");

                });

        });
    }

    $(document).on("ready",function () {

        // Save tracking code function
        trackingCodeSave();
        // Complete order function
        as_completeTrackingOrder();
        // Tracking code sending
        sendTrackingCode();
    });

})(jQuery);