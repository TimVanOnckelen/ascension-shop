<?php
/*
Plugin Name: Ascension Shop
Plugin URI: https://www.xeweb.be
Description: Custom shop plugin for Ascension
Version: 1.0.3
Author: XeWeb
Author URI: https://www.xeweb.be
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/vendor/autoload.php';

define( 'XE_ASCENSION_SHOP_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
define( 'XE_ASCENSION_SHOP_FILE', __FILE__ );
define( 'XE_ASCENSION_SHOP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'XE_ASCENSION_SHOP_PLUGIN_TEMPLATE_PATH', plugin_dir_path( __FILE__ )."templates/" );

// Init main BEFORE affialte WP runs do's
add_action("init","__xe_ascension_shop",8);


function __xe_ascension_shop(){
	// Load main
	new \AscensionShop\Main();

	// Activation Hooks
	new \AscensionShop\Affiliate\Activation();
}

?>