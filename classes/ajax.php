<?php

namespace NF\WPBOUTIK;

use MoneticoDemoWebKit\Monetico\Request\OrderContext;
use MoneticoDemoWebKit\Monetico\Request\OrderContextBilling;
use MoneticoDemoWebKit\Monetico\Request\OrderContextClient;
use MoneticoDemoWebKit\Monetico\Request\PaymentRequest;

class Ajax {

	use Singleton;

	/**
	 * Constructor for the ajax class. Hooks in methods.
	 */
	public function __construct() {
		// Stripe
		add_action( 'wp_ajax_wpboutik_ajax_checkout_stripe_elements', array(
			$this,
			'wpboutik_ajax_checkout_stripe_elements'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_checkout_stripe_elements', array(
			$this,
			'wpboutik_ajax_checkout_stripe_elements'
		) );

		// Security ammount
		add_action( 'wp_ajax_wpboutik_ajax_checkout_price', array(
			$this,
			'wpboutik_ajax_checkout_price'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_checkout_price', array(
			$this,
			'wpboutik_ajax_checkout_price'
		) );
		add_action( 'wp_ajax_wpboutik_remove_promo', array(
			$this,
			'wpboutik_remove_promo'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_remove_promo', array(
			$this,
			'wpboutik_remove_promo'
		) );

		add_action( 'wp_ajax_wpboutik_ajax_finish_order_after_payment_stripe', array(
			$this,
			'wpboutik_ajax_finish_order_after_payment_stripe'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_finish_order_after_payment_stripe', array(
			$this,
			'wpboutik_ajax_finish_order_after_payment_stripe'
		) );

		//PayPal
		add_action( 'wp_ajax_wpboutik_ajax_order_after_payment_paypal', array(
			$this,
			'wpboutik_ajax_order_after_payment_paypal'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_order_after_payment_paypal', array(
			$this,
			'wpboutik_ajax_order_after_payment_paypal'
		) );

		// Mollie
		add_action( 'wp_ajax_wpboutik_ajax_create_payment_mollie', array(
			$this,
			'wpboutik_ajax_create_payment_mollie'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_create_payment_mollie', array(
			$this,
			'wpboutik_ajax_create_payment_mollie'
		) );
		add_action(
			'wp_ajax_wpboutik_ajax_check_payment_mollie',
			array(
				$this,
				'wpboutik_ajax_check_payment_mollie'
			)
		);
		add_action(
			'wp_ajax_nopriv_wpboutik_ajax_check_payment_mollie',
			array(
				$this,
				'wpboutik_ajax_check_payment_mollie'
			)
		);
		add_action(
			'wp_ajax_wpboutik_ajax_finish_payment_mollie',
			array(
				$this,
				'wpboutik_ajax_finish_payment_mollie'
			)
		);
		add_action(
			'wp_ajax_nopriv_wpboutik_ajax_finish_payment_mollie',
			array(
				$this,
				'wpboutik_ajax_finish_payment_mollie'
			)
		);

		// Order
		add_action( 'wp_ajax_wpboutik_ajax_create_order', array( $this, 'wpboutik_ajax_create_order' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_create_order', array( $this, 'wpboutik_ajax_create_order' ) );
		add_action( 'wp_ajax_wpboutik_ajax_order_cancel_payment', array(
			$this,
			'wpboutik_ajax_order_cancel_payment'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_order_cancel_payment', array(
			$this,
			'wpboutik_ajax_order_cancel_payment'
		) );

		// back in stock
		add_action( 'wp_ajax_wpboutik_ajax_create_mail_back_in_stock', array(
			$this,
			'wpboutik_ajax_create_mail_back_in_stock'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_create_mail_back_in_stock', array(
			$this,
			'wpboutik_ajax_create_mail_back_in_stock'
		) );

		// Change payment type
		add_action( 'wp_ajax_wpboutik_ajax_checkout_change_payment_type', array(
			$this,
			'wpboutik_ajax_checkout_change_payment_type'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_checkout_change_payment_type', array(
			$this,
			'wpboutik_ajax_checkout_change_payment_type'
		) );

		// Change delivery method
		add_action( 'wp_ajax_wpboutik_ajax_checkout_add_price_delivery_method', array(
			$this,
			'wpboutik_ajax_checkout_add_price_delivery_method'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_checkout_add_price_delivery_method', array(
			$this,
			'wpboutik_ajax_checkout_add_price_delivery_method'
		) );

		add_action( 'wp_ajax_wpboutik_ajax_checkout_modify_tax_rate', array(
			$this,
			'wpboutik_ajax_checkout_modify_tax_rate'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_checkout_modify_tax_rate', array(
			$this,
			'wpboutik_ajax_checkout_modify_tax_rate'
		) );

		// Cart
		add_action( 'wp_ajax_wpboutik_ajax_add_to_cart', array( $this, 'wpboutik_ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_add_to_cart', array( $this, 'wpboutik_ajax_add_to_cart' ) );

		add_action( 'wp_ajax_wpboutik_ajax_add_to_cart_renew', array( $this, 'wpboutik_ajax_add_to_cart_renew' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_add_to_cart_renew', array( $this, 'wpboutik_ajax_add_to_cart_renew' ) );

		add_action( 'wp_ajax_wpboutik_ajax_client_stripe', array( $this, 'createPayementAndClient' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_client_stripe', array( $this, 'createPayementAndClient' ) );

		add_action( 'wp_ajax_wpboutik_stop_payment_license', array( $this, 'wpboutik_ajax_stop_payment_license' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_stop_payment_license', array( $this, 'wpboutik_ajax_stop_payment_license' ) );
		add_action( 'wp_ajax_wpboutik_ajax_remove_url_license', array( $this, 'wpboutik_ajax_remove_url_license' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_remove_url_license', array( $this, 'wpboutik_ajax_remove_url_license' ) );

		add_action( 'wp_ajax_wpboutik_ajax_save_data_checkout', array( $this, 'wpboutik_ajax_save_data_checkout' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_save_data_checkout', array(
			$this,
			'wpboutik_ajax_save_data_checkout'
		) );

		add_action( 'wp_ajax_wpboutik_ajax_remove_to_cart', array( $this, 'wpboutik_ajax_remove_to_cart' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_remove_to_cart', array( $this, 'wpboutik_ajax_remove_to_cart' ) );
		add_action( 'wp_ajax_wpboutik_ajax_update_qty_to_cart', array( $this, 'wpboutik_ajax_update_qty_to_cart' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_update_qty_to_cart', array(
			$this,
			'wpboutik_ajax_update_qty_to_cart'
		) );

		add_action( 'wp_ajax_wpboutik_ajax_apply_coupon_code', array( $this, 'apply_coupon_code' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_apply_coupon_code', array(
			$this,
			'apply_coupon_code'
		) ); // Si les utilisateurs non connectés peuvent également appliquer le code promo

		add_action( 'wp_ajax_wpboutik_ajax_apply_gift_card_code', array( $this, 'apply_gift_card_code' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_apply_gift_card_code', array(
			$this,
			'apply_gift_card_code'
		) ); // Si les utilisateurs non connectés peuvent également appliquer le code promo

		// Boxtal
		add_action( 'wp_ajax_wpboutik_ajax_checkout_boxtal_load_price_offers', array(
			$this,
			'wpboutik_ajax_checkout_boxtal_load_price_offers'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_checkout_boxtal_load_price_offers', array(
			$this,
			'wpboutik_ajax_checkout_boxtal_load_price_offers'
		) );
		add_action( 'wp_ajax_wpboutik_ajax_checkout_boxtal_load_listpoints', array(
			$this,
			'wpboutik_ajax_checkout_boxtal_load_listpoints'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_checkout_boxtal_load_listpoints', array(
			$this,
			'wpboutik_ajax_checkout_boxtal_load_listpoints'
		) );

		// Monetico
		add_action( 'wp_ajax_wpboutik_ajax_create_payment_monetico', array(
			$this,
			'wpboutik_ajax_create_payment_monetico'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_create_payment_monetico', array(
			$this,
			'wpboutik_ajax_create_payment_monetico'
		) );

		// Paybox
		add_action( 'wp_ajax_wpboutik_ajax_create_payment_paybox', array(
			$this,
			'wpboutik_ajax_create_payment_paybox'
		) );
		add_action( 'wp_ajax_nopriv_wpboutik_ajax_create_payment_paybox', array(
			$this,
			'wpboutik_ajax_create_payment_paybox'
		) );

		// Search product
		add_action( 'wp_ajax_wpb_search_products', array( $this, 'search_products_callback' ) );
		add_action( 'wp_ajax_nopriv_wpb_search_products', array( $this, 'search_products_callback' ) );
	}

	public function wpboutik_ajax_checkout_price() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'checkout-nonce-paypal' ) ) {
			return;
		}

		$method = '';
		if ( isset( $_POST['method_shipping_id'] ) ) {
			$method = get_option( 'wpboutik_options_shipping_method_' . sanitize_text_field( $_POST['method_shipping_id'] ) );
		}

		$shipping_country_key = sanitize_text_field( $_POST['shipping_country_key'] );

		echo json_encode( [
			'value'    => ( wpboutik_calculateOrderAmount( [], $method, $shipping_country_key, $_POST['datas'] ) / 100 ),
			'currency' => strtolower( get_wpboutik_currency() )
		] );
		wp_die();
	}

	public function wpboutik_remove_promo() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'checkout-nonce-remove-promo' ) ) {
			return;
		}

		$status = true;
		if ( isset( $_POST['delete_promo'] ) ) {
			switch ( $_POST['delete_promo'] ) {
				case 'coupon' :
					setcookie( 'wpboutik_coupons_code', '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
					$message = 'Le coupon à bien été retiré.';
					break;
				case 'gift_card' :
					WPB_Gift_Card::remove_all_cookie();
					$message = 'La carte cadeau à bien été retirée.';
					break;
				default :
					$message = 'erreur de requête.';
					$status  = false;
					break;
			}
		}

		echo json_encode( [
			'message' => $message,
			'status'  => $status
		] );
		wp_die();
	}

	public function wpboutik_ajax_checkout_stripe_elements() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'checkout-nonce' ) ) {
			return;
		}

		$method = '';
		if ( isset( $_POST['method_shipping_id'] ) ) {
			$method = get_option( 'wpboutik_options_shipping_method_' . sanitize_text_field( $_POST['method_shipping_id'] ) );
		}

		$shipping_country_key = sanitize_text_field( $_POST['shipping_country_key'] );

		$options = get_option( 'wpboutik_options_params' );

		\Stripe\Stripe::setApiKey( $options['stripe_secret_key'] );

		try {
			parse_str( $_POST['datas'], $form_datas );
			if (!empty($form_datas['email_address'])) {
				$customer = getOrCreateStripeCustomer(
					$form_datas['email_address'],
					$options['stripe_secret_key']
				);
			} else {
				$customer = null;
			}
			if ( ! isset( $_COOKIE['wpboutik_paymentintent_id'] ) ) {
				// Create a PaymentIntent with amount and currency
				$paymentIntent = \Stripe\PaymentIntent::create( [
					'amount'                    => wpboutik_calculateOrderAmount( [], $method, $shipping_country_key, $form_datas ),
					'currency'                  => strtolower( get_wpboutik_currency() ),
					'automatic_payment_methods' => [
						'enabled' => true,
					],
					// Verify your integration in this guide by including this parameter
					'metadata'                  => [ 'integration_check' => 'accept_a_payment' ],
					'customer' => $customer
				]);

				setcookie( 'wpboutik_paymentintent_id', $paymentIntent->id, 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
			} else {
				$paymentIntent = updatePaymentIntentStripe( $method, $shipping_country_key, '', $form_datas, $customer );
			}

			$output = [
				'clientSecret' => $paymentIntent->client_secret,
				'customer' => $customer
			];

			wp_send_json_success( $output );
			wp_die();
		} catch ( Error $e ) {
			http_response_code( 500 );
			echo json_encode( [ 'error' => $e->getMessage() ] );
		}
	}

	public function createPayementAndClient () {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wpb-checkout-client-stripe' ) ) {
			return;
		}
		if ( ! isset( $_POST['payment_id'] ) || ! isset( $_POST['email_address'] ) ) {
			return;
		}

		$options = get_option( 'wpboutik_options_params' );
		\Stripe\Stripe::setApiKey( $options['stripe_secret_key'] );
		$customer = getOrCreateStripeCustomer(
			$_POST['email_address'],
			$options['stripe_secret_key']
		);
		$payment = \Stripe\PaymentMethod::retrieve($_POST['payment_id']);
		$payment->attach(['customer' => $customer]);

		echo wp_send_json(
			array(
				'success'      => true,
				'payment' => $payment->id
			)
		);
		wp_die();
}

	public function wpboutik_ajax_finish_order_after_payment_stripe() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'checkout-finish-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['order_id_finish'] ) ) {
			return;
		}

		if ( ! isset( $_POST['payment_intent_id'] ) ) {
			return;
		}

		$order_id          = sanitize_text_field( $_POST['order_id_finish'] );
		$payment_intent_id = sanitize_text_field( $_POST['payment_intent_id'] );

		$options = get_option( 'wpboutik_options_params' );

		\Stripe\Stripe::setApiKey( $options['stripe_secret_key'] );

		$wpboutik_paymentintent_id = $payment_intent_id;
		\Stripe\PaymentIntent::update( $wpboutik_paymentintent_id, [
			'metadata' => array(
				'order_id' => $order_id
			)
		] );

		try {
			// Récupérer les détails du paiement mis à jour
			$paymentIntent = \Stripe\PaymentIntent::retrieve( $wpboutik_paymentintent_id );

			// Récupérer les frais de transaction du paiement
			$charges         = $paymentIntent->charges->data;
			$transactionFees = 0;

			// Parcourir toutes les charges associées au paiement pour calculer les frais de transaction
			foreach ( $charges as $charge ) {
				$transactionFees += $charge->balance_transaction->fee / 100; // Divisé par 100 car Stripe utilise les centimes
			}

			//echo "Frais de transaction : " . $transactionFees . " " . $paymentIntent->currency;
		} catch ( \Stripe\Exception\ApiErrorException $e ) {
			echo "Erreur : " . esc_html( $e->getMessage() );
		}

		$options = get_option( 'wpboutik_options' );

		$api_request = WPB_Api_Request::request( 'order', 'stripe_status' )
		                              ->add_multiple_to_body( [
			                              'options'          => $options,
			                              'order_id'         => $order_id,
			                              'payment_id'       => $payment_intent_id,
			                              'transaction_fees' => $transactionFees,
		                              ] )->exec();

		if ( ! $api_request->is_error() ) {
			wp_send_json_success();
		}
	}

	public function wpboutik_ajax_order_after_payment_paypal() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'checkout-paypal-finish-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['order_id_finish'] ) ) {
			return;
		}

		if ( ! isset( $_POST['payment_intent_id'] ) ) {
			return;
		}

		$order_id          = sanitize_text_field( $_POST['order_id_finish'] );
		$payment_intent_id = sanitize_text_field( $_POST['payment_intent_id'] );
		$transaction_fees  = sanitize_text_field( $_POST['transaction_fees'] );

		$options = get_option( 'wpboutik_options' );

		$api_request = WPB_Api_Request::request( 'order', 'paypal_status' )
		                              ->add_multiple_to_body( [
			                              'options'          => $options,
			                              'order_id'         => $order_id,
			                              'payment_id'       => $payment_intent_id,
			                              'transaction_fees' => $transaction_fees,
		                              ] )->exec();

		if ( ! $api_request->is_error() ) {
			wp_send_json_success();
		}
	}

	public function wpboutik_ajax_create_payment_mollie() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'checkout-mollie-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['order_id'] ) ) {
			return;
		}

		if ( ! isset( $_POST['cardToken'] ) ) {
			return;
		}

		if ( ! isset( $_POST['total'] ) ) {
			return;
		}

		$order_id     = sanitize_text_field( $_POST['order_id'] );
		$total        = sanitize_text_field( number_format( (float) WPB_Gift_Card::get_finale_price( $_POST['total'] ), 2 ) );
		$redirect_url = sanitize_text_field( $_POST['redirectUrl'] );
		$card_token   = sanitize_text_field( $_POST['cardToken'] );

		$options = get_option( 'wpboutik_options_params' );

		$mollie_test = $options['mollie_test'];
		if ( (bool) $mollie_test ) {
			$mollie_api_key = $options['mollie_api_key_test'];
		} else {
			$mollie_api_key = $options['mollie_api_key_live'];
		}

		$mollie = new \Mollie\Api\MollieApiClient();
		$mollie->setApiKey( $mollie_api_key );
		$options = get_option( 'wpboutik_options' );

		$currency = get_wpboutik_currency();
		if ( empty( $currency ) ) {
			$currency = 'EUR';
		}

		try {

			$checkout_url = wpboutik_get_page_permalink( 'checkout' );
			//$cancel_url   = add_query_arg( 'payment_mollie_status', 'cancelled', wpboutik_get_page_permalink( 'checkout' ) );
			$cancel_url = wpboutik_get_endpoint_url( 'order-received', $order_id, $checkout_url );
			$payment    = $mollie->payments->create( [
				"method"      => "creditcard",
				"amount"      => [
					"currency" => $currency,
					"value"    => number_format( (float) WPB_Gift_Card::get_finale_price( $total ), 2 )
				],
				"description" => "Order #" . $order_id,
				"redirectUrl" => wpboutik_get_endpoint_url( 'order-received', $order_id, $checkout_url ),
				"webhookUrl"  => rest_url( 'wpboutik/v1/wpboutik_mollie_webhook' ),
				"cancelUrl"   => $cancel_url,
				"cardToken"   => $card_token,
				"metadata"    => [
					"order_id"    => $order_id,
					"redirectUrl" => $redirect_url,
					"cancelUrl"   => $cancel_url,
				],
			] );
			echo wp_send_json(
				array(
					'success'     => true,
					'paymentId'   => $payment->id,
					'redirectUrl' => $redirect_url,
					'checkoutUrl' => $payment->_links->checkout->href,
				)
			);
			wp_die();
		} catch ( \Exception $e ) {
			echo wp_send_json(
				array(
					'success'      => false,
					'errorMessage' => $e->getMessage()
				)
			);
			wp_die();
		}
	}

	public static function linearise_xml( $str1 ) {
		$pattern = '/[\n\r]\s*/';

		return preg_replace( $pattern, '', $str1 );
	}

	public function wpboutik_ajax_create_payment_paybox() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'checkout-paybox-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['order_id'] ) ) {
			return;
		}

		if ( ! isset( $_POST['billing_details'] ) ) {
			return;
		}

		if ( ! isset( $_POST['total_qty'] ) ) {
			return;
		}

		if ( ! isset( $_POST['total'] ) ) {
			return;
		}
		$order_id        = sanitize_text_field( $_POST['order_id'] );
		$total           = sanitize_text_field( $_POST['total'] );
		$total_qty       = sanitize_text_field( $_POST['total_qty'] );
		$total           = $total * 100; // convertir en centimes d'euros
		$billing_details = $_POST['billing_details'];
		$email           = sanitize_email( $billing_details['email'] );

		$options = get_option( 'wpboutik_options_params' );

		$paybox_num_site   = $options['paybox_num_site'];
		$paybox_num_rang   = $options['paybox_num_rang'];
		$paybox_id         = $options['paybox_id'];
		$paybox_secret_key = $options['paybox_secret_key'];

		$currency = get_wpboutik_currency();
		if ( empty( $currency ) ) {
			$currency = 'EUR';
		}

		$devise_code = '978';
		if ( $currency === 'USD' ) {
			$devise_code = '840';
		} elseif ( $currency === 'GBP' ) {
			$devise_code = '826';
		} elseif ( $currency === 'CFA' ) {
			$devise_code = '952';
		}

		try {

			$checkout_url = wpboutik_get_page_permalink( 'checkout' );
			$repondre_a   = rest_url( 'wpboutik/v1/wpboutik_paybox_webhook' );
			$PBX_EFFECTUE = wpboutik_get_endpoint_url( 'order-received', $order_id, $checkout_url );
			$PBX_REFUSE   = wpboutik_get_endpoint_url( 'order-received', $order_id, $checkout_url );
			$PBX_ANNULE   = wpboutik_get_endpoint_url( 'order-received', $order_id, $checkout_url );
			$PBX_ATTENTE  = wpboutik_get_endpoint_url( 'order-received', $order_id, $checkout_url ); // Retourne code réponse 99999 pour moyen de paiement qui nécessite un délai de confirmation de paiement
			$hash         = 'SHA512';
			$dateTime     = date( "c" );

			$PBX_SHOPPINGCART = <<<XML
<?xml version='1.0' encoding='utf-8'?>
<shoppingcart>
    <total>
        <totalQuantity>{$total_qty}</totalQuantity>
    </total>
</shoppingcart>
XML;

			$PBX_BILLING = <<<XML
<?xml version='1.0' encoding='utf-8'?>
<Billing>
    <Address>
        <FirstName>{$billing_details['first_name']}</FirstName>
        <LastName>{$billing_details['last_name']}</LastName>
        <Address1>{$billing_details['address']['line1']}</Address1>
        <CountryCode>{$billing_details['address']['country']}</CountryCode>
        <ZipCode>{$billing_details['address']['postal_code']}</ZipCode>
        <City>{$billing_details['address']['city']}</City>
    </Address>
</Billing>
XML;
			$msg         = "PBX_SITE=" . $paybox_num_site . "&PBX_RANG=" . $paybox_num_rang . "&PBX_IDENTIFIANT=" . $paybox_id . "&PBX_TOTAL=" . $total . "&PBX_DEVISE=" . $devise_code . "&PBX_CMD=" . $order_id . "&PBX_PORTEUR=" . $email . "&PBX_RETOUR=Mt:M;Ref:R;Auto:A;Retour:E&PBX_EFFECTUE=" . $PBX_EFFECTUE . "&PBX_REFUSE=" . $PBX_REFUSE . "&PBX_ANNULE=" . $PBX_ANNULE . "&PBX_ATTENTE=" . $PBX_ATTENTE . "&PBX_REPONDRE_A=" . $repondre_a . "&PBX_HASH=" . $hash . "&PBX_TIME=" . $dateTime . "&PBX_SHOPPINGCART=" . self::linearise_xml( $PBX_SHOPPINGCART ) . "&PBX_BILLING=" . self::linearise_xml( $PBX_BILLING );

			$binKey = pack( "H*", $paybox_secret_key );
			$hmac   = strtoupper( hash_hmac( 'sha512', $msg, $binKey ) );

			$data = array(
				'PBX_SITE'         => $paybox_num_site,
				'PBX_RANG'         => $paybox_num_rang,
				'PBX_IDENTIFIANT'  => $paybox_id,
				'PBX_TOTAL'        => $total,
				'PBX_DEVISE'       => $devise_code,
				'PBX_CMD'          => $order_id,
				'PBX_PORTEUR'      => $email,
				'PBX_RETOUR'       => 'Mt:M;Ref:R;Auto:A;Retour:E',
				'PBX_EFFECTUE'     => $PBX_EFFECTUE,
				'PBX_REFUSE'       => $PBX_REFUSE,
				'PBX_ANNULE'       => $PBX_ANNULE,
				'PBX_ATTENTE'      => $PBX_ATTENTE,
				'PBX_REPONDRE_A'   => $repondre_a,
				'PBX_HASH'         => $hash,
				'PBX_TIME'         => $dateTime,
				'PBX_HMAC'         => $hmac,
				'PBX_SHOPPINGCART' => self::linearise_xml( $PBX_SHOPPINGCART ),
				'PBX_BILLING'      => self::linearise_xml( $PBX_BILLING )
			);

			$formHtml = '<form id="wpbFormPaybox" method="POST" action="' . WPBOUTIK_PAYBOX_URL . '">';
			foreach ( $data as $key => $value ) {
				$formHtml .= '<input type="hidden" name="' . $key . '" value="' . htmlentities( $value ) . '">';
			}
			$formHtml .= '</form>';

			echo wp_send_json(
				array(
					'success'   => true,
					'form_html' => $formHtml
				)
			);
			wp_die();
		} catch ( \Exception $e ) {
			echo wp_send_json(
				array(
					'success'      => false,
					'errorMessage' => $e->getMessage()
				)
			);
			wp_die();
		}
	}

	public function wpboutik_ajax_create_order() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'create-order-nonce' ) ) {
			return;
		}

		//email-address=kulka.nicolas%40gmail.com
		//shipping_first_name=Nicolas
		//last-name=KULKA
		//company=&
		//address=177%20all%C3%A9e%20de%20la%20Roseraie
		// city=FOUJU&country=United%20States
		//postal-code=77390&phone=0760956645

		//&apartment=&&region=&&payment-type=on&card-number=&name-on-card=&expiration-date=&cvc=&quantity=3"

		if ( ! isset( $_POST['datas'] ) ) {
			return;
		}

		parse_str( $_POST['datas'], $form_datas );

		do_action( 'wpboutik_ajax_create_order', $form_datas );

		$datas = [
			'email_address'            => sanitize_email( $form_datas['email_address'] ),
			'shipping_first_name'      => sanitize_text_field( $form_datas['shipping_first_name'] ),
			'shipping_last_name'       => sanitize_text_field( $form_datas['shipping_last_name'] ),
			'shipping_company'         => sanitize_text_field( $form_datas['shipping_company'] ),
			'shipping_address'         => sanitize_text_field( $form_datas['shipping_address'] ),
			'shipping_city'            => sanitize_text_field( $form_datas['shipping_city'] ),
			'shipping_country'         => sanitize_text_field( $form_datas['shipping_country'] ),
			'shipping_postal_code'     => sanitize_text_field( $form_datas['shipping_postal_code'] ),
			'shipping_phone'           => sanitize_text_field( $form_datas['shipping_phone'] ),
			'billing_first_name'       => sanitize_text_field( $form_datas['billing_first_name'] ),
			'billing_last_name'        => sanitize_text_field( $form_datas['billing_last_name'] ),
			'billing_company'          => sanitize_text_field( $form_datas['billing_company'] ),
			'billing_address'          => sanitize_text_field( $form_datas['billing_address'] ),
			'billing_city'             => sanitize_text_field( $form_datas['billing_city'] ),
			'billing_country'          => sanitize_text_field( $form_datas['billing_country'] ),
			'billing_postal_code'      => sanitize_text_field( $form_datas['billing_postal_code'] ),
			'billing_phone'            => sanitize_text_field( $form_datas['billing_phone'] ),
			'order_comments'           => sanitize_text_field( $form_datas['order_comments'] ),
			'method_id'                => ( isset( $form_datas['delivery-method'] ) ) ? sanitize_text_field( $form_datas['delivery-method'] ) : '',
			'list_point_code'          => ( isset( $form_datas[ 'code_point_' . $form_datas['delivery-method'] ] ) ) ? sanitize_text_field( $form_datas[ 'code_point_' . $form_datas['delivery-method'] ] ) : '',
			'list_point_address'       => ( isset( $form_datas[ 'address_point_' . $form_datas['delivery-method'] ] ) ) ? sanitize_text_field( $form_datas[ 'address_point_' . $form_datas['delivery-method'] ] ) : '',
			'billing_same_as_shipping' => sanitize_text_field( $form_datas['same-as-shipping'] ),
			'payment_type'             => sanitize_text_field( $form_datas['payment-type'] ),
			'tva_intra'                => ( isset( $form_datas['tva_intra'] ) ) ? sanitize_text_field( $form_datas['tva_intra'] ) : ''
		];

		$method = get_option( 'wpboutik_options_shipping_method_' . sanitize_text_field( $form_datas['delivery-method'] ) );

		$ordertotal = $subtotal = $total_qty = 0;
		if ( ! WPB()->cart->is_empty() ) {
			$taxes_class  = array();
			$activate_tax = wpboutik_get_option_params( 'activate_tax' );
			foreach ( WPB()->cart->get_cart() as $cart_item_key => $subArray ) {
				$subArray = (object) $subArray;

				$selling_fees = get_post_meta( $subArray->product_id, 'selling_fees', true );
				if ( empty( $selling_fees ) || ( ! empty( $subArray->customization ) && ! empty( $subArray->customization['renew'] ) ) ) {
					$selling_fees = 0;
				}

				if ( $subArray->variation_id != "0" ) {
					$id_for_tax_class = $subArray->variation_id;
					$variants         = get_post_meta( $subArray->product_id, 'variants', true );
					$variation        = wpboutik_get_variation_by_id( json_decode( $variants ), $subArray->variation_id );
					$price            = ( strpos( $subArray->variation_id, 'custom' ) !== false ) ? $subArray->customization['gift_card_price'] : $variation->price;
				} else {
					$id_for_tax_class = $subArray->product_id;
					$price            = get_post_meta( $subArray->product_id, 'price', true );
				}

				if ( $activate_tax ) {
					$tax_class                                      = get_post_meta( $subArray->product_id, 'tax', true );
					$taxes_class[ $tax_class ][ $id_for_tax_class ] = $subArray->quantity * ( $price + $selling_fees);
				}

				$subtotal  = $subtotal + ( $subArray->quantity * ( $price + $selling_fees) );
				$total_qty = $total_qty + $subArray->quantity;
			}

			$wp_get_discount           = wpb_get_discount_cart( $subtotal, WPB()->cart->get_cart(), $activate_tax, $taxes_class );
			$discount                  = ( $wp_get_discount['discount'] ?? 0 );
			$taxes_class               = ( $wp_get_discount['taxes_class'] ?? $taxes_class );
			$id_products_with_discount = ( $wp_get_discount['id_products_with_discount'] ?? [] );
			$coupon_id                 = ( $wp_get_discount['coupon_id'] ?? null );

			$tax = 0;
			if ( $taxes_class ) {
				$tax_rates = get_wpboutik_tax_rates();
				if ( isset( $tax_rates[ $form_datas['shipping_country'] ] ) ) {
					foreach ( $taxes_class as $tax_class => $products_of_tax ) {
						$count = 0;
						foreach ( $products_of_tax as $value ) {
							$count += $value;
						}
						$tax_value = round( ( $count ) * ( $tax_rates[ $form_datas['shipping_country'] ][ 'percent_tx_' . $tax_class ] / 100 ), 2 );
						$tax       += $tax_value;
					}
				}
			}

			$shipping = 0;
			if ( $method ) {
				$shipping = self::wpb_get_shipping_price( $method, $form_datas, WPB()->cart->get_cart() );
			}

			$ordertotal = $subtotal - $discount + $shipping + $tax;
		}

		$options = get_option( 'wpboutik_options' );


		$wp_user_id = '';
		if ( is_user_logged_in() ) {
			$wp_user_id = get_current_user_id();
		} else {
			$exists = email_exists( $datas['email_address'] );
			if ( $exists ) {
				$wp_user_id = $exists;
			} else {
				// Create user
				$user_id = wp_insert_user( array(
					'user_login' => $datas['email_address'],
					'user_email' => $datas['email_address'],
					'role'       => 'customer-wpb'
				) );
				wp_new_user_notification( $user_id, null, 'both' );
				// On success.
				if ( ! is_wp_error( $user_id ) ) {
					$wp_user_id = $user_id;
				}
			}
		}

		if ( empty( $wp_user_id ) ) {
			return;
		}

		delete_option( 'wpboutik_cart_abandoned_' . $wp_user_id );

		// TODO voir pour dire que le panier abandonné a été validé finalement ?

		$products_paypal = [];
		if ( $datas['payment_type'] === 'paypal' ) {
			foreach ( WPB()->cart->get_cart() as $stored_product ) {
				$stored_product = (object) $stored_product;

				$selling_fees = get_post_meta( $stored_product->product_id, 'selling_fees', true );
				if ( empty( $selling_fees ) || ( ! empty( $stored_product->customization ) && ! empty( $stored_product->customization['renew'] ) ) ) {
					$selling_fees = 0;
				}

				$price          = get_post_meta( $stored_product->product_id, 'price', true );
				$name           = get_the_title( $stored_product->product_id );
				// Les produits à payer avec leurs details
				$products_paypal[] = array(
					'name'        => $name,
					'description' => "Description du produit 1",
					'quantity'    => $stored_product->quantity,
					'unit_amount' => array(
						'value'         => $price + $selling_fees,
						'currency_code' => get_wpboutik_currency()
					)
				);
			}
		}

		// si le panier est abandonné je récupère l'app_id (identifiant laravel) sinon null
		// l'enregistrement de l'order_id dans le panier abandonné permet de le considérer comme récupéré (transformé en commande)
		$app_id = WPB()->session->abandonned_app_id();

		// // si le panier est enregistré comme abandonné, je supprime ses metas liées
		if ( WPB()->session->is_abandoned_cart() ) {
			WPB()->session->delete_abandonned_options();
		}
		$api_request = WPB_Api_Request::request( 'order', 'create' )
		                              ->add_multiple_to_body( [
			                              'lost_cart'                 => $app_id,
			                              'apikey'                    => $options['apikey'],
			                              'options'                   => $options,
			                              'wp_user_id'                => $wp_user_id,
			                              'datas_form'                => $datas,
			                              'products'                  => WPB()->cart->get_cart(),
			                              //'tax_rates'                 => $tax_rates[ $form_datas['shipping_country'] ]['percent_tx_standard'],
			                              'subtotal'                  => $subtotal,
			                              'discount'                  => round( $discount, 2 ),
			                              'shipping'                  => $shipping,
			                              'tax'                       => $tax,
			                              'total'                     => round( $ordertotal, 2 ),
			                              'coupon_id'                 => $coupon_id,
			                              'id_products_with_discount' => ( ! empty ( $id_products_with_discount ) ? json_encode( $id_products_with_discount ) : null ),
			                              'tva_intra'                 => ! empty( $form_datas['tva_intra'] ) ? $form_datas['tva_intra'] : null,
			                              'gift_card'                 => WPB_Gift_Card::get_gift_card_from_cookie(),
			                              'total_after_gift_card'     => round( WPB_Gift_Card::get_finale_price( $ordertotal ), 2 )
		                              ] )->exec();

		// For debug soucis
		/*var_dump( $api_request->get_response_body() );
		die;*/

		if ( ! $api_request->is_error() ) {
			WPB_Gift_Card::remove_all_cookie();
			setcookie( 'wpboutik_paymentintent_id', '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
			setcookie( 'wpboutik_coupons_code', '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );

			foreach ( $datas as $key => $value ) {
				add_user_meta( $wp_user_id, 'wpboutik_' . $key, $value );
			}

			$data = (array) json_decode( $api_request->get_response_body() );

			if ( $datas['billing_same_as_shipping'] === 'on' ) {
				$billing_details_arr_data = array(
					'name'       => $datas['shipping_first_name'] . ' ' . $datas['shipping_last_name'],
					'first_name' => $datas['shipping_first_name'],
					'last_name'  => $datas['shipping_last_name'],
					'address'    => array(
						'city'        => $datas['shipping_city'],
						'country'     => $datas['shipping_country'],
						'postal_code' => $datas['shipping_postal_code'],
						'line1'       => $datas['shipping_address'],
					),
					'phone'      => $datas['shipping_phone'],
					'email'      => $datas['email_address']
				);
			} else {
				$billing_details_arr_data = array(
					'name'       => $datas['billing_first_name'] . ' ' . $datas['billing_last_name'],
					'first_name' => $datas['billing_first_name'],
					'last_name'  => $datas['billing_last_name'],
					'address'    => array(
						'city'        => $datas['billing_city'],
						'country'     => $datas['billing_country'],
						'postal_code' => $datas['billing_postal_code'],
						'line1'       => $datas['billing_address'],
					),
					'phone'      => $datas['billing_phone'],
					'email'      => $datas['email_address']
				);
			}

			echo wp_send_json( array_merge( array(
				'success'         => true,
				'payment_type'    => $datas['payment_type'],
				'products_paypal' => $products_paypal,
				'subtotal'        => $subtotal,
				'total_qty'       => $total_qty,
				'discount'        => round( $discount, 2 ),
				'shipping'        => $shipping,
				'tax'             => $tax,
				'total'           => round( $ordertotal, 2 ),
				'billing_details' => $billing_details_arr_data,
				'order_id'        => $data['order_id'],
				'url'             => apply_filters( 'wpboutik_get_return_url', wpboutik_get_endpoint_url( 'order-received', $data['order_id'], wpboutik_get_page_permalink( 'checkout' ) ), $api_request ),
				$data
			) ) );
			wp_die();
		}
	}

	public function wpboutik_ajax_order_cancel_payment() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'checkout-cancel-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['order_id'] ) ) {
			return;
		}

		$order_id = sanitize_text_field( $_POST['order_id'] );

		$options = get_option( 'wpboutik_options' );

		$api_request = WPB_Api_Request::request( 'order', 'cancel' )
		                              ->add_multiple_to_body( [
			                              'options'  => $options,
			                              'order_id' => $order_id,
		                              ] )->exec();

		if ( ! $api_request->is_error() ) {
			wp_send_json_success();
		}
	}

	public function wpboutik_ajax_create_mail_back_in_stock() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'create-back-in-stock-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['product_id'] ) ) {
			return;
		}

		if ( ! isset( $_POST['email'] ) ) {
			return;
		}

		$product_id   = sanitize_text_field( $_POST['product_id'] );
		$variation_id = sanitize_text_field( $_POST['variation_id'] );
		$email        = sanitize_email( $_POST['email'] );
		$options      = get_option( 'wpboutik_options' );
		$response     = wp_remote_post(
			sprintf( '%s/create_mail_back_in_stock', WPBOUTIK_APP_URL . 'api' ),
			array(
				'body'      => wp_json_encode( array(
					'product_id'   => $product_id,
					'variation_id' => $variation_id,
					'email'        => $email,
					'options'      => $options,
				) ),
				'timeout'   => 60,
				'headers'   => array(
					'technology'   => 'wordpress',
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'sslverify' => false
			)
		);
		if ( ! is_wp_error( $response ) ) {
			echo wp_send_json( array(
				'success' => $response
			) );
			wp_die();
		}
	}

	public function wpboutik_ajax_checkout_change_payment_type() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'change-payment-type-nonce' ) ) {
			return;
		}

		$method = '';
		if ( isset( $_POST['method_shipping_id'] ) ) {
			$method = get_option( 'wpboutik_options_shipping_method_' . sanitize_text_field( $_POST['method_shipping_id'] ) );
		}

		if ( $method ) {
			$ordertotal = $subtotal = 0;
			if ( ! WPB()->cart->is_empty() ) {
				$taxes_class  = array();
				$activate_tax = wpboutik_get_option_params( 'activate_tax' );
				foreach ( WPB()->cart->get_cart() as $cart_item_key => $subArray ) {
					$subArray = (object) $subArray;

					$selling_fees = get_post_meta( $subArray->product_id, 'selling_fees', true );
					if ( empty( $selling_fees ) || ( ! empty( $subArray->customization ) && ! empty( $subArray->customization['renew'] ) ) ) {
						$selling_fees = 0;
					}

					if ( $subArray->variation_id != "0" ) {
						$id_for_tax_class = $subArray->variation_id;
						$variants         = get_post_meta( $subArray->product_id, 'variants', true );
						$variation        = wpboutik_get_variation_by_id( json_decode( $variants ), $subArray->variation_id );
						$price            = ( strpos( $subArray->variation_id, 'custom' ) !== false ) ? $subArray->customization['gift_card_price'] : $variation->price;
					} else {
						$id_for_tax_class = $subArray->product_id;
						$price            = get_post_meta( $subArray->id, 'price', true );
					}

					if ( $activate_tax ) {
						$tax_class                                      = get_post_meta( $subArray->product_id, 'tax', true );
						$taxes_class[ $tax_class ][ $id_for_tax_class ] = $subArray->quantity * ( $price + $selling_fees );
					}

					$subtotal = $subtotal + ( $subArray->quantity * ( $price + $selling_fees ) );
				}
				$shipping_country_key = sanitize_text_field( $_POST['shipping_country_key'] );

				$wp_get_discount = wpb_get_discount_cart( $subtotal, WPB()->cart->get_cart(), $activate_tax, $taxes_class );
				$discount        = ( $wp_get_discount['discount'] ?? 0 );
				$taxes_class     = ( $wp_get_discount['taxes_class'] ?? $taxes_class );

				$tax = 0;
				if ( $taxes_class ) {
					$tax_rates = get_wpboutik_tax_rates();
					if ( isset( $tax_rates[ $shipping_country_key ] ) ) {
						foreach ( $taxes_class as $tax_class => $products_of_tax ) {
							$count = 0;
							foreach ( $products_of_tax as $value ) {
								$count += $value;
							}
							$tax_value = round( ( $count ) * ( $tax_rates[ $shipping_country_key ][ 'percent_tx_' . $tax_class ] / 100 ), 2 );
							$tax       += $tax_value;
						}
					}
				}

				$shipping = 0;
				if ( $method ) {
					$shipping = self::wpb_get_shipping_price( $method, $_POST, WPB()->cart->get_cart(), $shipping_country_key );
				}

				$ordertotal = $subtotal - $discount + $shipping + $tax;
			}

			updatePaymentIntentStripe( $method, $shipping_country_key, round( $ordertotal, 2 ), $_POST );

			$data = array(
				'success' => true
			);
		} else {
			$data = array(
				'error' => 'Problem change payment type',
			);
		}

		echo wp_send_json( $data );
		wp_die();
	}

	public function wpboutik_ajax_checkout_add_price_delivery_method() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'update-delivery-method-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['method_id'] ) ) {
			return;
		}

		if ( ! isset( $_POST['payment_type'] ) ) {
			return;
		}

		$payment_type = sanitize_text_field( $_POST['payment_type'] );

		$method = get_option( 'wpboutik_options_shipping_method_' . sanitize_text_field( $_POST['method_id'] ) );

		if ( $method ) {
			$ordertotal = $subtotal = 0;
			if ( ! WPB()->cart->is_empty() ) {
				$taxes_class  = array();
				$activate_tax = wpboutik_get_option_params( 'activate_tax' );
				foreach ( WPB()->cart->get_cart() as $cart_item_key => $subArray ) {
					$subArray = (object) $subArray;

					$selling_fees = get_post_meta( $subArray->product_id, 'selling_fees', true );
					if ( empty( $selling_fees ) || ( ! empty( $subArray->customization ) && ! empty( $subArray->customization['renew'] ) ) ) {
						$selling_fees = 0;
					}

					if ( $subArray->variation_id != "0" ) {
						$id_for_tax_class = $subArray->variation_id;
						$variants         = get_post_meta( $subArray->product_id, 'variants', true );
						$variation        = wpboutik_get_variation_by_id( json_decode( $variants ), $subArray->variation_id );
						$price            = ( strpos( $subArray->variation_id, 'custom' ) !== false ) ? $subArray->customization['gift_card_price'] : $variation->price;
					} else {
						$id_for_tax_class = $subArray->product_id;
						$price            = get_post_meta( $subArray->product_id, 'price', true );
					}

					if ( $activate_tax ) {
						$tax_class                                      = get_post_meta( $subArray->product_id, 'tax', true );
						$taxes_class[ $tax_class ][ $id_for_tax_class ] = $subArray->quantity * ( $price + $selling_fees );
					}

					$subtotal = $subtotal + ( $subArray->quantity * ( $price + $selling_fees ) );
				}
				$shipping_country_key = sanitize_text_field( $_POST['shipping_country_key'] );

				$wp_get_discount = wpb_get_discount_cart( $subtotal, WPB()->cart->get_cart(), $activate_tax, $taxes_class );
				$discount        = ( $wp_get_discount['discount'] ?? 0 );
				$taxes_class     = ( $wp_get_discount['taxes_class'] ?? $taxes_class );

				$tax      = 0;
				$tax_show = array();
				if ( $taxes_class ) {
					$tax_rates = get_wpboutik_tax_rates();
					if ( isset( $tax_rates[ $shipping_country_key ] ) ) {
						foreach ( $taxes_class as $tax_class => $products_of_tax ) {
							$count = 0;
							foreach ( $products_of_tax as $value ) {
								$count += $value;
							}
							$tax_value = round( ( $count ) * ( $tax_rates[ $shipping_country_key ][ 'percent_tx_' . $tax_class ] / 100 ), 2 );
							$tax       += $tax_value;

							$tax_show[ $tax_class ] = array(
								'value' => wpboutik_format_number( $tax_value ),
								'name'  => $tax_rates[ $shipping_country_key ][ 'name_tx_' . $tax_class ]
							);
						}
					}
				}

				$shipping = 0;
				if ( $method ) {
					$shipping = self::wpb_get_shipping_price( $method, $_POST, WPB()->cart->get_cart(), $shipping_country_key );
				}

				$ordertotal = $subtotal - $discount + $shipping + $tax;
			}

			if ( 'card' == $payment_type ) {
				updatePaymentIntentStripe( $method, $shipping_country_key, round( $ordertotal, 2 ), $_POST );
			}

			$data = array(
				'success'          => true,
				'method_flat_rate' => wpboutik_format_number( $shipping ),
				'tax'              => $tax_show,
				'ordertotal'       => wpboutik_format_number( $ordertotal ),
			);
		} else {
			$data = array(
				'error' => 'Problem method_id',
			);
		}

		echo wp_send_json( $data );
		wp_die();
	}

	public function wpboutik_ajax_checkout_modify_tax_rate() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'modify-tax-rate-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['shipping_country_key'] ) ) {
			return;
		}

		if ( ! isset( $_POST['payment_type'] ) ) {
			return;
		}

		$shipping_country_key = sanitize_text_field( $_POST['shipping_country_key'] );
		$payment_type         = sanitize_text_field( $_POST['payment_type'] );

		$activate_tax = wpboutik_get_option_params( 'activate_tax' );

		if ( ! $activate_tax ) {
			return;
		}

		$tax_rates = get_wpboutik_tax_rates();

		if ( $tax_rates ) {
			$method = '';
			if ( isset( $_POST['method_shipping_id'] ) ) {
				$method = get_option( 'wpboutik_options_shipping_method_' . sanitize_text_field( $_POST['method_shipping_id'] ) );
			}

			$taxes_class = array();
			$ordertotal  = $subtotal = 0;
			if ( ! WPB()->cart->is_empty() ) {
				foreach ( WPB()->cart->get_cart() as $cart_item_key => $subArray ) {
					$subArray = (object) $subArray;

					$selling_fees = get_post_meta( $subArray->product_id, 'selling_fees', true );
					if ( empty( $selling_fees ) || ( ! empty( $subArray->customization ) && ! empty( $subArray->customization['renew'] ) ) ) {
						$selling_fees = 0;
					}

					if ( $subArray->variation_id != "0" ) {
						$id_for_tax_class = $subArray->variation_id;
						$variants         = get_post_meta( $subArray->product_id, 'variants', true );
						$variation        = wpboutik_get_variation_by_id( json_decode( $variants ), $subArray->variation_id );
						$price            = ( strpos( $subArray->variation_id, 'custom' ) !== false ) ? $subArray->customization['gift_card_price'] : $variation->price;
					} else {
						$id_for_tax_class = $subArray->product_id;
						$price            = get_post_meta( $subArray->product_id, 'price', true );
					}

					$tax_class                                      = get_post_meta( $subArray->product_id, 'tax', true );
					$taxes_class[ $tax_class ][ $id_for_tax_class ] = $subArray->quantity * ( $price + $selling_fees );
					$subtotal                                       = $subtotal + ( $subArray->quantity * ( $price + $selling_fees ) );
				}

				$wp_get_discount = wpb_get_discount_cart( $subtotal, WPB()->cart->get_cart(), $activate_tax, $taxes_class );
				$discount        = ( $wp_get_discount['discount'] ?? 0 );
				$taxes_class     = ( $wp_get_discount['taxes_class'] ?? $taxes_class );

				$tax      = 0;
				$tax_show = array();
				if ( isset( $tax_rates[ $shipping_country_key ] ) ) {
					foreach ( $taxes_class as $tax_class => $products_of_tax ) {
						$count = 0;
						foreach ( $products_of_tax as $value ) {
							$count += $value;
						}
						$tax_value = round( ( $count ) * ( $tax_rates[ $shipping_country_key ][ 'percent_tx_' . $tax_class ] / 100 ), 2 );
						$tax       += $tax_value;

						$tax_show[ $tax_class ] = array(
							'value' => wpboutik_format_number( $tax_value ),
							'name'  => $tax_rates[ $shipping_country_key ][ 'name_tx_' . $tax_class ]
						);
					}
				}

				$shipping = 0;
				if ( $method ) {
					$shipping = self::wpb_get_shipping_price( $method, $_POST, WPB()->cart->get_cart(), $shipping_country_key );
				}

				$ordertotal = $subtotal - $discount + $shipping + $tax;
			}

			if ( 'card' == $payment_type ) {
				updatePaymentIntentStripe( $method, $shipping_country_key, round( $ordertotal, 2 ), $_POST );
			}

			$data = array(
				'success'    => true,
				'tax'        => $tax_show,
				'ordertotal' => wpboutik_format_number( $ordertotal ),
			);
		} else {
			$data = array(
				'error' => 'Problem tax_rate',
			);
		}

		echo wp_send_json( $data );
		wp_die();
	}

	public function wpboutik_ajax_add_to_cart() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'add-to-cart-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['product_id'] ) ) {
			return;
		}

		$product_id   = apply_filters( 'wpboutik_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$product_name = apply_filters( 'wpboutik_add_to_cart_product_name', sanitize_text_field( $_POST['product_name'] ) );
		if ( get_post_meta( absint( $_POST['product_id'] ), 'type', true ) === 'gift_card' ) {
			$product_name = get_the_title( absint( $_POST['product_id'] ) );
		}
		$quantity          = empty( $_POST['quantity'] ) ? 1 : intval( wp_unslash( $_POST['quantity'] ) );
		$variation_id      = sanitize_text_field( $_POST['variation_id'] );
		$passed_validation = apply_filters( 'wpboutik_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );
		$variations        = array();
		$customizations    = ( ! isset ( $_POST['customization'] ) ) ? null : wp_unslash( $_POST['customization'] );
		$variants          = get_post_meta( absint( $_POST['product_id'] ), 'variants', true );
		if ( ! empty( $variants ) && '[]' != $variants ) {
			$variations = $variants;
		}

		if ( $passed_validation && false !== WPB()->cart->add_to_cart( $product_id, $product_name, $quantity, $variation_id, $variations, [], $customizations ) && 'publish' === $product_status ) {
			do_action( 'wpboutik_ajax_added_to_cart', $product_id );

			self::get_refreshed_fragments( $product_id );

		} else {
			$data = array(
				'error'       => true,
				'product_url' => apply_filters( 'wpboutik_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
			);

			wp_send_json( $data );
		}
	}
	
	public function wpboutik_ajax_stop_payment_license() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wpb-stop-licenses' ) ) {
			return;
		}

		if ( ! isset( $_POST['subscription'] ) || empty($_POST['subscription']) ) {
			return;
		}
		$request = WPB_Api_Request::request('licenses', 'cancel')->add_multiple_to_body( [
			'options'         => get_option( 'wpboutik_options' ),
			'subscription_id' => $_POST['subscription'],
		] )->exec();
		print json_encode( [
			'result' => !$request->is_error(),
			'response' => json_decode($request->get_response_body())
		] );
		wp_die();
	}
	
	public function wpboutik_ajax_remove_url_license() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wpb-remove-url' ) ) {
			return;
		}

		if ( ! isset( $_POST['subscription'] ) || !isset($_POST['url']) ) {
			return;
		}

		$request = WPB_Api_Request::request('licenses', 'remove_url')->add_multiple_to_body( [
			'subscription' => $_POST['subscription'],
			'url' => $_POST['url'],
		] )->exec();
		print json_encode( [
			'result' => !$request->is_error(),
			'response' => json_decode($request->get_response_body())
		] );
		wp_die();
	}

	public function wpboutik_ajax_add_to_cart_renew() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'wpb-renew-licenses' ) ) {
			return;
		}

		if ( ! isset( $_POST['product_id'] ) || ! isset( $_POST['code'] ) ) {
			return;
		}

		$product_id   = apply_filters( 'wpboutik_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$product_name = get_the_title($_POST['product_id']);
		$variants          = get_post_meta( absint( $_POST['product_id'] ), 'variants', true );
		if ( ! empty( $variants ) && '[]' != $variants ) {
			$variations = $variants;
		}
		$variation_id = isset($_POST['variation_id']) ? $_POST['variation_id'] : '0';
		if ( false !== WPB()->cart->add_to_cart( $product_id, $product_name, 1, $variation_id, $variations, [], ['renew' => $_POST['code']] )) {
			do_action( 'wpboutik_ajax_added_to_cart', $product_id );
			self::get_refreshed_fragments( $product_id );
		} else {
			$data = array(
				'error'       => true,
				'product_url' => apply_filters( 'wpboutik_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
			);
			wp_send_json( $data );
		}
	}

	public function wpboutik_ajax_save_data_checkout() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'checkout-save-data-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['datas'] ) ) {
			return;
		}

		WPB()->cart->save_data_checkout( $_POST['datas'] );
	}

	/**
	 * Get a refreshed cart fragment, including the mini cart HTML.
	 */
	public static function get_refreshed_fragments( $product_id, $minicart_open = false ) {
		ob_start();

		include trailingslashit( WPBOUTIK_TEMPLATES ) . '/cart/mini-cart.php';

		$mini_cart = ob_get_clean();

		$currency_symbol = get_wpboutik_currency_symbol();

		$subtotal = $shipping = $tax = 0;
		foreach ( WPB()->cart->get_cart() as $cart_item_key => $subArray ) {
			$subArray = (object) $subArray;

			$selling_fees = get_post_meta( $subArray->product_id, 'selling_fees', true );
			if ( empty( $selling_fees ) || ( ! empty( $subArray->customization ) && ! empty( $subArray->customization['renew'] ) ) ) {
				$selling_fees = 0;
			}

			if ( $subArray->variation_id != "0" ) {
				$variants  = get_post_meta( $subArray->product_id, 'variants', true );
				$variation = wpboutik_get_variation_by_id( json_decode( $variants ), $subArray->variation_id );
				$price     = ( strpos( $subArray->variation_id, 'custom' ) !== false ) ? $subArray->customization['gift_card_price'] : $variation->price;
			} else {
				$price = get_post_meta( $subArray->product_id, 'price', true );
			}

			$subtotal = $subtotal + ( $subArray->quantity * ( $price + $selling_fees ) );
		}
		$ordertotal = $subtotal + $shipping + $tax;

		$data = array(
			'fragments'     => apply_filters(
				'wpboutik_add_to_cart_fragments',
				array(
					'WPBpanierDropdown' => $mini_cart,
				)
			),
			'cart_hash'     => WPB()->cart->get_cart_hash(),
			'product_id'    => $product_id,
			'count_product' => WPB()->cart->get_cart_contents_count(),
			'subtotal'      => $subtotal . $currency_symbol,
			'ordertotal'    => $ordertotal . $currency_symbol
		);

		wp_send_json( $data );
	}

	public function wpboutik_ajax_remove_to_cart() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'remove-to-cart-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['product_id'] ) ) {
			return;
		}

		ob_start();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$cart_item_key = wpb_clean( isset( $_POST['cart_item_key'] ) ? wp_unslash( $_POST['cart_item_key'] ) : '' );

		if ( $cart_item_key && false !== WPB()->cart->remove_cart_item( $cart_item_key ) ) {
			self::get_refreshed_fragments( $_POST['product_id'] );
			setcookie( 'wpboutik_coupons_code', '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
			// si après modification du panier, ce dernier est vide
			// et si le panier était considérer comme abandonné
			if ( WPB()->cart->is_empty() && WPB()->session->is_abandoned_cart() ) {
				// dans le cas ou il est déjà enregistré sur l'app
				$app_id = WPB()->session->abandonned_app_id();
				if ( ! ! $app_id ) {
					// nous demandons sa suppression (ce n'est plus un panier abandonné)
					$api_request = WPB_Api_Request::request( 'abandonned_cart', 'delete' )
					                              ->add_multiple_to_body( [
						                              'app_id' => $app_id,
					                              ] )->exec();
					// nous supprimons les données de panier abandonné
					WPB()->session->delete_abandonned_options();
				}
			}
		} else {
			wp_send_json_error();
		}
	}

	public function wpboutik_ajax_update_qty_to_cart() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'update-qty-to-cart-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['product_id'] ) ) {
			return;
		}

		$product_id   = apply_filters( 'wpboutik_update_qty_to_cart_product_id', absint( $_POST['product_id'] ) );
		$variation_id = apply_filters( 'wpboutik_update_qty_to_cart_variation_id', sanitize_text_field( $_POST['variation_id'] ) );
		do_action( 'wpboutik_ajax_updated_qty_to_cart', $product_id );
		$quantity = absint( $_POST['quantity'] );

		$cart_updated = false;

		if ( ! WPB()->cart->is_empty() ) {
			foreach ( WPB()->cart->get_cart() as $cart_item_key => $values ) {

				if ( $values['product_id'] !== $product_id || $values['variation_id'] !== $variation_id ) {
					continue;
				}

				// Skip product if no updated quantity was posted.
				//|| ! isset( $cart_totals[ $cart_item_key ]['qty'] )
				/*if ( WPB()->cart->get_cart_item( $cart_item_key ) !== null ) {
					continue;
				}*/

				// Sanitize.
				/*$quantity = apply_filters( 'woocommerce_stock_amount_cart_item', preg_replace( '/[^0-9\.]/', '', $cart_totals[ $cart_item_key ]['qty'] ), $cart_item_key );

				if ( '' === $quantity || $quantity === $values['quantity'] ) {
					continue;
				}*/

				// Update cart validation.
				$passed_validation = apply_filters( 'wpboutik_update_cart_validation', true, $cart_item_key, $values, $quantity );

				if ( $passed_validation ) {
					WPB()->cart->set_quantity( $cart_item_key, $quantity, false );
					$cart_updated = true;
				}
			}
		}

		// Trigger action - let 3rd parties update the cart if they need to and update the $cart_updated variable.
		$cart_updated = apply_filters( 'wpboutik_update_cart_action_cart_updated', $cart_updated );

		if ( $cart_updated ) {
			WPB()->cart->calculate_totals();
		}

		self::get_refreshed_fragments( $product_id );
	}

	/**
	 * apply_coupon_code form.
	 *
	 * @throws \Exception On login error.
	 */
	public function apply_coupon_code() {

		if ( ! wp_verify_nonce( $_POST['nonce'], 'wpboutik-coupon-code-nonce' ) ) {
			return;
		}

		if ( isset( $_POST['coupon_code'] ) ) {
			setcookie( 'wpboutik_error_coupon_code', '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
			try {

				$cookie_set = false;

				$coupon_list = wpboutik_get_options_coupon_list();
				if ( $coupon_list ) {

					$products_id_in_cart = [];
					if ( ! WPB()->cart->is_empty() ) {
						foreach ( WPB()->cart->get_cart() as $product ) {
							$products_id_in_cart[] = ( $product['variation_id'] != 0 ) ? $product['variation_id'] : $product['product_id'];
						}
					}
					$products_id_in_cart_cat = wp_list_pluck( WPB()->cart->get_cart(), 'product_id' );

					foreach ( $coupon_list as $coupon_id ) {
						$apply  = true;
						$coupon = get_option( $coupon_id );

						//var_dump($coupon, $_POST['coupon_code']);

						// check coupon exist
						if ( $coupon['code'] != $_POST['coupon_code'] ) {
							continue;
						}

						// check limit code
						if ( $coupon['limit_code'] !== null ) {
							//Si usage > limit ==> continue
							if ( $coupon['usage_code'] >= $coupon['limit_code'] ) {
								$apply = false;
							}
						}

						// check limit user
						if ( isset( $coupon['limit_user'] ) && $coupon['limit_user'] !== null ) {
							//Si usage > limit ==> continue
							if ( isset( $coupon['usage_user'] ) ) {
								$usage_user = json_decode( $coupon['usage_user'] );
								if ( is_user_logged_in() ) {
									$current_user_id = get_current_user_id();
									if ( isset( $usage_user->{$current_user_id} ) ) {
										if ( $usage_user->{$current_user_id} >= $coupon['limit_user'] ) {
											$apply = false;
										}
									}
								}
							}
						}

						// check date expire
						if ( $coupon['date_expire'] !== null ) {
							if ( new \DateTime( 'now' ) > new \DateTime( $coupon['date_expire'] ) ) {
								$apply = false;
							}
						}

						if ( ! empty( $coupon['id_products'] ) ) {
							if ( ! wpboutikCheckCommonElements( $coupon['id_products'], $products_id_in_cart ) ) {
								$apply = false;
							}
						}

						if ( ! empty( $coupon['id_categories'] ) ) {
							$apply_categories       = false;
							$list_products_category = new \WP_Query( array(
								'post_type'      => 'wpboutik_product',
								'posts_per_page' => - 1,
								'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
								'fields'         => 'ids',
								'tax_query'      => array(
									array(
										'taxonomy' => 'wpboutik_product_cat',
										'field'    => 'term_id',
										'terms'    => $coupon['id_categories'],
									)
								),
							) );

							foreach ( $list_products_category->posts as $id_product_coupon ) {
								if ( in_array( $id_product_coupon, $products_id_in_cart_cat ) ) {
									$apply_categories = true;
									break;
								}
							}
							if ( $apply_categories === false ) {
								$apply = false;
							}
						}

						if ( true === $apply ) {
							// For multiple coupon code
							/*$wpboutik_coupons_code = json_decode( stripslashes( $_COOKIE['wpboutik_coupons_code'] ) );
							if(empty($wpboutik_coupons_code)) {
								$wpboutik_coupons_code = [];
							}
							setcookie('wpboutik_coupons_code', json_encode( array_merge( $wpboutik_coupons_code, array('type' => $coupon['type'], 'valeur' => $coupon['valeur'] ) ) ), 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );*/

							$cookie_set = true;
							$id_code    = str_replace( 'wpboutik_options_coupon_code_', '', $coupon_id );
							setcookie( 'wpboutik_coupons_code', json_encode( array(
								'id'   => $id_code,
								'code' => $_POST['coupon_code']
							) ), 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
							break;
						} else {
							$cookie_set = false;
							setcookie( 'wpboutik_coupons_code', '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
						}
					}
				}

				if ( $cookie_set !== true ) {
					throw new \Exception( sprintf( 'Le code promo %s n\'est pas valide', sanitize_text_field( $_POST['coupon_code'] ) ) );
				} else {
					setcookie( 'wpboutik_error_coupon_code', '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
					echo wp_send_json( array(
						'success' => true,
						'url'     => wpboutik_get_page_permalink( 'checkout' )
					) );
					wp_die();
				}
			} catch ( \Exception $e ) {
				do_action( 'wpboutik_coupon_code_failed' );
				setcookie( 'wpboutik_error_coupon_code', $e->getMessage(), 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
				echo wp_send_json( array(
					'success' => false,
					'url'     => wpboutik_get_page_permalink( 'checkout' )
				) );
				wp_die();
			}
		}
	}

	/**
	 * apply_gift_card_code form.
	 *
	 * @throws \Exception On login error.
	 */
	public function apply_gift_card_code() {

		if ( ! wp_verify_nonce( $_POST['nonce'], 'wpboutik-gift-card-code-nonce' ) ) {
			return;
		}

		if ( isset( $_POST['coupon_code'] ) ) {
			setcookie( 'wpboutik_error_coupon_code', '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
			try {

				$cookie_set = false;

				$code = WPB_Gift_Card::find( $_POST['coupon_code'] );
				if ( $code ) {
					if ( get_option( 'wpboutik_options_gift_card_multiple' ) == 'no'
					     && $code['original_value'] != $code['available_value'] ) {
						throw new \Exception( sprintf( 'Ce code à déjà été utilisé.', sanitize_text_field( $_POST['coupon_code'] ) ) );
					}
					$cookie_set = true;
					WPB_Gift_Card::set_code_cookie( $code['id'], $_POST['coupon_code'] );
				}
				if ( $cookie_set !== true ) {
					throw new \Exception( sprintf( 'Ce code n\'est pas valide.', sanitize_text_field( $_POST['coupon_code'] ) ) );
				} else {
					WPB_Gift_Card::remove_error_cookie();
					echo wp_send_json( array(
						'success' => true,
						'url'     => wpboutik_get_page_permalink( 'checkout' )
					) );
					wp_die();
				}
			} catch ( \Exception $e ) {
				do_action( 'wpboutik_coupon_code_failed' );
				WPB_Gift_Card::set_error_cookie( $e->getMessage() );
				echo wp_send_json( array(
					'success' => false,
					'url'     => wpboutik_get_page_permalink( 'checkout' )
				) );
				wp_die();
			}
		}
	}

	public function wpboutik_ajax_checkout_boxtal_load_price_offers() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'checkout-boxtal-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['pays'] ) ) {
			return;
		}

		if ( ! isset( $_POST['ville'] ) ) {
			return;
		}

		if ( ! isset( $_POST['cp'] ) ) {
			return;
		}

		if ( ! isset( $_POST['adresse'] ) ) {
			return;
		}

		if ( ! isset( $_POST['type'] ) ) {
			return;
		}

		$method_list = wpboutik_get_options_shipping_method_list();
		if ( ! $method_list ) {
			return;
		}

		$datas_method_boxtal = [];

		$weight_colis = 0;
		if ( ! WPB()->cart->is_empty() ) {
			foreach ( WPB()->cart->get_cart() as $single_product ) {
				$weight      = get_post_meta( $single_product['product_id'], 'weight', true );
				$weight_unit = get_post_meta( $single_product['product_id'], 'weight_unit', true );
				if ( $weight_unit === 'kg' ) {
					$weight_colis += $weight;
				} else {
					// Gramme to kilos
					$weight_colis += $weight / 1000;
				}
			}
		}

		foreach ( $method_list as $method_name ) {
			$html      = '';
			$method_id = str_replace( 'wpboutik_options_shipping_method_', '', $method_name );
			$method    = get_option( $method_name );

			if ( isset( $method['boxtal_carrier'] ) && ! empty( $method['boxtal_carrier'] ) ) {
				$options = [
					'expediteur.pays'          => 'FR',
					'expediteur.code_postal'   => wpboutik_get_option_params( 'sender_postalcode' ),
					'expediteur.ville'         => wpboutik_get_option_params( 'sender_city' ),
					'expediteur.type'          => wpboutik_get_option_params( 'sender_type' ),
					'expediteur.adresse'       => wpboutik_get_option_params( 'sender_address' ),
					'destinataire.pays'        => $_POST['pays'],
					'destinataire.code_postal' => $_POST['cp'],
					'destinataire.ville'       => $_POST['ville'],
					'destinataire.type'        => $_POST['type'],
					'destinataire.adresse'     => $_POST['adresse'],
					'colis_1.poids'            => ( $weight_colis == 0 ) ? wpboutik_get_option_params( 'package_weight' ) : $weight_colis,
					'colis_1.longueur'         => ( wpboutik_get_option_params( 'package_length' ) ) ? wpboutik_get_option_params( 'package_length' ) : '20',
					'colis_1.largeur'          => ( wpboutik_get_option_params( 'package_width' ) ) ? wpboutik_get_option_params( 'package_width' ) : '20',
					'colis_1.hauteur'          => ( wpboutik_get_option_params( 'package_height' ) ) ? wpboutik_get_option_params( 'package_height' ) : '20',
					'code_contenu'             => '100',
					'operator'                 => $method['boxtal_carrier']
				];

				$boxtal_email    = wpboutik_get_option_params( 'boxtal_email' );
				$boxtal_password = wpboutik_get_option_params( 'boxtal_password' );

				$response = wp_remote_get( // phpcs:ignore
					sprintf( '%s/cotation', WPBOUTIK_BOXTAL_URL . 'api/v1' ),
					array(
						'body'    => $options, // phpcs:ignore.
						'timeout' => 60, // phpcs:ignore
						'headers' => array(
							'Content-Type'  => 'application/xml; charset=utf-8',
							'Authorization' => 'Basic ' . base64_encode( $boxtal_email . ':' . $boxtal_password )
						),
					)
				);

				if ( $response["response"]["code"] == 401 ) {
					wp_send_json_error();
				}

				$body = wp_remote_retrieve_body( $response );
				$xml  = simplexml_load_string( $body );

				$offer = $xml->shipment->offer;

				$label_operator = (string) $offer->operator->label[0];
				$code_operator  = $offer->operator->code;
				$logo_operator  = $offer->operator->logo;
				$label_service  = (string) $offer->service->label[0];
				$code_service   = $offer->service->code;
				$price_ht       = $offer->price->{'tax-exclusive'};
				$price_ttc      = $offer->price->{'tax-inclusive'};

				$boxtal_margin_shipping_costs = wpboutik_get_option_params( 'boxtal_margin_shipping_costs' );
				if ( $boxtal_margin_shipping_costs && $boxtal_margin_shipping_costs != 0 ) {
					$price_ttc = $price_ttc + ( $price_ttc * ( $boxtal_margin_shipping_costs / 100 ) );
				}

				$label_delivery = '';
				if ( isset( $offer->delivery->type->label[0] ) ) {
					$label_delivery = (string) $offer->delivery->type->label[0];
				}

				$code_delivery = '';
				if ( isset( $offer->delivery->type->code[0] ) ) {
					$code_delivery = (string) $offer->delivery->type->code[0];
				}

				// Trying to access array offset on value of type null si pays différent de France
				$delivery_date = '';
				if ( isset( $offer->characteristics->label[1] ) ) {
					$delivery_date = (string) $offer->characteristics->label[1];
				}

				if ( ! empty( $delivery_date ) ) {
					$html .= '<span class="mt-1 flex items-center text-sm text-gray-500"><p>' . $delivery_date . '</p></span>';
				}

				if ( $code_delivery === 'PICKUP_POINT' ) {
					$backgroundcolor = wpboutik_get_backgroundcolor_button();
					$hovercolor      = wpboutik_get_hovercolor_button();

					$html .= '
                    <!-- Bouton déclencheur -->
                    <button data-method-id="' . $method_id . '" data-nonce="' . wp_create_nonce( "load-listpoints-nonce" ) . '"
                            data-operator-code="' . $method["boxtal_carrier"] . '" data-operator-logo="' . $logo_operator . '" type="button"
                            class="openModalMethod wpb-btn">Choisir mon point relais
                    </button>

                    <!-- Modal -->
                    <div id="myModal' . $method_id . '"
                         class="fixed hidden inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white p-8 rounded-md shadow-lg overflow-auto max-w-md h-1/2 relative">
                        <button  data-method-id="' . $method_id . '" type="button" class="closeModalMethod absolute border-none bg-transparent top-0 right-0 mt-2 mr-2 text-gray-600 hover:text-gray-800">
                          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                          </svg>
                        </button>
                        <div class="content_modal_boxtal_' . $method_id . '"></div>
                        </div>
                    </div>';
				}

				$datas_method_boxtal[ $method_id ] = array(
					'flat_rate'                => wpboutik_format_number( (float) $price_ttc ) . get_wpboutik_currency_symbol(),
					'flat_rate_without_symbol' => wpboutik_format_number( (float) $price_ttc ),
					'html'                     => $html
				);
			}
		}

		if ( ! empty( $datas_method_boxtal ) ) {
			echo wp_send_json( array(
				'success' => true,
				'data'    => $datas_method_boxtal
			) );
		} else {
			wp_send_json_error();
		}

		wp_die();
	}

	public function wpboutik_ajax_checkout_boxtal_load_listpoints() {
		if ( ! isset( $_POST['nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['nonce'], 'load-listpoints-nonce' ) ) {
			return;
		}

		if ( ! isset( $_POST['pays'] ) ) {
			return;
		}

		if ( ! isset( $_POST['ville'] ) ) {
			return;
		}

		if ( ! isset( $_POST['cp'] ) ) {
			return;
		}

		if ( ! isset( $_POST['adresse'] ) ) {
			return;
		}

		if ( ! isset( $_POST['operator_code'] ) ) {
			return;
		}

		if ( ! isset( $_POST['method_id'] ) ) {
			return;
		}

		if ( ! isset( $_POST['flat_rate'] ) ) {
			return;
		}

		$html = '<h3 class="text-base font-semibold leading-6 text-gray-900">Je choisis mon point relais</h3>';

		$response_listPoints = wp_remote_get( // phpcs:ignore
			sprintf( '%s/%s/listpoints', WPBOUTIK_BOXTAL_URL . 'api/v1', $_POST['operator_code'] ),
			array(
				'body'    => array(
					'pays'    => $_POST['pays'],
					'ville'   => $_POST['ville'],
					'cp'      => $_POST['cp'],
					'adresse' => $_POST['adresse']
				), // phpcs:ignore.
				'timeout' => 60, // phpcs:ignore
				'headers' => array(
					'Content-Type'  => 'application/xml; charset=utf-8',
					'Authorization' => 'Basic ' . base64_encode( 'wpboutik30@gmail.com:SA7q4zjLgrDn3-j' )
				),
			)
		);

		$bodylistPoints = wp_remote_retrieve_body( $response_listPoints );
		$xmlListPoint   = simplexml_load_string( $bodylistPoints );

		$html .= '<span>' . count( $xmlListPoint ) . ' ' . __( 'results', 'wpboutik' ) . '</span>';

		$html .= '<ul role="list" class="divide-y divide-gray-100">';

		$backgroundcolor = wpboutik_get_backgroundcolor_button();
		$hovercolor      = wpboutik_get_hovercolor_button();

		foreach ( $xmlListPoint as $point ) {
			$code_point      = (string) $point->code[0];
			$name_point      = (string) $point->name[0];
			$address_point   = (string) $point->address[0];
			$city_point      = (string) $point->city[0];
			$zipcode_point   = (string) $point->zipcode[0];
			$country_point   = (string) $point->country[0];
			$latitude_point  = (string) $point->latitude[0];
			$longitude_point = (string) $point->longitude[0];
			$horaires        = [];
			foreach ( $point->schedule->day as $day ) {
				//$day->weekday
				$horaires[] = array(
					'open_am'  => (string) $day->open_am[0],
					'close_am' => (string) $day->close_am[0],
					'open_pm'  => (string) $day->open_pm[0],
					'close_pm' => (string) $day->close_pm[0],
				);
			}
			$html .= '<li class="flex justify-between gap-x-6 py-5">
                <div class="flex min-w-0 gap-x-4">
                  <img class="h-10 w-16 flex-none rounded-full bg-gray-50" src="' . $_POST['operator_logo'] . '" alt="">
                  <div class="min-w-0 flex-auto">
                    <p class="text-sm font-semibold leading-6 text-gray-900"><a href="https://www.google.com/maps?q=' . $latitude_point . ',' . $longitude_point . '" target="_blank">' . $name_point . '</a></p>
                    <p class="mt-1 truncate text-xs leading-5 text-gray-500">' . $address_point . '</p>
                    <p class="mt-1 truncate text-xs leading-5 text-gray-500">' . $zipcode_point . ' ' . $city_point . '</p>
                  </div>
                </div>
                <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                  <p class="text-sm leading-6 text-gray-900">' . $_POST['flat_rate'] . '</p>
                  <p class="mt-1 text-xs leading-5 text-gray-500"><button type="button" data-method-id="' . $_POST['method_id'] . '" data-name-point="' . $name_point . '" data-address-point="' . $address_point . '" data-city-point="' . $zipcode_point . ' ' . $city_point . '" data-code-point="' . $code_point . '" class="chooseCodePoint w-full rounded-md border border-transparent bg-[var(--backgroundcolor)] py-2 px-2 text-base font-medium text-white shadow-sm hover:bg-[var(--hovercolor)] focus:outline-none focus:ring-2 focus:ring-[var(--backgroundcolor)] focus:ring-offset-2 focus:ring-offset-gray-50 disabled:opacity-75"
                            style="--backgroundcolor: ' . $backgroundcolor . ';--hovercolor: ' . $hovercolor . '">Choisir</button></p>
                </div>
              </li>';
		}

		$html .= '</ul>';

		echo wp_send_json( array(
			'success' => true,
			'data'    => array(
				'method_id' => $_POST['method_id'],
				'html'      => $html
			)
		) );
		wp_die();
	}

	public static function wpb_get_shipping_price( $method, $form_datas, $products, $shipping_country_key = '' ) {
		$shipping = $method['flat_rate'];

		if ( isset( $method['boxtal_carrier'] ) && ! empty( $method['boxtal_carrier'] ) ) {
			$weight_colis = 0;
			foreach ( $products as $single_product ) {
				$single_product = (object) $single_product;
				$weight         = get_post_meta( $single_product->product_id, 'weight', true );
				$weight_unit    = get_post_meta( $single_product->product_id, 'weight_unit', true );
				if ( $weight_unit === 'kg' ) {
					$weight_colis += $weight;
				} else {
					// Gramme to kilos
					$weight_colis += $weight / 1000;
				}
			}

			$options = [
				'expediteur.pays'          => 'FR',
				'expediteur.code_postal'   => wpboutik_get_option_params( 'sender_postalcode' ),
				'expediteur.ville'         => wpboutik_get_option_params( 'sender_city' ),
				'expediteur.type'          => wpboutik_get_option_params( 'sender_type' ),
				'expediteur.adresse'       => wpboutik_get_option_params( 'sender_address' ),
				'destinataire.pays'        => ( ! empty( $shipping_country_key ) ? $shipping_country_key : sanitize_text_field( $form_datas['shipping_country'] ) ),
				'destinataire.code_postal' => sanitize_text_field( $form_datas['shipping_postal_code'] ),
				'destinataire.ville'       => sanitize_text_field( $form_datas['shipping_city'] ),
				'destinataire.type'        => ( ! empty( sanitize_text_field( $form_datas['shipping_company'] ) ) ) ? 'entreprise' : 'particulier',
				'destinataire.adresse'     => sanitize_text_field( $form_datas['shipping_address'] ),
				'colis_1.poids'            => ( $weight_colis == 0 ) ? wpboutik_get_option_params( 'package_weight' ) : $weight_colis,
				'colis_1.longueur'         => ( wpboutik_get_option_params( 'package_length' ) ) ? wpboutik_get_option_params( 'package_length' ) : '20',
				'colis_1.largeur'          => ( wpboutik_get_option_params( 'package_width' ) ) ? wpboutik_get_option_params( 'package_width' ) : '20',
				'colis_1.hauteur'          => ( wpboutik_get_option_params( 'package_height' ) ) ? wpboutik_get_option_params( 'package_height' ) : '20',
				'code_contenu'             => '100',
				'operator'                 => $method['boxtal_carrier']
			];

			$boxtal_email    = wpboutik_get_option_params( 'boxtal_email' );
			$boxtal_password = wpboutik_get_option_params( 'boxtal_password' );

			$response = wp_remote_get( // phpcs:ignore
				sprintf( '%s/cotation', WPBOUTIK_BOXTAL_URL . 'api/v1' ),
				array(
					'body'    => $options, // phpcs:ignore.
					'timeout' => 60, // phpcs:ignore
					'headers' => array(
						'Content-Type'  => 'application/xml; charset=utf-8',
						'Authorization' => 'Basic ' . base64_encode( $boxtal_email . ':' . $boxtal_password )
					),
				)
			);

			if ( $response["response"]["code"] != 401 ) {
				$body = wp_remote_retrieve_body( $response );
				$xml  = simplexml_load_string( $body );

				$offer     = $xml->shipment->offer;
				$price_ttc = $offer->price->{'tax-inclusive'};

				$boxtal_margin_shipping_costs = wpboutik_get_option_params( 'boxtal_margin_shipping_costs' );
				if ( $boxtal_margin_shipping_costs && $boxtal_margin_shipping_costs != 0 ) {
					$price_ttc = $price_ttc + ( $price_ttc * ( $boxtal_margin_shipping_costs / 100 ) );
				}

				$shipping = (float) $price_ttc;
			}
		}

		return get_evenly_reduced_shipping($method, $shipping);
	}

	public function wpboutik_ajax_create_payment_monetico() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'checkout-monetico-nonce' ) ) {
			return;
		}
		if ( ! isset( $_POST['order_id'] ) ) {
			return;
		}
		if ( ! isset( $_POST['billing_details'] ) ) {
			return;
		}
		if ( ! isset( $_POST['total'] ) ) {
			return;
		}
		$order_id              = sanitize_text_field( $_POST['order_id'] );
		$total                 = sanitize_text_field( $_POST['total'] );
		$billing_details       = $_POST['billing_details'];
		$options               = get_option( 'wpboutik_options_params' );
		$monetico_cle_mac      = $options['monetico_cle_mac'];
		$monetico_tpe          = $options['monetico_tpe'];
		$monetico_code_societe = $options['monetico_code_societe'];
		$options               = get_option( 'wpboutik_options' );
		$currency              = get_wpboutik_currency();
		if ( empty( $currency ) ) {
			$currency = 'EUR';
		}

		try {
			$checkout_url = wpboutik_get_page_permalink( 'checkout' );

			$billing = new OrderContextBilling( $billing_details['address']['line1'], $billing_details['address']['city'], $billing_details['address']['postal_code'], $billing_details['address']['country'] );
			//$billing->setPhone( $billing_details['phone'] ); // see technical documentation for correct formatting

			//composer require giggsey/libphonenumber-for-php
			/*use libphonenumber\PhoneNumberUtil;
			use libphonenumber\PhoneNumberFormat;

			function formatPhoneNumber($phoneNumber) {
			    $phoneNumberUtil = PhoneNumberUtil::getInstance();

			    try {
			        // Parse le numéro de téléphone
			        $numberProto = $phoneNumberUtil->parse($phoneNumber, null);

			        // Formatage du numéro selon les conventions locales
			        $formattedNumber = $phoneNumberUtil->format($numberProto, PhoneNumberFormat::INTERNATIONAL);

			        return $formattedNumber;
			    } catch (\libphonenumber\NumberParseException $e) {
			        // Gestion des erreurs de parsing
			        return 'Erreur de format de numéro de téléphone : ' . $e->getMessage();
			    }
			}

			// Exemple d'utilisation
			$phoneNumber = '06 06 60 60 60';
			$formattedPhoneNumber = formatPhoneNumber($phoneNumber);
			echo $formattedPhoneNumber;  // Sortie : "+33 6 06 60 60 60"
			 */

			$billing->setName( $billing_details['name'] );
			$billing->setEmail( $billing_details['email'] );

			$context = new OrderContext( $billing );

			$paymentRequest = new PaymentRequest( $order_id, $total, $currency, 'FR', $context );
			//$paymentRequest->setTexteLibre('Do not forget to HTML-encode every field value otherwise characters like " or \' might cause issues');

			$paymentRequest->setUrlRetourOk( $_POST['url_ok'] );
			//$paymentRequest->setUrlRetourErreur();

			$formFields = $paymentRequest->getFormFields();
			$formHtml   = '<form id="wpbFormMonetico" method="POST" action="' . WPBOUTIK_MONETICO_URL . '">';
			foreach ( $formFields as $key => $value ) {
				$formHtml .= '<input type="hidden" name="' . $key . '" value="' . htmlentities( $value ) . '">';
			}
			$formHtml .= '</form>';

			echo wp_send_json(
				array(
					'success'   => true,
					'form_html' => $formHtml
					//'paymentId' => $payment->id,
				)
			);
			wp_die();
		} catch ( \Exception $e ) {
			echo wp_send_json(
				array(
					'success'      => false,
					'errorMessage' => $e->getMessage()
				)
			);
			wp_die();
		}
	}

	public function search_products_callback() {
		$search_query = $_POST['search_query'];

		$products = new \WP_Query( array(
			'post_type'      => 'wpboutik_product',
			'posts_per_page' => 15,
			'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
			's'              => $search_query
		) );

		$response = '';
		if ( $products->have_posts() ) :
			$product_details = '';
			while ( $products->have_posts() ) :
				$products->the_post();
				$response .= get_wpb_template_parts( 'search-result' );
				if ( get_theme_mod( 'wpboutik_search_product_add_to_cart', 'yes' ) === 'yes' && $_POST['no_detail'] == 'false' ) {
					$product_details .= get_wpb_template_parts( 'product-card', [
						'class' => 'search-product-details',
						'id'    => 'search-product-details-' . get_the_ID()
					] );
				}
			endwhile;
			$response = '<div class="search-products-results-list">' . $response . '</div>' . $product_details;
			if ( $products->max_num_pages > 1 ) :
				/* translators: %d is replaced with number of products */
				$response .= '<a class="search-product-more wpb-link" href="' . wpboutik_get_page_permalink( 'shop' ) . '?search=' . $search_query . '">' . __( 'Search results' ) . '<span>' . str_replace( '%d', $products->found_posts, __( 'Number of items found: %d' ) ) . '</span></a>';
			endif;
		else :
			$response = '<p class="empty-search-product-response">' . __( 'No results found.' ) . '</p>';
		endif;

		// Envoyer la réponse
		wp_send_json( $response );
		wp_die();
	}
}