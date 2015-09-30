<?php
/**
 * Template for comments.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
?>

<?php tf_hook_comment_before(); //hook ?>

<?php if ( have_comments() || comments_open() ) : ?>

<div id="comments" class="tf_module_comments" itemscope itemtype="http://schema.org/UserComments">

	<?php tf_hook_comment_start(); //hook ?>

	<?php if ( post_password_required() && have_comments() ) : ?>

		<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'themify-flow' ); ?></p>

	<?php elseif ( have_comments() ) : ?>

		<h3 class="comments-title"><?php comments_number(__('No Comments','themify-flow'), __('One Comment','themify-flow'), __('% Comments','themify-flow') );?></h3>

		<?php // Comment Pagination
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<div class="tf_pagination top clearfix">
				<?php paginate_comments_links( array('prev_text' => '&lsaquo;', 'next_text' => '&rsaquo;') );?>
			</div>
			<!-- /.tf_pagination -->
		<?php endif; ?>

		<ol class="comment-list">
			<?php wp_list_comments('callback=tf_theme_comment'); ?>
		</ol>

		<?php // Comment Pagination
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<div class="tf_pagination bottom clearfix">
				<?php paginate_comments_links( array('prev_text' => '&lsaquo;', 'next_text' => '&rsaquo;') );?>
			</div>
			<!-- /.tf_pagination -->
		<?php endif; ?>

	<?php endif; // end have_comments() ?>

	<?php if ( comments_open() ) : ?>

		<?php
                    global $req, $aria_req, $user_identity;
                    global $aria_req;
                    $custom_comment_form = array( 'fields' => apply_filters( 'comment_form_default_fields', array(
			'author' => '<p class="comment-form-author">' .
					'<input id="author" name="author" type="text" value="' .
					esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' class="required" />' .
					'<label for="author">' . __( 'Your Name' , 'themify-flow' ) . '</label> ' .
					( $req ? '<span class="required">*</span>' : '' ) .
					'</p>',
			'email'  => '<p class="comment-form-email">' .
					'<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' class="required email" />' .
					'<label for="email">' . __( 'Your Email' , 'themify-flow' ) . '</label> ' .
					( $req ? '<span class="required">*</span>' : '' ) .
					'</p>',
			'url'    =>  '<p class="comment-form-url">' .
					'<input id="url" name="url" type="text" value="' . esc_attr(  $commenter['comment_author_url'] ) . '" size="30"' . $aria_req . ' />' .
					'<label for="website">' . __( 'Your Website' , 'themify-flow' ) . '</label> ' .
					'</p>') ),
			'comment_field' => '<p class="comment-form-comment">' .
					'<textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" class="required"></textarea>' .
					'</p>',
			'logged_in_as' => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%s">%s</a>. <a href="%s">Log out?</a>', 'themify-flow' ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( get_the_ID() ) ) )	) . '</p>',
			'comment_notes_before' => '',
			'comment_notes_after' => '',
			'cancel_reply_link' => __( 'Cancel' , 'themify-flow' ),
			'label_submit' => __( 'Post Comment' , 'themify-flow' ),
		);
		comment_form($custom_comment_form);
		?>

	<?php endif; // end comments_open() ?>

	<?php tf_hook_comment_end(); //hook ?>

</div>
<!-- /.tf_module_comments -->

<?php endif; ?>

<?php tf_hook_comment_after(); //hook ?>