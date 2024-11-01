<?php

namespace NF\WPBOUTIK;

class Product {

	use Singleton;

	public function __construct() {
		add_action( 'init', array( __CLASS__, 'wpboutik_custom_post_type' ), 5 );

		/**
		 * Additional Information tab.
		 *
		 * @see wpboutik_display_product_attributes()
		 */
		add_action( 'wpboutik_product_additional_information', array(
			__CLASS__,
			'wpboutik_display_product_attributes'
		), 10 );


		add_filter( 'get_edit_post_link', array( __CLASS__, 'wpboutik_product_get_edit_post_link' ), 10, 3 );

		add_action( 'init', array( __CLASS__, 'wpboutik_register_experience_meta_fields' ) );
		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'wpboutik_disable_gutenberg' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'wpboutik_add_custom_box' ) );
		add_action( 'wp_head', array( __CLASS__, 'wpb_meta_tags' ) );
		add_filter( 'wpboutik_product_tabs', array( __CLASS__, 'wpboutik_default_product_tabs' ) );

		add_action( 'pre_get_posts', array( __CLASS__, 'modifier_requete_medias' ) );

		add_shortcode( 'wpboutik_dropdown_menu_cart', array( __CLASS__, 'wpboutik_show_dropdown_menu_cart' ) );
		add_action( 'wp_ajax_wpboutik_get_cart_fragments', array( __CLASS__, 'get_cart_fragments' ) );
		add_action( 'wp_ajax_nopriv_wpboutik_get_cart_fragments', array( __CLASS__, 'get_cart_fragments' ) );

		add_filter( 'wpboutik_query_archive', array( __CLASS__, 'wpboutik_query_archive' ) );

		add_filter( 'cron_schedules', array( __CLASS__, 'wpb_add_cron_schedules' ) );
		add_action( 'wp', array( __CLASS__, 'planifier_enregistrement_paniers_abandonnes' ) );
		//add_action( 'init', array( __CLASS__, 'wpb_saveAbandonedCart' ) );
		add_action( 'wpboutik_save_cart_abandonned_hook', array( __CLASS__, 'wpb_saveAbandonedCart' ) );
		add_action( 'wpboutik_cleanup_sessions', array( __CLASS__, 'wpb_cleanup_session_data' ) );

		//add_action( 'pre_get_posts', array( __CLASS__, 'wpb_archive_pre_get_posts' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'wpb_taxonomy_pre_get_posts' ) );

		add_action( 'wp_trash_post', array( __CLASS__, 'disable_trash_for_custom_post_type' ), 10, 1 );
	}

	public static function wpb_archive_pre_get_posts( $query ) {
		if ( ! is_admin() && $query->is_main_query() && $query->is_post_type_archive( 'wpboutik_product' ) ) {

			$paged   = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			$orderby = ( get_query_var( 'orderby' ) ) ? get_query_var( 'orderby' ) : '';

			$ordersql = [
				'meta_value_num' => 'DESC',
				'date'           => 'DESC', // Primary sort: by post date
				'title'          => 'ASC'   // Secondary, fallback sort: by post title
			];
			$meta_key = 'mis_en_avant';

			if ( $orderby ) {
				switch ( $orderby ) {
					case 'menu_order':
						$ordersql = [ $orderby, 'ASC' ];
						break;
					case 'popularity':
					case 'rating':
					case 'price':
						$ordersql = [ 'meta_value_num' => 'ASC', 'title' => 'ASC' ];
						$meta_key = $orderby;
						break;
					case 'price-desc':
						$ordersql = [ 'meta_value_num' => 'DESC', 'title' => 'ASC' ];
						$meta_key = 'price';
						break;
					case 'date':
						$ordersql = [ 'date', 'desc' ];
						break;
				}
			}

			$query = apply_filters( 'wpboutik_query_archive', $query );

			$meta_query = apply_filters( 'wpboutik_meta_query_archive', array(
				array(
					'key'     => 'price',
					'compare' => 'EXISTS',
				)
			) );

			$query->set( 'meta_query', $meta_query );
			$query->set( 'orderby', $ordersql );
			$query->set( 'meta_key', $meta_key );
			//$query->set( 'paged', $paged );
		}
	}

	public static function wpb_taxonomy_pre_get_posts( $query ) {
		if ( ! is_admin() && $query->is_main_query() && $query->is_tax( 'wpboutik_product_cat' ) ) {

			//$paged   = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			$orderby = ( get_query_var( 'orderby' ) ) ? get_query_var( 'orderby' ) : '';

			$ordersql = [
				'meta_value_num' => 'DESC',
				'date'           => 'DESC', // Primary sort: by post date
				'title'          => 'ASC'   // Secondary, fallback sort: by post title
			];
			$meta_key = 'mis_en_avant';

			if ( $orderby ) {
				switch ( $orderby ) {
					case 'menu_order':
						$ordersql = [ $orderby, 'ASC' ];
						break;
					case 'popularity':
					case 'rating':
					case 'price':
						$ordersql = [ 'meta_value_num' => 'ASC', 'title' => 'ASC' ];
						$meta_key = $orderby;
						break;
					case 'price-desc':
						$ordersql = [ 'meta_value_num' => 'DESC', 'title' => 'ASC' ];
						$meta_key = 'price';
						break;
					case 'date':
						$ordersql = [ 'date', 'desc' ];
						break;
				}
			}

			$meta_query = apply_filters( 'wpboutik_meta_query_archive', array(
				array(
					'key'     => 'price',
					'compare' => 'EXISTS',
				)
			) );

			$query->set( 'meta_query', $meta_query );
			$query->set( 'orderby', $ordersql );
			$query->set( 'meta_key', $meta_key );
			//$query->set( 'paged', $paged );

			if ( get_theme_mod( 'wpboutik_show_out_stock_product', 'yes' ) == 'no' ) {
				$args = array(
					'post_type'      => 'wpboutik_product', // Change to your desired post type if not post
					'posts_per_page' => - 1, // Number of posts to retrieve (-1 to retrieve all)
					'meta_query'     => array(
						array(
							'key'   => 'gestion_stock',
							'value' => 1
						),
						array(
							'key'     => 'continu_rupture',
							'value'   => 1,
							'compare' => '!='
						),
					),
					'fields'         => 'ids',
				);

				$post_ids = get_posts( $args );

				$post__not_in = [];

				if ( $post_ids ) {
					foreach ( $post_ids as $post_id ) {
						$variants = get_post_meta( $post_id, 'variants', true );
						if ( ! empty( $variants ) && '[]' != $variants ) {
							$empty__all_variants = true;
							foreach ( json_decode( $variants ) as $variation ) {
								if ( (int) $variation->quantity !== 0 ) {
									$empty__all_variants = false;
									break;
								}
							}
							if ( $empty__all_variants ) {
								$post__not_in[] = $post_id;
							}
						} else {
							$qty = get_post_meta( $post_id, 'qty', true );
							if ( $qty < 1 ) {
								$post__not_in[] = $post_id;
							}
						}
					}
					wp_reset_postdata(); // Restore global post data
				}

				$query->set( 'post__not_in', $post__not_in );
			}
		}
	}

	public static function wpb_add_cron_schedules() {
		$schedules['every_ten_minutes'] = array(
			'interval' => 600,
			'display'  => __( 'Every 10 Minutes' ),
		);

		return $schedules;
	}

	public static function planifier_enregistrement_paniers_abandonnes() {
		if ( ! wp_next_scheduled( 'wpboutik_save_cart_abandonned_hook' ) ) {
			wp_schedule_event( time(), 'every_ten_minutes', 'wpboutik_save_cart_abandonned_hook' );
		}

		if ( ! wp_next_scheduled( 'wpboutik_cleanup_sessions' ) ) {
			wp_schedule_event( time() + ( 6 * HOUR_IN_SECONDS ), 'twicedaily', 'wpboutik_cleanup_sessions' );
		}
	}

	public static function wpb_saveAbandonedCart() {
		global $wpdb;
		$must_be_saved = false;
		$table         = $wpdb->prefix . 'wpboutik_sessions';
		$sessions      = $wpdb->get_results( "SELECT * FROM `$table`;" );
		foreach ( $sessions as $session ) {
			if ( is_numeric( $session->session_key ) ) {
				$cart_abandoned = get_user_meta( $session->session_key, 'wpboutik_cart_abandoned', true );
			} else {
				$cart_abandoned = get_option( 'wpboutik_cart_abandoned_' . $session->session_id, false );
			}
			$lastModified = strtotime( $session->last_updated );
			if ( ! $cart_abandoned ) {
				$currentTime    = strtotime( 'now' );
				$timeDifference = $currentTime - $lastModified;
				// Définir le temps après lequel le panier est considéré comme abandonné (en secondes)
				$cut_off_abandonned_cart = wpboutik_get_option_params( 'cut_off_abandonned_cart' );
				$abandonedTimeThreshold  = ( ! empty( $cut_off_abandonned_cart ) )
					? $cut_off_abandonned_cart * 60 // conversion en secondes 
					: 1200; // 20 minutes
				//$abandonedTimeThreshold = 120; // for debug

				if ( $timeDifference > $abandonedTimeThreshold ) {
					$must_be_saved = true;
				}
			} else {
				$cart_abandoned_last_save = get_option( 'wpboutik_cart_abandoned_save_app_' . $session->session_id, false );
				if ( $cart_abandoned_last_save && $cart_abandoned_last_save < $lastModified ) {
					$must_be_saved = true;
				}
			}

			if ( $must_be_saved ) {
				$app_id = get_option( 'wpboutik_cart_abandoned_app_' . $session->session_id, null );

				$value = unserialize( $session->session_value );
				foreach ( $value as $key => $content ) {
					if ( $key == 'data_user' ) {
						$string = urldecode( $content );
						parse_str( $string, $new_content );
						$value[ $key ] = $new_content;
						continue;
					}
					$unserialized  = unserialize( $content );
					$value[ $key ] = ( $unserialized !== false ) ? $unserialized : $content;
				}

				if ( empty( $value['cart'] ) || $value['cart'] === '[]' ) {
					continue;
				}

				if ( is_numeric( $session->session_key ) ) {
					$user_id           = $session->session_key;
					$current_user_data = get_userdata( $session->session_key );
					$user_email        = $current_user_data->user_email;
					$current_user_data = [];
				} else if ( ! empty( $value['data_user'] ) ) {
					$user_id           = null;
					$user_email        = $value['data_user']['email_address'];
					$current_user_data = $value['data_user'];
				} else {
					$user_id           = null;
					$user_email        = null;
					$current_user_data = [];
				}

				$api_request  = WPB_Api_Request::request( 'abandonned_cart', 'save' )
				                               ->add_multiple_to_body( [
					                               'app_id'       => $app_id,
					                               'options'      => get_option( 'wpboutik_options' ),
					                               'wp_user_id'   => $user_id,
					                               'user_email'   => $user_email,
					                               'cart_data'    => $value['cart'],
					                               'user_data'    => $current_user_data,
					                               'checkout_url' => wpboutik_get_page_permalink( 'checkout' )
				                               ] )->exec();
				$api_response = json_decode( $api_request->get_response_body() );
				// Marquer le panier comme abandonné une fois qu'il est enregistré
				if ( is_numeric( $session->session_key ) ) {
					update_user_meta( $session->session_key, 'wpboutik_cart_abandoned', true );
					update_user_meta( $session->session_key, 'wpboutik_cart_abandoned_app', $api_response->abandonned_cart_id );
					update_user_meta( $session->session_key, 'wpboutik_cart_abandoned_save_app', strtotime( 'now' ) );
				} else {
					update_option( 'wpboutik_cart_abandoned_' . $session->session_id, true );
					update_option( 'wpboutik_cart_abandoned_app_' . $session->session_id, $api_response->abandonned_cart_id );
					update_option( 'wpboutik_cart_abandoned_save_app_' . $session->session_id, strtotime( 'now' ) );
				}
			}
		}
		$wpdb->flush();

		// Aucun panier abandonné trouvé
		return false;
	}

	/**
	 * Cleans up session data - cron callback.
	 *
	 */
	public static function wpb_cleanup_session_data() {
		$session_class = apply_filters( 'wpboutik_session_handler', '\NF\WPBOUTIK\WPB_Session_Handler' );
		$session       = new $session_class();

		if ( is_callable( array( $session, 'cleanup_sessions' ) ) ) {
			$session->cleanup_sessions();
		}
	}

	// Register Custom Post Type
	public static function wpboutik_custom_post_type() {

		$permalinks = wpboutik_get_permalink_structure();

		register_taxonomy(
			'wpboutik_product_cat',
			apply_filters( 'wpboutik_taxonomy_objects_product_cat', array( 'wpboutik_product' ) ),
			apply_filters(
				'wpboutik_taxonomy_args_product_cat',
				array(
					'hierarchical'      => true,
					'label'             => __( 'Categories', 'wpboutik' ),
					'labels'            => array(
						'name'              => __( 'Product categories WPB', 'wpboutik' ),
						'singular_name'     => __( 'Category', 'wpboutik' ),
						'menu_name'         => _x( 'Categories', 'Admin menu name', 'wpboutik' ),
						'search_items'      => __( 'Search categories', 'wpboutik' ),
						'all_items'         => __( 'All categories', 'wpboutik' ),
						'parent_item'       => __( 'Parent category', 'wpboutik' ),
						'parent_item_colon' => __( 'Parent category:', 'wpboutik' ),
						'edit_item'         => __( 'Edit category', 'wpboutik' ),
						'update_item'       => __( 'Update category', 'wpboutik' ),
						'add_new_item'      => __( 'Add new category', 'wpboutik' ),
						'new_item_name'     => __( 'New category name', 'wpboutik' ),
						'not_found'         => __( 'No categories found', 'wpboutik' ),
					),
					'show_ui'           => false,
					'show_in_nav_menus' => true,
					'query_var'         => true,
					'rewrite'           => array(
						'slug'         => $permalinks['category_rewrite_slug'],
						'with_front'   => false,
						'hierarchical' => true,
					),
				)
			)
		);

		register_taxonomy(
			'wpboutik_product_tag',
			apply_filters( 'wpboutik_taxonomy_objects_product_tag', array( 'wpboutik_product' ) ),
			apply_filters(
				'wpboutik_taxonomy_args_product_tag',
				array(
					'hierarchical' => false,
					'label'        => __( 'Product tags', 'wpboutik' ),
					'labels'       => array(
						'name'                       => __( 'Product tags', 'wpboutik' ),
						'singular_name'              => __( 'Tag', 'wpboutik' ),
						'menu_name'                  => _x( 'Tags', 'Admin menu name', 'wpboutik' ),
						'search_items'               => __( 'Search tags', 'wpboutik' ),
						'all_items'                  => __( 'All tags', 'wpboutik' ),
						'edit_item'                  => __( 'Edit tag', 'wpboutik' ),
						'update_item'                => __( 'Update tag', 'wpboutik' ),
						'add_new_item'               => __( 'Add new tag', 'wpboutik' ),
						'new_item_name'              => __( 'New tag name', 'wpboutik' ),
						'popular_items'              => __( 'Popular tags', 'wpboutik' ),
						'separate_items_with_commas' => __( 'Separate tags with commas', 'wpboutik' ),
						'add_or_remove_items'        => __( 'Add or remove tags', 'wpboutik' ),
						'choose_from_most_used'      => __( 'Choose from the most used tags', 'wpboutik' ),
						'not_found'                  => __( 'No tags found', 'wpboutik' ),
					),
					'show_ui'      => false,
					'query_var'    => true,
					'rewrite'      => array(
						'slug'       => $permalinks['tag_rewrite_slug'],
						'with_front' => false,
					),
				)
			)
		);

		$labels = array(
			'name'                  => _x( 'Produits', 'Post Type General Name', 'text_domain' ),
			'singular_name'         => _x( 'Produit', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'             => __( 'Produits', 'text_domain' ),
			'name_admin_bar'        => __( 'Post Type', 'text_domain' ),
			'archives'              => __( 'Item Archives', 'text_domain' ),
			'attributes'            => __( 'Item Attributes', 'text_domain' ),
			'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
			'all_items'             => __( 'Tous les produits', 'text_domain' ),
			'add_new_item'          => __( 'Add New Item', 'text_domain' ),
			'add_new'               => __( 'Add New', 'text_domain' ),
			'new_item'              => __( 'New Item', 'text_domain' ),
			'edit_item'             => __( 'Edit product', 'wpboutik' ),
			'update_item'           => __( 'Update Item', 'text_domain' ),
			'view_item'             => __( 'View Item', 'text_domain' ),
			'view_items'            => __( 'View Items', 'text_domain' ),
			'search_items'          => __( 'Search Item', 'text_domain' ),
			'not_found'             => __( 'Not found', 'text_domain' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
			'featured_image'        => __( 'Featured Image', 'text_domain' ),
			'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
			'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
			'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
			'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
			'items_list'            => __( 'Items list', 'text_domain' ),
			'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
			'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
		);
		$args   = array(
			'label'                 => __( 'Post Type', 'text_domain' ),
			'description'           => __( 'Post Type Description', 'text_domain' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields' ),
			'hierarchical'          => false,
			'rewrite'               => $permalinks['product_rewrite_slug'] ? array(
				'slug'       => $permalinks['product_rewrite_slug'],
				'with_front' => false,
				'feeds'      => true,
			) : false,
			'map_meta_cap'          => true,
			/*'capabilities' => array(
				'create_posts' => 'do_not_allow'
			),*/
			'public'                => true,
			'show_ui'               => true,
			'query_var'             => true,
			'menu_position'         => 5,
			'show_in_menu'          => false,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			/*'show_in_menu'          => true,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,*/
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'show_in_rest'          => true,
			'rest_controller_class' => 'WPBoutik_Endpoint',
			'capability_type'       => 'post',
		);
		register_post_type( 'wpboutik_product', $args );

	}

	/**
	 * Outputs a list of product attributes for a product.
	 */
	public static function wpboutik_display_product_attributes( $product ) {
		$product_attributes = array();

		$weight      = get_post_meta( $product->ID, 'weight', true );
		$custom_data = json_decode( get_post_meta( $product->ID, 'custom_data', true ) );

		// Display weight and dimensions before attribute list.
		$display_dimensions = apply_filters( 'wpb_product_enable_dimensions_display', $weight || wpboutik_product_has_dimensions( $product ) );

		if ( $display_dimensions && $weight ) {
			$product_attributes['weight'] = array(
				'label' => __( 'Weight', 'wpboutik' ),
				'value' => wpboutik_format_weight( $weight, $product ),
			);
		}

		if ( $display_dimensions && wpboutik_product_has_dimensions( $product ) ) {
			$product_attributes['dimensions'] = array(
				'label' => __( 'Dimensions', 'wpboutik' ),
				'value' => wpboutik_format_dimensions( wpboutik_product_get_dimensions( $product ) ),
			);
		}

		$sku = get_post_meta( get_the_ID(), 'sku', true );
		if ( $sku ) {
			$product_attributes['sku'] = array(
				'label' => __( 'SKU', 'wpboutik' ),
				'value' => $sku,
			);
		}
		if ( ! empty( $custom_data ) ) {
			foreach ( $custom_data as $data ) {
				$product_attributes[ $data->title ] = array(
					'label' => $data->title,
					'value' => ( $data->type == 'image' ) ? '<img style="max-width: 100px; max-height: 75px; object-fit: contain;" src="' . $data->value . '" />' : $data->value,
				);
			}
		}
		$type    = get_post_meta( $product->ID, 'type', true );
		$options = get_post_meta( $product->ID, 'options', true );
		if ( $options ) {
			$options = json_decode( $options );
			foreach ( $options as $option ) {
				$values = array();
				foreach ( $option->values as $option_value ) {
					$value_name = esc_html( $option_value->name );
					$values[]   = $value_name;
				}

				$product_attributes[ 'attribute_' . sanitize_title_with_dashes( $option->name ) ] = array(
					'label' => $option->name,
					'type' => $option->type,
					'value' => apply_filters( 'wpboutik_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $option, $values ),
				);
			}
		}

		/**
		 * Hook: wpboutik_display_product_attributes.
		 */
		$product_attributes = apply_filters( 'wpboutik_display_product_attributes', $product_attributes, $product );

		extract(
			array(
				'product_attributes' => $product_attributes,
				// Legacy params.
				'product'            => $product,
				'attributes'         => $options,
				'display_dimensions' => $display_dimensions,
			)
		);

		include trailingslashit( WPBOUTIK_TEMPLATES ) . '/single/product-attributes.php';
	}

	public static function wpboutik_product_get_edit_post_link( $link, $post_id, $context ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return;
		}

		if ( 'wpboutik_product' === $post->post_type ) {
			return;
		}

		return $link;
	}

	public static function wpboutik_register_experience_meta_fields() {
		register_meta( 'post', 'wpboutik_post_id', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'integer',
			'description'    => 'wpboutik_post_id',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'price', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'number',
			'description'    => 'price',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'price_before_reduction', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'number',
			'description'    => 'price before reduction',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'first_image', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'description'    => 'first_image',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'images', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'description'    => 'images',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'galerie_images', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'description'    => 'galerie image',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'sku', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'description'    => 'sku',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'meta_description', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'description'    => 'meta_description',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'gestion_stock', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'boolean',
			'description'    => 'Gestion stock',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'mis_en_avant', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'boolean',
			'description'    => 'Mis en avant',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'continu_rupture', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'boolean',
			'description'    => 'Vendre même si en rupture',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'qty', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'integer',
			'description'    => 'Quantité',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'files', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'description'    => 'files',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'type', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'description'    => 'Type ?',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'tax', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'description'    => 'Tax ?',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'weight', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'number',
			'description'    => 'Poids',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'weight_unit', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'description'    => 'Unité de poids',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'length', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'number',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'height', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'number',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'width', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'number',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'options', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'variants', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'single'         => true,
			'show_in_rest'   => true
		) );
		
		register_meta( 'post', 'licenses', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'selling_fees', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'number',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'recursive', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'boolean',
			'single'         => true,
			'show_in_rest'   => true
		) );
		register_meta( 'post', 'recursive_number', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'number',
			'single'         => true,
			'show_in_rest'   => true
		) );
		register_meta( 'post', 'recursive_type', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'id_products_upsell', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'single'         => true,
			'show_in_rest'   => true
		) );

		register_meta( 'post', 'custom_data', array(
			'object_subtype' => 'wpboutik_product',
			'type'           => 'string',
			'single'         => true,
			'show_in_rest'   => true
		) );
	}

	public static function wpboutik_disable_gutenberg( $current_status, $post_type ) {
		// Use your post type key instead of 'product'
		if ( $post_type === 'wpboutik_product' ) {
			return false;
		}

		return $current_status;
	}

	public static function wpboutik_add_custom_box() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		add_meta_box(
			'wpboutik_product_box_id',                 // Unique ID
			'Données du produit provenant de WPBoutik',      // Box title
			array( __CLASS__, 'wpboutik_custom_box_html' ),  // Content callback, must be of type callable
			'wpboutik_product'                            // Post type
		);

		if ( 'comment' === $screen_id && isset( $_GET['c'] ) && metadata_exists( 'comment', sanitize_text_field( wp_unslash( $_GET['c'] ) ), 'rating' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			add_meta_box( 'wpboutik-rating', __( 'Rating', 'wpboutik' ), '\NF\WPBOUTIK\Ratings::output', 'comment', 'normal', 'high' );
		}
	}

	public static function wpboutik_custom_box_html( $post ) {
		?>
        <div>
            <p>
                <label for="price">Description</label>
                <textarea disabled name="description"><?php echo esc_attr( $post->post_content ); ?></textarea>
            </p>
            <p>
                <label for="price">Price</label>
                <input type="text" disabled name="price" value="<?php echo esc_attr( $post->price ); ?>">
            </p>
            <p>
                <label for="price">Price before reduction</label>
                <input type="text" disabled name="price_before_reduction"
                       value="<?php echo esc_attr( $post->price_before_reduction ); ?>">
            </p>
            <p>
                Images
				<?php echo esc_html( $post->images ); ?>
            </p>
            <p>
            <ul class="product_images">
				<?php
				$product_image_gallery = $post->galerie_images;

				$attachments = explode( ',', $product_image_gallery );

				if ( ! empty( $attachments ) ) {
					foreach ( $attachments as $attachment_id ) {
						$attachment = wp_get_attachment_image( $attachment_id, 'thumbnail' );

						// if attachment is empty skip.
						if ( empty( $attachment ) ) {
							continue;
						}
						?>
                        <li class="image" data-attachment_id="<?php echo esc_attr( $attachment_id ); ?>">
							<?php echo $attachment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </li>
						<?php
					}
				} ?>
            </ul>
            </p>
            <p>
                <label for="sku">SKU</label>
                <input type="text" disabled name="price" value="<?php echo esc_attr( $post->sku ); ?>">
            </p>
            <p>
                <label for="gestion_stock">Gestion du stock</label>
                <input type="text" disabled name="gestion_stock"
                       value="<?php echo esc_attr( $post->gestion_stock ); ?>">
            </p>
            <p>
                <label for="continu_rupture">Continu rupture</label>
                <input type="text" disabled name="continu_rupture"
                       value="<?php echo esc_attr( $post->continu_rupture ); ?>">
            </p>
            <p>
                <label for="qty">Quantité</label>
                <input type="text" disabled name="qty" value="<?php echo esc_attr( $post->qty ); ?>">
            </p>
            <p>
                <label for="type">Type ?</label>
                <input type="text" disabled name="type"
                       value="<?php echo esc_attr( $post->type ); ?>">
            </p>
            <p>
                <label for="sku">Poids</label>
                <input type="text" disabled name="weight" value="<?php echo esc_attr( $post->weight ); ?>">
            </p>
            <p>
                <label for="sku">Unité de poids</label>
                <input type="text" disabled name="weight_unit" value="<?php echo esc_attr( $post->weight_unit ); ?>">
            </p>
        </div>
		<?php
	}

	public static function wpb_meta_tags() {
		if ( is_wpboutik_product() ) {
			global $post;
			$meta_title       = get_post_meta( $post->ID, 'meta_title', true );
			$meta_description = ( $post->post_excerpt ) ? $post->post_excerpt : get_post_meta( $post->ID, 'meta_description', true );

			if ( empty( $meta_title ) ) {
				$meta_title = get_the_title( $post->ID ) . ' - ' . get_bloginfo( 'name' );
			}

			if ( ! empty( $meta_title ) ) {
				echo '<meta name="title" content="' . esc_attr( $meta_title ) . '">';
				echo '<title>' . esc_html( $meta_title ) . '</title>';
			}

			if ( ! empty( $meta_description ) ) {
				echo '<meta name="description" content="' . esc_attr( $meta_description ) . '">';
			}
		}

		if ( is_wpboutik_product_taxonomy() ) {
			$category         = get_queried_object();
			$meta_title       = $category->name . ' - ' . get_bloginfo( 'name' );
			$meta_description = $category->description;

			if ( ! empty( $meta_title ) ) {
				echo '<meta name="title" content="' . esc_attr( $meta_title ) . '">';
				echo '<title>' . esc_html( $meta_title ) . '</title>';
			}

			if ( ! empty( $meta_description ) ) {
				echo '<meta name="description" content="' . esc_attr( $meta_description ) . '">';
			}
		}
	}

	/**
	 * Add default product tabs to product pages.
	 *
	 * @param array $tabs Array of tabs.
	 *
	 * @return array
	 */
	public static function wpboutik_default_product_tabs( $tabs = array() ) {
		global $product, $post;

		// Description tab - shows product content.
		if ( $post->post_content ) {
			$tabs['description'] = array(
				'title'    => __( 'Description', 'wpboutik' ),
				'priority' => 10,
				'callback' => array( __CLASS__, 'wpboutik_product_description_template' ),
			);
		}

		// Additional information tab - shows attributes.
		if ( get_post_meta( $post->ID, 'type', true ) != 'gift_card' ) {

			$options     = get_post_meta( $post->ID, 'options', true );
			$custom_data = get_post_meta( $post->ID, 'custom_data', true );
			$custom_data = json_decode( $custom_data );
			$weight      = get_post_meta( $post->ID, 'weight', true );
			$sku         = get_post_meta( $post->ID, 'sku', true );

			if ( ( ! empty( $options ) && '[]' != $options ) || ! empty( $custom_data ) || $sku || apply_filters( 'wpboutik_product_enable_dimensions_display', $weight || wpboutik_product_has_dimensions( $post ) ) ) {
				$tabs['additional_information'] = array(
					'title'    => __( 'Additional information', 'wpboutik' ),
					'priority' => 20,
					'callback' => array( __CLASS__, 'wpboutik_product_additional_information_template' ),
				);
			}

			// Reviews tab - shows comments.
			if ( comments_open() ) {
				$tabs['reviews'] = array(
					/* translators: %s: reviews count */
					'title'    => __( 'Reviews', 'wpboutik' ),
					'count'    => $post->comment_count,
					'priority' => 30,
					'callback' => 'comments_template',
				);
			}
		}

		return $tabs;
	}

	public static function wpboutik_product_description_template() {
		extract(
			array(
				'heading' => apply_filters( 'wpboutik_product_description_heading', __( 'Description', 'wpboutik' ) )
			)
		);

		include trailingslashit( WPBOUTIK_TEMPLATES ) . '/single/product-description.php';
	}

	public static function wpboutik_product_additional_information_template() {
		extract(
			array(
				'heading' => apply_filters( 'wpboutik_product_additional_information_heading', __( 'Additional information', 'wpboutik' ) )
			)
		);

		include trailingslashit( WPBOUTIK_TEMPLATES ) . '/single/product-additional-information.php';
	}

	public static function modifier_requete_medias( $query ) {
		if ( is_admin() && $query->get( 'post_type' ) == 'attachment' ) {
			$query->set( 'post_parent__not_in', get_posts( [ 'post_type' => 'wpboutik_product', 'fields' => 'ids' ] ) );
			$query->set( 'meta_query', array(
				array(
					'key'     => 'wpb_invoice',
					'compare' => 'NOT EXISTS',
				)
			) );
		}
	}

	public static function wpboutik_show_dropdown_menu_cart() {
		ob_start(); // Commence à mettre en tampon la sortie

		$backgroundcolor              = wpboutik_get_backgroundcolor_button();
		$hovercolor                   = wpboutik_get_hovercolor_button();
		$title_product_color          = wpboutik_get_title_product_color();
		$title_product_color_on_hover = wpboutik_get_title_product_color_on_hover();
		$currency_symbol              = get_wpboutik_currency_symbol();
		?>
        <a href="#" class="WPBpanierBtn focus:outline-none focus:shadow-outline">
			<?php
			if ( get_theme_mod( 'wpboutik_show_cart_icon' ) ) {
				$dashicon                = get_theme_mod( 'wpboutik_choose_cart_icon' );
				$wpboutik_size_cart_icon = get_theme_mod( 'wpboutik_size_cart_icon', 20 );
				if ( ! empty( $wpboutik_size_cart_icon ) && '20' != $wpboutik_size_cart_icon ) {
					$dashicon = '<span style="font-size:' . $wpboutik_size_cart_icon . 'px">' . $dashicon . '</span>';
					$dashicon .= '<span class="sr-only">'.__( 'Cart', 'wpboutik' ).'</span>';
				}


			} else {
				$dashicon = __( 'Cart', 'wpboutik' );
			}
			echo $dashicon;
			echo do_shortcode( '[wpboutik_cartcount]' );
			?>
        </a>

		<?php include trailingslashit( WPBOUTIK_TEMPLATES ) . '/cart/mini-cart.php';

		return ob_get_clean(); // Récupère le tampon de sortie
	}

	public static function get_cart_fragments() {
		ob_start();

		include trailingslashit( WPBOUTIK_TEMPLATES ) . '/cart/mini-cart.php';

		$output = ob_get_clean();
		wp_send_json_success( array( 'fragments' => array( 'panierDropdown' => $output ) ) );
	}

	public static function wpboutik_query_archive( $query_array ) {
		if ( get_theme_mod( 'wpboutik_show_out_stock_product', 'yes' ) != 'no' ) {
			return $query_array;
		}

		$args = array(
			'post_type'      => 'wpboutik_product', // Change to your desired post type if not post
			'posts_per_page' => - 1, // Number of posts to retrieve (-1 to retrieve all)
			'meta_query'     => array(
				array(
					'key'   => 'gestion_stock',
					'value' => 1
				),
				array(
					'key'     => 'continu_rupture',
					'value'   => 1,
					'compare' => '!='
				),
			),
			'fields'         => 'ids',
		);

		$post_ids = get_posts( $args );

		$post__not_in = [];

		if ( $post_ids ) {
			foreach ( $post_ids as $post_id ) {
				$variants = get_post_meta( $post_id, 'variants', true );
				if ( ! empty( $variants ) && '[]' != $variants ) {
					$empty__all_variants = true;
					foreach ( json_decode( $variants ) as $variation ) {
						if ( (int) $variation->quantity !== 0 ) {
							$empty__all_variants = false;
							break;
						}
					}
					if ( $empty__all_variants ) {
						$post__not_in[] = $post_id;
					}
				} else {
					$qty = get_post_meta( $post_id, 'qty', true );
					if ( $qty < 1 ) {
						$post__not_in[] = $post_id;
					}
				}
			}
			wp_reset_postdata(); // Restore global post data
		}

		$query_array['post__not_in'] = $post__not_in;

		return $query_array;
	}

	// Disable moving the post to trash
	public static function disable_trash_for_custom_post_type( $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( $post_type === 'wpboutik_product' ) {
			wp_delete_post( $post_id, true ); // Force delete the post
		}
	}
}
