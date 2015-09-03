<?php
/**
 * Template for fallback single post view (ie. when no Flow Template assigned, this loop template will be in use)
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */

function render_content() { ?>
	
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<?php get_template_part( 'includes/loop' , 'single'); ?>
	
		<?php wp_link_pages(array('before' => '<p><strong>' . __('Pages:', 'themify-flow') . ' </strong>', 'after' => '</p>', 'next_or_number' => 'number')); ?>
		
		<?php comments_template(); ?>

	<?php endwhile; endif; ?>
	
<?php
}

add_action( 'tf_template_render_content', 'render_content' );

do_action( 'tf_template_render', basename( __FILE__ ) );