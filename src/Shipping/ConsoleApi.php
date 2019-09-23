<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 15/07/2019
 * Time: 15:38
 */

namespace AscensionShop\Shipping;


class ConsoleApi
{

    public function __construct()
    {

        // Try adding a subscription
        add_action('rest_api_init', function () {
            register_rest_route('ascension-admin/v1', '/add/tracking', array(
                'methods' => 'POST',
                'callback' => array($this, 'addTracking'),
                'permission_callback' => array($this, 'get_items_permissions_check'),
            ));
        });

        // Try adding a subscription
        add_action('rest_api_init', function () {
            register_rest_route('ascension-admin/v1', '/order/complete', array(
                'methods' => 'POST',
                'callback' => array($this, 'completeTracking'),
                'permission_callback' => array($this, 'get_items_permissions_check'),
            ));
        });

        // Send tracking code
        add_action('rest_api_init', function () {
            register_rest_route('ascension-admin/v1', '/order/send_trackingcode', array(
                'methods' => 'POST',
                'callback' => array($this, 'sendTrackingCode'),
                'permission_callback' => array($this, 'get_items_permissions_check'),
            ));
        });
    }

    /**
     * @param $request
     *
     * @return \WP_REST_Response
     */
    public function addTracking($request)
    {

        $request_data = $request->get_params();

        // Setup return array
        $data = array();

        // save the tracking code
        $this->saveTrackingCode($request_data["postId"], $request_data["tc"]);

        // the status
        $data["status"] = "success";

        // Create the response object
        $response = new \WP_REST_Response($data);

        // Add a custom status code
        $response->set_status(201);

        // Return response
        return $response;


    }

    /**
     * @param $id
     * @param $tracking
     *
     * @return mixed
     */
    private function saveTrackingCode($id, $tracking)
    {

        // Save tracking code
        return update_post_meta($id, "as_trackingcode", $tracking);

    }

    /**
     * Complete the tracking
     * @param $request
     *
     * @return \WP_REST_Response
     */
    public function completeTracking($request)
    {

        $request_data = $request->get_params();

        // Setup return array
        $data = array();

        // Save order complete
        $this->saveOrderComplete($request_data["postId"]);

        // the status
        $data["status"] = "success";

        // Create the response object
        $response = new \WP_REST_Response($data);

        // Add a custom status code
        $response->set_status(201);

        // Return response
        return $response;
    }

    private function saveOrderComplete($id)
    {
        // Complete order
        $order = wc_get_order($id);
        $order->update_status('completed');
        // Save tracking status to completed
        return update_post_meta($id, "as-tracking-status", "completed");
    }

    /**
     * Send tracking code to user
     * @param $request
     *
     * @return \WP_REST_Response
     */
    public function sendTrackingCode($request)
    {


        $request_data = $request->get_params();

        // Setup return array
        $data = array();

        // Set meta for sending email
        update_post_meta($request_data["postId"], "as-tracking-mail-send", "true");

        if (has_action("ascension_send_trackingcode")) {
            // Send a tracking code
            do_action("ascension_send_trackingcode", $request_data["postId"]);
        }

        // Create the response object
        $response = new \WP_REST_Response($data);

        // Add a custom status code
        $response->set_status(201);

        // Return response
        return $response;

    }

    /**
     * Check if a given request has access to get items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_items_permissions_check($request)
    {
        return current_user_can('edit_posts');
    }

    /**
     * @param $data
     *
     * @return array
     */
    private function properArray($data)
    {

        $newArray = array();

        foreach ($data as $item) {
            $newArray[$item["name"]] = $item["value"];
        }

        return $newArray;

    }

}