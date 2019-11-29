<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 13/08/2019
 * Time: 12:47
 */

namespace AscensionShop\Affiliate;


use AscensionShop\NationalManager\NationalManager;

class ClientCouponManager
{

    public function __construct()
    {

        add_action('woocommerce_cart_calculate_fees', array($this, "addUserDiscount"));
        add_action('woocommerce_cart_calculate_fees', array($this, "discountAsAffiliate"));

        add_action('admin_post_ascension-save_customer-discount', array($this, "saveDiscounts"), 10, 1);
        add_filter('woocommerce_cart_totals_get_fees_from_cart_taxes', array($this, "filterOutTax"), 10, 3);

	    // Edit client discount in backend
	    add_action('show_user_profile', array($this, 'editClientDiscount'),10,1);
	    add_action('edit_user_profile', array($this, 'editClientDiscount'),10,1);

	    add_action('edit_user_profile_update', array($this, "saveClientDiscount"),10,1);
	    add_action('edit_user_profile_update', array($this, "saveClientPartner"),10,1);
	    add_action('personal_options_update', array($this, "saveClientDiscount"),10,1);
	    add_action('personal_options_update',array($this, "saveClientPartner"),10,1);
    }


    /**
     * Discount af affiliate
     * @param \WC_Cart $cart
     */
    public function discountAsAffiliate(\WC_Cart $cart)
    {

        $user_id = get_current_user_id();
        // Filter out user id if order is for customer
        $user_id = apply_filters("ascension_user_id_coupons", $user_id);

        $aff_id = affwp_get_affiliate_id($user_id);

	    // Set rate
	    // WC()->session->set('ascension_order_rate',0);

        if ($aff_id != false) {

            $sub = new SubAffiliate($aff_id);
            $rate = $sub->getUserRate();

            // Set rate
	        WC()->session->set('ascension_order_rate',$rate);

            if($sub->getStatus() == 0){
                return;
            }

            if ($rate > 0) {

                $discount = ($cart->get_subtotal()) / 100 * $rate;

                $cart->add_fee('Affiliate Discount', -$discount, true, 'zero-rate');

            }

        }

        return;

    }

    public function filterOutTax($taxes, $fee, $o)
    {

        if ($fee->object->id == "discount-from-referring-parent") {
            $taxes[50] = 0;
        }

        if ($fee->object->id == "affiliate-discount") {
            $taxes[50] = 0;
        }

        return $taxes;
    }

    public function addUserDiscount(\WC_Cart $cart)
    {

        if (get_current_user_id() > 0) {

	        // Set rate
	       //  WC()->session->set('ascension_order_rate',0);

            $user_id = get_current_user_id();
            $user_id = apply_filters("ascension_user_id_coupons", $user_id);


            $rate = floatval($this->customerHasDiscount($user_id));


            if ($rate > 0) {


	            // Set rate
	            WC()->session->set('ascension_order_rate',$rate);

                $discount = ($cart->get_subtotal()) / 100 * $rate;

                $cart->add_fee('Discount from Referring Parent', -$discount, true, 'zero-rate');

            }

            return;

        }

        return;
    }

    /**
     * Save discounts
     */
    public function saveDiscounts()
    {

        $affiliate_id = affwp_get_affiliate_id(get_current_user_id());
        $nonce_verify = wp_verify_nonce($_REQUEST['_wpnonce'], 'ascension_save_customer_discount_' . $affiliate_id);

	    // National manager can mangage anyone :)
	    if(NationalManager::isNationalManger(get_current_user_id()) == true){
		    $nonce_verify = true;
		    $affiliate_id = 1;
	    }


	    if ($affiliate_id !== false) {

            if ($nonce_verify == true) {
                foreach ($_REQUEST["customer_rate"] as $id => $rate) {
	                $customerCurrentUser = $this->isCustomerFromCurrentUser($id);

	                if(NationalManager::isNationalManger(get_current_user_id())){
	                    $customerCurrentUser = true;
                    }

                    // Update rate if customer is from current user
                    if ($customerCurrentUser === true) {
                        update_user_meta($id, "ascension_shop_affiliate_coupon", $rate);
                    }

                }
            }
        }

        wp_safe_redirect($_REQUEST["_wp_http_referer"]);

    }

    /*
     * Check if given user is a customer of current affiliate
     */
    private function isCustomerFromCurrentUser($c_id)
    {

        $affiliate_id = affwp_get_affiliate_id(get_current_user_id());
        return Helpers::isClientOfPartnerOfSubPartner($c_id,$affiliate_id);

    }

    // Discount Manager for Clients of Affiliate
    private function customerHasDiscount($customer)
    {
        $coupon = get_user_meta($customer, "ascension_shop_affiliate_coupon", true);

        if ($coupon > 0) {
            // Return the percentage of user discount
            return $coupon;
        }

        return false;
    }

    public function editClientDiscount($user){

	    // Only show if current user can edit
	    if (!current_user_can('administrator')) return;

	    if (!is_admin()) return;


	    $customer_id = $this->getCustomerByUserId($user->ID);

	    if($customer_id > 0){
	        $parent_id = $this->getParentByCustomerId($customer_id);
        }else{
	        $parent_id = 0;
        }

	    $all_affiliates = affiliate_wp()->affiliates->get_affiliates(array('number' => 0,'orderby'=>'name','order'=>'ASC'));

	    $sub = new SubAffiliate($user->ID);

	    ?>
		<h2><?php _e("Klant instellingen - Partners"); ?></h2>
        <?php

	    if($sub->isSub() === true && $sub->getStatus() === 1){
		    _e("This user is an active partner. You cannot make a partner be a client.");
		    return;
	    }

	    if($sub->isSub() === true && $sub->getStatus() !== 1){
		    _e("This user is an INACTIVE partner.");
	    }

        ?>
	    <table class="form-table">
		    <tr>
			    <th><label for="as_user_ln"><?php _e("Klanten korting (%)") ?></label></th>
			    <td>
				    <input type="number" name="ascension_shop_affiliate_coupon" step=".01" value="<?php echo get_user_meta($user->ID,"ascension_shop_affiliate_coupon",true); ?>">
			    </td>
		    </tr>
            <tr>
                <th><label for="as_user_ln"><?php _e("Klant van"); ?></label></th>
                <td>
                    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>" />
                    <select name="ascension_shop_customer_of">
                        <option></option>
                        <?php
                        foreach ($all_affiliates as $a) {

	                       ?>
                            <option value="<?php echo $a->affiliate_id; ?>" <?php selected($a->affiliate_id,$parent_id); ?>><?php echo affiliate_wp()->affiliates->get_affiliate_name($a->affiliate_id) ?></option>
                            <?php

                        }
                        ?>
                    </select>
                </td>
            </tr>
	    </table>
	    <?php
    }

	/**
	 * @param $user_id
	 */
	public function saveClientDiscount($user_id)
	{
		$return = update_user_meta($user_id, 'ascension_shop_affiliate_coupon', $_POST['ascension_shop_affiliate_coupon']);


		return $return;
	}

	public function saveClientPartner($user_id){


			$customer_id = $this->getCustomerByUserId($user_id);

			if($customer_id > 0) {

			    $partner_id = absint($_REQUEST["ascension_shop_customer_of"]);
				global $wpdb;


			    if($partner_id < 0){
				    $query = $wpdb->query("DELETE FROM {$wpdb->prefix}affiliate_wp_customermeta WHERE affwp_customer_id='" . $customer_id . "' AND meta_key='affiliate_id'");

			    }else{
				    $query = $wpdb->query("UPDATE {$wpdb->prefix}affiliate_wp_customermeta SET meta_value='".$partner_id."' WHERE affwp_customer_id='" . $customer_id . "' AND meta_key='affiliate_id'");

			    }



			}else{
				affwp_add_customer(array(
					'first_name' => $_REQUEST["first_name"],
					'last_name' => $_REQUEST["last_name"],
					'email' => $_REQUEST["email"],
					'user_id' => $user_id,
					'affiliate_id' => $_REQUEST["ascension_shop_customer_of"],
					'date_created' => date()
				));
            }


    }

	private function getCustomerByUserId($user_id)
	{

	    if($user_id > 0) {
		    global $wpdb;
		    $query = $wpdb->get_row( "SELECT customer_id FROM {$wpdb->prefix}affiliate_wp_customers WHERE user_id='" . $user_id . "'" );

		    if ( isset( $query->customer_id ) ) {
			    return $query->customer_id;
		    }
	    }
		return 0;
	}

	private function getParentByCustomerId($customer_id)
	{
		global $wpdb;
		$query = $wpdb->get_row("SELECT meta_value FROM {$wpdb->prefix}affiliate_wp_customermeta WHERE affwp_customer_id='" . $customer_id . "' AND meta_key='affiliate_id'");

		if (isset($query->meta_value)) {
			return $query->meta_value;
		}
		return 0;
	}


}