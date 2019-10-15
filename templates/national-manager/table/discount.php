<form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" class="editDiscount">
    <input type="number" class="customer_rate" name="customer_rate[<?php echo $this->user->id; ?>]" step=".01" value="<?php echo get_user_meta($this->user->id,"ascension_shop_affiliate_coupon",true); ?>">
	<?php wp_nonce_field( 'ascension_save_customer_discount_'.$this->user->id ); ?>
    <input type="hidden" name="action" value="ascension-save_customer-discount">
    <input type="submit" value="<?php _e("Opslaan","ascension-shop"); ?>" />
</form>