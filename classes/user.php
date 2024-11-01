<?php

namespace NF\WPBOUTIK;

class User {

	use Singleton;

	public function __construct() {
		add_action( 'wp_footer', array( __CLASS__, 'wpb_display_admin_user_link' ) );
		add_action( 'init', array( __CLASS__, 'wpb_reconnect_as_admin' ) );
	}

	public static function wpb_display_admin_user_link() {
		// Vérifiez si l'ID de l'utilisateur administrateur précédent est stocké dans la variable de session
		if ( isset( $_SESSION['admin_user_id'] ) ) {
			$admin_user_id = $_SESSION['admin_user_id'];

			// Obtenez le lien de connexion en tant qu'utilisateur administrateur précédent
			$admin_user_login_url = wp_login_url( add_query_arg( 'login_as_admin', 'true' ) );

			// Affichez le lien pour vous reconnecter en tant qu'utilisateur administrateur précédent
			echo '<p><a href="' . esc_url( $admin_user_login_url ) . '">Reconnectez-vous en tant qu\'utilisateur administrateur précédent</a></p>';
		}
	}

	public static function wpb_reconnect_as_admin() {
		if ( isset( $_GET['login_as_admin'] ) && $_GET['login_as_admin'] === 'true' ) {
			// Vérifiez si l'ID de l'utilisateur administrateur précédent est stocké dans la variable de session
			if ( isset( $_SESSION['admin_user_id'] ) ) {
				$admin_user_id = $_SESSION['admin_user_id'];

				// Connectez-vous en tant qu'utilisateur administrateur précédent
				wp_set_current_user( $admin_user_id );
				wp_set_auth_cookie( $admin_user_id );
				do_action( 'wp_login', get_user_by( 'id', $admin_user_id )->user_login );

				// Redirigez vers la page d'accueil ou toute autre page souhaitée
				wp_redirect( home_url( '/' ) );
				exit;
			}
		}
	}

}
