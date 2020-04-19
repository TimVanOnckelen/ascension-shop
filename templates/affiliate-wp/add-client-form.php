<?php

use AscensionShop\Affiliate\Helpers;
use AscensionShop\NationalManager\NationalManager;

if(isset($_GET["m"])){
	?>
    <p><b><?php echo $_GET["m"];?></b></p>
	<?php
}
?>
<p>
<h3><?php _e("Nieuwe klant aanmaken","ascension-shop"); ?></h3>
<form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" id="addClient">
    <label for="email"><?php _e("Email adres","ascension-shop"); ?></label>
    <input type="email" name="email" id="email" required />
    <label for="name"><?php _e("Naam","ascension-shop"); ?></label>
    <input type="text" name="name" id="name" required/>
    <label for="lastname"><?php _e("Achternaam","ascension-shop"); ?></label>
    <input type="text" name="lastname" id="lastname" required/>
    <label for="adres"><?php _e("Adres","ascension-shop"); ?></label>
    <input type="text" name="adres" id="adres"/>
    <label for="city"><?php _e("Stad","ascension-shop"); ?></label>
    <input type="text" name="city" id="city" />
    <label for="postalcode"><?php _e("Postcode","ascension-shop"); ?></label>
    <input type="text" name="postalcode" id="postalcode" />
    <label for="phone"><?php _e("Telefoon","ascension-shop"); ?></label>
    <input type="text" name="phone" id="phone" required/>
    <label for="country"><?php _e("Land","ascension-shop"); ?></label>
    <select name="country" id="country">
        <option value=""></option>
        <?php
		$countries_obj   = new \WC_Countries();
		$countries   = $countries_obj->__get('countries');

		foreach($countries as $id => $c){
			echo '<option value="'.$id.'">'.$c.'</option>';
		}
		?>
    </select>
    <label for="vat"><?php _e("Bedrijf","ascension-shop"); ?></label>
    <input type="text" name="company" id="company" />
    <label for="vat"><?php _e("BTW nummer","ascension-shop"); ?></label>
    <input type="text" name="vat" id="vat" />
    <label for="discount"><?php _e("Korting (%)","ascension-shop"); ?></label>
    <input type="number" name="discount" id="discount" min="0" max="100" required/>
    <?php
        if(NationalManager::isNationalManger(get_current_user_id())){
            ?>
            <label for="ascension_shop_client_of"><?php _e( "Klant van", "ascension-shop" ); ?></label>
            <select name="ascension_shop_client_of" class="searchByPartner">
                <option></option>
		        <?php
		        $all_affiliates = affiliate_wp()->affiliates->get_affiliates( array( 'number'  => 0,
		                                                                             'orderby' => 'name',
		                                                                             'order'   => 'ASC'
		        ) );

		        foreach ( $all_affiliates as $a ) {

			        ?>
                    <option value="<?php echo $a->affiliate_id; ?>"><?php echo '#'.$a->affiliate_id.' '.affiliate_wp()->affiliates->get_affiliate_name( $a->affiliate_id ) ?></option>
			        <?php

		        }
		        ?>
            </select>
    <?php
        }
    ?>
	<?php wp_nonce_field( 'ascension_add_new_customer_'.$this->affiliate_id ); ?>
    <input type="hidden" name="action" value="ascension-save_add-client" />
    <input type="submit" value="<?php _e("Aanmaken","ascension-shop"); ?>" />
</form>
</p>