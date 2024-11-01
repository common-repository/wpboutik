<form method="POST">

								<?php do_action( "wpboutik_before_account_form_edit_address_{$load_address}" );
								$currency_symbol = get_wpboutik_currency_symbol(); ?>

                                <div class="mx-auto max-w-7xl sm:px-2 lg:px-8">
                                    <div class="mx-auto max-w-2xl px-4 lg:max-w-4xl lg:px-0">
                                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
											<?php echo apply_filters( 'wpboutik_account_edit_address_title', $page_title, $load_address ); ?></h1>
                                    </div>
									<?php
									if ( isset( $_COOKIE['wpboutik_error_address'] ) ) :
										$errors = json_decode( stripslashes( $_COOKIE['wpboutik_error_address'] ) ); ?>
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
									endif; ?>
                                </div>

                                <div class="mx-auto max-w-7xl sm:px-2 lg:px-8">
                                    <div class="grid grid-cols-6 gap-6">
                                        <div class="col-span-6 sm:col-span-3"><label
                                                    for="<?php echo $load_address; ?>_first_name"
                                                    class="block text-sm font-medium text-gray-700">Prénom</label>
                                            <div class="mt-1"><input id="<?php echo $load_address; ?>_first_name"
                                                                     name="<?php echo $load_address; ?>_first_name"
                                                                     type="text"
                                                                     class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                     aria-describedby="<?php echo $load_address; ?>_first_name"
                                                                     value="<?php echo $address['wpboutik_' . $load_address . '_first_name']['value']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-span-6 sm:col-span-3"><label
                                                    for="<?php echo $load_address; ?>_last_name"
                                                    class="block text-sm font-medium text-gray-700">Nom</label>
                                            <div class="mt-1"><input id="<?php echo $load_address; ?>_last_name"
                                                                     name="<?php echo $load_address; ?>_last_name"
                                                                     type="text"
                                                                     class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                     aria-describedby="<?php echo $load_address; ?>_last_name"
                                                                     value="<?php echo $address['wpboutik_' . $load_address . '_last_name']['value']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-span-3 sm:col-span-2"><label
                                                    for="<?php echo $load_address; ?>_company"
                                                    class="block text-sm font-medium text-gray-700">Société</label>
                                            <div class="mt-1"><input id="<?php echo $load_address; ?>_company"
                                                                     name="<?php echo $load_address; ?>_company"
                                                                     type="text"
                                                                     class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                     aria-describedby="<?php echo $load_address; ?>_company"
                                                                     value="<?php echo $address['wpboutik_' . $load_address . '_company']['value']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-span-3 sm:col-span-2"><label
                                                    for="<?php echo $load_address; ?>_phone"
                                                    class="block text-sm font-medium text-gray-700">Téléphone</label>
                                            <div class="mt-1"><input id="<?php echo $load_address; ?>_phone"
                                                                     name="<?php echo $load_address; ?>_phone"
                                                                     type="text"
                                                                     class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                     aria-describedby="<?php echo $load_address; ?>_phone"
                                                                     value="<?php echo $address['wpboutik_' . $load_address . '_phone']['value']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-span-9 sm:col-span-4"><label
                                                    for="<?php echo $load_address; ?>_address"
                                                    class="block text-sm font-medium text-gray-700">Adresse</label>
                                            <div class="mt-1"><input id="<?php echo $load_address; ?>_address"
                                                                     name="<?php echo $load_address; ?>_address"
                                                                     type="text"
                                                                     class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                     aria-describedby="<?php echo $load_address; ?>_address"
                                                                     value="<?php echo $address['wpboutik_' . $load_address . '_address']['value']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-span-3 sm:col-span-2"><label
                                                    for="<?php echo $load_address; ?>_city"
                                                    class="block text-sm font-medium text-gray-700">Ville</label>
                                            <div class="mt-1"><input id="<?php echo $load_address; ?>_city"
                                                                     name="<?php echo $load_address; ?>_city"
                                                                     type="text"
                                                                     class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                     aria-describedby="<?php echo $load_address; ?>_city"
                                                                     value="<?php echo $address['wpboutik_' . $load_address . '_city']['value']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-span-6 sm:col-span-4"><label
                                                    for="<?php echo $load_address; ?>_country"
                                                    class="block text-sm font-medium text-gray-700">Pays</label>
                                            <div class="mt-1">
                                                <div class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
													<?php $countries = get_wpboutik_countries(); ?>
                                                    <select id="<?php echo $load_address; ?>_country"
                                                            name="<?php echo $load_address; ?>_country"
                                                            class="form-select w-full ">
														<?php
														foreach ( $countries as $key => $country ) :
															$selected = '';
															if ( $key === $address['wpboutik_' . $load_address . '_country']['value'] ) {
																$selected = 'selected';
															} ?>
                                                            <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $country ); ?></option>
														<?php endforeach; ?>
                                                    </select>
                                                    <div class="form-error"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-span-3 sm:col-span-2"><label
                                                    for="<?php echo $load_address; ?>_postal_code"
                                                    class="block text-sm font-medium text-gray-700">Code
                                                postal</label>
                                            <div class="mt-1"><input id="<?php echo $load_address; ?>_postal_code"
                                                                     name="<?php echo $load_address; ?>_postal_code"
                                                                     type="text"
                                                                     class="block w-full flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                     aria-describedby="<?php echo $load_address; ?>_postal_code"
                                                                     value="<?php echo $address['wpboutik_' . $load_address . '_postal_code']['value']; ?>">
                                            </div>
                                        </div>
                                    </div>

									<?php do_action( "wpboutik_after_account_form_edit_address_{$load_address}" ); ?>


                                    <button type="submit"
                                            class="inline-flex mt-4 justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            name="save_address"
                                            value="<?php esc_attr_e( 'Save address', 'wpboutik' ); ?>"><?php esc_html_e( 'Save address', 'wpboutik' ); ?></button>
									<?php wp_nonce_field( 'wpboutik-edit_address', 'wpboutik-edit-address-nonce' ); ?>
                                    <input type="hidden" name="action" value="edit_address"/>
                                </div>
                            </form>