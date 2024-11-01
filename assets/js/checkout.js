jQuery(document).ready(function ($) {

    function removeQueryParameter(parameter) {
        if (window.location.search) {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has(parameter)) {
                urlParams.delete(parameter);
                var newUrl =
                    window.location.protocol +
                    "//" +
                    window.location.host +
                    window.location.pathname +
                    "?" +
                    urlParams.toString();
                window.history.replaceState({path: newUrl}, "", newUrl);
            }
        }
    }

    function checkUrlParameter(parameter, value) {
        if (window.location.search) {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has(parameter) && urlParams.get(parameter) === value) {
                return true;
            }
        }
        return false;
    }

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

    let order_id;

    if (checkUrlParameter("payment_mollie_status", "cancelled")) {
        if ($("#payment-error").hasClass("hidden")) {
            $("#payment-error").removeClass("hidden");
        }
        removeQueryParameter("payment_mollie_status");
    }
    if (checkUrlParameter("payment_paybox_status", "cancelled")) {
        if ($("#payment-error").hasClass("hidden")) {
            $("#payment-error").removeClass("hidden");
        }
        removeQueryParameter("payment_paybox_status");
    }

    $('#already_customer').change(function () {
        if ($('#formlogin_checkout').hasClass('hidden')) {
            $('#formlogin_checkout').removeClass('hidden');
        } else {
            $('#formlogin_checkout').addClass('hidden');
        }
    });

    $('#has_coupon_code').change(function () {
        if ($('#formcoupon_checkout').hasClass('hidden')) {
            $('#formcoupon_checkout').removeClass('hidden');
        } else {
            $('#formcoupon_checkout').addClass('hidden');
        }
    });
    $('#has_gift_card_code').change(function () {
        if ($('#formgift_card_checkout').hasClass('hidden')) {
            $('#formgift_card_checkout').removeClass('hidden');
        } else {
            $('#formgift_card_checkout').addClass('hidden');
        }
    });

    $('#cardstripe').hide();
    $('#paypaldiv').hide();
    $('#bacsdiv').hide();
    $('#cardmollie').hide();
    if ($("#card").is(":checked")) {
        $('#cardstripe').show();
    }
    if ($("#paypal").is(":checked")) {
        $('#paypaldiv').show();
    }
    if ($("#bacs").is(":checked")) {
        $('#bacsdiv').show();
    }
    if ($("#mollie").is(":checked")) {
        $('#cardmollie').show();
    }
    $('input[type=radio][name=payment-type]').change(function () {
        if ($('.create_order').attr('data-wpbcheckout') == 'free') {
            $('#paypaldiv').hide();
            $('#bacsdiv').hide();
            $('#cardstripe').hide();
            $('#card-button').show();
            $('#cardmollie').hide();
            return;
        }
        var id = $(this).attr('id');
        if (id == 'paypal') {
            $('#paypaldiv').show();
            $('#bacsdiv').hide();
            $('#cardstripe').hide();
            $('#card-button').hide();
            $('#cardmollie').hide();
        } else if (id == 'bacs') {
            $('#bacsdiv').show();
            $('#cardstripe').hide();
            $('#paypaldiv').hide();
            $('#card-button').show();
            $('#cardmollie').hide();
        } else if (id == 'mollie') {
            $('#bacsdiv').hide();
            $('#cardstripe').hide();
            $('#paypaldiv').hide();
            $('#card-button').show();
            $('#cardmollie').show();
        }
    });

    $('#same-as-shipping').change(function () {
        if ($('#billingform').hasClass('hidden')) {
            $('#billingform').removeClass('hidden');
        } else {
            $('#billingform').addClass('hidden');
        }
    });

    if (ajax_var_checkout.carriers_boxtal.length > 0) {
        function boxtalAjaxOffers(first = false) {
            var pays = $('#shipping_country').val();
            var ville = $('#shipping_city').val();
            var cp = $('#shipping_postal_code').val();
            var adresse = $('#shipping_address').val();
            var company = $('#shipping_company').val();

            // Vérifier si tous les champs sont remplis
            if (pays && ville && cp && adresse) {

                var type = (company) ? 'entreprise' : 'particulier';

                $.ajax({
                    type: 'post',
                    url: ajax_var_checkout.url,
                    data: {
                        action: 'wpboutik_ajax_checkout_boxtal_load_price_offers',
                        pays: pays,
                        ville: ville,
                        cp: cp,
                        adresse: adresse,
                        type: type,
                        nonce: ajax_var_checkout.nonce_boxtal,
                    },
                    success: function (response) {
                        if (response.success) {
                            $.each(response.data, function (method_id, data) {
                                $('#delivery-method-' + method_id + '-flat-rate').html(data.flat_rate);
                                $('input[name=flat_rate_' + method_id + ']').val(data.flat_rate_without_symbol);
                                $('#delivery-method-' + method_id + '-boxtal').html(data.html);

                                if (data.flat_rate == '0&euro;') {
                                    $('#delivery-method-' + method_id + '-flat-rate').closest('label').fadeOut();
                                } else {
                                    $('#delivery-method-' + method_id + '-flat-rate').closest('label').fadeIn();
                                }
                            });
                            $('#deliversmethod').show();
                            if (first) {
                                setTimeout(function () {
                                    // Simuler un "unclick"
                                    //$('input[type=radio][name=delivery-method]').prop('checked', false);

                                    // Simuler un nouveau clic
                                    $('input[type=radio][name=delivery-method]:last').click();
                                }, 500);
                            }
                        }
                    }
                });
            } else {
                // Cacher la div si au moins un champ n'est pas rempli
                $('#deliversmethod').hide();
            }
        }

        // Appeler la fonction au chargement de la page
        boxtalAjaxOffers(true);

        // Attacher un gestionnaire d'événement au changement de chaque champ
        $('#shipping_country, #shipping_city, #shipping_postal_code, #shipping_address').on('input', function () {
            // Appeler la fonction à chaque changement
            boxtalAjaxOffers();
        });
    }

    $(document).on("click", ".remove-promo", function (event) {
        event.preventDefault();
        let datas = {
            action: 'wpboutik_remove_promo',
            nonce: ajax_var_checkout.nonce_promo
        };
        if ($(this).hasClass('coupon')) {
            datas.delete_promo = 'coupon';
        } else if ($(this).hasClass('gift_card')) {
            datas.delete_promo = 'gift_card';
        } else {
            return false;
        }
        
        $.ajax({
            type: 'post',
            url: ajax_var_checkout.url,
            data: datas,
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    location.reload()
                }
            }
        });
    });

    $(document).on("click", ".openModalMethod", function (event) {
        event.preventDefault();
        var method_id = $(this).data('method-id');
        $("#myModal" + method_id).removeClass("hidden");
        $.ajax({
            type: 'post',
            url: ajax_var_checkout.url,
            data: {
                action: 'wpboutik_ajax_checkout_boxtal_load_listpoints',
                pays: $('#shipping_country').val(),
                ville: $('#shipping_city').val(),
                cp: $('#shipping_postal_code').val(),
                adresse: $('#shipping_address').val(),
                operator_code: $(this).data('operator-code'),
                operator_logo: $(this).data('operator-logo'),
                method_id: method_id,
                flat_rate: $('#delivery-method-' + method_id + '-flat-rate').html(),
                nonce: $(this).data('nonce'),
            },
            success: function (response) {
                if (response.success) {
                    $('.content_modal_boxtal_' + response.data.method_id).html(response.data.html);
                }
            }
        });
    });

    $(document).on("click", ".chooseCodePoint", function () {
        var code_point = $(this).data('code-point');
        var name_point = $(this).data('name-point');
        var address_point = $(this).data('address-point');
        var city_point = $(this).data('city-point');
        var method_id = $(this).data('method-id');

        var $existingReplacement = $('.replacedButton[data-method-id="' + method_id + '"]');
        if ($existingReplacement.length > 0) {
            // Si un remplacement existe déjà, mettez à jour son contenu
            $existingReplacement.html('<div class="replacedButton" data-method-id="' + method_id + '"><p>' + name_point + ' - ' + address_point + ' - ' + city_point + '</p><a data-method-id="' + method_id + '" href="#" class="openModalMethod text-[var(--backgroundcolor)] underline focus:outline-none">Changer de point relais</a></div>');
        } else {
            // Remplacez l'élément qui a remplacé le bouton précédemment
            $('.openModalMethod[data-method-id="' + method_id + '"]').replaceWith('<div class="replacedButton" data-method-id="' + method_id + '"><p>' + name_point + ' - ' + address_point + ' - ' + city_point + '</p><a data-method-id="' + method_id + '" href="#" class="openModalMethod text-[var(--backgroundcolor)] underline focus:outline-none">Changer de point relais</a></div>');
        }

        $("#myModal" + method_id).addClass("hidden");
        var radioElement = $('input[type=radio][name="delivery-method"][value="' + method_id + '"]');
        radioElement.click();
        radioElement.after('<input type="hidden" name="code_point_' + method_id + '" value="' + code_point + '">');
        radioElement.after('<input type="hidden" name="address_point_' + method_id + '" value="' + name_point + ' - ' + address_point + ' - ' + city_point + '">');
    });

    $(document).on("click", ".closeModalMethod", function () {
        var method_id = $(this).data('method-id');
        $("#myModal" + method_id).addClass("hidden");
    });

    $(".deliverymethod").mouseover(function () {
        $(this).addClass('ring-2 ring-[var(--backgroundcolor)]');
        $(this).find('.span').addClass('border');
        $(this).find('.span').removeClass('border-2');
    }).mouseout(function () {
        if (!$(this).children('input').is(':checked')) {
            $(this).removeClass('ring-2 ring-[var(--backgroundcolor)]');
            $(this).find('.span').addClass('border-2');
            $(this).find('.span').removeClass('border');
        }
    });

    $('#shipping_country').change(function () {
        $.ajax({
            type: 'post',
            url: ajax_var_checkout.url,
            data: {
                action: 'wpboutik_ajax_checkout_modify_tax_rate',
                payment_type: $('input[type=radio][name=payment-type]:checked').attr('id'),
                shipping_country_key: $(this).val(),
                shipping_postal_code: $('#shipping_postal_code').val(),
                shipping_city: $('#shipping_city').val(),
                shipping_company: $('#shipping_company').val(),
                shipping_address: $('#shipping_address').val(),
                method_shipping_id: $('input[type=radio][name=delivery-method]:checked').val(),
                nonce: $(this).data('nonce'),
            },
            success: function (response) {
                if (response.success) {
                    var taxes = response.tax;
                    if (taxes.length != 0) {
                        for (var key in taxes) {
                            if (taxes.hasOwnProperty(key)) {
                                var value = taxes[key];
                                $('#tax_name_' + key).html('(' + value.name + ')');
                                $('#tax_' + key).html(value.value);
                            }
                        }
                    } else {
                        $('#tax_name_standard').html('()');
                        $('#tax_standard').html(0);
                        $('#tax_name_reduce').html('()');
                        $('#tax_reduce').html(0);
                        $('#tax_name_zero').html('()');
                        $('#tax_zero').html(0);
                    }
                    $('#ordertotal').html(response.ordertotal);
                    $('#paypal-button-container').attr('data-total', response.ordertotal);
                }
            }
        });
    });

    $('input[type=radio][name=delivery-method]').change(function () {
        $.ajax({
            type: 'post',
            url: ajax_var_checkout.url,
            data: {
                action: 'wpboutik_ajax_checkout_add_price_delivery_method',
                payment_type: $('input[type=radio][name=payment-type]:checked').attr('id'),
                method_id: $(this).val(),
                shipping_country_key: $('#shipping_country').val(),
                shipping_country: $('#shipping_country').val(),
                shipping_postal_code: $('#shipping_postal_code').val(),
                shipping_city: $('#shipping_city').val(),
                shipping_company: $('#shipping_company').val(),
                shipping_address: $('#shipping_address').val(),
                nonce: $(this).data('nonce'),
            },
            success: function (response) {
                if (response.success) {
                    var taxes = response.tax;
                    if (taxes.length != 0) {
                        for (var key in taxes) {
                            if (taxes.hasOwnProperty(key)) {
                                var value = taxes[key];
                                $('#tax_name_' + key).html('(' + value.name + ')');
                                $('#tax_' + key).html(value.value);
                            }
                        }
                    } else {
                        $('#tax_name_standard').html('()');
                        $('#tax_standard').html(0);
                        $('#tax_name_reduce').html('()');
                        $('#tax_reduce').html(0);
                        $('#tax_name_zero').html('()');
                        $('#tax_zero').html(0);
                    }
                    $('#method_flat_rate').html(response.method_flat_rate);
                    $('#ordertotal').html(response.ordertotal);
                    $('#paypal-button-container').attr('data-total', response.ordertotal);
                }
            }
        });
    });

    $('input[type=radio][name=delivery-method]').click(function () {
        $('input[type=radio][name=delivery-method]:not(:checked)').closest('.deliverymethod').find('.icon').addClass('hidden');
        $('input[type=radio][name=delivery-method]:not(:checked)').closest('.deliverymethod').removeClass('ring-2 ring-[var(--backgroundcolor)]');

        if (!$(this).is(':checked')) {
            $(this).removeClass("border-transparent");
            $(this).addClass("border-gray-300");
            $(this).closest('.deliverymethod').children('.icon').addClass('hidden');
            $(this).closest('.deliverymethod').children('.span').addClass('border-transparent');
            $(this).closest('.deliverymethod').children('.span').removeClass('border');
            $(this).closest('.deliverymethod').children('.span').removeClass('border-[var(--backgroundcolor)]');
            $(this).closest('.deliverymethod').removeClass('ring-2 ring-[var(--backgroundcolor)]');
        } else {
            $(this).addClass("border-transparent");
            $(this).removeClass("border-gray-300");
            $(this).closest('.deliverymethod').children('.icon').removeClass('hidden');
            $(this).closest('.deliverymethod').children('.span').removeClass('border-transparent');
            $(this).closest('.deliverymethod').children('.span').addClass('border');
            $(this).closest('.deliverymethod').children('.span').addClass('border-[var(--backgroundcolor)]');
            $(this).closest('.deliverymethod').addClass('ring-2 ring-[var(--backgroundcolor)]');
        }
    });


    // PayPal
    /*
        paypal_sdk.Buttons({
            // Order is created on the server and the order id is returned
            createOrder: (data, actions) => {
                return fetch("/api/orders", {
                    method: "post",
                    // use the "body" param to optionally pass additional order information
                    // like product ids or amount
                })
                    .then((response) => response.json())
                    .then((order) => order.id);
            },
            // Finalize the transaction on the server after payer approval
            onApprove: (data, actions) => {
                return fetch(`/api/orders/${data.orderID}/capture`, {
                    method: "post",
                })
                    .then((response) => response.json())
                    .then((orderData) => {
                        // Successful capture! For dev/demo purposes:
                        console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));
                        const transaction = orderData.purchase_units[0].payments.captures[0];
                        alert(`Transaction ${transaction.status}: ${transaction.id}\n\nSee console for all available details`);
                        // When ready to go live, remove the alert and show a success message within this page. For example:
                        // const element = document.getElementById('paypal-button-container');
                        // element.innerHTML = '<h3>Thank you for your payment!</h3>';
                        // Or go to another URL:  actions.redirect('thank_you.html');
                    });
            }
        }).render('#paypal-button-container');*/

    $('.wpbapplycouponcode').on('click', function (e) {
        $thisbutton = $(this),
            nonce = $thisbutton.data('nonce');

        var data = {
            action: 'wpboutik_ajax_apply_coupon_code',
            coupon_code: $("input[name=coupon_code]").val(),
            nonce: nonce,
        };

        $.ajax({
            type: 'post',
            url: ajax_var_checkout.url,
            data: data,
            success: function (response) {
                window.location = response.url;
                return;
            },
        });
    });

    $('.wpb_apply_gift_card_code').on('click', function (e) {
        $thisbutton = $(this),
            nonce = $thisbutton.data('nonce');

        var data = {
            action: 'wpboutik_ajax_apply_gift_card_code',
            coupon_code: $("input[name=gift_card_code]").val(),
            nonce: nonce,
        };

        $.ajax({
            type: 'post',
            url: ajax_var_checkout.url,
            data: data,
            success: function (response) {
                window.location = response.url;
                return;
            },
        });
    });

    $('.create_order').on('click', function (e) {

        if ($('input[type=radio][name=payment-type]:checked').attr('id') === 'bacs' || $('input[type=radio][name=payment-type]:checked').attr('id') === 'monetico' || $('input[type=radio][name=payment-type]:checked').attr('id') === 'paybox' || $(this).attr('data-wpbcheckout')) {

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
            if (selectedDeliveryMethod) {
                const choosePointRelaisButton = document.querySelector('button[data-method-id="' + selectedDeliveryMethod.value + '"]');
                const replacedButton = document.querySelector('.replacedButton[data-method-id="' + selectedDeliveryMethod?.value + '"]');

                if (!replacedButton && choosePointRelaisButton) {
                    error['delivery_method'] = ajax_var_checkout.delivery_method_required_label;
                }
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
                            if (response.payment_type === 'monetico') {
                                $.ajax({
                                    type: 'post',
                                    url: ajax_var_checkout.url,
                                    data: {
                                        action: 'wpboutik_ajax_create_payment_monetico',
                                        order_id: response.order_id,
                                        total: response.total,
                                        billing_details: response.billing_details,
                                        url_ok: response.url,
                                        nonce: ajax_var_checkout.nonce_create_payment_monetico,
                                    },
                                    success: function (response) {
                                        if (response.success) {
                                            $('body').append(response.form_html);
                                            $('#wpbFormMonetico').submit();
                                        }
                                    },
                                });
                            } else if (response.payment_type === 'paybox') {
                                $.ajax({
                                    type: 'post',
                                    url: ajax_var_checkout.url,
                                    data: {
                                        action: 'wpboutik_ajax_create_payment_paybox',
                                        order_id: response.order_id,
                                        billing_details: response.billing_details,
                                        total_qty: response.total_qty,
                                        total: response.total,
                                        nonce: ajax_var_checkout.nonce_create_payment_paybox,
                                    },
                                    success: function (response) {
                                        if (response.success) {
                                            $('body').append(response.form_html);
                                            $('#wpbFormPaybox').submit();
                                        }
                                    },
                                });
                            } else {
                                //order_id = response.order_id;
                                window.location = response.url;
                                return;
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

    function saveDataCheckout(formDatas) {
        var data = {
            action: 'wpboutik_ajax_save_data_checkout',
            datas: formDatas,
            nonce: ajax_var_checkout.nonce_save_data_checkout,
        };

        $.ajax({
            type: 'post',
            url: ajax_var_checkout.url,
            data: data,
            success: function (response) {
            },
        });
    }

    $('#checkout_form').change(function () {
        saveDataCheckout($(this).serialize());
    });
});