<?php

namespace NF\WPBOUTIK;

class Admin_Dashboard {

	use Singleton;

	protected function init() {
		add_action( 'admin_footer', 'wpb_print_js', 25 );
		add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
	}

	public function wp_dashboard_setup() {
		wp_add_dashboard_widget( 'wpboutik_dashboard_status', __( 'WPBoutik Status', 'wpboutik' ), array(
			$this,
			'status_widget'
		) );
	}

	public function status_widget() {
		if ( empty( wpboutik_get_option( 'apikey' ) ) ) {
			return false;
		}

		$language     = wpboutik_get_option_params( 'language' );
		$project_slug = wpboutik_get_option_params( 'project_slug' );
		$url          = WPBOUTIK_APP_URL . $language . '/' . $project_slug . '/';
		if ( ! empty( $language ) ) {
			$url = WPBOUTIK_APP_URL . $language . '/' . $project_slug . '/';
		} ?>

        <style>

            #wpboutik_dashboard_status .inside {
                padding: 0;
                margin: 0
            }

            #wpboutik_dashboard_status .best-seller-this-month strong {
                margin-right: 48px
            }

            #wpboutik_dashboard_status .wpb_status_list {
                overflow: hidden;
                margin: 0;
            }

            #wpboutik_dashboard_status .wpb_status_list li {
                width: 50%;
                float: left;
                padding: 0;
                box-sizing: border-box;
                margin: 0;
                border-top: 1px solid #ececec;
                color: #aaa
            }

            #wpboutik_dashboard_status .wpb_status_list li a {
                display: block;
                color: #aaa;
                padding: 9px 12px;
                transition: all ease .5s;
                position: relative;
                font-size: 12px
            }

            #wpboutik_dashboard_status .wpb_status_list li a .wpb_sparkline {
                width: 4em;
                height: 2em;
                display: block;
                float: right;
                position: absolute;
                right: 0;
                top: 50%;
                margin-right: 12px;
                margin-top: -1.25em
            }

            #wpboutik_dashboard_status .wpb_status_list li strong {
                font-size: 18px;
                line-height: 1.2em;
                font-weight: 400;
                display: block;
                color: #21759b
            }

            #wpboutik_dashboard_status .wpb_status_list li a:hover {
                color: #2ea2cc
            }

            #wpboutik_dashboard_status .wpb_status_list li a:hover strong, #wpboutik_dashboard_status .wpb_status_list li a:hover::before {
                color: #2ea2cc !important
            }

            #wpboutik_dashboard_status .wpb_status_list li a::before {
                font-weight: 400;
                font-variant: normal;
                text-transform: none;
                margin: 0;
                text-indent: 0;
                top: 0;
                left: 0;
                height: 100%;
                text-align: center;
                content: "\e001";
                font-size: 2em;
                position: relative;
                width: auto;
                line-height: 1.2em;
                color: #464646;
                float: left;
                margin-right: 12px;
                margin-bottom: 12px
            }

            #wpboutik_dashboard_status .wpb_status_list li:first-child {
                border-top: 0
            }

            #wpboutik_dashboard_status .wpb_status_list li.sales-this-month,
            #wpboutik_dashboard_status .wpb_status_list li.margin-this-month,
            #wpboutik_dashboard_status .wpb_status_list li.best-seller-this-month {
                width: 100%
            }

            #wpboutik_dashboard_status .wpb_status_list li.processing-orders {
                border-right: 1px solid #ececec
            }

            #wpboutik_dashboard_status .wpb_status_list li.processing-orders a::before {
                font-family: Dashicons;
                content: "\f11c";
                color: #7ad03a
            }

            #wpboutik_dashboard_status .wpb_status_list li.on-hold-orders a::before {
                font-family: Dashicons;
                content: "\f159";
                color: #999
            }

            #wpboutik_dashboard_status .wpb_status_list li.low-in-stock {
                border-right: 1px solid #ececec
            }

            #wpboutik_dashboard_status .wpb_status_list li.sales-this-month,
            #wpboutik_dashboard_status .wpb_status_list li.margin-this-month,
            #wpboutik_dashboard_status .wpb_status_list li.best-seller-this-month {
                display: block;
                color: #aaa;
                padding: 9px 12px;
                transition: all ease .5s;
                position: relative;
                font-size: 12px;
            }

            /*#wpboutik_dashboard_status .wpb_status_list li.sales-this-month strong::before {
                font-family: Dashicons;
                content: "\f18e";
            }*/

            #wpboutik_dashboard_status .wpb_status_list li.low-in-stock a::before {
                font-family: Dashicons;
                content: "\f534";
                color: #ffba00
            }

            #wpboutik_dashboard_status .wpb_status_list li.out-of-stock a::before {
                font-family: Dashicons;
                content: "\f153";
                color: #a00
            }

        </style>

		<?php
		$currency_symbol = get_wpboutik_currency_symbol();
		$data_info_wpb   = wpboutik_get_data_info();

		if ( empty( $data_info_wpb ) ) {
			echo "Error Data WPBoutik";
		}
		$total_net = 0;
		if ( isset( $data_info_wpb['total_net'] ) && null !== $data_info_wpb['total_net']->total_net ) {
			$total_net = round( $data_info_wpb['total_net']->total_net, 2 );
		}
		$total_marge_brute = 0;
		if ( isset( $data_info_wpb['total_marge_brute'] ) && null !== $data_info_wpb['total_marge_brute']->total_margin ) {
			$total_marge_brute = round( $data_info_wpb['total_marge_brute']->total_margin, 2 );
		}
		$product_sell_most = 'Aucune';
		if ( isset( $data_info_wpb['product_sell_most'] ) ) {
			$product_sell_most = $data_info_wpb['product_sell_most'];
		}
		$count_product_sell_most = 0;
		if ( isset( $data_info_wpb['count_product_sell_most'] ) ) {
			$count_product_sell_most = $data_info_wpb['count_product_sell_most'];
		}
		$orders_processing_count = 0;
		if ( isset( $data_info_wpb['orders_processing_count'] ) ) {
			$orders_processing_count = $data_info_wpb['orders_processing_count'];
		}
		$orders_pending_count = 0;
		if ( isset( $data_info_wpb['orders_pending_count'] ) ) {
			$orders_pending_count = $data_info_wpb['orders_pending_count'];
		}
		$product_low_stock_count = 0;
		if ( isset( $data_info_wpb['product_low_stock_count'] ) ) {
			$product_low_stock_count = $data_info_wpb['product_low_stock_count'];
		} ?>
        <ul class="wpb_status_list">
            <li class="sales-this-month">
                <strong><?php echo $total_net; ?><?php echo $currency_symbol; ?></strong>
                de
                ventes nettes ce mois
            </li>
            <li class="margin-this-month">
                <strong><?php echo $total_marge_brute; ?><?php echo $currency_symbol; ?></strong>
                de
                marge brute ce mois
            </li>
            <li class="best-seller-this-month">
                <strong><?php echo $product_sell_most; ?></strong> meilleure vente du mois
                (<?php echo $count_product_sell_most; ?> vendus)
            </li>
            <li class="processing-orders">
                <a href="<?php echo $url . 'orders'; ?>">
                    <strong><?php echo $orders_processing_count; ?>
                        commande<?php echo ( $orders_processing_count > 1 ) ? 's' : ''; ?></strong> en
                    cours de traitement </a>
            </li>
            <li class="on-hold-orders">
                <a href="<?php echo $url . 'orders'; ?>">
                    <strong><?php echo $orders_pending_count; ?> commande</strong> en attente </a>
            </li>
            <li class="low-in-stock">
                <a href="<?php echo $url . 'inventory'; ?>" target="_blank">
                    <strong><?php echo $product_low_stock_count; ?>
                        produit<?php echo ( $product_low_stock_count > 1 ) ? 's' : ''; ?></strong> en
                    stock faible </a>
            </li>
            <li class="out-of-stock">
                <a href="<?php echo $url . 'inventory'; ?>" target="_blank">
                    <strong><?php echo ( isset( $data_info_wpb['product_out_of_stock_count'] ) ) ? $data_info_wpb['product_out_of_stock_count'] : ''; ?>
                        produit<?php echo ( isset( $data_info_wpb['product_out_of_stock_count'] ) && $data_info_wpb['product_out_of_stock_count'] > 1 ) ? 's' : ''; ?></strong>
                    en rupture de stock </a>
            </li>
        </ul>
        <a href="<?php echo $url . 'dashboard'; ?>" target="_blank" class="button button-primary" style="text-align: center;
    margin: 11px auto;
    display: block;
    width: 35%;">Gérer ma boutique</a>
		<?php
	}
}