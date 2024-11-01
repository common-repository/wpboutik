<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPB_Google_Analytics_JS class
 *
 * JS for recording Google Analytics info
 */
class WPB_Google_Analytics_JS extends WPB_Abstract_Google_Analytics_JS {

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
	}

	/**
	 * Returns the tracker variable this integration should use
	 *
	 * @return string
	 */
	public static function tracker_var() {
		return apply_filters( 'wpboutik_ga_tracker_variable', 'ga' );
	}

	/**
	 * Loads the correct Google Analytics code (classic)
	 *
	 * @param boolean $order Classic analytics needs order data to set the currency correctly
	 */
	public static function load_analytics( $order = false ) {
		$logged_in = is_user_logged_in() ? 'yes' : 'no';

		self::load_analytics_classic( $logged_in, $order );
		self::classic_analytics_footer();
	}

	/**
	 * Loads ga.js analytics tracking code
	 *
	 * @param string $logged_in 'yes' if the user is logged in, no if not (this is a string so we can pass it to GA)
	 * @param boolean $order We don't always need to load order data for currency, so we omit
	 *                                 that if false is set, otherwise this is an order object
	 */
	protected static function load_analytics_classic( $logged_in, $order = false ) {
		$anonymize_enabled = '';
		if ( '1' === self::get( 'ga_anonymize_enabled' ) ) {
			$anonymize_enabled = "['_gat._anonymizeIp'],";
		}

		$track_404_enabled = '';
		if ( '1' === self::get( 'ga_404_tracking_enabled' ) && is_404() ) {
			// See https://developers.google.com/analytics/devguides/collection/gajs/methods/gaJSApiEventTracking#_trackevent
			$track_404_enabled = "['_trackEvent', 'Error', '404 Not Found', 'page: ' + document.location.pathname + document.location.search + ' referrer: ' + document.referrer ],";
		}

		$domainname = self::get( 'ga_set_domain_name' );

		if ( ! empty( $domainname ) ) {
			$set_domain_name = "['_setDomainName', '" . esc_js( self::get( 'ga_set_domain_name' ) ) . "'],";
		} else {
			$set_domain_name = '';
		}

		$code = "var _gaq = _gaq || [];
		_gaq.push(
			['_setAccount', '" . esc_js( self::get( 'ga_id' ) ) . "'], " . $set_domain_name .
		        $anonymize_enabled .
		        $track_404_enabled . "
			['_setCustomVar', 1, 'logged-in', '" . esc_js( $logged_in ) . "', 1],
			['_trackPageview']";

		if ( false !== $order ) {
			$code .= ",['_set', 'currencyCode', '" . esc_js( $order->get_currency() ) . "']";
		}

		$code .= ');';

		self::load_analytics_code_in_header( apply_filters( 'wpboutik_ga_classic_snippet_output', $code ) );
	}

	/**
	 * Enqueues JavaScript to build the addImpression object
	 *
	 * @param $product
	 * @param int $position
	 */
	public static function listing_impression( $product, $position ) {
		if ( is_search() ) {
			$list = 'Search Results';
		} else {
			$list = 'Product List';
		}

		$name = get_the_title( $product->ID );

		wpb_enqueue_js(
			self::tracker_var() . "( 'ec:addImpression', {
				'id': '" . esc_js( self::get_product_identifier( $product ) ) . "',
				'name': '" . esc_js( $name ) . "',
				'category': " . self::product_get_category_line( $product ) . "
				'list': '" . esc_js( $list ) . "',
				'position': '" . esc_js( $position ) . "'
			} );
		"
		);
	}

	/**
	 * Enqueues JavaScript to build an addProduct and click object
	 *
	 * @param $product
	 * @param int $position
	 */
	public static function listing_click( $product, $position ) {
		if ( is_search() ) {
			$list = 'Search Results';
		} else {
			$list = 'Product List';
		}

		$name = get_the_title( $product->ID );

		wpb_enqueue_js(
			"
			$( '.products .post-" . esc_js( $product->ID ) . " a' ).on( 'click', function() {
				if ( true === $(this).hasClass( 'add_to_cart_button' ) ) {
					return;
				}

				" . self::tracker_var() . "( 'ec:addProduct', {
					'id': '" . esc_js( self::get_product_identifier( $product ) ) . "',
					'name': '" . esc_js( $name ) . "',
					'category': " . self::product_get_category_line( $product ) . "
					'position': '" . esc_js( $position ) . "'
				});

				" . self::tracker_var() . "( 'ec:setAction', 'click', { list: '" . esc_js( $list ) . "' });
				" . self::tracker_var() . "( 'send', 'event', 'UX', 'click', ' " . esc_js( $list ) . "' );
			});
		"
		);
	}

	/**
	 * Loads in the footer
	 *
	 * @see wp_footer
	 */
	public static function classic_analytics_footer() {
		if ( '1' === self::get( 'ga_support_display_advertising' ) ) {
			$ga_url = "('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js'";
		} else {
			$ga_url = "('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'";
		}

		$code = "(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = " . $ga_url . ";
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();";

		wpb_enqueue_js( $code );
	}

	/**
	 * Generate code used to pass transaction data to Google Analytics.
	 *
	 * @param  $order Object.
	 */
	public function add_transaction( $order ) {
		$transaction_code = self::add_transaction_classic( $order );

		// Check localStorage to avoid duplicate transactions if page is reloaded without hitting server.
		$code = "
			var ga_orders = [];
			try {
				ga_orders = localStorage.getItem( 'ga_orders' );
				ga_orders = ga_orders ? JSON.parse( ga_orders ) : [];
			} catch {}
			if ( -1 === ga_orders.indexOf( '" . esc_js( $order->get_order_number() ) . "' ) ) {
				" . $transaction_code . "
				try {
					ga_orders.push( '" . esc_js( $order->get_order_number() ) . "' );
					localStorage.setItem( 'ga_orders', JSON.stringify( ga_orders ) );
				} catch {}
			}";

		wpb_enqueue_js( $code );
	}

	/**
	 * Transaction tracking for ga.js (classic)
	 *
	 * @param  $order Object
	 *
	 * @return string          Add Transaction Code
	 */
	protected function add_transaction_classic( $order ) {
		$code = "_gaq.push(['_addTrans',
			'" . esc_js( $order->get_order_number() ) . "', 	// order ID - required
			'" . esc_js( get_bloginfo( 'name' ) ) . "',  		// affiliation or store name
			'" . esc_js( $order->get_total() ) . "',   	    	// total - required
			'" . esc_js( $order->get_total_tax() ) . "',    	// tax
			'" . esc_js( $order->get_total_shipping() ) . "',	// shipping
			'" . esc_js( $order->billing_city ) . "',       	// city
			'" . esc_js( $order->billing_state ) . "',      	// state or province
			'" . esc_js( $order->billing_country ) . "'     	// country
		]);";

		// Order items
		if ( $order->get_items() ) {
			foreach ( $order->get_items() as $item ) {
				$code .= self::add_item_classic( $order, $item );
			}
		}

		$code .= "_gaq.push(['_trackTrans']);";

		return $code;
	}

	/**
	 * Generate Universal Analytics Enhanced Ecommerce transaction tracking code
	 *
	 * @param  $order
	 *
	 * @return string
	 */
	protected function add_transaction_enhanced( $order ) {
		$code = self::tracker_var() . "( 'set', '&cu', '" . esc_js( $order->get_currency() ) . "' );";

		// Order items
		if ( $order->get_items() ) {
			foreach ( $order->get_items() as $item ) {
				$code .= self::add_item_enhanced( $order, $item );
			}
		}

		$code .= self::tracker_var() . "( 'ec:setAction', 'purchase', {
			'id': '" . esc_js( $order->get_order_number() ) . "',
			'affiliation': '" . esc_js( get_bloginfo( 'name' ) ) . "',
			'revenue': '" . esc_js( $order->get_total() ) . "',
			'tax': '" . esc_js( $order->get_total_tax() ) . "',
			'shipping': '" . esc_js( $order->get_total_shipping() ) . "'
		} );";

		return $code;
	}

	/**
	 * Add Item (Classic)
	 *
	 * @param  $order Object
	 * @param array $item The item to add to a transaction/order
	 *
	 * @return string
	 */
	protected function add_item_classic( $order, $item ) {
		$_product = $item->get_product();

		$sku = get_post_meta( $_product->id, 'sku', true );

		$code = "_gaq.push(['_addItem',";
		$code .= "'" . esc_js( $order->get_order_number() ) . "',";
		$code .= "'" . esc_js( $sku ? $sku : $_product->get_id() ) . "',";
		$code .= "'" . esc_js( $item['name'] ) . "',";
		$code .= self::product_get_category_line( $_product );
		$code .= "'" . esc_js( $order->get_item_total( $item ) ) . "',";
		$code .= "'" . esc_js( $item['qty'] ) . "'";
		$code .= ']);';

		return $code;
	}

	/**
	 * Add Item (Enhanced)
	 *
	 * @param  $order Object
	 * @param  $item The item to add to a transaction/order
	 *
	 * @return string
	 */
	protected function add_item_enhanced( $order, $item ) {
		$_product = $item->get_product();
		$variant  = self::product_get_variant_line( $_product );

		$sku = get_post_meta( $_product->id, 'sku', true );

		$code = self::tracker_var() . "( 'ec:addProduct', {";
		$code .= "'id': '" . esc_js( $sku ? $sku : $_product->get_id() ) . "',";
		$code .= "'name': '" . esc_js( $item['name'] ) . "',";
		$code .= "'category': " . self::product_get_category_line( $_product );

		if ( '' !== $variant ) {
			$code .= "'variant': " . $variant;
		}

		$code .= "'price': '" . esc_js( $order->get_item_total( $item ) ) . "',";
		$code .= "'quantity': '" . esc_js( $item['qty'] ) . "'";
		$code .= '});';

		return $code;
	}

	/**
	 * Output JavaScript to track an enhanced ecommerce remove from cart action
	 */
	public function remove_from_cart() {
		echo( "
			<script>
			(function($) {
				$( document.body ).off('click', '.remove').on( 'click', '.remove', function() {
					" . esc_js( self::tracker_var() ) . "( 'ec:addProduct', {
						'id': ($(this).data('product_sku')) ? ($(this).data('product_sku')) : ('#' + $(this).data('product_id')),
						'quantity': $(this).parent().parent().find( '.qty' ).val() ? $(this).parent().parent().find( '.qty' ).val() : '1',
					} );
					" . esc_js( self::tracker_var() ) . "( 'ec:setAction', 'remove' );
					" . esc_js( self::tracker_var() ) . "( 'send', 'event', 'UX', 'click', 'remove from cart' );
				});
			})(jQuery);
			</script>
		" );
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

		$sku   = get_post_meta( $product->ID, 'sku', true );
		$price = get_post_meta( $product->ID, 'price', true );
		$name  = get_the_title( $product->ID );

		wpb_enqueue_js(
			self::tracker_var() . "( 'ec:addProduct', {
				'id': '" . esc_js( $sku ? $sku : ( '#' . $product->ID ) ) . "',
				'name': '" . esc_js( $name ) . "',
				'category': " . self::product_get_category_line( $product ) . "
				'price': '" . esc_js( $price ) . "',
			} );

			" . self::tracker_var() . "( 'ec:setAction', 'detail' );"
		);
	}

	/**
	 * Enqueue JS to track when the checkout process is started
	 *
	 * @param array $products items/contents of the cart
	 */
	public function checkout_process( $products ) {
		$code = '';

		foreach ( $products as $cart_item_key => $stored_product ) {
			$stored_product = apply_filters( 'wpboutik_cart_item_product', $stored_product );
			if ( $stored_product->variation_id != "0" ) {
				$variants  = get_post_meta( $stored_product->id, 'variants', true );
				$variation = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );
				$price     = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;
				$name      = $stored_product->name;
				$sku       = $variation->sku;
			} else {
				$price = get_post_meta( $stored_product->id, 'price', true );
				$name  = get_the_title( $stored_product->id );
				$sku   = get_post_meta( $stored_product->id, 'sku', true );
			}

			$code .= self::tracker_var() . "( 'ec:addProduct', {
				'id': '" . esc_js( $sku ? $sku : ( '#' . $stored_product->id ) ) . "',
				'name': '" . esc_js( $name ) . "',
				'category': " . self::product_get_category_line( $stored_product );

			if ( '' !== $variation ) {
				$code .= "'variant': " . $variation;
			}

			$code .= "'price': '" . esc_js( $price ) . "',
				'quantity': '" . esc_js( $stored_product->quantity ) . "'
			} );";
		}

		$code .= self::tracker_var() . "( 'ec:setAction','checkout' );";
		wpb_enqueue_js( $code );
	}

	/**
	 * Enqueue JavaScript for Add to cart tracking
	 *
	 * @param array $parameters associative array of _trackEvent parameters
	 * @param string $selector jQuery selector for binding click event
	 */
	public function event_tracking_code( $parameters, $selector ) {
		$parameters = apply_filters( 'wpboutik_ga_event_tracking_parameters', $parameters );

		$track_event = "_gaq.push(['_trackEvent', %s, %s, %s]);";

		wpb_enqueue_js(
			"
			$( '" . $selector . "' ).on( 'click', function() {
				" . sprintf( $track_event, $parameters['category'], $parameters['action'], $parameters['label'] ) . '
			});
		'
		);
	}

	/**
	 * Loads a code using the google-analytics handler in the head.
	 *
	 * @param string $code The code to add attached to the google-analytics handler
	 */
	protected static function load_analytics_code_in_header( $code ) {
		wp_register_script( 'google-analytics', '', array( 'google-analytics-opt-out' ), null, false );
		wp_add_inline_script( 'google-analytics', $code );
		wp_enqueue_script( 'google-analytics' );
	}

}
