<h3><?php _e("National Manager Overzicht","ascension-shop"); ?></h3>
<div class="pc-tab" id="affwp-affiliate-dashboard">
    <ul class="ascension-sub-menu" id="affwp-affiliate-dashboard-tabs">
        <li class="tab1 affwp-affiliate-dashboard-tab"><a href="?page=orders"><label for="tab1"><?php _e("Bestellingen","ascension-shop") ?></a></label></li>
        <li class="tab2 affwp-affiliate-dashboard-tab"><a href="?page=clients"><label for="tab2"><?php _e("Klanten","ascension-shop") ?></label></a></li>
        <li class="tab3 affwp-affiliate-dashboard-tab"><a href="?page=partners"><label for="tab3"><?php _e("Partners","ascension-shop") ?></label></a></li>
        <li class="tab4 affwp-affiliate-dashboard-tab"><a href="?page=add-partner"><label for="tab4"><?php _e("Partner Toevoegen","ascension-shop") ?></label></a></li>
    </ul>
    <section>
        <h4><?php _e("Je bent nationale manager voor:","ascension-shop"); ?><?php echo ' '.$this->lang[0]; ?></h4>
        <?php echo $this->content; ?>
    </section>
</div>