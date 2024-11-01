<?php

namespace NF\WPBOUTIK;

class Customize {

	use Singleton;

	public function __construct() {
		add_filter( 'plugin_action_links_' . WPBOUTIK_BASENAME, array( __CLASS__, 'plugin_action_links' ) );
		add_action( 'customize_register', array( __CLASS__, 'wpboutik_customize_register' ), 99 );
		add_action( 'customize_controls_enqueue_scripts', array( __CLASS__, 'custom_customize_enqueue' ) );

	}


	public static function plugin_action_links( $links ) {
		if ( is_network_admin() && is_plugin_active_for_network( WPBOUTIK_BASENAME ) ) {
			array_unshift( $links, '<a href="' . esc_url( add_query_arg(
					'autofocus[section]',
					'wpboutik_panel',
					network_admin_url( 'customize.php' )
				) ) . '">' . __( 'Personalization', 'wpboutik' ) . '</a>' );
			array_unshift( $links, '<a href="' . network_admin_url( 'admin.php?page=' . 'wpboutik-settings' ) . '">' . __( 'Settings', 'wpboutik' ) . '</a>' );
		} elseif ( ! is_network_admin() ) {
			array_unshift( $links, '<a href="' . esc_url( add_query_arg(
					'autofocus[section]',
					'wpboutik_panel',
					admin_url( 'customize.php' )
				) ) . '">' . __( 'Personalization', 'wpboutik' ) . '</a>' );
			array_unshift( $links, '<a href="' . admin_url( 'admin.php?page=' . 'wpboutik-settings' ) . '">' . __( 'Settings', 'wpboutik' ) . '</a>' );
		}

		return $links;
	}

	public static function wpboutik_customize_register( $wp_customize ) {
		$menu_locations = get_nav_menu_locations();
		$locations      = [
			'' => sprintf( '&mdash; %s &mdash;', esc_html__( 'Select a Menu' ) )
		];
		foreach ( $menu_locations as $name => $menu ) {
			$locations[ $name ] = $name;
		}

		$wp_customize->add_setting( 'wpboutik_backgroundcolor_button', array(
			'default'   => '#3c54cc',
			'transport' => 'refresh',
		) );

		$wp_customize->add_setting( 'wpboutik_hovercolor_button', array(
			'default'   => '#3043a3',
			'transport' => 'refresh',
		) );

		$wp_customize->add_setting( 'wpboutik_show_cart_dropdown', array(
			'transport' => 'refresh',
		) );

		$wp_customize->add_setting( 'wpboutik_show_cart_icon', array(
			'transport' => 'refresh',
		) );

		$wp_customize->add_setting( 'wpboutik_choose_cart_icon', array(
			'default'   => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag-fill" viewBox="0 0 16 16">
			<path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4z"/>
		</svg>',
			'transport' => 'refresh',
		) );

		$wp_customize->add_setting( 'wpboutik_size_cart_icon', array(
			'default'           => '20',
			'transport'         => 'refresh',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_setting( 'wpboutik_show_breadcrumb', array(
			'default'   => 'yes',
			'transport' => 'refresh',
		) );

		$wp_customize->add_setting( 'wpboutik_show_sidebar', array(
			'default'   => 'hidden',
			'transport' => 'refresh',
		) );

		$wp_customize->add_setting( 'wpboutik_show_archive_sidebar', array(
			'default'   => 'hidden',
			'transport' => 'refresh',
		) );

		$wp_customize->add_setting( 'wpboutik_show_cart_sidebar', array(
			'default'   => 'hidden',
			'transport' => 'refresh',
		) );

		$wp_customize->add_setting( 'wpboutik_title_product_color', array(
			'transport' => 'refresh',
			'default'   => '#000000'
		) );
		$wp_customize->add_setting( 'wpboutik_title_product_color_on_hover', array(
			'transport' => 'refresh',
			'default'   => '#3043a3'
		) );
		$wp_customize->add_setting( 'wpboutik_price_product_color', array(
			'transport' => 'refresh',
			'default'   => '#6b7280'
		) );
		$wp_customize->add_setting( 'wpb_button_text_color', array(
			'transport' => 'refresh',
			'default'   => '#ffffff'
		) );

		$wp_customize->add_panel( 'wpboutik_panel', array(
			'title'    => WPBOUTIK_WHITELABEL,
			'priority' => 300,
		) );

		$wp_customize->add_section( 'wpboutik_prebuilt_theme_section', array(
			'title'       => __( 'Prebuilt theme', 'wpboutik' ),
			'description' => __( 'Apply an atmosphere and a color scheme to your entire store in one click (store page, product sheet, basket and sidebar), you can then modify each element independently in the specific settings.', 'wpboutik' ),
			'panel'       => 'wpboutik_panel',
		) );
		$wp_customize->add_setting( 'wpb_theme_color_generator', array(
			'transport' => 'refresh',
		) );
		// WP_Customize_Theme_Colors
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpb_theme_color_generator',
				array(
					'type'        => 'text',
					'section'     => 'wpboutik_text_section',
					'description' => __( 'Explore our various presentation proposals, with the ability to customize each element in the dedicated tabs of WPBoutik.', 'wpboutik' ),
				)
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpb_theme_color_generator',
				array(
					'type'    => 'line',
					'section' => 'wpboutik_text_section',
				)
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Theme_Colors(
				$wp_customize,
				'wpb_theme_color_generator',
				array(
					'label'       => __( 'Colors theme', 'wpboutik' ),
					'section'     => 'wpboutik_prebuilt_theme_section',
					'description' => __( 'Choose or create a colors theme for your shop.', 'wpboutik' ),
				)
			)
		);

		$wp_customize->add_section( 'wpboutik_text_section', array(
			'title'       => __( 'Global Design', 'wpboutik' ),
			'description' => __( 'Personalize your product titles and prices: sizes, colors. As well as your main buttons and breadcrumbs.', 'wpboutik' ),
			'panel'       => 'wpboutik_panel',
		) );
		/*$wp_customize->add_setting( 'wpboutik_general_font_size', array(
			'default'           => '14',
			'sanitize_callback' => 'absint',
		) );*/

		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpb_general_text_global_info',
				array(
					//'label'       => __( 'Generals settings', 'wpboutik' ),
					'type'        => 'heading',
					'section'     => 'wpboutik_text_section',
					'description' => __( 'Default values for text styling.', 'wpboutik' ),
				)
			)
		);
		/*$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_general_font_size',
				array(
					'label'       => __( 'General font size', 'wpboutik' ),
					'section'     => 'wpboutik_text_section',
					'settings'    => 'wpboutik_general_font_size',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 10,
						'max'  => 25,
						'step' => 1,
					),
				)
			)
		);*/
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpb_general_text_heading_info',
				array(
					'label'       => __( 'Colors of titles and prices', 'wpboutik' ),
					'type'        => 'heading',
					'section'     => 'wpboutik_text_section',
					'description' => __( 'For styling title display.', 'wpboutik' ),
				)
			)
		);
		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'wpboutik_title_product_color', array(
			'label'    => __( 'Title product color', 'wpboutik' ),
			'section'  => 'wpboutik_text_section',
			'settings' => 'wpboutik_title_product_color',
		) ) );
		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'wpboutik_title_product_color_on_hover', array(
			'label'    => __( 'Title product color on hover', 'wpboutik' ),
			'section'  => 'wpboutik_text_section',
			'settings' => 'wpboutik_title_product_color_on_hover',
		) ) );
		/*$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpb_general_text_prices_info',
				array(
					//'label'       => __( 'Prices', 'wpboutik' ),
					'type'        => 'heading',
					'section'     => 'wpboutik_text_section',
					'description' => __( 'For styling prices display.', 'wpboutik' ),
				)
			)
		);*/
		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'wpboutik_price_product_color', array(
			'label'    => __( 'Price product color', 'wpboutik' ),
			'section'  => 'wpboutik_text_section',
			'settings' => 'wpboutik_price_product_color',
		) ) );

		/*$wp_customize->add_section( 'wpboutik_btn_section', array(
			'title'       => __( 'Buttons', 'wpboutik' ),
			'description' => __( 'Customize the store buttons appearance', 'wpboutik' ),
			'panel'       => 'wpboutik_panel',
		) );*/
		// Ajouter un champ pour personnaliser la couleur du texte des boutons

		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpb_general_text_label_info',
				array(
					'label'   => __( 'Buttons', 'wpboutik' ),
					'type'    => 'heading',
					'section' => 'wpboutik_text_section',
					//'description' => __( 'boutons description', 'wpboutik' ),
				)
			)
		);

		$wp_customize->add_setting( 'wpboutik_button_text_color', array(
			'default'           => '#ffffff',
			'sanitize_callback' => 'sanitize_hex_color',
		) );
		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'wpboutik_button_text_color', array(
			'label'    => __( 'Text color of add to cart buttons', 'wpboutik' ),
			'section'  => 'wpboutik_text_section',
			'settings' => 'wpboutik_button_text_color',
		) ) );
		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'wpboutik_backgroundcolor_button', array(
			'label'    => __( 'Color of the buttons (and after visit)', 'wpboutik' ),
			'section'  => 'wpboutik_text_section',
			'settings' => 'wpboutik_backgroundcolor_button',
		) ) );

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'wpboutik_hovercolor_button', array(
			'label'    => __( 'Button color on hover', 'wpboutik' ),
			'section'  => 'wpboutik_text_section',
			'settings' => 'wpboutik_hovercolor_button',
		) ) );
		// Ajouter un champ pour personnaliser la taille de la police des boutons
		$wp_customize->add_setting( 'wpboutik_button_font_size', array(
			'default'           => '16',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_button_font_size',
				array(
					'label'       => __( 'Font size “Add to cart” button', 'wpboutik' ),
					'section'     => 'wpboutik_text_section',
					'settings'    => 'wpboutik_button_font_size',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 10,
						'max'  => 23,
						'step' => 1,
					),
				)
			)
		);
		$wp_customize->add_setting( "wpboutik_button_border_radius", array(
			'default'   => 6,
			'transport' => 'refresh',
		) );
		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_button_border_radius',
				array(
					'label'       => __( 'Button border radius', 'wpboutik' ),
					'section'     => 'wpboutik_text_section',
					'settings'    => 'wpboutik_button_border_radius',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 40,
						'step' => 1,
					),
				)
			)
		);

// ******************************************************************************** ARCHIVE
		$wp_customize->add_section( 'wpboutik_archive_section', array(
			'title'       => __( 'Shop Page and Product Categories', 'wpboutik' ),
			'description' => __( 'Find here all the settings for your Shop Page and Product Categories (archive): Initial display, number of columns, colors, border radius, display mode, labels, etc...', 'wpboutik' ),
			'panel'       => 'wpboutik_panel',
		) );

		$wp_customize->add_setting( 'wpboutik_display_shop_page', array(
			'transport' => 'refresh',
			'default'   => 'product',
		) );

		$wp_customize->add_control( 'wpb_display_shop_page', array(
			'type'     => 'radio',
			'label'    => __( 'The store page displays:', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'settings' => 'wpboutik_display_shop_page',
			'choices'  => array(
				'product'                 => __( 'Products', 'wpboutik' ),
				'product_cat'             => __( 'Products categories', 'wpboutik' ),
				'product_cat_and_product' => __( 'Products categories and Products', 'wpboutik' ),
			),
		) );

		$wp_customize->add_setting( 'wpboutik_show_second_image_product', array(
			'default'   => 'no',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpb_show_second_image_product', array(
			'label'    => __( 'Show a second product image on hover', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'settings' => 'wpboutik_show_second_image_product',
			'type'     => 'radio',
			'choices'  => array(
				'yes' => __( 'Yes', 'wpboutik' ),
				'no'  => __( 'No', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_setting( 'wpboutik_show_out_stock_product', array(
			'default'   => 'yes',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpb_show_out_stock_product', array(
			'label'    => __( 'Show out of stock products', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'settings' => 'wpboutik_show_out_stock_product',
			'type'     => 'radio',
			'choices'  => array(
				'yes' => __( 'Yes', 'wpboutik' ),
				'no'  => __( 'No', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_setting( 'wpboutik_archive_col_number_desktop', array(
			'default'           => '4',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_archive_col_number_desktop',
				array(
					'label'       => __( 'Number of columns to display', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'settings'    => 'wpboutik_archive_col_number_desktop',
					'input_attrs' => array(
						'min'  => 1,
						'max'  => 6,
						'step' => 1,
					),
				)
			)
		);

		$wp_customize->add_setting( 'wpboutik_archive_col_number_mobile', array(
			'default'           => '2',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_archive_col_number_mobile',
				array(
					'label'       => __( 'Number of columns to display for mobile or tablet', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'settings'    => 'wpboutik_archive_col_number_mobile',
					'input_attrs' => array(
						'min'  => 1,
						'max'  => 6,
						'step' => 1,
					),
				)
			)
		);

		$wp_customize->add_setting( 'wpboutik_archive_col_spacing', array(
			'default'           => '15',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_archive_col_spacing',
				array(
					'label'       => __( 'Margin between columns', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'settings'    => 'wpboutik_archive_col_spacing',
					'description' => __( 'Size of margin between elements in archive page in px.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				)
			)
		);

		$wp_customize->add_setting( 'wpboutik_backgroundcolor_products', array(
			'transport' => 'refresh',
		) );

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'wpboutik_backgroundcolor_products', array(
			'label'    => __( 'Background color of products', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'settings' => 'wpboutik_backgroundcolor_products',
		) ) );
		$wp_customize->add_setting( 'wpboutik_product_direction', array(
			'default'   => 'column',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_product_direction', array(
			'label'    => __( 'Product display mode', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'settings' => 'wpboutik_product_direction',
			'type'     => 'radio',
			'choices'  => array(
				'column' => __( 'Vertical', 'wpboutik' ),
				'row'    => __( 'Horizontal', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'heading',
					'section' => 'wpboutik_archive_section',
					'label'   => __( 'Border\'s settings.', 'wpboutik' ),
				)
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'line',
					'section' => 'wpboutik_archive_section',
				)
			)
		);
		$wp_customize->add_setting( 'wpboutik_bordercolor_products', array(
			'transport' => 'refresh',
			'default'   => '#e5e7eb'
		) );

		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'wpboutik_bordercolor_products', array(
			'label'    => __( 'Product border color', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'settings' => 'wpboutik_bordercolor_products',
		) ) );

		$wp_customize->add_setting( 'wpboutik_borderradius_products', array(
			'default'           => '20',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_borderradius_products',
				array(
					'label'       => __( 'Product\'s card border radius', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'settings'    => 'wpboutik_borderradius_products',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 35,
						'step' => 1,
					),
				)
			)
		);

		$wp_customize->add_setting( 'wpboutik_borderweight_products', array(
			'default'           => '1',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_borderweight_products',
				array(
					'label'       => __( 'Product\'s card border weight', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'settings'    => 'wpboutik_borderweight_products',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 20,
						'step' => 1,
					),
				)
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'heading',
					'section' => 'wpboutik_archive_section',
					'label'   => __( 'Image\'s settings.', 'wpboutik' ),
				)
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'line',
					'section' => 'wpboutik_archive_section',
				)
			)
		);
		$wp_customize->add_setting( 'wpboutik_archive_image_format', array(
			'default'   => '16/9',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_archive_image_format', array(
			'label'    => __( 'Product image display format', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'settings' => 'wpboutik_archive_image_format',
			'type'     => 'radio',
			'choices'  => array(
				'1/1'  => __( 'Square', 'wpboutik' ),
				'16/9' => __( 'Landscape 16:9', 'wpboutik' ),
				'3/2'  => __( 'Landscape 3:2', 'wpboutik' ),
				'9/16' => __( 'Portrait 9:16', 'wpboutik' ),
				'2/3'  => __( 'Portrait 2:3', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_setting( 'wpboutik_archive_image_fill', array(
			'default'   => 'contain',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_archive_image_fill', array(
			'label'    => __( 'Image display mode', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'settings' => 'wpboutik_archive_image_fill',
			'type'     => 'radio',
			'choices'  => array(
				'contain' => __( 'Contain', 'wpboutik' ),
				'cover'   => __( 'Cover', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_setting( 'wpboutik_image_padding', array(
			'default'           => '7',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_image_padding',
				array(
					'label'       => __( 'Padding arround image', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'settings'    => 'wpboutik_image_padding',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 60,
						'step' => 1,
					),
				)
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'heading',
					'section' => 'wpboutik_archive_section',
					'label'   => __( 'Content\'s settings.', 'wpboutik' ),
				)
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'line',
					'section' => 'wpboutik_archive_section',
				)
			)
		);

		$wp_customize->add_setting( 'wpboutik_content_padding', array(
			'default'           => '7',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_content_padding',
				array(
					'label'       => __( 'Padding arround content', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'settings'    => 'wpboutik_content_padding',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 60,
						'step' => 1,
					),
				)
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'heading',
					'section' => 'wpboutik_archive_section',
					'label'   => __( 'Product title\'s settings.', 'wpboutik' ),
				)
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'line',
					'section' => 'wpboutik_archive_section',
				)
			)
		);

		$wp_customize->add_setting( 'wpboutik_archive_product_title_font_size', array(
			'default'           => '20',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpb_archive_product_title_font_size',
				array(
					'label'       => __( 'Product title font size', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'settings'    => 'wpboutik_archive_product_title_font_size',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 10,
						'max'  => 35,
						'step' => 1,
					),
				)
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'heading',
					'section' => 'wpboutik_archive_section',
					'label'   => __( 'Label\'s settings.', 'wpboutik' ),
				)
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'line',
					'section' => 'wpboutik_archive_section',
				)
			)
		);

		$wp_customize->add_setting( 'wpboutik_archive_price_font_size', array(
			'default'           => '16',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpb_archive_price_font_size',
				array(
					'label'       => __( 'Product price font size', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'settings'    => 'wpboutik_archive_price_font_size',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 10,
						'max'  => 35,
						'step' => 1,
					),
				)
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'heading',
					'section' => 'wpboutik_archive_section',
					'label'   => __( 'Excerpt\'s settings.', 'wpboutik' ),
				)
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpboutik_archive_section',
				array(
					'type'    => 'line',
					'section' => 'wpboutik_archive_section',
				)
			)
		);

		$wp_customize->add_setting( 'wpboutik_archive_have_excerpt', array(
				'default' => false,
			)
		);
		$wp_customize->add_control( 'wpboutik_archive_have_excerpt', array(
			'settings' => 'wpboutik_archive_have_excerpt',
			'label'    => __( 'Show product description', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'type'     => 'checkbox',
		) );
		$wp_customize->add_setting( 'wpboutik_archive_have_excerpt_mobile', array(
				'default' => false,
			)
		);
		$wp_customize->add_control( 'wpboutik_archive_have_excerpt_mobile', array(
			'settings' => 'wpboutik_archive_have_excerpt_mobile',
			'label'    => __( 'Hide product description on small devices', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'type'     => 'checkbox',
		) );
		$wp_customize->add_setting( 'wpboutik_archive_excerpt_size', array(
				'default' => 14,
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_archive_excerpt_size',
				array(
					'label'       => __( 'Excerpt font\'s size', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'settings'    => 'wpboutik_archive_excerpt_size',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 7,
						'max'  => 25,
						'step' => 1,
					),
				)
			)
		);
		$wp_customize->add_setting( 'wpboutik_archive_excerpt_length', array(
				'default' => 55,
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_archive_excerpt_length',
				array(
					'label'       => __( 'Length of short description', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'settings'    => 'wpboutik_archive_excerpt_length',
					'description' => __( 'Number of words to show in the excerpt.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 55,
						'step' => 1,
					),
				)
			)
		);

		$wp_customize->add_setting( 'wpboutik_color_labels', array(
			'default'   => '#166534',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'wpboutik_color_labels', array(
			'label'    => __( 'Color of labels (Out of stock, promo, etc.)', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'settings' => 'wpboutik_color_labels',
		) ) );
		$wp_customize->add_setting( 'wpboutik_background_color_labels', array(
			'default'   => '#dcfce7',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'wpboutik_background_color_labels', array(
			'label'    => __( 'Background-color of labels (Out of stock, promo, etc.)', 'wpboutik' ),
			'section'  => 'wpboutik_archive_section',
			'settings' => 'wpboutik_background_color_labels',
		) ) );
		$wp_customize->add_setting( 'wpboutik_font_size_labels', array(
			'default'   => '14',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_font_size_labels',
				array(
					'label'       => __( 'Label font size (Out of stock, promo, etc.)', 'wpboutik' ),
					'section'     => 'wpboutik_archive_section',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'settings'    => 'wpboutik_font_size_labels',
					'input_attrs' => array(
						'min'  => 7,
						'max'  => 25,
						'step' => 1,
					),
				)
			)
		);

// ******************************************************************************** ARCHIVE


// ******************************************************************************** SINGLE

		$wp_customize->add_section( 'wpboutik_single_section', array(
			'title'       => __( 'Products Page', 'wpboutik' ),
			'description' => __( 'Find here all the settings for your Product Page (single product): Font size, gallery replacement, alignments, labels, related products, etc...', 'wpboutik' ),
			'panel'       => 'wpboutik_panel',
		) );

		$wp_customize->add_setting( 'wpboutik_single_product_title_font_size', array(
			'default'           => '20',
			'sanitize_callback' => 'absint',
		) );
		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_single_product_title_font_size',
				array(
					'label'       => __( 'Single product title font size', 'wpboutik' ),
					'section'     => 'wpboutik_single_section',
					'settings'    => 'wpboutik_single_product_title_font_size',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 10,
						'max'  => 35,
						'step' => 1,
					),
				)
			)
		);
		$wp_customize->add_setting( 'wpboutik_single_price_font_size', array(
			'default'           => '18',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_single_price_font_size',
				array(
					'label'       => __( 'Single price font size', 'wpboutik' ),
					'section'     => 'wpboutik_single_section',
					'settings'    => 'wpboutik_single_price_font_size',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 10,
						'max'  => 35,
						'step' => 1,
					),
				)
			)
		);
		$wp_customize->add_setting( 'wpboutik_single_description_size', array(
			'default' => 14,
		) );
		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_single_description_size',
				array(
					'label'       => __( 'Long description text size', 'wpboutik' ),
					'section'     => 'wpboutik_single_section',
					'settings'    => 'wpboutik_single_description_size',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 7,
						'max'  => 25,
						'step' => 1,
					),
				)
			) );
		$wp_customize->add_setting( 'wpboutik_single_excerpt_size', array(
			'default' => 14,
		) );
		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_single_excerpt_size',
				array(
					'label'       => __( 'Excerpt font\'s size', 'wpboutik' ),
					'section'     => 'wpboutik_single_section',
					'settings'    => 'wpboutik_single_excerpt_size',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 7,
						'max'  => 25,
						'step' => 1,
					),
				)
			) );
		$wp_customize->add_setting( 'wpboutik_single_have_excerpt', array(
			'default' => true,
		) );
		$wp_customize->add_control( 'wpboutik_single_have_excerpt', array(
			'settings' => 'wpboutik_single_have_excerpt',
			'label'    => __( 'Show product description', 'wpboutik' ),
			'section'  => 'wpboutik_single_section',
			'type'     => 'checkbox',
		) );

		$wp_customize->add_setting( 'wpboutik_slideshow_images', array(
			'transport' => 'refresh',
			'default'   => true
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_slideshow_images', array(
			'label'    => __( 'Replace the “product image” gallery with a slideshow', 'wpboutik' ),
			'section'  => 'wpboutik_single_section',
			'settings' => 'wpboutik_slideshow_images',
			'type'     => 'checkbox',
		) ) );

		$wp_customize->add_setting( 'wpboutik_share_social', array(
			'transport' => 'refresh',
			'default'   => false
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_share_social', array(
			'label'    => __( 'Show social sharing buttons', 'wpboutik' ),
			'section'  => 'wpboutik_single_section',
			'settings' => 'wpboutik_share_social',
			'type'     => 'checkbox',
		) ) );

		$wp_customize->add_setting( 'wpboutik_gallery_position', array(
			'default'   => '-1',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_gallery_position', array(
			'label'    => __( 'Gallery position', 'wpboutik' ),
			'section'  => 'wpboutik_single_section',
			'settings' => 'wpboutik_gallery_position',
			'type'     => 'radio',
			'choices'  => array(
				'-1' => __( 'Left', 'wpboutik' ),
				'2'  => __( 'Right', 'wpboutik' ),
			),
		) ) );

		/*$wp_customize->add_setting( 'wpboutik_single_excerpt_length', array(
			'default' => 55,
		) );
		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_single_excerpt_length',
				array(
					'label'       => __( 'Excerpt length', 'wpboutik' ),
					'section'     => 'wpboutik_single_section',
					'settings'    => 'wpboutik_single_excerpt_length',
					'description' => __( 'Number of words to show in the excerpt.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 0,
						'max'  => 55,
						'step' => 1,
					),
				)
			) );*/


		$wp_customize->add_setting( 'wpboutik_gallery_align', array(
			'default'   => 'start',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_gallery_align', array(
			'label'    => __( 'Content Alignment', 'wpboutik' ),
			'section'  => 'wpboutik_single_section',
			'settings' => 'wpboutik_gallery_align',
			'type'     => 'radio',
			'choices'  => array(
				'start'  => __( 'Top', 'wpboutik' ),
				'center' => __( 'Middle', 'wpboutik' ),
				'end'    => __( 'Bottom', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_setting( 'wpboutik_qty_align', array(
			'default'   => 'calc(50% - .5rem)',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_qty_align', array(
			'label'    => __( 'Quantity Alignment', 'wpboutik' ),
			'section'  => 'wpboutik_single_section',
			'settings' => 'wpboutik_qty_align',
			'type'     => 'radio',
			'choices'  => array(
				'100%'              => __( 'Column', 'wpboutik' ),
				'calc(50% - .5rem)' => __( 'Row', 'wpboutik' )
			),
		) ) );

		$wp_customize->add_setting( 'wpboutik_single_have_cat', array(
			'default' => false,
		) );
		$wp_customize->add_control( 'wpboutik_single_have_cat', array(
			'settings' => 'wpboutik_single_have_cat',
			'label'    => __( 'Show labels Product categories', 'wpboutik' ),
			'section'  => 'wpboutik_single_section',
			'type'     => 'checkbox',
		) );

		$wp_customize->add_setting( 'wpboutik_cat_display', array(
			'default'   => 'wpb-btn',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_cat_display', array(
			'label'    => __( 'Show Categories labels as:', 'wpboutik' ),
			'section'  => 'wpboutik_single_section',
			'settings' => 'wpboutik_cat_display',
			'type'     => 'radio',
			'choices'  => array(
				'wpb-btn'  => __( 'Button', 'wpboutik' ),
				'wpb-link' => __( 'Link', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_setting( 'wpboutik_details_display', array(
			'default'   => 'product-tabs',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_details_display', array(
			'label'    => __( 'Display of contents (Description, Additional Information and Reviews):', 'wpboutik' ),
			'section'  => 'wpboutik_single_section',
			'settings' => 'wpboutik_details_display',
			'type'     => 'radio',
			'choices'  => array(
				'product-tabs'		 => __( 'In tab', 'wpboutik' ),
				'product-tabs-list' => __( 'After the content', 'wpboutik' )
			),
		) ) );

		$wp_customize->add_setting( 'wpboutik_show_related_products', array(
			'default'   => 'yes',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpb_show_related_products', array(
			'label'    => __( 'Show related products', 'wpboutik' ),
			'section'  => 'wpboutik_single_section',
			'settings' => 'wpboutik_show_related_products',
			'type'     => 'radio',
			'choices'  => array(
				'yes' => __( 'Yes', 'wpboutik' ),
				'no'  => __( 'No', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_setting( 'wpboutik_number_related_products', array(
			'default'   => 4,
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_number_related_products', array(
			'label'       => __( 'Number of related products', 'wpboutik' ),
			'section'     => 'wpboutik_single_section',
			'settings'    => 'wpboutik_number_related_products',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 1,
				'max'  => 6,
				'step' => 1,
			),
		) ) );


// ******************************************************************************** SINGLE


		$wp_customize->add_section( 'wpboutik_order_section', array(
			'title' => __( 'Order', 'wpboutik' ),
			'panel' => 'wpboutik_panel',
		) );

		$wp_customize->add_section( 'wpboutik_sidebar_section', array(
			'title' => __( 'Sidebar', 'wpboutik' ),
			'panel' => 'wpboutik_panel',
		) );

		$wp_customize->add_section( 'wpboutik_cart_section', array(
			'title' => __( 'Shopping cart icon', 'wpboutik' ),
			'panel' => 'wpboutik_panel',
		) );

		$wp_customize->add_section( 'wpboutik_searchproduct_section', array(
			'title' => __( 'Product Search', 'wpboutik' ),
			'panel' => 'wpboutik_panel',
		) );

		$desc_links   = '<p>' . __( 'Your WPBoutik settings', 'wpboutik' ) . '</p>';
		$desc_links   .= '<ul>';
		$desc_links   .= '<li>' . __( 'Adjust your WPBoutik sidebar widgets', 'wpboutik' ) . ' : <a href="/wp-admin/widgets.php" target="_blank">Widgets</a></li>';
		$desc_links   .= '<li>' . __( 'Adjust your WPBoutik pages', 'wpboutik' ) . ' : <a href="/wp-admin/admin.php?page=wpboutik-settings" target="_blank">' . __( 'Settings', 'wpboutik' ) . '</a></li>';
		$desc_links   .= '<li>' . __( 'Need help with setup', 'wpboutik' ) . ' : <a href="' . esc_url( WPBOUTIK_DOC_URL ) . '" target="_blank">' . __( 'See the documentation', 'wpboutik' ) . '</a></li>';
		$language     = wpboutik_get_option_params( 'language' );
		$project_slug = wpboutik_get_option_params( 'project_slug' );
		if ( ! empty( $project_slug ) ) {
			$desc_links .= '<li>' . __( 'Modify your options, products, etc.', 'wpboutik' ) . ' : <a class="external-link" href="' . esc_url( WPBOUTIK_APP_URL . $language . '/' . $project_slug . '/products' ) . '" target="_blank">' . esc_html__( 'Edit my products', 'wpboutik' ) . '</a></li>';
		}
		$desc_links .= '<li>' . __( 'Access your WPBoutik account', 'wpboutik' ) . ' : <a class="external-link" href="' . esc_url( WPBOUTIK_APP_URL . $language . '/' . $project_slug . '/profile' ) . '" target="_blank">' . esc_html__( 'View my profile', 'wpboutik' ) . '</a></li>';
		$desc_links .= '</ul>';

		$wp_customize->add_section( 'wpboutik_link_section', array(
			'title'       => __( 'Links', 'wpboutik' ),
			'description' => $desc_links,
			'panel'       => 'wpboutik_panel',
		) );


		$wp_customize->add_setting( 'wpboutik_shop_page_id', array(
			'transport' => 'refresh',
		) );

		$wp_customize->add_control( 'wpb_link_shop_page', array(
			'label'    => __( 'Shop page', 'wpboutik' ),
			'section'  => 'wpboutik_link_section',
			'settings' => 'wpboutik_shop_page_id',
			'type'     => 'dropdown-pages'
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpb_show_cart_dropdown', array(
			'label'    => __( 'Display the cart in submenu of the cart link', 'wpboutik' ),
			'section'  => 'wpboutik_cart_section',
			'settings' => 'wpboutik_show_cart_dropdown',
			'type'     => 'checkbox',
		) ) );

		$wp_customize->add_setting( "wpboutik_mini_cart_menu", array(
			'default'   => '',
			'transport' => 'refresh',
		) );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_mini_cart_menu', array(
			'label'    => __( 'Select the menu', 'wpboutik' ),
			'section'  => 'wpboutik_cart_section',
			'settings' => 'wpboutik_mini_cart_menu',
			'type'     => 'select',
			'choices'  => $locations,
		) ) );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpb_show_cart_icon', array(
			'label'    => __( 'Display a visual icon for the cart', 'wpboutik' ),
			'section'  => 'wpboutik_cart_section',
			'settings' => 'wpboutik_show_cart_icon',
			'type'     => 'checkbox',
		) ) );

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpb_choose_cart_icon', array(
			'label'       => __( 'Choice icon', 'wpboutik' ),
			'section'     => 'wpboutik_cart_section',
			'settings'    => 'wpboutik_choose_cart_icon',
			'type'        => 'radio',
			'choices'     => array(
				'<svg xmlns="http://www.w3.org/2000/svg" style="width:1.2em;height:1.2em" fill="currentColor" viewBox="0 0 16 16">
				<path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4z"/>
			</svg>'     => 'Cart',
				'<svg xmlns="http://www.w3.org/2000/svg" style="width:1.2em;height:1.2em" fill="currentColor" viewBox="0 0 16 16">
				<path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
			</svg>'     => 'Cart',
				'<svg xmlns="http://www.w3.org/2000/svg" style="width:1.2em;height:1.2em" fill="currentColor" viewBox="0 0 16 16">
				<path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9zM1 7v1h14V7zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5"/>
			</svg>' => 'basket',
				'<svg xmlns="http://www.w3.org/2000/svg" style="width:1.2em;height:1.2em" fill="currentColor" viewBox="0 0 16 16">
				<path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
			</svg>' => 'Products',
				'<svg xmlns="http://www.w3.org/2000/svg"" style="width:1.2em;height:1.2em" fill="currentColor" viewBox="0 0 16 16">
				<path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
			</svg>' => 'Products',
			),
			'input_attrs' => array(
				'class' => 'icon-radio',
			),
		) ) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpb_size_cart_icon',
				array(
					'label'       => __( 'Size cart icon menu', 'wpboutik' ),
					'section'     => 'wpboutik_cart_section',
					'settings'    => 'wpboutik_size_cart_icon',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 12,
						'max'  => 30,
						'step' => 1,
					),
				)
			)
		);

		$wp_customize->add_setting( "wpboutik_search_product", array(
			'default'   => 'yes',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpb_search_product', array(
			'label'    => __( 'Show and activate the product research', 'wpboutik' ),
			'section'  => 'wpboutik_searchproduct_section',
			'settings' => 'wpboutik_search_product',
			'type'     => 'radio',
			'choices'  => array(
				'yes' => __( 'Yes', 'wpboutik' ),
				'no'  => __( 'No', 'wpboutik' ),
			),
		) ) );
		$wp_customize->add_setting( "wpboutik_search_product_show_input", array(
			'default'   => '',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_search_product_show_input', array(
			'label'    => __( 'Show search', 'wpboutik' ),
			'section'  => 'wpboutik_searchproduct_section',
			'settings' => 'wpboutik_search_product_show_input',
			'type'     => 'radio',
			'choices'  => array(
				''               => __( 'Click on the magnifying glass', 'wpboutik' ),
				' visible-input' => __( 'Directly in the menu', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_setting( "wpboutik_search_product_menu", array(
			'default'   => '',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpb_search_product_menu', array(
			'label'    => __( 'Select the menu where the search is visible', 'wpboutik' ),
			'section'  => 'wpboutik_searchproduct_section',
			'settings' => 'wpboutik_search_product_menu',
			'type'     => 'select',
			'choices'  => $locations,
		) ) );

		$wp_customize->add_setting( "wpboutik_search_product_add_to_cart", array(
			'default'   => 'yes',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_search_product_add_to_cart', array(
			'label'    => __( 'Add to cart when result is hovered.', 'wpboutik' ),
			'section'  => 'wpboutik_searchproduct_section',
			'settings' => 'wpboutik_search_product_add_to_cart',
			'type'     => 'radio',
			'choices'  => array(
				'yes' => __( 'Yes', 'wpboutik' ),
				'no'  => __( 'No', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_setting( "wpboutik_search_product_add_to_cart_position", array(
			'default'   => '-100%',
			'transport' => 'refresh',
		) );
		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpboutik_search_product_add_to_cart_position', array(
			'label'    => __( 'Add to cart position', 'wpboutik' ),
			'section'  => 'wpboutik_searchproduct_section',
			'settings' => 'wpboutik_search_product_add_to_cart_position',
			'type'     => 'radio',
			'choices'  => array(
				'-100%' => __( 'Left', 'wpboutik' ),
				'100%'  => __( 'Right', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_control(
			new WP_Customize_Html_Area(
				$wp_customize,
				'wpb_general_breadcrumb',
				array(
					'label'   => __( 'Breadcrumb', 'wpboutik' ),
					'type'    => 'heading',
					'section' => 'wpboutik_text_section',
				)
			)
		);

		$wp_customize->add_control( new \WP_Customize_Control( $wp_customize, 'wpb_show_breadcrumb', array(
			'label'    => __( 'Show breadcrumb', 'wpboutik' ),
			'section'  => 'wpboutik_text_section',
			'settings' => 'wpboutik_show_breadcrumb',
			'type'     => 'radio',
			'choices'  => array(
				'yes' => __( 'Yes', 'wpboutik' ),
				'no'  => __( 'No', 'wpboutik' ),
			),
		) ) );

		$wp_customize->add_setting( 'wpboutik_default_image', array(
			'transport' => 'refresh',
		) );
		$wp_customize->add_control(
			new \WP_Customize_Image_Control(
				$wp_customize,
				'wpb_default_image',
				array(
					'label'    => __( 'Upload a default product image', 'wpboutik' ),
					'section'  => 'wpboutik_text_section',
					'settings' => 'wpboutik_default_image',
					'context'  => 'your_setting_context'
				)
			)
		);

		$wp_customize->add_control( 'wpboutik_show_sidebar', array(
			'type'     => 'radio',
			'label'    => __( 'Choose where your sidebar product page will appear', 'wpboutik' ),
			'section'  => 'wpboutik_sidebar_section',
			'settings' => 'wpboutik_show_sidebar',
			'choices'  => array(
				'hidden' => __( 'Hidden', 'wpboutik' ),
				'left'   => __( 'Left', 'wpboutik' ),
				'right'  => __( 'Right', 'wpboutik' ),
			),
		) );

		$wp_customize->add_control( 'wpboutik_show_archive_sidebar', array(
			'type'     => 'radio',
			'label'    => __( 'Choose where your sidebar archive pages will appear', 'wpboutik' ),
			'section'  => 'wpboutik_sidebar_section',
			'settings' => 'wpboutik_show_archive_sidebar',
			'choices'  => array(
				'hidden' => __( 'Hidden', 'wpboutik' ),
				'left'   => __( 'Left', 'wpboutik' ),
				'right'  => __( 'Right', 'wpboutik' ),
			),
		) );

		$wp_customize->add_control( 'wpboutik_show_cart_sidebar', array(
			'type'     => 'radio',
			'label'    => __( 'Choose where your sidebar cart page will appear', 'wpboutik' ),
			'section'  => 'wpboutik_sidebar_section',
			'settings' => 'wpboutik_show_cart_sidebar',
			'choices'  => array(
				'hidden' => __( 'Hidden', 'wpboutik' ),
				'left'   => __( 'Left', 'wpboutik' ),
				'right'  => __( 'Right', 'wpboutik' ),
			),
		) );

		$wp_customize->add_setting( 'wpboutik_title_widget_font_size', array(
			'default'           => '30',
			'sanitize_callback' => 'absint',
		) );

		$wp_customize->add_control(
			new WP_Customize_Range_Control(
				$wp_customize,
				'wpboutik_title_widget_font_size',
				array(
					'label'       => __( 'Size of widget titles', 'wpboutik' ),
					'section'     => 'wpboutik_sidebar_section',
					'settings'    => 'wpboutik_title_widget_font_size',
					'description' => __( 'The measurement is in pixels.', 'wpboutik' ),
					'input_attrs' => array(
						'min'  => 10,
						'max'  => 45,
						'step' => 1,
					),
				)
			)
		);
		$wp_customize->add_setting( 'wpboutik_title_widget_color', array(
			'default' => '#333',
		) );
		$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, 'wpboutik_title_widget_color', array(
			'label'    => __( 'Title widget color', 'wpboutik' ),
			'section'  => 'wpboutik_sidebar_section',
			'settings' => 'wpboutik_title_widget_color',
		) ) );
	}

	/**
	 * Enqueue script for custom customize control.
	 */
	public static function custom_customize_enqueue() {
		$min    = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_script( 'wpboutik-custom-customize', WPBOUTIK_URL . 'assets/js/custom.customize' . $min . '.js', array(
			'jquery',
			'customize-controls'
		), WPBOUTIK_VERSION, true );
		$stripe_public_key = wpboutik_get_option_params( 'stripe_public_key' );

		$args = array(
			'post_type'      => 'wpboutik_product', // Assurez-vous que c'est le bon type de post pour vos produits
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$latest_product = get_posts( $args );
		wp_localize_script( 'wpboutik-custom-customize', 'wpboutik', array(
			'url'                 => get_permalink( wpboutik_get_page_id( 'shop' ) ),
			'url_sidebar_section' => ( $latest_product ) ? get_permalink( $latest_product[0]->ID ) : '',
		) );
	}
}

if ( class_exists( '\WP_Customize_Control' ) ) {
	class WP_Customize_Range_Control extends \WP_Customize_Control {
		public $type = 'custom_range';

		public function enqueue() {
			$min    = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';
			wp_enqueue_script(
				'wpb-range-control',
				WPBOUTIK_URL . 'assets/js/range-control' . $min . '.js',
				array( 'jquery' ),
				WPBOUTIK_VERSION,
				true
			);

			wp_localize_script( 'wpb-range-control', 'default_params', array(
				'wpboutik_size_cart_icon'                  => '20',
				//'wpboutik_general_font_size'               => '14',
				'wpboutik_archive_product_title_font_size' => '14',
				'wpboutik_archive_price_font_size'         => '16',
				'wpboutik_single_product_title_font_size'  => '20',
				'wpboutik_single_price_font_size'          => '18',
				'wpboutik_button_font_size'                => '16',
				'wpboutik_button_border_radius'            => '6',
				'wpboutik_archive_col_number_desktop'      => '4',
				'wpboutik_archive_col_number_mobile'       => '2',
				'wpboutik_archive_col_spacing'             => '15',
				'wpboutik_archive_excerpt_length'          => '55',
				'wpboutik_font_size_labels'                => '14'
			) );
		}

		public function render_content() {
			$value = $this->value();
			// Utilisez la valeur par défaut du réglage si $value est vide
			$value = ! empty( $value ) ? $value : $this->setting->default;
			?>
            <style>
                .ambiance-choices {
                    display: flex;
                    flex-wrap: wrap;
                    gap: .4rem;
                }

                .ambiance-choice, .color-theme-purpose {
                    padding: .2rem .5rem;
                    cursor: pointer;
                    background-color: transparent;
                    border: solid 2px rgb(60, 84, 204) !important;
                    border-radius: 7px;
                    color: rgb(60, 84, 204) !important;
                    box-shadow: none;
                }
            </style>
            <div>
				<?php if ( ! empty( $this->label ) ) : ?>
                    <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif; ?>
                <div style="display: flex">
                    <div style="width: 86%">
                        <div class="cs-range-value"><?php echo esc_attr( $value ); ?></div>
                        <input data-input-type="range" type="range" <?php $this->input_attrs(); ?>
                               value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
                    </div>
                    <div style="width: 9%;margin-left: auto;">
                        <button class="reset-default" style="height: 100%"><span
                                    class="dashicons dashicons-image-rotate"
                                    style="width: 14px;height: 14px;font-size: 14px;"></span></button>
                    </div>
                </div>
				<?php if ( ! empty( $this->description ) ) : ?>
                    <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php endif; ?>
            </div>
			<?php
		}
	}

	class WP_Customize_Theme_Colors extends \WP_Customize_Control {
		public $type = 'color_theme';

		public function enqueue() {
			$min    = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';
			wp_enqueue_script(
				'wpb-theme-color',
				WPBOUTIK_URL . 'assets/js/theme-color' . $min . '.js',
				array( 'jquery' ),
				WPBOUTIK_VERSION,
				true
			);
		}

		public function render_content() {
			$value = $this->value();
			// Utilisez la valeur par défaut du réglage si $value est vide
			$value = ! empty( $value ) ? $value : $this->setting->default;
			?>
            <div>
            <span class="customize-control-title">Ambiances</span>
            <div style="margin-bottom: 10px;">
                <div class="ambiance-choices">
                    <button class="ambiance-choice" data-ambiance="serious">Strict</button>
                    <button class="ambiance-choice" data-ambiance="pro">Sérieux</button>
                    <button class="ambiance-choice" data-ambiance="nice">High-tech</button>
                    <button class="ambiance-choice" data-ambiance="fun">Amical</button>
                    <button class="ambiance-choice" data-ambiance="minimaliste">Minimaliste</button>
                    <button class="ambiance-choice" data-ambiance="maximaliste">Maximaliste</button>
                    <button class="ambiance-choice" data-ambiance="funky">Funky</button>
                    <button class="ambiance-choice" data-ambiance="wpboutik">WPBoutik</button>
                </div>
            </div>
            <div>
				<?php if ( ! empty( $this->label ) ) : ?>
                    <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $this->description ) ) : ?>
                    <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php endif; ?>
                <div>
                    <div class="color-choices">

                    </div>
                    <!-- style="height: 100%" -->
                    <button class="color-theme-purpose">
						<span style="width: 14px;height: 14px;font-size: 14px;">
							Plus de couleurs
						</span>
                    </button>
                    <div class="custom-color-choices">

                    </div>
                </div>

            </div>
			<?php
		}
	}

	class WP_Customize_Html_Area extends \WP_Customize_Control {
		public $settings = 'blogname';
		public $description = '';

		public function render_content() {
			switch ( $this->type ) {
				default:
				case 'text' :
					echo '<p class="description">' . esc_html( $this->description ) . '</p>';
					break;
				case 'heading':
					echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
					break;
				case 'line' :
					echo '<hr />';
					break;
			}
		}
	}
}