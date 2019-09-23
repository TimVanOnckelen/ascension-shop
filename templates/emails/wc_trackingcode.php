<?php
/**
 * Tracking code is send to costumer
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @hooked WC_Emails::email_header() Output the email header
 */


$tracking = $order->get_meta('as_trackingcode');

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p><?php __( 'Je bestelling is verstuurd', 'ascension-shop' ); ?></p>

	<p><h3><?php _e("Je bestelling is verstuurd","ascension-shop") ?></h3></p>
<?php if(isset($tracking) && $tracking != ''){ ?>
    <p><b><h4><?php _e("Jouw trackingcode","ascension-shop"); ?><a href="https://ips.cypruspost.gov.cy/ipswebtrack/IPSWeb_item_events.aspx?itemid=<?php echo $order->get_meta('as_trackingcode'); ?>"><?php echo $order->get_meta('as_trackingcode'); ?></a></h4></b></p>
    <p><?php echo sprintf(__('Werkt bovenstaande link niet? Geef je code dan in via %s','ascension-shop'),"https://www.track-trace.com/post"); ?></p>
    <?php } ?>
	<p><?php echo __( 'Ter referentie vindt u hieronder een overzicht van uw bestelling :', 'ascension-shop' ); ?></p>

<h2><?php _e("Bestelling","ascension-shop"); echo " #".$order->get_id(); ?></h2>
<table style="border:1px solid #e8e8e8">
    <thead>
    <tr><th>Product</th><th>Aantal</th></tr>
    </thead>
<?php
$items = $order->get_items();

foreach ( $order->get_items() as $item ) {
    ?>
    <tr><td><?php echo $item["name"]; ?></td><td><?php echo $item["qty"]; ?></td></tr>
    <?php
}
?>
</table>
<?php
/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
// do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
