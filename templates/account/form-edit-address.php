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
$hovercolor      = wpboutik_get_hovercolor_button();

$page_title = ( 'billing' === $load_address ) ? esc_html__( 'Billing address', 'wpboutik' ) : esc_html__( 'Shipping address', 'wpboutik' );

if ( empty( $load_address ) ) : ?>
	<?php include WPBOUTIK_TEMPLATES . '/account/my-address.php'; ?>
<?php else : ?>

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
                            <?php wpb_form('address', [
                                'load_address' => $load_address,
                                'page_title'   => $page_title,
                                'address'      => $address
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
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
