/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';
import { Button, ButtonGroup } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';
const {useSelect} = wp.data;
import { useEntityProp } from '@wordpress/core-data';
import show_button_cart from '../addToCartTools';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({attributes, setAttributes, context: {postId, postType}}) {
	if (postType != 'wpboutik_product')
		return (
			<div { ...useBlockProps({
				className: 'wp-block-button__link wp-element-button wpboutik_archive_add_to_cart_button'
			}) }>
				{__( 'Add to cart', 'wpboutik' )}
			</div>
		)

	const post = useSelect(
		select => select('core').getEntityRecord('postType','wpboutik_product', postId)
	)
	const [metas] = useEntityProp( 'postType', postType, 'meta', postId);
	const showButton = show_button_cart(metas);
	return (
			<div { ...useBlockProps({
				className: 'wp-block-button__link wp-element-button wpboutik_archive_add_to_cart_button'
			}) }>
				{
					(showButton) ?
						(metas != undefined && metas.variants != '[]' && !!metas.variants) ? 
							__( 'Choose options', 'wpboutik' ) 
						:
							__( 'Add to cart', 'wpboutik' )
					:
						__( 'Out of stock', 'wpboutik' )
				}
			</div>
	);
}
