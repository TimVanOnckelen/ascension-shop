<div class="tab1">
    <p><?php printf(__("Alle orders voor de %s shop","ascenion-shop"),$this->lang[0]); ?><br />
	    <?php _e("Je kan alle orders filteren op naam, id, status of bedrag.","ascenion-shop"); ?></p>

    <table id="all-orders" class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">

        <thead>
        <tr>
            <th><?php _e("ID","ascension-shop") ?></th>
            <th><?php _e("Datum","ascension-shop") ?></th>
            <th><?php _e("Status","ascension-shop") ?></th>
            <th><?php _e("Bedrag","ascension-shop") ?></th>
            <th><?php _e("Klant","ascension-shop") ?></th>
            <th><?php _e("Acties","ascension-shop") ?></th>

        </tr>
        </thead>
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
        <td>
            <?php
	            $pdf_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&document_type=invoice&order_ids=' . $o->get_id() . '&my-account' ), 'generate_wpo_wcpdf' );
	            echo "<a href='".$pdf_url."'><button>".__("Download factuur","ascension-shop")."</button></a>";

            ?>
            <a href="<?php echo $o->get_view_order_url();?>"><button><?php _e("Bekijk","ascension-shop"); ?></button></a>
        </td>
    </tr>
    <?php } ?>

    </tbody>
</table>
</div>
<script>
    (function($){
        $(document).ready( function () {
            $('#all-orders ').DataTable();
        } );

    })(jQuery);
</script>