<?php

namespace NF\WPBOUTIK;

use NF\WPBOUTIK\Block_Templates_Utils;

const THEME = 'wpboutik';

class Block_Templates {

	use Singleton;

	public static function init() {
		// add_action( 'template_redirect', array( __CLASS__, 'render_block_template' ) );

		add_filter( 'pre_get_block_template', [ __CLASS__, 'pre_get_block_template' ], 10, 3 );
		add_filter( 'pre_get_block_file_template', [ __CLASS__, 'get_block_file_template' ], 10, 3 );
		add_filter( 'get_block_templates', [ __CLASS__, 'add_block_templates' ], 10, 3 );
		add_filter( 'taxonomy_template_hierarchy', array(
			__CLASS__,
			'add_archive_product_to_eligible_for_fallback_templates'
		), 10, 1 );
	}

	public static function render_block_template() {
		if ( is_embed() || ! Block_Templates_Utils::supports_block_templates() ) {
			return;
		}

		if (
			is_singular( 'wpboutik_product' ) && self::block_template_is_available( 'single-wpboutik_product' )
		) {
			global $post;

			$valid_slugs = [ 'single-wpboutik_product' ];
			if ( 'wpboutik_product' === $post->post_type && $post->post_name ) {
				$valid_slugs[] = 'single-wpboutik_product-' . $post->post_name;
			}
			$templates = get_block_templates( array( 'slug__in' => $valid_slugs ) );
			return $templates;
		} elseif ( is_post_type_archive( 'wpboutik_product' ) && is_search() ) {
			$templates = get_block_templates( array( 'slug__in' => array( 'wpboutik_product-search-results' ) ) );
			return $templates;
		} elseif (
			( is_post_type_archive( 'wpboutik_product' ) || is_tax( 'wpboutik_product_cat' ) ) && self::block_template_is_available( 'archive-wpboutik_product' )
		) {
			$templates = get_block_templates( array( 'slug__in' => array( 'archive-wpboutik_product' ) ) );
			return $templates;
		} elseif (
			is_page()
		) {
			if ( get_the_ID() == wpboutik_get_page_id( 'cart' ) && ! Block_Templates_Utils::theme_has_template( 'cart' ) && self::block_template_is_available( 'cart' ) ) {
				$templates = get_block_templates( array( 'slug__in' => array( 'cart' ) ) );
			}
		}
		// elseif (
		// 	is_checkout() &&
		// 	! Block_Templates_Utils::theme_has_template( CheckoutTemplate::get_slug() ) && self::block_template_is_available( CheckoutTemplate::get_slug() )
		// ) {
		// 	add_filter( 'wpboutik_has_block_template', '__return_true', 10, 0 );
		// } 
		else {
			$queried_object = get_queried_object();
			if ( is_null( $queried_object ) ) {
				return;
			}
		}
	}

	public static function pre_get_block_template( $template, $id, $template_type ) {
		// Add protection against invalid ids.
		if ( ! is_string( $id ) || ! strstr( $id, '//' ) ) {
			return null;
		}
		// Add protection against invalid template types.
		if (
			'wp_template' !== $template_type &&
			'wp_template_part' !== $template_type
		) {
			return null;
		}
		$template_name_parts = explode( '//', $id );
		$theme               = $template_name_parts[0] ?? '';
		$slug                = $template_name_parts[1] ?? '';

		if ( empty( $theme ) || empty( $slug ) || ! Block_Templates_Utils::template_is_eligible_for_product_archive_fallback( $slug ) ) {
			return null;
		}

		$wp_query_args  = array(
			'post_name__in' => array( 'archive-wpboutik_product', $slug ),
			'post_type'     => $template_type,
			'post_status'   => array( 'auto-draft', 'draft', 'publish', 'trash' ),
			'no_found_rows' => true,
			'tax_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => $theme,
				),
			),
		);
		$template_query = new \WP_Query( $wp_query_args );
		$posts          = $template_query->posts;

		// If we have more than one result from the query, it means that the current template is present in the db (has
		// been customized by the user) and we should not return the `archive-wpboutik_product` template.
		if ( count( $posts ) > 1 ) {
			return null;
		}

		if ( count( $posts ) > 0 && 'archive-wpboutik_product' === $posts[0]->post_name ) {
			$template = _build_block_template_result_from_post( $posts[0] );

			if ( ! is_wp_error( $template ) ) {
				$template->id          = $theme . '//' . $slug;
				$template->slug        = $slug;
				$template->title       = Block_Templates_Utils::get_block_template_title( $slug );
				$template->description = Block_Templates_Utils::get_block_template_description( $slug );
				unset( $template->source );

				return $template;
			}
		}

		return $template;
	}

	public static function get_block_file_template( $template, $id, $template_type ) {
		$template_name_parts = explode( '//', $id );

		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}

		list( $template_id, $template_slug ) = $template_name_parts;

		// If the theme has an archive-wpboutik_product.html template, but not a taxonomy-product_cat/tag/attribute.html template let's use the themes archive-wpboutik_product.html template.
		if ( Block_Templates_Utils::template_is_eligible_for_product_archive_fallback_from_theme( $template_slug ) ) {
			$template_path   = Block_Templates_Utils::get_theme_template_path( 'archive-wpboutik_product' );
			$template_object = Block_Templates_Utils::create_new_block_template_object( $template_path, $template_type, $template_slug, true );

			return Block_Templates_Utils::build_template_result_from_file( $template_object, $template_type );
		}

		// If we are not dealing with a wpboutik template let's return early and let it continue through the process.
		if ( Block_Templates_Utils::PLUGIN_SLUG !== $template_id ) {
			return $template;
		}

		// If we don't have a template let Gutenberg do its thing.
		if ( ! self::block_template_is_available( $template_slug, $template_type ) ) {
			return $template;
		}

		$directory          = Block_Templates_Utils::get_templates_directory( $template_type );
		$template_file_path = $directory . '/' . $template_slug . '.html';
		$template_object    = Block_Templates_Utils::create_new_block_template_object( $template_file_path, $template_type, $template_slug );
		$template_built     = Block_Templates_Utils::build_template_result_from_file( $template_object, $template_type );

		if ( null !== $template_built ) {
			return $template_built;
		}

		// Hand back over to Gutenberg if we can't find a template.
		return $template;
	}

	public static function add_block_templates( $query_result, $query, $template_type ) {
		if ( ! Block_Templates_Utils::supports_block_templates( $template_type ) ) {
			return $query_result;
		}

		if (is_page()) {
			if (get_the_ID() == wpboutik_get_page_id( 'cart' )) {
				$templates = self::get_block_templates( ['cart'], $template_type );
				return $templates;
			}
		}

		$post_type      = isset( $query['post_type'] ) ? $query['post_type'] : '';
		$slugs          = isset( $query['slug__in'] ) ? $query['slug__in'] : array();
		$template_files = self::get_block_templates( $slugs, $template_type );
		if (empty($template_files)) {
			return $query_result;
		} else {
			$query_result = [];
		}
		// @todo: Add apply_filters to _gutenberg_get_template_files() in Gutenberg to prevent duplication of logic.
		foreach ( $template_files as $template_file ) {

			// If we have a template which is eligible for a fallback, we need to explicitly tell Gutenberg that
			// it has a theme file (because it is using the fallback template file). And then `continue` to avoid
			// adding duplicates.
			if ( Block_Templates_Utils::set_has_theme_file_if_fallback_is_available( $query_result, $template_file ) ) {
				continue;
			}
			// If the current $post_type is set (e.g. on an Edit Post screen), and isn't included in the available post_types
			// on the template file, then lets skip it so that it doesn't get added. This is typically used to hide templates
			// in the template dropdown on the Edit Post page.
			if ( $post_type &&
			     isset( $template_file->post_types ) &&
			     ! in_array( $post_type, $template_file->post_types, true )
			) {
				continue;
			}

			// It would be custom if the template was modified in the editor, so if it's not custom we can load it from
			// the filesystem.
			if ( 'custom' !== $template_file->source ) {
				$template = Block_Templates_Utils::build_template_result_from_file( $template_file, $template_type );
			} else {
				$template_file->title       = Block_Templates_Utils::get_block_template_title( $template_file->slug );
				$template_file->description = Block_Templates_Utils::get_block_template_description( $template_file->slug );
				$query_result[]             = $template_file;
				continue;
			}

			$is_not_custom   = false === array_search(
					wp_get_theme()->get_stylesheet() . '//' . $template_file->slug,
					array_column( $query_result, 'id' ),
					true
				);
			$fits_slug_query =
				! isset( $query['slug__in'] ) || in_array( $template_file->slug, $query['slug__in'], true );
			$fits_area_query =
				! isset( $query['area'] ) || ( property_exists( $template_file, 'area' ) && $template_file->area === $query['area'] );
			$should_include  = $is_not_custom && $fits_slug_query && $fits_area_query;
			if ( $should_include ) {
				$query_result[] = $template;
			}
		}

		// We need to remove theme (i.e. filesystem) templates that have the same slug as a customised one.
		// This only affects saved templates that were saved BEFORE a theme template with the same slug was added.
		$query_result = Block_Templates_Utils::remove_theme_templates_with_custom_alternative( $query_result );
		/**
		 * WPB templates from theme aren't included in `self::get_block_templates()` but are handled by Gutenberg.
		 * We need to do additional search through all templates file to update title and description for WPB
		 * templates that aren't listed in theme.json.
		 */
		$query_result = array_map(
			function ( $template ) {
				if ( 'theme' === $template->origin && Block_Templates_Utils::template_has_title( $template ) ) {
					return $template;
				}
				if ( $template->title === $template->slug ) {
					$template->title = Block_Templates_Utils::get_block_template_title( $template->slug );
				}
				if ( ! $template->description ) {
					$template->description = Block_Templates_Utils::get_block_template_description( $template->slug );
				}

				return $template;
			},
			$query_result
		);
		return $query_result;
	}


	public static function add_archive_product_to_eligible_for_fallback_templates( $template_hierarchy ) {
		$template_slugs = array_map(
			'_strip_template_file_suffix',
			$template_hierarchy
		);

		$templates_eligible_for_fallback = array_filter(
			$template_slugs,
			array( Block_Templates_Utils::class, 'template_is_eligible_for_product_archive_fallback' )
		);

		if ( count( $templates_eligible_for_fallback ) > 0 ) {
			$template_hierarchy[] = 'archive-wpboutik_product';
		}

		return $template_hierarchy;
	}


	public static function get_block_templates_from_db( $slugs = array(), $template_type = 'wp_template' ) {
		return Block_Templates_Utils::get_block_templates_from_db( $slugs, $template_type );
	}

	public static function block_template_is_available( $template_name, $template_type = 'wp_template' ) {
		if ( ! $template_name ) {
			return false;
		}
		$directory = Block_Templates_Utils::get_templates_directory( $template_type ) . '/' . $template_name . '.html';

		return is_readable(
			       $directory
		       ) || self::get_block_templates( array( $template_name ), $template_type );
	}

	public static function get_block_templates( $slugs = array(), $template_type = 'wp_template' ) {
		$templates_from_db  = Block_Templates_Utils::get_block_templates_from_db( $slugs, $template_type );
		$templates_from_wpb = self::get_block_templates_from_wpboutik( $slugs, $templates_from_db, $template_type );
		$templates          = array_merge( $templates_from_db, $templates_from_wpb );

		return $templates;
		// return Block_Templates_Utils::filter_block_templates_by_feature_flag( $templates );
	}


	public static function get_block_templates_from_wpboutik( $slugs, $already_found_templates, $template_type = 'wp_template' ) {
		$directory      = Block_Templates_Utils::get_templates_directory( $template_type );
		$template_files = Block_Templates_Utils::get_template_paths( $directory );
		$templates      = array();

		foreach ( $template_files as $template_file ) {
			// Skip the Product Gallery template part, as it is not supposed to be exposed at this point.
			if ( str_contains( $template_file, 'templates/parts/product-gallery.html' ) ) {
				continue;
			}

			$template_slug = Block_Templates_Utils::generate_template_slug_from_path( $template_file );

			// This template does not have a slug we're looking for. Skip it.
			if ( is_array( $slugs ) && count( $slugs ) > 0 && ! in_array( $template_slug, $slugs, true ) ) {
				continue;
			}

			// If the theme already has a template, or the template is already in the list (i.e. it came from the
			// database) then we should not overwrite it with the one from the filesystem.
			if (
				Block_Templates_Utils::theme_has_template( $template_slug ) ||
				count(
					array_filter(
						$already_found_templates,
						function ( $template ) use ( $template_slug ) {
							$template_obj = (object) $template; //phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.Found

							return $template_obj->slug === $template_slug;
						}
					)
				) > 0 ) {
				continue;
			}

			if ( Block_Templates_Utils::template_is_eligible_for_product_archive_fallback_from_db( $template_slug, $already_found_templates ) ) {
				$template              = clone Block_Templates_Utils::get_fallback_template_from_db( $template_slug, $already_found_templates );
				$template_id           = explode( '//', $template->id );
				$template->id          = $template_id[0] . '//' . $template_slug;
				$template->slug        = $template_slug;
				$template->title       = Block_Templates_Utils::get_block_template_title( $template_slug );
				$template->description = Block_Templates_Utils::get_block_template_description( $template_slug );
				$templates[]           = $template;
				continue;
			}

			// If the theme has an archive-wpboutik_product.html template, but not a taxonomy-product_cat/tag/attribute.html template let's use the themes archive-wpboutik_product.html template.
			if ( Block_Templates_Utils::template_is_eligible_for_product_archive_fallback_from_theme( $template_slug ) ) {
				$template_file = Block_Templates_Utils::get_theme_template_path( 'archive-wpboutik_product' );
				$templates[]   = Block_Templates_Utils::create_new_block_template_object( $template_file, $template_type, $template_slug, true );
				continue;
			}

			// At this point the template only exists in the Blocks filesystem, if is a taxonomy-product_cat/tag/attribute.html template
			// let's use the archive-wpboutik_product.html template from Blocks.
			if ( Block_Templates_Utils::template_is_eligible_for_product_archive_fallback( $template_slug ) ) {
				$template_file = self::get_template_path_from_wpboutik( 'archive-wpboutik_product' );
				$templates[]   = Block_Templates_Utils::create_new_block_template_object( $template_file, $template_type, $template_slug, false );
				continue;
			}

			// At this point the template only exists in the Blocks filesystem and has not been saved in the DB,
			// or superseded by the theme.
			$templates[] = Block_Templates_Utils::create_new_block_template_object( $template_file, $template_type, $template_slug );
		}

		return $templates;
	}

	public static function get_template_path_from_wpboutik( $template_slug, $template_type = 'wp_template' ) {
		return Block_Templates_Utils::get_templates_directory( $template_type ) . '/' . $template_slug . '.html';
	}

}