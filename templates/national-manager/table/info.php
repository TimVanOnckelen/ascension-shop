<?php
use AscensionShop\Lib\TemplateEngine;
?>
<div id="info-user-<?php
echo $this->user->ID; ?>" class="partnerArea-header no-borders">
    <div class="header">
		<?php echo $this->user->first_name; ?> <?php echo $this->user->last_name; ?><br />
		<?php echo $this->user->user_email; ?><br />
		<?php echo get_user_meta( $this->user->ID, 'billing_phone', true ); ?><br />
        <b>
        <?php $status = get_user_meta( $this->user->ID, 'ascension_status', true );
        if($status == "non-active"){
            _e('Niet actief',"ascension-shop");
        }else{
	        _e('Actief',"ascension-shop");
        }
        ?>
        </b>
        <div class="modal" id="adress-user-<?php
		echo $this->user->ID; ?>" style="display: none;">
			<?php echo get_user_meta( $this->user->ID, 'billing_address_1', true ); ?><br />
			<?php echo get_user_meta( $this->user->ID, 'billing_postcode', true ). ' '.get_user_meta( $this->user->ID, 'billing_city', true ); ?><br />
			<?php echo WC()->countries->countries[ get_user_meta( $this->user->ID, 'billing_country', true )]; ?><br />
			<?php echo get_user_meta( $this->user->ID, 'billing_company', true ); ?><br />
			<?php echo get_user_meta( $this->user->ID, 'vat_number', true ); ?><br />
        </div>
    </div>
    <div class="buttons">
        <b><a href="?tab=orders&id=<?php echo $this->user->ID;?>"><?php _e("Bestellingen","ascension-shop"); ?></a><br />
        <b><a rel="modal:open" href="#adress-user-<?php echo $this->user->ID;?>"><?php _e("Adres","ascension-shop"); ?></a><br />
		<?php
		$user = $this->user;
		$adt_rp_key = get_password_reset_key($this->user);
		$user_login = $user->user_login;
		if(!is_wp_error($adt_rp_key)) {
			$rp_link = '<b><a href="' . wp_login_url() . "?action=rp&key=$adt_rp_key&login=" . rawurlencode( $user_login ) . '" target="_blank">' . __( "Reset wachtwoord", "ascension-shop" ) . '</a></b>';
		}else{
		    $rp_link = __("Wachtwoord aanpassen niet mogelijk","ascension-shop");
        }
		echo $rp_link;
		?>
        </b>
    </div>
</div>
<div class="modal" id="user-edit-<?php echo $this->user->ID; ?>">
	<?php
	$t = new TemplateEngine();
	// Make it accessable in template
	$this->user->user_id = $this->user->ID;
	$t->customer = $this->user;
	$t->customer->email = $this->user->user_email;
	$t->affiliate_id = affwp_get_affiliate_id();
	echo $t->display("affiliate-wp/edit-client-form.php");

	?>
</div>

