<?php

use AscensionShop\Affiliate\Helpers;
use AscensionShop\Affiliate\SubAffiliate;
use AscensionShop\Lib\TemplateEngine;

$affiliate_id = affwp_get_affiliate_id();
$sub = new SubAffiliate($affiliate_id);


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

    <h4><?php _e( 'Referrals', 'affiliate-wp' ); ?></h4>

	<?php
	$per_page  = 500000000;
	/** @var \AffWP\Referral[] $referrals */
	$referrals = affiliate_wp()->referrals->get_referrals(
		array(
			'number'       => $per_page,
			'affiliate_id' => $affiliate_id,
			'customer_id' => $_GET["client"],
			'status'       => $_GET["status"],
            'date' => array('start' => $_GET["from"],'end' => $_GET["to"]),
            'description' => $_GET["partner"],
            'search' => true,
            'orderby' => "custom",
            'order' => 'ASC'
		)
	);

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

    <table id="affwp-affiliate-dashboard-referrals-table" class="affwp-table">
        <thead>
        <tr>
            <th class="referral-order-id"><?php _e( 'Order', 'affiliate-wp' ); ?></th>
            <th class="referral-client"><?php _e( 'Klant', 'affiliate-wp' ); ?></th>
            <th class="referral-amount"><?php _e( 'Commission', 'affiliate-wp' ); ?></th>
            <th class="referral-percentage"><?php _e( 'Percentage', 'affiliate-wp' ); ?></th>
            <th class="referral-status"><?php _e( 'Status', 'affiliate-wp' ); ?></th>
            <th class="referral-date"><?php _e( 'Datum', 'affiliate-wp' ); ?></th>
            <?php
			/**
			 * Fires in the dashboard referrals template, within the table header element.
			 */
			do_action( 'affwp_referrals_dashboard_th' );
			?>
        </tr>
        </thead>

        <tbody>
		<?php if ( $referrals ) :

            $old_parent = "";

            foreach ( $referrals as $referral ) :

	            $order_id = $referral->reference;
	            $order = new \WC_Order($order_id);
	            $user = $order->get_user();

	            // Get percentage
	            $percentage =  Helpers::getPercentageTable($sub,$order,$referral);

			    $parent = Helpers::getParentFromRef($referral);

				if(str_replace(' ','',strtolower($old_parent)) != str_replace(' ','',strtolower($parent))){

					$old_parent = $parent;
				    if($parent != '') {
					    // Add a extra header row
					    ?>
                        <tr style="background-color:#eee;">
                            <td colspan="9"><b><?php echo $parent. ' - '.$percentage; ?></b></td>
                        </tr>
					    <?php
				    }else{
					    // Add a extra header row
					    ?>
                        <tr style="background-color:#eee;">
                            <td colspan="9"><b><?php echo __("Eigen commissies","ascension-shop"); ?></b></td>
                        </tr>
					    <?php
                    }

                }

                ?>
                <tr>
                    <td class="ascension-order-info" data-th="<?php _e( 'Order', 'affiliate-wp' ); ?>">

                        <a href="#" class="ascension-order-details-hover"># <?php echo $referral->reference; ?></a>

		                <?php
		                $t = new TemplateEngine();
		                $t->order = $order;
		                echo $t->display("affiliate-wp/dashboard-order-info.php");
		                ?>

                    </td>
                    <td><?php echo $user->first_name . " " . $user->last_name; ?></td>
                    <td class="referral-amount" data-th="<?php _e( 'Commission', 'affiliate-wp' ); ?>"><?php echo affwp_currency_filter( affwp_format_amount( $referral->amount ) ); ?></td>
                    <td class="referral-percentage" data-th="<?php _e( 'Percentage', 'affiliate-wp' ); ?>">

                       <?php echo $percentage; ?>

                    </td>
                    <td class="referral-status <?php echo $referral->status; ?>" data-th="<?php _e( 'Status', 'affiliate-wp' ); ?>"><?php echo affwp_get_referral_status_label( $referral );  ?></td>
                    <td class="referral-date" data-th="<?php _e( 'Date', 'affiliate-wp' ); ?>"><?php echo esc_html( $referral->date_i18n( 'datetime' ) ); ?></td>
					<?php
					/**
					 * Fires within the table data of the dashboard referrals template.
					 *
					 * @param \AffWP\Referral $referral Referral object.
					 */
					do_action( 'affwp_referrals_dashboard_td', $referral ); ?>
                </tr>
			<?php endforeach; ?>

		<?php else : ?>

            <tr>
                <td class="affwp-table-no-data" colspan="5"><?php _e( 'You have not made any referrals yet.', 'affiliate-wp' ); ?></td>
            </tr>

		<?php endif; ?>
        <tr><td><b><?php _e("Totaal","ascension-shop"); ?></b></td><td></td><td>&euro; <?php echo $totals["total"]; ?></td><td></td><td></td><td></td><td></td></tr>
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
