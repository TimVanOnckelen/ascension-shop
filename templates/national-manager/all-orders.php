<div>
	<?php

	use AscensionShop\Affiliate\SubAffiliate;
	use AscensionShop\NationalManager\Frontend;
	use AscensionShop\NationalManager\NationalManager;

	$langs = '';
	foreach ($this->lang as $l){
		$langs .= $l. ' ';
	}
	?>
    <p><?php printf(__("Alle orders voor de %s shop","ascenion-shop"),$langs); ?><br />
		<?php _e("Je kan alle orders filteren op naam, id, status of bedrag.","ascenion-shop"); ?></p>
	<?php
	$sub = new SubAffiliate(NationalManager::getNationalManagerCountryAff(get_current_user_id()));
	// Get all affiliates
	$all_affiliates = $sub->getAllChildren(2,true,true);

	// Build an array of affiliate IDs and names for the drop down
	$affiliate_dropdown = array();
	// Add self
	$affiliate_dropdown[$sub->getId()] = $sub->getName();

	if ($all_affiliates && !empty($all_affiliates)) {

		foreach ($all_affiliates as $a) {

			if ($affiliate_name = $a->getName()) {
				$affiliate_dropdown[$a->getId()] = $affiliate_name;
			}

		}
	}
	?>
    <div class="partnerArea-header">
        <div class="header">

	        <?php if(!isset($_GET["id"]) && !isset($_GET["partner_id"])){ ?>
                <select id="searchOrderByClient">
                    <option value=""><?php _e("Alle klanten","ascension-shop") ?></option>
                </select>
                <select id="searchOrderByPartner">
                    <option value=""><?php _e("Alle partners","ascension-shop") ?></option>
			        <?php foreach ($affiliate_dropdown as $affiliate_id => $affiliate_name) : ?>
                        <option value="<?php echo esc_attr($affiliate_id); ?>"><?php echo esc_html($affiliate_name); ?></option>
			        <?php endforeach; ?>
                </select>
		        <?php
	        }else{
		        $user_id = $_GET["id"];
		        $partner_id = $_GET["partner_id"];
		        $first_name = get_user_meta($user_id,"first_name",true);
		        $last_name = get_user_meta($user_id,"last_name",true);
		        // Show the current user
		        echo "<h3>".$first_name.' '.$last_name."</h3>";
	        }
	        ?>

            <label>
				<?php _e("Datum","ascension-shop"); ?>
            </label>
            <input type="date" id="orders-from" placeholder="<?php _e("Van","ascension-shop"); ?>"><br /><input type="date" id="orders-to" placeholder="<?php _e("Tot","ascension-shop"); ?>">
        </div>
    </div>

    <table id="all-orders" class="affwp-table affwp-table-responsive">
        <thead>
        <tr>
            <th><?php _e("ID","ascension-shop") ?></th>
            <th><?php _e("Datum","ascension-shop") ?></th>
            <th><?php _e("Klant","ascension-shop") ?></th>
            <th><?php _e("Partner","ascension-shop") ?></th>
            <th><?php _e("Status","ascension-shop") ?></th>
            <th><?php _e("Bedrag","ascension-shop") ?></th>
            <th><?php _e("Acties","ascension-shop") ?></th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>