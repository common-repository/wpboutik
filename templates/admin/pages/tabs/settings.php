<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use NF\WPBOUTIK\Tabs_Admin;

$options_available = apply_filters(
	'wpboutik_tabs_admin_options_available', [
		'apikey'   => [
			'key'         => 'apikey',
			'label'       => __( 'API Key', 'wpboutik' ),
			'description' => sprintf( esc_html__( 'Log in to %1$sWPBoutik%2$s to get your API key.', 'wpboutik' ), '<a target="_blank" href="' . WPBOUTIK_APP_URL . 'fr/register' . '">', '</a>' ),
		],
		'shop'     => [
			'key'   => 'wpboutik_shop_page_id',
			'label' => __( 'Shop page', 'wpboutik' ),
		],
		'cart'     => [
			'key'   => 'wpboutik_cart_page_id',
			'label' => __( 'Cart page', 'wpboutik' ),
		],
		'checkout' => [
			'key'   => 'wpboutik_checkout_page_id',
			'label' => __( 'Checkout page', 'wpboutik' ),
		],
		'account' => [
			'key'   => 'wpboutik_account_page_id',
			'label' => __( 'Account page', 'wpboutik' ),
		],
		'terms' => [
			'key'   => 'wpboutik_terms_page_id',
			'label' => __( 'General Terms and Conditions page', 'wpboutik' ),
		],
	]
);

/*$user_info = $this->user_api_services->get_user_info();
$plans     = $this->user_api_services->get_plans();*/

?>

<h3><?php esc_html_e( 'Main configuration', 'wpboutik' ); ?></h3>
<hr>
<table class="form-table">
    <tbody>
	<?php foreach ( $options_available as $key => $value ) : ?>
		<?php if ( 'apikey' === $key ) : ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr( $options_available['apikey']['key'] ); ?>">
						<?php echo esc_html( $options_available['apikey']['label'] ); ?>
                    </label>
                    <p class="sub-label"><?php echo $options_available['apikey']['description']; //phpcs:ignore ?></p>
                </th>
                <td class="forminp forminp-text">
                    <input
                            name="<?php echo esc_attr( sprintf( '%s[%s]', WPBOUTIK_SLUG, $options_available['apikey']['key'] ) ); ?>"
                            id="<?php echo esc_attr( $options_available['apikey']['key'] ); ?>"
                            type="password"
                            required
                            placeholder="wpboutik_XXXXXXXXXXXX"
                            value="<?php echo esc_attr( $this->options[ $options_available['apikey']['key'] ] ); ?>"
                    >
                    <br>
					<?php
					if ( empty( $this->options[ $options_available['apikey']['key'] ] ) ) {
						?>
                        <p class="description"><?php esc_html_e( 'If you don\'t have an account, you can create one in 20 seconds !', 'wpboutik' ); ?></p>
						<?php
					}
					?>
                </td>
            </tr>
		<?php else : ?>
			<?php if ( ! empty( $this->options[ $options_available[ 'apikey' ]['key'] ] ) ) : ?>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="<?php echo esc_attr( $options_available[ $key ]['key'] ); ?>">
							<?php echo esc_html( $options_available[ $key ]['label'] ); ?>
                        </label>
                    </th>
                    <td class="forminp forminp-text">
						<?php
						wp_dropdown_pages(
							array(
								'name'              => esc_attr( sprintf( '%s[%s]', WPBOUTIK_SLUG, $options_available[ $key ]['key'] ) ),
								'id'                => $options_available[ $key ]['key'],
								'show_option_none'  => __( '&mdash; Select &mdash;' ),
								'option_none_value' => '0',
								'selected'          => (isset($this->options[ $options_available[ $key ]['key'] ])) ? $this->options[ $options_available[ $key ]['key'] ] : '',
							)
						) ?>
                    </td>
                </tr>
			<?php endif; ?>
		<?php endif; ?>
	<?php endforeach; ?>
    </tbody>
</table>