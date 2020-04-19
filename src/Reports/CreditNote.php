<?php


namespace AscensionShop\Reports;

use AscensionShop\Lib\TemplateEngine;
use Dompdf\Options;
use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;
use WPO\WC\PDF_Invoices\Compatibility\WC_DateTime;

use AscensionShop\Affiliate\Helpers;
use Dompdf\Dompdf;

class CreditNote {

	/**
	 * Document type.
	 * @var String
	 */
	public $type = 'invoice';
	private $partner_id;
	private $date_from;
	private $date_to;
	private $template = '';
	private $settings = '';
	private $ref_status = array( "paid", "unpaid" ); // ,"rejected"

	/**
	 * Document slug.
	 * @var String
	 */
	public $slug;

	public function __construct( $from = null, $to = null, $partner_id = null ) {
		$this->partner_id = $partner_id;
		$this->date_from  = $from;
		$this->date_to = $to;

		$this->settings = $this->get_settings();

	}

	public function setRefsToPaid($status="paid"){

		$commissions = $this->getCommissions();

		foreach ($commissions as $c){
			affwp_set_referral_status($c->ID,$status);
		}

		$user_id = affwp_get_affiliate_user_id($this->partner_id);
		$key = md5("report_paid".$this->date_to.$this->date_from.$this->partner_id);
		// Set this to paid
		update_user_meta($user_id,$key,$status);
	}


	public function getPaidStatus(){

		$user_id = affwp_get_affiliate_user_id($this->partner_id);
		$key = md5("report_paid".$this->date_to.$this->date_from.$this->partner_id);
		$paid_status = get_user_meta($user_id,$key,true);

		if($paid_status == "paid"){
			$this->ref_status = array("paid","unpaid");
		}

	}

	public function generateCreditNote(){

		/*
		echo $this->getTemplate();
		die();
*/
		$options = new Options();
		$options->set('isRemoteEnabled', true);
		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($this->getTemplate());
		$dompdf->render();
		$dompdf->stream("credit-note.pdf");
		exit;
	}

	private function getTemplate(){
		$t = new TemplateEngine();
		$t->name = $this->get_shop_name();
		$t->logo = wp_get_attachment_url($this->settings["header_logo"]);
		$t->settings = $this->settings;
		$t->address = $this->get_settings_text("shop_address");
		$t->refferals = $this->getCommissions();
		$t->partner_id = $this->partner_id;
		$t->date_from = $this->date_from;
		$t->date_to = $this->date_to;

		return $t->display("reports/pdf/credit-note-affiliate.php");
	}

	private function getCommissions(){

		// Check if we need to get paid or unpaid items from this creditnote
		// $this->getPaidStatus();


		/** @var \AffWP\Referral[] $referrals */
		$referrals = affiliate_wp()->referrals->get_referrals(
			array(
				'number'       => -1,
				'affiliate_id' => $this->partner_id,
				'status'       => $this->ref_status,
				'orderby' => "custom",
				'order' => 'ASC'
			)
		);

		foreach ($referrals as $id => $ref) {
			$date_paid = get_post_meta( $ref->reference, "_paid_date", true );
			$order     = wc_get_order( $ref->reference );

			// Order does not exists anymore :)
			if ( is_bool( $order ) ) {
				unset( $referrals[ $id ] );
				continue;
			}

			// check rejected refs on refunds
			if ( $ref->status === "rejected" ) {
				// Check if order is refunded
				$refunddata = $order->get_refunds();
				if ( isset( $refunddata[0] ) ) {
					$date_paid                = $refunddata[0]->get_date_created()->format( 'd-m-Y' );
					$referrals[ $id ]->amount = - $ref->amount;
					$referrals[ $id ]->status = "refund";
					error_log( $date_paid );
				} else { // Unset ref is not refund
					unset( $referrals[ $id ] );
					continue;
				}
			}

			$date_paid  = strtotime( $date_paid );
			$end_date   = strtotime( $this->date_to . ' 23:59' );
			$start_date = strtotime( $this->date_from . ' 00:00' );

			if ( $date_paid <= $end_date && $date_paid >= $start_date ) {
				continue;
			} else {// Unset refrence
				unset( $referrals[ $id ] );
			}

		}

		return $referrals;
	}


	public function get_type() {
		return $this->type;
	}

	public function get_settings( $latest = false ) {
		// get most current settings
		$common_settings = WPO_WCPDF()->settings->get_common_document_settings();
		$document_settings = get_option( 'wpo_wcpdf_documents_settings_'.$this->get_type() );
		$settings = (array) $document_settings + (array) $common_settings;

		return $settings;
	}


	public function get_settings_text( $settings_key, $default = false, $autop = true ) {
		if ( !empty( $this->settings[$settings_key]['default'] ) ) {
			$text = wptexturize( trim( $this->settings[$settings_key]['default'] ) );
			if ($autop === true) {
				$text = wpautop( $text );
			}
		} else {
			$text = $default;
		}
		// legacy filters
		if ( in_array( $settings_key, array( 'shop_name', 'shop_address', 'footer', 'extra_1', 'extra_2', 'extra_3' ) ) ) {
			$text = apply_filters( "wpo_wcpdf_{$settings_key}", $text, $this );
		}
		return apply_filters( "wpo_wcpdf_{$settings_key}_settings_text", $text, $this );
	}

	public function get_shop_name() {
		$default = get_bloginfo( 'name' );
		return $this->get_settings_text( 'shop_name', $default, false );
	}

}