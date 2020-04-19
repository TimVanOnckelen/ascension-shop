<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 29/08/2019
 * Time: 14:19
 */

namespace AscensionShop\PartnerArea;


use AscensionShop\Affiliate\Helpers;
use AscensionShop\Affiliate\SubAffiliate;
use AscensionShop\Lib\MessageHandeling;
use AscensionShop\Lib\TemplateEngine;
use AscensionShop\NationalManager\NationalManager;

class AddClients
{

	public function __construct()
	{
		add_action("ascension-add-client", array($this, "addClientForm"));
		add_action('admin_post_ascension-save_add-client', array($this, "saveNewClient"), 10, 1);
		add_action('admin_post_ascension-edit_customer', array($this, "editClient"), 10, 1);
		add_action( 'wp_ajax_ascension-edit_customer', array($this, "editClient"), 10, 1);

		add_action('admin_post_ascension-edit_partner', array($this, "editPartner"), 10, 1);
		add_action('wp_ajax_ascension-edit_partner', array($this, "editPartner"), 10, 1);

		add_action('admin_post_ascension-add_partner', array($this, "addNewPartner"), 10, 1);


	}

	public function addClientForm()
	{
		$t = new TemplateEngine();
		$t->affiliate_id = affwp_get_affiliate_id(get_current_user_id());
		echo $t->display("affiliate-wp/add-client-form.php");
	}

	/**
	 * Save a new client
	 */
	public function saveNewClient()
	{

		// Woocommerce fix
		$this->check_prerequisites();

		// Get affiliate & nonce verify
		$affiliate_id = affwp_get_affiliate_id(get_current_user_id());
		$nonce_verify = wp_verify_nonce($_REQUEST['_wpnonce'], 'ascension_add_new_customer_' . $affiliate_id);

		// Add customer for other user, only for national manager
		if(NationalManager::isNationalManger(get_current_user_id()) == true){
			if($_REQUEST["ascension_shop_client_of"] > 0){
				$affiliate_id = $_REQUEST["ascension_shop_client_of"];
			}
		}

		// Setup a username
		$username = strtolower($_REQUEST["name"] . "." . $_REQUEST["lastname"]).rand(0,10);

		if ($nonce_verify == true && $affiliate_id > 0) {
			// Add a new user to wordpress
			$user_id = wc_create_new_customer($_REQUEST['email'], $username, wp_generate_password());

			// Add a new affiliate customer
			if (!is_wp_error($user_id)) {

				$customer = affwp_add_customer(array(
					'first_name' => $_REQUEST["name"],
					'last_name' => $_REQUEST["lastname"],
					'email' => $_REQUEST["email"],
					'user_id' => $user_id,
					'affiliate_id' => $affiliate_id
				));


				wp_update_user(array(
					'ID' => $user_id,
					'first_name' => $_POST["name"],
					'last_name' => $_POST["lastname"],
					'display_name' => $_POST["name"].' '.$_POST["lastname"]));

				// Update client discount
				update_user_meta($user_id, "ascension_shop_affiliate_coupon", $_REQUEST["discount"]);
				update_user_meta($user_id,"billing_first_name",$_POST["name"]);
				update_user_meta($user_id,"billing_last_name",$_POST["lastname"]);
				update_user_meta($user_id,"billing_address_1",$_REQUEST["adres"]);
				update_user_meta($user_id,"billing_city",$_REQUEST["city"]);
				update_user_meta($user_id,"billing_phone",$_REQUEST["phone"]);
				update_user_meta($user_id,"billing_postcode",$_REQUEST["postalcode"]);
				update_user_meta($user_id,"billing_company",$_REQUEST["company"]);
				update_user_meta($user_id,"billing_country",$_REQUEST["country"]);
				update_user_meta($user_id,"vat_number",$_REQUEST["vat"]);

				if ($customer > 0) {
					MessageHandeling::setMessage(__("Nieuw klant succesvol aangemaakt", "ascension-shop"), "error");
				} else {
					MessageHandeling::setMessage(__("Er ging iets mis, probeer het opnieuw.", "ascension-shop"), "error");

				}
			} else {
				MessageHandeling::setMessage($user_id->get_error_message(), "error");
			}
		}

		wp_safe_redirect($_REQUEST["_wp_http_referer"]);

	}

	public function editClient(){

		$affiliate_id = affwp_get_affiliate_id(get_current_user_id());
		$nonce_verify = wp_verify_nonce($_REQUEST['_wpnonce'], 'ascension_edit_customer' . $affiliate_id);

		$is_client_of = Helpers::isClientOfPartnerOfSubPartner($_POST["customer_id"],$affiliate_id);
		$username = strtolower($_REQUEST["name"] . "." . $_REQUEST["lastname"]);

		// National manager can mangage anyone :)
		if(NationalManager::isNationalManger(get_current_user_id()) == true){
			$nonce_verify = true;
			$is_partner_of = true;
			$affiliate_id = 1;
			$is_client_of = true;
		}

		// error_log("client:".$is_client_of);

		// No client of aff, so return
		if($is_client_of == false){
			return;
		}



		// Verify if user has access
		if ($nonce_verify == true && $affiliate_id > 0) {


			wp_update_user(array(
				'ID' => $_POST["user_id"],
				'first_name' => $_POST["name"],
				'last_name' => $_POST["lastname"],
				'display_name' => $_POST["name"].' '.$_POST["lastname"]));

			// Update all meta
			update_user_meta($_POST["user_id"],"billing_first_name",$_POST["name"]);
			update_user_meta($_POST["user_id"],"billing_last_name",$_POST["lastname"]);
			update_user_meta($_POST["user_id"],"billing_address_1",$_POST["adres"]);
			update_user_meta($_POST["user_id"],"billing_city",$_POST["city"]);
			update_user_meta($_POST["user_id"],"billing_phone",$_POST["phone"]);
			update_user_meta($_POST["user_id"],"billing_postcode",$_POST["postalcode"]);
			update_user_meta($_POST["user_id"],"billing_country",$_POST["country"]);
			update_user_meta($_POST["user_id"],"billing_company",$_POST["company"]);
			update_user_meta($_POST["user_id"],"vat_number",$_POST["vat"]);
			update_user_meta($_POST["user_id"],"ascension_status",$_POST["ascension_status"]);

			if($_POST["customer_id"] != '') {
				affwp_update_customer( array(
					"customer_id" => $_POST["customer_id"],
					"first_name"  => $_POST["name"],
					"last_name"   => $_POST["lastname"],
				) );
			}


			// Update partner
			if(NationalManager::isNationalManger(get_current_user_id()) == true){


				// Add customer if needed
				if($_POST["customer_id"] <= 0) {

					$user = get_userdata( $_POST["user_id"]);

					affwp_add_customer(array(
						'first_name' => $_POST["name"],
						'last_name' => $_POST["lastname"],
						'email' => $user->user_email,
						'user_id' => $_POST["user_id"],
						'affiliate_id' => $_POST["ascension_shop_customer_of"]
					));
				}else { // Edit partner is already exists

					$partner_id  = absint( $_POST["ascension_shop_customer_of"] );
					$customer_id = Helpers::getCustomerByUserId( $_POST["user_id"] );

					global $wpdb;
					$query = $wpdb->query( "UPDATE {$wpdb->prefix}affiliate_wp_customermeta SET meta_value='" . $partner_id . "' WHERE affwp_customer_id='" . $customer_id . "' AND meta_key='affiliate_id'" );
				}

			}

		}else{
			die("Error: This is not your partner. You cannot edit him.");
		}

		wp_safe_redirect($_REQUEST["_wp_http_referer"]);
	}


	/**
	 * Save a new partner
	 */
	public function addNewPartner()
	{

		// Only NM can add new partners
		if(NationalManager::isNationalManger(get_current_user_id()) != true){
			return;
		}

		$partner = affwp_add_affiliate(
			array(
				"user_id" => $_POST["user_id"],
				"status" => "active",
				"rate" => $_POST["rate"]
			)
		);

		if(is_numeric($partner)){
			$sub = new SubAffiliate($partner);
			$sub->saveParent($_POST["ascension_shop_partner_of"]);

			// Remove "client from"
			$partner_id  = 0;
			$customer_id = Helpers::getCustomerByUserId( $_POST["user_id"] );

			global $wpdb;
			$query = $wpdb->query( "UPDATE {$wpdb->prefix}affiliate_wp_customermeta SET meta_value='" . $partner_id . "' WHERE affwp_customer_id='" . $customer_id . "' AND meta_key='affiliate_id'" );

			// Return message
			// Return message
			MessageHandeling::setMessage(__("Nieuwe partner succesvol aangemaakt", "ascension-shop"), "error");
		}else{
			MessageHandeling::setMessage(__("Er ging iets mis tijdens het aanmaken van de partner. Probeer opnieuw.", "ascension-shop"), "error");
		}

		wp_safe_redirect($_REQUEST["_wp_http_referer"]);


	}

	public function editPartner() {


		$affiliate_id  = affwp_get_affiliate_id( get_current_user_id() );
		if(NationalManager::isNationalManger(get_current_user_id())){

			// Only get the clients from given country
			$affiliate_id = NationalManager::getNationalManagerCountryAff(get_current_user_id());

		}

		$nonce_verify  = wp_verify_nonce( $_REQUEST['_wpnonce'], 'ascension_edit_partner' . $affiliate_id );
		$affiliate     = new SubAffiliate($_REQUEST["partner_id"]);
		$is_partner_of = $affiliate->isSubAffiliateOf($_REQUEST["partner_id"]);
		$sub           = new SubAffiliate( $_REQUEST["partner_id"] );
		$user_id       = $sub->getUserId();


		// National manager can mangage anyone :)
		if ( NationalManager::isNationalManger( get_current_user_id() ) == true ) {
			$is_partner_of = true;
			$affiliate_id  = 1;
		}

		$removeclient = true;


		if ( $is_partner_of == true ) {
			if ( $nonce_verify == true && $affiliate_id > 0 ) {


				wp_update_user( array(
					'ID'         => $user_id,
					'first_name' => $_REQUEST["name"],
					'last_name'  => $_REQUEST["lastname"]
				) );

				update_user_meta($user_id,"billing_first_name",$_REQUEST["name"]);
				update_user_meta($user_id,"billing_last_name",$_REQUEST["lastname"]);
				update_user_meta( $user_id, "billing_address_1", $_REQUEST["adres"] );
				update_user_meta( $user_id, "billing_city", $_REQUEST["city"] );
				update_user_meta( $user_id, "billing_phone", $_REQUEST["phone"] );
				update_user_meta($user_id,"billing_country",$_REQUEST["country"]);
				update_user_meta( $user_id, "billing_postcode", $_REQUEST["postalcode"] );
				update_user_meta($user_id,"billing_company",$_REQUEST["company"]);
				update_user_meta( $user_id, "vat_number", $_REQUEST["vat"] );

				$customer_id = Helpers::getCustomerByUserId($user_id);

				// Only if national manager
				if ( NationalManager::isNationalManger(get_current_user_id()) ) {

					global $wpdb;

					if ( $_REQUEST["ascension_status"] == 1 ) {
						$status = "active";
					} else {
						$status       = "inactive";
						$removeclient = false;

						$user_info = get_userdata( $user_id );

						$data = array(
							"first_name"   => $_REQUEST["name"],
							"last_name"    => $_REQUEST["lastname"],
							"email"        => $user_info->user_email,
							"affiliate_id" => $_REQUEST["ascension_shop_customer_of"],
							"user_id"      => $user_id
						);

						if ( $customer_id ) {
							// Remove as client!
							global $wpdb;
							$query = $wpdb->insert( "{$wpdb->prefix}affiliate_wp_customermeta", array( "meta_key"          => "affiliate_id",
							                                                                           "meta_value"        => $_REQUEST["ascension_shop_customer_of"],
							                                                                           "affwp_customer_id" => $customer_id
							) );
						}
						// Set user role
						affwp_add_customer( $data );

					}

					// Update the rate
					$wpdb->update( "{$wpdb->prefix}affiliate_wp_affiliates", array( "rate"   => $_REQUEST["rate"],
					                                                                "status" => $status
					), array( "affiliate_id" => $sub->getId() ) );


					// Save parent
					if ( $_REQUEST["ascension_shop_customer_of"] != $_REQUEST["partner_id"] ) {
						if ( $_REQUEST["ascension_shop_customer_of"] != $sub->getParentId() ) {
							$sub->saveParent( $_REQUEST["ascension_shop_customer_of"] );
						}
					}


				}

				affwp_update_customer( array(
					"customer_id" => $customer_id,
					"first_name"  => $_POST["name"],
					"last_name"   => $_POST["lastname"],
				) );

				if ( $removeclient === true ) {
					// Remove as client!
					global $wpdb;
					$query = $wpdb->query( "DELETE FROM {$wpdb->prefix}affiliate_wp_customermeta WHERE affwp_customer_id='" . $customer_id . "'" );
				}

			}else{
				die("Not a valid nonce");

			}
		}else{
			die("Not a valid partner");

		}

		wp_safe_redirect( $_REQUEST["_wp_http_referer"] );

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