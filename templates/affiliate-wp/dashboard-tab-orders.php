<?php

use AscensionShop\NationalManager\Frontend;
use AscensionShop\NationalManager\NationalManager;

// Get partner id
$partner = affwp_get_affiliate_id();
// National manager clients
if(NationalManager::isNationalManger(get_current_user_id())){
	$partner = NationalManager::getNationalManagerCountryAff(get_current_user_id());
}

?>
<div class="partnerArea-header">
    <div class="header">
        <label><?php _e("Klant","ascension-shop") ?></label>

        <?php if(!isset($_GET["id"])){ ?>
        <select id="searchOrderByClient">
            <option value=""><?php _e("Alle klanten","ascension-shop") ?></option>
        </select>
        <?php
	        }else{
	            $user_id = $_GET["id"];
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
<div class="affwp-tab-content">
    <table id="all-orders" class="affwp-table affwp-table-responsive">
        <thead>
        <tr>
            <th><input type="number" id="order-id-search" /> </th>
            <th> </th>
            <th></th>
            <th></th>
            <th>
            </th>
            <th></th>
            <th></th>
        </tr>
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