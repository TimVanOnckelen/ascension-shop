<h3><?php _e("Bestellingen van klanten","ascension-shop"); ?></h3>
<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">


    <tbody>

    <?php foreach ($this->orders as $o) {?>
    <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-completed order">
        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="Bestelling">
           # <?php echo $o->get_id(); ?>

        </td>
        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date" data-title="Datum">
            <?php echo $o->get_date_created()->date("F j, Y, g:i:s A T"); ?>

        </td>
        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" data-title="Status">
            <?php echo $o->get_status(); ?>
        </td>
        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total" data-title="Totaal">
            <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">€</span> <?php echo $o->get_total(); ?></span>
        </td>
        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions" data-title="Acties">
            <?php $user = $o->get_user(); echo '#'.$user->ID . ' ' .$user->first_name. " ".$user->last_name; ?></span>
        </td>
        <td class="ascension-order-info">
            <a href="#" class="ascension-order-details-hover"><?php _e("Bekijk order info","ascension-shop"); ?></a>

            <?php
            $t = new \AscensionShop\Lib\TemplateEngine();
            $t->order = $o;
            echo $t->display("affiliate-wp/dashboard-order-info.php");
            ?>
        </td>
    </tr>
    <?php } ?>

    </tbody>
</table>