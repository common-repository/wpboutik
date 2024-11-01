<?php

use NF\WPBOUTIK\WPB_Api_Request;
use NF\WPBOUTIK\WPB_Gift_Card;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Format the postcode according to the country and length of the postcode.
 *
 * @param string $postcode Unformatted postcode.
 * @param string $country Base country.
 *
 * @return string
 */
function wpboutik_format_postcode( $postcode, $country ) {
	$postcode = wpboutik_normalize_postcode( $postcode );

	switch ( $country ) {
		case 'CA':
		case 'GB':
			$postcode = substr_replace( $postcode, ' ', - 3, 0 );
			break;
		case 'IE':
			$postcode = substr_replace( $postcode, ' ', 3, 0 );
			break;
		case 'BR':
		case 'PL':
			$postcode = substr_replace( $postcode, '-', - 3, 0 );
			break;
		case 'JP':
			$postcode = substr_replace( $postcode, '-', 3, 0 );
			break;
		case 'PT':
			$postcode = substr_replace( $postcode, '-', 4, 0 );
			break;
		case 'US':
			$postcode = rtrim( substr_replace( $postcode, '-', 5, 0 ), '-' );
			break;
		case 'NL':
			$postcode = substr_replace( $postcode, ' ', 4, 0 );
			break;
	}

	return apply_filters( 'wpboutik_format_postcode', trim( $postcode ), $country );
}

/**
 * Normalize postcodes.
 *
 * Remove spaces and convert characters to uppercase.
 *
 * @param string $postcode Postcode.
 *
 * @return string
 */
function wpboutik_normalize_postcode( $postcode ) {
	return preg_replace( '/[\s\-]/', '', trim( strtoupper( $postcode ) ) );
}

/**
 * Checks for a valid postcode.
 *
 * @param string $postcode Postcode to validate.
 * @param string $country Country to validate the postcode for.
 *
 * @return bool
 */

function wpb_is_postcode( $postcode, $country ) {
	if ( strlen( trim( preg_replace( '/[\s\-A-Za-z0-9]/', '', $postcode ) ) ) > 0 ) {
		return false;
	}

	switch ( $country ) {
		case 'AT':
			$valid = (bool) preg_match( '/^([0-9]{4})$/', $postcode );
			break;
		case 'BA':
			$valid = (bool) preg_match( '/^([7-8]{1})([0-9]{4})$/', $postcode );
			break;
		case 'BR':
			$valid = (bool) preg_match( '/^([0-9]{5})([-])?([0-9]{3})$/', $postcode );
			break;
		case 'CH':
			$valid = (bool) preg_match( '/^([0-9]{4})$/i', $postcode );
			break;
		case 'DE':
			$valid = (bool) preg_match( '/^([0]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{3}$/', $postcode );
			break;
		case 'ES':
		case 'FR':
		case 'IT':
			$valid = (bool) preg_match( '/^([0-9]{5})$/i', $postcode );
			break;
		case 'GB':
			$valid = wpb_is_gb_postcode( $postcode );
			break;
		case 'HU':
			$valid = (bool) preg_match( '/^([0-9]{4})$/i', $postcode );
			break;
		case 'IE':
			$valid = (bool) preg_match( '/([AC-FHKNPRTV-Y]\d{2}|D6W)[0-9AC-FHKNPRTV-Y]{4}/', wpboutik_normalize_postcode( $postcode ) );
			break;
		case 'JP':
			$valid = (bool) preg_match( '/^([0-9]{3})([-]?)([0-9]{4})$/', $postcode );
			break;
		case 'PT':
			$valid = (bool) preg_match( '/^([0-9]{4})([-])([0-9]{3})$/', $postcode );
			break;
		case 'US':
			$valid = (bool) preg_match( '/^([0-9]{5})(-[0-9]{4})?$/i', $postcode );
			break;
		case 'CA':
			// CA Postal codes cannot contain D,F,I,O,Q,U and cannot start with W or Z. https://en.wikipedia.org/wiki/Postal_codes_in_Canada#Number_of_possible_postal_codes.
			$valid = (bool) preg_match( '/^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])([\ ])?(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$/i', $postcode );
			break;
		case 'PL':
			$valid = (bool) preg_match( '/^([0-9]{2})([-])([0-9]{3})$/', $postcode );
			break;
		case 'CZ':
		case 'SK':
			$valid = (bool) preg_match( '/^([0-9]{3})(\s?)([0-9]{2})$/', $postcode );
			break;
		case 'NL':
			$valid = (bool) preg_match( '/^([1-9][0-9]{3})(\s?)(?!SA|SD|SS)[A-Z]{2}$/i', $postcode );
			break;
		case 'SI':
			$valid = (bool) preg_match( '/^([1-9][0-9]{3})$/', $postcode );
			break;
		case 'LI':
			$valid = (bool) preg_match( '/^(94[8-9][0-9])$/', $postcode );
			break;
		default:
			$valid = true;
			break;
	}

	return apply_filters( 'wpboutik_validate_postcode', $valid, $postcode, $country );
}

/**
 * Check if is a GB postcode.
 *
 * @param string $to_check A postcode.
 *
 * @return bool
 */

function wpb_is_gb_postcode( $to_check ) {

	// Permitted letters depend upon their position in the postcode.
	// https://en.wikipedia.org/wiki/Postcodes_in_the_United_Kingdom#Validation.
	$alpha1 = '[abcdefghijklmnoprstuwyz]'; // Character 1.
	$alpha2 = '[abcdefghklmnopqrstuvwxy]'; // Character 2.
	$alpha3 = '[abcdefghjkpstuw]';         // Character 3 == ABCDEFGHJKPSTUW.
	$alpha4 = '[abehmnprvwxy]';            // Character 4 == ABEHMNPRVWXY.
	$alpha5 = '[abdefghjlnpqrstuwxyz]';    // Character 5 != CIKMOV.

	$pcexp = array();

	// Expression for postcodes: AN NAA, ANN NAA, AAN NAA, and AANN NAA.
	$pcexp[0] = '/^(' . $alpha1 . '{1}' . $alpha2 . '{0,1}[0-9]{1,2})([0-9]{1}' . $alpha5 . '{2})$/';

	// Expression for postcodes: ANA NAA.
	$pcexp[1] = '/^(' . $alpha1 . '{1}[0-9]{1}' . $alpha3 . '{1})([0-9]{1}' . $alpha5 . '{2})$/';

	// Expression for postcodes: AANA NAA.
	$pcexp[2] = '/^(' . $alpha1 . '{1}' . $alpha2 . '[0-9]{1}' . $alpha4 . ')([0-9]{1}' . $alpha5 . '{2})$/';

	// Exception for the special postcode GIR 0AA.
	$pcexp[3] = '/^(gir)(0aa)$/';

	// Standard BFPO numbers.
	$pcexp[4] = '/^(bfpo)([0-9]{1,4})$/';

	// c/o BFPO numbers.
	$pcexp[5] = '/^(bfpo)(c\/o[0-9]{1,3})$/';

	// Load up the string to check, converting into lowercase and removing spaces.
	$postcode = strtolower( $to_check );
	$postcode = str_replace( ' ', '', $postcode );

	// Assume we are not going to find a valid postcode.
	$valid = false;

	// Check the string against the six types of postcodes.
	foreach ( $pcexp as $regexp ) {
		if ( preg_match( $regexp, $postcode, $matches ) ) {
			// Remember that we have found that the code is valid and break from loop.
			$valid = true;
			break;
		}
	}

	return $valid;
}

/**
 * Validates a phone number using a regular expression.
 *
 * @param string $phone Phone number to validate.
 *
 * @return bool
 */

function wpb_is_phone( $phone ) {
	if ( 0 < strlen( trim( preg_replace( '/[\s\#0-9_\-\+\/\(\)\.]/', '', $phone ) ) ) ) {
		return false;
	}

	return true;
}

function wpboutik_get_order( $order_id, $wp_user_id = '' ) {
	$options = get_option( 'wpboutik_options' );

	if ( empty( $wp_user_id ) ) {
		$wp_user_id = get_current_user_id();
	}

	$api_request = WPB_Api_Request::request( 'order', 'get' )
	                              ->add_multiple_to_body( [
		                              'options'    => $options,
		                              'wp_user_id' => $wp_user_id,
		                              'order_id'   => $order_id,
	                              ] )->exec();

	if ( ! $api_request->is_error() ) {
		$response = (array) json_decode( $api_request->get_response_body() );
	}

	return $response;
}

function wpboutik_get_data_info() {
	$options = get_option( 'wpboutik_options' );

	if ( false === ( $response = get_transient( 'wpboutik_data_info' ) ) ) {
		$api_request = WPB_Api_Request::request( 'data_info' )
		                              ->add_to_body( 'options', $options )
		                              ->exec();

		$response = (array) json_decode( $api_request->get_response_body() );

		set_transient( 'wpboutik_data_info', $response, HOUR_IN_SECONDS );
	}

	return $response;
}

function wpb_get_default_image( $classes = 'h-full w-full object-center lg:h-full lg:w-full', $id = false, $variant_id = false ) {
	$post_id = ( $id ) ? $id : get_the_ID();
	if ( get_post_meta( $post_id, 'type', true ) == 'gift_card' ) {
		$options = get_post_meta( $post_id, 'options', true );
		$options = json_decode( $options );
		if ( $options ) {
			foreach ( $options as $opt ) {
				if ( $opt->id == 'opt_visuel_gc' ) {
					if ( ! empty( $opt->values ) ) {
						if ( $variant_id ) {
							$image_id = explode( '-', $variant_id )[1];
							foreach ( $opt->values as $val ) {
								if ( $val->id == $image_id ) {
									$img_url = $val->value;
								}
							}
						} else {
							$img_url = $opt->values[0]->value;
						}
						ob_start();
						?>
                        <img alt="<?= get_the_title() ?>" src="<?= $img_url ?>" class="<?= $classes ?> object-contain"/>
						<?php
						return ob_get_clean();
					}
				}
			}
		}
	}
	if ( get_theme_mod( 'wpboutik_default_image' ) ) {
		$default_img = get_theme_mod( 'wpboutik_default_image' );

		return '<img src="' . esc_url( $default_img ) . '" alt="Default image" class="' . $classes . '">';
	}

	return '<div class="' . $classes . '"></div>';
}

function wpb_get_default_image_url( $id = false, $variant_id = false ) {
	$post_id = ( $id ) ? $id : get_the_ID();
	if ( get_post_meta( $post_id, 'type', true ) == 'gift_card' ) {
		$options = get_post_meta( $post_id, 'options', true );
		$options = json_decode( $options );
		if ( $options ) {
			foreach ( $options as $opt ) {
				if ( $opt->id == 'opt_visuel_gc' ) {
					if ( ! empty( $opt->values ) ) {
						if ( $variant_id ) {
							$image_id = explode( '-', $variant_id )[1];
							foreach ( $opt->values as $val ) {
								if ( $val->id == $image_id ) {
									$img_url = $val->value;
								}
							}
						} else {
							$img_url = $opt->values[0]->value;
						}

						return $img_url;
					}
				}
			}
		}
	}

	if ( has_post_thumbnail( $post_id ) ) :
		return get_the_post_thumbnail_url( $post_id, 'large' );
	endif;
	$images = get_post_meta( $post_id, 'galerie_images', true );
	if ( $images ) :
		$images = explode( ',', $images );
		echo wp_get_attachment_image_url( $images[0], 'large' );
	endif;

	if ( get_theme_mod( 'wpboutik_default_image' ) ) {
		$default_img = get_theme_mod( 'wpboutik_default_image' );

		return esc_url( $default_img );
	}

	return '';
}

function get_comments_count_and_average_rating( $id = false ) {
	$post_id = ( $id ) ? $id : get_the_ID();
	global $wpdb;

	$query = $wpdb->prepare( "
			SELECT COUNT(*) AS total_comments, AVG(meta_value) AS average_rating
			FROM {$wpdb->comments}
			INNER JOIN {$wpdb->commentmeta} ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
			WHERE {$wpdb->comments}.comment_post_ID = %d
			AND {$wpdb->commentmeta}.meta_key = 'rating'
			AND {$wpdb->comments}.comment_approved = 1
	", $post_id );

	$result = $wpdb->get_row( $query );

	return $result;
}

function wpb_sku_or_default( $id = false ) {
	$post_id = ( $id ) ? $id : get_the_ID();
	$sku     = get_post_meta( $post_id, 'sku', true );
	if ( ! empty( $sku ) ) {
		return $sku;
	} else {
		return '#' . $post_id;
	}
}

function display_categories_with_post_count_recursive( $current_term_slug, $parent_id = 0, $indent = 0 ) {
	$output     = '';
	$categories = get_terms( [
		'taxonomy'   => 'wpboutik_product_cat',
		'hide_empty' => true,
		'orderby'    => 'name',
		'parent'     => $parent_id
	] );

	if ( $categories ) {
		foreach ( $categories as $category ) {
			$selected = '';
			if ( ! empty( $current_term_slug ) && $category->slug === $current_term_slug ) {
				$selected = 'selected';
			}
			$output .= '<option value="' . $category->term_id . '" ' . $selected . ' data-link="' . esc_url( get_term_link( $category ) ) . '">' . str_repeat( '-', $indent ) . $category->name . ' (' . $category->count . ')' . '</option>';
			$output .= display_categories_with_post_count_recursive( $current_term_slug, $category->term_id, $indent + 1 );
		}
	}

	return $output;
}

function wpb_get_select_product_cat() {
	global $wp_query, $post;

	$dropdown_args = array(
		'hide_empty' => 1,
	);

	$current_cat   = false;
	$cat_ancestors = array();

	if ( is_tax( 'wpboutik_product_cat' ) ) {
		$current_cat   = $wp_query->queried_object;
		$cat_ancestors = get_ancestors( $current_cat->term_id, 'wpboutik_product_cat' );

	} elseif ( is_singular( 'wpboutik_product' ) ) {

		$args_terms = apply_filters(
			'wpboutik_product_categories_widget_product_terms_args',
			array(
				'orderby' => 'parent',
				'order'   => 'DESC',
			)
		);

		$cache_key = 'wpb_wpboutik_product_cat' . md5( wp_json_encode( $args_terms ) );
		$terms     = wp_cache_get( $cache_key, 'product_' . $post->ID );

		if ( false === $terms ) {
			$terms = wp_get_post_terms( $post->ID, 'wpboutik_product_cat', $args_terms );
			wp_cache_add( $cache_key, $terms, 'product_' . $post->ID );
		}

		if ( $terms ) {
			$main_term     = apply_filters( 'wpboutik_product_categories_widget_main_term', $terms[0], $terms );
			$current_cat   = $main_term;
			$cat_ancestors = get_ancestors( $main_term->term_id, 'wpboutik_product_cat' );
		}
	}

	wpb_product_dropdown_categories(
		apply_filters(
			'wpboutik_product_categories_widget_dropdown_args',
			wp_parse_args(
				$dropdown_args,
				array(
					'show_count'         => 1,
					'hierarchical'       => 1,
					'show_uncategorized' => 0,
					'selected'           => $current_cat ? $current_cat->slug : '',
				)
			)
		)
	);

	wp_enqueue_script( 'selectWpb' );
	wp_enqueue_style( 'select2wpb' );

	wpb_enqueue_js(
		"
				jQuery( '.dropdown_wpboutik_product_cat' ).on( 'change', function() {
					if ( jQuery(this).val() != '' ) {
						var this_page = '';
						var home_url  = '" . esc_js( home_url( '/' ) ) . "';
						if ( home_url.indexOf( '?' ) > 0 ) {
							this_page = home_url + '&wpboutik_product_cat=' + jQuery(this).val();
						} else {
							this_page = home_url + '?wpboutik_product_cat=' + jQuery(this).val();
						}
						location.href = this_page;
					} else {
						location.href = '" . esc_js( wpboutik_get_page_permalink( 'shop' ) ) . "';
					}
				});

				if ( jQuery().selectWPB ) {
					var wpb_product_cat_select = function() {
						jQuery( '.dropdown_wpboutik_product_cat' ).selectWPB( {
							placeholder: '" . esc_js( __( 'Toutes les catégories', 'wpboutik' ) ) . "',
							minimumResultsForSearch: 5,
							width: '100%',
							allowClear: true,
							language: {
								noResults: function() {
									return '" . esc_js( _x( 'No matches found', 'enhanced select', 'wpboutik' ) ) . "';
								}
							}
						} );
					};
					wpb_product_cat_select();
				}
			"
	);

	/*$categories = get_terms( [
		'taxonomy'   => 'wpboutik_product_cat',
		'hide_empty' => true,
		'orderby'    => 'name',
		'parent'     => 0
	] );

	$current_term_slug = '';
	if ( is_tax( 'wpboutik_product_cat' ) ) {
		$current_term      = get_queried_object();
		$current_term_slug = $current_term->slug;
	}
	if ( $categories ) {
		$select = '<select name="cat" id="cat" class="mt-2 block max-w-sm rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">';
		$select .= "<option value='-1' data-link='" . esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ) . "'>" . __( 'Toutes les catégories', 'wpboutik' ) . "</option>";

		foreach ( $categories as $category ) {
			$selected = '';
			if ( ! empty( $current_term_slug ) && $category->slug === $current_term_slug ) {
				$selected = 'selected';
			}
			$select .= '<option value="' . $category->term_id . '" ' . $selected . ' data-link="' . esc_url( get_term_link( $category ) ) . '">' . str_repeat( '--', $category->depth ) . $category->name . ' (' . $category->count . ')' . '</option>';

			$select .= display_categories_with_post_count_recursive( $current_term_slug, $category->term_id, 1 );
		}

		$select .= "</select>";

		echo $select; ?>
        <script type="text/javascript">
            var dropdown = document.getElementById("cat");

            function onCatChange() {
                var link = jQuery(this).find(':selected').data('link');
                location.href = link;
            }

            dropdown.onchange = onCatChange;
        </script>
		<?php
	}*/
}

function wpb_get_select_ordering() {
	$catalog_orderby_options = apply_filters(
		'wpb_catalog_orderby',
		array(
			'default'    => __( 'Default sorting', 'wpboutik' ),
			//'menu_order' => __( 'Sort by menu sorting', 'wpboutik' ),
			//'popularity' => __( 'Sort by popularity', 'wpboutik' ),
			//'rating'     => __( 'Sort by average rating', 'wpboutik' ),
			//'date'       => __( 'Sort by latest', 'wpboutik' ),
			'price'      => __( 'Sort by price: low to high', 'wpboutik' ),
			'price-desc' => __( 'Sort by price: high to low', 'wpboutik' ),
		)
	);

	global $wp;
	$current_url = home_url( add_query_arg( array(), $wp->request ) );

	$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; ?>
    <select name="orderby" id="orderby"
            aria-label="<?php esc_attr_e( 'Shop order', 'wpboutik' ); ?>">
		<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
            <option
                    value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
		<?php endforeach; ?>
    </select>
    <script type="text/javascript">
        var dropdown = document.getElementById("orderby");

        function onOrderbyChange() {
            var link = jQuery(this).find(':selected').val();
            location.href = "<?php echo $current_url . '/?paged=1&orderby='; ?>" + link;
        }

        dropdown.onchange = onOrderbyChange;
    </script>
	<?php
}

function wpboutik_sort_terms_hierarchy( $terms, $parent = 0 ) {
	$tree = array();
	if ( empty( $terms ) ) {
		return $tree;
	}
	foreach ( $terms as $term ) {
		if ( $term->parent == $parent ) {
			$children = wpboutik_sort_terms_hierarchy( $terms, $term->term_id );
			if ( $children ) {
				$term->children = $children;
			}
			$tree[] = $term;
		}
	}

	return $tree;
}

function wpboutik_print_terms_hierarchy( $terms ) {
	$term_id = get_queried_object()->term_id;
	foreach ( $terms as $term ) : ?>
        <li>
            <a href="<?php echo esc_url( esc_url( get_term_link( $term ) ) ); ?>">
				<?php echo $term->name; ?>
            </a>
            <svg viewBox="0 0 6 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M4.878 4.34H3.551L.27 16.532h1.327l3.281-12.19z" fill="currentColor"/>
            </svg>
        </li>
		<?php

		if ( isset( $term->children ) && ! empty( $term->children ) ) {
			if ( ! is_singular( 'wpboutik_product' ) ) {
				$show = false;
				foreach ( $term->children as $term_children ) {
					if ( $term_id == $term_children->term_id ) {
						$show = true;
						break;
					}
				}

				if ( $show ) {
					wpboutik_print_terms_hierarchy( $term->children );
				}
			} else {
				wpboutik_print_terms_hierarchy( $term->children );
			}
		}
	endforeach;
}

function wpboutik_show_button_cart( $post_id ) {
	if ( ! WPB()->is_subscription_active() ) {
		return false;
	}
	$showbtn = true;
	// Si gestion de stock
	$gestion_stock = get_post_meta( $post_id, 'gestion_stock', true );
	if ( $gestion_stock == 1 ) {
		// Check si on continu de vendre en cas de rupture
		$continu_rupture = get_post_meta( $post_id, 'continu_rupture', true );
		if ( $continu_rupture == 1 ) {
			return $showbtn;
		}

		$variants = get_post_meta( $post_id, 'variants', true );
		if ( ! empty( $variants ) && '[]' != $variants ) {
			$showbtn = false;
			foreach ( json_decode( $variants ) as $variation ) {
				if ( (int) $variation->quantity !== 0 ) {
					$showbtn = true;
					break;
				}
			}
		} else {
			$qty = get_post_meta( $post_id, 'qty', true );
			if ( $qty < 1 ) {
				$showbtn = false;
			}
		}

		return $showbtn;
	}

	return $showbtn;
}

function wpboutik_product_availability( $id = false, $variant_id = false ) {
	$post_id = ( $id ) ? $id : get_the_ID();
	if ( ! WPB()->is_subscription_active() ) {
		return "https://schema.org/SoldOut";
	}
	// Si gestion de stock
	if ( get_post_meta( $post_id, 'gestion_stock', true ) == 1 ) {
		// Check si on continu de vendre en cas de rupture
		$continu_rupture = get_post_meta( $post_id, 'continu_rupture', true ) == 1;
		$variants        = json_decode( get_post_meta( $post_id, 'variants', true ) );
		if ( $variant_id ) {
			$variant = wpboutik_get_variation_by_id( $variants, $variant_id );
			if ( $variant->quantity < 1 ) {
				return ( ( $continu_rupture ) ? "https://schema.org/BackOrder" : "https://schema.org/OutOfStock" );
			} else {
				return "https://schema.org/InStock";
			}
		}
		$qty = get_post_meta( $post_id, 'qty', true );
		if ( ! empty ( $variants ) ) {
			$response = false;
			foreach ( $variants as $variant ) {
				if ( $variant->quantity > 0 ) {
					return "https://schema.org/InStock";
				}
			}
			if ( ! $response ) {
				return ( ( $continu_rupture ) ? "https://schema.org/BackOrder" : "https://schema.org/OutOfStock" );
			}
		}
		if ( $qty < 1 ) {
			return ( ( $continu_rupture ) ? "https://schema.org/BackOrder" : "https://schema.org/OutOfStock" );
		} else {
			return "https://schema.org/InStock";
		}
	}

	return "https://schema.org/InStock";
}

function wpboutik_get_variation_by_id( $variants, $id ) {
	foreach ( $variants as $variant ) {
		if ( $variant->id === $id ) {
			return $variant;
		}
	}

	return null;
}

function wpboutik_upload_invoice( $invoice_link, $order_id, $order_status, $type = '' ) {
	$ch = curl_init( $invoice_link );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	$contenu_fichier = curl_exec( $ch );
	curl_close( $ch );

	$year  = date( "Y" );
	$month = date( "m" );

	if ( ! empty( $type ) ) {
		if ( 'credit' === $type ) {
			$dossier_destination = wp_upload_dir()['basedir'] . '/wpboutik/credit/' . $year . '/' . $month . '/';
		} else {
			$dossier_destination = wp_upload_dir()['basedir'] . '/wpboutik/invoice/' . $year . '/' . $month . '/';
		}
	} else {
		if ( 'refunded' === $order_status ) {
			$dossier_destination = wp_upload_dir()['basedir'] . '/wpboutik/credit/' . $year . '/' . $month . '/';
		} else {
			$dossier_destination = wp_upload_dir()['basedir'] . '/wpboutik/invoice/' . $year . '/' . $month . '/';
		}
	}
	if ( ! file_exists( $dossier_destination ) ) {
		wp_mkdir_p( $dossier_destination );
	}

	$nom_fichier = basename( $invoice_link );

	$chemin_destination = $dossier_destination . $nom_fichier;

	if ( file_put_contents( $chemin_destination, $contenu_fichier ) ) {
		$fichier_attachement = array(
			'post_title'     => $nom_fichier,
			'post_content'   => '',
			'post_mime_type' => 'application/pdf',
			'post_status'    => 'inherit'
		);

		$attachement_id = wp_insert_attachment( $fichier_attachement, $chemin_destination );
		if ( ! empty( $type ) ) {
			if ( 'credit' === $type ) {
				update_post_meta( $attachement_id, 'wpb_credit', true );

				// Send url wp_refund_link in WPBoutik
				$api_request = WPB_Api_Request::request( 'refund_link' )
				                              ->add_multiple_to_body( [
					                              'options'         => get_option( 'wpboutik_options' ),
					                              'order_id'        => $order_id,
					                              'wpb_refund_link' => wp_get_attachment_url( $attachement_id )
				                              ] )->exec();

			} else {
				update_post_meta( $attachement_id, 'wpb_invoice', true );

				$api_request = WPB_Api_Request::request( 'invoice_link' )
				                              ->add_multiple_to_body( [
					                              'options'          => get_option( 'wpboutik_options' ),
					                              'order_id'         => $order_id,
					                              'wpb_invoice_link' => wp_get_attachment_url( $attachement_id )
				                              ] )->exec();
			}
		} else {
			if ( 'refunded' === $order_status ) {
				update_post_meta( $attachement_id, 'wpb_credit', true );

				// Send url wp_refund_link in WPBoutik
				$api_request = WPB_Api_Request::request( 'refund_link' )
				                              ->add_multiple_to_body( [
					                              'options'         => get_option( 'wpboutik_options' ),
					                              'order_id'        => $order_id,
					                              'wpb_refund_link' => wp_get_attachment_url( $attachement_id )
				                              ] )->exec();
			} else {
				update_post_meta( $attachement_id, 'wpb_invoice', true );

				$api_request = WPB_Api_Request::request( 'invoice_link' )
				                              ->add_multiple_to_body( [
					                              'options'          => get_option( 'wpboutik_options' ),
					                              'order_id'         => $order_id,
					                              'wpb_invoice_link' => wp_get_attachment_url( $attachement_id )
				                              ] )->exec();
			}
		}

		return $attachement_id;
	}
}

/**
 * WPBoutik Date Format - Allows to change date format for everything WPBoutik.
 *
 * @return string
 */
function wpboutik_date_format() {
	$date_format = get_option( 'date_format' );
	if ( empty( $date_format ) ) {
		// Return default date format if the option is empty.
		$date_format = 'F j, Y';
	}

	return apply_filters( 'wpboutik_date_format', $date_format );
}

/**
 * Returns formatted dimensions.
 */
function wpboutik_product_get_dimensions( $product ) {

	$width  = get_post_meta( $product->ID, 'width', true );
	$height = get_post_meta( $product->ID, 'height', true );
	$length = get_post_meta( $product->ID, 'length', true );

	return array(
		'length' => $length,
		'width'  => $width,
		'height' => $height,
	);
}

/**
 * Returns whether or not the product has dimensions set.
 *
 * @return bool
 */
function wpboutik_product_has_dimensions( $product ) {
	$width  = get_post_meta( $product->ID, 'width', true );
	$height = get_post_meta( $product->ID, 'height', true );
	$length = get_post_meta( $product->ID, 'length', true );

	//&& ! $this->get_virtual()
	return ( $length || $height || $width );
}

/**
 * Format dimensions for display.
 */
function wpboutik_format_dimensions( $dimensions ) {
	$dimension_string = implode( ' &times; ', array_filter( array_map( 'wpboutik_format_localized_decimal', $dimensions ) ) );

	if ( ! empty( $dimension_string ) ) {
		// get_option( 'wpboutik_dimension_unit' )
		$dimension_string .= ' ' . 'cm';
	} else {
		$dimension_string = __( 'N/A', 'wpboutik' );
	}

	return apply_filters( 'wpboutik_format_dimensions', $dimension_string, $dimensions );
}

/**
 * Format a decimal with the decimal separator for prices or PHP Locale settings.
 *
 * @param string $value Decimal to localize.
 *
 * @return string
 */
function wpboutik_format_localized_decimal( $value ) {
	$locale        = localeconv();
	$decimal_point = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';
	$decimal       = ( ! empty( wpb_get_price_decimal_separator() ) ) ? wpb_get_price_decimal_separator() : $decimal_point;

	return apply_filters( 'wpboutik_format_localized_decimal', str_replace( '.', $decimal, strval( $value ) ), $value );
}

/**
 * Format a weight for display.
 *
 * @param float $weight Weight.
 *
 * @return string
 */
function wpboutik_format_weight( $weight, $product ) {
	$weight_string = wpboutik_format_localized_decimal( $weight );

	if ( ! empty( $weight_string ) ) {
		$weight_unit   = get_post_meta( $product->ID, 'weight_unit', true );
		$weight_string .= ' ' . $weight_unit;
	} else {
		$weight_string = __( 'N/A', 'wpboutik' );
	}

	return apply_filters( 'wpboutik_format_weight', $weight_string, $weight );
}

function wpboutik_format_number( $number ) {
	if ( floor( $number ) == $number ) {
		return number_format( $number, 0, ',', ' ' );
	} else {
		return number_format( $number, 2, ',', ' ' );
	}
}

function wpboutik_get_min_max_prices( $array ) {
	$min_price = PHP_INT_MAX;
	$max_price = 0;
	foreach ( $array as $item ) {
		if ( $item->status == "1" && ! empty( $item->price ) ) {
			if ( isset( $item->price ) && $item->price < $min_price ) {
				$min_price = $item->price;
			}
			if ( isset( $item->price ) && $item->price > $max_price ) {
				$max_price = $item->price;
			}
		}
	}

	if ( $min_price == PHP_INT_MAX ) {
		$min_price = 0;
	}

	return array( "min_price" => $min_price, "max_price" => $max_price );
}

/**
 * Checks if a user (by email or ID or both) has bought an item.
 *
 * @param string $customer_email Customer email to check.
 * @param int $user_id User ID to check.
 * @param int $product_id Product ID to check.
 *
 * @return bool
 */
function wpboutik_customer_bought_product( $customer_email, $user_id, $product_id ) {
	global $wpdb;

	$result = apply_filters( 'wpboutik_pre_customer_bought_product', null, $customer_email, $user_id, $product_id );

	if ( null !== $result ) {
		return $result;
	}

	$transient_name  = 'wpboutik_customer_bought_product_' . md5( $customer_email . $user_id );
	$transient_value = get_transient( $transient_name );
	delete_transient( $transient_name );

	if ( isset( $transient_value['value'] ) ) {
		$result = $transient_value['value'];
	} else {
		/*$customer_data = array( $user_id );

		if ( $user_id ) {
			$user = get_user_by( 'id', $user_id );

			if ( isset( $user->user_email ) ) {
				$customer_data[] = $user->user_email;
			}
		}

		if ( is_email( $customer_email ) ) {
			$customer_data[] = $customer_email;
		}

		$customer_data = array_map( 'esc_sql', array_filter( array_unique( $customer_data ) ) );
		$statuses      = array_map( 'esc_sql', array( 'processing', 'completed' ) );

		if ( count( $customer_data ) === 0 ) {
			return false;
		}*/

		$current_page = empty( $current_page ) ? 1 : absint( $current_page );

		$options     = get_option( 'wpboutik_options' );
		$api_request = WPB_Api_Request::request( 'customer', 'orders' )
		                              ->add_multiple_to_body( [
			                              'options'    => $options,
			                              'wp_user_id' => $user_id,
			                              'page'       => $current_page,
			                              'paginate'   => true,
			                              'status'     => array( 'processing', 'completed' )
		                              ] )->exec();

		$result = [];
		if ( ! $api_request->is_error() ) {
			$response = (array) json_decode( $api_request->get_response_body() );
			$products = ( empty( $response['products'] ) ) ? [] : $response['products'];
			foreach ( $products as $products_order ) {
				foreach ( $products_order as $product ) {
					$result[] = $product->wp_product_id;
				}
			}
		}

		$transient_value = array(
			'value' => $result,
		);

		set_transient( $transient_name, $transient_value, DAY_IN_SECONDS * 30 );
	}

	return in_array( absint( $product_id ), $result, true );
}

function wpboutik_paginate_links( $args = '' ) {
	global $wp_query, $wp_rewrite;
	$backgroundcolor = wpboutik_get_backgroundcolor_button();

	// Setting up default values based on the current URL.
	$pagenum_link = html_entity_decode( get_pagenum_link() );
	$url_parts    = explode( '?', $pagenum_link );

	// Get max pages and current page out of the current query, if available.
	if ( ! empty( $args['total'] ) ) {
		$total = $args['total'];
	} else {
		$total = isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1;
	}
	$current = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1;

	// Append the format placeholder to the base URL.
	$pagenum_link = trailingslashit( $url_parts[0] ) . '%_%';

	// URL base depends on permalink settings.
	$format = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
	$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';

	$defaults = array(
		'base'               => $pagenum_link,
		// http://example.com/all_posts.php%_% : %_% is replaced by format (below).
		'format'             => $format,
		// ?page=%#% : %#% is replaced by the page number.
		'total'              => $total,
		'current'            => $current,
		'aria_current'       => 'page',
		'show_all'           => false,
		'prev_next'          => true,
		'prev_text'          => __( 'Previous' ),
		'next_text'          => __( 'Next' ),
		'end_size'           => 1,
		'mid_size'           => 2,
		'type'               => 'list',
		'add_args'           => array(),
		// Array of query args to add.
		'add_fragment'       => '',
		'before_page_number' => '',
		'after_page_number'  => '',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! is_array( $args['add_args'] ) ) {
		$args['add_args'] = array();
	}

	// Merge additional query vars found in the original URL into 'add_args' array.
	if ( isset( $url_parts[1] ) ) {
		// Find the format argument.
		$format       = explode( '?', str_replace( '%_%', $args['format'], $args['base'] ) );
		$format_query = isset( $format[1] ) ? $format[1] : '';
		wp_parse_str( $format_query, $format_args );

		// Find the query args of the requested URL.
		wp_parse_str( $url_parts[1], $url_query_args );

		// Remove the format argument from the array of query arguments, to avoid overwriting custom format.
		foreach ( $format_args as $format_arg => $format_arg_value ) {
			unset( $url_query_args[ $format_arg ] );
		}

		$args['add_args'] = array_merge( $args['add_args'], urlencode_deep( $url_query_args ) );
	}

	// Who knows what else people pass in $args.
	$total = (int) $args['total'];
	if ( $total < 2 ) {
		return;
	}
	$current  = (int) $args['current'];
	$end_size = (int) $args['end_size']; // Out of bounds? Make it the default.
	if ( $end_size < 1 ) {
		$end_size = 1;
	}
	$mid_size = (int) $args['mid_size'];
	if ( $mid_size < 0 ) {
		$mid_size = 2;
	}

	$add_args   = $args['add_args'];
	$r          = '';
	$page_links = array();
	$dots       = false;

	if ( $args['prev_next'] && $current && 1 < $current ) :
		$link = str_replace( '%_%', 2 == $current ? '' : $args['format'], $args['base'] );
		$link = str_replace( '%#%', $current - 1, $link );
		if ( $add_args ) {
			$link = add_query_arg( $add_args, $link );
		}
		$link .= $args['add_fragment'];

		$page_links[] = sprintf(
			'<a href="%s" class="wpb-btn wpb-lined wpb-page">%s</a>',
			esc_url( apply_filters( 'wpboutik_paginate_links', $link ) ),
			$args['prev_text']
		);
	endif;

	for ( $n = 1; $n <= $total; $n ++ ) :
		if ( $n == $current ) :
			$page_links[] = sprintf(
				'<a href="#" aria-current="%s" class="wpb-btn wpb-lined wpb-page current-page">%s</a>',
				esc_attr( $args['aria_current'] ),
				$args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number']
			);

			$dots = true;
		else :
			if ( $args['show_all'] || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
				$link = str_replace( '%_%', 1 == $n ? '' : $args['format'], $args['base'] );
				$link = str_replace( '%#%', $n, $link );
				if ( $add_args ) {
					$link = add_query_arg( $add_args, $link );
				}
				$link .= $args['add_fragment'];

				$page_links[] = sprintf(
					'<a href="%s" class="wpb-btn wpb-lined wpb-page">%s</a>',
					/** This filter is documented in wp-includes/general-template.php */
					esc_url( apply_filters( 'wpboutik_paginate_links', $link ) ),
					$args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number']
				);

				$dots = true;
            elseif ( $dots && ! $args['show_all'] ) :
				$page_links[] = '<span class="wpb-btn wpb-lined wpb-page more">...</span>';

				$dots = false;
			endif;
		endif;
	endfor;

	if ( $args['prev_next'] && $current && $current < $total ) :
		$link = str_replace( '%_%', $args['format'], $args['base'] );
		$link = str_replace( '%#%', $current + 1, $link );
		if ( $add_args ) {
			$link = add_query_arg( $add_args, $link );
		}
		$link .= $args['add_fragment'];

		$page_links[] = sprintf(
			'<a href="%s" class="wpb-btn wpb-lined wpb-page">%s</a>',
			/** This filter is documented in wp-includes/general-template.php */
			esc_url( apply_filters( 'wpboutik_paginate_links', $link ) ),
			$args['next_text']
		);
	endif;

	switch ( $args['type'] ) {
		case 'array':
			return $page_links;

		case 'list':
			$r .= '<nav aria-label="Pagination" class="wpb-pagination">';
			$r .= implode( " ", $page_links );
			$r .= "</nav>";
			break;

		default:
			$r = implode( "\n", $page_links );
			break;
	}

	/**
	 * Filters the HTML output of paginated links for archives.
	 *
	 * @param string $r HTML output.
	 * @param array $args An array of arguments. See paginate_links()
	 *                     for information on accepted arguments.
	 */
	$r = apply_filters( 'wpboutik_paginate_links_output', $r, $args );

	return $r;
}

function wpboutik_in_array_r( $needle, $array ) {
	if ( ! is_array( $array ) ) {
		return false;
	}

	foreach ( $array as $object ) {
		$object = (object) $object;
		if ( strpos( $object->value, $needle ) !== false ) {
			return true;
		}
	}

	return false;
}

function wpboutikCheckCommonElements( $array1, $array2 ) {
	foreach ( $array1 as $value1 ) {
		if ( is_array( $value1 ) ) {
			foreach ( $value1 as $subValue1 ) {
				if ( in_array( $subValue1, $array2 ) ) {
					return true;
				}
			}
		} else {
			if ( in_array( $value1, $array2 ) ) {
				return true;
			}
		}
	}

	return false;
}


/**
 * Retrieve page permalink.
 *
 * @param string $page page slug.
 * @param string|bool $fallback Fallback URL if page is not set. Defaults to home URL. @since 3.4.0.
 *
 * @return string
 */
function wpboutik_get_page_permalink( $page, $fallback = null ) {
	$page_id   = wpboutik_get_page_id( $page );
	$permalink = 0 < $page_id ? get_permalink( $page_id ) : '';

	if ( ! $permalink ) {
		$permalink = is_null( $fallback ) ? get_home_url() : $fallback;
	}

	return apply_filters( 'wpboutik_get_' . $page . '_page_permalink', $permalink );
}

/**
 * Get endpoint URL.
 *
 * Gets the URL for an endpoint, which varies depending on permalink settings.
 *
 * @param string $endpoint Endpoint slug.
 * @param string $value Query param value.
 * @param string $permalink Permalink.
 *
 * @return string
 */
function wpboutik_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
	if ( ! $permalink ) {
		$permalink = get_permalink();
	}

	// Map endpoint to options.
	$query_vars = ( new NF\WPBOUTIK\Query )->get_query_vars();
	$endpoint   = ! empty( $query_vars[ $endpoint ] ) ? $query_vars[ $endpoint ] : $endpoint;
	$value      = ( get_option( 'wpboutik_myaccount_edit_address_endpoint', 'edit-address' ) === $endpoint ) ? wpboutik_edit_address_i18n( $value ) : $value;

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );
		} else {
			$query_string = '';
		}
		$url = trailingslashit( $permalink );

		if ( $value ) {
			$url .= trailingslashit( $endpoint ) . user_trailingslashit( $value );
		} else {
			$url .= user_trailingslashit( $endpoint );
		}

		$url .= $query_string;
	} else {
		$url = add_query_arg( $endpoint, $value, $permalink );
	}

	return apply_filters( 'wpboutik_get_endpoint_url', $url, $endpoint, $value, $permalink );
}

/**
 * Get the edit address slug translation.
 *
 * @param string $id Address ID.
 * @param bool $flip Flip the array to make it possible to retrieve the values ​​from both sides.
 *
 * @return string       Address slug i18n.
 */
function wpboutik_edit_address_i18n( $id, $flip = false ) {
	$slugs = apply_filters(
		'wpboutik_edit_address_slugs',
		array(
			'billing'  => sanitize_title( _x( 'billing', 'edit-address-slug', 'wpboutik' ) ),
			'shipping' => sanitize_title( _x( 'shipping', 'edit-address-slug', 'wpboutik' ) ),
		)
	);

	if ( $flip ) {
		$slugs = array_flip( $slugs );
	}

	if ( ! isset( $slugs[ $id ] ) ) {
		return $id;
	}

	return $slugs[ $id ];
}

function wpboutik_get_account_endpoint_url( $endpoint ) {
	if ( 'dashboard' === $endpoint ) {
		return wpboutik_get_page_permalink( 'account' );
	}

	if ( 'customer-logout' === $endpoint ) {
		return wpboutik_logout_url();
	}

	return wpboutik_get_endpoint_url( $endpoint, '', wpboutik_get_page_permalink( 'account' ) );
}


/**
 * Get account menu item classes.
 *
 * @param string $endpoint Endpoint.
 *
 * @return string
 */
function wpboutik_get_account_menu_item_classes( $endpoint ) {
	global $wp;

	$classes = array(
		'group',
		'border-l-4',
		'px-3',
		'py-2',
		'flex',
		'items-center',
		'text-sm',
		'font-medium'
	);

	// Set current item class.
	$current = isset( $wp->query_vars[ $endpoint ] );
	if ( 'dashboard' === $endpoint && ( isset( $wp->query_vars['page'] ) || empty( $wp->query_vars ) ) ) {
		$current = true; // Dashboard is not an endpoint, so needs a custom check.
	} elseif ( 'orders' === $endpoint && isset( $wp->query_vars['view-order'] ) ) {
		$current = true; // When looking at individual order, highlight Orders list item (to signify where in the menu the user currently is).
	} elseif ( 'licenses' === $endpoint && isset( $wp->query_vars['licenses'] ) ) {
		$current = true; // When looking at individual order, highlight Orders list item (to signify where in the menu the user currently is).
	} elseif ( 'payment-methods' === $endpoint && isset( $wp->query_vars['add-payment-method'] ) ) {
		$current = true;
	}

	if ( $current ) {
		$classes[] = 'bg-teal-50';
		$classes[] = 'border-teal-500';
		$classes[] = 'text-teal-700';
		$classes[] = 'hover:bg-teal-50';
		$classes[] = 'hover:text-teal-700';
	} else {
		$classes[] = 'border-transparent';
		$classes[] = 'text-gray-900';
		$classes[] = 'hover:bg-gray-50';
		$classes[] = 'hover:text-gray-900';
		$classes[] = 'border-transparent';
	}

	$classes = apply_filters( 'wpboutik_account_menu_item_classes', $classes, $endpoint );

	return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
}

/**
 * Get My Account menu items.
 *
 * @return array
 */
function wpboutik_get_account_menu_items() {
	$endpoints = array(
		'orders'    => 'orders',
		'downloads' => 'downloads',
		//'payment-methods' => 'payment-methods',
	);

	$items = array(
		'dashboard' => __( 'Dashboard', 'wpboutik' ),
		'orders'    => __( 'Orders', 'wpboutik' ),
		'downloads' => __( 'Downloads', 'wpboutik' ),
		//'payment-methods' => __( 'Payment methods', 'wpboutik' ),
	);

	$licenses = get_user_meta( get_current_user_id(), 'license_code' );
	if ( ! empty( $licenses ) ) {
		$endpoints['licenses'] = 'licenses';
		$items['licenses']     = __( 'Licenses', 'wpboutik' );
	}
	$abos = get_user_meta( get_current_user_id(), 'abonnements' );
	if ( ! empty( $abos ) ) {
		$endpoints['abonnements'] = 'abonnements';
		$items['abonnements']     = __( 'Abonnements', 'wpboutik' );
	}

	$enpoints = array_merge( $endpoints, [
		'edit-address'    => 'edit-address',
		'edit-account'    => 'edit-account',
		'customer-logout' => 'customer-logout',
	] );
	$items    = array_merge( $items, [
		'edit-address'    => __( 'Addresses', 'wpboutik' ),
		'edit-account'    => __( 'Account details', 'wpboutik' ),
		'customer-logout' => __( 'Log out' ),
	] );
	// Remove missing endpoints.
	foreach ( $endpoints as $endpoint_id => $endpoint ) {
		if ( empty( $endpoint ) ) {
			unset( $items[ $endpoint_id ] );
		}
	}

	// Check if payment gateways support add new payment methods.
	if ( isset( $items['payment-methods'] ) ) {
		$support_payment_methods = false;
		foreach ( WC()->payment_gateways->get_available_payment_gateways() as $gateway ) {
			if ( $gateway->supports( 'add_payment_method' ) || $gateway->supports( 'tokenization' ) ) {
				$support_payment_methods = true;
				break;
			}
		}

		if ( ! $support_payment_methods ) {
			unset( $items['payment-methods'] );
		}
	}

	return apply_filters( 'wpboutik_account_menu_items', $items, $endpoints );
}

function display_recursivity( $recursive_type, $recursive_number ) {
	if ( $recursive_number > 1 ) {
		return sprintf( __( 'every %d %s', 'wpboutik' ), $recursive_number, translated_recursive_type( $recursive_type ) );
	} else {
		return sprintf( __( 'every %s', 'wpboutik' ), translated_recursive_type( $recursive_type ) );
	}
}

/**
 * Traduction du type de récurence
 *
 * @param string $recursive_type
 *
 * @return string traduced type of récurence
 */
function translated_recursive_type( $recursive_type ) {
	switch ( $recursive_type ) {
		case 'DAY':
			$translated_recursive_type = __( 'day', 'wpboutik' );
			break;
		case 'MONTH':
			$translated_recursive_type = __( 'month', 'wpboutik' );
			break;
		case 'YEAR':
			$translated_recursive_type = __( 'year', 'wpboutik' );
			break;
		default:
			$translated_recursive_type = $recursive_type;
	}

	return $translated_recursive_type;
}

/**
 * Get logout endpoint.
 *
 * @param string $redirect Redirect URL.
 *
 * @return string
 */
function wpboutik_logout_url( $redirect = '' ) {
	$redirect = $redirect ? $redirect : apply_filters( 'wpboutik_logout_default_redirect_url', wpboutik_get_page_permalink( 'account' ) );

	return wp_nonce_url( wpboutik_get_endpoint_url( 'customer-logout', '', $redirect ), 'customer-logout' );
}


if ( ! function_exists( 'wpboutik_get_backgroundcolor_button' ) ) {
	function wpboutik_get_backgroundcolor_button() {
		$wpboutik_backgroundcolor = get_theme_mod( 'wpboutik_backgroundcolor_button', '#3c54cc' );

		return $wpboutik_backgroundcolor;
	}
}

if ( ! function_exists( 'wpboutik_get_hovercolor_button' ) ) {
	function wpboutik_get_hovercolor_button() {
		$wpboutik_hovercolor = get_theme_mod( 'wpboutik_hovercolor_button', '#3043a3' );

		return $wpboutik_hovercolor;
	}
}

if ( ! function_exists( 'wpboutik_get_title_product_color' ) ) {
	function wpboutik_get_title_product_color() {
		$wpboutik_title_product_color = get_theme_mod( 'wpboutik_title_product_color', '#000000' );

		return $wpboutik_title_product_color;
	}
}

if ( ! function_exists( 'wpboutik_get_title_product_color_on_hover' ) ) {
	function wpboutik_get_title_product_color_on_hover() {
		$wpboutik_title_product_color_on_hover = get_theme_mod( 'wpboutik_title_product_color_on_hover', '#3043a3' );

		return $wpboutik_title_product_color_on_hover;
	}
}

/*if ( ! function_exists( 'wpboutik_get_general_font_size' ) ) {
	function wpboutik_get_general_font_size() {
		$wpboutik_general_font_size = get_theme_mod( 'wpboutik_general_font_size', 14 );

		return $wpboutik_general_font_size;
	}
}*/

if ( ! function_exists( 'wpboutik_get_display_shop_page' ) ) {
	function wpboutik_get_display_shop_page() {
		$wpboutik_display_shop_page = get_theme_mod( 'wpboutik_display_shop_page', 'product' );

		return $wpboutik_display_shop_page;
	}
}

if ( ! function_exists( 'wpboutik_show_breadcrumb' ) ) {
	function wpboutik_show_breadcrumb() {
		$wpboutik_show_breadcrumb = get_theme_mod( 'wpboutik_show_breadcrumb', 'yes' );

		return $wpboutik_show_breadcrumb;
	}
}

if ( ! function_exists( 'wpboutik_get_button_text_color' ) ) {
	function wpboutik_get_button_text_color() {
		$wpboutik_button_text_color = get_theme_mod( 'wpboutik_button_text_color', '#ffffff' );

		return $wpboutik_button_text_color;
	}
}

if ( ! function_exists( 'wpboutik_get_show_archive_sidebar' ) ) {
	function wpboutik_get_show_archive_sidebar() {
		$wpboutik_show_archive_sidebar = get_theme_mod( 'wpboutik_show_archive_sidebar', 'hidden' );

		return $wpboutik_show_archive_sidebar;
	}
}

/**
 * Get all options
 * @return array
 * @throws Exception
 * @since 1.0
 *
 */
function wpboutik_get_options() {
	$options = get_option( 'wpboutik_options' );

	if ( $options ) {
		return $options;
	}

	$options = array(
		'apikey' => '',
	);

	return $options;
}

function wpboutik_get_options_params() {
	$options = get_option( 'wpboutik_options_params' );

	if ( $options ) {
		return $options;
	}

	return false;
}

function wpboutik_get_options_shipping_method_list() {
	$options = get_option( 'wpboutik_options_shipping_method_list' );

	if ( $options ) {
		return $options;
	}

	return [];
}

function wpboutik_get_options_coupon_list() {
	$options = get_option( 'wpboutik_options_coupon_list' );

	if ( $options ) {
		return $options;
	}

	return [];
}

/**
 * Get option param
 *
 * @param string $key
 *
 * @return mixed
 * @throws Exception
 * @since 1.0
 */
function wpboutik_get_option_params( $key ) {
	$options = wpboutik_get_options_params();

	if ( empty( $options ) ) {
		return false;
	}

	if ( ! isset ( $options[ $key ] ) ) {
		return false;
	}

	$val = $options[ $key ];

	return $val;
}

/**
 * Get option
 *
 * @param string $key
 *
 * @return mixed
 * @throws Exception
 * @since 1.0
 */
function wpboutik_get_option( $key ) {
	$options = wpboutik_get_options();

	if ( ! isset( $options[ $key ] ) ) {
		return false;
	}

	return $options[ $key ];
}

/**
 * Get API KEY WPBoutik
 * @return string
 * @since 1.0
 */
function wpboutik_get_api_key() {
	return wpboutik_get_option( 'apikey' );
}

/**
 * Retrieve page ids - used for shop, cart, checkout. returns -1 if no page is found.
 *
 * @param string $page Page slug.
 *
 * @return int
 */
function wpboutik_get_page_id( $page ) {
	$page = apply_filters( 'wpboutik_get_' . $page . '_page_id', wpboutik_get_option( 'wpboutik_' . $page . '_page_id' ) );

	return $page ? absint( $page ) : - 1;
}

/**
 * Get all available Currency symbols.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (http://cldr.unicode.org/translation/currency-names)
 *
 * @return array
 * @since 1.0
 */
function get_wpboutik_currency_symbols() {

	$symbols = apply_filters(
		'wpboutik_currency_symbols',
		array(
			'AED' => '&#x62f;.&#x625;',
			'AFN' => '&#x60b;',
			'ALL' => 'L',
			'AMD' => 'AMD',
			'ANG' => '&fnof;',
			'AOA' => 'Kz',
			'ARS' => '&#36;',
			'AUD' => '&#36;',
			'AWG' => 'Afl.',
			'AZN' => 'AZN',
			'BAM' => 'KM',
			'BBD' => '&#36;',
			'BDT' => '&#2547;&nbsp;',
			'BGN' => '&#1083;&#1074;.',
			'BHD' => '.&#x62f;.&#x628;',
			'BIF' => 'Fr',
			'BMD' => '&#36;',
			'BND' => '&#36;',
			'BOB' => 'Bs.',
			'BRL' => '&#82;&#36;',
			'BSD' => '&#36;',
			'BTC' => '&#3647;',
			'BTN' => 'Nu.',
			'BWP' => 'P',
			'BYR' => 'Br',
			'BYN' => 'Br',
			'BZD' => '&#36;',
			'CAD' => '&#36;',
			'CDF' => 'Fr',
			'CHF' => '&#67;&#72;&#70;',
			'CLP' => '&#36;',
			'CNY' => '&yen;',
			'COP' => '&#36;',
			'CRC' => '&#x20a1;',
			'CUC' => '&#36;',
			'CUP' => '&#36;',
			'CVE' => '&#36;',
			'CZK' => '&#75;&#269;',
			'DJF' => 'Fr',
			'DKK' => 'DKK',
			'DOP' => 'RD&#36;',
			'DZD' => '&#x62f;.&#x62c;',
			'EGP' => 'EGP',
			'ERN' => 'Nfk',
			'ETB' => 'Br',
			'EUR' => '&euro;',
			'FJD' => '&#36;',
			'FKP' => '&pound;',
			'GBP' => '&pound;',
			'GEL' => '&#x20be;',
			'GGP' => '&pound;',
			'GHS' => '&#x20b5;',
			'GIP' => '&pound;',
			'GMD' => 'D',
			'GNF' => 'Fr',
			'GTQ' => 'Q',
			'GYD' => '&#36;',
			'HKD' => '&#36;',
			'HNL' => 'L',
			'HRK' => 'kn',
			'HTG' => 'G',
			'HUF' => '&#70;&#116;',
			'IDR' => 'Rp',
			'ILS' => '&#8362;',
			'IMP' => '&pound;',
			'INR' => '&#8377;',
			'IQD' => '&#x639;.&#x62f;',
			'IRR' => '&#xfdfc;',
			'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
			'ISK' => 'kr.',
			'JEP' => '&pound;',
			'JMD' => '&#36;',
			'JOD' => '&#x62f;.&#x627;',
			'JPY' => '&yen;',
			'KES' => 'KSh',
			'KGS' => '&#x441;&#x43e;&#x43c;',
			'KHR' => '&#x17db;',
			'KMF' => 'Fr',
			'KPW' => '&#x20a9;',
			'KRW' => '&#8361;',
			'KWD' => '&#x62f;.&#x643;',
			'KYD' => '&#36;',
			'KZT' => '&#8376;',
			'LAK' => '&#8365;',
			'LBP' => '&#x644;.&#x644;',
			'LKR' => '&#xdbb;&#xdd4;',
			'LRD' => '&#36;',
			'LSL' => 'L',
			'LYD' => '&#x644;.&#x62f;',
			'MAD' => '&#x62f;.&#x645;.',
			'MDL' => 'MDL',
			'MGA' => 'Ar',
			'MKD' => '&#x434;&#x435;&#x43d;',
			'MMK' => 'Ks',
			'MNT' => '&#x20ae;',
			'MOP' => 'P',
			'MRU' => 'UM',
			'MUR' => '&#x20a8;',
			'MVR' => '.&#x783;',
			'MWK' => 'MK',
			'MXN' => '&#36;',
			'MYR' => '&#82;&#77;',
			'MZN' => 'MT',
			'NAD' => 'N&#36;',
			'NGN' => '&#8358;',
			'NIO' => 'C&#36;',
			'NOK' => '&#107;&#114;',
			'NPR' => '&#8360;',
			'NZD' => '&#36;',
			'OMR' => '&#x631;.&#x639;.',
			'PAB' => 'B/.',
			'PEN' => 'S/',
			'PGK' => 'K',
			'PHP' => '&#8369;',
			'PKR' => '&#8360;',
			'PLN' => '&#122;&#322;',
			'PRB' => '&#x440;.',
			'PYG' => '&#8370;',
			'QAR' => '&#x631;.&#x642;',
			'RMB' => '&yen;',
			'RON' => 'lei',
			'RSD' => '&#1088;&#1089;&#1076;',
			'RUB' => '&#8381;',
			'RWF' => 'Fr',
			'SAR' => '&#x631;.&#x633;',
			'SBD' => '&#36;',
			'SCR' => '&#x20a8;',
			'SDG' => '&#x62c;.&#x633;.',
			'SEK' => '&#107;&#114;',
			'SGD' => '&#36;',
			'SHP' => '&pound;',
			'SLL' => 'Le',
			'SOS' => 'Sh',
			'SRD' => '&#36;',
			'SSP' => '&pound;',
			'STN' => 'Db',
			'SYP' => '&#x644;.&#x633;',
			'SZL' => 'L',
			'THB' => '&#3647;',
			'TJS' => '&#x405;&#x41c;',
			'TMT' => 'm',
			'TND' => '&#x62f;.&#x62a;',
			'TOP' => 'T&#36;',
			'TRY' => '&#8378;',
			'TTD' => '&#36;',
			'TWD' => '&#78;&#84;&#36;',
			'TZS' => 'Sh',
			'UAH' => '&#8372;',
			'UGX' => 'UGX',
			'USD' => '&#36;',
			'UYU' => '&#36;',
			'UZS' => 'UZS',
			'VEF' => 'Bs F',
			'VES' => 'Bs.S',
			'VND' => '&#8363;',
			'VUV' => 'Vt',
			'WST' => 'T',
			'XAF' => 'CFA',
			'XCD' => '&#36;',
			'XOF' => 'CFA',
			'XPF' => 'Fr',
			'YER' => '&#xfdfc;',
			'ZAR' => '&#82;',
			'ZMW' => 'ZK',
		)
	);

	return $symbols;
}

/**
 * Get Base Currency Code.
 *
 * @return string
 */
function get_wpboutik_currency() {
	return apply_filters( 'wpboutik_currency', wpboutik_get_option_params( 'devise' ) );
}

/**
 * Get Currency symbol.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (http://cldr.unicode.org/translation/currency-names)
 *
 * @param string $currency Currency. (default: '').
 *
 * @return string
 */
function get_wpboutik_currency_symbol( $currency = '' ) {
	if ( ! $currency ) {
		$currency = get_wpboutik_currency();
		if ( empty( $currency ) ) {
			$currency = 'EUR';
		}
	}

	$symbols = get_wpboutik_currency_symbols();

	$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

	return apply_filters( 'wpboutik_currency_symbol', $currency_symbol, $currency );
}

function get_wpboutik_countries() {
	$countries = apply_filters(
		'wpboutik_countries',
		array(
			'AF' => __( 'Afghanistan', 'wpboutik' ),
			'AX' => __( 'Åland Islands', 'wpboutik' ),
			'AL' => __( 'Albania', 'wpboutik' ),
			'DZ' => __( 'Algeria', 'wpboutik' ),
			'AS' => __( 'American Samoa', 'wpboutik' ),
			'AD' => __( 'Andorra', 'wpboutik' ),
			'AO' => __( 'Angola', 'wpboutik' ),
			'AI' => __( 'Anguilla', 'wpboutik' ),
			'AQ' => __( 'Antarctica', 'wpboutik' ),
			'AG' => __( 'Antigua and Barbuda', 'wpboutik' ),
			'AR' => __( 'Argentina', 'wpboutik' ),
			'AM' => __( 'Armenia', 'wpboutik' ),
			'AW' => __( 'Aruba', 'wpboutik' ),
			'AU' => __( 'Australia', 'wpboutik' ),
			'AT' => __( 'Austria', 'wpboutik' ),
			'AZ' => __( 'Azerbaijan', 'wpboutik' ),
			'BS' => __( 'Bahamas', 'wpboutik' ),
			'BH' => __( 'Bahrain', 'wpboutik' ),
			'BD' => __( 'Bangladesh', 'wpboutik' ),
			'BB' => __( 'Barbados', 'wpboutik' ),
			'BY' => __( 'Belarus', 'wpboutik' ),
			'BE' => __( 'Belgium', 'wpboutik' ),
			'PW' => __( 'Belau', 'wpboutik' ),
			'BZ' => __( 'Belize', 'wpboutik' ),
			'BJ' => __( 'Benin', 'wpboutik' ),
			'BM' => __( 'Bermuda', 'wpboutik' ),
			'BT' => __( 'Bhutan', 'wpboutik' ),
			'BO' => __( 'Bolivia', 'wpboutik' ),
			'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'wpboutik' ),
			'BA' => __( 'Bosnia and Herzegovina', 'wpboutik' ),
			'BW' => __( 'Botswana', 'wpboutik' ),
			'BV' => __( 'Bouvet Island', 'wpboutik' ),
			'BR' => __( 'Brazil', 'wpboutik' ),
			'IO' => __( 'British Indian Ocean Territory', 'wpboutik' ),
			'BN' => __( 'Brunei', 'wpboutik' ),
			'BG' => __( 'Bulgaria', 'wpboutik' ),
			'BF' => __( 'Burkina Faso', 'wpboutik' ),
			'BI' => __( 'Burundi', 'wpboutik' ),
			'KH' => __( 'Cambodia', 'wpboutik' ),
			'CM' => __( 'Cameroon', 'wpboutik' ),
			'CA' => __( 'Canada', 'wpboutik' ),
			'CV' => __( 'Cape Verde', 'wpboutik' ),
			'KY' => __( 'Cayman Islands', 'wpboutik' ),
			'CF' => __( 'Central African Republic', 'wpboutik' ),
			'TD' => __( 'Chad', 'wpboutik' ),
			'CL' => __( 'Chile', 'wpboutik' ),
			'CN' => __( 'China', 'wpboutik' ),
			'CX' => __( 'Christmas Island', 'wpboutik' ),
			'CC' => __( 'Cocos (Keeling) Islands', 'wpboutik' ),
			'CO' => __( 'Colombia', 'wpboutik' ),
			'KM' => __( 'Comoros', 'wpboutik' ),
			'CG' => __( 'Congo (Brazzaville)', 'wpboutik' ),
			'CD' => __( 'Congo (Kinshasa)', 'wpboutik' ),
			'CK' => __( 'Cook Islands', 'wpboutik' ),
			'CR' => __( 'Costa Rica', 'wpboutik' ),
			'HR' => __( 'Croatia', 'wpboutik' ),
			'CU' => __( 'Cuba', 'wpboutik' ),
			'CW' => __( 'Cura&ccedil;ao', 'wpboutik' ),
			'CY' => __( 'Cyprus', 'wpboutik' ),
			'CZ' => __( 'Czech Republic', 'wpboutik' ),
			'DK' => __( 'Denmark', 'wpboutik' ),
			'DJ' => __( 'Djibouti', 'wpboutik' ),
			'DM' => __( 'Dominica', 'wpboutik' ),
			'DO' => __( 'Dominican Republic', 'wpboutik' ),
			'EC' => __( 'Ecuador', 'wpboutik' ),
			'EG' => __( 'Egypt', 'wpboutik' ),
			'SV' => __( 'El Salvador', 'wpboutik' ),
			'GQ' => __( 'Equatorial Guinea', 'wpboutik' ),
			'ER' => __( 'Eritrea', 'wpboutik' ),
			'EE' => __( 'Estonia', 'wpboutik' ),
			'ET' => __( 'Ethiopia', 'wpboutik' ),
			'FK' => __( 'Falkland Islands', 'wpboutik' ),
			'FO' => __( 'Faroe Islands', 'wpboutik' ),
			'FJ' => __( 'Fiji', 'wpboutik' ),
			'FI' => __( 'Finland', 'wpboutik' ),
			'FR' => __( 'France', 'wpboutik' ),
			'GF' => __( 'French Guiana', 'wpboutik' ),
			'PF' => __( 'French Polynesia', 'wpboutik' ),
			'TF' => __( 'French Southern Territories', 'wpboutik' ),
			'GA' => __( 'Gabon', 'wpboutik' ),
			'GM' => __( 'Gambia', 'wpboutik' ),
			'GE' => __( 'Georgia', 'wpboutik' ),
			'DE' => __( 'Germany', 'wpboutik' ),
			'GH' => __( 'Ghana', 'wpboutik' ),
			'GI' => __( 'Gibraltar', 'wpboutik' ),
			'GR' => __( 'Greece', 'wpboutik' ),
			'GL' => __( 'Greenland', 'wpboutik' ),
			'GD' => __( 'Grenada', 'wpboutik' ),
			'GP' => __( 'Guadeloupe', 'wpboutik' ),
			'GU' => __( 'Guam', 'wpboutik' ),
			'GT' => __( 'Guatemala', 'wpboutik' ),
			'GG' => __( 'Guernsey', 'wpboutik' ),
			'GN' => __( 'Guinea', 'wpboutik' ),
			'GW' => __( 'Guinea-Bissau', 'wpboutik' ),
			'GY' => __( 'Guyana', 'wpboutik' ),
			'HT' => __( 'Haiti', 'wpboutik' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'wpboutik' ),
			'HN' => __( 'Honduras', 'wpboutik' ),
			'HK' => __( 'Hong Kong', 'wpboutik' ),
			'HU' => __( 'Hungary', 'wpboutik' ),
			'IS' => __( 'Iceland', 'wpboutik' ),
			'IN' => __( 'India', 'wpboutik' ),
			'ID' => __( 'Indonesia', 'wpboutik' ),
			'IR' => __( 'Iran', 'wpboutik' ),
			'IQ' => __( 'Iraq', 'wpboutik' ),
			'IE' => __( 'Ireland', 'wpboutik' ),
			'IM' => __( 'Isle of Man', 'wpboutik' ),
			'IL' => __( 'Israel', 'wpboutik' ),
			'IT' => __( 'Italy', 'wpboutik' ),
			'CI' => __( 'Ivory Coast', 'wpboutik' ),
			'JM' => __( 'Jamaica', 'wpboutik' ),
			'JP' => __( 'Japan', 'wpboutik' ),
			'JE' => __( 'Jersey', 'wpboutik' ),
			'JO' => __( 'Jordan', 'wpboutik' ),
			'KZ' => __( 'Kazakhstan', 'wpboutik' ),
			'KE' => __( 'Kenya', 'wpboutik' ),
			'KI' => __( 'Kiribati', 'wpboutik' ),
			'KW' => __( 'Kuwait', 'wpboutik' ),
			'KG' => __( 'Kyrgyzstan', 'wpboutik' ),
			'LA' => __( 'Laos', 'wpboutik' ),
			'LV' => __( 'Latvia', 'wpboutik' ),
			'LB' => __( 'Lebanon', 'wpboutik' ),
			'LS' => __( 'Lesotho', 'wpboutik' ),
			'LR' => __( 'Liberia', 'wpboutik' ),
			'LY' => __( 'Libya', 'wpboutik' ),
			'LI' => __( 'Liechtenstein', 'wpboutik' ),
			'LT' => __( 'Lithuania', 'wpboutik' ),
			'LU' => __( 'Luxembourg', 'wpboutik' ),
			'MO' => __( 'Macao', 'wpboutik' ),
			'MK' => __( 'North Macedonia', 'wpboutik' ),
			'MG' => __( 'Madagascar', 'wpboutik' ),
			'MW' => __( 'Malawi', 'wpboutik' ),
			'MY' => __( 'Malaysia', 'wpboutik' ),
			'MV' => __( 'Maldives', 'wpboutik' ),
			'ML' => __( 'Mali', 'wpboutik' ),
			'MT' => __( 'Malta', 'wpboutik' ),
			'MH' => __( 'Marshall Islands', 'wpboutik' ),
			'MQ' => __( 'Martinique', 'wpboutik' ),
			'MR' => __( 'Mauritania', 'wpboutik' ),
			'MU' => __( 'Mauritius', 'wpboutik' ),
			'YT' => __( 'Mayotte', 'wpboutik' ),
			'MX' => __( 'Mexico', 'wpboutik' ),
			'FM' => __( 'Micronesia', 'wpboutik' ),
			'MD' => __( 'Moldova', 'wpboutik' ),
			'MC' => __( 'Monaco', 'wpboutik' ),
			'MN' => __( 'Mongolia', 'wpboutik' ),
			'ME' => __( 'Montenegro', 'wpboutik' ),
			'MS' => __( 'Montserrat', 'wpboutik' ),
			'MA' => __( 'Morocco', 'wpboutik' ),
			'MZ' => __( 'Mozambique', 'wpboutik' ),
			'MM' => __( 'Myanmar', 'wpboutik' ),
			'NA' => __( 'Namibia', 'wpboutik' ),
			'NR' => __( 'Nauru', 'wpboutik' ),
			'NP' => __( 'Nepal', 'wpboutik' ),
			'NL' => __( 'Netherlands', 'wpboutik' ),
			'NC' => __( 'New Caledonia', 'wpboutik' ),
			'NZ' => __( 'New Zealand', 'wpboutik' ),
			'NI' => __( 'Nicaragua', 'wpboutik' ),
			'NE' => __( 'Niger', 'wpboutik' ),
			'NG' => __( 'Nigeria', 'wpboutik' ),
			'NU' => __( 'Niue', 'wpboutik' ),
			'NF' => __( 'Norfolk Island', 'wpboutik' ),
			'MP' => __( 'Northern Mariana Islands', 'wpboutik' ),
			'KP' => __( 'North Korea', 'wpboutik' ),
			'NO' => __( 'Norway', 'wpboutik' ),
			'OM' => __( 'Oman', 'wpboutik' ),
			'PK' => __( 'Pakistan', 'wpboutik' ),
			'PS' => __( 'Palestinian Territory', 'wpboutik' ),
			'PA' => __( 'Panama', 'wpboutik' ),
			'PG' => __( 'Papua New Guinea', 'wpboutik' ),
			'PY' => __( 'Paraguay', 'wpboutik' ),
			'PE' => __( 'Peru', 'wpboutik' ),
			'PH' => __( 'Philippines', 'wpboutik' ),
			'PN' => __( 'Pitcairn', 'wpboutik' ),
			'PL' => __( 'Poland', 'wpboutik' ),
			'PT' => __( 'Portugal', 'wpboutik' ),
			'PR' => __( 'Puerto Rico', 'wpboutik' ),
			'QA' => __( 'Qatar', 'wpboutik' ),
			'RE' => __( 'Reunion', 'wpboutik' ),
			'RO' => __( 'Romania', 'wpboutik' ),
			'RU' => __( 'Russia', 'wpboutik' ),
			'RW' => __( 'Rwanda', 'wpboutik' ),
			'BL' => __( 'Saint Barth&eacute;lemy', 'wpboutik' ),
			'SH' => __( 'Saint Helena', 'wpboutik' ),
			'KN' => __( 'Saint Kitts and Nevis', 'wpboutik' ),
			'LC' => __( 'Saint Lucia', 'wpboutik' ),
			'MF' => __( 'Saint Martin (French part)', 'wpboutik' ),
			'SX' => __( 'Saint Martin (Dutch part)', 'wpboutik' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'wpboutik' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'wpboutik' ),
			'SM' => __( 'San Marino', 'wpboutik' ),
			'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'wpboutik' ),
			'SA' => __( 'Saudi Arabia', 'wpboutik' ),
			'SN' => __( 'Senegal', 'wpboutik' ),
			'RS' => __( 'Serbia', 'wpboutik' ),
			'SC' => __( 'Seychelles', 'wpboutik' ),
			'SL' => __( 'Sierra Leone', 'wpboutik' ),
			'SG' => __( 'Singapore', 'wpboutik' ),
			'SK' => __( 'Slovakia', 'wpboutik' ),
			'SI' => __( 'Slovenia', 'wpboutik' ),
			'SB' => __( 'Solomon Islands', 'wpboutik' ),
			'SO' => __( 'Somalia', 'wpboutik' ),
			'ZA' => __( 'South Africa', 'wpboutik' ),
			'GS' => __( 'South Georgia/Sandwich Islands', 'wpboutik' ),
			'KR' => __( 'South Korea', 'wpboutik' ),
			'SS' => __( 'South Sudan', 'wpboutik' ),
			'ES' => __( 'Spain', 'wpboutik' ),
			'LK' => __( 'Sri Lanka', 'wpboutik' ),
			'SD' => __( 'Sudan', 'wpboutik' ),
			'SR' => __( 'Suriname', 'wpboutik' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'wpboutik' ),
			'SZ' => __( 'Swaziland', 'wpboutik' ),
			'SE' => __( 'Sweden', 'wpboutik' ),
			'CH' => __( 'Switzerland', 'wpboutik' ),
			'SY' => __( 'Syria', 'wpboutik' ),
			'TW' => __( 'Taiwan', 'wpboutik' ),
			'TJ' => __( 'Tajikistan', 'wpboutik' ),
			'TZ' => __( 'Tanzania', 'wpboutik' ),
			'TH' => __( 'Thailand', 'wpboutik' ),
			'TL' => __( 'Timor-Leste', 'wpboutik' ),
			'TG' => __( 'Togo', 'wpboutik' ),
			'TK' => __( 'Tokelau', 'wpboutik' ),
			'TO' => __( 'Tonga', 'wpboutik' ),
			'TT' => __( 'Trinidad and Tobago', 'wpboutik' ),
			'TN' => __( 'Tunisia', 'wpboutik' ),
			'TR' => __( 'Turkey', 'wpboutik' ),
			'TM' => __( 'Turkmenistan', 'wpboutik' ),
			'TC' => __( 'Turks and Caicos Islands', 'wpboutik' ),
			'TV' => __( 'Tuvalu', 'wpboutik' ),
			'UG' => __( 'Uganda', 'wpboutik' ),
			'UA' => __( 'Ukraine', 'wpboutik' ),
			'AE' => __( 'United Arab Emirates', 'wpboutik' ),
			'GB' => __( 'United Kingdom (UK)', 'wpboutik' ),
			'US' => __( 'United States (US)', 'wpboutik' ),
			'UM' => __( 'United States (US) Minor Outlying Islands', 'wpboutik' ),
			'UY' => __( 'Uruguay', 'wpboutik' ),
			'UZ' => __( 'Uzbekistan', 'wpboutik' ),
			'VU' => __( 'Vanuatu', 'wpboutik' ),
			'VA' => __( 'Vatican', 'wpboutik' ),
			'VE' => __( 'Venezuela', 'wpboutik' ),
			'VN' => __( 'Vietnam', 'wpboutik' ),
			'VG' => __( 'Virgin Islands (British)', 'wpboutik' ),
			'VI' => __( 'Virgin Islands (US)', 'wpboutik' ),
			'WF' => __( 'Wallis and Futuna', 'wpboutik' ),
			'EH' => __( 'Western Sahara', 'wpboutik' ),
			'WS' => __( 'Samoa', 'wpboutik' ),
			'YE' => __( 'Yemen', 'wpboutik' ),
			'ZM' => __( 'Zambia', 'wpboutik' ),
			'ZW' => __( 'Zimbabwe', 'wpboutik' ),
		)
	);

	return $countries;
}

function get_wpboutik_states() {
	return array(
		'AF' => array(),
		'AO' => array( // Angola states.
			'BGO' => __( 'Bengo', 'wpboutik' ),
			'BLU' => __( 'Benguela', 'wpboutik' ),
			'BIE' => __( 'Bié', 'wpboutik' ),
			'CAB' => __( 'Cabinda', 'wpboutik' ),
			'CNN' => __( 'Cunene', 'wpboutik' ),
			'HUA' => __( 'Huambo', 'wpboutik' ),
			'HUI' => __( 'Huíla', 'wpboutik' ),
			'CCU' => __( 'Kuando Kubango', 'wpboutik' ),
			'CNO' => __( 'Kwanza-Norte', 'wpboutik' ),
			'CUS' => __( 'Kwanza-Sul', 'wpboutik' ),
			'LUA' => __( 'Luanda', 'wpboutik' ),
			'LNO' => __( 'Lunda-Norte', 'wpboutik' ),
			'LSU' => __( 'Lunda-Sul', 'wpboutik' ),
			'MAL' => __( 'Malanje', 'wpboutik' ),
			'MOX' => __( 'Moxico', 'wpboutik' ),
			'NAM' => __( 'Namibe', 'wpboutik' ),
			'UIG' => __( 'Uíge', 'wpboutik' ),
			'ZAI' => __( 'Zaire', 'wpboutik' ),
		),
		'AR' => array( // Argentinian provinces.
			'C' => __( 'Ciudad Autónoma de Buenos Aires', 'wpboutik' ),
			'B' => __( 'Buenos Aires', 'wpboutik' ),
			'K' => __( 'Catamarca', 'wpboutik' ),
			'H' => __( 'Chaco', 'wpboutik' ),
			'U' => __( 'Chubut', 'wpboutik' ),
			'X' => __( 'Córdoba', 'wpboutik' ),
			'W' => __( 'Corrientes', 'wpboutik' ),
			'E' => __( 'Entre Ríos', 'wpboutik' ),
			'P' => __( 'Formosa', 'wpboutik' ),
			'Y' => __( 'Jujuy', 'wpboutik' ),
			'L' => __( 'La Pampa', 'wpboutik' ),
			'F' => __( 'La Rioja', 'wpboutik' ),
			'M' => __( 'Mendoza', 'wpboutik' ),
			'N' => __( 'Misiones', 'wpboutik' ),
			'Q' => __( 'Neuquén', 'wpboutik' ),
			'R' => __( 'Río Negro', 'wpboutik' ),
			'A' => __( 'Salta', 'wpboutik' ),
			'J' => __( 'San Juan', 'wpboutik' ),
			'D' => __( 'San Luis', 'wpboutik' ),
			'Z' => __( 'Santa Cruz', 'wpboutik' ),
			'S' => __( 'Santa Fe', 'wpboutik' ),
			'G' => __( 'Santiago del Estero', 'wpboutik' ),
			'V' => __( 'Tierra del Fuego', 'wpboutik' ),
			'T' => __( 'Tucumán', 'wpboutik' ),
		),
		'AT' => array(),
		'AU' => array( // Australian states.
			'ACT' => __( 'Australian Capital Territory', 'wpboutik' ),
			'NSW' => __( 'New South Wales', 'wpboutik' ),
			'NT'  => __( 'Northern Territory', 'wpboutik' ),
			'QLD' => __( 'Queensland', 'wpboutik' ),
			'SA'  => __( 'South Australia', 'wpboutik' ),
			'TAS' => __( 'Tasmania', 'wpboutik' ),
			'VIC' => __( 'Victoria', 'wpboutik' ),
			'WA'  => __( 'Western Australia', 'wpboutik' ),
		),
		'AX' => array(),
		'BD' => array( // Bangladeshi states (districts).
			'BD-05' => __( 'Bagerhat', 'wpboutik' ),
			'BD-01' => __( 'Bandarban', 'wpboutik' ),
			'BD-02' => __( 'Barguna', 'wpboutik' ),
			'BD-06' => __( 'Barishal', 'wpboutik' ),
			'BD-07' => __( 'Bhola', 'wpboutik' ),
			'BD-03' => __( 'Bogura', 'wpboutik' ),
			'BD-04' => __( 'Brahmanbaria', 'wpboutik' ),
			'BD-09' => __( 'Chandpur', 'wpboutik' ),
			'BD-10' => __( 'Chattogram', 'wpboutik' ),
			'BD-12' => __( 'Chuadanga', 'wpboutik' ),
			'BD-11' => __( "Cox's Bazar", 'wpboutik' ),
			'BD-08' => __( 'Cumilla', 'wpboutik' ),
			'BD-13' => __( 'Dhaka', 'wpboutik' ),
			'BD-14' => __( 'Dinajpur', 'wpboutik' ),
			'BD-15' => __( 'Faridpur ', 'wpboutik' ),
			'BD-16' => __( 'Feni', 'wpboutik' ),
			'BD-19' => __( 'Gaibandha', 'wpboutik' ),
			'BD-18' => __( 'Gazipur', 'wpboutik' ),
			'BD-17' => __( 'Gopalganj', 'wpboutik' ),
			'BD-20' => __( 'Habiganj', 'wpboutik' ),
			'BD-21' => __( 'Jamalpur', 'wpboutik' ),
			'BD-22' => __( 'Jashore', 'wpboutik' ),
			'BD-25' => __( 'Jhalokati', 'wpboutik' ),
			'BD-23' => __( 'Jhenaidah', 'wpboutik' ),
			'BD-24' => __( 'Joypurhat', 'wpboutik' ),
			'BD-29' => __( 'Khagrachhari', 'wpboutik' ),
			'BD-27' => __( 'Khulna', 'wpboutik' ),
			'BD-26' => __( 'Kishoreganj', 'wpboutik' ),
			'BD-28' => __( 'Kurigram', 'wpboutik' ),
			'BD-30' => __( 'Kushtia', 'wpboutik' ),
			'BD-31' => __( 'Lakshmipur', 'wpboutik' ),
			'BD-32' => __( 'Lalmonirhat', 'wpboutik' ),
			'BD-36' => __( 'Madaripur', 'wpboutik' ),
			'BD-37' => __( 'Magura', 'wpboutik' ),
			'BD-33' => __( 'Manikganj ', 'wpboutik' ),
			'BD-39' => __( 'Meherpur', 'wpboutik' ),
			'BD-38' => __( 'Moulvibazar', 'wpboutik' ),
			'BD-35' => __( 'Munshiganj', 'wpboutik' ),
			'BD-34' => __( 'Mymensingh', 'wpboutik' ),
			'BD-48' => __( 'Naogaon', 'wpboutik' ),
			'BD-43' => __( 'Narail', 'wpboutik' ),
			'BD-40' => __( 'Narayanganj', 'wpboutik' ),
			'BD-42' => __( 'Narsingdi', 'wpboutik' ),
			'BD-44' => __( 'Natore', 'wpboutik' ),
			'BD-45' => __( 'Nawabganj', 'wpboutik' ),
			'BD-41' => __( 'Netrakona', 'wpboutik' ),
			'BD-46' => __( 'Nilphamari', 'wpboutik' ),
			'BD-47' => __( 'Noakhali', 'wpboutik' ),
			'BD-49' => __( 'Pabna', 'wpboutik' ),
			'BD-52' => __( 'Panchagarh', 'wpboutik' ),
			'BD-51' => __( 'Patuakhali', 'wpboutik' ),
			'BD-50' => __( 'Pirojpur', 'wpboutik' ),
			'BD-53' => __( 'Rajbari', 'wpboutik' ),
			'BD-54' => __( 'Rajshahi', 'wpboutik' ),
			'BD-56' => __( 'Rangamati', 'wpboutik' ),
			'BD-55' => __( 'Rangpur', 'wpboutik' ),
			'BD-58' => __( 'Satkhira', 'wpboutik' ),
			'BD-62' => __( 'Shariatpur', 'wpboutik' ),
			'BD-57' => __( 'Sherpur', 'wpboutik' ),
			'BD-59' => __( 'Sirajganj', 'wpboutik' ),
			'BD-61' => __( 'Sunamganj', 'wpboutik' ),
			'BD-60' => __( 'Sylhet', 'wpboutik' ),
			'BD-63' => __( 'Tangail', 'wpboutik' ),
			'BD-64' => __( 'Thakurgaon', 'wpboutik' ),
		),
		'BE' => array(),
		'BG' => array( // Bulgarian states.
			'BG-01' => __( 'Blagoevgrad', 'wpboutik' ),
			'BG-02' => __( 'Burgas', 'wpboutik' ),
			'BG-08' => __( 'Dobrich', 'wpboutik' ),
			'BG-07' => __( 'Gabrovo', 'wpboutik' ),
			'BG-26' => __( 'Haskovo', 'wpboutik' ),
			'BG-09' => __( 'Kardzhali', 'wpboutik' ),
			'BG-10' => __( 'Kyustendil', 'wpboutik' ),
			'BG-11' => __( 'Lovech', 'wpboutik' ),
			'BG-12' => __( 'Montana', 'wpboutik' ),
			'BG-13' => __( 'Pazardzhik', 'wpboutik' ),
			'BG-14' => __( 'Pernik', 'wpboutik' ),
			'BG-15' => __( 'Pleven', 'wpboutik' ),
			'BG-16' => __( 'Plovdiv', 'wpboutik' ),
			'BG-17' => __( 'Razgrad', 'wpboutik' ),
			'BG-18' => __( 'Ruse', 'wpboutik' ),
			'BG-27' => __( 'Shumen', 'wpboutik' ),
			'BG-19' => __( 'Silistra', 'wpboutik' ),
			'BG-20' => __( 'Sliven', 'wpboutik' ),
			'BG-21' => __( 'Smolyan', 'wpboutik' ),
			'BG-23' => __( 'Sofia', 'wpboutik' ),
			'BG-22' => __( 'Sofia-Grad', 'wpboutik' ),
			'BG-24' => __( 'Stara Zagora', 'wpboutik' ),
			'BG-25' => __( 'Targovishte', 'wpboutik' ),
			'BG-03' => __( 'Varna', 'wpboutik' ),
			'BG-04' => __( 'Veliko Tarnovo', 'wpboutik' ),
			'BG-05' => __( 'Vidin', 'wpboutik' ),
			'BG-06' => __( 'Vratsa', 'wpboutik' ),
			'BG-28' => __( 'Yambol', 'wpboutik' ),
		),
		'BH' => array(),
		'BI' => array(),
		'BJ' => array( // Benin states.
			'AL' => __( 'Alibori', 'wpboutik' ),
			'AK' => __( 'Atakora', 'wpboutik' ),
			'AQ' => __( 'Atlantique', 'wpboutik' ),
			'BO' => __( 'Borgou', 'wpboutik' ),
			'CO' => __( 'Collines', 'wpboutik' ),
			'KO' => __( 'Kouffo', 'wpboutik' ),
			'DO' => __( 'Donga', 'wpboutik' ),
			'LI' => __( 'Littoral', 'wpboutik' ),
			'MO' => __( 'Mono', 'wpboutik' ),
			'OU' => __( 'Ouémé', 'wpboutik' ),
			'PL' => __( 'Plateau', 'wpboutik' ),
			'ZO' => __( 'Zou', 'wpboutik' ),
		),
		'BO' => array( // Bolivian states.
			'B' => __( 'Chuquisaca', 'wpboutik' ),
			'H' => __( 'Beni', 'wpboutik' ),
			'C' => __( 'Cochabamba', 'wpboutik' ),
			'L' => __( 'La Paz', 'wpboutik' ),
			'O' => __( 'Oruro', 'wpboutik' ),
			'N' => __( 'Pando', 'wpboutik' ),
			'P' => __( 'Potosí', 'wpboutik' ),
			'S' => __( 'Santa Cruz', 'wpboutik' ),
			'T' => __( 'Tarija', 'wpboutik' ),
		),
		'BR' => array( // Brazillian states.
			'AC' => __( 'Acre', 'wpboutik' ),
			'AL' => __( 'Alagoas', 'wpboutik' ),
			'AP' => __( 'Amapá', 'wpboutik' ),
			'AM' => __( 'Amazonas', 'wpboutik' ),
			'BA' => __( 'Bahia', 'wpboutik' ),
			'CE' => __( 'Ceará', 'wpboutik' ),
			'DF' => __( 'Distrito Federal', 'wpboutik' ),
			'ES' => __( 'Espírito Santo', 'wpboutik' ),
			'GO' => __( 'Goiás', 'wpboutik' ),
			'MA' => __( 'Maranhão', 'wpboutik' ),
			'MT' => __( 'Mato Grosso', 'wpboutik' ),
			'MS' => __( 'Mato Grosso do Sul', 'wpboutik' ),
			'MG' => __( 'Minas Gerais', 'wpboutik' ),
			'PA' => __( 'Pará', 'wpboutik' ),
			'PB' => __( 'Paraíba', 'wpboutik' ),
			'PR' => __( 'Paraná', 'wpboutik' ),
			'PE' => __( 'Pernambuco', 'wpboutik' ),
			'PI' => __( 'Piauí', 'wpboutik' ),
			'RJ' => __( 'Rio de Janeiro', 'wpboutik' ),
			'RN' => __( 'Rio Grande do Norte', 'wpboutik' ),
			'RS' => __( 'Rio Grande do Sul', 'wpboutik' ),
			'RO' => __( 'Rondônia', 'wpboutik' ),
			'RR' => __( 'Roraima', 'wpboutik' ),
			'SC' => __( 'Santa Catarina', 'wpboutik' ),
			'SP' => __( 'São Paulo', 'wpboutik' ),
			'SE' => __( 'Sergipe', 'wpboutik' ),
			'TO' => __( 'Tocantins', 'wpboutik' ),
		),
		'CA' => array( // Canadian states.
			'AB' => __( 'Alberta', 'wpboutik' ),
			'BC' => __( 'British Columbia', 'wpboutik' ),
			'MB' => __( 'Manitoba', 'wpboutik' ),
			'NB' => __( 'New Brunswick', 'wpboutik' ),
			'NL' => __( 'Newfoundland and Labrador', 'wpboutik' ),
			'NT' => __( 'Northwest Territories', 'wpboutik' ),
			'NS' => __( 'Nova Scotia', 'wpboutik' ),
			'NU' => __( 'Nunavut', 'wpboutik' ),
			'ON' => __( 'Ontario', 'wpboutik' ),
			'PE' => __( 'Prince Edward Island', 'wpboutik' ),
			'QC' => __( 'Quebec', 'wpboutik' ),
			'SK' => __( 'Saskatchewan', 'wpboutik' ),
			'YT' => __( 'Yukon Territory', 'wpboutik' ),
		),
		'CH' => array( // Cantons of Switzerland.
			'AG' => __( 'Aargau', 'wpboutik' ),
			'AR' => __( 'Appenzell Ausserrhoden', 'wpboutik' ),
			'AI' => __( 'Appenzell Innerrhoden', 'wpboutik' ),
			'BL' => __( 'Basel-Landschaft', 'wpboutik' ),
			'BS' => __( 'Basel-Stadt', 'wpboutik' ),
			'BE' => __( 'Bern', 'wpboutik' ),
			'FR' => __( 'Fribourg', 'wpboutik' ),
			'GE' => __( 'Geneva', 'wpboutik' ),
			'GL' => __( 'Glarus', 'wpboutik' ),
			'GR' => __( 'Graubünden', 'wpboutik' ),
			'JU' => __( 'Jura', 'wpboutik' ),
			'LU' => __( 'Luzern', 'wpboutik' ),
			'NE' => __( 'Neuchâtel', 'wpboutik' ),
			'NW' => __( 'Nidwalden', 'wpboutik' ),
			'OW' => __( 'Obwalden', 'wpboutik' ),
			'SH' => __( 'Schaffhausen', 'wpboutik' ),
			'SZ' => __( 'Schwyz', 'wpboutik' ),
			'SO' => __( 'Solothurn', 'wpboutik' ),
			'SG' => __( 'St. Gallen', 'wpboutik' ),
			'TG' => __( 'Thurgau', 'wpboutik' ),
			'TI' => __( 'Ticino', 'wpboutik' ),
			'UR' => __( 'Uri', 'wpboutik' ),
			'VS' => __( 'Valais', 'wpboutik' ),
			'VD' => __( 'Vaud', 'wpboutik' ),
			'ZG' => __( 'Zug', 'wpboutik' ),
			'ZH' => __( 'Zürich', 'wpboutik' ),
		),
		'CN' => array( // Chinese states.
			'CN1'  => __( 'Yunnan / 云南', 'wpboutik' ),
			'CN2'  => __( 'Beijing / 北京', 'wpboutik' ),
			'CN3'  => __( 'Tianjin / 天津', 'wpboutik' ),
			'CN4'  => __( 'Hebei / 河北', 'wpboutik' ),
			'CN5'  => __( 'Shanxi / 山西', 'wpboutik' ),
			'CN6'  => __( 'Inner Mongolia / 內蒙古', 'wpboutik' ),
			'CN7'  => __( 'Liaoning / 辽宁', 'wpboutik' ),
			'CN8'  => __( 'Jilin / 吉林', 'wpboutik' ),
			'CN9'  => __( 'Heilongjiang / 黑龙江', 'wpboutik' ),
			'CN10' => __( 'Shanghai / 上海', 'wpboutik' ),
			'CN11' => __( 'Jiangsu / 江苏', 'wpboutik' ),
			'CN12' => __( 'Zhejiang / 浙江', 'wpboutik' ),
			'CN13' => __( 'Anhui / 安徽', 'wpboutik' ),
			'CN14' => __( 'Fujian / 福建', 'wpboutik' ),
			'CN15' => __( 'Jiangxi / 江西', 'wpboutik' ),
			'CN16' => __( 'Shandong / 山东', 'wpboutik' ),
			'CN17' => __( 'Henan / 河南', 'wpboutik' ),
			'CN18' => __( 'Hubei / 湖北', 'wpboutik' ),
			'CN19' => __( 'Hunan / 湖南', 'wpboutik' ),
			'CN20' => __( 'Guangdong / 广东', 'wpboutik' ),
			'CN21' => __( 'Guangxi Zhuang / 广西壮族', 'wpboutik' ),
			'CN22' => __( 'Hainan / 海南', 'wpboutik' ),
			'CN23' => __( 'Chongqing / 重庆', 'wpboutik' ),
			'CN24' => __( 'Sichuan / 四川', 'wpboutik' ),
			'CN25' => __( 'Guizhou / 贵州', 'wpboutik' ),
			'CN26' => __( 'Shaanxi / 陕西', 'wpboutik' ),
			'CN27' => __( 'Gansu / 甘肃', 'wpboutik' ),
			'CN28' => __( 'Qinghai / 青海', 'wpboutik' ),
			'CN29' => __( 'Ningxia Hui / 宁夏', 'wpboutik' ),
			'CN30' => __( 'Macao / 澳门', 'wpboutik' ),
			'CN31' => __( 'Tibet / 西藏', 'wpboutik' ),
			'CN32' => __( 'Xinjiang / 新疆', 'wpboutik' ),
		),
		'CZ' => array(),
		'DE' => array(),
		'DK' => array(),
		'DZ' => array(
			'DZ-01' => __( 'Adrar', 'wpboutik' ),
			'DZ-02' => __( 'Chlef', 'wpboutik' ),
			'DZ-03' => __( 'Laghouat', 'wpboutik' ),
			'DZ-04' => __( 'Oum El Bouaghi', 'wpboutik' ),
			'DZ-05' => __( 'Batna', 'wpboutik' ),
			'DZ-06' => __( 'Béjaïa', 'wpboutik' ),
			'DZ-07' => __( 'Biskra', 'wpboutik' ),
			'DZ-08' => __( 'Béchar', 'wpboutik' ),
			'DZ-09' => __( 'Blida', 'wpboutik' ),
			'DZ-10' => __( 'Bouira', 'wpboutik' ),
			'DZ-11' => __( 'Tamanghasset', 'wpboutik' ),
			'DZ-12' => __( 'Tébessa', 'wpboutik' ),
			'DZ-13' => __( 'Tlemcen', 'wpboutik' ),
			'DZ-14' => __( 'Tiaret', 'wpboutik' ),
			'DZ-15' => __( 'Tizi Ouzou', 'wpboutik' ),
			'DZ-16' => __( 'Algiers', 'wpboutik' ),
			'DZ-17' => __( 'Djelfa', 'wpboutik' ),
			'DZ-18' => __( 'Jijel', 'wpboutik' ),
			'DZ-19' => __( 'Sétif', 'wpboutik' ),
			'DZ-20' => __( 'Saïda', 'wpboutik' ),
			'DZ-21' => __( 'Skikda', 'wpboutik' ),
			'DZ-22' => __( 'Sidi Bel Abbès', 'wpboutik' ),
			'DZ-23' => __( 'Annaba', 'wpboutik' ),
			'DZ-24' => __( 'Guelma', 'wpboutik' ),
			'DZ-25' => __( 'Constantine', 'wpboutik' ),
			'DZ-26' => __( 'Médéa', 'wpboutik' ),
			'DZ-27' => __( 'Mostaganem', 'wpboutik' ),
			'DZ-28' => __( 'M’Sila', 'wpboutik' ),
			'DZ-29' => __( 'Mascara', 'wpboutik' ),
			'DZ-30' => __( 'Ouargla', 'wpboutik' ),
			'DZ-31' => __( 'Oran', 'wpboutik' ),
			'DZ-32' => __( 'El Bayadh', 'wpboutik' ),
			'DZ-33' => __( 'Illizi', 'wpboutik' ),
			'DZ-34' => __( 'Bordj Bou Arréridj', 'wpboutik' ),
			'DZ-35' => __( 'Boumerdès', 'wpboutik' ),
			'DZ-36' => __( 'El Tarf', 'wpboutik' ),
			'DZ-37' => __( 'Tindouf', 'wpboutik' ),
			'DZ-38' => __( 'Tissemsilt', 'wpboutik' ),
			'DZ-39' => __( 'El Oued', 'wpboutik' ),
			'DZ-40' => __( 'Khenchela', 'wpboutik' ),
			'DZ-41' => __( 'Souk Ahras', 'wpboutik' ),
			'DZ-42' => __( 'Tipasa', 'wpboutik' ),
			'DZ-43' => __( 'Mila', 'wpboutik' ),
			'DZ-44' => __( 'Aïn Defla', 'wpboutik' ),
			'DZ-45' => __( 'Naama', 'wpboutik' ),
			'DZ-46' => __( 'Aïn Témouchent', 'wpboutik' ),
			'DZ-47' => __( 'Ghardaïa', 'wpboutik' ),
			'DZ-48' => __( 'Relizane', 'wpboutik' ),
		),
		'EE' => array(),
		'EG' => array( // Egypt states.
			'EGALX' => __( 'Alexandria', 'wpboutik' ),
			'EGASN' => __( 'Aswan', 'wpboutik' ),
			'EGAST' => __( 'Asyut', 'wpboutik' ),
			'EGBA'  => __( 'Red Sea', 'wpboutik' ),
			'EGBH'  => __( 'Beheira', 'wpboutik' ),
			'EGBNS' => __( 'Beni Suef', 'wpboutik' ),
			'EGC'   => __( 'Cairo', 'wpboutik' ),
			'EGDK'  => __( 'Dakahlia', 'wpboutik' ),
			'EGDT'  => __( 'Damietta', 'wpboutik' ),
			'EGFYM' => __( 'Faiyum', 'wpboutik' ),
			'EGGH'  => __( 'Gharbia', 'wpboutik' ),
			'EGGZ'  => __( 'Giza', 'wpboutik' ),
			'EGIS'  => __( 'Ismailia', 'wpboutik' ),
			'EGJS'  => __( 'South Sinai', 'wpboutik' ),
			'EGKB'  => __( 'Qalyubia', 'wpboutik' ),
			'EGKFS' => __( 'Kafr el-Sheikh', 'wpboutik' ),
			'EGKN'  => __( 'Qena', 'wpboutik' ),
			'EGLX'  => __( 'Luxor', 'wpboutik' ),
			'EGMN'  => __( 'Minya', 'wpboutik' ),
			'EGMNF' => __( 'Monufia', 'wpboutik' ),
			'EGMT'  => __( 'Matrouh', 'wpboutik' ),
			'EGPTS' => __( 'Port Said', 'wpboutik' ),
			'EGSHG' => __( 'Sohag', 'wpboutik' ),
			'EGSHR' => __( 'Al Sharqia', 'wpboutik' ),
			'EGSIN' => __( 'North Sinai', 'wpboutik' ),
			'EGSUZ' => __( 'Suez', 'wpboutik' ),
			'EGWAD' => __( 'New Valley', 'wpboutik' ),
		),
		'ES' => array( // Spanish states.
			'C'  => __( 'A Coruña', 'wpboutik' ),
			'VI' => __( 'Araba/Álava', 'wpboutik' ),
			'AB' => __( 'Albacete', 'wpboutik' ),
			'A'  => __( 'Alicante', 'wpboutik' ),
			'AL' => __( 'Almería', 'wpboutik' ),
			'O'  => __( 'Asturias', 'wpboutik' ),
			'AV' => __( 'Ávila', 'wpboutik' ),
			'BA' => __( 'Badajoz', 'wpboutik' ),
			'PM' => __( 'Baleares', 'wpboutik' ),
			'B'  => __( 'Barcelona', 'wpboutik' ),
			'BU' => __( 'Burgos', 'wpboutik' ),
			'CC' => __( 'Cáceres', 'wpboutik' ),
			'CA' => __( 'Cádiz', 'wpboutik' ),
			'S'  => __( 'Cantabria', 'wpboutik' ),
			'CS' => __( 'Castellón', 'wpboutik' ),
			'CE' => __( 'Ceuta', 'wpboutik' ),
			'CR' => __( 'Ciudad Real', 'wpboutik' ),
			'CO' => __( 'Córdoba', 'wpboutik' ),
			'CU' => __( 'Cuenca', 'wpboutik' ),
			'GI' => __( 'Girona', 'wpboutik' ),
			'GR' => __( 'Granada', 'wpboutik' ),
			'GU' => __( 'Guadalajara', 'wpboutik' ),
			'SS' => __( 'Gipuzkoa', 'wpboutik' ),
			'H'  => __( 'Huelva', 'wpboutik' ),
			'HU' => __( 'Huesca', 'wpboutik' ),
			'J'  => __( 'Jaén', 'wpboutik' ),
			'LO' => __( 'La Rioja', 'wpboutik' ),
			'GC' => __( 'Las Palmas', 'wpboutik' ),
			'LE' => __( 'León', 'wpboutik' ),
			'L'  => __( 'Lleida', 'wpboutik' ),
			'LU' => __( 'Lugo', 'wpboutik' ),
			'M'  => __( 'Madrid', 'wpboutik' ),
			'MA' => __( 'Málaga', 'wpboutik' ),
			'ML' => __( 'Melilla', 'wpboutik' ),
			'MU' => __( 'Murcia', 'wpboutik' ),
			'NA' => __( 'Navarra', 'wpboutik' ),
			'OR' => __( 'Ourense', 'wpboutik' ),
			'P'  => __( 'Palencia', 'wpboutik' ),
			'PO' => __( 'Pontevedra', 'wpboutik' ),
			'SA' => __( 'Salamanca', 'wpboutik' ),
			'TF' => __( 'Santa Cruz de Tenerife', 'wpboutik' ),
			'SG' => __( 'Segovia', 'wpboutik' ),
			'SE' => __( 'Sevilla', 'wpboutik' ),
			'SO' => __( 'Soria', 'wpboutik' ),
			'T'  => __( 'Tarragona', 'wpboutik' ),
			'TE' => __( 'Teruel', 'wpboutik' ),
			'TO' => __( 'Toledo', 'wpboutik' ),
			'V'  => __( 'Valencia', 'wpboutik' ),
			'VA' => __( 'Valladolid', 'wpboutik' ),
			'BI' => __( 'Biscay', 'wpboutik' ),
			'ZA' => __( 'Zamora', 'wpboutik' ),
			'Z'  => __( 'Zaragoza', 'wpboutik' ),
		),
		'FI' => array(),
		'FR' => array(),
		'GH' => array( // Ghanaian Regions.
			'AF' => __( 'Ahafo', 'wpboutik' ),
			'AH' => __( 'Ashanti', 'wpboutik' ),
			'BA' => __( 'Brong-Ahafo', 'wpboutik' ),
			'BO' => __( 'Bono', 'wpboutik' ),
			'BE' => __( 'Bono East', 'wpboutik' ),
			'CP' => __( 'Central', 'wpboutik' ),
			'EP' => __( 'Eastern', 'wpboutik' ),
			'AA' => __( 'Greater Accra', 'wpboutik' ),
			'NE' => __( 'North East', 'wpboutik' ),
			'NP' => __( 'Northern', 'wpboutik' ),
			'OT' => __( 'Oti', 'wpboutik' ),
			'SV' => __( 'Savannah', 'wpboutik' ),
			'UE' => __( 'Upper East', 'wpboutik' ),
			'UW' => __( 'Upper West', 'wpboutik' ),
			'TV' => __( 'Volta', 'wpboutik' ),
			'WP' => __( 'Western', 'wpboutik' ),
			'WN' => __( 'Western North', 'wpboutik' ),
		),
		'GP' => array(),
		'GR' => array( // Greek Regions.
			'I' => __( 'Attica', 'wpboutik' ),
			'A' => __( 'East Macedonia and Thrace', 'wpboutik' ),
			'B' => __( 'Central Macedonia', 'wpboutik' ),
			'C' => __( 'West Macedonia', 'wpboutik' ),
			'D' => __( 'Epirus', 'wpboutik' ),
			'E' => __( 'Thessaly', 'wpboutik' ),
			'F' => __( 'Ionian Islands', 'wpboutik' ),
			'G' => __( 'West Greece', 'wpboutik' ),
			'H' => __( 'Central Greece', 'wpboutik' ),
			'J' => __( 'Peloponnese', 'wpboutik' ),
			'K' => __( 'North Aegean', 'wpboutik' ),
			'L' => __( 'South Aegean', 'wpboutik' ),
			'M' => __( 'Crete', 'wpboutik' ),
		),
		'GF' => array(),
		'HK' => array( // Hong Kong states.
			'HONG KONG'       => __( 'Hong Kong Island', 'wpboutik' ),
			'KOWLOON'         => __( 'Kowloon', 'wpboutik' ),
			'NEW TERRITORIES' => __( 'New Territories', 'wpboutik' ),
		),
		'HU' => array( // Hungary states.
			'BK' => __( 'Bács-Kiskun', 'wpboutik' ),
			'BE' => __( 'Békés', 'wpboutik' ),
			'BA' => __( 'Baranya', 'wpboutik' ),
			'BZ' => __( 'Borsod-Abaúj-Zemplén', 'wpboutik' ),
			'BU' => __( 'Budapest', 'wpboutik' ),
			'CS' => __( 'Csongrád-Csanád', 'wpboutik' ),
			'FE' => __( 'Fejér', 'wpboutik' ),
			'GS' => __( 'Győr-Moson-Sopron', 'wpboutik' ),
			'HB' => __( 'Hajdú-Bihar', 'wpboutik' ),
			'HE' => __( 'Heves', 'wpboutik' ),
			'JN' => __( 'Jász-Nagykun-Szolnok', 'wpboutik' ),
			'KE' => __( 'Komárom-Esztergom', 'wpboutik' ),
			'NO' => __( 'Nógrád', 'wpboutik' ),
			'PE' => __( 'Pest', 'wpboutik' ),
			'SO' => __( 'Somogy', 'wpboutik' ),
			'SZ' => __( 'Szabolcs-Szatmár-Bereg', 'wpboutik' ),
			'TO' => __( 'Tolna', 'wpboutik' ),
			'VA' => __( 'Vas', 'wpboutik' ),
			'VE' => __( 'Veszprém', 'wpboutik' ),
			'ZA' => __( 'Zala', 'wpboutik' ),
		),
		'ID' => array( // Indonesia Provinces.
			'AC' => __( 'Daerah Istimewa Aceh', 'wpboutik' ),
			'SU' => __( 'Sumatera Utara', 'wpboutik' ),
			'SB' => __( 'Sumatera Barat', 'wpboutik' ),
			'RI' => __( 'Riau', 'wpboutik' ),
			'KR' => __( 'Kepulauan Riau', 'wpboutik' ),
			'JA' => __( 'Jambi', 'wpboutik' ),
			'SS' => __( 'Sumatera Selatan', 'wpboutik' ),
			'BB' => __( 'Bangka Belitung', 'wpboutik' ),
			'BE' => __( 'Bengkulu', 'wpboutik' ),
			'LA' => __( 'Lampung', 'wpboutik' ),
			'JK' => __( 'DKI Jakarta', 'wpboutik' ),
			'JB' => __( 'Jawa Barat', 'wpboutik' ),
			'BT' => __( 'Banten', 'wpboutik' ),
			'JT' => __( 'Jawa Tengah', 'wpboutik' ),
			'JI' => __( 'Jawa Timur', 'wpboutik' ),
			'YO' => __( 'Daerah Istimewa Yogyakarta', 'wpboutik' ),
			'BA' => __( 'Bali', 'wpboutik' ),
			'NB' => __( 'Nusa Tenggara Barat', 'wpboutik' ),
			'NT' => __( 'Nusa Tenggara Timur', 'wpboutik' ),
			'KB' => __( 'Kalimantan Barat', 'wpboutik' ),
			'KT' => __( 'Kalimantan Tengah', 'wpboutik' ),
			'KI' => __( 'Kalimantan Timur', 'wpboutik' ),
			'KS' => __( 'Kalimantan Selatan', 'wpboutik' ),
			'KU' => __( 'Kalimantan Utara', 'wpboutik' ),
			'SA' => __( 'Sulawesi Utara', 'wpboutik' ),
			'ST' => __( 'Sulawesi Tengah', 'wpboutik' ),
			'SG' => __( 'Sulawesi Tenggara', 'wpboutik' ),
			'SR' => __( 'Sulawesi Barat', 'wpboutik' ),
			'SN' => __( 'Sulawesi Selatan', 'wpboutik' ),
			'GO' => __( 'Gorontalo', 'wpboutik' ),
			'MA' => __( 'Maluku', 'wpboutik' ),
			'MU' => __( 'Maluku Utara', 'wpboutik' ),
			'PA' => __( 'Papua', 'wpboutik' ),
			'PB' => __( 'Papua Barat', 'wpboutik' ),
		),
		'IE' => array( // Republic of Ireland.
			'CW' => __( 'Carlow', 'wpboutik' ),
			'CN' => __( 'Cavan', 'wpboutik' ),
			'CE' => __( 'Clare', 'wpboutik' ),
			'CO' => __( 'Cork', 'wpboutik' ),
			'DL' => __( 'Donegal', 'wpboutik' ),
			'D'  => __( 'Dublin', 'wpboutik' ),
			'G'  => __( 'Galway', 'wpboutik' ),
			'KY' => __( 'Kerry', 'wpboutik' ),
			'KE' => __( 'Kildare', 'wpboutik' ),
			'KK' => __( 'Kilkenny', 'wpboutik' ),
			'LS' => __( 'Laois', 'wpboutik' ),
			'LM' => __( 'Leitrim', 'wpboutik' ),
			'LK' => __( 'Limerick', 'wpboutik' ),
			'LD' => __( 'Longford', 'wpboutik' ),
			'LH' => __( 'Louth', 'wpboutik' ),
			'MO' => __( 'Mayo', 'wpboutik' ),
			'MH' => __( 'Meath', 'wpboutik' ),
			'MN' => __( 'Monaghan', 'wpboutik' ),
			'OY' => __( 'Offaly', 'wpboutik' ),
			'RN' => __( 'Roscommon', 'wpboutik' ),
			'SO' => __( 'Sligo', 'wpboutik' ),
			'TA' => __( 'Tipperary', 'wpboutik' ),
			'WD' => __( 'Waterford', 'wpboutik' ),
			'WH' => __( 'Westmeath', 'wpboutik' ),
			'WX' => __( 'Wexford', 'wpboutik' ),
			'WW' => __( 'Wicklow', 'wpboutik' ),
		),
		'IN' => array( // Indian states.
			'AP' => __( 'Andhra Pradesh', 'wpboutik' ),
			'AR' => __( 'Arunachal Pradesh', 'wpboutik' ),
			'AS' => __( 'Assam', 'wpboutik' ),
			'BR' => __( 'Bihar', 'wpboutik' ),
			'CT' => __( 'Chhattisgarh', 'wpboutik' ),
			'GA' => __( 'Goa', 'wpboutik' ),
			'GJ' => __( 'Gujarat', 'wpboutik' ),
			'HR' => __( 'Haryana', 'wpboutik' ),
			'HP' => __( 'Himachal Pradesh', 'wpboutik' ),
			'JK' => __( 'Jammu and Kashmir', 'wpboutik' ),
			'JH' => __( 'Jharkhand', 'wpboutik' ),
			'KA' => __( 'Karnataka', 'wpboutik' ),
			'KL' => __( 'Kerala', 'wpboutik' ),
			'MP' => __( 'Madhya Pradesh', 'wpboutik' ),
			'MH' => __( 'Maharashtra', 'wpboutik' ),
			'MN' => __( 'Manipur', 'wpboutik' ),
			'ML' => __( 'Meghalaya', 'wpboutik' ),
			'MZ' => __( 'Mizoram', 'wpboutik' ),
			'NL' => __( 'Nagaland', 'wpboutik' ),
			'OR' => __( 'Orissa', 'wpboutik' ),
			'PB' => __( 'Punjab', 'wpboutik' ),
			'RJ' => __( 'Rajasthan', 'wpboutik' ),
			'SK' => __( 'Sikkim', 'wpboutik' ),
			'TN' => __( 'Tamil Nadu', 'wpboutik' ),
			'TS' => __( 'Telangana', 'wpboutik' ),
			'TR' => __( 'Tripura', 'wpboutik' ),
			'UK' => __( 'Uttarakhand', 'wpboutik' ),
			'UP' => __( 'Uttar Pradesh', 'wpboutik' ),
			'WB' => __( 'West Bengal', 'wpboutik' ),
			'AN' => __( 'Andaman and Nicobar Islands', 'wpboutik' ),
			'CH' => __( 'Chandigarh', 'wpboutik' ),
			'DN' => __( 'Dadra and Nagar Haveli', 'wpboutik' ),
			'DD' => __( 'Daman and Diu', 'wpboutik' ),
			'DL' => __( 'Delhi', 'wpboutik' ),
			'LD' => __( 'Lakshadeep', 'wpboutik' ),
			'PY' => __( 'Pondicherry (Puducherry)', 'wpboutik' ),
		),
		'IR' => array( // Iran States.
			'KHZ' => __( 'Khuzestan  (خوزستان)', 'wpboutik' ),
			'THR' => __( 'Tehran  (تهران)', 'wpboutik' ),
			'ILM' => __( 'Ilaam (ایلام)', 'wpboutik' ),
			'BHR' => __( 'Bushehr (بوشهر)', 'wpboutik' ),
			'ADL' => __( 'Ardabil (اردبیل)', 'wpboutik' ),
			'ESF' => __( 'Isfahan (اصفهان)', 'wpboutik' ),
			'YZD' => __( 'Yazd (یزد)', 'wpboutik' ),
			'KRH' => __( 'Kermanshah (کرمانشاه)', 'wpboutik' ),
			'KRN' => __( 'Kerman (کرمان)', 'wpboutik' ),
			'HDN' => __( 'Hamadan (همدان)', 'wpboutik' ),
			'GZN' => __( 'Ghazvin (قزوین)', 'wpboutik' ),
			'ZJN' => __( 'Zanjan (زنجان)', 'wpboutik' ),
			'LRS' => __( 'Luristan (لرستان)', 'wpboutik' ),
			'ABZ' => __( 'Alborz (البرز)', 'wpboutik' ),
			'EAZ' => __( 'East Azarbaijan (آذربایجان شرقی)', 'wpboutik' ),
			'WAZ' => __( 'West Azarbaijan (آذربایجان غربی)', 'wpboutik' ),
			'CHB' => __( 'Chaharmahal and Bakhtiari (چهارمحال و بختیاری)', 'wpboutik' ),
			'SKH' => __( 'South Khorasan (خراسان جنوبی)', 'wpboutik' ),
			'RKH' => __( 'Razavi Khorasan (خراسان رضوی)', 'wpboutik' ),
			'NKH' => __( 'North Khorasan (خراسان شمالی)', 'wpboutik' ),
			'SMN' => __( 'Semnan (سمنان)', 'wpboutik' ),
			'FRS' => __( 'Fars (فارس)', 'wpboutik' ),
			'QHM' => __( 'Qom (قم)', 'wpboutik' ),
			'KRD' => __( 'Kurdistan / کردستان)', 'wpboutik' ),
			'KBD' => __( 'Kohgiluyeh and BoyerAhmad (کهگیلوییه و بویراحمد)', 'wpboutik' ),
			'GLS' => __( 'Golestan (گلستان)', 'wpboutik' ),
			'GIL' => __( 'Gilan (گیلان)', 'wpboutik' ),
			'MZN' => __( 'Mazandaran (مازندران)', 'wpboutik' ),
			'MKZ' => __( 'Markazi (مرکزی)', 'wpboutik' ),
			'HRZ' => __( 'Hormozgan (هرمزگان)', 'wpboutik' ),
			'SBN' => __( 'Sistan and Baluchestan (سیستان و بلوچستان)', 'wpboutik' ),
		),
		'IS' => array(),
		'IT' => array( // Italy Provinces.
			'AG' => __( 'Agrigento', 'wpboutik' ),
			'AL' => __( 'Alessandria', 'wpboutik' ),
			'AN' => __( 'Ancona', 'wpboutik' ),
			'AO' => __( 'Aosta', 'wpboutik' ),
			'AR' => __( 'Arezzo', 'wpboutik' ),
			'AP' => __( 'Ascoli Piceno', 'wpboutik' ),
			'AT' => __( 'Asti', 'wpboutik' ),
			'AV' => __( 'Avellino', 'wpboutik' ),
			'BA' => __( 'Bari', 'wpboutik' ),
			'BT' => __( 'Barletta-Andria-Trani', 'wpboutik' ),
			'BL' => __( 'Belluno', 'wpboutik' ),
			'BN' => __( 'Benevento', 'wpboutik' ),
			'BG' => __( 'Bergamo', 'wpboutik' ),
			'BI' => __( 'Biella', 'wpboutik' ),
			'BO' => __( 'Bologna', 'wpboutik' ),
			'BZ' => __( 'Bolzano', 'wpboutik' ),
			'BS' => __( 'Brescia', 'wpboutik' ),
			'BR' => __( 'Brindisi', 'wpboutik' ),
			'CA' => __( 'Cagliari', 'wpboutik' ),
			'CL' => __( 'Caltanissetta', 'wpboutik' ),
			'CB' => __( 'Campobasso', 'wpboutik' ),
			'CE' => __( 'Caserta', 'wpboutik' ),
			'CT' => __( 'Catania', 'wpboutik' ),
			'CZ' => __( 'Catanzaro', 'wpboutik' ),
			'CH' => __( 'Chieti', 'wpboutik' ),
			'CO' => __( 'Como', 'wpboutik' ),
			'CS' => __( 'Cosenza', 'wpboutik' ),
			'CR' => __( 'Cremona', 'wpboutik' ),
			'KR' => __( 'Crotone', 'wpboutik' ),
			'CN' => __( 'Cuneo', 'wpboutik' ),
			'EN' => __( 'Enna', 'wpboutik' ),
			'FM' => __( 'Fermo', 'wpboutik' ),
			'FE' => __( 'Ferrara', 'wpboutik' ),
			'FI' => __( 'Firenze', 'wpboutik' ),
			'FG' => __( 'Foggia', 'wpboutik' ),
			'FC' => __( 'Forlì-Cesena', 'wpboutik' ),
			'FR' => __( 'Frosinone', 'wpboutik' ),
			'GE' => __( 'Genova', 'wpboutik' ),
			'GO' => __( 'Gorizia', 'wpboutik' ),
			'GR' => __( 'Grosseto', 'wpboutik' ),
			'IM' => __( 'Imperia', 'wpboutik' ),
			'IS' => __( 'Isernia', 'wpboutik' ),
			'SP' => __( 'La Spezia', 'wpboutik' ),
			'AQ' => __( "L'Aquila", 'wpboutik' ),
			'LT' => __( 'Latina', 'wpboutik' ),
			'LE' => __( 'Lecce', 'wpboutik' ),
			'LC' => __( 'Lecco', 'wpboutik' ),
			'LI' => __( 'Livorno', 'wpboutik' ),
			'LO' => __( 'Lodi', 'wpboutik' ),
			'LU' => __( 'Lucca', 'wpboutik' ),
			'MC' => __( 'Macerata', 'wpboutik' ),
			'MN' => __( 'Mantova', 'wpboutik' ),
			'MS' => __( 'Massa-Carrara', 'wpboutik' ),
			'MT' => __( 'Matera', 'wpboutik' ),
			'ME' => __( 'Messina', 'wpboutik' ),
			'MI' => __( 'Milano', 'wpboutik' ),
			'MO' => __( 'Modena', 'wpboutik' ),
			'MB' => __( 'Monza e della Brianza', 'wpboutik' ),
			'NA' => __( 'Napoli', 'wpboutik' ),
			'NO' => __( 'Novara', 'wpboutik' ),
			'NU' => __( 'Nuoro', 'wpboutik' ),
			'OR' => __( 'Oristano', 'wpboutik' ),
			'PD' => __( 'Padova', 'wpboutik' ),
			'PA' => __( 'Palermo', 'wpboutik' ),
			'PR' => __( 'Parma', 'wpboutik' ),
			'PV' => __( 'Pavia', 'wpboutik' ),
			'PG' => __( 'Perugia', 'wpboutik' ),
			'PU' => __( 'Pesaro e Urbino', 'wpboutik' ),
			'PE' => __( 'Pescara', 'wpboutik' ),
			'PC' => __( 'Piacenza', 'wpboutik' ),
			'PI' => __( 'Pisa', 'wpboutik' ),
			'PT' => __( 'Pistoia', 'wpboutik' ),
			'PN' => __( 'Pordenone', 'wpboutik' ),
			'PZ' => __( 'Potenza', 'wpboutik' ),
			'PO' => __( 'Prato', 'wpboutik' ),
			'RG' => __( 'Ragusa', 'wpboutik' ),
			'RA' => __( 'Ravenna', 'wpboutik' ),
			'RC' => __( 'Reggio Calabria', 'wpboutik' ),
			'RE' => __( 'Reggio Emilia', 'wpboutik' ),
			'RI' => __( 'Rieti', 'wpboutik' ),
			'RN' => __( 'Rimini', 'wpboutik' ),
			'RM' => __( 'Roma', 'wpboutik' ),
			'RO' => __( 'Rovigo', 'wpboutik' ),
			'SA' => __( 'Salerno', 'wpboutik' ),
			'SS' => __( 'Sassari', 'wpboutik' ),
			'SV' => __( 'Savona', 'wpboutik' ),
			'SI' => __( 'Siena', 'wpboutik' ),
			'SR' => __( 'Siracusa', 'wpboutik' ),
			'SO' => __( 'Sondrio', 'wpboutik' ),
			'SU' => __( 'Sud Sardegna', 'wpboutik' ),
			'TA' => __( 'Taranto', 'wpboutik' ),
			'TE' => __( 'Teramo', 'wpboutik' ),
			'TR' => __( 'Terni', 'wpboutik' ),
			'TO' => __( 'Torino', 'wpboutik' ),
			'TP' => __( 'Trapani', 'wpboutik' ),
			'TN' => __( 'Trento', 'wpboutik' ),
			'TV' => __( 'Treviso', 'wpboutik' ),
			'TS' => __( 'Trieste', 'wpboutik' ),
			'UD' => __( 'Udine', 'wpboutik' ),
			'VA' => __( 'Varese', 'wpboutik' ),
			'VE' => __( 'Venezia', 'wpboutik' ),
			'VB' => __( 'Verbano-Cusio-Ossola', 'wpboutik' ),
			'VC' => __( 'Vercelli', 'wpboutik' ),
			'VR' => __( 'Verona', 'wpboutik' ),
			'VV' => __( 'Vibo Valentia', 'wpboutik' ),
			'VI' => __( 'Vicenza', 'wpboutik' ),
			'VT' => __( 'Viterbo', 'wpboutik' ),
		),
		'IL' => array(),
		'IM' => array(),

		/**
		 * Japan States.
		 *
		 * English notation of prefectures conform to the notation of Japan Post.
		 * The suffix corresponds with the Japanese translation file.
		 */
		'JP' => array(
			'JP01' => __( 'Hokkaido', 'wpboutik' ),
			'JP02' => __( 'Aomori', 'wpboutik' ),
			'JP03' => __( 'Iwate', 'wpboutik' ),
			'JP04' => __( 'Miyagi', 'wpboutik' ),
			'JP05' => __( 'Akita', 'wpboutik' ),
			'JP06' => __( 'Yamagata', 'wpboutik' ),
			'JP07' => __( 'Fukushima', 'wpboutik' ),
			'JP08' => __( 'Ibaraki', 'wpboutik' ),
			'JP09' => __( 'Tochigi', 'wpboutik' ),
			'JP10' => __( 'Gunma', 'wpboutik' ),
			'JP11' => __( 'Saitama', 'wpboutik' ),
			'JP12' => __( 'Chiba', 'wpboutik' ),
			'JP13' => __( 'Tokyo', 'wpboutik' ),
			'JP14' => __( 'Kanagawa', 'wpboutik' ),
			'JP15' => __( 'Niigata', 'wpboutik' ),
			'JP16' => __( 'Toyama', 'wpboutik' ),
			'JP17' => __( 'Ishikawa', 'wpboutik' ),
			'JP18' => __( 'Fukui', 'wpboutik' ),
			'JP19' => __( 'Yamanashi', 'wpboutik' ),
			'JP20' => __( 'Nagano', 'wpboutik' ),
			'JP21' => __( 'Gifu', 'wpboutik' ),
			'JP22' => __( 'Shizuoka', 'wpboutik' ),
			'JP23' => __( 'Aichi', 'wpboutik' ),
			'JP24' => __( 'Mie', 'wpboutik' ),
			'JP25' => __( 'Shiga', 'wpboutik' ),
			'JP26' => __( 'Kyoto', 'wpboutik' ),
			'JP27' => __( 'Osaka', 'wpboutik' ),
			'JP28' => __( 'Hyogo', 'wpboutik' ),
			'JP29' => __( 'Nara', 'wpboutik' ),
			'JP30' => __( 'Wakayama', 'wpboutik' ),
			'JP31' => __( 'Tottori', 'wpboutik' ),
			'JP32' => __( 'Shimane', 'wpboutik' ),
			'JP33' => __( 'Okayama', 'wpboutik' ),
			'JP34' => __( 'Hiroshima', 'wpboutik' ),
			'JP35' => __( 'Yamaguchi', 'wpboutik' ),
			'JP36' => __( 'Tokushima', 'wpboutik' ),
			'JP37' => __( 'Kagawa', 'wpboutik' ),
			'JP38' => __( 'Ehime', 'wpboutik' ),
			'JP39' => __( 'Kochi', 'wpboutik' ),
			'JP40' => __( 'Fukuoka', 'wpboutik' ),
			'JP41' => __( 'Saga', 'wpboutik' ),
			'JP42' => __( 'Nagasaki', 'wpboutik' ),
			'JP43' => __( 'Kumamoto', 'wpboutik' ),
			'JP44' => __( 'Oita', 'wpboutik' ),
			'JP45' => __( 'Miyazaki', 'wpboutik' ),
			'JP46' => __( 'Kagoshima', 'wpboutik' ),
			'JP47' => __( 'Okinawa', 'wpboutik' ),
		),
		'KE' => array( // Kenya counties.
			'KE01' => __( 'Baringo', 'wpboutik' ),
			'KE02' => __( 'Bomet', 'wpboutik' ),
			'KE03' => __( 'Bungoma', 'wpboutik' ),
			'KE04' => __( 'Busia', 'wpboutik' ),
			'KE05' => __( 'Elgeyo-Marakwet', 'wpboutik' ),
			'KE06' => __( 'Embu', 'wpboutik' ),
			'KE07' => __( 'Garissa', 'wpboutik' ),
			'KE08' => __( 'Homa Bay', 'wpboutik' ),
			'KE09' => __( 'Isiolo', 'wpboutik' ),
			'KE10' => __( 'Kajiado', 'wpboutik' ),
			'KE11' => __( 'Kakamega', 'wpboutik' ),
			'KE12' => __( 'Kericho', 'wpboutik' ),
			'KE13' => __( 'Kiambu', 'wpboutik' ),
			'KE14' => __( 'Kilifi', 'wpboutik' ),
			'KE15' => __( 'Kirinyaga', 'wpboutik' ),
			'KE16' => __( 'Kisii', 'wpboutik' ),
			'KE17' => __( 'Kisumu', 'wpboutik' ),
			'KE18' => __( 'Kitui', 'wpboutik' ),
			'KE19' => __( 'Kwale', 'wpboutik' ),
			'KE20' => __( 'Laikipia', 'wpboutik' ),
			'KE21' => __( 'Lamu', 'wpboutik' ),
			'KE22' => __( 'Machakos', 'wpboutik' ),
			'KE23' => __( 'Makueni', 'wpboutik' ),
			'KE24' => __( 'Mandera', 'wpboutik' ),
			'KE25' => __( 'Marsabit', 'wpboutik' ),
			'KE26' => __( 'Meru', 'wpboutik' ),
			'KE27' => __( 'Migori', 'wpboutik' ),
			'KE28' => __( 'Mombasa', 'wpboutik' ),
			'KE29' => __( 'Murang’a', 'wpboutik' ),
			'KE30' => __( 'Nairobi County', 'wpboutik' ),
			'KE31' => __( 'Nakuru', 'wpboutik' ),
			'KE32' => __( 'Nandi', 'wpboutik' ),
			'KE33' => __( 'Narok', 'wpboutik' ),
			'KE34' => __( 'Nyamira', 'wpboutik' ),
			'KE35' => __( 'Nyandarua', 'wpboutik' ),
			'KE36' => __( 'Nyeri', 'wpboutik' ),
			'KE37' => __( 'Samburu', 'wpboutik' ),
			'KE38' => __( 'Siaya', 'wpboutik' ),
			'KE39' => __( 'Taita-Taveta', 'wpboutik' ),
			'KE40' => __( 'Tana River', 'wpboutik' ),
			'KE41' => __( 'Tharaka-Nithi', 'wpboutik' ),
			'KE42' => __( 'Trans Nzoia', 'wpboutik' ),
			'KE43' => __( 'Turkana', 'wpboutik' ),
			'KE44' => __( 'Uasin Gishu', 'wpboutik' ),
			'KE45' => __( 'Vihiga', 'wpboutik' ),
			'KE46' => __( 'Wajir', 'wpboutik' ),
			'KE47' => __( 'West Pokot', 'wpboutik' ),
		),
		'KR' => array(),
		'KW' => array(),
		'LA' => array(
			'AT' => __( 'Attapeu', 'wpboutik' ),
			'BK' => __( 'Bokeo', 'wpboutik' ),
			'BL' => __( 'Bolikhamsai', 'wpboutik' ),
			'CH' => __( 'Champasak', 'wpboutik' ),
			'HO' => __( 'Houaphanh', 'wpboutik' ),
			'KH' => __( 'Khammouane', 'wpboutik' ),
			'LM' => __( 'Luang Namtha', 'wpboutik' ),
			'LP' => __( 'Luang Prabang', 'wpboutik' ),
			'OU' => __( 'Oudomxay', 'wpboutik' ),
			'PH' => __( 'Phongsaly', 'wpboutik' ),
			'SL' => __( 'Salavan', 'wpboutik' ),
			'SV' => __( 'Savannakhet', 'wpboutik' ),
			'VI' => __( 'Vientiane Province', 'wpboutik' ),
			'VT' => __( 'Vientiane', 'wpboutik' ),
			'XA' => __( 'Sainyabuli', 'wpboutik' ),
			'XE' => __( 'Sekong', 'wpboutik' ),
			'XI' => __( 'Xiangkhouang', 'wpboutik' ),
			'XS' => __( 'Xaisomboun', 'wpboutik' ),
		),
		'LB' => array(),
		'LR' => array( // Liberia provinces.
			'BM' => __( 'Bomi', 'wpboutik' ),
			'BN' => __( 'Bong', 'wpboutik' ),
			'GA' => __( 'Gbarpolu', 'wpboutik' ),
			'GB' => __( 'Grand Bassa', 'wpboutik' ),
			'GC' => __( 'Grand Cape Mount', 'wpboutik' ),
			'GG' => __( 'Grand Gedeh', 'wpboutik' ),
			'GK' => __( 'Grand Kru', 'wpboutik' ),
			'LO' => __( 'Lofa', 'wpboutik' ),
			'MA' => __( 'Margibi', 'wpboutik' ),
			'MY' => __( 'Maryland', 'wpboutik' ),
			'MO' => __( 'Montserrado', 'wpboutik' ),
			'NM' => __( 'Nimba', 'wpboutik' ),
			'RV' => __( 'Rivercess', 'wpboutik' ),
			'RG' => __( 'River Gee', 'wpboutik' ),
			'SN' => __( 'Sinoe', 'wpboutik' ),
		),
		'LU' => array(),
		'MD' => array( // Moldova states.
			'C'  => __( 'Chișinău', 'wpboutik' ),
			'BL' => __( 'Bălți', 'wpboutik' ),
			'AN' => __( 'Anenii Noi', 'wpboutik' ),
			'BS' => __( 'Basarabeasca', 'wpboutik' ),
			'BR' => __( 'Briceni', 'wpboutik' ),
			'CH' => __( 'Cahul', 'wpboutik' ),
			'CT' => __( 'Cantemir', 'wpboutik' ),
			'CL' => __( 'Călărași', 'wpboutik' ),
			'CS' => __( 'Căușeni', 'wpboutik' ),
			'CM' => __( 'Cimișlia', 'wpboutik' ),
			'CR' => __( 'Criuleni', 'wpboutik' ),
			'DN' => __( 'Dondușeni', 'wpboutik' ),
			'DR' => __( 'Drochia', 'wpboutik' ),
			'DB' => __( 'Dubăsari', 'wpboutik' ),
			'ED' => __( 'Edineț', 'wpboutik' ),
			'FL' => __( 'Fălești', 'wpboutik' ),
			'FR' => __( 'Florești', 'wpboutik' ),
			'GE' => __( 'UTA Găgăuzia', 'wpboutik' ),
			'GL' => __( 'Glodeni', 'wpboutik' ),
			'HN' => __( 'Hîncești', 'wpboutik' ),
			'IL' => __( 'Ialoveni', 'wpboutik' ),
			'LV' => __( 'Leova', 'wpboutik' ),
			'NS' => __( 'Nisporeni', 'wpboutik' ),
			'OC' => __( 'Ocnița', 'wpboutik' ),
			'OR' => __( 'Orhei', 'wpboutik' ),
			'RZ' => __( 'Rezina', 'wpboutik' ),
			'RS' => __( 'Rîșcani', 'wpboutik' ),
			'SG' => __( 'Sîngerei', 'wpboutik' ),
			'SR' => __( 'Soroca', 'wpboutik' ),
			'ST' => __( 'Strășeni', 'wpboutik' ),
			'SD' => __( 'Șoldănești', 'wpboutik' ),
			'SV' => __( 'Ștefan Vodă', 'wpboutik' ),
			'TR' => __( 'Taraclia', 'wpboutik' ),
			'TL' => __( 'Telenești', 'wpboutik' ),
			'UN' => __( 'Ungheni', 'wpboutik' ),
		),
		'MQ' => array(),
		'MT' => array(),
		'MX' => array( // Mexico States.
			'DF' => __( 'Ciudad de México', 'wpboutik' ),
			'JA' => __( 'Jalisco', 'wpboutik' ),
			'NL' => __( 'Nuevo León', 'wpboutik' ),
			'AG' => __( 'Aguascalientes', 'wpboutik' ),
			'BC' => __( 'Baja California', 'wpboutik' ),
			'BS' => __( 'Baja California Sur', 'wpboutik' ),
			'CM' => __( 'Campeche', 'wpboutik' ),
			'CS' => __( 'Chiapas', 'wpboutik' ),
			'CH' => __( 'Chihuahua', 'wpboutik' ),
			'CO' => __( 'Coahuila', 'wpboutik' ),
			'CL' => __( 'Colima', 'wpboutik' ),
			'DG' => __( 'Durango', 'wpboutik' ),
			'GT' => __( 'Guanajuato', 'wpboutik' ),
			'GR' => __( 'Guerrero', 'wpboutik' ),
			'HG' => __( 'Hidalgo', 'wpboutik' ),
			'MX' => __( 'Estado de México', 'wpboutik' ),
			'MI' => __( 'Michoacán', 'wpboutik' ),
			'MO' => __( 'Morelos', 'wpboutik' ),
			'NA' => __( 'Nayarit', 'wpboutik' ),
			'OA' => __( 'Oaxaca', 'wpboutik' ),
			'PU' => __( 'Puebla', 'wpboutik' ),
			'QT' => __( 'Querétaro', 'wpboutik' ),
			'QR' => __( 'Quintana Roo', 'wpboutik' ),
			'SL' => __( 'San Luis Potosí', 'wpboutik' ),
			'SI' => __( 'Sinaloa', 'wpboutik' ),
			'SO' => __( 'Sonora', 'wpboutik' ),
			'TB' => __( 'Tabasco', 'wpboutik' ),
			'TM' => __( 'Tamaulipas', 'wpboutik' ),
			'TL' => __( 'Tlaxcala', 'wpboutik' ),
			'VE' => __( 'Veracruz', 'wpboutik' ),
			'YU' => __( 'Yucatán', 'wpboutik' ),
			'ZA' => __( 'Zacatecas', 'wpboutik' ),
		),
		'MY' => array( // Malaysian states.
			'JHR' => __( 'Johor', 'wpboutik' ),
			'KDH' => __( 'Kedah', 'wpboutik' ),
			'KTN' => __( 'Kelantan', 'wpboutik' ),
			'LBN' => __( 'Labuan', 'wpboutik' ),
			'MLK' => __( 'Malacca (Melaka)', 'wpboutik' ),
			'NSN' => __( 'Negeri Sembilan', 'wpboutik' ),
			'PHG' => __( 'Pahang', 'wpboutik' ),
			'PNG' => __( 'Penang (Pulau Pinang)', 'wpboutik' ),
			'PRK' => __( 'Perak', 'wpboutik' ),
			'PLS' => __( 'Perlis', 'wpboutik' ),
			'SBH' => __( 'Sabah', 'wpboutik' ),
			'SWK' => __( 'Sarawak', 'wpboutik' ),
			'SGR' => __( 'Selangor', 'wpboutik' ),
			'TRG' => __( 'Terengganu', 'wpboutik' ),
			'PJY' => __( 'Putrajaya', 'wpboutik' ),
			'KUL' => __( 'Kuala Lumpur', 'wpboutik' ),
		),
		'MZ' => array( // Mozambique provinces.
			'MZP'   => __( 'Cabo Delgado', 'wpboutik' ),
			'MZG'   => __( 'Gaza', 'wpboutik' ),
			'MZI'   => __( 'Inhambane', 'wpboutik' ),
			'MZB'   => __( 'Manica', 'wpboutik' ),
			'MZL'   => __( 'Maputo Province', 'wpboutik' ),
			'MZMPM' => __( 'Maputo', 'wpboutik' ),
			'MZN'   => __( 'Nampula', 'wpboutik' ),
			'MZA'   => __( 'Niassa', 'wpboutik' ),
			'MZS'   => __( 'Sofala', 'wpboutik' ),
			'MZT'   => __( 'Tete', 'wpboutik' ),
			'MZQ'   => __( 'Zambézia', 'wpboutik' ),
		),
		'NA' => array( // Namibia regions.
			'ER' => __( 'Erongo', 'wpboutik' ),
			'HA' => __( 'Hardap', 'wpboutik' ),
			'KA' => __( 'Karas', 'wpboutik' ),
			'KE' => __( 'Kavango East', 'wpboutik' ),
			'KW' => __( 'Kavango West', 'wpboutik' ),
			'KH' => __( 'Khomas', 'wpboutik' ),
			'KU' => __( 'Kunene', 'wpboutik' ),
			'OW' => __( 'Ohangwena', 'wpboutik' ),
			'OH' => __( 'Omaheke', 'wpboutik' ),
			'OS' => __( 'Omusati', 'wpboutik' ),
			'ON' => __( 'Oshana', 'wpboutik' ),
			'OT' => __( 'Oshikoto', 'wpboutik' ),
			'OD' => __( 'Otjozondjupa', 'wpboutik' ),
			'CA' => __( 'Zambezi', 'wpboutik' ),
		),
		'NG' => array( // Nigerian provinces.
			'AB' => __( 'Abia', 'wpboutik' ),
			'FC' => __( 'Abuja', 'wpboutik' ),
			'AD' => __( 'Adamawa', 'wpboutik' ),
			'AK' => __( 'Akwa Ibom', 'wpboutik' ),
			'AN' => __( 'Anambra', 'wpboutik' ),
			'BA' => __( 'Bauchi', 'wpboutik' ),
			'BY' => __( 'Bayelsa', 'wpboutik' ),
			'BE' => __( 'Benue', 'wpboutik' ),
			'BO' => __( 'Borno', 'wpboutik' ),
			'CR' => __( 'Cross River', 'wpboutik' ),
			'DE' => __( 'Delta', 'wpboutik' ),
			'EB' => __( 'Ebonyi', 'wpboutik' ),
			'ED' => __( 'Edo', 'wpboutik' ),
			'EK' => __( 'Ekiti', 'wpboutik' ),
			'EN' => __( 'Enugu', 'wpboutik' ),
			'GO' => __( 'Gombe', 'wpboutik' ),
			'IM' => __( 'Imo', 'wpboutik' ),
			'JI' => __( 'Jigawa', 'wpboutik' ),
			'KD' => __( 'Kaduna', 'wpboutik' ),
			'KN' => __( 'Kano', 'wpboutik' ),
			'KT' => __( 'Katsina', 'wpboutik' ),
			'KE' => __( 'Kebbi', 'wpboutik' ),
			'KO' => __( 'Kogi', 'wpboutik' ),
			'KW' => __( 'Kwara', 'wpboutik' ),
			'LA' => __( 'Lagos', 'wpboutik' ),
			'NA' => __( 'Nasarawa', 'wpboutik' ),
			'NI' => __( 'Niger', 'wpboutik' ),
			'OG' => __( 'Ogun', 'wpboutik' ),
			'ON' => __( 'Ondo', 'wpboutik' ),
			'OS' => __( 'Osun', 'wpboutik' ),
			'OY' => __( 'Oyo', 'wpboutik' ),
			'PL' => __( 'Plateau', 'wpboutik' ),
			'RI' => __( 'Rivers', 'wpboutik' ),
			'SO' => __( 'Sokoto', 'wpboutik' ),
			'TA' => __( 'Taraba', 'wpboutik' ),
			'YO' => __( 'Yobe', 'wpboutik' ),
			'ZA' => __( 'Zamfara', 'wpboutik' ),
		),
		'NL' => array(),
		'NO' => array(),
		'NP' => array( // Nepal states (Zones).
			'BAG' => __( 'Bagmati', 'wpboutik' ),
			'BHE' => __( 'Bheri', 'wpboutik' ),
			'DHA' => __( 'Dhaulagiri', 'wpboutik' ),
			'GAN' => __( 'Gandaki', 'wpboutik' ),
			'JAN' => __( 'Janakpur', 'wpboutik' ),
			'KAR' => __( 'Karnali', 'wpboutik' ),
			'KOS' => __( 'Koshi', 'wpboutik' ),
			'LUM' => __( 'Lumbini', 'wpboutik' ),
			'MAH' => __( 'Mahakali', 'wpboutik' ),
			'MEC' => __( 'Mechi', 'wpboutik' ),
			'NAR' => __( 'Narayani', 'wpboutik' ),
			'RAP' => __( 'Rapti', 'wpboutik' ),
			'SAG' => __( 'Sagarmatha', 'wpboutik' ),
			'SET' => __( 'Seti', 'wpboutik' ),
		),
		'NZ' => array( // New Zealand States.
			'NL' => __( 'Northland', 'wpboutik' ),
			'AK' => __( 'Auckland', 'wpboutik' ),
			'WA' => __( 'Waikato', 'wpboutik' ),
			'BP' => __( 'Bay of Plenty', 'wpboutik' ),
			'TK' => __( 'Taranaki', 'wpboutik' ),
			'GI' => __( 'Gisborne', 'wpboutik' ),
			'HB' => __( 'Hawke’s Bay', 'wpboutik' ),
			'MW' => __( 'Manawatu-Wanganui', 'wpboutik' ),
			'WE' => __( 'Wellington', 'wpboutik' ),
			'NS' => __( 'Nelson', 'wpboutik' ),
			'MB' => __( 'Marlborough', 'wpboutik' ),
			'TM' => __( 'Tasman', 'wpboutik' ),
			'WC' => __( 'West Coast', 'wpboutik' ),
			'CT' => __( 'Canterbury', 'wpboutik' ),
			'OT' => __( 'Otago', 'wpboutik' ),
			'SL' => __( 'Southland', 'wpboutik' ),
		),
		'PE' => array( // Peru states.
			'CAL' => __( 'El Callao', 'wpboutik' ),
			'LMA' => __( 'Municipalidad Metropolitana de Lima', 'wpboutik' ),
			'AMA' => __( 'Amazonas', 'wpboutik' ),
			'ANC' => __( 'Ancash', 'wpboutik' ),
			'APU' => __( 'Apurímac', 'wpboutik' ),
			'ARE' => __( 'Arequipa', 'wpboutik' ),
			'AYA' => __( 'Ayacucho', 'wpboutik' ),
			'CAJ' => __( 'Cajamarca', 'wpboutik' ),
			'CUS' => __( 'Cusco', 'wpboutik' ),
			'HUV' => __( 'Huancavelica', 'wpboutik' ),
			'HUC' => __( 'Huánuco', 'wpboutik' ),
			'ICA' => __( 'Ica', 'wpboutik' ),
			'JUN' => __( 'Junín', 'wpboutik' ),
			'LAL' => __( 'La Libertad', 'wpboutik' ),
			'LAM' => __( 'Lambayeque', 'wpboutik' ),
			'LIM' => __( 'Lima', 'wpboutik' ),
			'LOR' => __( 'Loreto', 'wpboutik' ),
			'MDD' => __( 'Madre de Dios', 'wpboutik' ),
			'MOQ' => __( 'Moquegua', 'wpboutik' ),
			'PAS' => __( 'Pasco', 'wpboutik' ),
			'PIU' => __( 'Piura', 'wpboutik' ),
			'PUN' => __( 'Puno', 'wpboutik' ),
			'SAM' => __( 'San Martín', 'wpboutik' ),
			'TAC' => __( 'Tacna', 'wpboutik' ),
			'TUM' => __( 'Tumbes', 'wpboutik' ),
			'UCA' => __( 'Ucayali', 'wpboutik' ),
		),

		/**
		 * Philippine Provinces.
		 */
		'PH' => array(
			'ABR' => __( 'Abra', 'wpboutik' ),
			'AGN' => __( 'Agusan del Norte', 'wpboutik' ),
			'AGS' => __( 'Agusan del Sur', 'wpboutik' ),
			'AKL' => __( 'Aklan', 'wpboutik' ),
			'ALB' => __( 'Albay', 'wpboutik' ),
			'ANT' => __( 'Antique', 'wpboutik' ),
			'APA' => __( 'Apayao', 'wpboutik' ),
			'AUR' => __( 'Aurora', 'wpboutik' ),
			'BAS' => __( 'Basilan', 'wpboutik' ),
			'BAN' => __( 'Bataan', 'wpboutik' ),
			'BTN' => __( 'Batanes', 'wpboutik' ),
			'BTG' => __( 'Batangas', 'wpboutik' ),
			'BEN' => __( 'Benguet', 'wpboutik' ),
			'BIL' => __( 'Biliran', 'wpboutik' ),
			'BOH' => __( 'Bohol', 'wpboutik' ),
			'BUK' => __( 'Bukidnon', 'wpboutik' ),
			'BUL' => __( 'Bulacan', 'wpboutik' ),
			'CAG' => __( 'Cagayan', 'wpboutik' ),
			'CAN' => __( 'Camarines Norte', 'wpboutik' ),
			'CAS' => __( 'Camarines Sur', 'wpboutik' ),
			'CAM' => __( 'Camiguin', 'wpboutik' ),
			'CAP' => __( 'Capiz', 'wpboutik' ),
			'CAT' => __( 'Catanduanes', 'wpboutik' ),
			'CAV' => __( 'Cavite', 'wpboutik' ),
			'CEB' => __( 'Cebu', 'wpboutik' ),
			'COM' => __( 'Compostela Valley', 'wpboutik' ),
			'NCO' => __( 'Cotabato', 'wpboutik' ),
			'DAV' => __( 'Davao del Norte', 'wpboutik' ),
			'DAS' => __( 'Davao del Sur', 'wpboutik' ),
			'DAC' => __( 'Davao Occidental', 'wpboutik' ),
			'DAO' => __( 'Davao Oriental', 'wpboutik' ),
			'DIN' => __( 'Dinagat Islands', 'wpboutik' ),
			'EAS' => __( 'Eastern Samar', 'wpboutik' ),
			'GUI' => __( 'Guimaras', 'wpboutik' ),
			'IFU' => __( 'Ifugao', 'wpboutik' ),
			'ILN' => __( 'Ilocos Norte', 'wpboutik' ),
			'ILS' => __( 'Ilocos Sur', 'wpboutik' ),
			'ILI' => __( 'Iloilo', 'wpboutik' ),
			'ISA' => __( 'Isabela', 'wpboutik' ),
			'KAL' => __( 'Kalinga', 'wpboutik' ),
			'LUN' => __( 'La Union', 'wpboutik' ),
			'LAG' => __( 'Laguna', 'wpboutik' ),
			'LAN' => __( 'Lanao del Norte', 'wpboutik' ),
			'LAS' => __( 'Lanao del Sur', 'wpboutik' ),
			'LEY' => __( 'Leyte', 'wpboutik' ),
			'MAG' => __( 'Maguindanao', 'wpboutik' ),
			'MAD' => __( 'Marinduque', 'wpboutik' ),
			'MAS' => __( 'Masbate', 'wpboutik' ),
			'MSC' => __( 'Misamis Occidental', 'wpboutik' ),
			'MSR' => __( 'Misamis Oriental', 'wpboutik' ),
			'MOU' => __( 'Mountain Province', 'wpboutik' ),
			'NEC' => __( 'Negros Occidental', 'wpboutik' ),
			'NER' => __( 'Negros Oriental', 'wpboutik' ),
			'NSA' => __( 'Northern Samar', 'wpboutik' ),
			'NUE' => __( 'Nueva Ecija', 'wpboutik' ),
			'NUV' => __( 'Nueva Vizcaya', 'wpboutik' ),
			'MDC' => __( 'Occidental Mindoro', 'wpboutik' ),
			'MDR' => __( 'Oriental Mindoro', 'wpboutik' ),
			'PLW' => __( 'Palawan', 'wpboutik' ),
			'PAM' => __( 'Pampanga', 'wpboutik' ),
			'PAN' => __( 'Pangasinan', 'wpboutik' ),
			'QUE' => __( 'Quezon', 'wpboutik' ),
			'QUI' => __( 'Quirino', 'wpboutik' ),
			'RIZ' => __( 'Rizal', 'wpboutik' ),
			'ROM' => __( 'Romblon', 'wpboutik' ),
			'WSA' => __( 'Samar', 'wpboutik' ),
			'SAR' => __( 'Sarangani', 'wpboutik' ),
			'SIQ' => __( 'Siquijor', 'wpboutik' ),
			'SOR' => __( 'Sorsogon', 'wpboutik' ),
			'SCO' => __( 'South Cotabato', 'wpboutik' ),
			'SLE' => __( 'Southern Leyte', 'wpboutik' ),
			'SUK' => __( 'Sultan Kudarat', 'wpboutik' ),
			'SLU' => __( 'Sulu', 'wpboutik' ),
			'SUN' => __( 'Surigao del Norte', 'wpboutik' ),
			'SUR' => __( 'Surigao del Sur', 'wpboutik' ),
			'TAR' => __( 'Tarlac', 'wpboutik' ),
			'TAW' => __( 'Tawi-Tawi', 'wpboutik' ),
			'ZMB' => __( 'Zambales', 'wpboutik' ),
			'ZAN' => __( 'Zamboanga del Norte', 'wpboutik' ),
			'ZAS' => __( 'Zamboanga del Sur', 'wpboutik' ),
			'ZSI' => __( 'Zamboanga Sibugay', 'wpboutik' ),
			'00'  => __( 'Metro Manila', 'wpboutik' ),
		),
		'PK' => array( // Pakistan's states.
			'JK' => __( 'Azad Kashmir', 'wpboutik' ),
			'BA' => __( 'Balochistan', 'wpboutik' ),
			'TA' => __( 'FATA', 'wpboutik' ),
			'GB' => __( 'Gilgit Baltistan', 'wpboutik' ),
			'IS' => __( 'Islamabad Capital Territory', 'wpboutik' ),
			'KP' => __( 'Khyber Pakhtunkhwa', 'wpboutik' ),
			'PB' => __( 'Punjab', 'wpboutik' ),
			'SD' => __( 'Sindh', 'wpboutik' ),
		),
		'PL' => array(),
		'PT' => array(),
		'PY' => array( // Paraguay states.
			'PY-ASU' => __( 'Asunción', 'wpboutik' ),
			'PY-1'   => __( 'Concepción', 'wpboutik' ),
			'PY-2'   => __( 'San Pedro', 'wpboutik' ),
			'PY-3'   => __( 'Cordillera', 'wpboutik' ),
			'PY-4'   => __( 'Guairá', 'wpboutik' ),
			'PY-5'   => __( 'Caaguazú', 'wpboutik' ),
			'PY-6'   => __( 'Caazapá', 'wpboutik' ),
			'PY-7'   => __( 'Itapúa', 'wpboutik' ),
			'PY-8'   => __( 'Misiones', 'wpboutik' ),
			'PY-9'   => __( 'Paraguarí', 'wpboutik' ),
			'PY-10'  => __( 'Alto Paraná', 'wpboutik' ),
			'PY-11'  => __( 'Central', 'wpboutik' ),
			'PY-12'  => __( 'Ñeembucú', 'wpboutik' ),
			'PY-13'  => __( 'Amambay', 'wpboutik' ),
			'PY-14'  => __( 'Canindeyú', 'wpboutik' ),
			'PY-15'  => __( 'Presidente Hayes', 'wpboutik' ),
			'PY-16'  => __( 'Alto Paraguay', 'wpboutik' ),
			'PY-17'  => __( 'Boquerón', 'wpboutik' ),
		),
		'RE' => array(),
		'RO' => array( // Romania states.
			'AB' => __( 'Alba', 'wpboutik' ),
			'AR' => __( 'Arad', 'wpboutik' ),
			'AG' => __( 'Argeș', 'wpboutik' ),
			'BC' => __( 'Bacău', 'wpboutik' ),
			'BH' => __( 'Bihor', 'wpboutik' ),
			'BN' => __( 'Bistrița-Năsăud', 'wpboutik' ),
			'BT' => __( 'Botoșani', 'wpboutik' ),
			'BR' => __( 'Brăila', 'wpboutik' ),
			'BV' => __( 'Brașov', 'wpboutik' ),
			'B'  => __( 'București', 'wpboutik' ),
			'BZ' => __( 'Buzău', 'wpboutik' ),
			'CL' => __( 'Călărași', 'wpboutik' ),
			'CS' => __( 'Caraș-Severin', 'wpboutik' ),
			'CJ' => __( 'Cluj', 'wpboutik' ),
			'CT' => __( 'Constanța', 'wpboutik' ),
			'CV' => __( 'Covasna', 'wpboutik' ),
			'DB' => __( 'Dâmbovița', 'wpboutik' ),
			'DJ' => __( 'Dolj', 'wpboutik' ),
			'GL' => __( 'Galați', 'wpboutik' ),
			'GR' => __( 'Giurgiu', 'wpboutik' ),
			'GJ' => __( 'Gorj', 'wpboutik' ),
			'HR' => __( 'Harghita', 'wpboutik' ),
			'HD' => __( 'Hunedoara', 'wpboutik' ),
			'IL' => __( 'Ialomița', 'wpboutik' ),
			'IS' => __( 'Iași', 'wpboutik' ),
			'IF' => __( 'Ilfov', 'wpboutik' ),
			'MM' => __( 'Maramureș', 'wpboutik' ),
			'MH' => __( 'Mehedinți', 'wpboutik' ),
			'MS' => __( 'Mureș', 'wpboutik' ),
			'NT' => __( 'Neamț', 'wpboutik' ),
			'OT' => __( 'Olt', 'wpboutik' ),
			'PH' => __( 'Prahova', 'wpboutik' ),
			'SJ' => __( 'Sălaj', 'wpboutik' ),
			'SM' => __( 'Satu Mare', 'wpboutik' ),
			'SB' => __( 'Sibiu', 'wpboutik' ),
			'SV' => __( 'Suceava', 'wpboutik' ),
			'TR' => __( 'Teleorman', 'wpboutik' ),
			'TM' => __( 'Timiș', 'wpboutik' ),
			'TL' => __( 'Tulcea', 'wpboutik' ),
			'VL' => __( 'Vâlcea', 'wpboutik' ),
			'VS' => __( 'Vaslui', 'wpboutik' ),
			'VN' => __( 'Vrancea', 'wpboutik' ),
		),
		'RS' => array(),
		'SG' => array(),
		'SK' => array(),
		'SI' => array(),
		'TH' => array( // Thailand states.
			'TH-37' => __( 'Amnat Charoen', 'wpboutik' ),
			'TH-15' => __( 'Ang Thong', 'wpboutik' ),
			'TH-14' => __( 'Ayutthaya', 'wpboutik' ),
			'TH-10' => __( 'Bangkok', 'wpboutik' ),
			'TH-38' => __( 'Bueng Kan', 'wpboutik' ),
			'TH-31' => __( 'Buri Ram', 'wpboutik' ),
			'TH-24' => __( 'Chachoengsao', 'wpboutik' ),
			'TH-18' => __( 'Chai Nat', 'wpboutik' ),
			'TH-36' => __( 'Chaiyaphum', 'wpboutik' ),
			'TH-22' => __( 'Chanthaburi', 'wpboutik' ),
			'TH-50' => __( 'Chiang Mai', 'wpboutik' ),
			'TH-57' => __( 'Chiang Rai', 'wpboutik' ),
			'TH-20' => __( 'Chonburi', 'wpboutik' ),
			'TH-86' => __( 'Chumphon', 'wpboutik' ),
			'TH-46' => __( 'Kalasin', 'wpboutik' ),
			'TH-62' => __( 'Kamphaeng Phet', 'wpboutik' ),
			'TH-71' => __( 'Kanchanaburi', 'wpboutik' ),
			'TH-40' => __( 'Khon Kaen', 'wpboutik' ),
			'TH-81' => __( 'Krabi', 'wpboutik' ),
			'TH-52' => __( 'Lampang', 'wpboutik' ),
			'TH-51' => __( 'Lamphun', 'wpboutik' ),
			'TH-42' => __( 'Loei', 'wpboutik' ),
			'TH-16' => __( 'Lopburi', 'wpboutik' ),
			'TH-58' => __( 'Mae Hong Son', 'wpboutik' ),
			'TH-44' => __( 'Maha Sarakham', 'wpboutik' ),
			'TH-49' => __( 'Mukdahan', 'wpboutik' ),
			'TH-26' => __( 'Nakhon Nayok', 'wpboutik' ),
			'TH-73' => __( 'Nakhon Pathom', 'wpboutik' ),
			'TH-48' => __( 'Nakhon Phanom', 'wpboutik' ),
			'TH-30' => __( 'Nakhon Ratchasima', 'wpboutik' ),
			'TH-60' => __( 'Nakhon Sawan', 'wpboutik' ),
			'TH-80' => __( 'Nakhon Si Thammarat', 'wpboutik' ),
			'TH-55' => __( 'Nan', 'wpboutik' ),
			'TH-96' => __( 'Narathiwat', 'wpboutik' ),
			'TH-39' => __( 'Nong Bua Lam Phu', 'wpboutik' ),
			'TH-43' => __( 'Nong Khai', 'wpboutik' ),
			'TH-12' => __( 'Nonthaburi', 'wpboutik' ),
			'TH-13' => __( 'Pathum Thani', 'wpboutik' ),
			'TH-94' => __( 'Pattani', 'wpboutik' ),
			'TH-82' => __( 'Phang Nga', 'wpboutik' ),
			'TH-93' => __( 'Phatthalung', 'wpboutik' ),
			'TH-56' => __( 'Phayao', 'wpboutik' ),
			'TH-67' => __( 'Phetchabun', 'wpboutik' ),
			'TH-76' => __( 'Phetchaburi', 'wpboutik' ),
			'TH-66' => __( 'Phichit', 'wpboutik' ),
			'TH-65' => __( 'Phitsanulok', 'wpboutik' ),
			'TH-54' => __( 'Phrae', 'wpboutik' ),
			'TH-83' => __( 'Phuket', 'wpboutik' ),
			'TH-25' => __( 'Prachin Buri', 'wpboutik' ),
			'TH-77' => __( 'Prachuap Khiri Khan', 'wpboutik' ),
			'TH-85' => __( 'Ranong', 'wpboutik' ),
			'TH-70' => __( 'Ratchaburi', 'wpboutik' ),
			'TH-21' => __( 'Rayong', 'wpboutik' ),
			'TH-45' => __( 'Roi Et', 'wpboutik' ),
			'TH-27' => __( 'Sa Kaeo', 'wpboutik' ),
			'TH-47' => __( 'Sakon Nakhon', 'wpboutik' ),
			'TH-11' => __( 'Samut Prakan', 'wpboutik' ),
			'TH-74' => __( 'Samut Sakhon', 'wpboutik' ),
			'TH-75' => __( 'Samut Songkhram', 'wpboutik' ),
			'TH-19' => __( 'Saraburi', 'wpboutik' ),
			'TH-91' => __( 'Satun', 'wpboutik' ),
			'TH-17' => __( 'Sing Buri', 'wpboutik' ),
			'TH-33' => __( 'Sisaket', 'wpboutik' ),
			'TH-90' => __( 'Songkhla', 'wpboutik' ),
			'TH-64' => __( 'Sukhothai', 'wpboutik' ),
			'TH-72' => __( 'Suphan Buri', 'wpboutik' ),
			'TH-84' => __( 'Surat Thani', 'wpboutik' ),
			'TH-32' => __( 'Surin', 'wpboutik' ),
			'TH-63' => __( 'Tak', 'wpboutik' ),
			'TH-92' => __( 'Trang', 'wpboutik' ),
			'TH-23' => __( 'Trat', 'wpboutik' ),
			'TH-34' => __( 'Ubon Ratchathani', 'wpboutik' ),
			'TH-41' => __( 'Udon Thani', 'wpboutik' ),
			'TH-61' => __( 'Uthai Thani', 'wpboutik' ),
			'TH-53' => __( 'Uttaradit', 'wpboutik' ),
			'TH-95' => __( 'Yala', 'wpboutik' ),
			'TH-35' => __( 'Yasothon', 'wpboutik' ),
		),
		'TR' => array( // Turkey States.
			'TR01' => __( 'Adana', 'wpboutik' ),
			'TR02' => __( 'Adıyaman', 'wpboutik' ),
			'TR03' => __( 'Afyon', 'wpboutik' ),
			'TR04' => __( 'Ağrı', 'wpboutik' ),
			'TR05' => __( 'Amasya', 'wpboutik' ),
			'TR06' => __( 'Ankara', 'wpboutik' ),
			'TR07' => __( 'Antalya', 'wpboutik' ),
			'TR08' => __( 'Artvin', 'wpboutik' ),
			'TR09' => __( 'Aydın', 'wpboutik' ),
			'TR10' => __( 'Balıkesir', 'wpboutik' ),
			'TR11' => __( 'Bilecik', 'wpboutik' ),
			'TR12' => __( 'Bingöl', 'wpboutik' ),
			'TR13' => __( 'Bitlis', 'wpboutik' ),
			'TR14' => __( 'Bolu', 'wpboutik' ),
			'TR15' => __( 'Burdur', 'wpboutik' ),
			'TR16' => __( 'Bursa', 'wpboutik' ),
			'TR17' => __( 'Çanakkale', 'wpboutik' ),
			'TR18' => __( 'Çankırı', 'wpboutik' ),
			'TR19' => __( 'Çorum', 'wpboutik' ),
			'TR20' => __( 'Denizli', 'wpboutik' ),
			'TR21' => __( 'Diyarbakır', 'wpboutik' ),
			'TR22' => __( 'Edirne', 'wpboutik' ),
			'TR23' => __( 'Elazığ', 'wpboutik' ),
			'TR24' => __( 'Erzincan', 'wpboutik' ),
			'TR25' => __( 'Erzurum', 'wpboutik' ),
			'TR26' => __( 'Eskişehir', 'wpboutik' ),
			'TR27' => __( 'Gaziantep', 'wpboutik' ),
			'TR28' => __( 'Giresun', 'wpboutik' ),
			'TR29' => __( 'Gümüşhane', 'wpboutik' ),
			'TR30' => __( 'Hakkari', 'wpboutik' ),
			'TR31' => __( 'Hatay', 'wpboutik' ),
			'TR32' => __( 'Isparta', 'wpboutik' ),
			'TR33' => __( 'İçel', 'wpboutik' ),
			'TR34' => __( 'İstanbul', 'wpboutik' ),
			'TR35' => __( 'İzmir', 'wpboutik' ),
			'TR36' => __( 'Kars', 'wpboutik' ),
			'TR37' => __( 'Kastamonu', 'wpboutik' ),
			'TR38' => __( 'Kayseri', 'wpboutik' ),
			'TR39' => __( 'Kırklareli', 'wpboutik' ),
			'TR40' => __( 'Kırşehir', 'wpboutik' ),
			'TR41' => __( 'Kocaeli', 'wpboutik' ),
			'TR42' => __( 'Konya', 'wpboutik' ),
			'TR43' => __( 'Kütahya', 'wpboutik' ),
			'TR44' => __( 'Malatya', 'wpboutik' ),
			'TR45' => __( 'Manisa', 'wpboutik' ),
			'TR46' => __( 'Kahramanmaraş', 'wpboutik' ),
			'TR47' => __( 'Mardin', 'wpboutik' ),
			'TR48' => __( 'Muğla', 'wpboutik' ),
			'TR49' => __( 'Muş', 'wpboutik' ),
			'TR50' => __( 'Nevşehir', 'wpboutik' ),
			'TR51' => __( 'Niğde', 'wpboutik' ),
			'TR52' => __( 'Ordu', 'wpboutik' ),
			'TR53' => __( 'Rize', 'wpboutik' ),
			'TR54' => __( 'Sakarya', 'wpboutik' ),
			'TR55' => __( 'Samsun', 'wpboutik' ),
			'TR56' => __( 'Siirt', 'wpboutik' ),
			'TR57' => __( 'Sinop', 'wpboutik' ),
			'TR58' => __( 'Sivas', 'wpboutik' ),
			'TR59' => __( 'Tekirdağ', 'wpboutik' ),
			'TR60' => __( 'Tokat', 'wpboutik' ),
			'TR61' => __( 'Trabzon', 'wpboutik' ),
			'TR62' => __( 'Tunceli', 'wpboutik' ),
			'TR63' => __( 'Şanlıurfa', 'wpboutik' ),
			'TR64' => __( 'Uşak', 'wpboutik' ),
			'TR65' => __( 'Van', 'wpboutik' ),
			'TR66' => __( 'Yozgat', 'wpboutik' ),
			'TR67' => __( 'Zonguldak', 'wpboutik' ),
			'TR68' => __( 'Aksaray', 'wpboutik' ),
			'TR69' => __( 'Bayburt', 'wpboutik' ),
			'TR70' => __( 'Karaman', 'wpboutik' ),
			'TR71' => __( 'Kırıkkale', 'wpboutik' ),
			'TR72' => __( 'Batman', 'wpboutik' ),
			'TR73' => __( 'Şırnak', 'wpboutik' ),
			'TR74' => __( 'Bartın', 'wpboutik' ),
			'TR75' => __( 'Ardahan', 'wpboutik' ),
			'TR76' => __( 'Iğdır', 'wpboutik' ),
			'TR77' => __( 'Yalova', 'wpboutik' ),
			'TR78' => __( 'Karabük', 'wpboutik' ),
			'TR79' => __( 'Kilis', 'wpboutik' ),
			'TR80' => __( 'Osmaniye', 'wpboutik' ),
			'TR81' => __( 'Düzce', 'wpboutik' ),
		),
		'TZ' => array( // Tanzania States.
			'TZ01' => __( 'Arusha', 'wpboutik' ),
			'TZ02' => __( 'Dar es Salaam', 'wpboutik' ),
			'TZ03' => __( 'Dodoma', 'wpboutik' ),
			'TZ04' => __( 'Iringa', 'wpboutik' ),
			'TZ05' => __( 'Kagera', 'wpboutik' ),
			'TZ06' => __( 'Pemba North', 'wpboutik' ),
			'TZ07' => __( 'Zanzibar North', 'wpboutik' ),
			'TZ08' => __( 'Kigoma', 'wpboutik' ),
			'TZ09' => __( 'Kilimanjaro', 'wpboutik' ),
			'TZ10' => __( 'Pemba South', 'wpboutik' ),
			'TZ11' => __( 'Zanzibar South', 'wpboutik' ),
			'TZ12' => __( 'Lindi', 'wpboutik' ),
			'TZ13' => __( 'Mara', 'wpboutik' ),
			'TZ14' => __( 'Mbeya', 'wpboutik' ),
			'TZ15' => __( 'Zanzibar West', 'wpboutik' ),
			'TZ16' => __( 'Morogoro', 'wpboutik' ),
			'TZ17' => __( 'Mtwara', 'wpboutik' ),
			'TZ18' => __( 'Mwanza', 'wpboutik' ),
			'TZ19' => __( 'Coast', 'wpboutik' ),
			'TZ20' => __( 'Rukwa', 'wpboutik' ),
			'TZ21' => __( 'Ruvuma', 'wpboutik' ),
			'TZ22' => __( 'Shinyanga', 'wpboutik' ),
			'TZ23' => __( 'Singida', 'wpboutik' ),
			'TZ24' => __( 'Tabora', 'wpboutik' ),
			'TZ25' => __( 'Tanga', 'wpboutik' ),
			'TZ26' => __( 'Manyara', 'wpboutik' ),
			'TZ27' => __( 'Geita', 'wpboutik' ),
			'TZ28' => __( 'Katavi', 'wpboutik' ),
			'TZ29' => __( 'Njombe', 'wpboutik' ),
			'TZ30' => __( 'Simiyu', 'wpboutik' ),
		),
		'LK' => array(),
		'SE' => array(),
		'UG' => array( // Uganda districts. Ref: https://en.wikipedia.org/wiki/ISO_3166-2:UG.
			'UG314' => __( 'Abim', 'wpboutik' ),
			'UG301' => __( 'Adjumani', 'wpboutik' ),
			'UG322' => __( 'Agago', 'wpboutik' ),
			'UG323' => __( 'Alebtong', 'wpboutik' ),
			'UG315' => __( 'Amolatar', 'wpboutik' ),
			'UG324' => __( 'Amudat', 'wpboutik' ),
			'UG216' => __( 'Amuria', 'wpboutik' ),
			'UG316' => __( 'Amuru', 'wpboutik' ),
			'UG302' => __( 'Apac', 'wpboutik' ),
			'UG303' => __( 'Arua', 'wpboutik' ),
			'UG217' => __( 'Budaka', 'wpboutik' ),
			'UG218' => __( 'Bududa', 'wpboutik' ),
			'UG201' => __( 'Bugiri', 'wpboutik' ),
			'UG235' => __( 'Bugweri', 'wpboutik' ),
			'UG420' => __( 'Buhweju', 'wpboutik' ),
			'UG117' => __( 'Buikwe', 'wpboutik' ),
			'UG219' => __( 'Bukedea', 'wpboutik' ),
			'UG118' => __( 'Bukomansimbi', 'wpboutik' ),
			'UG220' => __( 'Bukwa', 'wpboutik' ),
			'UG225' => __( 'Bulambuli', 'wpboutik' ),
			'UG416' => __( 'Buliisa', 'wpboutik' ),
			'UG401' => __( 'Bundibugyo', 'wpboutik' ),
			'UG430' => __( 'Bunyangabu', 'wpboutik' ),
			'UG402' => __( 'Bushenyi', 'wpboutik' ),
			'UG202' => __( 'Busia', 'wpboutik' ),
			'UG221' => __( 'Butaleja', 'wpboutik' ),
			'UG119' => __( 'Butambala', 'wpboutik' ),
			'UG233' => __( 'Butebo', 'wpboutik' ),
			'UG120' => __( 'Buvuma', 'wpboutik' ),
			'UG226' => __( 'Buyende', 'wpboutik' ),
			'UG317' => __( 'Dokolo', 'wpboutik' ),
			'UG121' => __( 'Gomba', 'wpboutik' ),
			'UG304' => __( 'Gulu', 'wpboutik' ),
			'UG403' => __( 'Hoima', 'wpboutik' ),
			'UG417' => __( 'Ibanda', 'wpboutik' ),
			'UG203' => __( 'Iganga', 'wpboutik' ),
			'UG418' => __( 'Isingiro', 'wpboutik' ),
			'UG204' => __( 'Jinja', 'wpboutik' ),
			'UG318' => __( 'Kaabong', 'wpboutik' ),
			'UG404' => __( 'Kabale', 'wpboutik' ),
			'UG405' => __( 'Kabarole', 'wpboutik' ),
			'UG213' => __( 'Kaberamaido', 'wpboutik' ),
			'UG427' => __( 'Kagadi', 'wpboutik' ),
			'UG428' => __( 'Kakumiro', 'wpboutik' ),
			'UG101' => __( 'Kalangala', 'wpboutik' ),
			'UG222' => __( 'Kaliro', 'wpboutik' ),
			'UG122' => __( 'Kalungu', 'wpboutik' ),
			'UG102' => __( 'Kampala', 'wpboutik' ),
			'UG205' => __( 'Kamuli', 'wpboutik' ),
			'UG413' => __( 'Kamwenge', 'wpboutik' ),
			'UG414' => __( 'Kanungu', 'wpboutik' ),
			'UG206' => __( 'Kapchorwa', 'wpboutik' ),
			'UG236' => __( 'Kapelebyong', 'wpboutik' ),
			'UG126' => __( 'Kasanda', 'wpboutik' ),
			'UG406' => __( 'Kasese', 'wpboutik' ),
			'UG207' => __( 'Katakwi', 'wpboutik' ),
			'UG112' => __( 'Kayunga', 'wpboutik' ),
			'UG407' => __( 'Kibaale', 'wpboutik' ),
			'UG103' => __( 'Kiboga', 'wpboutik' ),
			'UG227' => __( 'Kibuku', 'wpboutik' ),
			'UG432' => __( 'Kikuube', 'wpboutik' ),
			'UG419' => __( 'Kiruhura', 'wpboutik' ),
			'UG421' => __( 'Kiryandongo', 'wpboutik' ),
			'UG408' => __( 'Kisoro', 'wpboutik' ),
			'UG305' => __( 'Kitgum', 'wpboutik' ),
			'UG319' => __( 'Koboko', 'wpboutik' ),
			'UG325' => __( 'Kole', 'wpboutik' ),
			'UG306' => __( 'Kotido', 'wpboutik' ),
			'UG208' => __( 'Kumi', 'wpboutik' ),
			'UG333' => __( 'Kwania', 'wpboutik' ),
			'UG228' => __( 'Kween', 'wpboutik' ),
			'UG123' => __( 'Kyankwanzi', 'wpboutik' ),
			'UG422' => __( 'Kyegegwa', 'wpboutik' ),
			'UG415' => __( 'Kyenjojo', 'wpboutik' ),
			'UG125' => __( 'Kyotera', 'wpboutik' ),
			'UG326' => __( 'Lamwo', 'wpboutik' ),
			'UG307' => __( 'Lira', 'wpboutik' ),
			'UG229' => __( 'Luuka', 'wpboutik' ),
			'UG104' => __( 'Luwero', 'wpboutik' ),
			'UG124' => __( 'Lwengo', 'wpboutik' ),
			'UG114' => __( 'Lyantonde', 'wpboutik' ),
			'UG223' => __( 'Manafwa', 'wpboutik' ),
			'UG320' => __( 'Maracha', 'wpboutik' ),
			'UG105' => __( 'Masaka', 'wpboutik' ),
			'UG409' => __( 'Masindi', 'wpboutik' ),
			'UG214' => __( 'Mayuge', 'wpboutik' ),
			'UG209' => __( 'Mbale', 'wpboutik' ),
			'UG410' => __( 'Mbarara', 'wpboutik' ),
			'UG423' => __( 'Mitooma', 'wpboutik' ),
			'UG115' => __( 'Mityana', 'wpboutik' ),
			'UG308' => __( 'Moroto', 'wpboutik' ),
			'UG309' => __( 'Moyo', 'wpboutik' ),
			'UG106' => __( 'Mpigi', 'wpboutik' ),
			'UG107' => __( 'Mubende', 'wpboutik' ),
			'UG108' => __( 'Mukono', 'wpboutik' ),
			'UG334' => __( 'Nabilatuk', 'wpboutik' ),
			'UG311' => __( 'Nakapiripirit', 'wpboutik' ),
			'UG116' => __( 'Nakaseke', 'wpboutik' ),
			'UG109' => __( 'Nakasongola', 'wpboutik' ),
			'UG230' => __( 'Namayingo', 'wpboutik' ),
			'UG234' => __( 'Namisindwa', 'wpboutik' ),
			'UG224' => __( 'Namutumba', 'wpboutik' ),
			'UG327' => __( 'Napak', 'wpboutik' ),
			'UG310' => __( 'Nebbi', 'wpboutik' ),
			'UG231' => __( 'Ngora', 'wpboutik' ),
			'UG424' => __( 'Ntoroko', 'wpboutik' ),
			'UG411' => __( 'Ntungamo', 'wpboutik' ),
			'UG328' => __( 'Nwoya', 'wpboutik' ),
			'UG331' => __( 'Omoro', 'wpboutik' ),
			'UG329' => __( 'Otuke', 'wpboutik' ),
			'UG321' => __( 'Oyam', 'wpboutik' ),
			'UG312' => __( 'Pader', 'wpboutik' ),
			'UG332' => __( 'Pakwach', 'wpboutik' ),
			'UG210' => __( 'Pallisa', 'wpboutik' ),
			'UG110' => __( 'Rakai', 'wpboutik' ),
			'UG429' => __( 'Rubanda', 'wpboutik' ),
			'UG425' => __( 'Rubirizi', 'wpboutik' ),
			'UG431' => __( 'Rukiga', 'wpboutik' ),
			'UG412' => __( 'Rukungiri', 'wpboutik' ),
			'UG111' => __( 'Sembabule', 'wpboutik' ),
			'UG232' => __( 'Serere', 'wpboutik' ),
			'UG426' => __( 'Sheema', 'wpboutik' ),
			'UG215' => __( 'Sironko', 'wpboutik' ),
			'UG211' => __( 'Soroti', 'wpboutik' ),
			'UG212' => __( 'Tororo', 'wpboutik' ),
			'UG113' => __( 'Wakiso', 'wpboutik' ),
			'UG313' => __( 'Yumbe', 'wpboutik' ),
			'UG330' => __( 'Zombo', 'wpboutik' ),
		),
		'UM' => array(
			'81' => __( 'Baker Island', 'wpboutik' ),
			'84' => __( 'Howland Island', 'wpboutik' ),
			'86' => __( 'Jarvis Island', 'wpboutik' ),
			'67' => __( 'Johnston Atoll', 'wpboutik' ),
			'89' => __( 'Kingman Reef', 'wpboutik' ),
			'71' => __( 'Midway Atoll', 'wpboutik' ),
			'76' => __( 'Navassa Island', 'wpboutik' ),
			'95' => __( 'Palmyra Atoll', 'wpboutik' ),
			'79' => __( 'Wake Island', 'wpboutik' ),
		),
		'US' => array( // United States.
			'AL' => __( 'Alabama', 'wpboutik' ),
			'AK' => __( 'Alaska', 'wpboutik' ),
			'AZ' => __( 'Arizona', 'wpboutik' ),
			'AR' => __( 'Arkansas', 'wpboutik' ),
			'CA' => __( 'California', 'wpboutik' ),
			'CO' => __( 'Colorado', 'wpboutik' ),
			'CT' => __( 'Connecticut', 'wpboutik' ),
			'DE' => __( 'Delaware', 'wpboutik' ),
			'DC' => __( 'District Of Columbia', 'wpboutik' ),
			'FL' => __( 'Florida', 'wpboutik' ),
			'GA' => _x( 'Georgia', 'US state of Georgia', 'wpboutik' ),
			'HI' => __( 'Hawaii', 'wpboutik' ),
			'ID' => __( 'Idaho', 'wpboutik' ),
			'IL' => __( 'Illinois', 'wpboutik' ),
			'IN' => __( 'Indiana', 'wpboutik' ),
			'IA' => __( 'Iowa', 'wpboutik' ),
			'KS' => __( 'Kansas', 'wpboutik' ),
			'KY' => __( 'Kentucky', 'wpboutik' ),
			'LA' => __( 'Louisiana', 'wpboutik' ),
			'ME' => __( 'Maine', 'wpboutik' ),
			'MD' => __( 'Maryland', 'wpboutik' ),
			'MA' => __( 'Massachusetts', 'wpboutik' ),
			'MI' => __( 'Michigan', 'wpboutik' ),
			'MN' => __( 'Minnesota', 'wpboutik' ),
			'MS' => __( 'Mississippi', 'wpboutik' ),
			'MO' => __( 'Missouri', 'wpboutik' ),
			'MT' => __( 'Montana', 'wpboutik' ),
			'NE' => __( 'Nebraska', 'wpboutik' ),
			'NV' => __( 'Nevada', 'wpboutik' ),
			'NH' => __( 'New Hampshire', 'wpboutik' ),
			'NJ' => __( 'New Jersey', 'wpboutik' ),
			'NM' => __( 'New Mexico', 'wpboutik' ),
			'NY' => __( 'New York', 'wpboutik' ),
			'NC' => __( 'North Carolina', 'wpboutik' ),
			'ND' => __( 'North Dakota', 'wpboutik' ),
			'OH' => __( 'Ohio', 'wpboutik' ),
			'OK' => __( 'Oklahoma', 'wpboutik' ),
			'OR' => __( 'Oregon', 'wpboutik' ),
			'PA' => __( 'Pennsylvania', 'wpboutik' ),
			'RI' => __( 'Rhode Island', 'wpboutik' ),
			'SC' => __( 'South Carolina', 'wpboutik' ),
			'SD' => __( 'South Dakota', 'wpboutik' ),
			'TN' => __( 'Tennessee', 'wpboutik' ),
			'TX' => __( 'Texas', 'wpboutik' ),
			'UT' => __( 'Utah', 'wpboutik' ),
			'VT' => __( 'Vermont', 'wpboutik' ),
			'VA' => __( 'Virginia', 'wpboutik' ),
			'WA' => __( 'Washington', 'wpboutik' ),
			'WV' => __( 'West Virginia', 'wpboutik' ),
			'WI' => __( 'Wisconsin', 'wpboutik' ),
			'WY' => __( 'Wyoming', 'wpboutik' ),
			'AA' => __( 'Armed Forces (AA)', 'wpboutik' ),
			'AE' => __( 'Armed Forces (AE)', 'wpboutik' ),
			'AP' => __( 'Armed Forces (AP)', 'wpboutik' ),
		),
		'VN' => array(),
		'YT' => array(),
		'ZA' => array( // South African states.
			'EC'  => __( 'Eastern Cape', 'wpboutik' ),
			'FS'  => __( 'Free State', 'wpboutik' ),
			'GP'  => __( 'Gauteng', 'wpboutik' ),
			'KZN' => __( 'KwaZulu-Natal', 'wpboutik' ),
			'LP'  => __( 'Limpopo', 'wpboutik' ),
			'MP'  => __( 'Mpumalanga', 'wpboutik' ),
			'NC'  => __( 'Northern Cape', 'wpboutik' ),
			'NW'  => __( 'North West', 'wpboutik' ),
			'WC'  => __( 'Western Cape', 'wpboutik' ),
		),
		'ZM' => array( // Zambia's Provinces. Ref: https://en.wikipedia.org/wiki/ISO_3166-2:ZM.
			'ZM-01' => __( 'Western', 'wpboutik' ),
			'ZM-02' => __( 'Central', 'wpboutik' ),
			'ZM-03' => __( 'Eastern', 'wpboutik' ),
			'ZM-04' => __( 'Luapula', 'wpboutik' ),
			'ZM-05' => __( 'Northern', 'wpboutik' ),
			'ZM-06' => __( 'North-Western', 'wpboutik' ),
			'ZM-07' => __( 'Southern', 'wpboutik' ),
			'ZM-08' => __( 'Copperbelt', 'wpboutik' ),
			'ZM-09' => __( 'Lusaka', 'wpboutik' ),
			'ZM-10' => __( 'Muchinga', 'wpboutik' ),
		),
	);
}

function get_wpboutik_tax_rates() {
	$tax_rates = apply_filters(
		'wpboutik_tax_rates',
		array(
			'AT' => [
				'percent_tx_standard' => 20.0000,
				'name_tx_standard'    => 'TVA 20%',
				'percent_tx_reduce'   => 10.0000,
				'name_tx_reduce'      => 'TVA 10%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'BE' => [
				'percent_tx_standard' => 21.0000,
				'name_tx_standard'    => 'TVA 21%',
				'percent_tx_reduce'   => 6.0000,
				'name_tx_reduce'      => 'TVA 6%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'BG' => [
				'percent_tx_standard' => 20.0000,
				'name_tx_standard'    => 'TVA 20%',
				'percent_tx_reduce'   => 9.0000,
				'name_tx_reduce'      => 'TVA 9%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'CY' => [
				'percent_tx_standard' => 19.0000,
				'name_tx_standard'    => 'TVA 19%',
				'percent_tx_reduce'   => 5.0000,
				'name_tx_reduce'      => 'TVA 5%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'CZ' => [
				'percent_tx_standard' => 21.0000,
				'name_tx_standard'    => 'TVA 21%',
				'percent_tx_reduce'   => 10.0000,
				'name_tx_reduce'      => 'TVA 10%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'DE' => [
				'percent_tx_standard' => 19.0000,
				'name_tx_standard'    => 'TVA 19%',
				'percent_tx_reduce'   => 7.0000,
				'name_tx_reduce'      => 'TVA 7%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'DK' => [
				'percent_tx_standard' => 25.0000,
				'name_tx_standard'    => 'TVA 25%',
				'percent_tx_reduce'   => 0,
				'name_tx_reduce'      => 'TVA 0%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'EE' => [
				'percent_tx_standard' => 20.0000,
				'name_tx_standard'    => 'TVA 20%',
				'percent_tx_reduce'   => 9.0000,
				'name_tx_reduce'      => 'TVA 9%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'EL' => [
				'percent_tx_standard' => 24.0000,
				'name_tx_standard'    => 'TVA 24%',
				'percent_tx_reduce'   => 6.0000,
				'name_tx_reduce'      => 'TVA 6%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			], // Grèce
			'GR' => [
				'percent_tx_standard' => 24.0000,
				'name_tx_standard'    => 'TVA 24%',
				'percent_tx_reduce'   => 6.0000,
				'name_tx_reduce'      => 'TVA 6%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			], // Grèce ISO
			'ES' => [
				'percent_tx_standard' => 21.0000,
				'name_tx_standard'    => 'TVA 21%',
				'percent_tx_reduce'   => 10.0000,
				'name_tx_reduce'      => 'TVA 10%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'FI' => [
				'percent_tx_standard' => 24.0000,
				'name_tx_standard'    => 'TVA 24%',
				'percent_tx_reduce'   => 10.0000,
				'name_tx_reduce'      => 'TVA 10%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'FR' => [
				'percent_tx_standard' => 20.0000,
				'name_tx_standard'    => 'TVA 20%',
				'percent_tx_reduce'   => 5.5000,
				'name_tx_reduce'      => 'TVA 5,5%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'UK' => [
				'percent_tx_standard' => 20.0000,
				'name_tx_standard'    => 'TVA 20%',
				'percent_tx_reduce'   => 0,
				'name_tx_reduce'      => 'TVA 0%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'HR' => [
				'percent_tx_standard' => 25.0000,
				'name_tx_standard'    => 'TVA 25%',
				'percent_tx_reduce'   => 5.0000,
				'name_tx_reduce'      => 'TVA 5%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'HU' => [
				'percent_tx_standard' => 27.0000,
				'name_tx_standard'    => 'TVA 27%',
				'percent_tx_reduce'   => 5.0000,
				'name_tx_reduce'      => 'TVA 5%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'IE' => [
				'percent_tx_standard' => 23.0000,
				'name_tx_standard'    => 'TVA 23%',
				'percent_tx_reduce'   => 13.5000,
				'name_tx_reduce'      => 'TVA 13,5%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'IT' => [
				'percent_tx_standard' => 22.0000,
				'name_tx_standard'    => 'TVA 22%',
				'percent_tx_reduce'   => 5.0000,
				'name_tx_reduce'      => 'TVA 5%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'LT' => [
				'percent_tx_standard' => 21.0000,
				'name_tx_standard'    => 'TVA 21%',
				'percent_tx_reduce'   => 5.0000,
				'name_tx_reduce'      => 'TVA 5%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'LU' => [
				'percent_tx_standard' => 16.0000,
				'name_tx_standard'    => 'TVA 16%',
				'percent_tx_reduce'   => 7.0000,
				'name_tx_reduce'      => 'TVA 7%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'LV' => [
				'percent_tx_standard' => 21.0000,
				'name_tx_standard'    => 'TVA 21%',
				'percent_tx_reduce'   => 12.0000,
				'name_tx_reduce'      => 'TVA 12%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'MT' => [
				'percent_tx_standard' => 18.0000,
				'name_tx_standard'    => 'TVA 18%',
				'percent_tx_reduce'   => 5.0000,
				'name_tx_reduce'      => 'TVA 5%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'NL' => [
				'percent_tx_standard' => 21.0000,
				'name_tx_standard'    => 'TVA 21%',
				'percent_tx_reduce'   => 0.0000,
				'name_tx_reduce'      => 'TVA 0%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'PL' => [
				'percent_tx_standard' => 23.0000,
				'name_tx_standard'    => 'TVA 23%',
				'percent_tx_reduce'   => 5.0000,
				'name_tx_reduce'      => 'TVA 5%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'PT' => [
				'percent_tx_standard' => 23.0000,
				'name_tx_standard'    => 'TVA 23%',
				'percent_tx_reduce'   => 6.0000,
				'name_tx_reduce'      => 'TVA 6%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'RO' => [
				'percent_tx_standard' => 19.0000,
				'name_tx_standard'    => 'TVA 19%',
				'percent_tx_reduce'   => 5.0000,
				'name_tx_reduce'      => 'TVA 5%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'SI' => [
				'percent_tx_standard' => 22.0000,
				'name_tx_standard'    => 'TVA 22%',
				'percent_tx_reduce'   => 9.5000,
				'name_tx_reduce'      => 'TVA 9,5%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'SE' => [
				'percent_tx_standard' => 25.0000,
				'name_tx_standard'    => 'TVA 25%',
				'percent_tx_reduce'   => 6.0000,
				'name_tx_reduce'      => 'TVA 6%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
			'SK' => [
				'percent_tx_standard' => 20.0000,
				'name_tx_standard'    => 'TVA 20%',
				'percent_tx_reduce'   => 10.0000,
				'name_tx_reduce'      => 'TVA 10%',
				'percent_tx_zero'     => 0,
				'name_tx_zero'        => 'TVA 0%'
			],
		)
	);

	return $tax_rates;
}

/**
 * Get a service WPBoutik
 *
 * @param string $service
 *
 * @return object
 * @throws Exception
 * @since 1.0
 *
 */
function wpboutik_get_service( $service ) {
	//return get_service( $service );
}

function wpboutik_calculateOrderAmount( array $items, $method, $shipping_country_key, $form_datas = [] ): int {
	$ordertotal = 0;
	if ( ! WPB()->cart->is_empty() ) {

		$subtotal = 0;

		$activate_tax = wpboutik_get_option_params( 'activate_tax' );
		if ( $activate_tax ) {
			$taxes_class = array();
		}
		foreach ( WPB()->cart->get_cart() as $cart_item_key => $subArray ) {
			$subArray = (object) $subArray;
			if ( $subArray->variation_id != "0" ) {
				$id_for_tax_class = $subArray->variation_id;
				$variants         = get_post_meta( $subArray->product_id, 'variants', true );
				$variation        = wpboutik_get_variation_by_id( json_decode( $variants ), $subArray->variation_id );
				$price            = ( strpos( $subArray->variation_id, 'custom' ) !== false ) ? $subArray->customization['gift_card_price'] : $variation->price;
			} else {
				$id_for_tax_class = $subArray->product_id;
				$price            = get_post_meta( $subArray->product_id, 'price', true );
			}

			if ( $activate_tax ) {
				$tax_class = get_post_meta( $subArray->product_id, 'tax', true );;
				$taxes_class[ $tax_class ][ $id_for_tax_class ] = $subArray->quantity * $price;
			}
			$selling_fees = get_post_meta( $subArray->product_id, 'selling_fees', true );
			$type         = get_post_meta( $subArray->product_id, 'type', true );
			if ( ( $type != 'abonnement' && $type != "plugin" ) || empty( $selling_fees ) || ! empty( $subArray->customization['renew'] ) ) {
				$selling_fees = 0;
			}
			$subtotal = $subtotal + ( $subArray->quantity * ( $price + $selling_fees ) );
		}

		$wp_get_discount = wpb_get_discount_cart( $subtotal, WPB()->cart->get_cart(), $activate_tax, ( $taxes_class ?? null ) );
		$discount        = ( $wp_get_discount['discount'] ?? 0 );
		$taxes_class     = ( $wp_get_discount['taxes_class'] ?? ( $taxes_class ?? false ) );

		$tax = 0;
		if ( $taxes_class ) {
			$tax_rates = get_wpboutik_tax_rates();
			if ( isset( $tax_rates[ $shipping_country_key ] ) ) {
				foreach ( $taxes_class as $tax_class => $products_of_tax ) {
					$count = 0;
					foreach ( $products_of_tax as $value ) {
						$count += $value;
					}
					$tax_value = round( ( $count ) * ( $tax_rates[ $shipping_country_key ][ 'percent_tx_' . $tax_class ] / 100 ), 2 );
					$tax       += $tax_value;
				}
			}
		}

		$shipping = 0;
		if ( $method ) {
			parse_str( $_POST['datas'], $form_datas );
			$shipping = \NF\WPBOUTIK\Ajax::wpb_get_shipping_price( $method, $form_datas, WPB()->cart->get_cart() );
			$shipping = round( $shipping, 2 );
		}

		$ordertotal = $subtotal - $discount + $shipping + $tax;

		$ordertotal = WPB_Gift_Card::get_finale_price( $ordertotal );

		return $ordertotal * 100;
	}

	return $ordertotal;
}

function get_evenly_reduced_shipping( $method, $shipping_value ) {
	if ( isset( $method['reduce'] ) && ! empty( $method['reduce'] ) ) {
		$reduce_opts = ( ! is_array( $method['reduce'] ) ) ? json_decode( $method['reduce'] ) : $method['reduce'];
		if ( $reduce_opts && ! ! sizeof( $reduce_opts ) ) {
			$cart_total = wpboutik_calculateOrderAmount( [], false, 'fr' ) / 100;
			$max_reduce = false;
			foreach ( $reduce_opts as $reduce ) {
				$reduce = (array) $reduce;
				if ( (float) $reduce['cart_value'] > $cart_total ) {
					break;
				} else {
					$max_reduce = [
						'value' => (float) $reduce['value'],
						'unit'  => $reduce['unit']
					];
				}
			}

			if ( $max_reduce ) {
				if ( $max_reduce['unit'] == 'percent' ) {
					$shipping_value = $shipping_value - ( $shipping_value * ( $max_reduce['value'] / 100 ) );
				} else {
					$shipping_value = $shipping_value - $max_reduce['value'];
				}
			}
		}
	}

	return ( $shipping_value >= 0 ) ? $shipping_value : 0;
}

function wpboutik_removeElementWithValue( $array, $key, $value ) {
	foreach ( $array as $cart_item_key => $subArray ) {
		if ( $subArray->$key == $value ) {
			unset( $array[ $cart_item_key ] );
		}
	}

	return $array;
}

function getOrCreateStripeCustomer( $email, $apiKey ) {
	Stripe\Stripe::setApiKey( $apiKey );

	// Search for an existing customer with the given email
	$customers = Stripe\Customer::all( [ 'email' => $email, 'limit' => 1 ] );

	if ( ! empty( $customers->data ) ) {
		// Return the ID of the existing customer
		return $customers->data[0]->id;
	} else {
		// Create a new customer
		$customer = Stripe\Customer::create( [
			'email' => $email,
		] );

		// Return the ID of the new customer
		return $customer->id;
	}
}


function updatePaymentIntentStripe( $method, $shipping_country_key, $amount = '', $form_datas = [], $customer = null ) {
	if ( ! isset ( $_COOKIE['wpboutik_paymentintent_id'] ) ) {
		return false;
	}

	$options = get_option( 'wpboutik_options_params' );

	\Stripe\Stripe::setApiKey( $options['stripe_secret_key'] );

	$wpboutik_paymentintent_id = $_COOKIE['wpboutik_paymentintent_id'];
	$paymentIntent             = \Stripe\PaymentIntent::update( $wpboutik_paymentintent_id, [
		'amount'   => ( $amount ) ? $amount * 100 : wpboutik_calculateOrderAmount( [], $method, $shipping_country_key, $form_datas ),
		'customer' => $customer
	] );

	return $paymentIntent;
}

function wpboutik_get_permalink_structure() {
	$saved_permalinks = (array) get_option( 'wpboutik_permalinks', array() );
	$permalinks       = wp_parse_args(
		array_filter( $saved_permalinks ),
		array(
			'product_base'           => _x( 'product', 'slug', 'wpboutik' ),
			'category_base'          => _x( 'product-category', 'slug', 'wpboutik' ),
			'tag_base'               => _x( 'product-tag', 'slug', 'wpboutik' ),
			'attribute_base'         => '',
			'use_verbose_page_rules' => false,
		)
	);

	$permalinks['product_rewrite_slug']   = untrailingslashit( $permalinks['product_base'] );
	$permalinks['category_rewrite_slug']  = untrailingslashit( $permalinks['category_base'] );
	$permalinks['tag_rewrite_slug']       = untrailingslashit( $permalinks['tag_base'] );
	$permalinks['attribute_rewrite_slug'] = untrailingslashit( $permalinks['attribute_base'] );

	return $permalinks;
}

if ( ! function_exists( 'wpboutik_comments' ) ) {

	/**
	 * Output the Review comments template.
	 *
	 * @param WP_Comment $comment Comment object.
	 * @param array $args Arguments.
	 * @param int $depth Depth.
	 */
	function wpboutik_comments( $comment, $args, $depth ) {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['comment'] = $comment;

		extract(
			array(
				'comment' => $comment,
				'args'    => $args,
				'depth'   => $depth,
			)
		);

		include trailingslashit( WPBOUTIK_TEMPLATES ) . '/single/product-review.php';
	}
}

if ( ! function_exists( 'is_wpboutik_product_taxonomy' ) ) {

	/**
	 * is_wpboutik_product_taxonomy - Returns true when viewing a product taxonomy archive.
	 *
	 * @return bool
	 */
	function is_wpboutik_product_taxonomy() {
		return is_tax( get_object_taxonomies( 'wpboutik_product' ) );
	}
}

function wpb_get_gift_card_cart() {
	if ( ! isset( $_COOKIE['wpboutik_gift_card_code'] ) ) {
		return false;
	}
	$wpboutik_coupons_code = json_decode( stripslashes( $_COOKIE['wpboutik_gift_card_code'] ) );
	$code                  = get_option( 'wpboutik_options_gift_card_code_' . $wpboutik_coupons_code->id );

	return $code;
}

function wpb_get_discount_cart( $subtotal, $products, $activate_tax, $taxes_class = null, $id_products_with_discount = [] ) {
	$discount = 0;
	if ( ! isset( $_COOKIE['wpboutik_coupons_code'] ) ) {
		return false;
	}

	$wpboutik_coupons_code = json_decode( stripslashes( $_COOKIE['wpboutik_coupons_code'] ) );
	$coupon_code           = get_option( 'wpboutik_options_coupon_code_' . $wpboutik_coupons_code->id );

	if ( ! empty( $coupon_code['id_categories'] ) ) {
		$list_products_category = new \WP_Query( array(
			'post_type'      => 'wpboutik_product',
			'posts_per_page' => - 1,
			'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' ),
			'fields'         => 'ids',
			'tax_query'      => array(
				array(
					'taxonomy' => 'wpboutik_product_cat',
					'field'    => 'term_id',
					'terms'    => $coupon_code['id_categories'],
				)
			),
		) );

		$coupon_code['id_products'] = array_merge( $coupon_code['id_products'], array_combine( $list_products_category->posts, $list_products_category->posts ) );
	}

	if ( ! empty( $coupon_code['id_products'] ) ) {
		$flatArrayIdProducts = array();

		foreach ( $coupon_code['id_products'] as $subArray ) {
			if ( is_array( $subArray ) ) {
				$flatArrayIdProducts = array_merge( $flatArrayIdProducts, array_values( $subArray ) );
			} else {
				$flatArrayIdProducts = array_merge( $flatArrayIdProducts, array( $subArray ) );
			}
		}
	}

	if ( 'percent' == $coupon_code['type'] ) {
		$coupon_code['valeur'] = ( $coupon_code['valeur'] < 100 ) ? $coupon_code['valeur'] : 100;
		if ( empty( $coupon_code['id_products'] ) && empty( $coupon_code['id_categories'] ) ) {
			$prices = 0;
			foreach ( $products as $stored_product ) {
				$stored_product = (object) $stored_product;
				if ( $stored_product->variation_id != "0" ) {
					$id_for_tax_class            = $stored_product->variation_id;
					$id_products_with_discount[] = $stored_product->variation_id;
					$variants                    = get_post_meta( $stored_product->product_id, 'variants', true );
					$variation                   = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );

					if ( isset( $coupon_code['exclude_promo_product'] ) && $coupon_code['exclude_promo_product'] == 1 ) {
						if ( isset ( $variation->price_before_reduction ) ) {
							$price_before_reduction = $variation->price_before_reduction;
							if ( $price_before_reduction && $price_before_reduction != 0 ) {
								continue;
							}
						}
					}

					$price = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;
				} else {

					if ( isset( $coupon_code['exclude_promo_product'] ) && $coupon_code['exclude_promo_product'] == 1 ) {
						$price_before_reduction = get_post_meta( $stored_product->product_id, 'price_before_reduction', true );
						if ( $price_before_reduction && $price_before_reduction != 0 ) {
							continue;
						}
					}

					$id_for_tax_class            = $stored_product->product_id;
					$id_products_with_discount[] = $stored_product->product_id;
					$price                       = get_post_meta( $stored_product->product_id, 'price', true );
				}

				$selling_fees = get_post_meta( $stored_product->product_id, 'selling_fees', true );
				$type         = get_post_meta( $stored_product->product_id, 'type', true );
				if ( ( $type != 'abonnement' && $type != "plugin" ) || empty( $selling_fees ) || ! empty( $stored_product->customization['renew'] ) ) {
					$selling_fees = 0;
				}

				$tax_class                                      = get_post_meta( $stored_product->product_id, 'tax', true );
				$prices                                         += $stored_product->quantity * ( $price + $selling_fees );
				$taxes_class[ $tax_class ][ $id_for_tax_class ] = $stored_product->quantity * ( ( $price + $selling_fees ) - ( ( $price + $selling_fees ) * ( $coupon_code['valeur'] / 100 ) ) );
			}

			$discount = $prices * ( $coupon_code['valeur'] / 100 );
		} else {
			$discount = 0;
			foreach ( $products as $stored_product ) {
				$stored_product = (object) $stored_product;
				if ( in_array( $stored_product->product_id, $coupon_code['id_products'] ) ) {
					if ( $stored_product->variation_id != "0" ) {

						if ( ! in_array( $stored_product->variation_id, $flatArrayIdProducts ) ) {
							continue;
						}

						$id_for_tax_class            = $stored_product->variation_id;
						$id_products_with_discount[] = $stored_product->variation_id;
						$variants                    = get_post_meta( $stored_product->product_id, 'variants', true );
						$variation                   = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );

						if ( isset( $coupon_code['exclude_promo_product'] ) && $coupon_code['exclude_promo_product'] == 1 ) {
							if ( isset ( $variation->price_before_reduction ) ) {
								$price_before_reduction = $variation->price_before_reduction;
								if ( $price_before_reduction && $price_before_reduction != 0 ) {
									continue;
								}
							}
						}

						$price = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;
					} else {

						if ( isset( $coupon_code['exclude_promo_product'] ) && $coupon_code['exclude_promo_product'] == 1 ) {
							$price_before_reduction = get_post_meta( $stored_product->product_id, 'price_before_reduction', true );
							if ( $price_before_reduction && $price_before_reduction != 0 ) {
								continue;
							}
						}

						$id_for_tax_class            = $stored_product->product_id;
						$id_products_with_discount[] = $stored_product->product_id;
						$price                       = get_post_meta( $stored_product->product_id, 'price', true );
					}

					$selling_fees = get_post_meta( $stored_product->product_id, 'selling_fees', true );
					$type         = get_post_meta( $stored_product->product_id, 'type', true );
					if ( ( $type != 'abonnement' && $type != "plugin" ) || empty( $selling_fees ) || ! empty( $stored_product->customization['renew'] ) ) {
						$selling_fees = 0;
					}

					$discount = $discount + ( $stored_product->quantity * ( ( $price + $selling_fees ) * ( $coupon_code['valeur'] / 100 ) ) );
					if ( $activate_tax ) {
						$tax_class                                      = get_post_meta( $stored_product->product_id, 'tax', true );
						$taxes_class[ $tax_class ][ $id_for_tax_class ] = $stored_product->quantity * ( ( $price + $selling_fees ) - ( ( $price + $selling_fees ) * ( $coupon_code['valeur'] / 100 ) ) );
					}
				}
			}
		}
	} else {
		$totalQuantity = 0;
		foreach ( $products as $item ) {
			$item          = (object) $item;
			$totalQuantity += $item->quantity;
		}

		if ( empty( $coupon_code['id_products'] ) && empty( $coupon_code['id_categories'] ) ) {
			$prices = 0;
			foreach ( $products as $stored_product ) {
				$stored_product = (object) $stored_product;
				if ( $stored_product->variation_id != "0" ) {
					$id_for_tax_class            = $stored_product->variation_id;
					$id_products_with_discount[] = $stored_product->variation_id;
					$variants                    = get_post_meta( $stored_product->product_id, 'variants', true );
					$variation                   = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );

					if ( isset( $coupon_code['exclude_promo_product'] ) && $coupon_code['exclude_promo_product'] == 1 ) {
						if ( isset ( $variation->price_before_reduction ) ) {
							$price_before_reduction = $variation->price_before_reduction;
							if ( $price_before_reduction && $price_before_reduction != 0 ) {
								continue;
							}
						}
					}

					$price = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;
				} else {

					if ( isset( $coupon_code['exclude_promo_product'] ) && $coupon_code['exclude_promo_product'] == 1 ) {
						$price_before_reduction = get_post_meta( $stored_product->product_id, 'price_before_reduction', true );
						if ( $price_before_reduction && $price_before_reduction != 0 ) {
							continue;
						}
					}

					$id_for_tax_class            = $stored_product->product_id;
					$id_products_with_discount[] = $stored_product->product_id;
					$price                       = get_post_meta( $stored_product->product_id, 'price', true );
				}

				$selling_fees = get_post_meta( $stored_product->product_id, 'selling_fees', true );
				$type         = get_post_meta( $stored_product->product_id, 'type', true );
				if ( ( $type != 'abonnement' && $type != "plugin" ) || empty( $selling_fees ) || ! empty( $stored_product->customization['renew'] ) ) {
					$selling_fees = 0;
				}

				$tax_class = get_post_meta( $stored_product->product_id, 'tax', true );
				$prices    += $stored_product->quantity * ( $price + $selling_fees );

				$taxes_class[ $tax_class ][ $id_for_tax_class ] = $stored_product->quantity * ( ( $price + $selling_fees ) - ( $coupon_code['valeur'] / $totalQuantity ) );
			}

			$discount = ( $prices > $coupon_code['valeur'] ) ? $coupon_code['valeur'] : $prices;
		} else {
			$discount = 0;
			foreach ( $products as $stored_product ) {

				$stored_product = (object) $stored_product;
				if ( in_array( $stored_product->product_id, $coupon_code['id_products'] ) ) {
					if ( $stored_product->variation_id != "0" ) {

						if ( ! in_array( $stored_product->variation_id, $flatArrayIdProducts ) ) {
							continue;
						}

						$id_for_tax_class            = $stored_product->variation_id;
						$id_products_with_discount[] = $stored_product->variation_id;
						$variants                    = get_post_meta( $stored_product->product_id, 'variants', true );
						$variation                   = wpboutik_get_variation_by_id( json_decode( $variants ), $stored_product->variation_id );

						if ( isset( $coupon_code['exclude_promo_product'] ) && $coupon_code['exclude_promo_product'] == 1 ) {
							if ( isset ( $variation->price_before_reduction ) ) {
								$price_before_reduction = $variation->price_before_reduction;
								if ( $price_before_reduction && $price_before_reduction != 0 ) {
									continue;
								}
							}
						}

						$price = ( strpos( $stored_product->variation_id, 'custom' ) !== false ) ? $stored_product->customization['gift_card_price'] : $variation->price;
					} else {

						if ( isset( $coupon_code['exclude_promo_product'] ) && $coupon_code['exclude_promo_product'] == 1 ) {
							$price_before_reduction = get_post_meta( $stored_product->product_id, 'price_before_reduction', true );
							if ( $price_before_reduction && $price_before_reduction != 0 ) {
								continue;
							}
						}

						$id_for_tax_class            = $stored_product->product_id;
						$id_products_with_discount[] = $stored_product->product_id;
						$price                       = get_post_meta( $stored_product->product_id, 'price', true );
					}

					$discount = $discount + ( $stored_product->quantity * $coupon_code['valeur'] );

					$selling_fees = get_post_meta( $stored_product->product_id, 'selling_fees', true );
					$type         = get_post_meta( $stored_product->product_id, 'type', true );
					if ( ( $type != 'abonnement' && $type != "plugin" ) || empty( $selling_fees ) || ! empty( $stored_product->customization['renew'] ) ) {
						$selling_fees = 0;
					}

					if ( $activate_tax ) {
						$tax_class                                      = get_post_meta( $stored_product->product_id, 'tax', true );
						$taxes_class[ $tax_class ][ $id_for_tax_class ] = $stored_product->quantity * ( ( $price + $selling_fees ) - ( $coupon_code['valeur'] / $totalQuantity ) );
					}

					if ( $discount > ( $price + $selling_fees ) && $subtotal < ( $price + $selling_fees ) ) {
						$discount = $price + $selling_fees;
					}

					if ( $discount > ( $price + $selling_fees ) && $activate_tax ) {
						$taxes_class[ $tax_class ][ $id_for_tax_class ] = 0;
					}
				}
			}
			$discount = ( $subtotal > $discount ) ? $discount : $subtotal;
		}
	}

	return array(
		'discount'                  => $discount,
		'taxes_class'               => $taxes_class,
		'id_products_with_discount' => $id_products_with_discount,
		'coupon_id'                 => $wpboutik_coupons_code->id
	);
}

function wpboutik_get_best_sellers() {
	$options = get_option( 'wpboutik_options' );
	if ( false === ( $response = get_transient( 'wpboutik_best_sellers' ) ) ) {

		$api_query = WPB_Api_Request::request( 'best_sellers' )
		                            ->add_to_body( 'options', $options )
		                            ->exec();

		$response = (array) json_decode( $api_query->get_response_body() );

		set_transient( 'wpboutik_best_sellers', $response, HOUR_IN_SECONDS );
	}

	return $response;
}

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code Code.
 */
function wpb_enqueue_js( $code ) {
	global $wpb_queued_js;

	if ( empty( $wpb_queued_js ) ) {
		$wpb_queued_js = '';
	}

	$wpb_queued_js .= "\n" . $code . "\n";
}

/**
 * Output any queued javascript code in the footer.
 */
function wpb_print_js() {
	global $wpb_queued_js;

	if ( ! empty( $wpb_queued_js ) ) {
		// Sanitize.
		$wpb_queued_js = wp_check_invalid_utf8( $wpb_queued_js );
		$wpb_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $wpb_queued_js );
		$wpb_queued_js = str_replace( "\r", '', $wpb_queued_js );

		$js = "<!-- WPBoutik JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $wpb_queued_js });\n</script>\n";

		/**
		 * Queued jsfilter.
		 *
		 * @param string $js JavaScript code.
		 *
		 * @since 1.0.0
		 */
		echo apply_filters( 'wpboutik_queued_js', $js ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		unset( $wpb_queued_js );
	}
}

if ( ! function_exists( 'is_wpboutik_order_received_page' ) ) {

	/**
	 * Is_wpoutik_order_received_page - Returns true when viewing the order received page.
	 *
	 * @return bool
	 */
	function is_wpboutik_order_received_page() {
		global $wp;

		$page_id = wpboutik_get_page_id( 'checkout' );

		return apply_filters( 'is_wpboutik_order_received_page', ( $page_id && is_page( $page_id ) && isset( $wp->query_vars['order-received'] ) ) );
	}
}

/**
 * Is_wpboutik - Returns true if on a page which uses WPBoutik templates (cart and checkout are standard pages with shortcodes and thus are not included).
 *
 * @return bool
 */
function is_wpboutik() {
	return apply_filters( 'is_wpboutik', is_wpboutik_shop() || is_wpboutik_product_taxonomy() || is_wpboutik_product() );
}

if ( ! function_exists( 'is_wpboutik_shop' ) ) {

	/**
	 * Is_wpboutik_shop - Returns true when viewing the product type archive (shop).
	 *
	 * @return bool
	 */
	function is_wpboutik_shop() {
		return ( is_post_type_archive( 'wpboutik_product' ) || is_page( wpboutik_get_page_id( 'shop' ) ) );
	}
}

if ( ! function_exists( 'is_wpboutik_product' ) ) {

	/**
	 * Is_wpboutik_product - Returns true when viewing a single product.
	 *
	 * @return bool
	 */
	function is_wpboutik_product() {
		return is_singular( array( 'wpboutik_product' ) );
	}
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var Data to sanitize.
 *
 * @return string|array
 */
function wpb_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'wpb_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Get the price format depending on the currency position.
 *
 * @return string
 */
function get_wpboutik_price_format() {
	//$currency_pos = get_option( 'wpboutik_currency_pos' );
	$currency_pos = 'right_space';
	$format       = '%1$s%2$s';

	switch ( $currency_pos ) {
		case 'left':
			$format = '%1$s%2$s';
			break;
		case 'right':
			$format = '%2$s%1$s';
			break;
		case 'left_space':
			$format = '%1$s&nbsp;%2$s';
			break;
		case 'right_space':
			$format = '%2$s&nbsp;%1$s';
			break;
	}

	return apply_filters( 'wpboutik_price_format', $format, $currency_pos );
}

/**
 * Return the thousand separator for prices.
 *
 * @return string
 * @since  2.3
 */
function wpb_get_price_thousand_separator() {
	return stripslashes( apply_filters( 'wpb_get_price_thousand_separator', ' ' ) );
}

/**
 * Return the decimal separator for prices.
 *
 * @return string
 */
function wpb_get_price_decimal_separator() {
	$separator = apply_filters( 'wpb_get_price_decimal_separator', ',' );

	return $separator ? stripslashes( $separator ) : '.';
}

/**
 * Outputs hidden form inputs for each query string variable.
 *
 * @param string|array $values Name value pairs, or a URL to parse.
 * @param array $exclude Keys to exclude.
 * @param string $current_key Current key we are outputting.
 * @param bool $return Whether to return.
 *
 * @return string
 * @since 3.0.0
 */
function wpb_query_string_form_fields( $values = null, $exclude = array(), $current_key = '', $return = false ) {
	if ( is_null( $values ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$values = $_GET;
	} elseif ( is_string( $values ) ) {
		$url_parts = wp_parse_url( $values );
		$values    = array();

		if ( ! empty( $url_parts['query'] ) ) {
			// This is to preserve full-stops, pluses and spaces in the query string when ran through parse_str.
			$replace_chars = array(
				'.' => '{dot}',
				'+' => '{plus}',
			);

			$query_string = str_replace( array_keys( $replace_chars ), array_values( $replace_chars ), $url_parts['query'] );

			// Parse the string.
			parse_str( $query_string, $parsed_query_string );

			// Convert the full-stops, pluses and spaces back and add to values array.
			foreach ( $parsed_query_string as $key => $value ) {
				$new_key            = str_replace( array_values( $replace_chars ), array_keys( $replace_chars ), $key );
				$new_value          = str_replace( array_values( $replace_chars ), array_keys( $replace_chars ), $value );
				$values[ $new_key ] = $new_value;
			}
		}
	}
	$html = '';

	foreach ( $values as $key => $value ) {
		if ( in_array( $key, $exclude, true ) ) {
			continue;
		}
		if ( $current_key ) {
			$key = $current_key . '[' . $key . ']';
		}
		if ( is_array( $value ) ) {
			$html .= wpb_query_string_form_fields( $value, $exclude, $key, true );
		} else {
			$html .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( wp_unslash( $value ) ) . '" />';
		}
	}

	if ( $return ) {
		return $html;
	}

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * WPBoutik Dropdown categories.
 *
 * @param array $args Args to control display of dropdown.
 */
function wpb_product_dropdown_categories( $args = array() ) {
	global $wp_query;

	$args = wp_parse_args(
		$args,
		array(
			'pad_counts'         => 1,
			'show_count'         => 1,
			'hierarchical'       => 1,
			'hide_empty'         => 1,
			'show_uncategorized' => 1,
			'orderby'            => 'name',
			'selected'           => isset( $wp_query->query_vars['wpboutik_product_cat'] ) ? $wp_query->query_vars['wpboutik_product_cat'] : '',
			'show_option_none'   => __( 'Toutes les catégories', 'wpboutik' ),
			'option_none_value'  => '',
			'value_field'        => 'slug',
			'taxonomy'           => 'wpboutik_product_cat',
			'name'               => 'wpboutik_product_cat',
			'class'              => 'dropdown_wpboutik_product_cat',
		)
	);

	if ( 'order' === $args['orderby'] ) {
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = 'order';
	}
	$args['aria_describedby'] = 'widget-select-product-cat';
	echo '<span class="sr-only" id="widget-select-product-cat">' . __( 'Select Category' ) . '</span>';
	wp_dropdown_categories( $args );
}

add_filter( 'walker_nav_menu_start_el', 'gol_walker_nav_menu_start_el', 10, 4 );
function gol_walker_nav_menu_start_el( $item_output, $item, $depth, $args ) {

	preg_match_all( '/<a.+>(\[.+\])<\/a>/i', $item_output, $matches );
	if ( ! empty( $matches[1][0] ) ) {
		$item_output = $matches[1][0];
	}

	return do_shortcode( $item_output );
}

/**
 * Given an element name, returns a class name.
 *
 * If the WP-related function is not defined or current theme is not a FSE theme, return empty string.
 *
 * @param string $element The name of the element.
 *
 * @return string
 */
function wpb_wp_theme_get_element_class_name( $element ) {
	if ( wpb_current_theme_is_fse_theme() && function_exists( 'wp_theme_get_element_class_name' ) ) {
		return wp_theme_get_element_class_name( $element );
	}

	return '';
}

/**
 * Check if the current theme is a block theme.
 *
 * @return bool
 */
function wpb_current_theme_is_fse_theme() {
	if ( function_exists( 'wp_is_block_theme' ) ) {
		return (bool) wp_is_block_theme();
	}
	if ( function_exists( 'gutenberg_is_fse_theme' ) ) {
		return (bool) gutenberg_is_fse_theme();
	}

	return false;
}

/**
 * Get data if set, otherwise return a default value or null. Prevents notices when data is not set.
 *
 * @param mixed $var Variable.
 * @param string $default Default value.
 *
 * @return mixed
 */
function wpb_get_var( &$var, $default = null ) {
	return isset( $var ) ? $var : $default;
}

/**
 * Retrieves a user row based on password reset key and login.
 *
 * @param string $key Hash to validate sending user's password.
 * @param string $login The user login.
 *
 * @return \WP_User|bool User's database row on success, false for invalid keys
 * @uses $wpdb WordPress Database object.
 */
function wpb_check_password_reset_key( $key, $login ) {
	// Check for the password reset key.
	// Get user data or an error message in case of invalid or expired key.
	$user = check_password_reset_key( $key, $login );

	if ( is_wp_error( $user ) ) {
		//wc_add_notice( __( 'This key is invalid or has already been used. Please reset your password again if needed.', 'wpboutik' ), 'error' );
		return false;
	}

	return $user;
}

/**
 * Set a cookie - wrapper for setcookie using WP constants.
 *
 * @param string $name Name of the cookie being set.
 * @param string $value Value of the cookie.
 * @param integer $expire Expiry of the cookie.
 * @param bool $secure Whether the cookie should be served only over https.
 * @param bool $httponly Whether the cookie is only accessible over HTTP, not scripting languages like JavaScript.
 */
function wpb_setcookie( $name, $value, $expire = 0, $secure = false, $httponly = false ) {
	/**
	 * Controls whether the cookie should be set via wpb_setcookie().
	 *
	 * @param bool $set_cookie_enabled If wpb_setcookie() should set the cookie.
	 * @param string $name Cookie name.
	 * @param string $value Cookie value.
	 * @param integer $expire When the cookie should expire.
	 * @param bool $secure If the cookie should only be served over HTTPS.
	 */
	if ( ! apply_filters( 'wpboutik_set_cookie_enabled', true, $name, $value, $expire, $secure ) ) {
		return;
	}

	if ( ! headers_sent() ) {
		/**
		 * Controls the options to be specified when setting the cookie.
		 *
		 * @see   https://www.php.net/manual/en/function.setcookie.php
		 *
		 * @param array $cookie_options Cookie options.
		 * @param string $name Cookie name.
		 * @param string $value Cookie value.
		 */
		$options = apply_filters(
			'wpboutik_set_cookie_options',
			array(
				'expires'  => $expire,
				'secure'   => $secure,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				/**
				 * Controls whether the cookie should only be accessible via the HTTP protocol, or if it should also be
				 * accessible to Javascript.
				 *
				 * @see   https://www.php.net/manual/en/function.setcookie.php
				 *
				 * @param bool $httponly If the cookie should only be accessible via the HTTP protocol.
				 * @param string $name Cookie name.
				 * @param string $value Cookie value.
				 * @param int $expire When the cookie should expire.
				 * @param bool $secure If the cookie should only be served over HTTPS.
				 */
				'httponly' => apply_filters( 'wpboutik_cookie_httponly', $httponly, $name, $value, $expire, $secure ),
			),
			$name,
			$value
		);

		setcookie( $name, $value, $options );
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		headers_sent( $file, $line );
		trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // @codingStandardsIgnoreLine
	}
}

/**
 * Checks to see whether or not a string starts with another.
 *
 * @param string $string The string we want to check.
 * @param string $starts_with The string we're looking for at the start of $string.
 * @param bool $case_sensitive Indicates whether the comparison should be case-sensitive.
 *
 * @return bool True if the $string starts with $starts_with, false otherwise.
 */
function wpb_starts_with( string $string, string $starts_with, bool $case_sensitive = true ): bool {
	$len = strlen( $starts_with );
	if ( $len > strlen( $string ) ) {
		return false;
	}

	$string = substr( $string, 0, $len );

	if ( $case_sensitive ) {
		return strcmp( $string, $starts_with ) === 0;
	}

	return strcasecmp( $string, $starts_with ) === 0;
}

/**
 * Initialize and load the cart functionality.
 *
 * @return void
 */
function wpb_load_cart() {
	if ( ! did_action( 'before_wpboutik_init' ) || doing_action( 'before_wpboutik_init' ) ) {
		/* translators: 1: wpb_load_cart 2: wpboutik_init */
		return;
	}

	// Ensure dependencies are loaded in all contexts.
	include_once WPBOUTIK_DIR . 'cart-functions.php';

	WPB()->initialize_session();
	WPB()->initialize_cart();
}

/**
 * Remove precision from an array of number and return an array of int.
 *
 * @param array $value Number to add precision to.
 *
 * @return int|array
 */
function wpb_remove_number_precision_deep( $value ) {
	if ( ! is_array( $value ) ) {
		return wpb_remove_number_precision( $value );
	}

	foreach ( $value as $key => $sub_value ) {
		$value[ $key ] = wpb_remove_number_precision_deep( $sub_value );
	}

	return $value;
}

/**
 * Remove precision from a number and return a float.
 *
 * @param float $value Number to add precision to.
 *
 * @return float
 */
function wpb_remove_number_precision( $value ) {
	if ( ! $value ) {
		return 0.0;
	}

	$cent_precision = pow( 10, wpb_get_price_decimals() );

	return $value / $cent_precision;
}

/**
 * Return the number of decimals after the decimal point.
 *
 * @return int
 */
function wpb_get_price_decimals() {
	return absint( apply_filters( 'wpb_get_price_decimals', get_option( 'wpboutik_price_num_decimals', 2 ) ) );
}

/**
 * Format decimal numbers ready for DB storage.
 *
 * Sanitize, optionally remove decimals, and optionally round + trim off zeros.
 *
 * This function does not remove thousands - this should be done before passing a value to the function.
 *
 * @param float|string $number Expects either a float or a string with a decimal separator only (no thousands).
 * @param mixed $dp number  Number of decimal points to use, blank to use woocommerce_price_num_decimals, or false to avoid all rounding.
 * @param bool $trim_zeros From end of string.
 *
 * @return string
 */
function wpb_format_decimal( $number, $dp = false, $trim_zeros = false ) {
	$number = $number ?? '';

	$locale   = localeconv();
	$decimals = array( wpb_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

	// Remove locale from string.
	if ( ! is_float( $number ) ) {
		$number = str_replace( $decimals, '.', $number );

		// Convert multiple dots to just one.
		$number = preg_replace( '/\.(?![^.]+$)|[^0-9.-]/', '', wpb_clean( $number ) );
	}

	if ( false !== $dp ) {
		$dp     = intval( '' === $dp ? wpb_get_price_decimals() : $dp );
		$number = number_format( floatval( $number ), $dp, '.', '' );
	} elseif ( is_float( $number ) ) {
		// DP is false - don't use number format, just return a string using whatever is given. Remove scientific notation using sprintf.
		$number = str_replace( $decimals, '.', sprintf( '%.' . wpb_get_rounding_precision() . 'f', $number ) );
		// We already had a float, so trailing zeros are not needed.
		$trim_zeros = true;
	}

	if ( $trim_zeros && strstr( $number, '.' ) ) {
		$number = rtrim( rtrim( $number, '0' ), '.' );
	}

	return $number;
}

/**
 * Get rounding precision for internal WC calculations.
 * Will return the value of wpb_get_price_decimals increased by 2 decimals, with 6 being the minimum.
 *
 * @return int
 */
function wpb_get_rounding_precision() {
	$precision = wpb_get_price_decimals() + 2;
	if ( $precision < absint( 6 ) ) {
		$precision = absint( 6 );
	}

	return $precision;
}

/**
 * Round a tax amount.
 *
 * @param double $value Amount to round.
 * @param int $precision DP to round. Defaults to wpb_get_price_decimals.
 *
 * @return float
 */
function wpb_round_tax_total( $value, $precision = null ) {
	$precision   = is_null( $precision ) ? wpb_get_price_decimals() : intval( $precision );
	$rounded_tax = round( $value, $precision ); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctionParameters.round_modeFound

	return apply_filters( 'wpb_round_tax_total', $rounded_tax, $value, $precision );
}

if ( ! function_exists( 'wpb_prices_include_tax' ) ) {

	/**
	 * Are prices inclusive of tax?
	 *
	 * @return bool
	 */
	function wpb_prices_include_tax() {
		$activate_tax = wpboutik_get_option_params( 'activate_tax' );

		return $activate_tax && apply_filters( 'wpboutik_prices_include_tax', false == 'yes' );
	}
}

add_action( 'wp', function () {
	if ( isset( $_REQUEST['api_key'] ) && isset( $_REQUEST['wp_user_id'] ) && isset( $_REQUEST['token'] ) ) {

		if ( isset( $_REQUEST['api_key'] ) && ! empty( $_REQUEST['api_key'] ) ) {
			$options = wpboutik_get_options();
			if ( $options['apikey'] !== $_REQUEST['api_key'] ) {
				return new \WP_Error( 'error-apikey', __( 'api_key not valid !', 'wpboutik' ), array( 'status' => 500 ) );
			}

			if ( isset( $_REQUEST['token'] ) && ! empty( $_REQUEST['token'] ) ) {
				$stored_secret_token = wpboutik_get_option_params( 'wpb_connect_secret_token' );

				if ( password_verify( $_REQUEST['token'], $stored_secret_token ) ) {
					$user_data = get_user_by( 'id', $_REQUEST['wp_user_id'] );

					// Set current user to this user's id
					wp_set_current_user( $user_data->ID, $user_data->user_login );

					// Same with cookie
					wp_set_auth_cookie( $user_data->ID );

					// The hook "wp_login"
					do_action( 'wp_login', $user_data->user_login, $user_data );

					wp_redirect( wpboutik_get_page_permalink( 'account' ) );
					wp_die();
				} else {
					return new \WP_Error( 'error-token', __( 'Invalid token!', 'wpboutik' ), array( 'status' => 500 ) );
				}
			}
		}
	}
} );

/**
 * display wpboutik fields in template
 */
function wpb_field( $field_name, $args = [] ) {
	if ( locate_template( 'wpboutik/fields/field-' . $field_name . '.php' ) != '' ) {
		// yep, load template from theme
		get_template_part( 'wpboutik/fields/field', $field_name, $args );
	} else {
		// nope, load the template from plugin
		include WPBOUTIK_DIR . 'templates/fields/' . $field_name . '.php';
	}
}

/**
 * load wpboutik html fields in template
 */
function get_wpb_field( $field_name, $args = [] ) {
	ob_start();
	if ( locate_template( 'wpboutik/fields/field-' . $field_name . '.php' ) != '' ) {
		// yep, load template from theme
		get_template_part( 'wpboutik/fields/field', $field_name, $args );
	} else {
		// nope, load the template from plugin
		include WPBOUTIK_DIR . 'templates/fields/' . $field_name . '.php';
	}

	return ob_get_clean();
}

/**
 * display wpboutik template_parts
 */
function wpb_template_parts( $template, $args = [] ) {
	if ( locate_template( 'wpboutik/parts/' . $template . '.php' ) != '' ) {
		// yep, load template from theme
		get_template_part( 'wpboutik/parts/' . $template, null, $args );
	} else {
		// nope, load the template from plugin
		include WPBOUTIK_DIR . 'templates/parts/' . $template . '.php';
	}
}

/**
 * get wpboutik html template_parts
 */
function get_wpb_template_parts( $template, $args = [] ) {
	ob_start();
	if ( locate_template( 'wpboutik/parts/' . $template . '.php' ) != '' ) {
		// yep, load template from theme
		get_template_part( 'wpboutik/parts/' . $template, null, $args );
	} else {
		// nope, load the template from plugin
		include WPBOUTIK_DIR . 'templates/parts/' . $template . '.php';
	}

	return ob_get_clean();
}

/**
 * get wpboutik html template_parts
 */
function wpb_form( $template, $vars = [] ) {
	foreach ( $vars as $var => $value ) {
		$$var = $value;
	}
	include WPBOUTIK_DIR . 'templates/forms/' . $template . '.php';
}