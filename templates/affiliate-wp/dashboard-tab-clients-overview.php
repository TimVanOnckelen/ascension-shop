<?php

use AscensionShop\Lib\TemplateEngine;

$affiliate_id = affwp_get_affiliate_id();
$customers    = affiliate_wp_lifetime_commissions()->integrations->get_customers_for_affiliate( $affiliate_id );
?>

    <div id="affwp-affiliate-dashboard-lifetime-customers" class="affwp-tab-content">
            <h4><?php _e( 'Klanten', 'ascension-shop' ); ?></h4>
            <p>
                <input type="text" id="searchClient" onkeyup="searchClientTable()" placeholder="<?php _e("Zoek op naam, telefoon, adres of email","ascension-shop"); ?>">
            </p>
            <p><a href="<?php echo $_SERVER['REQUEST_URI'].'&generateReport=clients';?>"><button><?php _e("Download als XLS","ascension-shop"); ?></button></a></p>
            <p>
                <a href="#addClient"><?php _e("Nieuwe klant aanmaken"); ?></a>
            </p>

		<?php if ( $customers ) : ?>

            <table id="clients-overview" class="affwp-table affwp-table-responsive">
            <thead>
            <tr>
                <th>ID</th>
                <th><?php _e("Naam","ascension-shop"); ?></th>
                <th class="customer-first-name"><?php _e( 'Gegevens', 'ascension-shop' ); ?></th>
                <th><?php _e("Tools","ascension-shop"); ?></th>
                <th class="customer-discount"><?php _e('Korting %',"ascension-shop") ?></th>
            </tr>
            </thead>

            <tbody>
			<?php foreach ( $customers as $customer ) : ?>


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
                            <a href="?tab=commission-overview&client=<?php echo $customer->customer_id; ?>"><?php _e("Bestellingen","ascension-shop"); ?></a>
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
            </table><?php else : ?>
            <p><?php _e( 'You don\'t have any customers yet.', 'ascension-shop' ); ?></p>
		<?php endif; ?>

		<?php  if(!isset($_GET["ascension-download-report"])){ ?>
			<?php do_action("ascension-after-clients"); ?>
		<?php } ?>
    </div>

<?php  if(!isset($_GET["ascension-download-report"])){ ?>
    <script>
        function searchClientTable() {
            // Declare variables
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchClient");
            filter = input.value.toUpperCase();
            table = document.getElementById("clients-overview");
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[2];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
<?php } ?>