<h2><?php _e("Ik wil bestellen voor ","ascension-shop"); ?></h2>
<label></label>
<div class="can-toggle ascension-toggle">
	<input id="ascension-order-for" name="ascension-order-for" type="checkbox">
	<label for="ascension-order-for">
		<div class="can-toggle__switch" data-checked="<?php _e("Klant","ascension-shop"); ?>" data-unchecked="<?php _e("Mezelf","ascension-shop"); ?>"></div>
	</label>
</div>

<div id="ascension-clients-form" style="display: none;">
<label for="ascension-clients"><?php _e("Klanten","ascension-shop"); ?></label>
<select id="ascension-clients" name="ascension-clients">
    <option value=""></option>
<?php
	foreach ($this->customers as $c){
		echo '<option value="'.$c->customer_id.'">'.$c->first_name.' '.$c->last_name.'</option>';
	}
?>
</select>
    <div id="ascension-who-pays-container" style="display: none;" >
        <h2><?php _e("Wie betaalt?","ascension-shop"); ?></h2>

    <div class="can-toggle ascension-toggle-whopays">
        <input id="ascension-who-pay" name="ascension-who-pays" type="checkbox">
        <label for="ascension-who-pay">
            <div class="can-toggle__switch" data-checked="<?php _e("Klant","ascension-shop"); ?>" data-unchecked="<?php _e("Ik","ascension-shop"); ?>"></div>
        </label>
    </div>

    </div>
</div>

