<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 4/08/2019
 * Time: 18:13
 */

namespace AscensionShop\Affiliate;


class Activation
{

    public function __construct()
    {

        // Run hook;
        register_activation_hook(__FILE__, array($this, "addOriginalAmountColomn"));

    }

    public function addOriginalAmountColomn()
    {

        global $wpdb;

        // Add a custom amount colomn to db
        $query = "ALTER TABLE " . $wpdb->prefix . "affiliate_wp_referrals
ADD COLUMN xe_original_amount mediumtext 0;";

        // Add to db
        $wpdb->query($wpdb->prepare($query));
    }
}