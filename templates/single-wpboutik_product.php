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

global $post;

if ( have_posts() ) :
	$wpboutik_show_sidebar = get_theme_mod( 'wpboutik_show_sidebar', 'hidden' );

while ( have_posts() ) :
	the_post(); ?>
    <script>
        jQuery(document).ready(function ($) {
            $('.imggalfirst')
                .wrap('<span style="display:inline-block"></span>')
                //.css('display', 'block')
                .parent()
                .zoom();

            $('.imggal')
                .wrap('<span style="display:inline-block" class="hidden"></span>')
                //.css('display', 'block')
                .parent()
                .zoom();
        });
    </script>
	<?php if ( 'yes' === wpboutik_show_breadcrumb() ) : ?>
        <div class="wpb-container">
			<?php wpb_template_parts( 'breadcrumbs' ); ?>
        </div>
	<?php endif; ?>

    <div class="wpb-container wpb-content with-sidebar <?php echo ( is_active_sidebar( 'wpboutik_product_sidebar' ) ) ? 'aside-' . esc_attr( $wpboutik_show_sidebar ) : ''; ?>">

        <?php
        if ( is_active_sidebar( 'wpboutik_product_sidebar' ) && $wpboutik_show_sidebar != 'hidden' ) :
            echo '<aside id="secondary" class="widget-area wpb-widget wpb-product-sidebar">';
            dynamic_sidebar( 'wpboutik_product_sidebar' );
            echo '</aside>';
        endif; ?>
        <!-- CONTENT START -->
        <div class="wpb-single-product-content">

            <div class="wpb-single-product-datas">
                <div class="wpb-single-product-content">
                    <?php if ( get_theme_mod( 'wpboutik_single_have_cat', false ) ) :
                        wpb_field( 'category' );
                    endif; ?>

                    <?php wpb_field( 'title', [ 'title_tag' => 'h1', 'link' => false ] ) ?>

                    <?php wpb_field( 'price', [ 'id' => 'current_price' ] ) ?>

                    <?php if ( get_theme_mod( 'wpboutik_single_have_excerpt', true ) ) :
                        wpb_field( 'excerpt-single' );
                    endif; ?>

                    <div class="wpb-single-product-form">
                        <?php wpb_template_parts( 'product-form' ) ?>
                    </div>
	                <?php wpb_template_parts( 'share' ) ?>
                </div>
                <?php wpb_template_parts( 'slideshow' ) ?>
            </div>
            <?php do_action( 'wpboutik_after_single_product', $post ); ?>
            <?php wpb_template_parts( get_theme_mod( 'wpboutik_details_display', 'product-tabs' ) ) ?>
            <?php wpb_template_parts( 'related' ) ?>
        </div>
        <!-- CONTENT END -->
    </div>
<?php
endwhile;
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