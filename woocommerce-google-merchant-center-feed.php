<?php
/**
 * Plugin Name: WooCommerce Google Merchant Center Feed
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-google-merchant-center-feed
 * Description: Creates a Feed to integrate with your Google Merchant Center.
 * Author: claudiosanches
 * Author URI: http://claudiosmweb.com/
 * Version: 1.0.6
 * License: GPLv2 or later
 * Text Domain: wcgmcf
 * Domain Path: /languages/
 */

define( 'WOO_GMCF_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_GMCF_URL', plugin_dir_url( __FILE__ ) );

/**
 * WooCommerce fallback notice.
 */
function wcgmcf_woocommerce_fallback_notice() {
	echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Ultimate Google Product Feed depends on the last version of %s to work!', 'wcgmcf' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', 'wcgmcf' ) . '</a>' ) . '</p></div>';
}

/**
 * Load functions.
 */
function wcgmcf_gateway_load() {

	/**
	 * Load textdomain.
	 */
	load_plugin_textdomain( 'wcgmcf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Checks with WooCommerce is installed.
	if ( ! class_exists( 'WC_Integration' ) ) {
		add_action( 'admin_notices', 'wcgmcf_woocommerce_fallback_notice' );

		return;
	}

	/**
	 * Add a new integration to WooCommerce.
	 *
	 * @param  array $integrations WooCommerce integrations.
	 *
	 * @return array               Integrations with WooCommerce Google Merchant Center Feed.
	 */
	function wcgmcf_add_integration( $integrations ) {
		$integrations[] = 'WC_Google_Merchant_Center_Feed';

		return $integrations;
	}

	add_filter( 'woocommerce_integrations', 'wcgmcf_add_integration' );

	// Include integration class.
	require_once WOO_GMCF_PATH . 'includes/class-wc-google-merchant-center-feed.php';
}

add_action( 'plugins_loaded', 'wcgmcf_gateway_load', 0 );

/**
 * Add rewrite rules to support /feed/wcgmcf and /wcgmcf.xml URIs
 * @param $wp_rewrite
 */
function wcgmcf_rewrite( $wp_rewrite ) {
	$feed_rules        = array(
		'feed/(.+)' => 'index.php?feed=' . $wp_rewrite->preg_index( 1 ),
		'(.+).xml'  => 'index.php?feed=' . $wp_rewrite->preg_index( 1 ),
	);
	$wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
}
add_filter( 'generate_rewrite_rules', 'wcgmcf_rewrite' );

/**
 * flush_rules() if our rules are not yet included
 */
function wcgmcf_flush_rules() {
	$rules = get_option( 'rewrite_rules' );

	if ( ! isset( $rules['feed/(.+)'] )
	     || ! isset( $rules['(.+).xml'] )
	) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
}
add_action( 'wp_loaded', 'wcgmcf_flush_rules' );

/**
 * Executed when the wcgmcf feed is requested.
 */
function wcgmcf_feed() {
	load_template( WOO_GMCF_PATH . 'templates/feed.php' );
}

/**
 * Creates the new feed type
 */
function wcgmcf_init() {
	add_feed( 'wcgmcf', 'wcgmcf_feed' );
}
add_action( 'init', 'wcgmcf_init' );
