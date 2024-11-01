<?php

namespace NF\WPBOUTIK;

class Plugin {

	use Singleton;

	/**
	 * @var Option_Service_WPBOUTIK
	 */
	private $option_services;
	/**
	 * @var User_Api_Service_WPBOUTIK
	 */
	private $user_api_services;
	/**
	 * @var array
	 */
	private $options;
	/**
	 * @var array|array[]
	 */
	private $tabs;
	/**
	 * @var string
	 */
	private $tab_active;
	/**
	 * @var bool
	 */
	private $subscription;

	/**
	 * Cart instance.
	 *
	 * @var WPB_Cart
	 */
	public $cart = null;

	/**
	 * Session instance.
	 *
	 * @var WPB_Session|WPB_Session_Handler
	 */
	public $session = null;

	/**
	 * Customer instance.
	 *
	 * @var WPB_Customer
	 */
	public $customer = null;

	protected function init() {
		add_action( 'init', array( __CLASS__, 'wpboutik_flush_rewrite_rules_maybe' ), 99 );
		add_filter( 'script_loader_tag', array( __CLASS__, 'wpb_add_data_namespace_for_script_paypal' ), 10, 3 );
		add_filter( 'wpboutik_tax_rates', array( __CLASS__, 'wpboutik_tax_rates_project' ) );
		add_shortcode( 'wpboutik_cartcount', array( __CLASS__, 'wpboutik_cart_count_shortcode' ) );
		add_shortcode( 'wpb_youtube', array( __CLASS__, 'wpboutik_youtube_iframe' ) );
		add_shortcode( 'wpb_last_products', array( __CLASS__, 'wpboutik_last_products' ) );
		add_shortcode( 'wpb_featured_products', array( __CLASS__, 'wpboutik_featured_products' ) );
		add_filter( 'wp_nav_menu_items', array( __CLASS__, 'wpboutik_product_search' ), 10, 2 );
		add_filter( 'walker_nav_menu_start_el', array( __CLASS__, 'wpboutik_start_el' ), 20, 2 );
		add_filter( 'comment_form_submit_field', array( __CLASS__, 'submit_comment' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
		$api_key = \wpboutik_get_api_key();
		add_action( 'admin_post_wpboutik_save_settings', array( __CLASS__, 'wpboutik_save_settings' ) );
		add_action( 'admin_post_wpboutik_disconnect_project', array( __CLASS__, 'wpboutik_disconnect_project' ) );
		add_action( 'admin_post_wpboutik_save_settings_analytics', array(
			__CLASS__,
			'wpboutik_save_settings_analytics'
		) );
		if ( empty( $api_key ) && ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'wpboutik-settings' ) === false ) ) { // phpcs:ignore
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 1 );
		}
		add_action( 'admin_head', array( $this, 'custom_admin_style' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), PHP_INT_MAX );
		add_action( 'show_admin_bar', array( $this, 'show_admin_bar' ), PHP_INT_MAX );
		add_action( 'admin_init', array( $this, 'redirect_customer_wpb' ), PHP_INT_MAX );
		add_action( 'wp_loaded', array( __CLASS__, 'process_login' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_lost_password' ), 20 );
		add_action( 'wp_loaded', array( __CLASS__, 'process_reset_password' ), 20 );
		add_action( 'init', array( __CLASS__, 'redirect_to_shop_page' ) );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wpboutik_zoom_single_img' ), 1 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wpboutik_import_scripts_and_styles' ), 9999 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wpboutik_include_stripe_js' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wpboutik_ajax_add_to_cart_js' ) );
		add_filter( 'block_categories_all', array( __CLASS__, 'add_custom_block_category' ), 10, 2 );

		/**
		 * Footer.
		 *
		 * @see  wpb_print_js()
		 */
		add_action( 'wp_footer', 'wpb_print_js', 25 );
		add_action( 'wp_footer', array( __CLASS__, 'wpboutik_microdata' ), 25 );


		// avif format word wordpress < 6.5
		add_filter( 'upload_mimes', [ __CLASS__, 'filter_allowed_mimes_for_avif' ], 1000, 1 );
		add_filter( 'getimagesize_mimes_to_exts', [ __CLASS__, 'filter_mime_to_exts' ], 1000, 1 );
		add_filter( 'mime_types', [ __CLASS__, 'filter_mime_types' ], 1000, 1 );
		add_filter( 'file_is_displayable_image', [ __CLASS__, 'fix_avif_displayable' ], 1000, 2 );
		add_filter( 'image_sideload_extensions', [ __CLASS__, 'image_sideload_extensions_filter' ], 10, 2 );
		add_filter( 'body_class', [ __CLASS__, 'body_class' ] );
		add_filter( 'display_post_states', [ __CLASS__, 'display_post_states' ], 10, 2 );

		add_action( 'after_setup_theme', [ __CLASS__, 'theme_setup' ] );

		$this->option_services   = wpboutik_get_service( 'Option_Service_WPBOUTIK' );
		$this->user_api_services = wpboutik_get_service( 'User_Api_Service_WPBOUTIK' );
		$this->subscription      = $this->app_subscription();

		return $this;
	}

	public static function wpboutik_microdata() {
		if ( is_wpboutik_product() ) {
			include( plugin_dir_path( __DIR__ ) . '/templates/microdata/product.php' );
		}
		if ( is_wpboutik_product_taxonomy() ) {
			include( plugin_dir_path( __DIR__ ) . '/templates/microdata/product-cat.php' );
		}
	}

	public static function submit_comment( $submit_field ) {
		global $post;
		if ( empty( $post->post_type ) || 'wpboutik_product' != $post->post_type ) {
			return $submit_field;
		}

		$newClass = 'wpb-btn'; // Classe que vous souhaitez ajouter

		// Expression régulière pour trouver l'élément input avec les attributs spécifiques
		$pattern = '/(<input[^>]*name="submit"[^>]*type="submit"[^>]*class=")([^"]*)"/';
		// Fonction de rappel pour ajouter la nouvelle classe
		$replacement = function ( $matches ) use ( $newClass ) {
			return $matches[1] . $matches[2] . ' ' . $newClass . '"';
		};

		// Remplacement de l'élément trouvé par la nouvelle version avec la classe ajoutée
		$updatedField = preg_replace_callback( $pattern, $replacement, $submit_field );

		return $updatedField;
	}

	public static function theme_setup() {
		add_theme_support( 'custom-line-height' ); // Equivalent to typography.lineHeight.
		add_theme_support( 'custom-spacing' ); // Equivalent to spacing.
		add_theme_support( 'border' ); // Equivalent to spacing.
		add_theme_support( 'link-color' ); // Equivalent to spacing.
		add_theme_support( 'appearance-tools' ); // Requires Gutenberg 14.0 or greater for now.
	}

	public static function custom_admin_style() {
		echo '<style>
			.wpb-notice:before {
				background-image: url("' . esc_url( plugin_dir_url( __DIR__ ) ) . '/assets/img/wp_boutik.png");
				content: "";
				background-size: 100%;
				background-repeat: no-repeat;
				mix-blend-mode: plus-lighter;
				display:inline-block;
				width: 2.5em;
				height: 2.5em;
				margin-block: 7px;
			}
			.wpb-notice {
				display:flex;
				align-items: center;
				gap: 1em;
				background: #3c54cc !important; 
				color: #fff; 
				border-radius: 10px;
				border-left: solid 0 transparent !important;
			} 
			.wpb-notice a {
			    color: #fff !important;
			    opacity: .8;
			}
			.wpb-notice a:hover {
			    opacity: 1;
			}
				.wpb-notice button:before {
					color: #fff !important;
					opacity: .8;
				}
				.wpb-notice button:hover:before {
					color: #fff !important;
					opacity: 1;
				}
		</style>';
	}

	public static function display_post_states( $post_states, $post ) {
		if ( $post->ID === wpboutik_get_page_id( 'cart' ) ) {
			$post_states[] = 'Page panier WPBoutik';
		}

		if ( $post->ID === wpboutik_get_page_id( 'checkout' ) ) {
			$post_states[] = 'Page de commande WPBoutik';
		}

		if ( $post->ID === wpboutik_get_page_id( 'shop' ) ) {
			$post_states[] = 'Page boutique WPBoutik';
		}

		if ( $post->ID === wpboutik_get_page_id( 'account' ) ) {
			$post_states[] = 'Page Mon compte WPBoutik';
		}

		return $post_states;
	}

	/**
	 * Function for `image_sideload_extensions` filter-hook.
	 *
	 * @param string[] $allowed_extensions Array of allowed file extensions.
	 * @param string $file The URL of the image to download.
	 *
	 * @return string[]
	 */
	public static function image_sideload_extensions_filter( $allowed_extensions, $file ) {
		if ( ! in_array( 'avif', $allowed_extensions ) ) {
			$allowed_extensions[] = 'avif';
		}
		if ( ! in_array( 'webp', $allowed_extensions ) ) {
			$allowed_extensions[] = 'webp';
		}

		return $allowed_extensions;
	}

    public static function body_class( $classes ) {
	    if ( ! is_wpboutik_shop() && ! is_page( wpboutik_get_page_id( 'cart' ) ) && ! is_page( wpboutik_get_page_id( 'checkout' ) ) && ! is_page( wpboutik_get_page_id( 'account' ) ) && ! is_wpboutik_product() && ! is_wpboutik_product_taxonomy() ) {
		    return $classes;
	    }

	    $classes[] = 'wpboutik-body';
	    return $classes;
    }

	public static function filter_allowed_mimes_for_avif( $mime_types ) {
		if ( empty( $mime_types['avif'] ) ) {
			$mime_types['avif'] = 'image/avif';
		}
		if ( empty( $mime_types['webp'] ) ) {
			$mime_types['webp'] = 'image/webp';
		}

		return $mime_types;
	}

	public static function filter_mime_to_exts( $mime_to_exsts ) {
		if ( empty( $mime_to_exsts['image/avif'] ) ) {
			$mime_to_exsts['image/avif'] = 'avif';
		}
		if ( empty( $mime_to_exsts['image/webp'] ) ) {
			$mime_to_exsts['image/webp'] = 'webp';
		}

		return $mime_to_exsts;
	}

	public static function filter_mime_types( $mimes ) {
		if ( empty( $mimes['avif'] ) ) {
			$mimes['avif'] = 'image/avif';
		}
		if ( empty( $mimes['webp'] ) ) {
			$mimes['webp'] = 'image/webp';
		}

		return $mimes;
	}

	public static function fix_avif_displayable( $result, $path ) {
		// Pypass avif.
		if ( str_ends_with( $path, '.avif' ) ) {
			return true;
		}
		if ( str_ends_with( $path, '.webp' ) ) {
			return true;
		}

		return $result;
	}

	/**
	 * Initialize the session class.
	 *
	 * @return void
	 */
	public function initialize_session() {
		/**
		 * Filter to overwrite the session class that handles session data for users.
		 */
		$session_class = apply_filters( 'wpboutik_session_handler', 'NF\WPBOUTIK\WPB_Session_Handler' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingSinceComment
		if ( is_null( $this->session ) || ! $this->session instanceof $session_class ) {
			$this->session = new $session_class();
			$this->session->init();
		}
	}

	/**
	 * Initialize the customer and cart objects and setup customer saving on shutdown.
	 *
	 * @return void
	 */
	public function initialize_cart() {
		// Cart needs customer info.
		if ( is_null( $this->customer ) || ! $this->customer instanceof WPB_Customer ) {
			$this->customer = new WPB_Customer( get_current_user_id(), true );
			// Customer should be saved during shutdown.
			// WHY ? add_action( 'shutdown', array( $this->customer, 'save' ), 10 );
		}
		if ( is_null( $this->cart ) || ! $this->cart instanceof WPB_Cart ) {
			$this->cart = new WPB_Cart();
		}
	}


	/**
	 * Flush rewrite rules if the previously added flag exists,
	 * and then remove the flag.
	 */
	public static function wpboutik_flush_rewrite_rules_maybe() {
		if ( get_option( 'wpboutik_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'wpboutik_flush_rewrite_rules_flag' );
		}
	}

	public static function wpb_add_data_namespace_for_script_paypal( $tag, $handle, $src ) {
		if ( 'paypaljs' === $handle ) {
			$tag = '<script id="paypaljs-js" data-namespace="paypal_sdk" src="' . esc_url( $src ) . '"></script>';
		}

		return $tag;
	}

	public static function wpboutik_tax_rates_project( $taxes ) {
		$activate_tax = wpboutik_get_option_params( 'activate_tax' );
		if ( ! $activate_tax ) {
			return $taxes;
		}

		$taxes_project = wpboutik_get_option_params( 'taxes' );
		if ( ! $taxes_project ) {
			return $taxes;
		}

		$taxes_project = json_decode( $taxes_project );

		foreach ( $taxes_project as $tax_project ) {
			$code_pays           = $tax_project->code_pays;
			$percent_tx_standard = $tax_project->percent_tx_standard;
			$name_tx_standard    = $tax_project->name_tx_standard;
			$percent_tx_reduce   = $tax_project->percent_tx_reduce;
			$name_tx_reduce      = $tax_project->name_tx_reduce;
			$percent_tx_zero     = $tax_project->percent_tx_zero;
			$name_tx_zero        = $tax_project->name_tx_zero;

			$result[ $code_pays ] = [
				'percent_tx_standard' => floatval( $percent_tx_standard ),
				'name_tx_standard'    => $name_tx_standard,
				'percent_tx_reduce'   => floatval( $percent_tx_reduce ),
				'name_tx_reduce'      => $name_tx_reduce,
				'percent_tx_zero'     => floatval( $percent_tx_zero ),
				'name_tx_zero'        => $name_tx_zero
			];
		}

		return $result;
	}

	public static function wpboutik_youtube_iframe( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => '', // L'ID de la vidéo YouTube
			),
			$atts,
			'youtube'
		);

		// Vérifier si l'ID de la vidéo est spécifié
		if ( empty( $atts['id'] ) ) {
			return 'L\'ID de la vidéo YouTube est manquant.';
		}

		// Générer le code HTML pour intégrer la vidéo YouTube
		$html = '<iframe style="width:80%; aspect-ratio: 16/9;" src="https://www.youtube.com/embed/' . esc_attr( $atts['id'] ) . '" frameborder="0" allowfullscreen></iframe>';

		return $html;
	}

	public static function wpboutik_last_products( $atts ) {
		$atts = shortcode_atts(
			array(
				'col'         => get_theme_mod( 'wpboutik_archive_col_number_desktop', 4 ),
				'nb_products' => get_theme_mod( 'wpboutik_archive_col_number_desktop', 4 ),
			),
			$atts,
			'wpb_last_products'
		);
		if ( $atts['col'] > 6 ) {
			$atts['col'] = 6;
		}
		if ( $atts['col'] < 0 ) {
			$atts['col'] = 1;
		}
		$query = new \WP_Query( [
			'post_type'      => 'wpboutik_product',
			'orderby'        => 'date',
			'posts_per_page' => $atts['nb_products']
		] );

		$return = '<div class="wpb-product-list grid-cols grid-cols-' . $atts['col'] . '">';
		if ( $query->have_posts() ) :
			while ( $query->have_posts() ) :
				$query->the_post();
				$return .= get_wpb_template_parts( 'product-card' );
			endwhile;
		endif;
		$return .= '</div>';
		wp_reset_query();

		return $return;
	}

	public static function wpboutik_featured_products( $atts ) {
		$atts = shortcode_atts(
			array(
				'col'         => get_theme_mod( 'wpboutik_archive_col_number_desktop', 4 ),
				'nb_products' => get_theme_mod( 'wpboutik_archive_col_number_desktop', 4 ),
			),
			$atts,
			'wpb_last_products'
		);
		if ( $atts['col'] > 6 ) {
			$atts['col'] = 6;
		}
		if ( $atts['col'] < 0 ) {
			$atts['col'] = 1;
		}
		$query = new \WP_Query( [
			'post_type'      => 'wpboutik_product',
			'orderby'        => 'date',
			'posts_per_page' => $atts['nb_products'],
			'meta_query'     => array(
				array(
					'key'   => 'mis_en_avant',
					'value' => 1,
				),
			)
		] );

		$return = '<div class="wpb-product-list grid-cols grid-cols-' . $atts['col'] . '">';
		if ( $query->have_posts() ) :
			while ( $query->have_posts() ) :
				$query->the_post();
				$return .= get_wpb_template_parts( 'product-card' );
			endwhile;
		endif;
		$return .= '</div>';
		wp_reset_query();

		return $return;
	}

	public static function wpboutik_cart_count_shortcode() {
		$backgroundcolor = wpboutik_get_backgroundcolor_button();
		$cartcount       = ( ! empty( WPB()->cart ) ) ? WPB()->cart->get_cart_contents_count() : 0;

		$return = '';

		if ( ! is_wpboutik_shop() && ! is_page( wpboutik_get_page_id( 'cart' ) ) && ! is_page( wpboutik_get_page_id( 'checkout' ) ) && ! is_page( wpboutik_get_page_id( 'account' ) ) && ! is_wpboutik_product() && ! is_wpboutik_product_taxonomy() ) {
			$return .= '<style>
.wpboutik-cart-count {
z-index: 55;
}
.wpboutik-cart-count.text-white {
    --tw-text-opacity: 1 !important;
    color: rgb(255 255 255 / var(--tw-text-opacity)) !important;
}
.wpboutik-cart-count.leading-5 {
    line-height: 1.25rem !important;
}
.wpboutik-cart-count.text-xs {
    font-size: 0.75rem !important;
}
.wpboutik-cart-count.text-center {
    text-align: center !important;
}
.wpboutik-cart-count.bg-\[var\(--backgroundcolor\)\] {
    background-color: var(--backgroundcolor) !important;
}
.wpboutik-cart-count.rounded-full {
    border-radius: 9999px !important;
}
.wpboutik-cart-count.w-5 {
    width: 1.25rem !important;
}
.wpboutik-cart-count.h-5 {
    height: 1.25rem !important;
}
.wpboutik-cart-count.absolute {
    position: absolute !important;
		right: 0 !important;
    top: 0 !important;
		transform: translateX(50%) translateY(-50%);
}</style>';
		}

		if ( $cartcount === '0' || empty( $cartcount ) ) {
			$return .= ' <span class="hidden h-5 w-5 rounded-full text-center text-xs leading-5 bg-[var(--backgroundcolor)] top-0 right-0 text-white z-50 -translate-y-1/2 translate-x-1/2 wpboutik-cart-count" style="--backgroundcolor: ' . $backgroundcolor . '">' . $cartcount . '</span>';
		} else {
			$return .= ' <span class="absolute h-5 w-5 rounded-full text-center text-xs leading-5 bg-[var(--backgroundcolor)] top-0 right-0 text-white z-50 -translate-y-1/2 translate-x-1/2 wpboutik-cart-count" style="--backgroundcolor: ' . $backgroundcolor . '">' . $cartcount . '</span>';
		}

		return $return;
	}

	public static function wpboutik_cart_icon_shortcode() {
		if ( get_theme_mod( 'wpboutik_show_cart_dropdown' ) ) {
			$dashicon = Product::wpboutik_show_dropdown_menu_cart();
			$dashicon .= '<div class="WPBpanierDropdown-overlay"></div>';

		} else {
			if ( get_theme_mod( 'wpboutik_show_cart_icon' ) ) {
				$wpboutik_size_cart_icon = get_theme_mod( 'wpboutik_size_cart_icon', 20 );
				$style                   = '';
				if ( ! empty( $wpboutik_size_cart_icon ) && '20' != $wpboutik_size_cart_icon ) {
					$style = ' style="font-size:' . $wpboutik_size_cart_icon . 'px"';
				}
				$dashicon = '<a' . $style . ' href="' . esc_url( get_permalink( wpboutik_get_page_id( 'cart' ) ) ) . '">' . get_theme_mod( 'wpboutik_choose_cart_icon' ) . do_shortcode( '[wpboutik_cartcount]' ) . '</a>';
			} else {
				$dashicon = '<a href="' . esc_url( get_permalink( wpboutik_get_page_id( 'cart' ) ) ) . '">' . __( 'Cart', 'wpboutik' ) . do_shortcode( '[wpboutik_cartcount]' ) . '</a>';
			}
		}

		return $dashicon;
	}

	public static function wpboutik_start_el( $item_output, $item ) {
		// Rare case when $item is not an object, usually with custom themes.
		if ( ! is_object( $item ) || ! isset( $item->object ) ) {
			return $item_output;
		}

		if ( false === strpos( $item->title, 'wpboutik_cartcount' ) ) {
			return $item_output;
		}

		$item_output = do_shortcode( $item_output );

		return $item_output;
	}

	/**
	 * Enqueue css
	 */
	public static function admin_enqueue_scripts() {
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_style( 'wpboutik', WPBOUTIK_URL . 'assets/css/style' . $min . '.css', array(), WPBOUTIK_VERSION );
		wp_enqueue_script( 'postbox' );
	}

	public static function wpboutik_save_settings() {
		$redirect_url = admin_url( 'admin.php?page=' . 'wpboutik-settings' );
		if ( ! isset( $_GET['tab'] ) || ! isset( $_GET['_wpnonce'] ) ) {
			wp_redirect( $redirect_url );
			exit;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wpboutik_save_settings' ) ) {
			wp_redirect( $redirect_url );
			exit;
		}

		$tab     = sanitize_text_field( $_GET['tab'] );
		$options = $_POST['wpboutik'];

		switch ( $tab ) {
			case Tabs_Admin::SETTINGS:
				$response = self::save_options_to_wpboutik( $options );

				// && is_array( $response['result'] )
				if ( isset( $response['first_install'] ) && $response['first_install'] === true ) {
					$title_of_page_shop = __( 'Shop', 'wpboutik' );
					$shop_page_id       = wp_insert_post(
						array(
							'comment_status' => 'close',
							'ping_status'    => 'close',
							'post_author'    => 1,
							'post_title'     => $title_of_page_shop,
							'post_name'      => strtolower( str_replace( ' ', '-', trim( $title_of_page_shop ) ) ),
							'post_status'    => 'publish',
							'post_type'      => 'page',
						)
					);

					$title_of_page_cart = __( 'Cart', 'wpboutik' );
					$cart_page_id       = wp_insert_post(
						array(
							'comment_status' => 'close',
							'ping_status'    => 'close',
							'post_author'    => 1,
							'post_title'     => $title_of_page_cart,
							'post_name'      => strtolower( str_replace( ' ', '-', trim( $title_of_page_cart ) ) ),
							'post_status'    => 'publish',
							'post_type'      => 'page',
						)
					);

					$title_of_page_checkout = __( 'Checkout', 'wpboutik' );
					$checkout_page_id       = wp_insert_post(
						array(
							'comment_status' => 'close',
							'ping_status'    => 'close',
							'post_author'    => 1,
							'post_title'     => $title_of_page_checkout,
							'post_name'      => strtolower( str_replace( ' ', '-', trim( $title_of_page_checkout ) ) ),
							'post_status'    => 'publish',
							'post_type'      => 'page',
						)
					);

					$title_of_page_account = __( 'My account', 'wpboutik' );
					$account_page_id       = wp_insert_post(
						array(
							'comment_status' => 'close',
							'ping_status'    => 'close',
							'post_author'    => 1,
							'post_title'     => $title_of_page_account,
							'post_name'      => strtolower( str_replace( ' ', '-', trim( $title_of_page_account ) ) ),
							'post_status'    => 'publish',
							'post_type'      => 'page',
						)
					);

					update_option( 'wpboutik_options', array_merge( $options, array(
						'wpboutik_shop_page_id'     => $shop_page_id,
						'wpboutik_cart_page_id'     => $cart_page_id,
						'wpboutik_checkout_page_id' => $checkout_page_id,
						'wpboutik_account_page_id'  => $account_page_id,
					) ) );

					$locations      = get_nav_menu_locations();
					$first_nav_menu = reset( $locations );

					wp_update_nav_menu_item( $first_nav_menu, 0, array(
						'menu-item-title'     => __( 'Shop', 'wpboutik' ),
						'menu-item-object-id' => $shop_page_id,
						'menu-item-object'    => 'page',
						'menu-item-status'    => 'publish',
						'menu-item-type'      => 'post_type',
					) );
					wp_update_nav_menu_item( $first_nav_menu, 0, array(
						'menu-item-title'     => __( 'My account', 'wpboutik' ),
						'menu-item-object-id' => $account_page_id,
						'menu-item-object'    => 'page',
						'menu-item-status'    => 'publish',
						'menu-item-type'      => 'post_type',
					) );

					set_theme_mod( 'wpboutik_mini_cart_menu', $first_nav_menu );
					set_theme_mod( 'wpboutik_search_product_menu', $first_nav_menu );
					set_theme_mod( 'wpboutik_show_cart_icon', true );

					update_option( 'wpboutik_version', WPBOUTIK_VERSION );
				}
				break;
		}

		wp_redirect( $redirect_url );
		exit;
	}


	public static function wpboutik_product_search( $items, $args ) {
		$wpboutik_search_product = get_theme_mod( 'wpboutik_search_product', 'yes' );
		$wpboutik_mini_cart      = get_theme_mod( 'wpboutik_mini_cart_menu' );

		if ( ! empty( $wpboutik_mini_cart ) &&
		     get_theme_mod( 'wpboutik_mini_cart_menu' ) == $args->theme_location
		) {
			$items .= '<li class="menu-item nav-item menu-item-minicart">';
			$items .= self::wpboutik_cart_icon_shortcode();
			$items .= '</li>';
		}

		if ( ! empty( $wpboutik_search_product ) &&
		     $wpboutik_search_product == "yes" &&
		     get_theme_mod( 'wpboutik_search_product_menu' ) == $args->theme_location
		) {
			$dashicon = '<li class="menu-item nav-item menu-item-search-product">';
			$dashicon .= '<a href="#" class="search_product_link' . ( empty( get_theme_mod( 'wpboutik_search_product_show_input', '' ) ) ? '' : ' only-mobile' ) . '"><span style="font-size:' . get_theme_mod( 'wpboutik_size_cart_icon', 20 ) . 'px"><svg xmlns="http://www.w3.org/2000/svg" class="wpb_cart_dashicon" style="width:1.2em;height:1.2em" fill="currentColor" viewBox="0 0 16 16">
			<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
		</svg></span><span class="sr-only">' . __( 'Cart', 'wpboutik' ) . '</span>
		</a>';


			$dashicon .= '<div class="search_product_box' . get_theme_mod( 'wpboutik_search_product_show_input', '' ) . '">';
			$dashicon .= '<input type="search" id="wpb-product-search" placeholder="Rechercher un produit">';
			$dashicon .= '<span class="dashicons dashicons-no close_search_product"></span>';
			$dashicon .= '<div class="wpb-search-results"></div>';
			$dashicon .= '</div></li>';
			$items    .= $dashicon;
		}

		return $items;
	}

	public static function wpboutik_disconnect_project() {
		$redirect_url = admin_url( 'admin.php?page=' . 'wpboutik-settings' );

		if ( ! isset( $_POST['tab'] ) || ! isset( $_POST['_wpnonce'] ) ) {
			wp_redirect( $redirect_url );
			exit;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wpboutik_disconnect_project' ) ) {
			wp_redirect( $redirect_url );
			exit;
		}

		\NF\WPBOUTIK\Plugin::disconnect_project();
		wp_redirect( $redirect_url );
		exit;
	}

	public static function wpboutik_save_settings_analytics() {
		$redirect_url = admin_url( 'admin.php?page=' . 'wpboutik-git google-analytics' );
		if ( ! isset( $_GET['tab'] ) || ! isset( $_GET['_wpnonce'] ) ) {
			wp_redirect( $redirect_url );
			exit;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wpboutik_save_settings_analytics' ) ) {
			wp_redirect( $redirect_url );
			exit;
		}

		$tab     = sanitize_text_field( $_GET['tab'] );
		$options = $_POST['wpboutik'];

		switch ( $tab ) {
			case 'analytics':
				$response = self::save_options_google_analytics_to_wpboutik( $options );
		}

		wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * @param array $options
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function save_options_google_analytics_to_wpboutik( $options ) {

		if ( ! isset( $options['ga_gtag_enabled'] ) ) {
			$options['ga_gtag_enabled'] = '0';
		}
		if ( ! isset( $options['ga_standard_tracking_enabled'] ) ) {
			$options['ga_standard_tracking_enabled'] = '0';
		}
		if ( ! isset( $options['ga_support_display_advertising'] ) ) {
			$options['ga_support_display_advertising'] = '0';
		}
		if ( ! isset( $options['ga_support_enhanced_link_attribution'] ) ) {
			$options['ga_support_enhanced_link_attribution'] = '0';
		}
		if ( ! isset( $options['ga_anonymize_enabled'] ) ) {
			$options['ga_anonymize_enabled'] = '0';
		}
		if ( ! isset( $options['ga_404_tracking_enabled'] ) ) {
			$options['ga_404_tracking_enabled'] = '0';
		}
		if ( ! isset( $options['ga_event_tracking_enabled'] ) ) {
			$options['ga_event_tracking_enabled'] = '0';
		}
		if ( ! isset( $options['ga_linker_allow_incoming_enabled'] ) ) {
			$options['ga_linker_allow_incoming_enabled'] = '0';
		}
		if ( ! isset( $options['ga_enhanced_ecommerce_tracking_enabled'] ) ) {
			$options['ga_enhanced_ecommerce_tracking_enabled'] = '0';
		}
		if ( ! isset( $options['ga_enhanced_remove_from_cart_enabled'] ) ) {
			$options['ga_enhanced_remove_from_cart_enabled'] = '0';
		}
		if ( ! isset( $options['ga_enhanced_product_impression_enabled'] ) ) {
			$options['ga_enhanced_product_impression_enabled'] = '0';
		}
		if ( ! isset( $options['ga_enhanced_product_click_enabled'] ) ) {
			$options['ga_enhanced_product_click_enabled'] = '0';
		}
		if ( ! isset( $options['ga_enhanced_product_detail_view_enabled'] ) ) {
			$options['ga_enhanced_product_detail_view_enabled'] = '0';
		}
		if ( ! isset( $options['ga_enhanced_checkout_process_enabled'] ) ) {
			$options['ga_enhanced_checkout_process_enabled'] = '0';
		}

		update_option( 'wpboutik_options_google_analytics', $options );

		return array(
			'success' => true,
		);
	}

	/**
	 * @param array $options
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function save_options_to_wpboutik( $options ) {
		$wpboutik_options = get_option( 'wpboutik_options' );
		if ( empty( $wpboutik_options ) ) {
			update_option( 'wpboutik_options', $options );
		} else {
			update_option( 'wpboutik_options', ( $options + $wpboutik_options ) );
		}

		$first_install = get_option( 'wpboutik_options_first_install' );

		$api_query = WPB_Api_Request::request( 'init' )->add_multiple_to_body( $options )->exec();

		if ( false === $first_install ) {
			if ( $api_query->is_error() ) {
				return array(
					'success' => false,
				);
			}

			add_option( 'wpboutik_options_first_install', true );

			return array(
				'success'       => true,
				'first_install' => true,
				'result'        => json_decode( $api_query->get_response_body() ),
			);
		} else {
			return array(
				'success' => true,
			);
		}
	}

	public function admin_notices() {
		?>
        <div class="error settings-error notice is-dismissible wpb-notice">
            <p>
				<?php
				// translators: 1 HTML Tag, 2 HTML Tag
				echo sprintf( esc_html__( 'WPBoutik is installed but not yet configured, you need to configure WPBoutik here : %1$sWPBoutik configuration page%2$s. The configuration takes only 1 minute! ', 'wpboutik' ), '<a href="' . esc_url( admin_url( 'admin.php?page=' . 'wpboutik-settings' ) ) . '">', '</a>' );
				?>
            </p>
        </div>
		<?php
	}

	public static function admin_notices_no_curl() {
		?>
        <div class="error settings-error notice is-dismissible wpb-notice">
            <p>
				<?php
				// translators: 1 HTML Tag, 2 HTML Tag
				echo esc_html__( 'WPBoutik: You need to activate cURL. If you need help, just ask us directly at support@wpboutik.com.', 'wpboutik' );
				?>
            </p>
        </div>
		<?php
	}

	public static function admin_notices_warning_woocommerce() {
		?>
        <div class="error settings-error notice is-dismissible wpb-notice">
            <p>
				<?php
				// translators: 1 HTML Tag, 2 HTML Tag
				echo esc_html__( 'WPBoutik: WooCommerce appears to be enabled on this site, which may cause conflicts and malfunctions. Please disable it to avoid any issues.', 'wpboutik' );
				?>
            </p>
        </div>
		<?php
	}

	public static function admin_notices_plugin_update() {
		?>
        <div class="error settings-error notice is-dismissible wpb-notice">
            <p>
				<?php
				// translators: 1 HTML Tag, 2 HTML Tag
				echo esc_html__( 'WPBoutik: A new version is available. Please update your plugin to avoid any functionality issues.', 'wpboutik' );
				?>
            </p>
        </div>
		<?php
	}

	public static function admin_notices_json_functions() {
		?>
        <div class="error settings-error notice is-dismissible wpb-notice">
            <p>
				<?php
				// translators: 1 HTML Tag, 2 HTML Tag
				echo esc_html__( 'WPBoutik: You need to activate package php-json. If you need help, please contact your host or just ask us directly at support@wpboutik.com.', 'wpboutik' );
				?>
            </p>
        </div>
		<?php
	}

	/**
	 * Add menu and sub pages
	 *
	 * @return void
	 * @since 1.0
	 * @see admin_menu
	 *
	 */
	public function add_plugin_menu() {
		$menu_icon = 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjEiIHg9IjBweCIgeT0iMHB4IiB3aWR0aD0iMTc0Ljc4NDcxMzc0NTEyIiBoZWlnaHQ9IjE2NS4wMDAwMTUyNTg3OSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeG1sbnM6YT0iaHR0cDovL25zLmFkb2JlLmNvbS9BZG9iZVNWR1ZpZXdlckV4dGVuc2lvbnMvMy4wLyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSI+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSBzY2FsZSgxLjMyNDk5MTY5NDcyMTEgMS4zMjQ5OTI3ODI4NjM5KSByb3RhdGUoMCA2NS45NTY5MDkxNzk2ODggNjIuMjY0NDk1ODQ5NjA5KSI+PHBhdGggZD0iTTkzLjMyNCA4OS41NjZjLTIuODktLjAwMi00Ljg4IDIuNDkxLTQuNjI4IDQuNjc0LS4xMjMgMi40NTggMS44NTYgNC42NDQgNC42NzUgNC42MjMgMi41NTEtLjAxOCA0LjY3Mi0yLjE0MiA0LjY4MS00LjYxMy4wMDktMi41MzQtMi4xNzUtNC42ODItNC43MjgtNC42ODR6bS0zMS4yMjgtLjAwM2MtMi42NjQtLjA0Mi00LjY1NSAxLjk1Ny00LjY1MSA0LjY3Mi4wMDQgMi42NTkgMS45MDEgNC41OTEgNC41NTIgNC42MzQgMi42MDkuMDQzIDQuNjk0LTIuMDQgNC42ODItNC42NzUtLjAxNC0yLjU3NS0yLjAwNy00LjU4OS00LjU4My00LjYzMXoiIGZpbGw9IiM1NjViNmUiPjwvcGF0aD48cGF0aCBkPSJNMTcuODM1IDc5LjEyMWExLjc3IDEuNzcgMCAwMC0zLjM3MSAxLjA3OGwyLjc0NSA4LjU4NWExLjc2OCAxLjc2OCAwIDAwMi4yMjQgMS4xNDYgMS43NjggMS43NjggMCAwMDEuMTQ2LTIuMjI0bC0yLjc0NC04LjU4NXptLTQuODQxIDMuNjM3bC0uNTUtMS43MjFhMy45NTQgMy45NTQgMCAwMTIuNTYzLTQuOTcgMy45NSAzLjk1IDAgMDE0Ljg5OSAyLjM3NWMuMDcuMDkxLjY2OCAxLjgzLjc0IDEuOTI2bDguNTI4LTIuNzI3Yy0xLjM2OC00LjI3OS0yLjQyOC04LjUyLTQuMDcxLTEyLjUyMS0yLjYyOS02LjQtOS4zNjEtOS40NzItMTUuNjktNy41NDlDMi45MDUgNTkuNTQ4LTEuMTI3IDY1LjY4OC4yOCA3Mi4zNzNjLjkzNCA0LjQzNyAyLjY4OSA4LjcgNC4xMzMgMTMuMjE1bDguNTkyLTIuNzQ3Yy0uMDA1LS4wMjgtLjAwNi0uMDU1LS4wMTEtLjA4M3pNNjkuNjUgMEM1Mi45OTMgMCAzNy44NTkgNi41OTIgMjYuNjc0IDE3LjI4M2MtMS4zOTEuNzQ1LTIuMzc2IDIuMjk0LTIuMjYxIDMuNjQyLjE2NiAxLjkzNCAxLjczOSAzLjM3MiAzLjg1MiAzLjU2Ni4zODUuMDM1Ljc2Mi4xMjUgMS4wOTMuMzI0IDEuNzA0IDEuMDI1IDMuNDEgMi4wNTMgNS4wNDggMy4xNzguNDgyLjMzMS44My44MjguOTg3IDEuMzkzIDEuMTE3IDQuMDExIDIuMTUgOC4wNDYgMy4yMjggMTIuMDY4IDEuOTgzIDcuNDA1IDMuOTggMTQuODA3IDUuOTY4IDIyLjIxIDEuNzkxIDYuNjc1IDMuNTc4IDEzLjM1IDUuMzY1IDIwLjAyNC43IDIuNjEzIDEuMjE1IDMuMDE0IDMuOTc0IDMuMDE1IDcuNTc5LjAwNSAxNS4xNTguMDAyIDIyLjczNi4wMDJoNi4wMDdjNS44MTQtLjAwMyAxMS42My4wMTEgMTcuNDQ1LS4wMTggMS42MTEtLjAwOCAyLjQ4MS0uNzIyIDIuODE3LTIuMjg0LjI2OC0xLjI0NC0uMjI1LTIuMzMxLTEuMzE2LTIuODg3LS43MDktLjM2LTEuNjM2LS40MDItMi40NjUtLjQwNC0xMC4xMzUtLjAxNy0yMC4yNy0uMDE0LTMwLjQwMy0uMDE2YTQuMzY0IDQuMzY0IDAgMDEtLjY4Mi4wNTljLTIuMjgzLjAxMS03LjM5NC4wMTQtMTAuNjczLjAxNGEyLjk0MiAyLjk0MiAwIDAxLTIuOTQxLTIuOTQ5IDIuOTQzIDIuOTQzIDAgMDEyLjYxOS0yLjkzYzMuNjA2LS4zOTggMTAuMDcyLTEuMTIgMTMuNzcxLTEuNTE5IDQuMjMzLS40NTcgOC40NzMtLjg2NiAxMi43MS0xLjI5Ny4wNDctLjAwNS4wOTQtLjAxNC4xNDItLjAyIDQuNjIzLS41NTEgOS4yNDUtMS4xMjUgMTMuODczLTEuNjQxIDEuNjk5LS4xODggMy4xOTctMS40MSAzLjM5Ny0zLjA5NC40ODQtNC4wNTguOTI4LTguMTIxIDEuMzUyLTEyLjE4N2EyMTgwLjc0IDIxODAuNzQgMCAwMDEuNjc3LTE2Ljc0NCAzLjQwNyAzLjQwNyAwIDAwLS4yMDItMS41MTZjLS43OTQtMi4xMTktMS41MTUtMi41NDctNC4yMDgtMi41NDgtMTcuNTQyLS4wMDQtMzUuMDg1LS4wMDEtNTIuNjI3LS4wMDYtMS4wOTYgMC0yLjIwNC4wMzUtMy4yODEtLjEyLS40NDktLjA2NC0xLjA4NS0uNTEtMS4yMDQtLjkxLS43MDEtMi4zNTYtMS4yMTMtNC43Ny0xLjkyNS03LjEyMy0uMjU2LS44NDQtLjczNy0xLjc0OS0xLjM4OC0yLjMxNC0xLjAyMS0uODkxLTIuMjg5LTEuNDkyLTMuNDIyLTIuMjYxLTEuNDQ3LS45ODMtMy4wNzEtMS42ODItNC4yNDUtMy4wN0M0MS42NzggOS45NDIgNTUuMDM0IDQuNDc1IDY5LjY1IDQuNDc1YzMxLjg2NSAwIDU3Ljc5IDI1LjkyNSA1Ny43OSA1Ny43OSAwIDMxLjg2NC0yNS45MjUgNTcuNzg5LTU3Ljc5IDU3Ljc4OS0xOC40MDcgMC0zNC44MTQtOC42NjctNDUuNDA1LTIyLjExNyA2LjMzOC0yLjU0OCA5LjIyNS0xMS44NzkgNS4zOTYtMTcuODc2bC04LjMwOCAyLjY1Ni4wMjQuMjM2IDEuNjI4IDUuMDkyYTMuOTQ3IDMuOTQ3IDAgMDEtMS4zMjggNC4zMDRjLS40NTQuMzM5LTEuNDY5LjkzOS0yLjguODIyYTQuODEzIDQuODEzIDAgMDEtLjIxLS4wMjNjLS4wOTEtLjAxNC0uMTgyLS4wMjctLjI3NC0uMDQ3YTMuOTQxIDMuOTQxIDAgMDEtMi45MTktMi42NDdsLS4xNTctLjQ5MWMtLjU3Ny0xLjMyMS0uODUtMi44NTEtMS4zOTctNC4xOTEtLjA1NC0uMTMzLS4xMzgtLjI1NC0uMzE2LS41NzVsLTguMzA4IDIuNjU2Yy4zNTQgNi45ODEgNy44NjIgMTIuODQzIDE0LjQyMyAxMS41MjIgMTEuMzYgMTUuMjUyIDI5LjUxNiAyNS4xNTQgNDkuOTUxIDI1LjE1NCAzNC4zMzMgMCA2Mi4yNjQtMjcuOTMxIDYyLjI2NC02Mi4yNjRDMTMxLjkxNCAyNy45MzEgMTAzLjk4MyAwIDY5LjY1IDB6bTEzLjQ1NSA0NC41MDdhMi4zMjkgMi4zMjkgMCAwMTQuNjU3IDB2MTkuNjA3YTIuMzMgMi4zMyAwIDAxLTQuNjU3IDBWNDQuNTA3em0tMTIuMDYzIDBhMi4zMjggMi4zMjggMCAwMTQuNjU2IDB2MTkuNjA3YTIuMzI4IDIuMzI4IDAgMDEtNC42NTYgMFY0NC41MDd6bS0xMi4wNjUgMGEyLjMzIDIuMzMgMCAwMTQuNjU3IDB2MTkuNjA3YTIuMzI5IDIuMzI5IDAgMDEtNC42NTcgMFY0NC41MDd6IiBmaWxsPSIjMDA5NGQ5Ij48L3BhdGg+PC9nPjwvZz48L3N2Zz4=';

		add_menu_page( WPBOUTIK_WHITELABEL, WPBOUTIK_WHITELABEL, 'manage_options', 'wpboutik-settings', array(
			$this,
			'wpboutik_plugin_settings_page'
		), $menu_icon );
	}

	/**
	 * Page settings
	 *
	 * @return void
	 * @throws \Exception
	 * @since 1.0
	 *
	 */
	public function wpboutik_plugin_settings_page() {
		$this->tabs       = Tabs_Admin::get_full_tabs();
		$this->tab_active = Tabs_Admin::SETTINGS;

		if ( isset( $_GET['tab'] ) ) {
			$this->tab_active = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
		}

		$this->options = wpboutik_get_options();

		try {
			//$user_info = $this->user_api_services->get_user_info();
			$user_info = [];
			if ( isset( $user_info['allowed'] ) ) {
				$this->option_services->set_option_by_key( 'allowed', $user_info['allowed'] );
			}
		} catch ( \Exception $e ) {
			// If an exception occurs, do nothing, keep wg_allowed.
		}

		include_once WPBOUTIK_TEMPLATES_ADMIN_PAGES . '/settings.php';
	}

	/**
	 * @return void
	 * @since 1.0
	 */
	public function add_admin_bar_menu() {
		global $wp_admin_bar;
		global $wp;

		if ( is_admin() ) {
			$args = array(
				'parent' => 'site-name',
				'id'     => 'view-wpboutik-store',
				'title'  => __( 'View store', 'wpboutik' ),
				'href'   => esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ),
				'meta'   => false
			);
			$wp_admin_bar->add_node( $args );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
			false;
		}

		if ( is_wpboutik_product() ) {
			$wpboutik_post_id = get_post_meta( get_the_ID(), 'wpboutik_post_id', true );

			if ( $wpboutik_post_id ) {
				$language     = wpboutik_get_option_params( 'language' );
				$project_slug = wpboutik_get_option_params( 'project_slug' );
				$url          = WPBOUTIK_APP_URL . $language . '/' . $project_slug . '/';
				if ( ! empty( $language ) ) {
					$url = WPBOUTIK_APP_URL . $language . '/' . $project_slug . '/';
				}

				$wp_admin_bar->add_menu(
					array(
						'id'    => 'edit-wpb-product',
						'title' => __( 'Edit product', 'wpboutik' ),
						'href'  => $url . 'products/' . $wpboutik_post_id . '/edit',
						'meta'  => array(
							'target' => '_blank'
						)
					)
				);
			}
		}

		/*if( is_tax( 'wpboutik_product_cat' ) ) {
			$language     = wpboutik_get_option_params( 'language' );
			$project_slug = wpboutik_get_option_params( 'project_slug' );
			$url          = WPBOUTIK_APP_URL . $language . '/' . $project_slug . '/';
			if ( ! empty( $language ) ) {
				$url = WPBOUTIK_APP_URL . $language . '/' . $project_slug . '/';
			}

			$wp_admin_bar->add_menu(
				array(
					'id'    => 'edit-wpb-product-cat',
					'title' => __( 'Edit product cat', 'wpboutik' ),
					'href'  => $url . 'categories/' . 'xx' . '/edit',
					'meta'  => array(
						'target' => '_blank'
					)
				)
			);
		}*/

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'wpboutik',
				'title' => WPBOUTIK_WHITELABEL,
				'href'  => '',
			)
		);

		if ( is_admin() ) {
			$url_to_edit = get_home_url();
		} else {
			$url_to_edit = home_url( add_query_arg( array(), $wp->request ) );
		}

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'wpboutik-settings',
				'parent' => 'wpboutik',
				'title'  => __( 'Plugin settings', 'wpboutik' ),
				'href'   => admin_url( 'admin.php?page=wpboutik-settings' ),
			)
		);

		$language     = wpboutik_get_option_params( 'language' );
		$project_slug = wpboutik_get_option_params( 'project_slug' );
		$url          = WPBOUTIK_APP_URL . 'fr/login';
		if ( ! empty( $language ) ) {
			$url = WPBOUTIK_APP_URL . $language . '/' . $project_slug . '/dashboard';
		}

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'wpboutik-dashboard',
				'parent' => 'wpboutik',
				'title'  => __( 'WPBoutik dashboard', 'wpboutik' ),
				'href'   => esc_url( $url ),
				'meta'   => array(
					'target' => '_blank',
				),
			)
		);

	}

	public function show_admin_bar( $show ) {
		if ( current_user_can( 'customer-wpb' ) ) {
			return false;
		}

		return $show;
	}

	public function redirect_customer_wpb() {
		$updates = get_plugin_updates();
		if ( ! empty( $updates['wpboutik/wpboutik.php'] ) && ! empty( $updates['wpboutik/wpboutik.php']->update ) ) {
			add_action( 'admin_notices', array( '\NF\WPBOUTIK\Plugin', 'admin_notices_plugin_update' ) );
		}

		if ( current_user_can( 'customer-wpb' ) && ! wp_doing_ajax() ) {
			wp_redirect( wpboutik_get_page_permalink( 'shop' ) );
			exit;
		}
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public static function plugin_path() {
		return untrailingslashit( WPBOUTIK_DIR );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public static function template_path() {
		return apply_filters( 'wpboutik_template_path', 'wpboutik/' );
	}

	/**
	 * Process the login form.
	 *
	 * @throws \Exception On login error.
	 */
	private function app_subscription() {
		$options             = get_option( 'wpboutik_subscription', [] );
		$subscription_status = ( $options['status'] ) ? $options['status'] : false;
		$subscription_end    = ( $options['date'] ) ? $options['date'] : null;

		if ( ! $subscription_status || empty( $subscription_end ) || $subscription_end < time() ) {
			$api_request = WPB_Api_Request::request( 'subscription' )->exec();
			if ( ! $api_request->is_error() ) {
				$response            = $api_request->get_response_body();
				$response            = json_decode( $response );
				$subscription_status = isset( $response->hasSubscription ) ? $response->hasSubscription : false;
				$subscription_end    = isset( $response->date_expire ) ? $response->date_expire : null;
				update_option(
					'wpboutik_subscription',
					[
						'status' => $subscription_status,
						'date'   => $subscription_end,
					]
				);
			}
		}

		return $subscription_status;
	}

	public function is_subscription_active() {
		//return true;
		return $this->subscription;
	}

	/**
	 * Process the login form.
	 *
	 * @throws \Exception On login error.
	 */
	public static function process_login() {
		if ( ! isset( $_REQUEST['wpboutik-login-nonce'] ) ) {
			return false;
		}
		if ( ! WPB()->is_subscription_active() ) {
			throw new \Exception( esc_html__( 'Login is currently not available !' ) );
		}
		if ( isset( $_POST['log'], $_POST['pwd'] ) && wp_verify_nonce( $_REQUEST['wpboutik-login-nonce'], 'wpboutik-login' ) ) {
			setcookie( 'wpboutik_error_login', '', 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
			try {
				$creds = array(
					'user_login'    => trim( wp_unslash( $_POST['log'] ) ),
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'user_password' => $_POST['pwd'],
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					'remember'      => isset( $_POST['rememberme'] ),
					// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);

				// On multisite, ensure user exists on current site, if not add them before allowing login.
				if ( is_multisite() ) {
					$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

					if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
						add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
					}
				}

				// Perform the login.
				$user = wp_signon( apply_filters( 'wpboutik_login_credentials', $creds ), is_ssl() );

				if ( is_wp_error( $user ) ) {
					throw new \Exception( $user->get_error_message() );
				} else {

					if ( ! empty( $_POST['redirect'] ) ) {
						$redirect = wp_unslash( $_POST['redirect'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					} else {
						$redirect = wpboutik_get_page_permalink( 'account' );
					}

					wp_redirect( wp_validate_redirect( apply_filters( 'wpboutik_login_redirect', remove_query_arg( 'wc_error', $redirect ), $user ), wpboutik_get_page_permalink( 'account' ) ) ); // phpcs:ignore
					exit;
				}
			} catch ( \Exception $e ) {
				do_action( 'wpboutik_login_failed' );
				setcookie( 'wpboutik_error_login', $e->getMessage(), 0, '/', sanitize_text_field( $_SERVER['HTTP_HOST'] ) );
				wp_redirect( $_SERVER['HTTP_REFERER'] );
				die;
			}
		}
	}

	/**
	 * Handle lost password form.
	 */
	public static function process_lost_password() {
		if ( isset( $_POST['wpb_reset_password'], $_POST['user_login'] ) ) {
			$nonce_value = wpb_get_var( $_REQUEST['wpboutik-lost-password-nonce'], wpb_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

			if ( ! wp_verify_nonce( $nonce_value, 'lost_password' ) ) {
				return;
			}

			$success = self::retrieve_password();

			// If successful, redirect to my account with query arg set.
			if ( $success ) {
				wp_safe_redirect( add_query_arg( 'reset-link-sent', 'true', wpboutik_get_account_endpoint_url( 'lost-password' ) ) );
				exit;
			}
		}
	}

	/**
	 * Handles sending password retrieval email to customer.
	 *
	 * Based on retrieve_password() in core wp-login.php.
	 *
	 * @return bool True: when finish. False: on error
	 * @uses $wpdb WordPress Database object
	 */
	public static function retrieve_password() {
		$login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : ''; // WPCS: input var ok, CSRF ok.

		if ( empty( $login ) ) {
			return false;
		} else {
			// Check on username first, as customers can use emails as usernames.
			$user_data = get_user_by( 'login', $login );
		}

		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $login ) && apply_filters( 'wpboutik_get_username_from_email', true ) ) {
			$user_data = get_user_by( 'email', $login );
		}
		$errors = new \WP_Error();

		do_action( 'lostpassword_post', $errors, $user_data );

		if ( $errors->get_error_code() ) {
			return false;
		}

		if ( ! $user_data ) {
			return false;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
			return false;
		}

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		do_action( 'retrieve_password', $user_login );
		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );
		if ( ! $allow ) {
			return false;
		} elseif ( is_wp_error( $allow ) ) {
			return false;
		}

		// Get password reset key (function introduced in WordPress 4.4).
		$key = get_password_reset_key( $user_data );

		// Send email notification.
		do_action( 'wpboutik_reset_password_notification', $user_login, $key );
		if ( $user_login && $key ) {
			$user = get_user_by( 'login', $user_login );

			$options   = get_option( 'wpboutik_options' );
			$api_query = WPB_Api_Request::request( 'mail', 'reset_pass' )
			                            ->add_to_body( 'options', $options )
			                            ->add_to_body( 'wp_user_id', $user->ID )
			                            ->add_to_body( 'reset_key', $key )
			                            ->exec();
			// var_dump($api_query->get_response_body()); die;

		}

		return true;
	}

	/**
	 * Handle reset password form.
	 */
	public static function process_reset_password() {
		$nonce_value = wpb_get_var( $_REQUEST['wpboutik-reset-password-nonce'], wpb_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

		if ( ! wp_verify_nonce( $nonce_value, 'reset_password' ) ) {
			return;
		}

		$posted_fields = array( 'wpb_reset_password', 'password_1', 'password_2', 'reset_key', 'reset_login' );

		foreach ( $posted_fields as $field ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				return;
			}

			if ( in_array( $field, array( 'password_1', 'password_2' ), true ) ) {
				// Don't unslash password fields
				// @see https://github.com/woocommerce/woocommerce/issues/23922.
				$posted_fields[ $field ] = $_POST[ $field ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			} else {
				$posted_fields[ $field ] = wp_unslash( $_POST[ $field ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			}
		}

		$user   = wpb_check_password_reset_key( $posted_fields['reset_key'], $posted_fields['reset_login'] );
		$errors = [];

		if ( $user instanceof \WP_User ) {
			if ( empty( $posted_fields['password_1'] ) || empty( $posted_fields['password_2'] ) ) {
				$errors[] = new \WP_Error( 'empty_pass', __( 'Passwords cannot be empty.' ) );
			}

			if ( $posted_fields['password_1'] !== $posted_fields['password_2'] ) {
				$errors[] = new \WP_Error( 'wrong_pass', __( '<strong>Error:</strong> The passwords do not match.' ) );
			}

			do_action( 'validate_password_reset', $errors, $user );

			if ( 0 === sizeof( $errors ) ) {
				self::reset_password( $user, $posted_fields['password_1'] );

				do_action( 'wpboutik_customer_reset_password', $user );

				wp_safe_redirect( add_query_arg( 'password-reset', 'true', wpboutik_get_page_permalink( 'account' ) ) );
				exit;
			} else {
				$_SESSION['wpboutik_error_reset_pass'] = [];
				foreach ( $errors as $error ) {
					$_SESSION['wpboutik_error_reset_pass'][] = $error->get_error_message();
				}
			}
		}
	}

	/**
	 * Handles resetting the user's password.
	 *
	 * @param object $user The user.
	 * @param string $new_pass New password for the user in plaintext.
	 */
	public static function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );
		self::set_reset_password_cookie();

		if ( ! apply_filters( 'wpboutik_disable_password_change_notification', false ) ) {
			wp_password_change_notification( $user );
		}
	}

	/**
	 * Set or unset the cookie.
	 *
	 * @param string $value Cookie value.
	 */
	public static function set_reset_password_cookie( $value = '' ) {
		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
		$rp_path   = isset( $_SERVER['REQUEST_URI'] ) ? current( explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : ''; // WPCS: input var ok, sanitization ok.

		if ( $value ) {
			setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		} else {
			setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		}
	}

	public static function redirect_to_shop_page() {
		if ( isset( $_GET['wpboutik_home'] ) && $_GET['wpboutik_home'] == 'show' ) {
			wp_redirect( esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ) );
			exit();
		}
	}

	public static function wpboutik_zoom_single_img() {
		if ( ! is_wpboutik_product() ) {
			return false;
		}
		wp_enqueue_script( 'wpboutik-zoom', WPBOUTIK_URL . 'assets/js/jquery.zoom.min.js', array(
			'jquery',
		), '1.0', array( 'in_footer' => true ) );
	}

	public static function wpboutik_import_scripts_and_styles() {

		//--wpb-global-font-size : " . get_theme_mod( 'wpboutik_general_font_size', 14 ) . "px;

		$wpb_variables = "
		:root {
			--wpb-label-bg : " . get_theme_mod( 'wpboutik_background_color_labels', '#dcfce7' ) . ";
			--wpb-label-text : " . get_theme_mod( 'wpboutik_color_labels', '#166534' ) . ";
			--wpb-label-font-size : " . get_theme_mod( 'wpboutik_font_size_labels', 14 ) . "px;
			--wpb-excerpt-text : " . get_theme_mod( 'wpboutik_archive_excerpt_size', 14 ) . "px;
			--wpb-single-excerpt-text : " . get_theme_mod( 'wpboutik_single_excerpt_size', 14 ) . "px;
			--wpb-excerpt-small-device : " . ( get_theme_mod( 'wpboutik_archive_have_excerpt_mobile', false ) ? 'none' : 'block' ) . ";
			--wpb-single-description-text : " . get_theme_mod( 'wpboutik_single_description_size', 14 ) . "px;
			--wpb-title-widget-text : " . get_theme_mod( 'wpboutik_title_widget_font_size', 30 ) . "px;
			--wpb-title-widget-color : " . get_theme_mod( 'wpboutik_title_widget_color', '#333' ) . ";
			--wpb-btn-bg : " . wpboutik_get_backgroundcolor_button() . ";
			--wpb-btn-bg-hover : " . wpboutik_get_hovercolor_button() . ";
			--wpb-btn-text : " . wpboutik_get_button_text_color() . ";
			--wpb-btn-font-size : " . get_theme_mod( 'wpboutik_button_font_size', 16 ) . "px;
			--wpb-btn-border-radius : " . get_theme_mod( 'wpboutik_button_border_radius', 6 ) . "px;
			--wpb-title-color : " . wpboutik_get_title_product_color() . ";
			--wpb-title-color-hover : " . wpboutik_get_title_product_color_on_hover() . ";
			--wpb-title-font-size : " . get_theme_mod( 'wpboutik_single_product_title_font_size', 20 ) . "px;
			--wpb-archive-title-font-size : " . get_theme_mod( 'wpboutik_archive_product_title_font_size', 14 ) . "px;
			--wpb-price-color : " . get_theme_mod( 'wpboutik_price_product_color', '#6b7280' ) . ";
			--wpb-price-font-size : " . get_theme_mod( 'wpboutik_single_price_font_size', 18 ) . "px;
			--wpb-archive-price-font-size : " . get_theme_mod( 'wpboutik_archive_price_font_size', 16 ) . "px;
			--wpb-wpboutik_backgroundcolor_products : " . get_theme_mod( 'wpboutik_backgroundcolor_products', '#ffffff' ) . ";
			--wpb-archive-columns : " . get_theme_mod( 'wpboutik_archive_col_number_desktop', 4 ) . ";
			--wpb-archive-columns-sm : " . get_theme_mod( 'wpboutik_archive_col_number_mobile', 2 ) . ";
			--wpb-archive-columns-spacing : " . get_theme_mod( 'wpboutik_archive_col_spacing', 15 ) . "px;
			--wpb-product-bg : " . get_theme_mod( 'wpboutik_backgroundcolor_products', '#ffffff' ) . ";
			--wpb-product-border-color : " . get_theme_mod( 'wpboutik_bordercolor_products', '#e5e7eb' ) . ";
			--wpb-product-border-radius : " . get_theme_mod( 'wpboutik_borderradius_products', '20' ) . "px;
			--wpb-product-border-weight : " . get_theme_mod( 'wpboutik_borderweight_products', '1' ) . "px;
			--wpb-product-image-format : " . get_theme_mod( 'wpboutik_archive_image_format', '16/9' ) . ";
			--wpb-product-image-fill : " . get_theme_mod( 'wpboutik_archive_image_fill', 'contain' ) . ";
			--wpb-product-image-padding : " . get_theme_mod( 'wpboutik_image_padding', 7 ) . "px;
			--wpb-product-content-padding : " . get_theme_mod( 'wpboutik_content_padding', 7 ) . "px;
			--wpb-product-image-radius : calc(var(--wpb-product-border-radius) - var(--wpb-product-image-padding));
			--wpb-gallery-position : " . get_theme_mod( 'wpboutik_gallery_position', - 1 ) . ";
			--wpb-gallery-align : " . get_theme_mod( 'wpboutik_gallery_align', 'start' ) . ";
			--wpb-qty-align : " . get_theme_mod( 'wpboutik_qty_align', 'calc(50% - .5rem)' ) . ";
			--wpb-search-add-to-cart-hover : " . get_theme_mod( 'wpboutik_search_product_add_to_cart_position', '-100%' ) . ";
			--wpb-search-add-to-cart-left : " . ( get_theme_mod( 'wpboutik_search_product_add_to_cart_position', '-100%' ) == '-100%' ? '0' : 'auto' ) . ";
			--wpb-search-add-to-cart-right : " . ( get_theme_mod( 'wpboutik_search_product_add_to_cart_position', '-100%' ) == '-100%' ? 'auto' : '0' ) . ";
		}
		";
		$min           = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_style( 'wpboutik-main-style', WPBOUTIK_URL . 'assets/css/wpb-style' . $min . '.css', array(), WPBOUTIK_VERSION, false );
		wp_add_inline_style( 'wpboutik-main-style', $wpb_variables );

		echo "<style>html{font-size: 100% !important;}</style>";
		if ( ! wpb_current_theme_is_fse_theme() && ! is_wpboutik_shop() && ! is_page( wpboutik_get_page_id( 'cart' ) ) && ! is_page( wpboutik_get_page_id( 'checkout' ) ) && ! is_page( wpboutik_get_page_id( 'account' ) ) && ! is_wpboutik_product() && ! is_wpboutik_product_taxonomy() ) {
			return false;
		}
		wp_enqueue_style( 'wpboutik-css', WPBOUTIK_URL . 'assets/dist/output.css', array(), WPBOUTIK_VERSION, false );
	}

	public static function wpboutik_include_stripe_js() {
		if ( ! is_page( wpboutik_get_page_id( 'checkout' ) ) ) {
			return false;
		}

		$payment_type = wpboutik_get_option_params( 'payment_type' );
		if ( is_string( $payment_type ) ) {
			$payment_type = json_decode( $payment_type );
		}

		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		if ( wpboutik_in_array_r( 'mollie', $payment_type ) ) {
			$mollie_api_key_test = wpboutik_get_option_params( 'mollie_api_key_test' );
			$mollie_api_key_live = wpboutik_get_option_params( 'mollie_api_key_live' );
			if ( ( ! empty ( $mollie_api_key_test ) || ! empty( $mollie_api_key_live ) ) ) {
				wp_enqueue_script( 'molliejs', 'https://js.mollie.com/v1/mollie.js', array(), time(), array( 'in_footer' => true ) );
				wp_enqueue_script( 'wpboutik_mollie_checkoutjs', WPBOUTIK_URL . 'assets/js/checkout_mollie' . $min . '.js', array(
					'jquery',
					'molliejs'
				), '1.0', array( 'in_footer' => true ) );
				wp_localize_script( 'wpboutik_mollie_checkoutjs', 'ajax_var_mollie_checkout', array(
					'url'                                 => admin_url( 'admin-ajax.php' ),
					'mollie_profile_ID'                   => wpboutik_get_option_params( 'mollie_profile_ID' ),
					'mollie_test'                         => wpboutik_get_option_params( 'mollie_test' ),
					'locale'                              => get_locale(),
					'shipping_first_name_required_label'  => __( 'First Name is required', 'wpboutik' ),
					'shipping_last_name_required_label'   => __( 'Last Name is required', 'wpboutik' ),
					'email_address_required_label'        => __( 'Email is required', 'wpboutik' ),
					'shipping_address_required_label'     => __( 'Address is required', 'wpboutik' ),
					'shipping_city_required_label'        => __( 'City is required', 'wpboutik' ),
					'shipping_country_required_label'     => __( 'Country is required', 'wpboutik' ),
					'shipping_postal_code_required_label' => __( 'Postal code is required', 'wpboutik' ),
					'shipping_phone_required_label'       => __( 'Phone is required', 'wpboutik' ),
					'terms_required_label'                => __( 'Please accept the terms', 'wpboutik' ),
					'delivery_method_required_label'      => __( 'Please select a relay point', 'wpboutik' ),
				) );
			}
		}

		/*if ( wpboutik_in_array_r( 'paybox', $payment_type ) ) {
			wp_enqueue_script( 'wpboutik_paybox_checkoutjs', WPBOUTIK_URL . 'assets/js/checkout_paybox' . $min . '.js', array(
				'jquery',
			), '1.0', true );
			wp_localize_script( 'wpboutik_paybox_checkoutjs', 'ajax_var_paybox_checkout', array(
				'url'                                 => admin_url( 'admin-ajax.php' ),
				'locale'                              => get_locale(),
				'shipping_first_name_required_label'  => __( 'First Name is required', 'wpboutik' ),
				'shipping_last_name_required_label'   => __( 'Last Name is required', 'wpboutik' ),
				'email_address_required_label'        => __( 'Email is required', 'wpboutik' ),
				'shipping_address_required_label'     => __( 'Address is required', 'wpboutik' ),
				'shipping_city_required_label'        => __( 'City is required', 'wpboutik' ),
				'shipping_country_required_label'     => __( 'Country is required', 'wpboutik' ),
				'shipping_postal_code_required_label' => __( 'Postal code is required', 'wpboutik' ),
				'shipping_phone_required_label'       => __( 'Phone is required', 'wpboutik' ),
				'terms_required_label'                => __( 'Please accept the terms', 'wpboutik' ),
				'delivery_method_required_label'      => __( 'Please select a relay point', 'wpboutik' ),
			) );
		}*/

		if ( wpboutik_in_array_r( 'card', $payment_type ) ) {
			$stripe_public_key = wpboutik_get_option_params( 'stripe_public_key' );
			$stripe_secret_key = wpboutik_get_option_params( 'stripe_secret_key' );
			if ( ! empty( $stripe_public_key ) && ! empty( $stripe_secret_key ) ) {
				wp_enqueue_script( 'stripejs', 'https://js.stripe.com/v3/', array(), time(), array( 'in_footer' => true ) );
				wp_enqueue_script( 'wpboutik_stripe_checkoutjs', WPBOUTIK_URL . 'assets/js/checkout_stripe' . $min . '.js', array(
					'jquery',
					'stripejs'
				), WPBOUTIK_VERSION, array( 'in_footer' => true ) );
			}
		}

		$currency = get_wpboutik_currency();

		if ( wpboutik_in_array_r( 'paypal', $payment_type ) && ! is_wpboutik_order_received_page() ) {
			$currency_value = '';
			if ( isset( $currency ) ) {
				$currency_value = '&currency=' . $currency;
			}
			$paypal_id = wpboutik_get_option_params( 'paypal_id' );
			if ( ! empty( $paypal_id ) ) {
				wp_enqueue_script( 'paypaljs', 'https://www.paypal.com/sdk/js?client-id=' . esc_attr( $paypal_id ) . '&intent=capture' . $currency_value, array(), time(), array( 'in_footer' => true ) );
				wp_enqueue_script( 'wpboutik_paypal_checkoutjs', WPBOUTIK_URL . 'assets/js/checkout_paypal' . $min . '.js', array(
					'jquery',
					'paypaljs'
				), WPBOUTIK_VERSION, array( 'in_footer' => true ) );
			}
		}

		wp_enqueue_script( 'wpboutik_checkoutjs', WPBOUTIK_URL . 'assets/js/checkout' . $min . '.js', array(
			'jquery'
		), WPBOUTIK_VERSION, array( 'in_footer' => true ) );

		$stripe_public_key = wpboutik_get_option_params( 'stripe_public_key' );

		$method_list     = wpboutik_get_options_shipping_method_list();
		$carriers_boxtal = [];
		if ( $method_list ) {
			foreach ( $method_list as $method_name ) {
				//wpboutik_options_shipping_method_1
				$method = get_option( $method_name );
				if ( isset( $method['boxtal_carrier'] ) && ! empty( $method['boxtal_carrier'] ) ) {
					$carriers_boxtal[] = $method['boxtal_carrier'];
				}
			}
		}

		wp_localize_script( 'wpboutik_checkoutjs', 'ajax_var_checkout', array(
			'url'                                 => admin_url( 'admin-ajax.php' ),
			'currency'                            => $currency,
			'nonce_finish_paypal'                 => wp_create_nonce( 'checkout-paypal-finish-nonce' ),
			'nonce_finish_mollie'                 => wp_create_nonce( 'checkout-mollie-nonce' ),
			'nonce_create_payment_monetico'       => wp_create_nonce( 'checkout-monetico-nonce' ),
			'nonce_create_payment_paybox'         => wp_create_nonce( 'checkout-paybox-nonce' ),
			'nonce_create_order'                  => wp_create_nonce( 'create-order-nonce' ),
			'nonce'                               => wp_create_nonce( 'checkout-nonce' ),
			'nonce_payment_stripe'                => wp_create_nonce( 'wpb-checkout-client-stripe' ),
			'nonce_paypal'                        => wp_create_nonce( 'checkout-nonce-paypal' ),
			'nonce_promo'                         => wp_create_nonce( 'checkout-nonce-remove-promo' ),
			'nonce_finish'                        => wp_create_nonce( 'checkout-finish-nonce' ),
			'nonce_cancel'                        => wp_create_nonce( 'checkout-cancel-nonce' ),
			'nonce_save_data_checkout'            => wp_create_nonce( 'checkout-save-data-nonce' ),
			'stripe_public_key'                   => $stripe_public_key,
			'nonce_boxtal'                        => wp_create_nonce( 'checkout-boxtal-nonce' ),
			'nonce_subscription'                  => wp_create_nonce( 'check-subscription-nonce' ),
			'carriers_boxtal'                     => $carriers_boxtal,
			'shipping_first_name_required_label'  => __( 'First Name is required', 'wpboutik' ),
			'shipping_last_name_required_label'   => __( 'Last Name is required', 'wpboutik' ),
			'email_address_required_label'        => __( 'Email is required', 'wpboutik' ),
			'shipping_address_required_label'     => __( 'Address is required', 'wpboutik' ),
			'shipping_city_required_label'        => __( 'City is required', 'wpboutik' ),
			'shipping_country_required_label'     => __( 'Country is required', 'wpboutik' ),
			'shipping_postal_code_required_label' => __( 'Postal code is required', 'wpboutik' ),
			'shipping_phone_required_label'       => __( 'Phone is required', 'wpboutik' ),
			'terms_required_label'                => __( 'Please accept the terms', 'wpboutik' ),
			'delivery_method_required_label'      => __( 'Please select a relay point', 'wpboutik' ),
			'current_cart_licenses'							  => WPB()->cart->get_licenses(),
		) );
	}

	public static function wpboutik_ajax_add_to_cart_js() {
		/*if ( ! is_wpboutik_shop() && ! is_page( wpboutik_get_page_id( 'cart' ) ) && ! is_page( wpboutik_get_page_id( 'checkout' ) ) && ! is_wpboutik_product() && ! is_tax( 'wpboutik_product_cat' ) ) {
			return false;
		}*/

		$variations = $product_price = $product_first_image = '';
		if ( is_wpboutik_product() ) {
			$product_price = get_post_meta( get_the_ID(), 'price', true );
			$variants      = get_post_meta( get_the_ID(), 'variants', true );
			if ( ! empty( $variants ) && '[]' != $variants ) {
				$variations     = json_decode( $variants );
				$min_max_prices = wpboutik_get_min_max_prices( $variations );

				if ( $min_max_prices['min_price'] != $min_max_prices['max_price'] ) {
					$product_price = wpboutik_format_number( $min_max_prices['min_price'] ) . ' - ' . wpboutik_format_number( $min_max_prices['max_price'] );
				} else {
					$product_price = wpboutik_format_number( $min_max_prices['min_price'] );
				}
			}
			if ( has_post_thumbnail( get_the_ID() ) ) {
				$product_first_image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
			}
		}

		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_script( 'wpboutik_ajax_add_cart', WPBOUTIK_URL . 'assets/js/ajax_add_to_cart' . $min . '.js', array( 'jquery' ), WPBOUTIK_VERSION, array( 'in_footer' => true ) );
		wp_localize_script( 'wpboutik_ajax_add_cart', 'ajax_var', array(
			'url'                            => admin_url( 'admin-ajax.php' ),
			'currency'                       => get_wpboutik_currency_symbol(),
			'currency_name'                  => get_wpboutik_currency(),
			'locale'                         => get_locale(),
			'app_wpboutik'                   => WPBOUTIK_APP_URL,
			'add_to_cart_text'               => __( 'Add to cart', 'wpboutik' ),
			'nonce_count'                    => wp_create_nonce( 'update-total-cart-nonce' ),
			'nonce_licenses_remove'          => wp_create_nonce( 'wpb-stop-licenses' ),
			'nonce_licenses_renew'           => wp_create_nonce( 'wpb-renew-licenses' ),
			'nonce_licenses_remove_url'      => wp_create_nonce( 'wpb-remove-url' ),
			'i18n_make_a_selection_text'     => esc_attr__( 'Please select some product options before adding this product to your cart.', 'wpboutik' ),
			'product_price'                  => $product_price,
			'product_continu_rupture'        => get_post_meta( get_the_ID(), 'continu_rupture', true ),
			'product_price_before_reduction' => get_post_meta( get_the_ID(), 'price_before_reduction', true ),
			'product_sku'                    => get_post_meta( get_the_ID(), 'sku', true ),
			'product_qty'                    => get_post_meta( get_the_ID(), 'qty', true ),
			'product_name'                   => get_the_title( get_the_ID() ),
			'product_first_image'            => $product_first_image,
			'variations'                     => $variations,
			'licenses'						 => get_post_meta( get_the_ID(), 'licenses', true),
			'cart_empty_html'                => __( 'Empty cart', 'wpboutik' ) . '
                    <div class="mt-6 text-center text-sm text-gray-500">
                        <p>
                            <a href="' . esc_url( get_permalink( wpboutik_get_page_id( 'shop' ) ) ) . '"
                               class="wpb-link">' . __( 'Continue Shopping', 'wpboutik' ) . '
                                <span aria-hidden="true"> &rarr;</span>
                            </a>
                        </p>
                    </div>'
		) );
	}

	public static function add_custom_block_category( $categories, $post ) {

		array_unshift( $categories, array(
			'slug'  => 'wpboutik', // Replace with your desired category slug
			'title' => __( 'WPBoutik', 'wpboutik' )
		) );

		return $categories;
	}

	public static function disconnect_project() {
		$method_list = wpboutik_get_options_shipping_method_list();
		foreach ( $method_list as $method_name ) :
			delete_option( $method_name );
		endforeach;

		delete_option( 'wpboutik_options_coupon_list' );
		delete_option( 'wpboutik_options_params' );
		delete_option( 'wpboutik_options_shipping_method_list' );

		$options = get_option( 'wpboutik_options' );
		unset( $options['apikey'] );
		update_option( 'wpboutik_options', $options );
		//delete_option( 'wpboutik_options_first_install' ); => ne pas supprimer
		delete_option( 'wpboutik_options_params' );
		delete_option( 'wpboutik_product_cat_children' );
		delete_option( 'wpboutik_options_google_analytics' );

		delete_transient( 'wpboutik_data_info' );

		flush_rewrite_rules();
		delete_option( 'wpboutik_flush_rewrite_rules_flag' );

		delete_post_meta_by_key( 'wpb_import' );

		$terms = get_terms( array(
			'taxonomy'   => 'wpboutik_product_cat',
			'hide_empty' => false,
		) );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				wp_delete_term( $term->term_id, 'wpboutik_product_cat' );
			}
		}

		$posts = get_posts( array(
			'post_type'      => 'wpboutik_product',
			'posts_per_page' => - 1,
			'fields'         => 'ids',
		) );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post_id ) {
				wp_delete_post( $post_id, true );
			}
		}
	}

}