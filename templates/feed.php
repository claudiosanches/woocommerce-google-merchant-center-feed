<?php
/**
 * Google Product Feed Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Sets the charset.
@ob_clean();
header( 'Content-type: application/xml' );

$plugin_dir_path = dirname( plugin_dir_path( __FILE__ ) ) . '/';

// Helpers classes.
require_once $plugin_dir_path . 'includes/class-wc-google-merchant-center-feed-simplexml.php';
require_once $plugin_dir_path . 'includes/class-wc-google-merchant-center-feed-generator.php';

$feed = new WC_Google_Merchant_Center_Feed_Generator();
echo $feed->render();

exit;
