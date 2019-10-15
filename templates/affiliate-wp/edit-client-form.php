<form class="editUser" method="POST" action="<?php use AscensionShop\Affiliate\Helpers;
use AscensionShop\NationalManager\NationalManager;

echo admin_url('admin-post.php'); ?>" style="display: none;" id="edit-user-<?php echo $this->customer->user_id ?>" data-id="<?php echo $this->customer->user_id ?>">
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
    <label for="vat"><?php _e("Bedrijf","ascension-shop"); ?></label>
    <input type="text" name="company" id="company"  value="<?php echo get_user_meta( $this->customer->user_id, 'billing_company', true ); ?>"  />
    <label for="vat"><?php _e("BTW nummer","ascension-shop"); ?></label>
    <input type="text" name="vat" id="vat"  value="<?php echo get_user_meta( $this->customer->user_id, 'vat_number', true ); ?>"  />

    <?php

    if ( NationalManager::isNationalManger(get_current_user_id()) ) {
	    $customer_id = Helpers::getCustomerByUserId( $this->customer->user_id );

	    if ( $customer_id > 0 ) {
		    $parent_id = Helpers::getParentByCustomerId($customer_id );
	    }else{
	        $parent_id = 0;
        }

	    $all_affiliates = affiliate_wp()->affiliates->get_affiliates( array( 'number'  => 0,
	                                                                         'orderby' => 'name',
	                                                                         'order'   => 'ASC'
	    ) );
	    ?>
        <label for="ascension_shop_customer_of"><?php _e( "Klant van", "ascension-shop" ); ?></label>
        <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>"/>
        <select name="ascension_shop_customer_of">
            <option></option>
		    <?php
		    foreach ( $all_affiliates as $a ) {

			    ?>
                <option value="<?php echo $a->affiliate_id; ?>" <?php selected( $a->affiliate_id, $parent_id ); ?>><?php echo affiliate_wp()->affiliates->get_affiliate_name( $a->affiliate_id ) ?></option>
			    <?php

		    }
		    ?>
        </select>
	    <?php
    }
    ?>

	<?php wp_nonce_field( 'ascension_edit_customer'.$this->affiliate_id ); ?>
    <input type="hidden" name="user_id" value="<?php echo $this->customer->user_id; ?>"/>
    <input type="hidden" name="customer_id" value="<?php echo $this->customer->customer_id; ?>"/>
    <input type="hidden" name="action" value="ascension-edit_customer">
	<input type="submit" value="<?php _e("Aanpassen","ascension-shop"); ?>" />
</form>
