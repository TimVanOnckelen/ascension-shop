<h2><?php _e("Level rates","ascension-shop"); ?></h2>
<p>
<h3><?php _e("Levels management","ascension-shop"); ?></h3>
<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <label for="level">
		<?php _e("Level","ascension-shop"); ?>
	</label>
	<input type="number" name="level" placeholder="A number" />
	<label for="rate">
		<?php _e("Rate","ascension-shop"); ?>
	</label>
	<input type="number" name="rate" placeholder="%" />
    <input type="hidden" name="action" value="xe_add_level" />
    <?php wp_referer_field(); ?>
    <input type="submit" value="<?php _e("Opslaan","ascension-shop"); ?>" />
</form>
</p>
<h3><?php _e("Overview","ascension-shop"); ?></h3>
<p><?php _e("Edit levels to change there values. Set rate to 0 to disable level.","ascension-shop"); ?></p>
<table class="widefat fixed striped posts" cellspacing="0" >
	<thead>
	<th><?php _e("Level","ascension-shop"); ?></th>
	<th><?php _e("Rate","ascension-shop"); ?></th>
	</thead>
	<tbody>
    <?php

    foreach($this->levels as $l => $data){
      ?>
        <tr>
            <td><?php echo $l; ?></td>
            <td><?php echo $data["rate"]; ?>%</td>
        </tr>
    <?php
    }

    ?>


	</tbody>
</table>

