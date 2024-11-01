<?php
/**
 * The template to display the reviewers meta data (name, verified owner, review date)
 */

defined( 'ABSPATH' ) || exit;

global $comment;
//$verified = wc_review_is_from_verified_owner( $comment->comment_ID );

if ( '0' === $comment->comment_approved ) { ?>

	<div class="mt-6 flex items-center text-sm lg:col-span-4 lg:col-start-1 lg:row-start-1 lg:mt-0 lg:flex-col lg:items-start xl:col-span-3">
		<p class="font-medium text-gray-900">
		    <em>
			    <?php esc_html_e( 'Your review is awaiting approval', 'wpboutik' ); ?>
            </em>
        </p>
    </div>

<?php } else { ?>

    <div class="mt-6 flex items-center text-sm lg:col-span-4 lg:col-start-1 lg:row-start-1 lg:mt-0 lg:flex-col lg:items-start xl:col-span-3">
	    <?php
	    /*if ( 'yes' === get_option( 'wpboutik_review_rating_verification_label' ) && $verified ) {
			echo '<em class="wpboutik-review__verified verified">(' . esc_attr__( 'verified owner', 'wpboutik' ) . ')</em> ';
		}*/		?>
	    <?php
	    /**
	     * Thewpboutik_review_before hook
	     *
	     * @hooked wpboutik_review_display_gravatar - 10
	     */
	    do_action( 'wpboutik_review_before', $comment );
	    ?>
        <p class="font-medium text-gray-900"><?php comment_author(); ?></p>
        <time datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>" class="ml-4 border-l border-gray-200 pl-4 text-gray-500 lg:ml-0 lg:mt-2 lg:border-0 lg:pl-0"><?php echo esc_html( get_comment_date( wpboutik_date_format() ) ); ?></time>
    </div>

	<?php
}
