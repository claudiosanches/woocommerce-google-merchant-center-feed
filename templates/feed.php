<?php
/**
 * Template Name: Google Product Feed
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Sets the charset.
@ob_clean();
header( 'Content-type: application/xml' );

// Helpers classes.
require_once WOO_UGPF_PATH . 'includes/class-wc-ugpf-simplexml.php';
require_once WOO_UGPF_PATH . 'includes/class-wc-ugpf-xml.php';

$feed = new WC_UGPF_XML;
echo $feed->render();

exit;
