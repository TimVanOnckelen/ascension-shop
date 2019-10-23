<p>
    <?php
    use AscensionShop\Affiliate\Helpers;
    use AscensionShop\Affiliate\SubAffiliate;
    use AscensionShop\NationalManager\NationalManager;
    ?>
<form method="POST" action="<?php
echo admin_url('admin-post.php'); ?>" style="display: none;" id="edit-user-<?php echo $this->partner->getUserId() ?>" class="editPartner" data-id="<?php echo $this->partner->getUserId() ?>">
	<label for="email"><?php _e("Email adres","ascension-shop"); ?></label>
	<input type="email" name="email" id="email" value="<?php echo $this->partner->getEmail(); ?>" disabled />
	<label for="name"><?php _e("Naam","ascension-shop"); ?></label>
	<input type="text" name="name" id="name" value="<?php echo get_user_meta( $this->partner->getUserId(), 'first_name', true ); ?>" required/>
	<label for="lastname"><?php _e("Achternaam","ascension-shop"); ?></label>
	<input type="text" name="lastname" id="lastname" value="<?php echo get_user_meta( $this->partner->getUserId(), 'last_name', true ); ?>" required/>
	<label for="adres"><?php _e("Adres","ascension-shop"); ?></label>
	<input type="text" name="adres" id="adres" value="<?php echo get_user_meta( $this->partner->getUserId(), 'billing_address_1', true ); ?>" />
	<label for="city"><?php _e("Stad","ascension-shop"); ?></label>
	<input type="text" name="city" id="city" value="<?php echo get_user_meta( $this->partner->getUserId(), 'billing_city', true ); ?>" />
	<label for="postalcode"><?php _e("Postcode","ascension-shop"); ?></label>
	<input type="text" name="postalcode" id="postalcode" value="<?php echo get_user_meta( $this->partner->getUserId(), 'billing_postcode', true ); ?>" />
    <label for="country"><?php _e("Land","ascension-shop"); ?></label>
    <select name="country" id="country">
        <option value=""></option>
        <?php
		$countries_obj   = new \WC_Countries();
		$countries   = $countries_obj->__get('countries');
		$user_country = get_user_meta( $this->partner->getUserId(), 'billing_country', true );

		foreach($countries as $id => $c){
			echo '<option value="'.$id.'" '.selected($id,$user_country).'>'.$c.'</option>';
		}
		?>
    </select>
	<label for="phone"><?php _e("Telefoon","ascension-shop"); ?></label>
	<input type="text" name="phone" id="phone" value="<?php echo get_user_meta( $this->partner->getUserId(), 'billing_phone', true ); ?>" />
    <label for="vat"><?php _e("Bedrijf","ascension-shop"); ?></label>
    <input type="text" name="company" id="company" value="<?php echo get_user_meta( $this->partner->getUserId(), 'billing_company', true );?>"/>
    <label for="vat"><?php _e("BTW nummer","ascension-shop"); ?></label>
    <input type="text" name="vat" id="vat"  value="<?php echo get_user_meta( $this->partner->getUserId(), 'vat_number', true ); ?>"  />
	<?php

	if ( NationalManager::isNationalManger(get_current_user_id()) ) {

		$all_affiliates = affiliate_wp()->affiliates->get_affiliates( array( 'number'  => 0,
		                                                                     'orderby' => 'name',
		                                                                     'order'   => 'ASC'
		) );
		?>
        <label for="ascension_shop_customer_of"><?php _e( "Sub partner van", "ascension-shop" ); ?></label>
        <select name="ascension_shop_customer_of">
            <option></option>
			<?php
			foreach ( $all_affiliates as $a ) {

			    // Do not add yourself :)
			    if($a->affiliate_id == $this->partner->getId()){
			        continue;
                }

				?>
                <option value="<?php echo $a->affiliate_id; ?>" <?php selected( $a->affiliate_id, $this->partner->getParentId() ); ?>><?php echo affiliate_wp()->affiliates->get_affiliate_name( $a->affiliate_id ) ?></option>
				<?php

			}
			?>
        </select>
        <label for="vat"><?php _e("Commissie","ascension-shop"); ?> %</label>
        <input type="text" name="rate" id="rate"  value="<?php echo $this->partner->getUserRate(); ?>"  />
		<?php
	}
	?>
	<?php wp_nonce_field( 'ascension_edit_partner'.$this->affiliate_id ); ?>
    <input type="hidden" name="partner_id" value="<?php echo $this->partner->getId(); ?>"/>
    <input type="hidden" name="action" value="ascension-edit_partner">
	<input type="submit" value="<?php _e("Aanpassen","ascension-shop"); ?>" />
</form>
</p>