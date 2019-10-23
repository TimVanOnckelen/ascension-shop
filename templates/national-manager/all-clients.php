<div class="tab2">
	<p><?php use AscensionShop\Lib\TemplateEngine;

		$affiliate_id = affwp_get_affiliate_id();


		printf(__("Overzicht van alle klanten van de %s shop","ascension-shop"),$this->lang[0]); ?></p>
    <?php
    // Get all affiliates
    $all_affiliates = affiliate_wp()->affiliates->get_affiliates(array('number' => 0,'orderby'=>'name'));

    // Build an array of affiliate IDs and names for the drop down
    $affiliate_dropdown = array();

    if ($all_affiliates && !empty($all_affiliates)) {

	    foreach ($all_affiliates as $a) {

		    if ($affiliate_name = affiliate_wp()->affiliates->get_affiliate_name($a->affiliate_id)) {
			    $affiliate_dropdown[$a->affiliate_id] = $affiliate_name;
		    }

	    }
    }
    ?>
    <label for="searchByPartner"><?php _e("Filter op partner") ?></label>
    <select id="searchByPartner">
            <option value=""></option>
		    <?php foreach ($affiliate_dropdown as $affiliate_id => $affiliate_name) : ?>
                <option value="<?php echo esc_attr($affiliate_id); ?>"><?php echo esc_html($affiliate_name); ?></option>
		    <?php endforeach; ?>
    </select>
    <table id="all-clients" class="affwp-table affwp-table-responsive">

        <thead>
        <tr>
            <th><?php _e("ID","ascension-shop") ?></th>
            <th><?php _e("Naam","ascension-shop") ?></th>
            <th><?php _e("Gegevens","ascension-shop") ?></th>
            <th><?php _e("Klant van","ascension-shop") ?></th>
            <th><?php _e("Tools","ascension-shop") ?></th>
            <th width="20%"><?php _e("Korting","ascension-shop") ?></th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
