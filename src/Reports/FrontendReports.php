<?php


namespace AscensionShop\Reports;


use AscensionShop\Affiliate\Helpers;
use AscensionShop\Affiliate\SubAffiliate;
use AscensionShop\NationalManager\NationalManager;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class FrontendReports {


	function __construct() {
		add_action("init",array($this,"generateCatcher"));
	}

	public function generateCatcher(){
		if(isset($_GET["generateReport"])){

			switch ($_GET["generateReport"]):
				case 'commissions';
					$this->generateCommissionOverview();
					die();
				break;
				case 'partners';
				$this->generatePartnerOverview();
				break;
				case 'clients';
				$this->generateClientOverview();
				break;

			endswitch;
		}
	}

	private function getAllPartners(  ) {

		$return_array = array();

		$partners = affiliate_wp()->affiliates->get_affiliates(
			array( 'number'  => -1,
			       'orderby' => 'name',
			       'order'   => 'ASC' ) );
		foreach ($partners as $p){
			$return_array[] = new SubAffiliate($p->affiliate_id);
		}

		return $return_array;
	}

	private function generatePartnerOverview(){

		$affiliate_id = affwp_get_affiliate_id();

		if(!NationalManager::isNationalManger(get_current_user_id())) {
			$sub      = new SubAffiliate( $affiliate_id );
			$partners = $sub->getAllChildren();
		}else{
			$partners = $this->getAllPartners();
		}

		$partners_amount = count($partners);

		$data = array();
		$data[0] = array("id" => __("ID", "woocommerce"), "name" => __("Voornaam", "woocommerce"), "email" => __("Email", "woocommerce"),"adress" => __("Adress", "woocommerce"), "postcode" => __("Postcode", "woocommerce") ,"phone"=> __("Telefoon"),"commission" => __("Commissie %"),"status"=>__("Status"));

		foreach ($partners as $partner){
			$new = array();
			$new["id"] = $partner->getId();
			$new["name"] = get_user_meta( $partner->getUserId(), 'first_name', true ). ' '.get_user_meta( $partner->getUserId(), 'last_name', true );;
			$new["email"] = $partner->getEmail();
			$new["adress"] = get_user_meta( $partner->getUserId(), 'billing_address_1', true );
			$new["postcode"] = get_user_meta( $partner->getUserId(), 'billing_postcode', true );
			$new["phone"] = get_user_meta( $partner->getUserId(), 'billing_phone', true );
			$new["commission"] = $partner->getUserRate();
			$new["status"] = $partner->getStatus(true);

			$data[] = $new;
		}


		$filename = "partners";

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		// set title
		$sheet->setCellValue('A1',"Partners");
		// Set counter
		$counter = 2;

		$totals = array();

		foreach ($data as $product){
			$sheet->setCellValue('A'.$counter, $product["id"]);
			$sheet->setCellValue('B'.$counter, $product["name"]);
			$sheet->setCellValue('C'.$counter,  $product["email"]);
			$sheet->setCellValue('D'.$counter,  $product["adress"]);
			$sheet->setCellValue('E'.$counter,  $product["postcode"]);
			$sheet->setCellValue('F'.$counter, $product["phone"]);
			$sheet->setCellValue('G'.$counter, $product["commission"]);
			$sheet->setCellValue('H'.$counter, $product["status"]);


			$counter++;
		}

		// Set header bold
		$sheet->getStyle("A2:I2")->getFont()->setBold( true );


// Redirect output to a client’s web browser (Xlsx)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
		header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');

		exit();
	}

	/**
	 * Generate a client overview
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	private function generateClientOverview(){

	    // Affiliate id
		$affiliate_id = affwp_get_affiliate_id();

		if($_GET["nm"] == true) {
            if (NationalManager::isNationalManger(get_current_user_id())) {

                // Only get the clients from given country
                $affiliate_id = NationalManager::getNationalManagerCountryAff(get_current_user_id());

            }
        }

        // get customers
        $customers = Helpers::getAllCustomersFromPartnerAndSubs($affiliate_id,false,false,false);

        $include = array();

        // Loop over customers
        if(count($customers)> 0) {
            // $include[] = get_current_user_id();
            foreach ( $customers as $c ) {
                $include[] = $c->user_id;
            }
        }else{
            $include[] = 0;
        }

        $users = new \WP_User_Query(
                array('include' => $include,
                    'number' => -1)
        );
        $users_result = $users->get_results();


        $data = array();
		$data[0] = array("id" => __("ID", "woocommerce"), "first_name" => __("Voornaam", "woocommerce"), "last_name" => __("Achternaam", "woocommerce"),"email" => __("Email", "woocommerce"),"adress" => __("Adress", "woocommerce"), "postcode" => __("Postcode", "woocommerce") ,"phone"=> __("Telefoon"),"discount" => __("Korting"));

		foreach ($users_result as $customer){

		    $customer = Helpers::getCustomerByUserId($customer->ID);
		    $customer = affwp_get_customer($customer);

			$new = array();
			$new["id"] = $customer->customer_id;
			$new["first_name"] = $customer->first_name;
			$new["last_name"] = $customer->last_name;
			$new["email"] = $customer->email;
			$new["adress"] = get_user_meta( $customer->user_id, 'billing_address_1', true );
			$new["postcode"] = get_user_meta( $customer->user_id, 'billing_postcode', true );
			$new["phone"] = get_user_meta( $customer->user_id, 'billing_phone', true );
			$new["discount"] = get_user_meta($customer->user_id,"ascension_shop_affiliate_coupon",true);

			$data[] = $new;
		}


		$filename = "customers";

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		// set title
		$sheet->setCellValue('A1',"Customers");
		// Set counter
		$counter = 2;

		$totals = array();

		foreach ($data as $product){
			$sheet->setCellValue('A'.$counter, $product["id"]);
			$sheet->setCellValue('B'.$counter, $product["first_name"]);
			$sheet->setCellValue('C'.$counter, $product["last_name"]);
			$sheet->setCellValue('D'.$counter,  $product["email"]);
			$sheet->setCellValue('E'.$counter,  $product["adress"]);
			$sheet->setCellValue('F'.$counter,  $product["postcode"]);
			$sheet->setCellValue('G'.$counter, $product["phone"]);
			$sheet->setCellValue('H'.$counter, $product["discount"]);

			if($counter > 2) {
				$totals["total"] += $product["total"];
				$totals["sub_total"] += $product["sub_total"];
				$totals["total_weight"] += $product["total_weight"];
			}

			$counter++;
		}

		// Set header bold
		$sheet->getStyle("A2:I2")->getFont()->setBold( true );


// Redirect output to a client’s web browser (Xlsx)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
		header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');

		exit();

	}


	private function generateCommissionOverview(){


		$affiliate_id = affwp_get_affiliate_id();
		$sub          = new SubAffiliate( $affiliate_id );

		// Totals
		$total_commision = 0;
		$total_ex_vat    = 0;
		$total_inc_vat   = 0;

		$customers = affiliate_wp_lifetime_commissions()->integrations->get_customers_for_affiliate( $affiliate_id );
		usort( $customers, function ( $first, $second ) {
			return strcasecmp( $first->first_name, $second->first_name );
		} );

		$month = date( "Y-m", time() );

		/** Get month & year */
		if ( ! isset( $_GET["from"] ) ) {

			if ( ! isset( $_GET["to"] ) ) {
				$_GET["to"] = date( 'Y-m-d', strtotime( 'last day of this month' ) );
			}

			$_GET["from"] = $month . '-01';
		}

		if ( ! isset( $_GET["status"] ) ) {
			$_GET["status"] = array();
		}




		$per_page = 500000000;
		/** @var \AffWP\Referral[] $referrals */
		$referrals = affiliate_wp()->referrals->get_referrals(
			array(
				'number'       => $per_page,
				'affiliate_id' => $affiliate_id,
				'customer_id'  => $_GET["client"],
				'status'       => "unpaid",
				'date'         => array( 'start' => $_GET["from"], 'end' => $_GET["to"] ),
				'description'  => $_GET["partner"],
				'search'       => true
			)
		);

		$data = array();
		$data[0] = array("order" => __("Bestelling", "woocommerce"), "user" => __("Gebruiker", "woocommerce"), "user_id" => __("Gebruikers ID", "woocommerce"),"commission" => __("Commissie", "woocommerce"),"percentage" => __("Percentage", "woocommerce"), "status" => __("Status", "woocommerce") ,"date"=> __("Datum"),"total_ex_vat" => __("Bedrag inclusief BTW"),"total_inc_vat"=> __("Bedrag exclusief BTW"));

		foreach($referrals as $ref){


			$order_id = $ref->reference;
			$order = new \WC_Order($order_id);
			$user = $order->get_user();

			$new["order"] = $ref->reference;
			$new["user"] = $user->first_name. ' '.$user->last_name;
			$new["user_id"] = $user->ID;
			$new["commission"] =  affwp_format_amount($ref->amount);
			$new["percentage"] = $this->getPercentage($sub,$order,$ref);
			$new["status"] =  affwp_get_referral_status_label( $ref );
			$new["data"] = esc_html( $ref->date_i18n( 'datetime' ) );
			$new["total_ex_vat"] =  round($order->get_total() - $order->get_total_tax(), 2); ;
			$new["total_inc_vat"] =  round($order->get_total(), 2); ;

			$data[] = $new;
		}


		$filename = "commission-".$_GET['from']."-".$_GET['to']."-".$_GET['client']."";

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		// set title
		$sheet->setCellValue('A1',"Commission from ".$_GET['from']." till ".$_GET['to']." - ".$_GET['client']."");
		// Set counter
		$counter = 2;

		$totals = array();

		foreach ($data as $product){
			$sheet->setCellValue('A'.$counter, $product["order"]);
			$sheet->setCellValue('B'.$counter, $product["user"]);
			$sheet->setCellValue('C'.$counter, $product["user_id"]);
			$sheet->setCellValue('D'.$counter,  $product["commission"]);
			$sheet->setCellValue('E'.$counter,  $product["percentage"]);
			$sheet->setCellValue('F'.$counter,  $product["status"]);
			$sheet->setCellValue('G'.$counter, $product["date"]);
			$sheet->setCellValue('H'.$counter, $product["total_ex_vat"]);
			$sheet->setCellValue('I'.$counter, $product["total_inc_vat"]);

			if($counter > 2) {
				$totals["total"] += $product["total"];
				$totals["sub_total"] += $product["sub_total"];
				$totals["total_weight"] += $product["total_weight"];
			}

			$counter++;
		}

		// Set header bold
		$sheet->getStyle("A2:I2")->getFont()->setBold( true );

/*
		$styleArray = array(
			'borders' => array(
				'outline' => array(
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
					'color' => array('argb' => '00000000'),
				),
			),
		);

		$counter++;

		// Add a footer
		/*$sheet->setCellValue('D'.$counter, $totals["sub_total"]);
		$sheet->setCellValue('E'.$counter, $totals["total"]);
		$sheet->setCellValue('G'.$counter, $totals["total_weight"]);
		$sheet->getStyle('D'.$counter)->applyFromArray($styleArray);
		$sheet->getStyle('E'.$counter)->applyFromArray($styleArray);
		$sheet->getStyle('G'.$counter)->applyFromArray($styleArray);
		*/

// Redirect output to a client’s web browser (Xlsx)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
		header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		$writer->save('php://output');

		exit();
	}

	private function outputCsv($fileName, $assocDataArray)
	{
		ob_clean();
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment;filename=' . $fileName);
		if(isset($assocDataArray['0'])){
			$fp = fopen('php://output', 'w');
			fputcsv($fp, array_keys($assocDataArray['0']));
			foreach($assocDataArray AS $values){
				fputcsv($fp, $values);
			}
			fclose($fp);
		}
		ob_flush();
	}

	private function getPercentage($sub,$order,$referral){
		/**
		 * Get the rates
		 */
		$user_rate =  $sub->getUserRate();
		$min = '';
		$return_rate = $user_rate;
		$total = $order->get_subtotal();
		$amount = round( $referral->amount,2);
		$amount_check = round($user_rate*($total/100),2);

		/**
		 * Check if there is a new rate
		 */
		if($amount_check != $amount){
			$min = $amount_check - $amount;
			$min_percentage = round(100*($min/$total),2);
			if($min_percentage > $user_rate){
				$min_percentage = $user_rate;
			}
			$min = ' - '.$min_percentage.'%';
			$new_rate = $user_rate-$min_percentage;
			$min .= ' = '.$new_rate.'%';
		}

		return $user_rate.'%'.$min;
	}
}