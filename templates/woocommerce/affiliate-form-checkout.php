<h2><?php _e("Ik wil bestellen voor ","ascension-shop"); ?></h2>
<label></label>
<div class="">
    <input name="ascension-order-for" type="radio" value="me" checked="checked" /><?php _e("Mezelf","ascension-shop"); ?><br />
    <input name="ascension-order-for" type="radio" value="client" /><?php _e("Klant","ascension-shop"); ?>

</div>

<div id="ascension-clients-form" style="display: none;">
<label for="ascension-clients"><?php _e("Klanten","ascension-shop"); ?></label>
<select id="ascension-clients" name="ascension-clients">
    <option value=""></option>
<?php
	foreach ($this->customers as $c){
		$userdata = get_userdata( $c->user_id );
		if ( $userdata->first_name != '' and $userdata->last_name != '' ) {
			echo '<option value="' . $c->customer_id . '">' . $userdata->first_name . ' ' . $userdata->last_name . '</option>';
		}
	}
?>
</select>
    <div id="ascension-who-pays-container" style="display: none;" >
        <h2><?php _e("Wie betaalt?","ascension-shop"); ?></h2>

    <div>

        <input name="ascension-who-pays" type="radio" value="false" checked="checked" /><?php _e("Ik","ascension-shop"); ?><br />
        <input name="ascension-who-pays" type="radio" value="true" /><?php _e("Klant","ascension-shop"); ?>

    </div>

    </div>
</div>

