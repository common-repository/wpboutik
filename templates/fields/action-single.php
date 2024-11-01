<?php if ( WPB()->is_subscription_active() ) : ?>
	<?php if ( wpboutik_show_button_cart( get_the_ID() ) || ( ! empty( $has_variations ) ) ) : ?>
        <div>
            <div class="show_rupture_stock" style="display: none">
                <span class="wpb-info wpb-notif-text wpb-soldout"><?php _e( 'Out of stock', 'wpboutik' ); ?></span>
                <div id="backinstock">
                    <?php wpb_form( 'back-in-stock' ) ?>
                </div>
            </div>
            <div class="show_inactif_option" style="display: none">
                <span class="wpb-info wpb-notif-text wpb-soldout">
                <?php _e( 'Option non disponible', 'wpboutik' ); ?>
                </span>
            </div>
    
            <div class="add_btn_single">
                <style>
                    input[type=number]::-webkit-inner-spin-button,
                    input[type=number]::-webkit-outer-spin-button {
                        opacity: 1;
                    }
                </style>
    
                <?php $continu_rupture = get_post_meta( get_the_ID(), 'continu_rupture', true ); ?>
    
                <div class="<?= ( ! empty( $args['has_variations'] ) && empty( $args['default_variation'] ) ) ? 'hidden choose_qty wpb-field' : 'choose_qty wpb-field'; ?>">
                    <p <?= ( get_theme_mod( 'wpboutik_qty_align', 'calc(50% - .5rem)' ) != '100%' ) ? 'class="sr-only"' : '' ?>><?php _e( 'Quantity', 'wpboutik' ); ?></p>
                    <input type="number" value="1" step="1" min="1"
                           max="<?php echo ( ! empty( $continu_rupture ) && 1 != $continu_rupture ) ? get_post_meta( get_the_ID(), 'qty', true ) : ''; ?>"
                           name="quantity" title="Qté" size="4" placeholder="" inputmode="numeric" autocomplete="off">
                </div>
                <div class="wpb-add-to-cart-container">
                    <input type="hidden" name="variation_id" class="variation_id"
                           value="<?= $args['default_variation'] ? $args['default_variation'] : '' ?>"/>
                    <input type="hidden" name="product_sku"
                           value="<?php echo get_post_meta( get_the_ID(), 'sku', true ); ?>"/>
                    <input type="hidden" name="product_gestion_stock"
                           value="<?php echo ( 1 == get_post_meta( get_the_ID(), 'gestion_stock', true ) && 1 != get_post_meta( get_the_ID(), 'continu_rupture', true ) ) ? '1' : '0'; ?>"/>
                    <input type="hidden" name="product_id" value="<?php the_ID(); ?>"/>
                    <input type="hidden" name="product_name" value="<?php the_title(); ?>"/>
                    <?php if (get_post_meta( get_the_ID(), 'type', true ) == 'plugin') : ?>
                        <input type="hidden" name="abonnement" id="product_subscription" value="" />
                    <?php endif; ?>
                    <button type="submit" data-nonce="<?php echo wp_create_nonce( "add-to-cart-nonce" ); ?>"
                            class="wpb-btn wpboutik_single_add_to_cart_button">
                        <?php _e( 'Add to cart', 'wpboutik' ); ?>
                    </button>
                    <a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'cart' ) ) ); ?>"
                       class="view-cartwpb-<?= get_the_ID() ?> view-cartwpb wpb-link wpb-cart-link hidden">
                        <?php _e( 'View cart', 'wpboutik' ); ?>
                        <span aria-hidden="true"> &rarr;</span>
                    </a>
                    <?php do_action( 'wpboutik_after_add_to_cart_button', get_post( get_the_ID() ) ); ?>
                </div>
            </div>
        </div>
	<?php else : ?>
        <span class="wpb-info wpb-notif-text wpb-soldout">
        <?php _e( 'Out of stock', 'wpboutik' ); ?>
      </span>
      <form id="backinstock">
          <?php wpb_form( 'back-in-stock' ) ?>
      </form>
	<?php endif; ?>
<?php endif; ?>
