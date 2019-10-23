<div class="tab4">
	<?php
	use AscensionShop\Affiliate\Helpers;
	use AscensionShop\Affiliate\SubAffiliate;
	use AscensionShop\NationalManager\NationalManager;
	?>
    <p>
        <?php _e("Add a new partner based on a current user. Do you want to add a new partner that has no account yet? Create a customer first, and then make him a partner here.","ascenion-shop"); ?>
    </p>
<form method="POST" action="<?php
echo admin_url('admin-post.php'); ?>">

    <?php
    $all_affiliates = affiliate_wp()->affiliates->get_affiliates( array( 'number'  => 0,
                                                                         'orderby' => 'name',
                                                                         'order'   => 'ASC'
    ) );

    $affiliates_ids = array();

    // Get all affiliate ids
    foreach ( $affiliates_ids as $a){
        $affiliates_ids[] = $a->user_id;
    }

    $all_other_users = get_users(array(
            'exclude' => $affiliates_ids
    ));

    ?>
    <label for="user_id"><?php _e( "Gebruiker", "ascension-shop" ); ?></label>
    <select name="user_id" class="searchByPartner">
        <option></option>
		<?php
		foreach ( $all_other_users as $a ) {
			$firstName = get_user_meta($a->ID, 'first_name', true);
			$lastName = get_user_meta($a->ID, 'last_name', true);

			?>
            <option value="<?php echo $a->ID; ?>"><?php echo  '#'.$a->ID.' '.$firstName. ' '.$lastName; ?></option>
			<?php

		}
		?>
    </select>

        <label for="ascension_shop_partner_of"><?php _e( "Sub partner van", "ascension-shop" ); ?></label>
        <select name="ascension_shop_partner_of" class="searchByPartner">
            <option></option>
			<?php
			foreach ( $all_affiliates as $a ) {

				?>
                <option value="<?php echo $a->affiliate_id; ?>"><?php echo '#'.$a->affiliate_id.' '.affiliate_wp()->affiliates->get_affiliate_name( $a->affiliate_id ) ?></option>
				<?php

			}
			?>
        </select>
        <label for="rate"><?php _e("Commissie","ascension-shop"); ?> %</label>
        <input type="text" name="rate" id="rate"  value=""  />

	<?php wp_nonce_field( 'ascension_add_partner' ); ?>
    <input type="hidden" name="action" value="ascension-add_partner">
    <input type="submit" value="<?php _e("Toevoegen","ascension-shop"); ?>" />
</form>
</div>