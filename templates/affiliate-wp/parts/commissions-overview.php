<?php

use AscensionShop\Affiliate\Helpers;
use AscensionShop\Affiliate\SubAffiliate;

$extra_info = "";

if(isset($_GET["client"]) && $_GET["client"] != ''){

	$client = affwp_get_customer($_GET["client"]);

	$extra_info .= '<b>'.__("Klant","ascension-shop").': </b>'.$client->first_name.' '.$client->last_name.'<br />';
}

if(isset($_GET["partner"]) && $_GET["partner"] != ''){


	$extra_info .= '<b>'.__("Partner","ascension-shop").'</b>: '.affwp_get_affiliate_name($_GET["partner"]).'<br />';
}

global $wp;

?>
<h2><?php _e("Rapport commissies","asension-shop"); ?></h2>

<div class="ascension-report-overview">
	<div class="info"><?php echo $extra_info;?></div>
	<br />
	<b><u><?php _e("Overzicht","asenscion-shop"); ?></u></b><br />
    <table class="order-details">
        <thead>
        <tr>
            <th class="referral-order-id"><?php _e( 'Product(en)', 'affiliate-wp' ); ?></th>
            <th class="referral-client"><?php _e( 'Commissie', 'affiliate-wp' ); ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php _e("Commissie","acension-shop"); ?> <?php echo ' '.$_GET["from"]. ' - '.$_GET["to"]; ?></td>
            <td><?php echo affwp_currency_filter( affwp_format_amount($this->totals["total"])); ?></td>
        </tr>
		<?php
		if($this->totals["paid"] > 0){
			?>
            <tr>
                <td><?php _e("Reeds uitbetaald","acension-shop"); ?> <?php echo ' '.$this->date_from. ' - '.$this->date_to; ?></td>
                <td> - <?php echo affwp_currency_filter( affwp_format_amount($this->totals["paid"])); ?></td>
            </tr>
			<?php
		}
		?>
        <tr>
            <td style="text-align: right;"><b><?php _e("Commissies","acension-shop"); ?></b></td>
            <td><?php echo affwp_currency_filter( affwp_format_amount($this->totals["unpaid"])); ?></td>
        </tr>
        </tbody>
    </table>


	<div class="blocks"><a href="<?php echo $_SERVER['REQUEST_URI'].'&generateReport=commissions';?>"><button><?php _e("Download als XLSx","ascension-shop"); ?></button></a>
		<!-- <a href="#" class="downloadOverview"><button><?php _e("Print overzicht","ascension-shop"); ?></button></a></div>-->

</div>