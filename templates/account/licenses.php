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
                                <table id="licenses-user-table">
                                    <tr>
                                        <th scope="col">
											<?= __( 'Product', 'wpboutik' ) ?>
                                        </th>
                                        <th scope="col">
											<?= __( 'Code', 'wpboutik' ) ?>
                                        </th>
                                        <th scope="col">
											<?= __( 'Limit date', 'wpboutik' ) ?>
                                        </th>
                                        <th scope="col">
											<?= __( 'Installations', 'wpboutik' ) ?>
                                        </th>
                                        <th scope="col">
											<?= __( 'automatic renewal', 'wpboutik' ) ?>
                                        </th>
                                    </tr>
									<?php
									foreach ( $licenses as $license ) :
										$pid = $license['product'];
										$unlimited = empty( $license['limit_code'] );
										$delay = '';
										if ( ! empty( $license['variant_id'] ) ) {
											$variants  = get_post_meta( $license['product'], 'variants', true );
											$variation = wpboutik_get_variation_by_id( json_decode( $variants ), $license['variant_id'] );
											$price     = $variation->price;
											if ( ! empty( $variation->recursive ) && $variation->recursive == 1 ) {
												$delay = display_recursivity( $variation->recursive_type, $variation->recursive_number );
											}
										} else {
											$price = get_post_meta( $pid, 'price', true );
											if ( get_post_meta( $pid, 'recursive', true ) == 1 ) {
												$delay = display_recursivity( get_post_meta( $pid, 'recursive_type', true ), get_post_meta( $pid, 'recursive_number', true ) );
											}
										} ?>
                                        <tr>
                                            <td>
                                                <a target="_blank"
                                                   href="<?= get_the_permalink( $pid ) ?>"><?= get_the_title( $pid ) ?></a>
                                                <br/>
                                                <small>
													<?= $price . get_wpboutik_currency_symbol() . ' ' . $delay ?>
                                                </small>
                                            </td>
                                            <td>
                                                <code><?= $license['code'] ?></code>
                                            </td>
                                            <td>
												<?php
												if ( ! $unlimited ) {
													$timestamp = strtotime( $license['limit_code'] );
													// Récupérer le format de date défini dans l'administration de WordPress
													$date_format = get_option( 'date_format' );
													// Afficher la date au format défini
													print date_i18n( $date_format, $timestamp );
												} else {
													print __( 'Unlimited', 'wpboutik' );
												}
												?>
                                            </td>
                                            <td>
												<?php if ( $license['limit_url'] == 0 ) : ?>
													<?= __( 'Unlimited', 'wpboutik' ) ?>
												<?php else : ?>
													<?= sizeof( $license['urls'] ) ?> / <?= $license['limit_url'] ?>
													<?php if ( sizeof( $license['urls'] ) > 0 ) : ?>
                                                        <br/>
                                                        <a href="#" data-urls='<?= json_encode( $license['urls'] ) ?>'
                                                           data-license="<?= $license['id'] ?>"
                                                           class="wpb-link wpb-manage-urls">
															<?= __( 'Manage urls', 'wpboutik' ) ?>
                                                        </a>
													<?php endif; ?>
												<?php endif; ?>
                                            </td>
                                            <td>
												<?php if ( ! $unlimited ) : ?>
													<?= ( ! empty( $license['auto_renew'] ) ) ? __( 'Yes' ) : __( 'No' ) ?>
                                                    <br/>
													<?= ( ! empty( $license['auto_renew'] ) ) ? '<a href="#" class="wpb-link wpb-resiliation" data-subscription="' . $license['id'] . '">' . __( 'Résilier' ) . '</a>' : '<a href="#" data-product="' . $license['product'] . '" data-variant="' . $license['variant_id'] . '" data-code="' . $license['code'] . '" class="wpb-link wpb-renew">' . __( 'Renouveler' ) . '</a>' ?>
												<?php else : ?>
                                                    -
												<?php endif; ?>
                                            </td>
                                        </tr>
									<?php endforeach; ?>
                                </table>
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
