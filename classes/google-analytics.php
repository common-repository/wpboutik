<?php

namespace NF\WPBOUTIK;

use WPB_Abstract_Google_Analytics_JS;
use WPB_Google_Analytics_JS;
use WPB_Google_Gtag_JS;

class Google_Analytics {

	use Singleton;

	/**
	 * Returns the proper class based on Gtag settings.
	 *
	 * @param array $options Options
	 *
	 * @return WPB_Abstract_Google_Analytics_JS
	 */
	protected function get_tracking_instance( $options = array() ) {
		if ( '1' === $this->ga_gtag_enabled ) {
			return WPB_Google_Gtag_JS::get_instance( $options );
		}

		return WPB_Google_Analytics_JS::get_instance( $options );
	}

	public function __construct() {
		$constructor = $this->init_options();
		add_action( 'admin_menu', array( $this, 'add_plugin_submenu' ) );

		// Contains snippets/JS tracking code
		include_once 'analytics/class-wpb-abstract-google-analytics-js.php';
		include_once 'analytics/class-wpb-google-analytics-js.php';
		include_once 'analytics/class-wpb-google-gtag-js.php';
		$this->get_tracking_instance( $constructor );

		// Admin Options
		/*add_filter( 'woocommerce_tracker_data', array( $this, 'track_options' ) );
		add_action( 'woocommerce_update_options_integration_google_analytics', array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_integration_google_analytics', array( $this, 'show_options_info' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets' ) );*/

		// Tracking code
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_tracking_code' ), 9 );
		add_filter( 'script_loader_tag', array( $this, 'async_script_loader_tags' ), 10, 3 );

		// Event tracking code
		add_action( 'wpboutik_after_add_to_cart_button', array( $this, 'add_to_cart' ) );
		add_action( 'wp_footer', array( $this, 'loop_add_to_cart' ) );
		add_action( 'wpboutik_after_cart', array( $this, 'remove_from_cart' ) );
		add_action( 'wpboutik_after_shop_loop_item', array( $this, 'listing_impression' ), 10, 2 );
		add_action( 'wpboutik_after_shop_loop_item', array( $this, 'listing_click' ), 10, 2 );
		add_action( 'wpboutik_after_single_product', array( $this, 'product_detail' ) );
		add_action( 'wpboutik_after_checkout_form', array( $this, 'checkout_process' ) );

		// utm_nooverride parameter for Google AdWords
		add_filter( 'wpboutik_get_return_url', array( $this, 'utm_nooverride' ) );
	}

	/**
	 * Loads all of our options for this plugin (stored as properties as well)
	 *
	 * @return array An array of options that can be passed to other classes
	 */
	public function init_options() {
		$options = array(
			'ga_id',
			'ga_set_domain_name',
			'ga_gtag_enabled',
			'ga_standard_tracking_enabled',
			'ga_support_display_advertising',
			'ga_support_enhanced_link_attribution',
			'ga_anonymize_enabled',
			'ga_404_tracking_enabled',
			'ga_enhanced_ecommerce_tracking_enabled',
			'ga_enhanced_remove_from_cart_enabled',
			'ga_enhanced_product_impression_enabled',
			'ga_enhanced_product_click_enabled',
			'ga_enhanced_checkout_process_enabled',
			'ga_enhanced_product_detail_view_enabled',
			'ga_event_tracking_enabled',
			'ga_linker_cross_domains',
			'ga_linker_allow_incoming_enabled',
		);

		$constructor                       = array();
		$wpboutik_options_google_analytics = get_option( 'wpboutik_options_google_analytics' );
		foreach ( $options as $option ) {
			$constructor[ $option ] = $this->$option = $wpboutik_options_google_analytics[ $option ];
		}

		return $constructor;
	}

	/**
	 * Add sub page
	 *
	 * @return void
	 * @since 1.0
	 * @see admin_menu
	 *
	 */
	public function add_plugin_submenu() {
		add_submenu_page( 'wpboutik-settings', 'Google Analytics', 'Google Analytics', 'manage_options', 'wpboutik-google-analytics', array(
			$this,
			'wpboutik_plugin_settings_google_analytics_page'
		) );
	}

	public function wpboutik_plugin_settings_google_analytics_page() {
		$options_available = array(
			'ga_id'              => array(
				'title'       => __( 'Google Analytics Tracking ID', 'wpboutik-google-analytics-integration' ),
				'description' => __( 'Log into your Google Analytics account to find your ID. e.g. <code>GT-XXXXX</code> or <code>G-XXXXX</code>', 'wpboutik-google-analytics-integration' ),
				'type'        => 'text',
				'placeholder' => 'GT-XXXXX',
			),
			'ga_set_domain_name' => array(
				'title'       => __( 'Set Domain Name', 'wpboutik-google-analytics-integration' ),
				/* translators: Read more link */
				'description' => sprintf( __( '(Optional) Sets the <code>_setDomainName</code> variable. %1$sSee here for more information%2$s.', 'wpboutik-google-analytics-integration' ), '<a href="https://developers.google.com/analytics/devguides/collection/gajs/gaTrackingSite#multipleDomains" target="_blank">', '</a>' ),
				'type'        => 'text',
				'default'     => '',
				'class'       => 'legacy-setting',
			),

			'ga_gtag_enabled'                        => array(
				'title'         => __( 'Tracking Options', 'wpboutik-google-analytics-integration' ),
				'label'         => __( 'Use Global Site Tag', 'wpboutik-google-analytics-integration' ),
				/* translators: Read more link */
				'description'   => sprintf( __( 'The Global Site Tag provides streamlined tagging across Google’s site measurement, conversion tracking, and remarketing products. This must be enabled to use a Google Analytics 4 Measurement ID (e.g., <code>G-XXXXX</code> or <code>GT-XXXXX</code>). %1$sSee here for more information%2$s.', 'wpboutik-google-analytics-integration' ), '<a href="https://support.google.com/analytics/answer/7475631?hl=en" target="_blank">', '</a>' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'default'       => 'yes'
			),
			'ga_standard_tracking_enabled'           => array(
				'label'         => __( 'Enable Standard Tracking', 'wpboutik-google-analytics-integration' ),
				'description'   => __( 'This tracks session data such as demographics, system, etc. You don\'t need to enable this if you are using a 3rd party Google analytics plugin.', 'wpboutik-google-analytics-integration' ),
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
			),
			'ga_support_display_advertising'         => array(
				'label'         => __( '"Display Advertising" Support', 'wpboutik-google-analytics-integration' ),
				/* translators: Read more link */
				'description'   => sprintf( __( 'Set the Google Analytics code to support Display Advertising. %1$sRead more about Display Advertising%2$s.', 'wpboutik-google-analytics-integration' ), '<a href="https://support.google.com/analytics/answer/2700409" target="_blank">', '</a>' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
			),
			'ga_support_enhanced_link_attribution'   => array(
				'label'         => __( 'Use Enhanced Link Attribution', 'wpboutik-google-analytics-integration' ),
				/* translators: Read more link */
				'description'   => sprintf( __( 'Set the Google Analytics code to support Enhanced Link Attribution. %1$sRead more about Enhanced Link Attribution%2$s.', 'wpboutik-google-analytics-integration' ), '<a href="https://support.google.com/analytics/answer/7377126?hl=en" target="_blank">', '</a>' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
			),
			'ga_anonymize_enabled'                   => array(
				'label'         => __( 'Anonymize IP addresses', 'wpboutik-google-analytics-integration' ),
				/* translators: Read more link */
				'description'   => sprintf( __( 'Enabling this option is mandatory in certain countries due to national privacy laws. %1$sRead more about IP Anonymization%2$s.', 'wpboutik-google-analytics-integration' ), '<a href="https://support.google.com/analytics/answer/2763052" target="_blank">', '</a>' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'default'       => 'yes',
			),
			'ga_404_tracking_enabled'                => array(
				'label'         => __( 'Track 404 (Not found) Errors', 'wpboutik-google-analytics-integration' ),
				/* translators: Read more link */
				'description'   => sprintf( __( 'Enable this to find broken or dead links. An "Event" with category "Error" and action "404 Not Found" will be created in Google Analytics for each incoming pageview to a non-existing page. By setting up a "Custom Goal" for these events within Google Analytics you can find out where broken links originated from (the referrer). %1$sRead how to set up a goal%2$s.', 'wpboutik-google-analytics-integration' ), '<a href="https://support.google.com/analytics/answer/1032415" target="_blank">', '</a>' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'default'       => 'yes',
			),
			'ga_event_tracking_enabled'              => array(
				'label'         => __( 'Add to Cart Events', 'wpboutik-google-analytics-integration' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'default'       => 'yes',
			),
			'ga_linker_cross_domains'                => array(
				'title'       => __( 'Cross Domain Tracking', 'wpboutik-google-analytics-integration' ),
				/* translators: Read more link */
				'description' => sprintf( __( 'Add a comma separated list of domains for automatic linking. %1$sRead more about Cross Domain Measurement%2$s', 'wpboutik-google-analytics-integration' ), '<a href="https://support.google.com/analytics/answer/7476333" target="_blank">', '</a>' ),
				'type'        => 'text',
				'placeholder' => 'example.com, example.net',
				'default'     => '',
			),
			'ga_linker_allow_incoming_enabled'       => array(
				'label'         => __( 'Accept Incoming Linker Parameters', 'wpboutik-google-analytics-integration' ),
				'description'   => __( 'Enabling this option will allow incoming linker parameters from other websites.', 'wpboutik-google-analytics-integration' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'default'       => 'no',
			),
			'ga_enhanced_ecommerce_tracking_enabled' => array(
				'title'         => __( 'Enhanced eCommerce', 'wpboutik-google-analytics-integration' ),
				'label'         => __( 'Enable Enhanced eCommerce ', 'wpboutik-google-analytics-integration' ),
				/* translators: Read more link */
				'description'   => sprintf( __( 'Enhanced eCommerce allows you to measure more user interactions with your store, including: product impressions, product detail views, starting the checkout process, adding cart items, and removing cart items. Global Site Tag must be enabled for Enhanced eCommerce to work. %1$sSee here for more information%2$s.', 'wpboutik-google-analytics-integration' ), '<a href="https://support.google.com/analytics/answer/6032539?hl=en" target="_blank">', '</a>' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'default'       => 'no',
				'class'         => 'legacy-setting',
			),

			// Enhanced eCommerce Sub-Settings

			'ga_enhanced_remove_from_cart_enabled' => array(
				'label'         => __( 'Remove from Cart Events', 'wpboutik-google-analytics-integration' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'default'       => 'yes',
				'class'         => 'enhanced-setting',
			),

			'ga_enhanced_product_impression_enabled' => array(
				'label'         => __( 'Product Impressions from Listing Pages', 'wpboutik-google-analytics-integration' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'default'       => 'yes',
				'class'         => 'enhanced-setting',
			),

			'ga_enhanced_product_click_enabled' => array(
				'label'         => __( 'Product Clicks from Listing Pages', 'wpboutik-google-analytics-integration' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'default'       => 'yes',
				'class'         => 'enhanced-setting',
			),

			'ga_enhanced_product_detail_view_enabled' => array(
				'label'         => __( 'Product Detail Views', 'wpboutik-google-analytics-integration' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'default'       => 'yes',
				'class'         => 'enhanced-setting',
			),

			'ga_enhanced_checkout_process_enabled' => array(
				'label'         => __( 'Checkout Process Initiated', 'wpboutik-google-analytics-integration' ),
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'default'       => 'yes',
				'class'         => 'enhanced-setting',
			),
		);
		$url_form          = wp_nonce_url(
			add_query_arg(
				[
					'action' => 'wpboutik_save_settings_analytics',
					'tab'    => 'analytics',
				],
				admin_url( 'admin-post.php' )
			),
			'wpboutik_save_settings_analytics'
		);

		$options = get_option( 'wpboutik_options_google_analytics' );

		?>

        <div id="wrap-wpboutik">
        <div class="wrap">
        <form method="post" id="mainform" action="<?php echo esc_url( $url_form ); ?>">
            <h3><?php esc_html_e( 'Google Analytics', 'wpboutik' ); ?></h3>
            <p>Google Analytics is a free service offered by Google that generates detailed statistics about the
                visitors to
                a website.</p>
            <hr>
            <table class="form-table">
                <tbody>
				<?php
				foreach ( $options_available as $key => $value ) : ?>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $key ); ?>">
								<?php echo ( isset( $options_available[ $key ]['title'] ) ) ? esc_html( $options_available[ $key ]['title'] ) : ''; ?>
                            </label>
                        </th>
                        <td class="forminp forminp-text">
                            <label for="<?php echo esc_attr( $key ); ?>">
                                <input
                                        name="<?php echo esc_attr( sprintf( '%s[%s]', WPBOUTIK_SLUG, $key ) ); ?>"
                                        id="<?php echo esc_attr( $key ); ?>"
                                        type="<?php echo esc_attr( $options_available[ $key ]['type'] ); ?>"
                                        placeholder="<?php echo ( isset( $options_available[ $key ]['placeholder'] ) ) ? esc_attr( $options_available[ $key ]['placeholder'] ) : ''; ?>"
                                        value="<?php echo ( $options_available[ $key ]['type'] === 'checkbox' ) ? '1' : esc_attr( $options[ $key ] ); ?>"
									<?php echo ( ( isset( $options_available[ $key ]['default'] ) && 'yes' === $options_available[ $key ]['default'] && ! isset( $options[ $key ] ) ) || '1' === $options[ $key ] ) ? 'checked' : ''; ?>
                                />
								<?php echo ( isset( $options_available[ $key ]['label'] ) ) ? esc_html( $options_available[ $key ]['label'] ) : ''; ?>
                            </label>
                            <br/>
							<?php if ( isset( $options_available[ $key ]['description'] ) ) : ?>
                                <p class="description"><?php echo $options_available[ $key ]['description']; //phpcs:ignore ?></p>
							<?php endif; ?>
                        </td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
			<?php
			submit_button(); ?>
            <input type="hidden" name="tab" value="tt">
        </form>
		<?php
	}

	/**
	 * Check if tracking is disabled
	 *
	 * @param string $type The setting to check
	 *
	 * @return bool         True if tracking for a certain setting is disabled
	 */
	private function disable_tracking( $type ) {
		return is_admin() || current_user_can( 'manage_options' ) || ( ! $this->ga_id ) || 'no' === $type || apply_filters( 'wpboutik_ga_disable_tracking', false, $type );
	}

	/**
	 * Determine if the conditions are met for enhanced ecommerce interactions to be displayed.
	 * Currently checks if Global Tags are enabled, plus Enhanced eCommerce.
	 *
	 * @param array $extra_checks Any extra option values that should be 'yes' to proceed
	 *
	 * @return bool                Whether enhanced ecommerce transactions can be displayed.
	 */
	protected function enhanced_ecommerce_enabled( $extra_checks = [] ) {
		if ( ! is_array( $extra_checks ) ) {
			$extra_checks = [ $extra_checks ];
		}

		// False if gtag and UA are disabled.
		if ( $this->disable_tracking( $this->ga_gtag_enabled ) ) {
			return false;
		}

		// False if gtag or UA is enabled, but enhanced ecommerce is disabled.
		if ( $this->disable_tracking( $this->ga_enhanced_ecommerce_tracking_enabled ) ) {
			return false;
		}

		// False if any specified interaction-level checks are disabled.
		foreach ( $extra_checks as $option_value ) {
			if ( $this->disable_tracking( $option_value ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Google Analytics event tracking for single product add to cart
	 */
	public function add_to_cart( $product ) {
		if ( $this->disable_tracking( $this->ga_event_tracking_enabled ) ) {
			return;
		}
		if ( ! is_single() ) {
			return;
		}

		if ( '1' === $this->ga_gtag_enabled ) {
			$this->get_tracking_instance()->add_to_cart( $product );

			return;
		}

		$sku = get_post_meta( $product->ID, 'sku', true );

		// Add single quotes to allow jQuery to be substituted into _trackEvent parameters
		$parameters             = array();
		$parameters['category'] = "'" . __( 'Products', 'wpboutik' ) . "'";
		$parameters['action']   = "'" . __( 'Add to Cart', 'wpboutik' ) . "'";
		$parameters['label']    = "'" . esc_js( $sku ? __( 'ID:', 'wpboutik' ) . ' ' . $sku : '#' . $product->ID ) . "'";

		if ( ! $this->disable_tracking( $this->ga_enhanced_ecommerce_tracking_enabled ) ) {

			$item = '{';

			$variants = get_post_meta( $product->ID, 'variants', true );

			if ( $variants ) {
				$item .= "'id': google_analytics_integration_product_data[ $('input[name=\"variation_id\"]').val() ] !== undefined ? google_analytics_integration_product_data[ $('input[name=\"variation_id\"]').val() ].id : false,";
				$item .= "'variant': google_analytics_integration_product_data[ $('input[name=\"variation_id\"]').val() ] !== undefined ? google_analytics_integration_product_data[ $('input[name=\"variation_id\"]').val() ].variant : false,";
			} else {
				$item .= "'id': '" . $this->get_tracking_instance()->get_product_identifier( $product ) . "',";
			}

			$item .= "'name': '" . esc_js( get_the_title( $product->ID ) ) . "',";
			$item .= "'category': " . $this->get_tracking_instance()->product_get_category_line( $product );
			$item .= "'quantity': $( 'input.qty' ).val() ? $( 'input.qty' ).val() : '1'";
			$item .= '}';

			$parameters['item'] = $item;

			$code                   = $this->get_tracking_instance()->tracker_var() . "( 'ec:addProduct', {$item} );";
			$parameters['enhanced'] = $code;
		}

		$this->get_tracking_instance()->event_tracking_code( $parameters, '.single_add_to_cart_button' );
	}

	/**
	 * Google Analytics event tracking for loop add to cart
	 */
	public function loop_add_to_cart() {
		if ( $this->disable_tracking( $this->ga_event_tracking_enabled ) || '1' === $this->ga_gtag_enabled ) {
			return;
		}

		// Add single quotes to allow jQuery to be substituted into _trackEvent parameters
		$parameters             = array();
		$parameters['category'] = "'" . __( 'Products', 'wpboutik' ) . "'";
		$parameters['action']   = "'" . __( 'Add to Cart', 'wpboutik' ) . "'";
		$parameters['label']    = "($(this).data('product_sku')) ? ($(this).data('product_sku')) : ('#' + $(this).data('product_id'))"; // Product SKU or ID

		if ( ! $this->disable_tracking( $this->ga_enhanced_ecommerce_tracking_enabled ) ) {
			$item               = '{';
			$item               .= "'id': ($(this).data('product_sku')) ? ($(this).data('product_sku')) : ('#' + $(this).data('product_id')),";
			$item               .= "'quantity': $(this).data('quantity')";
			$item               .= '}';
			$parameters['item'] = $item;

			$code                   = $this->get_tracking_instance()->tracker_var() . "( 'ec:addProduct', " . $item . ' );';
			$parameters['enhanced'] = $code;
		}

		$this->get_tracking_instance()->event_tracking_code( $parameters, '.add_to_cart_button:not(.product_type_variable, .product_type_grouped)' );
	}

	/**
	 * Enhanced Analytics event tracking for removing a product from the cart
	 */
	public function remove_from_cart() {
		if ( ! $this->enhanced_ecommerce_enabled( $this->ga_enhanced_remove_from_cart_enabled ) ) {
			return;
		}

		$this->get_tracking_instance()->remove_from_cart();
	}

	/**
	 * Measures a listing impression (from search results)
	 */
	public function listing_impression( $product, $position ) {
		if ( ! $this->enhanced_ecommerce_enabled( $this->ga_enhanced_product_impression_enabled ) ) {
			return;
		}

		$this->get_tracking_instance()->listing_impression( $product, $position );
	}

	/**
	 * Measure a product click from a listing page
	 */
	public function listing_click( $product, $position ) {
		if ( ! $this->enhanced_ecommerce_enabled( $this->ga_enhanced_product_click_enabled ) ) {
			return;
		}

		$this->get_tracking_instance()->listing_click( $product, $position );
	}

	/**
	 * Measure a product detail view
	 */
	public function product_detail( $product ) {
		if ( ! $this->enhanced_ecommerce_enabled( $this->ga_enhanced_product_detail_view_enabled ) ) {
			return;
		}

		$this->get_tracking_instance()->product_detail( $product );
	}

	/**
	 * Tracks when the checkout form is loaded
	 *
	 * @param mixed $checkout (unused)
	 */
	public function checkout_process( $checkout ) {
		if ( ! $this->enhanced_ecommerce_enabled( $this->ga_enhanced_checkout_process_enabled ) ) {
			return;
		}

		$this->get_tracking_instance()->checkout_process( WPB()->cart->get_cart() );
	}

	/**
	 * Add the utm_nooverride parameter to any return urls. This makes sure Google Adwords doesn't mistake the offsite gateway as the referrer.
	 *
	 * @param string $return_url WPBoutik Return URL
	 *
	 * @return string URL
	 */
	public function utm_nooverride( $return_url ) {
		// We don't know if the URL already has the parameter so we should remove it just in case
		$return_url = remove_query_arg( 'utm_nooverride', $return_url );

		// Now add the utm_nooverride query arg to the URL
		$return_url = add_query_arg( 'utm_nooverride', '1', $return_url );

		return esc_url( $return_url, null, 'db' );
	}

	/**
	 * Display the tracking codes
	 * Acts as a controller to figure out which code to display
	 */
	public function enqueue_tracking_code() {
		global $wp;
		$display_ecommerce_tracking = false;

		if ( $this->disable_tracking( 'all' ) ) {
			return;
		}

		// Check if is order received page and stop when the products and not tracked
		if ( is_wpboutik_order_received_page() ) {
			$order_id = isset( $wp->query_vars['order-received'] ) ? $wp->query_vars['order-received'] : 0;
			$order    = wpboutik_get_order( $order_id );

			//&& ! (bool) $order->get_meta( '_ga_tracked' )
			if ( $order ) {
				$display_ecommerce_tracking = true;
				$this->enqueue_ecommerce_tracking_code( $order_id );
			}
		}

		if ( is_wpboutik() || is_page( wpboutik_get_page_id( 'cart' ) ) || ( is_page( wpboutik_get_page_id( 'checkout' ) ) && ! $display_ecommerce_tracking ) ) {
			$display_ecommerce_tracking = true;
			$this->enqueue_standard_tracking_code();
		}

		if ( ! $display_ecommerce_tracking && '1' === $this->ga_standard_tracking_enabled ) {
			$this->enqueue_standard_tracking_code();
		}
	}

	/**
	 * Generate Standard Google Analytics tracking
	 */
	protected function enqueue_standard_tracking_code() {
		$this->get_tracking_instance()->load_opt_out();
		$this->get_tracking_instance()->load_analytics();
	}

	/**
	 * Generate eCommerce tracking code
	 *
	 * @param int $order_id The Order ID for adding a transaction.
	 */
	protected function enqueue_ecommerce_tracking_code( $order_id ) {
		// Get the order and output tracking code.
		$order = wpboutik_get_order( $order_id );

		// Make sure we have a valid order object.
		if ( ! $order ) {
			return;
		}

		// Check order key.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		/*$order_key = empty( $_GET['key'] ) ? '' : wpb_clean( wp_unslash( $_GET['key'] ) );
		if ( ! $order->key_is_valid( $order_key ) ) {
			return;
		}*/

		$this->get_tracking_instance()->add_transaction( $order );
		$this->get_tracking_instance()->load_opt_out();
		$this->get_tracking_instance()->load_analytics();

		// Mark the order as tracked.
		//$order->update_meta_data( '_ga_tracked', 1 );
		//$order->save();
	}

	/**
	 * Add async to script tags with defined handles.
	 *
	 * @param string $tag HTML for the script tag.
	 * @param string $handle Handle of the script.
	 * @param string $src Src of the script.
	 *
	 * @return string
	 */
	public function async_script_loader_tags( $tag, $handle, $src ) {
		if ( ! in_array( $handle, array( 'google-tag-manager' ), true ) ) {
			return $tag;
		}

		return str_replace( '<script src', '<script async src', $tag );
	}
}