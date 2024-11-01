<?php
if ( wp_is_block_theme() ) {
	?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>"/>
		<?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
    <div class="wp-site-blocks">
	<?php
	block_header_area();
} else {
	get_header();
}

global $wp;
$wpboutik_show_sidebar = get_theme_mod('wpb_show_archive_sidebar');
$is_active_sidebar = is_active_sidebar( 'wpboutik_product_sidebar' );
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;


if ( have_posts() ) : ?>
    <div class="wpb-container with-sidebar <?php echo ( $is_active_sidebar ) ? 'aside-' . esc_attr( $wpboutik_show_sidebar ) : ''; ?>"">
			<?php
			if ( is_active_sidebar( 'wpboutik_product_sidebar' ) && $wpboutik_show_sidebar != 'hidden' ) :
				echo '<aside id="secondary" class="widget-area wpb-widget wpb-product-sidebar">';
				dynamic_sidebar( 'wpboutik_product_sidebar' );
				echo '</aside>';
			endif; ?>

			<div>
				<div class="sm:flex sm:items-baseline sm:justify-between">
						<h2 class="text-2xl font-bold tracking-tight text-gray-900"><?php echo single_term_title( '', false ); ?></h2>
				</div>
				<div class="wpb-product-list">
					<?php
					while ( have_posts() ) :
						the_post();
						wpb_template_parts( 'product-card' );
					endwhile; ?>
				</div>
				<?php
				$big = 999999999; // need an unlikely integer
				echo wpboutik_paginate_links( array(
					'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
					'format' => '?paged=%#%',
					'current' => max( 1, get_query_var('paged') ),
				) ); ?>
			</div>

		</div>
<?php
endif;
if ( wp_is_block_theme() ) {
	block_footer_area();
	echo '</div>';
	wp_footer(); ?>
    </body>
    </html>
	<?php
} else {
	get_footer();
}