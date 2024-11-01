<?php

namespace NF\WPBOUTIK;

class Ratings {

	use Singleton;

	public function __construct() {
		add_action( 'comment_post', array( __CLASS__, 'add_comment_rating' ), 1 );

		/**
		 * Reviews
		 *
		 * @see wpboutik_review_display_gravatar()
		 * @see wpboutik_review_display_rating()
		 * @see wpboutik_review_display_meta()
		 * @see wpboutik_review_display_comment_text()
		 */
		add_action( 'wpboutik_review_before', array( $this, 'wpboutik_review_display_gravatar' ), 10 );
		add_action( 'wpboutik_review_before_comment_meta', array( $this, 'wpboutik_review_display_rating' ), 10 );
		add_action( 'wpboutik_review_meta', array( $this, 'wpboutik_review_display_meta' ), 10 );
		add_action( 'wpboutik_review_comment_text', array( $this, 'wpboutik_review_display_comment_text' ), 10 );

		add_filter( 'comments_template', array( $this, 'wpb_comments_template_loader' ) );
		add_filter( 'comments_open', array( $this, 'wpboutik_product_comments_open' ), 10, 2 );
	}

	/**
	 * Rating field for comments.
	 *
	 * @param int $comment_id Comment ID.
	 */
	public static function add_comment_rating( $comment_id ) {
		if ( isset( $_POST['rating'], $_POST['comment_post_ID'] ) && 'wpboutik_product' === get_post_type( absint( $_POST['comment_post_ID'] ) ) ) { // WPCS: input var ok, CSRF ok.
			if ( ! $_POST['rating'] || $_POST['rating'] > 5 || $_POST['rating'] < 0 ) { // WPCS: input var ok, CSRF ok, sanitization ok.
				return;
			}
			add_comment_meta( $comment_id, 'rating', intval( $_POST['rating'] ), true ); // WPCS: input var ok, CSRF ok.
		}
	}

	/**
	 * Output the metabox.
	 *
	 * @param object $comment Comment being shown.
	 */
	public static function output( $comment ) {
		wp_nonce_field( 'wpboutik_save_data', 'wpboutik_meta_nonce' );

		$current = get_comment_meta( $comment->comment_ID, 'rating', true );
		?>
        <select name="rating" id="rating">
			<?php
			for ( $rating = 1; $rating <= 5; $rating ++ ) {
				printf( '<option value="%1$s"%2$s>%1$s</option>', $rating, selected( $current, $rating, false ) ); // WPCS: XSS ok.
			}
			?>
        </select>
		<?php
	}

	/**
	 * Save meta box data
	 *
	 * @param mixed $data Data to save.
	 *
	 * @return mixed
	 */
	public static function save( $data ) {
		// Not allowed, return regular value without updating meta.
		if ( ! isset( $_POST['wpboutik_meta_nonce'], $_POST['rating'] ) || ! wp_verify_nonce( wp_unslash( $_POST['wpboutik_meta_nonce'] ), 'wpboutik_save_data' ) ) { // WPCS: input var ok, sanitization ok.
			return $data;
		}

		if ( $_POST['rating'] > 5 || $_POST['rating'] < 0 ) { // WPCS: input var ok.
			return $data;
		}

		$comment_id = $data['comment_ID'];

		update_comment_meta( $comment_id, 'rating', intval( wp_unslash( $_POST['rating'] ) ) ); // WPCS: input var ok.

		// Return regular value after updating.
		return $data;
	}

	/**
	 * Display the review authors gravatar
	 *
	 * @param array $comment WP_Comment.
	 *
	 * @return void
	 */
	public function wpboutik_review_display_gravatar( $comment ) {
		echo get_avatar( $comment, apply_filters( 'wpboutik_review_gravatar_size', '60' ), '', '', array( 'class' => 'h-12 w-12 rounded-full' ) );
	}

	/**
	 * Display the reviewers star rating
	 *
	 * @return void
	 */
	public function wpboutik_review_display_rating() {
		if ( post_type_supports( 'wpboutik_product', 'comments' ) ) {
			include trailingslashit( WPBOUTIK_TEMPLATES ) . '/single/product-review-rating.php';
		}
	}

	/**
	 * Display the review authors meta (name, verified owner, review date)
	 *
	 * @return void
	 */
	public function wpboutik_review_display_meta() {
		include trailingslashit( WPBOUTIK_TEMPLATES ) . '/single/product-review-meta.php';
	}

	/**
	 * Display the review content.
	 */
	public function wpboutik_review_display_comment_text() {
		echo '<div class="description">';
		comment_text();
		echo '</div>';
	}

	public function wpb_comments_template_loader( $template ) {
		if ( get_post_type() !== 'wpboutik_product' ) {
			return $template;
		}

		if ( file_exists( trailingslashit( WPBOUTIK_TEMPLATES ) . '/single/product-reviews.php' ) ) {
			return trailingslashit( WPBOUTIK_TEMPLATES ) . '/single/product-reviews.php';
		}
	}

	public function wpboutik_product_comments_open( $open, $post_id ) {

		$post = get_post( $post_id );

		if ( 'wpboutik_product' == $post->post_type ) {
			$open = true;
		}

		return $open;
	}
}
