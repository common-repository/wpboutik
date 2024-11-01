(function ( api ) {
    api.panel( 'wpboutik_panel', function( panel ) {
        var previousUrl, clearPreviousUrl, previewUrlValue;
        previewUrlValue = api.previewer.previewUrl;
        clearPreviousUrl = function() {
            previousUrl = null;
        };

        panel.expanded.bind( function( isExpanded ) {
            var url;
            if ( isExpanded ) {
                url = wpboutik.url;
                previousUrl = previewUrlValue.get();
                previewUrlValue.set( url );
                previewUrlValue.bind( clearPreviousUrl );
            } else {
                previewUrlValue.unbind( clearPreviousUrl );
                if ( previousUrl ) {
                    previewUrlValue.set( previousUrl );
                }
            }
        } );
    } );

    api.panel( 'wpboutik_archive_section', function( panel ) {
        var previousUrl, clearPreviousUrl, previewUrlValue;
        previewUrlValue = api.previewer.previewUrl;
        clearPreviousUrl = function() {
            previousUrl = null;
        };

        panel.expanded.bind( function( isExpanded ) {
            var url;
            if ( isExpanded ) {
                url = wpboutik.url;
                previousUrl = previewUrlValue.get();
                previewUrlValue.set( url );
                previewUrlValue.bind( clearPreviousUrl );
            } else {
                previewUrlValue.unbind( clearPreviousUrl );
                if ( previousUrl ) {
                    previewUrlValue.set( previousUrl );
                }
            }
        } );
    } );

    api.section( 'wpboutik_single_section', function( section ) {
        var previousUrl, clearPreviousUrl, previewUrlValue;
        previewUrlValue = api.previewer.previewUrl;
        clearPreviousUrl = function() {
            previousUrl = null;
        };

        section.expanded.bind( function( isExpanded ) {
            var url;
            if ( isExpanded ) {
                url = wpboutik.url_sidebar_section;
                previousUrl = previewUrlValue.get();
                previewUrlValue.set( url );
                previewUrlValue.bind( clearPreviousUrl );
            } else {
                previewUrlValue.unbind( clearPreviousUrl );
                if ( previousUrl ) {
                    previewUrlValue.set( previousUrl );
                }
            }
        } );
    } );

    api.section( 'wpboutik_display_section', function( section ) {
        var previousUrl, clearPreviousUrl, previewUrlValue;
        previewUrlValue = api.previewer.previewUrl;
        clearPreviousUrl = function() {
            previousUrl = null;
        };

        section.expanded.bind( function( isExpanded ) {
            var url;
            if ( isExpanded ) {
                url = wpboutik.url;
                previousUrl = previewUrlValue.get();
                previewUrlValue.set( url );
                previewUrlValue.bind( clearPreviousUrl );
            } else {
                previewUrlValue.unbind( clearPreviousUrl );
                if ( previousUrl ) {
                    previewUrlValue.set( previousUrl );
                }
            }
        } );
    } );

    api.bind('ready', function () {
        var radioInputs = document.querySelectorAll('#customize-control-wpb_choose_cart_icon input[type="radio"]');
        radioInputs.forEach(function (input) {
            var iconClass = input.value;
            var iconHTML = iconClass;
            input.closest('span').querySelector('label').innerHTML = iconHTML;
        });
        var shopPageControl = document.getElementById('customize-control-wpb_link_shop_page');
        if (shopPageControl) {
            shopPageControl.style.display = 'none';
        }
    });
} ( wp.customize ) );