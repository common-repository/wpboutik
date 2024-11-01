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
 ?>
    <main class="relative">
        <div class="mx-auto max-w-screen-xl px-4 pb-6 sm:px-6 lg:px-8 lg:pb-16">
            <div class="divide-y divide-gray-200 lg:divide-y-0 lg:divide-x">

                <div class="divide-y divide-gray-200">
                    <div class="py-6 px-4 sm:p-6 lg:pb-8">

                        <div class="mx-auto max-w-2xl px-4 lg:max-w-4xl lg:px-0">
                            <h1 class="text-2xl font-bold tracking-tight text-center text-gray-900 sm:text-3xl">
								<?php _e( 'Reset password' ); ?></h1>
                        </div>

						<?php
						if ( isset( $_SESSION['wpboutik_error_reset_pass'] ) ) :
                            foreach ($_SESSION['wpboutik_error_reset_pass'] as $wpboutik_error_login) : ?>
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
                                            <h3 class="text-sm font-medium text-red-800"><?php echo $wpboutik_error_login; ?></h3>
                                        </div>
                                    </div>
                                </div>
                            <?php
                            endforeach;
                            unset($_SESSION['wpboutik_error_reset_pass']);
                            endif;
                        ?>

                        <main>
                            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                                <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
									<?php do_action( 'wpboutik_before_reset_password_form' ); ?>

                                    <form method="post" class="space-y-6 lost_reset_password">

                                        <p><?php echo apply_filters( 'wpboutik_reset_password_message', esc_html__( 'Enter a new password below.', 'wpboutik' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>

                                        <p class="mt-1">
                                            <label for="password_1"><?php esc_html_e( 'New password', 'wpboutik' ); ?>
                                                &nbsp;<span class="required">*</span></label>
                                            <input type="password"
                                            required class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-[var(--backgroundcolor)] focus:outline-none focus:ring-[var(--backgroundcolor)] sm:text-sm" style="--backgroundcolor: <?php echo $backgroundcolor; ?>"                                                   name="password_1" id="password_1" autocomplete="new-password"/>
                                        </p>
                                        <p class="mt-1">
                                            <label for="password_2"><?php esc_html_e( 'Re-enter new password', 'wpboutik' ); ?>
                                                &nbsp;<span class="required">*</span></label>
                                            <input type="password"
                                            required class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-[var(--backgroundcolor)] focus:outline-none focus:ring-[var(--backgroundcolor)] sm:text-sm" style="--backgroundcolor: <?php echo $backgroundcolor; ?>"                                                   name="password_2" id="password_2" autocomplete="new-password"/>
                                        </p>

                                        <input type="hidden" name="reset_key"
                                               value="<?php echo esc_attr( $key ); ?>"/>
                                        <input type="hidden" name="reset_login"
                                               value="<?php echo esc_attr( $login ); ?>"/>

                                        <div class="clear"></div>

										<?php do_action( 'wpboutik_resetpassword_form' ); ?>

                                        <p class="mt-1 form-row">
                                            <input type="hidden" name="wpb_reset_password" value="true"/>
                                            <button type="submit"
                                            class="flex w-full justify-center rounded-md border border-transparent bg-[var(--backgroundcolor)] py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-[var(--hovercolor)] focus:outline-none focus:ring-2 focus:ring-[var(--backgroundcolor)] focus:ring-offset-2" style="--backgroundcolor: <?php echo $backgroundcolor; ?>;--hovercolor: <?php echo $hovercolor; ?>"
                                            value="<?php esc_attr_e( 'Save', 'wpboutik' ); ?>"><?php esc_html_e( 'Save', 'wpboutik' ); ?></button>
                                        </p>

										<?php wp_nonce_field( 'reset_password', 'wpboutik-reset-password-nonce' ); ?>

                                    </form>
									<?php do_action( 'wpboutik_after_reset_password_form' ); ?>
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


