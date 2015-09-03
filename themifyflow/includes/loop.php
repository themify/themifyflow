<?php
/**
 * Template for fallback blog post loop (ie. when no Flow Template assigned, this template will be in use)
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */
?>
<?php if(!is_single()){ global $more; $more = 0; } //enable more link ?>

<?php tf_hook_post_before(); // hook ?>

<article itemscope itemtype="http://schema.org/Article" id="post-<?php the_ID(); ?>" <?php post_class( 'tf_post clearfix' ); ?>>

	<?php tf_hook_post_start(); // hook ?>

	<div class="tf_post_content">
				
		<?php tf_hook_before_post_title(); // Hook ?>

		<h1 class="tf_post_title entry-title" itemprop="name">
			<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
		</h1>

		<?php tf_hook_after_post_title(); // Hook ?>

		<?php the_content(__('More &rarr;', 'themify-flow')); ?>

		<?php edit_post_link(__('Edit', 'themify-flow'), '<span class="edit-button">[', ']</span>'); ?>

	</div>
	<!-- /.post-content -->
	<?php tf_hook_post_end(); // hook ?>

</article>
<!-- /.post -->

<?php tf_hook_post_after(); // hook ?>