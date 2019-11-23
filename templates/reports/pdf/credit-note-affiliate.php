<?php use AscensionShop\Affiliate\Helpers;
use AscensionShop\Affiliate\SubAffiliate;
use AscensionShop\Lib\TemplateEngine;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <style type="text/css">
        /* Main Body */
        @page {
            margin-top: 1cm;
            margin-bottom: 3cm;
            margin-left: 2cm;
            margin-right: 2cm;
        }
        body {
            background: #fff;
            color: #000;
            margin: 0cm;
            font-family: 'Open Sans', sans-serif;
            /* want to use custom fonts? http://docs.wpovernight.com/ascension-shop/using-custom-fonts/ */
            font-size: 9pt;
            line-height: 100%; /* fixes inherit dompdf bug */
        }

        h1, h2, h3, h4 {
            font-weight: bold;
            margin: 0;
        }

        h1 {
            font-size: 16pt;
            margin: 5mm 0;
        }

        h2 {
            font-size: 14pt;
        }

        h3, h4 {
            font-size: 9pt;
        }


        ol,
        ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        li,
        ul {
            margin-bottom: 0.75em;
        }

        p {
            margin: 0;
            padding: 0;
        }

        p + p {
            margin-top: 1.25em;
        }

        a {
            border-bottom: 1px solid;
            text-decoration: none;
        }

        /* Basic Table Styling */
        table {
            border-collapse: collapse;
            border-spacing: 0;
            page-break-inside: always;
            border: 0;
            margin: 0;
            padding: 0;
        }

        th, td {
            vertical-align: top;
            text-align: left;
        }

        table.container {
            width:100%;
            border: 0;
        }

        tr.no-borders,
        td.no-borders {
            border: 0 !important;
            border-top: 0 !important;
            border-bottom: 0 !important;
            padding: 0 !important;
            width: auto;
        }

        /* Header */
        table.head {
            margin-bottom: 12mm;
        }

        td.header img {
            max-height: 3cm;
            width: auto;
        }

        td.header {
            font-size: 16pt;
            font-weight: 700;
        }

        td.shop-info {
            width: 40%;
        }
        .document-type-label {
            text-transform: uppercase;
        }

        /* Recipient addressses & order data */
        table.order-data-addresses {
            width: 100%;
            margin-bottom: 10mm;
        }

        td.order-data {
            width: 40%;
        }

        .invoice .shipping-address {
            width: 30%;
        }

        .packing-slip .billing-address {
            width: 30%;
        }

        td.order-data table th {
            font-weight: normal;
            padding-right: 2mm;
        }

        /* Order details */
        table.order-details {
            width:100%;
            margin-bottom: 8mm;
        }

        .quantity,
        .price {
            width: 20%;
        }

        .order-details tr {
            page-break-inside: always;
            page-break-after: auto;
        }

        .order-details td,
        .order-details th {
            border-bottom: 1px #ccc solid;
            border-top: 1px #ccc solid;
            padding: 0.375em;
        }

        .order-details th {
            font-weight: bold;
            text-align: left;
        }

        .order-details thead th {
            color: white;
            background-color: #830051;
            border-color: #830051;
        }

        /* product bundles compatibility */
        .order-details tr.bundled-item td.product {
            padding-left: 5mm;
        }

        .order-details tr.product-bundle td,
        .order-details tr.bundled-item td {
            border: 0;
        }

        .order-details tr.bundled-item.hidden {
            display: none;
        }

        div.clean {
            padding: 10px;
            margin: 30px;
            margin-left: 0;
        }

        a {
            color: #000;
            text-decoration: none;
        }

        /* item meta formatting for WC2.6 and older */
        dl {
            margin: 4px 0;
        }

        dt, dd, dd p {
            display: inline;
            font-size: 7pt;
            line-height: 7pt;
        }

        dd {
            margin-left: 5px;
        }

        dd:after {
            content: "\A";
            white-space: pre;
        }
        /* item-meta formatting for WC3.0+ */
        .wc-item-meta {
            margin: 4px 0;
            font-size: 7pt;
            line-height: 7pt;
        }
        .wc-item-meta p {
            display: inline;
        }
        .wc-item-meta li {
            margin: 0;
            margin-left: 5px;
        }

        /* Notes & Totals */
        .customer-notes {
            margin-top: 5mm;
        }

        table.totals {
            width: 100%;
            margin-top: 5mm;
        }

        table.totals th,
        table.totals td {
            border: 0;
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
        }

        table.totals th.description,
        table.totals td.price {
            width: 50%;
        }

        table.totals tr.order_total td,
        table.totals tr.order_total th {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
        }

        table.totals tr.payment_method {
            display: none;
        }

        /* Footer Imprint */
        #footer {
            position: absolute;
            bottom: -2cm;
            left: 0;
            right: 0;
            height: 2cm; /* if you change the footer height, don't forget to change the bottom (=negative height) and the @page margin-bottom as well! */
            text-align: center;
            border-top: 0.1mm solid gray;
            margin-bottom: 0;
            padding-top: 2mm;
        }

        /* page numbers */
        .pagenum:before {
            content: counter(page);
        }
        .pagenum,.pagecount {
            font-family: sans-serif;
        }
    </style>
</head>
<body>
<?php
$sub = new SubAffiliate($this->partner_id);
$totals = Helpers::getTotalsFromRefs($this->refferals);



?>
	<table class="head container">
		<tr class="underline">
			<td class="header">
				<div class="header-stretcher">
            <img src="<?php echo $this->logo; ?>" />
				</div>
			</td>
            <td class="shop-info">
                <div class="shop-name"><h3><?php echo $this->settings["shop_name"][ICL_LANGUAGE_CODE]; ?></h3></div>
                <div class="shop-address"><?php echo nl2br(trim($this->settings["shop_address"][ICL_LANGUAGE_CODE])); ?></div>
            </td>
		</tr>
	</table>

	<h1 class="document-type-label">
		<?php if( $this->logo ) _e("Credit Nota","ascension-shop"); ?>
	</h1>

    <table class="order-data-addresses">
        <tr>
            <td class="address billing-address">
               <?php
               echo $sub->getName();
               ?><br />
	            <?php
	            echo get_user_meta($sub->getUserId(),"billing_address_1",true);
	            ?><br />
	            <?php
	            echo get_user_meta($sub->getUserId(),"billing_postcode",true). ' '.get_user_meta($sub->getUserId(),"billing_city",true);
	            ?><br />
	            <?php
	            echo WC()->countries->countries[get_user_meta($sub->getUserId(),"billing_country",true)];
	            ?><br />
	            <?php
	            echo get_user_meta($sub->getUserId(),"billing_email",true);
	            ?><br />
	            <?php
	            echo get_user_meta($sub->getUserId(),"billing_company",true);
	            ?><br />
	            <?php
	            echo get_user_meta($sub->getUserId(),"vat_number",true);
	            ?><br />
            </td>

            <td class="order-data">
                <table>

                        <tr class="invoice-date">
                            <th><?php _e( 'Factuur datum:', 'ascension-shop' ); ?></th>
                            <td><?php echo date("d-m-Y",time()); ?></td>
                        </tr>

                    <tr class="order-number">
                        <th><?php _e( 'Partner nummer', 'ascension-shop' ); ?></th>
                        <td><?php echo $sub->getId(); ?></td>
                    </tr>
                    <tr class="order-date">
                        <th><?php _e( 'Klant nummer:', 'ascension-shop' ); ?></th>
                        <td><?php echo $sub->getUserId(); ?></td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

<table class="order-details">
    <thead>
    <tr>
        <th class="referral-order-id"><?php _e( 'Product(en)', 'affiliate-wp' ); ?></th>
        <th class="referral-client"><?php _e( 'Commissie', 'affiliate-wp' ); ?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><?php _e("Commissiefactuur","acension-shop"); ?> <?php echo ' '.$this->date_from. ' - '.$this->date_to; ?></td>
        <td><?php echo affwp_currency_filter( affwp_format_amount($totals["total"])); ?></td>
    </tr>
    <?php
    if($totals["paid"] > 0){
        ?>
        <tr>
            <td><?php _e("Reeds uitbetaald","acension-shop"); ?> <?php echo ' '.$this->date_from. ' - '.$this->date_to; ?></td>
            <td> - <?php echo affwp_currency_filter( affwp_format_amount($totals["paid"])); ?></td>
        </tr>
    <?php
    }
    ?>
    <tr>
        <td style="text-align: right;"><b><?php _e("Creditfactuur","acension-shop"); ?></b></td>
        <td><?php echo affwp_currency_filter( affwp_format_amount($totals["unpaid"])); ?></td>
    </tr>
    </tbody>
</table>

<hr />
<div class="clean" style="page-break-after:always;">
	<p>
		<?php echo nl2br(trim($this->settings["shop_address"][ICL_LANGUAGE_CODE])); ?>
    </p>
    <p>
        <?php echo get_home_url(); ?>
    </p>
</div>

<table class="head container">
    <tr class="underline">
        <td class="header">
            <div class="header-stretcher">
                <img src="<?php echo $this->logo; ?>" />
            </div>
        </td>
        <td class="shop-info">
            <div class="shop-name"><h3><?php echo $this->settings["shop_name"][ICL_LANGUAGE_CODE]; ?></h3></div>
            <div class="shop-address"><?php echo nl2br(trim($this->settings["shop_address"][ICL_LANGUAGE_CODE])); ?></div>
        </td>
    </tr>
</table>

<table class="order-data-addresses">
    <tr>
        <td class="address billing-address">
			<?php
			echo $sub->getName();
			?><br />
			<?php
			echo get_user_meta($sub->getUserId(),"billing_address_1",true);
			?><br />
			<?php
			echo get_user_meta($sub->getUserId(),"billing_postcode",true). ' '.get_user_meta($sub->getUserId(),"billing_city",true);
			?><br />
			<?php
			echo WC()->countries->countries[get_user_meta($sub->getUserId(),"billing_country",true)];
			?><br />
			<?php
			echo get_user_meta($sub->getUserId(),"billing_email",true);
			?><br />
			<?php
			echo get_user_meta($sub->getUserId(),"billing_company",true);
			?><br />
			<?php
			echo get_user_meta($sub->getUserId(),"vat_number",true);
			?><br />
        </td>

        <td class="order-data">
            <table>

                <tr class="invoice-date">
                    <th><?php _e( 'Invoice Date:', 'ascension-shop' ); ?></th>
                    <td><?php echo date("d-m-Y",time()); ?></td>
                </tr>

                <tr class="order-number">
                    <th><?php _e( 'Partner nummer', 'ascension-shop' ); ?></th>
                    <td><?php echo $sub->getId(); ?></td>
                </tr>
                <tr class="order-date">
                    <th><?php _e( 'Klant nummer:', 'ascension-shop' ); ?></th>
                    <td><?php echo $sub->getUserId(); ?></td>
                </tr>

            </table>
        </td>
    </tr>
</table>

<h2><?php _e("Overzicht","ascension-shop"); ?> - <?php echo $sub->getName(); ?></h2>
<div class="clean">
    <p>
        <b><?php _e("Partner","ascension-shop"); ?></b>: <?php echo $sub->getName(); ?><br />
        <b><?php _e("Commissie","ascension-shop"); ?></b>: <?php echo $sub->getUserRate(); ?> %<br />
    </p>
</div>
<table class="order-details">
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

    if ( $this->refferals ) :

		$old_parent = "";
	    $sub = new SubAffiliate($this->partner_id);

		foreach ( $this->refferals  as $referral ) :

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
                </td>
                <td><?php echo $user->first_name . " " . $user->last_name; ?></td>
                <td class="referral-percentage" data-th="<?php _e( 'Percentage', 'affiliate-wp' ); ?>">

					<?php echo $percentage; ?>

                </td>
                <td class="referral-date" data-th="<?php _e( 'Date', 'affiliate-wp' ); ?>"><?php echo esc_html( $referral->date_i18n( 'datetime' ) ); ?></td>
                <td><?php echo date('d F Y H:i',strtotime(get_post_meta($referral->reference,"_paid_date",true))); ?></td>
				<?php
				/**
				 * Fires within the table data of the dashboard referrals template.
				 *
				 * @param \AffWP\Referral $referral Referral object.
				 */
				do_action( 'affwp_referrals_dashboard_td', $referral ); ?>
                <td class="referral-amount" data-th="<?php _e( 'Commission', 'affiliate-wp' ); ?>"><?php echo affwp_currency_filter( affwp_format_amount( $referral->amount ) ); ?></td>

            </tr>
		<?php endforeach; ?>

	<?php else : ?>

        <tr>
            <td class="affwp-table-no-data" colspan="5"><?php _e( 'Geen commissies gevonden.', 'affiliate-wp' ); ?></td>
        </tr>

	<?php endif; ?>
    <tr><td><b><?php _e("Totaal","ascension-shop"); ?></b></td><td></td><td></td><td></td><td></td><td></td><td></td><td><?php echo affwp_currency_filter( affwp_format_amount($totals["total"])); ?></td></tr>
    </tbody>
</table>
</table>
</body>
</html>