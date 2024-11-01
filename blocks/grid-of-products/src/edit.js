/**
 * WordPress components that create the necessary UI elements for the block
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-components/
 */
import {TextControl} from '@wordpress/components';
import { __experimentalNumberControl as NumberControl } from '@wordpress/components';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {useBlockProps} from '@wordpress/block-editor';

import {RichText} from '@wordpress/rich-text';
import { useEffect } from "@wordpress/element";

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({attributes, setAttributes}) {
    const blockProps = useBlockProps();

    //useEffect( () => {
    //apiFetch({path: '/wp/v2/wpboutik_product?per_page=' . attributes.nb_products}).then(
    apiFetch({path: '/wp/v2/wpboutik_product?per_page=5'}).then(
        (result) => {
            setAttributes({
                products: result
            });
        },
        (error) => {
        }
    )
    //}, [attributes.nb_products] );

    return (
        <div {...blockProps}>
            <TextControl
                label={__('Titre du bloc', 'wpboutik')}
                value={attributes.message}
                onChange={(val) => setAttributes({message: val})}
            />
            <NumberControl
                label={__('Nombre de produits', 'wpboutik')}
                value={attributes.nb_products}
                onChange={(val) => setAttributes({nb_products: val})}
            />
        </div>
    );
}
