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

if ( have_posts() ) :
	$backgroundcolor = wpboutik_get_backgroundcolor_button();
	$hovercolor = wpboutik_get_hovercolor_button();
	$title_product_color = wpboutik_get_title_product_color();
	$title_product_color_on_hover = wpboutik_get_title_product_color_on_hover();
	$currency_symbol = get_wpboutik_currency_symbol();
	$wpboutik_show_cart_sidebar = get_theme_mod( 'wpboutik_show_cart_sidebar', 'hidden' );
	$activate_tax = wpboutik_get_option_params( 'activate_tax' ); ?>

    <main>
        <div class="wpb-container wpb-content with-sidebar <?php echo ( is_active_sidebar( 'wpboutik_cart_sidebar' ) ) ? 'aside-' . $wpboutik_show_cart_sidebar : ''; ?>">
			<?php
			if ( is_active_sidebar( 'wpboutik_cart_sidebar' ) && $wpboutik_show_cart_sidebar != 'hidden' && ! wp_is_mobile() ) :
				echo '<aside id="secondary" class="widget-area wpb-widget wpb-product-sidebar">';
				dynamic_sidebar( 'wpboutik_cart_sidebar' );
				echo '</aside>';
			endif; ?>
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900"><?php _e( 'Shopping Cart', 'wpboutik' ); ?></h1>
                <form id="formcartwpb" class="mt-12">
					<?php
					if ( WPB()->cart->is_empty() ) :
						_e( 'Empty cart', 'wpboutik' ); ?>
                        <div class="mt-6 text-center text-sm text-gray-500">
                            <p>
                                <a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ); ?>"
                                   class="wpb-link">
									<?php _e( 'Continue Shopping', 'wpboutik' ); ?>
                                    <span aria-hidden="true"> &rarr;</span>
                                </a>
                            </p>
                        </div>
					<?php
					else : ?>
                        <section aria-labelledby="cart-heading">
                            <h2 id="cart-heading"
                                class="sr-only"><?php _e( 'Items in your shopping cart', 'wpboutik' ); ?></h2>
                            <ul role="list" id="subcart"
                                class="divide-y divide-gray-200 border-t border-b border-gray-200">
								<?php
								$subtotal         = 0;
								$options          = '';
								foreach ( WPB()->cart->get_cart() as $cart_item_key => $stored_product ) :
									$stored_product = (object) $stored_product;
									$selling_fees = get_post_meta( $stored_product->product_id, 'selling_fees', true );
									if ( empty( $selling_fees ) || ( ! empty( $stored_product->customization ) && ! empty( $stored_product->customization['renew'] ) ) ) {
										$selling_fees = 0;
									}
									if ( $stored_product->variation_id != "0" ) {
										$variants  = get_post_meta( $stored_product->product_id, 'variants', true );
										$variation = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );
										if ( $variation ) {

											$price = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;

											$options = '';
											foreach ( $variation->name as $option ) {
												if ( preg_match( '/^#[0-9a-fA-F]+$/', $option ) ) {
													$options .= '<span style="display: inline-block; width: 1.1em; height: 1.1em; background-color: ' . $option . ';"></span>,';
												} else {
													$options .= $option . ',';
												}
											}
											$options = substr( $options, 0, - 1 );

											$price   = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;
											//$options = implode( ', ', $variation->name );

											$name           = get_the_title( $stored_product->product_id );
											$sku            = $variation->sku;
											$max_quantity   = $variation->quantity;
											$recursive      = $variation->recursive;
											$recursive_type = $variation->recursive_type;
											$recursive_number = $variation->recursive_number;

										} else {
											continue;
										}
									} else {
										$price          = get_post_meta( $stored_product->product_id, 'price', true );
										$name           = get_the_title( $stored_product->product_id );
										$sku            = get_post_meta( $stored_product->product_id, 'sku', true );
										$max_quantity   = get_post_meta( $stored_product->product_id, 'qty', true );
										$recursive      = get_post_meta( $stored_product->product_id, 'recursive', true );
										$recursive_type = get_post_meta( $stored_product->product_id, 'recursive_type', true );
										$recursive_number = get_post_meta( $stored_product->product_id, 'recursive_number', true );
									}

									$continu_rupture       = get_post_meta( $stored_product->product_id, 'continu_rupture', true );
									$qty_product_to_bought = ( ! empty( $continu_rupture ) && 1 != $continu_rupture && ! empty( $max_quantity ) && $stored_product->quantity > $max_quantity ) ? $max_quantity : $stored_product->quantity;
									$type                  = get_post_meta( $stored_product->product_id, 'type', true );
									$price_renew           = '';
									if ( $type == 'abonnement' || ( $type == 'plugin' && $recursive ) ) {
										$price_renew = ( ( $selling_fees > 0 ) ? ' ' . __( 'then', 'wpboutik' ) . ' ' . esc_html( wpboutik_format_number( $price ) ) . $currency_symbol . ' ' : ' ' ) . display_recursivity( $recursive_type, $recursive_number );
									}
									$subtotal = $subtotal + ( $qty_product_to_bought * ( $price + $selling_fees ) ); ?>
                                    <li class="wpb-productcart cart">
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

                                        <div class="wpb-productcart-item">
                                            <div class="wpb-productcart-item-details">
                                                <div class="pr-6">
                                                    <h3 class="text-sm">
                                                        <a href="<?php echo esc_url( get_permalink( $stored_product->product_id ) ); ?>"
                                                           class="<?php echo ( empty( $title_product_color ) ) ? 'text-indigo-600' : ''; ?> font-medium text-base hover:text-[var(--hovercolor)]"
                                                           style="<?php echo( ! empty( $title_product_color ) ? 'color: ' . $title_product_color : '' ); ?>;--hovercolor: <?php echo $title_product_color_on_hover; ?>"><?php echo $name; ?></a>
                                                    </h3>
													<?php if ( ! empty( $options ) ) : ?>
                                                        <p class="mt-1 text-xs text-gray-500">
															<?= $options ?>
                                                        </p>
													<?php endif; ?>
													<?php if ( $sku ) : ?>
                                                        <p class="m-0 mt-1 text-sm text-gray-500">SKU
                                                            : <?php echo esc_attr( $sku ); ?></p>
													<?php endif; ?>
													<?php
													if ( ! empty( $stored_product->customization ) ) {
														?>
                                                        <div class="mt-6 flex flex-col gap-4">
															<?php foreach ( $stored_product->customization as $key => $value ) :
																if ( empty( $value ) ) {
																	continue;
																}
																$name = '';
																switch ( $key ) {
																	case 'gift_card_price' :
																		$name  = __( 'Value', 'wpboutik' );
																		$value = esc_html( wpboutik_format_number( $value ) . $currency_symbol );
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
                                                                        <dd class="ml-2 text-gray-700 font-bold"><?php echo $value; ?></dd>
                                                                    </div>
                                                                </dl>
															<?php endforeach; ?>
                                                        </div>
														<?php
													}
													?>

                                                </div>

                                                <p class="text-sm font-medium text-gray-900"><?php echo __( 'Unit price', 'wpboutik' ) . ( $activate_tax ? ' HT' : '' ) . ' : ' . esc_html( wpboutik_format_number( $price + $selling_fees ) . $currency_symbol ) . $price_renew; ?></p>
                                            </div>

                                            <div class="wpb-productcart-item-actions">
                                                <div class="wpb-field">
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
                                                           class="changeqty wpb-qty-cart"
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

                                        </div>
                                    </li>
								<?php
								endforeach; ?>
                            </ul>
                        </section>

                        <!-- Order summary -->
                        <section aria-labelledby="summary-heading" class="mt-6 mx-auto max-w-2xl">
                            <div class="rounded-lg bg-gray-50 px-4 py-6 sm:p-6 lg:p-8">
                                <h2 id="summary-heading"
                                    class="sr-only"><?php _e( 'Order summary', 'wpboutik' ); ?></h2>

                                <div class="flow-root">
                                    <dl class="-my-4 divide-y divide-gray-200 text-sm">
                                        <div class="flex items-center justify-between py-4">
                                            <dt class="text-gray-600"><?php _e( 'Subtotal', 'wpboutik' ); ?></dt>
                                            <dd class="font-medium text-gray-900"
                                                id="subtotal"><?php echo esc_html( wpboutik_format_number( $subtotal ) . $currency_symbol ); ?></dd>
                                        </div>
                                        <div class="flex items-center justify-between py-4">
                                            <dt class="text-base font-medium text-gray-900"><?php _e( 'Order total', 'wpboutik' ); ?><?php echo ( $activate_tax ) ? ' HT' : ''; ?></dt>
											<?php
											$order_total = $subtotal; ?>
                                            <dd class="text-base font-medium text-gray-900"
                                                id="ordertotal"><?php echo esc_html( wpboutik_format_number( $order_total ) . $currency_symbol ); ?></dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                            <div class="mt-10">
								<?php if ( WPB()->is_subscription_active() ) : ?>
                                    <a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'checkout' ) ) ); ?>"
                                       class="wpb-btn">
										<?php _e( 'Checkout', 'wpboutik' ); ?>
                                    </a>
								<?php endif; ?>
                            </div>

                            <div class="mt-6 text-center text-sm text-gray-500">
                                <p>
									<?php _e( 'or', 'wpboutik' ); ?>
                                    <a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ); ?>"
                                       class="wpb-link">
										<?php _e( 'Continue Shopping', 'wpboutik' ); ?>
                                        <span aria-hidden="true"> &rarr;</span>
                                    </a>
                                </p>
                            </div>
                        </section>
					<?php
					endif; ?>
                </form>
				<?php do_action( 'wpboutik_after_cart' ); ?>
            </div>
        </div>
    </main>
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