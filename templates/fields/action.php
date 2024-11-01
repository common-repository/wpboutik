<?php if ( wpboutik_show_button_cart( get_the_ID() ) ) :
  $options = get_post_meta( get_the_ID(), 'options', true );
  if ( ! empty( $options ) && '[]' != $options ) : ?>
    <a href="<?php the_permalink(); ?>" class="wpb-btn">
      <?php _e( 'Choose options', 'wpboutik' ); ?>
    </a>
  <?php else : ?>
    <button data-nonce="<?php echo wp_create_nonce( "add-to-cart-nonce" ); ?>"
            data-product_id="<?php the_ID(); ?>"
            data-variation_id=""
            data-product_sku="<?php echo get_post_meta( get_the_ID(), 'sku', true ); ?>"
            data-product_name="<?php the_title(); ?>"
            class="wpb-btn wpboutik_archive_add_to_cart_button">
        <?php _e( 'Add to cart', 'wpboutik' ); ?>
      </button>
      <a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'cart' ) ) ); ?>"
          class="view-cartwpb-<?php the_ID(); ?> hidden wpb-link wpb-cart-link">
            <?php _e( 'View cart', 'wpboutik' ); ?>
          <span aria-hidden="true"> &rarr;</span>
      </a>
  <?php endif; 
else : ?>
  <span class="wpb-btn">
    <?php _e( 'View product', 'wpboutik' ); ?>
  </span>
  <span class="wpb-info wpb-soldout">
    <?php _e( 'Out of stock', 'wpboutik' ); ?>
  </span>

<?php endif; ?>