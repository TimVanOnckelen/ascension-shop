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

<style>
    .dataTables_filter {
        display: block;
    }
</style>
<div id="affwp-affiliate-dashboard-lifetime-customers" class="printArea affwp-tab-content">

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
    </p>

    <p><a href="<?php echo $_SERVER['REQUEST_URI'].'&generateReport=partners';?>"><button><?php _e("Download als XLSx","ascension-shop"); ?></button></a></p>
    <!-- <p>	<a href="#" class="downloadOverview"><button><?php _e("Print overzicht","ascension-shop"); ?></button></a> -->
    </p>

		<?php if ( $partners ) : ?>

        <table id="partners-overview" class="affwp-table affwp-table-responsive">
            <thead>
            <tr>
                <th>ID</th>
                <th class="customer-first-name" width="40%"><?php _e( 'Gegevens', 'ascension-shop' ); ?></th>
                <th><?php _e("Status","ascension-shop"); ?></th>
                <th><?php _e("Sub partner van","ascension-shop"); ?></th>
                <th class="customer-discount"><?php _e('Commissie (%)',"ascension-shop") ?></th>
            </tr>
            </thead>


            <tbody>
	        <?php foreach ( $partners as $partner ) :

		        if ( $partner ): ?>


                    <tr>
                        <td><a href="?tab=commission-overview&partner=<?php echo $partner->getId(); ?>">#<?php echo $partner->getId(); ?></a><br />
                            <a href="#edit-user-<?php echo $partner->getUserId(); ?>" class="edit-user" rel="modal:open" data-id="<?php echo $partner->getUserId(); ?>"><?php _e("Bewerk","ascension-shop"); ?></a><br />
                        </td>
                        <td class="customer-first-name" data-th="<?php _e( 'Gegevens', 'ascension-shop' ); ?>">
                            <div id="info-user-<?php echo $partner->getUserId(); ?>" class="partnerArea-header no-borders">
                                <div class="header">
                                    <a href="?tab=commission-overview&partner=<?php echo $partner->getId(); ?>"><b><?php echo $partner->getName(); ?><br /></b></a>
							        <?php echo get_user_meta( $partner->getUserId(), 'billing_phone', true ); ?><br />
							        <?php echo $partner->getEmail(); ?><br />
                                </div>
                                <div class="modal" id="adress-user-<?php
						        echo $partner->getUserId(); ?>" style="display: none;">
							        <?php echo get_user_meta( $partner->getUserId(), 'billing_address_1', true ); ?><br />
							        <?php echo get_user_meta( $partner->getUserId(), 'billing_postcode', true ). ' '.get_user_meta( $partner->getUserId(), 'billing_city', true ); ?><br />
							        <?php if($partner_country != ''){ echo WC()->countries->countries[$partner_country]; } ?> <br />
                                    <br />
							        <?php echo get_user_meta( $partner->getUserId(), 'billing_company', true ); ?><br />
							        <?php echo get_user_meta( $partner->getUserId(), 'vat_number', true ); ?><br />
                                </div>
                                <div class="buttons">
                                    <a href="#adress-user-<?php echo $partner->getUserId(); ?>" class="edit-user" rel="modal:open" data-id="<?php echo $partner->getUserId(); ?>"><?php _e("Adres","ascension-shop"); ?></a><br />

                                </div>
                            </div>
                            <div class="modal" id="user-edit-<?php echo $partner->getUserId(); ?>">
						        <?php

						        $t = new TemplateEngine();
						        $t->partner = $partner;
						        $t->affiliate_id = $affiliate_id;
						        echo $t->display("affiliate-wp/edit-partner-form.php");

						        ?>
                            </div>
                        </td>
                        <td><?php
					        if($partner->getStatus() == 1){
						        _e("Actief","ascension-shop");
					        }else{
						        _e("Niet actief","ascension-shop");
					        }
					        ?></td>
                        <td><?php
					        if($partner->getParentId() > 0){
						        echo '#'.$partner->getParentId();
						        echo ' '.affiliate_wp()->affiliates->get_affiliate_name($partner->getParentId());
					        } ?>
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
<script>
    (function($){
        $(document).ready( function () {
            $('#partners-overview ').DataTable({
                autoWidth: false
            });
        } );

    })(jQuery);
</script>