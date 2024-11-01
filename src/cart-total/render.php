<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<section aria-labelledby="summary-heading" <?= get_block_wrapper_attributes() ?>>
    <div>
        <h2 id="summary-heading">
			<?php _e( 'Order summary', 'wpboutik' ); ?>
        </h2>

        <div class="flow-root">
            <dl>
                <div>
                    <dt>
						<?php _e( 'Subtotal', 'wpboutik' ); ?>
                    </dt>
                    <dd id="subtotal">
						<?= esc_html( WPB()->cart->get_subtotal() . $currency_symbol ); ?>
                    </dd>
                </div>
                <div>
                    <dt><?php _e( 'Order total', 'wpboutik' ); ?><?php echo ( $activate_tax ) ? ' HT' : ''; ?></dt>
                    <dd class="text-base font-medium text-gray-900"
                        id="ordertotal">
						<?= esc_html( WPB()->cart->get_total() . $currency_symbol ); ?>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</section>
