<?php
$currency_symbol        = get_wpboutik_currency_symbol();
$variations             = get_post_meta( get_the_ID(), 'variants', true );
$price_before_reduction = get_post_meta( get_the_ID(), 'price_before_reduction', true );
$type                   = get_post_meta( get_the_ID(), 'type', true );
$recursive              = get_post_meta( get_the_ID(), 'recursive', true );
$recursive_type         = get_post_meta( get_the_ID(), 'recursive_type', true );
$recursive_number       = get_post_meta( get_the_ID(), 'recursive_number', true );
$price                  = get_post_meta( get_the_ID(), 'price', true );
$selling_fees           = get_post_meta( get_the_ID(), 'selling_fees', true );
if ( empty( $selling_fees ) ) {
	$selling_fees = 0;
}
?>
<p class="product-price<?= ! empty( $args['class'] ) ? ' ' . $args['class'] : '' ?>" <?= ! empty( $args['id'] ) ? 'id="' . $args['id'] . '"' : '' ?>>
	<?php
	$variations = get_post_meta( get_the_ID(), 'variants', true );
	if ( ! empty( $variations ) && '[]' != $variations ) :
		$min_max_prices = wpboutik_get_min_max_prices( json_decode( $variations ) );
		if ( $min_max_prices['min_price'] != $min_max_prices['max_price'] ) {
			echo wpboutik_format_number( $min_max_prices['min_price'] + $selling_fees ) . ' - ' . wpboutik_format_number( $min_max_prices['max_price'] + $selling_fees ) . ' ' . $currency_symbol;
		} else {
			echo wpboutik_format_number( $min_max_prices['min_price'] + $selling_fees ) . ' ' . $currency_symbol;
		}
	else :
		if ( $price_before_reduction ) : ?>
            <span class="before-reduction-price"><?php echo esc_html( wpboutik_format_number( $price_before_reduction + $selling_fees ) . $currency_symbol ); ?></span>
		<?php endif; ?>
        <span class="price"><?php echo esc_html( wpboutik_format_number( $price + $selling_fees ) . $currency_symbol . ' ' ); ?></span>
		<?php if ( $type == 'abonnement' || ( $type == 'plugin' && $recursive == 1 ) ) : ?>
        <span>
			<?= ' ' . __( 'then', 'wpboutik' ) . ' ' . esc_html( wpboutik_format_number( $price ) . $currency_symbol . ' ' ) . ' ' . display_recursivity( $recursive_type, $recursive_number ) ?>
			</span>
	<?php endif; ?>

	<?php endif; ?>
</p>