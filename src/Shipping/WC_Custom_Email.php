<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 16/07/2019
 * Time: 19:27
 */

namespace AscensionShop\Shipping;


use AscensionShop\Shipping\emails\TrackingCode_Email;

class WC_Custom_Email
{

    /**
     * Custom_WC_Email constructor.
     */
    public function __construct()
    {
        // Filtering the emails and adding our own email.
        add_action('woocommerce_email_classes', array($this, 'register_email'), 10, 1);

    }

    /**
     * @param array $emails
     *
     * @return array
     */
    public function register_email($emails)
    {

        $emails['WC_trackingcode'] = new TrackingCode_Email();
        return $emails;
    }


}