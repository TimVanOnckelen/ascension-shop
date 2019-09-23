<div class="ascension-order-details">
	<?php _e("Producten","ascension-shop"); ?>
	<table>
		<thead>
		<th><?php _e("Product","ascension-shop"); ?></th>
		<th><?php _e("Aantal","ascension-shop"); ?></th>
		<th><?php _e("Totaal ex BTW","ascension-shop"); ?></th>
		<th><?php _e("BTW","ascension-shop"); ?></th>
		<th><?php _e("Totaal","ascension-shop"); ?></th>
		</thead>
		<tbody>

		<?php
		foreach($this->order->get_items() as $item){
			?>
			<tr>
				<td><?php echo $item->get_name(); ?></td>
				<td><?php echo $item->get_quantity(); ?></td>
				<td>&euro;<?php echo round($item->get_total(),2); ?></td>
				<td>&euro;<?php echo $item->get_total_tax(); ?></td>
				<td>&euro;<?php echo round($item->get_total_tax()+$item->get_total(),2); ?></td>

			</tr>
			<?php
		}?>

		</tbody>
	</table>
	<table>
		<tbody>
		<tr>
			<td><b><?php _e("Sub totaal","ascension-shop"); ?></b></td>
			<td>&euro; <?php echo round($this->order->get_subtotal(),2); ?></td>
		</tr>
		<tr>
			<td><b><?php _e("Belastingen","ascension-shop"); ?></b></td>
			<td>&euro; <?php echo round($this->order->get_total_tax(),2); ?></td>
		</tr>

			<?php
			$fees = $this->order->get_fees();
			if(sizeof($fees) > 0){
				foreach ($fees as $item_fee){
					?>
					<tr>
						<td><b><?php echo $item_fee->get_name(); ?></b></td>
						<td>&euro; <?php echo round($item_fee->get_total(),2); ?></td>
					</tr>
			<?php
				}
			}
			?>
		<tr>
			<td><b><?php _e("Totaal","ascension-shop"); ?></b></td>
			<td>&euro; <?php echo round($this->order->get_total(),2); ?></td>
		</tr>
		</tbody>
	</table>
</div>
