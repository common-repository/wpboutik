<?php
$id      = ( ! empty( $args['id'] ) ) ? 'id="' . $args['id'] . '"' : '';
$classes = ( ! empty( $args['class'] ) ) ? $args['class'] . ' product-item' : 'product-item';
$classes .= ' direction-' . get_theme_mod( 'wpboutik_product_direction', 'column' );
?>

<div class="<?= $classes ?>" <?= $id ?>>
	<?php wpb_field( 'image', array( 'archive' => true ) ) ?>
    <div class="product-card-details">
        <div>
            <h3 class="product-title">
                <a class="product-card-permalink" href="<?php the_permalink(); ?>">
					<?php the_title(); ?>
                </a>
            </h3>
        </div>
		<?php
		wpb_field( 'price' );
		if ( get_theme_mod( 'wpboutik_archive_have_excerpt' ) ) {
			wpb_field( 'excerpt' );
		} ?>
        <div class="product-action">
			<?php wpb_field( 'action' ) ?>
        </div>
    </div>
</div>