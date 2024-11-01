<?php

use NF\WPBOUTIK\WPB_Gift_Card;

$product_type = '';
if ( get_post_meta( get_the_ID(), 'type', true ) == 'gift_card' ) {
	$product_type = '"product_type": "53",';
} else {
	$sorted_terms = wpboutik_sort_terms_hierarchy( get_the_terms( get_the_ID(), 'wpboutik_product_cat' ) );
	if ( ! empty( $sorted_terms ) ) {
		$product_type = '"product_type": "' . get_the_title( wpboutik_get_page_id( 'shop' ) );
		foreach ( $sorted_terms as $term ) {
			$product_type .= ' > ' . $term->name;
		}
		$product_type .= '",';
	}
}
$rating = get_comments_count_and_average_rating();
if ( $rating->total_comments != 0 && ! empty( $rating->average_rating ) ) {
	$rating_datas = '
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "' . $rating->average_rating . '",
      "reviewCount": "' . $rating->total_comments . '"
    },';
} else {
	$rating_datas = '';
}

$variants      = get_post_meta( get_the_ID(), 'variants', true );
$variants      = json_decode( $variants );
$variants_json = '';
if ( ! empty( $variants ) ) {
	$variants_json .= '"hasVariant": [';
	foreach ( $variants as $variant ) {
		$variants_json .= '{
        "@type": "Product",
        "sku": "' . wpb_sku_or_default() . '",
        ' . ( empty( $variant->image_temp ) ? '' : '"image": "' . WPBOUTIK_APP_URL . $variant->image_temp . '",' ) . '
        "name": "' . get_the_title() . ' ' . implode( ' ', $variant->name ) . '",
        "offers": {
          "@type": "Offer",
          ' . wpboutik_product_availability( get_the_ID(), $variant->id ) . '
          "url": "' . get_the_permalink() . '",
          "priceCurrency": "' . get_wpboutik_currency() . '",
          "price": ' . $variant->price . '
        }
      },';
	}
	$variants_json = substr_replace( $variants_json, '', - 1 );
	$variants_json .= '],';
}
?>
<script type="application/ld+json">
    {
      "@context": "http://schema.org",
      "@type": "Product",
      "name": "<?= get_the_title() ?>",
  "image": "<?= wpb_get_default_image_url() ?>",
  "description": "<?= get_the_excerpt() ?>",
  "datePublished": "<?= get_post_datetime( get_the_ID(), 'date' )->format( \DateTime::ATOM ) ?>",
	<?= ( $date = get_post_datetime( get_the_ID(), 'modified' ) ) ? '"dateModified": "' . $date->format( \DateTime::ATOM ) . '",' : '' ?>
    "url": "<?= get_the_permalink() ?>",
  "sku": "<?= wpb_sku_or_default() ?>",
	<?= $rating_datas ?>
  <?= $variants_json ?>
  <?= $product_type ?>
    "offers": {
      "@type": "Offer",
      "price": "<?= get_post_meta( get_the_ID(), 'price', true ) ?>",
    "priceCurrency": "<?= get_wpboutik_currency() ?>",
    "availability" : "<?= wpboutik_product_availability( get_the_ID() ) ?>",
    "url": "<?= get_the_permalink() ?>"
  }
}
</script>