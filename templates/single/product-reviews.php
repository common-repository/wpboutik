<?php
/**
 * Display single product reviews (comments)
 */

defined( 'ABSPATH' ) || exit;

global $post;

if ( ! comments_open() ) {
	return;
}

?>
<div id="reviews" class="wpboutik-Reviews wpb-single-product-content">
	<div id="comments">
		<?php if ( have_comments() ) : ?>
            <div class="mt-6 space-y-10 divide-y divide-gray-200 border-b border-t border-gray-200 pb-10">
				<?php wp_list_comments( apply_filters( 'wpboutik_product_review_list_args', array( 'callback' => 'wpboutik_comments' ) ) ); ?>
            </div>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="wpboutik-pagination">';
				paginate_comments_links(
					apply_filters(
						'wpboutik_comment_pagination_args',
						array(
							'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
							'next_text' => is_rtl() ? '&larr;' : '&rarr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<p class="wpboutik-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'wpboutik' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( wpboutik_customer_bought_product( '', get_current_user_id(), get_the_ID() ) ) : ?>
		<div id="review_form_wrapper">
			<div id="review_form">
				<?php
				$commenter    = wp_get_current_commenter();
				$comment_form = array(
					/* translators: %s is product title */
					'title_reply'         => have_comments() ? esc_html__( 'Add a review', 'wpboutik' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'wpboutik' ), get_the_title() ),
					/* translators: %s is product title */
					'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'wpboutik' ),
					'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
					'title_reply_after'   => '</span>',
					'comment_notes_after' => '',
					'label_submit'        => esc_html__( 'Submit', 'wpboutik' ),
					'logged_in_as'        => '',
					'comment_field'       => '',
				);

				$name_email_required = (bool) get_option( 'require_name_email', 1 );
				$fields              = array(
					'author' => array(
						'label'    => __( 'Name', 'wpboutik' ),
						'type'     => 'text',
						'value'    => $commenter['comment_author'],
						'required' => $name_email_required,
					),
					'email'  => array(
						'label'    => __( 'Email', 'wpboutik' ),
						'type'     => 'email',
						'value'    => $commenter['comment_author_email'],
						'required' => $name_email_required,
					),
				);

				$comment_form['fields'] = array();

				foreach ( $fields as $key => $field ) {
					$field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
					$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

					if ( $field['required'] ) {
						$field_html .= '&nbsp;<span class="required">*</span>';
					}

					$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

					$comment_form['fields'][ $key ] = $field_html;
				}

				$account_page_url = wpboutik_get_page_permalink( 'account' );
				if ( $account_page_url ) {
					/* translators: %s opening and closing link tags respectively */
					$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'wpboutik' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
				}

				//if ( wc_review_ratings_enabled() ) {
					$comment_form['comment_field'] = '<div class="comment-form-rating wpb-field"><label for="rating">' . esc_html__( 'Your rating', 'wpboutik' ) . ( true ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating" required>
						<option value="">' . esc_html__( 'Rate&hellip;', 'wpboutik' ) . '</option>
						<option value="5">' . esc_html__( 'Perfect', 'wpboutik' ) . '</option>
						<option value="4">' . esc_html__( 'Good', 'wpboutik' ) . '</option>
						<option value="3">' . esc_html__( 'Average', 'wpboutik' ) . '</option>
						<option value="2">' . esc_html__( 'Not that bad', 'wpboutik' ) . '</option>
						<option value="1">' . esc_html__( 'Very poor', 'wpboutik' ) . '</option>
					</select></div>';
				//}

				$comment_form['comment_field'] .= '<p class="comment-form-comment wpb-field"><label for="comment">' . esc_html__( 'Your review', 'wpboutik' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" class="block w-full rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:py-1.5 sm:text-sm sm:leading-6" cols="45" rows="8" required></textarea></p>';

				comment_form( apply_filters( 'wpboutik_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>
	<?php else : ?>
		<p class="wpboutik-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'wpboutik' ); ?></p>
	<?php endif; ?>

	<div class="clear"></div>
</div>
