<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 22/08/2019
 * Time: 18:04
 */

namespace AscensionShop\Woocommerce;


class CustomerPayGateway extends \WC_Payment_Gateway
{

    public function __construct()
    {
        $this->id = "ascension_customer_gateway";
        $this->icon = apply_filters('woocommerce_bacs_icon', '');
        $this->has_fields = false;
        $this->method_title = __('Ascension customer gateway', 'ascension-shop');
        $this->method_description = "A gateway to manage affiliate orders for clients";
        $this->init_form_fields();
    }


    public function init_form_fields()
    {

        $this->form_fields = apply_filters('wc_offline_form_fields', array(

            'enabled' => array(
                'title' => __('Enable/Disable', 'ascension-shop'),
                'type' => 'checkbox',
                'label' => __('Enable Offline Payment', 'ascension-shop'),
                'default' => 'yes'
            ),

            'title' => array(
                'title' => __('Title', 'ascension-shop'),
                'type' => 'text',
                'description' => __('This controls the title for the payment method the customer sees during checkout.', 'ascension-shop'),
                'default' => __('Klant logt in en betaald zelf', 'ascension-shop'),
                'desc_tip' => true,
            ),

            'description' => array(
                'title' => __('Description', 'ascension-shop'),
                'type' => 'textarea',
                'description' => __('Payment method description that the customer will see on your checkout.', 'ascension-shop'),
                'default' => __('Please remit payment to Store Name upon pickup or delivery.', 'ascension-shop'),
                'desc_tip' => true,
            ),

        ));
    }

    public function process_payment($order_id)
    {

        $order = wc_get_order($order_id);

        // Mark as on-hold (we're awaiting the payment)
        //$order->update_status( 'on-hold', __( 'Wachten op betaling van klant. Betalingsinstructies zijn verzonden.', 'ascension-shop' ) );

        // Reduce stock levels
        //$order->reduce_order_stock();

        $pay_now_url = esc_url($order->get_checkout_payment_url());
        $this->instructions = sprintf(__("De bestelling kan nu worden betaald via <a href='%s'>deze link.</a>", "ascension-shop"), $pay_now_url);

        // Remove cart
        WC()->cart->empty_cart();

        // Reset session vars
        WC()->session->set('ascension_affiliate_client_id_order', false);
        WC()->session->set('ascension_affiliate_who_pays_order', false);

        // Send an email with instructions
        WC()->mailer()->get_emails()['WC_Email_Customer_Invoice']->trigger($order_id);


        // Return thankyou redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }

    /**
     * Output for the order received page.
     * @param $order_id
     */
    public function thankyou_page($order_id)
    {

        $order = new \WC_Order($order_id);
        $pay_now_url = esc_url($order->get_checkout_payment_url());
        //$instructions = sprintf(__("De bestelling kan nu worden betaald via <a href='%s'>deze link.</a>", "ascension-shop"), $pay_now_url);
        // echo wpautop( wptexturize($instructions ) );

    }


    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {


        if (!$sent_to_admin) {
            $pay_now_url = esc_url($order->get_checkout_payment_url());
            $instructions = sprintf(__("De bestelling kan nu worden betaald via <a href='%s'>deze link.</a>", "ascension-shop"), $pay_now_url);
            echo wpautop(wptexturize($instructions));

        }
    }


}