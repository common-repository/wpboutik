<?php

namespace NF\WPBOUTIK;
class Widgets {
	use Singleton;

	public function __construct() {
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'enqueue_block_assets', array( $this, 'load_tailwind_css_for_widget' ), 999 );
	}

	/**
	 * Chargez Tailwind CSS dans le back office uniquement lorsque votre widget est actif
	 */
	public function load_tailwind_css_for_widget() {
		$min    = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_style( 'wpboutik-widget-css', WPBOUTIK_URL . 'assets/css/widgets' . $min . '.css', array(), time(), false );
	}

	// Register Widgets
	public function register_widgets() {

		register_sidebar( array(
			'name'          => __( 'WBoutik Product Sidebar', 'wpboutik' ),
			'id'            => 'wpboutik_product_sidebar',
			'description'   => __( 'Widgets added here will appear on single wpboutik product pages.', 'wpboutik' ),
			'before_widget' => '<div id="%1$s" class="widget widget_block %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widgettitle">',
			'after_title'   => '</h2>',
		) );

		register_sidebar( array(
			'name'          => __( 'WBoutik Archive Sidebar', 'wpboutik' ),
			'id'            => 'wpboutik_archive_sidebar',
			'description'   => __( 'Widgets added here will appear on wpboutik archive product pages.', 'wpboutik' ),
			'before_widget' => '<div id="%1$s" class="widget widget_block %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widgettitle">',
			'after_title'   => '</h2>',
		) );

		register_sidebar( array(
			'name'          => __( 'WBoutik Cart Sidebar', 'wpboutik' ),
			'id'            => 'wpboutik_cart_sidebar',
			'description'   => __( 'Widgets added here will appear on wpboutik cart page.', 'wpboutik' ),
			'before_widget' => '<div id="%1$s" class="widget widget_block %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widgettitle">',
			'after_title'   => '</h2>',
		) );

		register_widget( \NF\WPBOUTIK\Widget_Nouveaux_Produits::class );
		register_widget( \NF\WPBOUTIK\Widget_Produits_Mis_En_Avant::class );
		register_widget( \NF\WPBOUTIK\Widget_Meilleures_Ventes::class );
		//register_widget( \NF\WPBOUTIK\Widget_Categories_Produits::class );
		register_widget( \NF\WPBOUTIK\Widget_Product_Categories::class );
		register_widget( \NF\WPBOUTIK\Widget_Price_Filter::class );
		register_widget( \NF\WPBOUTIK\Widget_Search_Product::class );
		register_widget( \NF\WPBOUTIK\Widget_Gift_Card::class );

		add_filter( 'wpboutik_meta_query_archive', array( __CLASS__, 'wpboutik_meta_query_archive' ) );
	}

	public static function wpboutik_meta_query_archive( $meta_query ) {
		if ( ( is_wpboutik_shop() || is_wpboutik_product_taxonomy() ) ) {
			if ( ! empty( $_GET['min_price'] ) || ! empty( $_GET['max_price'] ) ) {
				if ( ! empty( $_GET['min_price'] ) || ! empty( $_GET['max_price'] ) ) {
					$current_min_price = isset( $_GET['min_price'] ) ? floatval( $_GET['min_price'] ) : 0;
					$current_max_price = isset( $_GET['max_price'] ) ? floatval( $_GET['max_price'] ) : PHP_INT_MAX;

					$meta_query[] = array(
						'key'     => 'price',
						'value'   => array( $current_min_price, $current_max_price ),
						'compare' => 'BETWEEN',
						'type'    => 'DECIMAL(10,' . wpb_get_price_decimals() . ')',
					);

					return $meta_query;
				}
			}
		}

		return $meta_query;
	}
}

/**
 * Widget Nouveaux Produits
 */
class Widget_Nouveaux_Produits extends \WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'wpb_new_products',
			__( 'WPBoutik Liste Nouveaux Produits', 'wpboutik' ),
			array( 'description' => __( 'Affiche la liste des nouveaux produits', 'wpboutik' ), )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 *
	 * @see WP_Widget::widget()
	 *
	 */
	public function widget( $args, $instance ) {
		$widget_title                 = ! empty( $instance['widget_title'] ) ? $instance['widget_title'] : __( 'New products', 'wpboutik' );
		echo $args['before_widget'];
		if ( ! empty( $widget_title ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $widget_title ) . $args['after_title'];
		}
		// Récupère le nombre de produits à afficher depuis le paramètre X
		$nombre_produits = ! empty( $instance['nombre_produits'] ) ? $instance['nombre_produits'] : 3;
		// Query pour récupérer les X derniers produits
		$args_query = array(
			'post_type'      => 'wpboutik_product',
			'posts_per_page' => $nombre_produits,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		$query      = new \WP_Query( $args_query );
		// Boucle pour afficher les produits
		if ( $query->have_posts() ) {
			echo '<ul class="mt-5 mb-10">';
			while ( $query->have_posts() ) {
				$query->the_post();

				echo '<li class="wpb-widget-product">';
					wpb_field('image', ['format' => 'thumbnail']);
					echo '<div>';
						wpb_field('title', ['title_tag' => 'span']);
						wpb_field('price');
					echo '</div>';
				echo '</li>';
			}
			echo '</ul>';
			wp_reset_postdata();
		}
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @see WP_Widget::form()
	 *
	 */
	public function form( $instance ) {
		$nombre_produits = ! empty( $instance['nombre_produits'] ) ? $instance['nombre_produits'] : 3;
		$widget_title    = ! empty( $instance['widget_title'] ) ? esc_attr( $instance['widget_title'] ) : '';
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'widget_title' ); ?>"><?php _e( 'Widget Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'widget_title' ); ?>"
                   name="<?php echo $this->get_field_name( 'widget_title' ); ?>" type="text"
                   value="<?php echo $widget_title; ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'nombre_produits' ); ?>"><?php _e( 'Nombre de produits à afficher:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'nombre_produits' ); ?>"
                   name="<?php echo $this->get_field_name( 'nombre_produits' ); ?>" type="number" min="1" step="1"
                   value="<?php echo esc_attr( $nombre_produits ); ?>">
        </p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @see WP_Widget::update()
	 *
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = array();
		$instance['nombre_produits'] = ( ! empty( $new_instance['nombre_produits'] ) ) ? absint( $new_instance['nombre_produits'] ) : 3;
		$instance['widget_title']    = ( ! empty( $new_instance['widget_title'] ) ) ? esc_attr( $new_instance['widget_title'] ) : '';

		return $instance;
	}
} // class Widget_Nouveaux_Produits

/**
 * Widget Produits mis en avant
 */
class Widget_Produits_Mis_En_Avant extends \WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'widget_produits_mis_en_avant',
			__( 'WPBoutik Liste Produits mis en avant', 'wpboutik' ),
			array( 'description' => __( 'Affiche la liste des produits mis en avant', 'wpboutik' ), )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 *
	 * @see WP_Widget::widget()
	 *
	 */
	public function widget( $args, $instance ) {
		// Récupère le nombre de produits à afficher depuis le paramètre X
		$nombre_produits = ! empty( $instance['nombre_produits'] ) ? $instance['nombre_produits'] : 3;
		$args_query      = array(
			'post_type'      => 'wpboutik_product',
			'meta_query'     => array(
				array(
					'key'   => 'mis_en_avant',
					'value' => 1,
				),
			),
			'posts_per_page' => $nombre_produits,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		$query           = new \WP_Query( $args_query );
		if ( $query->have_posts() ) {

			$widget_title = ! empty( $instance['widget_title'] ) ? $instance['widget_title'] : __( 'Featured products', 'wpboutik' );
			echo $args['before_widget'];
			if ( ! empty( $widget_title ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $widget_title ) . $args['after_title'];
			}

			echo '<ul class="mt-5 mb-10">';
			while ( $query->have_posts() ) {
				$query->the_post();

				echo '<li class="wpb-widget-product">';
					wpb_field('image', ['format' => 'thumbnail']);
					echo '<div>';
						wpb_field('title', ['title_tag' => 'span']);
						wpb_field('price');
					echo '</div>';
				echo '</li>';
			}
			echo '</ul>';
			wp_reset_postdata();
			echo $args['after_widget'];
		} else {
			echo $args['before_widget'].$args['after_widget'];
		}
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @see WP_Widget::form()
	 *
	 */
	public function form( $instance ) {
		$nombre_produits = ! empty( $instance['nombre_produits'] ) ? $instance['nombre_produits'] : 3;
		$widget_title    = ! empty( $instance['widget_title'] ) ? esc_attr( $instance['widget_title'] ) : '';
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'widget_title' ); ?>"><?php _e( 'Widget Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'widget_title' ); ?>"
                   name="<?php echo $this->get_field_name( 'widget_title' ); ?>" type="text"
                   value="<?php echo $widget_title; ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'nombre_produits' ); ?>"><?php _e( 'Nombre de produits à afficher:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'nombre_produits' ); ?>"
                   name="<?php echo $this->get_field_name( 'nombre_produits' ); ?>" type="number" min="1" step="1"
                   value="<?php echo esc_attr( $nombre_produits ); ?>">
        </p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @see WP_Widget::update()
	 *
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = array();
		$instance['nombre_produits'] = ( ! empty( $new_instance['nombre_produits'] ) ) ? absint( $new_instance['nombre_produits'] ) : 3;
		$instance['widget_title']    = ( ! empty( $new_instance['widget_title'] ) ) ? esc_attr( $new_instance['widget_title'] ) : '';

		return $instance;
	}
} // class Widget_Produits_Mis_En_Avant

/**
 * Widget Meilleures Ventes
 */
class Widget_Meilleures_Ventes extends \WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'widget_meilleures_ventes',
			__( 'WPBoutik Liste Meilleures Ventes', 'wpboutik' ),
			array( 'description' => __( 'Affiche la liste des meilleures ventes de produits', 'wpboutik' ), )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 *
	 * @see WP_Widget::widget()
	 *
	 */
	public function widget( $args, $instance ) {
		$best_sellers = wpboutik_get_best_sellers();
		if ( ! empty( $best_sellers['best_sellers'] ) ) {
			$widget_title = ! empty( $instance['widget_title'] ) ? $instance['widget_title'] : __( 'Best Sellers', 'wpboutik' );
			echo $args['before_widget'];
			if ( ! empty( $widget_title ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $widget_title ) . $args['after_title'];
			}
			// Récupère le nombre de produits à afficher depuis le paramètre X
			$nombre_produits = ! empty( $instance['nombre_produits'] ) ? $instance['nombre_produits'] : 3;

			// Query pour récupérer les X meilleures ventes de produits
			$args_query = array(
				'post_type'      => 'wpboutik_product',
				'post__in'       => $best_sellers['best_sellers'],
				'posts_per_page' => $nombre_produits,
			);
			$query      = new \WP_Query( $args_query );
			// Boucle pour afficher les produits
			if ( $query->have_posts() ) {
				echo '<ul class="mt-5 mb-10">';
				while ( $query->have_posts() ) {
					$query->the_post();
					echo '<li class="wpb-widget-product">';
					wpb_field('image', ['format' => 'thumbnail']);
					echo '<div>';
					wpb_field('title', ['title_tag' => 'span']);
					wpb_field('price');
					echo '</div>';
					echo '</li>';
				}
				echo '</ul>';
				wp_reset_postdata();
			}
			echo $args['after_widget'];
		}
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @see WP_Widget::form()
	 *
	 */
	public function form( $instance ) {
		$nombre_produits = ! empty( $instance['nombre_produits'] ) ? $instance['nombre_produits'] : 3;
		$widget_title    = ! empty( $instance['widget_title'] ) ? esc_attr( $instance['widget_title'] ) : '';
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'widget_title' ); ?>"><?php _e( 'Widget Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'widget_title' ); ?>"
                   name="<?php echo $this->get_field_name( 'widget_title' ); ?>" type="text"
                   value="<?php echo $widget_title; ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'nombre_produits' ); ?>"><?php _e( 'Nombre de produits à afficher:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'nombre_produits' ); ?>"
                   name="<?php echo $this->get_field_name( 'nombre_produits' ); ?>" type="number" min="1" step="1"
                   value="<?php echo esc_attr( $nombre_produits ); ?>">
        </p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @see WP_Widget::update()
	 *
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                    = array();
		$instance['nombre_produits'] = ( ! empty( $new_instance['nombre_produits'] ) ) ? absint( $new_instance['nombre_produits'] ) : 3;
		$instance['widget_title']    = ( ! empty( $new_instance['widget_title'] ) ) ? esc_attr( $new_instance['widget_title'] ) : '';

		return $instance;
	}
} // class Widget_Meilleures_Ventes

class Widget_Categories_Produits extends \WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'widget_categories_produits',
			__( 'WPBoutik Liste Categories Produits', 'wpboutik' ),
			array( 'description' => __( 'Affiche la liste des catégories de produits', 'wpboutik' ), )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 *
	 * @see WP_Widget::widget()
	 *
	 */
	public function widget( $args, $instance ) {
		$widget_title = ! empty( $instance['widget_title'] ) ? $instance['widget_title'] : __( 'Les catégories de produits', 'wpboutik' );
		echo $args['before_widget'];
		if ( ! empty( $widget_title ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $widget_title ) . $args['after_title'];
		}

		echo '<ul class="mt-5 mb-10">';
		echo '<span class="sr-only" id="widget-select-product-cat">'.__('Select Category').'</span>';
		wp_dropdown_categories( array(
			'taxonomy'     => 'wpboutik_product_cat',
			'hierarchical' => 1,
			'aria_describedby' => 'widget-select-product-cat'
		) );
		/*wp_list_categories( array(
			'taxonomy' => 'wpboutik_product_cat',
			'orderby'  => 'name',
			'title_li' => '',
		) );*/
		echo '</ul>';
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @see WP_Widget::form()
	 *
	 */
	public function form( $instance ) {
		$widget_title = ! empty( $instance['widget_title'] ) ? esc_attr( $instance['widget_title'] ) : '';
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'widget_title' ); ?>"><?php _e( 'Widget Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'widget_title' ); ?>"
                   name="<?php echo $this->get_field_name( 'widget_title' ); ?>" type="text"
                   value="<?php echo $widget_title; ?>"/>
        </p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @see WP_Widget::update()
	 *
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = array();
		$instance['widget_title'] = ( ! empty( $new_instance['widget_title'] ) ) ? esc_attr( $new_instance['widget_title'] ) : '';

		return $instance;
	}
}

class Widget_Price_Filter extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'wpboutik_widget_price_filter',
			__( 'WPBoutik Filter Products by Price', 'wpboutik' ),
			array( 'description' => __( 'Display a slider to filter products in your store by price.', 'wpboutik' ), )
		//@TODO => Indiquer que le widget "WPBoutik Filter Products by Price", ne s'affichera que sur la page Boutique ou Archive produit
		);

		wp_register_script( 'wpb-accounting', WPBOUTIK_URL . 'assets/js/accounting/accounting.min.js', array( 'jquery' ), '0.4.2', array( 'in_footer' => true ) );
		wp_register_script( 'wpb-jquery-ui-touchpunch', WPBOUTIK_URL . 'assets/js/jquery-ui-touch-punch/jquery-ui-touch-punch.min.js', array( 'jquery-ui-slider' ), '1.0', array( 'in_footer' => true ) );
		$min    = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';
		wp_register_script( 'wpb-price-slider', WPBOUTIK_URL . 'assets/js/frontend/price-slider' . $min . '.js', array(
			'jquery-ui-slider',
			'wpb-jquery-ui-touchpunch',
			'wpb-accounting'
		), '1.0', array( 'in_footer' => true ) );
		wp_localize_script(
			'wpb-price-slider',
			'wpboutik_price_slider_params',
			array(
				'currency_format_num_decimals' => 0,
				'currency_format_symbol'       => get_wpboutik_currency_symbol(),
				'currency_format_decimal_sep'  => esc_attr( wpb_get_price_decimal_separator() ),
				'currency_format_thousand_sep' => esc_attr( wpb_get_price_thousand_separator() ),
				'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array(
					'%s',
					'%v'
				), get_wpboutik_price_format() ) ),
			)
		);

		if ( is_customize_preview() ) {
			wp_enqueue_script( 'wpb-price-slider' );
		}
	}

	/**
	 * Output widget.
	 *
	 * @param array $args Arguments.
	 * @param array $instance Widget instance.
	 *
	 * @see WP_Widget
	 *
	 */
	public function widget( $args, $instance ) {
		global $wp, $wp_query;

		if ( ! is_wpboutik_shop() && ! is_wpboutik_product_taxonomy() ) {
			return;
		}

		// If there are not posts and we're not filtering, hide the widget.
		if ( $wp_query->is_main_query() && ! $wp_query->post_count && ! isset( $_GET['min_price'] ) && ! isset( $_GET['max_price'] ) ) { // WPCS: input var ok, CSRF ok.
			return;
		}

		wp_enqueue_script( 'wpb-price-slider' );

		// Round values to nearest 10 by default.
		$step = max( apply_filters( 'wpboutik_price_filter_widget_step', 10 ), 1 );

		// Find min and max price in current result set.
		$prices    = $this->get_filtered_price();
		$min_price = $prices->min_price;
		$max_price = $prices->max_price;

		// Check to see if we should add taxes to the prices if store are excl tax but display incl.
		/*$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

		if ( wc_tax_enabled() && ! wc_prices_include_tax() && 'incl' === $tax_display_mode ) {
			$tax_class = apply_filters( 'woocommerce_price_filter_widget_tax_class', '' ); // Uses standard tax class.
			$tax_rates = WC_Tax::get_rates( $tax_class );

			if ( $tax_rates ) {
				$min_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $min_price, $tax_rates ) );
				$max_price += WC_Tax::get_tax_total( WC_Tax::calc_exclusive_tax( $max_price, $tax_rates ) );
			}
		}*/

		$min_price = apply_filters( 'wpboutik_price_filter_widget_min_amount', floor( $min_price / $step ) * $step );
		$max_price = apply_filters( 'wpboutik_price_filter_widget_max_amount', ceil( $max_price / $step ) * $step );

		// If both min and max are equal, we don't need a slider.
		if ( $min_price === $max_price ) {
			return;
		}

		$current_min_price = isset( $_GET['min_price'] ) ? floor( floatval( wp_unslash( $_GET['min_price'] ) ) / $step ) * $step : $min_price; // WPCS: input var ok, CSRF ok.
		$current_max_price = isset( $_GET['max_price'] ) ? ceil( floatval( wp_unslash( $_GET['max_price'] ) ) / $step ) * $step : $max_price; // WPCS: input var ok, CSRF ok.

		$widget_title = ! empty( $instance['widget_title'] ) ? $instance['widget_title'] : __( 'Filtrer par prix', 'wpboutik' );
		echo $args['before_widget'];
		if ( ! empty( $widget_title ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $widget_title ) . $args['after_title'];
		}

		if ( '' === get_option( 'permalink_structure' ) ) {
			$form_action = remove_query_arg( array(
				'page',
				'paged',
				'product-page'
			), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
		} else {
			$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
		}

		do_action( 'wpboutik_widget_price_filter_start', $args );

		$backgroundcolor   = wpboutik_get_backgroundcolor_button();
		$hovercolor        = wpboutik_get_hovercolor_button();
		$button_text_color = wpboutik_get_button_text_color(); ?>

        <style>
            .widget_wpboutik_widget_price_filter .price_slider_amount .button {
                color: <?php echo !empty( $button_text_color ) ? $button_text_color : '#fff'; ?>;
                border: 1px solid<?php echo $backgroundcolor; ?>;
                background-color: <?php echo $backgroundcolor; ?>;
            }

            .widget_wpboutik_widget_price_filter .price_slider_amount .button:hover {
                background-color: <?php echo $hovercolor; ?>;
            }

            .widget_wpboutik_widget_price_filter .ui-slider .ui-slider-handle {
                background-color: <?php echo $backgroundcolor; ?>;
            }

            .widget_wpboutik_widget_price_filter .ui-slider .ui-slider-range {
                background-color: <?php echo $backgroundcolor; ?>;
            }

            .widget_wpboutik_widget_price_filter .price_slider_wrapper .ui-widget-content {
                background-color: <?php echo $hovercolor; ?>;
            }
        </style>

        <form method="get" action="<?php echo esc_url( $form_action ); ?>">
            <div class="price_slider_wrapper">
                <div class="price_slider" style="display:none;"></div>
                <div class="price_slider_amount" data-step="<?php echo esc_attr( $step ); ?>">
                    <label class="screen-reader-text"
                           for="min_price"><?php esc_html_e( 'Min price', 'wpboutik' ); ?></label>
                    <input type="text" id="min_price" name="min_price"
                           value="<?php echo esc_attr( $current_min_price ); ?>"
                           data-min="<?php echo esc_attr( $min_price ); ?>"
                           placeholder="<?php echo esc_attr__( 'Min price', 'wpboutik' ); ?>"/>
                    <label class="screen-reader-text"
                           for="max_price"><?php esc_html_e( 'Max price', 'wpboutik' ); ?></label>
                    <input type="text" id="max_price" name="max_price"
                           value="<?php echo esc_attr( $current_max_price ); ?>"
                           data-max="<?php echo esc_attr( $max_price ); ?>"
                           placeholder="<?php echo esc_attr__( 'Max price', 'wpboutik' ); ?>"/>
					<?php /* translators: Filter: verb "to filter" */ ?>
                    <button type="submit"
                            class="button<?php echo esc_attr( function_exists( 'wp_theme_get_element_class_name' ) && wp_theme_get_element_class_name( 'button' ) ? ' ' . wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php echo esc_html__( 'Filter', 'wpboutik' ); ?></button>
                    <div class="price_label" style="display:none;">
						<?php echo esc_html__( 'Price:', 'wpboutik' ); ?> <span class="from"></span> &mdash; <span
                                class="to"></span>
                    </div>
					<?php echo wpb_query_string_form_fields( null, array(
						'min_price',
						'max_price',
						'paged'
					), '', true ); ?>
                    <div class="clear"></div>
                </div>
            </div>
        </form>

		<?php do_action( 'wpboutik_widget_price_filter_end', $args );

		echo $args['after_widget'];
	}

	/**
	 * Get filtered min price for current products.
	 *
	 * @return int
	 */
	protected function get_filtered_price() {
		global $wpdb, $wp_query;

		$args       = $wp_query->query_vars;
		$tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
		$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

		/*if ( ! is_post_type_archive( 'product' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
			$tax_query[] = WC()->query->get_main_tax_query();
		}

		foreach ( $meta_query + $tax_query as $key => $query ) {
			if ( ! empty( $query['price_filter'] ) || ! empty( $query['rating_filter'] ) ) {
				unset( $meta_query[ $key ] );
			}
		}

		$meta_query = new WP_Meta_Query( $meta_query );
		$tax_query  = new WP_Tax_Query( $tax_query );
		$search     = WC_Query::get_main_search_query_sql();

		$meta_query_sql   = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql    = $tax_query->get_sql( $wpdb->posts, 'ID' );
		$search_query_sql = $search ? ' AND ' . $search : '';

		$sql = "
			SELECT min( min_price ) as min_price, MAX( max_price ) as max_price
			FROM {$wpdb->wc_product_meta_lookup}
			WHERE product_id IN (
				SELECT ID FROM {$wpdb->posts}
				" . $tax_query_sql['join'] . $meta_query_sql['join'] . "
				WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'wpboutik_price_filter_post_type', array( 'wpboutik_product' ) ) ) ) . "')
				AND {$wpdb->posts}.post_status = 'publish'
				" . $tax_query_sql['where'] . $meta_query_sql['where'] . $search_query_sql . '
			)';*/

		$sql = "SELECT 
    CAST(MIN(CAST(meta_price.meta_value AS SIGNED)) AS DECIMAL(10,2)) AS min_price,
    CAST(MAX(CAST(meta_price.meta_value AS SIGNED)) AS DECIMAL(10,2)) AS max_price
FROM 
    {$wpdb->posts} AS p
INNER JOIN
    {$wpdb->postmeta} AS meta_price ON (p.ID = meta_price.post_id AND meta_price.meta_key = 'price')
WHERE
    p.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'wpboutik_price_filter_post_type', array( 'wpboutik_product' ) ) ) ) . "')
    AND p.post_status = 'publish';";

		//$sql = apply_filters( 'wpboutik_price_filter_sql', $sql, $meta_query_sql, $tax_query_sql );
		$sql = apply_filters( 'wpboutik_price_filter_sql', $sql );

		return $wpdb->get_row( $sql ); // WPCS: unprepared SQL ok.
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @see WP_Widget::form()
	 *
	 */
	public function form( $instance ) {
		$widget_title = ! empty( $instance['widget_title'] ) ? esc_attr( $instance['widget_title'] ) : '';
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'widget_title' ); ?>"><?php _e( 'Widget Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'widget_title' ); ?>"
                   name="<?php echo $this->get_field_name( 'widget_title' ); ?>" type="text"
                   value="<?php echo $widget_title; ?>"/>
        </p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @see WP_Widget::update()
	 *
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = array();
		$instance['widget_title'] = ( ! empty( $new_instance['widget_title'] ) ) ? esc_attr( $new_instance['widget_title'] ) : '';

		return $instance;
	}
}

class Widget_Gift_Card extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'wpboutik_widget_gift_card',
			__( 'WPBoutik Gift Card Widget', 'wpboutik' ),
			array( 'description' => __( 'Display Gift card product.', 'wpboutik' ), )
		);
	}

	/**
	 * Output widget.
	 *
	 * @param array $args Arguments.
	 * @param array $instance Widget instance.
	 *
	 * @see WP_Widget
	 *
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		$widget_title = ! empty( $instance['widget_title'] ) ? $instance['widget_title'] : __( 'Gift Card', 'wpboutik' );
		if ( ! empty( $widget_title ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $widget_title ) . $args['after_title'];
		}
		$product = new \WP_Query([
			'post_type'  => 'wpboutik_product',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
				'key' => 'type',
				'value' => 'gift_card',
				),
			),				
		]);
		if ($product->have_posts()) :
			while($product->have_posts()) :
				$product->the_post();
				wpb_template_parts('product-card');
			endwhile;
		else :
			echo '<p>'.__('No gift cards were found on this site.', 'wpboutik').'</p>';
		endif;
		wp_reset_query();
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @see WP_Widget::form()
	 *
	 */
	public function form( $instance ) {
		$widget_title = ! empty( $instance['widget_title'] ) ? esc_attr( $instance['widget_title'] ) : '';
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'widget_title' ); ?>"><?php _e( 'Widget Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'widget_title' ); ?>"
                   name="<?php echo $this->get_field_name( 'widget_title' ); ?>" type="text"
                   value="<?php echo $widget_title; ?>"/>
        </p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @see WP_Widget::update()
	 *
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = array();
		$instance['widget_title'] = ( ! empty( $new_instance['widget_title'] ) ) ? esc_attr( $new_instance['widget_title'] ) : '';

		return $instance;
	}
}

class Widget_Search_Product extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'wpboutik_widget_search_product',
			__( 'WPBoutik Product Search Widget', 'wpboutik' ),
			array( 'description' => __( 'Display an input to search products.', 'wpboutik' ), )
		);
	}

	/**
	 * Output widget.
	 *
	 * @param array $args Arguments.
	 * @param array $instance Widget instance.
	 *
	 * @see WP_Widget
	 *
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		$widget_title = ! empty( $instance['widget_title'] ) ? $instance['widget_title'] : '';
		if ( ! empty( $widget_title ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $widget_title ) . $args['after_title'];
		}
		$dashicon = '<div class="search_product_box visible-input">';
		$dashicon .= '<input type="search" id="wpb-product-search" placeholder="Rechercher un produit">';
		$dashicon .= '<span class="dashicons dashicons-no close_search_product"></span>';
		$dashicon .= '<div class="wpb-search-results"></div>';
		$dashicon .= '</div>';
		echo $dashicon;
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @see WP_Widget::form()
	 *
	 */
	public function form( $instance ) {
		$widget_title = ! empty( $instance['widget_title'] ) ? esc_attr( $instance['widget_title'] ) : '';
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'widget_title' ); ?>"><?php _e( 'Widget Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'widget_title' ); ?>"
                   name="<?php echo $this->get_field_name( 'widget_title' ); ?>" type="text"
                   value="<?php echo $widget_title; ?>"/>
        </p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @see WP_Widget::update()
	 *
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = array();
		$instance['widget_title'] = ( ! empty( $new_instance['widget_title'] ) ) ? esc_attr( $new_instance['widget_title'] ) : '';

		return $instance;
	}
}

class Widget_Product_Categories extends \WP_Widget {

	/**
	 * Category ancestors.
	 *
	 * @var array
	 */
	public $cat_ancestors;

	/**
	 * Current Category.
	 *
	 * @var bool
	 */
	public $current_cat;
	public $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'wpboutik_widget_product_categories',
			__( 'WPBoutik Product Categories', 'wpboutik' ),
			array( 'description' => __( 'A list or dropdown of product categories.', 'wpboutik' ), )
		);

		wp_register_script( 'select2wpb', WPBOUTIK_URL . 'assets/js/select2/select2.full.min.js', array( 'jquery' ), '4.0.3', array( 'in_footer' => true ) );
		wp_register_script( 'selectwpb', WPBOUTIK_URL . 'assets/js/selectWPB/selectWPB.full.min.js', array( 'jquery' ), '1.0.6', array( 'in_footer' => true ) );

		$this->settings = array(
			'title'              => array(
				'type'  => 'text',
				'std'   => __( 'Product categories', 'wpboutik' ),
				'label' => __( 'Title', 'wpboutik' ),
			),
			'orderby'            => array(
				'type'    => 'select',
				'std'     => 'name',
				'label'   => __( 'Order by', 'wpboutik' ),
				'options' => array(
					'order' => __( 'Category order', 'wpboutik' ),
					'name'  => __( 'Name', 'wpboutik' ),
				),
			),
			'dropdown'           => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show as dropdown', 'wpboutik' ),
			),
			'count'              => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show product counts', 'wpboutik' ),
			),
			'hierarchical'       => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show hierarchy', 'wpboutik' ),
			),
			'show_children_only' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Only show children of the current category', 'wpboutik' ),
			),
			'hide_empty'         => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Hide empty categories', 'wpboutik' ),
			),
			'max_depth'          => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Maximum depth', 'wpboutik' ),
			),
		);
	}

	public function form( $instance ) {
		// Output widget settings form in the WordPress admin
		foreach ( $this->settings as $key => $setting ) {
			$value = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting['std'];

			switch ( $setting['type'] ) {
				case 'text':
					echo '<p>';
					echo '<label for="' . esc_attr( $this->get_field_id( $key ) ) . '">' . esc_html( $setting['label'] ) . ':</label>';
					echo '<input class="widefat" id="' . esc_attr( $this->get_field_id( $key ) ) . '" name="' . esc_attr( $this->get_field_name( $key ) ) . '" type="text" value="' . esc_attr( $value ) . '" />';
					echo '</p>';
					break;

				case 'select':
					echo '<p>';
					echo '<label for="' . esc_attr( $this->get_field_id( $key ) ) . '">' . esc_html( $setting['label'] ) . ':</label>';
					echo '<select class="widefat" id="' . esc_attr( $this->get_field_id( $key ) ) . '" name="' . esc_attr( $this->get_field_name( $key ) ) . '">';
					foreach ( $setting['options'] as $option_key => $option_label ) {
						echo '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_html( $option_label ) . '</option>';
					}
					echo '</select>';
					echo '</p>';
					break;

				case 'checkbox':
					echo '<p>';
					echo '<input class="checkbox" type="checkbox" ' . checked( $value, 1, false ) . ' id="' . esc_attr( $this->get_field_id( $key ) ) . '" name="' . esc_attr( $this->get_field_name( $key ) ) . '" />';
					echo '<label for="' . esc_attr( $this->get_field_id( $key ) ) . '">' . esc_html( $setting['label'] ) . '</label>';
					echo '</p>';
					break;
			}
		}
	}

	public function update( $new_instance, $old_instance ) {
		// Save widget settings
		$instance = array();
		foreach ( $this->settings as $key => $setting ) {
			switch ( $setting['type'] ) {
				case 'checkbox':
					$instance[ $key ] = ! empty( $new_instance[ $key ] ) ? 1 : 0;
					break;
				default:
					$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
					break;
			}
		}

		return $instance;
	}

	/**
	 * Output widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Widget instance.
	 *
	 * @see WP_Widget
	 */
	public function widget( $args, $instance ) {
		global $wp_query, $post;

		$count              = isset( $instance['count'] ) ? $instance['count'] : $this->settings['count']['std'];
		$hierarchical       = isset( $instance['hierarchical'] ) ? $instance['hierarchical'] : $this->settings['hierarchical']['std'];
		$show_children_only = isset( $instance['show_children_only'] ) ? $instance['show_children_only'] : $this->settings['show_children_only']['std'];
		$dropdown           = isset( $instance['dropdown'] ) ? $instance['dropdown'] : $this->settings['dropdown']['std'];
		$orderby            = isset( $instance['orderby'] ) ? $instance['orderby'] : $this->settings['orderby']['std'];
		$hide_empty         = isset( $instance['hide_empty'] ) ? $instance['hide_empty'] : $this->settings['hide_empty']['std'];
		$dropdown_args      = array(
			'hide_empty' => $hide_empty,
		);
		$list_args          = array(
			'show_count'   => $count,
			'hierarchical' => $hierarchical,
			'taxonomy'     => 'wpboutik_product_cat',
			'hide_empty'   => $hide_empty,
		);
		$max_depth          = absint( isset( $instance['max_depth'] ) ? $instance['max_depth'] : $this->settings['max_depth']['std'] );

		$list_args['menu_order'] = false;
		$dropdown_args['depth']  = $max_depth;
		$list_args['depth']      = $max_depth;

		if ( 'order' === $orderby ) {
			$list_args['orderby']      = 'meta_value_num';
			$dropdown_args['orderby']  = 'meta_value_num';
			$list_args['meta_key']     = 'order';
			$dropdown_args['meta_key'] = 'order';
		}

		$this->current_cat   = false;
		$this->cat_ancestors = array();

		if ( is_tax( 'wpboutik_product_cat' ) ) {
			$this->current_cat   = $wp_query->queried_object;
			$this->cat_ancestors = get_ancestors( $this->current_cat->term_id, 'wpboutik_product_cat' );

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
				$main_term           = apply_filters( 'wpboutik_product_categories_widget_main_term', $terms[0], $terms );
				$this->current_cat   = $main_term;
				$this->cat_ancestors = get_ancestors( $main_term->term_id, 'wpboutik_product_cat' );
			}
		}

		// Show Siblings and Children Only.
		if ( $show_children_only && $this->current_cat ) {
			if ( $hierarchical ) {
				$include = array_merge(
					$this->cat_ancestors,
					array( $this->current_cat->term_id ),
					get_terms(
						'wpboutik_product_cat',
						array(
							'fields'       => 'ids',
							'parent'       => 0,
							'hierarchical' => true,
							'hide_empty'   => false,
						)
					),
					get_terms(
						'wpboutik_product_cat',
						array(
							'fields'       => 'ids',
							'parent'       => $this->current_cat->term_id,
							'hierarchical' => true,
							'hide_empty'   => false,
						)
					)
				);
				// Gather siblings of ancestors.
				if ( $this->cat_ancestors ) {
					foreach ( $this->cat_ancestors as $ancestor ) {
						$include = array_merge(
							$include,
							get_terms(
								'wpboutik_product_cat',
								array(
									'fields'       => 'ids',
									'parent'       => $ancestor,
									'hierarchical' => false,
									'hide_empty'   => false,
								)
							)
						);
					}
				}
			} else {
				// Direct children.
				$include = get_terms(
					'wpboutik_product_cat',
					array(
						'fields'       => 'ids',
						'parent'       => $this->current_cat->term_id,
						'hierarchical' => true,
						'hide_empty'   => false,
					)
				);
			}

			$list_args['include']     = implode( ',', $include );
			$dropdown_args['include'] = $list_args['include'];

			if ( empty( $include ) ) {
				return;
			}
		} elseif ( $show_children_only ) {
			$dropdown_args['depth']        = 1;
			$dropdown_args['child_of']     = 0;
			$dropdown_args['hierarchical'] = 1;
			$list_args['depth']            = 1;
			$list_args['child_of']         = 0;
			$list_args['hierarchical']     = 1;
		}

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		if ( $dropdown ) {
			echo '<div class="wpb-field">';
			wpb_product_dropdown_categories(
				apply_filters(
					'wpboutik_product_categories_widget_dropdown_args',
					wp_parse_args(
						$dropdown_args,
						array(
							'show_count'         => $count,
							'hierarchical'       => $hierarchical,
							'show_uncategorized' => 0,
							'selected'           => $this->current_cat ? $this->current_cat->slug : '',
						)
					)
				)
			);
			echo '</div>';

			if ( ( is_wpboutik_shop() || is_wpboutik_product_taxonomy() ) ) {
				wp_enqueue_script( 'selectwpb' );
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
			}
		} else {
			include_once WPBOUTIK_DIR . 'includes/walkers/class-wpb-product-cat-list-walker.php';

			$list_args['walker']                     = new \WPB_Product_Cat_List_Walker();
			$list_args['title_li']                   = '';
			$list_args['pad_counts']                 = 1;
			$list_args['show_option_none']           = __( 'No product categories exist.', 'wpboutik' );
			$list_args['current_category']           = ( $this->current_cat ) ? $this->current_cat->term_id : '';
			$list_args['current_category_ancestors'] = $this->cat_ancestors;
			$list_args['max_depth']                  = $max_depth;

			echo '<ul class="product-categories">';

			wp_list_categories( apply_filters( 'wpboutik_product_categories_widget_args', $list_args ) );

			echo '</ul>';
		}

		echo $args['after_widget'];
	}
}