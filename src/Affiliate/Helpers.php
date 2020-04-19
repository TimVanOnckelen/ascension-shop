<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 2/08/2019
 * Time: 12:21
 */

namespace AscensionShop\Affiliate;


use AscensionShop\NationalManager\NationalManager;

class Helpers
{

    /**
     * Update level on add to parent
     * @param $parent_id
     * @param $sub
     *
     * @return int|mixed
     */
    static public function updateLevels($parent_id, $sub)
    {

        // get Parent Level
        $parent_level = affwp_get_affiliate_meta($parent_id, "ascension_aff_level", true);

        $sub_children = self::getAllChilderen($sub);


        if ($parent_level < 1) { // Parent is not a sub affiliate, so he's level 0 in matrix

            $parent_level = 0;
            $sub_level = 1;

            // When there is no parent :)
            if ($parent_id <= 1) {
                $sub_level = 0;
            }

            // Update the sub level
            affwp_update_affiliate_meta($sub, "ascension_aff_level", $sub_level);

        } else {
            // As sub becomes child of parent, we add a level 1 to sub.
            $sub_level = $parent_level + 1;
            // Update the sub level
            affwp_update_affiliate_meta($sub, "ascension_aff_level", $sub_level);
        }

        // Sub has children, so update levels
        if ($sub_children != false) {
            self::updateWholeMatrix($sub_children, $sub_level);
        }

        // Return the new level of the sub
        return $sub_level;
    }

    /**
     * Update all children of parent
     * @param $ids
     * @param $start_level
     *
     * @return int
     */
    static private function updateWholeMatrix($ids, $start_level)
    {

        // Add one to level
        $sub_level = $start_level + 1;

        // Update every child & add 1 level
        foreach ($ids as $u) {

            // Update own level
            affwp_update_affiliate_meta($u->affiliate_id, "ascension_aff_level", $sub_level);

            // Update children if needed
            $sub_children = self::getAllChilderen($u->affiliate_id);

            // Sub has children, so update levels
            if ($sub_children != false) {
                self::updateWholeMatrix($sub_children, $sub_level);
            }

        }

        // Return the updated level
        return $sub_level;

    }

    /**
     * Get all children id of the given parent
     * @param $parent_id
     *
     * @return array|bool|null|object
     */
    static public function getAllChilderen($parent_id)
    {


        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_wp_affiliatemeta';
        $children = $wpdb->get_results("SELECT affiliate_id FROM {$table_name} WHERE meta_key = 'ascension_parent_id' AND meta_value='{$parent_id}'", OBJECT);

        $amount = count($children);

        // User has children, return the ids
        if ($amount > 0) {
            return $children;
        }

        // No Children
        return false;

    }

    /**
     * Get all first level Affiliates
     * @return array|null|object
     */
    static public function getAllAffiliatesWithLevelZero()
    {

        global $wpdb;
        $table_name = $wpdb->prefix . 'affiliate_wp_affiliatemeta';
        $children = $wpdb->get_results("SELECT * FROM {$table_name}  WHERE meta_key = 'ascension_aff_level' AND meta_value='0'", OBJECT);

        return $children;

    }


    /**
     * @param $reference
     * @param $context
     *
     * @return array|null|object
     */
    static function getReferralsFromOrder($reference, $context)
    {

        global $wpdb;

        if (defined('AFFILIATE_WP_NETWORK_WIDE') && AFFILIATE_WP_NETWORK_WIDE) {

            $referral_table = 'affiliate_wp_referrals';

        } else {

            $referral_table = $wpdb->prefix . 'affiliate_wp_referrals';

        }

        $referrals = $wpdb->get_results($wpdb->prepare(
            "
		SELECT *
		FROM {$referral_table}
		WHERE reference = %d
		AND context = %s
		",
            $reference,
            $context
        ));

        return $referrals;

    }

    /**
     * @param $reference
     * @return array|null|object
     */
    static function getDirectRefOfOrder($reference)
    {

        global $wpdb;

        if (defined('AFFILIATE_WP_NETWORK_WIDE') && AFFILIATE_WP_NETWORK_WIDE) {

            $referral_table = 'affiliate_wp_referrals';

        } else {

            $referral_table = $wpdb->prefix . 'affiliate_wp_referrals';

        }

        $referrals = $wpdb->get_results($wpdb->prepare(
            "
		SELECT *
		FROM {$referral_table}
		WHERE reference = %d
		AND custom != %s
		",
            $reference,
            "indirect"
        ));

        return $referrals;

    }

    static function getTotalsFromRefs($refs){
	    $totals            = array();
	    $totals["total"]   = 0;
	    $totals["pending"] = 0;
	    $totals["unpaid"]  = 0;
	    $totals["paid"]    = 0;
	    $totals["refund"]  = 0;

	    foreach ( $refs as $ref ) {
		    $totals["total"] += $ref->amount;

		    if ( $ref->status == 'unpaid' ) {
			    $totals["unpaid"] += $ref->amount;
		    }
		    if ( $ref->status == 'pending' ) {
			    $totals["pending"] += $ref->amount;
		    }
		    if ( $ref->status == 'paid' ) {
			    $totals["paid"] += $ref->amount;
		    }
		    if ( $ref->status == 'refund' ) {
			    $totals["refund"] -= $ref->amount;
			    $totals["total"]  -= $ref->amount; // Make total clean
			    $totals["unpaid"] += $ref->amount;
		    }
	    }


	    return $totals;
    }

	/**
	 * Get the parent from the given referral
	 * @param $referral
	 *
	 * @return string
	 */
    static function getParentFromRef($referral){

    	$parent = '';

	    if(isset($referral->custom["parent"])) {
		    $parent = affiliate_wp()->affiliates->get_affiliate_name( $referral->custom["parent"] );
	    }elseif(strpos($referral->description, 'Indirect') !== false){
		    // Legacy support for orders before v1.0.5
		    $temp_parent = str_replace( 'Indirect Referral FROM', '', $referral->description );
		    $temp_parent = explode( "|", $temp_parent );
		    $parent      = $temp_parent[0];
	    }

	    return $parent;
    }


	/**
	 * @param $sub
	 * @param $order
	 * @param $referral
	 *
	 * @return string
	 */
	static function getPercentageTable( $sub, $order, $referral ) {
		/**
		 * Get the rates
		 */
		$user_rate   = $sub->getUserRate();
		$min         = '';
		$return_rate = $user_rate;
		$extra_vat   = 0;
		$country     = $order->get_shipping_country();

		// Add VAT on CY when tax number is set
		if ( $country === "CY" ) {


		}

		$total = round( $order->get_subtotal() - $order->get_discount_total() + $extra_vat, 2 );

		$amount       = $referral->amount;
		$amount_check = $user_rate * ( $total / 100 );
		$extra        = '';

		if ( user_can( get_current_user_id(), "administrator" ) ) {
			// $extra = 'ONLY TESTING - ONLY ADMINS SEE THIS Total:'.$total.' Amount:'.$amount . ' amount-check:'.$amount_check. ' ORDER ID ';
		}

		/**
		 * Check if there is a new rate
		 */
		// if ( $amount_check != $amount ) {
		$min            = $amount_check - $amount;
		$min_percentage = round( 100 * ( $min / $total ), 0 );

		if ( $min_percentage > $user_rate ) {
			$min_percentage = $user_rate;
		} elseif ( $min_percentage < 0 ) {
			$min_percentage = 0;

		}

		$min      = ' - ' . $min_percentage . '%';
		$new_rate = $user_rate - $min_percentage;
		$min      .= ' = ' . $new_rate . '%';

		// }

		return $user_rate . '%' . $min . $extra;
	}

	/**
	 * Make payout array per affiliate
	 * @param $referrals
	 *
	 * @return array
	 */
    static function countPerRef($referrals,$date_to=0,$date_from=0){

    	$new_array = array();

    	foreach ($referrals as $id => $ref){

			    /**
			     * Filter out by date paid
			     */
			    $date_paid  = get_post_meta( $ref->reference, "_paid_date", true );
			    $date_paid  = strtotime( $date_paid );
			    $start_date = strtotime( $date_from . ' 00:00' );
			    $end_date   = strtotime( $date_to . ' 23:59:59' );


			    if ( $date_paid > $end_date OR $date_paid < $start_date ) {
				    continue;
			    }

    		if(isset($new_array[$ref->affiliate_id])) {
			    $new_array[ $ref->affiliate_id ]["amount"] += $ref->amount;
			    $new_array[$ref->affiliate_id]["refs"] += 1;

			    if($ref->status != $new_array[$ref->affiliate_id]["status"] ){
			    	if($new_array[$ref->affiliate_id]["status"] != 'partially paid'){
					    $new_array[$ref->affiliate_id]["status"] = 'partially paid';
				    }
			    }

		    }else{
			    $new_array[ $ref->affiliate_id ]["amount"] = $ref->amount;
			    $new_array[ $ref->affiliate_id ]["affiliate_id"] = $ref->affiliate_id;
			    $new_array[$ref->affiliate_id]["name"] = affiliate_wp()->affiliates->get_affiliate_name($ref->affiliate_id);
			    $new_array[$ref->affiliate_id]["email"] = affwp_get_affiliate_email($ref->affiliate_id);
			    $new_array[$ref->affiliate_id]["refs"] = 1;
			    $new_array[$ref->affiliate_id]["status"] = $ref->status;
		    }
	    }

    	return $new_array;

    }

	/**
	 * Get all clients, including the ones from the partners
	 * @param $aff_id
	 *
	 * @return array
	 */
    public static function getAllCustomersFromPartnerAndSubs($aff_id,$addPartners = false,$allow_inactive = 1,$everyone=false){

	    global $wpdb;

	    // Add to array
	    $childeren_mysql = "";

	    $sub = new SubAffiliate($aff_id);
	    $childeren = $sub->getAllChildren($allow_inactive,false,$everyone);

	    if($childeren != null) {
		    $childeren = self::getAllIdsFromSubs( $childeren );
		    $childeren[] = $aff_id;
		    $childeren_mysql = join( "','", $childeren );
	    }else{
	    	// No subs, so only add current partner
		    $childeren_mysql = $aff_id;
	    }

	    $query = $wpdb->get_results("SELECT {$wpdb->prefix}affiliate_wp_customermeta.affwp_customer_id,{$wpdb->prefix}affiliate_wp_customers.user_id FROM {$wpdb->prefix}affiliate_wp_customermeta INNER JOIN {$wpdb->prefix}affiliate_wp_customers ON  {$wpdb->prefix}affiliate_wp_customermeta.affwp_customer_id = {$wpdb->prefix}affiliate_wp_customers.customer_id WHERE {$wpdb->prefix}affiliate_wp_customermeta.meta_key='affiliate_id' AND meta_value IN ('{$childeren_mysql}')");
	    $customers = array();

	    if(count($wpdb->last_result) > 0){
	    	foreach ($wpdb->last_result as $customer){

				$show = true;
	    		$active = get_user_meta($customer->user_id,"ascension_status",true);
	    		// Do not show inactive if not asked for
	    		if($active === "non-active" && $allow_inactive === false){
	    			$show = false;
			    }

	    		// Only add active customers
	    		if($show === true) {
				    $customers[] = affwp_get_customer( $customer->affwp_customer_id );
			    }
		    }
	    }

	    // Add partners if needed
	    if($addPartners == true){

	    	if($childeren != null){

	    		foreach ($childeren as $c){

	    			if($c == $aff_id){
	    				continue;
				    }

					$s = new SubAffiliate($c);
					$customer_id = Helpers::getCustomerByUserId($s->getUserId());
				    $customers[] = affwp_get_customer( $customer_id);
			    }
		    }

	    }

	    return $customers;

    }

	/**
	 * Check if is a client of partner or sub partner
	 * @param $client
	 * @param $affiliate
	 *
	 * @return bool
	 */
    public static function isClientOfPartnerOfSubPartner($client,$affiliate){

	    global $wpdb;

	    // Add to array
	    $childeren_mysql = $affiliate;

	    $sub = new SubAffiliate($affiliate);
	    $childeren = $sub->getAllChildren();

	    if($childeren != null) {
		    $childeren = self::getAllIdsFromSubs( $childeren,$affiliate );
		    $childeren_mysql = join( "','", $childeren );
	    }

	    // Always true
	    if(NationalManager::isNationalManger(get_current_user_id())){
	    	return true;
	    }

	    $query = $wpdb->get_results("SELECT affwp_customer_id FROM {$wpdb->prefix}affiliate_wp_customermeta WHERE meta_key='affiliate_id' AND meta_value IN ('{$childeren_mysql}') AND affwp_customer_id='{$client}'");

	    if(count($wpdb->last_result) > 0){
	    	return true;
	    }else{
	    	return false;
	    }

    }

    private static function getAllIdsFromSubs($subs,$aff=false){

    	$return = array();

    	if($aff !== false){
    		$return[] = $aff;
	    }

    	foreach($subs as $s){
    		$return[] = $s->getId();
	    }

    	return $return;

    }

	public static function getCustomerByUserId($user_id)
	{

		global $wpdb;
		$query = $wpdb->get_row("SELECT customer_id FROM {$wpdb->prefix}affiliate_wp_customers WHERE user_id='" . $user_id . "'");

		if (isset($query->customer_id)) {
			return $query->customer_id;
		}
		return 0;
	}

	public static function getParentByCustomerId($customer_id)
	{
		global $wpdb;
		$query = $wpdb->get_row( "SELECT meta_value FROM {$wpdb->prefix}affiliate_wp_customermeta WHERE affwp_customer_id='" . $customer_id . "' AND meta_key='affiliate_id'" );

		if ( isset( $query->meta_value ) ) {
			return $query->meta_value;
		}

		return 0;
	}


	/**
	 * Calculate amounts without & with vat
	 *
	 * @param $ref
	 *
	 * @return array
	 *
	 */
	public static function calculateExIncVat( $ref ) {
		$order_id      = $ref->reference;
		$order         = new \WC_Order( $order_id );
		$user          = $order->get_user();
		$fee_total     = 0;
		$fee_total_tax = 0;

		// Iterating through order fee items ONLY
		foreach ( $order->get_items( 'fee' ) as $item_id => $item_fee ) {

			// The fee total amount
			$fee_total += $item_fee->get_total();

			// The fee total tax amount
			$fee_total_tax += $item_fee->get_total_tax();

		}

		// Fix issue on zero vat
		if ( $order->get_total_tax() <= 0 ) {
			$fee_total     = 0;
			$fee_total_tax = 0;
		}

		$return       = array();
		$return["ex"] = $order->get_total() - $fee_total - $fee_total_tax;
		$return["in"] = $order->get_total() - $order->get_total_tax() - $fee_total;

		return $return;
	}

}