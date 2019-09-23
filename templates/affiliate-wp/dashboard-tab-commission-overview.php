<?php

use AscensionShop\Affiliate\Helpers;
use AscensionShop\Affiliate\SubAffiliate;
use AscensionShop\Lib\TemplateEngine;

$affiliate_id = affwp_get_affiliate_id();
$sub = new SubAffiliate($affiliate_id);

// Totals
$total_commision = 0;
$total_ex_vat = 0;
$total_inc_vat = 0;

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

<div id="affwp-affiliate-dashboard-referrals" class="affwp-tab-content">

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
            'search' => true
		)
	);
	?>

	<?php
	/**
	 * Fires before the referrals dashbaord data able within the referrals template.
	 *
	 * @param int $affiliate_id Affiliate ID.
	 */
	do_action( 'affwp_referrals_dashboard_before_table', $affiliate_id );
	?>

    <form method="GET" id="ascension-filters" >
        <input type="hidden" name="tab" value="commission-overview" />
        <label for="from"><?php _e("Van","ascension-shop"); ?></label><input type="date" name="from" value="<?php echo $_GET["from"]; ?>" />
        <label for="to"><?php _e("Tot","ascension-shop"); ?></label><input type="date" name="to" value="<?php echo $_GET["to"]; ?>" />
        <p>
        <label for="client"><?php _e("Klant","ascension-shop"); ?></label>
        <select name="client">
            <option value=""><?php _e("Alle klanten"); ?></option>
			<?php
			foreach ($customers as $c){
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

                $children = $sub->getAllChildren();

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

    <?php

    // Add titles
    if(isset($_GET["from"])){
	    $extra_info .= "Van ".$_GET["from"].' tot '.$_GET["to"].'<br />';
    }

    if(isset($_GET["client"]) && $_GET["client"] != ''){

        $client = affwp_get_customer($_GET["client"]);

        $extra_info .= "Klant: ".$client->first_name.' '.$client->last_name.'<br />';
    }
    ?>
    <h2><?php _e("Overzicht commissies","asension-shop"); ?></h2>
    <p><?php echo $extra_info;?></p>
    <table id="affwp-affiliate-dashboard-referrals" class="affwp-table affwp-table-responsive">
        <thead>
        <tr>
            <th class="referral-order-id"><?php _e( 'Order', 'affiliate-wp' ); ?></th>
            <th class="referral-client"><?php _e( 'Klant', 'affiliate-wp' ); ?></th>
            <th class="referral-amount"><?php _e( 'Commission', 'affiliate-wp' ); ?></th>
            <th class="referral-percentage"><?php _e( 'Percentage', 'affiliate-wp' ); ?></th>
            <th class="referral-status"><?php _e( 'Status', 'affiliate-wp' ); ?></th>
            <th class="referral-date"><?php _e( 'Date', 'affiliate-wp' ); ?></th>
			<?php
			/**
			 * Fires in the dashboard referrals template, within the table header element.
			 */
			do_action( 'affwp_referrals_dashboard_th' );
			?>
        </tr>
        </thead>

        <tbody>
		<?php if ( $referrals ) : ?>

			<?php foreach ( $referrals as $referral ) :

                $total_commision += $referral->amount;

                ?>
                <tr>
                    <td class="ascension-order-info" data-th="<?php _e( 'Order', 'affiliate-wp' ); ?>">

                        <?php
                        $order_id = $referral->reference;
                        $order = new \WC_Order($order_id);
                        $user = $order->get_user();
                        ?>

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

                        <?php

                        /**
                         * Get the rates
                         */
                            $user_rate =  $sub->getUserRate();
                            $min = '';
                            $return_rate = $user_rate;
                            $total = $order->get_subtotal();
                            $amount = round( $referral->amount,2);
                            $amount_check = round($user_rate*($total/100),2);

                        /**
                         * Check if there is a new rate
                         */
                            if($amount_check != $amount){
                                $min = $amount_check - $amount;
                                $min_percentage = round(100*($min/$total),2);
                                if($min_percentage > $user_rate){
                                    $min_percentage = $user_rate;
                                }
                                $min = ' - '.$min_percentage.'%';
                                $new_rate = $user_rate-$min_percentage;
                                $min .= ' = '.$new_rate.'%';
                            }

                            echo $user_rate.'%'.$min;
                        ?>

                    </td>
                    <td class="referral-status <?php echo $referral->status; ?>" data-th="<?php _e( 'Status', 'affiliate-wp' ); ?>"><?php echo affwp_get_referral_status_label( $referral ); ?></td>
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
        <tr><td><b><?php _e("Totaal","ascension-shop"); ?></b></td><td></td><td>&euro; <?php echo $total_commision; ?></td><td></td><td></td><td></td><td></td></tr>
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
