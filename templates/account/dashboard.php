<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/wpboutik/account/dashboard.php.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);
?>

<div class="divide-y divide-gray-200 lg:col-span-9">
    <div class="py-6 px-4 sm:p-6 lg:pb-8">

    <p>
        <?php
        printf(
            /* translators: 1: user display name 2: logout url */
            wp_kses( __( 'Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)', 'wpboutik' ), $allowed_html ),
            '<strong>' . esc_html( $current_user->display_name ) . '</strong>',
            esc_url( wpboutik_logout_url() )
        );
        ?>
    </p>

    <p>
        <?php
        /* translators: 1: Orders URL 2: Address URL 3: Account URL. */
        $dashboard_desc = __( 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">billing address</a>, and <a href="%3$s">edit your password and account details</a>.', 'wpboutik' );
        printf(
            wp_kses( $dashboard_desc, $allowed_html ),
            esc_url( wpboutik_get_endpoint_url( 'orders' ) ),
            esc_url( wpboutik_get_endpoint_url( 'edit-address' ) ),
            esc_url( wpboutik_get_endpoint_url( 'edit-account' ) )
        );
        ?>
    </p>

    <?php
        /**
         * My Account dashboard.
         */
        do_action( 'wpboutik_account_dashboard' );

    /* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
    ?>
    </div>
</div>
