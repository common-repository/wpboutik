<?php
/**
 * Product attributes
 *
 * Used by list_attributes() in the products class.
 *
 * This template can be overridden by copying it to yourtheme/wpboutik/single-product/product-attributes.php.
 *
 * HOWEVER, on occasion WPBoutik will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see
 * @package
 * @version
 */

defined( 'ABSPATH' ) || exit;

if ( ! $product_attributes || get_post_meta(get_the_ID(), 'type', true) === 'gift_card') {
	return;
}
?>
<table class="wpb-product-attributes">
	<?php foreach ( $product_attributes as $product_attribute_key => $product_attribute ) : ?>
		<tr class="wpb-product-attributes-item wpb-product-attributes-item--<?php echo esc_attr( $product_attribute_key ); ?>">
			<th class="wpb-product-attributes-item-label"><?php echo wp_kses_post( $product_attribute['label'] ); ?></th>
			<td class="wpb-product-attributes-item-value">
				<?php if ( strpos($product_attribute['value'], 'data:image') ) { 
					$product_attribute['value'];
				} else {
					if($product_attribute['type'] == 'color') {                        
						foreach(explode(',', strip_tags($product_attribute['value'])) as $color) {
							echo '<span style="display: inline-block; width: 2em; height: 2em; background-color: '.$color.'; margin-inline-end: .5em"></span>';
						}
					} else {
						print wp_kses_post( $product_attribute['value'] );
					}
				}  ?></td>
		</tr>
	<?php endforeach; ?>
</table>
