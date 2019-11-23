<?php
/**
 * Created by PhpStorm.
 * User: Tim Van Onckelen
 * Date: 4/09/2019
 * Time: 12:44
 */

namespace AscensionShop\Woocommerce;


use AscensionShop\Lib\TemplateEngine;

class MyOrders
{

    public function __construct()
    {
        add_action("woocommerce_after_account_orders", array($this, "ordersForCustomers"));
        add_action("woocommerce_my_account_my_orders_actions", array($this, "filterOutOrdersByParent"), 10, 2);
        add_action("woocommerce_my_account_my_orders_column_order-total", array($this, "filterOutTotalAmount"), 10, 1);
        add_action("woocommerce_view_order", array($this, "removeOrderFromParent"), 5, 1);
        add_filter("wpo_wcpdf_check_privs", array($this, "checkIfInvoiceIsAvailable"), 10, 2);
    }

    public function ordersForCustomers($has_orders)
    {
        wp_enqueue_style("ascension-info-css", XE_ASCENSION_SHOP_PLUGIN_DIR . "/assets/css/refferal-order-info.min.css");

        $this->getOrdersFromClients();

    }

    private function getOrdersFromClients()
    {

        $user_id = affwp_get_affiliate_id(get_current_user_id());

        global $wpdb;
        $table_name = $wpdb->prefix . 'postmeta';
        $children = $wpdb->get_results("SELECT * FROM {$table_name} WHERE meta_key = '_ascension_order_maker' AND meta_value='{$user_id}' ORDER BY post_id DESC", OBJECT);
        $orders = array();

        if (count($children) > 0) {
            foreach ($children as $item) {
                $orders[] = new \WC_Order($item->post_id);
            }

            $t = new TemplateEngine();
            $price = array_column($orders, 'ID');

            $t->orders = $orders;
            echo $t->display("woocommerce/client-orders.php");
        }


    }

    public function filterOutOrdersByParent($actions, $order)
    {

        // Get order maker
        $parent_aff_id = get_post_meta($order->get_id(), '_ascension_order_maker', true);
        $payer = get_post_meta($order->get_id(), '_ascension_order_payer', true);
        $parent_id = affwp_get_affiliate_user_id($parent_aff_id);
        $parent_name = affwp_get_affiliate_name($parent_aff_id);

        if ($parent_id == get_current_user_id()) {
            return $actions;
        }


        // Nothing to do, just go on :)
        if ($parent_id <= 0 OR $payer === true) {
            return $actions;
        }

        foreach ($actions as $key => $item) {
            unset($actions[$key]);
        }

        printf(__("Bestelling betaald door %s - Neem contact op voor meer info.", "ascension-shop"), $parent_name);

        return $actions;

    }

    public function filterOutTotalAmount($order)
    {
        // Get order maker
        $parent_aff_id = get_post_meta($order->get_id(), '_ascension_order_maker', true);
        $payer = get_post_meta($order->get_id(), '_ascension_order_payer', true);
        $parent_id = affwp_get_affiliate_user_id($parent_aff_id);
        $parent_name = affwp_get_affiliate_name($parent_aff_id);
        $total = $order->get_total();


        if ($parent_id == get_current_user_id()) {
            echo "&euro;" . $total;
            return;
        }

        // Nothing to do, just go on :)
        if ($parent_id <= 0 OR $payer == 1) {
            echo "&euro;" . $total;
            return;

        }

        _e("Niet beschikbaar", "ascension-shop");
    }

    public function removeOrderFromParent($order)
    {

        $order = new \WC_Order($order);
        // Get order maker
        $parent_aff_id = get_post_meta($order->get_id(), '_ascension_order_maker', true);
        $payer = get_post_meta($order->get_id(), '_ascension_order_payer', true);
        $parent_id = affwp_get_affiliate_user_id($parent_aff_id);
        $parent_name = affwp_get_affiliate_name($parent_aff_id);

        if ($parent_id == get_current_user_id()) {
            return;
        }

        // Nothing to do, just go on :)
        if ($parent_id <= 0 OR $payer == 1) {
            return;
        }

        // Else remove order details
        remove_action('woocommerce_view_order', 'woocommerce_order_details_table', 10);
        return printf(__("Bestelling door %s betaald - Neem contact op voor meer info.", "ascension-shop"), $parent_name);

    }

    public function checkIfInvoiceIsAvailable($allowed, $order_ids)
    {

        // User who can edit always have access to posts
        if(current_user_can("manage_woocommerce")){
            return true;
        }


        $order = new \WC_Order($order_ids[0]);
        // Get order maker
        $parent_aff_id = get_post_meta($order->get_id(), '_ascension_order_maker', true);
        $payer = get_post_meta($order->get_id(), '_ascension_order_payer', true);
        $parent_id = affwp_get_affiliate_user_id($parent_aff_id);
        $parent_name = affwp_get_affiliate_name($parent_aff_id);

        if ($parent_id == get_current_user_id()) {
            return $allowed;
        }

        // Nothing to do, just go on :)
        if ($parent_id <= 0 OR $payer == 1) {
            return $allowed;
        }

        // Not allowed, because order made by parent
        return false;

    }


}