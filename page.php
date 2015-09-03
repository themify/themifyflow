<?php
/**
 * Template for fallback page view (ie. when no Flow Template assigned, this loop template will be in use)
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */

function render_content() { ?>
	
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<div id="page-<?php the_ID(); ?>" class="type-page" itemscope itemtype="http://schema.org/Article">
						
			<!-- page-title -->
			<h1 class="tf_page_title" itemprop="name"><?php the_title(); ?></h1>
			<!-- /page-title -->

			<div class="tf_page_content entry-content" itemprop="articleBody">
			
				<?php the_content(); ?>
			
				<?php wp_link_pages(array('before' => '<p><strong>'.__('Pages:','themify-flow').'</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
				
				<?php edit_post_link(__('Edit','themify-flow'), '[', ']'); ?>
				
				<!-- comments -->
				<?php comments_template(); ?>
				<!-- /comments -->
				
			</div>
			<!-- /.post-content -->
		
			</div><!-- /.type-page -->
		<?php endwhile; endif; ?>
<?php
}

add_action( 'tf_template_render_content', 'render_content' );

do_action( 'tf_template_render', basename( __FILE__ ) );