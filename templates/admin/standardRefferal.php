<h1><?php _e("Standaard Affiliate","ascension-shop"); ?></h1>
<p>
	<?php
	_e("Selecteer hieronder de standaard Affiliate per taal. Deze wordt gebruikt wanneer er geen refferal is ingesteld.");
	?>
</p>
<form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
<?php
	foreach ($this->langs as $lang){
		?>
		<p>
		<label for="<?php echo $lang["code"] ?>"><?php echo $lang["native_name"] ?></label>
		<select name="<?php echo $lang["code"] ?>">
			<option value=""></option>
			<?php
				foreach ($this->affiliates as $aff){
					$user = get_user_by("id",$aff->user_id);
					$the_value = get_option("ascension-shop_standard_ref_".$lang["code"]);

					?>
			<option value="<?php echo $aff->affiliate_id; ?>" <?php selected( $the_value, $aff->affiliate_id ); ?>><?php echo $user->first_name." ".$user->last_name ?></option>
					<?php
				}
			?>
		</select>
		</p>
	<?php
	}

?>
	<?php wp_nonce_field( 'ascension_save_standard_ref'.$this->user_id ); ?>
	<input type="hidden" name="action" value="ascension-save_standard_ref" />
	<input type="submit" value="<?php _e("Opslaan","ascension-shop"); ?>"/>
</form>
