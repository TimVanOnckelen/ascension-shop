<form method="GET" id="ascension-filters" >
	<input type="hidden" name="tab" value="commission-overview" />
	<label for="from"><?php _e("Van","ascension-shop"); ?></label><input type="date" name="from" value="<?php echo $_GET["from"]; ?>" />
	<label for="to"><?php _e("Tot","ascension-shop"); ?></label><input type="date" name="to" value="<?php echo $_GET["to"]; ?>" />
	<p>
		<label for="client"><?php _e("Klant","ascension-shop"); ?></label>
		<select name="client">
			<option value=""><?php _e("Alle klanten"); ?></option>
			<?php
			foreach ($this->customers as $c){
				$selected = "";
				if($c->customer_id == $_GET["client"]){
					$selected = "SELECTED";
				}

				echo '<option value="'.$c->customer_id.'" '.$selected.'>'.$c->first_name.' '.$c->last_name.'</option>';
			}
			?>
		</select>
		<label for="direct">
			<?php _e("Partner","ascension-shop"); ?>
		</label>
		<select name="partner">
			<option value=""><?php _e("Alle partners + eigen","ascension-shop");?></option>
			<?php

			$children = $this->sub->getAllChildren();

			foreach($children as $c){
				$name = affiliate_wp()->affiliates->get_affiliate_name($c->getId());
				echo '<option '.selected($name,$_GET["partner"]).' value="'.$name.'">'.$name.'</option>';
			}


			?>
		</select>
		<label for="status">
			<?php _e("Status","ascension-shop"); ?>
		</label>
		<select name="status">
			<option value=""><?php _e("Alle commissies","ascension-shop");?></option>
			<option <?php selected($_GET["status"],"paid"); ?> value="paid"><?php _e("Betaald","ascension-shop");?></option>
			<option <?php selected($_GET["status"],"unpaid"); ?> value="unpaid"><?php _e("Onuitbetaald","ascension-shop");?></option>
			<option <?php selected($_GET["status"],"pending"); ?> value="pending"><?php _e("Wachtend","ascension-shop");?></option>
			<option <?php selected($_GET["status"],"rejected"); ?> value="rejected"><?php _e("Geweigerd","ascension-shop");?></option>
		</select>
	</p>

	<input type="submit" value="<?php _e("Filter commissies"); ?>" />
</form>