<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<nav aria-label="Breadcrumb" <?= get_block_wrapper_attributes(); ?>>
	<ol role="list">
			<li>
				<a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ); ?>"
						class="mr-4 text-sm font-medium text-gray-900"><?php _e( 'All products', 'wpboutik' ); ?></a>
				<svg viewBox="0 0 6 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" >
						<path d="M4.878 4.34H3.551L.27 16.532h1.327l3.281-12.19z" fill="currentColor"/>
				</svg>
			</li>

			<?php
			$sorted_terms = wpboutik_sort_terms_hierarchy( get_the_terms( get_the_ID(), 'wpboutik_product_cat' ) );
			wpboutik_print_terms_hierarchy( $sorted_terms ); ?>

			<li class="text-sm">
					<a href="#" aria-current="page"
							class="font-medium text-gray-500 hover:text-gray-600"><?php the_title(); ?></a>
			</li>
	</ol>
</nav>