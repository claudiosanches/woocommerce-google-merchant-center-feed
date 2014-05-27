<?php
/**
 * Plugin Name: WooCommerce Google Merchant Center Feed
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-google-merchant-center-feed
 * Description: Creates a Feed to integrate with your Google Merchant Center.
 * Author: claudiosanches
 * Author URI: http://claudiosmweb.com/
 * Version: 1.1.0
 * License: GPLv2 or later
 * Text Domain: woocommerce-google-merchant-center-feed
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Google_Merchant_Center_Feed' ) ) :

/**
 * WooCommerce Boleto main class.
 */
class WC_Google_Merchant_Center_Feed {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.1.0';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin actions.
	 */
	public function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		if ( class_exists( 'WC_Integration' ) ) {
			// Plugin classes.
			$this->includes();

			// Admin.
			if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
				$this->admin_include();
			}

			add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
			add_action( 'init', array( __CLASS__, 'add_feed' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-google-merchant-center-feed' );

		load_textdomain( 'woocommerce-google-merchant-center-feed', trailingslashit( WP_LANG_DIR ) . 'woocommerce-google-merchant-center-feed/woocommerce-google-merchant-center-feed-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce-google-merchant-center-feed', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Include plugin classes.
	 *
	 * @return void
	 */
	protected function includes() {
		require_once 'includes/class-wc-google-merchant-center-feed-integration.php';
	}

	/**
	 * Include admin classes.
	 *
	 * @return void
	 */
	protected function admin_include() {
		// require_once 'includes/class-wc-boleto-admin.php';
	}

	/**
	 * Add a new integration to WooCommerce.
	 *
	 * @param  array $integrations WooCommerce integrations.
	 *
	 * @return array               Integrations with WooCommerce Google Merchant Center Feed.
	 */
	public function add_integration( $integrations ) {
		$integrations[] = 'WC_Google_Merchant_Center_Feed_Integration';

		return $integrations;
	}

	/**
	 * Add feed.
	 *
	 * @return void
	 */
	public static function add_feed() {
		add_feed( 'wc-google-merchant-center.xml', array( __CLASS__, 'feed_template' ) );
	}

	public static function feed_template() {
		load_template( plugin_dir_path( __FILE__ ) . 'templates/feed.php' );
	}

	/**
	 * Plugin activate method.
	 *
	 * @return void
	 */
	public static function activate() {
		add_feed( 'wc-google-merchant-center.xml', array( __CLASS__, 'feed_template' ) );

		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivate method.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * WooCommerce fallback notice.
	 *
	 * @return string
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Google Merchant Center Feed depends on the last version of %s to work!', 'woocommerce-google-merchant-center-feed' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', 'woocommerce-google-merchant-center-feed' ) . '</a>' ) . '</p></div>';
	}
}

/**
 * Plugin activation and deactivation methods.
 */
register_activation_hook( __FILE__, array( 'WC_Google_Merchant_Center_Feed', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WC_Google_Merchant_Center_Feed', 'deactivate' ) );

/**
 * Initialize the plugin.
 */
add_action( 'plugins_loaded', array( 'WC_Google_Merchant_Center_Feed', 'get_instance' ), 0 );

endif;
