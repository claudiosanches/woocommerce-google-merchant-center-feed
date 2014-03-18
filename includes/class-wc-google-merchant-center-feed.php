<?php
/**
 * Ultimate Google Product Feed class.
 *
 * @since 1.0.3
 */
class WC_Google_Merchant_Center_Feed extends WC_Integration {

	/**
	 * Init and hook in the integration.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->id                 = 'google-merchant-center';
		$this->method_title       = __( 'Google Merchant Center', 'wcgmcf' );
		$this->method_description = __( 'Creates a Feed to integrate with your Google Merchant Center.', 'wcgmcf' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->page         = apply_filters( 'wc_google_merchant_center_feed_page', sanitize_title( _x( 'product-feed', 'page slug', 'wcgmcf' ) ) );
		$this->items_total  = $this->get_option( 'items_total' );
		$this->category     = $this->get_option( 'category' );
		$this->product_type = $this->get_option( 'product_type' );

		// Save integration options.
		add_action( 'woocommerce_update_options_integration_google-merchant-center', array( $this, 'process_admin_options' ) );

		// Add write panel tab.
		add_action( 'woocommerce_product_write_panel_tabs', array( &$this, 'add_tab' ) );

		// Create write panel.
		add_action( 'woocommerce_product_write_panels', array( &$this, 'tab_view' ) );

		// Save meta.
		add_action( 'woocommerce_process_product_meta', array( &$this, 'save_tab_options' ) );

		// Add page template.
		add_filter( 'page_template', array( &$this, 'feed_template' ) );

		// Load scripts.
		add_action( 'admin_enqueue_scripts', array( &$this, 'scripts' ) );
	}

	/**
	 * Initialise Integration Settings Form Fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'items_total' => array(
				'title'       => __( 'Number of items in the feed', 'wcgmcf' ),
				'type'        => 'text',
				'description' => __( 'Total number of items that will be displayed in the feed', 'wcgmcf' ),
				'desc_tip'    => true,
				'default'     => '10'
			),
			'defaults' => array(
				'title'       => __( 'Default Options', 'wcgmcf' ),
				'type'        => 'title',
				'description' => sprintf( __( 'You need help completing these options? Check the %s.', 'wcgmcf' ), '<a href="http://support.google.com/merchants/bin/answer.py?answer=188494" target="_blank">' . __( 'Products Feed Specification', 'wcgmcf' ) . '</a>' )
			),
			'category' => array(
				'title'       => __( 'Default Category', 'wcgmcf' ),
				'type'        => 'textarea',
				'default'     => ''
			),
			'product_type' => array(
				'title'       => __( 'Default Product Type', 'wcgmcf' ),
				'type'        => 'textarea',
				'default'     => ''
			),
		);
	}

	/**
	 * Load metabox scripts.
	 *
	 * @return void
	 */
	public function scripts() {
		$screen = get_current_screen();

		if ( 'product' === $screen->id ) {
			wp_enqueue_script( 'wc-gmcf-metabox', WOO_GMCF_URL . '/assets/js/jquery.wc-gmcf.min.js', array( 'jquery' ), null, true );
		}
	}

	/**
	 * Add new tab.
	 */
	public function add_tab() {
		echo '<li class="advanced_tab advanced_options wc_gmcf_tab"><a href="#wc_gmcf_tab">' . __( 'Google Merchant', 'wcgmcf' ) . '</a></li>';
	}

	/**
	 * Tab content.
	 */
	public function tab_view() {
		global $post;

		$options = get_post_meta( $post->ID, 'wc_gmcf', true );
		$active = get_post_meta( $post->ID, 'wc_gmcf_active', true );
		?>
		<div id="wc_gmcf_tab" class="panel woocommerce_options_panel">
			<div id="wc_gmcf_tab_active" class="options_group">
				<?php
					woocommerce_wp_checkbox(
						array(
							'id' => 'wc_gmcf_active',
							'label' => __( 'Include in Product Feed?', 'wcgmcf' ),
							'description' => __( 'Enable this option to include in this product in your Product Feed', 'wcgmcf' ),
							'value' => isset( $active ) ? $active : ''
						)
					);
				?>
				<p class="form-field"><?php _e( 'You need help completing these options? Check this:', 'wcgmcf' ); ?> <a href="http://support.google.com/merchants/bin/answer.py?answer=188494" target="_blank"><?php _e( 'Products Feed Specification', 'wcgmcf' ) ?></a></p>
			</div>
			<div id="wc_gmcf_items">
				<div id="wc_gmcf_tab_basic" class="options_group">
					<p class="form-field"><strong><?php _e( 'Basic Product Information', 'wcgmcf' ); ?></strong></p>
					<?php
						// Description.
						woocommerce_wp_textarea_input(
							array(
								'id' => 'wc_gmcf[description]',
								'label' => __( 'Description', 'wcgmcf' ),
								'description' => __( 'Description of the item', 'wcgmcf' ),
								'value' => isset( $options['description'] ) ? $options['description'] : ''
							)
						);

						// Category.
						woocommerce_wp_textarea_input( array(
							'id' => 'wc_gmcf[category]',
							'label' => __( 'Category', 'wcgmcf' ),
							'description' => __( '<a href="http://support.google.com/merchants/bin/answer.py?answer=1705911" target="_blank">Google\'s category of the item</a>', 'wcgmcf' ),
							'value' => isset( $options['category'] ) ? $options['category'] : $this->category
						) );

						// Product Type.
						woocommerce_wp_textarea_input( array(
							'id' => 'wc_gmcf[product_type]',
							'label' => __( 'Product Type', 'wcgmcf' ),
							'description' => __( 'Your category of the item', 'wcgmcf' ),
							'value' => isset( $options['product_type'] ) ? $options['product_type'] : $this->product_type
						) );

						// Condition.
						woocommerce_wp_select( array(
							'id' => 'wc_gmcf[condition]',
							'label' => __( 'Condition', 'wcgmcf' ),
							'description' => __( 'Condition or state of the item', 'wcgmcf' ),
							'value' => isset( $options['condition'] ) ? $options['condition'] : '',
							'options' => array(
								__( 'new', 'wcgmcf' ),
								__( 'used', 'wcgmcf' ),
								__( 'refurbished', 'wcgmcf' )
							)
						) );
					?>
				</div>
				<div id="wc_gmcf_tab_availability" class="options_group">
					<p class="form-field"><strong><?php _e( 'Availability', 'wcgmcf' ); ?></strong></p>
					<?php
						// Availability.
						woocommerce_wp_select( array(
							'id' => 'wc_gmcf[availability]',
							'label' => __( 'Availability', 'wcgmcf' ),
							'description' => __( 'Availability status of the item', 'wcgmcf' ),
							'value' => isset( $options['availability'] ) ? $options['availability'] : '',
							'options' => array(
								__( 'in stock', 'wcgmcf' ),
								__( 'available for order', 'wcgmcf' ),
								__( 'out of stock', 'wcgmcf' ),
								__( 'preorder', 'wcgmcf' )
							)
						) );
					?>
				</div>
				<div id="wc_gmcf_tab_unique" class="options_group">
					<p class="form-field"><strong><?php _e( 'Unique Product Identifiers', 'wcgmcf' ); ?></strong></p>
					<?php
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[active_unique]',
								'label' => __( 'Include in Feed?', 'wcgmcf' ),
								'description' => __( 'Enable this option to include "Unique Product Identifiers" in Google products Feed', 'wcgmcf' ),
								'value' => isset( $options['active_unique'] ) ? $options['active_unique'] : ''
							)
						);
					?>
					<div class="wc_ugp_wrap">
						<?php
							// Brand.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[brand]',
									'label' => __( 'Brand', 'wcgmcf' ),
									'description' => __( 'Brand of the item', 'wcgmcf' ),
									'value' => isset( $options['brand'] ) ? $options['brand'] : ''
								)
							);

							// GTIN.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[gtin]',
									'label' => __( 'GTIN', 'wcgmcf' ),
									'description' => __( 'Global Trade Item Number (GTIN) of the item', 'wcgmcf' ),
									'value' => isset( $options['gtin'] ) ? $options['gtin'] : ''
								)
							);

							// MPN.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[mpn]',
									'label' => __( 'MPN', 'wcgmcf' ),
									'description' => __( 'Manufacturer Part Number (MPN) of the item', 'wcgmcf' ),
									'value' => isset( $options['mpn'] ) ? $options['mpn'] : ''
								)
							);

						?>
					</div>
				</div>
				<div id="wc_gmcf_tab_tax" class="options_group">
					<p class="form-field"><strong><?php _e( 'Tax & Shipping', 'wcgmcf' ); ?></strong></p>
					<?php
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[active_tax]',
								'label' => __( 'Include in Feed?', 'wcgmcf' ),
								'description' => __( 'Enable this option to include "Tax & Shipping" in Google products Feed', 'wcgmcf' ),
								'value' => isset( $options['active_tax'] ) ? $options['active_tax'] : ''
							)
						);
					?>
					<div class="wc_ugp_wrap">
						<?php
							// Tax.
							woocommerce_wp_textarea_input(
								array(
									'id' => 'wc_gmcf[tax]',
									'label' => __( 'Tax', 'wcgmcf' ),
									'description' => __( 'This attribute is only available in the US. Example: <code>US:CA:8.25:y,US:926*:8.75:y</code>', 'wcgmcf' ),
									'value' => isset( $options['tax'] ) ? $options['tax'] : ''
								)
							);

							// Shipping.
							woocommerce_wp_textarea_input(
								array(
									'id' => 'wc_gmcf[shipping]',
									'label' => __( 'Shipping', 'wcgmcf' ),
									'description' => __( 'This attribute provides the specific shipping estimate for the product. Example: <code>US:024*:Ground:6.49 USD,US:MA:Express:13.12 USD</code>', 'wcgmcf' ),
									'value' => isset( $options['shipping'] ) ? $options['shipping'] : ''
								)
							);

							// Shipping Weight.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[shipping_weight]',
									'label' => __( 'Shipping Weight', 'wcgmcf' ),
									'description' => __( 'Weight of the item for shipping. Accept only the following units of weight: lb, oz, g, kg. Example: <code>3 kg</code>', 'wcgmcf' ),
									'value' => isset( $options['shipping_weight'] ) ? $options['shipping_weight'] : ''
								)
							);

						?>
					</div>
				</div>
				<div id="wc_gmcf_tab_apparel" class="options_group">
					<p class="form-field"><strong><?php _e( 'Apparel Products', 'wcgmcf' ); ?></strong></p>
					<?php
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[active_apparel]',
								'label' => __( 'Include in Feed?', 'wcgmcf' ),
								'description' => __( 'Enable this option to include "Apparel Products" in Google products Feed', 'wcgmcf' ),
								'value' => isset( $options['active_apparel'] ) ? $options['active_apparel'] : ''
							)
						);
					?>
					<div class="wc_ugp_wrap">
						<?php
							// Gender.
							woocommerce_wp_select(
								array(
									'id' => 'wc_gmcf[gender]',
									'label' => __( 'Gender', 'wcgmcf' ),
									'description' => __( 'Gender of the item', 'wcgmcf' ),
									'value' => isset( $options['gender'] ) ? $options['gender'] : '',
									'options' => array(
										__( 'male', 'wcgmcf' ),
										__( 'female', 'wcgmcf' ),
										__( 'unisex', 'wcgmcf' )
									)
								)
							);

							// Age Group.
							woocommerce_wp_select(
								array(
									'id' => 'wc_gmcf[age_group]',
									'label' => __( 'Age Group', 'wcgmcf' ),
									'description' => __( 'Target age group of the item', 'wcgmcf' ),
									'value' => isset( $options['age_group'] ) ? $options['age_group'] : '',
									'options' => array(
										__( 'adult', 'wcgmcf' ),
										__( 'kids', 'wcgmcf' )
									)
								)
							);

							// Color.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[color]',
									'label' => __( 'Color', 'wcgmcf' ),
									'description' => __( 'Color of the item', 'wcgmcf' ),
									'value' => isset( $options['color'] ) ? $options['color'] : ''
								)
							);

							// Size.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[size]',
									'label' => __( 'Size', 'wcgmcf' ),
									'description' => __( 'Size of the item', 'wcgmcf' ),
									'value' => isset( $options['size'] ) ? $options['size'] : ''
								)
							);
						?>
					</div>
				</div>
				<div id="wc_gmcf_tab_nearby" class="options_group">
					<p class="form-field"><strong><?php _e( 'Nearby Stores (USA and UK only)', 'wcgmcf' ); ?></strong></p>
					<?php
						// Nearby Stores.
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[online_only]',
								'label' => __( 'Online Only', 'wcgmcf' ),
								'description' => __( 'Whether an item is available for purchase only online', 'wcgmcf' ),
								'value' => isset( $options['online_only'] ) ? $options['online_only'] : ''
							)
						);
					?>
				</div>
				<div id="wc_gmcf_tab_installments" class="options_group">
					<p class="form-field"><strong><?php _e( 'Multiple Installments (Brazil Only)', 'wcgmcf' ); ?></strong></p>
					<?php
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[active_installments]',
								'label' => __( 'Include in Feed?', 'wcgmcf' ),
								'description' => __( 'Enable this option to include "Installments" in Google products Feed', 'wcgmcf' ),
								'value' => isset( $options['active_installments'] ) ? $options['active_installments'] : ''
							)
						);
					?>
					<div class="wc_ugp_wrap">
						<?php
							// Multiple Installments.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[installment_months]',
									'label' => __( 'Number', 'wcgmcf' ),
									'description' => __( 'Number of installments to pay for an item', 'wcgmcf' ),
									'value' => isset( $options['installment_months'] ) ? $options['installment_months'] : ''
								)
							);
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[installment_amount]',
									'label' => __( 'Amount', 'wcgmcf' ),
									'description' => __( 'Amount of installments to pay for an item', 'wcgmcf' ),
									'value' => isset( $options['installment_amount'] ) ? $options['installment_amount'] : ''
								)
							);
						?>
					</div>
				</div>
				<div id="wc_gmcf_tab_attributes" class="options_group">
					<p class="form-field"><strong><?php _e( 'Additional Attributes', 'wcgmcf' ); ?></strong></p>
					<?php
						// Excluded Destinations.
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[excluded_destination_ps]',
								'label' => __( 'Product Search', 'wcgmcf' ),
								'description' => __( 'Excluded Destinations in Product Search', 'wcgmcf' ),
								'value' => isset( $options['excluded_destination_ps'] ) ? $options['excluded_destination_ps'] : ''
							)
						);
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[excluded_destination_pa]',
								'label' => __( 'Product Ads', 'wcgmcf' ),
								'description' => __( 'Excluded Destinations in Product Ads', 'wcgmcf' ),
								'value' => isset( $options['excluded_destination_pa'] ) ? $options['excluded_destination_pa'] : ''
							)
						);
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[excluded_destination_cs]',
								'label' => __( 'Commerce Search', 'wcgmcf' ),
								'description' => __( 'Excluded Destinations in Commerce Search', 'wcgmcf' ),
								'value' => isset( $options['excluded_destination_cs'] ) ? $options['excluded_destination_cs'] : ''
							)
						);

						// Expiration Date.
						woocommerce_wp_text_input(
							array(
								'id' => 'wc_gmcf[expiration_date]',
								'label' => __( 'Expiration Date', 'wcgmcf' ),
								'description' => __( 'Date that an item will expire. Format type like a <code>YYYY-MM-DD</code>', 'wcgmcf' ),
								'value' => isset( $options['expiration_date'] ) ? $options['expiration_date'] : ''
							)
						);

					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save tab meta.
	 *
	 * @return void
	 */
	function save_tab_options( $post_id ) {
		if ( isset( $_POST['wc_gmcf_active'] ) ) {
			update_post_meta( $post_id, 'wc_gmcf_active', $_POST['wc_gmcf_active'] );
		} else {
			delete_post_meta( $post_id, 'wc_gmcf_active' );
		}

		if ( isset( $_POST['wc_gmcf'] ) ) {
			update_post_meta( $post_id, 'wc_gmcf', $_POST['wc_gmcf'] );
		}
	}

	/**
	 * Add custom feed template page.
	 *
	 * @param string $page_template Template file path.
	 *
	 * @return string               Feed template file path.
	 */
	public function feed_template( $page_template ) {
		if ( is_page( $this->page ) ) {
			$page_template = WOO_GMCF_PATH . 'templates/feed.php';
		}

		return $page_template;
	}
}
