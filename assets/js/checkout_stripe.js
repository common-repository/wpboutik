jQuery(document).ready(function ($) {

    const stripe = Stripe(ajax_var_checkout.stripe_public_key);

// The items the customer wants to buy
    const items = [{id: "xl-tshirt"}];

    let elements;
    let cardElement;
    let clientSecret;
    let order_id;

    initialize();

    $('input[type=radio][name=payment-type]').change(function () {
        var id = $(this).attr('id');
        if (id == 'card') {
            $('#cardstripe').show();
            $('#paypaldiv').hide();
            $('#bacsdiv').hide();
            var payment_type = $('input[type=radio][name=payment-type]:checked').attr('id');
            if ('card' == payment_type) {
                createElementsStripe();
            }
            $.ajax({
                type: 'post',
                url: ajax_var_checkout.url,
                data: {
                    action: 'wpboutik_ajax_checkout_change_payment_type',
                    shipping_country_key: $('#shipping_country').val(),
                    shipping_postal_code: $('#shipping_postal_code').val(),
                    shipping_city: $('#shipping_city').val(),
                    shipping_company: $('#shipping_company').val(),
                    shipping_address: $('#shipping_address').val(),
                    method_shipping_id: $('input[type=radio][name=delivery-method]:checked').val(),
                    nonce: $(this).data('nonce'),
                }
            });
        }
    });

    // Fetches a payment intent and captures the client secret
    async function initialize() {
        /*const { clientSecret } = await fetch( ajax_var.create, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ items }),
        }).then((r) => r.json());*/

        var payment_type = $('input[type=radio][name=payment-type]:checked').attr('id');
        if ('card' == payment_type) {
            createElementsStripe();
        }
    }

    function createElementsStripe() {
        $.ajax({
            type: 'post',
            url: ajax_var_checkout.url,
            data: {
                action: 'wpboutik_ajax_checkout_stripe_elements',
                method_shipping_id: $('input[type=radio][name=delivery-method]:checked').val(),
                shipping_country_key: $('#shipping_country').val(),
                datas: $("#checkout_form").serialize(),
                nonce: ajax_var_checkout.nonce,
            },
            success: function (response) {
                clientSecret = response.data.clientSecret;
                const appearance = {
                    theme: 'stripe'
                };
                elements = stripe.elements({clientSecret, appearance});

                cardElement = elements.create("card");
                cardElement.mount("#card-element");
                window._customer_stripe = response.customer;                
                /*const paymentElementOptions = {
                    layout: "tabs",
                    paymentMethodOrder: ['card']
                };

                const paymentElement = elements.create("payment", paymentElementOptions);
                paymentElement.mount("#payment-element");*/
            }
        });
    }

    $("#card-element").on('change', ({error}) => {
        let displayError = document.getElementById('card-errors');
        if (error) {
            displayError.textContent = error.message;
        } else {
            displayError.textContent = '';
        }
    });

// Fetches the payment intent status after payment submission
    async function checkStatus() {
        const clientSecret = new URLSearchParams(window.location.search).get(
            "payment_intent_client_secret"
        );

        if (!clientSecret) {
            return;
        }

        const {paymentIntent} = await stripe.retrievePaymentIntent(clientSecret);

        switch (paymentIntent.status) {
            case "succeeded":
                showMessage("Payment succeeded!");
                break;
            case "processing":
                showMessage("Your payment is processing.");
                break;
            case "requires_payment_method":
                showMessage("Your payment was not successful, please try again.");
                break;
            default:
                showMessage("Something went wrong.");
                break;
        }
    }

// ------- UI helpers -------

    function showMessage(messageText) {
        const messageContainer = document.querySelector("#payment-message");

        messageContainer.classList.remove("hidden");
        messageContainer.textContent = messageText;

        setTimeout(function () {
            messageContainer.classList.add("hidden");
            messageText.textContent = "";
        }, 4000);
    }

// Show a spinner on payment submission
    function setLoading(isLoading) {
        if (isLoading) {
            // Disable the button and show a spinner
            document.querySelector("#card-button").disabled = true;
            document.querySelector("#spinner").classList.remove("hidden");
        } else {
            document.querySelector("#card-button").disabled = false;
            document.querySelector("#spinner").classList.add("hidden");
        }
    }

    $('#email_address').change(function () {
        let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailRegex.test($(this).val())){
            createElementsStripe();
        }
    })

    $('.create_order').on('click', function (e) {
        console.log('customer id is : '+window._customer_stripe);
        if( $('input[type=radio][name=payment-type]:checked').attr('id') === 'card' ) {

            e.preventDefault();
            setLoading(true);

            //define and declare and empty errors object
            let error = {};

            const inputs = {
                "shipping_first_name": ["shipping_first_name", ajax_var_checkout.shipping_first_name_required_label, /^[a-zA-Z0-9]+$/, "First Name must be letters only"],
                "shipping_last_name": ["shipping_last_name", ajax_var_checkout.shipping_last_name_required_label, /^[a-zA-Z0-9]+$/, "Last Name must be letters only"],
                "email_address": ["email_address", ajax_var_checkout.email_address_required_label, /^[a-zA-Z0-9+.]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/, "Email must be a valid email"],
                "shipping_address": ["shipping_address", ajax_var_checkout.shipping_address_required_label, /^\s*\S+(?:\s+\S+){2}/, "Address must be letters only"],
                "shipping_city": ["shipping_city", ajax_var_checkout.shipping_city_required_label, /^[a-zA-Z]+$/, "City must be letters only"],
                "shipping_country": ["shipping_country", ajax_var_checkout.shipping_country_required_label, /^[a-zA-Z0-9]+$/, "Country must be letters only"],
                "shipping_postal_code": ["shipping_postal_code", ajax_var_checkout.shipping_postal_code_required_label, /^[0-9]+$/, "Postal code must be numerics only"],
                "shipping_phone": ["shipping_phone", ajax_var_checkout.shipping_phone_required_label, /^[0-9]+$/, "Phone must be numerics only"],
                "terms": ["terms", "Terms is required", null, ajax_var_checkout.terms_required_label],
            };

            for (let key in inputs) {
                const input = inputs[key];
                const element = document.getElementById(key);
                const errorElement = document.getElementsByClassName(input[0] + "Error")[0];
                const value = element.value.trim();

                errorElement.classList.remove('block');
                errorElement.classList.add('hidden');
                element.classList.remove("border-red-500");

                if (key === 'terms' && !element.checked) {
                    error[key] = input[3];
                } else if (value === "") {
                    error[input[0]] = input[1];
                } else if (input[2] !== null && input[2].length && !value.match(input[2])) {
                    error[input[0]] = input[3];
                }
            }

            const selectedDeliveryMethod = document.querySelector('input[name="delivery-method"]:checked');
            const choosePointRelaisButton = document.querySelector('button[data-method-id="' + selectedDeliveryMethod?.value + '"]');
            const replacedButton = document.querySelector('.replacedButton[data-method-id="' + selectedDeliveryMethod?.value + '"]');

            if (selectedDeliveryMethod && !replacedButton && choosePointRelaisButton) {
                error['delivery_method'] = ajax_var_checkout.delivery_method_required_label;
            }

            if (Object.keys(error).length > 0) {
                for (let key in error) {
                    const element = document.getElementById(key);
                    const errorElement = document.getElementsByClassName(key + "Error")[0];
                    if (element && element.length) {
                        element.classList.add("border-red-500");
                    }
                    errorElement.classList.remove('hidden');
                    errorElement.classList.add('block');
                    errorElement.innerHTML = error[key];
                }
                setLoading(false);
            } else {

                $thisbutton = $(this),
                    nonce = $thisbutton.data('nonce'),
                    datas = $("#checkout_form").serialize();

                var data = {
                    action: 'wpboutik_ajax_create_order',
                    datas: datas,
                    nonce: nonce,
                };

                $.ajax({
                    type: 'post',
                    url: ajax_var_checkout.url,
                    data: data,
                    success: function (response) {
                        if (response.success) {
                            order_id = response.order_id;
                            if (response.payment_type == 'card') {
                                var billing_details = response.billing_details;
                                delete billing_details['first_name'];
                                delete billing_details['last_name'];
                                stripe
                                .createPaymentMethod({
                                  type: 'card',
                                  card: cardElement,
                                  billing_details: billing_details,
                                })
                                .then(function(result) {
                                    let paymentMethodId = result.paymentMethod.id;
                                    $.ajax({
                                        type: 'post',
                                        url: ajax_var_checkout.url,
                                        data: {
                                            email_address: billing_details.email,
                                            payment_id: paymentMethodId,
                                            nonce: ajax_var_checkout.nonce_payment_stripe,
                                            action: 'wpboutik_ajax_client_stripe'
                                        },
                                        success: function (response) {
                                            stripe.confirmCardPayment(clientSecret, {
                                                payment_method: response.payment
                                            }).then(function (result) {
                                                setLoading(false);
                                                if (result.error) {
                                                    // Show error to your customer (for example, insufficient funds)
                                                    console.log(result.error.message);
                                                    //showMessage(result.error.message);
                                                    $('#card-errors').html(result.error.message);
                                                } else {
            
                                                    // The payment has been processed!
                                                    if (result.paymentIntent.status === 'succeeded') {
                                                        // Show a success message to your customer
                                                        // There's a risk of the customer closing the window before callback
                                                        // execution. Set up a webhook or plugin to listen for the
                                                        // payment_intent.succeeded event that handles any business critical
                                                        // post-payment actions.
            
                                                        $.ajax({
                                                            type: 'post',
                                                            url: ajax_var_checkout.url,
                                                            data: {
                                                                action: 'wpboutik_ajax_finish_order_after_payment_stripe',
                                                                payment_intent_id: result.paymentIntent.id,
                                                                order_id_finish: order_id,
                                                                nonce: ajax_var_checkout.nonce_finish
                                                            },
                                                            success: function(response) {
                                                                if ( response.success ) {
                                                                    setTimeout(function() {
                                                                        window.location.href = response.billing_details.url;
                                                                        // return;
                                                                    }, 1000);
                                                                }
                                                            }
                                                        });
                                                    } else if (result.paymentIntent.status === 'canceled') {
                                                        $.ajax({
                                                            type: 'post',
                                                            url: ajax_var_checkout.url,
                                                            data: {
                                                                action: 'wpboutik_ajax_order_cancel_payment',
                                                                order_id: order_id,
                                                                nonce: ajax_var_checkout.nonce_cancel
                                                            }
                                                        });
                                                    }
                                                }
                                            });
                                        }
                                    })
                                });
                            }
                        } else {
                            /*if (response.error && response.product_url) {
                                window.location = response.product_url;
                                return;
                            }*/
                        }
                    },
                });
            }
        }
    });
});