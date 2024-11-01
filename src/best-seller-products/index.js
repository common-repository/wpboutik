/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockVariation } from '@wordpress/blocks';

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
import BestSellerIcon from '../icons/best_seller';

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
    icon: <BestSellerIcon/>,
    attributes: {
        namespace: metadata.name,
        query: {
            perPage: 6,
            pages: 0,
            offset: 0,
            postType: 'wpboutik_product',
            inherit: false,
						bestSeller: true
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

registerBlockVariation(
	'core/query', {
    name: 'wpboutik/sticky-product',
    title: 'WPBoutik - sticky product',
		category: metadata.category,
		description: 'affichage des produit mis en avant',
    isActive: ( { namespace, query } ) => {
        return (
            namespace === 'wpboutik/sticky-product'
            && query.postType === 'wpboutik_product'
        );
    },
    icon: <BestSellerIcon/>,
    attributes: {
        namespace: 'wpboutik/sticky-product',
        query: {
            perPage: 6,
            pages: 0,
            offset: 0,
            postType: 'wpboutik_product',
            inherit: false,
						stickyProduct: true
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