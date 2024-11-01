<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WP_REST_Posts_Controller' ) ) {

	/**
	 * Class Grunion_Contact_Form_Endpoint
	 * Used as 'rest_controller_class' parameter when 'feedback' post type is registered in modules/contact-form/grunion-contact-form.php.
	 */
	class WPBoutik_Endpoint extends WP_REST_Posts_Controller {

		/**
		 * Constructor.
		 *
		 * @param string $post_type Post type.
		 */
		public function __construct( $post_type ) {
			parent::__construct( $post_type );
			$this->post_type = $post_type;

			$this->meta = new WPBoutik_REST_Post_Meta_Fields( $this->post_type );
		}

		/**
		 * Checks if a given request has access to create a post.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 *
		 * @return true|WP_Error True if the request has access to create items, WP_Error object otherwise.
		 */
		public function create_item_permissions_check( $request ) {
			if ( ! empty( $request['id'] ) ) {
				return new \WP_Error(
					'rest_post_exists',
					__( 'Cannot create existing post.' ),
					array( 'status' => 400 )
				);
			}

			if ( ! isset( $request['api_key'] ) ) {
				return new \WP_Error( 'invalid_param', sprintf( esc_html__( 'Not param correct api key.', 'wpboutik' ) ) );
			}

			if ( ! empty( $request['api_key'] ) && ( ! is_string( $request['api_key'] ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $request['api_key'] ) ) ) {
				/* translators: %s is replaced with api key */
				return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $request['api_key'] ) );
			}

			return true;
		}

		/**
		 * Checks if a post can be deleted.
		 *
		 * @param WP_Post $post Post object.
		 *
		 * @return bool Whether the post can be deleted.
		 * @since 4.7.0
		 *
		 */
		protected function check_delete_permission( $post ) {
			$post_type = get_post_type_object( $post->post_type );

			return true;
		}

		/**
		 * Checks if a given request has access to update a post.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 *
		 * @return true|WP_Error True if the request has access to update the item, WP_Error object otherwise.
		 * @since 4.7.0
		 *
		 */
		public function update_item_permissions_check( $request ) {
			$post = $this->get_post( $request['id'] );
			if ( is_wp_error( $post ) ) {
				return $post;
			}

			$post_type = get_post_type_object( $this->post_type );

			return true;
		}

		/**
		 * Checks if a given request has access to delete a post.
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 *
		 * @return true|WP_Error True if the request has access to delete the item, WP_Error object otherwise.
		 */
		public function delete_item_permissions_check( $request ) {
			$post = $this->get_post( $request['id'] );
			if ( is_wp_error( $post ) ) {
				return $post;
			}

			if ( ! isset( $request['api_key'] ) ) {
				return new \WP_Error( 'invalid_param', sprintf( esc_html__( 'Not param correct api key.', 'wpboutik' ) ) );
			}

			if ( ! empty( $request['api_key'] ) && ( ! is_string( $request['api_key'] ) || ! preg_match( '/^[wpboutik_a-z0-9]+$/i', $request['api_key'] ) ) ) {
				/* translators: %s is replaced with api key */
				return new \WP_Error( 'invalid_param', sprintf( esc_html__( '%s must be an alphanumeric string.', 'wpboutik' ), $request['api_key'] ) );
			}

			return true;
		}

		/**
		 * Determines validity and normalizes the given status parameter.
		 *
		 * @param string $post_status Post status.
		 * @param WP_Post_Type $post_type Post type.
		 *
		 * @return string|WP_Error Post status or WP_Error if lacking the proper permission.
		 * @since 4.7.0
		 *
		 */
		protected function handle_status_param( $post_status, $post_type ) {

			switch ( $post_status ) {
				case 'draft':
				case 'pending':
					break;
				case 'private':
					if ( ! current_user_can( $post_type->cap->publish_posts ) ) {
						return new \WP_Error(
							'rest_cannot_publish',
							__( 'Sorry, you are not allowed to create private posts in this post type.' ),
							array( 'status' => rest_authorization_required_code() )
						);
					}
					break;
				case 'publish':
				case 'future':
					break;
				default:
					if ( ! get_post_status_object( $post_status ) ) {
						$post_status = 'draft';
					}
					break;
			}

			return $post_status;
		}

	}

}

if ( class_exists( 'WP_REST_Post_Meta_Fields' ) ) {

	/**
	 * Class Grunion_Contact_Form_Endpoint
	 * Used as 'rest_controller_class' parameter when 'feedback' post type is registered in modules/contact-form/grunion-contact-form.php.
	 */
	class WPBoutik_REST_Post_Meta_Fields extends WP_REST_Post_Meta_Fields {
		/**
		 * Deletes a meta value for an object.
		 *
		 * @param int $object_id Object ID the field belongs to.
		 * @param string $meta_key Key for the field.
		 * @param string $name Name for the field that is exposed in the REST API.
		 *
		 * @return true|WP_Error True if meta field is deleted, WP_Error otherwise.
		 * @since 4.7.0
		 *
		 */
		protected function delete_meta_value( $object_id, $meta_key, $name ) {
			$meta_type = $this->get_meta_type();

			if ( null === get_metadata_raw( $meta_type, $object_id, wp_slash( $meta_key ) ) ) {
				return true;
			}

			if ( ! delete_metadata( $meta_type, $object_id, wp_slash( $meta_key ) ) ) {
				return new \WP_Error(
					'rest_meta_database_error',
					__( 'Could not delete meta value from database.' ),
					array(
						'key'    => $name,
						'status' => WP_Http::INTERNAL_SERVER_ERROR,
					)
				);
			}

			return true;
		}

		/**
		 * Updates multiple meta values for an object.
		 *
		 * Alters the list of values in the database to match the list of provided values.
		 *
		 * @param int $object_id Object ID to update.
		 * @param string $meta_key Key for the custom field.
		 * @param string $name Name for the field that is exposed in the REST API.
		 * @param array $values List of values to update to.
		 *
		 * @return bool|WP_Error True if meta fields are updated, WP_Error otherwise.
		 * @since 4.7.0
		 *
		 */
		protected function update_multi_meta_value( $object_id, $meta_key, $name, $values ) {
			$meta_type = $this->get_meta_type();

			$current_values = get_metadata( $meta_type, $object_id, $meta_key, false );
			$subtype        = get_object_subtype( $meta_type, $object_id );

			$to_remove = $current_values;
			$to_add    = $values;

			foreach ( $to_add as $add_key => $value ) {
				$remove_keys = array_keys(
					array_filter(
						$current_values,
						function ( $stored_value ) use ( $meta_key, $subtype, $value ) {
							return $this->is_meta_value_same_as_stored_value( $meta_key, $subtype, $stored_value, $value );
						}
					)
				);

				if ( empty( $remove_keys ) ) {
					continue;
				}

				if ( count( $remove_keys ) > 1 ) {
					// To remove, we need to remove first, then add, so don't touch.
					continue;
				}

				$remove_key = $remove_keys[0];

				unset( $to_remove[ $remove_key ] );
				unset( $to_add[ $add_key ] );
			}

			/*
			 * `delete_metadata` removes _all_ instances of the value, so only call once. Otherwise,
			 * `delete_metadata` will return false for subsequent calls of the same value.
			 * Use serialization to produce a predictable string that can be used by array_unique.
			 */
			$to_remove = array_map( 'maybe_unserialize', array_unique( array_map( 'maybe_serialize', $to_remove ) ) );

			foreach ( $to_remove as $value ) {
				if ( ! delete_metadata( $meta_type, $object_id, wp_slash( $meta_key ), wp_slash( $value ) ) ) {
					return new \WP_Error(
						'rest_meta_database_error',
						/* translators: %s: Custom field key. */
						sprintf( __( 'Could not update the meta value of %s in database.' ), $meta_key ),
						array(
							'key'    => $name,
							'status' => WP_Http::INTERNAL_SERVER_ERROR,
						)
					);
				}
			}

			foreach ( $to_add as $value ) {
				if ( ! add_metadata( $meta_type, $object_id, wp_slash( $meta_key ), wp_slash( $value ) ) ) {
					return new \WP_Error(
						'rest_meta_database_error',
						/* translators: %s: Custom field key. */
						sprintf( __( 'Could not update the meta value of %s in database.' ), $meta_key ),
						array(
							'key'    => $name,
							'status' => WP_Http::INTERNAL_SERVER_ERROR,
						)
					);
				}
			}

			return true;
		}

		/**
		 * Updates a meta value for an object.
		 *
		 * @param int $object_id Object ID to update.
		 * @param string $meta_key Key for the custom field.
		 * @param string $name Name for the field that is exposed in the REST API.
		 * @param mixed $value Updated value.
		 *
		 * @return bool|WP_Error True if the meta field was updated, WP_Error otherwise.
		 * @since 4.7.0
		 *
		 */
		protected function update_meta_value( $object_id, $meta_key, $name, $value ) {
			$meta_type = $this->get_meta_type();

			// Do the exact same check for a duplicate value as in update_metadata() to avoid update_metadata() returning false.
			$old_value = get_metadata( $meta_type, $object_id, $meta_key );
			$subtype   = get_object_subtype( $meta_type, $object_id );

			if ( 1 === count( $old_value ) && $this->is_meta_value_same_as_stored_value( $meta_key, $subtype, $old_value[0], $value ) ) {
				return true;
			}

			if ( ! update_metadata( $meta_type, $object_id, wp_slash( $meta_key ), wp_slash( $value ) ) ) {
				return new \WP_Error(
					'rest_meta_database_error',
					/* translators: %s: Custom field key. */
					sprintf( __( 'Could not update the meta value of %s in database.' ), $meta_key ),
					array(
						'key'    => $name,
						'status' => WP_Http::INTERNAL_SERVER_ERROR,
					)
				);
			}

			return true;
		}
	}

}