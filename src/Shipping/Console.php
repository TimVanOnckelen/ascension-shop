<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 10/07/2019
 * Time: 14:38
 */

namespace AscensionShop\Shipping;


use AscensionShop\Lib\TemplateEngine;

class Console
{

    public function __construct()
    {

        // Load ConsolePage
        // add_action( 'plugins_loaded', array($this,'loadConsole') );
        add_action("admin_menu", array($this, "addConsolePage"));

        // Filter for Shipping console
        add_filter('woocommerce_order_data_store_cpt_get_orders_query', array($this, 'handle_custom_query_var'), 10, 2);

        // Load JS
        add_action('admin_enqueue_scripts', array($this, "enqueue_console_js"));

    }

    /**
     * Add the console page to admin
     */
    public function addConsolePage()
    {
        add_submenu_page(
            'woocommerce',
            'Shipping Console',
            'Shipping Console',
            'manage_woocommerce',
            'ascension-shop-shipping-console',
            array($this, 'loadConsole')
        );
    }

    /**
     * View the console
     */
    public function loadConsole()
    {

        global $wp;

        $t = new TemplateEngine();

        $status = "null";

        // Make searching by status possible
        if (isset($_GET["shipping_status"])) {
            $status = $_GET["shipping_status"];
        }

        $t->orders = $this->getAllOrders($status);
        $t->currentUrl = add_query_arg($wp->query_string, '', admin_url('admin.php?page=ascension-shop-shipping-console'));

        echo $t->display("admin/console.php");

    }

    /**
     * Get orders by current admin lang
     * @param $status
     * @return mixed
     */
    private function getAllOrders($status)
    {

        $current_lang = ICL_LANGUAGE_CODE;

        if ($status != "completed") {
            $extra = "wc-processing";
        } else {
            $extra = "wc-completed";
        }

        return wc_get_orders(
            array(
                "_store_manager_ln" => $current_lang,
                "as-tracking-status" => $status,
                "status" => $extra,
                "limit" => -1
            )
        );

    }

    /**
     * Handle a custom 'customvar' query var to get orders with the 'customvar' meta.
     * @param array $query - Args for WP_Query.
     * @param array $query_vars - Query vars from WC_Order_Query.
     * @return array modified $query
     */
    public function handle_custom_query_var($query, $query_vars)
    {
        if (!empty($query_vars['wpml_language'])) {
            $query['meta_query'][] = array(
                'key' => 'wpml_language',
                'value' => esc_attr($query_vars['wpml_language']),
            );
        }

        // Filter by tracking code status
        if (!empty($query_vars['as-tracking-status'])) {

            // The null fix
            if ($query_vars['as-tracking-status'] == "null") {
                // Add the meta query var
                $query['meta_query'][] = array(
                    'key' => 'as-tracking-status',
                    'compare' => 'NOT EXISTS',
                    'value' => 'null',
                );
            } else {
                // Add the meta query var
                $query['meta_query'][] = array(
                    'key' => 'as-tracking-status',
                    'value' => esc_attr($query_vars['as-tracking-status']),
                );
            }


        }

        return $query;
    }

    /**
     * Enqueue console js
     * @param $hook
     */
    public function enqueue_console_js($hook)
    {

        // Do not add on wrong page
        if ($hook != 'woocommerce_page_ascension-shop-shipping-console') {
            return;
        }
        // Add jquery
        wp_enqueue_script("jquery");
        wp_enqueue_script("shipping-console", XE_ASCENSION_SHOP_PLUGIN_DIR . "src/Assets/js/shipping-console.js");

        // Add details
        wp_localize_script('shipping-console', 'ascensionshop', array(
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ));

    }
}