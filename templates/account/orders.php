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

								<?php do_action( 'wpboutik_before_account_orders', $has_orders ); ?>
								<?php if ( $has_orders ) :
									$currency_symbol = get_wpboutik_currency_symbol(); ?>

                                    <div class="mx-auto max-w-7xl sm:px-2 lg:px-8">
                                        <div class="mx-auto max-w-2xl px-4 lg:max-w-4xl lg:px-0">
                                            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl"><?php _e( 'Order history', 'wpboutik' ); ?></h1>
                                            <!--<p class="mt-2 text-sm text-gray-500">Check the status of recent orders,
												manage
												returns, and discover similar products.</p>-->
                                        </div>
                                    </div>

                                    <section aria-labelledby="recent-heading" class="mt-16">
                                        <h2 id="recent-heading" class="sr-only">Recent orders</h2>
                                        <div class="mx-auto max-w-7xl sm:px-2 lg:px-8">
                                            <div class="mx-auto max-w-2xl space-y-8 sm:px-4 lg:max-w-4xl lg:px-0">
												<?php foreach ( $customer_orders as $order ) : ?>
                                                    <div
                                                            class="border-t border-b border-gray-200 bg-white shadow-sm sm:rounded-lg sm:border">
                                                        <h3 class="sr-only"><?php _e( 'Order placed on', 'wpboutik' ); ?>
                                                            <time
                                                                    datetime="<?php echo date( 'Y-m-d', strtotime( $order->created_at ) ); ?>"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->created_at)); ?></time>
                                                        </h3>

                                                        <div
                                                                class="flex items-center border-b border-gray-200 p-4 sm:grid sm:grid-cols-4 gap-4 sm:p-6">
                                                            <dl class="grid flex-1 grid-cols-2 gap-x-6 text-sm sm:col-span-3 sm:grid-cols-3 lg:col-span-2">
                                                                <div>
                                                                    <dt class="font-medium text-gray-900"><?php _e( 'Order number', 'wpboutik' ); ?></dt>
                                                                    <dd class="mt-1 m-0 text-gray-500"><?php echo $order->id; ?></dd>
                                                                </div>
                                                                <div class="hidden sm:block">
                                                                    <dt class="font-medium text-gray-900"><?php _e( 'Date placed', 'wpboutik' ); ?></dt>
                                                                    <dd class="mt-1 m-0 text-gray-500">
                                                                        <time
                                                                                datetime="<?php echo date( 'Y-m-d', strtotime( $order->created_at ) ); ?>"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->created_at)); ?></time>
                                                                    </dd>
                                                                </div>
                                                                <div>
                                                                    <dt class="font-medium text-gray-900"><?php _e( 'Total amount', 'wpboutik' ); ?></dt>
                                                                    <dd class="mt-1 m-0 font-medium text-gray-900"><?php echo esc_html( wpboutik_format_number( $order->total ) . $currency_symbol ); ?></dd>
                                                                </div>
                                                            </dl>

                                                            <div class="relative flex justify-end lg:hidden">
                                                                <div class="flex items-center">
                                                                    <button type="button"
                                                                            class="showbtnorder -m-2 flex items-center p-2 text-gray-400 hover:text-gray-500"
                                                                            data-order_id="<?php echo $order->id; ?>"
                                                                            id="menu-<?php echo $order->id; ?>-button"
                                                                            aria-expanded="false"
                                                                            aria-haspopup="true">
                                                                        <span class="sr-only">Options for order WU88191111</span>
                                                                        <!-- Heroicon name: outline/ellipsis-vertical -->
                                                                        <svg class="h-6 w-6"
                                                                             xmlns="http://www.w3.org/2000/svg"
                                                                             fill="none" viewBox="0 0 24 24"
                                                                             stroke-width="1.5"
                                                                             stroke="currentColor" aria-hidden="true">
                                                                            <path stroke-linecap="round"
                                                                                  stroke-linejoin="round"
                                                                                  d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"/>
                                                                        </svg>
                                                                    </button>
                                                                </div>

                                                                <!--
																  Dropdown menu, show/hide based on menu state.

																  Entering: "transition ease-out duration-100"
																	From: "transform opacity-0 scale-95"
																	To: "transform opacity-100 scale-100"
																  Leaving: "transition ease-in duration-75"
																	From: "transform opacity-100 scale-100"
																	To: "transform opacity-0 scale-95"
																-->
                                                                <div
                                                                        class="hidden btnorder btnorder-<?php echo $order->id; ?> right-0 z-10 mt-2 w-40 origin-bottom-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                                                        role="menu" aria-orientation="vertical"
                                                                        aria-labelledby="menu-<?php echo $order->id; ?>-button"
                                                                        tabindex="-1">
                                                                    <div class="py-1" role="none">
                                                                        <!-- Active: "bg-gray-100 text-gray-900", Not Active: "text-gray-700" -->
                                                                        <a href="<?php echo wpboutik_get_endpoint_url( 'view-order', $order->id, wpboutik_get_page_permalink( 'account' ) ); ?>"
                                                                           class="text-gray-700 block px-4 py-2 text-sm"
                                                                           role="menuitem" tabindex="-1"
                                                                           id="menu-0-item-0"><?php _e( 'View', 'wpboutik' ); ?></a>
																		<?php if ( $order->has_facture == '1' ) : ?>
                                                                            <a href="<?php echo esc_url( $order->facture_link ); ?>"
                                                                               target="_blank"
                                                                               class="text-gray-700 block px-4 py-2 text-sm"
                                                                               role="menuitem" tabindex="-1"
                                                                               id="menu-0-item-1"><?php _e( 'Invoice', 'wpboutik' ); ?></a>
																		<?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div
                                                                    class="hidden lg:col-span-2 lg:flex lg:items-center lg:justify-end lg:space-x-4">
                                                                <a href="<?php echo wpboutik_get_endpoint_url( 'view-order', $order->id, wpboutik_get_page_permalink( 'account' ) ); ?>"
                                                                   class="flex items-center justify-center rounded-md border border-gray-300 bg-white py-2 px-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[var(--backgroundcolor)] focus:ring-offset-2">
                                                                    <span><?php _e( 'View Order', 'wpboutik' ); ?></span>
                                                                    <span class="sr-only">WU88191111</span>
                                                                </a>

																<?php if ( $order->has_facture == '1' ) : ?>
                                                                    <a href="<?php echo ( ! empty( $order->wpb_invoice_link ) ) ? esc_url( $order->wpb_invoice_link ) : esc_url( $order->facture_link ); ?>"
                                                                       target="_blank"
                                                                       class="flex items-center justify-center rounded-md border border-gray-300 bg-white py-2 px-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[var(--backgroundcolor)] focus:ring-offset-2">
                                                                        <span><?php _e( 'View Invoice', 'wpboutik' ); ?></span>
                                                                        <span
                                                                                class="sr-only">for order <?php echo $order->id; ?></span>
                                                                    </a>
																<?php endif; ?>
	                                                            <?php if ( $order->has_refund == '1' ) : ?>
                                                                    <a href="<?php echo ( ! empty( $order->wpb_refund_link ) ) ? esc_url( $order->wpb_refund_link ) : esc_url( $order->refund_link ); ?>"
                                                                       target="_blank"
                                                                       class="flex items-center justify-center rounded-md border border-gray-300 bg-white py-2 px-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[var(--backgroundcolor)] focus:ring-offset-2">
                                                                        <span><?php _e( 'View Credit', 'wpboutik' ); ?></span>
                                                                        <span
                                                                                class="sr-only">for order <?php echo $order->id; ?></span>
                                                                    </a>
	                                                            <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- Products -->
                                                        <h4 class="sr-only">Items</h4>
                                                        <ul role="list" class="list-none divide-y divide-gray-200">
                                                            <li class="px-4 sm:px-6 mb-4">
                                                                <div class="mt-6 sm:flex sm:justify-between">
                                                                    <div class="flex items-center">
                                                                        <!-- Heroicon name: mini/check-circle -->
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

                                                                        <p class="ml-2 m-0 text-sm font-medium text-gray-500">
                                                                            Status
                                                                            : <?php echo $order->status; ?>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </li>
															<?php
															if ( isset( $products->{$order->id} ) ) :
																$products_order = $products->{$order->id};

																foreach ( $products_order as $product ) :
																	//var_dump($product);die;
																	$wp_product = get_post( $product->wp_product_id );
																	if ( empty( $wp_product ) ) {
																		continue;
																	} ?>
                                                                    <li class="p-4 sm:p-6">
                                                                        <div class="flex items-center sm:items-start">
                                                                            <div
                                                                                    class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg sm:h-40 sm:w-40">
                                                                                <a href="<?php echo esc_url( $wp_product->guid ); ?>"
                                                                                   target="_blank">
																					<?php if ( has_post_thumbnail( $product->wp_product_id ) ) :
																						echo get_the_post_thumbnail( $product->wp_product_id, 'post-thumbnail', array(
																							'class' => 'h-full w-full object-contain',
																							'alt'   => esc_html( $product->name )
																						) );
																					else :
																						echo wpb_get_default_image( 'h-full w-full object-contain' );
																					endif; ?>
                                                                                </a>
                                                                            </div>
                                                                            <div class="ml-6 flex-1 text-sm">
                                                                                <div
                                                                                        class="font-medium text-gray-900 sm:flex sm:justify-between">
                                                                                    <h5>
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

                                                                                        $recursive        = get_post_meta( $product->wp_product_id, 'recursive', true );
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
		                                                                                       // $recursive        = get_post_meta( $product->wp_product_id, 'recursive', true );
		                                                                                        $recursive_type   = get_post_meta( $product->wp_product_id, 'recursive_type', true );
		                                                                                        $recursive_number = get_post_meta( $product->wp_product_id, 'recursive_number', true );
	                                                                                        }
	                                                                                        $name .= ' - ' . display_recursivity( $recursive_type, $recursive_number );
                                                                                        }
                                                                                        ?>
                                                                                        <a href="<?php echo esc_url( $wp_product->guid ); ?>"
                                                                                           target="_blank"><?php echo $name; ?></a>
                                                                                    </h5>
                                                                                </div>

                                                                                <div
                                                                                        class="flex flex-1 items-end justify-between pt-2">
                                                                                    <p class="mt-1 text-sm font-medium text-gray-900"><?php echo esc_html( wpboutik_format_number( $product->price_ht ) . $currency_symbol ); ?></p>

                                                                                    <div class="ml-4">
                                                                                        <p class="mt-1 text-sm font-medium text-gray-900"><span
                                                                                                    class="text-gray-500"><?php _e( 'Quantity', 'wpboutik' ); ?></span> <?php echo esc_html( $product->qty ); ?>
                                                                                        </p>
                                                                                        <!--<div class="flex flex-1 justify-center">
																							<a href="<?php echo esc_url( $wp_product->guid ); ?>"
																							   target="_blank"
																							   class="whitespace-nowrap text-[var(--backgroundcolor)] hover:text-[var(--hovercolor)]"
																							   style="--backgroundcolor: <?php echo $backgroundcolor; ?>;--hovercolor: <?php echo $hovercolor; ?>">View product</a>
																						</div>-->
                                                                                    </div>
                                                                                </div>

                                                                                <!--<p class="hidden text-gray-500 sm:mt-2 sm:block">Are you
																					a minimalist looking for a compact carry option? The
																					Micro Backpack is the perfect size for your
																					essential everyday carry items. Wear it like a
																					backpack or carry it like a satchel for all-day
																					use.</p>-->
                                                                            </div>
                                                                        </div>
                                                                    </li>
																<?php endforeach; ?>
															<?php endif; ?>
                                                        </ul>
                                                    </div>
												<?php endforeach; ?>
                                            </div>

                                            <script>
                                                jQuery('.showbtnorder').on('click', function () {
                                                    var order_id = jQuery(this).data('order_id');
                                                    var child = jQuery('.showbtnorder').closest('.relative').find('.btnorder-' + order_id);
                                                    if (child.hasClass('hidden')) {
                                                        child.removeClass('hidden');
                                                        child.addClass('absolute');
                                                    } else {
                                                        child.removeClass('absolute');
                                                        child.addClass('hidden');
                                                    }
                                                });
                                            </script>

                                        </div>
                                    </section>
								<?php else : ?>
                                    <div class="rounded-md bg-blue-50 p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <!-- Heroicon name: mini/information-circle -->
                                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg"
                                                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z"
                                                          clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3 flex-1 md:flex md:justify-between">
                                                <p class="text-sm text-blue-700"><?php esc_html_e( 'No order has been made yet.', 'wpboutik' ); ?></p>
                                                <p class="mt-3 text-sm md:mt-0 md:ml-6">
                                                    <a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ); ?>"
                                                       class="whitespace-nowrap font-medium text-blue-700 hover:text-blue-600">
														<?php esc_html_e( 'Browse products', 'wpboutik' ); ?>
                                                        <span aria-hidden="true"> &rarr;</span>
                                                    </a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
								<?php endif; ?>
								<?php do_action( 'wpboutik_after_account_orders', $has_orders ); ?>
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
