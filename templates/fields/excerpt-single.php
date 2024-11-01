<div class="wpb-excerpt single prose">
  <?php if ( has_excerpt() ) :
    echo get_the_excerpt();
  elseif ( $meta_desc = get_post_meta( get_the_ID(), 'meta_description', true ) ) : ?>
      <p>
        <?= $meta_desc; ?>
      </p>
  <?php endif; ?>
</div>