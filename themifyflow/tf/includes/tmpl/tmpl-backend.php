<script type="text/html" id="tmpl-tf_import_link">
	<a href="#" class="add-new-h2 tf_lightbox_new" data-type="{{ data.type }}"><?php _e('Add New', 'themify-flow') ?></a>
	<a href="#" class="add-new-h2 tf_lightbox_import" data-type="{{ data.type }}"><?php _e('Add via Import', 'themify-flow') ?></a>
</script>

<script type="text/html" id="tmpl-tf_import_form">
	<?php
	$output = '<div class="tf_import_form_wrapper">'; 
	$output .= sprintf( '<h3>%s</h3>', __( 'Select a file to import', 'themify-flow') );

	if ( is_multisite() && !is_upload_space_available() ) {
		$output .= sprintf( __( '<p>Sorry, you have filled your %s MB storage quota so uploading has been disabled.</p>', 'themify-flow' ), get_space_allowed() );
	} else {
		$output .= sprintf( '<p>
								<div class="tf-plupload-upload-uic hide-if-no-js" id="%stf-plupload-upload-ui">
									<input id="%stf-plupload-browse-button" type="button" value="%s" class="tf_btn" />
								</div>
							</p>',
				'tf_import_file', 
				'tf_import_file', __('Upload', 'themify-flow') 
		);
		
		$max_upload_size = (int) wp_max_upload_size() / ( 1024 * 1024 );
		$output .= sprintf( __( '<p>Maximum upload file size: %d MB.</p>', 'themify-flow' ), $max_upload_size );
	}
	$output .= '</div>';
	echo $output;
	?>
</script>

<script type="text/html" id="tmpl-tf_lightbox">
	<div class="tf_admin_lightbox tf_interface {{ data.lightboxClass }}">
		<div class="tf_lightbox_title">{{ data.title }}</div>
		<# if ( data.closeBtn === 'yes' ) { #>
		<a href="#" class="tf_close_lightbox">&times;</a>
		<# } #>
		<div class="tf_lightbox_container"><?php echo tf_loader_span('small'); ?></div>
	</div>
</script>

<script type="text/html" id="tmpl-tf_template_part">
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
							<a class="tf_lightbox_link_region-edit" data-edit-url="{{ data.edit_url }}" title="<?php _e('Edit', 'themify-flow' ) ?>" href="#">
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
		<div class="tf_active_block_caption tf_interface">{{ data.caption }}</div>
		<div class="tf_active_block_element">{{{ data.element }}}</div>
	</div>
</script>

<script type="text/html" id="tmpl-tf_active_module">
	<?php if ( is_admin() ): ?>
	<div class="tf_module active_module" data-tf-module-title="{{ data.tf_module_title }}" data-tf-module="{{ data.tf_module }}" data-tf-content="{{ data.content }}" data-tf-atts="{{ data.atts }}">
		<div class="tf_active_module_menu">
			<div class="menu_icon">
			</div>
			<ul class="tf_dropdown">
				<li>
					<a class="tf_lightbox_link_module-edit" title="<?php _e('Edit', 'themify-flow') ?>" href="#">
						<span class="ti-pencil"></span> <?php _e('Edit', 'themify-flow') ?>
					</a>
				</li>
				<li>
					<a class="tf_lightbox_link_module-duplicate" title="<?php _e('Duplicate', 'themify-flow') ?>" href="#">
						<span class="ti-layers"></span> <?php _e('Duplicate', 'themify-flow') ?>
					</a>
				</li>
				<li>
					<a class="tf_lightbox_link_module-delete" title="<?php _e('Delete', 'themify-flow') ?>" href="#">
						<span class="ti-close"></span> <?php _e('Delete', 'themify-flow') ?>
					</a>
				</li>
			</ul>
		</div>
		<div class="module_label">
			<strong class="module_name">{{ data.tf_module_title }}</strong>
			<em class="module_excerpt"></em>
		</div>
	</div>

	<?php else: ?>
	<div class="tf_active_block active_module clearfix {{ data.tf_inline_block }}" data-tf-module-title="{{ data.tf_module_title }}" data-tf-module="{{ data.tf_module }}" data-tf-content="{{ data.content }}" data-tf-atts="{{ data.atts }}">
		<div class="tf_active_block_overlay"></div>
		<div class="tf_active_block_menu tf_interface">
			<ul class="tf_active_block_menu_ul">
				<li class="tf_active_block_menu_li"><span class="ti-menu"></span>
					<ul>
						<li>
							<a class="tf_lightbox_link_module-edit" title="<?php _e('Edit', 'themify-flow') ?>" href="#">
								<span class="ti-pencil"></span>
							</a>
						</li>
						<li>
							<a class="tf_lightbox_link_module-style" title="<?php _e('Style', 'themify-flow') ?>" href="#">
								<span class="ti-brush"></span>
							</a>
						</li>
						<li>
							<a class="tf_lightbox_link_module-duplicate" title="<?php _e('Duplicate', 'themify-flow' ) ?>" href="#">
								<span class="ti-layers"></span>
							</a>
						</li>
						<li>
							<a class="tf_lightbox_link_module-delete" title="<?php _e('Delete', 'themify-flow') ?>" href="#">
								<span class="ti-close"></span>
							</a>
						</li>
					</ul>
				</li>
			</ul>
		</div>
		<div class="tf_active_block_caption tf_interface">{{ data.tf_module_title }}</div>
		<div class="tf_active_block_element">{{{ data.element }}}</div>
	</div>
	<?php endif; ?>
</script>

<script type="text/html" id="tmpl-tf_row">
	<?php echo do_shortcode( '[tf_row][tf_column grid="fullwidth"][/tf_column][/tf_row]' ); ?>
</script>

<script type="text/html" id="tmpl-tf_sub_row">
	<?php echo do_shortcode( '[tf_sub_row][tf_sub_column grid="fullwidth"][/tf_sub_column][/tf_sub_row]' ); ?>
</script>

<script type="text/html" id="tmpl-tf_column">
	<div class="tf_col {{ data.newclass }}" data-tf-atts="{{ data.atts }}" data-tf-shortcode="{{ data.shortcode }}">
		<div class="tf_module_holder">
			<div class="tf_empty_holder_text">{{ data.placeholder }}</div>
		</div>
	</div>
</script>

<script type="text/html" id="tmpl-tf_back_column">
	<div class="tf_back_col {{ data.newclass }}" data-tf-atts="{{ data.atts }}" data-tf-shortcode="{{ data.shortcode }}">
		<div class="tf_module_holder">
			<div class="tf_empty_holder_text">{{ data.placeholder }}</div>
		</div>
	</div>
</script>

<script type="text/html" id="tmpl-tf_grid_menu">
	<?php echo tf_grid_lists('module'); ?>
</script>

<script type="text/html" id="tmpl-tf_row_droppable">
	<div class="tf_row_droppable clearfix {{ data.class }}">
		<div class="tf_empty_holder_text">{{ data.placeholder }}</div>
	</div>
</script>

<script type="text/html" id="tmpl-tf_element_row">
	<div class="tf_back_row clearfix tf_gutter_default" data-tf-shortcode="tf_back_row" data-tf-atts="">
			
		<div class="tf_back_row_top">
			
			<div class="tf_left">
				<div class="tf_back_row_menu">
					<div class="tf_menu_icon tf_row_btn"><span class="ti-menu"></span></div>
					<ul class="tf_dropdown">
						<li><a href="#" class="tf_back_delete_row">Delete</a></li>
					</ul>
				</div>

				<?php echo tf_grid_lists('row', null, array('grid_menu_class' => 'tf_grid_menu', 'grid_icon_class' => 'tf_row_btn') ); ?>
				<!-- /tf_grid_menu -->
			</div>
			<!-- /tf_left -->
	
			<div class="tf_right">
				<a href="#" class="tf_row_btn tf_toggle_row"></a>
			</div>
			<!-- /tf_right -->
	
		</div>
		<!-- /tf_back_row_top -->
	
		<div class="tf_back_row_content">
	
			<div class="tf_back_col tf_colfullwidth first" data-tf-shortcode="tf_back_column" data-tf-atts="<?php echo esc_attr( json_encode(array('grid' => 'fullwidth')) ); ?>">
				<div class="tf_module_holder">
					<div class="tf_empty_holder_text"><?php _e('drop module here', 'themify-flow') ?></div>
				</div>
			</div>
			<!-- /tf_back_col -->
	
		</div>
		<!-- /tf_back_row_content -->
	
	</div>
	<!-- /tf_back_row -->
</script>

<script type="text/html" id="tmpl-tf_module_helper">
	<div class="tf_interface tf_module_interface">
		<div class="tf_module module-type-{{ data.name }}" data-module-title="{{ data.title }}" data-module-name="{{ data.name }}">
			<strong class="module_name">{{ data.title }}</strong>
			<a href="#" title="<?php _e('Add module', 'themify-flow') ?>" class="add_module" data-module-name="{{ data.name }}"><?php _e('Add', 'themify-flow') ?></a>
		</div>
	</div>
</script>

<script type="text/html" id="tmpl-tf_back_module_helper">
	<div class="tf_interface tf_back_module_interface">
		<div class="tf_back_module tf_module_name_{{ data.slug }}" data-element-slug="{{ data.slug }}"> 
			<strong class="tf_module_name">{{ data.title }}</strong>
		</div>
	</div>
</script>

<script type="text/html" id="tmpl-tf_gallery_preview">
	<# _.each( data.sizes, function( image, key ){ #>
	<p class="tf_thumb_preview">
		<span class="tf_thumb_preview_placeholder"><img src="{{ image.thumbnail.url }}" width="50" height="50"></span>
	</p>
	<# } ); #>
</script>

<script type="text/html" id="tmpl-tf_routine_loader">
	<?php echo tf_loader_span(); ?>
</script>