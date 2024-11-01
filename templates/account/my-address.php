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
$backgroundcolor   = wpboutik_get_backgroundcolor_button();
$hovercolor   = wpboutik_get_hovercolor_button(); ?>

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

								<?php do_action( 'wpboutik_before_account_edit_address' );
									$currency_symbol = get_wpboutik_currency_symbol(); ?>

                                    <div class="mx-auto max-w-7xl sm:px-2 lg:px-8">
                                        <div class="mx-auto mb-4 max-w-2xl px-4 lg:max-w-4xl lg:px-0">
                                            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl"><?php _e('Addresses', 'wpboutik'); ?></h1>
                                            <p class="mt-2 text-sm text-gray-500">Les adresses suivantes seront utilisées par défaut sur la page de commande.</p>
                                        </div>
	                                    <?php
	                                    if ( isset( $_GET['success'] ) ) :
		                                    $message = __( 'Address changed successfully.', 'wpboutik' ); 
                                            wpb_field('success', ['message' => $message]);
	                                    endif; ?>
                                    </div>

                                    <div class="mx-auto max-w-7xl sm:px-2 lg:px-8">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <header>
                                                    <h3 class="float-left font-medium">Adresse de facturation</h3>
                                                    <a href="<?php echo esc_url( wpboutik_get_endpoint_url( 'edit-address', 'billing' ) ); ?>" class="float-right edit">Modifier</a>
                                                </header>
                                                <address class="clear-both">
	                                                <?php echo $address['wpboutik_billing_first_name']['value'] . ' ' . $address['wpboutik_billing_last_name']['value']; ?><br><?php echo $address['wpboutik_billing_address']['value']; ?><br><?php echo $address['wpboutik_billing_postal_code']['value'] . ' ' . $address['wpboutik_billing_city']['value']; ?>		</address>
                                            </div>
                                            <div>
                                                <header>
                                                    <h3 class="float-left font-medium">Adresse de livraison</h3>
                                                    <a href="<?php echo esc_url( wpboutik_get_endpoint_url( 'edit-address', 'shipping' ) ); ?>" class="float-right edit">Modifier</a>
                                                </header>
                                                <address class="clear-both">
	                                                <?php echo $address['wpboutik_shipping_first_name']['value'] . ' ' . $address['wpboutik_shipping_last_name']['value']; ?><br><?php echo $address['wpboutik_shipping_address']['value']; ?><br><?php echo $address['wpboutik_shipping_postal_code']['value'] . ' ' . $address['wpboutik_shipping_city']['value']; ?>		</address>
                                            </div>
                                        </div>
                                    </div>
								<?php do_action( 'wpboutik_after_account_edit_address' ); ?>
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
