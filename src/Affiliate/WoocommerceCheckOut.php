<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 20/08/2019
 * Time: 12:17
 */

namespace AscensionShop\Affiliate;


use AscensionShop\Lib\TemplateEngine;

class WoocommerceCheckOut
{

    function __construct()
    {
        add_action("woocommerce_before_checkout_form", array($this, "addAffiliateForm"), 10);
        add_action("wp_enqueue_scripts", array($this, "loadCheckoutCssJS"));

        // Change the customer id on checkout
        add_filter("woocommerce_checkout_customer_id", array($this, "changeCustomerIdOnOrder"), 10, 1);

        // Affiliate customer add note
        add_action("woocommerce_checkout_update_order_meta", array($this, "addCustomOrderNote"), 10, 1);

        // Disable payment gateways
        add_filter('woocommerce_available_payment_gateways', array($this, "customerPaysGateway"), 10, 1);

        // Apply filter
        add_filter("ascension_user_id_coupons", array($this, "changeUserIdForCoupons"), 10, 1);

        // Try adding a subscription
        add_action('rest_api_init', function () {
            register_rest_route('ascension/v1', '/add/order/customer', array(
                'methods' => 'POST',
                'callback' => array($this, 'setClientIdForSession'),
            ));
        });

        // Add notice
	    // add_filter( 'woocommerce_before_cart', array($this,"returnNoticeOfOtherPartner"), 10);

	    add_filter("woocommerce_payment_gateways", array($this, "addGateway"), 10, 1);

        add_filter("woocommerce_checkout_update_order_meta", array($this, "saveShippingAdress"));
        add_filter("woocommerce_checkout_update_order_meta", array($this, "saveDifferentEmail"));

        add_filter('woocommerce_email_recipient_customer_processing_order', array($this, "sendEmailToParent"), 1, 2);
        add_filter('woocommerce_email_recipient_customer_completed_order', array($this, "sendEmailToParent"), 1, 2);
        add_filter('woocommerce_email_recipient_customer_invoice', array($this, "sendEmailToParent"), 1, 2);
        add_filter('woocommerce_email_recipient_customer_pending_order', array($this, "sendEmailToParent"), 1, 2);
        add_filter('woocommerce_email_recipient_customer_on_hold_order', array($this, "sendEmailToParent"), 1, 2);


    }


    public function loadCheckoutCssJS()
    {

        $aff_id = affwp_get_affiliate_id();

        // Load global js & css
	    wp_enqueue_script("select2","https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.11/js/select2.min.js");
		wp_enqueue_style("select2","https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.11/css/select2.min.css");

        // Only load on checkout as affiliate
        if (is_checkout() && $aff_id > 0) {
            wp_enqueue_style("ascension-shop-toggle", XE_ASCENSION_SHOP_PLUGIN_DIR . 'assets/css/toggles.min.css');
            wp_enqueue_script("ascension-unserialize", XE_ASCENSION_SHOP_PLUGIN_DIR . 'assets/js/unserialize.min.js');
            wp_enqueue_script("ascension-shop-checkout", XE_ASCENSION_SHOP_PLUGIN_DIR . 'assets/js/ascension-affiliate-checkout.min.js', null, '1.0.6');
            // Add details
            wp_localize_script('ascension-shop-checkout', 'ascension', array(
                'root' => esc_url_raw(rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
            ));
        }
    }

    /**
     * Display the form on checko
     * @throws \Exception
     */
    public function addAffiliateForm()
    {

        $aff_id = affwp_get_affiliate_id();


        // Show the clients logo
        if ($aff_id > 0) {
	        $sub = new SubAffiliate($aff_id);

	        if($sub->getStatus() == "0"){
	        	return;
	        }

	        $t = new TemplateEngine();
            $customers = Helpers::getAllCustomersFromPartnerAndSubs($aff_id);
            usort($customers, function ($first, $second) {
                return strcasecmp($first->first_name, $second->first_name);
            });
            $t->customers = $customers;
            echo $t->display("woocommerce/affiliate-form-checkout.php");
        }

    }

    /**
     * Customer id :)
     * @param $customer_id
     *
     * @return mixed
     */
    public function changeCustomerIdOnOrder($customer_id)
    {

	    $aff_id = affwp_get_affiliate_id(get_current_user_id());
	    $custom_customer = WC()->session->get('ascension_affiliate_client_id_order');

	    if ($custom_customer <= 0) {
		    return $customer_id;
	    }

	    $is_customer = Helpers::isClientOfPartnerOfSubPartner($custom_customer, $aff_id);
	    // get the customer id
	    $custom_customer = $this->getUserId($custom_customer);

	    if ($is_customer > 0) {

		    if (isset($custom_customer) && is_numeric($custom_customer)) {
			    return $custom_customer;
		    }

	    }
	    // Nothing to do, just return
	    return $customer_id;


    }

    /**
     * Set a client id in session when Affiliate orders for client
     * @param $request
     *
     * @return \WP_REST_Response
     */
    public function setClientIdForSession($request)
    {

        // Let's go!
        $formData = (array)$request["data"];

        $aff_id = affwp_get_affiliate_id();
        $is_customer = affiliate_wp_lifetime_commissions()->integrations->is_customer_of_affiliate($formData["customer"], $aff_id);

        // Woocommerce fix in REST
        $this->check_prerequisites();

        // Only do if user is customer
        if ($is_customer > 0) {

            // Set client id
            WC()->session->set('ascension_affiliate_client_id_order', $formData["customer"]);
            WC()->session->set('ascension_affiliate_who_pays_order', $formData["who_pays"]);
	        WC()->session->set( 'ascension_affiliate_order_for_child_affiliate', false );

            // Return the client data
            $data["customer"] = $this->setUpCustomerResponse($formData["customer"]);
            $data["status"] = true;


        } else {

        	// Get parent of client
	        $parent_client = Helpers::getParentByCustomerId($formData["customer"]);
	        $parent_client = new SubAffiliate($parent_client);
	        $is_child_of_partner = Helpers::isClientOfPartnerOfSubPartner($formData["customer"],$aff_id);

	           if($parent_client->getId() != $aff_id) {
	           	    // Affiliate is making an order for an other affiliate OR client of other affiliate
		           WC()->session->set( 'ascension_affiliate_client_id_order', $formData["customer"] );
		           WC()->session->set('ascension_affiliate_who_pays_order', $formData["who_pays"]);
		           WC()->session->set( 'ascension_affiliate_order_for_child_affiliate', $parent_client->getUserId() );
		        } else{
		        	// Just an order for itself
			        WC()->session->set( 'ascension_affiliate_client_id_order', 0 );
			        WC()->session->set( 'ascension_affiliate_who_pays_order', false );
		           WC()->session->set( 'ascension_affiliate_order_for_child_affiliate', false );

	           }


	        $data["original_partner"] = $parent_client->getId();
            $data["customer"] = $this->setUpCustomerResponse($formData["customer"]);

            $data["status"] = true;
        }

        // Add notices
        $this->returnNoticeOfOtherPartner();

        // Create the response object
        $response = new \WP_REST_Response($data);

        // Add a custom status code
        $response->set_status(201);

        // Return response
        return $response;

    }

    public function returnNoticeOfOtherPartner(){
	    $other_partner = WC()->session->get('ascension_affiliate_order_for_child_affiliate' );
	    $who_pays = WC()->session->get('ascension_affiliate_who_pays_order');

	    wc_clear_notices();

	    if($other_partner > 0 && $who_pays == "false"){
		    $aff_id = affwp_get_affiliate_id();

	    	$other_partner = affwp_get_affiliate_id($other_partner);

		    if($other_partner == $aff_id){
			    return;
		    }

		    $sub = new SubAffiliate($other_partner);
	    	wc_add_notice(sprintf(__("Je maakt een order voor een klant van jouw sub-partner %s. Zijn/haar percentage (%s%s) wordt toegepast."),$sub->getName(),$sub->getUserRate(),"%"),"notice");
	    }

    }

    /**
     * Return customer data
     * @param $customer_id
     *
     * @return array
     */
    private function setUpCustomerResponse($customer_id)
    {

        $customer_id = $this->getUserId($customer_id);

        if ($customer_id == 0) {
            $customer_id = get_current_user_id();
        }

        $return = array();
        $customer = new \WC_Customer($customer_id);
        $return["billing_first_name"] = $customer->get_billing_first_name();
        $return["billing_last_name"] = $customer->get_billing_last_name();
        $return["billing_company"] = $customer->get_billing_company();
        $return["billing_address_1"] = $customer->get_billing_address_1();
        $return["billing_address_2"] = $customer->get_billing_address_2();
        $return["billing_postcode"] = $customer->get_billing_postcode();
        $return["billing_city"] = $customer->get_billing_city();
        $return["billing_state"] = $customer->get_billing_state();
        $return["billing_phone"] = $customer->get_billing_phone();
        $return["billing_email"] = $customer->get_email();
        // $return["vat_number"] = $customer->get_vat_number();
        $return["shipping_first_name"] = $customer->get_shipping_first_name();
        $return["shipping_last_name"] = $customer->get_shipping_last_name();
        $return["shipping_company"] = $customer->get_shipping_company();
        $return["shipping_address_1"] = $customer->get_shipping_address_1();
        $return["shipping_address_2"] = $customer->get_shipping_address_2();
        $return["shipping_postcode"] = $customer->get_shipping_postcode();
        $return["shipping_city"] = $customer->get_shipping_city();
        $return["shipping_state"] = $customer->get_shipping_state();
        $return["vat_number"] = get_user_meta($customer_id, 'vat_number', true);


        return $return;

    }

    /**
     * @param $order_id
     */
    public function addCustomOrderNote($order_id)
    {

	    wc_clear_notices();

        $aff_id = affwp_get_affiliate_id(get_current_user_id());
        $custom_customer = WC()->session->get('ascension_affiliate_client_id_order');
        $who_pays = WC()->session->get('ascension_affiliate_who_pays_order');
		$order_rate = 	WC()->session->get('ascension_order_rate');

	    $order = new \WC_Order($order_id);

	    $order->update_meta_data('_ascension_order_rate',$order_rate);

	    if ($custom_customer > 0 && $aff_id > 0) {

            // Add meta from order maker
            $order->update_meta_data('_ascension_order_maker', $aff_id);
            $order->update_meta_data('_ascension_order_payer', $who_pays);

            $sub = new SubAffiliate($aff_id);

            // The text for the note
            $note = __("Bestelling gemaakt door Affiliate Partner #" . $aff_id. " ".$sub->getName(), "ascension-shop");

            // Add the note
            $order->add_order_note($note);

        }

	    // Save the data
	    $order->save();
    }

    public function saveDifferentEmail($order_id)
    {

        $order = new \WC_Order($order_id);
        // Get order maker
        $parent_id = get_post_meta($order->get_id(), '_ascension_order_maker', true);
        $payer = get_post_meta($order->get_id(), '_ascension_order_payer', true);
        $parent_id = affwp_get_affiliate_user_id($parent_id);

        // Parent is set, and Payer is set to client
        if ($parent_id <= 0) {
            return;
        } else {
            if ($payer == "true" OR $payer === true) { // client pays
                //if ($order->get_payment_method() != "mollie_wc_gateway_banktransfer") { // Affiliate will get email with order details
                    return;
                //}
            }
        }
        // Partner pays, so everything goes to partner.
        $parent = get_user_by('id', $parent_id);
        // Change billing email
        $order->set_billing_email($parent->user_email);
        $order->save();

    }

    public function saveShippingAdress($order_id)
    {

        if (isset($_POST["ship_to_different_address"])) {
            // update data
            $customer = new \WC_Order($order_id);
            $customer->set_shipping_first_name($_POST["shipping_first_name"]);
            $customer->set_shipping_last_name($_POST["shipping_last_name"]);
            $customer->set_shipping_company($_POST["shipping_company"]);
            $customer->set_shipping_address_1($_POST["shipping_address_1"]);
            $customer->set_shipping_address_2($_POST["shipping_address_2"]);
            $customer->set_shipping_postcode($_POST["shipping_postcode"]);
            $customer->set_shipping_city($_POST["shipping_city"]);
            $customer->set_shipping_state($_POST["shipping_state"]);
            $customer->set_shipping_country($_POST["shipping_country"]);


            $customer->save();
        }

    }

    /**
     * @param $customer_id
     *
     * @return mixed
     */
    private function getUserId($customer_id)
    {
        global $wpdb;

        $user_id = $wpdb->get_row($wpdb->prepare("SELECT user_id FROM " . $wpdb->prefix . "affiliate_wp_customers WHERE customer_id=%d LIMIT 1", $customer_id));

        if (isset($user_id->user_id)) {
            return $user_id->user_id;
        }

        return 0;
    }


    /**
     * CHange the user id of order is for customer
     * @param $user_id
     *
     * @return mixed
     */
    public function changeUserIdForCoupons($user_id)
    {

        $custom_customer = WC()->session->get('ascension_affiliate_client_id_order');
	    $custom_partner = WC()->session->get('ascension_affiliate_order_for_child_affiliate');
	    $who_pays = WC()->session->get('ascension_affiliate_who_pays_order');

	    // If current user pays, return he's id or from the custom partner
	    if($who_pays == "false"){
	    	if($custom_partner != '' && $custom_partner > 0){
	    		return $custom_partner;
		    }else{
	    		return $user_id;
		    }
	    }else{
	    	if($custom_customer > 0){
	    		return $this->getUserId($custom_customer);
		    }
	    }

        // Just regular id :)
        return $user_id;
    }

    /**
     * Add custom gateway
     * @param $gateways
     *
     * @return array
     */
    public function addGateway($gateways)
    {
        $gateways[] = 'AscensionShop\Woocommerce\CustomerPayGateway';
        return $gateways;
    }

    /**
     * @param $available_gateways
     *
     * @return mixed
     */
    public function customerPaysGateway($available_gateways)
    {

        // Fix availabillity of wc session
        if (null === WC()->session) {
            $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');

            //Prefix session class with global namespace if not already namespaced
            if (false === strpos($session_class, '\\')) {
                $session_class = '\\' . $session_class;
            }

            WC()->session = new $session_class();
            WC()->session->init();
        }

        $custom_customer = WC()->session->get('ascension_affiliate_client_id_order');
        $who_pays = WC()->session->get('ascension_affiliate_who_pays_order');

        // error_log(json_encode($available_gateways));

        // Unset every gateway expect the custom customer one
        if ($who_pays === true && $custom_customer !== false) {
            foreach ($available_gateways as $key => $data) {
                if ($key != "ascension_customer_gateway" && $key != "mollie_wc_gateway_banktransfer") {
                    unset($available_gateways[$key]);
                }
                if ($key == "ascension_customer_gateway") {
                    $available_gateways[$key]->title = __("Klant logt in en betaald zelf", "ascension-shop");
                    //error_log(json_encode($available_gateways[$key]));
                }
            }


        } else {
            unset($available_gateways["ascension_customer_gateway"]);
        }


        return $available_gateways;
    }

    /**
     * @param $recipient
     * @param $order
     * @return mixed
     */
    public function sendEmailToParent($recipient, $order)
    {

        // Get order maker
        $parent_id = get_post_meta($order->get_id(), '_ascension_order_maker', true);
        $payer = get_post_meta($order->get_id(), '_ascension_order_payer', true);
        $parent_id = affwp_get_affiliate_user_id($parent_id);

        error_log($payer);

        // Nothing to do, just go on :)
        if ($parent_id <= 0) {
            return $recipient;
        } else {
            if ($payer == "true" OR $payer === true) {
                    return $recipient;
            }
        }


        // Order is payed by parent, so send invoice to parent
        $client = get_user_by('id', $order->get_user_id());
        $parent = get_user_by('id', $parent_id);


        return $parent->user_email;
    }


    /**
     * Check any prerequisites required for our add to cart request.
     */
    private function check_prerequisites()
    {
        if (defined('WC_ABSPATH')) {
            // WC 3.6+ - Cart and notice functions are not included during a REST request.
            include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
            include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
        }

        if (null === WC()->session) {
            $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');

            //Prefix session class with global namespace if not already namespaced
            if (false === strpos($session_class, '\\')) {
                $session_class = '\\' . $session_class;
            }

            WC()->session = new $session_class();
            WC()->session->init();
        }

        if (null === WC()->customer) {
            WC()->customer = new \WC_Customer(get_current_user_id(), true);
        }

        if (null === WC()->cart) {
            WC()->cart = new \WC_Cart();

            // We need to force a refresh of the cart contents from session here (cart contents are normally refreshed on wp_loaded, which has already happened by this point).
            WC()->cart->get_cart();
        }
    }

}
