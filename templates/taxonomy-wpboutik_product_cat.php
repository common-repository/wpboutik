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

$wpboutik_show_archive_sidebar = wpboutik_get_show_archive_sidebar();

if ( 'yes' === wpboutik_show_breadcrumb() ) : ?>
    <div class="wpb-container">
		<?php wpb_template_parts( 'breadcrumbs' ); ?>
    </div>
<?php endif; ?>
<div class="wpb-container wpb-content with-sidebar <?php echo ( is_active_sidebar( 'wpboutik_archive_sidebar' ) ) ? 'aside-' . $wpboutik_show_archive_sidebar : ''; ?>">

<?php
if ( is_active_sidebar( 'wpboutik_archive_sidebar' ) && $wpboutik_show_archive_sidebar != 'hidden' ) :
	echo '<aside id="secondary" class="widget-area wpb-widget wpb-product-sidebar">';
	dynamic_sidebar( 'wpboutik_archive_sidebar' );
	echo '</aside>';
endif; ?>
    <!-- CONTENT START -->
    <div class="wpb-single-product-content">

    <h2 class="product-title"><?php echo single_term_title( '', false ); ?></h2>
<?php if ( have_posts() ) : ?>
    <div class="wpb-filter-fields">
        <div class="wpb-field">
			<?php wpb_get_select_product_cat(); ?>
        </div>
        <div class="wpb-field">
			<?php wpb_get_select_ordering(); ?>
        </div>
    </div>

	<?php
	$desc = get_term_meta( get_queried_object()->term_id, 'wpboutik_description', true );
	if ( ! empty( $desc ) ) : ?>
        <div>
			<?php echo $desc; ?>
        </div>
	<?php endif; ?>
    <div class="wpb-product-list">
		<?php
		$currency_symbol = get_wpboutik_currency_symbol();
		$i               = 0;
		while ( have_posts() ) :
			the_post();
			wpb_template_parts( 'product-card' );
		endwhile; ?>
    </div>


	<?php
	$big = 999999999; // need an unlikely integer
	echo wpboutik_paginate_links( array(
		'base'    => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
		'format'  => '?paged=%#%',
		'current' => max( 1, get_query_var( 'paged' ) )
	) ); ?>
    </div>
    </div>
    <!-- CONTENT END -->
</div>
<?php
else :
	_e( 'Aucun produit n\'existe avec ce filtre.', 'wpboutik' );
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