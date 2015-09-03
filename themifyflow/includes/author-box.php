<?php
/**
 * Post Author Box Template.
 * 
 * @package ThemifyFlow
 * @since 1.0.0
 */

if( ! isset( $avatar_size ) ) {
	$avatar_size = 50;
}
?>

	<div class="tf_author_box clearfix" itemscope itemtype="http://data-vocabulary.org/Person">
	
		<p class="tf_author_avatar">
			<?php echo get_avatar( get_the_author_meta('user_email'), $avatar_size, '' ); ?>
		</p>

		<div class="tf_author_bio">
		
			<h4 class="tf_author_name">
				<span itemprop="name">
					<?php if( get_the_author_meta( 'user_url' ) ) { ?>
							<a href="<?php echo get_the_author_meta('user_url'); ?>" itemprop="url"><?php echo get_the_author_meta('first_name').' '.get_the_author_meta('last_name'); ?></a>
					<?php } else { ?>
						<?php echo get_the_author_meta('first_name').' '.get_the_author_meta('last_name'); ?>
					<?php } ?>
				</span>
			</h4>
				<?php echo get_the_author_meta('description'); ?>

				<?php if( get_the_author_meta( 'user_url' ) ) { ?>
						<p class="tf_author_link">
							<a href="<?php echo get_the_author_meta('user_url'); ?>" itemprop="url">&rarr; <?php echo get_the_author_meta('user_firstname').' '.get_the_author_meta('user_lastname'); ?> </a>
						</p>
				<?php } ?>
		</div> 
		<!-- /tf_author_bio -->
			
	</div>	
	<!-- /tf_module_author_box -->		