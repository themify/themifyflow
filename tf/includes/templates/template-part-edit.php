<!doctype html>
<html <?php echo tf_get_html_schema(); ?> <?php language_attributes(); ?>>

	<head>
		<meta charset="<?php bloginfo('charset'); ?>">

		<!-- wp_header -->
		<?php
		/**
		 * @hooked Themify_Flow::title_tag_fallback (if WP version < 4.1) - 10
		 */
		wp_head(); ?>
	</head>

	<body <?php body_class('template-part-edit'); ?>>

		<div class="single-template-part-container">

			<?php if (have_posts()) while (have_posts()) : the_post(); ?>
				
				<div class="tf_template_part_title">
					<?php
					$title = is_singular( 'tf_template_part' ) ? __('<span>Template Part:</span> %s', 'themify-flow') : __('Content: %s', 'themify-flow');
					echo sprintf( $title, get_the_title() ); ?>
				</div>
				
				<div class="tf_content">
					<div class="tf_content_builder"><?php echo $TF_Layout->render( get_the_content() ); ?></div>
				</div>

			<?php endwhile; ?>

		</div>
		<!-- /.single-template-builder-container -->

	<!-- wp_footer -->
	<?php wp_footer(); ?>

	</body>

</html>