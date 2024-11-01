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


							<div class="mx-auto max-w-7xl sm:px-2 lg:px-8">
								<div class="mx-auto max-w-2xl px-4 lg:max-w-4xl lg:px-0">
									<h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl"><?php esc_html_e( 'Account details', 'wpboutik' ); ?></h1>
									<?php
									if ( isset( $_COOKIE['wpboutik_error_account_details'] ) ) :
										$errors = json_decode( stripslashes( $_COOKIE['wpboutik_error_account_details'] ) ); ?>
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
                                                    <h3 class="text-sm font-medium text-red-800">There
                                                        were <?php echo count( $errors ); ?> errors with your
                                                        submission</h3>
                                                    <div class="mt-2 text-sm text-red-700">
                                                        <ul role="list" class="list-disc space-y-1 pl-5 m-0">
															<?php
															foreach ( $errors as $error ) :
																echo '<li>' . $error . '</li>';
															endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
									<?php
									endif;
									if ( isset( $_GET['success'] ) ) :
										$message = __( 'Account details changed successfully.', 'wpboutik' ); ?>
                                        <div class="rounded-md bg-green-50 p-4 mb-2">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <div class="ml-3 flex-1 md:flex md:justify-between">
                                                    <p class="text-sm text-green-700 m-0"><?php echo $message; ?></p>
                                                </div>
                                            </div>
                                        </div>
									<?php
									endif; ?>
								</div>

							    <form method="POST">

								<?php do_action( 'wpboutik_edit_account_form_start' ); ?>

                                <div class="grid grid-cols-6 gap-6">
                                    <div class="col-span-6 sm:col-span-3"><label
                                                for="account_first_name"
                                                class="block text-sm font-medium text-gray-700"><?php esc_html_e( 'First name', 'wpboutik' ); ?> <span class="required">*</span></label>
                                        <div class="mt-1"><input id="account_first_name"
                                                                 name="account_first_name"
                                                                 type="text"
                                                                 class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                 aria-describedby="account_first_name"
                                                                 autocomplete="given-name"
                                                                 value="<?php echo esc_attr( $user->first_name ); ?>">
                                        </div>
                                    </div>

                                    <div class="col-span-6 sm:col-span-3"><label
                                                for="account_last_name"
                                                class="block text-sm font-medium text-gray-700"><?php esc_html_e( 'Last name', 'wpboutik' ); ?> <span class="required">*</span></label>
                                        <div class="mt-1"><input id="account_last_name"
                                                                 name="account_last_name"
                                                                 type="text"
                                                                 class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                 aria-describedby="account_last_name"
                                                                 autocomplete="family-name"
                                                                 value="<?php echo esc_attr( $user->last_name ); ?>">
                                        </div>
                                    </div>

                                    <div class="col-span-6 sm:col-span-3"><label
                                                for="account_display_name"
                                                class="block text-sm font-medium text-gray-700"><?php esc_html_e( 'Display name', 'wpboutik' ); ?> <span class="required">*</span></label>
                                        <div class="mt-1"><input id="account_display_name"
                                                                 name="account_display_name"
                                                                 type="text"
                                                                 class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                 aria-describedby="account_display_name"
                                                                 value="<?php echo esc_attr( $user->display_name ); ?>" />
                                            <span class="text-xs"><em><?php esc_html_e( 'This will be how your name will be displayed in the account section and in reviews', 'wpboutik' ); ?></em></span>
                                        </div>
                                    </div>

                                    <div class="col-span-6 sm:col-span-3"><label
                                                for="account_email"
                                                class="block text-sm font-medium text-gray-700"><?php esc_html_e( 'Email address', 'wpboutik' ); ?> <span class="required">*</span></label>
                                        <div class="mt-1"><input id="account_email"
                                                                 name="account_email"
                                                                 type="text"
                                                                 class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                 aria-describedby="account_email"
                                                                 autocomplete="email"
                                                                 value="<?php echo esc_attr( $user->user_email ); ?>" />
                                        </div>
                                    </div>
                                </div>

								<fieldset class="mt-4">
									<legend><?php esc_html_e( 'Password change', 'wpboutik' ); ?></legend>

									<p class="clear-both">
										<label for="password_current" class="block text-sm font-medium text-gray-700"><?php esc_html_e( 'Current password (leave blank to leave unchanged)', 'wpboutik' ); ?></label>
										<input type="password" class="w-full box-border" name="password_current" id="password_current" autocomplete="off" />
									</p>
                                    <p class="clear-both">
										<label for="password_1" class="block text-sm font-medium text-gray-700"><?php esc_html_e( 'New password (leave blank to leave unchanged)', 'wpboutik' ); ?></label>
										<input type="password" class="w-full box-border" name="password_1" id="password_1" autocomplete="off" />
									</p>
                                    <p class="clear-both">
										<label for="password_2" class="block text-sm font-medium text-gray-700"><?php esc_html_e( 'Confirm new password', 'wpboutik' ); ?></label>
										<input type="password" class="w-full box-border" name="password_2" id="password_2" autocomplete="off" />
									</p>
								</fieldset>
								<div class="clear"></div>

								<?php do_action( 'wpboutik_edit_account_form' ); ?>

                                <button type="submit" class="inline-flex mt-4 justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        name="save_account_details"
                                        value="<?php esc_attr_e( 'Save changes', 'wpboutik' ); ?>"><?php esc_html_e( 'Save changes', 'wpboutik' ); ?></button>
                                <?php wp_nonce_field( 'wpboutik_save_account_details', 'wpboutik-save-account-details-nonce' ); ?>
                                <input type="hidden" name="action" value="save_account_details" />

								<?php do_action( 'wpboutik_edit_account_form_end' ); ?>
							</form>
							</div>
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
