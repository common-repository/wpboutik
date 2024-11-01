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

$currency_symbol = get_wpboutik_currency_symbol();
$activate_tax    = wpboutik_get_option_params( 'activate_tax' );

if ( isset( $order ) ) :

	do_action( 'wpboutik_before_thankyou', $order->id ); ?>

	<?php /*if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'wpboutik' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'wpboutik' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'wpboutik' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else :*/ ?>

    <main class="bg-white px-4 pt-16 pb-24 sm:px-6 sm:pt-24 lg:px-8 lg:py-32">
        <div class="mx-auto max-w-4xl">
            <div>
                <h1 class="text-base font-medium text-indigo-600"><?php esc_html_e( 'Thank you!', 'wpboutik' ); ?></h1>
                <p class="mt-2 text-4xl font-bold tracking-tight"><?php esc_html_e( "It's on the way!", 'wpboutik' ); ?></p>

				<?php
				if ( $order->payment_type === 'bacs' ) {
					$bacs_inscruction       = wpboutik_get_option_params( 'bacs_inscruction' );
					$bacs_num_account       = wpboutik_get_option_params( 'bacs_num_account' );
					$bacs_name_bank         = wpboutik_get_option_params( 'bacs_name_bank' );
					$bacs_code_guichet_bank = wpboutik_get_option_params( 'bacs_code_guichet_bank' );
					$bacs_iban_bank         = wpboutik_get_option_params( 'bacs_iban_bank' );
					$bacs_bicswift_bank     = wpboutik_get_option_params( 'bacs_bicswift_bank' );
					if ( ! empty( $bacs_num_account ) && ! empty( $bacs_name_bank ) ) {
						if ( ! empty( $bacs_inscruction ) ) {
							echo wp_kses_post( wpautop( wptexturize( wp_kses_post( $bacs_inscruction ) ) ) );
						}
						$account_html = '<ul role="list" class="mt-3 grid grid-cols-1 sm:grid-cols-1 md:grid-cols-4 gap-4">' . PHP_EOL;

						// BACS account fields shown on the thanks page and in emails.
						$account_fields = apply_filters(
							'wpboutik_bacs_account_fields',
							array(
								'bank_name'      => array(
									'label' => __( 'Bank', 'wpboutik' ),
									'value' => $bacs_name_bank,
								),
								'account_number' => array(
									'label' => __( 'Account number', 'wpboutik' ),
									'value' => $bacs_num_account,
								),
								'code_guichet'   => array(
									'label' => __( 'Code guichet', 'wpboutik' ),
									'value' => $bacs_code_guichet_bank,
								),
								'iban'           => array(
									'label' => __( 'IBAN', 'wpboutik' ),
									'value' => $bacs_iban_bank,
								),
								'bic'            => array(
									'label' => __( 'BIC', 'wpboutik' ),
									'value' => $bacs_bicswift_bank,
								),
							),
							$order->id
						);

						$i = 0;
						foreach ( $account_fields as $field_key => $field ) {
							if ( ! empty( $field['value'] ) ) {
								$col_class = 'col-span-2';
								if ( $i % 2 === 0 ) {
									$col_class = 'col-span-1';
								}
								$account_html .= '<li class="' . esc_attr( $field_key . ' ' . $col_class ) . ' *flex rounded-md shadow-sm"><div class="flex flex-1 items-center justify-between truncate rounded-md border border-gray-200 bg-white"><div class="flex-1 truncate px-4 py-2 text-sm"><p class="m-0 font-medium text-gray-900 hover:text-gray-600">' . wp_kses_post( $field['label'] ) . '</p><p class="m-0 text-gray-500"><strong>' . wp_kses_post( wptexturize( $field['value'] ) ) . '</strong></p></div></div></li>' . PHP_EOL;
								$i ++;
							}
						}

						$account_html .= '</ul>';
						echo '<div><h2 class="text-sm font-medium text-gray-500">' . esc_html__( 'Our bank details', 'wpboutik' ) . '</h2>' . wp_kses_post( PHP_EOL . $account_html ) . '</div>';
					}
				}
				?>

				<?php if ( $order->payment_type === 'bacs' || $order->status === 'on-hold' ) : ?>
                    <p class="mt-2 text-base text-gray-500">Votre commande #<?php echo $order->id; ?> ne sera pas
                        effective tant que les fonds ne seront pas reçus.</p>
				<?php elseif ( $order->status === 'failed' ) : ?>
                    <p class="mt-2 text-base text-gray-500">Votre commande #<?php echo $order->id; ?> a échouée car nous
                        n'avons pas reçus les fonds.</p>
				<?php else : ?>
                    <p class="mt-2 text-base text-gray-500">Votre commande #<?php echo $order->id; ?> a été expédiée et
                        sera bientôt avec vous.</p>
				<?php endif; ?>

                <!--<dl class="mt-12 text-sm font-medium">
					<dt class="text-gray-900">Tracking number</dt>
					<dd class="mt-2 text-indigo-600">51547878755545848512</dd>
				</dl>-->
            </div>

            <section aria-labelledby="order-heading" class="mt-10 border-t border-gray-200">
                <h2 id="order-heading" class="sr-only"><?php esc_html_e( 'Your order', 'wpboutik' ); ?></h2>

                <h3 class="sr-only">Items</h3>

				<?php
				if ( $products ) :
					if ( $activate_tax ) {
						$taxes_class = array();
					}
					foreach ( $products as $product ) : ?>
                        <div class="flex space-x-6 border-b border-gray-200 py-10">
							<?php if ( has_post_thumbnail( $product->wp_product_id ) ) :
								echo get_the_post_thumbnail( $product->wp_product_id, 'post-thumbnail', array(
									'class' => 'h-20 w-20 flex-none rounded-lg object-contain sm:h-40 sm:w-40',
									'alt'   => esc_html( $product->name )
								) );
							else :
								echo wpb_get_default_image( 'h-20 w-20 flex-none rounded-lg object-contain sm:h-40 sm:w-40', $product->wp_product_id, $product->variation_id );
							endif; ?>

                            <div class="flex flex-auto flex-col">
                                <div>
                                    <h4 class="font-medium text-gray-900">
										<?php
										$array_name = explode( '-', $product->name );
										$name       = $array_name[0];
										$name .= ( ! isset( $array_name[1] ) ) ? '' : ' - ';

										foreach ( explode( ',', $array_name[1] ) as $option ) {
											if ( preg_match( '/^#[0-9a-fA-F]+$/', trim( $option ) ) ) {
												$name .= '<span style="display: inline-block;width: 1.1em;height: 1.1em;vertical-align: middle;background-color: ' . $option . ';"></span>,';
											} else {
												$name .= $option . ', ';
											}
										}

										$name = substr( $name, 0, - 2 );

										$recursive              = get_post_meta( get_the_ID(), 'recursive', true );
										if ( $product->type == 'abonnement' || ( $product->type == 'plugin' && $recursive == 1 ) ) {
											if ( $product->variation_id != "0" ) {
												$variants  = get_post_meta( $product->wp_product_id, 'variants', true );
												$variation = wpboutik_get_variation_by_id( json_decode( $variants ), $product->variation_id );
												if ( $variation ) {
													//$recursive        = $variation->recursive;
													$recursive_type   = $variation->recursive_type;
													$recursive_number = $variation->recursive_number;
												}
											} else {
												//$recursive        = get_post_meta( $product->wp_product_id, 'recursive', true );
												$recursive_type   = get_post_meta( $product->wp_product_id, 'recursive_type', true );
												$recursive_number = get_post_meta( $product->wp_product_id, 'recursive_number', true );
											}
											$name .= ' - ' . display_recursivity( $recursive_type, $recursive_number );
										} ?>
                                        <a href="<?php echo get_permalink($product->wp_product_id); ?>"><?php echo $name; ?></a>
                                    </h4>
                                    <!--<p class="mt-2 text-sm text-gray-600">This glass bottle comes with a mesh insert for
                                        steeping tea or cold-brewing coffee. Pour from any angle and remove the top for easy
                                        cleaning.</p>-->
									<?php
									if ( $order->status === 'completed' && get_post_meta( $product->wp_product_id, 'type', true ) === "virtual_product" ) {
										$files = get_post_meta( $product->wp_product_id, 'files', true );
										if ( $files ) {
											$files = json_decode( $files );
											//echo 'Liste des fichiers téléchargable :<br>'; ?>
                                            <ul role="list"
                                                class="divide-y divide-gray-100 rounded-md border-solid border border-gray-200">
												<?php foreach ( $files as $file ) : ?>
                                                    <li class="flex items-center justify-between py-4 pl-4 pr-5 text-sm leading-6">
                                                        <div class="flex w-0 flex-1 items-center">
                                                            <svg class="h-5 w-5 flex-shrink-0 text-gray-400"
                                                                 viewBox="0 0 20 20" fill="currentColor"
                                                                 aria-hidden="true">
                                                                <path fill-rule="evenodd"
                                                                      d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z"
                                                                      clip-rule="evenodd"/>
                                                            </svg>
                                                            <div class="ml-4 flex min-w-0 flex-1 gap-2">
                                                                <span class="truncate font-medium"><?php echo $file->name; ?></span>
                                                                <!--<span class="flex-shrink-0 text-gray-400">2.4mb</span>-->
                                                            </div>
                                                        </div>
                                                        <div class="ml-4 flex-shrink-0">
                                                            <a href="<?php echo WPBOUTIK_APP_URL . $file->file; ?>"
                                                               target="_blank" class="font-medium text-indigo-600"
                                                               download="<?php echo $file->name; ?>"><?php esc_html_e( 'Download', 'wpboutik' ); ?></a>
                                                        </div>
                                                    </li>
												<?php endforeach; ?>
                                            </ul>
											<?php
										}
									} ?>
                                </div>
                                <div class="mt-6 flex flex-1 items-end">
                                    <dl class="flex space-x-4 divide-x divide-gray-200 text-sm sm:space-x-6">
                                        <div class="flex">
                                            <dt class="font-medium text-gray-900"><?php esc_html_e( 'Quantity', 'wpboutik' ); ?></dt>
                                            <dd class="ml-2 text-gray-700"><?php echo $product->qty; ?></dd>
                                        </div>
                                        <div class="flex pl-4 sm:pl-6">
                                            <dt class="font-medium text-gray-900"><?php esc_html_e( 'Price', 'wpboutik' ); ?></dt>
                                            <dd class="ml-2 text-gray-700"><?php echo wpboutik_format_number( $product->price_ht ) . $currency_symbol; ?></dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
						<?php
						if ( $activate_tax ) {
							$tax_product                                 = get_post_meta( $product->wp_product_id, 'tax', true );
							$taxes_class[ $tax_product ][ $product->id ] = $product->qty * $product->price_ht;
						}
					endforeach;
				endif;
				?>

                <div class="sm:ml-40 sm:pl-6">
                    <h3 class="sr-only"><?php esc_html_e( 'Your information', 'wpboutik' ); ?></h3>

                    <h4 class="sr-only"><?php esc_html_e( 'Addresses', 'wpboutik' ); ?></h4>
                    <dl class="grid grid-cols-2 gap-x-6 py-10 text-sm">
                        <div>
                            <dt class="font-medium text-gray-900"><?php esc_html_e( 'Shipping address', 'wpboutik' ); ?></dt>
                            <dd class="m-0 mt-2 text-gray-700">
                                <address class="not-italic">
                                    <span class="block"><?php echo $order->order_shipping_first_name . ' ' . $order->order_shipping_last_name; ?></span>
                                    <span class="block"><?php echo $order->order_shipping_address; ?></span>
                                    <span class="block"><?php echo $order->order_shipping_postal_code . ' ' . $order->order_shipping_city; ?></span>
                                </address>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-900"><?php esc_html_e( 'Billing address', 'wpboutik' ); ?></dt>
                            <dd class="m-0 mt-2 text-gray-700">
                                <address class="not-italic">
                                    <span class="block"><?php echo $order->order_billing_first_name . ' ' . $order->order_billing_last_name; ?></span>
                                    <span class="block"><?php echo $order->order_billing_address; ?></span>
                                    <span class="block"><?php echo $order->order_billing_postal_code . ' ' . $order->order_billing_city; ?></span>
                                </address>
                            </dd>
                        </div>
                    </dl>

                    <h4 class="sr-only"><?php esc_html_e( 'Payment', 'wpboutik' ); ?></h4>
                    <dl class="grid grid-cols-2 gap-x-6 border-t border-gray-200 py-10 text-sm">
						<?php if ( isset( $order->payment_type ) ) : ?>
                            <div>
                                <dt class="font-medium text-gray-900"><?php esc_html_e( 'Payment method', 'wpboutik' ); ?></dt>
								<?php
								if ( 'card' === $order->payment_type || 'mollie' === $order->payment_type || 'paybox' === $order->payment_type || 'monetico' === $order->payment_type ) {
									$label = __( 'Credit card', 'wpboutik' );
								} elseif ( 'paypal' === $order->payment_type ) {
									$label = __( 'PayPal', 'wpboutik' );
								} elseif ( 'bacs' === $order->payment_type ) {
									$label = __( 'Bacs / Check', 'wpboutik' );
								} ?>
                                <dd class="m-0 mt-2 text-gray-700">
                                    <p class="m-0"><?php echo $label; ?></p>
                                    <!--<p class="m-0">Mastercard</p>
									<p class="m-0"><span aria-hidden="true">••••</span><span class="sr-only">Ending in </span>1545</p>-->
                                </dd>
                            </div>
						<?php endif; ?>
						<?php if ( $shipping_method ) : ?>
                            <div>
                                <dt class="font-medium text-gray-900"><?php esc_html_e( 'Shipping method', 'wpboutik' ); ?></dt>
                                <dd class="m-0 mt-2 text-gray-700">
                                    <p class="m-0"><?php echo $shipping_method->method; ?></p>
									<?php if ( ! empty( $order->list_point_address ) ) : ?>
                                        <p class="m-0"><?php echo $order->list_point_address; ?></p>
									<?php endif; ?>
                                </dd>
                            </div>
						<?php endif; ?>
                    </dl>

                    <h3 class="sr-only"><?php esc_html_e( 'Summary', 'wpboutik' ); ?></h3>

                    <dl class="space-y-6 border-t border-gray-200 pt-10 text-sm">
                        <div class="flex justify-between">
                            <dt class="font-medium text-gray-900"><?php esc_html_e( 'Subtotal', 'wpboutik' ); ?></dt>
                            <dd class="text-gray-700"><?php echo wpboutik_format_number( $order->subtotal ) . $currency_symbol; ?></dd>
                        </div>
						<?php if ( ! empty( $order->discount ) ) : ?>
                            <div class="flex justify-between">
                                <dt class="flex font-medium text-gray-900">
									<?php
									esc_html_e( 'Discount', 'wpboutik' );
									if ( $order->coupon_type == 'percent' ) {
										$value = $order->coupon_value . '%';
									} else {
										$value = $order->coupon_value . $currency_symbol;
									}
									?>
                                    <span class="ml-2 rounded-full bg-gray-200 py-0.5 px-2 text-xs text-gray-600"><?php echo $order->coupon_code; ?></span>
                                    <span class="py-1 px-2 text-xs text-gray-600">(<?php echo $value; ?>)</span>
                                </dt>
                                <dd class="text-gray-700"><?php echo esc_html( '-' . round( $order->discount, 2 ) . $currency_symbol ); ?> </dd>
                            </div>
						<?php endif; ?>
						<?php
						if ( $activate_tax ) :
							if ( $taxes_class ) :
								$tax_rates = get_wpboutik_tax_rates();
								if ( isset( $tax_rates[ $order->order_shipping_country ] ) ) :
									foreach ( $taxes_class as $tax_class => $products_of_tax ) :
										$count = 0;
										foreach ( $products_of_tax as $value ) {
											if ( ! empty( $order->discount ) ) {
												if ( $order->coupon_type == 'percent' ) {
													$count += $value - ( $value * ( $order->coupon_value / 100 ) );
												} else {
													$count += $value - ( $order->coupon_value / count( $products ) );
												}
											} else {
												$count += $value;
											}
										} ?>
                                        <div class="flex justify-between">
                                            <dt class="font-medium text-gray-900"><?php esc_html_e( 'Taxes', 'wpboutik' ); ?>
                                                <span id="tax_name_<?php echo esc_attr( $tax_class ); ?>">(<?php echo empty( $tax_rates[ $order->order_shipping_country ][ 'name_tx_' . $tax_class ] ) ? '' : $tax_rates[ $order->order_shipping_country ][ 'name_tx_' . $tax_class ]; ?>)</span>
                                            </dt>
											<?php
											$tax_value = ( empty( $tax_rates[ $order->order_shipping_country ][ 'percent_tx_' . $tax_class ] ) ) ? 0 : round( ( $count ) * ( $tax_rates[ $order->order_shipping_country ][ 'percent_tx_' . $tax_class ] / 100 ), 2 ); ?>
                                            <dd class="text-gray-900"><span
                                                        id="tax_<?php echo esc_attr( $tax_class ); ?>"><?php echo esc_html( wpboutik_format_number( $tax_value ) ); ?></span><?php echo esc_html( $currency_symbol ); ?>
                                            </dd>
                                        </div>
									<?php
									endforeach;
								endif;
							endif;
						endif; ?>
						<?php if ( $shipping_method ) : ?>
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-900"><?php esc_html_e( 'Shipping', 'wpboutik' ); ?></dt>
                                <dd class="text-gray-700"><?php echo wpboutik_format_number( $order->shipping_flat_rate ) . $currency_symbol; ?></dd>
                            </div>
						<?php endif; ?>
                        <div class="flex justify-between">
                            <dt class="font-medium text-gray-900"><?php esc_html_e( 'Total', 'wpboutik' ); ?></dt>
                            <dd class="text-gray-900"><?php echo wpboutik_format_number( $order->total ) . $currency_symbol; ?></dd>
                        </div>
                    </dl>
                </div>
            </section>
        </div>
    </main>

	<?php //endif;
	?>

	<?php do_action( 'wpboutik_thankyou', $order->id ); ?>

<?php else : ?>
    <main class="bg-white px-4 pt-16 pb-24 sm:px-6 sm:pt-24 lg:px-8 lg:py-32">
        <div class="mx-auto max-w-4xl">
            <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'wpboutik_thankyou_order_received_text', esc_html__( 'Thank you. Your order has been received.', 'wpboutik' ), null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
        </div>
    </main>

<?php endif; ?>

<?php
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
