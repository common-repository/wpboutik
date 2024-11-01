<?php
/**
 * Cart totals calculation class.
 *
 * Methods are protected and class is final to keep this as an internal API.
 * May be opened in the future once structure is stable.
 *
 * Rounding guide:
 * - if something is being stored e.g. item total, store unrounded. This is so taxes can be recalculated later accurately.
 * - if calculating a total, round (if settings allow).
 */

namespace NF\WPBOUTIK;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPB_Cart_Totals class.
 */
final class WPB_Cart_Totals {
	use WPB_Item_Totals;

	/**
	 * Reference to cart object.
	 *
	 * @var WPB_Cart
	 */
	protected $cart;

	/**
	 * Reference to customer object.
	 * @var array
	 */
	protected $customer;

	/**
	 * Line items to calculate.
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * Fees to calculate.
	 *
	 * @var array
	 */
	protected $fees = array();

	/**
	 * Shipping costs.
	 *
	 * @var array
	 */
	protected $shipping = array();

	/**
	 * Applied coupon objects.
	 *
	 * @var array
	 */
	protected $coupons = array();

	/**
	 * Item/coupon discount totals.
	 *
	 * @var array
	 */
	protected $coupon_discount_totals = array();

	/**
	 * Item/coupon discount tax totals.
	 *
	 * @var array
	 */
	protected $coupon_discount_tax_totals = array();

	/**
	 * Should taxes be calculated?
	 *
	 * @var boolean
	 */
	protected $calculate_tax = true;

	/**
	 * Stores totals.
	 *
	 * @var array
	 */
	protected $totals = array(
		'fees_total'         => 0,
		'fees_total_tax'     => 0,
		'items_subtotal'     => 0,
		'items_subtotal_tax' => 0,
		'items_total'        => 0,
		'items_total_tax'    => 0,
		'total'              => 0,
		'shipping_total'     => 0,
		'shipping_tax_total' => 0,
		'discounts_total'    => 0,
	);

	/**
	 * Cache of tax rates for a given tax class.
	 *
	 * @var array
	 */
	protected $item_tax_rates;

	/**
	 * Sets up the items provided, and calculate totals.
	 *
	 * @param WPB_Cart $cart Cart object to calculate totals for.
	 *
	 * @throws \Exception If missing WPB_Cart object.
	 */
	public function __construct( &$cart = null ) {
		if ( ! is_a( $cart, 'NF\WPBOUTIK\WPB_Cart' ) ) {
			throw new \Exception( 'A valid WPB_Cart object is required' );
		}

		$this->cart = $cart;
		$this->calculate();
	}

	/**
	 * Run all calculation methods on the given items in sequence.
	 */
	protected function calculate() {
		$this->calculate_item_totals();
		//$this->calculate_shipping_totals();
		$this->calculate_totals();
	}

	/**
	 * Get default blank set of props used per item.
	 *
	 * @return array
	 */
	protected function get_default_item_props() {
		return (object) array(
			'object'             => null,
			'tax_class'          => '',
			'taxable'            => false,
			'quantity'           => 0,
			'product'            => false,
			'price_includes_tax' => false,
			'subtotal'           => 0,
			'subtotal_tax'       => 0,
			'subtotal_taxes'     => array(),
			'total'              => 0,
			'total_tax'          => 0,
			'taxes'              => array(),
		);
	}

	/**
	 * Get default blank set of props used per fee.
	 *
	 * @return array
	 */
	protected function get_default_fee_props() {
		return (object) array(
			'object'    => null,
			'tax_class' => '',
			'taxable'   => false,
			'total_tax' => 0,
			'taxes'     => array(),
		);
	}

	/**
	 * Get default blank set of props used per shipping row.
	 *
	 * @return array
	 */
	protected function get_default_shipping_props() {
		return (object) array(
			'object'    => null,
			'tax_class' => '',
			'taxable'   => false,
			'total'     => 0,
			'total_tax' => 0,
			'taxes'     => array(),
		);
	}

	/**
	 * Handles a cart or order object passed in for calculation. Normalises data
	 * into the same format for use by this class.
	 *
	 * Each item is made up of the following props, in addition to those returned by get_default_item_props() for totals.
	 *  - key: An identifier for the item (cart item key or line item ID).
	 *  - cart_item: For carts, the cart item from the cart which may include custom data.
	 *  - quantity: The qty for this line.
	 *  - price: The line price in cents.
	 *  - product: The product object this cart item is for.
	 *
	 */
	protected function get_items_from_cart() {
		$this->items = array();

		//var_dump($this->cart->get_cart());die;

		$activate_tax = wpboutik_get_option_params( 'activate_tax' );
		foreach ( $this->cart->get_cart() as $cart_item_key => $cart_item ) {

			$selling_fees = get_post_meta( $cart_item->product_id, 'selling_fees', true );
			if ( empty( $selling_fees ) || ( ! empty( $cart_item->customization ) && ! empty( $cart_item->customization['renew'] ) ) ) {
				$selling_fees = 0;
			}

			if ( $cart_item['variation_id'] != "0" ) {
				$id_for_tax_class = $cart_item['variation_id'];
				$variants         = get_post_meta( $cart_item['product_id'], 'variants', true );
				$variation        = wpboutik_get_variation_by_id( json_decode( $variants ), $cart_item['variation_id'] );
				$price            = ( strpos( $cart_item['variation_id'], 'custom' ) !== false ) ? $cart_item->gift_card_price : $variation->price;
			} else {
				$id_for_tax_class = $cart_item['product_id'];
				$price            = get_post_meta( $cart_item['product_id'], 'price', true );
			}

			$tax_class   = '';
			$taxes_class = array();
			if ( $activate_tax ) {
				$tax_class                                      = get_post_meta( $cart_item['product_id'], 'tax', true );
				$taxes_class[ $tax_class ][ $id_for_tax_class ] = $cart_item['quantity'] * ( $price + $selling_fees );
			}

			$item                     = $this->get_default_item_props();
			$item->key                = $cart_item_key;
			$item->object             = $cart_item;
			$item->tax_class          = $tax_class;
			$item->taxable            = $activate_tax;
			$item->price_includes_tax = wpb_prices_include_tax();
			$item->quantity           = $cart_item['quantity'];
			$item->price              = $price + $selling_fees;
			//$item->product                 = $cart_item['data'];
			$item->tax_rates               = $taxes_class;
			$this->items[ $cart_item_key ] = $item;
		}
	}

	/**
	 * Get item costs grouped by tax class.
	 *
	 * @return array
	 */
	protected function get_tax_class_costs() {
		$item_tax_classes     = wp_list_pluck( $this->items, 'tax_class' );
		$shipping_tax_classes = wp_list_pluck( $this->shipping, 'tax_class' );
		$fee_tax_classes      = wp_list_pluck( $this->fees, 'tax_class' );
		$costs                = array_fill_keys( $item_tax_classes + $shipping_tax_classes + $fee_tax_classes, 0 );
		$costs['non-taxable'] = 0;

		foreach ( $this->items + $this->fees + $this->shipping as $item ) {
			if ( 0 > $item->total ) {
				continue;
			}
			if ( ! $item->taxable ) {
				$costs['non-taxable'] += $item->total;
			} elseif ( 'inherit' === $item->tax_class ) {
				$costs[ reset( $item_tax_classes ) ] += $item->total;
			} else {
				$costs[ $item->tax_class ] += $item->total;
			}
		}

		return array_filter( $costs );
	}

	/**
	 * Get shipping methods from the cart and normalise.
	 */
	/*protected function get_shipping_from_cart() {
		$this->shipping = array();

		if ( ! $this->cart->show_shipping() ) {
			return;
		}

		foreach ( $this->cart->calculate_shipping() as $key => $shipping_object ) {
			$shipping_line            = $this->get_default_shipping_props();
			$shipping_line->object    = $shipping_object;
			$shipping_line->tax_class = get_option( 'woocommerce_shipping_tax_class' );
			$shipping_line->taxable   = true;
			$shipping_line->total     = wc_add_number_precision_deep( $shipping_object->cost );
			$shipping_line->taxes     = wc_add_number_precision_deep( $shipping_object->taxes, false );
			$shipping_line->taxes     = array_map( array( $this, 'round_item_subtotal' ), $shipping_line->taxes );
			$shipping_line->total_tax = array_sum( $shipping_line->taxes );

			$this->shipping[ $key ] = $shipping_line;
		}
	}*/

	/**
	 * Return array of coupon objects from the cart. Normalises data
	 * into the same format for use by this class.
	 */
	/*protected function get_coupons_from_cart() {
		$this->coupons = $this->cart->get_coupons();

		foreach ( $this->coupons as $coupon ) {
			switch ( $coupon->get_discount_type() ) {
				case 'fixed_product':
					$coupon->sort = 1;
					break;
				case 'percent':
					$coupon->sort = 2;
					break;
				case 'fixed_cart':
					$coupon->sort = 3;
					break;
				default:
					$coupon->sort = 0;
					break;
			}

			// Allow plugins to override the default order.
			$coupon->sort = apply_filters( 'woocommerce_coupon_sort', $coupon->sort, $coupon );
		}

		uasort( $this->coupons, array( $this, 'sort_coupons_callback' ) );
	}*/

	/**
	 * Sort coupons so discounts apply consistently across installs.
	 *
	 * In order of priority;
	 *  - sort param
	 *  - usage restriction
	 *  - coupon value
	 *  - ID
	 *
	 *
	 * @return int
	 */
	/*protected function sort_coupons_callback( $a, $b ) {
		if ( $a->sort === $b->sort ) {
			if ( $a->get_limit_usage_to_x_items() === $b->get_limit_usage_to_x_items() ) {
				if ( $a->get_amount() === $b->get_amount() ) {
					return $b->get_id() - $a->get_id();
				}

				return ( $a->get_amount() < $b->get_amount() ) ? - 1 : 1;
			}

			return ( $a->get_limit_usage_to_x_items() < $b->get_limit_usage_to_x_items() ) ? - 1 : 1;
		}

		return ( $a->sort < $b->sort ) ? - 1 : 1;
	}*/

	/**
	 * Ran to remove all base taxes from an item. Used when prices include tax, and the customer is tax exempt.
	 *
	 * @param object $item Item to adjust the prices of.
	 *
	 * @return object
	 */
	/*protected function remove_item_base_taxes( $item ) {
		if ( $item->price_includes_tax && $item->taxable ) {
			if ( apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
				$base_tax_rates = WC_Tax::get_base_tax_rates( $item->product->get_tax_class( 'unfiltered' ) );
			} else {
				$base_tax_rates = $item->tax_rates;
			}

			// Work out a new base price without the shop's base tax.
			$taxes = WC_Tax::calc_tax( $item->price, $base_tax_rates, true );

			// Now we have a new item price (excluding TAX).
			$item->price              = round( $item->price - array_sum( $taxes ), 2 );
			$item->price_includes_tax = false;
		}

		return $item;
	}*/

	/**
	 * Only ran if woocommerce_adjust_non_base_location_prices is true.
	 *
	 * If the customer is outside of the base location, this removes the base
	 * taxes. This is off by default unless the filter is used.
	 *
	 * Uses edit context so unfiltered tax class is returned.
	 *
	 * @param object $item Item to adjust the prices of.
	 *
	 * @return object
	 */
	/*protected function adjust_non_base_location_price( $item ) {
		if ( $item->price_includes_tax && $item->taxable ) {
			$base_tax_rates = WC_Tax::get_base_tax_rates( $item->product->get_tax_class( 'unfiltered' ) );

			if ( $item->tax_rates !== $base_tax_rates ) {
				// Work out a new base price without the shop's base tax.
				$taxes     = WC_Tax::calc_tax( $item->price, $base_tax_rates, true );
				$new_taxes = WC_Tax::calc_tax( $item->price - array_sum( $taxes ), $item->tax_rates, false );

				// Now we have a new item price.
				$item->price = $item->price - array_sum( $taxes ) + array_sum( $new_taxes );
			}
		}

		return $item;
	}*/

	/**
	 * Get discounted price of an item with precision (in cents).
	 *
	 * @param object $item_key Item to get the price of.
	 *
	 * @return int
	 */
	protected function get_discounted_price_in_cents( $item_key ) {
		$item  = $this->items[ $item_key ];
		$price = isset( $this->coupon_discount_totals[ $item_key ] ) ? $item->price - $this->coupon_discount_totals[ $item_key ] : $item->price;

		return $price;
	}

	/**
	 * Get tax rates for an item. Caches rates in class to avoid multiple look ups.
	 *
	 * @param object $item Item to get tax rates for.
	 *
	 * @return array of taxes
	 */
	/*protected function get_item_tax_rates( $item ) {
		if ( ! wc_tax_enabled() ) {
			return array();
		}
		$tax_class      = $item->product->get_tax_class();
		$item_tax_rates = isset( $this->item_tax_rates[ $tax_class ] ) ? $this->item_tax_rates[ $tax_class ] : $this->item_tax_rates[ $tax_class ] = WC_Tax::get_rates( $item->product->get_tax_class(), $this->cart->get_customer() );

		// Allow plugins to filter item tax rates.
		return apply_filters( 'woocommerce_cart_totals_get_item_tax_rates', $item_tax_rates, $item, $this->cart );
	}*/

	/**
	 * Get item costs grouped by tax class.
	 *
	 * @return array
	 */
	protected function get_item_costs_by_tax_class() {
		$tax_classes = array(
			'non-taxable' => 0,
		);

		foreach ( $this->items + $this->fees + $this->shipping as $item ) {
			if ( ! isset( $tax_classes[ $item->tax_class ] ) ) {
				$tax_classes[ $item->tax_class ] = 0;
			}

			if ( $item->taxable ) {
				$tax_classes[ $item->tax_class ] += $item->total;
			} else {
				$tax_classes['non-taxable'] += $item->total;
			}
		}

		return $tax_classes;
	}

	/**
	 * Get a single total with or without precision (in cents).
	 *
	 * @param string $key Total to get.
	 * @param bool $in_cents Should the totals be returned in cents, or without precision.
	 *
	 * @return int|float
	 */
	public function get_total( $key = 'total', $in_cents = false ) {
		$totals = $this->get_totals( $in_cents );

		return isset( $totals[ $key ] ) ? $totals[ $key ] : 0;
	}

	/**
	 * Set a single total.
	 *
	 * @param string $key Total name you want to set.
	 * @param int $total Total to set.
	 */
	protected function set_total( $key, $total ) {
		$this->totals[ $key ] = $total;
	}

	/**
	 * Get all totals with or without precision (in cents).
	 *
	 * @param bool $in_cents Should the totals be returned in cents, or without precision.
	 *
	 * @return array.
	 */
	public function get_totals( $in_cents = false ) {
		return $in_cents ? $this->totals : wpb_remove_number_precision_deep( $this->totals );
	}

	/**
	 * Returns array of values for totals calculation.
	 *
	 * @param string $field Field name. Will probably be `total` or `subtotal`.
	 *
	 * @return array Items object
	 */
	protected function get_values_for_total( $field ) {
		return array_values( wp_list_pluck( $this->items, $field ) );
	}

	/**
	 * Get taxes merged by type.
	 *
	 * @param bool $in_cents If returned value should be in cents.
	 * @param array|string $types Types to merge and return. Defaults to all.
	 *
	 * @return array
	 */
	protected function get_merged_taxes( $in_cents = false, $types = array( 'items', 'fees', 'shipping' ) ) {
		$items = array();
		$taxes = array();

		if ( is_string( $types ) ) {
			$types = array( $types );
		}

		foreach ( $types as $type ) {
			if ( isset( $this->$type ) ) {
				$items = array_merge( $items, $this->$type );
			}
		}

		foreach ( $items as $item ) {
			foreach ( $item->taxes as $rate_id => $rate ) {
				if ( ! isset( $taxes[ $rate_id ] ) ) {
					$taxes[ $rate_id ] = 0;
				}
				$taxes[ $rate_id ] += $this->round_line_tax( $rate );
			}
		}

		return $in_cents ? $taxes : wpb_remove_number_precision_deep( $taxes );
	}

	/**
	 * Round merged taxes.
	 *
	 * @param array $taxes Taxes to round.
	 *
	 * @return array
	 */
	protected function round_merged_taxes( $taxes ) {
		foreach ( $taxes as $rate_id => $tax ) {
			$taxes[ $rate_id ] = $this->round_line_tax( $tax );
		}

		return $taxes;
	}

	/**
	 * Combine item taxes into a single array, preserving keys.
	 *
	 * @param array $item_taxes Taxes to combine.
	 *
	 * @return array
	 */
	protected function combine_item_taxes( $item_taxes ) {
		$merged_taxes = array();
		foreach ( $item_taxes as $taxes ) {
			foreach ( $taxes as $tax_id => $tax_amount ) {
				if ( ! isset( $merged_taxes[ $tax_id ] ) ) {
					$merged_taxes[ $tax_id ] = 0;
				}
				$merged_taxes[ $tax_id ] += $tax_amount;
			}
		}

		return $merged_taxes;
	}

	/*
	|--------------------------------------------------------------------------
	| Calculation methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Calculate item totals.
	 */
	protected function calculate_item_totals() {
		$this->get_items_from_cart();
		$this->calculate_item_subtotals();
		///$this->calculate_discounts();

		$activate_tax = wpboutik_get_option_params( 'activate_tax' );

		foreach ( $this->cart->get_cart() as $cart_item_key => $cart_item ) {
			$cart_item    = (object) $cart_item;
			$selling_fees = get_post_meta( $cart_item->product_id, 'selling_fees', true );
			if ( empty( $selling_fees ) || ( ! empty( $cart_item->customization ) && ! empty( $cart_item->customization['renew'] ) ) ) {
				$selling_fees = 0;
			}
			if ( $cart_item->variation_id != "0" ) {
				$id_for_tax_class = $cart_item->variation_id;
				$variants         = get_post_meta( $cart_item->product_id, 'variants', true );
				$variation        = wpboutik_get_variation_by_id( json_decode( $variants ), $cart_item->variation_id );
				$price            = ( strpos( $cart_item->variation_id, 'custom' ) !== false ) ? $cart_item->customization['gift_card_price'] : $variation->price;
			} else {
				$id_for_tax_class = $cart_item->product_id;
				$price            = get_post_meta( $cart_item->product_id, 'price', true );
			}

			$tax_class = '';
			if ( $activate_tax ) {
				$tax_class                                      = get_post_meta( $cart_item->product_id, 'tax', true );
				$taxes_class[ $tax_class ][ $id_for_tax_class ] = $cart_item->quantity * ( $price + $selling_fees );
			}
		}

		$subtotal = $this->cart->get_subtotal();

		$wp_get_discount           = wpb_get_discount_cart( $subtotal, $this->items, $activate_tax, $taxes_class );
		$discount                  = ( $wp_get_discount['discount'] ?? 0 );
		$taxes_class               = ( $wp_get_discount['taxes_class'] ?? $taxes_class );
		$id_products_with_discount = ( $wp_get_discount['id_products_with_discount'] ?? [] );
		$coupon_id                 = ( $wp_get_discount['coupon_id'] ?? null );

		$tax = 0;
		if ( $taxes_class ) {
			$tax_rates = get_wpboutik_tax_rates();
			if ( isset( $tax_rates['FR'] ) ) {
				foreach ( $taxes_class as $tax_class => $products_of_tax ) {
					$count = 0;
					foreach ( $products_of_tax as $value ) {
						$count += $value;
					}
					$tax_value = round( ( $count ) * ( $tax_rates['FR'][ 'percent_tx_' . $tax_class ] / 100 ), 2 );
					$tax       += $tax_value;
				}
			}
		}

		$shipping = 0;
		/*$method = get_option( 'wpboutik_options_shipping_method_' . sanitize_text_field( $form_datas['delivery-method'] ) );
		if ( $method ) {
			$shipping = Ajax::wpb_get_shipping_price( $method, $form_datas, $this->items );
		}*/

		//$ordertotal = $subtotal - $discount + $shipping + $tax;

		$items_total = $subtotal - $discount + $shipping + $tax;

		$this->set_total( 'items_total', $items_total );
		$this->set_total( 'items_total_tax', $tax );

		$this->cart->set_cart_contents_total( $this->get_total( 'items_total' ) );
		$this->cart->set_cart_contents_tax( array_sum( $this->get_merged_taxes( false, 'items' ) ) );
		$this->cart->set_cart_contents_taxes( $this->get_merged_taxes( false, 'items' ) );
	}

	/**
	 * Subtotals are costs before discounts.
	 *
	 * To prevent rounding issues we need to work with the inclusive price where possible
	 * otherwise we'll see errors such as when working with a 9.99 inc price, 20% VAT which would
	 * be 8.325 leading to totals being 1p off.
	 *
	 * Pre tax coupons come off the price the customer thinks they are paying - tax is calculated
	 * afterwards.
	 *
	 * e.g. $100 bike with $10 coupon = customer pays $90 and tax worked backwards from that.
	 */
	protected function calculate_item_subtotals() {
		$merged_subtotal_taxes = array(); // Taxes indexed by tax rate ID for storage later.

		//$adjust_non_base_location_prices = apply_filters( 'woocommerce_adjust_non_base_location_prices', true );
		//$is_customer_vat_exempt          = $this->cart->get_customer()->get_is_vat_exempt();
		$is_customer_vat_exempt = false;

		foreach ( $this->items as $item_key => $item ) {
			//if ( $item->price_includes_tax ) {
			/*if ( $is_customer_vat_exempt ) {
				$item = $this->remove_item_base_taxes( $item );
			} elseif ( $adjust_non_base_location_prices ) {*/
			//$item = $this->adjust_non_base_location_price( $item );
			//}
			//}

			$item->subtotal = $item->price * $item->quantity;

			$tax = 0;
			if ( $item->tax_rates ) {
				$tax_rates = get_wpboutik_tax_rates();
				if ( isset( $tax_rates['FR'] ) ) {
					foreach ( $item->tax_rates as $tax_class => $products_of_tax ) {
						$count = 0;
						foreach ( $products_of_tax as $value ) {
							$count += $value;
						}
						$tax_value = round( ( $count ) * ( $tax_rates['FR'][ 'percent_tx_' . $tax_class ] / 100 ), 2 );
						$tax       += $tax_value;
					}
				}
			}

			$item->subtotal_tax = $tax;

			/*if ( $this->calculate_tax && $item->product->is_taxable() ) {
				$item->subtotal_taxes = WC_Tax::calc_tax( $item->subtotal, $item->tax_rates, $item->price_includes_tax );
				$item->subtotal_tax   = array_sum( array_map( array( $this, 'round_line_tax' ), $item->subtotal_taxes ) );

				if ( $item->price_includes_tax ) {
					// Use unrounded taxes so we can re-calculate from the orders screen accurately later.
					$item->subtotal = $item->subtotal - array_sum( $item->subtotal_taxes );
				}

				foreach ( $item->subtotal_taxes as $rate_id => $rate ) {
					if ( ! isset( $merged_subtotal_taxes[ $rate_id ] ) ) {
						$merged_subtotal_taxes[ $rate_id ] = 0;
					}
					$merged_subtotal_taxes[ $rate_id ] += $this->round_line_tax( $rate );
				}
			}*/

			$this->cart->cart_contents[ $item_key ]['line_tax_data']     = array( 'subtotal' => $item->subtotal_taxes );
			$this->cart->cart_contents[ $item_key ]['line_subtotal']     = $item->subtotal;
			$this->cart->cart_contents[ $item_key ]['line_subtotal_tax'] = $item->subtotal_tax;
		}

		$items_subtotal = $this->get_rounded_items_total( $this->get_values_for_total( 'subtotal' ) );

		// Prices are not rounded here because they should already be rounded based on settings in `get_rounded_items_total` and in `round_line_tax` method calls.
		$this->set_total( 'items_subtotal', $items_subtotal );
		$this->set_total( 'items_subtotal_tax', array_sum( $merged_subtotal_taxes ), 0 );

		$this->cart->set_subtotal( $this->get_total( 'items_subtotal' ) );
		$this->cart->set_subtotal_tax( $this->get_total( 'items_subtotal_tax' ) );
	}

	/**
	 * Calculate COUPON based discounts which change item prices.
	 */
	protected function calculate_discounts() {
		//$this->get_coupons_from_cart();

		/*	if ( wc_prices_include_tax() ) {
				$this->set_total( 'discounts_total', array_sum( $this->coupon_discount_totals ) - array_sum( $this->coupon_discount_tax_totals ) );
				$this->set_total( 'discounts_tax_total', array_sum( $this->coupon_discount_tax_totals ) );
			} else {
				$this->set_total( 'discounts_total', array_sum( $this->coupon_discount_totals ) );
				$this->set_total( 'discounts_tax_total', array_sum( $this->coupon_discount_tax_totals ) );
			}

			$this->cart->set_coupon_discount_totals( wpb_remove_number_precision_deep( $coupon_discount_amounts ) );
			$this->cart->set_coupon_discount_tax_totals( wpb_remove_number_precision_deep( $coupon_discount_tax_amounts ) );

			// Add totals to cart object. Note: Discount total for cart is excl tax.
			$this->cart->set_discount_total( $this->get_total( 'discounts_total' ) );
			$this->cart->set_discount_tax( $this->get_total( 'discounts_tax_total' ) );*/
	}

	/**
	 * Calculate any shipping taxes.
	 */
	/*protected function calculate_shipping_totals() {
		$this->get_shipping_from_cart();
		$this->set_total( 'shipping_total', array_sum( wp_list_pluck( $this->shipping, 'total' ) ) );
		$this->set_total( 'shipping_tax_total', array_sum( wp_list_pluck( $this->shipping, 'total_tax' ) ) );

		$this->cart->set_shipping_total( $this->get_total( 'shipping_total' ) );
		$this->cart->set_shipping_tax( $this->get_total( 'shipping_tax_total' ) );
		$this->cart->set_shipping_taxes( wpb_remove_number_precision_deep( $this->combine_item_taxes( wp_list_pluck( $this->shipping, 'taxes' ) ) ) );
	}*/

	/**
	 * Main cart totals.
	 */
	protected function calculate_totals() {
		$this->set_total( 'total', round( $this->get_total( 'items_total', true ) + $this->get_total( 'fees_total', true ) + $this->get_total( 'shipping_total', true ) + array_sum( $this->get_merged_taxes( true ) ), 0 ) );
		$items_tax = array_sum( $this->get_merged_taxes( false, array( 'items' ) ) );
		// Shipping and fee taxes are rounded separately because they were entered excluding taxes (as opposed to item prices, which may or may not be including taxes depending upon settings).
		$shipping_and_fee_taxes = round( array_sum( $this->get_merged_taxes( false, array(
			'fees',
			'shipping'
		) ) ), wpb_get_price_decimals(), 2 );
		$this->cart->set_total_tax( $items_tax + $shipping_and_fee_taxes );

		// Allow plugins to hook and alter totals before final total is calculated.
		if ( has_action( 'wpboutik_calculate_totals' ) ) {
			do_action( 'wpboutik_calculate_totals', $this->cart );
		}

		// Allow plugins to filter the grand total, and sum the cart totals in case of modifications.
		$this->cart->set_total( max( 0, apply_filters( 'wpboutik_calculated_total', $this->get_total( 'total' ), $this->cart ) ) );
	}
}
