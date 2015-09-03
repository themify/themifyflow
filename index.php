<?php
/**
 * Template for common archive pages, author and search results.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */

function render_content() { ?>
	<?php if (have_posts()) : ?>
		
		<!-- loops-wrapper -->
		<div id="tf_loops_wrapper" class="tf_loops_wrapper">

			<?php while (have_posts()) : the_post(); ?>
	
				<?php if(is_search()): ?>
					<?php get_template_part( 'includes/loop' , 'search'); ?>
				<?php else: ?>
					<?php get_template_part( 'includes/loop' , 'index'); ?>
				<?php endif; ?>
	
			<?php endwhile; ?>
						
		</div>
		<!-- /loops-wrapper -->

		<?php get_template_part( 'includes/pagination'); ?>
	
	<?php endif; ?>	
<?php
}

add_action( 'tf_template_render_content', 'render_content' );

do_action( 'tf_template_render', basename( __FILE__ ) );