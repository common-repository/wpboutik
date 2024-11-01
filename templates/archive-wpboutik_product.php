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
	<?php block_header_area(); ?>
	<?php
} else {
	get_header();
}

$paged   = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$orderby = ( get_query_var( 'orderby' ) ) ? get_query_var( 'orderby' ) : '';

$ordersql = [
	'meta_value_num' => 'DESC',
	'date'           => 'DESC', // Primary sort: by post date
	'title'          => 'ASC'   // Secondary, fallback sort: by post title
];
$meta_key = 'mis_en_avant';

if ( $orderby ) {
	switch ( $orderby ) {
		case 'menu_order':
			$ordersql = [ $orderby, 'ASC' ];
			break;
		case 'popularity':
		case 'rating':
		case 'price':
			$ordersql = [ 'meta_value_num' => 'ASC', 'title' => 'ASC' ];
			$meta_key = $orderby;
			break;
		case 'price-desc':
			$ordersql = [ 'meta_value_num' => 'DESC', 'title' => 'ASC' ];
			$meta_key = 'price';
			break;
		case 'date':
			$ordersql = [ 'date', 'desc' ];
			break;
	}
}

$query = apply_filters( 'wpboutik_query_archive', array(
	'post_type'  => 'wpboutik_product',
	'meta_query' => apply_filters( 'wpboutik_meta_query_archive', array(
		array(
			'key'     => 'price',
			'compare' => 'EXISTS',
		)
	) ),
	'orderby'    => $ordersql,
	'meta_key'   => $meta_key,
	'paged'      => $paged
) );
if ( ! empty( $_GET['search'] ) ) {
	$query['s'] = $_GET['search'];
}
$posts = new WP_Query( $query );

if ( $posts->have_posts() ) :
	$display_shop_page             = wpboutik_get_display_shop_page();
	$wpboutik_show_archive_sidebar = wpboutik_get_show_archive_sidebar();
if ( 'product' === $display_shop_page ) : ?>
    <div class="wpb-container wpb-content with-sidebar <?php echo ( is_active_sidebar( 'wpboutik_archive_sidebar' ) ) ? 'aside-' . $wpboutik_show_archive_sidebar : ''; ?>">

		<?php
		if ( is_active_sidebar( 'wpboutik_archive_sidebar' ) && $wpboutik_show_archive_sidebar != 'hidden' ) :
			echo '<aside id="secondary" class="widget-area wpb-widget wpb-product-sidebar">';
			dynamic_sidebar( 'wpboutik_archive_sidebar' );
			echo '</aside>';
		endif; ?>
        <!-- CONTENT START -->
        <div class="wpb-single-product-content">
            <div class="wpb-filter-fields">
                <div class="wpb-field">
					<?php wpb_get_select_product_cat(); ?>
                </div>
                <div class="wpb-field">
					<?php wpb_get_select_ordering(); ?>
                </div>
            </div>
            <div class="wpb-product-list">
				<?php
				$currency_symbol = get_wpboutik_currency_symbol();
				$i               = 0;
				while ( $posts->have_posts() ) :
					$posts->the_post();
					wpb_template_parts( 'product-card' );
				endwhile; ?>
            </div>

			<?php
			$big = 999999999; // need an unlikely integer
			echo wpboutik_paginate_links( array(
				'base'    => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
				'format'  => '?paged=%#%',
				'current' => max( 1, get_query_var( 'paged' ) ),
				'total'   => $posts->max_num_pages
			) ); ?>
        </div>
    </div>
<?php
elseif ( 'product_cat' === $display_shop_page ) :?>
    <div class="wpb-container wpb-content with-sidebar <?php echo ( is_active_sidebar( 'wpboutik_archive_sidebar' ) ) ? 'aside-' . $wpboutik_show_archive_sidebar : ''; ?>">

		<?php
		if ( is_active_sidebar( 'wpboutik_archive_sidebar' ) && ! wp_is_mobile() ) :
			echo '<aside id="secondary" class="widget-area wpb-widget wpb-product-sidebar">';
			dynamic_sidebar( 'wpboutik_archive_sidebar' );
			echo '</aside>';
		endif; ?>
        <!-- CONTENT START -->
        <div class="wpb-single-product-content">
            <div class="mt-6 grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-4 mb-5">
				<?php
				$categories            = get_terms( [
					'taxonomy'   => 'wpboutik_product_cat',
					'hide_empty' => true,
					'parent'     => 0
				] );
				if ( $categories ) :
					foreach ( $categories as $category ) :
						$term_link = get_term_link( $category );
						$wpb_cat_image = get_term_meta( $category->term_id, 'wpb_cat_image', true ); ?>
                        <div
                                class="group relative flex flex-col overflow-hidden rounded-lg border border-gray-200">
                            <div
                                    class="flex justify-center items-center min-h-[284px] aspect-w-1 aspect-h-1 w-full overflow-hidden group-hover:opacity-75 h-[284px] lg:aspect-none <?php echo ( $wpb_cat_image ) ? 'bg-white' : 'bg-gray-300'; ?>">
								<?php if ( $wpb_cat_image ) : ?>
                                    <img src="<?php echo esc_url( WPBOUTIK_APP_URL . $wpb_cat_image ); ?>"
                                         alt="a finaliser"
                                         class="object-contain">
								<?php
								endif; ?>
                            </div>

                            <div class="flex flex-1 flex-col space-y-2 p-4">
                                <h3 class="text-sm font-medium text-gray-900">
                                    <a href="<?php echo esc_url( $term_link ); ?>">
                                        <span aria-hidden="true" class="absolute inset-0 bottom-20"></span>
										<?php echo $category->name; ?>
                                    </a>
                                </h3>
                            </div>
                        </div>
					<?php
					endforeach;
				endif; ?>
            </div>
        </div>
    </div>
<?php
elseif ( 'product_cat_and_product' === $display_shop_page ) : ?>
    <div class="wpb-container wpb-content with-sidebar <?php echo ( is_active_sidebar( 'wpboutik_archive_sidebar' ) ) ? 'aside-' . $wpboutik_show_archive_sidebar : ''; ?>">

		<?php
		if ( is_active_sidebar( 'wpboutik_archive_sidebar' ) && ! wp_is_mobile() ) :
			echo '<aside id="secondary" class="widget-area wpb-widget wpb-product-sidebar">';
			dynamic_sidebar( 'wpboutik_archive_sidebar' );
			echo '</aside>';
		endif; ?>
        <!-- CONTENT START -->
        <div class="wpb-single-product-content">
			<?php _e( 'Categories' ); ?>
            <div class="mt-6 grid grid-cols-1 gap-y-10 gap-x-6 sm:grid-cols-2 lg:grid-cols-4 mb-5">
				<?php
				$categories            = get_terms( [
					'taxonomy'   => 'wpboutik_product_cat',
					'hide_empty' => true,
					'parent'     => 0
				] );
				if ( $categories ) :
					foreach ( $categories as $category ) :
						$term_link = get_term_link( $category );
						$wpb_cat_image = get_term_meta( $category->term_id, 'wpb_cat_image', true ); ?>
                        <div
                                class="group relative flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white">
                            <div
                                    class="flex justify-center items-center min-h-[284px] aspect-w-1 aspect-h-1 w-full overflow-hidden group-hover:opacity-75 h-[284px] lg:aspect-none <?php echo ( $wpb_cat_image ) ? 'bg-white' : 'bg-gray-300'; ?>">
								<?php if ( $wpb_cat_image ) : ?>
                                    <img src="<?php echo esc_url( WPBOUTIK_APP_URL . $wpb_cat_image ); ?>"
                                         alt="a finaliser"
                                         class="object-contain">
								<?php
								endif; ?>
                            </div>

                            <div class="flex flex-1 flex-col space-y-2 p-4">
                                <h3 class="text-sm font-medium text-gray-900">
                                    <a href="<?php echo esc_url( $term_link ); ?>">
                                        <span aria-hidden="true" class="absolute inset-0 bottom-20"></span>
										<?php echo $category->name; ?>
                                    </a>
                                </h3>
                            </div>
                        </div>
					<?php
					endforeach;
				endif; ?>
            </div>
			<?php _e( 'Products', 'wpboutik' ); ?>
            <div class="wpb-product-list">
				<?php
				$currency_symbol = get_wpboutik_currency_symbol();
				$i               = 0;
				while ( $posts->have_posts() ) :
					$posts->the_post();
					wpb_template_parts( 'product-card' );
				endwhile; ?>
            </div>


			<?php
			$big = 999999999; // need an unlikely integer
			echo wpboutik_paginate_links( array(
				'base'    => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
				'format'  => '?paged=%#%',
				'current' => max( 1, get_query_var( 'paged' ) ),
				'total'   => $posts->max_num_pages
			) ); ?>
        </div>
    </div>
</div>
<?php
endif;
else : ?>
    <div class="wpb-container">
		<?php _e( 'Aucun produit n\'existe avec ce filtre.', 'wpboutik' ); ?>
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