<?php

namespace NF\WPBOUTIK;

use \NF\WPBOUTIK\Blocks_Utils;
use \NF\WPBOUTIK\Block_Templates;

class Blocks {

	use Singleton;

	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_filter( 'pre_render_block', array( __CLASS__, 'pre_render_block' ), 10, 2 );
		add_filter( 'rest_wpboutik_product_query', array( __CLASS__, 'rest_query' ), 10, 2 );

		if ( wpb_current_theme_is_fse_theme() ) {
			Block_Templates::get_instance();
		}
	}

	public static function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			// Gutenberg is not active.
			return;
		}
		//detecte tout les blocks présents dans le dossier build et les enregistre
		foreach ( glob( WPBOUTIK_DIR . '/build/*', GLOB_ONLYDIR ) as $dir ) {
			$dirname = basename( $dir );
			register_block_type(
				WPBOUTIK_DIR . '/build/' . $dirname
			);
		}

		self::blocks_patterns();

	}

	public static function pre_render_block( $pre_render, $parsed_block ) {
		if ( ! empty( $parsed_block['attrs']['namespace'] ) && $parsed_block['attrs']['namespace'] === 'wpboutik/best-seller-products' ) {
			add_filter(
				'query_loop_block_query_vars',
				function ( $query, $block ) {
					$best_sellers = wpboutik_get_best_sellers();
					if ( ! empty( $best_sellers['best_sellers'] ) ) {
						$query['post__in'] = $best_sellers['best_sellers'];
					} else {
						$query['post__in'] = array( 0 );
					}

					return $query;
				},
				10,
				2
			);
		}
		if ( ! empty( $parsed_block['attrs']['namespace'] ) && $parsed_block['attrs']['namespace'] === 'wpboutik/sticky-product' ) {
			add_filter(
				'query_loop_block_query_vars',
				function ( $query, $block ) {
					$query['meta_query'] = array(
						array(
							'key'   => 'mis_en_avant',
							'value' => 1,
						),
					);

					return $query;
				},
				10,
				2
			);
		}
	}

	public static function rest_query( $args, $request ) {
		$bestSeller = $request['bestSeller'];

		$stickyProducts = $request['stickyProduct'];
		// proceed if it exists
		// add same meta query arguments
		if ( $bestSeller ) {
			$best_sellers = wpboutik_get_best_sellers();
			if ( ! empty( $best_sellers['best_sellers'] ) ) {
				$args['post__in'] = $best_sellers['best_sellers'];
			} else {
				$args['post__in'] = array( 0 );
			}
		}

		if ( $stickyProducts ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'mis_en_avant',
					'value' => 1,
				),
			);
		}

		return $args;
	}

	public static function getBlockTemplate( string $file ): array {
		$file_content = file_get_contents( $file );
		$search       = preg_match( "/<!--\s@name:(.*)\s-->/", $file_content, $matches );
		$label        = ( $search && ! empty( $matches[1] ) ) ? $matches[1] : 'WPBoutik pattern';
		$search       = preg_match( "/<!--\s@description:(.*)\s-->/", $file_content, $matches );
		$description  = ( $search && ! empty( $matches[1] ) ) ? $matches[1] : '';

		$file_info = pathinfo( $file );
		$name      = $file_info['filename'];

		return [
			$name,
			$label,
			$description,
			preg_replace( "/(<!--\s@name:.*\s-->|<!--\s@description:.*\s-->)/", '', $file_content )
		];
	}

	public static function blocks_patterns() {
		// WPBOUTIK_BLOCKS_TEMPLATES
		register_block_pattern_category( 'wpboutik-pattern', array(
			'label' => 'WPBoutik'
		) );

		$patterns_files = glob( WPBOUTIK_BLOCKS_TEMPLATES . '/patterns/*.html' );
		foreach ( $patterns_files as $file ) {
			[ $slug, $label, $description, $content ] = self::getBlockTemplate( $file );
			$args = [
				'title'       => $label,
				'content'     => $content,
				'description' => $description,
				'categories'  => array( 'wpboutik-pattern' ),
				'source'      => 'plugin'
			];
			register_block_pattern(
				'wpboutik-pattern/' . $slug,
				$args
			);
		}

	}
}