<?php
$terms = get_the_terms( get_the_ID(), 'wpboutik_product_cat' );
$class = get_theme_mod( 'wpboutik_cat_display', 'wpb-btn' );

if ( ! empty( $terms ) ) {
	echo '<div class="wpb-cat">';
	foreach ( $terms as $term ) {
		echo sprintf( '<a class="' . $class . '" href="%1$s">%2$s</a>',
			esc_url( get_term_link( $term->slug, 'wpboutik_product_cat' ) ),
			esc_html( $term->name )
		);
	}
	echo '</div>';
}
?>