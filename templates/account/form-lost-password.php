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
        <div class="mx-auto max-w-screen-xl px-4 pb-6 sm:px-6 lg:px-8 lg:pb-16">
            <div class="divide-y divide-gray-200 lg:divide-y-0 lg:divide-x">

                <div class="divide-y divide-gray-200">
                    <div class="py-6 px-4 sm:p-6 lg:pb-8">

                        <div class="mx-auto max-w-2xl px-4 lg:max-w-4xl lg:px-0">
                            <h1 class="text-2xl font-bold tracking-tight text-center text-gray-900 sm:text-3xl">
								<?php _e( 'Lost your password?' ); ?></h1>
                        </div>
                        <?php if ( isset( $_COOKIE['wpboutik_error_login'] ) ) :
								$wpboutik_error_login = $_COOKIE['wpboutik_error_login']; ?>
								<?php wpb_field('error', ['message' => $wpboutik_error_login]) ?>
							<?php
                        endif; ?>

                        <main>
                            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                                <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
									<?php
                                    if ( isset( $reset_link_sent ) && $reset_link_sent === true ) :

                                        /* Password reset email has been sent. */ ?>
										<?php do_action( 'wpboutik_before_lost_password_confirmation_message' ); ?>

                                        <p><?php echo esc_html( apply_filters( 'wpboutik_lost_password_confirmation_message', esc_html__( 'A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.', 'wpboutik' ) ) ); ?></p>

										<?php
                                        do_action( 'wpboutik_after_lost_password_confirmation_message' );
                                    else :
                                        wpb_form('lost-password');
									endif; ?>
                                </div>
                            </div>
                        </main>
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
