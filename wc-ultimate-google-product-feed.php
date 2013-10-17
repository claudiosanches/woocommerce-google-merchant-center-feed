<?php
/**
 * Plugin Name: WooCommerce Ultimate Google Product Feed
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-ultimate-google-product-feed
 * Description: Creates a Feed to integrate with your Google Merchant Center.
 * Author: claudiosanches
 * Author URI: http://claudiosmweb.com/
 * Version: 1.0.0
 * License: GPLv2 or later
 * Text Domain: wcugpf
 * Domain Path: /languages/
 */

define( 'WOO_UGPF_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_UGPF_URL', plugin_dir_url( __FILE__ ) );

/**
 * WooCommerce fallback notice.
 */
function wcugpf_woocommerce_fallback_notice() {
    echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Ultimate Google Product Feed depends on the last version of %s to work!', 'wcugpf' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', 'wcugpf' ) . '</a>' ) . '</p></div>';
}

/**
 * Load functions.
 */
function wcugpf_gateway_load() {

    /**
     * Load textdomain.
     */
    load_plugin_textdomain( 'wcugpf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    // Checks with WooCommerce is installed.
    if ( ! class_exists( 'WC_Integration' ) ) {
        add_action( 'admin_notices', 'wcugpf_woocommerce_fallback_notice' );

        return;
    }

    /**
     * Add a new integration to WooCommerce.
     *
     * @param  array $integrations WooCommerce integrations.
     *
     * @return array               Integrations with Google Product Feed.
     */
    function wcugpf_add_integration( $integrations ) {
        $integrations[] = 'WC_Ultimate_Google_Product_Feed';

        return $integrations;
    }

    add_filter( 'woocommerce_integrations', 'wcugpf_add_integration' );

    // Include integration class.
    require_once WOO_UGPF_PATH . 'includes/class-wc-ultimate-google-product-feed.php';
}

add_action( 'plugins_loaded', 'wcugpf_gateway_load', 0 );

/**
 * Adds custom settings url in plugins page.
 *
 * @param  array $links Default links.
 *
 * @return array        Default links and settings link.
 */
function wcugpf_action_links( $links ) {

    $settings = array(
        'settings' => sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'admin.php?page=woocommerce_settings&tab=integration&section=google-product-feed' ),
            __( 'Settings', 'wcugpf' )
        )
    );

    return array_merge( $settings, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wcugpf_action_links' );

/**
 * Create feed page on plugin install.
 */
function wcugpf_create_page() {
    $slug = sanitize_title( __( 'product-feed', 'wcugpf' ) );

    if ( ! get_page_by_path( $slug ) ) {
        $page = array(
            'post_title'     => __( 'Google Product Feed', 'wcugpf' ),
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

register_activation_hook( __FILE__, 'wcugpf_create_page' );
