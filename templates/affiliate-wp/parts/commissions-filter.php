<form method="GET" id="ascension-filters" >
    <div class="partnerArea-header">
    <div class="header">
	<input type="hidden" name="tab" value="commission-overview" />
	<label for="from"><?php _e("Van","ascension-shop"); ?></label><input type="date" name="from" value="<?php echo $_GET["from"]; ?>" />
	<label for="to"><?php _e("Tot","ascension-shop"); ?></label><input type="date" name="to" value="<?php echo $_GET["to"]; ?>" />
	<p>
		<label for="client"><?php _e("Klant","ascension-shop"); ?></label>
		<select name="client"  class="searchByPartner">
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
		<select name="partner" class="searchByPartner">
			<option value=""><?php _e("Alle partners + eigen","ascension-shop");?></option>
			<?php

			$children = $this->sub->getAllChildren();

			foreach($children as $c){
				$name = affwp_get_affiliate_name($c->getId());
				echo '<option '.selected($name,$_GET["partner"]).' value="'.$c->getId().'">'.$name.'</option>';
			}


			?>
		</select>
	</p>

    </div>
    <div class="buttons">
	<input type="submit" value="<?php _e("Filter commissies"); ?>" />
    </div>
    </div>
</form>
