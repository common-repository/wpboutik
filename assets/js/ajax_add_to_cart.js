jQuery(document).ready(function ($) {


    function number_format (number) {
        return new Intl.NumberFormat(ajax_var.locale.replace('_', '-'), { style: 'currency', currency: ajax_var.currency_name }).format(number)
    }
    function setLoading(btn, isLoading) {
        if (isLoading) {
            // Disable the button and show a spinner
            btn.attr("disabled", "disabled");
            btn.html('<svg aria-hidden="true" role="status" class="mr-2 inline h-5 w-5 animate-spin text-indigo-400" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
                '                                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"></path>\n' +
                '                                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="#FFF"></path>\n' +
                '                                    </svg>');
        } else {
            btn.removeAttr("disabled");
            btn.html(ajax_var.add_to_cart_text);
        }
    }

    function rafraichirContenuPanier() {
        /*var xhr = new XMLHttpRequest();
        xhr.open('GET', '/wp-admin/admin-ajax.php?action=wpboutik_get_cart_fragments', true);

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 400) {
                var response = JSON.parse(xhr.responseText);

                // Mettez à jour le contenu du panier sur la page
                var panierDropdown = document.getElementsByClassName('WPBpanierDropdown');
                panierDropdown.innerHTML = response.data.fragments.panierDropdown;
            }
        };

        xhr.send();*/
        $.ajax({
            url: ajax_var.url,
            type: 'GET',
            dataType: 'json',
            data: {
                action: 'wpboutik_get_cart_fragments'
            },
            success: function (response) {
                $('.WPBpanierDropdown').replaceWith(response.data.fragments.panierDropdown);
                if (response.success) {
                    // $('.WPBpanierDropdown').remove();
                    // $('.WPBpanierBtn').after(response.data.fragments.panierDropdown);
                    //$('.WPBpanierDropdown').replaceWith(response.data.fragments.panierDropdown);
                    //$( ".WPBpanierDropdown" ).load(window.location.href + " #panierDropdown" );
                }
            },
            error: function (xhr, status, error) {
                console.error('Erreur lors de la requête AJAX : ' + status + ' - ' + error);
            }
        });
    }

    $('.select_option_wpb').on('change', function (e) {
        e.preventDefault();

        $thisselect = $(this),
            $form = $thisselect.closest('form.cart'),
            variations = $form.find('input[name=variations]').val() || '';
        if ($thisselect.attr('id').match('opt_visuel_gc') != null) {
            $img = $('[for="' + $thisselect.attr('id') + '"] img');
            $('.imggalfirst img').attr('src', $img.attr('src'));
            $('.zoomImg').attr('src', $img.attr('src'));
        }
        var formok = true;
        var selectElements = $('#formcartsingle').serializeArray().filter(function (item) {
            return item.name.indexOf('select') !== -1;
        });

        const variant = [];
        //if(selectElements.length > 1) {
        $(selectElements).each(function (index, element) {
            if (element.value == '') {
                formok = false;
                return false;
            }
            variant.push(element.value);
        });
        /*} else {
            $(selectElements).each(function(index, element) {
                if(element.value == '') {
                    formok = false;
                    return false;
                }
                variant.push(element.value);
            });
        }*/
        if (true === formok) {
            $('.add_btn_single').show();
            $('.show_rupture_stock').hide();
            $('.show_inactif_option').hide();
            $('.current_price_before_reduction').removeClass('hidden');
            $('.choose_qty').removeClass('hidden');
            $('.current_price_before_reduction').hide();
            $.each(ajax_var.variations, function (index, element) {
                if (compareArrays(variant, element.options)) {
                    if (element?.recursive && element.recursive == 1) {
                        for (let abo of JSON.parse(ajax_var.licenses)) {
                            if (abo.variant_id == element.id) {
                                $('#product_subscription').val(JSON.stringify(abo));
                                break;
                            }
                        }
                    }
                    $('input[name=quantity]').val(1);
                    if (ajax_var.product_continu_rupture == '' && ajax_var.product_continu_rupture != 1) {
                        $('input[name=quantity]').attr('max', element.quantity);
                    }
                    let new_price = number_format(element.price)
                    if (element.price_before_reduction != undefined && element.price_before_reduction != '') {
                        new_price = '<span class="before-reduction-price">'+number_format(element.price_before_reduction)+'</span>&nbsp;'+new_price
                    }
                    if (element?.recursive && element.recursive == 1) {
                        new_price += ' for '+element.recursive_number+' '+element.recursive_type
                    }
                    $('#current_price').html(new_price)
                    if (element.image_temp != undefined && element.image_temp != '') {
                        $('#tabs-2-panel-1 img').attr('src', ajax_var.app_wpboutik + element.image_temp);
                        $('.showimggalery:first-child img').attr('src', ajax_var.app_wpboutik + element.image_temp);
                    } else {
                        $('#tabs-2-panel-1 img').attr('src', ajax_var.product_first_image);
                        $('.showimggalery:first-child img').attr('src', ajax_var.product_first_image);
                    }
                    $('input[name=variation_id]').val(element.id);
                    $('input[name=product_sku]').val(element.sku);
                    $('input[name=product_name]').val(ajax_var.product_name + ' - ' + element.name.join(', '));
                    if ($('input[name="product_gestion_stock"]').val() == 1) {
                        if (element.quantity == '0') {
                            $('.add_btn_single').hide();
                            $('.show_rupture_stock').show();
                        }
                    }
                    if (element.status == '0') {
                        $('.add_btn_single').hide();
                        $('.show_rupture_stock').hide();
                        $('.show_inactif_option').show();
                        $('#current_price').html('');
                        $('.current_price_before_reduction').hide();
                    }
                }
            });
        } else {
            $('.choose_qty').addClass('hidden');
            $('input[name=quantity]').val(1);
            if (ajax_var.product_continu_rupture != '' && ajax_var.product_continu_rupture != 1) {
                $('input[name=quantity]').attr('max', ajax_var.product_qty);
            }
            let abo = '';
            if (element?.recursive && element.recursive == 1) {
                abo += ' for '+element.recursive_number+' '+element.recursive_type
            }
            $('#current_price').html(ajax_var.product_price + ajax_var.currency + abo);
            $('#tabs-2-panel-1 img').attr('src', ajax_var.product_first_image);
            $('.showimggalery:first-child img').attr('src', ajax_var.product_first_image);
            if (ajax_var.product_price_before_reduction != undefined && ajax_var.product_price_before_reduction != '') {
                $('.current_price_before_reduction').show();
                $('.current_price_before_reduction span').html(ajax_var.product_price_before_reduction);
            }
            if (ajax_var.variations.length > 0) {
                $('.current_price_before_reduction').addClass('hidden');
            }
            $('input[name=variation_id]').val(0);
            $('input[name=product_sku]').val(ajax_var.product_sku);
            $('input[name=product_name]').val(ajax_var.product_name);
            $('.show_inactif_option').hide();
        }
    });

    function compareArrays(array1, array2) {
        // Vérifie si les deux tableaux ont la même longueur
        if (array1.length != array2.length) {
            return false;
        }

        // Boucle sur chaque élément des deux tableaux
        for (var i = 0; i < array1.length; i++) {
            // Vérifie si l'élément courant est un tableau
            if (Array.isArray(array1[i]) && Array.isArray(array2[i])) {
                // Si l'élément courant est un tableau, compare les tableaux récursivement
                if (!compareArrays(array1[i], array2[i])) {
                    return false;
                }
            } else if (array1[i] != array2[i]) {
                // Si l'élément courant n'est pas un tableau, compare les éléments
                return false;
            }
        }

        // Les deux tableaux sont identiques
        return true;
    }

    $('.wpb-opt-price input').on('change', function () {
        if ($('#opt_price_gc_custom').is(':checked')) {
            $('.wpb-field-price').removeClass('hidden');
        } else {
            $('.wpb-field-price').addClass('hidden');
        }
    });

    $('input#gift_card_price').on('input', function () {
        let price = 0;
        if ($(this).val() != '') {
            price += +($(this).val())
        } 
        $('#current_price').html(number_format(price))
    });

    $('.wpboutik_single_add_to_cart_button').on('click', function (e) {
        e.preventDefault();

        let $thisbutton = $(this),
            $form = $thisbutton.closest('form.cart'),
            nonce = $thisbutton.data('nonce'),
            product_qty = $form.find('input[name=quantity]').val() || 1,
            product_id = $form.find('input[name=product_id]').val() || 0,
            product_sku = $form.find('input[name=product_sku]').val() || '',
            variation_id = $form.find('input[name=variation_id]').val() || 0;
        product_name = $form.find('input[name=product_name]').val() || '';

        let customization = {};
        $form.find('.customizable_fields').each(function () {
            customization[$(this).attr('name')] = $(this).val();
        });

        var formok = true;
        var selectElements = $('#formcartsingle').serializeArray().filter(function (item) {
            return item.name.indexOf('select') !== -1;
        });

        if (selectElements.length >= 1) {
            $(selectElements).each(function (index, element) {
                if (element.value == '') {
                    formok = false;
                    return false;
                }
            });
        }

        if (false === formok) {
            window.alert(ajax_var.i18n_make_a_selection_text);
            return false;
        }

        setLoading($thisbutton, true);

        var data = {
            action: 'wpboutik_ajax_add_to_cart',
            product_id: product_id,
            product_sku: product_sku,
            quantity: product_qty,
            variation_id: variation_id,
            product_name: product_name,
            nonce: nonce,
            customization
        };

        $.ajax({
            type: 'post',
            url: ajax_var.url,
            data: data,
            success: function (response) {
                setLoading($thisbutton, false);
                if (response && response.fragments) {

                    $('.wpboutik-cart-count').removeClass('hidden');
                    $('.wpboutik-cart-count').addClass('absolute');
                    $('.wpboutik-cart-count').html(response.count_product);
                    $('.view-cartwpb-' + response.product_id).removeClass('hidden');
                    $('.view-cartwpb-' + response.product_id).show();

                    $.each(response.fragments, function (key, value) {
                        $('.' + key).replaceWith(value);
                    });
                }
            }
        });
    });

    //wpboutik_stop_payment_license
    $('.wpb-resiliation').on('click', function (e) {
        e.preventDefault();
        let $id = $(this).data('subscription');
        let data = {
            action: 'wpboutik_stop_payment_license',
            subscription: $id,
            nonce: ajax_var.nonce_licenses_remove
        };
        $.ajax({
            type: 'post',
            url: ajax_var.url,
            data: data,
            success: function (response) {
                // console.log(response);
                window.location.reload()
            }
        });
    })

    function createPopup ($title, $content) {
        let $layout = $('<div class="wpb-popup-layout"></div>');
        let $popup = $('<div class="wpb-popup"></div>');
        let $header = $(`<div class="wpb-popup-header">${$title}</div>`);
        let $close = $('<a href="#" class="wpb-close-popup"><svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg></a>');
        $header.append($close);
        $popup.append($header);
        $popup.append($content);
        $layout.append($popup);
        $('body').append($layout);
        $close.on('click', function (e) {
            e.preventDefault();
            $layout.remove();
        });
        $layout.on('click', function (e) {
            if (e.target.classList.contains('wpb-popup-layout')) {
                $(this).remove();
            }
        });

    } 

    $('.wpb-manage-urls').on('click', function (e) {
        e.preventDefault();
        let $content = $('<ul class="wpb-urls-list"></ul>');
        let $datas = $(this).data('urls');
        let $license = $(this).data('license');
        if ($datas.length) {
            for (let $data of $datas) {
                let $li = $(`<li>${$data.url}</li>`);
                let $delete = $('<a class="wpb-btn" href="#">Supprimer</a>');
                $delete.on('click', function (e) {
                    e.preventDefault();
                    $.ajax({
                        type: 'post',
                        url: ajax_var.url,
                        data: {
                            action: 'wpboutik_ajax_remove_url_license',
                            nonce: ajax_var.nonce_licenses_remove_url,
                            subscription: $license,
                            url: $data.id
                        },
                        success: () => {
                            $li.remove();
                            $('#licenses-user-table').load(window.location.href + ' #licenses-user-table > *');
                            if ($content.is(':empty')) {
                                $('.wpb-popup-layout').remove();
                            }
                        }
                    })
                })
                $li.append($delete);
                $content.append($li);
            }
        }
        createPopup('Gestion des urls', $content);
    })

    $('.wpb-renew').on('click', function (e) {
        e.preventDefault();
        let data = {
            action: 'wpboutik_ajax_add_to_cart_renew',
            product_id: $(this).data('product'),
            varaiant_id: $(this).data('variant'),
            code: $(this).data('code'),
            nonce: ajax_var.nonce_licenses_renew
        };
        $.ajax({
            type: 'post',
            url: ajax_var.url,
            data: data,
            success: function (response) {
                window.location.reload();
            }
        });
    })
    $('.create_mail_back_in_stock').on('click', function (e) {
        e.preventDefault();

        $thisbutton = $(this),
            $form = $thisbutton.closest('form.cart'),
            nonce = $thisbutton.data('nonce'),
            product_id = $thisbutton.data('product_id') || 0,
            variation_id = $form.find('input[name=variation_id]').val() || 0,
            email = $form.find('input[name=mail_backinstock]').val() || 0;

        var data = {
            action: 'wpboutik_ajax_create_mail_back_in_stock',
            product_id: product_id,
            variation_id: variation_id,
            email: email,
            nonce: nonce,
        };

        $.ajax({
            type: 'post',
            url: ajax_var.url,
            data: data,
            success: function (response) {
                $('.create_mail_back_in_stock_form').html("");
                $('.create_mail_back_in_stock_form').html("Votre demande a bien été prise en compte.");
            }
        });
    });

    $(document).on('click', '.wpboutik_archive_add_to_cart_button', function (e) {
        e.preventDefault();

        $thisbutton = $(this),
            nonce = $thisbutton.data('nonce'),
            product_qty = 1,
            product_id = $thisbutton.data('product_id') || 0,
            product_sku = $thisbutton.data('product_sku') || 0,
            variation_id = $thisbutton.data('variation_id') || 0;
        product_name = $thisbutton.data('product_name') || '';

        setLoading($thisbutton, true);

        var data = {
            action: 'wpboutik_ajax_add_to_cart',
            product_id: product_id,
            product_sku: product_sku,
            quantity: product_qty,
            variation_id: variation_id,
            product_name: product_name,
            nonce: nonce,
        };

        $.ajax({
            type: 'post',
            url: ajax_var.url,
            data: data,
            success: function (response) {
                setLoading($thisbutton, false);
                /*if (response.count_product) {
                    $('.wpboutik-cart-count').removeClass('hidden');
                    //$('.wpboutik-cart-count').addClass('inline-block');
                    $('.wpboutik-cart-count').addClass('absolute');
                    $('.wpboutik-cart-count').html(response.count_product);
                    $('.view-cartwpb-' + response.product_id).removeClass('hidden');
                    $('.view-cartwpb-' + response.product_id).show();
                    rafraichirContenuPanier();
                }*/
                if (response && response.fragments) {

                    $('.wpboutik-cart-count').removeClass('hidden');
                    $('.wpboutik-cart-count').addClass('absolute');
                    $('.wpboutik-cart-count').html(response.count_product);
                    $('.view-cartwpb-' + response.product_id).removeClass('hidden');
                    $('.view-cartwpb-' + response.product_id).show();

                    $.each(response.fragments, function (key, value) {
                        $('.' + key).replaceWith(value);
                    });
                }
            }
        });
    });

    $(document).on('click', '.wpboutik_single_remove_to_cart_button', function (e) {
        e.preventDefault();
        $thisbutton = $(this),
            nonce = $thisbutton.data('nonce'),
            cart_item_key = $thisbutton.data('cart_item_key'),
            product_id = $thisbutton.data('product_id');
        var data = {
            action: 'wpboutik_ajax_remove_to_cart',
            product_id: product_id,
            nonce: nonce,
            cart_item_key: cart_item_key,
        };

        $.ajax({
            type: 'post',
            url: ajax_var.url,
            data: data,
            success: function (response) {
                if (response.count_product) {
                    $('.wpboutik-cart-count').html(response.count_product);
                    $('#subtotal').html(response.subtotal);
                    $('#ordertotal').html(response.ordertotal);
                    $('.ordertotal_mini').html(response.subtotal);
                } else {
                    $('.wpboutik-cart-count').addClass('hidden');
                    $('#formcartwpb').html(ajax_var.cart_empty_html);
                }

                $thisbutton.closest("li").addClass('hidden');

                if (response.fragments) {
                    $.each(response.fragments, function (key, value) {
                        $('.' + key).replaceWith(value);
                    });
                }
            }
        });
    });

    $(document).on('keyup mouseup', '.changeqty', function () {
        let $thisbutton = $(this),
            qty = $thisbutton.val(),
            nonce = $thisbutton.data('nonce'),
            product_id = $thisbutton.data('product_id'),
            variation_id = $thisbutton.data('variation_id');
        let data = {
            action: 'wpboutik_ajax_update_qty_to_cart',
            quantity: qty,
            product_id: product_id,
            variation_id: variation_id,
            nonce: nonce,
        };

        $.ajax({
            type: 'post',
            url: ajax_var.url,
            data: data,
            success: function (response) {
                if (response.count_product) {
                    $('.wpboutik-cart-count').html(response.count_product);
                    $('#subtotal').html(response.subtotal);
                    $('#ordertotal').html(response.ordertotal);
                    $('.ordertotal_mini').html(response.subtotal);
                }

                $.each(response.fragments, function (key, value) {
                    let dropdown = $('.WPBpanierDropdown');
                    $('.' + key).each(function () {
                        $(this).html($(value).html())
                    })
                });
            }
        });
    });

    $(".wpboutik-Tabs-panel").not(":first").hide();

    // Gérer le clic sur les liens des tabs
    $(".navsinglewpb a").click(function (e) {
        e.preventDefault();
        var tabId = $(this).attr("href");

        // Masquer tous les contenus des tabs
        $(".wpboutik-Tabs-panel").hide();

        // Afficher le contenu de la tab cliquée
        $(tabId).show();

        // Ajouter/retirer la classe "border-indigo-500 text-indigo-600" sur le lien de la tab cliquée
        $(".navsinglewpb a").removeClass("active");
        $(this).addClass("active");
    });

    $('#tabs').change(function () {
        var selectedTab = $(this).val();
        $('.wpboutik-Tabs-panel').hide();
        $('#' + selectedTab).show();
    });

    function searchResultPosition ($input, $resultBox) {
        if (window.matchMedia('(min-width: 750px)').matches) {
            if ($input.offset().left + $input.outerWidth(true) < $(window).outerWidth() / 2) {
                $resultBox.css({
                    'left': 0,
                    'right': 'auto'
                })
            } else {
                $resultBox.css({
                    'left': 'auto',
                    'right': 0
                })
            }
        } else {
            $resultBox.removeAttr('style');
        }
    }
    $(document).on('click', function (e) {
        const targeted = $(e.target).parents('.search_product_box.search-input-filled');
        if (!targeted.length) {
            const $toEmpty = $('.search_product_box.search-input-filled');
            if ($toEmpty.length) {
                const $inputFilled = $toEmpty.find('input');
                $inputFilled.val('');
                $inputFilled.removeClass('search-input-filled');
                $toEmpty.removeClass('visible');
                $toEmpty.find('.wpb-search-results').html('');
            }
        }
    });
    const $searchLinks = $('.search_product_box')
    $searchLinks.each(function () {
        let $this = $(this);
        let $menu = $this.parent().parent('ul');
        let $link = $this.prev('a.search_product_link');
        let $close = $this.find('.close_search_product');
        let $input = $this.find('input#wpb-product-search');
        let $resultBox = $this.find('.wpb-search-results');
        searchResultPosition($this, $resultBox);
        $(window).on('resize', function () {
            searchResultPosition($this, $resultBox);
        })
        if (!$this.hasClass('visible-input')) {
            $this.css({
                'height': $menu.outerHeight(),
                'width': $menu.outerWidth()
            });
        }
        $link.click(function (e) {
            e.preventDefault();
            $this.addClass('visible');
            $this.addClass('currently-target')
            setTimeout(function () {
                $input.focus();
            }, 500);
        })
        $close.click(function (e) {
            e.preventDefault();
            $input.val('');
            $resultBox.html('');
            $this.removeClass('visible');
        });
        $input.on('input', function () {
            $(this).parents('.search_product_box').addClass('search-input-filled');
            var searchQuery = $(this).val();
            if (searchQuery.length >= 3) {
                $.ajax({
                    type: 'post',
                    url: ajax_var.url,
                    data: {
                        action: 'wpb_search_products',
                        search_query: searchQuery,
                        no_detail: !$this.parent().hasClass('menu-item-search-product')
                    },
                    success: function (response) {
                        // Manipuler la réponse et afficher les résultats
                        $resultBox.html(response);
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                    }
                });
            } else {
                if (searchQuery.length) {
                    $resultBox.html('<p class="empty-search-product-response">La recherche nécessite au minimum 3 caractères</p>');
                } else {
                    $resultBox.html('');
                }
            }
        });
    });
    $(document).on(
        'mouseenter', '.search_product_box .search-result', function () {
            const to_show = $(this).attr('data-target');
            $('.search-product-details#' + to_show).addClass('focus');
        }
    );
    $(document).on(
        'mouseleave', '.search_product_box .search-result', function () {
            const to_show = $(this).attr('data-target');
            $('.search-product-details#' + to_show).removeClass('focus');
        }
    );

    let $minicartBtn = $('.WPBpanierBtn');
    $minicartBtn.each(function () {
        $(this).click(function (e) {
            e.preventDefault();
            $(this).next('.WPBpanierDropdown').removeClass('hidden');
        });
    })
    $(document).on('click', '.WPBpanierDropdown-overlay', function () {
        $(this).prev('.WPBpanierDropdown').addClass('hidden');
    });

    $('.product-item').hover(
        function() {
            if ($(this).find('img.hover').length > 0) {
                $(this).find('img.default').css('opacity', '0');
                $(this).find('img.hover').css('opacity', '1');
            }
        }, function() {
            if ($(this).find('img.hover').length > 0) {
                $(this).find('img.default').css('opacity', '1');
                $(this).find('img.hover').css('opacity', '0');
            }
        }
    );
});