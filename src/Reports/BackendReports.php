<?php


namespace AscensionShop\Reports;


use AscensionShop\Lib\TemplateEngine;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BackendReports {

	private $transient_name = '';

	public function __construct(){
		add_action( 'admin_menu', array($this, 'web_settings_init') );
		add_action( 'admin_post_export_sales', array($this, 'export_sales') );
		add_action( 'admin_post_export_orders', array($this, 'export_orders') );
	}

	public function web_settings_init() {

		add_menu_page(
			__('Rapporten','ascension-shop'),
			__('Rapporten','ascension-shop'),
			'manage_options',
			'ascension_reports',
		array($this,"ascension_reports"),
		'
dashicons-format-status');

		add_submenu_page(
			'ascension_reports',
			__( 'Sales reports'),
			__( 'Sales reports'),
			'manage_options',
			'sales-export-page',
			array($this, 'web_settings_page')
		);
	}

	public function ascension_reports(){
		echo "Please use the sub menu to view reports.";
	}

	public function web_settings_page(){
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$lang = icl_get_languages();

		//pre($lang);

		// include template
		$t = new TemplateEngine();
		$t->lang = $lang;
		echo $t->display("reports/form-export-orders.php");

	}

	public function export_sales(){

		$language_data = explode('|', $_POST['languages']);
		$lang_code = $language_data[0];
		$lang_code_long = $language_data[1];
		$lang_code_id = $language_data[2];

		// get all orders with filters
		$customer_orders = get_posts( array(
			'numberposts' => - 1,
			'post_type'   => array( 'shop_order' ),
			'post_status' => array('wc-completed','wc-processing'), //old : array( 'wc-completed' )
			'date_query'  => array(
				'after'     => date('Y-m-d', strtotime('-1 day', strtotime($_POST['start-date']))),
				'before'    => date('Y-m-d', strtotime('+1 day', strtotime($_POST['end-date'])))
			),
			'orderby'     => 'date',
			'order'       => 'ASC',
			'meta_key'    => 'wpml_language',
			'meta_value'  => $lang_code
		) );

		$products = array();

		//get products through all orders
		foreach($customer_orders as $order){
			$order = wc_get_order($order->ID);
			foreach( $order->get_items() as $item_id => $item ){
				//Get the product ID
				$product_id = $item->get_variation_id();
				if($product_id == 0){
					$product_id = $item->get_product_id();
				}

				$language_product_id = apply_filters( 'wpml_object_id', $product_id, 'product', false, $lang_code );

				//Get the WC_Product object
				$product = wc_get_product( (($language_product_id != null)?$language_product_id:$product_id) );

				// The quantity
				$product_quantity = $item->get_quantity();

				// The product name
				$product_name = $product->get_name(); // … OR: $product->get_name();

				// The product weight
				$product_weight = $product->get_weight();
				if(strlen($product_weight) == 0){
					$product_weight = 0;
				}

				$sub_total = $item->get_subtotal();

				$total = $item->get_total();

				if(!array_key_exists($product_id,$products)){
					$products[$product_id] = array("quantity" => $product_quantity, "product_price" => $total, "weight" => $product_weight, "product_name" => $product_name, "sub_total" => $sub_total, "total" => $total,"total_weight" => $product_weight);
				}else{
					$products[$product_id]["quantity"] += $product_quantity;
					$products[$product_id]["sub_total"] += $sub_total;
					$products[$product_id]["total"] += $total;
					$products[$product_id]["total_weight"] += $product->get_weight();
				}
			}
		}

		//die();

		// add total weight to products
		foreach($products as $product_id => $product_data){
			$products[$product_id]['total_weight'] = $products[$product_id]['quantity'] * $products[$product_id]['weight'];
		}

		//set header
		$products[0] = array("quantity" => __("Quantity", "woocommerce"), "weight" => __("Weight", "woocommerce"), "product_name" => __("Product name", "woocommerce"),"sub_total" => __("Subtotal", "woocommerce"),"total" => __("Total", "woocommerce"), "total_weight" => __("Total", "woocommerce") .' '. __("Weight", "woocommerce"),"product_price"=> __("Product price"));
		ksort ($products);
		//$products[999999] = array("quantity" => 'old:'. $old_lang, "weight" => 'current:'. $my_current_lang, "product name" => $lang_code, "sub_total" => $lang_code_long, "total" => $lang_code_id, "total_weight" => '');


		//make file
		$export = __('Export');
		$filename = "$export-".$_POST['start-date'].'_'.$_POST['end-date'];

		/*
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$filename}.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		$this->outputCSV($products);*/

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		// set title
		$sheet->setCellValue('A1',"SALES ".$lang_code." ".$_POST["start-date"]. "-".$_POST["end-date"]);
		// Set counter
		$counter = 2;

		$totals = array();

		foreach ($products as $product){
			$sheet->setCellValue('A'.$counter, $product["quantity"]);
			$sheet->setCellValue('B'.$counter, $product["weight"]);
			$sheet->setCellValue('C'.$counter, $product["product_name"]);
			$sheet->setCellValue('D'.$counter,  $product["sub_total"]);
			$sheet->setCellValue('E'.$counter,  $product["total"]);
			$sheet->setCellValue('F'.$counter,  $product["product_price"]);
			$sheet->setCellValue('G'.$counter, $product["total_weight"]);

			if($counter > 2) {
				$totals["total"] += $product["total"];
				$totals["sub_total"] += $product["sub_total"];
				$totals["total_weight"] += $product["total_weight"];
			}

			$counter++;
		}

		// Set header bold
		$sheet->getStyle("A2:F2")->getFont()->setBold( true );


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
		$sheet->setCellValue('D'.$counter, $totals["sub_total"]);
		$sheet->setCellValue('E'.$counter, $totals["total"]);
		$sheet->setCellValue('G'.$counter, $totals["total_weight"]);
		$sheet->getStyle('D'.$counter)->applyFromArray($styleArray);
		$sheet->getStyle('E'.$counter)->applyFromArray($styleArray);
		$sheet->getStyle('G'.$counter)->applyFromArray($styleArray);


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
	}

	public function export_orders(){

		$language_data = explode('|', $_POST['languages']);
		$lang_code = $language_data[0];
		$lang_code_long = $language_data[1];
		$lang_code_id = $language_data[2];

		// get all orders with filters
		$customer_orders = get_posts( array(
			'numberposts' => - 1,
			'post_type'   => array( 'shop_order' ),
			'post_status' => wc_get_order_statuses(), //old : array( 'wc-completed' )
			'date_query'  => array(
				'after'     => date('Y-m-d', strtotime('-1 day', strtotime($_POST['start-date']))),
				'before'    => date('Y-m-d', strtotime('+1 day', strtotime($_POST['end-date'])))
			),
			'orderby'     => 'date',
			'order'       => 'ASC',
			'meta_key'    => 'wpml_language',
			'meta_value'  => $lang_code
		) );

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		// set title
		$sheet->setCellValue('A1',"ORDERS ".$lang_code." ".$_POST["start-date"]. "-".$_POST["end-date"]);
		// Set counter
		$counter = 2;

		$totals = array();


		$order_array = array();

		// Set headers
		$sheet->setCellValue('A'.$counter, "ID");
		$sheet->setCellValue('B'.$counter, "First Name");
		$sheet->setCellValue('C'.$counter, "Last Name");
		$sheet->setCellValue('D'.$counter,  "Company");
		$sheet->setCellValue('E'.$counter,  "User ID");
		$sheet->setCellValue('F'.$counter, "Sub Total");
		$sheet->setCellValue('G'.$counter, "Taxes");
		$sheet->setCellValue('H'.$counter, "Total");
		$sheet->setCellValue('H'.$counter, "VAT Number");

		$counter++;

		foreach($customer_orders as $order) {
			$order = wc_get_order( $order->ID );

			$sheet->setCellValue('A'.$counter, $order->get_id());
			$sheet->setCellValue('B'.$counter, $order->get_billing_first_name());
			$sheet->setCellValue('C'.$counter, $order->get_billing_last_name());
			$sheet->setCellValue('D'.$counter,  $order->get_billing_company());
			$sheet->setCellValue('E'.$counter,  $order->get_user_id());
			$sheet->setCellValue('F'.$counter, $order->get_subtotal());
			$totals["sub_total"] += $order->get_subtotal();
			$sheet->setCellValue('G'.$counter, $order->get_total_tax());
			$totals["taxes"] += $order->get_total_tax();
			$sheet->setCellValue('H'.$counter, $order->get_total());
			$totals["total"] += $order->get_total();
			$sheet->setCellValue('I'.$counter, get_post_meta($order->get_id(),'_vat_number',true));

			$counter++;
		}

		// Set header bold
		$sheet->getStyle("A2:I2")->getFont()->setBold( true );


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
		$sheet->setCellValue('F'.$counter, $totals["sub_total"]);
		$sheet->setCellValue('G'.$counter, $totals["taxes"]);
		$sheet->setCellValue('H'.$counter, $totals["total"]);
		$sheet->getStyle('F'.$counter)->applyFromArray($styleArray);
		$sheet->getStyle('G'.$counter)->applyFromArray($styleArray);
		$sheet->getStyle('H'.$counter)->applyFromArray($styleArray);

		$export = __('Export');
		$filename = "$export-".$_POST['start-date'].'_'.$_POST['end-date'];

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

	}


}