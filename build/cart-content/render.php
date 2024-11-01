<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
$currency_symbol = get_wpboutik_currency_symbol();
$activate_tax    = wpboutik_get_option_params( 'activate_tax' );

if ( empty( WPB()->cart ) || WPB()->cart->is_empty() ) : ?>
    <section aria-labelledby="cart-heading" <?= get_block_wrapper_attributes(); ?>>
		<?php _e( 'Empty cart', 'wpboutik' ); ?>
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>
                <a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ); ?>">
					<?php _e( 'Continue Shopping', 'wpboutik' ); ?>
                    <span aria-hidden="true"> &rarr;</span>
                </a>
            </p>
        </div>
    </section>
	<?php
	return;
endif; ?>
<section aria-labelledby="cart-heading" <?= get_block_wrapper_attributes(); ?>>
    <h2 id="cart-heading" class="sr-only">
		<?php _e( 'Items in your shopping cart', 'wpboutik' ); ?>
    </h2>
    <ul role="list" class="divide-y divide-gray-200 border-t border-b border-gray-200" id="subcart">
		<?php
		$subtotal = 0;
		foreach ( WPB()->cart->get_cart() as $cart_item_key => $stored_product ) :
			$stored_product = (object) $stored_product;
			if ( $stored_product->variation_id != "0" ) {
				$variants  = get_post_meta( $stored_product->product_id, 'variants', true );
				$variation = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );
				if ( $variation ) {
					$price            = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;
					$name         = $stored_product->product_name;
					$sku          = $variation->sku;
					$max_quantity = $variation->quantity;
				} else {
					continue;
				}
			} else {
				$price        = get_post_meta( $stored_product->product_id, 'price', true );
				$name         = get_the_title( $stored_product->product_id );
				$sku          = get_post_meta( $stored_product->product_id, 'sku', true );
				$max_quantity = get_post_meta( $stored_product->product_id, 'qty', true );
			}

			$continu_rupture       = get_post_meta( $stored_product->product_id, 'continu_rupture', true );
			$qty_product_to_bought = ( ! empty( $continu_rupture ) && 1 != $continu_rupture && ! empty( $max_quantity ) && $stored_product->quantity > $max_quantity ) ? $max_quantity : $stored_product->quantity;

            $selling_fees = get_post_meta( $stored_product->product_id, 'selling_fees', true );
			$type = get_post_meta( $stored_product->product_id, 'type', true );
			if (($type != 'abonnement' && $type != "plugin") || empty($selling_fees) || !empty($stored_product->customization['renew'])) {
				$selling_fees = 0;
			}

			$subtotal = $subtotal + ( $qty_product_to_bought * ($price + $selling_fees) ); ?>
            <li class="wpb-productcart">
                <div class="flex-shrink-0 bg-white">
					<?php if ( has_post_thumbnail( $stored_product->product_id ) ) :
						echo get_the_post_thumbnail( $stored_product->product_id, 'post-thumbnail', array(
							'class' => 'h-24 w-24 rounded-lg object-contain sm:h-32 sm:w-32',
							'alt'   => esc_html( $stored_product->product_name )
						) );
					else :
						echo wpb_get_default_image( 'h-24 w-24 rounded-lg object-contain sm:h-32 sm:w-32', $stored_product->product_id, $stored_product->variation_id );
					endif; ?>
                </div>

                <div class="relative ml-4 flex flex-1 flex-col justify-between sm:ml-6">
                    <div>
                        <div class="flex justify-between sm:grid sm:grid-cols-2">
                            <div class="pr-6">
                                <h3 class="text-sm">
                                    <a href="<?php echo esc_url( get_permalink( $stored_product->product_id ) ); ?>"
                                       class="font-medium text-base hover:text-[var(--hovercolor)]">
										<?php echo $name; ?>
                                    </a>
                                </h3>
                                <!--<p class="mt-1 text-sm text-gray-500">White</p>-->
								<?php if ( $sku ) : ?>
                                    <p class="m-0 mt-1 text-sm text-gray-500">SKU
                                        : <?php echo esc_attr( $sku ); ?></p>
								<?php endif; ?>
                            </div>

                            <p class="text-right text-sm font-medium text-gray-900"><?php echo __( 'Unit price', 'wpboutik' ) . ( $activate_tax ? ' HT' : '' ) . ' : ' . esc_html( wpboutik_format_number( $price ) . $currency_symbol ); ?></p>
                        </div>

                        <div class="mt-4 flex items-center sm:absolute sm:top-0 sm:left-1/2 sm:mt-0 sm:block">
                            <label for="quantity"
                                   class="sr-only"><?php _e( 'Quantity', 'wpboutik' ); ?>
                                , <?php echo $name; ?></label>
                            <style>
                                input[type=number]::-webkit-inner-spin-button,
                                input[type=number]::-webkit-outer-spin-button {
                                    opacity: 1;
                                }
                            </style>
                            <input type="number"
                                   data-product_id="<?php echo esc_attr( $stored_product->product_id ); ?>"
                                   data-variation_id="<?php echo esc_attr( $stored_product->variation_id ); ?>"
                                   data-nonce="<?php echo wp_create_nonce( 'update-qty-to-cart-nonce' ); ?>"
                                   class="changeqty"
                                   value="<?php echo esc_attr( $qty_product_to_bought ); ?>"
                                   step="1"
                                   min="1"
                                   max="<?php echo ( ! empty( $continu_rupture ) && 1 != $continu_rupture ) ? $max_quantity : ''; ?>"
                                   name="quantity" title="Qté" size="4"
                                   placeholder="" inputmode="numeric" autocomplete="off">
                            <button type="button"
                                    data-product_id="<?php echo esc_attr( $stored_product->product_id ); ?>"
                                    data-product_sku="<?php echo esc_attr( $sku ); ?>"
                                    data-nonce="<?php echo wp_create_nonce( 'remove-to-cart-nonce' ); ?>"
                                    data-cart_item_key="<?php echo esc_attr( $cart_item_key ); ?>"
                                    class="wpboutik_single_remove_to_cart_button"
                            >
                                <span><?php _e( 'Remove', 'wpboutik' ); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </li>
		<?php
		endforeach; ?>
    </ul>
</section>
