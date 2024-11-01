<?php

namespace NF\WPBOUTIK;

class Blocks_Template_Object {

	/**
	 * Informations about FSE templates, title and description, ordered by slug;
	 */
	public static function templatesInfos(): array {
		$infos = [
			'single-product'  => [
				'title'       => __( 'Page des produits', 'wpboutik' ),
				'description' => __( 'Page permettant l\'affichage du détail des produits', 'wpboutik' )
			],
			'archive-product' => [
				'title'       => __( 'Pages d\'archives des produits.', 'wpboutik' ),
				'description' => ''
			],
			'cart'            => [
				'title'       => __( 'Page panier', 'wpboutik' ),
				'description' => __( 'Page affichant le panier du visiteur', 'wpboutik' )
			],
		];

		return $infos;
	}


	public static function build_template_result_from_post( $post ) {
		$terms = get_the_terms( $post, 'wp_theme' );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		if ( ! $terms ) {
			return new \WP_Error( 'template_missing_theme', __( 'No theme is defined for this template.', 'wpboutik' ) );
		}

		$theme          = $terms[0]->name;
		$has_theme_file = true;

		$template                 = new \WP_Block_Template();
		$template->wp_id          = $post->ID;
		$template->id             = $theme . '//' . $post->post_name;
		$template->theme          = $theme;
		$template->content        = $post->post_content;
		$template->slug           = $post->post_name;
		$template->source         = 'custom';
		$template->type           = $post->post_type;
		$template->description    = $post->post_excerpt;
		$template->title          = $post->post_title;
		$template->status         = $post->post_status;
		$template->has_theme_file = $has_theme_file;
		$template->is_custom      = false;
		$template->post_types     = array(); // Don't appear in any Edit Post template selector dropdown.

		if ( 'wp_template_part' === $post->post_type ) {
			$type_terms = get_the_terms( $post, 'wp_template_part_area' );
			if ( ! is_wp_error( $type_terms ) && false !== $type_terms ) {
				$template->area = $type_terms[0]->name;
			}
		}

		if ( 'wpboutik' === $theme || 'wpboutik' === strtolower( $theme ) ) {
			$template->origin = 'plugin';
		}

		return $template;
	}
}