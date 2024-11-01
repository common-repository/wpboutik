<?php
$format  = ( ! empty( $args['format'] ) ) ? $args['format'] : 'medium';
$archive = ( isset( $args['archive'] ) && ! empty( $args['archive'] ) ) ? $args['archive'] : '';
if ( $archive ) {
	$args['class'] = ( ! empty( $args['class'] ) ? $args['class'] : '' ) . ' default';
} ?>
<div class="wpb-product-image">
	<?php if ( has_post_thumbnail() ) :
		the_post_thumbnail( $format, array(
			'class' => ! empty( $args['class'] ) ? $args['class'] : '',
			'alt'   => ! empty( $args['alt'] ) ? $args['alt'] : esc_html( get_the_title() )
		) );
		if ( get_theme_mod( 'wpboutik_show_second_image_product', 'no' ) === 'yes' && $archive ) :
			$images = get_post_meta( get_the_ID(), 'galerie_images', true );
			if ( $images ) :
				$images = explode( ',', $images );
				echo wp_get_attachment_image( $images[0], 'large', false, array(
					'class' => 'hover',
					'alt'   => esc_html( get_the_title() . '-2' )
				) );
			endif;
		endif;
	else :
		echo wpb_get_default_image(
			! empty( $args['class'] ) ? $args['class'] : 'h-full w-full object-center lg:h-full lg:w-full'
		);
	endif; ?>
</div>