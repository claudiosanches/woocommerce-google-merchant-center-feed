<?php
/**
 * Ultimate Google Product Feed class.
 */
class WC_Google_Merchant_Center_Feed_Integration extends WC_Integration {

	/**
	 * Init and hook in the integration.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->id                 = 'google-merchant-center';
		$this->method_title       = __( 'Google Merchant Center', 'woocommerce-google-merchant-center-feed' );
		$this->method_description = __( 'Creates a Feed to integrate with your Google Merchant Center.', 'woocommerce-google-merchant-center-feed' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->items_total  = $this->get_option( 'items_total' );
		$this->category     = $this->get_option( 'category' );
		$this->product_type = $this->get_option( 'product_type' );

		// Save integration options.
		add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );

		// Add write panel tab.
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_tab' ) );

		// Create write panel.
		add_action( 'woocommerce_product_write_panels', array( $this, 'tab_view' ) );

		// Save meta.
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_tab_options' ) );

		// Load scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * Initialise Integration Settings Form Fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'items_total' => array(
				'title'       => __( 'Number of items in the feed', 'woocommerce-google-merchant-center-feed' ),
				'type'        => 'text',
				'description' => __( 'Total number of items that will be displayed in the feed', 'woocommerce-google-merchant-center-feed' ),
				'desc_tip'    => true,
				'default'     => '10'
			),
			'defaults' => array(
				'title'       => __( 'Default Options', 'woocommerce-google-merchant-center-feed' ),
				'type'        => 'title',
				'description' => sprintf( __( 'You need help completing these options? Check the %s.', 'woocommerce-google-merchant-center-feed' ), '<a href="http://support.google.com/merchants/bin/answer.py?answer=188494" target="_blank">' . __( 'Products Feed Specification', 'woocommerce-google-merchant-center-feed' ) . '</a>' )
			),
			'category' => array(
				'title'       => __( 'Default Category', 'woocommerce-google-merchant-center-feed' ),
				'type'        => 'textarea',
				'default'     => ''
			),
			'product_type' => array(
				'title'       => __( 'Default Product Type', 'woocommerce-google-merchant-center-feed' ),
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
			wp_enqueue_script( 'wc-gmcf-metabox', plugins_url( 'assets/js/jquery.wc-gmcf.min.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), null, true );
		}
	}

	/**
	 * Add new tab.
	 */
	public function add_tab() {
		echo '<li class="advanced_tab advanced_options wc_gmcf_tab"><a href="#wc_gmcf_tab">' . __( 'Google Merchant', 'woocommerce-google-merchant-center-feed' ) . '</a></li>';
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
							'label' => __( 'Include in Product Feed?', 'woocommerce-google-merchant-center-feed' ),
							'description' => __( 'Enable this option to include in this product in your Product Feed', 'woocommerce-google-merchant-center-feed' ),
							'value' => isset( $active ) ? $active : ''
						)
					);
				?>
				<p class="form-field"><?php _e( 'You need help completing these options? Check this:', 'woocommerce-google-merchant-center-feed' ); ?> <a href="http://support.google.com/merchants/bin/answer.py?answer=188494" target="_blank"><?php _e( 'Products Feed Specification', 'woocommerce-google-merchant-center-feed' ) ?></a></p>
			</div>
			<div id="wc_gmcf_items">
				<div id="wc_gmcf_tab_basic" class="options_group">
					<p class="form-field"><strong><?php _e( 'Basic Product Information', 'woocommerce-google-merchant-center-feed' ); ?></strong></p>
					<?php
						// Description.
						woocommerce_wp_textarea_input(
							array(
								'id' => 'wc_gmcf[description]',
								'label' => __( 'Description', 'woocommerce-google-merchant-center-feed' ),
								'description' => __( 'Description of the item', 'woocommerce-google-merchant-center-feed' ),
								'value' => isset( $options['description'] ) ? $options['description'] : ''
							)
						);

						// Category.
						woocommerce_wp_textarea_input( array(
							'id' => 'wc_gmcf[category]',
							'label' => __( 'Category', 'woocommerce-google-merchant-center-feed' ),
							'description' => __( '<a href="http://support.google.com/merchants/bin/answer.py?answer=1705911" target="_blank">Google\'s category of the item</a>', 'woocommerce-google-merchant-center-feed' ),
							'value' => isset( $options['category'] ) ? $options['category'] : $this->category
						) );

						// Product Type.
						woocommerce_wp_textarea_input( array(
							'id' => 'wc_gmcf[product_type]',
							'label' => __( 'Product Type', 'woocommerce-google-merchant-center-feed' ),
							'description' => __( 'Your category of the item', 'woocommerce-google-merchant-center-feed' ),
							'value' => isset( $options['product_type'] ) ? $options['product_type'] : $this->product_type
						) );

						// Condition.
						woocommerce_wp_select( array(
							'id' => 'wc_gmcf[condition]',
							'label' => __( 'Condition', 'woocommerce-google-merchant-center-feed' ),
							'description' => __( 'Condition or state of the item', 'woocommerce-google-merchant-center-feed' ),
							'value' => isset( $options['condition'] ) ? $options['condition'] : '',
							'options' => array(
								__( 'new', 'woocommerce-google-merchant-center-feed' ),
								__( 'used', 'woocommerce-google-merchant-center-feed' ),
								__( 'refurbished', 'woocommerce-google-merchant-center-feed' )
							)
						) );
					?>
				</div>
				<div id="wc_gmcf_tab_availability" class="options_group">
					<p class="form-field"><strong><?php _e( 'Availability', 'woocommerce-google-merchant-center-feed' ); ?></strong></p>
					<?php
						// Availability.
						woocommerce_wp_select( array(
							'id' => 'wc_gmcf[availability]',
							'label' => __( 'Availability', 'woocommerce-google-merchant-center-feed' ),
							'description' => __( 'Availability status of the item', 'woocommerce-google-merchant-center-feed' ),
							'value' => isset( $options['availability'] ) ? $options['availability'] : '',
							'options' => array(
								__( 'in stock', 'woocommerce-google-merchant-center-feed' ),
								__( 'available for order', 'woocommerce-google-merchant-center-feed' ),
								__( 'out of stock', 'woocommerce-google-merchant-center-feed' ),
								__( 'preorder', 'woocommerce-google-merchant-center-feed' )
							)
						) );
					?>
				</div>
				<div id="wc_gmcf_tab_unique" class="options_group">
					<p class="form-field"><strong><?php _e( 'Unique Product Identifiers', 'woocommerce-google-merchant-center-feed' ); ?></strong></p>
					<?php
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[active_unique]',
								'label' => __( 'Include in Feed?', 'woocommerce-google-merchant-center-feed' ),
								'description' => __( 'Enable this option to include "Unique Product Identifiers" in Google products Feed', 'woocommerce-google-merchant-center-feed' ),
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
									'label' => __( 'Brand', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'Brand of the item', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['brand'] ) ? $options['brand'] : ''
								)
							);

							// GTIN.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[gtin]',
									'label' => __( 'GTIN', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'Global Trade Item Number (GTIN) of the item', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['gtin'] ) ? $options['gtin'] : ''
								)
							);

							// MPN.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[mpn]',
									'label' => __( 'MPN', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'Manufacturer Part Number (MPN) of the item', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['mpn'] ) ? $options['mpn'] : ''
								)
							);

						?>
					</div>
				</div>
				<div id="wc_gmcf_tab_tax" class="options_group">
					<p class="form-field"><strong><?php _e( 'Tax & Shipping', 'woocommerce-google-merchant-center-feed' ); ?></strong></p>
					<?php
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[active_tax]',
								'label' => __( 'Include in Feed?', 'woocommerce-google-merchant-center-feed' ),
								'description' => __( 'Enable this option to include "Tax & Shipping" in Google products Feed', 'woocommerce-google-merchant-center-feed' ),
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
									'label' => __( 'Tax', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'This attribute is only available in the US. Example: <code>US:CA:8.25:y,US:926*:8.75:y</code>', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['tax'] ) ? $options['tax'] : ''
								)
							);

							// Shipping.
							woocommerce_wp_textarea_input(
								array(
									'id' => 'wc_gmcf[shipping]',
									'label' => __( 'Shipping', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'This attribute provides the specific shipping estimate for the product. Example: <code>US:024*:Ground:6.49 USD,US:MA:Express:13.12 USD</code>', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['shipping'] ) ? $options['shipping'] : ''
								)
							);

							// Shipping Weight.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[shipping_weight]',
									'label' => __( 'Shipping Weight', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'Weight of the item for shipping. Accept only the following units of weight: lb, oz, g, kg. Example: <code>3 kg</code>', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['shipping_weight'] ) ? $options['shipping_weight'] : ''
								)
							);

						?>
					</div>
				</div>
				<div id="wc_gmcf_tab_apparel" class="options_group">
					<p class="form-field"><strong><?php _e( 'Apparel Products', 'woocommerce-google-merchant-center-feed' ); ?></strong></p>
					<?php
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[active_apparel]',
								'label' => __( 'Include in Feed?', 'woocommerce-google-merchant-center-feed' ),
								'description' => __( 'Enable this option to include "Apparel Products" in Google products Feed', 'woocommerce-google-merchant-center-feed' ),
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
									'label' => __( 'Gender', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'Gender of the item', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['gender'] ) ? $options['gender'] : '',
									'options' => array(
										__( 'male', 'woocommerce-google-merchant-center-feed' ),
										__( 'female', 'woocommerce-google-merchant-center-feed' ),
										__( 'unisex', 'woocommerce-google-merchant-center-feed' )
									)
								)
							);

							// Age Group.
							woocommerce_wp_select(
								array(
									'id' => 'wc_gmcf[age_group]',
									'label' => __( 'Age Group', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'Target age group of the item', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['age_group'] ) ? $options['age_group'] : '',
									'options' => array(
										__( 'adult', 'woocommerce-google-merchant-center-feed' ),
										__( 'kids', 'woocommerce-google-merchant-center-feed' )
									)
								)
							);

							// Color.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[color]',
									'label' => __( 'Color', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'Color of the item', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['color'] ) ? $options['color'] : ''
								)
							);

							// Size.
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[size]',
									'label' => __( 'Size', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'Size of the item', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['size'] ) ? $options['size'] : ''
								)
							);
						?>
					</div>
				</div>
				<div id="wc_gmcf_tab_nearby" class="options_group">
					<p class="form-field"><strong><?php _e( 'Nearby Stores (USA and UK only)', 'woocommerce-google-merchant-center-feed' ); ?></strong></p>
					<?php
						// Nearby Stores.
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[online_only]',
								'label' => __( 'Online Only', 'woocommerce-google-merchant-center-feed' ),
								'description' => __( 'Whether an item is available for purchase only online', 'woocommerce-google-merchant-center-feed' ),
								'value' => isset( $options['online_only'] ) ? $options['online_only'] : ''
							)
						);
					?>
				</div>
				<div id="wc_gmcf_tab_installments" class="options_group">
					<p class="form-field"><strong><?php _e( 'Multiple Installments (Brazil Only)', 'woocommerce-google-merchant-center-feed' ); ?></strong></p>
					<?php
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[active_installments]',
								'label' => __( 'Include in Feed?', 'woocommerce-google-merchant-center-feed' ),
								'description' => __( 'Enable this option to include "Installments" in Google products Feed', 'woocommerce-google-merchant-center-feed' ),
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
									'label' => __( 'Number', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'Number of installments to pay for an item', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['installment_months'] ) ? $options['installment_months'] : ''
								)
							);
							woocommerce_wp_text_input(
								array(
									'id' => 'wc_gmcf[installment_amount]',
									'label' => __( 'Amount', 'woocommerce-google-merchant-center-feed' ),
									'description' => __( 'Amount of installments to pay for an item', 'woocommerce-google-merchant-center-feed' ),
									'value' => isset( $options['installment_amount'] ) ? $options['installment_amount'] : ''
								)
							);
						?>
					</div>
				</div>
				<div id="wc_gmcf_tab_attributes" class="options_group">
					<p class="form-field"><strong><?php _e( 'Additional Attributes', 'woocommerce-google-merchant-center-feed' ); ?></strong></p>
					<?php
						// Excluded Destinations.
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[excluded_destination_ps]',
								'label' => __( 'Product Search', 'woocommerce-google-merchant-center-feed' ),
								'description' => __( 'Excluded Destinations in Product Search', 'woocommerce-google-merchant-center-feed' ),
								'value' => isset( $options['excluded_destination_ps'] ) ? $options['excluded_destination_ps'] : ''
							)
						);
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[excluded_destination_pa]',
								'label' => __( 'Product Ads', 'woocommerce-google-merchant-center-feed' ),
								'description' => __( 'Excluded Destinations in Product Ads', 'woocommerce-google-merchant-center-feed' ),
								'value' => isset( $options['excluded_destination_pa'] ) ? $options['excluded_destination_pa'] : ''
							)
						);
						woocommerce_wp_checkbox(
							array(
								'id' => 'wc_gmcf[excluded_destination_cs]',
								'label' => __( 'Commerce Search', 'woocommerce-google-merchant-center-feed' ),
								'description' => __( 'Excluded Destinations in Commerce Search', 'woocommerce-google-merchant-center-feed' ),
								'value' => isset( $options['excluded_destination_cs'] ) ? $options['excluded_destination_cs'] : ''
							)
						);

						// Expiration Date.
						woocommerce_wp_text_input(
							array(
								'id' => 'wc_gmcf[expiration_date]',
								'label' => __( 'Expiration Date', 'woocommerce-google-merchant-center-feed' ),
								'description' => __( 'Date that an item will expire. Format type like a <code>YYYY-MM-DD</code>', 'woocommerce-google-merchant-center-feed' ),
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

}
