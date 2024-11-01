function toggleDivDisabled(disabled) {
    const myDiv = document.getElementById('paypaldiv');
    if (disabled) {
        myDiv.classList.add('disabled');
    } else {
        myDiv.classList.remove('disabled');
    }
}

function validateInputs() {
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
    if (selectedDeliveryMethod) {
        const choosePointRelaisButton = document.querySelector('button[data-method-id="' + selectedDeliveryMethod.value + '"]');
        const replacedButton = document.querySelector('.replacedButton[data-method-id="' + selectedDeliveryMethod?.value + '"]');

        if (!replacedButton && choosePointRelaisButton) {
            error['delivery_method'] = ajax_var_checkout.delivery_method_required_label;
        }
    }

    if (jQuery.isEmptyObject(error)) {
        toggleDivDisabled(false);
    } else {
        toggleDivDisabled(true);
    }
}

jQuery(document).ready(function ($) {

    $('input').on('input', function () {
        validateInputs();
    });

    let order_id = '';
    let url = '';
    /*let products_paypal = '';
    let subtotal = '';
    let discount = '';
    let tax = '';
    let shipping = '';
    let total = '';*/
    paypal_sdk.Buttons({
        fundingSource: 'paypal',

        style: {
            shape: 'rect',
            height: 40,
        },

        onClick: () => {
            $.ajax({
                type: 'post',
                url: ajax_var_checkout.url,
                data: {
                    action: 'wpboutik_ajax_create_order',
                    datas: $("#checkout_form").serialize(),
                    nonce: ajax_var_checkout.nonce_create_order
                },
                success: function (response) {
                    if (response.success) {
                        order_id = response.order_id;
                        url = response.url;
                        /*products_paypal = response.products_paypal;
                        subtotal = response.subtotal;
                        discount = response.discount;
                        tax = response.tax;
                        shipping = response.shipping;
                        total = response.total;*/
                    }
                }
            });

        },

        // Sets up the transaction when a payment button is clicked
        createOrder: async (data, actions) => {

            const form_data = new FormData();

            form_data.append('action', 'wpboutik_ajax_checkout_price');
            form_data.append('method_shipping_id', $('input[type=radio][name=delivery-method]:checked').val());
            form_data.append('shipping_country_key', $('#shipping_country').val());
            form_data.append('nonce', ajax_var_checkout.nonce_paypal);
            form_data.append('datas', $("#checkout_form").serialize());

            const amount_params = await fetch(ajax_var_checkout.url, {
                method: 'POST',
                body: form_data
            });
            const amount_value = await amount_params.json();
            // La transaction
            return actions.order.create({
                purchase_units: [{
                    //items: products_paypal,
                    amount: amount_value
                }]
            });

        },
        // Finalize the transaction after payer approval
        onApprove: (data, actions) => {
            return actions.order.capture().then(function (orderData) {
                // Successful capture! For dev/demo purposes:
                //console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));
                //console.log(orderData);
                //alert(orderData.payer.name.given_name + ' ' + orderData.payer.name.surname + ', votre transaction est effectuée. Vous allez recevoir une notification très bientôt lorsque nous validons votre paiement.');
                const transaction = orderData.purchase_units[0].payments.captures[0];
                //alert(`Transaction ${transaction.status}: ${transaction.id}\n\nSee console for all available details`);
                // When ready to go live, remove the alert and show a success message within this page. For example:
                // const element = document.getElementById('paypal-button-container');
                // element.innerHTML = '<h3>Thank you for your payment!</h3>';
                // Or go to another URL:  actions.redirect('thank_you.html');

                //console.log(transaction);

                if (transaction.status === 'COMPLETED') {
                    jQuery.ajax({
                        type: 'post',
                        url: ajax_var_checkout.url,
                        data: {
                            action: 'wpboutik_ajax_order_after_payment_paypal',
                            payment_intent_id: transaction.id,
                            transaction_fees: transaction.seller_receivable_breakdown && transaction.seller_receivable_breakdown.paypal_fee ?
                                transaction.seller_receivable_breakdown.paypal_fee.value :
                                0,
                            order_id_finish: order_id,
                            licenses: ajax_var_checkout.current_cart_licenses,
                            nonce: ajax_var_checkout.nonce_finish_paypal
                        },
                        success: function (response) {
                            if (response.success) {
                                window.location.href = url;
                            }
                        }
                    });
                }
            });
        },
        // Annuler la transaction
        onCancel: function (data) {
            jQuery.ajax({
                type: 'post',
                url: ajax_var_checkout.url,
                data: {
                    action: 'wpboutik_ajax_order_cancel_payment',
                    order_id: order_id,
                    nonce: ajax_var_checkout.nonce_cancel
                }
            });
            alert("Transaction annulée !");
        },
        onError(err) {
            jQuery.ajax({
                type: 'post',
                url: ajax_var_checkout.url,
                data: {
                    action: 'wpboutik_ajax_order_cancel_payment',
                    order_id: order_id,
                    nonce: ajax_var_checkout.nonce_cancel
                }
            });
            alert('Le paiement à échoué ou a été refusé.')
        }
    }).render('#paypal-button-container');
});