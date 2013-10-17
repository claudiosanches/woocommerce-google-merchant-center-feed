<?php
/**
 * Ultimate Google Product Feed class.
 *
 * @since 1.0.0
 */
class WC_Ultimate_Google_Product_Feed extends WC_Integration {

    /**
     * Init and hook in the integration.
     *
     * @return void
     */
    public function __construct() {
        $this->id                 = 'google-product-feed';
        $this->method_title       = __( 'Google Product Feed', 'wcugpf' );
        $this->method_description = __( 'Creates a Feed to integrate with your Google Merchant Center.', 'wcugpf' );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->page         = apply_filters( 'wc_ultimate_google_product_feed_page', sanitize_title( __( 'product-feed', 'wcugpf' ) ) );
        $this->items_total  = $this->get_option( 'items_total' );
        $this->category     = $this->get_option( 'category' );
        $this->product_type = $this->get_option( 'product_type' );

        // Save integration options.
        add_action( 'woocommerce_update_options_integration_google-product-feed', array( $this, 'process_admin_options' ) );

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
                'title'       => __( 'Number of items in the feed', 'wcugpf' ),
                'type'        => 'text',
                'description' => __( 'Total number of items that will be displayed in the feed', 'wcugpf' ),
                'desc_tip'    => true,
                'default'     => '10'
            ),
            'defaults' => array(
                'title'       => __( 'Default Options', 'wcugpf' ),
                'type'        => 'title',
                'description' => sprintf( __( 'You need help completing these options? Check the %s.', 'wcugpf' ), '<a href="http://support.google.com/merchants/bin/answer.py?answer=188494" target="_blank">' . __( 'Products Feed Specification', 'wcugpf' ) . '</a>' )
            ),
            'category' => array(
                'title'       => __( 'Default Category', 'wcugpf' ),
                'type'        => 'textarea',
                'default'     => ''
            ),
            'product_type' => array(
                'title'       => __( 'Default Product Type', 'wcugpf' ),
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

        if ( 'product' === $screen->id )
            wp_enqueue_script( 'wc-ugpf-metabox', WOO_UGPF_URL . '/assets/js/jquery.wc-ugpf.js', array( 'jquery' ), null, true );
    }

    /**
     * Add new tab.
     */
    public function add_tab() {
        echo '<li class="advanced_tab advanced_options wc_ugpf_tab"><a href="#wc_ugpf_tab">' . __( 'Google Merchant', 'wcugpf' ) . '</a></li>';
    }

    /**
     * Tab content.
     */
    public function tab_view() {
        global $post;

        $options = get_post_meta( $post->ID, 'wc_ugpf', true );
        $active = get_post_meta( $post->ID, 'wc_ugpf_active', true );
        ?>
        <div id="wc_ugpf_tab" class="panel woocommerce_options_panel">
            <div id="wc_ugpf_tab_active" class="options_group">
                <?php
                    woocommerce_wp_checkbox(
                        array(
                            'id' => 'wc_ugpf_active',
                            'label' => __( 'Include in Product Feed?', 'wcugpf' ),
                            'description' => __( 'Enable this option to include in this product in your Product Feed', 'wcugpf' ),
                            'value' => isset( $active ) ? $active : ''
                        )
                    );
                ?>
                <p class="form-field"><?php _e( 'You need help completing these options? Check this:', 'wcugpf' ); ?> <a href="http://support.google.com/merchants/bin/answer.py?answer=188494" target="_blank"><?php _e( 'Products Feed Specification', 'wcugpf' ) ?></a></p>
            </div>
            <div id="wc_ugpf_items">
                <div id="wc_ugpf_tab_basic" class="options_group">
                    <p class="form-field"><strong><?php _e( 'Basic Product Information', 'wcugpf' ); ?></strong></p>
                    <?php
                        // Description.
                        woocommerce_wp_textarea_input(
                            array(
                                'id' => 'wc_ugpf[description]',
                                'label' => __( 'Description', 'wcugpf' ),
                                'description' => __( 'Description of the item', 'wcugpf' ),
                                'value' => isset( $options['description'] ) ? $options['description'] : ''
                            )
                        );

                        // Category.
                        woocommerce_wp_textarea_input( array(
                            'id' => 'wc_ugpf[category]',
                            'label' => __( 'Category', 'wcugpf' ),
                            'description' => __( '<a href="http://support.google.com/merchants/bin/answer.py?answer=1705911" target="_blank">Google\'s category of the item</a>', 'wcugpf' ),
                            'value' => isset( $options['category'] ) ? $options['category'] : $this->category
                        ) );

                        // Product Type.
                        woocommerce_wp_textarea_input( array(
                            'id' => 'wc_ugpf[product_type]',
                            'label' => __( 'Product Type', 'wcugpf' ),
                            'description' => __( 'Your category of the item', 'wcugpf' ),
                            'value' => isset( $options['product_type'] ) ? $options['product_type'] : $this->product_type
                        ) );

                        // Condition.
                        woocommerce_wp_select( array(
                            'id' => 'wc_ugpf[condition]',
                            'label' => __( 'Condition', 'wcugpf' ),
                            'description' => __( 'Condition or state of the item', 'wcugpf' ),
                            'value' => isset( $options['condition'] ) ? $options['condition'] : '',
                            'options' => array(
                                __( 'new', 'wcugpf' ),
                                __( 'used', 'wcugpf' ),
                                __( 'refurbished', 'wcugpf' )
                            )
                        ) );
                    ?>
                </div>
                <div id="wc_ugpf_tab_availability" class="options_group">
                    <p class="form-field"><strong><?php _e( 'Availability', 'wcugpf' ); ?></strong></p>
                    <?php
                        // Availability.
                        woocommerce_wp_select( array(
                            'id' => 'wc_ugpf[availability]',
                            'label' => __( 'Availability', 'wcugpf' ),
                            'description' => __( 'Availability status of the item', 'wcugpf' ),
                            'value' => isset( $options['availability'] ) ? $options['availability'] : '',
                            'options' => array(
                                __( 'in stock', 'wcugpf' ),
                                __( 'available for order', 'wcugpf' ),
                                __( 'out of stock', 'wcugpf' ),
                                __( 'preorder', 'wcugpf' )
                            )
                        ) );
                    ?>
                </div>
                <div id="wc_ugpf_tab_unique" class="options_group">
                    <p class="form-field"><strong><?php _e( 'Unique Product Identifiers', 'wcugpf' ); ?></strong></p>
                    <?php
                        woocommerce_wp_checkbox(
                            array(
                                'id' => 'wc_ugpf[active_unique]',
                                'label' => __( 'Include in Feed?', 'wcugpf' ),
                                'description' => __( 'Enable this option to include "Unique Product Identifiers" in Google products Feed', 'wcugpf' ),
                                'value' => isset( $options['active_unique'] ) ? $options['active_unique'] : ''
                            )
                        );
                    ?>
                    <div class="wc_ugp_wrap">
                        <?php
                            // Brand.
                            woocommerce_wp_text_input(
                                array(
                                    'id' => 'wc_ugpf[brand]',
                                    'label' => __( 'Brand', 'wcugpf' ),
                                    'description' => __( 'Brand of the item', 'wcugpf' ),
                                    'value' => isset( $options['brand'] ) ? $options['brand'] : ''
                                )
                            );

                            // GTIN.
                            woocommerce_wp_text_input(
                                array(
                                    'id' => 'wc_ugpf[gtin]',
                                    'label' => __( 'GTIN', 'wcugpf' ),
                                    'description' => __( 'Global Trade Item Number (GTIN) of the item', 'wcugpf' ),
                                    'value' => isset( $options['gtin'] ) ? $options['gtin'] : ''
                                )
                            );

                            // MPN.
                            woocommerce_wp_text_input(
                                array(
                                    'id' => 'wc_ugpf[mpn]',
                                    'label' => __( 'MPN', 'wcugpf' ),
                                    'description' => __( 'Manufacturer Part Number (MPN) of the item', 'wcugpf' ),
                                    'value' => isset( $options['mpn'] ) ? $options['mpn'] : ''
                                )
                            );

                        ?>
                    </div>
                </div>
                <div id="wc_ugpf_tab_tax" class="options_group">
                    <p class="form-field"><strong><?php _e( 'Tax & Shipping', 'wcugpf' ); ?></strong></p>
                    <?php
                        woocommerce_wp_checkbox(
                            array(
                                'id' => 'wc_ugpf[active_tax]',
                                'label' => __( 'Include in Feed?', 'wcugpf' ),
                                'description' => __( 'Enable this option to include "Tax & Shipping" in Google products Feed', 'wcugpf' ),
                                'value' => isset( $options['active_tax'] ) ? $options['active_tax'] : ''
                            )
                        );
                    ?>
                    <div class="wc_ugp_wrap">
                        <?php
                            // Tax.
                            woocommerce_wp_textarea_input(
                                array(
                                    'id' => 'wc_ugpf[tax]',
                                    'label' => __( 'Tax', 'wcugpf' ),
                                    'description' => __( 'This attribute is only available in the US. Example: <code>US:CA:8.25:y,US:926*:8.75:y</code>', 'wcugpf' ),
                                    'value' => isset( $options['tax'] ) ? $options['tax'] : ''
                                )
                            );

                            // Shipping.
                            woocommerce_wp_textarea_input(
                                array(
                                    'id' => 'wc_ugpf[shipping]',
                                    'label' => __( 'Shipping', 'wcugpf' ),
                                    'description' => __( 'This attribute provides the specific shipping estimate for the product. Example: <code>US:024*:Ground:6.49 USD,US:MA:Express:13.12 USD</code>', 'wcugpf' ),
                                    'value' => isset( $options['shipping'] ) ? $options['shipping'] : ''
                                )
                            );

                            // Shipping Weight.
                            woocommerce_wp_text_input(
                                array(
                                    'id' => 'wc_ugpf[shipping_weight]',
                                    'label' => __( 'Shipping Weight', 'wcugpf' ),
                                    'description' => __( 'Weight of the item for shipping. Accept only the following units of weight: lb, oz, g, kg. Example: <code>3 kg</code>', 'wcugpf' ),
                                    'value' => isset( $options['shipping_weight'] ) ? $options['shipping_weight'] : ''
                                )
                            );

                        ?>
                    </div>
                </div>
                <div id="wc_ugpf_tab_apparel" class="options_group">
                    <p class="form-field"><strong><?php _e( 'Apparel Products', 'wcugpf' ); ?></strong></p>
                    <?php
                        woocommerce_wp_checkbox(
                            array(
                                'id' => 'wc_ugpf[active_apparel]',
                                'label' => __( 'Include in Feed?', 'wcugpf' ),
                                'description' => __( 'Enable this option to include "Apparel Products" in Google products Feed', 'wcugpf' ),
                                'value' => isset( $options['active_apparel'] ) ? $options['active_apparel'] : ''
                            )
                        );
                    ?>
                    <div class="wc_ugp_wrap">
                        <?php
                            // Gender.
                            woocommerce_wp_select(
                                array(
                                    'id' => 'wc_ugpf[gender]',
                                    'label' => __( 'Gender', 'wcugpf' ),
                                    'description' => __( 'Gender of the item', 'wcugpf' ),
                                    'value' => isset( $options['gender'] ) ? $options['gender'] : '',
                                    'options' => array(
                                        __( 'male', 'wcugpf' ),
                                        __( 'female', 'wcugpf' ),
                                        __( 'unisex', 'wcugpf' )
                                    )
                                )
                            );

                            // Age Group.
                            woocommerce_wp_select(
                                array(
                                    'id' => 'wc_ugpf[age_group]',
                                    'label' => __( 'Age Group', 'wcugpf' ),
                                    'description' => __( 'Target age group of the item', 'wcugpf' ),
                                    'value' => isset( $options['age_group'] ) ? $options['age_group'] : '',
                                    'options' => array(
                                        __( 'adult', 'wcugpf' ),
                                        __( 'kids', 'wcugpf' )
                                    )
                                )
                            );

                            // Color.
                            woocommerce_wp_text_input(
                                array(
                                    'id' => 'wc_ugpf[color]',
                                    'label' => __( 'Color', 'wcugpf' ),
                                    'description' => __( 'Color of the item', 'wcugpf' ),
                                    'value' => isset( $options['color'] ) ? $options['color'] : ''
                                )
                            );

                            // Size.
                            woocommerce_wp_text_input(
                                array(
                                    'id' => 'wc_ugpf[size]',
                                    'label' => __( 'Size', 'wcugpf' ),
                                    'description' => __( 'Size of the item', 'wcugpf' ),
                                    'value' => isset( $options['size'] ) ? $options['size'] : ''
                                )
                            );
                        ?>
                    </div>
                </div>
                <div id="wc_ugpf_tab_nearby" class="options_group">
                    <p class="form-field"><strong><?php _e( 'Nearby Stores (USA and UK only)', 'wcugpf' ); ?></strong></p>
                    <?php
                        // Nearby Stores.
                        woocommerce_wp_checkbox(
                            array(
                                'id' => 'wc_ugpf[online_only]',
                                'label' => __( 'Online Only', 'wcugpf' ),
                                'description' => __( 'Whether an item is available for purchase only online', 'wcugpf' ),
                                'value' => isset( $options['online_only'] ) ? $options['online_only'] : ''
                            )
                        );
                    ?>
                </div>
                <div id="wc_ugpf_tab_installments" class="options_group">
                    <p class="form-field"><strong><?php _e( 'Multiple Installments (Brazil Only)', 'wcugpf' ); ?></strong></p>
                    <?php
                        woocommerce_wp_checkbox(
                            array(
                                'id' => 'wc_ugpf[active_installments]',
                                'label' => __( 'Include in Feed?', 'wcugpf' ),
                                'description' => __( 'Enable this option to include "Installments" in Google products Feed', 'wcugpf' ),
                                'value' => isset( $options['active_installments'] ) ? $options['active_installments'] : ''
                            )
                        );
                    ?>
                    <div class="wc_ugp_wrap">
                        <?php
                            // Multiple Installments.
                            woocommerce_wp_text_input(
                                array(
                                    'id' => 'wc_ugpf[installment_months]',
                                    'label' => __( 'Number', 'wcugpf' ),
                                    'description' => __( 'Number of installments to pay for an item', 'wcugpf' ),
                                    'value' => isset( $options['installment_months'] ) ? $options['installment_months'] : ''
                                )
                            );
                            woocommerce_wp_text_input(
                                array(
                                    'id' => 'wc_ugpf[installment_amount]',
                                    'label' => __( 'Amount', 'wcugpf' ),
                                    'description' => __( 'Amount of installments to pay for an item', 'wcugpf' ),
                                    'value' => isset( $options['installment_amount'] ) ? $options['installment_amount'] : ''
                                )
                            );
                        ?>
                    </div>
                </div>
                <div id="wc_ugpf_tab_attributes" class="options_group">
                    <p class="form-field"><strong><?php _e( 'Additional Attributes', 'wcugpf' ); ?></strong></p>
                    <?php
                        // Excluded Destinations.
                        woocommerce_wp_checkbox(
                            array(
                                'id' => 'wc_ugpf[excluded_destination_ps]',
                                'label' => __( 'Product Search', 'wcugpf' ),
                                'description' => __( 'Excluded Destinations in Product Search', 'wcugpf' ),
                                'value' => isset( $options['excluded_destination_ps'] ) ? $options['excluded_destination_ps'] : ''
                            )
                        );
                        woocommerce_wp_checkbox(
                            array(
                                'id' => 'wc_ugpf[excluded_destination_pa]',
                                'label' => __( 'Product Ads', 'wcugpf' ),
                                'description' => __( 'Excluded Destinations in Product Ads', 'wcugpf' ),
                                'value' => isset( $options['excluded_destination_pa'] ) ? $options['excluded_destination_pa'] : ''
                            )
                        );
                        woocommerce_wp_checkbox(
                            array(
                                'id' => 'wc_ugpf[excluded_destination_cs]',
                                'label' => __( 'Commerce Search', 'wcugpf' ),
                                'description' => __( 'Excluded Destinations in Commerce Search', 'wcugpf' ),
                                'value' => isset( $options['excluded_destination_cs'] ) ? $options['excluded_destination_cs'] : ''
                            )
                        );

                        // Expiration Date.
                        woocommerce_wp_text_input(
                            array(
                                'id' => 'wc_ugpf[expiration_date]',
                                'label' => __( 'Expiration Date', 'wcugpf' ),
                                'description' => __( 'Date that an item will expire. Format type like a <code>YYYY-MM-DD</code>', 'wcugpf' ),
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
        if ( isset( $_POST['wc_ugpf_active'] ) )
            update_post_meta( $post_id, 'wc_ugpf_active', $_POST['wc_ugpf_active'] );

        if ( isset( $_POST['wc_ugpf'] ) )
            update_post_meta( $post_id, 'wc_ugpf', $_POST['wc_ugpf'] );
    }

    /**
     * Add custom feed template page.
     *
     * @param string $page_template Template file path.
     *
     * @return string               Feed template file path.
     */
    public function feed_template( $page_template ) {
        if ( is_page( $this->page ) )
            $page_template = WOO_UGPF_PATH . 'templates/feed.php';

        return $page_template;
    }
}
