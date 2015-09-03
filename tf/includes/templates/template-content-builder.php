<!doctype html>
<html <?php echo tf_get_html_schema(); ?> <?php language_attributes(); ?>>

	<head>
		<meta charset="<?php bloginfo('charset'); ?>">

		<title><?php echo is_home() || is_front_page()? get_bloginfo('name') : wp_title('');?></title>

		<!-- wp_header -->
		<?php wp_head(); ?>
	</head>

	<body <?php body_class(); ?>>

		<div class="single-template-part-container">

			<?php if (have_posts()) while (have_posts()) : the_post(); ?>
				
				<div class="tf_template_part_title">
					<?php echo sprintf( __('Content: %s', 'themify-flow'), get_the_title() ); ?>
				</div>
				
				<div id="content" class="tf_content">
					<div class="tf_content_builder"><?php the_content(); ?></div>
				</div>

			<?php endwhile; ?>

		</div>
		<!-- /.single-template-builder-container -->

	<!-- wp_footer -->
	<?php wp_footer(); ?>

	</body>

</html>