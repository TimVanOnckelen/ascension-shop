<div class="wrap">
    <h1>Affiliates Reports Export</h1>
    <h2>Affiliate Payout Report</h2>
    <p>Get a report of all amounts per affiliate based on start & end date.<br />
    Report will only get valid Referrals that has a unpaid status.</p>
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