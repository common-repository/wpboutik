<?php

namespace NF\WPBOUTIK;

defined( 'ABSPATH' ) || exit;

class WPB_Customer {
	private $user_id;

	public function __construct( $user_id = null, $is_session = false ) {
		if ( $user_id ) {
			$this->user_id = $user_id;
		} else {
			$this->user_id = get_current_user_id();
		}

		// If this is a session, set or change the data store to sessions. Changes do not persist in the database.
		if ( $is_session && isset( WPB()->session ) ) {
			//$this->data_store = WC_Data_Store::load( 'customer-session' );
			//$this->data_store->read( $this );
		}
	}

	public function get_billing_first_name() {
		if ( $this->user_id != 0 ) {
			$wpboutik_billing_first_name = get_user_meta( $this->user_id, 'wpboutik_billing_first_name', true );

			return $wpboutik_billing_first_name;
		} else {
			return '';
		}
	}

	public function set_billing_first_name( $billing_first_name ) {
		update_user_meta( $this->user_id, 'wpboutik_billing_first_name', $billing_first_name );
	}

	public function get_billing_last_name() {
		if ( $this->user_id != 0 ) {
			$wpboutik_billing_last_name = get_user_meta( $this->user_id, 'wpboutik_billing_last_name', true );

			return $wpboutik_billing_last_name;
		} else {
			return '';
		}
	}

	public function set_billing_last_name( $billing_last_name ) {
		update_user_meta( $this->user_id, 'wpboutik_billing_last_name', $billing_last_name );
	}

	public function get_billing_company() {
		if ( $this->user_id != 0 ) {
			$wpboutik_billing_company = get_user_meta( $this->user_id, 'wpboutik_billing_company', true );

			return $wpboutik_billing_company;
		} else {
			return '';
		}
	}

	public function set_billing_company( $billing_company ) {
		update_user_meta( $this->user_id, 'wpboutik_billing_first_name', $billing_company );
	}

	public function get_billing_phone() {
		if ( $this->user_id != 0 ) {
			$wpboutik_billing_phone = get_user_meta( $this->user_id, 'wpboutik_billing_phone', true );

			return $wpboutik_billing_phone;
		} else {
			return '';
		}
	}

	public function set_billing_phone( $billing_phone ) {
		update_user_meta( $this->user_id, 'wpboutik_billing_phone', $billing_phone );
	}

	public function get_billing_address() {
		if ( $this->user_id != 0 ) {
			$wpboutik_billing_address = get_user_meta( $this->user_id, 'wpboutik_billing_address', true );

			return $wpboutik_billing_address;
		} else {
			return '';
		}
	}

	public function set_billing_address( $wpboutik_billing_address ) {
		update_user_meta( $this->user_id, 'wpboutik_billing_address', $wpboutik_billing_address );
	}

	public function get_billing_city() {
		if ( $this->user_id != 0 ) {
			$wpboutik_billing_city = get_user_meta( $this->user_id, 'wpboutik_billing_city', true );

			return $wpboutik_billing_city;
		} else {
			return '';
		}
	}

	public function set_billing_city( $billing_city ) {
		update_user_meta( $this->user_id, 'wpboutik_billing_city', $billing_city );
	}

	public function get_billing_country() {
		if ( $this->user_id != 0 ) {
			$wpboutik_billing_country = get_user_meta( $this->user_id, 'wpboutik_billing_country', true );

			return $wpboutik_billing_country;
		} else {
			return '';
		}
	}

	public function set_billing_country( $billing_country ) {
		update_user_meta( $this->user_id, 'wpboutik_billing_country', $billing_country );
	}

	public function get_billing_postal_code() {
		if ( $this->user_id != 0 ) {
			$wpboutik_billing_postal_code = get_user_meta( $this->user_id, 'wpboutik_billing_postal_code', true );

			return $wpboutik_billing_postal_code;
		} else {
			return '';
		}
	}

	public function set_billing_postal_code( $billing_postal_code ) {
		update_user_meta( $this->user_id, 'wpboutik_billing_postal_code', $billing_postal_code );
	}

	public function get_shipping_first_name() {
		if ( $this->user_id != 0 ) {
			$wpboutik_shipping_first_name = get_user_meta( $this->user_id, 'wpboutik_shipping_first_name', true );

			return $wpboutik_shipping_first_name;
		} else {
			return '';
		}
	}

	public function set_shipping_first_name( $shipping_first_name ) {
		update_user_meta( $this->user_id, 'wpboutik_shipping_first_name', $shipping_first_name );
	}

	public function get_shipping_last_name() {
		if ( $this->user_id != 0 ) {
			$wpboutik_shipping_last_name = get_user_meta( $this->user_id, 'wpboutik_shipping_last_name', true );

			return $wpboutik_shipping_last_name;
		} else {
			return '';
		}
	}

	public function set_shipping_last_name( $shipping_last_name ) {
		update_user_meta( $this->user_id, 'wpboutik_shipping_last_name', $shipping_last_name );
	}

	public function get_shipping_company() {
		if ( $this->user_id != 0 ) {
			$wpboutik_shipping_company = get_user_meta( $this->user_id, 'wpboutik_shipping_company', true );

			return $wpboutik_shipping_company;
		} else {
			return '';
		}
	}

	public function set_shipping_company( $shipping_company ) {
		update_user_meta( $this->user_id, 'wpboutik_shipping_first_name', $shipping_company );
	}

	public function get_shipping_phone() {
		if ( $this->user_id != 0 ) {
			$wpboutik_shipping_phone = get_user_meta( $this->user_id, 'wpboutik_shipping_phone', true );

			return $wpboutik_shipping_phone;
		} else {
			return '';
		}
	}

	public function set_shipping_phone( $shipping_phone ) {
		update_user_meta( $this->user_id, 'wpboutik_shipping_phone', $shipping_phone );
	}

	public function get_shipping_address() {
		if ( $this->user_id != 0 ) {
			$wpboutik_shipping_address = get_user_meta( $this->user_id, 'wpboutik_shipping_address', true );

			return $wpboutik_shipping_address;
		} else {
			return '';
		}
	}

	public function set_shipping_address( $wpboutik_shipping_address ) {
		update_user_meta( $this->user_id, 'wpboutik_shipping_address', $wpboutik_shipping_address );
	}

	public function get_shipping_city() {
		if ( $this->user_id != 0 ) {
			$wpboutik_shipping_city = get_user_meta( $this->user_id, 'wpboutik_shipping_city', true );

			return $wpboutik_shipping_city;
		} else {
			return '';
		}
	}

	public function set_shipping_city( $shipping_city ) {
		update_user_meta( $this->user_id, 'wpboutik_shipping_city', $shipping_city );
	}

	public function get_shipping_country() {
		if ( $this->user_id != 0 ) {
			$wpboutik_shipping_country = get_user_meta( $this->user_id, 'wpboutik_shipping_country', true );

			return $wpboutik_shipping_country;
		} else {
			return '';
		}
	}

	public function set_shipping_country( $shipping_country ) {
		update_user_meta( $this->user_id, 'wpboutik_shipping_country', $shipping_country );
	}

	public function get_shipping_postal_code() {
		if ( $this->user_id != 0 ) {
			$wpboutik_shipping_postal_code = get_user_meta( $this->user_id, 'wpboutik_shipping_postal_code', true );

			return $wpboutik_shipping_postal_code;
		} else {
			return '';
		}
	}

	public function set_shipping_postal_code( $shipping_postal_code ) {
		update_user_meta( $this->user_id, 'wpboutik_shipping_postal_code', $shipping_postal_code );
	}

	// Méthode pour mettre à jour les données en session lors de la connexion
	public function update_session_on_login( $user_id ) {
		// Récupère l'adresse de facturation depuis les données de l'utilisateur
		$billing_address = get_user_meta( $user_id, 'billing_address', true );

		// Met à jour l'adresse de facturation en session si elle est définie pour l'utilisateur
		if ( ! empty( $billing_address ) ) {
			$this->set_billing_address( $billing_address );
		}
	}

	// Méthode pour mettre à jour les données en session lors d'un ajout sur une page
	public function update_session_on_page_addition( $billing_address ) {
		// Met à jour l'adresse de facturation en session avec les données ajoutées sur la page
		$this->set_billing_address( $billing_address );
	}
}