/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockVariation, updateCategory } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */

/**
 * Internal dependencies
 */
import metadata from './block.json';
import ProductIcon from '../icons/product';
import WPBoutikIcon from '../icons/wp_boutik';

updateCategory(
	'wpboutik', { icon: <WPBoutikIcon/> }
)
registerBlockVariation(
	'core/query', {
    name: metadata.name,
    title: metadata.title,
		category: metadata.category,
		description: metadata.description,
    isActive: ( { namespace, query } ) => {
        return (
            namespace === metadata.name
            && query.postType === 'wpboutik_product'
        );
    },
    icon: <ProductIcon/>,
    attributes: {
        namespace: metadata.name,
        query: {
            perPage: 3,
            pages: 0,
            offset: 0,
            postType: 'wpboutik_product',
            inherit: false
        },
    },
    scope: [ 'inserter' ],
		allowedControls: [ 'taxQuery' ],
		innerBlocks: [
			[
				'core/post-template', {
					layout: {
						type:'grid',
						columnCount:3
					}
				}, [
					['core/pattern', {
						slug: 'wpboutik-pattern/reduced-product'
					}]
				]
			],
			[ 'core/query-pagination' ],
			[ 'core/query-no-results' ],
		],
	}
)