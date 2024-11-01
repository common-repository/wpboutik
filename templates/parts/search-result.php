<div class="search-result" data-target="search-product-details-<?= get_the_ID() ?>">
  <?php wpb_field('image') ?>
  <div class="product-info">
    <div class="product-header">
      <?php wpb_field('title', ['title_tag' => 'h4']) ?>
    </div>
    <?php wpb_field('price') ?>
  </div>
</div>