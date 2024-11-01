<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use NF\WPBOUTIK\Tabs_Admin;

$url_form = wp_nonce_url(
	add_query_arg(
		[
			'action' => 'wpboutik_save_settings',
			'tab'    => $this->tab_active,
		],
		admin_url( 'admin-post.php' )
	),
	'wpboutik_save_settings'
);

$url_disconnect = wp_nonce_url(
	add_query_arg(
		[
			'action' => 'wpboutik_disconnect_project',
			'tab'    => $this->tab_active,
		],
		admin_url( 'admin-post.php' )
	),
	'wpboutik_disconnect_project'
); ?>

<script>
    function confirmerFonctionWordPress() {
        var confirmation = confirm("Voulez-vous vraiment déconnectez votre projet WPBoutik ?");
        if (confirmation) {
            var form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", "<?php echo esc_url( $url_disconnect ); ?>");

            var input = document.createElement("input");
            input.setAttribute("type", "hidden");
            input.setAttribute("name", "action");
            input.setAttribute("value", "wpboutik_disconnect_project");

            var tab = document.createElement("input");
            tab.setAttribute("type", "hidden");
            tab.setAttribute("name", "tab");
            tab.setAttribute("value", "<?php echo $this->tab_active; ?>");

            var nonce = document.createElement("input");
            nonce.setAttribute("type", "hidden");
            nonce.setAttribute("name", "_wpnonce");
            nonce.setAttribute("value", "<?php echo wp_create_nonce( 'wpboutik_disconnect_project' ); ?>");

            form.appendChild(input);
            form.appendChild(tab);
            form.appendChild(nonce);

            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<div id="wrap-wpboutik">
    <div class="wrap">
        <form method="post" id="mainform" action="<?php echo esc_url( $url_form ); ?>">
			<?php
			switch ( $this->tab_active ) {
				case Tabs_Admin::SETTINGS:
				default:
					include_once WPBOUTIK_TEMPLATES_ADMIN_PAGES . '/tabs/settings.php';
					/*if ( ! $this->options['has_first_settings'] ) {
						include_once WPBOUTIK_TEMPLATES_ADMIN_PAGES . '/tabs/appearance.php';
						include_once WPBOUTIK__TEMPLATES_ADMIN_PAGES . '/tabs/advanced.php';
					}*/

					break;
				/*case Helper_Tabs_Admin_Weglot::STATUS:
					include_once WPBOUTIK_TEMPLATES_ADMIN_PAGES . '/tabs/status.php';
					break;
				case Helper_Tabs_Admin_Weglot::SUPPORT:
					include_once WPBOUTIK_TEMPLATES_ADMIN_PAGES . '/tabs/support.php';
					break;*/
			}

			//if ( ! in_array( $this->tab_active, [ Tabs_Admin::STATUS ], true ) ) {
			submit_button();
			//}
			?>
            <input type="hidden" name="tab" value="<?php echo esc_attr( $this->tab_active ); ?>">
        </form>
		<?php if ( ! empty( $this->options['apikey'] ) ) : ?>
            <span class="dashicons dashicons-admin-customizer"></span>&nbsp;
            <a href="<?php echo esc_url( add_query_arg(
				'autofocus[panel]',
				'wpboutik_panel',
				admin_url( 'customize.php' )
			) ); ?>">
				<?php esc_html_e( 'Customize WPBoutik to your colors', 'wpboutik' ); ?>
            </a>
            <hr>
            <span class="dashicons dashicons-heart"></span>&nbsp;
            <a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/wpboutik?rate=5#postform">
				<?php esc_html_e( 'Love WPBoutik? Give us 5 stars on WordPress.org', 'wpboutik' ); ?>
            </a>
            <p class="wpboutik-five-stars">
				<?php
				// translators: 1 HTML Tag, 2 HTML Tag
				echo sprintf( esc_html__( 'If you need any help, you can contact us via email us at %1$ssupport@wpboutik.com%2$s.', 'wpboutik' ), '<a href="mailto:support@wpboutik.com?subject=Besoinb d\'aide depuis l\'admin du plugin WPBoutik" target="_blank">', '</a>' );
				echo ' ';
				// translators: 1 HTML Tag, 2 HTML Tag
				echo sprintf( esc_html__( 'You can also check our %1$sFAQ%2$s.', 'wpboutik' ), '<a href="' . esc_url( WPBOUTIK_DOC_URL ) . '" target="_blank">', '</a>' ); ?>
            </p>
            <hr>
		<?php endif; ?>
    </div>
	<?php
	$language     = wpboutik_get_option_params( 'language' );
	$project_slug = wpboutik_get_option_params( 'project_slug' );
	if ( ! empty( $this->options['apikey'] ) && ! empty( $project_slug ) ) :
		?>
        <div class="wpboutik-infobox">
            <h3><?php esc_html_e( 'Where are my products?', 'wpboutik' ); ?></h3>
            <div>
                <p><?php esc_html_e( 'You can find all your products in your WPBoutik account:', 'wpboutik' ); ?></p>
                <a href="<?php echo esc_url( WPBOUTIK_APP_URL . $language . '/' . $project_slug . '/products' ); ?>"
                   target="_blank" class="wpboutik-editbtn">
					<?php esc_html_e( 'Edit my products', 'wpboutik' ); ?>
                </a>
                <p>
                    <span class="wp-menu-image dashicons-before dashicons-welcome-comments"></span><?php esc_html_e( 'When you edit your products in WPBoutik, remember to clear your cache (if you have a cache plugin) to make sure you see the latest version of your page)', 'wpboutik' ); ?>
                </p>
            </div>
            <a href="javascript:void(0);" onclick="confirmerFonctionWordPress()">Déconnectez mon projet</a>
        </div>
	<?php
	endif;
	?>
</div>

