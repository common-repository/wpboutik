<?php
if ( 'yes' === wpboutik_show_breadcrumb() ) :
	$sorted_terms = wpboutik_sort_terms_hierarchy( get_the_terms( get_the_ID(), 'wpboutik_product_cat' ) ); ?>
    <nav aria-label="Breadcrumb" class="wpb-breadcrumb">
        <ol role="list">
            <li>
                <a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ); ?>">
					<?php _e( 'All products', 'wpboutik' ); ?>
                </a>
                <svg viewBox="0 0 6 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4.878 4.34H3.551L.27 16.532h1.327l3.281-12.19z" fill="currentColor"/>
                </svg>
            </li>
			<?php
			wpboutik_print_terms_hierarchy( $sorted_terms ); ?>
            <!--<li>
			<div class="flex items-center">
			<a href="#" class="mr-4 text-sm font-medium text-gray-900">Clothing</a>
			<svg viewBox="0 0 6 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="h-5 w-auto text-gray-300">
			<path d="M4.878 4.34H3.551L.27 16.532h1.327l3.281-12.19z" fill="currentColor" />
			</svg>
			</div>
			</li>-->

			<?php if ( is_singular( 'wpboutik_product' ) ) : ?>
                <li class="text-sm">
                    <a href="#" aria-current="page">
						<?php the_title(); ?>
                    </a>
                </li>
			<?php endif; ?>
        </ol>
    </nav>
<?php endif; ?>