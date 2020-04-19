<h3><?php

	_e( "National Manager Overzicht", "ascension-shop" ); ?></h3>
<div class="pc-tab" id="affwp-affiliate-dashboard">
    <ul class="ascension-sub-menu" id="affwp-affiliate-dashboard-tabs">
        <li class="tab2 affwp-affiliate-dashboard-tab <?php if ( $_GET["page"] == "clients" or ! isset( $_GET["page"] ) ) {
			echo "active";
		} ?>"><a href="?page=clients"><label for="tab2"><?php _e( "Klanten", "ascension-shop" ) ?> (NM)</label></a></li>
        <li class="tab2 affwp-affiliate-dashboard-tab <?php if ( $_GET["page"] == "add-client" ) {
			echo "active";
		} ?>"><a href="?page=add-client"><label for="tab2"><?php _e( "Klant toevoegen", "ascension-shop" ) ?>
                    (NM)</label></a></li>
        <li class="tab4 affwp-affiliate-dashboard-tab <?php if ( $_GET["page"] == "commissions" ) {
			echo "active";
		} ?>"><a href="?page=commissions"><label for="tab4"><?php _e( "Commissies", "ascension-shop" ) ?>
                    (NM)</label></a></li>
        <li class="tab1 affwp-affiliate-dashboard-tab <?php if ( $_GET["page"] == "orders" ) {
			echo "active";
		} ?>"><a href="?page=orders"><label for="tab1"><?php _e( "Bestellingen", "ascension-shop" ) ?> (NM)</a></label>
        </li>
        <li class="tab3 affwp-affiliate-dashboard-tab <?php if ( $_GET["page"] == "partners" ) {
			echo "active";
		} ?>"><a href="?page=partners"><label for="tab3"><?php _e( "Partners", "ascension-shop" ) ?> (NM)</label></a>
        </li>
        <li class="tab4 affwp-affiliate-dashboard-tab <?php if ( $_GET["page"] == "add-partner" ) {
			echo "active";
		} ?>"><a href="?page=add-partner"><label for="tab4"><?php _e( "Partner Toevoegen", "ascension-shop" ) ?>
                    (NM)</label></a></li>

		<?php

		$tabs = affwp_get_affiliate_area_tabs();

		if ( $tabs ) {
			foreach ( $tabs as $tab_slug => $tab_title ) : ?>
				<?php if ( affwp_affiliate_area_show_tab( $tab_slug ) ) : ?>
                    <li class="affwp-affiliate-dashboard-tab<?php echo $active_tab == $tab_slug ? ' active' : ''; ?>">
                        <a href="<?php echo esc_url( affwp_get_affiliate_area_page_url( $tab_slug ) ); ?>"><?php echo $tab_title; ?></a>
                    </li>
				<?php endif; ?>
			<?php endforeach;
		}

		/**
		 * Fires immediately after core Affiliate Area tabs are output,
		 * but before the 'Log Out' tab is output (if enabled).
		 *
		 * @param int $affiliate_id ID of the current affiliate.
		 * @param string $active_tab Slug of the active tab.
		 *
		 * @since 1.0
		 *
		 */
		do_action( 'affwp_affiliate_dashboard_tabs', affwp_get_affiliate_id(), $active_tab );
		?>

		<?php if ( affiliate_wp()->settings->get( 'logout_link' ) ) : ?>
            <li class="affwp-affiliate-dashboard-tab">
                <a href="<?php echo esc_url( affwp_get_logout_url() ); ?>"><?php _e( 'Log out', 'affiliate-wp' ); ?></a>
            </li>
		<?php endif; ?>

    </ul>
    <section>
        <h4><?php _e( "Je bent nationale manager voor:", "ascension-shop" ); ?><?php echo ' ' . $this->lang[0]; ?></h4>
		<?php echo $this->content; ?>
    </section>
</div>