<?php
// If uninstall not called from WordPress exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) ) ) {

	status_header( 404 );
	exit;
}

// Delete feed page.
$feed_page = get_page_by_path( 'product-feed' );
if ( $feed_page ) {
	wp_delete_post( $feed_page->ID, true );
}
