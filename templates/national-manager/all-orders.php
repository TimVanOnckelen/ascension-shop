<div>
	<?php

	use AscensionShop\NationalManager\Frontend;
	use AscensionShop\NationalManager\NationalManager;

	$langs = '';
	foreach ($this->lang as $l){
		$langs .= $l. ' ';
	}
	?>
    <p><?php printf(__("Alle orders voor de %s shop","ascenion-shop"),$langs); ?><br />
		<?php _e("Je kan alle orders filteren op naam, id, status of bedrag.","ascenion-shop"); ?></p>
	<?php

	?>
    <div class="partnerArea-header">
        <div class="header">
            <label><?php _e("Klant","ascension-shop") ?></label>
            <select id="searchOrderByClient">
                <option value=""><?php _e("Alle klanten","ascension-shop") ?></option>
            </select>
            <label>
				<?php _e("Datum","ascension-shop"); ?>
            </label>
            <input type="date" id="orders-from" placeholder="<?php _e("Van","ascension-shop"); ?>"><br /><input type="date" id="orders-to" placeholder="<?php _e("Tot","ascension-shop"); ?>">
        </div>
    </div>

    <table id="all-orders" class="affwp-table affwp-table-responsive">
        <thead>
        <tr>
            <th><?php _e("ID","ascension-shop") ?></th>
            <th><?php _e("Datum","ascension-shop") ?></th>
            <th><?php _e("Klant","ascension-shop") ?></th>
            <th><?php _e("Partner","ascension-shop") ?></th>
            <th><?php _e("Status","ascension-shop") ?></th>
            <th><?php _e("Bedrag","ascension-shop") ?></th>
            <th><?php _e("Acties","ascension-shop") ?></th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>