<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 12/07/2019
 * Time: 21:59
 */

namespace AscensionShop\Woocommerce;


class OrderHooks
{

    function __construct()
    {
        // Add lang of store to order, for admin management
        add_action('woocommerce_checkout_create_order', array($this, 'addStoreLangToOrder'), 20, 2);
        add_action('woocommerce_order_status_pending', array($this, 'addStoreLangToOrder'), 20, 2);
    }

    /**
     * Add the store lang to order, for admin management :)
     * @param $order
     * @param $data
     */
    public function addStoreLangToOrder($order, $data)
    {
        $store_manager_ln = ICL_LANGUAGE_CODE;
        $order = new \WC_Order($order);
        $order->update_meta_data('_store_manager_ln', $store_manager_ln);
    }

}