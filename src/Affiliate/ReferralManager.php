<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 3/08/2019
 * Time: 19:27
 */

namespace AscensionShop\Affiliate;


class ReferralManager
{

    private $waterfall_log;

    private $order;
    public $context = "woocommerce";
    private $amount_array = array();

    private $fee_added = false;

    public function __construct()
    {
        // On insert Referral
        add_action("affwp_post_insert_referral", array($this, "prepare_indirect_referrals"), 10, 2);

        add_filter("affwp_insert_pending_referral", array($this, "filterOutFees"), 10, 8);

        // Amount filter!
        add_filter("affwp_calc_referral_amount", array($this, "filterAmount"), 10, 6);

        // Woocommerce hooks on
        add_action('woocommerce_order_status_completed', array($this, 'mark_referrals_complete'), 5);
        add_action('woocommerce_order_status_processing', array($this, 'mark_referrals_complete'), 5);

        // Handle order updates/cancellations
        add_action('woocommerce_order_status_completed_to_refunded', array($this, 'revoke_referrals_on_refund'), 10);
        add_action('woocommerce_order_status_on-hold_to_refunded', array($this, 'revoke_referrals_on_refund'), 10);
        add_action('woocommerce_order_status_processing_to_refunded', array($this, 'revoke_referrals_on_refund'), 10);
        add_action('woocommerce_order_status_processing_to_cancelled', array($this, 'revoke_referrals_on_refund'), 10);
        add_action('woocommerce_order_status_completed_to_cancelled', array($this, 'revoke_referrals_on_refund'), 10);
        add_action('woocommerce_order_status_pending_to_cancelled', array($this, 'revoke_referrals_on_refund'), 10);
        add_action('woocommerce_order_status_pending_to_failed', array($this, 'revoke_referrals_on_refund'), 10);
        add_action('wc-on-hold_to_trash', array($this, 'revoke_referrals_on_refund'), 10);
        add_action('wc-processing_to_trash', array($this, 'revoke_referrals_on_refund'), 10);
        add_action('wc-completed_to_trash', array($this, 'revoke_referrals_on_refund'), 10);

    }


    /**
     * @param $args
     * @param $amount
     * @param $reference
     * @param $description
     * @param $aff_id
     * @param $visit_id
     * @param $data
     * @param $context
     *
     * @return mixed
     */
    public function filterOutFees($args, $amount, $reference, $description, $aff_id, $visit_id, $data, $context)
    {

        $client_fee = $this->checkForClientFees($reference);

        // error_log(json_encode($args));
        // error_log($client_fee);

        $new_amount = $args["amount"] - $client_fee;
        // error_log(round($new_amount,2));
        $args["amount"] = $new_amount;

        return $args;

    }


    /**
     * Filter out the amount
     * @param $referral_amount
     * @param $affiliate_id
     * @param $amount
     * @param $reference
     * @param $product_id
     * @param $context
     *
     * @return float|int
     */
    public function filterAmount($referral_amount, $affiliate_id, $amount, $reference, $product_id, $context)
    {

        if ($amount <= 0) {
            return $amount;
        }

        // Check for client fees
        $client_fee = $this->checkForClientFees($reference);
        $already_added = get_transient($reference . "_added_fee");


        // Add client fee to first product
        if ($client_fee > 0 && $already_added < 0) {
            set_transient($reference . "_added_fee", $client_fee, 15);
            $amount = $amount + $client_fee;
        }


        $client_fee = 0;

        // get sub affiliate
        $sub = new SubAffiliate($affiliate_id);
        $parent = $sub->getParentId();
        $level = $sub->getLevel();

        // No parent, so nothing to do :)
        if ($level == 0) {
            // No level, than level 0
            if ($level === null OR $level == "") $level = 0;

            // get rate
            $rate = $sub->getUserRate();
            // Calculate amount with level rate
            $referral_amount = ($amount / 100 * $rate);

        } else {

            // Parents found
            // Calculate reference amounts
            $this->getReferenceTotalAmount($reference, $amount, $sub->getFullParentWaterfall(), $affiliate_id, $sub->getLevel(), $client_fee);

            // Get the amount for current product.
            $referral_amount = $this->amount_array[$sub->getLevel()];

        }


        return $referral_amount;

    }

    /**
     * Get or save original total amount
     * @param $reference
     * @param $amount
     *
     * @param null $parents
     * @param null $child
     * @param null $child_level
     * @param $client_fee
     * @return mixed
     */
    private function getReferenceTotalAmount($reference, $amount = null, $parents = null, $child = null, $child_level = null, $client_fee)
    {

        // The transient name
        $transient_name = $reference . "_total_amount";

        // Get the generated waterfall
        $waterfall = get_transient($transient_name);


        if (!isset($waterfall)) {

            // Add child to parents
            $parents[$child_level]["id"] = $child;
            $parents[$child_level]["level"] = $child_level;

            // Calculate waterfall
            $waterfall = $this->calculateAmount($parents, $amount, null, $child, $client_fee);
            set_transient($transient_name, $waterfall);
        } else {

            // Calculate waterfall
            $waterfall = $this->calculateAmount($parents, $amount, $waterfall, $child, $client_fee);
            set_transient($transient_name, $waterfall);


        }

        // error_log(json_encode($waterfall));

        return $waterfall;

    }

    private function getLevelAmount($reference, $level)
    {

        // The transient name
        $transient_name = $reference . "_total_amount";

        // Get the generated waterfall
        $waterfall = get_transient($transient_name);

        // Delete on the last parent
        if ($level == 0) {
            delete_transient($transient_name);
        }

        $result = $waterfall[$level];

        if ($result < 0) {
            $result = 0;
        }

        return $result;

    }

    /**
     * Calculate the amounts of the whole watefall
     * @param $waterfall
     * @param $amount
     *
     * @param array $old_waterfall
     * @param null $user_id
     * @param int $client_fee
     * @return array
     */
    private function calculateAmount($waterfall, $amount, $old_waterfall = array(), $user_id = null, $client_fee = 0)
    {

        $restAmount = $amount;
        // Old waterfall
        $resultWaterfall = $old_waterfall;

        $waterfallLength = count($waterfall);
        $counter = 0;
        /**
         * Loop over waterfall
         */
        foreach ($waterfall as $key => $i) {

            $counter++;

            // Nothing to do when level is wrong
            if (!($i["level"] > -1)) {
                continue;
            }

            $sub = new SubAffiliate($i["id"]);
            // Get rate of current user in level
            $rate = $sub->getUserRate();

            // If status is inactive, rate is zero
            if ($sub->getStatus() == false) {
                $rate = 0;
            }

            // Get value of current user
            $restAmount = (($amount / 100) * $rate);

            if ($restAmount < 0) {
                $restAmount = 0;
            }

            // For current product checking :)
            $this->amount_array[$i["level"]] = $restAmount;

            if (!isset($resultWaterfall[$i["level"]])) {
                $resultWaterfall[$i["level"]] = 0;
            }

            // Add the amount to the level
            $resultWaterfall[$i["level"]] = $resultWaterfall[$i["level"]] + $restAmount;

            // Remove client fee
            $resultWaterfall[$i["level"]] = $resultWaterfall[$i["level"]];


            // The level of the parent partner
            $prev_level = $i["level"] - 1;

            if ($prev_level >= 0) {

                if ($restAmount > 0) {
                    // Remove amount from parent
                    $resultWaterfall[$prev_level] = $resultWaterfall[$prev_level] - $restAmount;
                }

            }


        }

        /**
         * Fix all negatives generated by inactive partners or 0 rates
         */
        $resultWaterfall = $this->fixNegatives($resultWaterfall);

        // Loop over
        //error_log(json_encode($resultWaterfall));

        // Log the waterfall
        $this->waterfall_log = $resultWaterfall;

        // Return the result waterfall
        return $resultWaterfall;

    }

    /*
     * Get the client fee
     */
    private function checkForClientFees($order_id)
    {

        $order = new \WC_Order($order_id);

        if (!is_array($order->get_items('fee'))) {
            return;
        }

        foreach ($order->get_items('fee') as $item_id => $item_fee) {
            $name = $item_fee->get_name();

            if ($name == "Discount from Referring Parent") {
                return -$item_fee->get_total();
            }

            if ($name == "Affiliate Discount") {
                return -$item_fee->get_total();
            }


        }

        return 0;

    }

    /**
     * Fix negative numbers in waterfall
     * @param $waterfall
     *
     * @return mixed
     */
    private function fixNegatives($waterfall)
    {

        foreach ($waterfall as $key => $w) {

            if ($w < 0 && $key != 0) {
                $prev_level = $key - 1;
                $waterfall[$prev_level] = $waterfall[$prev_level] + $w;
                // SET ITSELF TO 0
                $waterfall[$key] = 0;
            }
        }

        return $waterfall;
    }

    /**
     * Determines if indirect referrals should be created and generates the upline.
     *
     * @access  public
     * @since   1.1
     * @param $referral_id
     * @param $data
     */
    public function prepare_indirect_referrals($referral_id, $data)
    {


        $affiliate_id = $data['affiliate_id'];
        $data['custom'] = maybe_unserialize($data['custom']);
        $referral = affiliate_wp()->referrals->get_by('referral_id', $referral_id, "wordpress");
        $referral_type = 'direct';

        if (empty($referral->custom)) {

            // Prevent overwriting subscription id
            if (empty($data['custom'])) {

                // Add referral type as custom referral data for direct referral
                affiliate_wp()->referrals->update($referral->referral_id, array('custom' => $referral_type), '', 'referral');

            }

        } elseif ($referral->custom == 'indirect') {
            return; // Prevent looping through indirect referrals
        }


        $sub = new SubAffiliate($affiliate_id);
        $parent = $sub->getParentId();
        $parent_object = new SubAffiliate($parent);

        // Add if user has a parent :)
        if ($parent >= 1) {
            // Add a parent
            $this->addParent($parent, $referral_id, $data, $parent_object->getLevel(), $affiliate_id);
        }

        return;
    }


    /**
     * Add parent
     * @param $parent_affiliate_id
     * @param $referral_id
     * @param $data
     * @param int $level_count
     * @param $affiliate_id
     */
    public function addParent($parent_affiliate_id, $referral_id, $data, $level_count = 0, $affiliate_id)
    {

        $this->order = new \WC_Order($data["reference"]);

        $direct_affiliate = affiliate_wp()->affiliates->get_affiliate_name($affiliate_id);

        $sub = new SubAffiliate($parent_affiliate_id);

        // Process cart and get amount
        $waterfall_amounts = $this->getLevelAmount($data["reference"], $sub->getLevel());


        $data['affiliate_id'] = $parent_affiliate_id;
        $data['description'] = $this->get_referral_description($level_count, $direct_affiliate);
        // $referral_amount, $affiliate_id, $amount, $reference, $product_id, $context
        $data['amount'] = $waterfall_amounts;
        $data['custom'] = 'indirect'; // Add referral type as custom referral data
        $data['context'] = 'woocommerce';

        unset($data['date']);
        unset($data['currency']);
        unset($data['status']);


        // create referral
        $referral_id = affiliate_wp()->referrals->add(apply_filters('ascension-shop-insert_pending_referral', $data, $parent_affiliate_id, $affiliate_id, $referral_id, $level_count));

        if ($referral_id) {

            $amount = affwp_currency_filter(affwp_format_amount($waterfall_amounts));
            $name = affiliate_wp()->affiliates->get_affiliate_name($parent_affiliate_id);

            $this->order->add_order_note(sprintf(__('Indirect Referral #%d for %s recorded for %s', 'asencion-shop'), $referral_id, $amount, $name));

            do_action('ascension-shop-indirect-referral-created', $referral_id, $data);

        }

    }

    /**
     * Return full total
     * @param $waterfall
     *
     * @return int
     */
    private function countFullTotal($waterfall)
    {

        $full_Total = 0;

        foreach ($waterfall as $price) {
            $full_Total += $price;
        }

        return $full_Total;

    }

    /**
     * Retrieve the WooCommerce referral description
     *
     * @since   1.0
     * @param $level_count
     * @param $direct_affiliate
     * @return string
     */
    public function get_referral_description($level_count, $direct_affiliate)
    {

        $items = $this->order->get_items();
        $description = array();
        $item_names = array();


        $description[] = 'Indirect Referral FROM ' . $direct_affiliate . ' | Level ' . $level_count . ' | ' . implode(', ', $item_names);

        return implode(', ', $description);

    }

    /**
     * Mark referrals as complete
     *
     * @since 1.0
     * @param int $order_id
     * @return bool|void
     */
    public function mark_referrals_complete($order_id = 0)
    {

        if (empty($order_id)) {
            return false;
        }

        $this->order = apply_filters('affwp_get_woocommerce_order', new \WC_Order($order_id));

        if (true === version_compare(WC()->version, '3.0.0', '>=')) {
            $payment_method = $this->order->get_payment_method();
        } else {
            $payment_method = get_post_meta($order_id, '_payment_method', true);
        }

        // If the WC status is 'wc-processing' and a COD order, leave as 'pending'.
        if ('wc-processing' == $this->order->get_status() && 'cod' === $payment_method) {
            return;
        }

        $reference = $order_id;
        $referrals = Helpers::getReferralsFromOrder($order_id, $this->context);

        // error_log($order_id.".".$this->context);

        if (empty($referrals)) {
            return false;
        }

        foreach ($referrals as $referral) {

            $this->complete_referral($referral, $reference);

        }

    }

    /**
     * Revoke referrals when an order is refunded
     *
     * @since 1.0
     * @param int $order_id
     */
    public function revoke_referrals_on_refund($order_id = 0)
    {

        if (empty($order_id)) {
            return;
        }

        if (is_a($order_id, 'WP_Post')) {
            $order_id = $order_id->ID;
        }

        if (!affiliate_wp()->settings->get('revoke_on_refund')) {
            return;
        }

        if ('shop_order' != get_post_type($order_id)) {
            return;
        }

        $referrals = Helpers::getReferralsFromOrder($order_id, $this->context);

        if (empty($referrals)) {
            return;
        }

        foreach ($referrals as $referral) {

            $this->reject_referral($referral);

        }

    }

    /**
     * Completes a referral. Used when orders are marked as completed
     *
     * @access  public
     * @since   1.0
     * @param $referral
     * @param   $reference The reference column for the referral to complete per the current context
     * @return  bool
     */
    public function complete_referral($referral, $reference)
    {
        if (empty($reference)) {
            return false;
        }

        if (!$referral) {

            $referral = affiliate_wp()->referrals->get_by('reference', $reference, $this->context);
        }

        if (empty($referral)) {
            return false;
        }

        if (is_object($referral) && $referral->status != 'pending') {
            // This referral has already been completed, rejected, or paid
            return false;
        }

        if (!apply_filters('ascension-shop_auto_complete_referral', true))
            return false;

        if (affwp_set_referral_status($referral->referral_id, 'unpaid')) {

            do_action('ascension-shop_complete_referral', $referral->referral_id, $referral, $reference);

            do_action('ascension-shop_mlm_complete_referral', $referral->referral_id, $referral, $reference);

            return true;
        }

        return false;

    }

    /**
     * Rejects a referal. Used when orders are refunded, deleted, or voided
     *
     * @access  public
     * @since   1.0
     * @param $referral
     * @return  bool
     */
    public function reject_referral($referral)
    {

        if (empty($referral)) {
            return false;
        }

        if (is_object($referral) && 'paid' == $referral->status) {
            // This referral has already been paid so it cannot be rejected
            return false;
        }

        if (affiliate_wp()->referrals->update($referral->referral_id, array('status' => 'rejected'), '', 'referral')) {

            return true;

        }

        return false;

    }
}
