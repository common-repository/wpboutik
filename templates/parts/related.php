<?php
if ( get_theme_mod( 'wpboutik_show_related_products', 'yes' ) == 'no' ) {
	return false;
}

$id_products_upsell = get_post_meta( get_the_ID(), 'id_products_upsell', true );
if ( ! empty( $id_products_upsell ) ) : ?>
    <section aria-labelledby="related-heading" class="wpb-related-products">
        <h2 id="related-heading" class="product-title">
            Produits recommandés
        </h2>

        <div class="wpb-product-list">
			<?php
			$id_products_upsell = json_decode( $id_products_upsell );
			$args               = array(
				'post_type'    => 'wpboutik_product',
				'post__not_in' => array( get_the_ID() ),
				'post__in'     => $id_products_upsell,
			);

			$related_query = new WP_Query( $args );
			if ( $related_query->have_posts() ) :
				while ( $related_query->have_posts() ) :
					$related_query->the_post();
					wpb_template_parts( 'product-card' );
				endwhile;
			endif;
			wp_reset_query(); ?>
        </div>
    </section>
<?php
else :
	$sorted_terms = wpboutik_sort_terms_hierarchy( get_the_terms( get_the_ID(), 'wpboutik_product_cat' ) );
	if ( $sorted_terms && ! is_wp_error( $sorted_terms ) ) :
		$category_ids = array();

		foreach ( $sorted_terms as $category ) {
			$category_ids[] = $category->term_id;
		}

		$args = array(
			'post_type'      => 'wpboutik_product',
			'posts_per_page' => get_theme_mod( 'wpboutik_number_related_products', 4 ),
			'post__not_in'   => array( get_the_ID() ),
			'orderby'        => 'rand',
			'tax_query'      => array(
				array(
					'taxonomy' => 'wpboutik_product_cat',
					'field'    => 'id',
					'terms'    => $category_ids,
				),
			),
		);

		$related_query = new WP_Query( $args );
		if ( $related_query->have_posts() ) : ?>
            <section class="wpb-related-products" aria-labelledby="related-heading">
                <h2 id="related-heading" class="wpb-product-title">
                    Produits en rapport
                </h2>

                <div class="wpb-product-list">
					<?php
					while ( $related_query->have_posts() ) :
						$related_query->the_post();
						wpb_template_parts( 'product-card' );
					endwhile; ?>
                </div>
            </section>
		<?php
		endif;
		wp_reset_query();
	endif;
endif;
