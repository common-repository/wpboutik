<?php
/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see wpboutik_default_product_tabs()
 */
$product_tabs = apply_filters( 'wpboutik_product_tabs', array() );

if ( ! empty( $product_tabs ) ) : ?>
<!-- TABS CONTAINER -->
<div class="wpb-tabs-container">

  <div class="sm:hidden wpb-field">
    <label for="tabs" class="sr-only">Select a tab</label>
    <select id="tabs" name="tabs">
    <?php foreach ( $product_tabs as $key => $product_tab ) : ?>
    <option
    value="tab-<?php echo esc_attr( $key ); ?>"><?php echo $product_tab['title']; ?></option>
    <?php endforeach; ?>
    </select>
  </div>

  <div class="hidden sm:block">

    <div class="border-b border-gray-200">
      <nav class="navsinglewpb" aria-label="Tabs">
        <?php foreach ( $product_tabs as $key => $product_tab ) : ?>
          <a href="#tab-<?php echo esc_attr( $key ); ?>"
          class="<?php echo ( $key === 'description' ) ? 'active ' : ''; ?> wpb-single-tab">
            <?php echo wp_kses_post( apply_filters( 'wpboutik_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
            <?php if ( isset( $product_tab['count'] ) ) : ?>
              <span>
                <?php echo $product_tab['count']; ?>
              </span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>

  </div>

  <div class="wpboutik-tabs wpb-tabs-wrapper">

    <?php foreach ( $product_tabs as $key => $product_tab ) : ?>
      <div class="wpboutik-Tabs-panel wpboutik-Tabs-panel--<?php echo esc_attr( $key ); ?> panel entry-content wpb-tab" id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
        <?php
        if ( isset( $product_tab['callback'] ) ) {
          call_user_func( $product_tab['callback'], $key, $product_tab );
        }
        ?>
      </div>
    <?php endforeach; ?>

    <?php do_action( 'wpboutik_product_after_tabs' ); ?>
  </div>
</div>
<!-- TABS CONTAINER - END -->

<?php endif; ?>