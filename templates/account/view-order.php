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
$backgroundcolor = wpboutik_get_backgroundcolor_button();
$hovercolor      = wpboutik_get_hovercolor_button(); ?>

    <main class="relative">
        <div class="wpb-container">
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="divide-y divide-gray-200 lg:grid lg:grid-cols-12 lg:divide-y-0 lg:divide-x">
					<?php

					/**
					 * My Account navigation.
					 */
					do_action( 'wpboutik_account_navigation' ); ?>

                    <div class="divide-y divide-gray-200 lg:col-span-9">
                        <div class="py-6 px-4 sm:p-6 lg:pb-8">

                            <main>
								<?php if ( $invalid_order ) : ?>
                                    <div class="rounded-md bg-red-50 p-4 mb-2">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20"
                                                     fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                          d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                                          clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-red-700">
													<?php echo $invalid_order; ?>
                                                    <a href="<?php echo esc_url( wpboutik_get_page_permalink( 'account' ) ); ?>"
                                                       class="font-medium text-red-700 underline hover:text-red-600"><?php echo esc_html__( 'My account', 'wpboutik' ); ?></a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
								<?php else : ?>
                                    <div class="mx-auto max-w-3xl">
                                        <div class="max-w-xl">
                                            <p class="mt-2 text-4xl font-bold tracking-tight"><?php echo sprintf( __( 'Order #%s', 'wpboutik' ), $order->id ); ?></p>
                                            <p class="mt-2 text-base text-gray-500">Date de commande :
                                                <time datetime="<?php echo date( 'Y-m-d', strtotime( $order->created_at ) ); ?>"><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $order->created_at ) ); ?></time>
                                            </p>
											<?php if ( $order->paid_date ) : ?>
                                                <p class="mt-2 text-base text-gray-500">Date de paiement :
                                                    <time datetime="<?php echo date( 'Y-m-d', strtotime( $order->paid_date ) ); ?>"><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $order->paid_date ) ); ?></time>
                                                </p>
											<?php endif; ?>
											<?php if ( $order->refund_date ) : ?>
                                                <p class="mt-2 text-base text-gray-500">Date de remboursement :
                                                    <time datetime="<?php echo date( 'Y-m-d', strtotime( $order->refund_date ) ); ?>"><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $order->refund_date ) ); ?></time>
                                                </p>
											<?php endif; ?>
											<?php if ( $activate_eu_vat ) : ?>
                                                <p class="mt-2 text-base text-gray-500"><?php echo esc_html( $input_name_eu_vat ); ?>
                                                    : <?php echo ( $order->tva_intra ) ? $order->tva_intra : ''; ?></p>
											<?php endif; ?>
                                            <p class="mt-2 text-sm font-medium text-gray-500 flex items-center">
												<?php if ( 'completed' === $order->status ) : ?>
                                                    <svg class="h-5 w-5 text-green-500"
                                                         xmlns="http://www.w3.org/2000/svg"
                                                         viewBox="0 0 20 20"
                                                         fill="currentColor"
                                                         aria-hidden="true">
                                                        <path fill-rule="evenodd"
                                                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                                              clip-rule="evenodd"/>
                                                    </svg>
												<?php elseif ( 'cancelled' === $order->status ) : ?>
                                                    <svg class="h-5 w-5 text-red-500"
                                                         xmlns="http://www.w3.org/2000/svg"
                                                         fill="none" viewBox="0 0 24 24"
                                                         stroke-width="1.5"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
												<?php elseif ( 'failed' === $order->status ) : ?>
                                                    <svg class="h-5 w-5 text-red-500"
                                                         xmlns="http://www.w3.org/2000/svg"
                                                         fill="none" viewBox="0 0 24 24"
                                                         stroke-width="1.5"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                                                    </svg>
												<?php elseif ( 'refunded' === $order->status ) : ?>
                                                    <svg class="h-5 w-5 text-red-500"
                                                         xmlns="http://www.w3.org/2000/svg"
                                                         fill="none" viewBox="0 0 24 24"
                                                         stroke-width="1.5"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              d="M11.25 9l-3 3m0 0l3 3m-3-3h7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
												<?php else : ?>
                                                    <svg class="h-5 w-5"
                                                         xmlns="http://www.w3.org/2000/svg"
                                                         fill="none" viewBox="0 0 24 24"
                                                         stroke-width="1.5"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
												<?php endif; ?>
                                                Statut de la commande : <?php echo $order->status; ?>
                                            </p>

                                            <!--<dl class="mt-12 text-sm font-medium">
												<dt class="text-gray-900">Tracking number</dt>
												<dd class="mt-2 text-indigo-600">51547878755545848512</dd>
											</dl>-->
                                        </div>

                                        <section aria-labelledby="order-heading" class="mt-10 border-t border-gray-200">
                                            <h2 id="order-heading"
                                                class="sr-only"><?php _e( 'Your order', 'wpboutik' ); ?></h2>

                                            <h3 class="sr-only">Items</h3>

											<?php

											if ( $products ) :
												if ( $activate_tax ) {
													$taxes_class = array();
												}
												foreach ( $products as $product ) :
													$wp_product = get_post( $product->wp_product_id );
													if ( empty( $wp_product ) ) {
														continue;
													} ?>
                                                    <div class="flex space-x-6 border-b border-gray-200 py-10">
                                                        <a href="<?php echo esc_url( $wp_product->guid ); ?>"
                                                           target="_blank">
															<?php if ( has_post_thumbnail( $product->wp_product_id ) ) :
																echo get_the_post_thumbnail( $product->wp_product_id, 'post-thumbnail', array(
																	'class' => 'h-20 w-20 flex-none rounded-lg object-contain sm:h-40 sm:w-40',
																	'alt'   => esc_html( $product->name )
																) );
															else :
																echo wpb_get_default_image( 'h-20 w-20 flex-none rounded-lg object-contain sm:h-40 sm:w-40', $product->wp_product_id, $product->variation_id );
															endif; ?>
                                                        </a>

                                                        <div class="flex flex-auto flex-col">
                                                            <div>
																<?php
																$array_name = explode( '-', $product->name );
																$name       = $array_name[0];
																$name       .= ( ! isset( $array_name[1] ) ) ? '' : ' - ';

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
																}
																?>
                                                                <h4 class="font-medium text-gray-900">
                                                                    <a href="<?php echo esc_url( $wp_product->guid ); ?>"
                                                                       target="_blank"><?php echo $name; ?></a>
                                                                </h4>
                                                                <!--<p class="mt-2 text-sm text-gray-600">This glass bottle comes with a mesh insert for
																	steeping tea or cold-brewing coffee. Pour from any angle and remove the top for easy
																	cleaning.</p>-->
																<?php
																if ( ! empty( $product->customization ) && $customization = json_decode( $product->customization ) ) { ?>
                                                                    <div class="mt-6 flex flex-col gap-4">
																		<?php foreach ( $customization as $key => $value ) :
																			$name = '';
																			switch ( $key ) {
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
																<?php }
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
                                                                                             viewBox="0 0 20 20"
                                                                                             fill="currentColor"
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
                                                                                           target="_blank"
                                                                                           class="font-medium text-indigo-600"
                                                                                           download="<?php echo $file->name; ?>"><?php _e( 'Download', 'wpboutik' ); ?></a>
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
                                                                        <dt class="font-medium text-gray-900"><?php _e( 'Quantity', 'wpboutik' ); ?></dt>
                                                                        <dd class="ml-2 text-gray-700"><?php echo $product->qty; ?></dd>
                                                                    </div>
                                                                    <div class="flex pl-4 sm:pl-6">
                                                                        <dt class="font-medium text-gray-900"><?php _e( 'Price', 'wpboutik' ); ?></dt>
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
                                                <h3 class="sr-only"><?php _e( 'Your information', 'wpboutik' ); ?></h3>

                                                <h4 class="sr-only"><?php _e( 'Addresses', 'wpboutik' ); ?></h4>
                                                <dl class="grid grid-cols-2 gap-x-6 py-10 text-sm">
                                                    <div>
                                                        <dt class="font-medium text-gray-900"><?php _e( 'Shipping address', 'wpboutik' ); ?></dt>
                                                        <dd class="m-0 mt-2 text-gray-700">
                                                            <address class="not-italic">
                                                                <span class="block"><?php echo $order->order_shipping_first_name . ' ' . $order->order_shipping_last_name; ?></span>
                                                                <span class="block"><?php echo $order->order_shipping_address; ?></span>
                                                                <span class="block"><?php echo $order->order_shipping_postal_code . ' ' . $order->order_shipping_city; ?></span>
                                                            </address>
                                                        </dd>
                                                    </div>
                                                    <div>
                                                        <dt class="font-medium text-gray-900"><?php _e( 'Billing address', 'wpboutik' ); ?></dt>
                                                        <dd class="m-0 mt-2 text-gray-700">
                                                            <address class="not-italic">
                                                                <span class="block"><?php echo $order->order_billing_first_name . ' ' . $order->order_billing_last_name; ?></span>
                                                                <span class="block"><?php echo $order->order_billing_address; ?></span>
                                                                <span class="block"><?php echo $order->order_billing_postal_code . ' ' . $order->order_billing_city; ?></span>
                                                            </address>
                                                        </dd>
                                                    </div>
                                                </dl>

                                                <h4 class="sr-only"><?php _e( 'Payment', 'wpboutik' ); ?></h4>
                                                <dl class="grid grid-cols-2 gap-x-6 border-t border-gray-200 py-10 text-sm">
													<?php if ( isset( $order->payment_type ) ) : ?>
                                                        <div>
                                                            <dt class="font-medium text-gray-900"><?php _e( 'Payment method', 'wpboutik' ); ?></dt>
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
                                                            <dt class="font-medium text-gray-900"><?php _e( 'Shipping method', 'wpboutik' ); ?></dt>
                                                            <dd class="m-0 mt-2 text-gray-700">
                                                                <p class="m-0"><?php echo $shipping_method->method; ?></p>
																<?php if ( ! empty( $order->list_point_address ) ) : ?>
                                                                    <p class="m-0"><?php echo $order->list_point_address; ?></p>
																<?php endif; ?>
                                                            </dd>
                                                        </div>
													<?php endif; ?>
                                                </dl>

                                                <h3 class="sr-only">Summary</h3>

                                                <h3 class="sr-only"><?php _e( 'Summary', 'wpboutik' ); ?></h3>

                                                <dl class="space-y-6 border-t border-gray-200 pt-10 text-sm">
                                                    <div class="flex justify-between">
                                                        <dt class="font-medium text-gray-900"><?php _e( 'Subtotal', 'wpboutik' ); ?></dt>
                                                        <dd class="text-gray-700"><?php echo wpboutik_format_number( $order->subtotal ) . $currency_symbol; ?></dd>
                                                    </div>
													<?php if ( ! empty( $order->discount ) ) : ?>
                                                        <div class="flex justify-between">
                                                            <dt class="flex font-medium text-gray-900">
																<?php
																_e( 'Discount', 'wpboutik' );
																if ( $order->coupon_type == 'percent' ) {
																	$value = $order->coupon_value . '%';
																} else {
																	$value = $order->coupon_value . $currency_symbol;
																} ?>
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
                                                                        <dt class="font-medium text-gray-900"><?php _e( 'Taxes', 'wpboutik' ); ?>
                                                                            <span id="tax_name_<?php echo esc_attr( $tax_class ); ?>">(<?php echo $tax_rates[ $order->order_shipping_country ][ 'name_tx_' . $tax_class ]; ?>)</span>
                                                                        </dt>
																		<?php
																		$tax_value = round( ( $count ) * ( $tax_rates[ $order->order_shipping_country ][ 'percent_tx_' . $tax_class ] / 100 ), 2 ); ?>
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
                                                            <dt class="font-medium text-gray-900"><?php _e( 'Shipping', 'wpboutik' ); ?></dt>
                                                            <dd class="text-gray-700"><?php echo wpboutik_format_number( $order->shipping_flat_rate ) . $currency_symbol; ?></dd>
                                                        </div>
													<?php endif; ?>
                                                    <div class="flex justify-between">
                                                        <dt class="font-medium text-gray-900"><?php _e( 'Total', 'wpboutik' ); ?></dt>
                                                        <dd class="text-gray-900"><?php echo wpboutik_format_number( $order->total ) . $currency_symbol; ?></dd>
                                                    </div>
													<?php if ( ! empty( $order->gift_card_payment ) ) : ?>
                                                        <div class="flex justify-between">
                                                            <dt class="font-medium text-gray-900">
																<?php _e( 'Gift Card payment', 'wpboutik' ); ?>
                                                            </dt>
                                                            <dd class="text-gray-900 text-right">
																<?php echo wpboutik_format_number( $order->gift_card_payment[0]->amount_used ) . $currency_symbol; ?>
                                                                <p class="text-xs text-gray-600">
																	<?= __( 'Left to be paid:', 'wpboutik' ) . ' ' . wpboutik_format_number( $order->total - $order->gift_card_payment[0]->amount_used ) . $currency_symbol; ?>
                                                                </p>
                                                            </dd>
                                                        </div>
													<?php endif; ?>
                                                </dl>
                                            </div>
                                        </section>
                                    </div>
								<?php endif; ?>
                            </main>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
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
