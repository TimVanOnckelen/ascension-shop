<?php


namespace AscensionShop\NationalManager;


use AscensionShop\Affiliate\Helpers;
use AscensionShop\Affiliate\SubAffiliate;
use AscensionShop\Lib\TemplateEngine;

class Frontend {

	function __construct() {

		add_filter( 'woocommerce_account_menu_items', array( $this, 'addNationalManagerLink' ), 100 );
		add_rewrite_endpoint( 'national-manager-area', EP_PAGES );
		add_action( 'woocommerce_account_national-manager-area_endpoint', array($this,"nationalManagerArea") );

		add_action( 'rest_api_init',  function() {
			register_rest_route( 'ascension-shop/v1', '/orders/all', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'loadOrders' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_others_posts' );
				}
			)  );

		});

		add_action( 'rest_api_init',  function() {
			register_rest_route( 'ascension-shop/v1', '/clients/all', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'triggerClientRest' ),
				'permission_callback' => function () {
					if(affwp_get_affiliate_id() > 0){
						return true;
					}
					if(NationalManager::isNationalManger(get_current_user_id()) == true){
						return true;
					}else{
						return false;
					}
				}
			)  );

		});

		add_action('wp_enqueue_scripts', array($this,'loadJs'));

		add_action('event_manager_get_dashboard_events_args',array($this,"getAllEventsIfNationalManager"),10,1);
		add_action('event_manager_user_can_edit_pending_submissions',array($this,"canEditEventNoParameters"));
		add_filter('event_manager_my_event_actions',array($this,"addAllActions"),10,2);
		add_filter('event_manager_user_can_edit_event',array($this,"canEditEvent"),10,2);
		add_filter('event_manager_event_dashboard_columns',array($this,"addNmColumns"),10,2);
		add_action('event_manager_event_dashboard_column_national_manager',array($this,"addNmColumnsData"),10,1);
		add_action('event_manager_event_dashboard_column_organizer',array($this,"addEventOrganizer"),10,1);

		add_action('wp',array($this,"approveEvent"));

	}

	public function loadJs() {

			global $wp;

			// Enqueu scripts
			wp_enqueue_script("dataTables","//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js","jquery");
			wp_enqueue_style("dataTables","//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css");
			wp_enqueue_script("sweetAlert","https://cdn.jsdelivr.net/npm/sweetalert2@8");
			wp_enqueue_style("ascension-info-css", XE_ASCENSION_SHOP_PLUGIN_DIR . "/assets/css/refferal-order-info.min.css",null,"1.0.1.7");
			wp_enqueue_style("national-manager",XE_ASCENSION_SHOP_PLUGIN_DIR."/assets/css/national-manager.min.css",null,"1.0.8");
			wp_enqueue_script("partnerAreaFunctions",XE_ASCENSION_SHOP_PLUGIN_DIR . "/assets/js/partnerAreaFunctions.min.js",array("jquery","sweetAlert","select2"),'1.1.13');

			// Add vars to script
			wp_localize_script( 'partnerAreaFunctions', 'partnerArea', array(
				'url'           => get_rest_url(null,"ascension-shop/v1/clients/all"),
				'nonce'          => wp_create_nonce( 'wp_rest' ),
				'editText'      => __("Bewerken","ascension-shop"),
				'savingText'    => __("Aan het opslaan...","ascension-shop"),
				'successTextPartner' => __("Partner succesvol aangepast!","ascension-shop"),
				'successText'    => __("Gebruiker succesvol aangepast!","ascension-shop"),
				"succesTextDiscount" => __("Korting succesvol aangepast!","ascension-shop"),
				"successTextTitle" => __("Aanpassen gelukt!","ascension-shop"),
				'referer'       => home_url( $wp->request ),
				'tableId'       => "#all-clients"
			) );

	}

	public function addNationalManagerLink($items ) {


		if ( NationalManager::isNationalManger(get_current_user_id()) ) {

			/*
			 * Normally this would be $slug => $title, but we're going to intercept the 'affiliate-area'
			 * value directly when overriding the endpoint URL in the 'woocommerce_get_endpoint_url' hook.
			 */
			$affiliate_area = array( 'national-manager-area' => NationalManager::getNationalMangerPageName() );

			$last_link = array();

			if ( array_key_exists( 'customer-logout', $items ) ) {

				// Grab the last link (probably the logout link).
				$last_link = array_slice( $items, count( $items ) - 1, 1, true );

				// Pop the last link off the end.
				array_pop( $items );

			}

			// Inject the Affiliate Area link 2nd to last, reinserting the last link.
			$items = array_merge( $items, $affiliate_area, $last_link );
		}


		return $items;

	}

	public function nationalManagerArea(){


		/**
		 * Check if user has access to national manager area
		 */
		if(!NationalManager::isNationalManger(get_current_user_id())){
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		}


		// Get the main template
		$main = new TemplateEngine();
		$main->lang = NationalManager::getNationalMangerLang(get_current_user_id());

		// Get Orders template
		$t = new TemplateEngine();
		$t->lang = NationalManager::getNationalMangerLang(get_current_user_id());
		$orders = $this->getOrders($t->lang[0]);
		$t->orders = $orders;
		$main->content = $t->display('national-manager/all-orders.php');

		// Get clients template
		$t = new TemplateEngine();
		$t->lang = NationalManager::getNationalMangerLang(get_current_user_id());
		$main->content .= $t->display('national-manager/all-clients.php');

		// Get partners template
		$t = new TemplateEngine();
		$t->lang = NationalManager::getNationalMangerLang(get_current_user_id());
		$main->content .= $t->display('national-manager/all-partners.php');

		// Add partner template
		$t = new TemplateEngine();
		$t->lang = NationalManager::getNationalMangerLang(get_current_user_id());
		$main->content .= $t->display('national-manager/add-partner-form.php');

		echo $main->display('national-manager/main.php');


	}

	private function loadOrders($request){

		$returndata = array();

		// Create the response object
		$response = new \WP_REST_Response( $returndata );

		// Add a custom status code
		$response->set_status( 201 );

		// Return response
		return $response;
	}


	private function getOrders($lang){

		global $wpdb;
		$table_name = $wpdb->prefix . 'postmeta';
		$children = $wpdb->get_results("SELECT * FROM {$table_name} WHERE meta_key = 'wpml_language' AND meta_value='{$lang}' ORDER BY post_id DESC", OBJECT);
		$orders = array();

		foreach ($children as $order){
			$post_type = get_post_type($order->post_id);
			if($post_type == "shop_order") {
				$orders[] = new \WC_Order( $order->post_id );
			}
		}

		return $orders;

	}

	public function triggerClientRest($request){

		$search = $request["search"]["value"];
		$amount = $request["length"];
		$start = $request["start"];
		$draw = $request["draw"];


		if(NationalManager::isNationalManger(get_current_user_id())){
			$partner = $request["columns"][0]["search"]["value"];
			$all = false;
		}else{ // only get from the current partner

			$partner = affwp_get_affiliate_id();
			$other_partner = $request["columns"][0]["search"]["value"];
			$sub = new SubAffiliate($partner);
			$all = true;

			if($sub->isSubAffiliateOf($other_partner) == true){
				$partner = $other_partner;
				$all = false;
			}

		}

		$returndata = self::loadClients($search,$amount,$start,$draw,$partner,$all);

		// Create the response object
		$response = new \WP_REST_Response( $returndata );

		// Add a custom status code
		$response->set_status( 201 );

		// Return response
		return $response;

	}

	/**
	 * @param $search
	 * @param $amount
	 * @param $start
	 * @param $draw
	 * @param null $partner
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function loadClients($search, $amount,$start,$draw,$partner= null,$all_clients=false){

		global $wpdb;

		$include = '';

		// If a partner is selected, add users
		if($partner > 0) {
			if($all_clients == false) {
				// Get customers by partner
				$customers = affiliate_wp_lifetime_commissions()->integrations->get_customers_for_affiliate( $partner );
			}else{ // Get all clients & clients from subs
				$customers = Helpers::getAllCustomersFromPartnerAndSubs($partner);
			}

			$include = array();

			if(count($customers)> 0) {
				$include[] = get_current_user_id();
				foreach ( $customers as $c ) {
					$include[] = $c->user_id;
				}
			}else{
				$include[] = 0;
			}
		}

		// Exclude all partners
		$exclude = self::getPartnersUserIds();

		// split search
		$split = preg_split(' ',$search,2);
		$firstname = '';
		$lastname  = '';

		if(isset($split[1])) {
			$firstname = $split[0];
			$lastname  = $split[1];
		}

		// Filter out users
		$users = new \WP_User_Query(
			array(
				'exclude' => $exclude,
				'number' => $amount,
				'offset' => $start,
				'include' => $include,
				'meta_key' => 'last_name',
				'orderby' => 'meta_value',
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key' => 'first_name',
						'value' => $search,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'last_name',
						'value' => $search,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'billing_address_1',
						'value' => $search,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'billing_phone',
						'value' => $search,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'billing_city',
						'value' => $search,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'billing_first_name',
						'value' => $search,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'company',
						'value' => $search,
						'compare' => 'LIKE'
					),
					array(
						'key' => 'vat_number',
						'value' => $search,
						'compare' => 'LIKE'
					)
				)
			)
		);

		// Get the results
		$users_result = $users->get_results();

		// Setup all data
		$returndata = array();
		$returndata["draw"] = $draw++;
		$returndata["recordsTotal"] = $users->get_total();
		$returndata["recordsFiltered"] = $users->get_total();
		$returndata["data"] = array();

		// Get all users
		foreach ($users_result as $customer){
			$temp = array();
			// Setup data
			$user_data = get_userdata($customer->ID);
			$temp["id"] = $user_data->ID;
			$temp["name"] = $user_data->first_name.' '. $user_data->last_name;
			$t = new TemplateEngine();
			$t->user_id = $user_data->ID;
			$t->user = $user_data;

			// Customer of
			$customer_id = Helpers::getCustomerByUserId($customer->ID);
			if($customer_id > 0 && $customer_id != '') {
				$parent = Helpers::getParentByCustomerId($customer_id);
				if($parent > 0) {
					$username = affiliate_wp()->affiliates->get_affiliate_name( $parent );
					$parent   = "#" . $parent . " " . $username;
				}else{
					$parent = "";
				}
			}else{
				$parent = "";
			}


			$temp["partner"] = $parent;
			$temp["info"] = $t->display('national-manager/table/info.php');
			$temp["discount"] = $t->display('national-manager/table/discount.php');;
			$returndata["data"][] = $temp;
		}

		return $returndata;
	}



	/**
	 * Create a meta query ready array for langs
	 * @param $lang_array
	 *
	 * @return array
	 */
	private function createMetaArrayLang($lang_array)
	{

		$user_ln_relation_array = array(
			'relation' => 'OR');

		// Langs In Array
		if (is_array($lang_array)) {
			// Add multiple langs
			foreach ($lang_array as $l) {

				array_push($user_ln_relation_array, array(
					'key' => 'wpml_language',
					'value' => $l,
				));
			}
		} else { // Add single lang
			array_push($user_ln_relation_array, array(
				'key' => 'wpml_language',
				'value' => $lang_array,
			));
		}

		return $user_ln_relation_array;

	}

	private static function getPartnersUserIds(){

		$id_array =array();

		$partners = affiliate_wp()->affiliates->get_affiliates(
			array( 'number'  => -1,
			       'orderby' => 'name',
			       'order'   => 'ASC' ) );
		foreach($partners as $p){
			$id_array[] = $p->user_id;
		}

		return $id_array;

	}

	public function getAllEventsIfNationalManager($args){

		if(NationalManager::isNationalManger(get_current_user_id()) != true){
			// Only own args
			return $args;
		}else{
			$posts_per_page = 25;

			return array(

				'post_type'           => 'event_listing',
				'post_status'         => array( 'publish', 'expired', 'pending' ),
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => $posts_per_page,
				'offset'              => ( max( 1, get_query_var('paged') ) - 1 ) * $posts_per_page,
				'orderby'             => 'date',
				'order'               => 'desc'
			);
		}

	}

	public function addAllActions($actions,$event){
		if(NationalManager::isNationalManger(get_current_user_id()) !== true){
			return $actions;
		}

		if(!isset($actions["edit"])){

		$actions ['edit'] = array (
			'label' => __ ( 'Edit', 'wp-event-manager' ),
			'nonce' => false
		);

		$actions['approve'] = array(
			'label' => __ ( 'Approve', 'wp-event-manager' ),
			'nonce' => false
		);

		}

		return $actions;
	}

	public function canEditEvent($can_edit,$event){

		if(NationalManager::isNationalManger(get_current_user_id()) == true){
			return true;
		}

		return $can_edit;

	}

	public function canEditEventNoParameters(){
		if(NationalManager::isNationalManger(get_current_user_id()) == true){
			return true;
		}
		return false;

	}

	public function addNmColumns($cols){
		if(NationalManager::isNationalManger(get_current_user_id()) == true){
			$cols['organizer'] = __('Event Organizer','wp-event-manager');
			$cols['national_manager'] = __('Approve','wp-event-manager');
		}
		return $cols;
	}

	public function addNmColumnsData($event){

		if($event->post_status != 'publish'){
			$ref = $_SERVER['REQUEST_URI'];
			echo '<a href="https://ascension.eu/de/?post_type=event_listing&amp;p='.$event->ID.'" target="_blank">View</a>';
			echo ' - <a href="?tab=events&ascension-event-approve='.$event->ID.'&ref='.$ref.'">Approve</a>';
		}
	}

	public function addEventOrganizer($event){

		$user = get_user_by( 'id', $event->post_author );

		echo $user->user_firstname. ' '.$user->last_name;

	}

	public function approveEvent(){
		if(NationalManager::isNationalManger(get_current_user_id()) != true){
			return;
		}

		if(isset($_REQUEST["ascension-event-approve"]) && is_numeric($_REQUEST["ascension-event-approve"])){

			$post = array( 'ID' => $_REQUEST["ascension-event-approve"], 'post_status' => "publish" );
			wp_update_post($post);

			wp_safe_redirect($_REQUEST["ref"]);
		}

	}
}