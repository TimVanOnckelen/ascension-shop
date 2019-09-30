<div class="wrap">
    <h1>Reports Export</h1>
    <h2><?php echo get_admin_page_title(); ?></h2>
    <p>Get a sales export for the selected country.<br />
    You'll get an overview of sold products, there price & weight.</p>
    <form action="admin-post.php" method="post">
        <p>
            <label for="start-date"><?=__("Start datum", "woocommerce")?></label>
            <input class="short" type="date" name="start-date" required>
        </p>
        <p>
            <label for="end-date"><?=__("Eind datum", "woocommerce")?></label>
            <input class="short" type="date" name="end-date" required>
        </p>
        <p>
            <label for="languages"><?=__("Selecteer uw taal")?></label>
            <select class="short" name="languages" required>
				<?php $i = 1; foreach($this->lang as $item){ ?>
                    <option value="<?php echo $item["code"]; ?>"><?=$item['translated_name']?> / <?=$item['native_name']?></option>
					<?php $i++; } ?>
            </select>
        </p>
        <input type="hidden" value="/" name="delimiter">
        <input type="hidden" name="action" value="export_sales">
        <input type="submit" class="button button-primary button-large" value="<?=__('Export')?>">
    </form>

    <h2>Order reports</h2>
    <p>Export an xls of all orders in the current periode for a specific country.
        <br />You will get main order data as customer, amount, totals, ...</p>
    <form action="admin-post.php" method="post">
        <p>
            <label for="start-date"><?=__("Start datum", "woocommerce")?></label>
            <input class="short" type="date" name="start-date" required>
        </p>
        <p>
            <label for="end-date"><?=__("Eind datum", "woocommerce")?></label>
            <input class="short" type="date" name="end-date" required>
        </p>
        <p>
            <label for="languages"><?=__("Selecteer uw taal")?></label>
            <select class="short" name="languages" required>
				<?php $i = 1; foreach($this->lang as $item){ ?>
                    <option value="<?php echo $item["code"]; ?>"><?=$item['translated_name']?> / <?=$item['native_name']?></option>
					<?php $i++; } ?>
            </select>
        </p>
        <input type="hidden" value="/" name="delimiter">
        <input type="hidden" name="action" value="export_orders">
        <input type="submit" class="button button-primary button-large" value="<?=__('Export')?>">
    </form>
</div>