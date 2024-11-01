<?php
/**
 * Cart session handling class.
 */

namespace NF\WPBOUTIK;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPB_Cart_Session class.
 */
final class WPB_Cart_Session {

	/**
	 * Reference to cart object.
	 */
	protected $cart;

	/**
	 * Sets up the items provided, and calculate totals.
	 *
	 * @param WPB_Cart $cart Cart object to calculate totals for.
	 *
	 * @throws \Exception If missing WPB_Cart object.
	 */
	public function __construct( $cart ) {
		if ( ! is_a( $cart, '\NF\WPBOUTIK\WPB_Cart' ) ) {
			throw new \Exception( 'A valid WPB_Cart object is required' );
		}

		$this->set_cart( $cart );
	}

	/**
	 * Sets the cart instance.
	 *
	 * @param WPB_Cart $cart Cart object.
	 */
	public function set_cart( WPB_Cart $cart ) {
		$this->cart = $cart;
	}


	/**
	 * Register methods for this object on the appropriate WordPress hooks.
	 */
	public function init() {
		/**
		 * Filters whether hooks should be initialized for the current cart session.
		 *
		 * @param bool $must_initialize Will be passed as true, meaning that the cart hooks should be initialized.
		 * @param bool $session The WPB_Cart_Session object that is being initialized.
		 *
		 * @returns bool True if the cart hooks should be actually initialized, false if not.
		 */
		if ( ! apply_filters( 'wpboutik_cart_session_initialize', true, $this ) ) {
			return;
		}

		add_action( 'wp_loaded', array( $this, 'get_cart_from_session' ) );
		add_action( 'wpboutik_cart_emptied', array( $this, 'destroy_cart_session' ) );
		add_action( 'wpboutik_after_calculate_totals', array( $this, 'set_session' ), 1000 );
		add_action( 'wpboutik_cart_loaded_from_session', array( $this, 'set_session' ) );
		add_action( 'wpboutik_removed_coupon', array( $this, 'set_session' ) );

		// Persistent cart stored to usermeta.
		add_action( 'wpboutik_add_to_cart', array( $this, 'persistent_cart_update' ) );
		add_action( 'wpboutik_cart_item_removed', array( $this, 'persistent_cart_update' ) );
		add_action( 'wpboutik_cart_item_restored', array( $this, 'persistent_cart_update' ) );
		add_action( 'wpboutik_cart_item_set_quantity', array( $this, 'persistent_cart_update' ) );

		// Cookie events - cart cookies need to be set before headers are sent.
		add_action( 'wpboutik_add_to_cart', array( $this, 'maybe_set_cart_cookies' ) );
		add_action( 'wp', array( $this, 'maybe_set_cart_cookies' ), 99 );
		add_action( 'shutdown', array( $this, 'maybe_set_cart_cookies' ), 0 );
	}

	/**
	 * Get the cart data from the PHP session and store it in class variables.
	 */
	public function get_cart_from_session() {
		do_action( 'wpboutik_load_cart_from_session' );
		$this->cart->set_totals( WPB()->session->get( 'cart_totals', null ) );
		/*$this->cart->set_applied_coupons( WPB()->session->get( 'applied_coupons', array() ) );
		$this->cart->set_coupon_discount_totals( WPB()->session->get( 'coupon_discount_totals', array() ) );
		$this->cart->set_coupon_discount_tax_totals( WPB()->session->get( 'coupon_discount_tax_totals', array() ) );
		$this->cart->set_removed_cart_contents( WPB()->session->get( 'removed_cart_contents', array() ) );*/

		$update_cart_session = false; // Flag to indicate the stored cart should be updated.
		$order_again         = false; // Flag to indicate whether this is a re-order.
		$cart                = WPB()->session->get( 'cart', null );
		$merge_saved_cart    = (bool) get_user_meta( get_current_user_id(), '_wpboutik_load_saved_cart_after_login', true );

		// Merge saved cart with current cart.
		if ( is_null( $cart ) || $merge_saved_cart ) {
			$saved_cart          = $this->get_saved_cart();
			$cart                = is_null( $cart ) ? array() : $cart;
			$cart                = array_merge( $saved_cart, $cart );
			$update_cart_session = true;

			delete_user_meta( get_current_user_id(), '_wpboutik_load_saved_cart_after_login' );
		}

		// Populate cart from order.
		if ( isset( $_GET['order_again'], $_GET['_wpnonce'] ) && is_user_logged_in() && wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'wpboutk-order_again' ) ) { // WPCS: input var ok, sanitization ok.
			$cart        = $this->populate_cart_from_order( absint( $_GET['order_again'] ), $cart ); // WPCS: input var ok.
			$order_again = true;
		}

		// Prime caches to reduce future queries.
		if ( is_callable( '_prime_post_caches' ) ) {
			_prime_post_caches( wp_list_pluck( $cart, 'product_id' ) );
		}

		$cart_contents = array();

		foreach ( $cart as $key => $values ) {
			if ( ! is_customize_preview() && 'customize-preview' === $key ) {
				continue;
			}

			/*$product = wc_get_product( $values['variation_id'] ? $values['variation_id'] : $values['product_id'] );

			if ( empty( $product ) || ! $product->exists() || 0 >= $values['quantity'] ) {
				continue;
			}

			/**
			 * Allow 3rd parties to validate this item before it's added to cart and add their own notices.
			 *
			 * @param bool       $remove_cart_item_from_session If true, the item will not be added to the cart. Default: false.
			 * @param string     $key Cart item key.
			 * @param array      $values Cart item values e.g. quantity and product_id.
			 * @param WC_Product $product The product being added to the cart.
			 */
			/*if ( apply_filters( 'woocommerce_pre_remove_cart_item_from_session', false, $key, $values, $product ) ) {
				$update_cart_session = true;
				/**
				 * Fires when cart item is removed from the session.
				 *
				 * @param string     $key Cart item key.
				 * @param array      $values Cart item values e.g. quantity and product_id.
				 * @param WC_Product $product The product being added to the cart.
				 */
			/*do_action( 'woocommerce_remove_cart_item_from_session', $key, $values, $product );

			/**
			 * Allow 3rd parties to override this item's is_purchasable() result with cart item data.
			 *
			 * @param bool       $is_purchasable If false, the item will not be added to the cart. Default: product's is_purchasable() status.
			 * @param string     $key Cart item key.
			 * @param array      $values Cart item values e.g. quantity and product_id.
			 * @param WC_Product $product The product being added to the cart.
			 */
			/*} elseif ( ! apply_filters( 'woocommerce_cart_item_is_purchasable', $product->is_purchasable(), $key, $values, $product ) ) {
				$update_cart_session = true;
				/* translators: %s: product name */
			/*$message = sprintf( __( '%s has been removed from your cart because it can no longer be purchased. Please contact us if you need assistance.', 'woocommerce' ), $product->get_name() );
			/**
			 * Filter message about item removed from the cart.
			 * @param string     $message Message.
			 * @param WC_Product $product Product data.
			 */
			/*$message = apply_filters( 'wpboutik_cart_item_removed_message', $message, $product );
			wc_add_notice( $message, 'error' );
			do_action( 'woocommerce_remove_cart_item_from_session', $key, $values );

		} elseif ( ! empty( $values['data_hash'] ) && ! hash_equals( $values['data_hash'], wpb_get_cart_item_data_hash( $product ) ) ) { // phpcs:ignore PHPCompatibility.PHP.NewFunctions.hash_equalsFound
			$update_cart_session = true;
			/* translators: %1$s: product name. %2$s product permalink */
			/*$message = sprintf( __( '%1$s has been removed from your cart because it has since been modified. You can add it back to your cart <a href="%2$s">here</a>.', 'woocommerce' ), $product->get_name(), $product->get_permalink() );
			$message = apply_filters( 'wpboutik_cart_item_removed_because_modified_message', $message, $product );
			wc_add_notice( $message, 'notice' );
			do_action( 'woocommerce_remove_cart_item_from_session', $key, $values );

		} else {*/
			// Put session data into array. Run through filter so other plugins can load their own session data.
			$session_data = array_merge(
				$values,
			/*array(
				'data' => $product,
			)*/
			);

			$cart_contents[ $key ] = apply_filters( 'wpboutik_get_cart_item_from_session', $session_data, $values, $key );

			// Add to cart right away so the product is visible in wpboutik_get_cart_item_from_session hook.
			$this->cart->set_cart_contents( $cart_contents );
			//}
		}

		// If it's not empty, it's been already populated by the loop above.
		if ( ! empty( $cart_contents ) ) {
			$this->cart->set_cart_contents( apply_filters( 'woocommerce_cart_contents_changed', $cart_contents ) );
		}

		do_action( 'wpboutik_cart_loaded_from_session', $this->cart );

		if ( $update_cart_session || is_null( WPB()->session->get( 'cart_totals', null ) ) ) {
			WPB()->session->set( 'cart', $this->get_cart_for_session() );
			$this->cart->calculate_totals();

			if ( $merge_saved_cart ) {
				$this->persistent_cart_update();
			}
		}

		// If this is a re-order, redirect to the cart page to get rid of the `order_again` query string.
		if ( $order_again ) {
			wp_safe_redirect( wpboutik_get_page_permalink( 'cart' ) );
			exit;
		}
	}

	/**
	 * Destroy cart session data.
	 */
	public function destroy_cart_session() {
		WPB()->session->set( 'cart', null );
		WPB()->session->set( 'cart_totals', null );
		WPB()->session->set( 'applied_coupons', null );
		WPB()->session->set( 'coupon_discount_totals', null );
		WPB()->session->set( 'coupon_discount_tax_totals', null );
		WPB()->session->set( 'removed_cart_contents', null );
		WPB()->session->set( 'order_awaiting_payment', null );
	}

	/**
	 * Will set cart cookies if needed and when possible.
	 */
	public function maybe_set_cart_cookies() {
		if ( ! headers_sent() && did_action( 'wp_loaded' ) ) {
			if ( ! $this->cart->is_empty() ) {
				$this->set_cart_cookies( true );
			} elseif ( isset( $_COOKIE['wpboutik_items_in_cart'] ) ) { // WPCS: input var ok.
				$this->set_cart_cookies( false );
			}
		}
	}

	/**
	 * Sets the php session data for the cart and coupons.
	 */
	public function set_session() {
		WPB()->session->set( 'cart', $this->get_cart_for_session() );
		WPB()->session->set( 'cart_totals', $this->cart->get_totals() );
		WPB()->session->set( 'applied_coupons', $this->cart->get_applied_coupons() );
		//WPB()->session->set( 'coupon_discount_totals', $this->cart->get_coupon_discount_totals() );
		//WPB()->session->set( 'coupon_discount_tax_totals', $this->cart->get_coupon_discount_tax_totals() );
		WPB()->session->set( 'removed_cart_contents', $this->cart->get_removed_cart_contents() );

		do_action( 'wpboutik_cart_updated' );
	}

	/**
	 * Returns the contents of the cart in an array without the 'data' element.
	 *
	 * @return array contents of the cart
	 */
	public function get_cart_for_session() {
		$cart_session = array();
		foreach ( $this->cart->get_cart() as $key => $values ) {
			$cart_session[ $key ] = $values;
			unset( $cart_session[ $key ]['data'] ); // Unset product object.
		}

		return $cart_session;
	}

	/**
	 * Save the persistent cart when the cart is updated.
	 */
	public function persistent_cart_update() {
		if ( get_current_user_id() && apply_filters( 'wpboutik_persistent_cart_enabled', true ) ) {
			update_user_meta(
				get_current_user_id(),
				'_wpboutik_persistent_cart_' . get_current_blog_id(),
				array(
					'cart' => $this->get_cart_for_session(),
				)
			);
		}
	}

	/**
	 * Delete the persistent cart permanently.
	 */
	public function persistent_cart_destroy() {
		if ( get_current_user_id() && apply_filters( 'wpboutik_persistent_cart_enabled', true ) ) {
			delete_user_meta( get_current_user_id(), '_wpboutik_persistent_cart_' . get_current_blog_id() );
		}
	}

	/**
	 * Set cart hash cookie and items in cart if not already set.
	 *
	 * @param bool $set Should cookies be set (true) or unset.
	 */
	private function set_cart_cookies( $set = true ) {
		if ( $set ) {
			$setcookies = array(
				'wpboutik_items_in_cart' => '1',
				'wpboutik_cart_hash'     => WPB()->cart->get_cart_hash(),
			);
			foreach ( $setcookies as $name => $value ) {
				if ( ! isset( $_COOKIE[ $name ] ) || $_COOKIE[ $name ] !== $value ) {
					wpb_setcookie( $name, $value );
				}
			}
		} else {
			$unsetcookies = array(
				'wpboutik_items_in_cart',
				'wpboutik_cart_hash',
			);
			foreach ( $unsetcookies as $name ) {
				if ( isset( $_COOKIE[ $name ] ) ) {
					wpb_setcookie( $name, 0, time() - HOUR_IN_SECONDS );
					unset( $_COOKIE[ $name ] );
				}
			}
		}

		do_action( 'wpboutik_set_cart_cookies', $set );
	}

	/**
	 * Get the persistent cart from the database.
	 *
	 * @return array
	 */
	private function get_saved_cart() {
		$saved_cart = array();

		if ( apply_filters( 'wpboutik_persistent_cart_enabled', true ) ) {
			$saved_cart_meta = get_user_meta( get_current_user_id(), '_wpboutik_persistent_cart_' . get_current_blog_id(), true );

			if ( isset( $saved_cart_meta['cart'] ) ) {
				$saved_cart = array_filter( (array) $saved_cart_meta['cart'] );
			}
		}

		return $saved_cart;
	}

	/**
	 * Get a cart from an order, if user has permission.
	 *
	 * @param int $order_id Order ID to try to load.
	 * @param array $cart Current cart array.
	 *
	 * @return array
	 *
	 */
	private function populate_cart_from_order( $order_id, $cart ) {
		$order = wpboutik_get_order( $order_id );
		$order = $order['order'];

		// || ! current_user_can( 'order_again', $order->get_id() )
		if ( ! $order->id || $order->status !== 'completed' ) {
			return;
		}

		if ( apply_filters( 'wpboutik_empty_cart_when_order_again', true ) ) {
			$cart = array();
		}

		$inital_cart_size = count( $cart );
		$order_items      = $order['products'];

		foreach ( $order_items as $item ) {
			$product_id     = (int) apply_filters( 'wpboutik_add_to_cart_product_id', $item->wp_product_id );
			$quantity       = $item->qty;
			$variation_id   = (int) $item->variation_id;
			$variations     = array(); // Mettre les variations wpboutik
			$cart_item_data = apply_filters( 'wpboutik_order_again_cart_item_data', array(), $item, $order );
			$product        = $item->get_product();
			$product_name   = $item->name;

			if ( ! $product ) {
				continue;
			}

			// Prevent reordering variable products if no selected variation.
			if ( ! $variation_id && $product->is_type( 'variable' ) ) {
				continue;
			}

			// Prevent reordering items specifically out of stock.
			if ( ! $product->is_in_stock() ) {
				continue;
			}

			if ( ! apply_filters( 'wpboutik_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $cart_item_data ) ) {
				continue;
			}

			// Add to cart directly.
			$cart_id = WPB()->cart->generate_cart_id( $product_id, $variation_id, $variations, $cart_item_data );

			//$product_data     = wc_get_product( $variation_id ? $variation_id : $product_id );
			$cart[ $cart_id ] = apply_filters(
				'wpboutik_add_order_again_cart_item',
				array_merge(
					$cart_item_data,
					array(
						'key'          => $cart_id,
						'product_id'   => $product_id,
						'variation_id' => $variation_id,
						'variations'   => $variations,
						'quantity'     => $quantity,
						'product_name' => $product_name,
						//'data'         => $product_data,
						'data_hash'    => wpb_get_cart_item_data_hash( $product_id ),
					)
				),
				$cart_id
			);
		}

		do_action_ref_array( 'wpboutik_ordered_again', array( $order->id, $order_items, &$cart ) );

		$num_items_in_cart           = count( $cart );
		$num_items_in_original_order = count( $order_items );
		$num_items_added             = $num_items_in_cart - $inital_cart_size;

		if ( $num_items_in_original_order > $num_items_added ) {
			/*wc_add_notice(
				sprintf(
					_n(
						'%d item from your previous order is currently unavailable and could not be added to your cart.',
						'%d items from your previous order are currently unavailable and could not be added to your cart.',
						$num_items_in_original_order - $num_items_added,
						'wpboutik'
					),
					$num_items_in_original_order - $num_items_added
				),
				'error'
			);*/
		}

		if ( 0 < $num_items_added ) {
			//wc_add_notice( __( 'The cart has been filled with the items from your previous order.', 'wpboutik' ) );
		}

		return $cart;
	}
}
