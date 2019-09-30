<?php
$extra_info = "";

if(isset($_GET["from"])){
	$extra_info .= '<h3>'.__("Van","ascension-shop").' '.$_GET["from"].' '.__("tot","ascension-shop").' '.$_GET["to"].'</h3>';
}

if(isset($_GET["client"]) && $_GET["client"] != ''){

	$client = affwp_get_customer($_GET["client"]);

	$extra_info .= '<b>'.__("Klant","ascension-shop").': </b>'.$client->first_name.' '.$client->last_name.'<br />';
}

if(isset($_GET["partner"]) && $_GET["partner"] != ''){


	$extra_info .= '<b>'.__("Partner","ascension-shop").'</b>: '.$_GET["partner"].'<br />';
}

global $wp;
?>
<h2><?php _e("Rapport commissies","asension-shop"); ?></h2>

<div class="ascension-report-overview" style="background-color:#eee; padding:10px;">
	<div class="info"><?php echo $extra_info;?></div>
	<br />
	<b><u><?php _e("Overzicht","asenscion-shop"); ?></u></b><br />
	<div class="totals">
		<div><b><?php _e("Totaal commissie","ascension-shop"); ?>:</b> <?php echo affwp_currency_filter( affwp_format_amount($this->totals["total"])); ?><br /></div>
		<div><b><?php _e("Onbetaalde commissie","ascension-shop"); ?>:</b> <?php echo affwp_currency_filter( affwp_format_amount($this->totals["unpaid"])); ?><br /></div>
		<div><b><?php _e("Wachtende commissie","ascension-shop"); ?>:</b> <?php echo affwp_currency_filter( affwp_format_amount($this->totals["pending"])); ?><br /></div>
		<div><b><?php _e("Betaalde commissie","ascension-shop"); ?>:</b> <?php echo affwp_currency_filter( affwp_format_amount($this->totals["paid"])); ?></div>
	</div>


	<div class="blocks"><a href="<?php echo $_SERVER['REQUEST_URI'].'&generateReport=commissions';?>"><button><?php _e("Download als XLSx","ascension-shop"); ?></button></a>
		<!-- <a href="#" class="downloadOverview"><button><?php _e("Print overzicht","ascension-shop"); ?></button></a></div>-->

</div>