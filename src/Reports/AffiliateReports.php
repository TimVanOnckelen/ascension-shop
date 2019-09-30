<?php


namespace AscensionShop\Reports;


use AscensionShop\Affiliate\Helpers;
use AscensionShop\Lib\TemplateEngine;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AffiliateReports {

	private $transient_name = '';

	public function __construct(){
		add_action( 'admin_menu', array($this, 'web_settings_init') );
		add_action( 'admin_post_export_affiliates', array($this, 'export_affiliates_payout') );
	}

	public function web_settings_init() {
		add_submenu_page(
			'affiliate-wp',
			__( 'Affiliate reports'),
			__( 'Affiliate reports'),
			'manage_options',
			'Affiliate-export-page',
			array($this, 'web_settings_page')
		);
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
		echo $t->display("reports/form-export-affiliates.php");

	}



	public function export_affiliates_payout(){


		$referrals = affiliate_wp()->referrals->get_referrals(
			array(
				'number'       => -1,
				'status'       => 'unpaid',
				'date' => array('start' => $_GET["from"],'end' => $_GET["to"]),
			)
		);

		$ref_totals = Helpers::countPerRef($referrals);


		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		// set title
		$sheet->setCellValue('A1',"Affiliate Payouts ".$_POST["start-date"]. "-".$_POST["end-date"]);
		// Set counter
		$counter = 2;

		$totals = array();


		$order_array = array();

		// Set headers
		$sheet->setCellValue('A'.$counter, "Affiliate id");
		$sheet->setCellValue('B'.$counter, "Name");
		$sheet->setCellValue('C'.$counter, "Email");
		$sheet->setCellValue('D'.$counter,  "Payout amount");
		$sheet->setCellValue('E'.$counter,  "Amount of referrals");

		$counter++;

		foreach($ref_totals as $ref) {

			$sheet->setCellValue('A'.$counter, $ref["affiliate_id"]);
			$sheet->setCellValue('B'.$counter, $ref["name"]);
			$sheet->setCellValue('C'.$counter, $ref["email"]);
			$sheet->setCellValue('D'.$counter,  $ref["amount"]);
			$totals["amount"] += $ref["amount"];
			$sheet->setCellValue('E'.$counter,  $ref["refs"]);
			$totals["refs"] += $ref["refs"];

			$counter++;
		}

		// Set header bold
		$sheet->getStyle("A2:E2")->getFont()->setBold( true );


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
		$sheet->setCellValue('D'.$counter, $totals["amount"]);
		$sheet->setCellValue('E'.$counter, $totals["refs"]);
		$sheet->getStyle('D'.$counter)->applyFromArray($styleArray);
		$sheet->getStyle('E'.$counter)->applyFromArray($styleArray);

		$export = __('Uitbetalingen');
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