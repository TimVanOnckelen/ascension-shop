<table style="border:1px solid #e8e8e8;width: 100%;text-align: left;">
	<thead>
	<tr><th><?php _e("Product","ascension-shop"); ?></th><th><?php _e("Aantal","ascension-shop"); ?></th></tr>
	</thead>
	<?php
	$order = $this->order;
	$items = $order->get_items();

	foreach ( $order->get_items() as $item ) {
		?>
		<tr><td><?php echo $item["name"]; ?></td><td><?php echo $item["qty"]; ?></td></tr>
		<?php
	}
	?>
</table>
