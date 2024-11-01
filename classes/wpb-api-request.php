<?php

namespace NF\WPBOUTIK;

defined( 'ABSPATH' ) || exit;

class WPB_Api_Request {
	private array $header = [];
	private array $body = [];
	private string $requested_url = '';
	private $response = false;
	private static $base_api = WPBOUTIK_APP_URL;
	private static $available_endpoint = [
		'products'        => 'products',
		'order'           => [
			'get'             => 'get_order_by_id',
			'create'          => 'create_order',
			'cancel'          => 'cancel_order',
			'on_hold'         => 'on_hold_order',
			'failed'          => 'failed_order',
			'stripe_status'   => 'complete_order_stripe_status',
			'paypal_status'   => 'complete_order_paypal_status',
			'mollie_status'   => 'complete_order_mollie_status',
			'paybox_status'   => 'complete_order_paybox_status',
			'monetico_status' => 'complete_order_monetico_status',
		],
		'licenses'					=> [
			'cancel' => 'cancel_auto_renew_license',
			'validate' => 'validate_license_website',
			'remove_url' => 'remove_url',
		],
		'mail'              => [
			'reset_pass'     => 'get_mail_reset_password',
			'update_address' => 'update_address_customer'
		],
		'customer'          => [
			'orders'         => 'get_orders_of_customer',
			'update_address' => 'update_address_customer',
		],
		'best_sellers'    => 'get_best_sellers',
		'abandonned_cart' => [
			'save'   => 'save_abandonned_cart',
			'lost'   => 'abandoned_cart_is_lost',
			'delete' => 'abandoned_cart_remove'
		],
		'upload_blob'     => 'upload_blob_desc',
		'update_qty'      => 'update_qty',
		'init'            => 'update_project',
		'data_info'       => 'get_data_info',
		'refund_link'     => 'update_refund_link_order',
		'invoice_link'    => 'update_invoice_link_order',
		'subscription'    => 'check_subscription'
	];

	private function __construct( $requested_url ) {
		$this->requested_url = $requested_url;
		$this->build_header();
	}

	private function build_header(): void {
		$options = get_option( 'wpboutik_options' );
		$this->set_header( 'technology', 'wordpress' );
		$this->set_header( 'Content-Type', 'application/json; charset=utf-8' );

		$first_install = get_option( 'wpboutik_options_first_install' );
		if ( $first_install === false ) {
			$this->set_header( 'wpb-key', 'init_project' );
		} else {
			if ( ! empty( $options ) && ! empty( $options['apikey'] ) ) {
				$this->set_header( 'wpb-key', $options['apikey'] );
			}
		}
	}

	public function set_header( string $key, string $value ): WPB_Api_Request {
		$this->header[ $key ] = $value;

		return $this;
	}

	public function add_to_body( string $key, mixed $value ) {
		$this->body[ $key ] = $value;

		return $this;
	}

	public function add_multiple_to_body( array $datas ) {
		foreach ( $datas as $key => $value ) {
			$this->body[ $key ] = $value;
		}

		return $this;
	}

	public static function request( string $key = '', string $subKey = '' ) {
		$requested = WPBOUTIK_APP_URL . 'api/';
		if ( ! empty( $key ) && isset( self::$available_endpoint[ $key ] ) ) {
			if (
				! empty( $subKey )
				&& isset( self::$available_endpoint[ $key ][ $subKey ] )
				&& is_string( self::$available_endpoint[ $key ][ $subKey ] )
			) {
				return new self( $requested . self::$available_endpoint[ $key ][ $subKey ] );
			} elseif ( is_string( self::$available_endpoint[ $key ] ) ) {
				return new self( $requested . self::$available_endpoint[ $key ] );
			}
		}

		return new \WP_Error( 'request-error', 'This request is not available.' );
	}

	public function exec() {
		$this->response = wp_remote_post(
			$this->requested_url,
			array(
				'body'      => wp_json_encode( $this->body ),
				'timeout'   => 60,
				'headers'   => $this->header,
				'sslverify' => false
			)
		);

		return $this;
	}

	public function has_response() {
		return ! empty( $this->response );
	}

	public function is_error() {
		return is_wp_error( $this->response );
	}

	public function get_error() {
		if ( $this->has_response() && $this->is_error() ) {
			return [
				'error_code'    => array_key_first( $this->response->errors ),
				'error_message' => $this->response->errors[ $error_code ][0] // TODO : Error
			];
		} else {
			return false;
		}
	}

	public function get_response() {
		return $this->response;
	}

	public function get_response_body() {
		// si besoin de passer par wordpress :
		// return wp_remote_retrieve_body( $this->response )
		return $this->response['body'];
	}

}