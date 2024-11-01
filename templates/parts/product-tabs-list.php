<?php
/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see wpboutik_default_product_tabs()
 */
$product_tabs = apply_filters( 'wpboutik_product_tabs', array() );

if ( ! empty( $product_tabs ) ) : ?>
    <div class="wpb-single-product-content">
		<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
            <h2><?= $product_tab['title'] ?></h2>
			<?php
			if ( isset( $product_tab['callback'] ) ) {
				call_user_func( $product_tab['callback'], $key, $product_tab );
			}
		endforeach; ?>
    </div>
<?php
endif; ?>