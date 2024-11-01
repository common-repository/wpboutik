<?php

namespace NF\WPBOUTIK;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Name pages
 *
 * @since 2.0
 */
abstract class Tabs_Admin {

	/**
	 * @var string
	 */
	const SETTINGS = 'settings';

	/**
	 * @var string
	 */
	const SUPPORT = 'support';

	/**
	 * Get full tabs information
	 * @static
	 * @return array
	 * @since 1.0
	 *
	 */
	public static function get_full_tabs() {
		return array(
			self::SETTINGS => array(
				'title' => __( 'Settings', 'wpboutik' ),
				'url'   => get_admin_url( null, sprintf( 'admin.php?page=%s&tab=%s', 'wpboutik-settings', self::SETTINGS ) ),
			),
		);
	}
}
