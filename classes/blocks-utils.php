<?php

namespace NF\WPBOUTIK;


class Blocks_Utils {

	/**
	 * Return all possible paths where to find fse template
	 * Search in plugin, theme, and parent_theme if needed
	 *
	 * @param string $slug - slug name of template
	 *
	 * @return array $possiblesPaths - all path where search
	 */
	public static function possiblePathsForTemplate( string $slug ): array {
		$theme            = get_stylesheet_directory();
		$parent_theme     = get_template_directory();
		$plugin_templates = WPBOUTIK_BLOCKS_TEMPLATES . '/templates';
		$possible_paths   = [
			[ 'theme', "$theme/templates/wpb-$slug.html" ],
			[ 'theme', "$theme/templates/wpb/$slug.html" ]
		];
		if ( $parent_theme != $theme ) {
			$possible_paths[] = [ 'theme', "$parent_theme/templates/wpb-$slug.html" ];
			$possible_paths[] = [ 'theme', "$parent_theme/templates/wpb/$slug.html" ];
		}
		$possible_paths[] = [ 'plugin', "$plugin_templates/wpb-$slug.html" ];
		$possible_paths[] = [ 'plugin', "$plugin_templates/$slug.html" ];

		return $possible_paths;
	}


	/**
	 * Return the first corresponding file finded to be used as a template
	 *
	 * @param string $slug - template's slug
	 *
	 * @return mixed $template - return file path or false
	 */
	public static function themeOrPluginHasTemplate( string $slug ): mixed {
		$paths = self::possiblePathsForTemplate( $slug );
		foreach ( $paths as $file ) {
			[ $origin, $path ] = $file;
			if ( file_exists( $path ) ) {
				return $file;
			}
		}

		return false;
	}

	/**
	 * Récupère le nom lisible, le slug et le contenu d'un template
	 *
	 * @param string $file - file path
	 *
	 * @return array [$slug, $label, $desciption, $content]
	 */
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


	/**
	 * récupère les templates dans la base de donnée wordpress.
	 *
	 * @param array $slugs - slug de template recherchés
	 * @param string $template_type - by default wp-template
	 *
	 * @return array $templates - list of template objects;
	 */
	public static function get_block_templates_from_db( $slugs = array(), $template_type = 'wp_template' ) {
		$query_args = array(
			'post_type'      => $template_type,
			'posts_per_page' => - 1,
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => array( 'wpboutik', get_stylesheet() ),
				),
			),
		);

		if ( is_array( $slugs ) && count( $slugs ) > 0 ) {
			$query_args['post_name__in'] = $slugs;
		}

		$query               = new \WP_Query( $query_args );
		$saved_wpb_templates = $query->posts;

		return array_map(
			function ( $saved_wpb_templates ) {
				return self::build_template_result_from_post( $saved_wpb_templates );
			},
			$saved_wpb_templates
		);
	}
}