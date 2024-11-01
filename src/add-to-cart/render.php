<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

if (is_singular( 'wpboutik_product' )) :
	wpb_field('action-single');
else :
	wpb_field('action');
endif; 
?>