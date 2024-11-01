<?php

namespace NF\WPBOUTIK;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPB_Gift_Card {

	static $error = 'wpboutik_error_coupon_code';
	static $registered = 'wpboutik_gift_card_code';

	public static function find( $code ) {
		global $wpdb;
		$table   = $wpdb->options;
		$results = $wpdb->get_results( "SELECT * FROM $table WHERE `option_name` LIKE 'wpboutik_options_gift_card_code_%' AND `option_value` LIKE '%$code%'" );
		if ( ! empty( $results ) ) {
			return maybe_unserialize( $results[0]->option_value );
		} else {
			return false;
		}
	}

	public static function remove_error_cookie() {
		setcookie( self::$error, '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
	}

	public static function remove_all_cookie() {
		setcookie( self::$error, '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
		setcookie( self::$registered, '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
	}

	public static function set_code_cookie( $id, $code ) {
		setcookie( self::$registered, json_encode( array(
			'id'   => $id,
			'code' => $code
		) ), 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
	}

	public static function set_error_cookie( $message ) {
		setcookie( self::$error, $message, 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
	}

	public static function get_gift_card( $id ) {
		return get_option( 'wpboutik_options_gift_card_code_' . $id );
	}

	public static function get_gift_card_from_cookie() {
		if ( self::has_cookie() ) {
			$wpboutik_gift_card_code = json_decode( stripslashes( $_COOKIE[ self::$registered ] ) );

			return self::get_gift_card( $wpboutik_gift_card_code->id );
		}

		return false;
	}

	public static function has_cookie() {
		return isset( $_COOKIE[ self::$registered ] );
	}

	public static function has_error() {
		return isset( $_COOKIE[ self::$error ] );
	}

	public static function get_error() {
		return stripslashes( $_COOKIE[ self::$error ] );
	}

	public static function get_finale_price( $original_price ) {
		if ( $code = self::get_gift_card_from_cookie() ) {
			if ( $code['available_value'] > $original_price ) {
				return 0;
			} else {
				return $original_price - $code['available_value'];
			}
		}

		return $original_price;
	}

	public static function get_rest_in_cart( $original_price ) {
		if ( $code = self::get_gift_card_from_cookie() ) {
			if ( $code['available_value'] < $original_price ) {
				return 0;
			} else {
				return $code['available_value'] - $original_price;
			}
		}

		return false;
	}

	public static function display_error() {
		if ( self::has_error() ) :
			$wpboutik_error_gift_card_code = self::get_error(); ?>
            <div class="rounded-md bg-red-50 p-4 mb-2">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20"
                             fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
							<?php echo esc_html( $wpboutik_error_gift_card_code ); ?>
                        </h3>
                    </div>
                </div>
            </div>
		<?php
		endif;
	}

}