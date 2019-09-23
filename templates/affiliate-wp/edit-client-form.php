<p>
<form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" style="display: none;" id="edit-user-<?php echo $this->customer->user_id ?>">
	<label for="email"><?php _e("Email adres","ascension-shop"); ?></label>
	<input type="email" name="email" id="email" value="<?php echo $this->customer->email; ?>" disabled />
	<label for="name"><?php _e("Naam","ascension-shop"); ?></label>
	<input type="text" name="name" id="name" value="<?php echo $this->customer->first_name; ?>" required/>
	<label for="lastname"><?php _e("Achternaam","ascension-shop"); ?></label>
	<input type="text" name="lastname" id="lastname" value="<?php echo $this->customer->last_name; ?>" required/>
	<label for="adres"><?php _e("Adres","ascension-shop"); ?></label>
	<input type="text" name="adres" id="adres" value="<?php echo get_user_meta( $this->customer->user_id, 'billing_address_1', true ); ?>" />
	<label for="city"><?php _e("Stad","ascension-shop"); ?></label>
	<input type="text" name="city" id="city" value="<?php echo get_user_meta( $this->customer->user_id, 'billing_city', true ); ?>" />
	<label for="postalcode"><?php _e("Postcode","ascension-shop"); ?></label>
	<input type="text" name="postalcode" id="postalcode" value="<?php echo get_user_meta( $this->customer->user_id, 'billing_postcode', true ); ?>" />
	<label for="phone"><?php _e("Telefoon","ascension-shop"); ?></label>
	<input type="text" name="phone" id="phone" value="<?php echo get_user_meta( $this->customer->user_id, 'billing_phone', true ); ?>" />
	<?php wp_nonce_field( 'ascension_edit_customer'.$this->affiliate_id ); ?>
    <input type="hidden" name="user_id" value="<?php echo $this->customer->user_id; ?>"/>
    <input type="hidden" name="customer_id" value="<?php echo $this->customer->customer_id; ?>"/>
    <input type="hidden" name="action" value="ascension-edit_customer">
	<input type="submit" value="<?php _e("Aanpassen","ascension-shop"); ?>" />
</form>
</p>