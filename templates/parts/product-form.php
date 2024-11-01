<?php 
  $variations = get_post_meta( get_the_ID(), 'variants', true );
  $has_variations = (!empty($variations) && $variations != '[]');
?>
<form class="cart" id="formcartsingle">
  <?php wpb_field('product-options') ?>
  <?php if ( get_post_meta( get_the_ID(), 'type', true ) == 'gift_card') : 
    wpb_field('gift-card-customization'); 
  endif;
  if (get_post_meta( get_the_ID(), 'type', true ) === 'gift_card') {
    $variations = json_decode($variations);
    $default = $variations[0]->id;
  } else {
    $default = '0';
  } 
  wpb_field('action-single', ['default_variation' => $default, 'has_variations' => $has_variations]);
  ?>
</form>