<?php

use AscensionShop\Affiliate\SubAffiliate;
use AscensionShop\Lib\TemplateEngine;

if($_GET["status"] == ''){
    $_GET["status"] = 2;
}

$affiliate_id = affwp_get_affiliate_id();
$sub          = new SubAffiliate($affiliate_id);
$partners     = $sub->getAllChildren($_GET["status"]);
$partners_amount = count($partners);
?>

<div id="affwp-affiliate-dashboard-lifetime-customers" class="printArea affwp-tab-content">

    <h4><?php _e( 'Partners', 'ascension-shop' ); ?></h4>
    <p>
    <form method="GET" id="ascension-filters" >
        <input type="hidden" name="tab" value="partners" />
        <select name="status">
            <option value=""><?php _e("Alle partners","xe-ascension"); ?></option>
            <option value="1" <?php selected($_GET["status"],1); ?>><?php _e("Actieve partners","xe-ascension"); ?></option>
            <option value="0" <?php selected($_GET["status"],0); ?>><?php _e("Inactieve partners","xe-ascension"); ?></option>
        </select>
        <input type="submit" value="Filter" />
    </form>
    </p>
    <p>
        <b><?php echo __("Aantal partners:","ascension-shop"). ' '.$partners_amount; ?></b>
        <br />
        <input type="text" id="searchClient" onkeyup="searchClientTable()" placeholder="<?php _e("Zoek op naam, telefoon, adres of email","ascension-shop"); ?>">
    </p>

    <p><a href="<?php echo $_SERVER['REQUEST_URI'].'&generateReport=partners';?>"><button><?php _e("Download als XLSx","ascension-shop"); ?></button></a></p>
    <!-- <p>	<a href="#" class="downloadOverview"><button><?php _e("Print overzicht","ascension-shop"); ?></button></a> -->
    </p>

		<?php if ( $partners ) : ?>

        <table id="clients-overview" class="affwp-table affwp-table-responsive">
            <thead>
            <tr>
                <th>ID</th>
                <th><?php _e("Naam",'ascension-shop'); ?></th>
                <th class="customer-first-name"><?php _e( 'Gegevens', 'ascension-shop' ); ?></th>
                <th><?php _e("Status","ascension-shop"); ?></th>
                <th><?php _e("Tools","ascension-shop"); ?></th>
                <th class="customer-discount"><?php _e('Commissie (%)',"ascension-shop") ?></th>
            </tr>
            </thead>

            <tbody>
			<?php foreach ( $partners as $partner ) : ?>


				<?php if ( $partner ): ?>

                    <tr>
                        <td><a href="?tab=commission-overview&partner=<?php echo $partner->getId(); ?>">#<?php echo $partner->getId(); ?></a></td>
                        <td>    <b><?php echo $partner->getName(); ?><br /></b>		</td>
                        <td class="customer-first-name" data-th="<?php _e( 'Gegevens', 'ascension-shop' ); ?>">
                            <div id="info-user-<?php echo $partner->getUserId(); ?>">
                            <b><?php echo $partner->getName(); ?><br /></b>							<?php echo get_user_meta( $partner->getUserId(), 'billing_address_1', true ); ?><br />
							<?php echo get_user_meta( $partner->getUserId(), 'billing_postcode', true ). ' '.get_user_meta( $partner->getUserId(), 'billing_city', true ); ?><br />
                            <br />
							<?php echo get_user_meta( $partner->getUserId(), 'billing_phone', true ); ?><br />
							<?php echo $partner->getEmail(); ?><br />
	                            <?php echo get_user_meta( $partner->getUserId(), 'vat_number', true ); ?><br />

                            </div>
	                        <?php

	                        $t = new TemplateEngine();
	                        $t->partner = $partner;
	                        $t->affiliate_id = $affiliate_id;
	                        echo $t->display("affiliate-wp/edit-partner-form.php");

	                        ?>
                        </td>
                        <td><?php
                            if($partner->getStatus() == 1){
                                _e("Actief","ascension-shop");
                            }else{
                                _e("Niet actief","ascension-shop");
                            }
                            ?></td>
                        <td>
                            <a href="#" class="edit-user" data-id="<?php echo $partner->getUserId(); ?>"><?php _e("Bewerk","ascension-shop"); ?></a><br />
                            <a href="?tab=commission-overview&partner=<?php echo $partner->getName(); ?>"><?php _e("Commissies","ascension-shop"); ?></a>
                        </td>
                        <td class="customer-discount" width="20%"><?php echo $partner->getUserRate(); ?>%
                        </td>
                    </tr>

				<?php endif; ?>

			<?php endforeach; ?>
            </tbody>
        </table>
	<?php else : ?>
        <p><?php _e( 'Je hebt nog geen partners.', 'ascension-shop' ); ?></p>
	<?php endif; ?>

	<?php do_action("ascension-after-partners"); ?>
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