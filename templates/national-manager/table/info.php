<?php
use AscensionShop\Lib\TemplateEngine;
?>
<div id="info-user-<?php
echo $this->user->ID; ?>">
	<?php echo $this->user->first_name; ?> <?php echo $this->user->last_name; ?><br />
	<?php echo get_user_meta( $this->user->ID, 'billing_address_1', true ); ?><br />
	<?php echo get_user_meta( $this->user->ID, 'billing_postcode', true ). ' '.get_user_meta( $this->user->ID, 'billing_city', true ); ?><br />
	<?php echo get_user_meta( $this->user->ID, 'billing_phone', true ); ?><br />
	<?php echo $this->user->user_email; ?><br />
	<?php echo get_user_meta( $this->user->ID, 'billing_company', true ); ?><br />
	<?php echo get_user_meta( $this->user->ID, 'vat_number', true ); ?><br />
	<?php
    $user = $this->user;
	$adt_rp_key = get_password_reset_key($this->user);
	$user_login = $user->user_login;
	$rp_link = '<b><a href="' . wp_login_url()."?action=rp&key=$adt_rp_key&login=" . rawurlencode($user_login) . '" target="_blank">'.__("Reset wachtwoord","ascension-shop").'</a></b>';
	echo $rp_link;
	?>
</div>
<?php

$t = new TemplateEngine();
// Make it accessable in template
$this->user->user_id = $this->user->ID;
$t->customer = $this->user;
$t->customer->email = $this->user->user_email;
$t->affiliate_id = affwp_get_affiliate_id();
echo $t->display("affiliate-wp/edit-client-form.php");

?>