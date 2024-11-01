<div class="wpb-back-in-stock">
  <div class="wpb-back-in-stock-header">
    <?php _e( 'Receive an email when the product is back in stock', 'wpboutik' ); ?>
  </div>
  <div class="create_mail_back_in_stock_form wpb-single-product-content">
    <div class="wpb-field">
      <input type="email" name="mail_backinstock" id="mail_backinstock" class="wpb-input">
    </div>
    <button type="submit"
    data-product_id="<?php the_ID(); ?>"
    data-nonce="<?php echo wp_create_nonce( "create-back-in-stock-nonce" ); ?>"
    class="create_mail_back_in_stock wpb-btn">
      <?php _e( 'Subscribe now', 'wpboutik' ); ?>
    </button>
  </div>
</div>
