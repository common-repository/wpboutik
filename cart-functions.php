<?php

/**
 * Clears the cart session when called.
 */
function wpb_empty_cart() {
	if ( ! isset( WPB()->cart ) || '' === WPB()->cart ) {
		WPB()->cart = new \NF\WPBOUTIK\WPB_Cart();
	}
	WPB()->cart->empty_cart( false );
}


/**
 * Gets a hash of important product data that when changed should cause cart items to be invalidated.
 *
 * The wpboutik_cart_item_data_to_validate filter can be used to add custom properties.
 *
 * @param $product_id Product id.
 * @return string
 */
function wpb_get_cart_item_data_hash( $product_id ) {
	return md5(
		wp_json_encode(
			apply_filters(
				'wpboutik_cart_item_data_to_validate',
				array(
					'type'       => get_post_meta( $product_id, 'type', true )
				),
				$product_id
			)
		)
	);
}

/**
 * Update logic triggered on login.
 *
 * @param string $user_login User login.
 * @param object $user       User.
 */
function wpb_user_logged_in( $user_login, $user ) {
	update_user_meta( $user->ID, '_wpboutik_load_saved_cart_after_login', 1 );
}
add_action( 'wp_login', 'wpb_user_logged_in', 10, 2 );