<?php

use AscensionShop\Lib\TemplateEngine;

$affiliate_id = affwp_get_affiliate_id();
$customers    = affiliate_wp_lifetime_commissions()->integrations->get_customers_for_affiliate( $affiliate_id );

do_action("ascension-add-client");

?>
