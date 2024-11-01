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
							<?php
							if ( $has_products_virtual ) {
								foreach ( $products as $product ) :
									$files = get_post_meta( $product->wp_product_id, 'files', true );
									if ( $files ) :
										$files = json_decode( $files );
										//echo 'Liste des fichiers téléchargable :<br>';
										?>
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
                                                           download="<?php echo $file->name; ?>"><?php _e('Download', 'wpboutik' ); ?></a>
                                                    </div>
                                                </li>
											<?php endforeach; ?>
                                        </ul>
									<?php
									endif;
								endforeach;
							} else {
								_e( 'You have no downloads.', 'wpboutik' );
							}
							?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
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
