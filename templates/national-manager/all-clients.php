<div>
	<p><?php use AscensionShop\Affiliate\SubAffiliate;
		use AscensionShop\Lib\TemplateEngine;
		use AscensionShop\NationalManager\NationalManager;


		printf(__("Overzicht van alle klanten van de %s shop","ascension-shop"),$this->lang[0]); ?></p>
    <?php
    $sub = new SubAffiliate(NationalManager::getNationalManagerCountryAff(get_current_user_id()));
    // Get all affiliates
    $all_affiliates = $sub->getAllChildren(true,true);

    // Build an array of affiliate IDs and names for the drop down
    $affiliate_dropdown = array();

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

            <label for="searchByPartner"><?php _e("Filter op partner") ?></label>
            <select id="searchByPartner">
                <option value=""></option>
				<?php foreach ($affiliate_dropdown as $affiliate_id => $affiliate_name) : ?>
                    <option value="<?php echo esc_attr($affiliate_id); ?>"><?php echo esc_html($affiliate_name); ?></option>
				<?php endforeach; ?>
            </select>
            <label><?php _e( "Naam", "ascension-shop" ); ?></label>
            <input type="text" id="searchByName" name="searchByName" placeholder="">
        </div>
        <div class="buttons">
            <p>
                <a href="?tab=add-client"><button><?php _e("Nieuwe klant aanmaken"); ?></button></a>
            </p>
            <p><a href="<?php echo $_SERVER['REQUEST_URI'].'?generateReport=clients';?>"><button><?php _e("Download als XLS","ascension-shop"); ?></button></a></p>
        </div>
    </div>

    <table id="all-clients" class="affwp-table affwp-table-responsive">

        <thead>
        <tr>
            <th><?php _e("ID","ascension-shop") ?></th>
            <th width="40%"><?php _e("Gegevens","ascension-shop") ?></th>
            <th><?php _e("Klant van","ascension-shop") ?></th>
            <th width="20%"><?php _e("Korting","ascension-shop") ?></th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
