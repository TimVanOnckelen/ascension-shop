<?php


namespace AscensionShop\NationalManager;


use AscensionShop\Lib\TemplateEngine;

class Frontend {

	function __construct() {

		add_filter( 'woocommerce_account_menu_items', array( $this, 'addNationalManagerLink' ), 100 );
		add_rewrite_endpoint( 'national-manager-area', EP_PAGES );
		add_action( 'woocommerce_account_national-manager-area_endpoint', array($this,"nationalManagerArea") );
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

		// enque style
		wp_enqueue_style("national-manager",XE_ASCENSION_SHOP_PLUGIN_DIR."/assets/css/national-manager.min.css",null,"1.0.5");
		wp_enqueue_script("dataTables","//cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js","jquery");
		wp_enqueue_style("dataTables","//cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css");

		// Get the main template
		$main = new TemplateEngine();
		$main->lang = NationalManager::getNationalMangerLang(get_current_user_id());

		// Get Orders template
		$t = new TemplateEngine();
		$t->lang = NationalManager::getNationalMangerLang(get_current_user_id());
		$orders = $this->getOrders($t->lang[0]);
		$t->orders = $orders;
		$main->content = $t->display('national-manager/all-orders.php');

		// Get Orders template
		$t = new TemplateEngine();
		$t->lang = NationalManager::getNationalMangerLang(get_current_user_id());
		$t->customers = affiliate_wp()->customers->get_customers(array("number" => -1));
		$main->content .= $t->display('national-manager/all-clients.php');


		echo $main->display('national-manager/main.php');


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
}