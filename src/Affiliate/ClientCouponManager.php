<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 13/08/2019
 * Time: 12:47
 */

namespace AscensionShop\Affiliate;


class ClientCouponManager
{

    public function __construct()
    {

        add_action('woocommerce_cart_calculate_fees', array($this, "addUserDiscount"));
        add_action('woocommerce_cart_calculate_fees', array($this, "discountAsAffiliate"));

        add_action('admin_post_ascension-save_customer-discount', array($this, "saveDiscounts"), 10, 1);
        add_filter('woocommerce_cart_totals_get_fees_from_cart_taxes', array($this, "filterOutTax"), 10, 3);
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

        if ($aff_id != false) {

            $sub = new SubAffiliate($aff_id);
            $rate = $sub->getUserRate();

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

            $user_id = get_current_user_id();
            $user_id = apply_filters("ascension_user_id_coupons", $user_id);

            $rate = $this->customerHasDiscount($user_id);

            if ($rate > 0) {

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

        if ($affiliate_id !== false) {

            if ($nonce_verify == true) {
                foreach ($_REQUEST["customer_rate"] as $id => $rate) {

                    // Update rate if customer is from current user
                    if ($this->isCustomerFromCurrentUser($id) === true) {
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
        $customers = affiliate_wp_lifetime_commissions()->integrations->get_customers_for_affiliate($affiliate_id);

        foreach ($customers as $c) {
            if ($c_id === $c->user_id) {
                return true;
            }
        }

        return false;

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
}