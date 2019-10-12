<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 28/08/2019
 * Time: 11:24
 */

namespace AscensionShop\Affiliate;


use AscensionShop\Lib\TemplateEngine;

class FrontendDashboard
{

    public function __construct()
    {

        // Add a th to the referrals table in the affiliate area.
        add_action('affwp_referrals_dashboard_th', array($this, 'totalAmount'));

        // Add a td to the referrals table in the affiliate area.
        add_action('affwp_referrals_dashboard_td', array($this, 'totalAmount_td'));

	    add_filter('affwp_affiliate_area_tabs', array($this, "addExtraTabs"));

	    add_filter('affwp_template_paths', array($this, "addCustomTemplateFolder"));

    }

    /**
     * Th for the lifetime referral column.
     *
     * @since 1.3
     */
    public function totalAmount()
    {
        ?>
        <th class="order-total-inc-btw"><?php _e('Totaal inc btw', 'ascension-shop'); ?></th>
        <th class="order-total-ex-btw"><?php _e('Totaal ex btw', 'ascension-shop'); ?></th>

        <?php
    }




    public function totalAmount_td($ref)
    {

        $order_id = $ref->reference;
        $order = new \WC_Order($order_id);
        $user = $order->get_user();
        $fee_total = 0;
        $fee_total_tax = 0;

	    // Iterating through order fee items ONLY
	    foreach( $order->get_items('fee') as $item_id => $item_fee ){

		    // The fee total amount
		    $fee_total += $item_fee->get_total();

		    // The fee total tax amount
		    $fee_total_tax += $item_fee->get_total_tax();
	    }


        ?>
        <td><?php echo affwp_currency_filter( affwp_format_amount( $order->get_total() - $fee_total - $fee_total_tax)); ?></td>
        <td><?php echo affwp_currency_filter( affwp_format_amount($order->get_total() - $order->get_total_tax() - $fee_total )); ?></td>
        <?php
    }


	/**
	 * @param $tabs
	 * @return mixed
	 */
	public function addExtraTabs($tabs)
	{
		wp_enqueue_style("ascension-info-css", XE_ASCENSION_SHOP_PLUGIN_DIR . "/assets/css/refferal-order-info.min.css",null,"1.0.1.7");
		wp_enqueue_script("printThis",XE_ASCENSION_SHOP_PLUGIN_DIR . "/assets/js/printThis-master/printThis.min.js");
		wp_enqueue_script("partnerAreaFunctions",XE_ASCENSION_SHOP_PLUGIN_DIR . "/assets/js/partnerAreaFunctions.min.js",array("jquery"),'1.0.14');


		unset($tabs["referrals"]);
	    unset($tabs["lifetime-customers"]);
        unset($tabs["stats"]);
		unset($tabs["payouts"]);
		unset($tabs["creatives"]);
		unset($tabs["settings"]);
		unset($tabs["waterfall"]);
		unset($tabs["graphs"]);

		$tabs["commission-overview"] = __("Commissies", "ascension-shop");
		$tabs["clients-overview"] = __("Klanten", "ascension-shop");
		$tabs["add-client"] = __("Nieuwe klant aanmaken", "ascension-shop");
		$tabs["partners"] = __("Partners", "ascension-shop");


		return $tabs;
	}
	/**
	 * @param $paths
	 * @return array
	 */
	public function addCustomTemplateFolder($paths)
	{
		$paths[] = XE_ASCENSION_SHOP_PLUGIN_TEMPLATE_PATH . 'affiliate-wp/';
		return $paths;
	}


}