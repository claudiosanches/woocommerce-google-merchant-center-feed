<?php
/**
 * Plugin Name: WooCommerce Google Merchant Center Feed
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-google-merchant-center-feed
 * Description: Creates a Feed to integrate with your Google Merchant Center.
 * Author: claudiosanches
 * Author URI: http://claudiosmweb.com/
 * Version: 1.0.1
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
 * Adds custom settings url in plugins page.
 *
 * @param  array $links Default links.
 *
 * @return array        Default links and settings link.
 */
function wcgmcf_action_links( $links ) {

    $settings = array(
        'settings' => sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'admin.php?page=woocommerce_settings&tab=integration&section=google-merchant-center' ),
            __( 'Settings', 'wcgmcf' )
        )
    );

    return array_merge( $settings, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wcgmcf_action_links' );

/**
 * Create feed page on plugin install.
 */
function wcgmcf_create_page() {
    $slug = sanitize_title( _x( 'product-feed', 'page slug', 'wcgmcf' ) );

    if ( ! get_page_by_path( $slug ) ) {
        $page = array(
            'post_title'     => _x( 'Product Feed', 'page name', 'wcgmcf' ),
            'post_name'      => $slug,
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'post_content'   => '',
        );

        wp_insert_post( $page );
    }
}

register_activation_hook( __FILE__, 'wcgmcf_create_page' );
