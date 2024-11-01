<?php

namespace NF\WPBOUTIK;

class Template {

	use Singleton;

	public function __construct() {
		if ( ! wpb_current_theme_is_fse_theme() ) {

			add_filter( 'template_include', array( $this, 'wpboutik_template_loader' ) );
		}

		add_filter( 'template_redirect', array( $this, 'endpoint_wpboutik_template' ), 0 );

		/**
		 * My Account.
		 */
		add_action( 'wpboutik_account_navigation', array( $this, 'wpboutik_account_navigation' ) );
		add_action( 'wpboutik_account_content', array( $this, 'wpboutik_account_content' ) );

		add_action( 'template_redirect', array( $this, 'wpboutik_template_redirect' ) );
		add_action( 'wp_loaded', array( $this, 'wpboutik_save_address' ) );
		add_action( 'wp_loaded', array( $this, 'wpboutik_save_account_details' ) );
		add_action( 'template_redirect', array( $this, 'wpboutik_redirect_shop_if_empty_cart_on_checkout' ) );
		add_action( 'template_redirect', array( $this, 'wpboutik_check_qty_dispo' ) );
	}

	public function wpboutik_template_loader( $template ) {
		global $wp;

		if ( is_embed() ) {
			return $template;
		}

		if ( is_search() ) {
			//$default_file = 'content-product.php';
			$default_file = '';
		} elseif ( is_wpboutik_product() ) {
			$default_file = 'single-wpboutik_product.php';
		} elseif ( is_wpboutik_product_taxonomy() ) {
			$object = get_queried_object();

			if ( is_tax( 'wpboutik_product_cat' ) || is_tax( 'wpboutik_product_tag' ) ) {
				$default_file = 'taxonomy-' . $object->taxonomy . '.php';
			} else {
				$default_file = 'archive-wpboutik_product.php';
			}
		} elseif ( is_wpboutik_shop() ) {
			$default_file = 'archive-wpboutik_product.php';
		} elseif ( is_page( wpboutik_get_page_id( 'cart' ) ) ) {
			$default_file = 'cart/cart.php';
		} elseif ( is_page( wpboutik_get_page_id( 'checkout' ) ) ) {
			$default_file = 'checkout.php';
		} elseif ( is_page( wpboutik_get_page_id( 'account' ) ) ) {
			if ( ! is_user_logged_in() || isset( $wp->query_vars['lost-password'] ) ) {

				// After password reset, add confirmation message.
				if ( ! empty( $_GET['password-reset'] ) ) { // WPCS: input var ok, CSRF ok.
					//wc_add_notice( __( 'Your password has been reset successfully.', 'wpboutik' ) );
				}

				if ( isset( $wp->query_vars['lost-password'] ) ) {
					$default_file = self::lost_password();
				} else {
					$default_file = 'account/form-login.php';
				}
			} else {
				$default_file = 'account/account.php';
			}
		} else {
			$default_file = '';
		}

		if ( $default_file ) {

			$templates = apply_filters( 'wpboutik_template_loader_files', array(), $default_file );

			if ( is_page_template() ) {
				$page_template = get_page_template_slug();

				if ( $page_template ) {
					$validated_file = validate_file( $page_template );
					if ( 0 === $validated_file ) {
						$templates[] = $page_template;
					} else {
						error_log( "WPBoutik: Unable to validate template path: \"$page_template\". Error Code: $validated_file." );
					}
				}
			}

			if ( is_wpboutik_product() ) {
				$object       = get_queried_object();
				$name_decoded = urldecode( $object->post_name );
				if ( $name_decoded !== $object->post_name ) {
					$templates[] = "single-wpboutik_product-{$name_decoded}.php";
				}
				$templates[] = "single-wpboutik_product-{$object->post_name}.php";
			}

			if ( is_wpboutik_product_taxonomy() ) {
				$object      = get_queried_object();
				$templates[] = 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';
				$templates[] = \NF\WPBOUTIK\Plugin::template_path() . 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';
				$templates[] = 'taxonomy-' . $object->taxonomy . '.php';
				$templates[] = \NF\WPBOUTIK\Plugin::template_path() . 'taxonomy-' . $object->taxonomy . '.php';
			}

			$templates[] = $default_file;
			$templates[] = \NF\WPBOUTIK\Plugin::template_path() . $default_file;

			$search_files = array_unique( $templates );
			$template     = locate_template( $search_files );

			if ( ! $template ) {
				$template = \NF\WPBOUTIK\Plugin::plugin_path() . '/templates/' . $default_file;
			}
		}

		return $template;
	}

	/**
	 * Lost password page handling.
	 */
	public static function lost_password() {
		/**
		 * After sending the reset link, don't show the form again.
		 */
		if ( ! empty( $_GET['reset-link-sent'] ) ) { // WPCS: input var ok, CSRF ok.
			extract( array(
				'reset_link_sent' => true,
			) );

			include WPBOUTIK_TEMPLATES . '/account/form-lost-password.php';
			exit;

			/**
			 * Process reset key / login from email confirmation link
			 */
		} elseif ( ! empty( $_GET['action'] ) && $_GET['action'] == 'rp' ) { // WPCS: input var ok, CSRF ok.
			$miss   = false;
			$rp_id  = $_GET['id'];
			$rp_key = $_GET['key'];
			if ( empty( $rp_id ) && empty( $rp_key ) ) {
				$miss = true;
			}
			$userdata = get_userdata( absint( $rp_id ) );
			$rp_login = $userdata ? $userdata->user_login : '';
			$user     = wpb_check_password_reset_key( $rp_key, $rp_login );
			// Reset key / login is correct, display reset password form with hidden key / login values.
			if ( is_object( $user ) ) {
				extract( array(
					'key'   => $rp_key,
					'login' => $rp_login,
				) );

				include WPBOUTIK_TEMPLATES . '/account/form-reset-password.php';
				exit;
			} else {
				$miss = true;
			}

			if ( $miss ) {
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				get_template_part( 404 );
				exit();
			}
		}

		// Show lost password form by default.
		extract( array(
			'form' => 'lost_password',
		) );

		include WPBOUTIK_TEMPLATES . '/account/form-lost-password.php';
		exit;
	}

	/** Give Endpoint a Template **/
	public function endpoint_wpboutik_template( $templates = '' ) {
		global $wp_query;

		$template = $wp_query->query_vars;

		if ( array_key_exists( 'orders', $template ) ) {
			if ( ! is_user_logged_in() ) {
				include WPBOUTIK_TEMPLATES . '/account/form-login.php';
				exit;
			}

			$current_page = empty( $current_page ) ? 1 : absint( $current_page );

			$options = get_option( 'wpboutik_options' );

			$api_request = WPB_Api_Request::request( 'customer', 'orders' )
			                              ->add_multiple_to_body( [
				                              'options'    => $options,
				                              'wp_user_id' => get_current_user_id(),
				                              'page'       => $current_page,
				                              'paginate'   => true,
			                              ] )->exec();

			if ( ! $api_request->is_error() ) {
				$response        = (array) json_decode( $api_request->get_response_body() );
				$customer_orders = $response['orders'];
				$products        = $response['products'];
			}

			extract(
				array(
					'current_page'    => absint( $current_page ),
					'customer_orders' => $customer_orders,
					'products'        => $products,
					'has_orders'      => 0 < count( $customer_orders ),
				)
			);

			include WPBOUTIK_TEMPLATES . '/account/orders.php';
			exit;
		} elseif ( array_key_exists( 'edit-address', $template ) ) {
			if ( ! is_user_logged_in() ) {
				include WPBOUTIK_TEMPLATES . '/account/form-login.php';
				exit;
			}

			$current_user = wp_get_current_user();
			$load_address = $template['edit-address'];
			$load_address = sanitize_key( $load_address );

			$address   = array();
			$addresses = array(
				'wpboutik_billing'  => array(
					'email',
					'first_name',
					'last_name',
					'company',
					'phone',
					'address',
					'city',
					'country',
					'postal_code'
				),
				'wpboutik_shipping' => array(
					'email',
					'first_name',
					'last_name',
					'company',
					'phone',
					'address',
					'city',
					'country',
					'postal_code'
				)
			);

			foreach ( $addresses as $key => $fields ) {
				foreach ( $fields as $field ) {

					$keytoarr = $key . '_' . $field;
					$value    = get_user_meta( get_current_user_id(), $keytoarr, true );

					/*if ( ! $value ) {
								 switch ( $keytoarr ) {
									 case 'wpb_billing_email':
									 case 'wpb_shipping_email':
										 $value = $customer->email;
										 break;
								 }
							 }*/

					$address[ $keytoarr ]['value'] = $value;
				}
			}

			extract(
				array(
					'load_address' => $load_address,
					'address'      => apply_filters( 'wpboutik_address_to_edit', $address, $load_address ),
				)
			);

			include WPBOUTIK_TEMPLATES . '/account/form-edit-address.php';
			exit;
		} elseif ( array_key_exists( 'edit-account', $template ) ) {
			if ( ! is_user_logged_in() ) {
				include WPBOUTIK_TEMPLATES . '/account/form-login.php';
				exit;
			}

			$current_user = wp_get_current_user();

			extract(
				array(
					'user' => $current_user,
				)
			);

			include WPBOUTIK_TEMPLATES . '/account/form-edit-account.php';
			exit;
		} elseif ( array_key_exists( 'view-order', $template ) ) {
			$order_id = $template['view-order'];
			$order    = wpboutik_get_order( $order_id );

			$invalid_order = '';
			if ( ! isset( $order['order'] ) ) {
				$invalid_order = esc_html__( 'Invalid order.', 'wpboutik' );
			}

			extract(
				array(
					'invalid_order'     => $invalid_order,
					'order'             => ( isset( $order['order'] ) ) ? $order['order'] : '',
					'products'          => ( isset( $order['products'] ) ) ? $order['products'] : '',
					'shipping_method'   => ( isset( $order['shipping_method'] ) ) ? $order['shipping_method'] : '',
					'currency_symbol'   => get_wpboutik_currency_symbol(),
					'activate_tax'      => wpboutik_get_option_params( 'activate_tax' ),
					'activate_eu_vat'   => wpboutik_get_option_params( 'activate_eu_vat' ),
					'input_name_eu_vat' => wpboutik_get_option_params( 'input_name_eu_vat' )
				)
			);

			include WPBOUTIK_TEMPLATES . '/account/view-order.php';
			exit;
		} elseif ( array_key_exists( 'licenses', $template ) ) {
			if ( ! is_user_logged_in() ) {
				include WPBOUTIK_TEMPLATES . '/account/form-login.php';
				exit;
			}
			$current_user = wp_get_current_user();
			$licenses = get_user_meta(get_current_user_id(), 'license_code');
			include WPBOUTIK_TEMPLATES . '/account/licenses.php';
			exit;
		} elseif ( array_key_exists( 'abonnements', $template ) ) {
			if ( ! is_user_logged_in() ) {
				include WPBOUTIK_TEMPLATES . '/account/form-login.php';
				exit;
			}
			$current_user = wp_get_current_user();
			$licenses = get_user_meta(get_current_user_id(), 'abonnements');
			include WPBOUTIK_TEMPLATES . '/account/abonnement.php';
			exit;
		} elseif ( array_key_exists( 'order-pay', $template ) ) {
			$order_id = $template['order-pay'];
			$order    = wpboutik_get_order( $order_id );
		} elseif ( array_key_exists( 'order-received', $template ) ) {
			$order_id = $template['order-received'];

			// Get the order.
			$order_id = apply_filters( 'wpboutik_thankyou_order_id', absint( $order_id ) );

			if ( $order_id > 0 ) {
				$order = wpboutik_get_order( $order_id );
			}

			//Si paiement mollie status failed ou canceled ou expired alors redirige vers la page checkout avec un message d'erreur
			if ( isset( $order['order'] ) ) {
				$order_object = $order['order'];
				$payment_type = $order_object->payment_type;
				$status       = $order_object->status;
				if ( $payment_type === "mollie" && $status === "cancelled" ) {
					/*$options = get_option( 'wpboutik_options_params' );
					WPB_Api_Request::request( 'order', 'cancel' )
					               ->add_multiple_to_body( [
						               'options'  => $options,
						               'order_id' => $order_id,
					               ] )->exec();*/
					$checkout_url = wpboutik_get_page_permalink( 'checkout' );
					$redirect_url = add_query_arg( 'payment_mollie_status', 'cancelled', $checkout_url );
					wp_redirect( $redirect_url );
					exit;
				}
				if ( $payment_type === "paybox" ) {
					if ( isset( $_GET['Retour'] ) && ! empty( $_GET['Retour'] ) ) {
						if ( $_GET['Retour'] == '00001' ) {
							$checkout_url = wpboutik_get_page_permalink( 'checkout' );
							$redirect_url = add_query_arg( 'payment_paybox_status', 'cancelled', $checkout_url );
							wp_redirect( $redirect_url );
							exit;
						}
					}
				}
			}

			WPB()->cart->empty_cart();

			if ( isset( $order['order'] ) ) {
				extract(
					array(
						'order'           => $order['order'],
						'products'        => $order['products'],
						'shipping_method' => $order['shipping_method']
					)
				);
			}

			include WPBOUTIK_TEMPLATES . '/checkout/thankyou.php';
			exit;
		} elseif ( array_key_exists( 'downloads', $template ) ) {
			$options = get_option( 'wpboutik_options' );

			$api_request = WPB_Api_Request::request( 'customer', 'orders' )
			                              ->add_multiple_to_body( [
				                              'options'    => $options,
				                              'wp_user_id' => get_current_user_id(),
				                              'status'     => array( 'completed' ),
				                              'type'       => 'virtual_product',
			                              ] )->exec();

			if ( ! $api_request->is_error() ) {
				$response = (array) json_decode( $api_request->get_response_body() );
				$products = $response['products'];
			}

			extract(
				array(
					'products'             => $products,
					'has_products_virtual' => 0 < count( $products ),
				)
			);

			include WPBOUTIK_TEMPLATES . '/account/downloads.php';
			exit;
		}
	}

	/**
	 * My Account navigation template.
	 */
	public function wpboutik_account_navigation() {
		include WPBOUTIK_TEMPLATES . '/account/navigation.php';
	}

	/**
	 * My Account content output.
	 */
	public function wpboutik_account_content() {
		extract( array(
			'current_user' => get_user_by( 'id', get_current_user_id() ),
		) );

		include WPBOUTIK_TEMPLATES . '/account/dashboard.php';
	}

	public function wpboutik_template_redirect() {

		global $wp;

		// Logout.
		if ( isset( $wp->query_vars['customer-logout'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'customer-logout' ) ) {
			wp_safe_redirect( str_replace( '&amp;', '&', wp_logout_url( wpboutik_get_page_permalink( 'account' ) ) ) );
			exit;
		}
	}

	public function wpboutik_save_address() {
		global $wp;

		if ( ! isset( $_REQUEST['wpboutik-edit-address-nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['wpboutik-edit-address-nonce'], 'wpboutik-edit_address' ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'edit_address' !== $_POST['action'] ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}
		$uri_parts = explode( '/', $_SERVER['REQUEST_URI'] );
		if ( in_array( 'billing', $uri_parts ) ) {
			$load_address = 'billing';
		}
		if ( in_array( 'shipping', $uri_parts ) ) {
			$load_address = 'shipping';
		}
		if ( ! isset( $_POST[ $load_address . '_country' ] ) ) {
			return;
		}

		$address = array(
			//$load_address . '_email',
			$load_address . '_first_name'  => array( 'label' => __( 'First name', 'wpboutik' ), 'required' => true ),
			$load_address . '_last_name'   => array( 'label' => __( 'Last name', 'wpboutik' ), 'required' => true ),
			$load_address . '_company'     => array( 'label' => __( 'Company', 'wpboutik' ) ),
			$load_address . '_phone'       => array( 'label' => __( 'Phone', 'wpboutik' ), 'validate' => true ),
			$load_address . '_address'     => array( 'label' => __( 'Address', 'wpboutik' ), 'required' => true ),
			$load_address . '_city'        => array( 'label' => __( 'City', 'wpboutik' ), 'required' => true ),
			$load_address . '_country'     => array( 'label' => __( 'Country', 'wpboutik' ), 'required' => true ),
			$load_address . '_postal_code' => array(
				'label'    => __( 'Postal code', 'wpboutik' ),
				'required' => true,
				'validate' => true
			),
		);

		$errors = [];
		foreach ( $address as $key => $field ) {
			$value = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';

			// Hook to allow modification of value.
			$value = apply_filters( 'wpboutik_process_account_field_' . $key, $value );

			// Validation: Required fields.
			if ( ! empty( $field['required'] ) && empty( $value ) ) {
				/* translators: %s: Field name. */
				$errors[] = sprintf( __( '%s is a required field.', 'wpboutik' ), $field['label'] );
			}

			if ( ! empty( $value ) ) {
				// Validation and formatting rules.
				if ( ! empty( $field['validate'] ) ) {
					switch ( $key ) {
						case $load_address . '_postal_code':
							$country = wp_unslash( $_POST[ $load_address . '_country' ] );
							$value   = wpboutik_format_postcode( $value, $country );

							if ( '' !== $value && ! wpb_is_postcode( $value, $country ) ) {
								switch ( $country ) {
									case 'IE':
										$postcode_validation_notice = __( 'Please enter a valid Eircode.', 'wpboutik' );
										break;
									default:
										$postcode_validation_notice = __( 'Please enter a valid postcode / ZIP.', 'wpboutik' );
								}
								$errors[] = $postcode_validation_notice;
							}
							break;
						case $load_address . '_phone':
							if ( '' !== $value && ! wpb_is_phone( $value ) ) {
								/* translators: %s: Phone number. */
								$errors[] = sprintf( __( '%s is not a valid phone number.', 'wpboutik' ), '<strong>' . $field['label'] . '</strong>' );
							}
							break;
						case $load_address . '_email':
							$value = strtolower( $value );

							if ( ! is_email( $value ) ) {
								/* translators: %s: Email address. */
								$errors[] = sprintf( __( '%s is not a valid email address.', 'wpboutik' ), '<strong>' . $field['label'] . '</strong>' );
							}
							break;
					}
				}
			}
		}

		/**
		 * Hook: wpboutik_after_save_address_validation.
		 *
		 * Allow developers to add custom validation logic and throw an error to prevent save.
		 *
		 * @param int $user_id User ID being saved.
		 * @param string $load_address Type of address e.g. billing or shipping.
		 * @param array $address The address fields.
		 */
		do_action( 'wpboutik_after_save_address_validation', $user_id, $load_address, $address );

		if ( 0 < count( $errors ) ) {
			setcookie( 'wpboutik_error_address', json_encode( $errors ), 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
			wp_redirect( $_SERVER['HTTP_REFERER'] );
			die;
		} else {
			setcookie( 'wpboutik_error_address', '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
		}

		$options = get_option( 'wpboutik_options' );

		$api_request = WPB_Api_Request::request( 'mail', 'update_address' )
		                              ->add_to_body( 'options', $options )
		                              ->add_to_body( 'wp_user_id', get_current_user_id() )
		                              ->add_to_body( 'datas_form', $_POST )
		                              ->exec();

		if ( ! $api_request->is_error() ) {
			$response = (array) json_decode( $api_request->get_response_body() );
			if ( true === $response['success'] ) {
				do_action( 'wpboutik_customer_save_address', $user_id, $load_address );

				$datas_user = $_POST;
				unset( $datas_user['save_address'] );
				unset( $datas_user['wpboutik-edit-address-nonce'] );
				unset( $datas_user['_wp_http_referer'] );
				unset( $datas_user['action'] );
				//$customer = new WPB_Customer( $user_id );
				foreach ( $datas_user as $key => $data ) {

					//$customer->set_[$key]($data);

					update_user_meta( $user_id, 'wpboutik_' . $key, $data );
				}

				wp_safe_redirect( wpboutik_get_endpoint_url( 'edit-address', '', wpboutik_get_page_permalink( 'account' ) ) . '?success=true' );
				exit;
			}
		}
	}

	public function wpboutik_save_account_details() {
		global $wp;

		if ( ! isset( $_REQUEST['wpboutik-save-account-details-nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['wpboutik-save-account-details-nonce'], 'wpboutik_save_account_details' ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_account_details' !== $_POST['action'] ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		$account_first_name   = ! empty( $_POST['account_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['account_first_name'] ) ) : '';
		$account_last_name    = ! empty( $_POST['account_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['account_last_name'] ) ) : '';
		$account_display_name = ! empty( $_POST['account_display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['account_display_name'] ) ) : '';
		$account_email        = ! empty( $_POST['account_email'] ) ? sanitize_email( wp_unslash( $_POST['account_email'] ) ) : '';
		$pass_cur             = ! empty( $_POST['password_current'] ) ? $_POST['password_current'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$pass1                = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$pass2                = ! empty( $_POST['password_2'] ) ? $_POST['password_2'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$save_pass            = true;

		// Current user data.
		$current_user       = get_user_by( 'id', $user_id );
		$current_first_name = $current_user->first_name;
		$current_last_name  = $current_user->last_name;
		$current_email      = $current_user->user_email;

		// New user data.
		$user               = new \stdClass();
		$user->ID           = $user_id;
		$user->first_name   = $account_first_name;
		$user->last_name    = $account_last_name;
		$user->display_name = $account_display_name;

		$errors = [];

		// Prevent display name to be changed to email.
		if ( is_email( $account_display_name ) ) {
			$errors[] = __( 'Display name cannot be changed to email address due to privacy concern.', 'wpboutik' );
		}

		// Handle required fields.
		$required_fields = apply_filters(
			'wpboutik_save_account_details_required_fields',
			array(
				'account_first_name'   => __( 'First name', 'wpboutik' ),
				'account_last_name'    => __( 'Last name', 'wpboutik' ),
				'account_display_name' => __( 'Display name', 'wpboutik' ),
				'account_email'        => __( 'Email address', 'wpboutik' ),
			)
		);

		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $_POST[ $field_key ] ) ) {
				/* translators: %s: Field name. */
				$errors[] = sprintf( __( '%s is a required field.', 'wpboutik' ), '<strong>' . esc_html( $field_name ) . '</strong>' );
			}
		}

		if ( $account_email ) {
			$account_email = sanitize_email( $account_email );
			if ( ! is_email( $account_email ) ) {
				$errors[] = __( 'Please provide a valid email address.', 'wpboutik' );
			} elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {
				$errors[] = __( 'This email address is already registered.', 'wpboutik' );
			}
			$user->user_email = $account_email;
		}

		if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
			$errors[]  = __( 'Please fill out all password fields.', 'wpboutik' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
			$errors[]  = __( 'Please enter your current password.', 'wpboutik' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			$errors[]  = __( 'Please re-enter your password.', 'wpboutik' );
			$save_pass = false;
		} elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
			$errors[]  = __( 'New passwords do not match.', 'wpboutik' );
			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
			$errors[]  = __( 'Your current password is incorrect.', 'wpboutik' );
			$save_pass = false;
		}


		if ( 0 < count( $errors ) ) {
			setcookie( 'wpboutik_error_account_details', json_encode( $errors ), 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
			wp_redirect( $_SERVER['HTTP_REFERER'] );
			die;
		} else {
			setcookie( 'wpboutik_error_account_details', '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
		}

		if ( count( $errors ) === 0 ) {
			wp_update_user( $user );
			if ( $pass1 && $save_pass ) {
				wp_set_password( $pass1, $current_user->ID );
			}

			// Update customer object to keep data in sync.
			/*$customer = new WPB_Customer( $user->ID );

				 if ( $customer ) {
					 // Keep billing data in sync if data changed.
					 if ( is_email( $user->user_email ) && $current_email !== $user->user_email ) {
						 $customer->set_billing_email( $user->user_email );
					 }

					 if ( $current_first_name !== $user->first_name ) {
						 $customer->set_billing_first_name( $user->first_name );
					 }

					 if ( $current_last_name !== $user->last_name ) {
						 $customer->set_billing_last_name( $user->last_name );
					 }

					 $customer->save();
				 }*/

			do_action( 'wpboutik_save_account_details', $user->ID );

			wp_safe_redirect( wpboutik_get_endpoint_url( 'edit-account', '', wpboutik_get_page_permalink( 'account' ) ) . '?success=true' );
			exit;
		}
	}

	public function wpboutik_redirect_shop_if_empty_cart_on_checkout() {
		if ( ! is_page( wpboutik_get_page_id( 'checkout' ) ) ) {
			return false;
		}

		if ( ! WPB()->cart->is_empty() ) {
			return false;
		}

		wp_redirect( esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ) );
		exit;
	}

	public function wpboutik_check_qty_dispo() {
		if ( ! is_page( wpboutik_get_page_id( 'cart' ) ) && ! is_page( wpboutik_get_page_id( 'checkout' ) ) ) {
			return false;
		}

		if ( WPB()->cart->is_empty() ) {
			return false;
		}

		foreach ( WPB()->cart->get_cart() as $cart_item_key => $stored_product ) {
			$stored_product = (object) $stored_product;

			$continu_rupture = get_post_meta( $stored_product->product_id, 'continu_rupture', true );

			if ( empty( $continu_rupture ) || 1 == $continu_rupture ) {
				continue;
			}

			if ( $stored_product->variation_id != "0" ) {
				$variants  = get_post_meta( $stored_product->product_id, 'variants', true );
				$variation = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );
				if ( $variation ) {
					$max_quantity = $variation->quantity;
				} else {
					continue;
				}
			} else {
				$max_quantity = get_post_meta( $stored_product->product_id, 'qty', true );
			}

			if ( ! empty( $max_quantity ) && $stored_product->quantity > $max_quantity ) {
				WPB()->cart->set_quantity( $cart_item_key, $max_quantity, false );
			}
		}
	}
}

add_action( 'init', array( '\NF\WPBOUTIK\Template', 'get_instance' ) );