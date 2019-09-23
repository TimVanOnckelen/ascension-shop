<?php


namespace AscensionShop\Affiliate;


use AscensionShop\Lib\TemplateEngine;
use Spipu\Html2Pdf\Html2Pdf;

class FrontendReports {

	function __construct(){
		add_action("init",array($this,"loadReport"));
	}

	public function loadReport(){
		if(isset($_GET["ascension-download-report"])){

		}
	}
}