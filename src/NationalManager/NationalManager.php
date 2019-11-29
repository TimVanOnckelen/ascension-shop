<?php


namespace AscensionShop\NationalManager;


class NationalManager {

	function __construct() {
		add_action("init",array($this,"nmCanViewOrder"),11);
	}

	/**
	 * National managers can view all orders
	 */
	public function nmCanViewOrder(){

		if(NationalManager::isNationalManger(get_current_user_id()) == true){

			$user = new \WP_User( get_current_user_id() );
			$user->add_cap( 'view_order' );
			$user->add_cap( 'read_shop_order' );

			return;
		}

	}
	static function isNationalManger($userId){
		$lang = get_user_meta($userId, 'as_user_ln');

		if(isset($lang[0])){
			return true;
		}else{
			return false;
		}
	}

	static function getNationalMangerLang($userId){
		$lang = get_user_meta($userId, 'as_user_ln',true);
		return $lang;
	}

	static function getNationalMangerPageName(){
		return __("National Manager Area","ascension-shop");
	}

	/**
	 * Get the top end affiliate of the current lang
	 * @param $userId
	 *
	 * @return mixed|void
	 */
	static public function getNationalManagerCountryAff($userId){
		$current_lang = get_user_meta($userId, 'as_user_ln',true);
		$current_lang = $current_lang[0];

		return get_option("ascension-shop_standard_ref_" . $current_lang);
	}

}