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
export default function Edit({attributes, setAttributes}) {
	return (
		<nav { ...useBlockProps() } aria-label="Breadcrumb">
			<ol role="list">
					<li>
						<a href="#">{ __( 'All products', 'wpboutik' ) }</a>
						<svg viewBox="0 0 6 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M4.878 4.34H3.551L.27 16.532h1.327l3.281-12.19z" fill="currentColor"/>
						</svg>
					</li>


					<li>
						<a href="#" aria-current="page" >{ __('Current page', 'wpboutik') }</a>
					</li>
			</ol>
		</nav>
	);
}
