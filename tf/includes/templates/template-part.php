<!doctype html>
<html <?php language_attributes(); ?>>

	<head>
		<meta charset="<?php bloginfo('charset'); ?>">

		<!-- wp_header -->
		<?php
		/**
		 * @hooked Themify_Flow::title_tag_fallback (if WP version < 4.1) - 10
		 */
		wp_head(); ?>
	</head>

	<body <?php body_class(); ?>>

		<div class="single-template-part-container">

			<?php if (have_posts()) while (have_posts()) : the_post(); ?>
				
				<div class="tf_template_part_title">
					<?php echo sprintf( __('<span>Template Part:</span> %s', 'themify-flow'), get_the_title() ); ?>
				</div>
				<!-- /tf_template_part_title -->
				
				<?php the_content(); ?>

			<?php endwhile; ?>

		</div>
		<!-- /.single-template-builder-container -->

	<!-- wp_footer -->
	<?php wp_footer(); ?>

	</body>

</html>