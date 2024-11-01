<?php
/**
 * Handle data for the current customers session.
 * Implements the WPB_Session abstract class.
 *
 * @class    WPB_Session_Handler
 */

namespace NF\WPBOUTIK;

defined( 'ABSPATH' ) || exit;

/**
 * Session handler class.
 */
class WPB_Session_Handler extends WPB_Session {

	/**
	 * Cookie name used for the session.
	 *
	 * @var string cookie name
	 */
	protected $_cookie;

	/**
	 * if session is registered in bdd.
	 *
	 * @var string cookie name
	 */
	protected $_session_id = null;


	/**
	 * Stores session expiry.
	 *
	 * @var string session due to expire timestamp
	 */
	protected $_session_expiring;

	/**
	 * Stores session due to expire timestamp.
	 *
	 * @var string session expiration timestamp
	 */
	protected $_session_expiration;

	/**
	 * True when the cookie exists.
	 *
	 * @var bool Based on whether a cookie exists.
	 */
	protected $_has_cookie = false;

	/**
	 * Table name for session data.
	 *
	 * @var string Custom session table name
	 */
	protected $_table;

	/**
	 * Constructor for the session class.
	 */
	public function __construct() {
		$this->_cookie = apply_filters( 'wpboutik_cookie', 'wp_wpboutik_session_' . COOKIEHASH );
		$this->_table  = $GLOBALS['wpdb']->prefix . 'wpboutik_sessions';
	}

	/**
	 * Init hooks and session data.
	 *
	 * @since 3.3.0
	 */
	public function init() {
		$this->init_session_cookie();

		add_action( 'wpboutik_set_cart_cookies', array( $this, 'set_customer_session_cookie' ), 10 );
		add_action( 'shutdown', array( $this, 'save_data' ), 20 );
		add_action( 'wp_logout', array( $this, 'destroy_session' ) );

		if ( ! is_user_logged_in() ) {
			add_filter( 'nonce_user_logged_out', array( $this, 'maybe_update_nonce_user_logged_out' ), 10, 2 );
		}
	}

	/**
	 * Setup cookie and customer ID.
	 */
	public function init_session_cookie() {
		$cookie = $this->get_session_cookie();

		if ( $cookie ) {
			// Customer ID will be an MD5 hash id this is a guest session.
			$this->_customer_id        = $cookie[0];
			$this->_session_expiration = $cookie[1];
			$this->_session_expiring   = $cookie[2];
			$this->_has_cookie         = true;
			$this->_data               = $this->get_session_data();

			if ( ! $this->is_session_cookie_valid() ) {
				$this->destroy_session();
				$this->set_session_expiration();
			}

			// If the user logs in, update session.
			if ( is_user_logged_in() && strval( get_current_user_id() ) !== $this->_customer_id ) {
				$guest_session_id   = $this->_customer_id;
				$this->_customer_id = strval( get_current_user_id() );
				$this->_dirty       = true;
				$this->save_data( $guest_session_id );
				$this->set_customer_session_cookie( true );
			}

			// Update session if its close to expiring.
			if ( time() > $this->_session_expiring ) {
				$this->set_session_expiration();
				$this->update_session_timestamp( $this->_customer_id, $this->_session_expiration );
			}
		} else {
			$this->set_session_expiration();
			$this->_customer_id = $this->generate_customer_id();
			$this->_data        = $this->get_session_data();
		}
	}

	/**
	 * Checks if session cookie is expired, or belongs to a logged out user.
	 *
	 * @return bool Whether session cookie is valid.
	 */
	private function is_session_cookie_valid() {
		// If session is expired, session cookie is invalid.
		if ( time() > $this->_session_expiration ) {
			return false;
		}

		// If user has logged out, session cookie is invalid.
		if ( ! is_user_logged_in() && ! $this->is_customer_guest( $this->_customer_id ) ) {
			return false;
		}

		// Session from a different user is not valid. (Although from a guest user will be valid)
		if ( is_user_logged_in() && ! $this->is_customer_guest( $this->_customer_id ) && strval( get_current_user_id() ) !== $this->_customer_id ) {
			return false;
		}

		return true;
	}

	/**
	 * Sets the session cookie on-demand (usually after adding an item to the cart).
	 *
	 * Since the cookie name (as of 2.1) is prepended with wp, cache systems like batcache will not cache pages when set.
	 *
	 * Warning: Cookies will only be set if this is called before the headers are sent.
	 *
	 * @param bool $set Should the session cookie be set.
	 */
	public function set_customer_session_cookie( $set ) {
		if ( $set ) {
			$to_hash           = $this->_customer_id . '|' . $this->_session_expiration;
			$cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
			$cookie_value      = $this->_customer_id . '||' . $this->_session_expiration . '||' . $this->_session_expiring . '||' . $cookie_hash;
			$this->_has_cookie = true;

			if ( ! isset( $_COOKIE[ $this->_cookie ] ) || $_COOKIE[ $this->_cookie ] !== $cookie_value ) {
				wpb_setcookie( $this->_cookie, $cookie_value, $this->_session_expiration, true, true );
			}
		}
	}

	/**
	 * Return true if the current user has an active session, i.e. a cookie to retrieve values.
	 *
	 * @return bool
	 */
	public function has_session() {
		return isset( $_COOKIE[ $this->_cookie ] ) || $this->_has_cookie || is_user_logged_in(); // @codingStandardsIgnoreLine.
	}

	/**
	 * Set session expiration.
	 */
	public function set_session_expiration() {
		$this->_session_expiring   = time() + intval( apply_filters( 'wpb_session_expiring', 60 * 60 * 47 ) ); // 47 Hours.
		$this->_session_expiration = time() + intval( apply_filters( 'wpb_session_expiration', 60 * 60 * 48 ) ); // 48 Hours.
	}

	/**
	 * Generate a unique customer ID for guests, or return user ID if logged in.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @return string
	 */
	public function generate_customer_id() {
		$customer_id = '';

		if ( is_user_logged_in() ) {
			$customer_id = strval( get_current_user_id() );
		}

		if ( empty( $customer_id ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hasher      = new \PasswordHash( 8, false );
			$customer_id = 't_' . substr( md5( $hasher->get_random_bytes( 32 ) ), 2 );
		}

		return $customer_id;
	}

	/**
	 * Checks if this is an auto-generated customer ID.
	 *
	 * @param string|int $customer_id Customer ID to check.
	 *
	 * @return bool Whether customer ID is randomly generated.
	 */
	private function is_customer_guest( $customer_id ) {
		$customer_id = strval( $customer_id );

		if ( empty( $customer_id ) ) {
			return true;
		}

		if ( 't_' === substr( $customer_id, 0, 2 ) ) {
			return true;
		}

		/**
		 * Legacy checks. This is to handle sessions that were created from a previous release.
		 * Maybe we can get rid of them after a few releases.
		 */

		// Almost all random $customer_ids will have some letters in it, while all actual ids will be integers.
		if ( strval( (int) $customer_id ) !== $customer_id ) {
			return true;
		}

		// Performance hack to potentially save a DB query, when same user as $customer_id is logged in.
		if ( is_user_logged_in() && strval( get_current_user_id() ) === $customer_id ) {
			return false;
		} else {
			if ( 0 === $customer_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get session unique ID for requests if session is initialized or user ID if logged in.
	 * Introduced to help with unit tests.
	 *
	 * @return string
	 * @since 5.3.0
	 */
	public function get_customer_unique_id() {
		$customer_id = '';

		if ( $this->has_session() && $this->_customer_id ) {
			$customer_id = $this->_customer_id;
		} elseif ( is_user_logged_in() ) {
			$customer_id = (string) get_current_user_id();
		}

		return $customer_id;
	}

	/**
	 * Get the session cookie, if set. Otherwise return false.
	 *
	 * Session cookies without a customer ID are invalid.
	 *
	 * @return bool|array
	 */
	public function get_session_cookie() {
		$cookie_value = isset( $_COOKIE[ $this->_cookie ] ) ? wp_unslash( $_COOKIE[ $this->_cookie ] ) : false; // @codingStandardsIgnoreLine.

		if ( empty( $cookie_value ) || ! is_string( $cookie_value ) ) {
			return false;
		}

		$parsed_cookie = explode( '||', $cookie_value );

		if ( count( $parsed_cookie ) < 4 ) {
			return false;
		}

		list( $customer_id, $session_expiration, $session_expiring, $cookie_hash ) = $parsed_cookie;

		if ( empty( $customer_id ) ) {
			return false;
		}

		// Validate hash.
		$to_hash = $customer_id . '|' . $session_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
			return false;
		}

		return array( $customer_id, $session_expiration, $session_expiring, $cookie_hash );
	}

	/**
	 * Get session data.
	 *
	 * @return array
	 */
	public function get_session_data() {
		return $this->has_session() ? (array) $this->get_session( $this->_customer_id, array() ) : array();
	}

	/**
	 * Gets a cache prefix. This is used in session names so the entire cache can be invalidated with 1 function call.
	 *
	 * @return string
	 */
	private function get_cache_prefix() {
		return \NF\WPBOUTIK\WPB_Cache_Helper::get_cache_prefix( 'wpb_session_id' );
	}

	/**
	 * Save data and delete guest session.
	 *
	 * @param int $old_session_key session ID before user logs in.
	 */
	public function save_data( $old_session_key = 0 ) {
		// Dirty if something changed - prevents saving nothing new.
		if ( $this->_dirty && $this->has_session() ) {
			global $wpdb;

			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}wpboutik_sessions (`session_key`, `session_value`, `session_expiry`) VALUES (%s, %s, %d)
 					ON DUPLICATE KEY UPDATE `session_value` = VALUES(`session_value`), `session_expiry` = VALUES(`session_expiry`)",
					$this->_customer_id,
					maybe_serialize( $this->_data ),
					$this->_session_expiration
				)
			);
			wp_cache_set( $this->get_cache_prefix() . $this->_customer_id, $this->_data, 'wpb_session_id', $this->_session_expiration - time() );
			$this->_dirty = false;
			if ( get_current_user_id() != $old_session_key && ! is_object( get_user_by( 'id', $old_session_key ) ) ) {
				$this->delete_session( $old_session_key );
			}
		}
	}

	/**
	 * Destroy all session data.
	 */
	public function destroy_session() {
		$this->delete_session( $this->_customer_id );
		$this->forget_session();
	}

	/**
	 * Forget all session data without destroying it.
	 */
	public function forget_session() {
		wpb_setcookie( $this->_cookie, '', time() - YEAR_IN_SECONDS, true, true );

		if ( ! is_admin() ) {
			include_once WPBOUTIK_DIR . 'cart-functions.php';
			wpb_empty_cart();
		}

		$this->_data        = array();
		$this->_dirty       = false;
		$this->_customer_id = $this->generate_customer_id();
	}

	/**
	 * When a user is logged out, ensure they have a unique nonce to manage cart and more using the customer/session ID.
	 * This filter runs everything `wp_verify_nonce()` and `wp_create_nonce()` gets called.
	 *
	 * @param int $uid User ID.
	 * @param string $action The nonce action.
	 *
	 * @return int|string
	 * @since 5.3.0
	 */
	public function maybe_update_nonce_user_logged_out( $uid, $action ) {
		if ( wpb_starts_with( $action, 'wpboutik' ) ) {
			return $this->has_session() && $this->_customer_id ? $this->_customer_id : $uid;
		}

		return $uid;
	}

	/**
	 * Cleanup session data from the database and clear caches.
	 */
	public function cleanup_sessions() {
		global $wpdb;
		$now = strtotime( 'now' );
		// avant suppression, je vais selectionner toutes les sessions qui expires pour les noter comme 'deffinitivement perdue' du côté de l'app
		$sessions = $wpdb->get_results( "SELECT * FROM $this->_table WHERE session_expiry < $now" );
		foreach ( $sessions as $session ) {
			$cart_abandoned = get_option( 'wpboutik_cart_abandoned_' . $session->session_id, false );
			if ( $cart_abandoned ) {
				if ( is_numeric( $session->session_key ) ) {
					$app_id = get_user_meta( $session->session_key, 'wpboutik_cart_abandoned_app', true );
				} else {
					$app_id = get_option( 'wpboutik_cart_abandoned_app_' . $session->session_id, false );
				}
				$api_request = WPB_Api_Request::request( 'abandonned_cart', 'lost' )
				                              ->add_multiple_to_body( [
					                              'app_id' => $app_id,
				                              ] )->exec();
				if ( is_numeric( $session->session_key ) ) {
					delete_user_meta( $session->session_key, 'wpboutik_cart_abandoned' );
					delete_user_meta( $session->session_key, 'wpboutik_cart_abandoned_app' );
					delete_user_meta( $session->session_key, 'wpboutik_cart_abandoned_save_app' );
				} else {
					delete_option( 'wpboutik_cart_abandoned_' . $session->session_id );
					delete_option( 'wpboutik_cart_abandoned_app_' . $session->session_id );
					delete_option( 'wpboutik_cart_abandoned_save_app_' . $session->session_id );
				}
			}
		}
		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->_table WHERE session_expiry < %d", $now ) ); // @codingStandardsIgnoreLine.

		if ( class_exists( '\NF\WPBOUTIK\WPB_Cache_Helper' ) ) {
			\NF\WPBOUTIK\WPB_Cache_Helper::invalidate_cache_group( 'wpb_session_id' );
		}
	}

	/**
	 * Returns the session.
	 *
	 * @param string $customer_id Customer ID.
	 * @param mixed $default Default session value.
	 *
	 * @return string|array
	 */
	public function get_session( $customer_id, $default = false ) {
		global $wpdb;

		if ( defined( 'WP_SETUP_CONFIG' ) ) {
			return false;
		}

		// Try to get it from the cache, it will return false if not present or if object cache not in use.
		$value = wp_cache_get( $this->get_cache_prefix() . $customer_id, 'wpb_session_id' );

		if ( false === $value ) {
			$db_session = $wpdb->get_results( $wpdb->prepare( "SELECT session_id, session_value FROM $this->_table WHERE session_key = %s", $customer_id ) ); // @codingStandardsIgnoreLine.
			if ( empty( $db_session ) ) {
				$value = $default;
			}
			$value             = $db_session[0]->session_value;
			$this->_session_id = $db_session[0]->session_id;
			$cache_duration    = $this->_session_expiration - time();
			if ( 0 < $cache_duration ) {
				wp_cache_add( $this->get_cache_prefix() . $customer_id, $value, 'wpb_session_id', $cache_duration );
			}
		}
		$cart = maybe_unserialize( $value );
		$cart_content = maybe_unserialize($cart['cart']);
		foreach ($cart_content as $key => $value) {
			// var_dump($value);
			$post = get_post($value['product_id']);
			// si le produit n'est plus sur wordpress...
			if (empty($post)) {
				// on vérifie s'il s'agit d'une carte cadeau
				if (!empty($value['variation_id']) && strpos($value['variation_id'], 'gf-') !== false){
					// si c'est une carte cadeau
					// on tente le chargement d'une éventuelle carte cadeau sur le site
					$args = [
						'post_type'   => 'wpboutik_product',
						'numberposts' => 1,
						'fields'			=> 'id',
						'meta_query'  => [
							[
								'key' => 'type',
								'value' => 'gift_card',
							]
						]
					];
					$gift_cards = get_posts($args);
					if(!empty($gift_cards)) {
						$cart_content[$key]['product_id'] = $gift_cards[0]->ID;
						continue;
					}
				}
				unset($cart_content[$key]);
				continue;
			}
			$gestion_stock = get_post_meta( $post->ID, 'gestion_stock', true );
			if ( $gestion_stock == 1 ) {
				// Check si on continu de vendre en cas de rupture
				$continu_rupture = get_post_meta( $post->ID, 'continu_rupture', true );
				if ( $continu_rupture == 1 ) {
					continue;
				}
				// vérification des quantitées disponnibles
				if ( ! empty( $value['variation_id'] ) && $value['variation_id'] != '0' ) {
					$variants  = get_post_meta( $post->ID, 'variants', true );
					$variation = wpboutik_get_variation_by_id( json_decode( $variants ), $value['variation_id'] );
					if ($variation->quantity <= 0) {
						unset($cart_content[$key]);
						continue;
					}
					if ((int) $variation->quantity <= $value['quantity']) {
						$cart_content[$key]['quantity'] = $variation->quantity;
					}
				} else {
					$qty = get_post_meta($post->ID, 'qty', true);
					if ($qty <= 0) {
						unset($cart_content[$key]);
						continue;
					}
					if ((int) $value['quantity'] <= (int) $qty) {
						$cart_content[$key]['quantity'] = $value['quantity'];
					}
				}
			}
		}
		$cart['cart'] = serialize($cart_content);
		return $cart;
	}

	/**
	 * Delete the session from the cache and database.
	 *
	 * @param int $customer_id Customer ID.
	 */
	public function delete_session( $customer_id ) {
		global $wpdb;

		wp_cache_delete( $this->get_cache_prefix() . $customer_id, 'wpb_session_id' );

		$wpdb->delete(
			$this->_table,
			array(
				'session_key' => $customer_id,
			)
		);
	}

	/**
	 * regarde si le panier est considéré comme abandonné
	 */
	public function is_abandoned_cart() {
		if ( is_user_logged_in() ) {
			return get_user_meta( get_current_user_id(), 'wpboutik_cart_abandoned', true );
		} else {
			return get_option( 'wpboutik_cart_abandoned_' . $this->_session_id, false );
		}
	}

	/**
	 * récupère l'identifiant laravel du panier abandonnée
	 */
	public function abandonned_app_id() {
		if ( is_user_logged_in() ) {
			return get_user_meta( get_current_user_id(), 'wpboutik_cart_abandoned_app', true );
		} else {
			return get_option( 'wpboutik_cart_abandoned_app_' . $this->_session_id, null );
		}
	}

	/**
	 * récupère l'identifiant laravel du panier abandonnée
	 */
	public function delete_abandonned_options() {
		if ( is_user_logged_in() ) {
			delete_user_meta( get_current_user_id(), 'wpboutik_cart_abandoned' );
			delete_user_meta( get_current_user_id(), 'wpboutik_cart_abandoned_app' );
			delete_user_meta( get_current_user_id(), 'wpboutik_cart_abandoned_save_app' );
		} else {
			delete_option( 'wpboutik_cart_abandoned_' . $this->_session_id );
			delete_option( 'wpboutik_cart_abandoned_app_' . $this->_session_id );
			delete_option( 'wpboutik_cart_abandoned_save_app_' . $this->_session_id );
		}
	}

	/**
	 * Update the session expiry timestamp.
	 *
	 * @param string $customer_id Customer ID.
	 * @param int $timestamp Timestamp to expire the cookie.
	 */
	public function update_session_timestamp( $customer_id, $timestamp ) {
		global $wpdb;

		$wpdb->update(
			$this->_table,
			array(
				'session_expiry' => $timestamp,
			),
			array(
				'session_key' => $customer_id,
			),
			array(
				'%d',
			)
		);
	}
}
