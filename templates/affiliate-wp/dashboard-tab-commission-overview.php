<?php

use AscensionShop\Affiliate\Helpers;
use AscensionShop\Affiliate\SubAffiliate;
use AscensionShop\Lib\TemplateEngine;

$affiliate_id = affwp_get_affiliate_id();
$sub = new SubAffiliate($affiliate_id);

if(!isset($_GET["partner"]) OR $_GET["partner"] == ''){
    $_GET["partner"] = $affiliate_id;
}

$customers = affiliate_wp_lifetime_commissions()->integrations->get_customers_for_affiliate($affiliate_id);
usort($customers, function ($first, $second) {
	return strcasecmp($first->first_name, $second->first_name);
});

$month = date("Y-m",time());

/** Get month & year */
if(!isset($_GET["from"])){

	if(!isset($_GET["to"])){
		$_GET["to"] = date('Y-m-d',strtotime('last day of this month'));
	}

    $_GET["from"] = $month.'-01';
}

if(!isset($_GET["status"])){
    $_GET["status"] = array(  );
}


?>

<div id="affwp-affiliate-dashboard-referrals" class="affwp-tab-content printArea">

	<?php

    $filter_Status = array("paid","unpaid");
    $date = array('start' => $_GET["from"],'end' => $_GET["to"]);

    // Leave dates out when filitering on paid & unpaid statusess
    if(in_array($_GET["status"],$filter_Status)){
        $date = null;
    }

	$per_page  = 500000000;
	/** @var \AffWP\Referral[] $referrals */
	$referrals = affiliate_wp()->referrals->get_referrals(
		array(
			'number'       => $per_page,
			'affiliate_id' => $affiliate_id,
			'customer_id' => $_GET["client"],
			'status'       => array("paid","unpaid"),
            'affiliate_id' => $_GET["partner"],
            'search' => true,
            'orderby' => "custom",
            'order' => 'ASC'
		)
	);

	/**
	 * Filter out by paid date
	 */
        foreach ($referrals as $id => $ref){

            if(in_array($ref->status,$filter_Status)) {

	            $date_paid  = get_post_meta( $ref->reference, "_paid_date", true );
	            $date_paid  = strtotime( $date_paid );
	            $end_date   = strtotime( $_GET["to"] . ' 00:00' );
	            $start_date = strtotime( $_GET["from"] . ' 00:00' );

	            if ( $date_paid <= $end_date && $date_paid >= $start_date ) {
		            continue;
	            } else {// Unset refrence
		            unset( $referrals[ $id ] );
	            }

            }

        }


	$totals = Helpers::getTotalsFromRefs($referrals);

	?>

	<?php
	/**
	 * Fires before the referrals dashbaord data able within the referrals template.
	 *
	 * @param int $affiliate_id Affiliate ID.
	 */
	do_action( 'affwp_referrals_dashboard_before_table', $affiliate_id );


	/**
	 * Get the filter template
	 *
	 */
    $t = new TemplateEngine();
    $t->customers = $customers;
    $t->sub = $sub;
    echo $t->display("affiliate-wp/parts/commissions-filter.php");


	/**
	 * Add Overview
	 *
	 */
	$t = new TemplateEngine();
	$t->totals = $totals;
	echo $t->display("affiliate-wp/parts/commissions-overview.php");
?>

    <table class="affwp-table affwp-table-responsive order-details">
        <thead>
        <tr>
            <th class="referral-order-id"><?php _e( 'Order', 'affiliate-wp' ); ?></th>
            <th class="referral-client"><?php _e( 'Klant', 'affiliate-wp' ); ?></th>
            <th class="referral-percentage"><?php _e( 'Percentage', 'affiliate-wp' ); ?></th>
            <th class="referral-date"><?php _e( 'Datum', 'affiliate-wp' ); ?></th>
            <th class="referral-date"><?php _e( 'Betaal datum', 'affiliate-wp' ); ?></th>
            <th class="referral-date"><?php _e( 'Totaal bedrag inc btw', 'affiliate-wp' ); ?></th>
            <th class="referral-date"><?php _e( 'Totaal bedrag ex btw', 'affiliate-wp' ); ?></th>
            <th class="referral-amount"><?php _e( 'Commission', 'affiliate-wp' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php
        
		if ( $referrals ) :

			$old_parent = "";
			$sub = new SubAffiliate( $affiliate_id );
			$sub_total = 0;
			$sub_total_ex = 0;
			$sub_total_in = 0;
			$counter = 0;
			$total_exvat = 0;
			$total_invat = 0;

			foreach ( $referrals as $referral ) :

				$order_id = $referral->reference;
				$order = new \WC_Order( $order_id );
				$user = $order->get_user();

				// Get percentage
				$percentage = Helpers::getPercentageTable( $sub, $order, $referral );

				$parent = Helpers::getParentFromRef($referral);

				if(str_replace(' ','',strtolower($old_parent)) != str_replace(' ','',strtolower($parent))){

					$temp_parent = $old_parent;
					$old_parent = $parent;

					if($parent != '') {
						// Add a extra header row
                        if($counter > 0) {
	                        ?>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><b><?php _e( "Totaal", "ascension-shop" ); ?></b></td>
                                <td><?php echo affwp_currency_filter( affwp_format_amount( $sub_total_ex ) ); ?></td>
                                <td><?php echo affwp_currency_filter( affwp_format_amount( $sub_total_in ) ); ?></td>
                                <td><b><?php echo affwp_currency_filter( affwp_format_amount( $sub_total ) ); ?></b>
                                </td>
                            </tr>
	                        <?php
                        }
						?>
                        <tr style="background-color:#eee;">
                            <td colspan="8"><b><?php echo $parent . ' - ' . $percentage; ?></b></td>
                        </tr>
						<?php

						$sub_total    = 0;
						$sub_total_ex = 0;
						$sub_total_in = 0;
					}else{
						if($counter > 0) {
							?>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><b><?php _e( "Totaal", "ascension-shop" ); ?></b></td>
                                <td><?php echo affwp_currency_filter( affwp_format_amount( $sub_total_ex ) ); ?></td>
                                <td><?php echo affwp_currency_filter( affwp_format_amount( $sub_total_in ) ); ?></td>
                                <td><b><?php echo affwp_currency_filter( affwp_format_amount( $sub_total ) ); ?></b>
                                </td>
                            </tr>
							<?php
						}
						?>
                        <tr style="background-color:#eee;">
                            <td colspan="8"><b><?php echo __( "Eigen commissies", "ascension-shop" ); ?></b></td>
                        </tr>
						<?php
						$sub_total    = 0;
						$sub_total_ex = 0;
						$sub_total_in = 0;
					}

				}

				$counter++;
				?>
                <tr>
                    <td class="ascension-order-info" data-th="<?php _e( 'Order', 'affiliate-wp' ); ?>">
                        <a target="_blank" href="<?php echo $order->get_view_order_url(); ?>"
                           class="ascension-order-details-hover"># <?php echo $referral->reference; ?></a>
                    </td>
                    <td><?php echo $order->get_billing_first_name() . " " . $order->get_billing_last_name(); ?></td>
                    <td class="referral-percentage" data-th="<?php _e( 'Percentage', 'affiliate-wp' ); ?>">

		                <?php echo $percentage; ?>

                    </td>
                    <td class="referral-date"
                        data-th="<?php _e( 'Date', 'affiliate-wp' ); ?>"><?php echo esc_html( $referral->date_i18n( 'datetime' ) ); ?></td>
                    <td><?php echo date( 'd F Y H:i', strtotime( get_post_meta( $referral->reference, "_paid_date", true ) ) ); ?></td>
	                <?php
	                // calculate amounts in & ex vat
	                $amounts_ex = Helpers::calculateExIncVat( $referral ); ?>

                    <td><?php echo affwp_currency_filter( affwp_format_amount( $amounts_ex["ex"] ) ); ?></td>
                    <td><?php echo affwp_currency_filter( affwp_format_amount( $amounts_ex["in"] ) ); ?></td>
                    <td class="referral-amount"
                        data-th="<?php _e( 'Commission', 'affiliate-wp' ); ?>"><?php echo affwp_currency_filter( affwp_format_amount( $referral->amount ) ); ?></td>

	                <?php
	                $sub_total_ex += $amounts_ex["ex"];
	                $sub_total_in += $amounts_ex["in"];
	                $sub_total    += $referral->amount;
	                $total_exvat  += $amounts_ex["ex"];
	                $total_invat  += $amounts_ex["in"];
	                ?>
                </tr>
			<?php endforeach; ?>

		<?php else : ?>

            <tr>
                <td class="affwp-table-no-data"
                    colspan="5"><?php _e( 'Geen commissies gevonden.', 'affiliate-wp' ); ?></td>
            </tr>

		<?php endif; ?>
        <tr>
            <td><b><?php _e( "Totaal", "ascension-shop" ); ?></b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><?php echo affwp_currency_filter( affwp_format_amount( $total_exvat ) ); ?></td>
            <td><?php echo affwp_currency_filter( affwp_format_amount( $total_invat ) ); ?></td>
            <td><?php echo affwp_currency_filter( affwp_format_amount( $totals["total"] ) ); ?></td>
        </tr>
        </tbody>
    </table>

	<?php
	/**
	 * Fires after the data table within the affiliate area referrals template.
	 *
	 * @param int $affiliate_id Affiliate ID.
	 */
	do_action( 'affwp_referrals_dashboard_after_table', $affiliate_id );
	?>



</div>
