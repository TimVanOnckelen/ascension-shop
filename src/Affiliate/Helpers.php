<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 2/08/2019
 * Time: 12:21
 */

namespace AscensionShop\Affiliate;


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
    	$totals = array();
    	$totals["total"] = 0;
    	$totals["pending"] = 0;
    	$totals["unpaid"] = 0;
    	$totals["paid"] = 0;

    	foreach($refs as $ref){
    		$totals["total"] += $ref->amount;

    		if($ref->status == 'unpaid'){
    			$totals["unpaid"] += $ref->amount;
		    }
		    if($ref->status == 'pending'){
			    $totals["pending"] += $ref->amount;
		    }
		    if($ref->status == 'paid'){
			    $totals["paid"] += $ref->amount;
		    }
	    }

    	return $totals;
    }

    static function getParentFromRef($referral){

    	$parent = '';

	    if(isset($referral->custom["parent"])) {
		    $parent = affiliate_wp()->affiliates->get_affiliate_name( $referral->custom["parent"] );
	    }elseif(strpos($referral->description, 'Indirect') !== false){
		    // Legacy support for orders before v1.0.5
		    $temp_parent = str_replace('Indirect Referral FROM','',$referral->description);
		    $temp_parent = explode("|",$temp_parent);
		    $parent = $temp_parent[0];
	    }

	    return $parent;
    }

    static function getPercentageTable($sub,$order,$referral){
	    /**
	     * Get the rates
	     */
	    $user_rate = $sub->getUserRate();
	    $min = '';
	    $return_rate = $user_rate;
	    $total = $order->get_subtotal();
	    $amount = round( $referral->amount, 2 );
	    $amount_check = round( $user_rate * ( $total / 100 ), 2 );

	    /**
	     * Check if there is a new rate
	     */
	    if ( $amount_check != $amount ) {
		    $min            = $amount_check - $amount;
		    $min_percentage = round( 100 * ( $min / $total ), 2 );
		    if ( $min_percentage > $user_rate ) {
			    $min_percentage = $user_rate;
		    }
		    $min      = ' - ' . $min_percentage . '%';
		    $new_rate = $user_rate - $min_percentage;
		    $min      .= ' = ' . $new_rate . '%';
	    }

	    return  $user_rate . '%' . $min;
    }

	/**
	 * Make payout array per affiliate
	 * @param $referrals
	 *
	 * @return array
	 */
    static function countPerRef($referrals){

    	$new_array = array();

    	foreach ($referrals as $ref){
    		if(isset($new_array[$ref->affiliate_id])) {
			    $new_array[ $ref->affiliate_id ]["amount"] += $ref->amount;
			    $new_array[$ref->affiliate_id]["refs"] += 1;
		    }else{
			    $new_array[ $ref->affiliate_id ]["amount"] = $ref->amount;
			    $new_array[ $ref->affiliate_id ]["affiliate_id"] = $ref->affiliate_id;
			    $new_array[$ref->affiliate_id]["name"] = affiliate_wp()->affiliates->get_affiliate_name($ref->affiliate_id);
			    $new_array[$ref->affiliate_id]["email"] = affwp_get_affiliate_email($ref->affiliate_id);
			    $new_array[$ref->affiliate_id]["refs"] = 1;

		    }
	    }

    	return $new_array;

    }

}