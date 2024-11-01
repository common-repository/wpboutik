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
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
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
	const ALLOWED_BLOCKS = [
		'wpboutik/cart-content',
		'wpboutik/cart-total',
		'wpboutik/cart-validate',
		'core/columns',
		'core/group',
		'core/heading',
		'core/paragraph'
	]
	const DEFAULT_TEMPLATE = [
		[ 'core/heading', { level: 2, content: 'Panier' } ],
		[ 'core/columns', {}, [
				 [ 'core/column', {}, [
						 [ 'wpboutik/cart-content', {} ]
				 ] ],
				 [ 'core/column', {}, [
						 [ 'wpboutik/cart-total', {} ],
						 [ 'wpboutik/cart-validate', {} ],
				 ] ],
		] ],
	]
	return (
			<div { ...useBlockProps() }>
				<InnerBlocks allowedBlocks={ ALLOWED_BLOCKS }
										 template={ DEFAULT_TEMPLATE }/>
			</div>
	);
}
