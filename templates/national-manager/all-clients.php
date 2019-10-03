<div class="tab2">
	<p><?php use AscensionShop\Lib\TemplateEngine;

		$affiliate_id = affwp_get_affiliate_id();


		printf(__("Overzicht van alle klanten van de %s shop","ascension-shop"),$this->lang[0]); ?></p>
    <table id="all-clients" class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">

        <thead>
        <tr>
            <th><?php _e("ID","ascension-shop") ?></th>
            <th><?php _e("Naam","ascension-shop") ?></th>
            <th><?php _e("Gegevens","ascension-shop") ?></th>
            <th><?php _e("Tools","ascension-shop") ?></th>
            <th><?php _e("Korting","ascension-shop") ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ( $this->customers as $customer ) : ?>


	        <?php if ( $customer ): ?>

                <tr>
                    <td><a href="?tab=commission-overview&client=<?php echo $customer->customer_id; ?>">#<?php echo $customer->customer_id; ?></a></td>
                    <td><b><?php echo $customer->first_name; ?> <?php echo $customer->last_name; ?></b></td>
                    <td class="customer-first-name" data-th="<?php _e( 'Gegevens', 'ascension-shop' ); ?>">
                        <div id="info-user-<?php echo $customer->user_id; ?>">
					        <?php echo $customer->first_name; ?> <?php echo $customer->last_name; ?><br />
					        <?php echo get_user_meta( $customer->user_id, 'billing_address_1', true ); ?><br />
					        <?php echo get_user_meta( $customer->user_id, 'billing_postcode', true ). ' '.get_user_meta( $customer->user_id, 'billing_city', true ); ?><br />
                            <br />
					        <?php echo get_user_meta( $customer->user_id, 'billing_phone', true ); ?><br />
					        <?php echo $customer->email; ?><br />
					        <?php echo get_user_meta( $customer->user_id, 'vat_number', true ); ?><br />

                        </div>
				        <?php

				        $t = new TemplateEngine();
				        $t->customer = $customer;
				        $t->affiliate_id = $affiliate_id;
				        echo $t->display("affiliate-wp/edit-client-form.php");

				        ?>
                    </td>
                    <td>
                        <a href="#" class="edit-user" data-id="<?php echo $customer->user_id; ?>"><?php _e("Bewerk","ascension-shop"); ?></a><br />
                    </td>

                    <td class="customer-discount" width="20%">
                        <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                            <input type="number" name="customer_rate[<?php echo $customer->user_id; ?>]" step=".01" value="<?php echo get_user_meta($customer->user_id,"ascension_shop_affiliate_coupon",true); ?>">
					        <?php wp_nonce_field( 'ascension_save_customer_discount_'.$affiliate_id ); ?>
                            <input type="hidden" name="action" value="ascension-save_customer-discount">
                            <input type="submit" value="<?php _e("Opslaan","ascension-shop"); ?>" />
                        </form>

                    </td>
                </tr>

	        <?php endif; ?>

        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
    (function($){
        $(document).ready( function () {
            $('#all-clients ').DataTable();
        } );

    })(jQuery);
</script>