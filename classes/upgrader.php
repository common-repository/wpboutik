<?php

namespace NF\WPBOUTIK;

class Upgrader {

	use Singleton;

	protected function init() {
		add_action( 'admin_init', [ __CLASS__, 'wpboutik_upgrader' ] );
		add_action( 'wpboutik_upgrade', [ __CLASS__, 'wpboutik_new_upgrade' ], 10, 2 );
	}

	public static function wpboutik_upgrader() {
		// Grab some infos.
		$actual_version = get_option( 'wpboutik_version' );

		// already installed but got updated.
		if ( WPBOUTIK_VERSION !== $actual_version ) {
			do_action( 'wpboutik_upgrade', WPBOUTIK_VERSION, $actual_version );
		}
	}

	/**
	 * What to do when Rocket is updated, depending on versions
	 *
	 * @param string $wpboutik_version Latest WPBoutik version.
	 * @param string $actual_version Installed WPBoutik version.
	 *
	 * @since 1.0
	 *
	 */
	public static function wpboutik_new_upgrade( $wpboutik_version, $actual_version ) {
		if ( version_compare( $actual_version, '1.0.4', '<' ) ) {
			update_option( 'wpboutik_version', WPBOUTIK_VERSION );
		}

		if ( version_compare( $actual_version, '1.0.5', '<' ) ) {
			if ( get_theme_mod( 'wpboutik_choose_cart_icon' ) === 'dashicons-cart' || get_theme_mod( 'wpboutik_choose_cart_icon' ) === 'dashicons-products' ) {
				set_theme_mod( 'wpboutik_choose_cart_icon', '<svg xmlns="http://www.w3.org/2000/svg" style="width:1.2em;height:1.2em" fill="currentColor" viewBox="0 0 16 16">
				<path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4z"/>
			</svg>' );
			}
		}

		if ( version_compare( $actual_version, '1.0.6', '<' ) ) {
			flush_rewrite_rules();
		}
	}
}