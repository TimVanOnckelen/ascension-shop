<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 16/07/2019
 * Time: 19:28
 */

namespace AscensionShop\Shipping\emails;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('WC_Email')) {
    return;
}


class TrackingCode_Email extends \WC_Email
{
    /**
     * Create an instance of the class.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Email slug we can use to filter other data.
        $this->id = 'WC_trackingcode';
        $this->title = __('Je bestelling is verstuurd', 'ascension-shop');
        $this->description = __('Hier is de trackingcode van jouw bestelling.', 'ascension-shop');
        // For admin area to let the user know we are sending this email to customers.
        $this->customer_email = true;
        $this->heading = __('Je bestelling is verstuurd', 'ascension-shop');
        // translators: placeholder is {blogname}, a variable that will be substituted when email is sent out
        $this->subject = sprintf(_x('[%s] - Jouw bestelling is verzonden', 'Jouw bestelling is verzonden', 'ascension-shop'), '{blogname}');

        // Template paths.
        $this->template_html = 'emails/wc_trackingcode.php';
        //$this->template_plain = 'emails/plain/wc-customer-cancelled-order.php';
        $this->template_base = XE_ASCENSION_SHOP_PLUGIN_TEMPLATE_PATH;

        // Action to which we hook onto to send the email.
        add_action('ascension_send_trackingcode', array($this, 'trigger'), 10, 1);
        // COnstruct current function
        parent::__construct();
    }

    /**
     * Trigger send email
     * @param $order_id
     */
    public function trigger($order_id)
    {

        $this->object = wc_get_order($order_id);
        if (version_compare('3.0.0', WC()->version, '>')) {
            $order_email = $this->object->billing_email;
        } else {
            $order_email = $this->object->get_billing_email();
        }
        $this->recipient = $order_email;
        if (!$this->is_enabled() || !$this->get_recipient()) {
            return;
        }
        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    public function get_content_html()
    {
        return wc_get_template_html($this->template_html, array(
            'order' => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text' => false,
            'email' => $this
        ), '', $this->template_base);
    }

    /**
     * Get content plain.
     *
     * @return string
     */
    public function get_content_plain()
    {
        return wc_get_template_html($this->template_plain, array(
            'order' => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text' => true,
            'email' => $this
        ), '', $this->template_base);
    }

}