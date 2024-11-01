<?php
/**
 * Review Comments Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="pt-10 lg:grid lg:grid-cols-12 lg:gap-x-8" id="li-comment-<?php comment_ID(); ?>">
    <div class="lg:col-span-8 lg:col-start-5 xl:col-span-9 xl:col-start-4 xl:grid xl:grid-cols-3 xl:items-start xl:gap-x-8">
        <div class="flex items-center xl:col-span-1">
	        <?php
	        /**
	         * The wpboutik_review_before_comment_meta hook.
	         *
	         * @hooked wpboutik_review_display_rating - 10
	         */
	        do_action( 'wpboutik_review_before_comment_meta', $comment );
	        ?>
        </div>

        <div class="mt-4 lg:mt-6 xl:col-span-2 xl:mt-0">
            <div class="mt-3 space-y-6 text-sm text-gray-500">
	            <?php
                /**
                 * The wpboutik_review_comment_text hook
                 *
                 * @hooked wpboutik_review_display_comment_text - 10
                 */
                do_action( 'wpboutik_review_comment_text', $comment );

                do_action( 'wpboutik_review_after_comment_text', $comment );
                ?>
            </div>
        </div>
    </div>

    <?php
    /**
     * The wpboutik_review_meta hook.
     *
     * @hooked wpboutik_review_display_meta - 10
     */
    do_action( 'wpboutik_review_meta', $comment );

    do_action( 'wpboutik_review_before_comment_text', $comment );
    ?>
</div>
