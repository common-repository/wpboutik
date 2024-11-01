<?php
/*
Plugin Name: WPBoutik
Description: Il n’a jamais été aussi simple de vendre en ligne !
Version: 1.0.5.1
Author: NicolasKulka, wpformation
Author URI: https://wpboutik.com/
Domain Path: languages
Tested up to: 6.5
Requires PHP: 7.0
Text Domain: wpboutik
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
// Plugin constants
define( 'WPBOUTIK_VERSION', '1.0.5.1' );
define( 'WPBOUTIK_FOLDER', 'wpboutik' );
define( 'WPBOUTIK_SLUG', 'wpboutik' );
define( 'WPBOUTIK_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'WPBOUTIK_APP_URL' ) ) {
	define( 'WPBOUTIK_APP_URL', 'https://app.wpboutik.com/' );
}
if ( ! defined( 'WPBOUTIK_WHITELABEL' ) ) {
	define( 'WPBOUTIK_WHITELABEL', 'WPBoutik' );
}
//https://wpboutik.com/documentation/
define( 'WPBOUTIK_DOC_URL', 'https://support.wpboutik.com/' );
define( 'WPBOUTIK_BOXTAL_URL', 'https://www.envoimoinscher.com/' );

// URL de test Monetico => https://p.monetico-services.com/test/paiement.cgi
define( 'WPBOUTIK_MONETICO_URL', 'https://p.monetico-services.com/paiement.cgi' );

// URL de test Paybox => https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi
define( 'WPBOUTIK_PAYBOX_URL', 'https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi' );

define( 'WPBOUTIK_URL', plugin_dir_url( __FILE__ ) );
define( 'WPBOUTIK_DIR', plugin_dir_path( __FILE__ ) );

define( 'WPBOUTIK_TEMPLATES', WPBOUTIK_DIR . 'templates' );
define( 'WPBOUTIK_BLOCKS_TEMPLATES', WPBOUTIK_DIR . 'templates/blocks' );
define( 'WPBOUTIK_TEMPLATES_ADMIN', WPBOUTIK_TEMPLATES . '/admin' );
define( 'WPBOUTIK_TEMPLATES_ADMIN_PAGES', WPBOUTIK_TEMPLATES_ADMIN . '/pages' );

require_once WPBOUTIK_DIR . 'autoload.php';
require_once WPBOUTIK_DIR . 'wpboutik-functions.php';
require_once WPBOUTIK_DIR . 'cart-functions.php';
require_once WPBOUTIK_DIR . 'controllers.php';

add_action( 'plugins_loaded', 'plugins_loaded_wpboutik' );
function plugins_loaded_wpboutik() {
	/**
	 * Action triggered before WPBoutik initialization begins.
	 */
	do_action( 'before_wpboutik_init' );

	include_once WPBOUTIK_DIR . 'classes/trait-wpb-item-totals.php';
	include_once WPBOUTIK_DIR . 'classes/wpb-api-request.php';
	include_once WPBOUTIK_DIR . 'classes/wpb-gift-card.php';

	\NF\WPBOUTIK\Plugin::get_instance();
	\NF\WPBOUTIK\Admin_Dashboard::get_instance();
	\NF\WPBOUTIK\Query::get_instance();
	\NF\WPBOUTIK\Widgets::get_instance();
	\NF\WPBOUTIK\Blocks::get_instance();
	\NF\WPBOUTIK\Ratings::get_instance();
	\NF\WPBOUTIK\Rest::get_instance();
	\NF\WPBOUTIK\Ajax::get_instance();
	//\NF\WPBOUTIK\Template::get_instance();
	\NF\WPBOUTIK\Customize::get_instance();
	\NF\WPBOUTIK\User::get_instance();
	\NF\WPBOUTIK\Product::get_instance();
	//\NF\WPBOUTIK\Google_Analytics::get_instance();
	\NF\WPBOUTIK\WPB_Cache_Helper::get_instance();
	\NF\WPBOUTIK\Upgrader::get_instance();

	//add filter to prevent load wpboutik if not needed
	$cancel_init = apply_filters( 'wpboutik_cancel_init', false );

	if ( $cancel_init ) {
		return;
	}

	if ( ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! wpb_is_rest_api_request() ) {
		include_once WPBOUTIK_DIR . 'cart-functions.php';
		include_once WPBOUTIK_DIR . 'classes/wpb-cart.php';
		include_once WPBOUTIK_DIR . 'classes/wpb-customer.php';
		include_once WPBOUTIK_DIR . 'classes/wpb-session-handler.php';
		include_once WPBOUTIK_DIR . 'classes/template.php';

		include_once WPBOUTIK_DIR . 'classes/Monetico/HmacComputer.php';
		include_once WPBOUTIK_DIR . 'classes/Monetico/Request/OrderContext.php';
		include_once WPBOUTIK_DIR . 'classes/Monetico/Request/OrderContextBilling.php';
		include_once WPBOUTIK_DIR . 'classes/Monetico/Request/PaymentRequest.php';
	}

	if ( ! function_exists( 'curl_version' ) || ! function_exists( 'curl_exec' ) ) {
		add_action( 'admin_notices', array( '\NF\WPBOUTIK\Plugin', 'admin_notices_no_curl' ) );
	}
	if ( ! function_exists( 'json_last_error' ) ) {
		add_action( 'admin_notices', array( '\NF\WPBOUTIK\Plugin', 'admin_notices_json_functions' ) );
	}

	load_plugin_textdomain( 'wpboutik', false, dirname( WPBOUTIK_BASENAME ) . '/languages' );

	add_filter( 'wp_update_comment_data', '\NF\WPBOUTIK\Ratings::save', 1 );

	if ( ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! wpb_is_rest_api_request() ) {
		wpb_load_cart();
	}

	/**
	 * Action triggered after WPBoutik initialization finishes.
	 */
	do_action( 'wpboutik_init' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingSinceComment
}

/**
 * Returns true if the request is a non-legacy REST API request.
 *
 * Legacy REST requests should still run some extra code for backwards compatibility.
 *
 * @todo: replace this function once core WP function is available: https://core.trac.wordpress.org/ticket/42061.
 *
 * @return bool
 */
function wpb_is_rest_api_request() {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return false;
	}

	$rest_prefix         = trailingslashit( rest_get_url_prefix() );
	$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	/**
	 * Whether this is a REST API request.
	 *
	 * @since 3.6.0
	 */
	return apply_filters( 'wpboutik_is_rest_api_request', $is_rest_api_request );
}

function wpboutik_activate() {
	add_role(
		'customer-wpb',
		'Customer WPBoutik',
		array(
			'read' => true,
		)
	);

	if ( ! get_option( 'wpboutik_flush_rewrite_rules_flag' ) ) {
		add_option( 'wpboutik_flush_rewrite_rules_flag', true );
	}

	wp_clear_scheduled_hook( 'wpboutik_cleanup_sessions' );

	global $wpdb;
	$table_name = $wpdb->prefix . 'wpboutik_sessions';

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    session_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  session_key char(32) NOT NULL,
  session_value longtext NOT NULL,
  session_expiry bigint(20) unsigned NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (session_id),
    UNIQUE KEY session_key (session_key)
) ENGINE=InnoDB;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	wpb_insert_widget_in_sidebar(
		[
			[ 'wpb_new_products', [], 'wpboutik_product_sidebar' ],
			[ 'widget_produits_mis_en_avant', [], 'wpboutik_product_sidebar' ],
			[ 'widget_produits_mis_en_avant', [], 'wpboutik_cart_sidebar' ],
			[ 'widget_meilleures_ventes', [], 'wpboutik_cart_sidebar' ],
			[ 'wpb_new_products', [], 'wpboutik_archive_sidebar' ],
			[ 'widget_produits_mis_en_avant', [], 'wpboutik_archive_sidebar' ],
			[ 'widget_meilleures_ventes', [], 'wpboutik_archive_sidebar' ],
		]
	);
}

function wpb_insert_widget_in_sidebar( $settings ) {
	// Retrieve sidebars, widgets and their instances
	$sidebars_widgets = get_option( 'sidebars_widgets', array() );
	foreach ( $settings as $setting ) {
		[ $widget_id, $widget_data, $sidebar ] = $setting;

		$widget_instances = get_option( 'widget_' . $widget_id, array() );
		$numeric_keys     = array_filter( array_keys( $widget_instances ), 'is_int' );
		$next_key         = $numeric_keys ? max( $numeric_keys ) + 1 : 2;

		// Add this widget to the sidebar
		if ( ! isset( $sidebars_widgets[ $sidebar ] ) ) {
			$sidebars_widgets[ $sidebar ] = array();
		}
		$sidebars_widgets[ $sidebar ][] = $widget_id . '-' . $next_key;

		// Add the new widget instance
		$widget_instances[ $next_key ] = $widget_data;
		update_option( 'widget_' . $widget_id, $widget_instances );
	}
	update_option( 'sidebars_widgets', $sidebars_widgets );
}

function wpboutik_uninstall() {
	wp_clear_scheduled_hook( 'wpboutik_cleanup_sessions' );
}

register_activation_hook( __FILE__, 'wpboutik_activate' );
register_deactivation_hook( __FILE__, 'wpboutik_uninstall' );

// Include the main WPboutik class.
if ( ! class_exists( 'NF\WPBOUTIK\Plugin', false ) ) {
	include_once WPBOUTIK_DIR . 'classes/plugin.php';
}

/**
 * Returns the main instance of WPB.
 */
function WPB(): \NF\WPBOUTIK\Plugin { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return NF\WPBOUTIK\Plugin::get_instance();
}

// Global for backwards compatibility.
$GLOBALS['wpboutik'] = WPB();