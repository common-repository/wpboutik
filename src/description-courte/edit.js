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
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';
const { useSelect } = wp.data;

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */

const DescriptionSettings = ({attributes, setAttributes}) => (<InspectorControls>
<PanelBody title={__( 'Words number' )}>
	<TextControl
		type="number"
		label={__('Number of word to display')}
		help={__('Leave empty to not limit the number of words to display.')}
		value={attributes.word_count}
		onChange={(newVal) => setAttributes({word_count: newVal})}
	/>
</PanelBody>
</InspectorControls>)

const exemple_description = '<p>Imperial troops have driven the Rebel forces from their hidden base and pursued them across the galaxy.</p>\n<p>Evading the dreaded Imperial Starfleet, a group of freedom fighters led by Luke Skywalker has established a new secret base on the remote ice world of Hoth.</p>\n<p>The evil lord Darth Vader, obsessed with finding young Skywalker, has dispatched thousands of remote probes into the far reaches of space…</p>'

function stripHTML(html) {
	var temporalDivElement = document.createElement("div");
	temporalDivElement.innerHTML = html;
	return temporalDivElement.textContent || temporalDivElement.innerText || "";
}

function with_word_count (text, word_count = '') {
	if (text == '')
		text = exemple_description
	text = stripHTML(text);
	if (word_count == '')
		return text;
	let words = text.split(/\s+/);
	word_count = +word_count;
	if (words.length <= word_count)
		return text;
	return words.slice(0, word_count).join(' ');
}


export default function Edit({attributes, setAttributes, context: {postId, postType}}) {
	if (postType != 'wpboutik_product')
		return (
			<>
				<DescriptionSettings {...{attributes, setAttributes}} />
				<p { ...useBlockProps() }>{with_word_count(exemple_description, attributes.word_count)}</p>
			</>
		)
	const post = useSelect(
		select => select('core').getEntityRecord('postType','wpboutik_product', postId)
	)
	return (
		<>
			<DescriptionSettings {...{attributes, setAttributes}} />
			<div { ...useBlockProps() } 
				 dangerouslySetInnerHTML={
					{
						__html: with_word_count(post.excerpt.raw, attributes.word_count)
					}
				 }>
			</div>
		</>
	);
}
