<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<div <?= get_block_wrapper_attributes() ?>>
	<div class="wp-block-buttons">
		<a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'checkout' ) ) ); ?>"
		class="wp-block-button__link wp-element-button">
			<?php _e( 'Checkout', 'wpboutik' ); ?>
		</a>
	</div>

	<div>
		<p>
			<?php _e( 'or', 'wpboutik' ); ?>
			<a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ); ?>">
				<?php _e( 'Continue Shopping', 'wpboutik' ); ?>
				<span aria-hidden="true"> &rarr;</span>
			</a>
		</p>
	</div>
</div>