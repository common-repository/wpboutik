<?php

defined( 'ABSPATH' ) || exit;

do_action( 'wpboutik_before_mini_cart' ); ?>


    <div class="WPBpanierDropdown hidden">
		<?php
		$backgroundcolor              = wpboutik_get_backgroundcolor_button();
		$hovercolor                   = wpboutik_get_hovercolor_button();
		$title_product_color          = wpboutik_get_title_product_color();
		$title_product_color_on_hover = wpboutik_get_title_product_color_on_hover();
		$activate_tax                 = wpboutik_get_option_params( 'activate_tax' );
		$currency_symbol              = get_wpboutik_currency_symbol();
		$subtotal                     = 0; ?>
        <div class="minicart-header">
			<?php if ( ! WPB()->cart->is_empty() ) : ?>
                <a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'cart' ) ) ); ?>"
                   class="wpb-link">
					<?php _e( 'View cart', 'wpboutik' ); ?>
                    <span aria-hidden="true"> &rarr;</span>
                </a>
			<?php else : ?>
                <a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ); ?>"
                   class="wpb-link">
                    <span aria-hidden="true"> &larr;</span>
					<?php _e( 'Continue Shopping', 'wpboutik' ); ?>
                </a>
			<?php endif; ?>

        </div>
        <div class="minicart-content">
			<?php if ( ! WPB()->cart->is_empty() ) : ?>
                <ul role="list" class="minicart-subcart">
					<?php
					do_action( 'wpboutik_before_mini_cart_contents' );

					foreach ( WPB()->cart->get_cart() as $cart_item_key => $stored_product ) {
						$stored_product = (object) $stored_product;
						if ( $stored_product->variation_id != "0" ) {
							$variants  = get_post_meta( $stored_product->product_id, 'variants', true );
							$variation = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );
							if ( $variation ) {
								$price        = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;
								$array_name   = explode( '-', $stored_product->product_name );
								$name_product = $array_name[0];

								$options = '';
								foreach ( $variation->name as $option ) {
									if ( preg_match( '/^#[0-9a-fA-F]+$/', $option ) ) {
										$options .= '<span style="display: inline-block;width: 1.1em;height: 1.1em;vertical-align: bottom;background-color: ' . $option . ';"></span>,';
									} else {
										$options .= $option . ',';
									}
								}
								$options = substr( $options, 0, - 1 );

								$sku              = $variation->sku;
								$max_quantity     = $variation->quantity;
								$abonnement       = ( isset( $variation->recursive ) && $variation->recursive == 1 ) ? ' for ' . $variation->recursive_number . ' ' . $variation->recursive_type : false;
								$recursive        = $variation->recursive;
								$recursive_type   = $variation->recursive_type;
								$recursive_number = $variation->recursive_number;

							} else {
								continue;
							}
						} else {
							$price            = get_post_meta( $stored_product->product_id, 'price', true );
							$name_product     = get_the_title( $stored_product->product_id );
							$sku              = get_post_meta( $stored_product->product_id, 'sku', true );
							$max_quantity     = get_post_meta( $stored_product->product_id, 'qty', true );
							$recursive        = get_post_meta( $stored_product->product_id, 'recursive', true );
							$recursive_type   = get_post_meta( $stored_product->product_id, 'recursive_type', true );
							$recursive_number = get_post_meta( $stored_product->product_id, 'recursive_number', true );
						}

						$continu_rupture       = get_post_meta( $stored_product->product_id, 'continu_rupture', true );
						$qty_product_to_bought = ( ! empty( $continu_rupture ) && 1 != $continu_rupture && ! empty( $max_quantity ) && $stored_product->quantity > $max_quantity ) ? $max_quantity : $stored_product->quantity;
						$selling_fees          = get_post_meta( $stored_product->product_id, 'selling_fees', true );
						$type                  = get_post_meta( $stored_product->product_id, 'type', true );
						if ( ( $type != 'abonnement' && $type != "plugin" ) || empty( $selling_fees ) || ! empty( $stored_product->customization['renew'] ) ) {
							$selling_fees = 0;
						}
						$price_renew = '';
						$type        = get_post_meta( $stored_product->product_id, 'type', true );
						if ( $type == 'abonnement' || ( $type == 'plugin' && $recursive ) ) {
							$price_renew = ( ( $selling_fees > 0 ) ? ' ' . __( 'then', 'wpboutik' ) . ' ' . esc_html( wpboutik_format_number( $price ) ) . $currency_symbol . ' ' : ' ' ) . display_recursivity( $recursive_type, $recursive_number );
						}
						$subtotal = $subtotal + ( $qty_product_to_bought * ( $price + $selling_fees ) ); ?>

                        <li class="wpb-productcart">
                            <div class="minicart-product-image">
								<?php if ( has_post_thumbnail( $stored_product->product_id ) ) :
									echo get_the_post_thumbnail( $stored_product->product_id, 'post-thumbnail', array(
										'class' => 'wpb-minicart-image',
										'alt'   => esc_html( $stored_product->product_name )
									) );
								else :
									echo wpb_get_default_image( 'wpb-minicart-image', $stored_product->product_id, $stored_product->variation_id );
								endif; ?>
                            </div>

                            <div class="minicart-product-details">
                                <div class="minicart-product-header">
                                    <h3>
                                        <a href="<?php echo esc_url( get_permalink( $stored_product->product_id ) ); ?>"
                                           style="<?php echo( ! empty( $title_product_color ) ? 'color: ' . $title_product_color : '' ); ?>;--hovercolor: <?php echo $title_product_color_on_hover; ?>"><?php echo $name_product; ?></a>
										<?php if ( ! empty( $options ) ) : ?>
                                            <br>
											<?= $options ?>
										<?php endif; ?>
                                    </h3>
                                    <p class="minicart-product-price"><?php echo esc_html( wpboutik_format_number( $price + $selling_fees ) . $currency_symbol . $price_renew ); ?></p>
                                </div>
								<?php
								if ( ! empty( $stored_product->customization ) ) { ?>
                                    <div class="mt-6 flex flex-col gap-4">
										<?php foreach ( $stored_product->customization as $key => $value ) :
											$name = '';
											switch ( $key ) {
												case 'gift_card_price' :
													$name = __( 'Value', 'wpboutik' );
													break;
												case 'gift_card_mail' :
													$name = __( 'Recipient\'s email', 'wpboutik' );
													break;
												case 'gift_card_message' :
													$name = __( 'Your message:', 'wpboutik' );
													break;
												default :
													$name = $key;
													break;
											}
											?>
                                            <dl class="flex space-x-4 divide-x divide-gray-200 text-sm sm:space-x-6">
                                                <div class="flex">
                                                    <dt class="font-medium text-gray-900"><?= $name ?></dt>
                                                    <dd class="ml-2 text-gray-700"><?php echo $value; ?></dd>
                                                </div>
                                            </dl>
										<?php endforeach; ?>
                                    </div>
									<?php
								}
								?>
                                <div class="wpb-field">
                                    <label for="quantity"
                                           class="sr-only"><?php _e( 'Quantity', 'wpboutik' ); ?>
                                        , <?php echo $name_product; ?></label>
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
                                           class="changeqty wpb-qty-cart"
                                           style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                           value="<?php echo esc_attr( $qty_product_to_bought ); ?>"
                                           step="1"
                                           min="1"
                                           max="<?php echo ( ! empty( $continu_rupture ) && 1 != $continu_rupture ) ? $max_quantity : ''; ?>"
                                           name="quantity" title="Qté" size="4"
                                           placeholder="" inputmode="numeric" autocomplete="off">
                                </div>
                                <button type="button"
                                        data-product_id="<?php echo esc_attr( $stored_product->product_id ); ?>"
                                        data-product_sku="<?php echo esc_attr( $sku ); ?>"
                                        data-nonce="<?php echo wp_create_nonce( 'remove-to-cart-nonce' ); ?>"
                                        data-cart_item_key="<?php echo esc_attr( $cart_item_key ); ?>"
                                        class="wpboutik_single_remove_to_cart_button wpb-btn wpb-lined">
									<?php _e( 'Remove', 'wpboutik' ); ?>
                                </button>
                            </div>
                        </li>

						<?php
					} ?>
                </ul>
			<?php else : ?>
                <p class="wpboutik-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'wpboutik' ); ?></p>
			<?php endif; ?>
        </div>
        <div class="minicart-footer">
			<?php if ( ! WPB()->cart->is_empty() ) : ?>
                <div class="minicart-total">
                    <span><?php _e( 'Panier total', 'wpboutik' ); ?><?php echo ( $activate_tax ) ? ' HT' : ''; ?></span>
					<?php
					$order_total = $subtotal; ?>
                    <span class="ordertotal_mini"><?php echo esc_html( wpboutik_format_number( $order_total ) . $currency_symbol ); ?></span>
                </div>
                <div class="text-center text-sm text-gray-500">
					<?php if ( WPB()->is_subscription_active() ) : ?>
                        <a class="minicart-checkout-link wpb-btn"
                           href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'checkout' ) ) ); ?>"
                           style="--backgroundcolor: #4f46e5;--hovercolor: #6366f1">
							<?php _e( 'Checkout', 'wpboutik' ); ?>
                        </a>
					<?php else : ?>
                        <p><?= __( 'Checkout currently not available !' ) ?></p>
					<?php endif; ?>
                </div>
			<?php else : ?>
                <div class="minicart-total">
                    <span><?php _e( 'Panier total', 'wpboutik' ); ?><?php echo ( $activate_tax ) ? ' HT' : ''; ?></span>
					<?php $order_total = $subtotal; ?>
                    <span class="ordertotal_mini"><?php echo 0 . $currency_symbol; ?></span>
                </div>
			<?php endif; ?>
        </div>
    </div>

<?php do_action( 'wpboutik_after_mini_cart' ); ?>