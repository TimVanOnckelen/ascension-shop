<?php

use AscensionShop\Affiliate\Helpers;
use AscensionShop\Affiliate\SubAffiliate;


$month = date("Y-m",time());


/** Get month & year */
if(!isset($_GET["start-date"])){

	if(!isset($_GET["end-date"])){
		$_GET["end-date"] = date('Y-m-d',strtotime('last day of this month'));
	}

	$_GET["start-date"] = $month.'-01';
}

?>
<div class="wrap">
    <h1>Partner Reports Export</h1>

    <h2>Partner overview commissions</h2>
    <p>Partners with no referrals in the selected time span are not displayed.</p>
    <form method="get">
        <label for="start-date"><?=__("Start datum", "woocommerce")?></label>
        <input class="short" type="date" name="start-date"  value="<?php echo $_GET["start-date"]; ?>">
        <label for="end-date"><?=__("Eind datum", "woocommerce")?></label>
        <input class="short" type="date" name="end-date"  value="<?php echo $_GET["end-date"]; ?>">
        <input type="hidden" name="page" value="partner-export-page" />
        <input type="submit" class="button button-primary button-large" value="<?=__('Filter')?>">
    </form>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th><?php _e("ID","ascension-shop"); ?></th>
            <th><?php _e("Naam","ascension-shop"); ?></th>
            <th><?php _e("Bedrag","ascension-shop"); ?></th>
            <th><?php _e("Aantal referrals","ascension-shop"); ?></th>
            <th><?php _e("Status","ascension-shop"); ?></th>
            <th><?php _e("Acties","ascension-shop"); ?></th>

        </tr>
        </thead>
    <?php
    $referrals = affiliate_wp()->referrals->get_referrals(
	    array(
		    'number'       => -1,
		    'status'       => array("unpaid","paid"),
	    )
    );

    $ref_totals = Helpers::countPerRef($referrals,$_GET["end-date"],$_GET["start-date"]);

    foreach ($ref_totals as $ref){
        ?>
        <tr><td><?php echo $ref["affiliate_id"];?></td>
            <td><?php echo $ref["name"];?></td>
            <td><?php echo affwp_currency_filter( affwp_format_amount( $ref["amount"]));?></td>
            <td><?php echo $ref["refs"]; ?></td>
            <td><?php echo $ref["status"];?></td>
            <td>
                <form action="admin-post.php" method="post">
                    <p>
                        <label for="languages"><?=__("Selecteer uw taal","ascension-shop")?></label>
                        <select class="short" name="lang" required>
			                <?php $i = 1; foreach($this->lang as $item){ ?>
                                <option value="<?php echo $item["code"]; ?>"><?=$item['translated_name']?> / <?=$item['native_name']?></option>
				                <?php $i++; } ?>
                        </select>
                    </p>
                    <input class="hidden" type="text" name="start-date" value="<?php echo $_GET["start-date"]; ?>">
                    <input class="hidden" type="text" name="end-date"  value="<?php echo $_GET["end-date"]; ?>">
                    <input type="hidden" value="<?php echo $ref["affiliate_id"]; ?>" name="affiliate">
                    <input type="hidden" value="/" name="delimiter">
                    <input type="hidden" name="action" value="export_credit_note_affiliates">
                    <input type="submit" class="button button-primary button-large" value="<?=__('Export as pdf')?>">
                </form>

                <p>
                <form action="admin-post.php" method="post">
                <input class="hidden" type="text" name="start-date" value="<?php echo $_GET["start-date"]; ?>">
                <input class="hidden" type="text" name="end-date"  value="<?php echo $_GET["end-date"]; ?>">
                    <input type="hidden" name="action" value="pay_credit_note">
                    <input type="hidden" value="<?php echo $ref["affiliate_id"]; ?>" name="affiliate">
                    <?php wp_referer_field(); ?>
                    <?php if($ref["status"] == "unpaid"){ ?>
                        <input type="hidden" name="status" value="paid" />
                        <input type="submit" class="button button-primary button-large" value="<?=__('Mark as paid')?>">
                    <?php }elseif($ref["status"] == "paid") {?>
                        <input type="hidden" name="status" value="unpaid" />
                        <input type="submit" class="button button-primary button-large" value="<?=__('Mark as unpaid')?>">
                    <?php }else{
                       ?>
                        <input type="hidden" name="status" value="paid" />
                        <input type="submit" class="button button-primary button-large" value="<?=__('Mark all as paid')?>">
                        <input type="hidden" name="status" value="unpaid" />
                        <input type="submit" class="button button-primary button-large" value="<?=__('Mark all as unpaid')?>">
                    <?php
                    }?>
                </form>
                </p>
            </td>
        </tr>
    <?php
    }
    ?>
    </table>

    <h2>Partner Payout Report</h2>
    <p>Get a report of all amounts per Partner based on start & end date.<br />
        Report will only get valid Referrals that have a unpaid status.</p>
    <p>Partners with no referrals in the selected time span are not displayed.</p>
    <form action="admin-post.php" method="post">
        <p>
            <label for="start-date"><?=__("Start datum", "woocommerce")?></label>
            <input class="short" type="date" name="start-date" required>
        </p>
        <p>
            <label for="end-date"><?=__("Eind datum", "woocommerce")?></label>
            <input class="short" type="date" name="end-date" required>
        </p>
        <input type="hidden" value="/" name="delimiter">
        <input type="hidden" name="action" value="export_affiliates">
        <input type="submit" class="button button-primary button-large" value="<?=__('Export')?>">
    </form>

</div>