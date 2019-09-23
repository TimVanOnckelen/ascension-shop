<h2><?php _e("Shipping console","ascension-shop"); ?> (<?php echo ICL_LANGUAGE_CODE; ?>)</h2>
<ul>
    <li><a href="<?php echo $this->currentUrl; ?>&shipping_status=null"><?php _e("Openstaande bestellingen","ascension-shop"); ?></a> </li>
    <li><a href="<?php echo $this->currentUrl; ?>&shipping_status=completed"><?php _e("Afgeronde bestellingen","ascension-shop"); ?></a> </li>
</ul>
<table class="widefat fixed striped posts" cellspacing="0" >
    <thead>
    <th><?php _e("Order ID","ascension-shop"); ?></th>
    <th><?php _e("Klant","ascension-shop"); ?></th>
    <th><?php _e("Adres","ascension-shop"); ?></th>
    <th><?php _e("Klant email","ascension-shop"); ?></th>
    <th><?php _e("Notities","ascension-shop"); ?></th>
    <th><?php _e("Order status","ascension-shop"); ?></th>
    <th><?php _e("Shipping pdf","ascension-shop"); ?></th>
    <th><?php _e("Tracking code","ascension-shop"); ?></th>
    <th><?php _e("Acties","ascension-shop"); ?></th>
    </thead>
    <tbody>
	<?php
	/**
	 * Created by PhpStorm.
	 * User: Tim
	 * Date: 12/07/2019
	 * Time: 13:03
	 */



	foreach ($this->orders as $o){

		$order_data = $o->get_data(); // The Order data
		$tracking_code = $o->get_meta('as_trackingcode'); // get the tracking code

		?>
        <tr class="">
            <td>#<?php echo $order_data['id']; ?></td>
            <td><?php echo $order_data['billing']['first_name']; ?> <?php echo $order_data['billing']['last_name'];  ?></td>
            <td><?php echo $order_data['shipping']['company']; ?> <?php echo $order_data['shipping']['first_name']; ?> <?php echo $order_data['shipping']['last_name']; ?><br /><?php echo $order_data['shipping']['address_1']; ?><br /><?php echo $order_data['shipping']['address_2']; ?><br /> <?php echo $order_data['shipping']['postcode'];  ?> <?php echo $order_data['shipping']['city'];  ?> <?php echo $order_data['shipping']['country'];  ?></td>
            <td><?php echo $order_data['billing']['email']; ?></td>
            <td><?php echo $o->get_customer_note();?></td>

            <td class="order_status"><mark class="order-status status-<?php echo $order_data['status']; ?>"><?php echo $order_data['status']; ?></mark></td>
            <td>
                <a href="<?php echo  wp_nonce_url(admin_url( "admin-ajax.php?action=generate_wpo_wcpdf&document_type=packing-slip&order_ids=" . $order_data["id"] ),'generate_wpo_wcpdf');?>"><button class="button"><?php _e("Pakbon","ascension-shop"); ?> </button></a>
                <a href="<?php echo  wp_nonce_url(admin_url( "admin-ajax.php?action=generate_wpo_wcpdf&document_type=invoice&order_ids=" . $order_data["id"] ),'generate_wpo_wcpdf');?>"><button class="button"><?php _e("Factuur","ascension-shop"); ?> </button></a>
            </td>
            <td><input type="text" class="tracking_code" value="<?php echo $tracking_code; ?>" data-id="<?php echo $order_data["id"]; ?>" /></td>
            <td>
                <button class="button sendTracking" data-id="<?php echo $order_data["id"]; ?>"><?php _e("Trackingcode verzenden","ascension-shop"); ?></button>
                <button class="button closeTracking" data-id="<?php echo $order_data["id"]; ?>"><?php _e("Afronden","ascension-shop"); ?></button>
            </td>
        </tr>
		<?php
	}
	?>


    </tbody>
</table>

