<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 16/07/2019
 * Time: 19:27
 */

namespace AscensionShop\Shipping;


use AscensionShop\Lib\TemplateEngine;
use AscensionShop\Shipping\emails\TrackingCode_Email;

class WC_Custom_Email
{

    /**
     * Custom_WC_Email constructor.
     */
    public function __construct()
    {
        // Filtering the emails and adding our own email.
        add_action('woocommerce_email_classes', array($this, 'register_email'), 10, 1);
        // Add shortcodes to email builder
        add_filter('woo_email_drag_and_drop_builder_load_additional_shortcode',array($this,"addExtraShortcodes"));
		add_filter('woo_email_drag_and_drop_builder_load_additional_shortcode_data',array($this,"addExtraShortcodeContent"),10,3);
		add_filter('woo_email_drag_and_drop_builder_load_additional_shortcode_data',array($this,"tableWithoutPrices"),10,3);
    }

    /**
     * @param array $emails
     *
     * @return array
     */
    public function register_email($emails)
    {

/*
    	foreach ($emails as $key => $v){
    		echo "<br />".$key;
	    }
*/
    	unset($emails['WC_Email_Customer_Completed_Order']);
    	unset($emails['WC_Email_Cancelled_Order']);
	    unset($emails['WC_Email_Failed_Order']);
	    unset($emails['WC_Email_Customer_Note']);
	    unset($emails['WC_Email_Customer_Refunded_Order']);
	    unset($emails['WC_Email_Customer_Credit_Note']);


        $emails['WC_trackingcode'] = new TrackingCode_Email();
        return $emails;
    }

    public function addExtraShortcodes($shortcodes){

    	$shortcodes["as_trackingcode"] = 'The tracking code';
	    $shortcodes["as_trackingcode_link"] = 'Tracking code with link';
	    $shortcodes["as_orders_table_no_prices"] = 'Table with contents of orders without prices';

	    return $shortcodes;
    }

    public function addExtraShortcodeContent($content,$order,$mail){

    	$tracking_code = $order->get_meta('as_trackingcode');

    	if(isset($tracking_code) && $tracking_code != '') {
		    $content["as_trackingcode"] = $order->get_meta( 'as_trackingcode' );
		    $content["as_trackingcode_link"] = '<h3>'.__("Trackingcode","ascension-shop").'</h3>';
		    $content["as_trackingcode_link"] .= '<a href="https://ips.cypruspost.gov.cy/ipswebtrack/IPSWeb_item_events.aspx?itemid=' . $order->get_meta( 'as_trackingcode' ) . '">' . $order->get_meta( 'as_trackingcode' ) . '</a>';
		    $content["as_trackingcode_link"] .= '<br />' . sprintf( __( 'Werkt bovenstaande link niet? Geef je code dan in via %s', 'ascension-shop' ), "https://www.track-trace.com/post" );
	    }else{
    		$content["as_trackingcode"] = '';
    		$content["as_trackingcode_link"] = '';
	    }

	    return $content;
    }

    public function tableWithoutPrices($content,$order,$mail){
		$t = new TemplateEngine();
		$t->order = $order;
		$table = $t->display("emails/wc_trackingcode_part_table.php");

		$content["as_orders_table_no_prices"] = $table;

		return $content;
    }


}