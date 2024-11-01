<?php

use NF\WPBOUTIK\WPB_Gift_Card;

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

$backgroundcolor              = wpboutik_get_backgroundcolor_button();
$hovercolor                   = wpboutik_get_hovercolor_button();
$title_product_color          = wpboutik_get_title_product_color();
$title_product_color_on_hover = wpboutik_get_title_product_color_on_hover();
$payment_type                 = wpboutik_get_option_params( 'payment_type' );
$monetico_cle_mac             = wpboutik_get_option_params( 'monetico_cle_mac' );
$mollie_api_key_test          = wpboutik_get_option_params( 'mollie_api_key_test' );
$mollie_api_key_live          = wpboutik_get_option_params( 'mollie_api_key_live' );
$paybox_id                    = wpboutik_get_option_params( 'paybox_id' );
$stripe_public_key            = wpboutik_get_option_params( 'stripe_public_key' );
$stripe_secret_key            = wpboutik_get_option_params( 'stripe_secret_key' );
$paypal_email                 = wpboutik_get_option_params( 'paypal_email' );
$currency_symbol              = get_wpboutik_currency_symbol();
$countries                    = get_wpboutik_countries();
$activate_tax                 = wpboutik_get_option_params( 'activate_tax' );
$activate_eu_vat              = wpboutik_get_option_params( 'activate_eu_vat' ); ?>

    <div class="wpb-container">
        <h2 class="sr-only"><?php _e( 'Checkout', 'wpboutik' ); ?></h2>

		<?php
		WPB_Gift_Card::display_error();
		if ( ! is_user_logged_in() ) : ?>
            <div class="mb-2 flex items-center">
                <input id="already_customer" name="already_customer" type="checkbox"
                       class="m-0 h-4 w-4 rounded border-gray-300 text-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)]"
                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                <label for="already_customer" class="m-0 pl-2 text-sm font-medium text-gray-900">
                    Déjà client ? Connectez vous
                </label>
            </div>

            <div class="mb-6 hidden" id="formlogin_checkout">
                <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                    <form class="space-y-6" action="#" method="POST">
                        <div>
                            <label for="email"
                                   class="block text-sm font-medium text-gray-700"><?php _e( 'Username or Email Address' ); ?>
                                <span class="text-red-600">*</span></label>
                            <div class="mt-1">
                                <input id="user_login" name="log" type="text" required
                                       class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-[var(--backgroundcolor)] focus:outline-none focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                            </div>
                        </div>

                        <div>
                            <label for="password"
                                   class="block text-sm font-medium text-gray-700"><?php _e( 'Password' ); ?></label>
                            <div class="mt-1">
                                <input id="user_pass" name="pwd" type="password" autocomplete="current-password"
                                       required
                                       class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-[var(--backgroundcolor)] focus:outline-none focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="rememberme" name="rememberme" type="checkbox"
                                       class="m-0 h-4 w-4 rounded border-gray-300 text-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)]"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                                <label for="rememberme"
                                       class="m-0 ml-2 block text-sm text-gray-900"><?php esc_html_e( 'Remember Me' ); ?></label>
                            </div>

                            <div class="text-sm">
                                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"
                                   class="wpb-link"><?php _e( 'Lost your password?' ); ?></a>
                            </div>
                        </div>

                        <div>
							<?php wp_nonce_field( 'wpboutik-login', 'wpboutik-login-nonce' ); ?>
                            <input type="hidden" name="redirect"
                                   value="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'checkout' ) ) ); ?>"/>
                            <button type="submit"
                                    class="flex w-full justify-center rounded-md border border-transparent bg-[var(--backgroundcolor)] py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-[var(--hovercolor)] focus:outline-none focus:ring-2 focus:ring-[var(--backgroundcolor)] focus:ring-offset-2"
                                    style="--backgroundcolor: <?php echo $backgroundcolor; ?>;--hovercolor: <?php echo $hovercolor; ?>">
								<?php _e( 'Sign in', 'wpboutik' ); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
		<?php endif; ?>
        <div id="payment-error" class="hidden mb-5">
            <h2 id="payment-error-message"
                class="text-lg text-red-600"><?php _e( 'Attention, le paiement n’a pas fonctionné, veuillez réessayer', 'wpboutik' ); ?></h2>
        </div>

        <form id="checkout_form" class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
            <div>
				<?php if ( is_user_logged_in() ) :
					$current_user = wp_get_current_user();
					$user_meta = get_user_meta( $current_user->ID ); ?>
                    <input type="hidden" id="email_address" name="email_address"
                           value="<?php echo $current_user->user_email; ?>"/>
                    <span class="email_addressError text-red-500 text-xs hidden">error</span>
				<?php else : ?>
                    <div class="mb-4">
                        <h2 class="text-lg font-medium text-gray-900"><?php _e( 'Contact information', 'wpboutik' ); ?></h2>

                        <div class="mt-4">
                            <label for="email_address"
                                   class="block text-sm font-medium text-gray-700"><?php _e( 'Email address', 'wpboutik' ); ?>
                                *</label>
                            <div class="mt-1">
                                <input type="email" id="email_address" name="email_address" autocomplete="email"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                                <span class="email_addressError text-red-500 text-xs hidden">error</span>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>

                <div class="">
                    <h2 class="text-lg font-medium text-gray-900"><?php _e( 'Shipping information', 'wpboutik' ); ?></h2>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="shipping_first_name"
                                   class="block text-sm font-medium text-gray-700"><?php _e( 'First name', 'wpboutik' ); ?>
                                <span class="text-red-600">*</span></label>
                            <div class="mt-1">
                                <input type="text" id="shipping_first_name" name="shipping_first_name"
                                       autocomplete="given-name"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                       value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_shipping_first_name'] ) ) ? reset( $user_meta['wpboutik_shipping_first_name'] ) : ''; ?>">

                                <span class="shipping_first_nameError text-red-500 text-xs hidden">error</span>
                            </div>
                        </div>

                        <div>
                            <label for="shipping_last_name"
                                   class="block text-sm font-medium text-gray-700"><?php _e( 'Last name', 'wpboutik' ); ?>
                                <span class="text-red-600">*</span></label>
                            <div class="mt-1">
                                <input type="text" id="shipping_last_name" name="shipping_last_name"
                                       autocomplete="family-name"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                       value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_shipping_last_name'] ) ) ? reset( $user_meta['wpboutik_shipping_last_name'] ) : ''; ?>">
                                <span class="shipping_last_nameError text-red-500 text-xs hidden">error</span>
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="shipping_company"
                                   class="block text-sm font-medium text-gray-700"><?php _e( 'Company', 'wpboutik' ); ?></label>
                            <div class="mt-1">
                                <input type="text" name="shipping_company" id="shipping_company"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                       value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_shipping_company'] ) ) ? reset( $user_meta['wpboutik_shipping_company'] ) : ''; ?>">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="shipping_address"
                                   class="block text-sm font-medium text-gray-700"><?php _e( 'Address', 'wpboutik' ); ?>
                                <span class="text-red-600">*</span></label>
                            <div class="mt-1">
                                <input type="text" name="shipping_address" id="shipping_address"
                                       autocomplete="street-address"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                       value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_shipping_address'] ) ) ? reset( $user_meta['wpboutik_shipping_address'] ) : ''; ?>">
                                <span class="shipping_addressError text-red-500 text-xs hidden">error</span>
                            </div>
                        </div>

                        <div>
                            <label for="shipping_city"
                                   class="block text-sm font-medium text-gray-700"><?php _e( 'City', 'wpboutik' ); ?>
                                <span class="text-red-600">*</span></label>
                            <div class="mt-1">
                                <input type="text" name="shipping_city" id="shipping_city"
                                       autocomplete="address-level2"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                       value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_shipping_city'] ) ) ? reset( $user_meta['wpboutik_shipping_city'] ) : ''; ?>">
                                <span class="shipping_cityError text-red-500 text-xs hidden">error</span>
                            </div>
                        </div>
                        <div>
                            <label for="shipping_country"
                                   class="block text-sm font-medium text-gray-700"><?php _e( 'Country', 'wpboutik' ); ?>
                                <span class="text-red-600">*</span></label>
                            <div class="mt-1">
                                <select id="shipping_country" name="shipping_country" autocomplete="country-name"
                                        data-nonce="<?php echo wp_create_nonce( 'modify-tax-rate-nonce' ); ?>"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                        style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
									<?php
									foreach ( $countries as $key => $country ) :
										$selected = '';
										if ( ! isset( $user_meta ) && $key == 'FR' ) {
											$selected = 'selected';
										} elseif ( ( isset( $user_meta ) && isset( $user_meta['wpboutik_shipping_country'] ) ) && $key === reset( $user_meta['wpboutik_shipping_country'] ) ) {
											$selected = 'selected';
										} ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $country ); ?></option>
									<?php endforeach; ?>
                                </select>
                                <span class="shipping_countryError text-red-500 text-xs hidden">error</span>
                            </div>
                        </div>

                        <div>
                            <label for="shipping_postal_code"
                                   class="block text-sm font-medium text-gray-700"><?php _e( 'Postal code', 'wpboutik' ); ?>
                                <span class="text-red-600">*</span></label>
                            <div class="mt-1">
                                <input type="text" name="shipping_postal_code" id="shipping_postal_code"
                                       autocomplete="<?php _e( 'Postal code', 'wpboutik' ); ?>"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                       value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_shipping_postal_code'] ) ) ? reset( $user_meta['wpboutik_shipping_postal_code'] ) : ''; ?>">
                                <span class="shipping_postal_codeError text-red-500 text-xs hidden">error</span>
                            </div>
                        </div>

                        <div>
                            <label for="shipping_phone"
                                   class="block text-sm font-medium text-gray-700"><?php _e( 'Phone', 'wpboutik' ); ?>
                                <span class="text-red-600">*</span></label>
                            <div class="mt-1">
                                <input type="text" name="shipping_phone" id="shipping_phone"
                                       autocomplete="<?php _e( 'Phone', 'wpboutik' ); ?>"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                       value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_shipping_phone'] ) ) ? reset( $user_meta['wpboutik_shipping_phone'] ) : ''; ?>">
                                <span class="shipping_phoneError text-red-500 text-xs hidden">error</span>
                            </div>
                        </div>
                    </div>

                    <section aria-labelledby="billing-heading" class="mt-6">
                        <h2 id="billing-heading" class="text-lg font-medium text-gray-900">
							<?php _e( 'Billing information', 'wpboutik' ); ?>
                        </h2>

                        <div class="mt-6 flex items-center">
                            <input
                                    id="same-as-shipping"
                                    name="same-as-shipping"
                                    type="checkbox"
                                    checked
                                    class="m-0 h-4 w-4 rounded border-gray-300 text-[var(--backgroundcolor)] accent-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)]"
                                    style="--backgroundcolor: <?php echo $backgroundcolor; ?>;--hovercolor: <?php echo $hovercolor; ?>"
                            />
                            <div class="ml-2">
                                <label for="same-as-shipping" class="text-sm font-medium text-gray-900">
									<?php _e( 'Same as shipping information', 'wpboutik' ); ?>
                                </label>
                            </div>
                        </div>

                        <div id="billingform" class="mt-4 grid grid-cols-1 gap-y-6 sm:grid-cols-2 gap-5 hidden">
                            <div>
                                <label for="billing_first_name"
                                       class="block text-sm font-medium text-gray-700"><?php _e( 'First name', 'wpboutik' ); ?></label>
                                <div class="mt-1">
                                    <input type="text" id="billing_first_name" name="billing_first_name"
                                           autocomplete="given-name"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                           style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                           value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_billing_first_name'] ) ) ? reset( $user_meta['wpboutik_billing_first_name'] ) : ''; ?>">
                                </div>
                            </div>

                            <div>
                                <label for="billing_last_name"
                                       class="block text-sm font-medium text-gray-700"><?php _e( 'Last name', 'wpboutik' ); ?></label>
                                <div class="mt-1">
                                    <input type="text" id="billing_last_name" name="billing_last_name"
                                           autocomplete="family-name"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                           style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                           value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_billing_last_name'] ) ) ? reset( $user_meta['wpboutik_billing_last_name'] ) : ''; ?>">
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="billing_company"
                                       class="block text-sm font-medium text-gray-700"><?php _e( 'Company', 'wpboutik' ); ?></label>
                                <div class="mt-1">
                                    <input type="text" name="billing_company" id="billing_company"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                           style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                           value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_billing_company'] ) ) ? reset( $user_meta['wpboutik_billing_company'] ) : ''; ?>">
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="billing_address"
                                       class="block text-sm font-medium text-gray-700"><?php _e( 'Address', 'wpboutik' ); ?></label>
                                <div class="mt-1">
                                    <input type="text" name="billing_address" id="billing_address"
                                           autocomplete="street-address"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                           style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                           value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_billing_address'] ) ) ? reset( $user_meta['wpboutik_billing_address'] ) : ''; ?>">
                                </div>
                            </div>

                            <div>
                                <label for="billing_city"
                                       class="block text-sm font-medium text-gray-700"><?php _e( 'City', 'wpboutik' ); ?></label>
                                <div class="mt-1">
                                    <input type="text" name="billing_city" id="billing_city"
                                           autocomplete="address-level2"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                           style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                           value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_billing_city'] ) ) ? reset( $user_meta['wpboutik_billing_city'] ) : ''; ?>">
                                </div>
                            </div>

                            <div>
                                <label for="billing_country"
                                       class="block text-sm font-medium text-gray-700"><?php _e( 'Country', 'wpboutik' ); ?></label>
                                <div class="mt-1">
                                    <select id="billing_country" name="billing_country" autocomplete="country-name"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                            style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
										<?php
										foreach ( $countries as $key => $country ) :
											$selected = '';
											if ( ( isset( $user_meta ) && isset( $user_meta['wpboutik_billing_country'] ) ) && $key === reset( $user_meta['wpboutik_billing_country'] ) ) {
												$selected = 'selected';
											} ?>
                                            <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $country ); ?></option>
										<?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="billing_postal_code"
                                       class="block text-sm font-medium text-gray-700"><?php _e( 'Postal code', 'wpboutik' ); ?></label>
                                <div class="mt-1">
                                    <input type="text" name="billing_postal_code" id="billing_postal_code"
                                           autocomplete="<?php _e( 'Postal code', 'wpboutik' ); ?>"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                           style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                           value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_billing_postal_code'] ) ) ? reset( $user_meta['wpboutik_billing_postal_code'] ) : ''; ?>">
                                </div>
                            </div>

                            <div>
                                <label for="billing_phone"
                                       class="block text-sm font-medium text-gray-700"><?php _e( 'Phone', 'wpboutik' ); ?></label>
                                <div class="mt-1">
                                    <input type="text" name="billing_phone" id="billing_phone"
                                           autocomplete="<?php _e( 'Phone', 'wpboutik' ); ?>"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                           style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                           value="<?php echo ( isset( $user_meta ) && isset( $user_meta['wpboutik_billing_phone'] ) ) ? reset( $user_meta['wpboutik_billing_phone'] ) : ''; ?>">
                                </div>
                            </div>
                        </div>
                    </section>

					<?php if ( $activate_eu_vat ) :
						$input_name_eu_vat = wpboutik_get_option_params( 'input_name_eu_vat' );
						$input_desc_eu_vat = wpboutik_get_option_params( 'input_desc_eu_vat' ); ?>
                        <section class="mt-6">
                            <h2 class="text-lg font-medium text-gray-900">
								<?php echo esc_html( $input_name_eu_vat ); ?>
                            </h2>
                            <div class="mt-4">
                                <input type="text" name="tva_intra" id="tva_intra"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                       placeholder="<?php echo esc_attr( $input_desc_eu_vat ); ?>"
                            </div>
                        </section>
					<?php endif; ?>
                    <section class="mt-6">
                        <h2 class="text-lg font-medium text-gray-900">
							<?php _e( 'Order comments', 'wpboutik' ); ?>
                        </h2>
                        <div class="mt-4">
                            <textarea rows="4" name="order_comments" id="order_comments"
                                      placeholder="<?php _e( 'Comments about your order, eg. : delivery instructions.', 'wpboutik' ); ?>"
                                      class="block w-full p-4 rounded-md border-gray-300 shadow-sm focus:border-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                      style="--backgroundcolor: <?php echo $backgroundcolor; ?>"></textarea>
                        </div>
                    </section>
					<?php
					$method_list = wpboutik_get_options_shipping_method_list();
					if ( $method_list && WPB()->cart->has_delivery_need() ) : ?>

                        <div class="mt-10" id="deliversmethod">
                            <fieldset class="border-none mx-0">
                                <h2 class="text-lg font-medium text-gray-900"><?php _e( 'Delivery method', 'wpboutik' ); ?></h2>
                                <span class="delivery_methodError text-red-500 text-xs hidden">error</span>
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
									<?php
									$i          = 1;
									foreach ( $method_list as $method_name ) :
										$method_id = str_replace( 'wpboutik_options_shipping_method_', '', $method_name );
										$method = get_option( $method_name );?>
                                        <label class="<?php echo ( $i == 1 ) ? 'deliverymethod relative  cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none border-transparent ring-2 ring-[var(--backgroundcolor)]' : 'deliverymethod relative cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none border-gray-300'; ?>"
                                               style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                                            <input type="radio" name="delivery-method"
                                                   data-nonce="<?php echo wp_create_nonce( 'update-delivery-method-nonce' ); ?>"
                                                   value=<?php echo esc_attr( $method_id ); ?> class="sr-only"
												<?php echo ( $i == 1 ) ? 'checked' : ''; ?>
                                                   aria-labelledby="delivery-method-<?php echo esc_attr( $method_id ); ?>-label"
                                                   aria-describedby="delivery-method-<?php echo esc_attr( $method_id ); ?>-description">
                                            <span class="flex flex-1">
                                          <span class="flex flex-col">
                                            <span id="delivery-method-<?php echo esc_attr( $method_id ); ?>-method"
                                                  class="block text-sm font-medium text-gray-900"><?php echo esc_html( $method['method'] ); ?></span>
                                            <span id="delivery-method-<?php echo esc_attr( $method_id ); ?>-description"
                                                  class="mt-1 flex items-center text-sm text-gray-500"><?php echo esc_html( $method['description'] ); ?></span>
                                              <div id="delivery-method-<?php echo esc_attr( $method_id ); ?>-boxtal"></div>
                                            <span id="delivery-method-<?php echo esc_attr( $method_id ); ?>-flat-rate"
                                                  class="mt-6 text-sm font-medium text-gray-900">
                                                  <?php
                                                    $price = get_evenly_reduced_shipping($method, $method['flat_rate']);
                                                    echo esc_html( wpboutik_format_number( $price ) . $currency_symbol ); 
                                                  ?></span>
                                              <input type="hidden"
                                                     name="flat_rate_<?php echo esc_attr( $method_id ); ?>"
                                                     value="<?php echo esc_attr( get_evenly_reduced_shipping($method, $method['flat_rate']) ); ?>">
                                          </span>
                                        </span>
                                            <div class="<?php echo ( $i == 1 ) ? 'show' : 'hidden'; ?> icon">
                                                <svg class="h-5 w-5 text-[var(--backgroundcolor)]"
                                                     style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                                     xmlns="http://www.w3.org/2000/svg"
                                                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                                          clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <span class="span pointer-events-none absolute -inset-px rounded-lg <?php echo ( $i == 1 ) ? 'border border-[var(--backgroundcolor)]' : 'border-transparent'; ?>"
                                                  style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
                                                  aria-hidden="true"></span>
                                        </label>
										<?php
										$i ++;
									endforeach; ?>

                                </div>
                            </fieldset>
                        </div>

					<?php
					endif; ?>

                    <!-- Payment -->
                    <div class="mt-10">
                        <h2 class="text-lg font-medium text-gray-900"><?php _e( 'Payment', 'wpboutik' ); ?></h2>

                        <fieldset class="mt-4 border-none mx-0">
                            <h2 class="sr-only"><?php _e( 'Payment type', 'wpboutik' ); ?></h2>
                            <div class="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">

								<?php
								if ( $payment_type && ! empty( $payment_type ) ) :
									if ( is_string( $payment_type ) ) {
										$payment_type = json_decode( $payment_type );
									}
									$i                    = 1;
									$allowed_payment_type = WPB()->cart->allowed_payments();
									foreach ( $payment_type as $payment ) :
										$payment = (array) $payment;
										$show             = true;
										if ( $payment['value'] === 'card' ) {
											if ( empty( $stripe_public_key ) || empty( $stripe_secret_key ) ) {
												$show = false;
											}
											$label = __( 'Credit card', 'wpboutik' );
										} elseif ( $payment['value'] === 'mollie' ) {
											if ( empty( $mollie_api_key_test ) && empty( $mollie_api_key_live ) ) {
												$show = false;
											}
											$label = __( 'Credit card', 'wpboutik' );
										} elseif ( $payment['value'] === 'monetico' ) {
											if ( empty( $monetico_cle_mac ) ) {
												$show = false;
											}
											$label = __( 'Credit card', 'wpboutik' );
										} elseif ( $payment['value'] === 'paybox' ) {
											if ( empty( $paybox_id ) ) {
												$show = false;
											}
											$label = __( 'Credit card', 'wpboutik' );
										} elseif ( $payment['value'] === 'paypal' ) {
											$label = __( 'PayPal', 'wpboutik' );
										} elseif ( $payment['value'] === 'bacs' ) {
											$label = __( 'Bacs / Check', 'wpboutik' );
										}
										if ( ! in_array( $payment['value'], $allowed_payment_type ) ) {
											$show = false;
										}
										$first = true;
										if ( true === $show ) :?>
                                            <div class="flex items-center">
                                                <input id="<?php echo esc_attr( $payment['value'] ); ?>"
                                                       value="<?php echo esc_attr( $payment['value'] ); ?>"
                                                       name="payment-type"
													<?php echo ( $first === true ) ? 'checked' : ''; ?>
                                                       data-nonce="<?php echo wp_create_nonce( 'change-payment-type-nonce' ); ?>"
                                                       type="radio" <?php echo ( $i === 1 ) ? 'checked' : ''; ?>
                                                       class="h-4 w-4 border-gray-300 text-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] accent-[var(--backgroundcolor)]"
                                                       style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                                                <label for="<?php echo esc_attr( $payment['value'] ); ?>"
                                                       class="m-0 ml-3 block text-sm font-medium text-gray-700"><?php echo esc_html( $label ); ?></label>
                                            </div>
											<?php
											$first = false;
										endif;
										$i ++;
									endforeach;
								else : ?>
                                    <div class="flex items-center">
                                        <input id="bacs" name="payment-type" type="radio" checked
                                               class="h-4 w-4 border-gray-300 text-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)] accent-[var(--backgroundcolor)]"
                                               style="--backgroundcolor: <?php echo $backgroundcolor; ?>;">
                                        <label for="bacs"
                                               class="ml-3 block text-sm font-medium text-gray-700"><?php _e( 'Bacs / Check', 'wpboutik' ); ?></label>
                                    </div>
								<?php
								endif; ?>
                            </div>
                        </fieldset>

						<?php if ( ! empty( $stripe_public_key ) && ! empty( $stripe_secret_key ) && wpboutik_in_array_r( 'card', $payment_type ) ) : ?>
                            <div id="cardstripe" class="mt-4">
                                <div id="card-element"></div>

                                <!-- We'll put the error messages in this element -->
                                <div id="card-errors" class="text-red-500 text-xs" role="alert"></div>
                            </div>
						<?php endif; ?>

                        <div id="bacsdiv" class="mt-4"></div>

						<?php if ( ( ! empty ( $mollie_api_key_test ) || ! empty( $mollie_api_key_live ) ) && wpboutik_in_array_r( 'mollie', $payment_type ) ) : ?>
                            <div id="cardmollie" class="mt-4">
                                <div id="cardmollie"></div>
                                <div id="form-error-mollie" class="field-error-mollie" role="alert"></div>
                            </div>
						<?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Order summary -->
            <div class="mt-10 lg:mt-0">
                <h2 class="text-lg font-medium text-gray-900"><?php _e( 'Order summary', 'wpboutik' ); ?></h2>

                <div class="mt-4 rounded-lg border border-gray-200 bg-white shadow-sm">
                    <h3 class="sr-only"><?php _e( 'Items in your cart', 'wpboutik' ); ?></h3>
                    <ul role="list" class="divide-y divide-gray-200">
						<?php
						$subtotal    = 0;
						$taxes_class = array();
						foreach ( WPB()->cart->get_cart() as $cart_item_key => $stored_product ) {
							$stored_product = (object) $stored_product;
							$selling_fees   = get_post_meta( $stored_product->product_id, 'selling_fees', true );
							if ( empty( $selling_fees ) || ( ! empty( $stored_product->customization ) && ! empty( $stored_product->customization['renew'] ) ) ) {
								$selling_fees = 0;
							}
							if ( $stored_product->variation_id != "0" ) {
								$id_for_tax_class = $stored_product->variation_id;
								$variants         = get_post_meta( $stored_product->product_id, 'variants', true );
								$variation        = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );

								$price      = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;
								$array_name = explode( '-', $stored_product->product_name );
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

								$price = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;
								//$name  = $stored_product->product_name;

								$sku            = $variation->sku;
								$recursive      = $variation->recursive;
								$recursive_type = $variation->recursive_type;
								$recursive_number = $variation->recursive_number;

							} else {
								$id_for_tax_class = $stored_product->product_id;
								$price            = get_post_meta( $stored_product->product_id, 'price', true );
								$name             = get_the_title( $stored_product->product_id );
								$sku              = get_post_meta( $stored_product->product_id, 'sku', true );
								$recursive        = get_post_meta( $stored_product->product_id, 'recursive', true );
								$recursive_type   = get_post_meta( $stored_product->product_id, 'recursive_type', true );
								$recursive_number   = get_post_meta( $stored_product->product_id, 'recursive_number', true );
							}

							if ( $activate_tax ) {
								$tax_class                                      = get_post_meta( $stored_product->product_id, 'tax', true );
								$taxes_class[ $tax_class ][ $id_for_tax_class ] = $stored_product->quantity * ( $price + $selling_fees );
							}
							$price_renew = '';
							$type        = get_post_meta( $stored_product->product_id, 'type', true );
							if ( $type == 'abonnement' || ( $type == 'plugin' && $recursive ) ) {
								$price_renew = ( ( $selling_fees > 0 ) ? ' ' . __( 'then', 'wpboutik' ) . ' ' . esc_html( wpboutik_format_number( $price ) ) . $currency_symbol . ' ' : ' ' ) . display_recursivity( $recursive_type, $recursive_number );
							}
							$subtotal = $subtotal + ( $stored_product->quantity * ( $price + $selling_fees ) ); ?>
                            <li class="flex py-6 px-4 sm:px-6">
                                <div class="flex-shrink-0">
									<?php if ( has_post_thumbnail( $stored_product->product_id ) ) :
										echo get_the_post_thumbnail( $stored_product->product_id, 'post-thumbnail', array(
											'class' => 'w-20 rounded-md',
											'alt'   => esc_html( $stored_product->product_name )
										) );
									else :
										echo wpb_get_default_image( 'w-20 rounded-md', $stored_product->product_id, $stored_product->variation_id );
									endif; ?>
                                </div>

                                <div class="ml-6 flex flex-1 flex-col">
                                    <div class="flex">
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-sm">
                                                <a href="<?php echo get_permalink( $stored_product->product_id ); ?>"
                                                   class="<?php echo ( empty( $title_product_color ) ) ? 'text-indigo-600' : ''; ?> font-medium text-base hover:text-[var(--hovercolor)]"
                                                   style="<?php echo( ! empty( $title_product_color ) ? 'color: ' . $title_product_color : '' ); ?>;--hovercolor: <?php echo $title_product_color_on_hover; ?>"><?php echo $name; ?></a>
                                            </h4>
                                            <!--<p class="mt-1 text-sm text-gray-500">Black</p>
											<p class="mt-1 text-sm text-gray-500">Large</p>-->
											<?php if ( $sku ) : ?>
                                                <p class="m-0 mt-1 text-sm text-gray-500">SKU
                                                    : <?php echo esc_attr( $sku ); ?></p>
											<?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="flex flex-1 items-end justify-between pt-2">
                                        <p class="mt-1 text-sm font-medium text-gray-900"><?php echo esc_html( wpboutik_format_number( $price + $selling_fees ) . $currency_symbol ) . $price_renew; ?></p>

                                        <div class="ml-4">
                                            <p class="mt-1 text-sm font-medium text-gray-900"><span
                                                        class="text-gray-500"><?php _e( 'Quantity', 'wpboutik' ); ?></span> <?php echo esc_html( $stored_product->quantity ); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </li>
							<?php
						} ?>
                    </ul>
                    <dl class="space-y-6 border-t border-gray-200 py-6 px-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm"><?php _e( 'Subtotal', 'wpboutik' ); ?></dt>
                            <dd class="text-sm font-medium text-gray-900"
                                id="subtotal"><?php echo esc_html( wpboutik_format_number( $subtotal ) . $currency_symbol ); ?></dd>
                        </div>
						<?php
						$discount        = 0;
						if ( isset( $_COOKIE['wpboutik_coupons_code'] ) ) :
							$wpboutik_coupons_code = json_decode( stripslashes( $_COOKIE['wpboutik_coupons_code'] ) );
							$coupon_code = get_option( 'wpboutik_options_coupon_code_' . $wpboutik_coupons_code->id );

							$wp_get_discount = wpb_get_discount_cart( $subtotal, WPB()->cart->get_cart(), $activate_tax, $taxes_class );
							$discount        = ( $wp_get_discount['discount'] ?? 0 );
							$taxes_class     = ( $wp_get_discount['taxes_class'] ?? $taxes_class ); ?>
                            <div class="flex items-center justify-between">
                                <dt class="flex font-medium text-gray-900"><?php _e( 'Discount', 'wpboutik' ); ?>
                                    <span class="ml-2 rounded-full bg-gray-200 py-1 px-2 text-xs text-gray-600">
                                            <?php echo $wpboutik_coupons_code->code; ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                 fill="currentColor" class="remove-promo coupon" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                            </svg>
                                        </span>
                                    <span class="py-1 px-2 text-xs text-gray-600">(<?php echo $coupon_code['valeur'] . ( ( 'percent' == $coupon_code['type'] ) ? '%' : $currency_symbol ); ?>)</span>
                                </dt>
                                <dd class="text-sm font-medium text-gray-900"
                                    id="subtotal"><?php echo esc_html( '-' . round( $discount, 2 ) . $currency_symbol ); ?></dd>
                            </div>
						<?php
						endif;
						$gift_card = 0;
						if ( $gift_card_code = WPB_Gift_Card::get_gift_card_from_cookie() ) :
							$gift_card = (float) $gift_card_code['available_value'];
							?>
                            <div class="flex items-center justify-between">
                                <dt class="flex font-medium text-gray-900"><?php _e( 'Gift card', 'wpboutik' ); ?>
                                    <span class="ml-2 rounded-full bg-gray-200 py-1 px-2 text-xs text-gray-600">
                                        <?= $gift_card_code['code']; ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             fill="currentColor" class="remove-promo gift_card" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                        </svg>
                                    </span>
                                    <span class="py-1 px-2 text-xs text-gray-600">(<?= $gift_card_code['available_value'] . $currency_symbol ?>)</span>
                                </dt>
                                <dd class="text-sm font-medium text-gray-900"
                                    id="subtotal"><?php echo esc_html( '-' . $gift_card_code['available_value'] . $currency_symbol ); ?></dd>
                            </div>
						<?php
						endif;

						$tax             = 0;
						if ( $activate_tax ) :
							$tax_rates = get_wpboutik_tax_rates();
							$country_key = 'FR';
							if ( ( isset( $user_meta ) && isset( $user_meta['wpboutik_shipping_country'] ) ) ) {
								$country_key = reset( $user_meta['wpboutik_shipping_country'] );
							}
							if ( $taxes_class ) :
								foreach ( $taxes_class as $tax_class => $products_of_tax ) :
									$count = 0;
									foreach ( $products_of_tax as $value ) {
										$count += $value;
									} ?>
                                    <div class="flex items-center justify-between">
                                        <dt class="text-sm"><?php _e( 'Taxes', 'wpboutik' ); ?>
                                            <span id="tax_name_<?php echo esc_attr( $tax_class ); ?>">(<?php echo empty( $tax_rates[ $country_key ][ 'name_tx_' . $tax_class ] ) ? '' : $tax_rates[ $country_key ][ 'name_tx_' . $tax_class ]; ?>)</span>
                                        </dt>
										<?php
										$tax_value = round( ( $count ) * ( ( empty( $tax_rates[ $country_key ][ 'percent_tx_' . $tax_class ] ) ? 0 : $tax_rates[ $country_key ][ 'percent_tx_' . $tax_class ] ) / 100 ), 2 );
										$tax       += $tax_value; ?>
                                        <dd class="text-sm font-medium text-gray-900"><span
                                                    id="tax_<?php echo esc_attr( $tax_class ); ?>"><?php echo esc_html( wpboutik_format_number( $tax_value ) ); ?></span><?php echo esc_html( $currency_symbol ); ?>
                                        </dd>
                                    </div>
								<?php
								endforeach;
							endif;
						endif;
						$method   = get_option( reset( $method_list ) );
						$shipping = 0;
						if ( $method && WPB()->cart->has_delivery_need() ) : ?>
                            <div class="flex items-center justify-between">
                                <dt class="text-sm"><?php _e( 'Shipping', 'wpboutik' ); ?></dt>
								<?php $shipping = 0;
								$shipping       = get_evenly_reduced_shipping($method, $method['flat_rate']); ?>
                                <dd class="text-sm font-medium text-gray-900"><span
                                            id="method_flat_rate"><?php echo esc_html( wpboutik_format_number( $shipping ) ); ?></span><?php echo esc_html( $currency_symbol ); ?>
                                </dd>
                            </div>
						<?php endif; ?>
                        <div class="flex items-center justify-between border-t border-gray-200 pt-6">
                            <dt class="text-base font-medium"><?php _e( 'Total', 'wpboutik' ); ?></dt>
							<?php
							$order_total = $subtotal - $discount + floatval( $shipping ) + $tax;

							$final_order_total = WPB_Gift_Card::get_finale_price( $order_total ) ?>
                            <dd class="text-base font-medium text-gray-900"><span
                                        id="ordertotal"><?php echo esc_html( wpboutik_format_number( $final_order_total ) ); ?></span><?php echo esc_html( $currency_symbol ); ?>
                            </dd>
                        </div>
						<?php if ( $rest_in_card = WPB_Gift_Card::get_rest_in_cart( $order_total ) ) : ?>
                            <span class="block text-xs text-gray-600">Solde de la carte cadeau après achat : <?= wpboutik_format_number( $rest_in_card ) . $currency_symbol ?></span>
							<?php if ( get_option( 'wpboutik_options_gift_card_multiple' ) == 'no' && $rest_in_card > 0 ) : ?>
                                <span class="block text-xs text-orange-600">Attention, les cartes cadeaux sont à usage unique le solde sera perdu après achat.</span>
							<?php endif; ?>
						<?php endif; ?>
                    </dl>

                    <div class="border-t border-gray-200 py-6 px-4 sm:px-6">

                        <div class="mb-2 flex items-center">
                            <input id="has_coupon_code" name="has_coupon_code" type="checkbox"
                                   class="m-0 h-4 w-4 rounded border-gray-300 text-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)]"
                                   style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                            <label for="has_coupon_code" class="m-0 pl-2 text-sm font-medium text-gray-900">
								<?php _e( 'Have a coupon code?', 'wpboutik' ); ?>
                            </label>
                        </div>

                        <div class="mb-6 hidden" id="formcoupon_checkout">
                            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                                <form id="checkout_formcoupon" class="space-y-6">
                                    <div>
                                        <label for="coupon_code"
                                               class="block text-sm font-medium text-gray-700"><?php _e( 'Coupon code', 'wpboutik' ); ?></label>
                                        <div class="mt-1">
                                            <input type="text" id="coupon_code" name="coupon_code"
                                                   class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-[var(--backgroundcolor)] focus:outline-none focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                                   style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <button type="button"
                                                data-nonce="<?php echo wp_create_nonce( 'wpboutik-coupon-code-nonce' ); ?>"
                                                class="wpbapplycouponcode flex w-full justify-center rounded-md border border-transparent bg-[var(--backgroundcolor)] py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-[var(--hovercolor)] focus:outline-none focus:ring-2 focus:ring-[var(--backgroundcolor)] focus:ring-offset-2"
                                                style="--backgroundcolor: <?php echo $backgroundcolor; ?>;--hovercolor: <?php echo $hovercolor; ?>">
											<?php _e( 'Apply coupon code', 'wpboutik' ); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="mb-2 flex items-center">
                            <input id="has_gift_card_code" name="has_gift_card_code" type="checkbox"
                                   class="m-0 h-4 w-4 rounded border-gray-300 text-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)]"
                                   style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                            <label for="has_gift_card_code" class="m-0 pl-2 text-sm font-medium text-gray-900">
								<?php _e( 'Have a gift card?', 'wpboutik' ); ?>
                            </label>
                        </div>

                        <div class="mb-6 hidden" id="formgift_card_checkout">
                            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                                <form id="checkout_formgift_card" class="space-y-6">
                                    <div>
                                        <label for="gift_card_code"
                                               class="block text-sm font-medium text-gray-700"><?php _e( 'Gift card', 'wpboutik' ); ?></label>
                                        <div class="mt-1">
                                            <input type="text" id="gift_card_code" name="gift_card_code"
                                                   class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-[var(--backgroundcolor)] focus:outline-none focus:ring-[var(--backgroundcolor)] sm:text-sm"
                                                   style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <button type="button"
                                                data-nonce="<?php echo wp_create_nonce( 'wpboutik-gift-card-code-nonce' ); ?>"
                                                class="wpb_apply_gift_card_code flex w-full justify-center rounded-md border border-transparent bg-[var(--backgroundcolor)] py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-[var(--hovercolor)] focus:outline-none focus:ring-2 focus:ring-[var(--backgroundcolor)] focus:ring-offset-2"
                                                style="--backgroundcolor: <?php echo $backgroundcolor; ?>;--hovercolor: <?php echo $hovercolor; ?>">
											<?php _e( 'Apply gift card code', 'wpboutik' ); ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

						<?php
						$privacy_policy_url = get_privacy_policy_url();
						$policy_page_id     = (int) get_option( 'wp_page_for_privacy_policy' );
						$page_title         = ( $policy_page_id ) ? get_the_title( $policy_page_id ) : '';

						if ( $privacy_policy_url && $page_title ) {
							echo '<p>' . __( 'Vos données personnelles seront utilisées pour le traitement de votre commande, vous accompagner au cours de votre visite du site web, et pour d’autres raisons décrites dans notre', 'wpboutik' ) . ' ' . sprintf(
									'<a class="privacy-policy-link" href="%s" target="_blank" rel="privacy-policy">%s</a>',
									esc_url( $privacy_policy_url ),
									esc_html( $page_title )
								) . '</p><br>';
						}
						?>
                        <div class="mb-2 flex items-center">
                            <input id="terms" name="terms" type="checkbox"
                                   class="m-0 h-4 w-4 rounded border-gray-300 text-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)]"
                                   style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
                            <label for="terms" class="m-0 pl-2 text-sm font-medium text-gray-900">
								<?php echo __( 'J’ai lu et j’accepte les', 'wpboutik' ) . ' ' . '<a class="terms-and-conditions-link" rel="terms-and-conditions" href="' . esc_url( get_permalink( wpboutik_get_page_id( 'terms' ) ) ) . '" target="_blank">' . get_the_title( wpboutik_get_page_id( 'terms' ) ) . '</a>.'; ?>
                                <span class="text-red-600">*</span>
                            </label>
                            <span class="termsError text-red-500 text-xs hidden">error</span>
                        </div>
						<?php if ( WPB()->is_subscription_active() ) : ?>
							<?php wpb_field( 'payment' ) ?>
						<?php else : ?>
                            <p><?= __( 'Checkout currently not available !' ) ?></p>
						<?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
		<?php do_action( 'wpboutik_after_checkout_form' ); ?>
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