<?php

namespace NF\WPBOUTIK;

class Query {

	use Singleton;

	/**
	 * Query vars to add to wp.
	 *
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Constructor for the query class. Hooks in methods.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoints' ) );
		if ( ! is_admin() ) {
			//add_action( 'wp_loaded', array( __CLASS__, 'get_errors' ), 20 );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
		}
		$this->init_query_vars();
		//$this->add_endpoints();
	}

	/**
	 * Get any errors from querystring.
	 */
	/*public function get_errors() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$error = ! empty( $_GET['wpb_error'] ) ? sanitize_text_field( wp_unslash( $_GET['wpb_error'] ) ) : '';

		if ( $error && ! wpb_has_notice( $error, 'error' ) ) {
			wpb_add_notice( $error, 'error' );
		}
	}*/

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {
		// Query vars to add to WP.
		$this->query_vars = array(
			// Checkout actions.
			'order-pay'                  => get_option( 'wpboutik_checkout_pay_endpoint', 'order-pay' ),
			'order-received'             => get_option( 'wpboutik_checkout_order_received_endpoint', 'order-received' ),
			// My account actions.
			'orders'                     => get_option( 'wpboutik_myaccount_orders_endpoint', 'orders' ),
			'view-order'                 => get_option( 'wpboutik_myaccount_view_order_endpoint', 'view-order' ),
			'downloads'                  => get_option( 'wpboutik_myaccount_downloads_endpoint', 'downloads' ),
			'edit-account'               => get_option( 'wpboutik_myaccount_edit_account_endpoint', 'edit-account' ),
			'edit-address'               => get_option( 'wpboutik_myaccount_edit_address_endpoint', 'edit-address' ),
			'licenses'                   => 'licenses',
			'abonnements'                => 'abonnements',
			'payment-methods'            => get_option( 'wpboutik_myaccount_payment_methods_endpoint', 'payment-methods' ),
			'lost-password'              => get_option( 'wpboutik_myaccount_lost_password_endpoint', 'lost-password' ),
			'customer-logout'            => get_option( 'wpboutik_logout_endpoint', 'customer-logout' ),
			'add-payment-method'         => get_option( 'wpboutik_myaccount_add_payment_method_endpoint', 'add-payment-method' ),
			'delete-payment-method'      => get_option( 'wpboutik_myaccount_delete_payment_method_endpoint', 'delete-payment-method' ),
			'set-default-payment-method' => get_option( 'wpboutik_myaccount_set_default_payment_method_endpoint', 'set-default-payment-method' ),
		);
	}

	/**
	 * Get page title for an endpoint.
	 *
	 * @param string $endpoint Endpoint key.
	 *
	 * @return string
	 */
	public function get_endpoint_title( $endpoint, $action = '' ) {
		global $wp;

		switch ( $endpoint ) {
			case 'order-pay':
				$title = __( 'Pay for order', 'wpboutik' );
				break;
			case 'order-received':
				$title = __( 'Order received', 'wpboutik' );
				break;
			case 'orders':
				if ( ! empty( $wp->query_vars['orders'] ) ) {
					/* translators: %s: page */
					$title = sprintf( __( 'Orders (page %d)', 'wpboutik' ), intval( $wp->query_vars['orders'] ) );
				} else {
					$title = __( 'Orders', 'wpboutik' );
				}
				break;
			case 'view-order':
				$order = wpboutik_get_order( $wp->query_vars['view-order'] );
				/* translators: %s: order number */
				$title = ( $order ) ? sprintf( __( 'Order #%s', 'wpboutik' ), $order->id ) : '';
				break;
			case 'downloads':
				$title = __( 'Downloads', 'wpboutik' );
				break;
			case 'licenses':
				$title = __( 'Licenses', 'wpboutik' );
				break;
			case 'abonnements':
				$title = __( 'Abonnements', 'wpboutik' );
				break;
			case 'edit-account':
				$title = __( 'Account details', 'wpboutik' );
				break;
			case 'edit-address':
				$title = __( 'Addresses', 'wpboutik' );
				break;
			case 'payment-methods':
				$title = __( 'Payment methods', 'wpboutik' );
				break;
			case 'add-payment-method':
				$title = __( 'Add payment method', 'wpboutik' );
				break;
			case 'lost-password':
				if ( in_array( $action, array( 'rp', 'resetpass', 'newaccount' ), true ) ) {
					$title = __( 'Set password', 'wpboutik' );
				} else {
					$title = __( 'Lost password', 'wpboutik' );
				}

				break;
			default:
				$title = '';
				break;
		}

		return apply_filters( 'wpboutik_endpoint_' . $endpoint . '_title', $title, $endpoint );
	}

	/**
	 * Add endpoints for query vars.
	 */
	public function add_endpoints() {
		$mask = EP_PAGES;
		/*if ( is_page( wpboutik_get_page_id( 'checkout' ) ) || is_page( wpboutik_get_page_id( 'account' ) ) ) {
			$mask = EP_ROOT | EP_PAGES;
		}*/
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( ! empty( $var ) ) {
				add_rewrite_endpoint( $var, $mask );
			}
		}
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->get_query_vars() as $key => $var ) {
			$vars[] = $key;
		}

		return $vars;
	}

	/**
	 * Get query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return apply_filters( 'wpboutik_get_query_vars', $this->query_vars );
	}

	/**
	 * Get query current active query var.
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp;

		foreach ( $this->get_query_vars() as $key => $value ) {
			if ( isset( $wp->query_vars[ $key ] ) ) {
				return $key;
			}
		}

		return '';
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// Map query vars to their keys, or get them if endpoints are not supported.
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $var ] ) );
			} elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}
}