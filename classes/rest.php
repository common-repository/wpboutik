<?php

namespace NF\WPBOUTIK;

class Rest {

	use Singleton;

	/**
	 * Constructor for the rest class. Hooks in methods.
	 */
	public function __construct() {
		add_action( "rest_insert_wpboutik_product", array(
			$this,
			'wpboutik_add_custom_taxonomy_rest_insert_post'
		), 10, 3 );

		add_action( 'rest_insert_wpboutik_product', array(
			$this,
			'set_featured_media_from_url_on_rest_insert_wpboutik_product'
		), 99, 3 );
		add_action( 'rest_insert_wpboutik_product', array(
			$this,
			'set_galerie_image_media_from_url_on_rest_insert_wpboutik_product'
		), 99, 3 );

		add_filter( 'rest_authentication_errors', array( $this, 'wpboutik_json_basic_auth_error' ) );

		add_filter( 'determine_current_user', array( $this, 'wpboutik_json_basic_auth_handler' ), 20 );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_coupon_code/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_coupon_code' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/gift_card_options/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_gift_card' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_coupon_code/', array(
					'methods'             => 'DELETE',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'delete_options_wpboutik_options_coupon_code' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_licenses/', array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_license_code' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_abonnements/', array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_abonnements_code' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_gift_card_code/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_gift_card_code' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_taxes/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_taxes' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_bacs/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_bacs' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_mails/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_mails' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_stripe/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_stripe' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_mollie_webhook/', array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'wpboutik_mollie_webhook' ),
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_mollie/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_mollie' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_monetico/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_monetico' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_paybox/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_paybox' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_abandonned_cart/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_abandonned_cart' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_paypal/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_paypal' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_boxtal/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_boxtal' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_shipping_method/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_options_wpboutik_options_shipping_method' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_category_product/', array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'create_orupdate_wpboutik_category_product' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_category_product/', array(
					'methods'             => 'DELETE',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'delete_wpboutik_category_product' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_options_shipping_method/', array(
					'methods'             => 'DELETE',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'delete_options_wpboutik_options_shipping_method' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_get_products/', array(
					'methods'             => 'GET',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'rest_wpboutik_get_products' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/validate_site/', array(
					'methods'             => 'GET',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'rest_wpboutik_validate_site' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_delete_all_shipping_methods/', array(
					'methods'             => 'DELETE',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'rest_wpboutik_delete_all_shipping_methods' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_delete_all_coupons_code/', array(
					'methods'             => 'DELETE',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'rest_wpboutik_delete_all_coupons_code' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		// import product woo
		// Type,SKU,Name,Published,Is featured?,Visibility in catalog,Short description,Description,Date sale price starts,Date sale price ends,Tax class,In stock?,Stock,Backorders allowed?,Sold individually?,Weight (kg),Length (cm),Width (cm),Height (cm),Allow customer reviews?,Purchase note,Sale price,Regular price,Categories,Tags,Shipping class,Images,Download limit,Download expiry days,Parent,Grouped products,Upsells,Cross-sells,External URL,Button text,Download 1 name,Download 1 URL,Attribute 1 name,Attribute 1 value(s),Attribute 1 visible,Attribute 1 global,Attribute 2 name,Attribute 2 value(s),Attribute 2 visible,Attribute 2 global,Attribute 1 default,Attribute 2 default
		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_get_wooproducts/', array(
					'methods'             => 'GET',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'rest_wpboutik_get_wooproducts' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_address_customer/', array(
					'methods'             => 'PUT',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'update_wpboutik_address_customer' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_reset_project/', array(
					'methods'             => 'DELETE',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'rest_wpboutik_delete_project' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/add_customer/', array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'add_customer' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/send_mail/', array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'send_mail' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/upload_invoice_or_credit/', array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'upload_invoice_or_credit' ),
					'args'                => array(
						'api_key' => array(
							'default'           => '',
							'type'              => 'string',
							'validate_callback' => function ( $param, $request ) {
								if ( ! empty ( $param ) && ( ! is_string( $param ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $param ) ) ) {
									/* translators: %s is replaced with the api key */
									return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $param ) );
								}

								return true;
							}
						),
					)
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_monetico_webhook/', array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'wpboutik_monetico_webhook' ),
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_stripe_webhook/', array(
					'methods'             => 'POST',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'wpboutik_stripe_webhook' ),
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/wpboutik_paybox_webhook/', array(
					'methods'             => 'GET',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'wpboutik_paybox_webhook' ),
				)
			);
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wpboutik/v1', '/validate_license/', array(
					'methods'             => 'GET',
					'permission_callback' => '__return_true',
					'callback'            => array( $this, 'validate_license' ),
				)
			);
		} );

	}

	public function set_featured_media_from_url_on_rest_insert_wpboutik_product( $post, $request, $update ) {
		$meta = $request->get_param( 'meta' );
		if ( ! isset( $meta['first_image'] ) ) {
			return;
		}

		// Assurez-vous que l'URL de l'image est définie dans le post meta
		$first_image = $meta['first_image'];

		if ( $first_image ) {

			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';

			$first_image = json_decode( $first_image );

			$image_url = substr( WPBOUTIK_APP_URL, 0, - 1 ) . $first_image->file;

			// Vérifiez si l'image est déjà attachée au post
			$attached_images = get_posts(
				array(
					'post_parent'    => $post->ID,
					'post_type'      => 'attachment',
					'meta_key'       => '_source_url',
					'meta_value'     => $image_url,
					'posts_per_page' => 1,
				)
			);

			if ( empty( $attached_images ) ) {
				// Téléchargez l'image depuis l'URL
				$image_id = media_sideload_image( $image_url, $post->ID, get_the_title( $post->ID ), 'id' );

				// Assurez-vous que l'ID de l'image est valide
				if ( ! is_wp_error( $image_id ) ) {
					// Définissez l'image comme "featured media" du post
					set_post_thumbnail( $post->ID, $image_id );
				}
			} else {
				// L'image est déjà attachée, utilisez son ID
				$image_id = $attached_images[0]->ID;

				// Définissez l'image comme "featured media" du post
				set_post_thumbnail( $post->ID, $image_id );
			}
		} else {
			delete_post_thumbnail( $post );
		}
	}

	public function set_galerie_image_media_from_url_on_rest_insert_wpboutik_product( $post, $request, $update ) {
		$meta = $request->get_param( 'meta' );
		if ( ! isset( $meta['images'] ) ) {
			return;
		}

		// Assurez-vous que l'URL de l'image est définie dans le post meta
		$images = $meta['images'];

		if ( $images ) {

			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';

			$images = json_decode( $images );

			foreach ( $images as $img ) {

				$image_url = substr( WPBOUTIK_APP_URL, 0, - 1 ) . $img->file;

				// Vérifiez si l'image est déjà attachée au post
				$attached_images = get_posts(
					array(
						'post_parent'    => $post->ID,
						'post_type'      => 'attachment',
						'meta_key'       => '_source_url',
						'meta_value'     => $image_url,
						'posts_per_page' => 1,
					)
				);

				if ( empty( $attached_images ) ) {
					// Téléchargez l'image depuis l'URL
					$image_id = media_sideload_image( $image_url, $post->ID, get_the_title( $post->ID ), 'id' );

					// Assurez-vous que l'ID de l'image est valide
					if ( ! is_wp_error( $image_id ) ) {
						// Définissez l'image comme "featured media" du post
						$gallery_ids[] = $image_id;
					}
				} else {
					$gallery_ids[] = $attached_images[0]->ID;
				}
			}

			if ( is_array( $gallery_ids ) ) {
				update_post_meta( $post->ID, 'galerie_images', implode( ',', $gallery_ids ) );
			}
		}
	}

	public function wpboutik_add_custom_taxonomy_rest_insert_post( $post, $request, $true ) {
		$params = $request->get_json_params();
		if ( array_key_exists( "terms", $params ) ) {
			foreach ( $params["terms"] as $taxonomy => $terms ) {
				wp_set_post_terms( $post->ID, $terms, $taxonomy );
			}
		}
	}

	public function wpboutik_json_basic_auth_error( $error ) {
		// Passthrough other errors
		if ( ! empty( $error ) ) {
			return $error;
		}

		global $wp_json_basic_auth_error;

		return $wp_json_basic_auth_error;
	}

	public function wpboutik_json_basic_auth_handler( $user ) {
		global $wp_json_basic_auth_error;

		$wp_json_basic_auth_error = null;

		// Don't authenticate twice
		if ( ! empty( $user ) ) {
			return $user;
		}

		// Check that we're trying to authenticate
		if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) ) {
			return $user;
		}

		$username = sanitize_text_field( $_SERVER['PHP_AUTH_USER'] );
		$password = sanitize_text_field( $_SERVER['PHP_AUTH_PW'] );

		/**
		 * In multi-site, wp_authenticate_spam_check filter is run on authentication. This filter calls
		 * get_currentuserinfo which in turn calls the determine_current_user filter. This leads to infinite
		 * recursion and a stack overflow unless the current function is removed from the determine_current_user
		 * filter during authentication.
		 */
		remove_filter( 'determine_current_user', array( $this, 'wpboutik_json_basic_auth_handler' ), 20 );

		$user = wp_authenticate( $username, $password );

		add_filter( 'determine_current_user', array( $this, 'wpboutik_json_basic_auth_handler' ), 20 );

		if ( is_wp_error( $user ) ) {
			$wp_json_basic_auth_error = $user;

			return null;
		}

		$wp_json_basic_auth_error = true;

		return $user->ID;
	}

	public function update_options_wpboutik_options_gift_card_code( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$option = array(
				'id'              => $params['id'],
				'code'            => $params['code'],
				'original_value'  => $params['original_value'],
				'available_value' => $params['available_value'],
				'limit_code'      => $params['limit_code'],
				'limit_user'      => $params['limit_user'],
				'pdf_url'         => $params['pdf_url'],
				'date_creation'   => $params['date_creation'],
			);

			$opt_name = 'wpboutik_options_gift_card_code_' . $params['id'];
			if ( (float) $params['available_value'] < 0.1 ) {
				delete_option( $opt_name );
			} else {
				update_option( $opt_name, $option );
			}

			return new \WP_REST_Response( 'Good update option wpboutik_options_gift_card_code', 200 );
		}
	}

	public function validate_license ($request) {
		$params = $request->get_params();
		if (isset($request['pid']) && isset($request['token']) && isset($request['email']) && isset($request['domain'])) {
			$user = get_user_by('email', $request['email']);
			if (!$user) {
				return new \WP_REST_Response( [
					'status' => false,
					'message' => 'Customer not found for email : '.$request['email'].'.'
				], 200 );
			} else {
				$existing = false;
				$licenses = get_user_meta($user->ID, 'license_code');
				// rappel d'une license :
				// $option = array(
				// 	'id'              => $params['id'],
				// 	'code'            => $params['code'],
				// 	'limit_code'      => $params['limit_date'],
				// 	'auto_renew'      => $params['auto_renew'],
				// 	'active'          => $params['active'],
				// 	'limit_url'       => $params['limit_url'],
				// 	'urls'        	  => $params['urls'],
				// );
				if (!empty($licenses)) {
					foreach($licenses as $license) {
						if ($license['code'] == $request['token']) {
							$api_query = WPB_Api_Request::request('licenses', 'validate')
							->add_multiple_to_body([
								'subscription_id' => $license['id'],
								'url' => $request['domain'],
								'product_id' => $request['pid'],
							])->exec();
							if ( $api_query->is_error() ) {
								return new \WP_REST_Response( [
									'status' => false,
									'message' => 'Error during validation, please try later.'
								], 200 );
							} else {
								$app_message = json_decode($api_query->get_response_body());
								return new \WP_REST_Response( [
									'status' => $app_message->result,
									'message' => $app_message->message
								], 200 );
			
							}
							break;
						}
					}
					return new \WP_REST_Response( [
						'status' => false,
						'message' => 'License not found for customer with email : '.$request['email'].'.'
					], 200 );
				} else {
					return new \WP_REST_Response( [
						'status' => false,
						'message' => 'No license found for customer with email : '.$request['email'].'.'
					], 200 );	
				}
			}

		} else {
			return new \WP_REST_Response( 'Wrong request !', 401 );
		}
		die;
	}
	
	public function update_options_wpboutik_options_license_code( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$option = array(
				'id'              => $params['id'],
				'code'            => $params['code'],
				'limit_code'      => $params['limit_date'],
				'auto_renew'      => $params['auto_renew'],
				'active'          => $params['active'],
				'product'         => $params['product'],
				'variant_id'         => $params['variant_id'],
				'limit_url'       => $params['limit_url'],
				'urls'        	  => $params['urls'],
			);

			$opt_name = 'license_code';
			$metas = get_user_meta( $params['user_id'], $opt_name, false);
			$meta_exist = false;
			foreach ($metas as $meta) {
				if ($meta['id'] == $option['id']) {
					update_user_meta($params['user_id'], $opt_name, $option, $meta);
					$meta_exist = true;
					break;
				}
			}
			if (!$meta_exist) {
				add_user_meta($params['user_id'], $opt_name, $option, false);
			}

			return new \WP_REST_Response( 'Good update option wpboutik_options_license_code', 200 );
		}
	}
	public function update_options_wpboutik_options_abonnements_code( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$option = array(
				'id'              => $params['id'],
				'limit_code'      => $params['limit_date'],
				'auto_renew'      => $params['auto_renew'],
				'active'          => $params['active'],
				'product'         => $params['product'],
				'variant_id'      => $params['variant_id'],
				'code'            => $params['code'],
			);

			$opt_name = 'abonnements';
			$metas = get_user_meta( $params['user_id'], $opt_name, false);
			$meta_exist = false;
			foreach ($metas as $meta) {
				if ($meta['id'] == $option['id']) {
					update_user_meta($params['user_id'], $opt_name, $option, $meta);
					$meta_exist = true;
					break;
				}
			}
			if (!$meta_exist) {
				add_user_meta($params['user_id'], $opt_name, $option, false);
			}

			return new \WP_REST_Response( 'Good update option wpboutik_options_abonnements_code', 200 );
		}
	}

	public function update_options_wpboutik_options_coupon_code( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$coupon = get_option( 'wpboutik_options_coupon_code_' . $params['coupon_id'] );

			$option = array(
				'coupon_id'             => $params['coupon_id'],
				'code'                  => ( isset( $params['code'] ) ) ? $params['code'] : $coupon['code'],
				'type'                  => ( isset( $params['type'] ) ) ? $params['type'] : $coupon['type'],
				'valeur'                => ( isset( $params['valeur'] ) ) ? $params['valeur'] : $coupon['valeur'],
				'id_products'           => ( isset( $params['id_products'] ) ) ? $params['id_products'] : $coupon['id_products'],
				'id_categories'         => ( isset( $params['id_categories'] ) ) ? $params['id_categories'] : $coupon['id_categories'],
				'limit_code'            => ( isset( $params['limit_code'] ) ) ? $params['limit_code'] : $coupon['limit_code'],
				'limit_user'            => ( isset( $params['limit_user'] ) ) ? $params['limit_user'] : $coupon['limit_user'],
				'date_expire'           => ( isset( $params['date_expire'] ) ) ? $params['date_expire'] : $coupon['date_expire'],
				'exclude_promo_product' => $params['exclude_promo_product'] ?? 0,
			);

			if ( isset( $params['usage_code'] ) ) {
				$option['usage_code'] = $params['usage_code'];
			} else {
				$option['usage_code'] = $coupon['usage_code'] ?? null;
			}

			if ( isset( $params['usage_user'] ) ) {
				$option['usage_user'] = $params['usage_user'];
			} else {
				$option['usage_user'] = $coupon['usage_user'] ?? null;
			}

			update_option( 'wpboutik_options_coupon_code_' . $params['coupon_id'], $option );

			$coupon_list = wpboutik_get_options_coupon_list();
			$new_option  = array_unique( array_merge( $coupon_list, array( 'wpboutik_options_coupon_code_' . $params['coupon_id'] ) ) );
			update_option( 'wpboutik_options_coupon_list', $new_option );

			return new \WP_REST_Response( 'Good update option wpboutik_options_coupon_code', 200 );
		}
	}

	public function update_options_wpboutik_gift_card( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}
			if ( ! empty( $params['multiple'] ) ) {
				update_option( 'wpboutik_options_gift_card_multiple', 'yes' );
			} else {
				update_option( 'wpboutik_options_gift_card_multiple', 'no' );
			}
			if ( ! empty( $params['custom_price'] ) ) {
				update_option( 'wpboutik_options_gift_card_custom_price', 'yes' );
			} else {
				update_option( 'wpboutik_options_gift_card_custom_price', 'no' );
			}
		}

		return new \WP_REST_Response( 'multiple usage set to : ' . $params['multiple'] . ' custom price to : ' . $params['custom_price'] . '.', 200 );
	}

	public function delete_options_wpboutik_options_coupon_code( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			delete_option( 'wpboutik_options_coupon_code_' . $params['coupon_id'] );

			$coupon_list = wpboutik_get_options_coupon_list();
			if ( ( $key = array_search( 'wpboutik_options_coupon_code_' . $params['coupon_id'], $coupon_list ) ) !== false ) {
				unset( $coupon_list[ $key ] );
			}
			update_option( 'wpboutik_options_coupon_list', $coupon_list );

			return new \WP_REST_Response( 'Good update option wpboutik_options_coupon_code', 200 );
		}
	}

	public function update_options_wpboutik_options( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$options = get_option( 'wpboutik_options_params' );
			if ( ! $options ) {
				$options = [];
			}

			update_option( 'wpboutik_options_params', array_merge( $options, array(
					'project_slug'             => $params['project_slug'] ?? '',
					'language'                 => $params['language'] ?? '',
					'devise'                   => $params['devise'] ?? '',
					'payment_type'             => $params['payment_type'] ?? '',
					'activate_facture'         => $params['activate_facture'] ?? '',
					'activate_tax'             => $params['activate_tax'] ?? '',
					'wpb_connect_secret_token' => $params['wpb_connect_secret_token'] ?? '',
				)
			) );

			return new \WP_REST_Response( 'Good update option wpboutik_options_params', 200 );
		}
	}

	public function update_options_wpboutik_options_taxes( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$options = get_option( 'wpboutik_options_params' );
			if ( ! $options ) {
				$options = [];
			}
			update_option( 'wpboutik_options_params', array_merge( $options, array(
					'taxes'             => ( isset( $params['taxes'] ) ) ? $params['taxes'] : '',
					'activate_eu_vat'   => ( isset( $params['activate_eu_vat'] ) ) ? $params['activate_eu_vat'] : '',
					'input_name_eu_vat' => ( isset( $params['input_name_eu_vat'] ) ) ? $params['input_name_eu_vat'] : '',
					'input_desc_eu_vat' => ( isset( $params['input_desc_eu_vat'] ) ) ? $params['input_desc_eu_vat'] : '',
				)
			) );

			return new \WP_REST_Response( 'Good update option wpboutik_options_params', 200 );
		}
	}

	public function update_options_wpboutik_options_bacs( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( isset( $params['bacs_num_account'] ) || isset( $params['bacs_inscruction'] ) ) {
				$options = get_option( 'wpboutik_options_params' );
				if ( ! $options ) {
					$options = [];
				}
				update_option( 'wpboutik_options_params', array_merge( $options, array(
						'bacs_inscruction'       => isset( $params['bacs_inscruction'] ) ? $params['bacs_inscruction'] : '',
						'bacs_num_account'       => isset( $params['bacs_num_account'] ) ? $params['bacs_num_account'] : '',
						'bacs_name_bank'         => isset( $params['bacs_name_bank'] ) ? $params['bacs_name_bank'] : '',
						'bacs_code_guichet_bank' => isset( $params['bacs_code_guichet_bank'] ) ? $params['bacs_code_guichet_bank'] : '',
						'bacs_iban_bank'         => isset( $params['bacs_iban_bank'] ) ? $params['bacs_iban_bank'] : '',
						'bacs_bicswift_bank'     => isset( $params['bacs_bicswift_bank'] ) ? $params['bacs_bicswift_bank'] : '',
					)
				) );
			}

			return new \WP_REST_Response( 'Good update option wpboutik_options_params', 200 );
		}
	}

	public function update_options_wpboutik_options_mails( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$options = get_option( 'wpboutik_options_params' );
			if ( ! $options ) {
				$options = [];
			}
			update_option( 'wpboutik_options_params', array_merge( $options, array(
					'email_from_name'             => isset( $params['email_from_name'] ) ? $params['email_from_name'] : '',
					'email_from_address'          => isset( $params['email_from_address'] ) ? $params['email_from_address'] : '',
					'email_header_image'          => isset( $params['email_header_image'] ) ? $params['email_header_image'] : '',
					'email_footer_text'           => isset( $params['email_footer_text'] ) ? $params['email_footer_text'] : '',
					'email_base_color'            => isset( $params['email_base_color'] ) ? $params['email_base_color'] : '',
					'email_background_color'      => isset( $params['email_background_color'] ) ? $params['email_background_color'] : '',
					'email_body_background_color' => isset( $params['email_body_background_color'] ) ? $params['email_body_background_color'] : '',
					'email_text_color'            => isset( $params['email_text_color'] ) ? $params['email_text_color'] : '',
				)
			) );

			return new \WP_REST_Response( 'Good update option wpboutik_options_params', 200 );
		}
	}

	public function update_options_wpboutik_options_stripe( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( isset( $params['stripe_public_key'] ) || isset( $params['stripe_secret_key'] ) ) {
				$options = get_option( 'wpboutik_options_params' );
				if ( ! $options ) {
					$options = [];
				}
				update_option( 'wpboutik_options_params', array_merge( $options, array(
						'stripe_public_key' => isset( $params['stripe_public_key'] ) ? $params['stripe_public_key'] : '',
						'stripe_secret_key' => isset( $params['stripe_secret_key'] ) ? $params['stripe_secret_key'] : '',
					)
				) );
			}

			return new \WP_REST_Response( 'Good update option wpboutik_options_params', 200 );
		}
	}

	public function wpboutik_mollie_webhook( $request ) {
		try {
			$params = $request->get_params();
			if ( empty( $params ) || ! isset( $params ) ) {
				return;
			}
			$payment_id = $params["id"];
			if ( ! isset( $payment_id ) ) {
				return;
			}
			$options = get_option( 'wpboutik_options_params' );

			$mollie_test = $options['mollie_test'];
			if ( (bool) $mollie_test ) {
				$mollie_api_key = $options['mollie_api_key_test'];
			} else {
				$mollie_api_key = $options['mollie_api_key_live'];
			}
			$mollie = new \Mollie\Api\MollieApiClient();
			$mollie->setApiKey( $mollie_api_key );
			$payment = $mollie->payments->get( $payment_id );
			if ( empty( $payment ) ) {
				return;
			}

			$transactionFees = number_format( $payment->amount->value - $payment->settlementAmount->value, 2 );

			$order_id   = $payment->metadata->order_id;
			$order_id   = sanitize_text_field( $order_id );
			$payment_id = sanitize_text_field( $payment_id );
			$options    = get_option( 'wpboutik_options_params' );
			if ( $payment->isPaid() && ! $payment->hasRefunds() && ! $payment->hasChargebacks() ) {
				WPB_Api_Request::request( 'order', 'mollie_status' )
				               ->add_multiple_to_body( [
					               'options'          => $options,
					               'order_id'         => $order_id,
					               'payment_id'       => $payment_id,
					               'transaction_fees' => $transactionFees,
				               ] )->exec();

				wp_send_json_success( array( 'success' => true ), 200 );

				return;
			} else if ( $payment->isCanceled() || $payment->isExpired() || $payment->isFailed() ) {
				WPB_Api_Request::request( 'order', 'cancel' )
				               ->add_multiple_to_body( [
					               'options'  => $options,
					               'order_id' => $order_id,
				               ] )->exec();

				wp_send_json_success( array( 'success' => true ), 200 );

				return;
			}
		} catch ( \Mollie\Api\Exceptions\ApiException $e ) {
			error_log( "API call failed: " . htmlspecialchars( $e->getMessage() ) );
			wp_send_json_error( array(
				'success' => false,
				'error'   => "API call failed: " . htmlspecialchars( $e->getMessage() )
			), 400 );
		}
	}
	

	public function update_options_wpboutik_options_mollie( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( isset( $params['mollie_api_key_test'] ) || isset( $params['mollie_api_key_live'] ) ) {
				$options = get_option( 'wpboutik_options_params' );
				if ( ! $options ) {
					$options = [];
				}
				update_option( 'wpboutik_options_params', array_merge( $options, array(
						'mollie_test'         => isset( $params['mollie_test'] ) ? $params['mollie_test'] : '',
						'mollie_api_key_live' => isset( $params['mollie_api_key_live'] ) ? $params['mollie_api_key_live'] : '',
						'mollie_api_key_test' => isset( $params['mollie_api_key_test'] ) ? $params['mollie_api_key_test'] : '',
						'mollie_profile_ID'   => isset( $params['mollie_profile_ID'] ) ? $params['mollie_profile_ID'] : '',
					)
				) );
			}

			return new \WP_REST_Response( 'Good update option wpboutik_options_params', 200 );
		}
	}

	public function update_options_wpboutik_options_monetico( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( isset( $params['monetico_cle_mac'] ) ) {
				$options = get_option( 'wpboutik_options_params' );
				if ( ! $options ) {
					$options = [];
				}
				update_option( 'wpboutik_options_params', array_merge( $options, array(
						'monetico_cle_mac'      => isset( $params['monetico_cle_mac'] ) ? $params['monetico_cle_mac'] : '',
						'monetico_tpe'          => isset( $params['monetico_tpe'] ) ? $params['monetico_tpe'] : '',
						'monetico_code_societe' => isset( $params['monetico_code_societe'] ) ? $params['monetico_code_societe'] : '',
					)
				) );
			}

			return new \WP_REST_Response( 'Good update option wpboutik_options_params', 200 );
		}
	}

	public function update_options_wpboutik_options_paybox( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( isset( $params['paybox_id'] ) ) {
				$options = get_option( 'wpboutik_options_params' );
				if ( ! $options ) {
					$options = [];
				}
				update_option( 'wpboutik_options_params', array_merge( $options, array(
						'paybox_num_site'   => isset( $params['paybox_num_site'] ) ? $params['paybox_num_site'] : '',
						'paybox_num_rang'   => isset( $params['paybox_num_rang'] ) ? $params['paybox_num_rang'] : '',
						'paybox_id'         => isset( $params['paybox_id'] ) ? $params['paybox_id'] : '',
						'paybox_secret_key' => isset( $params['paybox_secret_key'] ) ? $params['paybox_secret_key'] : '',
					)
				) );
			}

			return new \WP_REST_Response( 'Good update option wpboutik_options_params', 200 );
		}
	}

	public function update_options_wpboutik_options_abandonned_cart( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( isset( $params['cut_off_abandonned_cart'] ) ) {
				$options = get_option( 'wpboutik_options_params' );
				if ( ! $options ) {
					$options = [];
				}
				update_option( 'wpboutik_options_params', array_merge( $options, array(
						'cut_off_abandonned_cart' => isset( $params['cut_off_abandonned_cart'] ) ? $params['cut_off_abandonned_cart'] : '',
					)
				) );
			}

			return new \WP_REST_Response( 'Good update option wpboutik_options_params', 200 );
		}
	}

	public function update_options_wpboutik_options_paypal( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( isset( $params['paypal_email'] ) || isset( $params['paypal_id'] ) ) {
				$options = get_option( 'wpboutik_options_params' );
				if ( ! $options ) {
					$options = [];
				}
				update_option( 'wpboutik_options_params', array_merge( $options, array(
						'paypal_email'     => isset( $params['paypal_email'] ) ? $params['paypal_email'] : '',
						'paypal_id'        => isset( $params['paypal_id'] ) ? $params['paypal_id'] : '',
						'paypal_password'  => isset( $params['paypal_password'] ) ? $params['paypal_password'] : '',
						'paypal_signature' => isset( $params['paypal_signature'] ) ? $params['paypal_signature'] : '',
					)
				) );
			}

			return new \WP_REST_Response( 'Good update option wpboutik_options_params', 200 );
		}
	}

	public function update_options_wpboutik_options_boxtal( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( isset( $params['boxtal_email'] ) && isset( $params['boxtal_password'] ) ) {
				$options = get_option( 'wpboutik_options_params' );
				if ( ! $options ) {
					$options = [];
				}
				update_option( 'wpboutik_options_params', array_merge( $options, array(
						'boxtal_email'                 => $params['boxtal_email'],
						'boxtal_password'              => $params['boxtal_password'],
						'sender_type'                  => $params['sender_type'],
						'sender_address'               => $params['sender_address'],
						'sender_postalcode'            => $params['sender_postalcode'],
						'sender_city'                  => $params['sender_city'],
						'sender_last_name'             => $params['sender_last_name'],
						'sender_first_name'            => $params['sender_first_name'],
						'sender_company'               => $params['sender_company'],
						'sender_mail'                  => $params['sender_mail'],
						'sender_phone'                 => $params['sender_phone'],
						'package_weight'               => $params['package_weight'],
						'package_length'               => $params['package_length'],
						'package_width'                => $params['package_width'],
						'package_height'               => $params['package_height'],
						'boxtal_margin_shipping_costs' => $params['boxtal_margin_shipping_costs'],
					)
				) );
			}

			return new \WP_REST_Response( 'Good update option wpboutik_options_params', 200 );
		}
	}

	public function update_options_wpboutik_options_shipping_method( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$option = array(
				'method'         => $params['method'],
				'description'    => $params['description'],
				'flat_rate'      => $params['flat_rate'],
				'boxtal_carrier' => $params['boxtal_carrier'],
				'boxtal_service' => $params['boxtal_service'],
				'reduce' 				 => $params['reduce'],
			);

			update_option( 'wpboutik_options_shipping_method_' . $params['method_id'], $option );

			$method_list = wpboutik_get_options_shipping_method_list();
			$new_option  = array_unique( array_merge( $method_list, array( 'wpboutik_options_shipping_method_' . $params['method_id'] ) ) );
			update_option( 'wpboutik_options_shipping_method_list', $new_option );

			return new \WP_REST_Response( 'Good update option wpboutik_options_shipping_method', 200 );
		}
	}

	public function create_orupdate_wpboutik_category_product( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$term_name        = $params['name'];
			$term_slug        = ( isset( $params['slug'] ) ) ? $params['slug'] : '';
			$term_description = ( isset( $params['description'] ) ) ? $params['description'] : '';
			$term_image       = ( isset( $params['image'] ) ) ? $params['image'] : '';
			$term_parent      = ( isset( $params['parent'] ) ) ? $params['parent'] : '';
			$term_id          = ( isset( $params['term_id'] ) ) ? $params['term_id'] : '';

			$term = term_exists( $term_name, 'wpboutik_product_cat' );
			if ( ! empty( $term_id ) ) {
				wp_update_term( $term_id, 'wpboutik_product_cat', array(
						'name'        => $term_name,
						'slug'        => $term_slug,
						'description' => $term_description,
						'parent'      => $term_parent
					)
				);

				update_term_meta( $term_id, 'wpb_cat_image', $term_image );

				update_term_meta(
					$term_id,
					'wpboutik_description',
					$term_description
				);

				return rest_ensure_response( array( 'id' => $term_id ) );
			} elseif ( $term !== 0 && $term !== null ) {
				update_term_meta( $term['term_id'], 'wpb_cat_image', $term_image );
				update_term_meta(
					$term_id,
					'wpboutik_description',
					$term_description
				);

				return rest_ensure_response( array( 'id' => $term['term_id'] ) );
			} else {
				$term = wp_insert_term( $term_name, 'wpboutik_product_cat', array(
						'description' => $term_description,
						'parent'      => $term_parent
					)
				);

				update_term_meta( $term['term_id'], 'wpb_cat_image', $term_image );

				update_term_meta(
					$term['term_id'],
					'wpboutik_description',
					$term_description
				);

				return rest_ensure_response( array( 'id' => $term['term_id'] ) );
			}
		}
	}

	public function delete_wpboutik_category_product( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$term_id = $params['term_id'];

			wp_delete_term( $term_id, 'wpboutik_product_cat' );
		}
	}

	public function delete_options_wpboutik_options_shipping_method( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			delete_option( 'wpboutik_options_shipping_method_' . $params['method_id'] );

			$method_list = wpboutik_get_options_shipping_method_list();
			if ( ( $key = array_search( 'wpboutik_options_shipping_method_' . $params['method_id'], $method_list ) ) !== false ) {
				unset( $method_list[ $key ] );
			}
			update_option( 'wpboutik_options_shipping_method_list', $method_list );

			return new \WP_REST_Response( 'Good update option wpboutik_options_shipping_method', 200 );
		}
	}

	public function rest_wpboutik_get_products( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$products = new \WP_Query(
				array(
					'post_type'      => 'wpboutik_product',
					'posts_per_page' => 100, /* TODO : Queue for all products */
					'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
					'fields'         => 'ids'
				)
			);

			return rest_ensure_response( $products->posts );
		}
	}

	public function rest_wpboutik_validate_site( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				/* translators: %s is replaced with the api key */
				return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s is not correct for this app.', 'wpboutik' ), 'api key' ) );
			}

			return new \WP_REST_Response( 'Api is validated.', 200 );
		}

		/* translators: %s is replaced with the api key */
		return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s is required.', 'wpboutik' ), 'api key' ) );

	}

	public function rest_wpboutik_delete_all_shipping_methods( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$method_list = wpboutik_get_options_shipping_method_list();
			foreach ( $method_list as $method_name ):
				delete_option( $method_name );
			endforeach;

			delete_option( 'wpboutik_options_shipping_method_list' );

			return new \WP_REST_Response( 'Good update option wpboutik_options_shipping_method', 200 );
		}
	}

	public function rest_wpboutik_delete_all_coupons_code( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$coupon_list = wpboutik_get_options_coupon_list();
			foreach ( $coupon_list as $coupon_id ):
				delete_option( $coupon_id );
			endforeach;

			delete_option( 'wpboutik_options_coupon_list' );

			return new \WP_REST_Response( 'Good update option wpboutik_options_coupon_list', 200 );
		}
	}

	public function rest_wpboutik_delete_project( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			\NF\WPBOUTIK\Plugin::disconnect_project();

			return new \WP_REST_Response( 'Good update option wpboutik_options_shipping_method', 200 );
		}
	}

	public function rest_wpboutik_get_wooproducts( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( ! class_exists( 'woocommerce' ) ) {
				return new \WP_Error( 'error-woo', __( 'Woocommerce is not present in your WordPress', 'wpboutik' ), array( 'status' => 500 ) );
			}

			$categories_product = array();
			$all_categories     = get_categories(
				array(
					'taxonomy'     => 'product_cat',
					/*'meta_query' => array(
										 array(
											 'key' => 'wpb_import',
											 'compare' => 'NOT EXISTS', // Check if the meta key not exists for the term.
										 ),
									 ),*/
					'orderby'      => 'parent',
					'hierarchical' => 1
				)
			);
			if ( $all_categories ) {
				foreach ( $all_categories as $category_woo ) {
					$categories_product[] = array(
						'id'          => $category_woo->term_id,
						'name'        => $category_woo->name,
						'description' => $category_woo->description,
						'parent'      => $category_woo->parent,
					);
					//add_term_meta( $category_woo->term_id, 'wpb_import', 1, true );
				}
			}

			$products = new \WP_Query(
				array(
					'post_type'      => 'product',
					'posts_per_page' => - 1,
					'no_paging'      => true,
					//'posts_per_page' => 1,
					'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
					//'fields' => 'ids'
					'meta_query'     => array(
						array(
							'key'     => 'wpb_import',
							'compare' => 'NOT EXISTS', // Vérifiez si la clé de post meta n'existe pas.
						),
					),
				)
			);

			$postmetas          = array();
			$options_variations = array();
			$variations         = array();
			$cats               = array();
			$images             = array();
			foreach ( $products->posts as $product ) {

				add_post_meta( $product->ID, 'wpb_import', 1, true );

				/*if ( $product->ID !== 153420 ) {
									continue;
								}*/

				$images[ $product->ID ][] = wp_get_attachment_url( get_post_thumbnail_id( $product->ID ) );
				$wooproduct               = new \WC_product( $product->ID );
				$attachment_ids           = $wooproduct->get_gallery_image_ids();

				foreach ( $attachment_ids as $attachment_id ) {
					$images[ $product->ID ][] = wp_get_attachment_url( $attachment_id );
				}

				$postmetas[ $product->ID ] = get_post_meta( $product->ID );

				$categories = get_the_terms( $product->ID, 'product_cat' );
				if ( $categories ) {
					foreach ( $categories as $cat ) {
						if ( count( $categories ) > 1 && $cat->parent == 0 ) {
							continue;
						}
						$cats[ $product->ID ] = $cat->term_id;
						break;
					}
				}

				$wc_product = wc_get_product( $product->ID );

				if ( $wc_product->is_type( 'variable' ) ) {
					$attributes = $wc_product->get_variation_attributes();

					foreach ( $attributes as $attribute_name => $attribute_options ) {

						$newtab = array();
						foreach ( $attribute_options as $key => $option ) {
							// Obtenez le nom de la valeur de l'attribut à partir du slug
							$value_name = get_term_by( 'slug', $option, $attribute_name )->name;

							// Stockez le nom de la valeur dans le tableau
							$newtab[0][ $key ]['final_name'] = $value_name;
							$newtab[0][ $key ]['name']       = $option;
						}

						$options_variations[ $product->ID ][ wc_attribute_label( $attribute_name ) ] = $newtab;
					}

					$variations[ $product->ID ] = $wc_product->get_available_variations();
				}
			}

			return rest_ensure_response(
				array(
					'posts'              => $products->posts,
					'postmetas'          => $postmetas,
					'options_variations' => $options_variations,
					'variations'         => $variations,
					'images'             => $images,
					'categories_product' => $categories_product,
					'cats'               => $cats
				)
			);
		}
	}

	public function update_wpboutik_address_customer( $request ) {
		$params = $request->get_params();

		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( isset( $params['user_id'] ) ) {
				$user_id = $params['user_id'];
				unset( $params['api_key'] );
				unset( $params['user_id'] );
				foreach ( $params as $key => $value ) {
					update_user_meta( $user_id, $key, $value );
				}
			}

			return new \WP_REST_Response( 'Good update address customer', 200 );
		}
	}

	public function add_customer( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			// Create user
			$user_id = wp_insert_user(
				array(
					'user_login'   => $params['email'],
					'user_email'   => $params['email'],
					'user_pass'    => $params['password'],
					'display_name' => $params['name'],
					'first_name'   => $params['first_name'],
					'last_name'    => $params['last_name'],
					'role'         => 'customer-wpb'
				)
			);
			wp_new_user_notification( $user_id, null, 'both' );
			// On success.
			if ( ! is_wp_error( $user_id ) ) {
				$wp_user_id = $user_id;

				return rest_ensure_response(
					array(
						'wp_user_id' => $wp_user_id,
					)
				);
			} else {
				return rest_ensure_response(
					array(
						'error' => $user_id,
					)
				);
			}
		}
	}

	public function send_mail( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}
			$html_email = $params['html_email'];
			$to         = $params['to'];
			$subject    = $params['subject'];

			$attachments = '';
			if ( isset( $params['order_status'] ) && ! empty( $params['order_status'] ) ) {
				$html_email = apply_filters( 'wpboutik_html_email_order_status_' . $params['order_status'], $params['html_email'] );
				$to         = apply_filters( 'wpboutik_to_email_order_status_' . $params['order_status'], $params['to'] );
				$subject    = apply_filters( 'wpboutik_subject_email_order_status_' . $params['order_status'], $params['subject'] );

				if ( isset( $params['attachment'] ) && ! empty( $params['attachment'] ) ) {

					$attachment_id = wpboutik_upload_invoice( $params['attachment'], $params['order_id'], $params['order_status'] );
					$path_file     = get_attached_file( $attachment_id );

					if ( file_exists( $path_file ) ) {
						$attachments = array( $path_file );
					}

					$attachments = apply_filters( 'wpboutik_attachment_email_order_status_' . $params['order_status'], $attachments );
				}
			}

			if ( ! empty( $params['type'] ) && 'note' === $params['type'] ) {
				$html_email = apply_filters( 'wpboutik_html_email_note_order', $params['html_email'] );
				$to         = apply_filters( 'wpboutik_to_email_note_order', $params['to'] );
				$subject    = apply_filters( 'wpboutik_subject_email_note_order', $params['subject'] );
			}

			$email_from_name    = wpboutik_get_option_params( 'email_from_name' );
			$email_from_address = wpboutik_get_option_params( 'email_from_address' );

			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . $email_from_name . ' <' . $email_from_address . '>'
			);
			if ( ! empty( $params['b64_image'] ) ) {
				$b64 = $params['b64_image'];
				add_action( 'phpmailer_init', function ( &$phpmailer ) use ( $b64 ) {
					$phpmailer->SMTPKeepAlive = true;
					$phpmailer->addStringEmbeddedImage( base64_decode( str_replace( 'data:image/png;base64,', '', $b64 ) ), "gf_image", "gf_image", "base64", 'image/png' );
				} );
			}
			//mail($to, $subject, $html_email, implode("\r\n", $headers));
			$mail = wp_mail( $to, $subject, $html_email, $headers, $attachments );

			return rest_ensure_response( array(
				'to'                 => $params['to'],
				'subject'            => $params['subject'],
				'order_status'       => ! empty( $params['order_status'] ) ? $params['order_status'] : null,
				'email_from_name'    => $email_from_name,
				'email_from_address' => $email_from_address,
				'send_status'        => $mail
			) );
		}
	}

	public function upload_invoice_or_credit( $request ) {
		$params = $request->get_params();
		if ( isset( $params['api_key'] ) && ! empty( $params['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $params['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( isset( $params['attachment'] ) && ! empty( $params['attachment'] ) ) {
				$attachment_id = wpboutik_upload_invoice( $params['attachment'], $params['order_id'], $params['order_status'] );
			}
		}
	}

	public function wpboutik_monetico_webhook( $request ) {
		$params = $request->get_params();
		if ( empty( $params ) || ! isset( $params ) ) {
			return;
		}

		$order_id    = $params["reference"];
		$code_retour = $params["code-retour"];

		if ( ! isset( $code_retour ) ) {
			return;
		}

		$options = get_option( 'wpboutik_options_params' );

		// Handle the event
		switch ( $code_retour ) {
			case 'payetest':
			case 'paiement':
				WPB_Api_Request::request( 'order', 'monetico_status' )
				               ->add_multiple_to_body( [
					               'options'    => $options,
					               'order_id'   => $order_id,
					               'payment_id' => $params["numauto"],
				               ] )->exec();
				break;
			case 'Annulation':
				WPB_Api_Request::request( 'order', 'cancel' )
				               ->add_multiple_to_body( [
					               'options'  => $options,
					               'order_id' => $order_id,
				               ] )->exec();
				break;
			default:
				error_log( "API call failed: error code retour" );
				wp_send_json_error( array( 'success' => false, 'error' => "API call failed: error code retour" ), 400 );
		}

		header( 'Content-Type: text/plain' );
		echo "version=2\n";
		echo "cdr=0\n";
	}

	public function wpboutik_stripe_webhook( $request ) {
		$params = $request->get_params();
		if ( empty( $params ) || ! isset( $params ) ) {
			return;
		}

		/*$order_id    = $params["reference"];
		$code_retour = $params["code-retour"];
		$payment_id  = $params["numauto"];*/

		if ( ! isset( $payment_id ) ) {
			return;
		}

		$options = get_option( 'wpboutik_options_params' );

		// ===> https://stripe.com/docs/webhooks/quickstart

		\Stripe\Stripe::setApiKey( $options['stripe_secret_key'] );
		// Replace this endpoint secret with your endpoint's unique secret
		// If you are testing with the CLI, find the secret by running 'stripe listen'
		// If you are using an endpoint defined with the API or dashboard, look in your webhook settings
		// at https://dashboard.stripe.com/webhooks
		$endpoint_secret = 'whsec_...';

		$payload = @file_get_contents( 'php://input' );
		$event   = null;

		try {
			$event = \Stripe\Event::constructFrom(
				json_decode( $payload, true )
			);
		} catch ( \UnexpectedValueException $e ) {
			// Invalid payload
			echo '⚠️  Webhook error while parsing basic request.';

			wp_send_json_error( array( 'success' => false ), 400 );
		}
		if ( $endpoint_secret ) {
			// Only verify the event if there is an endpoint secret defined
			// Otherwise use the basic decoded event
			$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
			try {
				$event = \Stripe\Webhook::constructEvent(
					$payload, $sig_header, $endpoint_secret
				);
			} catch ( \Stripe\Exception\SignatureVerificationException $e ) {
				// Invalid signature
				echo '⚠️  Webhook error while validating signature.';

				wp_send_json_error( array( 'success' => false ), 400 );
			}
		}

		// Handle the event
		switch ( $event->type ) {
			case 'payment_intent.succeeded':
				$paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
				// Then define and call a method to handle the successful payment intent.
				// handlePaymentIntentSucceeded($paymentIntent);

				WPB_Api_Request::request( 'order', 'stripe_status' )
				               ->add_multiple_to_body( [
					               'options'    => $options,
					               'order_id'   => $order_id,
					               'payment_id' => $payment_id,
				               ] )->exec();

				break;
			case 'payment_method.attached':
				$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
				// Then define and call a method to handle the successful attachment of a PaymentMethod.
				// handlePaymentMethodAttached($paymentMethod);
				break;
			default:
				// Unexpected event type
				error_log( 'Received unknown event type' );
		}

		wp_send_json_success( array( 'success' => true ), 200 );
	}

	public function wpboutik_paybox_webhook( $request ) {
		$params = $request->get_params();
		if ( empty( $params ) || ! isset( $params ) ) {
			return;
		}

		$order_id    = $params["Ref"];
		$code_retour = $params["Retour"];
		$payment_id  = $params["Auto"];

		if ( ! isset( $payment_id ) ) {
			return;
		}

		$options = get_option( 'wpboutik_options_params' );

		$debut_code_retour = substr( $code_retour, 0, 3 );

		if ( $debut_code_retour === "001" && ctype_digit( substr( $code_retour, 3, 2 ) ) ) {
			switch ( $code_retour ) {
				default:
					WPB_Api_Request::request( 'order', 'failed' )
					               ->add_multiple_to_body( [
						               'options'  => $options,
						               'order_id' => $order_id,
					               ] )->exec();
					break;
			}
		} else {
			switch ( $code_retour ) {
				case '00000':
				case '00':
					WPB_Api_Request::request( 'order', 'paybox_status' )
					               ->add_multiple_to_body( [
						               'options'    => $options,
						               'order_id'   => $order_id,
						               'payment_id' => $payment_id,
					               ] )->exec();
					break;
				case '00001':
					WPB_Api_Request::request( 'order', 'cancel' )
					               ->add_multiple_to_body( [
						               'options'  => $options,
						               'order_id' => $order_id,
					               ] )->exec();
					break;
				case '99999':
					WPB_Api_Request::request( 'order', 'on_hold' )
					               ->add_multiple_to_body( [
						               'options'  => $options,
						               'order_id' => $order_id,
					               ] )->exec();
					break;
				default:
					error_log( "API call failed: error code retour" );
					wp_send_json_error( array(
						'success' => false,
						'error'   => "API call failed: error code retour"
					), 400 );
			}
		}

		wp_send_json_success( array( 'success' => true ), 200 );
	}
}