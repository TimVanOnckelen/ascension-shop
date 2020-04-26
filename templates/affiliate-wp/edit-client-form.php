<form class="editUser" method="POST" action="<?php use AscensionShop\Affiliate\Helpers;
use AscensionShop\NationalManager\NationalManager;

echo admin_url('admin-post.php'); ?>" id="edit-user-<?php echo $this->customer->user_id ?>" data-id="<?php echo $this->customer->user_id ?>">
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
    <label for="country"><?php _e("Land","ascension-shop"); ?></label>
    <select  name="country" id="country">
        <option value=""></option>
		<?php
		$countries_obj   = new \WC_Countries();
		$countries   = $countries_obj->__get('countries');
		$user_country = get_user_meta( $this->customer->user_id, 'billing_country', true );

		foreach($countries as $id => $c){
			echo '<option value="'.$id.'" '.selected($id,$user_country).'>'.$c.'</option>';
		}
		?>
    </select>
	<label for="phone"><?php _e("Telefoon","ascension-shop"); ?></label>
	<input type="text" name="phone" id="phone" value="<?php echo get_user_meta( $this->customer->user_id, 'billing_phone', true ); ?>" />
    <label for="vat"><?php _e("Bedrijf","ascension-shop"); ?></label>
    <input type="text" name="company" id="company"  value="<?php echo get_user_meta( $this->customer->user_id, 'billing_company', true ); ?>"  />
    <label for="vat"><?php _e("BTW nummer","ascension-shop"); ?></label>
    <input type="text" name="vat" id="vat"  value="<?php echo get_user_meta( $this->customer->user_id, 'vat_number', true ); ?>"  />
    <label for="vat"><?php _e("Status","ascension-shop"); ?></label>
    <select name="ascension_status" id="ascension_status">
        <?php $status =  get_user_meta( $this->customer->user_id, 'ascension_status', true );  ?>
        <option value=""><?php _e("Actief","ascension-shop"); ?></option>
        <option value="non-active" <?php if($status == "non-active"){echo  "SELECTED"; } ?>><?php _e("Niet actief","ascension-shop"); ?></option>
    </select>

    <?php
    $customer_id = Helpers::getCustomerByUserId( $this->customer->user_id );

    if ( NationalManager::isNationalManger(get_current_user_id()) ) {


	    if ( $customer_id > 0 ) {
		    $parent_id = Helpers::getParentByCustomerId( $customer_id );
	    } else {
		    $parent_id = 0;
	    }


	    ?>
        <label for="ascension_shop_customer_of"><?php _e( "Klant van", "ascension-shop" ); ?></label>
        <select name="ascension_shop_customer_of">
            <option></option>
            <option value="<?php echo $this->sub->getId(); ?>" <?php selected( $this->sub->getId(), $parent_id ); ?>><?php echo $this->sub->getName(); ?></option>
		    <?php
		    foreach ( $this->partners as $a ) {

			    ?>
                <option value="<?php echo $a->getId(); ?>" <?php selected( $a->getId(), $parent_id ); ?>><?php echo $a->getName(); ?></option>
			    <?php

		    }
		    ?>
        </select>
	    <?php
    }
    ?>

	<?php wp_nonce_field( 'ascension_edit_customer'.$this->affiliate_id ); ?>
    <input type="hidden" name="user_id" value="<?php echo $this->customer->user_id; ?>"/>
    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>"/>

    <input type="hidden" name="action" value="ascension-edit_customer">
	<input type="submit" value="<?php _e("Aanpassen","ascension-shop"); ?>" />
</form>
