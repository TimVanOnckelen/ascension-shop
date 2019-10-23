<?php

use AscensionShop\Affiliate\Helpers;
use AscensionShop\Affiliate\SubAffiliate;
use AscensionShop\Lib\TemplateEngine;

$affiliate_id = affwp_get_affiliate_id();
$customers    = Helpers::getAllCustomersFromPartnerAndSubs($affiliate_id);
$sub = new SubAffiliate($affiliate_id);
?>

<div id="affwp-affiliate-dashboard-lifetime-customers" class="affwp-tab-content">
    <h4><?php _e( 'Klanten', 'ascension-shop' ); ?></h4>
    <p><a href="<?php echo $_SERVER['REQUEST_URI'].'&generateReport=clients';?>"><button><?php _e("Download als XLS","ascension-shop"); ?></button></a></p>
    <p>
        <a href="?tab=add-client"><button><?php _e("Nieuwe klant aanmaken"); ?></button></a>
    </p>
	<?php
	// Get all affiliates
	$all_affiliates = $sub->getAllChildren();

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

	<?php do_action("ascension-after-clients"); ?>

</div>
