<div class="tab3">

<?php

use AscensionShop\Affiliate\SubAffiliate;
use AscensionShop\Lib\TemplateEngine;

$affiliate_id = affwp_get_affiliate_id();

if($_GET["status"] == ''){
	$_GET["status"] = 2;
}

$partners = affiliate_wp()->affiliates->get_affiliates(
	array( 'number'  => -1,
	       'orderby' => 'name',
            'order'   => 'ASC' ) );
$partners_amount = count($partners);

printf(__("Overzicht van alle partners van de %s shop","ascension-shop"),$this->lang[0]); ?>


	<div id="affwp-affiliate-dashboard-lifetime-customers" class="printArea affwp-tab-content">

		<h4><?php _e( 'Partners', 'ascension-shop' ); ?></h4>
        <p>
            <b><?php echo __("Aantal partners:","ascension-shop"). ' '.$partners_amount; ?></b>
        </p>
		</p>

		<?php if ( $partners ) : ?>

			<table id="partners-overview" class="affwp-table affwp-table-responsive">
				<thead>
				<tr>
					<th>ID</th>
					<th><?php _e("Naam",'ascension-shop'); ?></th>
					<th class="customer-first-name"><?php _e( 'Gegevens', 'ascension-shop' ); ?></th>
					<th><?php _e("Status","ascension-shop"); ?></th>
                    <th><?php _e("Sub partner van","ascension-shop"); ?></th>
                    <th><?php _e("Tools","ascension-shop"); ?></th>
					<th class="customer-discount"><?php _e('Commissie (%)',"ascension-shop") ?></th>
				</tr>
				</thead>

				<tbody>
				<?php foreach ( $partners as $partner ) :

					$partner = new SubAffiliate($partner->ID);

					if ( $partner ): ?>

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
									<?php echo get_user_meta( $partner->getUserId(), 'billing_company', true ); ?><br />
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
                            <td><?php
                                if($partner->getParentId() > 0){
                                    echo '#'.$partner->getParentId();
                                    echo ' '.affiliate_wp()->affiliates->get_affiliate_name($partner->getParentId());
                                } ?>
                            </td>
							<td>
								<a href="#" class="edit-user" data-id="<?php echo $partner->getUserId(); ?>"><?php _e("Bewerk","ascension-shop"); ?></a><br />
                                <?php
                                $user = new \WP_User($partner->getUserId());
                                $adt_rp_key = get_password_reset_key($user);
                                $user_login = $user->user_login;
                                $rp_link = '<a href="' . wp_login_url()."?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login) . '" target="_blank">'.__("Reset wachtwoord","ascension-shop").'</a>';
                                echo $rp_link;
                                ?>
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
</div>
<script>
    (function($){
        $(document).ready( function () {
            $('#partners-overview ').DataTable({
                'columnDefs'        : [         // see https://datatables.net/reference/option/columns.searchable
                    {
                        'searchable'    : false,
                        'targets'       : [2,3,4]
                    },
                ],
            });
        } );

    })(jQuery);
</script>
