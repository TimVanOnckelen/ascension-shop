<?php


namespace AscensionShop\NationalManager;


use AscensionShop\Affiliate\Helpers;
use AscensionShop\Affiliate\SubAffiliate;
use AscensionShop\Lib\TemplateEngine;

class Frontend {

	private $ref;

	function __construct() {

		add_filter( 'woocommerce_account_menu_items', array( $this, 'addNationalManagerLink' ), 1000 );
		add_rewrite_endpoint( 'national-manager-area', EP_PAGES );
		add_action( 'woocommerce_account_national-manager-area_endpoint', array($this,"nationalManagerArea") );

		/**
		 * Rest request to show all orders
		 */
		add_action( 'rest_api_init',  function() {
			register_rest_route( 'ascension-shop/v1', '/orders/all', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'loadOrdersREST' ),
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

		/**
		 * Rest request to get all clients
		 */
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

		/**
		 * REST request for clients in a select list
		 */
		add_action( 'rest_api_init',  function() {
			register_rest_route( 'ascension-shop/v1', '/clients/select-list', array(
				'methods'  => 'GET',
				'callback' => array( $this, 'clientSelectListRest' ),
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



		add_action('wp_enqueue_scripts', array($this,'loadJs'),100);

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
		wp_enqueue_script( "dataTables", "//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js", "jquery" );
		wp_enqueue_style( "dataTables", "//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" );
		wp_enqueue_script( "sweetAlert", "https://cdn.jsdelivr.net/npm/sweetalert2@8" );
		wp_enqueue_script( 'jquerymodal', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js' );
		wp_enqueue_style( 'jquerymodal', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css' );
		wp_enqueue_style( "ascension-info-css", XE_ASCENSION_SHOP_PLUGIN_DIR . "/assets/css/refferal-order-info.min.css", null, "1.0.1.7" );
		wp_enqueue_style( "national-manager", XE_ASCENSION_SHOP_PLUGIN_DIR . "/assets/css/national-manager.min.css", null, "1.0.12" );
		wp_deregister_script( "select2" );
		wp_enqueue_script( "select2", "https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js", "jquery", "1.0.1" );
		wp_enqueue_script( "partnerAreaFunctions", XE_ASCENSION_SHOP_PLUGIN_DIR . "/assets/js/partnerAreaFunctions.min.js", array(
			"jquery",
			"sweetAlert",
			"select2"
		), '1.2.4' );

		// Add vars to script
		wp_localize_script( 'partnerAreaFunctions', 'partnerArea', array(
			'url'                  => get_rest_url( null, "ascension-shop/v1/clients/all" ),
			'nonce'                => wp_create_nonce( 'wp_rest' ),
			'editText'             => __( "Bewerken", "ascension-shop" ),
			'savingText'           => __( "Aan het opslaan...", "ascension-shop" ),
			'successTextPartner'   => __( "Partner succesvol aangepast!", "ascension-shop" ),
			'successText'          => __( "Gebruiker succesvol aangepast!", "ascension-shop" ),
			"succesTextDiscount"   => __( "Korting succesvol aangepast!", "ascension-shop" ),
				"successTextTitle" => __("Aanpassen gelukt!","ascension-shop"),
				'referer'       => home_url( $wp->request ),
				'tableId'       => "#all-clients",
				'processingText' => __('Processing','ascension-shop'),
				'showText' => __('Show','ascension-shop'),
				'enteriesText' => __('enteries','ascension-shop'),

			) );

		// Add vars to script
		wp_localize_script( 'partnerAreaFunctions', 'OrderArea', array(
			'url'           => get_rest_url(null,"ascension-shop/v1/orders/all"),
			'nonce'          => wp_create_nonce( 'wp_rest' ),
			'downloadInvoiceText'      => __("Download factuur","ascension-shop"),
			'lookText'    => __("Bekijk","ascension-shop"),
			'referer'       => home_url( $wp->request ),
			'tableId'       => "#all-orders"
		) );


		// Add vars to script
		wp_localize_script( 'partnerAreaFunctions', 'getClients', array(
			'url'           => get_rest_url(null,"ascension-shop/v1/clients/select-list"),
			'nonce'          => wp_create_nonce( 'wp_rest' ),
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

		if(!isset($_GET["page"]) && isset($_GET["tab"])){
			$_GET["page"] = $_GET["tab"];
		}

		switch ($_GET["page"]){

			case "orders";
				// Get clients template
				$t             = new TemplateEngine();
				$t->lang       = NationalManager::getNationalMangerLang( get_current_user_id() );
				$main->content = $t->display( 'national-manager/all-orders.php' );
				break;

			case "clients";
				// Get clients template
				$t             = new TemplateEngine();
				$t->lang       = NationalManager::getNationalMangerLang( get_current_user_id() );
				$main->content = $t->display( 'national-manager/all-clients.php' );
				break;

			case "add-client";
				// Get clients template
				$t             = new TemplateEngine();
				$t->lang       = NationalManager::getNationalMangerLang( get_current_user_id() );
				$main->content = $t->display( 'national-manager/add-client-form.php' );
				break;

			case "partners";
				// Get partners template
				$t             = new TemplateEngine();
				$t->lang       = NationalManager::getNationalMangerLang( get_current_user_id() );
				$main->content = $t->display( 'national-manager/all-partners.php' );

				break;

			case "add-partner";
				// Add partner template
				$t = new TemplateEngine();
				$t->lang = NationalManager::getNationalMangerLang(get_current_user_id());
				$main->content = $t->display('national-manager/add-partner-form.php');
				break;

			case "commissions";
				// Add partner template
				$t = new TemplateEngine();
				$t->lang = NationalManager::getNationalMangerLang(get_current_user_id());
				$main->content = $t->display('national-manager/all-commisions.php');
				break;

			default;
			// Get Orders template
			$t = new TemplateEngine();
			$t->lang = NationalManager::getNationalMangerLang(get_current_user_id());
			// $t->clients = $this->loadAllClientIdsForSearch();
			$main->content = $t->display('national-manager/all-clients.php');

			break;

		}




		echo $main->display('national-manager/main.php');


	}


	/**
	 * Load the orders based on REST request
	 * @param $request
	 *
	 * @return \WP_REST_Response
	 */
	public function loadOrdersREST($request){

		$search = $request["columns"];
		$amount = $request["length"];
		$start = $request["start"];
		$draw = $request["draw"];
		$client = $request["columns"][4]["search"]["value"];
		$searchAll = $request["columns"][5]["search"]["value"];
		$referer = $request->get_header("referer");

		// Get partner id
		$partner = affwp_get_affiliate_id();


			if($request["columns"][3]["search"]["value"] != null){
				$partner = $request["columns"][3]["search"]["value"];
			}

		// Load clients
		$include = self::loadClientsFromGivenPartner($partner,$searchAll,true, $searchAll);

		$returndata = $this->loadOrdersRESTResponse($search,$amount,$start,$draw,$partner='',$client,$include,$referer);

		// Create the response object
		$response = new \WP_REST_Response( $returndata );

		// Add a custom status code
		$response->set_status( 201 );

		// Return response
		return $response;
	}


	/**
	 * Load all orders in a response
	 * @param $search
	 * @param $amount
	 * @param $start
	 * @param $draw
	 * @param $partner
	 * @param $client
	 * @param bool $include
	 *
	 * @return array
	 */
	public function loadOrdersRESTResponse($search,$amount,$start,$draw,$partner,$client,$include=false,$ref=false){


		if($client == '' OR $client == false){
			// Set include only clients from partner
			$client = $include;

			if(NationalManager::isNationalManger(get_current_user_id()) && strpos($ref, 'affiliate-area') === false){
				// Add every order, from every client. :)
				if($search[3]["search"]["value"] == null) { // Only when no partner is set
					$client = '';
				}
			}
		}else{
			// Client not valid from current partner & != NM
			if(!in_array($client,$include) && !NationalManager::isNationalManger(get_current_user_id())){
				$client = '0';
			}
		}

		// Set args
		$args = array(
			'limit'        => $amount,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'type'         => 'shop_order',
			'return'       => 'objects',
			'customer_id'  => $client,
			'paginate'     => 'true',
			'offset'       => $start,
			'date_created' => $search[1]["search"]["value"]
		);

		// Search by status if needed
		if($search[2]["search"]["value"] != null){
			$args["status"] = $search[2]["search"]["value"];
		}

		// Only get orders from specific lang if national manager
		if(NationalManager::isNationalManger(get_current_user_id()) && strpos($ref, 'affiliate-area') === false){

			// add lang
			$lang = NationalManager::getNationalMangerLang(get_current_user_id());
			$args["meta_key"] = "wpml_language";
			$args["meta_value"] = $lang[0];

		}



		if(!is_numeric($search[0]["search"]["value"])) {

			$query = new \WC_Order_Query( $args );

			$orders = $query->get_orders();
		}else{ // Single order

			$orders = '';
			$id = "";
			$order = wc_get_order($search[0]["search"]["value"]);
			$orders->orders = array();

			if(!empty($order) && $search[0]["search"]["value"] == $order->get_id()){


				$orders->orders[] = $order;

			}else{

			}

		}


		// Setup all data
		$returndata = array();
		$returndata["draw"] = $draw++;
		$returndata["recordsTotal"] = $orders->total;
		$returndata["recordsFiltered"] = $orders->total;
		$returndata["data"] = array();

		foreach ($orders->orders as $o) {

			$temp                 = array();
			$temp["id"]           = '#' . $o->get_id();
			$temp["date"]         = $o->get_date_created()->format( 'd-m-Y' );
			$temp["status"]       = wc_get_order_status_name( $o->get_status() );
			$temp["amount"]       = $o->get_formatted_order_total();
			$temp["trackingcode"] = $o->get_meta( 'as_trackingcode' );

			$complete_date = $o->get_date_completed();
			if ( $complete_date != null ) {
				$complete_date = $complete_date->format( 'd-m-Y' );
			}
			$temp["shippingdate"] = $complete_date;

			$user_data      = get_userdata( $o->get_customer_id() );
			$temp["client"] = '<a  target="_blank" href="?tab=orders&id=' . $user_data->ID . '">#' . $user_data->ID . ' ' . $user_data->first_name . ' ' . $user_data->last_name . '</a>';
			// Customer of
			$customer_id = Helpers::getCustomerByUserId( $o->get_customer_id() );
			if ( $customer_id > 0 && $customer_id != '' ) {
				$parent = Helpers::getParentByCustomerId( $customer_id );
				if ( $parent > 0 ) {
					$username = affwp_get_affiliate_name( $parent );
					$parent   = "#" . $parent . " " . $username;
				}else{
					$sub = affwp_get_affiliate_id($user_data->ID);
					if($sub > 0){
						$sub = new SubAffiliate($sub);
						$sub_parent = $sub->getParentId();
						$username = affwp_get_affiliate_name($sub_parent);
						$parent   = "#" . $sub_parent . " " . $username;

					}else {
						$parent = "";
					}
				}
			}else{
				$parent = "";
			}


			$temp["partner"] = $parent;
			$temp["actions"] = '';

			$document = wcpdf_get_document( "invoice", $o->get_id() );
			$exists   = method_exists( $document, 'exists' ) ? $document->exists() : false;

			// Add if exsists
			if ( $exists != false ) {
				$temp["actions"] .= '<a target="_blank" href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&document_type=invoice&order_ids=' . $o->get_id() . '&my-account' ), 'generate_wpo_wcpdf' ) . '"><button>' . __( "Download factuur", "ascension-shop" ) . '</button></a>';
			}

			ob_start();
			woocommerce_order_details_table( $o->get_id() );
			$order_view = ob_get_contents();
			ob_end_clean();


			// View order link
			$temp["actions"]      .= ' <a href="#viewOrder' . $o->get_id() . '" rel="modal:open"><button>' . __( "Bekijk", "ascension-shop" ) . '</button></a>';
			$temp["actions"]      .= '<div class="modal" id="viewOrder' . $o->get_id() . '">' . $order_view . '</div>';
			$returndata["data"][] = $temp;

		}

		return $returndata;

	}


    /**
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     * @throws \Exception
     */
	public function triggerClientRest(\WP_REST_Request $request){

		$referer = $request->get_header("referer");
		$search = $request["columns"][1]["search"]["value"];
		$amount = $request["length"];
		$start = $request["start"];
		$draw = $request["draw"];
		$ref = $request->get_header("referer");
		$everyone = false; // Load everyone
		$all = false;
		$searchStatus = $request["columns"][3]["search"]["value"];

		// Is a national manager & not in partner area
		if(NationalManager::isNationalManger(get_current_user_id()) && strpos($referer, 'affiliate-area') === false){

			$all_string = $request["columns"][2]["search"]["value"];
			// If empty, it's all
			if($all_string === ""){
				$all_string = 1;
			}
			// Set all
			$all = boolval($all_string);

			// Only get the clients from given country
			$partner = NationalManager::getNationalManagerCountryAff(get_current_user_id());
			$other_partner = $request["columns"][0]["search"]["value"];

			$sub = new SubAffiliate($partner);
			$everyone = true; // Load every sub client

			// Check if this is partner of given country
			if($other_partner > 0 && $partner !== $other_partner){
				$partner = $other_partner;
				$everyone = false;
			}

		}else{ // only get from the current partner

			$partner = affwp_get_affiliate_id();
			$other_partner = $request["columns"][0]["search"]["value"];
			$sub = new SubAffiliate($partner);
			$all = false;

			if($sub->isSubAffiliateOf($other_partner) === true){
				$partner = $other_partner;
				$all = false;
			}

			if($other_partner === $partner){
				$partner = $other_partner;
				$all = false;
			}

		}

		$returndata = self::loadClients($search,$amount,$start,$draw,$partner,$all,false,$everyone,$ref,$searchStatus);

		// Create the response object
		$response = new \WP_REST_Response( $returndata );

		// Add a custom status code
		$response->set_status( 201 );

		// Return response
		return $response;

	}

	/**
	 * @param $partner
	 * @param bool $all_clients
	 * @param bool $load_inactive
	 * @param bool $everyone
	 *
	 * @return array|string
	 */
	public static function loadClientsFromGivenPartner($partner,$all_clients=true,$load_inactive=false,$everyone=false){

		$include = '';

		if($partner > 0) {
			if($all_clients == false) {
				// Get customers by partner
				$customers = affiliate_wp_lifetime_commissions()->integrations->get_customers_for_affiliate( $partner );

			}else{ // Get all clients & clients from subs
				$customers = Helpers::getAllCustomersFromPartnerAndSubs($partner, false,$load_inactive,$everyone);
			}

			$include = array();

			if(count($customers)> 0) {
				// $include[] = get_current_user_id();
				foreach ( $customers as $c ) {
					$include[] = $c->user_id;
				}
			}else{
				$include[] = 0;
			}
		}

		return $include;

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
	public static function loadClients($search, $amount,$start,$draw,$partner= null,$all_clients=false,$allow_partners = false,$everyone = false,$ref = false,$status=true){

		global $wpdb;

		// Get clients
		$users        = self::loadClientQuery( $partner, $all_clients, $allow_partners, $amount, $start, $search, true, $everyone, $status );
		$users_result = $users->get_results();


		if ( NationalManager::isNationalManger( get_current_user_id() ) ) {
			$affiliate_id = NationalManager::getNationalManagerCountryAff( get_current_user_id() );
			$sub          = new \AscensionShop\Affiliate\SubAffiliate( $affiliate_id );
			$partners     = $sub->getAllChildren( 2, true, true );
		} else {
			$partners = array();
		}

		// Setup all data
		$returndata                    = array();
		$returndata["draw"]            = $draw ++;
		$returndata["recordsTotal"]    = $users->get_total();
		$returndata["recordsFiltered"] = $users->get_total();
		$returndata["data"]            = array();

		// Get all users
		foreach ( $users_result as $customer ) {
			$temp = array();
			// Setup data
			$user_data = get_userdata($customer);
			$temp["id"] = $user_data->ID;
			$temp["name"] = $user_data->first_name.' '. $user_data->last_name;
			$temp["text"] = $temp["name"];
			$t = new TemplateEngine();
			$t->user_id = $user_data->ID;
			$t->user = $user_data;

			// Customer of
			$customer_id = Helpers::getCustomerByUserId($customer);
			if($customer_id > 0 && $customer_id != '') {
				$parent = Helpers::getParentByCustomerId($customer_id);
				if($parent > 0) {
					$username = affwp_get_affiliate_name( $parent );
					$parent   = "#" . $parent . " " . $username;
				}else{
					// Maybe sub partner?
					$aff_id = affwp_get_affiliate_id($customer);

					// Get sub parent
					if($aff_id > 0) {
						$sub = new SubAffiliate($aff_id);
						$parent = $sub->getParentId();
						$parent = affwp_get_affiliate_name($parent);
					} else {
						$parent = "";
					}
				}
			} else {
				$parent = "";
			}

			$t->ref          = $ref;
			$temp["partner"] = $parent;
			$t->partners     = $partners;
			$t->sub          = $sub;
			$temp["info"]    = $t->display( 'national-manager/table/info.php' );


			/*
			 * Get status of user
			 */
			$status = get_user_meta( $t->user_id, 'ascension_status', true );
			if ( $status == "non-active" ) {
				$status = __( 'Niet actief', "ascension-shop" );
			} else {
				$status = __( 'Actief', "ascension-shop" );
			}


			$temp["status"] = $status;
			$temp["discount"] = $t->display('national-manager/table/discount.php');;
			$returndata["data"][] = $temp;
		}

		return $returndata;
	}

	/**
	 * Query for loading clients based on multiple parameters
	 * @param $partner
	 * @param bool $all_clients
	 * @param bool $allow_partners
	 * @param int $amount
	 * @param int $start
	 * @param string $search
	 *
	 * @return bool|mixed|\WP_User_Query
	 */
	public static function loadClientQuery($partner,$all_clients=false,$allow_partners=false,$amount=10,$start=0,$search='',$loadInactive=false,$everyone=false,$status=false){

		$include = '';
		$users_result = false;

		// If a partner is selected, add users
		$include = self::loadClientsFromGivenPartner($partner,$all_clients,$loadInactive,$everyone);

		if ( $allow_partners === false ) {
			// Exclude all partners
			$exclude = self::getPartnersUserIds();
		} else { // Allow partners
			$exclude = array();
		}

		// Set caching name
		$caching_name = 'ascension__' . md5( serialize( $include ) . '_' . serialize( $exclude ) . $amount . $start );
		// Get caching value
		// $cache_result = wp_cache_get( $caching_name );
		$cache_result = false;

		if ( $status == "non-active" ) {
			$meta_q = array(
				array(
					'key'   => 'ascension_status',
					'value' => 'non-active',
				)
			);
		} elseif ( $status == "active" ) {
			$meta_q = array(
				'relation' => "OR",
				array(
					'key'     => 'ascension_status',
					'value'   => '',
				),
				array(
					'key'     => 'ascension_status',
					'compare' => 'NOT EXISTS',
					'value'   => '',
				),
			);
		}else{
			$meta_q = array();
		}

		if(false === $cache_result) {
			// Filter out users
			$users = new \WP_User_Query(
				array(
					'exclude'    => $exclude,
					'number'     => $amount,
					'offset'     => $start,
					'include'    => $include,
					'meta_key'   => 'last_name',
					'search'         => '*'.esc_attr( $search ).'*',
					'fields' => 'id',
					'search_columns' => array(
						'display_name'
					),
					'orderby'    => 'meta_value',
					'meta_query'    => $meta_q
				)
			);

			// Set wp cache
			wp_cache_set($caching_name,$users,'',60);

		}else{ // set result as cached result
			$users = $cache_result;
		}


		return $users;

	}

	public function clientSelectListRest($request){

		// Get partner id
		$partner  = affwp_get_affiliate_id();
		$everyone = true;
		$referer  = $request->get_header( 'referer' );

		// National manager clients
		if ( NationalManager::isNationalManger( get_current_user_id() ) && strpos( $referer, 'affiliate-area' ) === false ) {
			$partner = NationalManager::getNationalManagerCountryAff( get_current_user_id() );
		}

		$users = self::loadClientQuery( $partner, true, false, 100, 0, $request["search"], true, $everyone );

		// The return data
		$users = $users->get_results();

		$returndata          = array();
		$returndata["items"] = array();

		if ( count( $users ) > 0 ) {
			// Create a select list
			foreach ( $users as $u ) {
				$name      = get_user_meta( $u, "first_name", true );
				$last_name = get_user_meta( $u, "last_name", true );

				$temp          = array();
				$temp["id"]    = $u;
				$temp["text"]  = $name . " " . $last_name;
				$returndata ["items"][] = $temp;
			}
		}

		// Create the response object
		$response = new \WP_REST_Response( $returndata );

		// Add a custom status code
		$response->set_status( 201 );

		// Return response
		return $response;

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
			       'order'   => 'ASC',
				'status' => 'active') );
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
			unset($cols['view_count']);
		}
		return $cols;
	}

	public function addNmColumnsData($event){

		if($event->post_status != 'publish'){
			$ref = $_SERVER['REQUEST_URI'];
			echo '<a target="_blank" href="https://ascension.eu/de/?post_type=event_listing&amp;p='.$event->ID.'" target="_blank">View</a>';
			echo ' - <a href="?tab=events&ascension-event-approve='.$event->ID.'&ref='.$ref.'">Approve</a>';
		}
		if($event->post_status == 'publish'){
			echo ' - <a href="?tab=events&ascension-event-inactive='.$event->ID.'&ref='.$ref.'">Set inactive</a>';
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

		if(isset($_REQUEST["ascension-event-inactive"]) && is_numeric($_REQUEST["ascension-event-inactive"])){

			$post = array( 'ID' => $_REQUEST["ascension-event-inactive"], 'post_status' => "pending" );
			wp_update_post($post);

			wp_safe_redirect($_REQUEST["ref"]);
		}

		if(isset($_REQUEST["ascension-event-approve"]) && is_numeric($_REQUEST["ascension-event-approve"])){

			$post = array( 'ID' => $_REQUEST["ascension-event-approve"], 'post_status' => "publish" );
			wp_update_post($post);

			wp_safe_redirect($_REQUEST["ref"]);
		}

	}


}