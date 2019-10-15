<h3><?php _e("National Manager Overzicht","ascension-shop"); ?></h3>
<div class="pc-tab">
    <input checked="checked" id="tab1" type="radio" name="pct" />
    <input id="tab2" type="radio" name="pct" />
    <input id="tab3" type="radio" name="pct" />
    <ul class="ascension-sub-menu">
        <li class="tab1"><label for="tab1"><?php _e("Bestellingen","ascension-shop") ?></label></li>
        <li class="tab2"><label for="tab2"><?php _e("Klanten","ascension-shop") ?></label></li>
        <li class="tab3"><label for="tab3"><?php _e("Partners","ascension-shop") ?></label></li>
    </ul>
    <section>
        <h4><?php _e("Je bent nationale manager voor:","ascension-shop"); ?><?php echo ' '.$this->lang[0]; ?></h4>
        <?php echo $this->content; ?>
    </section>
</div>