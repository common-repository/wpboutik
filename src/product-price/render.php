<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
if (get_post_type() != 'wpboutik_product')
	return;
?>

<div <?= get_block_wrapper_attributes(); ?>>
	<?php wpb_field('price') ?>
</div>