<?php
/**
 * Google Product Feed XML.
 *
 * @since 1.0.1
 */
class WC_GMCF_XML {

	/**
	 * Fix Condition label.
	 *
	 * @param  int    $condition Condition option.
	 *
	 * @return string            Fixed condition.
	 */
	protected function fix_condition( $condition ) {
		switch ( $condition ) {
			case 1:
				$value = 'used';
				break;
			case 2:
				$value = 'refurbished';
				break;

			default:
				$value = 'new';
				break;
		}

		return $value;
	}

	/**
	 * Fix Availability label.
	 *
	 * @param  int    $availability Availability option.
	 *
	 * @return string               Fixed availability.
	 */
	protected function fix_availability( $availability ) {
		switch ( $availability ) {
			case 1:
				$value = 'available for order';
				break;
			case 2:
				$value = 'out of stock';
				break;
			case 3:
				$value = 'preorder';
				break;

			default:
				$value = 'in stock';
				break;
		}

		return $value;
	}

	/**
	 * Fix Gender label.
	 *
	 * @param  int    $gender Gender option.
	 *
	 * @return string         Fixed gender.
	 */
	protected function fix_gender( $gender ) {
		switch ( $gender ) {
			case 1:
				$value = 'female';
				break;
			case 2:
				$value = 'unisex';
				break;

			default:
				$value = 'male';
				break;
		}

		return $value;
	}

	/**
	 * Fix Age Group label.
	 *
	 * @param  int    $age_group Age Group option.
	 *
	 * @return string            Fixed age group.
	 */
	protected function fix_age_group( $age_group ) {
		return ( 0 == $age_group ) ? 'adult' : 'kids';
	}

	/**
	 * Build the tax.
	 *
	 * @param  string $values Tax in string format.
	 *
	 * @return array          Tax in array format.
	 */
	protected function build_tax( $values ) {
		$tax = array();

		$values = explode( ',', $values );

		foreach ( $values as $value ) {
			$tax[] = explode( ':', $value );
		}

		return $tax;
	}

	/**
	 * Fix date.
	 *
	 * @param  string $from From date.
	 * @param  string $to   To date.
	 *
	 * @return string       Fixed date.
	 */
	protected function fix_date( $from, $to ) {
		$date_to = date( 'Y-m-d', strtotime( '+1 day', strtotime( date( 'Y-m-d', $to ) ) ) );

		return date( 'Y-m-d', $from ) . 'T00:00-0000/' . $date_to . 'T00:00-0000';
	}

	/**
	 * Render the XML.
	 *
	 * @return string XML/RSS.
	 */
	public function render() {
		// Settings.
		$settings = get_option( 'woocommerce_google-merchant-center_settings' );
		$items_total = isset( $settings['items_total'] ) ? (int) $settings['items_total'] : 10;

		// Get the currency
		$currency = get_option( 'woocommerce_currency' );

		// Set Google Namespace.
		$ns = 'http://base.google.com/ns/1.0';

		// Create a Feed.
		$xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="' . $ns . '"></rss>';
		$rss = new WC_GMCF_SimpleXML( $xml );

		// Add the channel.
		$channel = $rss->addChild( 'channel' );
		$channel->addChild( 'title', get_bloginfo( 'name' ) );
		$channel->addChild( 'link', get_home_url() );
		$channel->addChild( 'description' )->addCData( sanitize_text_field( get_bloginfo( 'description' ) ) );

		// Create a new WP_Query.
		$feed_query = new WP_Query(
			array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'ignore_sticky_posts' => 1,
				'meta_key' => 'wc_gmcf_active',
				'posts_per_page' => $items_total
			)
		);

		// Starts the Loop.
		while ( $feed_query->have_posts() ) {
			$feed_query->the_post();

			// Gets the product data.
			$product = get_product( get_the_ID() );

			$item = $channel->addChild( 'item' );
			$options = get_post_meta( get_the_ID(), 'wc_gmcf', true );

			// Basic Product Information.
			$item->addChild( 'g:id', get_the_ID(), $ns );
			$item->addChild( 'title' )->addCData( sanitize_text_field( get_the_title() ) );
			$item->addChild( 'description' )->addCData( sanitize_text_field( $options['description'] ) );
			$item->addChild( 'g:google_product_category', '', $ns )->addCData( sanitize_text_field( $options['category'] ) );
			$item->addChild( 'g:product_type', '', $ns )->addCData( sanitize_text_field( $options['product_type'] ) );
			$item->addChild( 'link', get_permalink() );

			$thumb = get_post_thumbnail_id();
			if ( $thumb ) {
				$image_url = wp_get_attachment_image_src( $thumb, 'shop_single' );
				$item->addChild( 'g:image_link', $image_url[0], $ns );
			}

			$item->addChild( 'g:condition', $this->fix_condition( $options['condition'] ), $ns );

			// Availability and Price.
			$item->addChild( 'g:availability', $this->fix_availability( $options['availability'] ), $ns );

			if ( $product->is_type( 'variable' ) ) {
				if ( $product->is_on_sale() ) {
					$item->addChild( 'g:price', $product->min_variation_regular_price . ' ' . $currency, $ns );
					$item->addChild( 'g:sale_price', $product->min_variation_price . ' ' . $currency, $ns );
				} else {
					$item->addChild( 'g:price', $product->get_price() . ' ' . $currency, $ns );
				}
			} else {
				if ( $product->is_on_sale() ) {
					$item->addChild( 'g:price', $product->regular_price . ' ' . $currency, $ns );
					$item->addChild( 'g:sale_price', $product->sale_price . ' ' . $currency, $ns );

					if ( ! empty( $product->sale_price_dates_from ) && ! empty( $product->sale_price_dates_to ) ) {
						$item->addChild( 'g:sale_price_effective_date', $this->fix_date( $product->sale_price_dates_from, $product->sale_price_dates_to ) , $ns );
					}
				} else {
					$item->addChild( 'g:price', $product->get_price() . ' ' . $currency, $ns );
				}
			}

			// Unique Product Identifiers.
			if ( isset( $options['active_unique'] ) ) {
				$item->addChild( 'g:brand', '', $ns )->addCData( sanitize_text_field( $options['brand'] ) );
				$item->addChild( 'g:gtin', $options['gtin'], $ns );
				$item->addChild( 'g:mpn', $options['mpn'], $ns );
			} else {
				$item->addChild( 'g:identifier_exists', 'FALSE', $ns );
			}

			// Tax and Shipping.
			if ( isset( $options['active_tax'] ) ) {
				// Taxs.
				if ( ! empty( $options['tax'] ) ) {
					foreach ( $this->build_tax( $options['tax'] ) as $value ) {
						$tax = $item->addChild( 'g:tax', '', $ns );
						if ( ! empty( $value[0] ) ) {
							$tax->addChild( 'g:country', $value[0], $ns );
						}
						if ( ! empty( $value[1] ) ) {
							$tax->addChild( 'g:region', $value[1], $ns );
						}
						if ( ! empty( $value[2] ) ) {
							$tax->addChild( 'g:rate', $value[2], $ns );
						}
						if ( ! empty( $value[3] ) ) {
							$tax->addChild( 'g:tax_ship', $value[3], $ns );
						}
					}
				}

				// Shipping.
				if ( ! empty( $options['shipping'] ) ) {
					foreach ( $this->build_tax( $options['shipping'] ) as $value ) {
						$shipping = $item->addChild( 'g:shipping', '', $ns );
						if ( ! empty( $value[0] ) ) {
							$shipping->addChild( 'g:country', $value[0], $ns );
						}
						if ( ! empty( $value[1] ) ) {
							$shipping->addChild( 'g:region', $value[1], $ns );
						}
						if ( ! empty( $value[2] ) ) {
							$shipping->addChild( 'g:service', $value[2], $ns );
						}
						if ( ! empty( $value[3] ) ) {
							$shipping->addChild( 'g:price', $value[3], $ns );
						}
					}
				}

				// Shipping Weight.
				if ( ! empty( $options['shipping_weight'] ) ) {
					$item->addChild( 'g:shipping_weight', $options['shipping_weight'], $ns );
				}
			}

			// Apparel Products.
			if ( isset( $options['active_apparel'] ) ) {
				$item->addChild( 'g:gender', $this->fix_gender( $options['gender'] ), $ns );
				$item->addChild( 'g:age_group', $this->fix_age_group( $options['age_group'] ), $ns );
				$item->addChild( 'g:color', $options['color'], $ns );
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

		// Filter the RSS.
		$rss = apply_filters( 'wc_google_merchant_center_feed_xml', $rss );

		// Format and print the XML.
		$dom = dom_import_simplexml( $rss )->ownerDocument;
		$dom->formatOutput = true;
		return $dom->saveXML();
	}
}
