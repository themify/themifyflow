<?php
/**
 * Template for BuddyPress pages
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */

function tf_buddypress() {
	while( have_posts() ) : the_post();
		the_content();
	endwhile;
}
add_action( 'tf_template_after_layout_content_render', 'tf_buddypress' );

do_action( 'tf_template_render', basename( __FILE__ ) );