<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPB_Abstract_Google_Analytics_JS class
 *
 * Abstract JS for recording Google Analytics/Gtag info
 */
abstract class WPB_Abstract_Google_Analytics_JS {

	/** @var WPB_Abstract_Google_Analytics_JS $instance Class Instance */
	protected static $instance;

	/** @var array $options Inherited Analytics options */
	protected static $options;

	/** @var string Developer ID */
	public const DEVELOPER_ID = 'dOGY3NW';

	/**
	 * Get the class instance
	 *
	 * @param array $options Options
	 *
	 * @return WPB_Abstract_Google_Analytics_JS
	 */
	abstract public static function get_instance( $options = array() );

	/**
	 * Return one of our options
	 *
	 * @param string $option Key/name for the option
	 *
	 * @return string         Value of the option
	 */
	protected static function get( $option ) {
		return self::$options[ $option ];
	}

	/**
	 * Returns the tracker variable this integration should use
	 *
	 * @return string
	 */
	abstract public static function tracker_var();

	/**
	 * Generic GA snippet for opt out
	 */
	public static function load_opt_out() {
		$code = "
			var gaProperty = '" . esc_js( self::get( 'ga_id' ) ) . "';
			var disableStr = 'ga-disable-' + gaProperty;
			if ( document.cookie.indexOf( disableStr + '=true' ) > -1 ) {
				window[disableStr] = true;
			}
			function gaOptout() {
				document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
				window[disableStr] = true;
			}";

		wp_register_script( 'google-analytics-opt-out', '', array(), null, false );
		wp_add_inline_script( 'google-analytics-opt-out', $code );
		wp_enqueue_script( 'google-analytics-opt-out' );
	}

	/**
	 * Enqueues JavaScript to build the addImpression object
	 *
	 * @param $product
	 * @param int $position
	 */
	abstract public static function listing_impression( $product, $position );

	/**
	 * Enqueues JavaScript to build an addProduct and click object
	 *
	 * @param $product
	 * @param int $position
	 */
	abstract public static function listing_click( $product, $position );

	/**
	 * Loads the correct Google Gtag code (classic)
	 *
	 * @param boolean $order Classic analytics needs order data to set the currency correctly
	 */
	abstract public static function load_analytics( $order = false );

	/**
	 * Generate code used to pass transaction data to Google Analytics.
	 *
	 * @param $order Object
	 */
	public function add_transaction( $order ) {
		if ( '1' === self::get( 'ga_enhanced_ecommerce_tracking_enabled' ) || '1' === self::get( 'ga_gtag_enabled' ) ) {
			wpb_enqueue_js( static::add_transaction_enhanced( $order ) );
		}
	}

	/**
	 * Generate Enhanced eCommerce transaction tracking code
	 *
	 * @param $order object
	 *
	 * @return string          Add Transaction Code
	 */
	abstract protected function add_transaction_enhanced( $order );

	/**
	 * Get item identifier from product data
	 *
	 * @param $product Object
	 *
	 * @return string
	 */
	public static function get_product_identifier( $product ) {
		$sku = get_post_meta( $product->ID, 'sku', true );
		if ( ! empty( $sku ) ) {
			return esc_js( $sku );
		} else {
			return esc_js( '#' . $product->ID );
		}
	}

	/**
	 * Returns a 'category' JSON line based on $product
	 *
	 * @param $_product to pull info for
	 *
	 * @return string                Line of JSON
	 */
	public static function product_get_category_line( $_product ) {
		$out        = [];
		$categories = get_the_terms( $_product->ID, 'wpboutik_product_cat' );
		if ( $categories ) {
			foreach ( $categories as $category ) {
				$out[] = $category->name;
			}
		}

		if ( empty( $out ) ) {
			return "";
		}

		return "'" . esc_js( join( '/', $out ) ) . "',";
	}

	/**
	 * Returns a 'variant' JSON line based on $product
	 *
	 * @param $_product to pull info for
	 *
	 * @return string                Line of JSON
	 */
	public static function product_get_variant_line( $_product ) {
		$out            = '';
		$variants       = get_post_meta( $_product->ID, 'variants', true );
		$variation_data = ( ( ! empty( $variants ) && '[]' != $variants ) ) ? wc_get_product_variation_attributes( $_product->get_id() ) : false;

		if ( is_array( $variation_data ) && ! empty( $variation_data ) ) {
			$out = "'" . esc_js( wc_get_formatted_variation( $variation_data, true ) ) . "',";
		}

		return $out;
	}

	/**
	 * Echo JavaScript to track an enhanced ecommerce remove from cart action
	 */
	abstract public function remove_from_cart();

	/**
	 * Enqueue JavaScript to track a product detail view
	 *
	 * @param $product
	 */
	abstract public function product_detail( $product );

	/**
	 * Enqueue JS to track when the checkout process is started
	 *
	 * @param array $cart items/contents of the cart
	 */
	abstract public function checkout_process( $cart );

	/**
	 * Enqueue JavaScript for Add to cart tracking
	 *
	 * @param array $parameters associative array of _trackEvent parameters
	 * @param string $selector jQuery selector for binding click event
	 */
	abstract public function event_tracking_code( $parameters, $selector );
}
