<?php
/**
 * Template Name: Google Product Feed
 */

// Helpers class.
require_once WOO_UGPF_PATH . 'classes/class-helpers.php';
$helper = new WC_Ultimate_Google_Product_Feed_Helpers;

// Get the currency
$currency = get_option( 'woocommerce_currency' );

// Set Google Namespace.
$ns = 'http://base.google.com/ns/1.0';

// Create a Feed.
$xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="' . $ns . '"></rss>';
$rss = new SimpleXMLElement( $xml );

// Add the channel;
$canal = $rss->addChild( 'channel' );
$canal->addChild( 'title', $helper->fix_text( get_bloginfo( 'name' ) ) );
$canal->addChild( 'link', get_home_url() );
$canal->addChild( 'description', $helper->fix_text( get_bloginfo( 'description' ) ) );

// Create a new WP_Query.
$feed_query = new WP_Query(
    array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'ignore_sticky_posts' => 1,
        'meta_key' => 'wc_ugpf_active',
        // 'meta_value' => '1'
    )
);

// Starts the Loop.
while ( $feed_query->have_posts() ) {
    $feed_query->the_post();

    $item = $canal->addChild( 'item' );
    $options = get_post_meta( get_the_ID(), 'wc_ugpf', true );
    $regular_price = get_post_meta( get_the_ID(), '_regular_price', true );
    $sale_price = get_post_meta( get_the_ID(), '_sale_price', true );
    $sale_price_dates_from = get_post_meta( get_the_ID(), '_sale_price_dates_from', true );
    $sale_price_dates_to = get_post_meta( get_the_ID(), '_sale_price_dates_to', true );

    // Basic Product Information.
    $item->addChild( 'g:id', get_the_ID(), $ns );
    $item->addChild( 'title', $helper->fix_text( get_the_title() ) );
    $item->addChild( 'description', $helper->fix_text( $options['description'] ) );
    $item->addChild( 'g:google_product_category', $helper->fix_text( $options['category'] ), $ns );
    $item->addChild( 'g:product_type', $helper->fix_text( $options['product_type'] ), $ns );
    $item->addChild( 'link', get_permalink() );

    $thumb = get_post_thumbnail_id();
    if ( $thumb ) {
        $image_url = wp_get_attachment_image_src( $thumb, 'shop_single' );
        $item->addChild( 'g:image_link', $image_url[0], $ns );
    }

    $item->addChild( 'g:condition', $helper->fix_condition( $options['condition'] ), $ns );

    // Availability and Price.
    $item->addChild( 'g:availability', $helper->fix_availability( $options['condition'] ), $ns );
    $item->addChild( 'g:price', $regular_price . ' ' . $currency, $ns );
    if ( ! empty ( $sale_price ) ) {
        $item->addChild( 'g:sale_price', $sale_price . ' ' . $currency, $ns );
        $item->addChild( 'g:sale_price_effective_date', $helper->fix_date( $sale_price_dates_from, $sale_price_dates_to ) , $ns );
    }

    // Unique Product Identifiers.
    if ( isset( $options['active_unique'] ) ) {
        $item->addChild( 'g:brand', $helper->fix_text( $options['brand'] ), $ns );
        $item->addChild( 'g:gtin', $options['gtin'], $ns );
        $item->addChild( 'g:mpn', $options['mpn'], $ns );
    }

    // Tax and Shipping.
    if ( isset( $options['active_tax'] ) ) {
        // Taxs.
        if ( ! empty( $options['tax'] ) ) {
            foreach ( $helper->fix_tax( $options['tax'] ) as $value ) {
                $tax = $item->addChild( 'g:tax', '', $ns );
                if ( !empty( $value[0] ) ) $tax->addChild( 'g:country', $value[0], $ns );
                if ( !empty( $value[1] ) ) $tax->addChild( 'g:region', $value[1], $ns );
                if ( !empty( $value[2] ) ) $tax->addChild( 'g:rate', $value[2], $ns );
                if ( !empty( $value[3] ) ) $tax->addChild( 'g:tax_ship', $value[3], $ns );
            }
        }

        // Shipping.
        if ( ! empty( $options['shipping'] ) ) {
            foreach ( $helper->fix_tax( $options['shipping'] ) as $value ) {
                $shipping = $item->addChild( 'g:shipping', '', $ns );
                if ( !empty( $value[0] ) ) $shipping->addChild( 'g:country', $value[0], $ns );
                if ( !empty( $value[1] ) ) $shipping->addChild( 'g:region', $value[1], $ns );
                if ( !empty( $value[2] ) ) $shipping->addChild( 'g:service', $value[2], $ns );
                if ( !empty( $value[3] ) ) $shipping->addChild( 'g:price', $value[3], $ns );
            }
        }

        // Shipping Weight.
        if ( ! empty( $options['shipping_weight'] ) ) {
            $item->addChild( 'g:shipping_weight', $options['shipping_weight'], $ns );
        }
    }

    // Apparel Products.
    if ( isset( $options['active_apparel'] ) ) {
        $item->addChild( 'g:gender', $helper->fix_gender( $options['gender'] ), $ns );
        $item->addChild( 'g:age_group', $helper->fix_age_group( $options['age_group'] ), $ns );
        $item->addChild( 'g:color', $helper->fix_text( $options['color'] ), $ns );
        $item->addChild( 'g:size', $options['size'], $ns );
    }

    // Nearby Stores.
    if ( isset( $options['online_only'] ) ) {
        $item->addChild( 'g:online_only', 'y', $ns );
    }

    // Multiple Installments.
    if ( isset( $options['active_installments'] ) ) {
        $installment = $item->addChild( 'g:installment', '', $ns );
        $installment->addChild( 'g:months', $options['installment_months'], $ns );
        $installment->addChild( 'g:amount', $options['installment_amount'] . ' BRL', $ns );
    }

    // Additional Attributes.
    if ( isset( $options['excluded_destination_ps'] ) ) {
        $item->addChild( 'g:excluded_destination', 'Product Search', $ns );
    }

    if ( isset( $options['excluded_destination_pa'] ) ) {
        $item->addChild( 'g:excluded_destination', 'Product Ads', $ns );
    }

    if ( isset( $options['excluded_destination_cs'] ) ) {
        $item->addChild( 'g:excluded_destination', 'Commerce Search', $ns );
    }

    if ( ! empty( $options['expiration_date'] ) ) {
        $item->addChild( 'g:expiration_date', $options['expiration_date'], $ns );
    }

}

wp_reset_postdata();

// Sets the charset.
header( 'content-type: application/rss+xml; charset=utf-8' );

// Format and print the XML.
$dom = dom_import_simplexml( $rss )->ownerDocument;
$dom->formatOutput = true;
echo $dom->saveXML();

exit;
