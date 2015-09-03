<?php
/**
 * This template is for single TF Template View Edit mode.
 * 
 * @package TF
 * @since 1.0.0
 */
?>
<!doctype html>
<html <?php echo tf_get_html_schema(); ?> <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">

	<!-- wp_header -->
	<?php
	/**
	 * @hooked Themify_Flow::title_tag_fallback (if WP version < 4.1) - 10
	 */
	wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	
	<?php tf_hook_body_start(); // hook ?>
	
	<div id="pagewrap" class="hfeed site">
		
		<?php if ( 'default' == $TF_Layout->header ):  ?>
			<div id="headerwrap">
		    
				<?php tf_hook_header_before(); // hook ?>
				
				<header id="header" class="tf_header">
		        
		        <?php tf_hook_header_start(); // hook ?>
				
				<div data-region-area="header" class="tf_template_part_region tf_template_part_region-edit">
					<a href="#" data-region="header" class="tf_interface tf_template_region_button tf_template_region_button-add">
						<span class="ti-plus"></span>
						<?php _e('Add Template Part', 'themify-flow') ?>
					</a>
					<div class="tf_template_part_region_render_content">
						<?php if( ! empty( $TF_Layout->region_header ) ): ?>
						<div class="tf_active_block">
							<div class="tf_active_block_overlay"></div>
							<div class="tf_active_block_menu tf_interface">
								<ul class="tf_active_block_menu_ul">
									<li class="tf_active_block_menu_li"><span class="ti-menu"></span>
										<ul>
											<li>
												<a class="tf_lightbox_link_region-swap" title="<?php _e('Swap', 'themify-flow') ?>" href="#">
													<span class="ti-back-right"></span>
												</a>
											</li>
											<li>
												<a class="tf_lightbox_link_region-edit" data-edit-url="<?php echo TF_Model::get_template_part_edit_url( $TF_Layout->region_header, array( 'tf_region' => 'header', 'parent_template_id' => $TF_Layout->layout_id ) ); ?>" title="<?php _e('Edit', 'themify-flow' ) ?>" href="#">
													<span class="ti-pencil"></span>
												</a>
											</li>
											<li>
												<a class="tf_lightbox_link_region-delete" title="<?php _e('Delete', 'themify-flow') ?>" href="#">
													<span class="ti-close"></span>
												</a>
											</li>
										</ul>
									</li>
								</ul>
							</div>
							<div class="tf_active_block_caption tf_interface"><?php echo sprintf( __('Template Part: %s', 'themify-flow'), TF_Model::get_template_part_title( TF_Model::get_shortcode_atts_val( $TF_Layout->region_header, 'slug' ) ) ); ?></div>
							<div class="tf_active_block_element">
								<?php echo $TF_Layout->render( $TF_Layout->region_header ); ?>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>

				<?php tf_hook_header_end(); // hook ?>
				
				</header>
				<!-- /#header -->
		        
		        <?php tf_hook_header_after(); // hook ?>
						
			</div>
			<!-- /#headerwrap -->
		<?php endif; ?>
		
		<div id="middlewrap"> 
			
			<div id="middle" class="pagewidth">

				<?php tf_hook_layout_before(); //hook ?>

				<?php tf_hook_content_before(); // hook ?>
				<!-- content -->
				<div id="content" class="tf_content">
			    	
			    	<?php tf_hook_content_start(); // hook ?>
									
					<div class="tf_content_builder">
						<?php if ( ! empty( $TF_Layout->layout_content ) ) {
							echo $TF_Layout->render( $TF_Layout->layout_content );
						}?>
					</div><!-- /tf_content -->
				    
					<?php tf_hook_content_end(); // hook ?>
				</div>
				<!-- /content -->
			    
			    <?php tf_hook_content_after(); // hook ?>

				<?php if ( 'sidebar_none' != $TF_Layout->sidebar ): ?>
					<?php tf_hook_sidebar_before(); // hook ?>

					<aside id="sidebar" class="tf_sidebar">

						<?php tf_hook_sidebar_start(); // hook ?>
					    
						<div data-region-area="sidebar" class="tf_template_part_region tf_template_part_region-edit">
							<a href="#" data-region="sidebar" class="tf_interface tf_template_region_button tf_template_region_button-add">
								<span class="ti-plus"></span>
								<?php _e('Add Template Part', 'themify-flow') ?>
							</a>
							<div class="tf_template_part_region_render_content">
								<?php if ( ! empty( $TF_Layout->region_sidebar ) ): ?>
								<div class="tf_active_block">
									<div class="tf_active_block_overlay"></div>
									<div class="tf_active_block_menu tf_interface">
										<ul class="tf_active_block_menu_ul">
											<li class="tf_active_block_menu_li"><span class="ti-menu"></span>
												<ul>
													<li>
														<a class="tf_lightbox_link_region-swap" title="<?php _e('Swap', 'themify-flow') ?>" href="#">
															<span class="ti-back-right"></span>
														</a>
													</li>
													<li>
														<a class="tf_lightbox_link_region-edit" data-edit-url="<?php echo TF_Model::get_template_part_edit_url( $TF_Layout->region_sidebar, array( 'tf_region' => 'sidebar', 'parent_template_id' => $TF_Layout->layout_id ) ); ?>" title="<?php _e('Edit', 'themify-flow' ) ?>" href="#">
															<span class="ti-pencil"></span>
														</a>
													</li>
													<li>
														<a class="tf_lightbox_link_region-delete" title="<?php _e('Delete', 'themify-flow') ?>" href="#">
															<span class="ti-close"></span>
														</a>
													</li>
												</ul>
											</li>
										</ul>
									</div>
									<div class="tf_active_block_caption tf_interface"><?php echo sprintf( __('Template Part: %s', 'themify-flow'), TF_Model::get_template_part_title( TF_Model::get_shortcode_atts_val( $TF_Layout->region_sidebar, 'slug' ) ) ); ?></div>
									<div class="tf_active_block_element">
										<?php echo $TF_Layout->render( $TF_Layout->region_sidebar ); ?>
									</div>
								</div>
								<?php endif; ?>
							</div>
						</div>
					    
						<?php tf_hook_sidebar_end(); // hook ?>

					</aside>
					<!-- /#sidebar -->

					<?php tf_hook_sidebar_after(); // hook ?>
				<?php endif; // sidebar if ?>

			<?php tf_hook_layout_after(); // hook ?>

			</div>
			<!-- /middle -->
			
		</div>
		<!-- /middlewrap -->
				
		<?php if( 'default' == $TF_Layout->footer ): ?>
		<div id="footerwrap">

			<?php tf_hook_footer_before(); // hook ?>
			<footer id="footer" class="tf_footer">
				<?php tf_hook_footer_start(); // hook ?>

				<div data-region-area="footer" class="tf_template_part_region tf_template_part_region-edit">
					<a href="#" data-region="footer" class="tf_interface tf_template_region_button tf_template_region_button-add">
						<span class="ti-plus"></span>
						<?php _e('Add Template Part', 'themify-flow') ?>
					</a>
					<div class="tf_template_part_region_render_content">
						<?php if( ! empty( $TF_Layout->region_footer ) ): ?>
						<div class="tf_active_block">
							<div class="tf_active_block_overlay"></div>
							<div class="tf_active_block_menu tf_interface">
								<ul class="tf_active_block_menu_ul">
									<li class="tf_active_block_menu_li"><span class="ti-menu"></span>
										<ul>
											<li>
												<a class="tf_lightbox_link_region-swap" title="<?php _e('Swap', 'themify-flow') ?>" href="#">
													<span class="ti-back-right"></span>
												</a>
											</li>
											<li>
												<a class="tf_lightbox_link_region-edit" data-edit-url="<?php echo TF_Model::get_template_part_edit_url( $TF_Layout->region_footer, array( 'tf_region' => 'footer', 'parent_template_id' => $TF_Layout->layout_id ) ); ?>" title="<?php _e('Edit', 'themify-flow' ) ?>" href="#">
													<span class="ti-pencil"></span>
												</a>
											</li>
											<li>
												<a class="tf_lightbox_link_region-delete" title="<?php _e('Delete', 'themify-flow') ?>" href="#">
													<span class="ti-close"></span>
												</a>
											</li>
										</ul>
									</li>
								</ul>
							</div>
							<div class="tf_active_block_caption tf_interface"><?php echo sprintf( __('Template Part: %s', 'themify-flow'), TF_Model::get_template_part_title( TF_Model::get_shortcode_atts_val( $TF_Layout->region_footer, 'slug' ) ) ); ?></div>
							<div class="tf_active_block_element">
								<?php echo $TF_Layout->render( $TF_Layout->region_footer ); ?>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
				
				<?php tf_hook_footer_end(); // hook ?>
			</footer>
			<!-- /#footer -->
			<?php tf_hook_footer_after(); // hook ?>
		</div>
		<!-- /#footerwrap -->
		<?php endif; ?>

	</div>
	<!-- /#pagewrap -->

	<!-- wp_footer -->
	<?php wp_footer(); ?>
	<?php tf_hook_body_end(); // hook ?>
</body>
</html>