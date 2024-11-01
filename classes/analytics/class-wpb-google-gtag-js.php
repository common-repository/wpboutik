<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPB_Google_Gtag_JS class
 *
 * JS for recording Google Gtag info
 */
class WPB_Google_Gtag_JS extends WPB_Abstract_Google_Analytics_JS {

	/** @var string $script_handle Handle for the front end JavaScript file */
	public $script_handle = 'wpboutik-google-analytics-integration';

	/**
	 * Get the class instance
	 *
	 * @param array $options Options
	 *
	 * @return WPB_Abstract_Google_Analytics_JS
	 */
	public static function get_instance( $options = array() ) {
		if ( null === self::$instance ) {
			self::$instance = new self( $options );
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 * Takes our options from the parent class so we can later use them in the JS snippets
	 *
	 * @param array $options Options
	 */
	public function __construct( $options = array() ) {
		self::$options = $options;
		// Setup frontend scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'woocommerce_before_single_product', array( $this, 'setup_frontend_scripts' ) );
	}

	/**
	 * Enqueue the frontend scripts and make formatted variant data available via filter
	 *
	 * @return void
	 */
	public function setup_frontend_scripts() {
		global $product;

		if ( $product instanceof WC_Product_Variable ) {
			// Filter variation data to include formatted strings required for add_to_cart event
			add_filter( 'woocommerce_available_variation', array( $this, 'variant_data' ), 10, 3 );
			// Add default inline product data for add to cart tracking
			wp_enqueue_script( $this->script_handle . '-ga-integration' );
		}
	}

	/**
	 * Register front end JavaScript
	 */
	public function register_scripts() {
		wp_register_script(
			$this->script_handle . '-ga-integration',
			self::get_js_asset_url( 'ga-integration.js' ),
			self::get_js_asset_dependencies( 'ga-integration', [ 'jquery' ] ),
			self::get_js_asset_version( 'ga-integration' ),
			array( 'in_footer' => true )
		);
		wp_enqueue_script(
			$this->script_handle . '-actions',
			self::get_js_asset_url( 'actions.js' ),
			self::get_js_asset_dependencies( 'actions' ),
			self::get_js_asset_version( 'actions' ),
			true
		);
	}

	/**
	 * Get the path to something in the plugin dir.
	 *
	 * @param string $end End of the path.
	 *
	 * @return string
	 */
	public function path( $end = '' ) {
		return untrailingslashit( dirname( __FILE__ ) ) . $end;
	}

	/**
	 * Get the URL to something in the plugin dir.
	 *
	 * @param string $end End of the URL.
	 *
	 * @return string
	 */
	public function url( $end = '' ) {
		return untrailingslashit( plugin_dir_url( plugin_basename( __FILE__ ) ) ) . $end;
	}

	/**
	 * Get the URL to something in the plugin JS assets build dir.
	 *
	 * @param string $end End of the URL.
	 *
	 * @return string
	 */
	public function get_js_asset_url( $end = '' ) {
		return $this->url( '/assets/js/build/' . $end );
	}

	/**
	 * Get the path to something in the plugin JS assets build dir.
	 *
	 * @param string $end End of the path.
	 *
	 * @return string
	 */
	public function get_js_asset_path( $end = '' ) {
		return $this->path( '/assets/js/build/' . $end );
	}

	/**
	 * Gets the asset.php generated file for an asset name.
	 *
	 * @param string $asset_name The name of the asset to get the file from.
	 *
	 * @return array The asset file. Or an empty array if the file doesn't exist.
	 */
	public function get_js_asset_file( $asset_name ) {
		try {
			// Exclusion reason: No reaching any user input
			// nosemgrep audit.php.lang.security.file.inclusion-arg
			//return require $this->get_js_asset_path( $asset_name . '.asset.php' );
		} catch ( Exception $e ) {
			return [];
		}
	}

	/**
	 * Gets the dependencies for an assets based on its asset.php generated file.
	 *
	 * @param string $asset_name The name of the asset to get the dependencies from.
	 * @param array $extra_dependencies Array containing extra dependencies to include in the dependency array.
	 *
	 * @return array The dependencies array. Empty array if no dependencies.
	 */
	public function get_js_asset_dependencies( $asset_name, $extra_dependencies = array() ) {
		$script_assets = $this->get_js_asset_file( $asset_name );
		$dependencies  = $script_assets['dependencies'] ?? [];

		return array_unique( array_merge( $dependencies, $extra_dependencies ) );
	}

	/**
	 * Gets the version for an assets based on its asset.php generated file.
	 *
	 * @param string $asset_name The name of the asset to get the version from.
	 *
	 * @return string|false The version. False in case no version is found.
	 */
	public function get_js_asset_version( $asset_name ) {
		$script_assets = $this->get_js_asset_file( $asset_name );

		return $script_assets['version'] ?? false;
	}

	/**
	 * Returns the tracker variable this integration should use
	 *
	 * @return string
	 */
	public static function tracker_var() {
		return apply_filters( 'wpboutik_gtag_tracker_variable', 'gtag' );
	}

	/**
	 * Add formatted id and variant to variable product data
	 *
	 * @param array $data Data accessible via `found_variation` trigger
	 * @param WC_Product_Variable $product
	 * @param WC_Product_Variation $variation
	 *
	 * @return array
	 */
	public function variant_data( $data, $product, $variation ) {
		$data['google_analytics_integration'] = array(
			'id'      => self::get_product_identifier( $variation ),
			'variant' => substr( self::product_get_variant_line( $variation ), 1, - 2 ),
		);

		return $data;
	}

	/**
	 * Returns Javascript string for Google Analytics events
	 *
	 * @param string $event The type of event
	 * @param array|string $data Event data to be sent. If $data is an array then it will be filtered, escaped, and encoded
	 *
	 * @return string
	 */
	public static function get_event_code( string $event, $data ): string {
		return sprintf( "%s('event', '%s', %s)", self::tracker_var(), esc_js( $event ), ( is_array( $data ) ? self::format_event_data( $data ) : $data ) );
	}

	/**
	 * Escape and encode event data
	 *
	 * @param array $data Event data to processed and formatted
	 *
	 * @return string
	 */
	public static function format_event_data( array $data ): string {
		$data = apply_filters( 'wpboutik_gtag_event_data', $data );

		// Recursively walk through $data array and escape all values that will be used in JS.
		array_walk_recursive(
			$data,
			function ( &$value, $key ) {
				$value = esc_js( $value );
			}
		);

		return wp_json_encode( $data );
	}

	/**
	 * Returns a list of category names the product is atttributed to
	 *
	 * @param  $product Product to generate category line for
	 *
	 * @return string
	 */
	public static function product_get_category_line( $product ) {
		$category_names = array();
		$categories     = get_the_terms( $product->id, 'product_cat' );

		if ( false !== $categories && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $category ) {
				$category_names[] = $category->name;
			}
		}

		return join( '/', $category_names );
	}

	/**
	 * Return list name for event
	 *
	 * @return string
	 */
	public static function get_list_name(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['s'] ) ? __( 'Search Results', 'wpboutik' ) : __( 'Product List', 'wpboutik' );
	}

	/**
	 * Enqueues JavaScript to build the view_item_list event
	 *
	 * @param $product
	 * @param int $position
	 */
	public static function listing_impression( $product, $position ) {
		$event_code = self::get_event_code(
			'view_item_list',
			array(
				'items' => array(
					array(
						'id'            => self::get_product_identifier( $product ),
						'name'          => get_the_title( $product->ID ),
						'category'      => self::product_get_category_line( $product ),
						'list'          => self::get_list_name(),
						'list_position' => $position,
					),
				),
			)
		);

		wpb_enqueue_js( $event_code );
	}

	/**
	 * Enqueues JavaScript for select_content and add_to_cart events for the product archive
	 *
	 * @param $product
	 * @param int $position
	 */
	public static function listing_click( $product, $position ) {
		$items = array(
			'id'            => self::get_product_identifier( $product ),
			'name'          => get_the_title( $product->ID ),
			'category'      => self::product_get_category_line( $product ),
			'list_position' => $position,
			'quantity'      => 1,
		);

		$select_content_event_code = self::get_event_code(
			'select_content',
			array(
				'items' => array( $items ),
			)
		);

		$add_to_cart_event_code = self::get_event_code(
			'add_to_cart',
			array(
				'items' => array( $items ),
			)
		);

		wpb_enqueue_js(
			"
			$( '.products .post-" . esc_js( $product->get_id() ) . " a' ).on('click', function() {
				if ( true === $(this).hasClass( 'add_to_cart_button' ) ) {
					$add_to_cart_event_code
				} else {
					$select_content_event_code
				}
			});"
		);
	}

	/**
	 * Output Javascript to track add_to_cart event on single product page
	 */
	public static function add_to_cart( $product ) {
		$items = array(
			'id'       => self::get_product_identifier( $product ),
			'name'     => $product->get_title(),
			'category' => self::product_get_category_line( $product ),
			'quantity' => 1,
		);

		// Set item data as Javascript variable so that quantity, variant, and ID can be updated before sending the event
		$event_code = '
			const item_data    = ' . self::format_event_data( $items ) . ';
			item_data.quantity = $("input.qty").val() ? $("input.qty").val() : 1;';

		$variants = get_post_meta( $product->ID, 'variants', true );

		if ( ! empty( $variants ) && '[]' != $variants ) {
			// Check the global google_analytics_integration_product_data Javascript variable contains data
			// for the current variation selection and if it does update the item_data to be sent for this event
			$event_code .= "
			const selected_variation = google_analytics_integration_product_data[ $('input[name=\"variation_id\"]').val() ];
			if ( selected_variation !== undefined ) {
				item_data.id       = selected_variation.id;
				item_data.variant  = selected_variation.variant;
			}
			";
		}

		$event_code .= self::get_event_code(
			'add_to_cart',
			'{"items": [item_data]}',
			false
		);

		wpb_enqueue_js(
			"$( '.single_add_to_cart_button' ).on('click', function() {
				$event_code
			});"
		);
	}

	/**
	 * Loads the standard Gtag code
	 *
	 * @param $order Object (not used in this implementation, but mandatory in the abstract class)
	 */
	public static function load_analytics( $order = false ) {
		$logged_in = is_user_logged_in() ? 'yes' : 'no';

		$track_404_enabled = '';
		if ( '1' === self::get( 'ga_404_tracking_enabled' ) && is_404() ) {
			// See https://developers.google.com/analytics/devguides/collection/gtagjs/events for reference
			$track_404_enabled = self::tracker_var() . "( 'event', '404_not_found', { 'event_category':'error', 'event_label':'page: ' + document.location.pathname + document.location.search + ' referrer: ' + document.referrer });";
		}

		$gtag_developer_id = '';
		if ( ! empty( self::DEVELOPER_ID ) ) {
			$gtag_developer_id = self::tracker_var() . "('set', 'developer_id." . self::DEVELOPER_ID . "', true);";
		}

		$gtag_id            = self::get( 'ga_id' );
		$gtag_cross_domains = ! empty( self::get( 'ga_linker_cross_domains' ) ) ? array_map( 'esc_js', explode( ',', self::get( 'ga_linker_cross_domains' ) ) ) : array();
		$gtag_snippet       = '
		window.dataLayer = window.dataLayer || [];
		function ' . self::tracker_var() . '(){dataLayer.push(arguments);}
		' . self::tracker_var() . "('js', new Date());
		$gtag_developer_id

		" . self::tracker_var() . "('config', '" . esc_js( $gtag_id ) . "', {
			'allow_google_signals': " . ( '1' === self::get( 'ga_support_display_advertising' ) ? 'true' : 'false' ) . ",
			'link_attribution': " . ( '1' === self::get( 'ga_support_enhanced_link_attribution' ) ? 'true' : 'false' ) . ",
			'anonymize_ip': " . ( '1' === self::get( 'ga_anonymize_enabled' ) ? 'true' : 'false' ) . ",
			'linker':{
				'domains': " . wp_json_encode( $gtag_cross_domains ) . ",
				'allow_incoming': " . ( '1' === self::get( 'ga_linker_allow_incoming_enabled' ) ? 'true' : 'false' ) . ",
			},
			'custom_map': {
				'dimension1': 'logged_in'
			},
			'logged_in': '$logged_in'
		} );

		$track_404_enabled
		";

		wp_register_script( 'google-tag-manager', 'https://www.googletagmanager.com/gtag/js?id=' . esc_js( $gtag_id ), array( 'google-analytics-opt-out' ), null, array( 'in_footer' => false ) );
		wp_add_inline_script( 'google-tag-manager', apply_filters( 'wpboutik_gtag_snippet', $gtag_snippet ) );
		wp_enqueue_script( 'google-tag-manager' );
	}

	/**
	 * Generate Gtag transaction tracking code
	 *
	 * @param  $order
	 *
	 * @return string
	 */
	public function add_transaction_enhanced( $order ) {
		$event_items = array();
		$order_items = $order->get_items();
		if ( ! empty( $order_items ) ) {
			foreach ( $order_items as $item ) {
				$event_items[] = self::add_item( $order, $item );
			}
		}

		return self::get_event_code(
			'purchase',
			array(
				'transaction_id' => $order->get_order_number(),
				'affiliation'    => get_bloginfo( 'name' ),
				'value'          => $order->get_total(),
				'tax'            => $order->get_total_tax(),
				'shipping'       => $order->get_total_shipping(),
				'currency'       => $order->get_currency(),
				'items'          => $event_items,
			)
		);
	}

	/**
	 * Add Item
	 *
	 * @param $order Object
	 * @param $item  The item to add to a transaction/order
	 */
	public function add_item( $order, $item ) {
		$product = $item->get_product();
		$variant = self::product_get_variant_line( $product );

		$event_item = array(
			'id'       => self::get_product_identifier( $product ),
			'name'     => $item['name'],
			'category' => self::product_get_category_line( $product ),
			'price'    => $order->get_item_total( $item ),
			'quantity' => $item['qty'],
		);

		if ( '' !== $variant ) {
			$event_item['variant'] = $variant;
		}

		return $event_item;
	}

	/**
	 * Output JavaScript to track an enhanced ecommerce remove from cart action
	 */
	public function remove_from_cart() {
		$event_code = self::get_event_code(
			'remove_from_cart',
			'{"items": [{
				"id": $(this).data("product_sku") ? $(this).data("product_sku") : "#" + $(this).data("product_id"),
				"quantity": $(this).parent().parent().find(".qty").val() ? $(this).parent().parent().find(".qty").val() : "1"
			 }]}'
		);

		// To track all the consecutive removals,
		// we listen for clicks on `.woocommerce` container(s),
		// as `.woocommerce-cart-form` and its items are re-rendered on each removal.
		wpb_enqueue_js(
			"const selector = '.woocommerce-cart-form__cart-item .remove';
			$( '.woocommerce' ).off('click', selector).on( 'click', selector, function() {
				$event_code
			});"
		);
	}

	/**
	 * Enqueue JavaScript to track a product detail view
	 *
	 * @param $product
	 */
	public function product_detail( $product ) {
		if ( empty( $product ) ) {
			return;
		}

		$event_code = self::get_event_code(
			'view_item',
			array(
				'items' => array(
					array(
						'id'       => $product->ID,
						'name'     => get_the_title( $product->ID ),
						'category' => self::product_get_category_line( $product ),
						'price'    => get_post_meta( $product->ID, 'price', true ),
					),
				),
			)
		);

		wpb_enqueue_js( $event_code );
	}

	/**
	 * Enqueue JS to track when the checkout process is started
	 *
	 * @param array $products items/contents of the cart
	 */
	public function checkout_process( $products ) {
		$items = array();
		foreach ( $products as $cart_item_key => $stored_product ) {
			$stored_product = apply_filters( 'wpboutik_cart_item_product', $stored_product );
			if ( $stored_product->variation_id != "0" ) {
				$variants  = get_post_meta( $stored_product->id, 'variants', true );
				$variation = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );
				$price            = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;
				$name      = $stored_product->name;
			} else {
				$price = get_post_meta( $stored_product->id, 'price', true );
				$name  = get_the_title( $stored_product->id );
			}

			$item_data = array(
				'id'       => $stored_product->id,
				'name'     => $name,
				'category' => self::product_get_category_line( $stored_product ),
				'price'    => $price,
				'quantity' => $stored_product->quantity,
			);

			if ( $stored_product->variation_id != "0" ) {
				$variants  = get_post_meta( $stored_product->id, 'variants', true );
				$variation = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );
				if ( '' !== $variation ) {
					$item_data['variant'] = $variation;
				}
			}

			$items[] = $item_data;
		}

		$event_code = self::get_event_code(
			'begin_checkout',
			array(
				'items' => $items,
			)
		);

		wpb_enqueue_js( $event_code );
	}

	/**
	 * @param array $parameters Associative array of _trackEvent parameters
	 * @param string $selector jQuery selector for binding click event
	 *
	 * @deprecated x.x.x
	 *
	 * Enqueue JavaScript for Add to cart tracking
	 *
	 */
	public function event_tracking_code( $parameters, $selector ) {
		wc_deprecated_function( 'event_tracking_code', '1.6.0', 'get_event_code' );

		// Called with invalid 'Add to Cart' action, update to sync with Default Google Analytics Event 'add_to_cart'
		$parameters['action']   = '\'add_to_cart\'';
		$parameters['category'] = '\'ecommerce\'';

		$parameters = apply_filters( 'wpboutik_gtag_event_tracking_parameters', $parameters );

		if ( '1' === self::get( 'ga_enhanced_ecommerce_tracking_enabled' ) ) {
			$track_event = sprintf(
				self::tracker_var() . "( 'event', %s, { 'event_category': %s, 'event_label': %s, 'items': [ %s ] } );",
				$parameters['action'],
				$parameters['category'],
				$parameters['label'],
				$parameters['item']
			);
		} else {
			$track_event = sprintf(
				self::tracker_var() . "( 'event', %s, { 'event_category': %s, 'event_label': %s } );",
				$parameters['action'],
				$parameters['category'],
				$parameters['label']
			);
		}

		wpb_enqueue_js(
			"
			$( '" . $selector . "' ).on( 'click', function() {
				" . $track_event . '
			});
		'
		);
	}

}
