jQuery(document).ready(function ($) {
    var mollie = Mollie(ajax_var_mollie_checkout.mollie_profile_ID, {
        locale: ajax_var_mollie_checkout.locale,
        testmode: ajax_var_mollie_checkout.mollie_test,
    });

    var options = {
        styles: {
            base: {
                color: 'rgba(0, 0, 0, 0.8)',
            },
        },
    };
    var cardComponent = mollie.createComponent('card', options);

    cardComponent.mount('#cardmollie');

    var form = document.getElementById('checkout_form');
    var formError = document.getElementById('form-error-mollie');
    var submitButton = document.getElementById('card-button');

    /**
     * Disables the form inputs and submit button
     */
    function disableForm() {
        submitButton.disabled = true;
    }

    /**
     * Enables the form inputs and submit button
     */
    function enableForm() {
        submitButton.disabled = false;
    }

    function setLoading(isLoading) {
        if (isLoading) {
            // Disable the button and show a spinner
            document.querySelector('#card-button').disabled = true;
            document.querySelector('#spinner').classList.remove('hidden');
        } else {
            document.querySelector('#card-button').disabled = false;
            document.querySelector('#spinner').classList.add('hidden');
        }
    }

    form.addEventListener('submit', function (e) {
        if (
            $('input[type=radio][name=payment-type]:checked').attr('id') === 'mollie'
        ) {
            console.log('mollie');

            e.preventDefault();

            setLoading(true);

            //define and declare and empty errors object
            let error = {};

            const inputs = {
                shipping_first_name: [
                    'shipping_first_name',
                    ajax_var_mollie_checkout.shipping_first_name_required_label,
                    /^[a-zA-Z0-9]+$/,
                    'First Name must be letters only',
                ],
                shipping_last_name: [
                    'shipping_last_name',
                    ajax_var_mollie_checkout.shipping_last_name_required_label,
                    /^[a-zA-Z0-9]+$/,
                    'Last Name must be letters only',
                ],
                email_address: [
                    'email_address',
                    ajax_var_mollie_checkout.email_address_required_label,
                    /^[a-zA-Z0-9+.]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/,
                    'Email must be a valid email',
                ],
                shipping_address: [
                    'shipping_address',
                    ajax_var_mollie_checkout.shipping_address_required_label,
                    /^\s*\S+(?:\s+\S+){2}/,
                    'Address must be letters only',
                ],
                shipping_city: [
                    'shipping_city',
                    ajax_var_mollie_checkout.shipping_city_required_label,
                    /^[a-zA-Z]+$/,
                    'City must be letters only',
                ],
                shipping_country: [
                    'shipping_country',
                    ajax_var_mollie_checkout.shipping_country_required_label,
                    /^[a-zA-Z0-9]+$/,
                    'Country must be letters only',
                ],
                shipping_postal_code: [
                    'shipping_postal_code',
                    ajax_var_mollie_checkout.shipping_postal_code_required_label,
                    /^[0-9]+$/,
                    'Postal code must be numerics only',
                ],
                shipping_phone: [
                    'shipping_phone',
                    ajax_var_mollie_checkout.shipping_phone_required_label,
                    /^[0-9]+$/,
                    'Phone must be numerics only',
                ],
                terms: [
                    'terms',
                    'Terms is required',
                    null,
                    ajax_var_mollie_checkout.terms_required_label,
                ],
            };

            for (let key in inputs) {
                const input = inputs[key];
                const element = document.getElementById(key);
                const errorElement = document.getElementsByClassName(
                    input[0] + 'Error'
                )[0];
                const value = element.value.trim();

                errorElement.classList.remove('block');
                errorElement.classList.add('hidden');
                element.classList.remove('border-red-500');
                if (key === 'terms' && !element.checked) {
                    error[key] = input[3];
                } else if (value === '') {
                    error[input[0]] = input[1];
                } else if (input[2]?.length && !value.match(input[2])) {
                    error[input[0]] = input[3];
                }
            }

            const selectedDeliveryMethod = document.querySelector(
                'input[name="delivery-method"]:checked'
            );
            const choosePointRelaisButton = document.querySelector(
                'button[data-method-id="' + selectedDeliveryMethod.value + '"]'
            );
            const replacedButton = document.querySelector(
                '.replacedButton[data-method-id="' +
                selectedDeliveryMethod?.value +
                '"]'
            );

            let cardToken;

            if (
                selectedDeliveryMethod &&
                !replacedButton &&
                choosePointRelaisButton
            ) {
                error['delivery_method'] =
                    ajax_var_mollie_checkout.delivery_method_required_label;
            }

            if (Object.keys(error).length > 0) {
                for (let key in error) {
                    const element = document.getElementById(key);
                    const errorElement = document.getElementsByClassName(
                        key + 'Error'
                    )[0];
                    if (element && element.length) {
                        element.classList.add('border-red-500');
                    }
                    errorElement.classList.remove('hidden');
                    errorElement.classList.add('block');
                    errorElement.innerHTML = error[key];
                }
                setLoading(false);
            } else {
                // Reset possible form error
                formError.textContent = '';

                // Get a payment token
                mollie.createToken().then(function (result) {
                    cardToken = result.token;
                    var error = result.error;

                    if (error) {
                        enableForm();
                        formError.textContent = error.message;
                        return;
                    }

                    // Add token to the form
                    var tokenInput = document.createElement('input');
                    tokenInput.setAttribute('name', 'token');
                    tokenInput.setAttribute('type', 'hidden');
                    tokenInput.setAttribute('value', cardToken);
                    tokenInput.setAttribute('id', 'token_input');

                    form.appendChild(tokenInput);

                    // Re-submit form to the server
                    //form.submit();
                });

                ($thisbutton = $(this)), (datas = $('#checkout_form').serialize());

                const btnOrder = form.querySelector('.create_order');
                const nonce = $(btnOrder).data('nonce');

                var data = {
                    action: 'wpboutik_ajax_create_order',
                    datas,
                    nonce,
                };

                $.ajax({
                    type: 'post',
                    url: ajax_var_checkout.url,
                    data: data,
                    success: function (response) {
                        if (response?.success) {
                            const {order_id, payment_type, total, url} = response;
                            if (order_id && payment_type == 'mollie') {
                                $.ajax({
                                    type: 'post',
                                    url: ajax_var_checkout.url,
                                    data: {
                                        action: 'wpboutik_ajax_create_payment_mollie',
                                        order_id,
                                        total: total,
                                        redirectUrl: url,
                                        cardToken,
                                        nonce: ajax_var_checkout.nonce_finish_mollie,
                                    },
                                    success: function (response) {
                                        const {success, checkoutUrl, errorMessage} =
                                            response;
                                        if (success && checkoutUrl) {
                                            window.location.href = checkoutUrl
                                        } else {
                                            setLoading(false);
                                            console.log(
                                                errorMessage,
                                                'success false for create payment'
                                            );
                                        }
                                    },
                                });
                            }
                        }
                    },
                    error: function (err) {
                        console.log(err, 'error');
                    },
                });
            }
        }
    });
});